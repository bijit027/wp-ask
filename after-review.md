# PollQuest — Full Rename & Release-Readiness Instructions

**Purpose:** Execute every change below to rename `PollQuest` to `PollQuest`, fix all WordPress.org review issues, and make the plugin ready for resubmission.

**Old plugin:** PollQuest (slug: `pollquest`)
**New plugin:** PollQuest (slug: `pollquest`)
**New display name:** `PollQuest – Surveys & Feedback Forms for WordPress`
**Owner:** Bijit Deb (WordPress.org username: `bijit027`)

---

## STEP 0 — Before starting

1. Confirm the domain you'll actually use in `Plugin URI` / `Author URI` is registered and resolves (e.g. `pollquest.io`, `getpollquest.com`, or omit URIs entirely if no domain is live yet). **Do not point to a domain that doesn't resolve** — this was one of the original rejection reasons.
2. Confirm the GitHub repo `https://github.com/bijit027/pollquest` exists and is set to **public** before referencing it anywhere.

---

## STEP 1 — Global Find & Replace (do this first, across entire codebase)

Run in this exact order. Use case-sensitive replace where noted.

| Find | Replace | Case-sensitive | Notes |
|---|---|---|---|
| `pollquest_` | `pollquest_` | yes | function/hook prefixes |
| `PollQuest_` | `PollQuest_` | yes | class name prefixes |
| `POLLQUEST_` | `POLLQUEST_` | yes | constants |
| `pollquest/v1` | `pollquest/v1` | yes | REST API namespace |
| `PollQuestConfig` | `PollQuestConfig` | yes | JS global var |
| `PollQuestHeatmapConfig` | `PollQuestHeatmapConfig` | yes | JS global var |
| `pollquest-` | `pollquest-` | yes | CSS classes, script/style handles, HTML IDs (e.g. `pollquest-content-inner` → `pollquest-content-inner`) |
| `'pollquest'` / `"pollquest"` | `'pollquest'` / `"pollquest"` | yes | text domain string literal — check every `__()`, `_e()`, `esc_html__()` etc. call |
| `PollQuest` | `PollQuest` | yes | remaining display-text occurrences (readme, comments, admin UI strings) |
| `pollquest` | `pollquest` | yes | catch-all pass — run last, then manually verify no false positives (e.g. don't touch unrelated words containing "ask") |

After the pass, run a full-repo search for the literal string `pollquest` (case-insensitive) and confirm **zero** occurrences remain, including in:
- `.js`, `.php`, `.json`, `.txt`, `.md` files
- Compiled/minified assets in `assets/` (must rebuild, not hand-edit — see Step 6)
- `package.json` name field
- `composer.json` if present
- Any `.pot`/`.po` language files

---

## STEP 2 — File & directory renames

- `pollquest.php` → `pollquest.php`
- Any directory literally named `pollquest` or similar → `pollquest`
- Update the main plugin file header block:

```php
/**
 * Plugin Name: PollQuest – Surveys & Feedback Forms for WordPress
 * Plugin URI: https://pollquest.io
 * Description: Create surveys, polls, and feedback forms for WordPress.
 * Author: Bijit Deb
 * Author URI: https://pollquest.io
 * Text Domain: pollquest
 * Version: 1.0.0
 */
```
> If `pollquest.io` is not live yet, remove the `Plugin URI` and `Author URI` lines entirely rather than pointing at a non-resolving domain.

---

## STEP 3 — readme.txt updates

```
=== PollQuest – Surveys & Feedback Forms for WordPress ===
Contributors: bijit027
Tags: survey, poll, feedback form, ratings, forms
Requires at least: [keep existing]
Tested up to: [keep existing]
Stable tag: [keep existing]
License: GPLv2 or later

Create surveys, polls, and feedback forms for WordPress.
```

Add this new section (anywhere after the short description, before `== Installation ==` is fine):

```
== Source Code ==

The full source code including all build files is available on GitHub:
https://github.com/bijit027/pollquest

Build requirements:
- Node.js 18+
- npm install
- npm run build
```

Do **not** re-add the long promotional tagline ("Create Interactive Feedback Forms, User Surveys, and Polls in Seconds"). Keep description language plain and factual — no superlatives, no "in seconds"/"best"/"ultimate" style marketing copy anywhere in readme.txt or plugin header.

---

## STEP 4 — Code fixes (content changes, not just renames)

### 4a. `includes/Handlers/FrontendHandler.php` (~line 144)

Replace:
```php
echo '<script>window.PollQuestConfig = ' . wp_json_encode($this->current_config) . ';</script>';
```
With:
```php
wp_add_inline_script('pollquest-frontend', 'window.PollQuestConfig = ' . wp_json_encode($this->current_config) . ';', 'before');
```
> Confirm `'pollquest-frontend'` matches the exact handle used in the corresponding `wp_enqueue_script()` call for this script. If the handle differs, use the real handle instead.

### 4b. `includes/Handlers/HeatmapHandler.php` (~line 82)

Replace:
```php
echo '<script>window.PollQuestHeatmapConfig = ' . wp_json_encode($this->tracking_config) . ';</script>';
```
With:
```php
wp_add_inline_script('pollquest-heatmap', 'window.PollQuestHeatmapConfig = ' . wp_json_encode($this->tracking_config) . ';', 'before');
```
> Confirm handle matches its `wp_enqueue_script()` registration, same caveat as above.

### 4c. Gravatar / ui-avatars.com privacy opt-in

Add a new setting, default `false`:
```php
'enable_gravatar' => false,
```

Add an admin settings toggle:
```
[ ] Enable Gravatar avatars
    (Sends a hash of respondent email addresses to gravatar.com to load profile photos)
    Disabled by default for privacy compliance.
```

In the avatar helper class, gate the Gravatar call:
```php
public function getAvatarUrl(string $email): string
{
    if (!get_option('pollquest_enable_gravatar', false)) {
        return ''; // empty — use local default avatar fallback
    }
    return 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($email)));
}
```

**Find every place `ui-avatars.com` URLs are generated** (search the codebase for `ui-avatars.com`) and wrap each occurrence with the same `get_option('pollquest_enable_gravatar', false)` check. There is more than one call site for this — do not assume it's only in the avatar helper class.

### 4d. REST API permission callbacks

**`includes/Controllers/SurveyController.php`** (~line 119) — impression endpoint:
```php
'permission_callback' => function($request) {
    $survey = $this->survey_repository->find(absint($request['id']));
    return $survey && $survey->status === 'publish';
},
```

**`includes/Controllers/PostRatingController.php`** (~line 67) — create rating endpoint:
```php
'permission_callback' => function($request) {
    $post = get_post(absint($request['post_id']));
    return $post && $post->post_status === 'publish';
},
```

**`includes/Controllers/PostRatingController.php`** (~line 43) — get rating endpoint:
```php
'permission_callback' => function($request) {
    $post = get_post(absint($request['post_id']));
    return $post && $post->post_status === 'publish';
},
```

### 4e. Remove `load_plugin_textdomain()`

**`includes/Plugin.php`** (~line 128) — delete this line entirely (not needed on WordPress.org since WP 4.6):
```php
load_plugin_textdomain('pollquest', false, dirname(POLLQUEST_PLUGIN_BASENAME) . '/languages');
```

---

## STEP 5 — Guideline 10 self-check (not explicitly line-flagged, but mentioned in the review)

Search all frontend-facing templates/views for any outbound links, credits, or branding inserted into the public site without user consent (e.g. a "Powered by PollQuest" footer link, or any external link auto-injected into survey/poll output). Any such link must be opt-in/removable by the site owner, not hardcoded.

---

## STEP 6 — Rebuild compiled assets

After all source changes above:
```bash
npm install
npm run build
```
This regenerates everything in `assets/` (including `assets/frontend/frontend.js`, `assets/admin/admin.js`, and all `assets/chunks/*.js`). Do not hand-edit compiled files — they must be regenerated from source so they match what's pushed to GitHub.

---

## STEP 7 — GitHub

1. Push the full `src/` directory (all Vue.js and vanilla JS source) plus build config (`package.json`, `vite`/`webpack` config, etc.) to `https://github.com/bijit027/pollquest`.
2. Set repository visibility to **public**.
3. Confirm the readme.txt "Source Code" section (Step 3) links to it correctly.

---

## STEP 8 — Testing before upload

- [ ] Fresh WordPress install, activate plugin — no fatal errors
- [ ] No PHP notices/warnings on activation or normal use
- [ ] Full-repo search for `pollquest` (case-insensitive) returns zero results
- [ ] All REST endpoints tested: confirm published content is accessible, non-published/draft content is correctly blocked
- [ ] Gravatar/ui-avatars calls confirmed OFF by default; confirm toggle works both directions
- [ ] Enqueued scripts load correctly (no console errors) after the `wp_add_inline_script` change — verify handle names match exactly
- [ ] `npm run build` output committed/zipped, matches GitHub source

---

## STEP 9 — Final checklist before uploading new version

```
[ ] Plugin renamed to PollQuest everywhere (zero "pollquest" occurrences)
[ ] Display name: PollQuest – Surveys & Feedback Forms for WordPress
[ ] Slug: pollquest
[ ] Contributors: bijit027
[ ] Author: Bijit Deb
[ ] Plugin URI / Author URI removed OR pointing to a domain that actually resolves
[ ] Source code pushed to GitHub as PUBLIC repo (github.com/bijit027/pollquest)
[ ] GitHub link + build instructions added to readme.txt
[ ] echo <script> tags replaced with wp_add_inline_script (both files)
[ ] Gravatar + ui-avatars.com calls gated behind opt-in setting, OFF by default
[ ] Post/survey status checks added to all 3 REST endpoints
[ ] load_plugin_textdomain() removed
[ ] No un-consented outbound links/credits on public-facing pages
[ ] npm run build completed after all changes
[ ] Tested on clean WordPress install, no fatal errors
[ ] New zip uploaded via "Add your plugin" page (logged in as bijit027)
```

---

## STEP 10 — Reply to WordPress.org review thread

Keep this short — they explicitly asked for no verbose/listing replies:

```
Hi,

Thanks for the review. I've renamed the plugin to PollQuest and would like
to request the slug `pollquest`. Source code is public at
https://github.com/bijit027/pollquest (linked in readme with build
instructions). All other reported issues have been addressed.

Best regards,
Bijit Deb
```

Reply in the **same email thread** — do not start a new thread.