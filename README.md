# eMINDy WordPress Assets

This repository contains the custom WordPress assets for the eMINDy mental wellness platform. It includes all code for the site’s child theme and core plugin, which together provide the site’s functionality and design.

- **wp-content/themes/emindy** – eMINDy child theme (for layout, styling, and templates)  
- **wp-content/plugins/emindy-core** – eMINDy core plugin (for custom post types, taxonomies, shortcodes, schema, self-tests, etc.)

> **Note:** This repo is for internal development and AI-assisted coding (Agentic). It is not a publicly distributed package. The live eMINDy site is deployed manually by building ZIP files for the theme and plugin – it does not pull directly from this repository.

---

## Structure

wp-content/
  themes/
    emindy/         # Child theme for the eMINDy site (requires Twenty Twenty-Five parent)
  plugins/
    emindy-core/    # Core plugin: CPTs, taxonomies, shortcodes, schema, tests, etc.

docs/
  *.md              # Documentation guides (overview, installation, architecture, etc.)

.github/
  workflows/
    build-zips.yml  # GitHub Actions workflow to build deployable theme & plugin ZIPs


The repository follows a standard WordPress structure. Key components of the system are contained within the child theme and plugin directories, while additional documentation is provided in the `docs/` folder (covering installation, architecture, development practices, user guide, optimization plans, and more).

---

## Internal Usage

Developers should use feature branches and Pull Requests when making changes (see `docs/development.md` and `docs/contributing.md` for guidelines). All code must adhere to WordPress coding standards and project-specific best practices (accessibility, i18n, security). Commit messages should be clear and prefixed by component (e.g., `theme: ...` or `plugin: ...`).

Continuous Integration is set up to package the theme and plugin into ZIPs for deployment – refer to `docs/installation.md` and `docs/RELEASE.md` for the deployment workflow.

For AI coding agents, refer to `docs/AGENTIC.md`, which defines what automated changes are allowed and the expected workflow for code suggestions.

---

## Getting Started

1. Ensure you have a WordPress environment (**v6.x or higher recommended**) with the **Twenty Twenty-Five** parent theme installed (required for the child theme).
2. Clone this repository into the WordPress `wp-content` directory, or download and extract the latest built ZIPs for the theme and plugin.
3. Activate the **eMINDy Core** plugin and **eMINDy** theme via the WordPress admin dashboard.
4. Refer to `docs/installation.md` for detailed setup steps (including required plugins such as **Polylang** for multilingual support).
5. After activation, configure the site as described in `docs/configuration.md` (create essential pages, set up language settings, etc.) to match the expected platform structure.

For a high-level understanding of the project’s mission and architecture, see:

- `docs/overview.md`
- `docs/architecture.md`

These documents explain how the theme and plugin work together to deliver the eMINDy mental wellness platform.

---
