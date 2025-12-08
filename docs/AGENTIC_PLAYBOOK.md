# Agentic Playbook for eMINDy WordPress Repo

This document describes how coding agents (e.g. GitHub-connected AI assistants) should work on this repository.

## 1. Repository structure

- Child theme:
  - `wp-content/themes/emindy`
- Core plugin:
  - `wp-content/plugins/emindy-core`
- Documentation:
  - `docs/ARCHITECTURE.md`
  - `docs/TODO_PHASE1.md`
  - `docs/CHANGELOG.md`
  - `docs/RELEASE.md`
  - `docs/AGENTIC_PLAYBOOK.md`

Custom post types and taxonomies, shortcodes, schema, assessments, and Polylang integration are all implemented in the `emindy-core` plugin.

## 2. General rules for agents

- Always work in a **feature branch** (never commit directly to `main`).
- Group changes by concern (e.g. "schema improvements", "UX tweaks", "shortcode refactor").
- Prefer small, focused PRs over large, mixed ones.
- Do not:
  - Introduce new PHP dependencies or external libraries.
  - Change database schemas or create new custom tables without explicit instructions.
  - Store secrets or environment-specific configuration in the repo.

## 3. Where to look before changing things

- Read `docs/ARCHITECTURE.md` to understand:
  - How the child theme and plugin are structured.
  - How CPTs, taxonomies, shortcodes, and schema are wired together.
- Read `docs/TODO_PHASE1.md` to see:
  - Which areas have known TODOs and in which phase they are meant to be addressed.
- Check `docs/CHANGELOG.md` to understand recent changes and avoid reintroducing old patterns.

## 4. Theme guidelines

- Restrict changes to:
  - Block templates under `wp-content/themes/emindy/templates/`
  - Pattern files under `wp-content/themes/emindy/patterns/`
  - `functions.php` and theme configuration as needed.
- Preserve:
  - Overall “calm, supportive” UX.
  - Compatibility with dark mode and RTL where applicable.
- Do not:
  - Hard-code per-post content in templates (use post content and meta instead).
  - Break archive or single template slugs/names that WordPress uses to resolve templates.

## 5. Plugin guidelines

- Main areas:
  - CPTs & taxonomies: `class-emindy-cpt.php`, `class-emindy-taxonomy.php`
  - Meta fields: `class-emindy-meta.php`
  - Shortcodes: `class-emindy-shortcodes.php`
  - Content injection: `class-emindy-content-inject.php`
  - Schema: `class-emindy-schema.php` and schema helpers
  - Polylang: meta copy integration
  - Newsletter: newsletter table and handlers
- Follow existing patterns:
  - Use WordPress sanitisation/escaping helpers.
  - Keep shortcodes cohesive; if a file becomes too large, plan a dedicated refactor.

## 6. i18n and Polylang

- All user-facing strings in PHP should be wrapped in translation functions:
  - `__( 'Text', 'emindy-core' )` for the plugin.
  - `__( 'Text', 'emindy' )` for the theme.
- Do not change Polylang configuration from code unless explicitly instructed.
- Remember that key meta (steps, chapters, durations) are copied to new translations via the `pll_copy_post_metas` filter.

## 7. Testing & safety

- Before committing:
  - Run PHP syntax checks for modified files using `php -l`.
  - Manually review changes for obvious issues (no debugging `var_dump`, `die`, etc.).
- In PR descriptions:
  - Mention which files and areas were changed.
  - List any manual smoke tests that should be run by the maintainer.

## 8. Release flow

- Once a PR is merged into `main`:
  - The maintainer can run the **"Build eMINDy theme & plugin ZIPs"** workflow.
  - The generated ZIPs are then deployed as described in `docs/RELEASE.md`.
