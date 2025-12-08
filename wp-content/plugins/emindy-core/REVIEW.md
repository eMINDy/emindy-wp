# eMINDy Theme & Core Plugin Technical Review

## Architecture Overview
### emindy-core plugin
- **Bootstrap & loading:** The plugin bootstraps CPT/taxonomy/shortcode/meta/schema modules, Ajax handlers, and analytics, while enqueueing shared CSS/JS for the player and assessments (`emindy-assess-core`, `phq9`, `gad7`, `video-analytics`). It also localizes AJAX endpoints/nonces and outputs fallback JSON-LD when Rank Math is absent.【F:wp-content/plugins/emindy-core/emindy-core.php†L12-L189】
- **Content models:** Registers three REST-exposed CPTs—`em_exercise`, `em_video`, `em_article`—with custom archive slugs and shared taxonomies for consistent filtering/navigation.【F:wp-content/plugins/emindy-core/includes/class-emindy-cpt.php†L6-L75】
- **Taxonomies & seed data:** Defines seven shared taxonomies (topic, technique, duration, format, use_case, level, a11y_feature) and inserts default terms for each on every `init` run to keep filters populated across CPTs.【F:wp-content/plugins/emindy-core/includes/class-emindy-taxonomy.php†L6-L117】
- **Shortcodes & UI:** Implements player, steps list, chapter list, transcript, assessment forms (PHQ-9/GAD-7), language switcher, related content, newsletter capture, and video filters to be embedded in templates or content blocks.【F:wp-content/plugins/emindy-core/includes/class-emindy-shortcodes.php†L6-L107】

### emindy child theme
- **Assets & supports:** Enqueues the child stylesheet and dark-mode toggle script, loads translations, and enables common theme supports (title tag, thumbnails, HTML5, align-wide, editor styles, responsive embeds).【F:wp-content/themes/emindy/functions.php†L14-L47】
- **SEO & UX helpers:** Adds search/404 noindex rules (and fallback meta tags), skip-link injection, YouTube preconnect hints, and query tweaks for video archives (per-page limits, topic filter, sanitized search, optional alpha sort).【F:wp-content/themes/emindy/functions.php†L49-L218】
- **Editorial tooling:** Provides schema ItemList helper plus a “Primary Topic” meta box for posts/pages (and emindy CPTs) with a warning notice when unset, enabling manual topical relevance control.【F:wp-content/themes/emindy/functions.php†L130-L205】
- **Templates:** Block templates for emindy CPTs (e.g., video single) layer breadcrumbs, metadata, content, related content, transcripts, and newsletter CTA sections, with multiple static headings/placeholders baked into the markup.【F:wp-content/themes/emindy/templates/single-em_video.html†L1-L107】

## Prioritized Issues
| Category | Priority | Location | Issue | Recommendation |
| --- | --- | --- | --- | --- |
| Security | High | Theme primary-topic save handler | Meta save lacks a capability check; any user able to trigger the nonce can update `_em_primary_topic`. | Gate save logic with `current_user_can( 'edit_post', $post_id )` before persisting meta.【F:wp-content/themes/emindy/functions.php†L186-L194】 |
| Performance | Medium | Taxonomy seed routine | Default term insertion runs on every `init`, adding repeated term existence checks to all requests. | Move seeding to activation or guard with an option/transient so it runs once unless explicitly refreshed.【F:wp-content/plugins/emindy-core/includes/class-emindy-taxonomy.php†L6-L117】 |
| Code quality | Medium | Search excerpt highlighter | Regex-based replacement injects `<mark>` into excerpts without output escaping and could mis-highlight multibyte queries. | Wrap the result in `wp_kses_post()` or switch to `str_ireplace` on escaped excerpts to avoid malformed markup.【F:wp-content/themes/emindy/functions.php†L99-L105】 |
| Accessibility | Medium | Video single template | Multiple headings/strings (“Key takeaways,” “Transcript,” “Stay in practice,” etc.) are hard-coded English, limiting localization and screen-reader clarity. | Replace static copy with translatable strings or block patterns that editors localize per language.【F:wp-content/themes/emindy/templates/single-em_video.html†L42-L105】 |
| SEO | Low | Video meta block | Updated date paragraph hardcodes “Updated:” text and a dot separator rather than using accessible, translatable constructs. | Convert to a localized string (e.g., `sprintf`) or block pattern with proper semantics and translation wrappers.【F:wp-content/themes/emindy/templates/single-em_video.html†L18-L27】 |
| Architecture | Low | Inline term seeding | Taxonomy defaults live in a runtime hook rather than activation, coupling boot-time requests to data provisioning. | Relocate defaults to activation hook alongside CPT/taxonomy registration and add a repair command for manual reseeding.【F:wp-content/plugins/emindy-core/includes/class-emindy-taxonomy.php†L6-L117】 |

## Quick Wins (Top 5–10)
1. Add `current_user_can( 'edit_post', $post_id )` to the primary-topic save callback to align with WP capability checks.【F:wp-content/themes/emindy/functions.php†L186-L194】
2. Gate taxonomy default term insertion behind an activation flag or transient to avoid per-request overhead.【F:wp-content/plugins/emindy-core/includes/class-emindy-taxonomy.php†L6-L117】
3. Internationalize static headings and placeholder text in `single-em_video.html` so Polylang locales get localized UI and better a11y cues.【F:wp-content/themes/emindy/templates/single-em_video.html†L42-L105】
4. Escape or sanitize the search excerpt highlighter output to prevent malformed markup while keeping `<mark>` tags.【F:wp-content/themes/emindy/functions.php†L99-L105】
5. Swap the “Updated:” meta text to a translatable string or block to improve SEO signals and localization consistency.【F:wp-content/themes/emindy/templates/single-em_video.html†L18-L27】
6. Add aria-labels or descriptive headings to the newsletter CTA and transcript summary to aid assistive tech users.【F:wp-content/themes/emindy/templates/single-em_video.html†L54-L105】
7. Document shortcode usage (player, assessments, transcript) in editor help to reduce misuse and ensure consistent embeds across templates.【F:wp-content/plugins/emindy-core/includes/class-emindy-shortcodes.php†L6-L107】

## Phased Refactor Roadmap
1. **Stability & Security (Phase 1):** Patch capability checks on meta saves; add sanitization/escaping to excerpt highlights and shortcode outputs; add automated smoke tests for shortcode rendering on sample CPTs.【F:wp-content/themes/emindy/functions.php†L99-L194】【F:wp-content/plugins/emindy-core/includes/class-emindy-shortcodes.php†L6-L107】
2. **Performance & Data Hygiene (Phase 2):** Move taxonomy seeding to activation with an idempotent check; cache heavy shortcode queries; profile enqueue stack to ensure assets load only when needed (e.g., assessments/player only on CPT singles).【F:wp-content/plugins/emindy-core/emindy-core.php†L122-L164】【F:wp-content/plugins/emindy-core/includes/class-emindy-taxonomy.php†L6-L117】
3. **Localization & Accessibility (Phase 3):** Replace hard-coded template copy with translatable strings/patterns; add aria attributes to interactive blocks; verify heading hierarchy across template parts and CTA components.【F:wp-content/themes/emindy/templates/single-em_video.html†L42-L107】
4. **SEO & Schema (Phase 4):** Standardize date/meta rendering with translatable patterns, ensure transcript/chapters populate schema via Rank Math filters or fallback JSON-LD, and consider caching schema output to avoid duplicate computations.【F:wp-content/plugins/emindy-core/emindy-core.php†L143-L157】【F:wp-content/plugins/emindy-core/includes/class-emindy-shortcodes.php†L88-L107】【F:wp-content/themes/emindy/templates/single-em_video.html†L18-L65】
5. **Architecture & DX (Phase 5):** Introduce activation/CLI tasks for reseeding taxonomies and validating CPT rewrites; centralize shortcode docs and template guidance in the repo to reduce editor errors and streamline manual ZIP deployments.【F:wp-content/plugins/emindy-core/includes/class-emindy-taxonomy.php†L6-L117】【F:wp-content/plugins/emindy-core/includes/class-emindy-shortcodes.php†L6-L107】
