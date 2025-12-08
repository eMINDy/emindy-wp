# eMINDy WordPress Site – Diagnostic and Optimization Plan

This document summarizes immediate debugging steps and phased improvements for the eMINDy child theme (`emindy`) and core plugin (`emindy-core`). It is intended for copy-and-paste into ChatGPT Codex when preparing fixes and enhancements.

## 1) Diagnose and Resolve the Critical Error (Immediate)
- **Enable logging**: Turn on `WP_DEBUG` and `WP_DEBUG_LOG` in `wp-config.php`; reproduce the issue and inspect `wp-content/debug.log` for the file/line causing the fatal error.
- **Isolate the source**: Toggle the `emindy-core` plugin and the `emindy` theme independently against a default theme to determine whether the failure originates in the plugin, theme, or a third-party conflict.
- **Verify database tables**: Ensure custom tables are created on activation (newsletter and analytics). Run `emindy_newsletter_install_table()` and `Analytics::install_table()` via activation hooks if missing.
- **Check hooks and namespaces**: Confirm CPTs/taxonomies register on `init`, classes are loaded before use, and hooks match current WordPress conventions.
- **Memory or config issues**: If logs indicate memory exhaustion, raise `WP_MEMORY_LIMIT` appropriately.
- **Regression tests**: After the fix, test CPT archives, single views, assessments (PHQ-9, GAD-7), search/filters, newsletters, analytics, schema output, and language switching with Rank Math both enabled and disabled.

## 2) Quick Wins and Code Hygiene (Phase 2)
- **Remove duplication**: Consolidate duplicate shortcode definitions (e.g., `[em_related]` appears twice). Clearly mark deprecated shortcodes (`[em_transcript]`, `[em_video_filters]`, `[em_video_player]`) with `@deprecated` notes and warn admins on use.
- **Input sanitization**: Sanitize all request data for shortcodes and AJAX handlers (`sanitize_text_field`, `absint`, `sanitize_key`, `wp_unslash`) and ensure hash/nonce checks are present for assessment result pages.
- **Nonce coverage**: Add nonce validation to any remaining forms or AJAX endpoints missing it.
- **Hardening**: Guard PHP entry points with `if ( ! defined( 'ABSPATH' ) ) { exit; }`.
- **Newsletter and privacy**: Add consent language to the newsletter form and consider double opt-in or ESP integration. Ensure table creation runs only on activation.
- **Archive consistency**: Align markup and query logic across video, exercise, and article archives; choose a single hub pattern.
- **Translations**: Wrap all user-facing strings with the correct text domain (`emindy` for theme, `emindy-core` for plugin). Prepare Farsi translations and avoid hard-coded English or Persian text.
- **Admin notices**: Use the `[em_admin_notice_missing_pages]` shortcode to flag missing critical pages and provide creation guidance.

## 3) Performance and SEO Enhancements (Phase 4)
- **Query and caching audit**: Use Query Monitor to find heavy queries; reduce nested `WP_Query` usage and cache expensive shortcode results via transients/object cache.
- **Asset strategy**: Defer/async non-critical scripts, minify/merge CSS/JS, compress assets (gzip/brotli), and lazy-load optimized images with `srcset/sizes`. Preconnect to third-party domains where appropriate.
- **SEO fundamentals**: Verify search engine visibility settings, pretty permalinks, correct CPT slugs, unique meta descriptions/H1s, XML sitemaps, HTTPS, and canonical URLs. Keep custom JSON-LD schema in sync with Rank Math to avoid duplication.
- **Core Web Vitals**: Implement page/object caching, set width/height on media to prevent CLS, and limit third-party script impact.

## 4) Design, UX, and Accessibility (Phases 3 & 4)
- **Pattern harmonization**: Deduplicate and unify hub patterns (`video-hub`, `exercise-hub`, `article-hub`, `libraries-hub`) so layouts, cards, spacing, typography, and CTAs are consistent.
- **Responsiveness and semantics**: Use semantic HTML with proper heading hierarchy, skip links, accessible form labels, alt text, descriptive aria labels, visible focus states, and fluid layouts across breakpoints.
- **CTAs and engagement**: Centralize CTA text as translatable strings; ensure related content and reading-time shortcodes are performant and relevant. Maintain dark mode contrast.
- **404 and navigation**: Provide a helpful 404 page and clear navigation including language switching.

## 5) Security and Privacy Hardening (Ongoing)
- **Updates and footprint**: Keep core, themes, and plugins updated; remove unused components. Enforce strong passwords, 2FA, login throttling, and HTTPS/HSTS.
- **Surface reduction**: Disable file editing (`DISALLOW_FILE_EDIT`), restrict XML-RPC if unused, and limit admin capabilities.
- **Data handling**: Document data flows, use double opt-in for newsletters, anonymize analytics, and honor opt-outs with defined retention policies.

## 6) Internationalization and Multilingual Support (Phase 5)
- **Strings and text domains**: Ensure every user-facing string uses translation helpers with the correct domain. Avoid hard-coded bilingual text in templates/patterns.
- **Meta duplication**: Confirm `pll_copy_post_metas` continues to copy custom meta fields when new ones are added.
- **Language UX**: Keep language switcher accessible and reflective of the current language; add `hreflang` when supported by SEO tooling.

## 7) Long-Term Refactors and Testing (Phases 4–6)
- **Separation of concerns**: Break the monolithic shortcodes class into focused classes (e.g., assessments, search, sharing) and adopt PSR-4 autoloading with Composer. Standardize namespacing and move shared helpers into utilities.
- **Extensibility**: Add hooks/filters to reduce the need for core edits by downstream customization.
- **Automation**: Introduce PHPUnit tests, integration tests for key flows, and GitHub Actions for CI (coding standards via `phpcs`/`wpcs`).
- **Documentation**: Expand `ARCHITECTURE.md` and onboarding docs with local setup, ZIP build steps, and deployment notes. Maintain `TODO_PHASE1.md` with a phased roadmap and mark completed work.
