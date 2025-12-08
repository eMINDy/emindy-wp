# eMINDy Theme & Core Plugin Technical Review

## Overview
- **Architecture:** The `emindy-core` plugin registers the `em_exercise`, `em_video`, and `em_article` CPTs plus shared taxonomies (`topic`, `technique`, `duration`, `format`, `use_case`, `level`, `a11y_feature`). It injects `[em_player]` on exercise singles and `[em_video_chapters]` on videos, and exposes assessment/player/newsletter shortcodes. Schema is generated via Rank Math filters with fallback JSON-LD builders (`HowTo`, `VideoObject`, `Article`).
- **Theme:** The `emindy` child theme loads dark-mode assets, adds skip links, tweaks queries, and defines block templates for CPT singles/archives. A primary-topic meta box supports SEO/grouping, and there are Rank Math robot hints for search/404 pages.

## Issues & Improvements
| Area | Severity | Location | Problem | Suggested Fix |
| --- | --- | --- | --- | --- |
| Security | High | `wp-content/themes/emindy/functions.php` save handler | Primary topic meta saves without checking user capabilities, so any POST with a valid nonce can update the meta. | Add `current_user_can( 'edit_post', $post_id )` before updating the meta and bail otherwise. |
| Performance | Medium | `wp-content/plugins/emindy-core/includes/class-emindy-taxonomy.php` default terms | Default terms are re-checked/inserted on every `init`, adding extra taxonomy queries on all requests. | Move term seeding into plugin activation or guard with an option/transient so it only runs when needed. |
| Multilingual / UX | Medium | `wp-content/themes/emindy/templates/single-em_video.html` | Several user-facing strings (“Key takeaways”, “Stay in practice”, placeholder paragraphs) are hard-coded in English and not translation-friendly. | Convert fixed copy to translatable strings or replace with block patterns/placeholders that editors can localize per language. |
| SEO / Content Safety | Low | `wp-content/themes/emindy/functions.php` search excerpt highlighting | Regex uses the raw search query; while preg_quote is applied, multibyte edge cases can still break highlights and add HTML in excerpts from filters. | Wrap result in `wp_kses_post` or use `highlight` via `str_ireplace` on an escaped excerpt to avoid regex edge cases and HTML injection risks. |

## Quick Wins
- Add capability check to the primary topic save callback to align with WordPress security best practices.
- Gate default taxonomy term insertion behind an activation hook to reduce per-request overhead.
- Internationalize static text in video templates so Polylang locales get consistent UI.
- Harden search excerpt highlighting output with `wp_kses_post()` to prevent malformed markup when patterns fail.
- Add `aria-label`/translated text to the newsletter CTA headings in templates for clearer a11y/SEO signals.

## Refactor Roadmap
1. **Security & Permissions:** Patch meta-saving capability checks across theme/plugin and ensure all nonce validations also verify current user rights.
2. **Performance:** Move initialization tasks (default terms, heavy queries) to activation or cached flows; audit shortcodes that spawn queries to ensure `no_found_rows` and cached schema.
3. **A11y & i18n:** Replace hard-coded template copy with translatable strings; ensure headings and interactive elements in shortcodes (player, assessments) carry labels and consistent hierarchy.
4. **SEO & Schema:** Add caching for JSON-LD builders and validate required fields (duration, transcript availability) for `VideoObject`/`HowTo` before output to avoid partial markup.
