# WPAsk Plugin — Build State Tracker

> **📌 Notice to AI models / developers picking this up:**
> Read this entire file before touching any code. It defines the architecture, what is fully functional, what still needs building, and critical implementation rules. The plugin's namespace is `InsightPulse` (for historical reasons — do not rename).

---

## 🏗 Architecture Overview

| Layer | Directory | Description |
|-------|-----------|-------------|
| Entry Point | `wpask.php` | Plugin header, PSR-4 autoloader, activation/deactivation hooks |
| Bootstrap | `includes/Plugin.php` | Singleton. Registers all handlers and REST routes |
| Controllers | `includes/Controllers/` | WordPress REST API controllers |
| Services | `includes/Services/` | Business logic (Submission, Session, Analytics, Targeting) |
| Repositories | `includes/Repositories/` | DB query layer (no raw SQL in controllers) |
| Models | `includes/Models/` | Simple DTOs (Survey, Response, Session, Meta) |
| Validators | `includes/Validators/` | Sanitise & validate API payloads |
| Handlers | `includes/Handlers/` | WordPress action/filter hooks (Admin menu, Frontend, Metabox, etc.) |
| Database | `includes/Database/` | Migrator + Migration files (dbDelta) |
| Emails | `includes/Emails/` | WP_Mail wrappers for notification emails |
| Admin SPA | `src/admin/` | Vue 3 + Vite admin interface |
| Frontend Widget | `src/frontend/` | Vanilla JS floating survey widget (Shadow DOM) |
| Built Assets | `assets/` | Compiled output from `npm run build` |

**Namespace:** `InsightPulse\` → maps to `includes/` (PSR-4)

**REST API Base:** `insightpulse/v1` (e.g. `GET /wp-json/insightpulse/v1/surveys`)

**Vue Admin Config:** Passed via `window.WPAskAdminConfig = { api_url, nonce }` (injected by `AdminMenuHandler.php`)

---

## 🎨 DESIGN DIRECTIVE — READ BEFORE BUILDING ANY UI

Always reference `/wp-content/plugins/userfeedback-lite-master/` for design inspiration (do NOT copy — improve). Also reference `/wp-content/plugins/plugin-design-refresh-main/` for layout/icon conventions.

1. **Sidebar**: Dark (`#1e1e3a`), white text, active state indicator pill
2. **Top Bar**: Clean white header per page with title and primary action buttons
3. **Cards**: White, `border-radius: 12px`, subtle shadow
4. **Color Palette**: Primary `#6366f1`, gradient `#6366f1 → #8b5cf6`, background `#f6f7fb`
5. **Typography**: `Inter` from Google Fonts — titles `700`, labels `500`, body `400`
6. **CSS Scoping**: Always scope inside `#wpask-admin-app` to avoid WordPress admin conflicts
7. **Icons**: Use `lucide-vue-next` exclusively — no emoji, no dashicons
8. **No `type="dashed"` on ElButton** — Element Plus only accepts: `default`, `primary`, `success`, `warning`, `info`, `danger`, `text`

---

## ✅ COMPLETED FEATURES

### Core Architecture
- [x] PSR-4 Autoloader in `wpask.php`
- [x] Plugin singleton `Plugin.php` wiring all handlers
- [x] Database migrations (dbDelta via `Migrator.php`)
  - `ipulse_surveys` — id, title, status, type, questions, settings, targeting, notifications, impressions, **publish_at**, created_at
  - `ipulse_responses` — id, survey_id, session_id, answers (JSON), ip, status, created_at
  - `ipulse_sessions` — cookie-based session tracking
  - `ipulse_meta` — key-value store for aggregated answer stats
  - `ipulse_post_ratings` — per-post star ratings
  - `ipulse_email_surveys` — email-sent tracking
  - `ipulse_heatmaps` — heatmap click data

### REST API Endpoints

| Method | Route | Controller | Status |
|--------|-------|-----------|--------|
| GET | `/surveys` | SurveyController | ✅ |
| POST | `/surveys` | SurveyController | ✅ |
| GET | `/surveys/{id}` | SurveyController | ✅ |
| PUT | `/surveys/{id}` | SurveyController | ✅ |
| POST | `/surveys/{id}/trash` | SurveyController | ✅ |
| POST | `/surveys/{id}/restore` | SurveyController | ✅ |
| DELETE | `/surveys/{id}` | SurveyController | ✅ |
| POST | `/surveys/{id}/duplicate` | SurveyController | ✅ |
| GET | `/surveys/{id}/results` | ResultsController | ✅ |
| GET | `/results-summary` | ResultsController | ✅ (aggregated global stats) |
| GET | `/surveys/{id}/responses` | ResponseController | ✅ |
| POST | `/responses/{id}/trash` | ResponseController | ✅ |
| POST | `/responses/{id}/restore` | ResponseController | ✅ |
| DELETE | `/responses/{id}` | ResponseController | ✅ |
| GET/POST | `/settings` | SettingsController | ✅ |
| GET | `/templates` | TemplateController | ✅ |
| POST | `/submit` | FrontendController | ✅ (public, rate-limited) |
| GET | `/logic-type` | LogicController | ✅ (post types + roles) |

### Admin SPA (Vue 3 + Vite)
- [x] `SurveysList.vue` — CRUD list, status tabs (All/Published/Draft/Trash), bulk actions, impressions counter, Lucide icons, status ping dots
- [x] `SurveyBuilder.vue` — 3-column layout, question list, live preview panel, inspector
  - [x] Question types: Rating, NPS, Short Text, Multiple Choice, Yes/No
  - [x] Settings tab: brand color, widget position, confirmation message, **schedule publish (`publish_at` datetime picker)**
  - [x] Targeting tab: match ALL / ANY, dynamic rule rows with post type and user status dropdowns (fetched from `/logic-type`)
  - [x] Notifications tab: enable/disable email, comma-separated addresses
- [x] `SurveyResults.vue` — metric cards (impressions, responses, completion rate), response table with per-row delete/restore
- [x] `Settings.vue` — general settings form wired to `/settings`
- [x] `Addons.vue` — addon card grid (UI only)
- [x] `Onboarding.vue` — multi-step onboarding wizard
- [x] `App.vue` — dark sidebar navigation, router-view

### Frontend Widget
- [x] Vanilla JS floating widget (`src/frontend/`)
- [x] Shadow DOM for CSS isolation
- [x] Renders: Rating, NPS, Short Text, Multiple Choice, Yes/No
- [x] Submits to `/submit` REST endpoint
- [x] Session tracking via cookies

### WordPress Integration
- [x] **FrontendHandler** — Conditionally enqueues widget, respects `publish_at` scheduling, `wpask_preview` query param for draft preview
- [x] **MetaboxHandler** — Per-post "Disable surveys" and "Force specific survey" meta boxes
- [x] **ShortcodeHandler** — `[wpask id="X"]` shortcode embeds survey in post/page content
- [x] **ReviewNoticeHandler** — Dismissible WP admin notice after 14 days, prompts for 5-star review
- [x] **ActivationHandler** — Redirects to onboarding on first activation
- [x] **AdminMenuHandler** — Registers `wpask` admin page, injects `WPAskAdminConfig`
- [x] **Email Notifications** — HTML email on new response, triggered via `insightpulse_response_saved` action
- [x] **WP Dashboard Widget** — "Recent Activity" mount point widget

### Design System
- [x] Full CSS variable system in `admin.css` using `oklch` color tokens
- [x] Dark sidebar, card-based layout, Inter/Sora fonts
- [x] Lucide icons throughout (no emoji)
- [x] CSS specificity fixed: `#wpask-admin-app .wpask-survey-row` ensures padding isn't overridden by WP resets

---

## ❌ NOT YET BUILT — REMAINING FEATURES

> These are confirmed gaps vs. UserFeedback Lite. Build them in this order.

### Priority 1 — Core Feature Gaps
- [x] **Wire `SurveyResults.vue` to live `/results-summary` API** — The Results view currently uses mock/static data. Fetch from `GET /results-summary` and `GET /surveys/{id}/results` and render real aggregated stats and per-question chart data.
- [x] **Expanded Question Types (backend + frontend):**
  - [x] `checkbox` — Multi-select (frontend widget render + analytics aggregation)
  - [x] `dropdown` — Select input type
  - [x] `date` — Date picker input
  - [x] `email` — Email address input with validation
  - [x] `number` — Numeric input with optional min/max
  - [ ] `file_upload` — File attachment (complex — do last)
  - Add each type to: `SurveyBuilder.vue` inspector, `frontend/` widget renderer, and `AnalyticsService.php` aggregation
- [x] **Conditional Logic (Skip Logic)** — Per-question branching: "If answer to Q1 is X, skip to Q3". UF Lite has this. Requires:
  - [x] JSON schema in `questions[]` for `logic` conditions
  - [x] Frontend widget skip engine
  - [x] Builder UI to configure conditions

### Priority 2 — Analytics & Reporting
- [ ] **Per-question charts in `SurveyResults.vue`** — Show bar charts for choice/radio, gauge for NPS, distribution for rating (use Chart.js or ApexCharts — already likely in node_modules)
- [x] **Response export (CSV)** — `GET /surveys/{id}/export` endpoint that streams a CSV of all responses. Add export button to Results view.
- [ ] **Date range filtering on responses** — Pass `?from=&to=` params to `/surveys/{id}/responses`

### Priority 3 — Advanced Targeting
- [ ] **More targeting rule types:**
  - [ ] `page` — Specific page (fetch from `/logic-type` — currently only post types, add pages query)
  - [ ] `referrer` — HTTP referrer URL contains/equals
  - [ ] `time_on_page` — Show after X seconds on page (JS timer in widget)
  - [ ] `scroll_depth` — Show after user scrolls X% (JS scroll listener)
  - [ ] `device` — Desktop / Mobile / Tablet (user agent detection)
  - [ ] `exit_intent` — Show on mouse-leave (JS mouseleave detection)
- [ ] **LogicController** — Update `/logic-type` to also return a paginated list of individual pages/posts for the "specific page" targeting option

### Priority 4 — Polish & Monetisation
- [ ] **Git push to `https://github.com/bijit027/wpask.git`** — Not yet pushed. Run: `git push origin main`
- [ ] **Addons.vue** — Hook up real addon data (at minimum show 2–3 "Pro" addon cards with lock icons and a Stripe/pricing link)
- [ ] **Survey Templates** — `TemplateController` exists but templates aren't selectable in the "New Survey" flow. Add a template picker modal to `SurveysList.vue` / onboarding.
- [ ] **Post Ratings Widget** — `ipulse_post_ratings` table exists. Build a "thumbs up/down" or "star" per-post rating widget that can be embedded via shortcode `[wpask_rating]`
- [ ] **Heatmap module** — `ipulse_heatmaps` table exists. This is a larger feature — defer to last.

---

## 🔧 Dev Environment Notes

- **Node:** NVM is required. When running npm commands, prepend: `export PATH="/Users/bijitdeb/.nvm/versions/node/v16.17.1/bin:$PATH" &&`
- **Build command:** `npm run build` (Vite 4)
- **Dev server:** `npm run dev` (Vite HMR on port 5173)
- **In `FrontendHandler.php` and `ShortcodeHandler.php`:** `$is_dev = true` — change to `false` before production build/deploy
- **WordPress URL:** Local dev (standard MAMP/LocalWP setup assumed)
- **REST Nonce:** Injected as `window.WPAskAdminConfig.nonce` by `AdminMenuHandler.php`

---

## 📁 Key Files Reference

| File | Purpose |
|------|---------|
| `wpask.php` | Entry point, autoloader, hooks |
| `includes/Plugin.php` | Central bootstrapper |
| `includes/Handlers/FrontendHandler.php` | Widget injection, scheduling enforcement |
| `includes/Handlers/ShortcodeHandler.php` | `[wpask id="X"]` shortcode |
| `includes/Handlers/ReviewNoticeHandler.php` | 14-day review prompt |
| `includes/Controllers/SurveyController.php` | Full survey CRUD + trash/restore/duplicate |
| `includes/Controllers/ResultsController.php` | Per-survey results + global `/results-summary` |
| `includes/Controllers/LogicController.php` | `/logic-type` for dynamic targeting dropdowns |
| `includes/Services/TargetingService.php` | Display rule engine |
| `includes/Services/AnalyticsService.php` | Aggregated answer stats from `ipulse_meta` |
| `src/admin/views/SurveyBuilder.vue` | Full survey builder UI |
| `src/admin/views/SurveysList.vue` | Survey list management |
| `src/admin/views/SurveyResults.vue` | Analytics dashboard (needs live API wiring) |
| `src/admin/admin.css` | Full design system (CSS variables, components) |

---

*Last updated: 2026-07-21 — Phase 2 complete. All Phase 1 architecture + Phase 2 features shipped.*
