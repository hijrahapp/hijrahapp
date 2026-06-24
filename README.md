# Hijrah App

> A personal-growth & self-development platform that guides users through structured **assessments**, **programs**, and **content enrichment** to support their personal "hijrah" (journey of transformation).

Hijrah App is the **backend + admin platform** behind [hijrah.life](https://hijrah.life). It is a single Laravel 12 codebase that serves two distinct surfaces:

1. **A mobile/customer REST API** — consumed by the end-user mobile app. Users take diagnostic **assessments** (methodologies), receive **weighted scores**, get **recommended programs** based on those scores, work through **program steps** and **liabilities** (obligations/checklists), and browse a library of **enrichment content**.
2. **A Livewire admin panel** (`/app/*`) — used by Admins, Super Admins, and Experts to author all of that content (methodologies, pillars, modules, questions, programs, liabilities, enrichments, feedback forms) and to review each user's answers and progress.

The product content is primarily **Arabic** (the API defaults to the `ar` locale); the admin panel UI is in English.

---

## Table of Contents

- [Core Concepts](#core-concepts)
- [Architecture at a Glance](#architecture-at-a-glance)
- [Tech Stack](#tech-stack)
- [Features](#features)
- [Local Development](#local-development)
- [Configuration & Feature Flags](#configuration--feature-flags)
- [API Overview](#api-overview)
- [Admin Panel](#admin-panel)
- [Testing](#testing)
- [Further Documentation](#further-documentation)

---

## Core Concepts

| Concept | Description |
|---|---|
| **Methodology** | A diagnostic assessment. Has a `type` (`simple`, `complex`, or `twoSection`) that controls its structure and how it is scored. |
| **Pillar** | A thematic grouping within a (complex/twoSection) methodology. Contains modules. |
| **Module** | A unit of assessment containing questions. Belongs to pillars and/or directly to methodologies. |
| **Question / Answer** | Survey items. Questions are attached at the methodology, pillar, or module level, each attachment carrying a **weight**. Answers carry a **percentage value (0–100)**. |
| **Scoring** | A weighted engine converts a user's selected answers into 0–100 percentage scores per module/pillar/methodology. These scores drive program recommendations. |
| **Program** | A guided improvement journey made of ordered **Steps**. Programs are *suggested* to a user when their module score falls within the program's `min_score`–`max_score` band. |
| **Step** | A single program activity: `journal`, `article`, `advice`, `daily_mission`, `quiz`, `video`, `audio`, `book`, or `challenge`. |
| **Liability** | An obligation/checklist ("todos") unlocked by completing certain modules. |
| **Enrichment** | Standalone content (articles, videos, audio, books) organized by categories, interests, and tags. Users can like/favorite. |
| **Feedback Form** | A versioned, language-scoped dynamic form attached to programs; users submit it after completing a program. |
| **Role** | `SuperAdmin`, `Admin`, `Expert`, `Customer`. Mobile users are always `Customer`; the admin panel is for the other three. |

A typical user flow: **sign up → set interests → take a methodology assessment → get scored → receive suggested programs → work through program steps & liabilities → submit feedback → explore enrichment content.**

---

## Architecture at a Glance

```
                ┌─────────────────────────────────────────────┐
   Mobile App ──┤  REST API  (routes/api.php, JSON, JWT)        │
                │  Controllers → Repositories → Services         │
                └───────────────┬─────────────────────────────┘
                                │
                    ┌───────────▼───────────┐
                    │   Domain / Services    │   ResultCalculationService
                    │   (scoring, status)    │   ContextStatusService
                    └───────────┬───────────┘
                                │
                          ┌─────▼─────┐
                          │  MySQL 8  │  (~38 tables, pivot-heavy)
                          └─────▲─────┘
                                │
                ┌───────────────┴─────────────────────────────┐
  Admin Users ──┤  Admin Panel (routes/web.php, Livewire 3)     │
                │  Full-page components → Tables → Modals        │
                └───────────────────────────────────────────────┘
```

- **Thin controllers → repositories (query logic) → services (scoring/status).** API resources enrich every response with per-user status and scores.
- **Two auth surfaces, one token format.** Both the API and the admin panel issue a custom **Firebase-JWT (HS256)** token. The API expects it in the `Authorization: Bearer` header; the admin panel stores it in the Laravel session.
- **Admin panel = Livewire full-page components.** Each section follows a *Page → Table → Modal* pattern, communicating via Livewire's event bus.

> For the full technical breakdown — data model, scoring algorithm, endpoint-by-endpoint reference, admin information architecture, and known tech debt — see **[`.claude/PROJECT.md`](.claude/PROJECT.md)**.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Language / Runtime | PHP 8.2+ |
| Framework | Laravel 12 |
| Admin UI | Livewire 3.6 |
| Frontend | TailwindCSS v4, Alpine.js 3, Vite 6, Metronic/KTUI theme, Quill editor |
| Database | MySQL 8 |
| Auth | Custom JWT (`firebase/php-jwt`), Firebase Auth (`kreait/laravel-firebase`), Google via Socialite |
| API Docs | Swagger / OpenAPI (`darkaonline/l5-swagger`) |
| Mail | SMTP (OTP & welcome emails, bilingual EN/AR templates) |
| Tooling | Pint (lint), PHPUnit, Pail (logs), Sail |

---

## Features

**Mobile / Customer API**
- Email/password signup & login with OTP email verification (toggleable)
- Social login: Firebase ID token and Google access token
- Password reset via OTP + time-boxed temp token
- Browse methodologies; fetch questions at methodology/pillar/module granularity
- Submit answers and retrieve them; automatic progress/status tracking
- Weighted scoring with branching ("dynamic") question support
- Suggested & enrolled programs with filters; start/complete/reset lifecycle
- Step progress (journals, quizzes, challenges, etc.)
- Liabilities (todo checklists) with completion gating
- Interests management; enrichment library with like/favorite
- Program feedback forms with aggregated stats

**Admin Panel**
- User management (Admins, Experts, Customers) with role-based visibility
- Full authoring of methodologies, pillars, modules, questions, tags ("banks")
- Program & step builder; liability builder; enrichment/category/interest management
- Dynamic feedback-form builder
- Per-user answer & progress inspection for methodologies, programs, liabilities, and feedback
- Methodology activation validation (completeness rules enforced before going live)

---

## Local Development

### Prerequisites
- PHP 8.2+ with Composer
- Node.js 18+ and npm
- MySQL 8 (a Docker container is the simplest path)

### Setup

```bash
# 1. Install dependencies
composer install
npm install

# 2. Environment
cp .env.example .env
php artisan key:generate
# Edit .env: set DB_* (host/port/user/password/database=hijrah) and JWT_SECRET

# 3. Database — start MySQL (example with Docker)
docker run -d --name hijrah-db -p 3306:3306 \
  -e MYSQL_ROOT_PASSWORD=password -e MYSQL_DATABASE=hijrah mysql:8.0

# 4. Migrate & seed
php artisan migrate
php artisan db:seed            # roles, demo data; see database/seeders/

# 5. Build assets & run
npm run build                  # or: npm run dev  (Vite watch)
php artisan serve              # http://127.0.0.1:8000
```

Then open **`http://127.0.0.1:8000/app/login`** for the admin panel. Seeded admin/expert accounts live in the `users` table (role via `roleId` → `roles`); the demo factory password is `password`.

> **One-command dev:** `composer dev` runs the server, queue listener, log tailer (Pail), and Vite concurrently.

### Generate API docs
```bash
php artisan l5-swagger:generate   # serves at /api/documentation
```

---

## Configuration & Feature Flags

Key `.env` values:

| Variable | Purpose |
|---|---|
| `DB_*` | MySQL connection (default DB name: `hijrah`) |
| `JWT_SECRET` | HS256 signing secret for API/admin tokens |
| `APP_LOCALE` / `APP_TIMEZONE` | Default `en` / `Africa/Cairo` (API requests default to `ar` via locale middleware) |
| `FIREBASE_*` | Firebase project ID, credentials file, database URL, storage bucket |
| `MAIL_*` | SMTP for OTP & welcome emails |
| `L5_SWAGGER_GENERATE_ALWAYS` | Regenerate Swagger docs on each request (dev only) |

Feature flags (`config/app.php → features`, all default `false`):

| Flag | Effect |
|---|---|
| `email_verification` | Enforce OTP verification on signup |
| `result_calculation` | Expose computed assessment scores |
| `dynamic_questions` | Enable branching/conditional surveys |
| `optimized_calculation` | Use the optimized scoring service |

---

## API Overview

Base path: `/api`. All routes run through `locale` middleware; most require `auth.jwt` + `auth.user`.

| Group | Highlights |
|---|---|
| `auth/*` | `signup`, `login`, `login/firebase`, `login/google`, `otp/verify`, `otp/resend`, `signup/complete` |
| `password/*` | `forget`, `otp/verify`, `reset` |
| `user/*` | create/list (Admin), delete (SuperAdmin) |
| `methodology/*` | browse methodologies, fetch questions, submit & read answers (4 granularities) |
| `program/*` | suggested/my programs + filters, detail, start/complete/reset, steps, feedback |
| `liability/*` | my liabilities, detail, todo updates, complete |
| `interest/*` | list, get/update user interests |
| `enrichment/*` | list, explore, detail, like, favorite |

Full request/response details are in the Swagger docs (`/api/documentation`) and in [`.claude/PROJECT.md`](.claude/PROJECT.md).

---

## Admin Panel

All admin routes are under `/app` and map directly to Livewire components (`routes/web.php`). Navigation groups:

- **Network** *(Admin/SuperAdmin only)* — Admins, Experts, Customers
- **Methodologies** — Methodologies, Pillars, Modules, Questions, Tags
- **Programs** — Programs, Liabilities, Feedback
- **Enrichments** — Enrichment, Category, Interest

Authentication is session-based: the Livewire `Login` component calls `AuthService::adminLogin()` (restricted to SuperAdmin/Admin/Expert), stores the JWT in the session, and each full-page component re-checks it in `mount()`.

---

## Testing

```bash
composer test          # clears config + runs php artisan test (PHPUnit)
./vendor/bin/pint       # code style
```

---

## Further Documentation

- **[`.claude/PROJECT.md`](.claude/PROJECT.md)** — comprehensive technical reference: data model & ERD, the scoring engine, full API reference, admin information architecture, auth internals, and known tech-debt/security notes.
- **`CLAUDE.md`** — repository workflow & AI-agent orchestration config.

---

*Hijrah App — built with Laravel. Product site: [hijrah.life](https://hijrah.life).*
