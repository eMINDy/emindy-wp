Installation & Setup Guide

This guide walks you through installing the eMINDy WordPress theme and plugin, both for local development and for a production (live) environment. It also covers required dependencies, initial configuration, and verifying that everything is set up correctly.

1. Prerequisites

Before installing eMINDy, ensure you have the following:

WordPress: A WordPress site running version 6.3 or above (the platform was developed and tested on WP 6.3–6.4). The site can be on a local development environment (e.g., using XAMPP, Local by Flywheel, etc.) or a web server for production.

PHP: Version 7.4 or higher (PHP 8.x recommended for performance).

Database: MySQL/MariaDB as required by WordPress.

Parent Theme: The WordPress Twenty Twenty-Five theme (slug twentytwentyfive). This should be included with WP 6.4+ by default. If not, download it from the official theme repository. Having this parent theme is necessary for the eMINDy child theme to function.

Permissions: Ensure you have access to upload themes/plugins or FTP into the WordPress installation, and that file permissions allow installing custom code.

Optionally, decide if you need the multilingual and SEO components:

If you plan to run the site in multiple languages (English/Persian), install the Polylang plugin (free) from the WordPress plugin repository.

For SEO enhancements, you might install Rank Math SEO or an alternative SEO plugin. eMINDy is built to integrate with Rank Math if present.

2. Installation for Development

If you are a developer setting up eMINDy for the first time on a local or staging environment, follow these steps:

A. Clone or Download the Repository

Clone the eMINDy repository from GitHub into your WordPress setup’s wp-content directory. For example:
cd path/to/your/wp-content
git clone https://github.com/eMINDy/emindy-wp.git emindy-wp

This will create an emindy-wp folder containing wp-content/themes/emindy and wp-content/plugins/emindy-core subfolders.

If you don’t have Git access, you can download the repository as a ZIP, extract it, and upload the emindy theme folder and emindy-core plugin folder into the respective wp-content/themes and wp-content/plugins directories.

B. Install the Parent Theme

Verify that Twenty Twenty-Five (the parent theme) is installed. In a development environment, you can simply check under Appearance → Themes for “Twenty Twenty-Five”. If it’s not there:

Download it from WordPress.org (if available separately) or update WordPress to the latest version (which includes the default theme).

Alternatively, copy the twentytwentyfive theme folder into wp-content/themes/.

C. Activate Theme and Plugin

In the WordPress admin dashboard, go to Plugins and find “eMINDy Core”. Click Activate. (Activation will trigger creation of the plugin’s custom DB tables – see notes below.)

Next, go to Appearance → Themes and activate the eMINDy theme (it should be listed as a child theme of Twenty Twenty-Five).

After activation, it’s good practice to navigate to Settings → Permalinks and simply hit Save Changes (you don’t need to modify anything). This flushes WordPress rewrite rules and ensures that the custom post type URLs (like /video-library/) work immediately.

D. Install Recommended Plugins (Development Environment)

For full parity with production, you may want to install:

Polylang: Activate it and configure at least two languages (e.g., English and Persian). You can initially skip actual translation setup; just having Polylang active will let you test language switching and how content is duplicated.

Rank Math SEO: If you want to test structured data integration. After installing, run its setup if needed (set basic options, nothing eMINDy-specific). If you prefer not to use Rank Math in dev, the eMINDy plugin will simply output fallback schema.

WP Mail SMTP or equivalent: In a dev environment, outbound emails (for the newsletter welcome or assessment results) might not work by default. Using an SMTP plugin or a mail catcher (like MailHog) can help you test those emails. This is optional but recommended if you intend to test the full flow locally.

E. Developer Configuration

Turn on debug mode in WordPress while developing. In your wp-config.php, ensure:
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
This will log any PHP errors/notices to wp-content/debug.log. It’s very useful for catching issues early. The eMINDy codebase strives to have no PHP warnings or notices.

(Optional) Increase PHP memory limit if your environment is low by default. eMINDy itself is not very heavy, but if you import media or have many plugins, memory might need to be bumped. For example:
define( 'WP_MEMORY_LIMIT', '256M' );
If using Polylang, set up the basics: go to Languages in the admin, add English and Persian (for example). You can choose one as default. Polylang will add a language switcher to the admin bar. No further config needed for now.

If using Rank Math, after activating it, you might want to disable its other modules not needed for dev (e.g., you can skip connecting an account, etc.). Just ensure Rank Math > Titles & Meta settings for post types and taxonomies are as you like (the defaults usually fine). eMINDy will inject additional schema regardless of those settings.

F. Importing Sample Content (Optional)

If you have exported content or a database from production, you can import it to have data to work with. Otherwise, consider creating a few dummy items:

Create some Topics, Techniques, etc., in Posts → Categories or via the CPT menus (since the taxonomies might appear there). Or use the default terms that were auto-created on plugin activation.

Add a couple of Exercises (with dummy steps JSON for testing, e.g., [{ "text": "Do X", "duration": 30 }, { "text": "Do Y", "duration": 30 }]).

Add a Video (you can use any YouTube video ID for testing – put a YouTube embed link in the content or use the [em_video_player] shortcode with an ID, although embedding via block is fine).

Add an Article with some text.

Create pages for Assessments, Assessment Result, Newsletter, etc. (See configuration section below for exact slug names and templates to use.)

This will help you see the site in action.

3. Installation for Production

When you’re ready to set up eMINDy on a live server (or update an existing live site), follow these steps. The process differs slightly because you likely want to use the packaged ZIP files and be cautious about downtime.

Important: Always backup your database and files before a first-time install or an upgrade on production. While eMINDy only adds data (custom tables, etc.) and doesn’t take over core, it’s good practice to have a rollback point. Also consider using a staging site to test the installation if possible.

A. Obtain the Release ZIPs

There are two main components to upload on production: the theme and the plugin. You can get these as packaged zip files by either building from source or downloading a pre-built release:

GitHub Actions Artifact: If you have access to the repository’s Actions, you can run the “Build eMINDy theme & plugin ZIPs” workflow. This will generate an artifact containing emindy-theme.zip and emindy-core.zip. Download these to your local machine.

Manual Build: Alternatively, you can create the zips manually:

Create a zip of the wp-content/themes/emindy folder (call it e.g. emindy-theme.zip).

Create a zip of the wp-content/plugins/emindy-core folder (emindy-core.zip).
Make sure not to include the .git folder or any development-only files in these zips.

B. Upload and Activate on WordPress

Log in to the WordPress Admin of your live site.

Install the Plugin:

Go to Plugins → Add New → Upload Plugin. Select emindy-core.zip and upload it.

WordPress will prompt to install it; proceed and then click Activate. If an older version is already installed, WordPress will ask to replace it – confirm the replacement.

After activation, verify that no errors occurred. The plugin will create/update its custom tables if not present. You can check for the presence of tables wp_emindy_newsletter and wp_emindy_analytics in your database (optional).

Install the Theme:

Go to Appearance → Themes → Add New → Upload Theme. Upload the emindy-theme.zip. If an eMINDy theme exists from before, it will ask to replace – confirm.

After uploading, activate the eMINDy theme. (If the parent Twenty Twenty-Five theme is not already present, WordPress will warn you – in that case, install the parent theme first via Appearance → Themes → Add New, search “Twenty Twenty-Five”, install, then activate eMINDy child theme.)

Post-Activation Steps:

Visit the front-end of the site and navigate to various pages:

Load one exercise page, one video page, and one article page to ensure they display properly (with no “template not found” errors and that dynamic content like players or chapters are appearing).

Check the archive pages: Exercises Library, Videos Library, Articles (if you have a unified library page or you might need to visit /exercise-library/ etc. for CPT archives).

Go to the Assessments page (/assessments/) and ensure it loads (the content might be minimal if you haven’t edited that page, but the template’s existence is key).

Go through an assessment quiz to test that it works end-to-end (see that the result page /assessment-result/ shows the outcome).

Visit the Newsletter sign-up page (/newsletter/) and ensure the form displays. You can do a test subscription with a throwaway email to verify the flow (after submission you should see a thank-you message, and if you check the admin email inbox, a notification; also check that the subscriber is added in the DB and received a welcome email if you have email set up).

Check core site features: menu, language switcher, dark mode toggle, search. Make sure search works (search for a term and see results, and that no odd characters show up). The first page load after activating might have regenerated permalinks, so search should be fine if permalinks are set.

If everything looks correct, the site is now running eMINDy.

C. Sample Configuration for Production

Permalink Structure: It’s recommended to use “Post name” permalinks (common for most sites) so that CPT URLs like /article/<slug> and custom archives like /video-library/ are neat. In Settings → Permalinks, ensure an appropriate structure (e.g., %postname%) is selected.

Polylang Setup: If using Polylang, configure your languages and translate pages/posts:

Add languages in Languages → Languages. eMINDy expects English and Persian, but you can add others. For each CPT, you may set whether it’s translatable (Polylang settings). Usually, yes – you’d want to provide content in multiple languages.

Translate the critical pages. e.g., once you have an “Assessments” page in English, create a Persian version of that page and assign the Polylang translation relationship. Do this for “Assessment Result”, “Newsletter”, and any navigation pages (“Start Here”, etc.), so that switching language finds the corresponding page.

The theme’s language switcher shortcode will automatically handle showing the dropdown or names as configured (by default in the header it’s set to dropdown with names, no flags).

Rank Math SEO: If installed, go through Rank Math’s setup wizard. Pay attention to:

Sitemap: ensure it includes the custom post types (Rank Math usually auto-detects and includes them in its sitemap unless turned off).

Meta Titles/Descriptions: you might want to set templates for the CPTs in Rank Math (e.g., for videos, use %title% – eMINDy will append schema but not meta titles).

Ensure NoIndex is set for search and 404 (Rank Math might do it by default; eMINDy theme also filters to enforce it).

After configuring, browse a few pages’ source to see that meta tags look correct and that either Rank Math’s JSON-LD or eMINDy’s fallback JSON-LD is present (but not both). If Rank Math is active, you should see a single unified JSON-LD block in your page source that includes HowTo or VideoObject for CPTs, meaning integration is working.

Email Sending: Configure an SMTP service if not already (for example, via WP Mail SMTP plugin with your SMTP or an API like SendGrid). This ensures the newsletter emails and assessment result emails reliably reach inboxes. Do a test: use the newsletter form and assessment email feature to verify emails are delivered and not flagged.

D. Troubleshooting Installation Issues

If you activate the plugin and immediately see a “critical error” on the site, check wp-content/debug.log (if debug was enabled) or the server error logs. Common causes could be missing PHP extensions or version mismatches. The code requires PHP 7.4+, and uses some modern PHP features (like typed properties), so if you’re on PHP 7.0 or something, it will fatal – upgrade PHP in that case.

Database tables not created: If the newsletter or analytics tables are not present, it could be that the activation hook didn’t run (which happens if you activated via a Composer-based deploy or WP-CLI without running activation). To fix, you can deactivate and reactivate the plugin via the admin, or manually call the installer functions:

E.g., in a WP-CLI or a small script, call \EMINDY\Core\Analytics::install_table() and the emindy_newsletter_install_table() function (defined in newsletter.php)
GitHub
. Afterwards, also flush permalinks as mentioned.

Parent theme missing: If eMINDy theme won’t activate, make sure the parent is in place. The style.css header of eMINDy indicates Template: twentytwentyfive
GitHub
, so the folder name must be exactly twentytwentyfive.

Permalinks 404: If custom post type pages return 404, it means rewrite rules weren’t refreshed. Saving Permalinks or calling flush_rewrite_rules() once (the plugin does that on activation automatically
GitHub
) will solve it.

Polylang translation of slugs: eMINDy custom post types use custom rewrite slugs (like “video” for em_video). Polylang might allow translating these slugs. It’s generally easier to keep them in English for consistency, but if needed you can define translations in Polylang settings. Just ensure archive slugs like “video-library” also get updated if you change them. This guide assumes default slugs.

4. Sample Configuration & Verification

Once installed, run through this checklist to configure and verify the site:

General Settings: Set your Site Title and Tagline (e.g., “eMINDy – Your Calm Corner” as title and a tagline). The theme will display these in the header.

Reading Settings: If you have a specific page for homepage or blog, assign them. Often eMINDy might use a custom Front Page, so you can leave “Your homepage displays” as “Latest Posts” if using our front-page template. If you want a separate blog page for WordPress Posts (if you use Posts separately from Articles CPT), set a page as “Posts page”. Typically though, you might not use the native Posts at all, focusing on Article CPT instead.

Create Required Pages: (This is important for eMINDy to function correctly)

Create a page titled Assessments (slug “assessments”). You don’t need to put any content in it unless desired; just publishing it is enough. The theme has a template page-assessments.html that will automatically be used for this page, showing the intro and placeholder for the quizzes.

Create a page titled Assessment Result (slug “assessment-result”). Again, no content needed from you; it uses page-assessment-result.html which contains the [em_assessment_result] shortcode to display quiz outcomes.

Create a page Newsletter (slug “newsletter”). You can leave content blank; page-newsletter.html will provide the design and include the newsletter sign-up form shortcode.

(Optional but recommended) Create a page Start Here (slug “start-here”). This could be a welcome page for new users. The theme doesn’t have a hardcoded template for it, but the header CTA button links to /start-here/ by default
GitHub
. You can use this page to introduce the platform, suggest first steps (like taking a self-test or trying a popular exercise).

(Optional) If you plan to have a blog outside of Articles CPT, create a page Blog (slug “blog”) and then in Settings → Reading, set that as the “Posts page”. If you won’t use WordPress Posts at all (focusing on the Article CPT for all articles), you can skip this. The plugin will redirect /blog to home if the page doesn’t exist, to avoid dead links
GitHub
.

(Optional) Create a page Library or All Content (slug “library” or “libraries”). The theme includes a template page-archive-library.html that when assigned to a page could display a unified library of all content types (via the pattern archive-library). This is not strictly required, but if you want one page listing everything or linking to each library section, this can be nice. If you don’t create it, the plugin’s 404 handler will redirect “/library” requests to the Articles section to handle users who might guess that URL
GitHub
.

Ensure any pages you created are published (not drafts) and in the correct language (if using Polylang, create translations for each as well).

Menu Setup: Go to Appearance → Menus (or the Site Editor navigation block) and ensure the main menu has the items you want. The theme likely provided a default menu with Home, Videos, Exercises, Articles, etc., plus the language switcher and search in the header. Adjust as needed – for instance, you may add the Assessments page to the menu so users can find the self-tests, or a Contact page if you have one.

Test Front-End: As a final verification, browse the site as a normal user:

Switch the site to Persian (if applicable) and make sure the pages we created have Persian versions or at least that the switcher doesn’t produce 404s (Polylang will default to showing only languages where translation exists, so it might hide the switch for pages that aren’t translated – that’s fine).

Fill out the newsletter form with a test email. Then check your database’s wp_emindy_newsletter table to see the entry (and check that you got the emails).

Try an assessment in each language (the quiz text is currently only in English, unless you translate the strings in the plugin language file – by default, Persian language would still show English questions unless translated, so you may want to translate those strings via Poedit or WP’s translation tools for a fully localized experience).

View an exercise and ensure the steps and player controls display properly (the player’s UI texts like “Start”, “Next step” are translatable, so verify the language toggle changes those too if you provided translations).

View a video page and test the chapter links (if you added chapters JSON, each should scroll or jump within the embedded video to that time – note: clicking a chapter likely opens the YouTube embed’s player at that timestamp).

Search for a keyword using the search bar. The theme should display results highlighting the term (the theme’s functions.php adds highlight in excerpts for search terms
GitHub
). Check that it returns results from all content (by default, WordPress search includes posts and pages; to include CPT content, the theme has adjusted queries or provided search integration).

If anything is not working as expected, double-check the previous configuration steps. Most commonly, missing pages or not flushing permalinks are the culprits for content not showing or 404s.

5. Updating eMINDy

(For future reference) To update the eMINDy theme or plugin on an existing site, the process is similar to installation:

Obtain the new version ZIPs (via build process or release download).

In WP Admin, deactivate eMINDy Core plugin (to unlock files), then upload the new plugin ZIP via Plugins → Add New (WordPress will handle replacing files) and reactivate. Or use FTP to overwrite the plugin files.

Upload the new theme ZIP via Appearance → Themes and activate the updated theme (or simply overwrite the theme files via FTP, since it’s a child theme, activation remains the same).

Check that version numbers bumped (in style.css and plugin header) and run through a quick test of key features.

Review the CHANGELOG.md (in the docs or release notes) to see if any new configuration is needed (e.g., if new pages or settings were introduced in the update).

Zero-downtime tip: Because the site’s front-end is largely static until user interaction, you can first update the theme (users might not notice a split-second switch) and then update the plugin. Most new features will not be invoked until both components are updated, so it’s generally safe to update in either order – but updating the plugin first ensures any new shortcodes or data are in place before the theme tries to use them.
