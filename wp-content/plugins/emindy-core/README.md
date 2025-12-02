# eMINDy Core Plugin

The **eMINDy Core** plugin provides the custom functionality behind the [eMINDy](https://example.com) project.  It registers custom post types and taxonomies, defines a collection of shortcodes, injects content into posts, outputs structured data, records analytics events and implements a simple newsletter subscription system.  It is designed to work alongside the eMINDy child theme but can be used independently of it.

## Features

* **Custom Post Types:** Registers `em_video`, `em_exercise` and `em_article` content types, each with its own archive and singular templates.
* **Taxonomies:** Defines seven taxonomies (`topic`, `technique`, `duration`, `format`, `use_case`, `level`, `a11y_feature`) with predefined terms for categorisation.
* **Shortcodes:** Exposes over a dozen shortcodes, including:
  * `[em_player]` – renders a step‑by‑step exercise player based on JSON metadata.
  * `[em_video_player]` – embeds a YouTube video or uses Lyte if available.
  * `[em_video_filters]` – outputs a search bar and topic dropdown for the video archive.
  * `[em_search_bar]`, `[em_search_query]`, `[em_search_section]` – helper search components.
  * `[em_phq9]` and `[em_gad7]` – interactive assessments with result sharing and email features.
  * `[em_related]` – displays related posts from the same taxonomy terms.
  * `[em_newsletter]` – displays a newsletter signup form (see below).
* **Newsletter:** A lightweight subscription mechanism that stores emails in a local table (`wp_emindy_newsletter`), sends welcome emails and fires an action hook for third‑party integrations.
* **Structured Data:** Outputs VideoObject, HowTo and Article schema as JSON‑LD when a dedicated SEO plugin is not available, augmenting SEO ranking.
* **Analytics:** Records frontend events (video plays, assessment completions) in a custom table and exposes an AJAX endpoint for logging.

## Installation

1. Upload the `emindy-core` directory to your WordPress `/wp-content/plugins/` directory.
2. Activate the plugin through the WordPress **Plugins** menu.
3. Upon activation the plugin will create two custom database tables: `wp_emindy_newsletter` for subscribers and `wp_emindy_analytics` for event logs.  These tables are removed on plugin uninstall (unless you modify the uninstall routine).

## Usage

Insert the supplied shortcodes into posts, pages or templates to add functionality.  For example:

```
[em_player]
[em_newsletter]
[em_video_player]
[em_phq9]
[em_gad7]
```

Each shortcode accepts optional attributes; consult the source code for details.  Many features (such as the video player) will automatically detect data saved via post meta.

### Newsletter shortcode

Use `[em_newsletter]` to embed a subscription form.  The form collects an email address and optional name, records consent, stores the entry in the newsletter table and sends a welcome email.  Administrators can hook into the `emindy_newsletter_subscribed` action to integrate with external email service providers.

## Localization

The plugin is fully translation‑ready.  Translatable strings are wrapped in the `__()` and `_e()` functions using the `emindy-core` text domain.  Translation files should reside in the `languages/` folder.  To generate a POT file you can run:

```
wp i18n make-pot . languages/emindy-core.pot
```

This repository includes a starter POT file to assist translators.

## Development notes

* Custom tables are created on plugin activation using `dbDelta()`, following best practices【742951481094139†L70-L95】.
* All database interactions are prepared and sanitised to prevent injection attacks.
* AJAX endpoints check nonces for security and restrict access appropriately.

For more information, consult the code in the `includes/` directory.
