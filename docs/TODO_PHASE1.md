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
- **Static exercise steps & tips** — addressed in Phase 3 by replacing hard-coded lists with `[em_exercise_steps]` and leaving tips editable per post content.  \
  Target phase: **Phase 3** (completed)
- **Static video takeaways & transcript** — single-em_video.html now uses neutral placeholders ready for structured data, avoiding stale hard-coded text. Further automation still planned.  \
  Target phase: **Phase 3** (partially completed; schema/meta automation deferred)
- **Pattern duplication and naming** — library hub patterns reviewed and labelled for their intended destinations (video/exercise/article hubs, overview hub, unified archive).  \
  Target phase: **Phase 3** (completed for naming/role clarity)
- **Dedicated assessment result page & template** — ensure `/assessment-result/` uses a consistent template that renders `[em_assessment_result]` without relying on manual page content.  \
  Target phase: **Phase 4** (completed)
- **Translation readiness (templates)** — Many headings and labels in templates/patterns (e.g. search labels, section titles, CTAs) are hard-coded in English. They should be wrapped in translation helpers and registered for Polylang.  \
  Target phase: **Phase 5**
- **Hard-coded CTAs and section copy** — Important CTAs and section texts are embedded directly in templates/patterns, which makes updating and translating them difficult. These should be moved to options or translatable strings.  \
  Target phase: **Phase 5**

## PLUGIN

- **Duplicate [em_related] implementations** — class-emindy-shortcodes.php defines the [em_related] shortcode in two places. The intended behaviour should be clarified and only one implementation kept.  \
  Target phase: **Phase 2**
- **Unused or orphaned shortcodes** — Initial audit done; unused helpers like `[em_transcript]`, `[em_video_filters]`, `[em_video_player]` are kept for compatibility but marked `@deprecated` for future cleanup.  \
  Target phase: **Phase 3** (partially completed; further removal pending)
- **Large monolithic shortcodes class** — class-emindy-shortcodes.php mixes many responsibilities (player, assessments, search UI, sharing, language switcher, etc.) in a single large file. It should later be split into smaller, focused classes/files.  \
  Target phase: **Phase 4**
- **Input sanitisation and security** — Some assessment-related shortcodes read from $_GET or trust query parameters without strong sanitisation or nonce protection. These should be reviewed and hardened.  \
  Target phase: **Phase 2**
- **Schema duplication and consistency** — Both includes/schema.php and class-emindy-schema.php generate schema. They should be checked for overlap and kept in sync, removing outdated logic.  \
  Target phase: **Phase 4**
- **Default taxonomy terms and labels** — Inline documentation added for default term groups to aid future validation; editorial review still needed.  \
  Target phase: **Phase 3** (partially completed)
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
- **Language switcher UX** — Shortcode updated with clearer labels, aria attributes and active-language indicators to improve accessibility while retaining existing structure.  \
  Target phase: **Phase 3** (completed)

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
