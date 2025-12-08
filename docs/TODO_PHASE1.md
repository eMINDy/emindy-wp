# Phase 1 TODOs and Improvement Points

This document lists inconsistencies, missing pieces and potential technical debt discovered during the Phase 1 architecture inventory.  \
Items are grouped by responsibility area and tagged with the suggested phase in which to address them.

Phases:
- Phase 2 – Quick wins and fixing broken pieces
- Phase 3 – Larger refactors and UX improvements
- Phase 4 – SEO / schema / performance alignment
- Phase 5 – Internationalisation (i18n) and Polylang
- Phase 6 – Long-term enhancements and testing

## THEME

- **Video archive consistency** — A dedicated archive-em_video.html template exists, but video archives also rely on shared hub patterns; layouts are inconsistent with exercise/article archives and should be aligned.  \
  Target phase: **Phase 2**
- **Static exercise steps & tips** — The single-em_exercise.html template contains hard-coded "Steps" and "Tips & variations" sections instead of rendering data from em_steps_json and other meta fields. This causes duplication and makes updates error-prone.  \
  Target phase: **Phase 3**
- **Static video takeaways & transcript** — The single-em_video.html template contains manually written key takeaways and transcript content. These should eventually be generated from structured meta or the main content to avoid stale or mismatched information.  \
  Target phase: **Phase 3**
- **Pattern duplication and naming** — Several patterns (libraries-hub.php, archive-library.php, video-hub.php, etc.) provide overlapping “library” layouts. They should be reviewed, consolidated and named consistently.  \
  Target phase: **Phase 3**
- **Translation readiness (templates)** — Many headings and labels in templates/patterns (e.g. search labels, section titles, CTAs) are hard-coded in English. They should be wrapped in translation helpers and registered for Polylang.  \
  Target phase: **Phase 5**
- **Hard-coded CTAs and section copy** — Important CTAs and section texts are embedded directly in templates/patterns, which makes updating and translating them difficult. These should be moved to options or translatable strings.  \
  Target phase: **Phase 5**

## PLUGIN

- **Duplicate [em_related] implementations** — class-emindy-shortcodes.php defines the [em_related] shortcode in two places. The intended behaviour should be clarified and only one implementation kept.  \
  Target phase: **Phase 2**
- **Unused or orphaned shortcodes** — Some shortcodes (e.g. transcript helpers, report links, certain search/”popular” widgets) do not appear to be used by the current theme. Their usage should be audited and either documented for editor use or removed.  \
  Target phase: **Phase 3**
- **Large monolithic shortcodes class** — class-emindy-shortcodes.php mixes many responsibilities (player, assessments, search UI, sharing, language switcher, etc.) in a single large file. It should later be split into smaller, focused classes/files.  \
  Target phase: **Phase 4**
- **Input sanitisation and security** — Some assessment-related shortcodes read from $_GET or trust query parameters without strong sanitisation or nonce protection. These should be reviewed and hardened.  \
  Target phase: **Phase 2**
- **Schema duplication and consistency** — Both includes/schema.php and class-emindy-schema.php generate schema. They should be checked for overlap and kept in sync, removing outdated logic.  \
  Target phase: **Phase 4**
- **Default taxonomy terms and labels** — Default taxonomy terms (e.g. topics like “Hope & Inspiration”) may not perfectly match current editorial needs. These should be validated and cleaned up with the content owner.  \
  Target phase: **Phase 3**
- **Newsletter table & privacy** — The newsletter feature writes email addresses into a custom table (emindy_newsletter) without double opt-in. The data model and flows should be reviewed for privacy/compliance and possibly integrated with an external ESP.  \
  Target phase: **Phase 4**

## SEO / SCHEMA

- **Align schema with templates** — The JSON-LD emitted for HowTo, VideoObject and Article sometimes assumes data (steps, durations, chapters) that is not fully visible in templates. Either templates or schema should be updated so they always match.  \
  Target phase: **Phase 4**
- **Video schema on archives** — The unified archive and hub pages list multiple videos but may not output appropriate ItemList / VideoObject schema for those lists. Investigate whether this would be beneficial and implement accordingly.  \
  Target phase: **Phase 4**
- **Breadcrumb schema** — Visual breadcrumbs are rendered (e.g. via Rank Math), but it should be verified that breadcrumb JSON-LD is also present in the structured data output.  \
  Target phase: **Phase 4**

## I18N (Internationalisation & Polylang)

- **Translate all user-facing strings** — All hard-coded English strings in templates and shortcodes should be wrapped with translation functions and registered so that Polylang can localise them.  \
  Target phase: **Phase 5**
- **Meta duplication across translations** — When posts are translated, custom meta (steps, chapters, durations) may not automatically copy over. A strategy is needed either to sync meta or to provide a separate meta translation UI.  \
  Target phase: **Phase 5**
- **Language switcher UX** — The [em_lang_switcher] shortcode should be reviewed for accessibility and styling so that language switching is easy and consistent across the site.  \
  Target phase: **Phase 3**

## OTHER

- **Code organisation and naming** — The plugin mixes procedural functions and class-based code. In later phases, a more consistent structure (e.g. autoloading and clearer namespaces) should be introduced.  \
  Target phase: **Phase 4**
- **Remove dead code** — Commented-out blocks and unused functions should be identified and removed to reduce noise.  \
  Target phase: **Phase 3**
- **Accessibility enhancements** — The existence of the a11y_feature taxonomy suggests accessibility is important; templates and shortcodes should be audited for accessibility (alt text, keyboard navigation, ARIA attributes, etc.).  \
  Target phase: **Phase 4**
- **Performance and caching** — Heavier shortcodes (search, related content, popular items) should be profiled and possibly cached to reduce database load.  \
  Target phase: **Phase 4**
- **Testing and CI** — Over the long term, unit/integration tests and a basic CI pipeline should be added to prevent regressions in critical parts of the plugin and theme.  \
  Target phase: **Phase 6**
- **Documentation** — Inline documentation and developer guides should continue to be improved after this initial phase so new contributors (or future agents) can quickly understand the system.  \
  Target phase: **Phase 2**
