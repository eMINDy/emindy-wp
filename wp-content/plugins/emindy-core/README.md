# eMINDy Core Plugin

The **eMINDy Core** plugin provides the custom functionality behind the eMINDy mental wellness platform. It registers custom post types and taxonomies, defines a collection of shortcodes, injects dynamic content into posts, outputs structured data (schema), records simple analytics, and implements a newsletter subscription system. The plugin is designed to work alongside the eMINDy child theme but can also function independently to provide key features on any WordPress site.

---

## Features

### Custom Post Types (CPTs)

The plugin registers three main content types for the platform:

- **`em_video`** – for video-based content (e.g. guided videos, talks).
- **`em_exercise`** – for guided practices or exercises (step-by-step activities).
- **`em_article`** – for articles or written posts (blog-style educational content).

Each CPT has its own archive page:

- `/video-library/`
- `/exercise-library/`
- `/article-library/`

and pretty permalinks for single entries (e.g. `/video/<slug>/`, etc.).

These types support the standard post fields (title, editor content, excerpt, featured image, etc.) and are public and REST API–enabled *(GitHub)*.

---

### Custom Taxonomies

The plugin defines a set of taxonomies attached to all the above CPTs for rich categorization:

- **`topic`** (hierarchical) – broad content topics (e.g. *Stress Relief*, *Confidence & Growth*, *Sleep & Focus*, etc.) *(GitHub)*.
- **`technique`** (hierarchical) – methods or techniques used (e.g. *Breathing*, *Body Scan*, *Journaling*, *Visualization*) *(GitHub)*.
- **`duration`** (flat) – duration ranges (e.g. *30s*, *1m*, *2–5m*, *10m+*) *(GitHub)*.
- **`format`** (flat) – content format/type (e.g. *video*, *article*, *audio*, *exercise*, *worksheet*) *(GitHub)*.
- **`use_case`** (hierarchical) – situational tags (e.g. *morning*, *bedtime*, *work break*, *study focus*) *(GitHub)*.
- **`level`** (flat) – difficulty or intensity level (e.g. *beginner*, *intermediate*, *deep*) *(GitHub)*.
- **`a11y_feature`** (flat) – accessibility features (e.g. *captions*, *transcript available*, *no music version*) *(GitHub)*.

Default terms for these taxonomies are seeded on activation (to provide a starting set of categories), and all are exposed in the REST API. Terms and taxonomies can be managed via the WP admin as usual.

---

### Shortcodes

The plugin provides over a dozen shortcodes to insert dynamic content and UI components. Key groups include:

#### Practice & Assessment Tools

- **`[em_player]`**  
  Renders an interactive step-by-step exercise player using the steps JSON meta of an Exercise post (includes timers, progress, etc.) *(GitHub)*.  
  Typically used at the top of exercise pages to guide the user through the practice steps.

- **`[em_exercise_steps]`**  
  Outputs a read-only list of steps for an exercise (an ordered list of step text/durations) *(GitHub)*.  
  Used in exercise content or templates for a textual outline of steps.

- **`[em_phq9]`**  
  Displays the **PHQ-9** mood self-assessment questionnaire (9 questions with radio inputs) *(GitHub)*.  
  Users can fill this in to get a depression severity score; results are handled via JavaScript and can be shared or emailed.

- **`[em_gad7]`**  
  Displays the **GAD-7** anxiety self-assessment questionnaire (7 questions with radio inputs) *(GitHub)*.  
  Similarly interactive on the client side.

- **`[em_assessment_result]`**  
  After a PHQ-9 or GAD-7 is submitted, this shortcode shows a summary of the score (with a severity band like “Mild” or “Moderate”) and a brief note *(GitHub, GitHub)*.  
  It expects `type`, `score`, and `sig` parameters in the URL. It’s usually placed on a dedicated “Assessment Result” page that the quizzes redirect to.

#### Video & Media

- **`[em_video_chapters]`**  
  Outputs a structured list of chapter timestamps and titles for a video, based on the video’s `em_chapters_json` meta. This helps users navigate sections of a longer video *(GitHub)*.

- **`[em_transcript]`** *(Deprecated)*  
  Displays the post content as a formatted transcript. Legacy shortcode, replaced by simply using the post content block.

- **`[em_video_player]`** *(Deprecated)*  
  Embeds a YouTube video player for the current post’s video ID. Legacy: video embedding is now typically done via the core embed block or other means.

#### Content Discovery & Links

- **`[em_related]`**  
  Shows a grid of related content items (e.g., “Related Exercises” or “Related Videos”) relevant to the current post *(GitHub)*.  
  It automatically finds posts sharing taxonomy terms (like the same topic) and falls back to a search if needed. It’s aware of language (Polylang) and will limit results to the current language by default.

- **`[em_related_posts]`**  
  Alias for `[em_related]` (for backward compatibility). Using it will internally call the same logic, with a deprecation warning for developers.

- **`[em_search_bar]`**  
  Outputs a search input field, optionally with some preset placeholders or settings.  
  This, and the following shortcodes with the `em_search_` prefix, assist in building custom search/filter UIs. They may be tied to older implementations and are subject to refactoring.

- **`[em_search_query]`, `[em_search_section]`**  
  Helper shortcodes related to displaying search queries or segmented search results.  
  These were used in earlier versions; advanced search UIs may now be handled by block patterns.

#### Utility & Miscellaneous

- **`[em_newsletter]`**  
  Embeds a newsletter sign-up form (see **Newsletter System** below) *(GitHub, GitHub)*.  
  This is typically placed on the Newsletter page or in footers to collect user emails.

- **`[em_lang_switcher]`**  
  Outputs a language switcher (using Polylang’s functions) as either a dropdown or list of languages *(GitHub)*.  
  In the theme’s header, for example, it’s used to show a dropdown allowing users to switch between English and Persian easily.

- **`[em_video_filters]`** *(Deprecated)*  
  Outputs a filter UI (search field + dropdown for topics) for the video library *(GitHub)*.  
  Legacy: similar functionality is achieved now via theme template code and core blocks.

- **`[em_admin_notice_missing_pages]`** *(Admin only)*  
  Outputs a notice (visible to admins) listing if certain critical pages are missing (like the blog page or library page), to prompt their creation *(GitHub)*.

Each shortcode is documented in the source with its purpose. You can insert these in WordPress posts/pages or in block shortcode blocks. Many are automatically used by theme templates, so you may not need to use them manually often.

---

### Newsletter System

The plugin includes a lightweight newsletter subscription feature:

- A front-end sign-up form via shortcode:  
  - `[em_newsletter]` or  
  - the lower-level `[em_newsletter_form]`.  

  Users provide name, email, and a consent checkbox *(GitHub, GitHub)*.

On submission, the plugin:

- Stores the subscriber info in a custom database table `wp_emindy_newsletter` *(GitHub)*.
- Sends out emails:
  - A **welcome email** to the subscriber with a friendly greeting and a suggested first exercise (this content can be edited in `newsletter.php` if needed) *(GitHub)*.
  - An **admin notification email** to the site admin address whenever a new subscriber joins *(GitHub)*.

The form ensures basic GDPR compliance:

- It records a consent flag.
- It does **not** add the email unless the consent checkbox is checked *(GitHub)*.

A WordPress action hook **`emindy_newsletter_subscribed`** is fired after a successful sign-up *(GitHub)*, allowing developers to integrate third-party email services (Mailchimp, etc.) by hooking into that event.

Duplicate sign-ups (same email) are prevented:

- The table has a `UNIQUE` index on `email`.
- If an email already exists, it will update the name/consent rather than creating a new row *(GitHub)*.

There is no complex newsletter-sending logic included (e.g., no campaign management). The expectation is to export the list or integrate an external service if needed. The built-in system is meant for small-scale or initial use, storing subscribers internally.

---

### Structured Data (SEO Schema)

The plugin enhances SEO by providing structured data (JSON-LD):

- For each custom content type, the plugin can output appropriate schema:
  - **HowTo** schema for exercises,
  - **VideoObject** for videos,
  - **Article** for articles *(GitHub, GitHub)*.

These are generated using the post’s metadata (steps, duration, etc.) and ensure search engines can understand the content type.

#### Rank Math Integration

If the **Rank Math SEO** plugin is active (as it is on the eMINDy site), the plugin hooks into Rank Math’s schema-filtering system:

- It enriches Rank Math’s output with eMINDy-specific data:
  - Adding **Organization** and **WebSite** schema,
  - Adding our HowTo/VideoObject details into Rank Math’s JSON-LD for each post *(GitHub, GitHub)*.

This avoids duplicating schema output while still providing rich data.

#### Fallback Mode

If Rank Math is **not** present:

- The plugin outputs its own `<script type="application/ld+json">` on the front-end with the needed schema for the current page *(GitHub)*.
- This is done via a `wp_head` action in the plugin, but only if Rank Math isn’t already handling it *(GitHub)*.

Additionally, the theme adds a simple filter for search/404 pages to mark them **noindex** (with or without Rank Math), to prevent indexing empty search results *(GitHub, GitHub)*.

---

### Analytics (Lightweight)

The plugin provides basic analytics/event tracking:

- On activation, it creates a table **`wp_emindy_analytics`** to log events (e.g., video plays, quiz completions) *(GitHub, GitHub)*.  
  The table stores:
  - Timestamp
  - Event type
  - Labels/values
  - Associated post ID
  - User IP
  - User agent

- The plugin registers AJAX endpoints (`emindy_track`) to allow front-end scripts to send events *(GitHub, GitHub)*.  
  For example, custom JavaScript might send an event when a user completes an assessment or finishes watching a video. The AJAX handler will record that event into the table asynchronously.

#### Security (Analytics)

- Nonces are required for these AJAX calls.  
  The plugin uses a shared nonce `emindy_assess` for all its AJAX requests *(GitHub)*.
- Logged-out users can also trigger events (e.g., all visitors), since `wp_ajax_nopriv_emindy_track` is enabled *(GitHub)*.

The collected data is minimal, and there’s **no** interface in WP Admin to view it out of the box – it’s primarily meant for analysis by developers (or could be exposed in a custom dashboard later). IP addresses are stored for uniqueness but truncated or limited in length, and user agents are capped to avoid huge entries *(GitHub)*.

---

### Content Injection

The plugin automatically injects certain shortcodes into post content so authors/editors don’t have to:

- When an **Exercise** (`em_exercise`) is displayed, the `[em_player]` shortcode is **prepended** to the content by a `the_content` filter *(GitHub)*.  
  This ensures every exercise page starts with the interactive player based on its steps.

- When a **Video** (`em_video`) is displayed, the `[em_video_chapters]` shortcode is **appended** after the content *(GitHub)*.  
  This lists the chapters below the video description.

These injections run:

- Only on the front-end view (checked via `is_singular( 'em_exercise' )`, etc.).
- Only on the main query loop.

The mechanism is defined in `class-emindy-content-inject.php` and can be adjusted if needed.

---

### Admin Utilities

The plugin also includes several utilities for administrators and editors:

- **Custom Meta Boxes**  
  It adds custom Meta Boxes in the post editor for Exercises and Videos *(GitHub, GitHub)*.  
  These meta boxes allow entering:
  - JSON data for steps or chapters,
  - Additional fields for exercises (like total time, supplies, tools, etc.).  
  This provides a user-friendly way to input the structured data that powers `[em_player]` and the schema.

- A small bit of admin JavaScript (`assets/js/admin-json.js`) is enqueued on those post edit screens to validate the JSON format in real-time and show messages like “Valid JSON ✔” or “Invalid JSON ✖” *(GitHub)*.  
  This helps content editors avoid JSON formatting mistakes.

- On the admin dashboard, if critical pages are missing (like the “Blog” page for posts or the “Library” page for unified content), an admin-only shortcode `[em_admin_notice_missing_pages]` can be placed in an admin dashboard widget or notice area to highlight the issue *(GitHub)*.  
  This isn’t automatic; it’s available for use in an admin custom dashboard or documentation page.

---

### Technical Design

The plugin is structured using classes and namespaces for clarity:

- All core functionality resides under the PHP namespace **`EMINDY\Core`**.
- Classes such as `CPT`, `Taxonomy`, `Shortcodes`, `Content_Inject`, `Meta`, `Schema`, `Ajax`, `Analytics` encapsulate respective features.

The plugin’s main file `emindy-core.php`:

- Loads all class definitions.
- Hooks their static methods into WordPress:

Key hooks and behavior:

- Custom post types and taxonomies are registered on `init` *(GitHub, GitHub)*.
- Shortcodes are registered on `init` (with priority 9, to ensure they’re ready early) *(GitHub)*.
- Content injection, meta field registration, etc., also hook into `init` *(GitHub)*.
- Assets (CSS/JS) are enqueued on `wp_enqueue_scripts` *(GitHub)*.
- Translation files are loaded on `plugins_loaded` *(GitHub)*.
- Activation and uninstall hooks are used to create and drop custom tables respectively *(GitHub, GitHub)*.

See `docs/architecture.md` for a full breakdown of class responsibilities and hooks.

---

## Installation

1. **Upload Files**

   - Copy the `emindy-core` plugin folder into your WordPress `wp-content/plugins/` directory, **or**
   - Install the plugin as a ZIP via:  
     **Plugins → Add New → Upload Plugin** using the packaged `emindy-core.zip`.

2. **Activate Plugin**

   - In WordPress admin, go to **Plugins** and activate **eMINDy Core**.

3. **Verify Tables**

   On activation, the plugin will automatically create two custom database tables:

   - `wp_emindy_newsletter` – stores newsletter subscribers (email, name, consent, etc.) *(GitHub)*.
   - `wp_emindy_analytics` – stores analytics event logs *(GitHub)*.

   These tables are created with the appropriate character set and collation, using WordPress’s `dbDelta` mechanism (following best practices *(GitHub)*). No further setup is required.

   > **Note:** If you uninstall the plugin, it will drop these tables (to avoid leaving data behind).  
   > If you prefer to keep subscriber data on uninstall, you can comment out the `DROP` statements in the `emindy_core_uninstall()` function.

4. **Permalinks**

   After activation, WordPress should become aware of the new post types. It’s recommended to go to:

   - **Settings → Permalinks** and click **“Save Changes”**  

   to flush rewrite rules, ensuring the custom post type URLs (like `/video/<slug>`) work immediately.

5. **Install Recommended Plugins (Optional)**

   For full functionality:

   - **Polylang** (if you need multilingual support).  
     eMINDy Core will detect Polylang and ensure translated posts copy necessary metadata.
   - **Rank Math SEO** (if you want enhanced SEO and XML sitemaps).  
     eMINDy Core will integrate with it seamlessly, but also works without it.
   - An **SMTP plugin** (like *WP Mail SMTP*) if you want to ensure emails (newsletter welcome, result emails) are reliably delivered.

6. **Activate eMINDy Theme (Recommended)**

   While not strictly required, the custom shortcodes and layout are designed with the eMINDy child theme in mind.

   - Activate the **eMINDy theme** (see its README) so that the front-end output of shortcodes integrates into the design as intended.  
     For example, the theme provides CSS for the classes output by these shortcodes.

After these steps, you can start adding content (Videos, Exercises, Articles) via the WordPress admin. The plugin’s features (shortcodes, etc.) will be ready for use in the content or automatically applied via the theme.

---

## Usage

Once activated, the plugin’s features largely work in the background or via shortcodes.

### Creating Content

In the admin, you’ll see new menu items for **Videos**, **Exercises**, and **Articles**. Use those to add content. Each content type behaves like a post, with some custom fields:

- **Exercises (`em_exercise`)**

  - Write the instructions in the editor.
  - Scroll down to the **“eMINDy JSON Meta”** meta box.
  - Paste or write a JSON array of steps (stored in `em_steps_json`).
  - Fill in optional fields like total seconds, prep time, etc.  
    These improve the `[em_player]` functionality and the schema output.
  - Save the post and view it – you should see the interactive player and steps list automatically.

- **Videos (`em_video`)**

  - Embed the YouTube video in the post content (e.g., paste a YouTube link which auto-embeds).
  - Add a JSON array of chapter timestamps in the meta box (`em_chapters_json`) if the video has sections.
  - When viewing the video post, a chapters list will appear after the content.

- **Articles (`em_article`)**

  - Articles are straightforward – just write the article.
  - They behave like normal posts; the plugin currently doesn’t add meta boxes for articles since they are mostly text.

### Inserting Shortcodes

Many shortcodes are placed for you via theme templates. However, you can also use them in the block editor.

Examples:

- To add a newsletter sign-up form in a post:

  ```text
  [em_newsletter]
To add a list of related articles to a custom page:
[em_related post_type="em_article" count="3"]
If the eMINDy theme is active, you may not need to manually add shortcodes for core features because the theme covers them. For instance:

The Assessments page template already contains the PHQ-9/GAD-7 forms or links.

The Assessment Result page uses [em_assessment_result] in its template.

Admin Monitoring

There isn’t a dedicated UI for the newsletter or analytics in the dashboard.

To view subscribers, you’ll need to look at the database (e.g., via phpMyAdmin or another tool) in the wp_emindy_newsletter table, or develop a small custom admin page to list them.

For analytics events, similarly check wp_emindy_analytics.

Future improvements may include basic admin views or exporting capabilities.

Localization (Content & Meta)

If adding a new language:

Create a .po file from the provided .pot (for both theme and plugin).

Place it in the languages/ folder.

The plugin will load the appropriate .mo file on plugins_loaded (GitHub).

The Polylang plugin will help manage translations for posts and taxonomies.

The plugin automatically ensures that when you create a translation of a post, certain meta (like steps JSON, times, chapters, etc.) are copied to the new language post so you don’t have to re-enter them (GitHub).

Newsletter Shortcode Usage

The [em_newsletter] shortcode is often used on a dedicated “Newsletter” sign-up page. When you insert this shortcode (or view the theme’s Newsletter page template), it will display:

A form with Email and Name fields and a Subscribe button (GitHub, GitHub).

A required consent checkbox with customizable text (e.g., “I agree to receive updates…”).
This text is set via translation strings, default provided in English/Persian (GitHub).

On successful submission:

The user is redirected to the same page with a ?success=1 parameter.

This triggers a thank-you message:
“Thank you! Please check your inbox.” displayed in place of the form (GitHub).

This indicates that a welcome email was sent.

No additional configuration is needed for the newsletter feature out of the box – just ensure:

You have a valid email set as the site admin (to get notifications).

Your server can send mail.

Localization (Plugin Text)

The plugin is fully translation-ready.

Translatable strings are wrapped in __(), _e(), etc., with text domain emindy-core.

Translation files (.mo/.po) should reside in the languages/ folder.

A template POT file languages/emindy-core.pot is included for convenience.

Developers can update the POT by running a WP-CLI command from the plugin directory:
wp i18n make-pot . languages/emindy-core.pot
This will scan the plugin and regenerate the POT file with any new strings (GitHub).
The same can be done for the theme (with its text domain and POT file).

The plugin also handles one Polylang-specific integration:

It adds a filter so that when duplicating content for translation, Polylang copies over custom meta fields like steps, chapters, etc., to the translated post (GitHub).
This ensures translators start with the same structured data and only need to translate textual content.

Development Notes

The code is structured into classes for maintainability. Check the includes/ directory for organized components. For example:

Shortcodes are all in the Shortcodes class (GitHub).

Custom types are in the CPT class, etc.

Static methods and WordPress hooks are used to initialize everything.

We follow WordPress PHP Coding Standards (via PHPCS and WPCS rulesets):

Indentation, sanitization, and escaping are enforced.

All database queries use $wpdb with prepared statements or dbDelta for schema changes.
See code comments for references to best practices – for example, custom table creation on activation is implemented per WP recommendations (GitHub).

Security

All user input (GET/POST in shortcodes or AJAX) is sanitized and validated.

Nonces are employed for critical actions (newsletter signup, tracking, assessment result generation) to prevent misuse.

The plugin also adds:
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
at the top of every file to prevent direct access.

Performance

The plugin aims to be efficient:

The [em_related] shortcode uses cached queries when possible and avoids expensive computations on each page load.

Heavy content (videos) is offloaded to YouTube.

Assets are loaded only when needed.

Further optimization is possible (see docs/optimization-plan.md for planned improvements like caching frequently used query results).

When developing new features, consider the existing structure and extend accordingly, for example:

Add new shortcodes to the Shortcodes class.

Add new CPTs via the CPT class.

This keeps the architecture consistent.

Versioning & Releases

The plugin and theme are versioned in their file headers (currently 0.5.0).

Update version numbers in both:

The theme’s style.css, and

The plugin main file
when making a release.

Document changes in docs/CHANGELOG.md.

For additional developer guidance (e.g., how to run GitHub Actions or follow the coding workflow), see:

docs/development.md

docs/AGENTIC.md (the latter is relevant if using AI assistance – GitHub).

For more technical details on how the plugin interacts with the theme and WordPress, refer to:

docs/architecture.md

which walks through the system’s design and hooks in depth.
