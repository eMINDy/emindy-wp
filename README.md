# eMINDy WordPress Assets

This repository contains the custom WordPress assets for the **eMINDy** mental wellness platform.

- `wp-content/themes/emindy` – eMINDy child theme  
- `wp-content/plugins/emindy-core` – eMINDy core plugin  

The repo is used for **internal development + AI agents (Agentic)**, not for public distribution.

---

## Structure

```text
wp-content/
  themes/
    emindy/         # Child theme for the eMINDy site
  plugins/
    emindy-core/    # Core plugin: CPTs, taxonomies, shortcodes, schema, tests, etc.
.github/
  workflows/
    build-zips.yml  # GitHub Actions workflow to build deployable ZIPs
