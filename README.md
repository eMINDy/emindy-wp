# eMINDy WordPress Assets

This repository contains the custom WordPress assets for the eMINDy mental wellness platform:

- `wp-content/themes/emindy` – eMINDy child theme (based on the default block theme).
- `wp-content/plugins/emindy-core` – eMINDy core plugin (custom CPTs, shortcodes, schema, etc.).

## Installation (manual deploy)

1. Download or clone this repository.
2. Copy the folders into your WordPress installation:

   - `wp-content/themes/emindy` → `/wp-content/themes/emindy`
   - `wp-content/plugins/emindy-core` → `/wp-content/plugins/emindy-core`

3. In wp-admin:
   - Activate the **eMINDy** child theme.
   - Activate the **eMINDy Core** plugin.

All production changes should go through this repository first, then be deployed manually as ZIPs to the live site.
