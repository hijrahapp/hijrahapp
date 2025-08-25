## ResultCalculationService — How scores are calculated

This document explains, in simple terms, how user answers are turned into percentages for Methodologies, Sections, Pillars, and Modules by `App\Services\ResultCalculationService`.

### TL;DR
- **Every question has a weight** (0–100). Every selected answer choice has a **percentage weight** (0–100).
- A question’s contribution = `question_weight * selected_answer_percent / 100`.
- **Overall methodology percentage** = sum of contributions for all methodology questions (global weights).
- **Pillar/Module percentages** are computed with the same contributions, but question weights are first **normalized so each pillar/module sums to 100** before summing.
- Unanswered questions contribute **0**.

### Data model (simplified)
- `user_answers`: stores what a user chose. Context can be `methodology` or `module`.
- `answer_contexts`: maps each answer choice to a numeric percent (0–100) per context (`methodology_question` or `module_question`).
- `methodology_question`: questions attached to a methodology with a `weight`; for complex/twoSection, `item_id` refers to a Pillar; for simple, `item_id` refers to a Module.
- `module_question`: questions attached to a module in a given methodology (and optionally to a pillar) with a `weight`.
- `methodology_pillar` (pivot): links pillars to a methodology and marks their `section` (first/second) when type is `twoSection`.
- `user_context_statuses`: stores completion status for module contexts.

### Methodology types
- **complex** or **twoSection**: Questions live at the methodology level; each question is assigned to a Pillar via `methodology_question.item_id`.
  - For `twoSection`, `calculateMethodologyResult()` uses only pillars in the `first` section. `calculateSectionResult()` computes a specific section (`first` or `second`).
- **simple**: Questions still live at the methodology level, but each question is assigned to a Module via `methodology_question.item_id`.

---

### 1) Method: calculateMethodologyResult(userId, methodologyId)

Returns a structure with:
- For complex/twoSection: `pillars[]` + `summary`
- For simple: `modules[]` + `summary`

Common summary fields:
- `overall_percentage`: the weighted sum of all methodology questions (using global weights).
- `total_questions`: total methodology questions.
- `answered_questions`: distinct questions the user answered in the methodology context.

How contributions are computed (all types):
1. For each methodology question, fetch the selected answer choice percent from `answer_contexts` (0–100), clamp to [0, 100].
2. Contribution to overall = `question_weight * answer_percent / 100`.
3. `overall_percentage` = sum of all contributions across all methodology questions, rounded to 2 decimals.

How pillar/module percentages are computed:
- Weights are first normalized within each group so that the group’s question weights sum to 100.
- For each question in the group:
  - `normalized_question_weight = question_weight * (100 / sum_of_group_weights)`.
  - Group contribution = `normalized_question_weight * answer_percent / 100`.
- Pillar/Module percentage = sum of group contributions, rounded to 2 decimals.

Counts shown per pillar/module:
- `summary.total_questions`: count of questions attached to that pillar/module in the methodology.
- `summary.answered_questions`: distinct count of answered questions within that group.
- `summary.completion_rate`: `answered / total * 100`.

Notes for `twoSection`:
- `calculateMethodologyResult()` includes only pillars in the `first` section when the methodology is `twoSection`.

---

### 2) Method: calculateSectionResult(userId, methodologyId, sectionNumber)

Only valid for `twoSection` methodologies.
- Picks `section = 'first'` when `sectionNumber = 1`, otherwise `'second'`.
- Gathers the pillars for that section.
- For each pillar, calls `calculatePillarResult(...)` and collects:
  - Pillar `percentage` (already normalized within the pillar’s modules).
  - Pillar `summary` (question totals/answered).
- Section `summary.overall_percentage` is the **average of the pillar percentages** (rounded to 2 decimals).
- Section `summary.total_questions` and `answered_questions` are sums across its pillars.
- Convenience: `percentage` is also set on the top-level result equal to `summary.overall_percentage`.

---

### 3) Method: calculatePillarResult(userId, pillarId, methodologyId)

- Gets the pillar’s modules for the given methodology via `modulesForMethodology(methodologyId)`.
- For each module, calls `calculateModuleResult(...)`.
- Pillar `summary.overall_percentage` is the **average of its module percentages** (rounded to 2 decimals).
- `summary.total_questions` and `answered_questions` are sums across those modules.
- Convenience: `percentage` is also set equal to `summary.overall_percentage`.

---

### 4) Method: calculateModuleResult(userId, moduleId, methodologyId, pillarId = null)

Context: This uses the `module` context in `user_answers` and `module_question` rows.

Steps:
1. Count `total_questions` from `module_question` filtered by `methodology_id`, `module_id`, and optional `pillar_id` (or `NULL` if not set).
   - If `total_questions = 0`, returns `null`.
2. For each question, find the selected answer percent from `answer_contexts` (context type `module_question`), clamp to [0, 100].
3. Accumulate `overallScore += question_weight * answer_percent / 100`.
4. Compute `answered_questions` = distinct questions answered by the user in this module context.
5. Return:
   - `percentage` = `overallScore` rounded to 2 decimals.
   - `summary.total_questions`, `summary.answered_questions`, and `summary.completion_rate` (`answered / total * 100`).

---

### 5) Status helpers

#### getPillarStatus(userId, pillarId, methodologyId)
- Looks at `user_context_statuses` for all modules in that pillar for the given methodology.
- Returns:
  - `not_started` if no statuses exist yet or no modules are linked.
  - `in_progress` if any module is `in_progress` or if some module statuses are missing.
  - `completed` only when all module statuses exist and none are `in_progress`.

#### getModuleStatus(userId, moduleId, methodologyId, pillarId = null)
- Reads the row from `user_context_statuses` for that module + methodology (+ pillar or `pillar_id = 0`).
- Returns the stored status or `not_started` if none exists.

---

### Important implementation details and assumptions
- Answer percents are clamped to `[0, 100]` before use.
- Unanswered questions contribute `0` to scores.
- For methodology-level `overall_percentage`, weights are used **globally** (no per-group normalization). This ensures each question’s global weight directly reflects its intended impact on the overall methodology score.
- For pillar/module percentages, question weights are **normalized within the group** so each pillar/module sums to 100. This makes each group’s percentage intuitively comparable regardless of how many questions it has or how they were weighted globally.
- For `twoSection` methodologies:
  - `calculateMethodologyResult()` computes pillar breakdown using only pillars in the `first` section.
  - `calculateSectionResult()` is the dedicated per-section rollup (first or second).

---

### Tiny example
Assume a simple methodology with two modules, and two questions per module.

- Module A weights: 20 and 30 (sum = 50). Selected answer percents: 50% and 80%.
  - Module A normalized weights: 20→40, 30→60 (sum = 100).
  - Module A percentage = 40×0.50 + 60×0.80 = 20 + 48 = 68.

- Module B weights: 10 and 40 (sum = 50). Selected answer percents: 100% and 0%.
  - Module B normalized weights: 10→20, 40→80.
  - Module B percentage = 20×1.00 + 80×0.00 = 20.

- Overall methodology percentage (global) uses original weights (20, 30, 10, 40):
  - Sum = (20×0.50) + (30×0.80) + (10×1.00) + (40×0.00)
  - = 10 + 24 + 10 + 0 = 44.

This shows how per-module percentages are normalized within each module, while the overall percentage respects the original global weights across all questions.


