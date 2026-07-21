# WPAsk Plugin — Build State Tracker

> **Notice to other AI models/agents:** 
> Read this file to understand the current architecture, what has been completed, and what is remaining. This plugin follows a strict Laravel-inspired WordPress architecture (Controllers, Services, Repositories, Models).

---

## 🎨 DESIGN DIRECTIVE — READ BEFORE BUILDING ANY UI

**Always reference `/wp-content/plugins/userfeedback-lite-master/` for design inspiration.**

Do NOT copy it — **improve upon it**. Follow these principles:

1. **Sidebar Navigation**: Use a dark sidebar (`#1e1e3a`) with white text and an active state indicator — similar to UserFeedback's sidebar but more modern.
2. **Top Bar**: A clean white header bar per page with the page title and primary action buttons.
3. **Cards**: White rounded cards (`border-radius: 12px`) with subtle shadows for all content sections.
4. **Color Palette**: Primary brand `#6366f1` (indigo), gradient `#6366f1 → #8b5cf6`, background `#f6f7fb`.
5. **Typography**: Use `Inter` font from Google Fonts. Titles `700`, labels `500`, body `400`.
6. **No WP Clutter**: Always reset WordPress admin CSS interference by setting `padding-left: 0 !important` on `#wpcontent`.
7. **Element Plus**: Used for interactive widgets (modals, dropdowns, form inputs). Do NOT use raw WordPress form elements.
8. **Live Preview Pane**: The Survey Builder must always have a right-side live browser preview of the widget.


## Current Status: Building Phase 3 (Repositories)

### ✅ Phase 1: Bootstrap & Core (Completed)
- `insightpulse.php` (Main entry point & PSR-4 autoloader)
- `uninstall.php` (Cleanup script)
- `includes/Plugin.php` (Singleton bootstrapper, currently has REST/Handlers commented out to prevent fatal errors while building)
- `includes/Capabilities.php` (Custom role caps)
- `includes/Config/app.php`

### ✅ Phase 2: Database Schema (Completed)
- `includes/Database/Migrator.php` (dbDelta runner)
- `includes/Database/Migrations/CreateSurveysTable.php`
- `includes/Database/Migrations/CreateResponsesTable.php`
- `includes/Database/Migrations/CreateSessionsTable.php`
- `includes/Database/Migrations/CreateMetaTable.php`
- `includes/Database/Migrations/CreatePostRatingsTable.php`
- `includes/Database/Migrations/CreateEmailSurveysTable.php`
- `includes/Database/Migrations/CreateHeatmapsTable.php`

### ✅ Phase 3: Models & Repositories (Completed)
- **Completed Models (DTOs):**
  - `includes/Models/Survey.php`
  - `includes/Models/Response.php`
  - `includes/Models/Session.php`
  - `includes/Models/Meta.php`
- **Completed Repositories:**
  - `includes/Repositories/SurveyRepository.php` 
  - `includes/Repositories/ResponseRepository.php`
  - `includes/Repositories/SessionRepository.php`
  - `includes/Repositories/MetaRepository.php`

### ✅ Phase 4: Services (Completed)
- `SubmissionService.php` (Validate & save)
- `SessionService.php` (Handle cookies)
- `AnalyticsService.php` (Handle pre-aggregated meta data)
- `TargetingService.php` (Display rules engine)

### ✅ Phase 5: Validators & Utils (Completed)
- `SurveyValidator.php`, `ResponseValidator.php`
- `IpHelper.php`, `GravatarHelper.php`, `Sanitizer.php`

### ✅ Phase 6: REST Controllers (Completed)
- `SurveyController.php`, `ResponseController.php`, `ResultsController.php`
- `SettingsController.php`, `TemplateController.php`, `FrontendController.php`

### ✅ Phase 7: Templates (Completed)
- `Registry.php`, `DefaultTemplates.php`

### ✅ Phase 8: Handlers (Completed)
- `ActivationHandler.php`, `AdminMenuHandler.php`, `FrontendHandler.php`, `MetaboxHandler.php`

### ✅ Phase 9: Frontend (Vue + Vanilla JS) (Completed)
- Admin SPA in Vue 3 (Survey Builder, Results, Settings, Addons)
- Floating Widget in Vanilla JS (Shadow DOM, Vite)
- UserFeedback Lite inspired premium UI Design

### ✅ Phase 10: Final Polish & Testing (Completed)
- Built `SurveyResults.vue` analytics dashboard
- Built `Settings.vue` configuration panel
- Built interactive `Onboarding.vue` wizard
- Wired up all remaining UI components to the REST API endpoints
- Resolved dev server asset loading for the frontend Vanilla JS widget

---
**🏆 PROJECT COMPLETE:** The WPAsk plugin architecture is fully scaffolded, styled, and wired together. The user should now be able to run `npm run dev` and test the end-to-end functionality.
