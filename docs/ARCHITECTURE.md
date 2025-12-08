# eMINDy Architecture Overview

_Last updated: 2025-12-08_

The eMINDy project is a WordPress-based mental wellness platform.  \
This document describes the current structure of the custom child theme and core plugin as they exist in this repository. It is meant to guide future refactors and new features.

---

## 1. Theme: emindy (wp-content/themes/emindy)

- Block-based child theme built on a core block theme.
- Provides block templates, template parts and patterns optimized for exercises, videos and articles.
- Global styles (colours, fonts, dark mode) are defined via `theme.json` / `style.css` and the parent theme provides base styling.

### 1.1 Templates

Key templates under `wp-content/themes/emindy/templates/` and their purposes:

- `single-em_exercise.html` – Single exercise view; shows breadcrumbs, title/meta, auto-injected `[em_player]`, a shortcode-driven steps list via `[em_exercise_steps]`, editable tips section, resource links and newsletter CTA blocks.【F:wp-content/themes/emindy/templates/single-em_exercise.html†L1-L93】
- `single-em_video.html` – Single video view; renders breadcrumbs, title/meta, post content with embedded video (chapters auto-injected), then neutral key takeaways/transcript shells and resources.【F:wp-content/themes/emindy/templates/single-em_video.html†L1-L94】
- `single-em_article.html` – Single article layout; displays breadcrumbs, content, newsletter and related content sections.【e52c30†L1-L120】
- `archive-em_exercise.html` – Exercise archive with header, query loop, filters and CTA blocks.【7a29f0†L1-L120】
- `archive-em_video.html` – Video archive template mirroring the exercise/article archive structure with query loop cards and a CTA footer.【8f46ad†L1-L120】
- `archive-em_article.html` – Article archive listing entries with pagination and a CTA banner.【db9a8a†L1-L120】
- `page-archive-library.html` – Template that loads the `archive-library` pattern to show a unified library view.【952e0c†L1-L40】
- `page-assessment-result.html` – Dedicated assessment result layout that renders a reassuring hero plus the `[em_assessment_result]` shortcode for `/assessment-result/` so the page works without manual editor content.【F:wp-content/themes/emindy/templates/page-assessment-result.html†L1-L34】
- `page-assessments.html` – Hub template for the main assessments page at `/assessments/`, combining a gentle intro with editable page content for links to PHQ-9/GAD-7 or other tools.【F:wp-content/themes/emindy/templates/page-assessments.html†L1-L26】
- Additional defaults: `index.html`, `search.html`, `404.html`, `home.html`, `single.html`, and utility pages such as `page-no-title.html`, `page-help-404.html`, `page-newsletter.html`, `page-articles.html`, `page-exercises.html`, `page-videos.html`, and `front-page.html`.

### 1.2 Patterns and hub layouts

Main patterns under `wp-content/themes/emindy/patterns/`:

- `video-hub.php` – Video library hub layout with hero, search bar, filters and grid of video cards; intended for a dedicated video library page.【F:wp-content/themes/emindy/patterns/video-hub.php†L1-L30】
- `exercise-hub.php` – Exercise library hub with search, filters and exercise grid; intended for a dedicated exercise library page.【F:wp-content/themes/emindy/patterns/exercise-hub.php†L1-L27】
- `article-hub.php` – Article library hub with featured hero, search, filters and article cards; intended for a dedicated article library page.【F:wp-content/themes/emindy/patterns/article-hub.php†L1-L27】
- `libraries-hub.php` – Overview pattern linking to the individual libraries and offering a global search across all content types.【F:wp-content/themes/emindy/patterns/libraries-hub.php†L1-L32】
- `archive-library.php` – Unified archive pattern loaded by `page-archive-library.html` to list multiple content types together.【F:wp-content/themes/emindy/patterns/archive-library.php†L1-L32】
- `front-page-emindy.php` – Front page layout featuring hero CTA, benefits, featured library sections and assessment prompts.【3b52b8†L1-L120】
- Blueprint/modern variants (`exercise-modern.php`, `video-modern.php`, `article-modern.php`, `home-modern.php`, and page blueprints) provide alternative hero + query loop compositions.【f42f8c†L1-L120】【d5be4f†L1-L120】

Several patterns (e.g., `video-hub.php`, `exercise-hub.php`, `article-hub.php`, `libraries-hub.php`, `archive-library.php`) deliver overlapping “library” layouts with similar search/filter/query-loop combinations, differing mainly in content type focus and naming.

### 1.3 Template parts

Header and footer template parts are defined in `wp-content/themes/emindy/parts/`:

- `header.html` – Flex layout with branding, navigation block, Polylang language switcher shortcode, search block, CTA button, and dark-mode toggle script hook.【bf25fa†L1-L60】
- `footer.html` – Footer with newsletter CTA, quick links and social links, inserted via `<wp:template-part>` in templates.【1c815a†L1-L120】

---

## 2. Plugin: emindy-core (wp-content/plugins/emindy-core)

The `emindy-core` plugin provides all custom functionality: custom post types, taxonomies, meta fields, shortcodes, content injection and structured data.

### 2.1 Custom post types (CPTs)

Registered in `includes/class-emindy-cpt.php`:

- `em_exercise` – Guided practices and how-to exercises.
  - Archive slug: `exercise-library` (`/exercise-library/`).
  - Rewrite slug: `exercise`.
  - Supports: title, editor, excerpt, thumbnail, revisions.【a357cc†L12-L35】
- `em_video` – Video-based content.
  - Archive slug: `video-library`.
  - Rewrite slug: `video`.
  - Supports: title, editor, excerpt, thumbnail, revisions.【a357cc†L37-L61】
- `em_article` – Articles and blog-style posts.
  - Archive slug: `article-library`.
  - Rewrite slug: `article`.
  - Supports: title, editor, excerpt, thumbnail, revisions.【a357cc†L63-L87】

All CPTs are public, exposed in the REST API, and share a common set of taxonomies.

### 2.2 Taxonomies

Registered in `includes/class-emindy-taxonomy.php` and attached to all CPTs:

- `topic` (hierarchical) – High-level topics such as stress relief, anxiety & clarity, confidence & growth, sleep & focus, etc.【de7f63†L26-L71】
- `technique` (hierarchical) – Techniques like breathing, body scan, grounding, journaling, affirmations, visualization, mindful walking, etc.【de7f63†L26-L73】
- `duration` (non-hierarchical) – Duration bands such as “30s”, “1m”, “2–5m”, “6–10m”, “10m+”.【de7f63†L26-L74】
- `format` (non-hierarchical) – Type of content: video, article, worksheet, exercise, test, audio, checklist.【de7f63†L26-L75】
- `use_case` (hierarchical) – Situational use cases: morning, bedtime, work break, commute, before sleep, study focus, social context, etc.【de7f63†L26-L78】
- `level` (non-hierarchical) – Depth/intensity level: beginner, gentle, intermediate, deep.【de7f63†L26-L79】
- `a11y_feature` (non-hierarchical) – Accessibility flags: captions, transcript, keyboard-friendly, low-vision friendly, no-music version.【de7f63†L26-L80】

Default terms are inserted for each taxonomy, and all are public and REST-enabled.

### 2.3 Meta fields

Registered in `includes/class-emindy-meta.php`:

- JSON meta for structured steps and chapters:
  - `em_steps_json` – Steps for exercises (used by the player).
  - `em_chapters_json` – Chapters for videos.【954188†L8-L27】
- Numeric meta for timing:
  - `em_total_seconds`, `em_prep_seconds`, `em_perform_seconds` for total/prep/perform times (seconds) used in schema.【954188†L27-L49】
- String/meta for HowTo-style content:
  - `em_supplies`, `em_tools`, `em_yield` for supplies, tools and output.【954188†L49-L76】

All fields are single-value, sanitized, and exposed to the REST API.

### 2.4 Shortcodes

Shortcodes are defined in `includes/class-emindy-shortcodes.php`:

- **Practice & assessment**:
  - `[em_player]` – Interactive practice player that renders steps from `em_steps_json` with timers/controls.【F:wp-content/plugins/emindy-core/includes/class-emindy-shortcodes.php†L47-L90】
  - `[em_exercise_steps]` – Read-only steps list sourced from `em_steps_json` for single exercise templates.【F:wp-content/plugins/emindy-core/includes/class-emindy-shortcodes.php†L92-L129】
  - `[em_phq9]`, `[em_gad7]` – PHQ-9 and GAD-7 self-check questionnaires with scoring (see JavaScript handling in assets).【F:wp-content/plugins/emindy-core/includes/class-emindy-shortcodes.php†L327-L399】
  - `[em_assessment_result]` – Displays assessment summary based on query parameters.【F:wp-content/plugins/emindy-core/includes/class-emindy-shortcodes.php†L497-L557】
  - Assessment result URLs share a base generated by `assessment_result_base_url()` in `includes/helpers.php`, reused by JavaScript localisation and AJAX signing to avoid hard-coded slugs.【F:wp-content/plugins/emindy-core/includes/helpers.php†L10-L23】【F:wp-content/plugins/emindy-core/emindy-core.php†L64-L73】【F:wp-content/plugins/emindy-core/includes/class-emindy-ajax.php†L14-L27】
- **Video & chapters**:
  - `[em_video_chapters]` – Renders a chapter list for video posts based on `em_chapters_json`, optionally linking to YouTube timestamps.【F:wp-content/plugins/emindy-core/includes/class-emindy-shortcodes.php†L276-L307】
- **Discovery & related content**:
  - `[em_related]` – Related content grid using shared taxonomies with language-aware queries and fallback search.【526e72†L117-L191】
  - Helpers for popular content, sitemap-like lists and report links appear alongside related logic (within the same class).
- **Search & utility**:
  - Search UI helpers such as filters, search bar snippets and quick filters live alongside other utility shortcodes (e.g., transcript helpers, table of contents, share links) within the class file.【F:wp-content/plugins/emindy-core/includes/class-emindy-shortcodes.php†L559-L920】
- **Newsletter**:
  - `[em_newsletter]` wraps the newsletter form output from the newsletter component or fallback shortcodes.【F:wp-content/plugins/emindy-core/includes/class-emindy-shortcodes.php†L361-L399】

Deprecated/legacy helpers such as `[em_transcript]`, `[em_video_filters]`, and `[em_video_player]` remain for backwards compatibility and are marked with `@deprecated` notices. The class still contains two `[em_related]` definitions and a broad mix of responsibilities that could be separated in future refactors.【F:wp-content/plugins/emindy-core/includes/class-emindy-shortcodes.php†L559-L920】

### 2.5 Content injection

`includes/class-emindy-content-inject.php` hooks `the_content` to inject shortcode output:

- For `em_exercise` posts, `[em_player]` is prepended so authors do not insert it manually.
- For `em_video` posts, `[em_video_chapters]` is appended after the main content.【b2cd2b†L1-L18】

### 2.6 Schema / structured data

Two schema layers are present:

- **Central builders**
  - `includes/class-emindy-schema.php` exposes static builders for HowTo (exercises), VideoObject (videos) and Article (articles). Each helper maps core post data, featured image, author/dates and relevant meta (steps, timing, supplies/tools, chapters, video embed, etc.) into a single JSON-LD array, returning `null` when key data (e.g. HowTo steps) is missing.【F:wp-content/plugins/emindy-core/includes/class-emindy-schema.php†L9-L195】
- **Rank Math integration (primary path)**
  - `includes/schema.php` enriches Rank Math JSON-LD with Organization, WebSite/SearchAction and safe archive CollectionPage/ItemList nodes, and injects the central builders’ output for CPTs so Rank Math and the plugin stay in sync.【F:wp-content/plugins/emindy-core/includes/schema.php†L15-L173】
- **Fallback schema (when Rank Math is not active)**
  - `includes/class-emindy-schema.php`’s `output_jsonld()` reuses the same builders to print a single JSON-LD `<script>` for the current singular CPT, keeping behaviour aligned with the Rank Math path.【F:wp-content/plugins/emindy-core/includes/class-emindy-schema.php†L197-L240】【F:wp-content/plugins/emindy-core/emindy-core.php†L118-L136】

### 2.7 Newsletter & analytics

- `includes/newsletter.php` creates and manages an `emindy_newsletter` table, handles submissions from `[em_newsletter_form]`, and triggers hooks for external ESP integrations.【fbbb40†L1-L93】
- Welcome and admin notification emails are sent on subscribe; consent is recorded.【fbbb40†L93-L138】
- Additional plugin files (`class-emindy-analytics.php`, `class-emindy-ajax.php`) provide analytics/AJAX handlers and an admin settings page (high level).

---

## 3. Content injection & Polylang integration

- Automatic player/chapter injection into exercises/videos via `the_content` filter ensures consistent embedding without editor steps.【b2cd2b†L1-L18】
- Language switcher shortcode `[em_lang_switcher]` leverages Polylang functions with configurable flags/names/dropdown settings, used in the header template.【526e72†L230-L260】【bf25fa†L27-L51】
- Related content queries optionally pass a `lang` parameter to limit results to the current Polylang language.【526e72†L141-L189】

---

## 4. File reference index

- `wp-content/plugins/emindy-core/includes/class-emindy-cpt.php`
- `wp-content/plugins/emindy-core/includes/class-emindy-taxonomy.php`
- `wp-content/plugins/emindy-core/includes/class-emindy-meta.php`
- `wp-content/plugins/emindy-core/includes/class-emindy-shortcodes.php`
- `wp-content/plugins/emindy-core/includes/class-emindy-content-inject.php`
- `wp-content/plugins/emindy-core/includes/schema.php`
- `wp-content/plugins/emindy-core/includes/class-emindy-schema.php`
- `wp-content/plugins/emindy-core/includes/newsletter.php`
- `wp-content/themes/emindy/templates/single-em_exercise.html`
- `wp-content/themes/emindy/templates/single-em_video.html`
- `wp-content/themes/emindy/templates/single-em_article.html`
- `wp-content/themes/emindy/templates/archive-em_exercise.html`
- `wp-content/themes/emindy/templates/archive-em_video.html`
- `wp-content/themes/emindy/templates/archive-em_article.html`
- `wp-content/themes/emindy/templates/page-archive-library.html`
- Key patterns under `wp-content/themes/emindy/patterns/`
