Configuration Guide

After installing the eMINDy theme and plugin, a few configuration steps in the WordPress admin will ensure all features are enabled and working correctly. This guide covers how to configure various aspects of the eMINDy platform, including creating essential pages, adjusting settings for multilingual support, enabling newsletter functionality, toggling layout options like RTL, and understanding where to manage different features.

1. Essential Pages Setup

Certain WordPress pages act as placeholders for dynamic content in eMINDy. These pages should be created (if not already present) and configured with the correct slugs so that the theme’s templates and plugin features hook into them automatically:

Assessments Page: Create a page titled “Assessments” with slug assessments. This page serves as the hub for self-assessment tools (PHQ-9, GAD-7). You don’t need to add any content manually unless desired. The eMINDy theme will detect this page and apply the page-assessments.html template, which includes an introductory message and placeholders (you can edit the intro text via the block editor if the template allows). In practice, you might add in the page content a brief description or links if needed, but the main content (the quizzes) are either included via shortcode or links leading to them.

Multilingual: If using Polylang, create translations for the Assessments page in each language (e.g., a Persian page with slug fa/assessments or equivalent translation). Ensure the pages are linked as translations of each other in Polylang so that language switching works on this page.

Assessment Result Page: Create a page titled “Assessment Result” with slug assessment-result. This page is where users see their quiz results. It should remain mostly blank in the editor. The theme uses page-assessment-result.html here, which automatically places the [em_assessment_result] shortcode. Do not remove that shortcode if you see it in the template code – it’s essential. Essentially, when a user submits a PHQ-9 or GAD-7, they get redirected to this page with query parameters, and this shortcode renders the result (score and message).

Configuration: No content needed from admin side. Just ensure the slug is exactly assessment-result (all lower-case, no extra words) so the template applies. For Polylang, you might translate the page title but keep the slug in English or Persian as you prefer – just remember the plugin’s redirect uses the slug from assessment_result_base_url() (which defaults to the English slug). It might be simplest to keep the slug as “assessment-result” in all languages (Polylang allows you to have a single slug for all translations if you choose).

Newsletter Sign-up Page: Create a page “Newsletter” with slug newsletter. This page will host the newsletter sign-up form. The theme’s page-newsletter.html template provides a design for this page, including a hero section (“Join the Calm Circle 🌿”) and benefits of subscribing, followed by the [em_newsletter_form] embedded via a Shortcode block
GitHub
.

Admin configuration: You can edit the textual sections of this page via the block editor if you want to customize the headline or description (since those are static blocks in the template). However, do not remove the Shortcode block that contains [em_newsletter_form] – that is what generates the subscription form. If you need to move it, ensure it stays somewhere in the page content.

Polylang users should create a translated version of the Newsletter page as well, so that the form and texts can appear in the other language. The shortcode will automatically output labels (“Email”, “Name”, consent text) in the appropriate language provided you supply translations for those strings in the plugin’s .po file. (By default, they might appear in English until translated, so consider translating the emindy-core text domain strings for a fully localized form.)

Library Pages (Optional): eMINDy can present content libraries in two ways: via the CPT archive URLs (e.g. /video-library/ which is automatically created by WP’s rewrite rules), and/or via static pages that load custom patterns.

The theme includes page-articles.html, page-videos.html, and page-exercises.html templates. These likely are simple pages that include a pattern or Query Loop to list content or provide an intro to each section. To use them, create pages titled “Articles”, “Videos”, and “Exercises” with corresponding slugs (articles, videos, exercises). Having these might be beneficial for Polylang or navigation (since archive pages like /article-library/ won’t have a translation or a menu entry by default).

If you create those pages, the theme should automatically use the respective templates which might include links or patterns. For example, “Videos” page could use the video-hub pattern: a hero + search + filter UI for videos.

Additionally, a “Library” or “All Libraries” page (slug library or libraries) can use the page-archive-library.html template to show an aggregate of all content or at least links to each library. If your site strategy includes such a page, create it accordingly. If not, ensure that the absence of it is handled (the plugin will redirect a /library URL to /articles if not present).

These pages are optional because the CPT archives themselves are accessible. However, having them can allow more curated content (like adding an introductory text on the Videos page). It’s up to your preference. If you do use them, also provide translations via Polylang for consistency.

Start Here / About Page: Not strictly required by the system, but highly recommended from a user experience perspective. The header’s “Start Here” button links to /start-here/ by default
GitHub
. Create a Start Here page to welcome new visitors. On this page, you might explain what eMINDy is, how to navigate the site, or maybe present a “first week program” or highlight content for newcomers. This page content is entirely up to you – there’s no special template enforced, so you can design it with the block editor freely.

If you don’t want a Start Here page, you should edit the header template (or navigation menu) to remove or change that button so users don’t hit a 404. For instance, you might retarget it to the Assessments page or an external resource. To edit it, go to Appearance → Editor, locate the header template part, and modify the “Start Here” button block (changing its URL or removing it).

Emergency Resources Page: The PHQ-9 and GAD-7 results suggest “please visit the Emergency page” if someone feels unsafe
GitHub
. We recommend creating a page with slug “emergency” or similar, where you list emergency contact information (e.g., hotlines, what to do in crisis, etc.) relevant to your audience. There is no predefined template for this, but linking something is important for user safety. After creating such a page, you should edit the language in the plugin or via translation to point exactly to that page if needed. (Currently, the string literally says “Emergency page” expecting you have a page by that name).

If you do create an Emergency page, add it to your menu or footer for visibility. If not, consider editing that phrase in the language file to direct users to a known resource (like “please seek professional help or contact [local hotline]” etc.). In configuration terms, just having such a page available is good practice.

In summary, once you have all these pages created and properly slugged, eMINDy’s dynamic pieces will fall into place around them. The plugin’s admin notice shortcode [em_admin_notice_missing_pages] can be used as a double-check (for example, you could temporarily put that shortcode in an admin dashboard widget or a hidden admin page to see if it reports any missing page slugs).

2. WordPress Settings Adjustments

A few WordPress settings should be reviewed for optimal eMINDy setup:

Reading Settings: If you created a static front page (like “Home” or using the Site Editor’s Front Page template), ensure Settings → Reading is set appropriately. You might let your Front Page be controlled by the theme’s front-page.html template (in which case you do not need to set a static homepage in settings; leave it to default “Your latest posts” so the block template takes effect). For the Posts page, if you are not using blog posts, you can leave it unset. If you did create a “Blog” page for posts, assign it here. This mostly affects built-in blog posts listing which eMINDy might not heavily use, since Articles CPT covers that need.

Discussion Settings: Decide if you want to allow comments on Articles or other CPTs. By default, the custom post types did not add support for comments (in code, we didn’t see 'supports' => ['comments']). So likely, comments are off for Videos/Exercises/Articles. It’s recommended to keep it that way for now (since moderation of mental health content comments can be sensitive). Thus, you can ignore or disable comments entirely via WordPress settings if desired.

Permalinks: As mentioned earlier, use a pretty permalink structure. If you change it, flush rewrites. The recommended is “Post name” which yields clean URLs. Ensure no conflicts: eMINDy CPT slugs (/video/…, /article/…, /exercise/…) are unique and should not conflict with any existing pages or other post types. If you have existing pages or posts with those same base slugs, you might need to rename one or the other. (For example, if you had a page “Video” using /video, that conflicts with the CPT slug /video. The CPT will take precedence typically. Best to avoid such conflicts by using different names.)

Privacy Policy: If your site requires a Privacy Policy page (often does), set it in Settings → Privacy. This link usually appears in the site footer by default. You should update the privacy policy text to include mention of the data eMINDy collects:

It stores newsletter subscribers’ emails and names, and that data is used only for sending the newsletter.

It logs anonymous usage analytics (if you keep that feature active) like event counts, but no personal identifiable info beyond possibly IP (which could be considered personal data in some jurisdictions).

It sets a cookie or uses localStorage for dark mode preference and possibly Polylang for language. Also mention cookies used by the YouTube embed (YouTube-nocookie still sets some, like player prefs).

Assure users that no quiz results are permanently stored – they are calculated on the fly.
This keeps your privacy policy accurate. (This isn’t a configuration step in code, but it’s an important admin task.)

Permalink Base for CPT (Advanced): The CPT slugs (video, article, exercise) and archive slugs (video-library, etc.) can be changed if absolutely needed, but that requires code change. Out-of-box, just be aware of what they are (you can see them in class-emindy-cpt.php or by visiting Settings → Permalinks after activation where sometimes custom post types show their structures).

3. Multilingual Configuration (Polylang)

If you have Polylang active:

After adding your languages, go to Languages → Settings:

Enable detection of browser language if you want (optional).

Ensure custom post types (em_video, em_exercise, em_article) are set to be translatable. Polylang usually has a section listing post types and taxonomies – check the boxes for our CPTs and taxonomies if they aren’t already. This allows you to input translated content for each.

The eMINDy plugin automatically copies some metadata for translations (steps, chapters, etc.) when you create a new translation of a post
GitHub
. Make sure this is working: when you click “+” to create a Persian version of an Exercise, after saving it should have the steps JSON copied over. (The title and content you’d translate manually.)

Menu translations: Polylang will want a menu per language. After setting up the English menu, switch the admin language to Persian (or just use the Polylang menu sync feature) to create a Persian menu. Include the corresponding pages (the ones with Persian titles). Because we used the same slug for some (like assessment-result) you might see them reused – that’s fine.

Language switcher in header: The theme places [em_lang_switcher dropdown="1" show_names="1" show_flags="0"] in the header
GitHub
. This means by default it will show a dropdown with language names (e.g., “English” and “فارسی”). You can configure this:

The shortcode accepts dropdown="0" for inline list instead, show_flags="1" if you want country flags (make sure to upload flag images or enable Polylang’s flag feature).

If you want to change this configuration, you have two options:

Edit the header template part: In the Site Editor, edit the Shortcode block for the language switcher. Change its attributes as needed or replace it with Polylang’s own block.

Polylang settings: Alternatively, Polylang offers a menu item or widget for language switcher. But since we already have the shortcode, editing it is simplest.

Translating Strings: The eMINDy plugin and theme come with .pot files. To translate the interface (like the text in the assessment questions, button labels, etc.), you’ll need to create language .po/.mo files. You can use a plugin like Loco Translate for convenience:

Go to Loco Translate (or Polylang’s Strings translation) and find the text domain emindy-core and emindy. Translate all user-facing strings to Persian (or your language). Key ones include:

Assessment questions and answer options for PHQ-9 and GAD-7.

“Start”, “Next”, “Reset” in the exercise player.

“Subscribe” button text, “I agree to receive updates…” consent text.

All those appear in the .pot and need manual translation as they are not content.

The theme text domain emindy covers things like “Search eMINDy” placeholder text, “Skip to content”, etc. Translate those as well.

After translating, test the site in the other language to see that UI elements are translated. It greatly enhances user experience for non-English users.

4. Newsletter Configuration

By default, the newsletter system is fully functional without additional setup, but you might want to configure a few things:

Admin Email: The plugin uses WordPress’ admin email (from Settings → General, “Administration Email Address”) to send notifications of new sign-ups
GitHub
. Ensure this email is correct and one that should receive these alerts (e.g., the site owner or a team alias).

Email Sending Settings: As mentioned earlier, use an SMTP plugin to configure outgoing email if your host’s PHP mail is unreliable. Provide SMTP server or API credentials so that wp_mail() (which eMINDy uses) will actually deliver. Test by signing up yourself.

Double Opt-In / Confirmation: eMINDy’s built-in form does not do double opt-in (it immediately adds and emails the welcome). If your organization requires double opt-in for compliance, you might need to extend the functionality:

One approach: integrate with a service like MailChimp via the emindy_newsletter_subscribed action hook to trigger their double opt-in process, or

Modify the code to not send welcome immediately but rather send a confirmation email that requires a click. That’s custom development beyond default config, so plan accordingly if needed.

For basic usage, current approach is acceptable for many (given explicit consent checkbox is ticked).

External Integration: If you prefer to use an external newsletter service entirely:

You can still use the [em_newsletter_form] for UI and hook into the action to push data out. For example, add a function on emindy_newsletter_subscribed that calls a Mailchimp API with the email. The subscriber will still be recorded locally (unless you disable that code).

Or you can bypass the built-in form and embed a third-party form on the Newsletter page if desired. In that case, you might want to remove or not use [em_newsletter_form].

These adjustments are beyond configuration (require coding), but just be aware the option exists.

Consent Text: The language “I agree to receive weekly email updates from eMINDy…” might need tweaking to match your actual sending frequency or content. Currently, it’s hardcoded in English
GitHub
 and translatable. If you want to change wording, the proper way is via translating that string or editing newsletter.php. For instance, if you plan monthly emails instead of weekly, update that text accordingly via PO file or code edit so you set correct expectations.

Unsubscribe Handling: The welcome email mentions that emails will have an unsubscribe link. If you use a third-party service, that service will handle unsubscribe. If you continue to send via WordPress, you’ll have to implement a way for users to unsubscribe:

Simpler: in each newsletter email you manually send, include a line like “To unsubscribe, reply to this email or contact us.” Then remove them manually.

More advanced: build an unsubscribe page and mechanism (not yet in the plugin by default). This isn’t configured out-of-box, so consider this if scaling.

For now, since eMINDy is internal, manual handling might be okay but keep this in mind for GDPR compliance.

5. Layout & Appearance Options

Dark Mode Toggle: The theme includes a dark mode toggle button in the header. By default, it works without config – it remembers the preference per user (via a little script that toggles a data-em-theme attribute and local storage). If you want to disable dark mode entirely (for example, if design changes make it undesirable):

You could remove the button from the header (edit the header template, remove the HTML block that contains the moon icon toggle).

Also, add some CSS to override any dark-specific styles to ensure consistent look.

However, it’s a nice feature to keep, especially for a wellness site (low light preference at night, etc.).

No further config needed if you keep it; just be aware as an admin that toggling it won’t change anything in admin area (it’s front-end only).

Homepage Configuration: The front page is managed via the block template and patterns. If you want to change what’s shown on the homepage:

Enter the Site Editor and open the Front Page template. You can rearrange or replace sections (e.g., maybe swap out which content is featured).

You might also decide to set a static page as homepage in Settings → Reading and design that page manually. If so, ensure the template assignment and content reflect what you want. The provided front-page template is a great starting point, but it’s flexible.

Customizing Colors/Styles: Many style preferences (colors, font sizes, spacing) are set in theme.json. As an admin, you can override some via the Global Styles interface (Appearance → Editor → Styles). For example, to change the background or text color globally, you could edit the theme’s palette. If you’re not comfortable with JSON, use the UI:

Go to Styles, and under Colors, adjust if needed. Because the child theme provides a palette, you can pick from those or add custom ones. The same for typography.

Save changes – they will be stored separately from code (in the database custom_styles post type).

If you mess up, you can always clear customizations or revert to theme defaults.

Logo and Icons: If the site has a logo, you can upload it via the Site Editor to the header (the header pattern currently just uses Site Title text by default, which is often fine for a clean look). For a more branded appearance, use a Site Logo block. Similarly, update social links in the footer if a pattern includes them (look at Appearance → Editor → Templates Part → footer to see if any placeholder or social icons are there to edit).

Footer Notices: If there’s any placeholder text in the footer (like © 2025 eMINDy), edit it in the Site Editor. Ensure compliance links (Privacy, maybe Terms) are in place.

6. Admin Panel and Editing Content

With initial setup done, here’s how to manage ongoing content in the WordPress admin:

Custom Post Types in Admin Menu: You’ll see Exercises, Videos, Articles in the WP dashboard menu. Use these like you would normal Posts:

Add New Video: Provide title, description, etc. For the video itself, you have options:

Use the WordPress Embed block: paste a YouTube URL (it will embed the video player).

Or use a Shortcode block [em_video_player] (though it’s deprecated, it still works if you provide a YouTube ID in a custom field).

Or simply upload a video file to media (not recommended for large videos, better to use YouTube).

If using YouTube embed, eMINDy’s schema will still pick up the video info if you put the YouTube ID in custom field em_youtube_id. Actually, how to set that? Possibly the plugin expected it from a custom field or the [em_video_player] might parse it. Alternatively, if you embed via oEmbed, WordPress has the video URL which we might not be capturing for schema. To be safe, after embedding, also add a custom field em_youtube_id = the video’s ID (the part after v= in URL). This will ensure the schema builder finds it and the [em_video_player] shortcode (if used) knows it.

Add any relevant taxonomies (topics, etc.) on the sidebar for the video. Publish.

For chapters: click Screen Options (top right in editor) and ensure custom fields are visible (if meta box isn’t visible). You should see eMINDy JSON Meta box as well. Paste a JSON array of chapters if you have it:

[
  { "time": "00:00", "title": "Introduction" },
  { "time": "01:30", "title": "Technique 1" }
]


Or whatever format is expected (the code just stored a JSON string, and the shortcode generates a list of links with those times). The format might be simply an array of objects with text or title and maybe time or offset. Check documentation or experiment with one. If format is wrong, the shortcode will just output nothing or an “invalid JSON” warning in admin. Use the JSON validator feedback in the meta box: after pasting, it will show “Valid JSON ✔” if correct, or “Invalid JSON” if not.

Add New Exercise: Title = name of exercise (e.g., “Deep Breathing”). In the editor, you might write a short introduction or the purpose of the exercise. But the actual steps of the exercise should go into the Steps JSON field in the meta box:

For example:

[
  { "text": "Breathe in slowly through your nose", "duration": 4 },
  { "text": "Hold your breath for a moment", "duration": 2 },
  { "text": "Exhale slowly through your mouth", "duration": 4 }
]


Each step has a description and optional duration (in seconds) that the player will use to time it. If you leave duration out, the player just won’t show a countdown for that step.

Fill the additional fields in the meta box:

Total time (if known, or the system can sum durations).

Prep time, perform time (if applicable).

Supplies, Tools, Yield – these are like HowTo schema fields (e.g., “Yoga mat” as a supply, or yield like “State of calm” as an outcome if relevant).
These fields are optional but useful for completeness and will appear in schema or could be shown in the template if designed so.

Choose relevant taxonomy terms (topics, etc.). E.g., Topic: Anxiety & Stress, Duration: 2-5m.

Publish. On the front-end, the exercise page will now show the title, any intro content you wrote, and then automatically the player interface followed by maybe a static Tips or related block if the template has one. The user can go through steps using the interface.

Add New Article: Title and body text as usual. You can use the block editor freely here (images, headings, etc.). Assign categories like Topic, etc. (Those taxonomies are shared so an Article can have a Topic too, making it show up in related content for a Video on the same topic).
Publish. The front-end will show the article content and below that possibly a newsletter sign-up prompt or related content (depending on theme template design).

Managing Categories/Taxonomies: The custom taxonomies like Topic and Technique can be managed under Exercises → Topics (or possibly in a generic “Topics” menu item depending on how WordPress grouped it). Add or rename terms as needed. The plugin seeded some default terms on activation:

It will not seed duplicates if they exist (it checks term_exists). So if you need to re-run seeding (for example, if you manually deleted all topics and want them back), you’d currently have to add them manually or write a small script to call the seeding function again. In normal use, adjust terms manually via the UI to match the content you have.

These taxonomies are hierarchical (for ones like Topic), meaning you can have parent-child, though the defaults might all be top-level. Use hierarchy if it helps (e.g., a parent topic “Stress” with child topics “Work Stress”, “Social Stress”).

The slug of terms might appear in URLs (especially if you enable taxonomy in permalinks), but generally taxonomy archives can be accessed like /topic/stress-relief/.

Menus & Navigation: After adding new content sections, ensure your navigation is updated. For example, if you want to highlight the Assessments, add it as a menu item. If you added a new content category or special page, include it where appropriate. The theme’s header and footer are editable via the block editor, so use that for structural nav changes.

Widgets: The theme likely doesn’t use classic widgets as it’s block-based, but if you have any legacy widget areas (for example, a footer widget added in code), manage them under Appearance → Widgets. By default, likely none aside from maybe a fallback.

Polylang content sync: When adding a new piece of content in one language, remember to add its translation in the other. Polylang adds handy flags or + icons in the content list. This ensures your site in both languages has equivalent content. If some content is language-specific (e.g., an article that only makes sense in one language), that’s fine, but know that a user switching languages on that page might get taken to the home page or an archive because the exact post isn’t available in the other language.

7. Advanced Configurations

Customizing Shortcode Output: Most shortcode output is styled via CSS classes (e.g., .em-player, .em-phq9 for the quiz form). If you want to tweak how these look (spacing, fonts, etc.), you can add custom CSS in the Customizer or Additional CSS. The classes are all prefixed with em- to avoid conflicts. For deeper changes (like altering the HTML structure or text within shortcodes), you’d have to modify the PHP in the plugin (which is beyond configuration – see development guide).

Admin Notices and Debugging: If something isn’t configured right, eMINDy might not explicitly warn in the UI (besides the missing pages notice if you insert that somewhere). Use debug.log for silent issues. For example, if the newsletter welcome email fails to send, it will log an error in debug.log (and the user just won’t get an email). Monitoring the logs is useful in initial setup.

Performance Settings: On high-traffic production sites, consider installing a caching plugin (like WP Super Cache or W3 Total Cache). eMINDy is mostly dynamic content but much of it can be cached safely:

Pages like videos, articles (static content with maybe dynamic related lists – those related lists can be cached for some minutes without trouble).

The exercise player and quizzes use JS so caching the page doesn’t break them (just ensure any nonce values are handled – eMINDy’s assessment nonce is embedded in a script; caching should still serve it fine since the nonce is long-lived during a session).

If using object cache, transients, etc., you could further optimize. The plugin currently doesn’t set transients for any queries by default (development might add in future).

These are outside eMINDy’s own config, but part of overall WP config for a smooth experience (see docs/optimization-plan.md for detailed recommendations).

By completing the above configuration steps, you should have a fully functional eMINDy platform tailored to your needs. The combination of correctly set pages, taxonomy terms, and integration plugins (Polylang/SEO/SMTP) ensures that both the content and the behind-the-scenes features (like multilingual support and emails) work harmoniously.

If you have any configuration-related questions or run into any setup issues, refer back to this guide, and check docs/architecture.md for a technical perspective or docs/user-guide.md to understand how it should look to end users (sometimes configuring becomes clearer when you think from the user’s viewpoint).
