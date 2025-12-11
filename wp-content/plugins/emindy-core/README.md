# eMINDy Core

**eMINDy Core** is the platform-layer plugin for the eMINDy WordPress site. It centralizes your **content model**, **core UX components**, **structured data**, **lightweight analytics**, and several **platform utilities** (assessments, newsletter, admin tooling, localization helpers).

---

## What this plugin is responsible for

### Content model
- **Custom Post Types (CPTs)**
  - Exercises
  - Videos
  - Articles
- **Shared taxonomies**
  - Topics + multiple classification taxonomies to power filtering, discovery, and related-content logic.

### Core UX features
- **Exercise player** (step-based guided practice)
- **Video chapters UI** (driven by JSON meta)
- **Assessments** (PHQ-9 and GAD-7)
  - Scoring + safety guidance
  - Shareable **signed result pages**
  - Optional email send flow
- **Discovery helpers**
  - Related content blocks, table of contents (TOC), search UI helpers, quick filters, etc.

### Structured data (schema)
- Integrates with **Rank Math JSON-LD pipeline** when available and/or provides fallback output.

### Lightweight analytics
- AJAX event tracking + **custom DB table** for logs (no UI).

### Newsletter signup
- Custom DB table + front-end form handler via `admin-post`.

### Admin tooling
- Meta boxes for JSON fields (chapters/steps)
- Validation helpers
- “Missing pages” notices

### Localization
- WordPress i18n (`emindy-core` text domain)
- JSON language packs for PHQ-9 labels
- Polylang meta-copy rules for structured meta fields

---

## Requirements
- WordPress: **6.0+**
- PHP: **7.4+**
- Optional integrations:
  - **Rank Math** (for enhanced JSON-LD pipeline integration)
  - **Polylang** (for multilingual content + meta copy behavior)

---

## Installation
1. Copy the plugin folder to:
   - `wp-content/plugins/emindy-core/`
2. Activate **eMINDy Core** in **WP Admin → Plugins**.
3. (Recommended) Flush rewrite rules after activation:
   - Visit **Settings → Permalinks** and click **Save Changes**.

---

## Directory structure (high-level)

### 1) Root bootstrap: `emindy-core.php`
The entry point. Defines constants, loads dependencies, registers hooks, and boots modules.

**Key behaviors**
- Defines constants:
  - `EMINDY_CORE_VERSION` (0.5.0)
  - `EMINDY_CORE_PATH`, `EMINDY_CORE_URL`
- Loads include files (helpers + classes)
- Boots modules:
  - `Ajax::register()`
  - `Analytics::register()`
  - `Diagnostics::register()`
  - `Admin::register()`
  - `CPT::register_all()`, `Taxonomy::register_all()`, `Meta::register()`
  - `Shortcodes::register_all()`
  - `Content_Inject::register()`
- Adds **Polylang meta copy** filter so translations keep structured fields:
  - `em_steps_json`, `em_chapters_json`
  - time/meta fields like `em_total_seconds`, etc.
- Conditionally enqueues assets based on:
  - Current CPT (exercise/video)
  - Presence of shortcodes in content (PHQ-9 / GAD-7 / player / chapters)
- Provides small fallback logic:
  - Schema fallback output hook in `wp_head`
  - 404 redirect helpers for `/library` and `/blog` if those pages don’t exist

### 2) Helpers: `includes/helpers.php`
Shared utilities (namespaced under `EMINDY\Core`) and global wrappers for backward compatibility.

**Notable helpers**
- Content-type checks:
  - `is_video()`, `is_exercise()`, `is_article()`
- Assessment share base URL:
  - `assessment_result_base_url()` (filterable)
- Robust JSON decoding:
  - `json_decode_safe()`
- Safe summaries:
  - `safe_summary()` (filterable)
- Time helpers used by schema/player:
  - `emindy_seconds_from_ts()`
  - `emindy_iso8601_duration()`
  - `emindy_array_filter_recursive()`

---

## Content model

### 3) CPTs: `includes/class-emindy-cpt.php`
Registers 3 CPTs:
- `em_exercise`
- `em_video`
- `em_article`

**Common traits**
- Public + queryable, designed to behave like real “content types”
- Share a common taxonomy set so filtering/related-content works consistently

**Extensibility filters**
- `emindy_cpt_common_args`
- `emindy_cpt_exercise_args`
- `emindy_cpt_video_args`
- `emindy_cpt_article_args`

### 4) Taxonomies: `includes/class-emindy-taxonomy.php`
Registers multiple taxonomies and attaches them to the CPTs.

**Shared taxonomy slugs**
- `em_topic`
- `em_technique`
- `em_duration`
- `em_format`
- `em_use_case`
- `em_level`
- `em_a11y_feature`

**Extensibility filters**
- `emindy_taxonomies` (extend/override taxonomy definitions)
- `emindy_taxonomy_post_types` (control which post types a taxonomy applies to)
- `emindy_taxonomy_default_terms` (seed default terms)
- `emindy_core_default_terms`

---

## Meta fields and storage

### 5) Meta registration/sanitization: `includes/class-emindy-meta.php`
Registers CPT meta using `register_post_meta()` with `show_in_rest => true` so it’s usable for:
- Admin editing
- REST API consumers
- Future headless/recommender features

**Important meta keys**
- Videos
  - `em_chapters_json` (JSON string): chapters array  
    Example objects: `{ "t": 60, "label": "Intro" }`
- Exercises
  - `em_steps_json` (JSON string): step list for the player  
    Example objects: `{ "label": "...", "duration": 60, "tip": "..." }`
- Time/meta fields (ints/strings), e.g.:
  - `em_total_seconds`, `em_prep_seconds`, `em_perform_seconds`
  - `em_supplies`, `em_tools`, `em_yield` (used for HowTo schema + UI)

**Sanitizers**
- `sanitize_json()` ensures valid JSON input and sanitizes nested values
- `sanitize_integer_meta()` ensures non-negative integers

---

## Front-end behavior

### 6) Auto injection: `includes/class-emindy-content-inject.php`
Automatically inserts core shortcodes into content so editors don’t have to.

- Exercises: prepends `[em_player]`
- Videos: appends `[em_video_chapters]`
- Runs on `the_content` at priority **9** (early enough that other filters “see” injected content)

**Filters**
- `emindy_content_inject_enabled`
- `emindy_content_inject_post_types`
- `emindy_content_inject_player_shortcode`
- `emindy_content_inject_chapters_shortcode`

---

## Shortcodes

### 7) Main shortcode registry: `includes/class-emindy-shortcodes.php`
This file provides the majority of user-facing components.

#### Core experiences
- `[em_player]` – Exercise stepper player (reads `em_steps_json`)
- `[em_exercise_steps]` – Steps list (printable/accessible views)
- `[em_video_chapters]` – Video chapters UI (reads `em_chapters_json`)
- `[em_related]` – Related content grid with taxonomy-based logic
- `[em_toc]` – Table of contents block (for long articles)
- `[em_share]` – Simple share UI helper

#### Assessments
- `[em_phq9]` – PHQ-9 assessment UI + scoring + safety guidance
- `[em_gad7]` – GAD-7 assessment UI + scoring + safety guidance
- `[em_assessment_result]` – Displays a result page from a signed URL

Result helpers:
- `[em_result_badge]`, `[em_result_count]`

#### Discovery & navigation helpers
- `[em_search_bar]`, `[em_search_query]`, `[em_search_section]`
- `[em_topics_pills]` – topic pills UI
- `[em_quick_filters]` – quick filter UI block
- `[em_sitemap_mini]`
- `[em_lang_switcher]` – language switcher helper
- `[em_reading_time]`

Popular widgets:
- `[em_popular_posts]`, `[em_popular_videos]`, `[em_popular_exercises]`

Blog helper:
- `[em_blog_categories]`

Admin/support utilities
- `[em_admin_notice_missing_pages]` – checks required pages exist and outputs notices
- `[em_report_link]`
- `[em_excerpt_highlight]`
- `[em_i18n]` – returns translated strings from Polylang keys (fallback to default)

#### Related-content logic (important detail)
`[em_related]` builds a `WP_Query` using:
- Current post type (or specified via attributes)
- Topic (current topic or specified topic slug)
- Optional technique/format filters
- Fallback behavior if strict query returns no posts

---

## Assessments system (sharing + emailing + safety)

### 8) AJAX endpoints: `includes/class-emindy-ajax.php`

#### Sign a result (prevents tampering)
- Uses:
  - `hash_hmac('sha256', "$type|$score", wp_salt('auth'))`
- Returns a signed URL pointing to the result page (default `/assessment-result/`)

**Filters**
- `emindy_assessment_supported_types`
- `emindy_assessment_sign_result_response`
- `emindy_assessment_result_url`
- `emindy_assessment_result_base_url`

#### Send assessment by email
- Inputs: `email`, `summary`, `kind` (kind is filterable)
- Basic per-IP rate limiting:
  - Default max: **5/hour**
  - Filters:
    - `emindy_assessment_rate_limit_max_per_hour`
    - `emindy_assessment_rate_limit_expiration`

**Email customization filters**
- `emindy_assessment_email_recipient`
- `emindy_assessment_email_subject`
- `emindy_assessment_email_body`
- `emindy_assessment_email_headers`

**Client IP filter**
- `emindy_assessment_client_ip`

**Security**
- Uses a single nonce action for endpoints (verified server-side)
- Sanitizes all `$_POST` values
- Uses signed URLs + `hash_equals()` verification in `[em_assessment_result]`

---

## Newsletter system

### 9) Newsletter module: `includes/class-emindy-newsletter.php`

#### Database table
Creates and uses:
- `{$wpdb->prefix}emindy_newsletter`

**Columns (as defined in SQL)**
- `id` (BIGINT, PK)
- `email` (unique)
- `name` (nullable)
- `consent` (TINYINT)
- `ip`, `ua`
- `created_at`

#### Front-end
- `[em_newsletter_form]` renders the signup form
- Submission uses `admin-post.php` action: `emindy_newsletter_submit`

**Filters**
- `emindy_newsletter_slug`
- `emindy_newsletter_redirect_base`
- `emindy_newsletter_admin_email`
- `emindy_newsletter_send_welcome`
- `emindy_newsletter_welcome_email_args`

**Action**
- `emindy_newsletter_subscribed` (fires after a successful subscribe)

---

## Analytics system

### 10) Analytics: `includes/class-emindy-analytics.php`

**Purpose**
- Minimal “event log” pipeline (**no UI**)
- Captures key front-end events via AJAX

**Database table**
- `{$wpdb->prefix}emindy_analytics` (created via `dbDelta()`)

**Tracking**
- AJAX action: `emindy_track` (supports logged-in + `nopriv`)

**Filters**
- `emindy_analytics_enabled`
- `emindy_analytics_ip`
- `emindy_analytics_ua`

**Action**
- `emindy_analytics_tracked` (fires after a log is written)

---

## Schema (SEO / rich results)

### 11) Schema: `includes/class-emindy-schema.php`
Integrates schema generation into the site.

**Key idea**
- Prefer hooking into **Rank Math JSON-LD pipeline** (when present)
- Provide fallback output where appropriate

**Schema types**
- Videos: `VideoObject`
- Exercises: HowTo-style structured data
- Potential additional support components where useful

Uses helper functions (e.g., ISO-8601 duration formatting) to produce valid schema durations.

---

## Admin tooling

### 12) Admin UI & meta boxes: `includes/class-emindy-admin.php`
Provides:
- Meta boxes for JSON editing:
  - `em_chapters_json` on `em_video`
  - `em_steps_json` on `em_exercise`
- Basic JSON validation + safe saving
- Admin assets (JS/CSS) for nicer meta editing
- “Missing pages” admin notice (reuses `[em_admin_notice_missing_pages]` so logic stays centralized)

**Required pages checked**
- `/assessments`
- `/assessment-result`
- `/newsletter`
- `/emergency`
- `/blog`
- `/library`

---

## Diagnostics

### 13) Diagnostics: `includes/class-emindy-diagnostics.php`
A “staging safety net” that can log warnings when `WP_DEBUG` is enabled.

Checks can include:
- Missing required classes
- Missing required DB tables

**Filters**
- `emindy_diagnostics_required_classes`
- `emindy_diagnostics_required_tables`
- `emindy_diagnostics_should_log`

---

## Assets (CSS/JS) and when they load

### CSS
- `assets/css/emindy-core.css` – shared UI styles (always loaded)
- `assets/css/player.css` – player UI styles
- `assets/css/assessments.css` – PHQ-9 / GAD-7 UI styles

### JS
- `assets/js/assess-core.js` – common AJAX + tracking helpers for assessments and analytics
- `assets/js/phq9.js` – PHQ-9 UI logic
- `assets/js/gad7.js` – GAD-7 UI logic
- `assets/js/player.js` – guided practice player logic (reads `data-steps`)
- `assets/js/video-analytics.js` – video tracking (play/chapter interactions, etc.)
- `assets/js/admin.js` – JSON meta box validation/UX
- `assets/js/theme-toggle.js` – theme behavior helper (if enabled/used)

### Fonts
- `assets/fonts/fonts.css` – self-hosted variable fonts (Inter + Vazirmatn) under the unified family alias:
  - **"eMINDy Sans"** (useful for bilingual/RTL readiness)

### Enqueue logic (high-level)
- Always loads `emindy-core.css`
- Loads assessment scripts/styles only when:
  - On `/assessments` page **or**
  - Shortcodes like `[em_phq9]`, `[em_gad7]`, `[em_assessment_result]` are present
- Loads player scripts/styles when:
  - Viewing an exercise **or**
  - Player-related shortcodes exist
- Loads video analytics when:
  - Viewing a video **or**
  - Chapters-related shortcodes exist

---

## Localization

### WordPress i18n
- Text domain: `emindy-core`
- POT file: `languages/emindy-core.pot`

### JSON packs (assessment labels)
- `languages/PHQ-9/phq9-fa.json`
- `languages/PHQ-9/phq9-es.json`

These JSON files provide localized strings for PHQ-9 UI labels where needed (in addition to standard WP translations).

---

## Extensibility reference (key hooks)

### CPT / Taxonomy
- `emindy_cpt_common_args`, `emindy_cpt_exercise_args`, `emindy_cpt_video_args`, `emindy_cpt_article_args`
- `emindy_taxonomies`, `emindy_taxonomy_post_types`, `emindy_taxonomy_default_terms`
- `emindy_core_default_terms`

### Content injection
- `emindy_content_inject_enabled`
- `emindy_content_inject_post_types`
- `emindy_content_inject_player_shortcode`
- `emindy_content_inject_chapters_shortcode`

### Assessments
- `emindy_assessment_supported_types`
- `emindy_assessment_result_base_url`
- `emindy_assessment_result_url`
- `emindy_assessment_rate_limit_max_per_hour`
- `emindy_assessment_rate_limit_expiration`
- `emindy_assessment_email_*` (recipient / subject / body / headers)
- `emindy_assessment_client_ip`
- `emindy_assessment_sign_result_response`

### Analytics
- `emindy_analytics_enabled`
- `emindy_analytics_ip`
- `emindy_analytics_ua`
- `emindy_analytics_tracked`

### Newsletter
- `emindy_newsletter_slug`
- `emindy_newsletter_redirect_base`
- `emindy_newsletter_admin_email`
- `emindy_newsletter_send_welcome`
- `emindy_newsletter_welcome_email_args`
- `emindy_newsletter_subscribed`

### Lifecycle actions
- `emindy_core_before_uninstall`
- `emindy_core_after_uninstall`

---

## Notes on security & privacy
- Assessment result URLs are **signed** to prevent tampering, and signature checks use `hash_equals()`.
- Assessment email sending includes basic **per-IP rate limiting** (filterable).
- All AJAX inputs are sanitized server-side and protected via a shared nonce action.
- Analytics is intentionally minimal: it logs events to a dedicated table and exposes hook points for downstream processing.

---

## Quick usage (common patterns)

### Automatic injection (default experience)
- On single **Exercise** pages, `[em_player]` is prepended automatically.
- On single **Video** pages, `[em_video_chapters]` is appended automatically.

### Manual embedding (optional)
- Add an assessment to any page:
  - `[em_phq9]` or `[em_gad7]`
- Add the result renderer to the result page template/content:
  - `[em_assessment_result]`
- Add newsletter signup form:
  - `[em_newsletter_form]`
- Add related content block to templates/content:
  - `[em_related]`

---

## Support / troubleshooting checklist
- If newly registered CPTs/taxonomies don’t appear, flush permalinks:
  - **Settings → Permalinks → Save Changes**
- If meta-based UI (chapters/steps) isn’t rendering:
  - Confirm JSON meta fields contain valid JSON strings
  - Confirm the relevant shortcode is injected or present in the content
- If schema output isn’t visible:
  - Confirm Rank Math settings (if used) and/or verify fallback schema hook is active
- If analytics/newsletter tables are missing:
  - Confirm plugin activation ran successfully and check staging diagnostics logs (when `WP_DEBUG` is enabled)

---

## Version
- Current plugin constant: **0.5.0** (`EMINDY_CORE_VERSION`)
