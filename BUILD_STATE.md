# WPAsk Plugin — Build State Tracker

> **Notice to other AI models/agents:** 
> Read this file to understand the current architecture, what has been completed, and what is remaining. This plugin follows a strict Laravel-inspired WordPress architecture (Controllers, Services, Repositories, Models).

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

### ⏳ Phase 7: Templates (Pending)
- `Registry.php`, `DefaultTemplates.php`

### ⏳ Phase 8: Handlers (Pending)
- `ActivationHandler.php`, `AdminMenuHandler.php`, `FrontendHandler.php`, `MetaboxHandler.php`
- *Note: Once these are built, uncomment them in `Plugin.php`.*

### ⏳ Phase 9: Frontend (Vue + Vanilla JS) (Pending)
- Admin SPA in Vue 3 (Survey Builder, Results)
- Floating Widget in Vanilla JS (Shadow DOM, Vite)

---
*Last Updated: Phase 3 Models completed. Proceeding to Repositories.*
