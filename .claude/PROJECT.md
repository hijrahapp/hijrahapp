# Hijrah App — Technical Reference

**Status:** Living document · Generated from a full code analysis + live run of the application.
**Stack:** Laravel 12 · Livewire 3.6 · PHP 8.2+ · MySQL 8 · TailwindCSS v4 / Alpine.js / Vite 6
**Product:** [hijrah.life](https://hijrah.life) — a personal-growth / self-development platform (content primarily Arabic).

This document is the deep technical companion to the top-level `README.md`. It describes *how the system actually works* — the data model, the scoring engine, the API surface, the admin panel, the auth internals, and the known tech debt.

---

## 1. What the Product Does

Hijrah ("journey of transformation") helps users improve themselves through a measure → recommend → guide loop:

1. **Measure** — the user completes a **methodology** (a structured, weighted assessment).
2. **Score** — answers are converted into 0–100 percentage scores per module / pillar / methodology.
3. **Recommend** — **programs** whose `min_score`–`max_score` band matches the user's module score are suggested.
4. **Guide** — the user works through program **steps** (journals, quizzes, challenges, media…) and **liabilities** (todo checklists), tracking progress.
5. **Reflect & Explore** — the user submits a **feedback form** after a program and browses **enrichment** content (articles/videos/audio/books) filtered by interests, categories, and tags.

Two surfaces consume one codebase:
- **Mobile/Customer REST API** (`routes/api.php`) — JSON, JWT-protected, Arabic-default.
- **Admin Panel** (`routes/web.php`, `/app/*`) — Livewire dashboard for Admins/SuperAdmins/Experts to author content and inspect user data.

> **Verified live** (local run against the seeded `hijrah` MySQL DB): the API issues JWTs and returns real Arabic content for interests/methodologies/enrichments/programs; the admin panel logs in and renders the full Metronic-themed dashboard with populated tables. 38 seeded users across 4 roles.

---

## 2. Architecture

```
app/
├── Http/
│   ├── Controllers/        # thin; delegate to repositories/services
│   │   ├── AuthController, PasswordController, UserController
│   │   ├── MethodologyController, QuestionController, UserAnswerController
│   │   ├── ProgramController, StepController, LiabilityController
│   │   ├── InterestController, EnrichmentController
│   │   └── App/            # admin-side helper controllers (User, UserDetails)
│   ├── Repositories/       # query logic (pivot-heavy raw DB), one per domain
│   ├── Services/           # AuthService, FirebaseService, GoogleService, OTPService, UserService
│   ├── Middleware/         # JwtMiddleware, UserMiddleware, RoleMiddleware, LocaleMiddleware, LogRequestMiddleware
│   ├── Requests/           # SubmitProgramFeedbackRequest (most validation is inline)
│   └── Resources/          # CategoryResource (others live in app/Resources)
├── Services/               # ResultCalculationService, ResultCalculationOptimizedService, ContextStatusService
├── Resources/              # API resources (Methodology/Pillar/Module/Question/Answer/Program/Step/…) incl. *DetailedResource pairs
├── Livewire/               # admin panel components (see §7)
├── Models/                 # 24 Eloquent models (see §4)
├── Enums/                  # RoleName, QuestionType
├── Traits/                 # WithoutUrlPagination, WithTableReload, WithTableSorting, HasTagTitles, DeletesStoredImages
├── Rules/                  # FileUrlValidation
├── Utils/                  # JWTUtils
├── Mail/                   # OTP / welcome / signup mailables
└── Swagger/                # OpenAPI annotations
```

**Layering:** `Controller (thin) → Repository (queries) → Service (scoring/status) → Model/DB`. API **Resources** receive an injected `user_id`/`section_number` and call the status/scoring services to enrich every response per-user.

**Request pipeline (API):** `locale` → `auth.jwt` → `auth.user` → (`auth.role`) → controller. `LogRequestMiddleware` is appended globally.

---

## 3. Domain Model & Relationships

### 3.1 The assessment hierarchy

```
Methodology (type: simple | complex | twoSection)
   ├──< methodology_pillar (section, weight) >── Pillar
   │                                               └──< pillar_module (methodology_id, weight) >── Module
   ├──< methodology_module (direct modules) >──────────────────────────────────────────────────── Module
   │
   └── questions attached at THREE levels, each pivot carrying a `weight` (+ sequence):
         methodology_question (item_id → owning pillar or module)
         pillar_question      (methodology_id)
         module_question      (methodology_id, pillar_id)

Question ──< questions_answers >── Answer
AnswerContext (answer_contexts): (context_type, context_id = pivot row id, answer_id, weight 0–100,
                                  dependent_context_type/id)   → per-context answer score + branching graph
Pillar self-relation: pillar_dependencies (pillar_id, depends_on_pillar_id, methodology_id)
Module self-relation: module_dependencies
```

- **`Methodology.type` drives everything.** `simple` → questions grouped by **module**; `complex`/`twoSection` → grouped by **pillar**. `twoSection` additionally splits pillars into `first`/`second` sections (`methodology_pillar.section`).
- **Weights live on the pivots**, not the questions/answers themselves. The same question can carry a different weight in different methodologies/modules.
- **`answer_contexts`** (historically `question_answer_weights`) is the crux of scoring: it maps a `(context, answer)` to a 0–100 percentage and optionally to a *dependent* context for dynamic/branching surveys.

### 3.2 Programs, liabilities, content

| Relationship | Pivot / detail |
|---|---|
| `Program` ─< `program_module` >─ `Module` | carries `methodology_id`, `pillar_id`, `min_score`, `max_score` (the suggestion band) |
| `Program` ─hasMany─ `Step` | ordered activities; `Step::TYPES` = journal, article, advice, daily_mission, quiz, video, audio, book, challenge |
| `Program` ─hasMany─ `ProgramFeedback` | feedback responses (JSON) |
| `Liability` ─< `liability_module` >─ `Module` | unlocked by completed modules; `todos` = JSON array (also `LiabilityModule` model) |
| `Enrichment` | `interests`, `categories`, `tags` stored as **JSON ID arrays** (resolved on demand), not pivots |
| `Category` / `Interest` / `Tag` | lookup tables with `active` flags |
| `FeedbackForm` | versioned, language-scoped; `form_structure` JSON self-generates validation rules |

### 3.3 User state & answers

| Model / table | Role |
|---|---|
| `User` | `belongsTo Role` via `roleId`; `interests` = JSON array of Interest IDs; `firebase_uid`, `otp`, `active`, `gender`, `birthDate` |
| `Role` | `name` cast to `RoleName` enum; `hasMany User` |
| `UserAnswer` (`user_answers`) | one row **per selected answer**: `(user_id, context_type[methodology/pillar/module/step], context_id, question_id, answer_id)` |
| `UserContextStatus` (`user_context_statuses`) | `(user_id, context_type, context_id, methodology_id, pillar_id, status)`; `methodology_id`/`pillar_id` use `0` as a "none" sentinel |
| `UserProgram` (`user_programs`) | `status`, `started_at`, `completed_at` |
| `UserStepProgress` | per-step `status`, `thought`, `score`, `challenges_done` (JSON), `percentage` |
| `UserLiabilityProgress` | `completed_todos` (JSON), `is_completed` |
| `UserEnrichment` | `like`, `favorite` booleans |
| `AnswerContext` | (see 3.1) — scoring weights + dependency graph |

### 3.4 Migration history note

~80 incremental migrations (`a1…i9`) show heavy schema churn: `question_answer_weights` → `answer_contexts`; an `objectives` table → `steps`; `item_id` added late to `methodology_question`; section question-meta added then dropped. The codebase carries the resulting debt (commented-out blocks, dual aggregation strategies, leftover loggers).

---

## 4. The Scoring Engine (heart of the app)

Three services in `app/Services/`. Gated by feature flags `result_calculation` and `optimized_calculation`.

### `ResultCalculationService`

Computes weighted percentage scores of a user's answers against the methodology structure.

**`calculateMethodologyResult($userId, $methodologyId)`**
1. **Guard:** returns `null` unless **all** methodology questions are answered.
2. **Branch by type:**
   - `complex` / `twoSection` → group by **pillar** (`methodology_question.item_id`); `twoSection` restricts to `methodology_pillar.section = 'first'`.
   - `simple` → group by **module** (`item_id`).
3. One aggregate SQL joins `methodology_question → answer_contexts → user_answers`, summing the selected answers' weights per question → `answer_percent` (clamped 0–100).
4. **Per-group score** = `Σ(questionWeight × answerPercent) / Σ(questionWeight)` (weight-normalized, 0–100). Overall score uses the same formula across all in-scope questions.
5. Emits `pillars[]` / `modules[]` with `percentage`, plus a `summary` (total/answered questions, completion_rate). *(`summary.text` is currently a `lorem_ipsum` placeholder.)*

**Other methods**
- `calculateModuleResult($userId, $moduleId, $methodologyId, $pillarId?)` — module-scoped; joins `module_question → answer_contexts → user_answers`. **This is what program suggestions use.** Returns `null` if the module has no in-scope questions.
- `calculatePillarResult` = **simple average** of its module percentages.
- `calculateSectionResult` = **simple average** of the section's pillar percentages.
- `getPillarStatus` / `getModuleStatus` — read `user_context_statuses` (`pillar_id = 0` = "no pillar").

> ⚠️ **Mixed aggregation philosophy:** methodology-level scoring is *weighted*, but pillar/section roll-ups are *plain averages*. Intentional or not, it's worth knowing when interpreting scores.

### `ResultCalculationOptimizedService`
A performance-oriented variant selected when the `optimized_calculation` flag is on. `ProgramRepository` injects this one directly.

### `ContextStatusService`
Pure status resolver (`not_started | in_progress | completed`) across every entity type: methodology (by answered-question count), pillar (rolled up from module statuses), module (`user_context_statuses`), program (`user_programs.status`), step (`user_step_progress`), liability (`is_completed` / any completed todos). Used by API resources to surface per-user progress.

### Program suggestion logic (`ProgramRepository::getSuggestedPrograms`)
For each active program → each linked module in `program_module` (with `methodology_id`, `pillar_id`, `min_score`, `max_score`): compute the user's module score; if `min_score ≤ score ≤ max_score`, the program qualifies (first qualifying module wins). Methodologies/pillars/statuses are bulk-loaded to avoid N+1. **This is the bridge from assessment results to recommendations.**

---

## 5. Authentication & Authorization

### Token model — custom JWT
- **Custom JWT via `firebase/php-jwt` (HS256)** — *not* `tymon/jwt-auth`. Helper: `app/Utils/JWTUtils.php`. Secret = `config('app.jwt_secret')` (`JWT_SECRET`).
- Payload: `{ sub: userId, name, email, role, expiry }`.
- `generateTokenResponse()` → **non-expiring** token (normal login/signup). `generateTempTokenResponse()` → 15-minute token (password-reset flow).
- Response shape: `{ access_token, token_type: "bearer", user: UserResource }`.

### Middleware (aliased in `bootstrap/app.php`)

| Alias | Class | Responsibility |
|---|---|---|
| `auth.jwt` | `JwtMiddleware` | Parse `Authorization: Bearer`, decode JWT, check `expiry`, merge `authUserId`. 401 on missing/invalid/expired. |
| `auth.user` | `UserMiddleware` | Load user by `authUserId`; 404 if missing, 403 if `!active`; merges `authUser`. |
| `auth.role` | `RoleMiddleware` | Variadic roles (`auth.role:Admin`). **SuperAdmin bypasses all checks.** |
| `locale` | `LocaleMiddleware` | Reads `locale` header (`en`/`ar`), **defaults `ar`**. Wraps the whole API group. |
| (global) | `LogRequestMiddleware` | Appended to all requests. |

### API auth flows (`AuthController` + `app/Http/Services/`)
- **Signup** (`POST /auth/signup`): dedup by email/verification; assigns `Customer`, `active=false`, generates 4-digit OTP (15-min), emails it only if `email_verification` flag is on; returns a JWT immediately (201).
- **Complete signup** (`POST /auth/signup/complete`): sets optional `gender`/`birthDate`.
- **Login** (`POST /auth/login`): email → `Hash::check` → active check → JWT.
- **OTP** (`POST /auth/otp/verify`, `/resend`): activates user, sets `email_verified_at`, sends WelcomeMail, returns fresh JWT.
- **Firebase login** (`POST /auth/login/firebase`): verify Firebase ID token → find/create local Customer (`firebase_uid`, `active=true`) → JWT + `isNewUser`.
- **Google login** (`POST /auth/login/google`): `signInWithIdpAccessToken('google.com', token)` → verify → same find/create path.
- **Password reset:** `forget` (send OTP) → `otp/verify` (returns 15-min temp JWT) → `reset` (requires temp token; rejects same-as-current; returns full JWT).

### Roles & permissions
- `app/Enums/RoleName.php`: `SuperAdmin`, `Admin`, `Expert`, `Customer`. Single-role model (one `roleId` per user); no granular policies.
- Mobile API: all signup/social paths create **Customer**. Only user-management routes are role-gated: `POST /user` & `GET /user/all` (Admin), `DELETE /user` (SuperAdmin).

### Admin panel auth (web) — session-stored JWT
- The Livewire `Auth\Login` component calls `AuthService::adminLogin($email, $password)`, which requires the role to be **SuperAdmin/Admin/Expert** (the API's generic `login()` has no such restriction).
- On success it stores `session('jwt_token')` + `session('user')` and redirects.
- **There is no route-level middleware on `routes/web.php`.** Each full-page Livewire component re-checks `session('jwt_token')` in `mount()`. Role-based routing/visibility is enforced in `Homepage\Index::mount()`, the sidebar blade, and `CustomersTable::isUserEditable()` — i.e. **session-client authorization, not server-route enforcement.**

---

## 6. API Reference

Base path `/api`. All under `locale`; most groups add `auth.jwt` + `auth.user`. (See Swagger at `/api/documentation` for payloads.)

### Auth & account
| Method | Path | Notes |
|---|---|---|
| POST | `/auth/signup` | create Customer, returns JWT |
| POST | `/auth/login` | email/password |
| POST | `/auth/login/firebase` | Firebase ID token |
| POST | `/auth/login/google` | Google access token |
| POST | `/auth/signup/complete` | gender/birthDate (auth) |
| POST | `/auth/otp/verify`, `/auth/otp/resend` | OTP (auth) |
| POST | `/password/forget`, `/password/otp/verify`, `/password/reset` | reset flow |
| POST/GET/DELETE | `/user`, `/user/all`, `/user` | create/list (Admin), delete (SuperAdmin) |

### Methodologies & answers (`/methodology`)
- `GET /all`, `GET /{id}`, `GET /{id}/section/{1|2}` *(twoSection only)*
- `GET /{id}/pillar/{pillarId}`, `/{id}/module/{moduleId}`, `/{id}/pillar/{pillarId}/module/{moduleId}`
- Questions: `GET /{id}/questions`, `…/pillar/{pillarId}/questions`, `…/module/{moduleId}/questions`, `…/pillar/{pillarId}/module/{moduleId}/questions`
  - With `dynamic_questions` on, returns `{ type: 'simple'|'dynamic', list }` and attaches `next_question_id` per answer.
- Submit answers: `POST …/answers` at all four granularities. Body `answers[].{question_id, answerIds[]}` + optional `endQuestions` (true → sets `UserContextStatus` to `completed`).
- Read answers: `GET …/answers` (returns `UserAnswerGroupedResource`, grouped by `question_id_contextId`).

### Programs (`/program`)
- `GET /suggested` (+ `/suggested/filters`), `GET /my` (+ `/my/filters`), `GET /{id}`
- Steps: `GET /{id}/steps`, `GET /{id}/step/{stepId}` (auto-starts; loads quiz Q&A)
- Step progress: `POST /{id}/step/{stepId}/start | /complete | /challenge` (type-specific payload validation)
- Lifecycle: `POST /{id}/start | /complete | /reset`
- Feedback: `GET /feedback/form`, `POST /{id}/feedback` (only if completed), `GET /{id}/feedback/stats`

### Liabilities (`/liability`)
- `GET /my` (+ `/my/filters`), `GET /{id}`, `POST /{id}/todo/update`, `POST /{id}/complete` (gated on all todos done)

### Interests (`/interest`)
- `GET /all`, `GET /user`, `POST /user/update` — interests stored as a JSON ID array on `users.interests`

### Enrichment (`/enrichment`)
- `GET /all` (filters: category names / type / search; dynamic `metadata` facets), `GET /explore`, `GET /{id}`, `POST /{id}/like`, `POST /{id}/favorite`

---

## 7. Admin Panel (Livewire)

All routes GET-only under `/app`, each mapped to a Livewire full-page component (`routes/web.php`). `/` redirects to `homepage.index`.

### Navigation / information architecture
| Group | Items | Notes |
|---|---|---|
| **Network** | Admins, Experts, Customers | gated to SuperAdmin/Admin in `sidebar.blade.php` |
| **Methodologies** | Methodologies, Pillars, Modules, Questions, Tags | "banks" of reusable content |
| **Programs** | Programs, Liabilities, Feedback | |
| **Enrichments** | Enrichment, Category, Interest | content & taxonomy |

Drill-down (non-sidebar) routes: `methodology.manage` / `.questions` / `.users` / `.user.answers`; `program.manage` / `.users` / `.user.answers`; `liability.manage` / `.users` / `.user.details`; `enrichment.manage`; `feedback-forms`; `program.feedback.users` / `.user.details`.

### Component pattern (per section)
1. **Page component** (e.g. `Methodologies`) — thin, `#[Layout('layouts.index')]`, auth check in `mount()`, renders a blade embedding the table + modal children.
2. **Table component** (e.g. `MethodologyTable`) — list state, pagination, filters, search, row actions, event listeners. Data via `#[Computed]` wrapped in `handleReloadState()`. Search = `where('name','like',…)`; tag filter = `whereJsonContains`.
3. **Modal component** (e.g. `MethodologyAddModal`) — shared add/edit form (`isEditMode`), dynamic `rules()`, `save()` with validation + `dispatch('refreshTable')` + toast.
4. **Manage component** (e.g. `MethodologyManage`) — large multi-section detail editor with per-section dirty tracking (`updated()` hook) and scoped `saveX()` methods.

### Cross-component conventions
- **Event-bus heavy:** `$this->dispatch(...)` / `$listeners` rather than nested props.
- **Confirmations:** a global `Shared\Modals\ConfirmationModal` (mounted once in `layouts/index.blade.php`) takes a callback name + payload and re-dispatches it back to the requesting component on confirm.
- **Shared inputs** (`Shared/Components/*`): `TagPicker`, `CategoryPicker`, `InterestPicker`, `ImagePicker`, `FilePicker`, `RichText`, `Textarea`, `ListInput` — two-way bound via Livewire `#[Modelable]`.
- **Toasts:** `dispatch('show-toast', …)` (KTUI toast).
- **Pagination:** mostly `WithoutUrlPagination` (page state not in URL) + the custom `components/ktui-pagination.blade.php`; a few use stock Livewire pagination.
- **Auth:** `session('jwt_token')` check repeated in every `mount()` (no shared base/middleware).
- **Embedded domain logic:** e.g. `MethodologyTable::validateMethodologyForActivation()` runs substantial raw-DB completeness checks before a methodology can be activated — business logic living inside a UI component.

### Frontend stack & assets
- TailwindCSS **v4** (`@tailwindcss/vite`) + a thin custom `.kt-*` layer in `resources/css/app.css`.
- **Alpine.js 3**, **Vite 6**, axios. **Metronic / KTUI** purchased theme shipped as static assets under `public/assets/` (loaded via `asset()`), plus hand-written `resources/js/app.js` reimplementing Metronic behaviors (drawers/menus/modals) — some duplication with vendored `ktui.min.js`. **Quill 1.3.7** via CDN for rich text.
- Layout shell `resources/views/layouts/index.blade.php` mounts singleton components (search, confirmation modal, user profile, change password), sidebar, header (mega-menu in `@persist`), footer, `{{ $slot }}`. Bare `layouts/auth.blade.php` for login/reset.

---

## 8. Shared Utilities

| Type | Item | Purpose |
|---|---|---|
| Trait | `WithoutUrlPagination` | Livewire pagination without URL query state; forces `ktui-pagination` view |
| Trait | `WithTableReload` | "reload shimmer" UX (1ms JS timeout → `finishTableReload()`) |
| Trait | `WithTableSorting` | `sortBy`/`sortDirection`, `applySorting(Builder)` (used by per-user lists) |
| Trait | `HasTagTitles` | resolve tag IDs → titles |
| Trait | `DeletesStoredImages` | model boot hook; deletes orphaned `/storage/` images on delete/update (`imageUrlAttributes()`) |
| Rule | `FileUrlValidation` | validates media URLs by extension/streaming-service allowlist; `::video()`/`::audio()`/`::book()` factories |
| Util | `JWTUtils` | JWT encode/decode; `generateTokenResponse` (non-expiring) / `generateTempTokenResponse` (15-min) |
| Enum | `QuestionType` | 7 types (YesNo, TrueFalse, MCQSingle, MCQMultiple, ratings, scale…) with `getAnswers()`, `requiresCustomAnswers()`, `getLabel()` |
| Enum | `RoleName` | SuperAdmin / Admin / Expert / Customer |

**Localization:** `resources/lang/{en,ar}/` (`messages.php`, `mail.php`, `lookups.php`). `LocaleMiddleware` defaults to **Arabic**. `Step`/`UserStepProgress` translate display attributes via `trans('lookups.…')`. Email templates exist in both `emails/en` and `emails/ar`. `APP_TIMEZONE=Africa/Cairo`.

---

## 9. Configuration & Feature Flags

`.env` essentials: `DB_*` (default DB `hijrah`), `JWT_SECRET`, `APP_LOCALE`/`APP_TIMEZONE`, `FIREBASE_*` (project id, credentials file under `storage/app/firebase/`, db url, storage bucket), `MAIL_*`, `L5_SWAGGER_GENERATE_ALWAYS`.

Feature flags (`config/app.php → features`, all default `false`):

| Flag | Effect |
|---|---|
| `email_verification` | Enforce OTP on signup. **When off, OTP value comparison is skipped entirely** (any 4-digit value verifies). |
| `result_calculation` | Expose computed assessment scores in resources. |
| `dynamic_questions` | Branching/conditional surveys (uses `answer_contexts.dependent_*`). |
| `optimized_calculation` | Use `ResultCalculationOptimizedService`. |

---

## 10. Local Development Runbook

> This is the exact path validated during analysis (macOS, PHP via Herd, MySQL via Docker).

```bash
composer install && npm install
cp .env.example .env
php artisan key:generate
# .env: DB_HOST=127.0.0.1, DB_PASSWORD=…, DB_DATABASE=hijrah, JWT_SECRET=…, SESSION_DRIVER=file

# MySQL (existing container or fresh):
docker run -d --name hijrah-db -p 3306:3306 \
  -e MYSQL_ROOT_PASSWORD=password -e MYSQL_DATABASE=hijrah mysql:8.0
php artisan migrate && php artisan db:seed

php artisan config:clear
npm run build
php artisan serve --host=127.0.0.1 --port=8000
```

- **Admin panel:** `http://127.0.0.1:8000/app/login`. Seeded admin emails are in `users` (role via `roleId`). Reset a test admin's password through tinker if unknown:
  ```php
  php artisan tinker --execute="\$u=App\Models\User::where('email','testadmin1@gmail.com')->first(); \$u->password=Hash::make('password'); \$u->active=1; \$u->save();"
  ```
- **API smoke test:**
  ```bash
  curl -X POST http://127.0.0.1:8000/api/auth/login \
    -H 'Content-Type: application/json' -H 'Accept: application/json' \
    -d '{"email":"testadmin1@gmail.com","password":"password"}'
  # → { access_token, token_type: "bearer", user: {...} }
  ```
- **Swagger:** `php artisan l5-swagger:generate` then `/api/documentation`.
- **All-in-one dev:** `composer dev` (server + queue + Pail logs + Vite).

---

## 11. Known Tech Debt & Security Notes

These are **read-only observations** from analysis — flagged for awareness, not yet fixed.

**Security**
1. **Non-expiring access tokens** with no refresh/revocation (`JWTUtils::generateTokenResponse` sets `expiry => null`) — applies to both API and admin session token.
2. **OTP bypass:** when `email_verification` is off, `OTPService::validate()` skips the OTP value check — any 4-digit code verifies.
3. **Plaintext credential logging:** `UserService` (`logger('password')…`) and the admin `Auth\Login::login()` (`logger($this->password)`) write passwords to logs.
4. **Exception leakage:** several API error responses return `$e->getMessage()` (Enrichment returns `$e->getTrace()`) to clients — should be gated to non-production.
5. **No web route middleware:** `/app/*` protection relies entirely on per-component `mount()` checks; a component missing the check would be unprotected.

**Quality / maintainability**
6. **Mixed scoring aggregation:** weighted at methodology level, simple average at pillar/section.
7. **Heavy domain logic in UI components** (e.g. `MethodologyTable::validateMethodologyForActivation`) — hard to test/reuse.
8. **Leftover debug logging / commented code** from schema migrations (e.g. `UserAnswerRepository::getUserAnswers` `Log::info`).
9. **Inconsistent validation:** mostly inline `Validator::make()` (only one Form Request); mixes 400/422 status codes.
10. **Resources split across two namespaces** (`app/Http/Resources` vs `app/Resources`).
11. **`RoleMiddleware` typo** — `__('mesages.unauthorized')` (missing "s") yields an untranslated key on one error path.
12. **JS duplication** — hand-written `app.js` reimplements behaviors also provided by vendored `ktui.min.js`.

---

## 12. Key File Map

| Area | Files |
|---|---|
| API routes | `routes/api.php` |
| Web/admin routes | `routes/web.php` |
| Middleware registration | `bootstrap/app.php` |
| Auth | `app/Http/Controllers/{AuthController,PasswordController}.php`, `app/Http/Services/*.php`, `app/Http/Middleware/{Jwt,User,Role,Locale}Middleware.php`, `app/Utils/JWTUtils.php` |
| Scoring | `app/Services/{ResultCalculationService,ResultCalculationOptimizedService,ContextStatusService}.php` |
| Query logic | `app/Http/Repositories/*Repository.php` |
| Admin exemplars | `app/Livewire/Homepage/Methodologies/{Methodologies,MethodologyTable,MethodologyAddModal,MethodologyManage}.php`, `app/Livewire/Shared/Modals/ConfirmationModal.php`, `app/Livewire/Shared/Components/TagPicker.php`, `app/Livewire/Homepage/Shared/Sidebar.php` |
| Layouts | `resources/views/layouts/{index,auth}.blade.php`, `layouts/partials/{head,scripts}.blade.php` |
| Frontend config | `package.json`, `vite.config.js`, `resources/css/app.css`, `resources/js/app.js` |
| Config & flags | `config/app.php` (`features`), `.env.example` |
| Data | `database/migrations/` (~80), `database/seeders/`, `app/Models/` |

---

*Generated via multi-agent code analysis + a live local run (API + admin panel verified end-to-end). Update this file as the system evolves.*
