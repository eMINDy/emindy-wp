# eMINDy WordPress Release & Deployment Guide

This document explains how to build and deploy the eMINDy child theme and core plugin from this repository.

## 1. Build ZIPs via GitHub Actions

1. Go to the **Actions** tab of the `emindy-wp` repository on GitHub.
2. Select the workflow named **"Build eMINDy theme & plugin ZIPs"**.
3. Click **"Run workflow"** and confirm.
4. Wait until the job finishes successfully.
5. In the workflow run, scroll down to the **Artifacts** section and download the `emindy-wp-zips` artifact.
6. Extract the downloaded archive locally; it contains:
   - `emindy-core.zip`
   - `emindy-theme.zip`

## 2. Deploy to your WordPress hosting

> Always make a backup or use a staging environment before updating live.

1. Log in to the WordPress admin of your target site.
2. Go to **Plugins → Add New → Upload Plugin**:
   - Upload `emindy-core.zip`.
   - If the plugin already exists, WordPress will offer to replace it with the new version.
   - Activate the plugin if it is not already active.
3. Go to **Appearance → Themes → Add New → Upload Theme**:
   - Upload `emindy-theme.zip` (the child theme).
   - If the theme already exists, WordPress will offer to replace it.
4. After updating:
   - Visit key pages:
     - one exercise, one video, one article
     - exercise/video/article library pages
     - `/assessments/` and `/assessment-result/`
   - Confirm that everything renders correctly.

## 3. Versioning & Changelog

- Theme and plugin versions are stored in:
  - `wp-content/themes/emindy/style.css` (Theme header)
  - `wp-content/plugins/emindy-core/emindy-core.php` (Plugin header)
- The high-level change history is maintained in `docs/CHANGELOG.md`.

## 4. Using Agentic or other coding agents

- Agents should:
  - Work in feature branches.
  - Follow the guidance in `docs/AGENTIC_PLAYBOOK.md`.
  - Avoid making changes directly on `main`.
- After Agentic changes are merged to `main`, you can repeat the steps above to build and deploy new ZIPs.
