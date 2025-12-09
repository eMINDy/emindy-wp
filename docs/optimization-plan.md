eMINDy WordPress Site – Diagnostic and Optimization Plan

This document summarizes immediate debugging steps and phased improvements for the eMINDy child theme (emindy) and core plugin (emindy-core). It serves as a roadmap to enhance the site’s stability, performance, and maintainability. Developers should follow these steps in order, addressing critical issues first, then quick wins, followed by longer-term enhancements.

1) Diagnose and Resolve the Critical Error (Immediate)

Enable logging: Turn on WP_DEBUG and WP_DEBUG_LOG in wp-config.php. Reproduce the site error and then inspect wp-content/debug.log for any fatal error messages – identify the file and line causing the failure.

Isolate the source: Temporarily deactivate the emindy-core plugin and/or switch to a default theme (like Twenty Twenty-Five parent) to see if the error originates from our plugin, theme, or a third-party conflict. This will pinpoint whether the crash is in our code.

Verify database tables: Ensure custom database tables (for newsletter and analytics) are present and properly created on activation. Specifically, check that the wp_emindy_newsletter and wp_emindy_analytics tables exist. If not, manually run emindy_newsletter_install_table() and EMINDY\Core\Analytics::install_table() (the functions our activation hook calls) to create them
GitHub
GitHub
. Missing tables could cause login or submission errors.

Check hooks and namespaces: Confirm that all WordPress hooks in our code match current WordPress conventions. For example, ensure the actions like init and template_redirect are correctly used. Also verify class autoloading or require_once includes aren’t missing (every class file should be required in emindy-core.php). A namespace issue (e.g., calling a class without the EMINDY\Core prefix) could trigger errors.

Memory or config issues: If the logs indicate memory exhaustion or timeouts, consider raising WP_MEMORY_LIMIT (e.g., to 256M) in wp-config.php temporarily and see if issue resolves. Low PHP memory could cause random crashes under load.

Regression tests: Once the immediate error is fixed, perform a thorough test of key site features:

Verify custom post type pages load (video, exercise, article single pages and archives).

Submit an assessment (PHQ-9, GAD-7) and ensure results display and email/share features work.

Try the search and filters on content libraries.

Test newsletter signup end-to-end (with WP_DEBUG to catch any hidden notices).

Test analytics logging if possible (like trigger a video play event and see if a row is added).

Also test with Rank Math enabled vs disabled (our schema logic differs) to ensure neither scenario triggers new errors.

Running through these will confirm that the critical fix didn’t introduce new issues and that the site is stable.

2) Quick Wins and Code Hygiene (Phase 2)

Remove duplication: There are instances of duplicate shortcode definitions in class-emindy-shortcodes.php (e.g., [em_related] appears twice). Clean these up – ensure each shortcode is defined only once
GitHub
. Also, check deprecated shortcodes ([em_transcript], [em_video_filters], [em_video_player]) – mark them clearly with @deprecated in docblocks and perhaps add a _doing_it_wrong notice so devs know not to use them
GitHub
. If they’re not needed at all (and not used in content), consider removing them to reduce code bloat.

Input sanitization: Audit all places where user input is processed:

Newsletter form handler: use sanitize_email() and sanitize_text_field() for name
GitHub
, and ensure we esc_sql or prepare DB queries (we do via $wpdb->prepare in insert).

Assessment AJAX handlers: confirm we absint the score and sanitize_key the type (we do
GitHub
) and verify nonce properly.

Shortcodes with user input (e.g., search shortcodes or [em_related] attributes): ensure we call sanitize_text_field or similar on attribute values.

Any use of $_SERVER (like in Analytics logging, we take REMOTE_ADDR and HTTP_USER_AGENT – we sanitize and truncate them
GitHub
, which is good).
By tightening sanitization, we prevent XSS or DB injection issues.

Nonce coverage: Review forms and AJAX actions for nonce checks. Ensure every frontend form or state-changing action has check_admin_referer or wp_verify_nonce:

Newsletter subscribe: It sets a nonce field _wpnonce and verifies it
GitHub
 – good.

Assessment sign_result and send_assessment AJAX: both call check_ajax_referer('emindy_assess')
GitHub
GitHub
 – good.

If any form lacks nonce (perhaps search forms might not need since they're GET requests), that’s fine. But any POST (like contact forms if existed) must have one.

Add nonces to any remaining AJAX endpoints missing them (none known, but double-check).

Hardening: Go through theme and plugin PHP files and add if ( ! defined( 'ABSPATH' ) ) { exit; } at top of any file that shouldn’t be accessed directly (most already have it, ensure all do
GitHub
GitHub
). This prevents direct access by malicious actors.

Newsletter and privacy: Update the newsletter form to include a concise consent message and maybe a link to privacy policy. We already added a consent checkbox
GitHub
; ensure this is required (HTML required is in place) and that we actually record that consent (we do as tinyint in DB). Consider implementing double opt-in (sending a confirmation email with a link to activate subscription) to comply with strict regulations – not urgent if current audience is limited, but worth planning.

Also ensure the newsletter table creation only runs on plugin activation, not on every page load (we moved it to register_activation_hook already
GitHub
, so we’re fine).

Archive consistency: The site has overlapping patterns for listing content (video-hub, exercise-hub, etc.). Standardize the layout and query logic:

E.g., ensure all archive pages show a consistent number of items (if Videos show 9 per page, let Exercises do the same) and that pagination works if more.

Confirm that the Quick Filters (topic dropdown, etc.) function similarly across libraries. If one uses a shortcode and another uses a custom query in template, unify approach for maintainability.

It might make sense to create one unified “library-hub” pattern or template part for the query loop to reuse. Deduplicate the patterns in patterns/ to avoid having to edit four files for one style change.

Translations: Confirm all user-facing strings are wrapped in the correct text domain:

Do a search for any __( or _e( with missing text-domain or an incorrect one (e.g., some might mistakenly use 'emindy' in plugin or vice versa). Fix them to 'emindy-core' for plugin, 'emindy' for theme.

Run wp i18n make-pot to regenerate POT files after string changes, so translators have updated references.

Also, audit that no hard-coded bilingual text remains in templates or patterns. E.g., the Persian text we saw in a code comment
GitHub
 is fine as comment, but in content, all Persian should come via translations, not hard-coded, to allow language switching.

Prepare to generate a Farsi translation file for the plugin once code stabilizes, since Phase 5 mentions focusing on i18n.

Admin notices: Leverage the [em_admin_notice_missing_pages] shortcode (which we have in code
GitHub
) to inform site admins if certain critical pages are missing. We can include this shortcode’s output on the WordPress dashboard via an admin dashboard widget or an admin notice upon activation:

For example, on plugin activation, check if pages like "Assessments", "Assessment Result", "Newsletter", "Blog", "Library" exist. If not, display an admin notice recommending creation.

This proactive step ensures site admins configure the needed pages and reduces end-user 404s. We have the logic in shortcode (checking get_page_by_path for 'blog' and 'library'
GitHub
); we could call it in an admin context as well.

By executing these quick improvements, we clean up the codebase, close easy security gaps, and enhance the editor/admin experience without altering functionality for end-users.

3) Performance and SEO Enhancements (Phase 4)

Query and caching audit: Use the Query Monitor plugin or similar in a staging environment to identify heavy database queries or slow PHP processes:

Look at page loads like homepage, archives with filters, and quiz result processing.

Reduce redundant WP_Query calls: e.g., if [em_related] runs a complex taxonomy query on every single post view, consider caching its results in a transient (with post ID + language as key) for, say, 1 hour. Related content doesn't change often, and caching would save resources when multiple users view the same popular post.

If certain queries (like Polylang’s, or Rank Math’s meta queries) are expensive, consider adding indexes to our custom tables or adjust plugin settings (like turning off some Rank Math modules not in use).

Specifically check the taxonomy queries on archive filters – ensure no_found_rows is true (as we set in related and some shortcodes
GitHub
) to avoid SQL calc rows overhead when pagination not needed.

Consider implementing object caching for repeated function calls like pll_get_post_language in loops (Polylang can add overhead; maybe cache current post language in a static variable).

Asset strategy: Our site is relatively light, but we can optimize front-end:

Defer or async non-critical scripts: e.g., the dark-mode toggle script can probably be loaded with defer since it’s not render-blocking (we enqueue it in footer already
GitHub
). If any third-party scripts (maybe YouTube embed adds some), see if we can add loading="lazy" to iframes or use the nocookie domain (we do use nocookie).

Minify/Merge CSS/JS: The theme’s CSS is not huge (~some KB), and plugin’s CSS similarly. But we could consider concatenating them or letting a plugin handle minification. In this phase, perhaps integrate a build step or use a WP plugin for minification. Ensure any such optimization doesn't break the site (test dark mode still toggles correctly after minification).

Compress assets: If we have any large images (check theme images, like any in patterns or headers), compress them (either manually or via a plugin).

Lazy-load images and iframes: WordPress 5.5+ by default adds loading="lazy" to images. Make sure our images in patterns or content have that attribute (they should if output via wp_get_attachment_image). For the YouTube iframes, consider adding loading="lazy" so offscreen videos don’t load until scrolled into view.

Preconnect third-party domains: We load YouTube embeds, likely fonts (if any Google Fonts via theme.json). Add a link rel="preconnect" for https://www.youtube-nocookie.com and https://i.ytimg.com (YouTube’s image domain) in the head to speed video loading
GitHub
. Our theme’s emindy_resource_hints function already does this preconnect for YouTube domains
GitHub
 – verify it covers all needed (it does for i.ytimg, youtube-nocookie, youtube.com, s.ytimg).

SEO fundamentals: Beyond schema:

Check search engine visibility setting: ensure the site is allowed to be indexed (unless in dev). If this is a staging site, keep discouraged; but for live, it should be open.

Pretty permalinks: Already configured via custom rewrite slugs – confirm they work on all content. If any odd slug issues (like collisions between page slugs and CPT rewrites), resolve by renaming pages or adjusting rewrite (e.g., we redirect /library to /articles if conflict
GitHub
).

Unique meta descriptions: We rely on Rank Math for SEO meta. Ensure each content type has appropriate template in Rank Math (like using excerpt for description, etc.). Update any that are blank.

H1 tags: Confirm each page has exactly one H1 (our templates use post title as H1 usually). No duplicate H1s (like site name in header is a <p>, which is good
GitHub
).

XML Sitemap: Rank Math will include our CPTs by default; check an example (like sitemap_index.xml) to ensure exercises, videos, articles appear and that language variants are included properly (Polylang+Rank Math should produce separate sitemaps or combined with hreflang).

HTTPS: ensure all site URLs are using https, especially YouTube embed should be https (it is).

Canonical URLs: With Polylang, each translation has its own URL, and Rank Math likely handles canonical and hreflang. Double-check that the output doesn’t show two canonical tags or something. If not using Rank Math, implement basic canonical link for CPTs to avoid duplicates (Polylang might add canonicals too).

Core Web Vitals: Aim for good FID, LCP, CLS:

Page/Object caching: If not already, implement a page cache. On a typical host, enabling something like WP Super Cache or the host’s caching will drastically improve time-to-first-byte. The site is mostly static content (except quiz result generation which is quick).

Layout stability (CLS):

Ensure images have width/height attributes or CSS aspect ratio so they don’t cause reflow when loading. In patterns, if an <img> tag doesn’t have dimensions, add them or use wp_get_attachment_image which adds them.

Check that our CSS doesn’t inject late-loading fonts or style changes that shift content. If using webfonts, maybe host them locally or preload to reduce flash.

The skip link becomes visible on focus (that’s fine, small CLS).

The dark mode toggle and language dropdown appear without causing jumps.

Third-party scripts: Only YouTube is significant. YouTube’s embed script might impact FID. Possibly consider using the YouTube iframe API asynchronously. For now, it’s acceptable given the type of site (we cannot remove it, obviously).

We can also set iframe width/height to cover at least a placeholder area to avoid layout jump when video loads.

Implementing these will make the site load faster and rank better without changing content. The goal is to achieve sub-2s first contentful paint on modern connections and a smooth user experience.

4) Design, UX, and Accessibility (Phases 3 & 4)

(Note: numbering suggests some items here might overlap in timeline with performance phase)

Pattern harmonization: Over time, multiple block patterns were created for similar layouts (videos, exercises, articles hubs). Deduplicate and unify them:

Possibly create a single “content-hub” pattern that uses a Query Loop with a post type filter, which can be reused via pattern for each section. This ensures consistent typography, spacing, buttons, etc.

Ensure the style (cards, headings) is consistent: e.g., all hub pages use the same card design (maybe a bordered box with subtle shadow for each item). Currently, minor differences might exist.

Remove older/unused patterns like those “modern” variants if they aren’t actually used. Or, if they present an alternative style, document when to use which. Clarity will help new contributors and content editors.

Responsiveness and semantics:

Do an audit of the site on various screen sizes: mobile (under 480px), tablet (~768px), desktop (1200px+). Fix any layout issues:

E.g., ensure the header menu collapses well (maybe convert to a hamburger on very small screens if needed; currently we rely on wrapping text, which might be fine for short menus).

Check that long Persian text doesn’t overflow or break layout (Persian tends to be longer; ensure containers can grow or break words appropriately).

Check that the assessment form is usable on mobile (radios should be big enough to tap, etc.).

Semantic HTML:

Confirm heading hierarchy on pages: The site title is not an H1 on inner pages (good, we rely on content title as H1). Ensure within content we use heading blocks appropriately (no skipping from H2 to H4, etc., which screen readers might announce oddly).

Add any needed ARIA attributes: e.g., the quiz result or score might need role="status" with aria-live="polite" so that screen readers announce results when they appear dynamically
GitHub
. We did add role="region" and aria-live="polite" to result container markup
GitHub
, which is great.

Ensure all clickable elements have an accessible name: e.g., the dark mode toggle has aria-label updated properly on toggle (we set data attributes for light/dark label and update aria-pressed
GitHub
).

The language switcher uses pll_the_languages output which likely outputs an accessible dropdown or list.

The newsletter form labels are properly associated (we have explicit <label for="em-nl-email">Email</label> etc.
GitHub
, good).

Check color contrast especially in dark mode: The palette was chosen carefully, but run a contrast checker to verify all text vs background meet WCAG AA (the gold vs deep blue, white vs teal, etc.).

Skip links, focus states: Already implemented skip link at top
GitHub
. Ensure focus outlines are visible (our CSS outlines in gold glow
GitHub
 looks fine). Maintain these when adjusting styles. All interactive elements should have a visible focus (we saw they do thanks to that CSS).

CTAs and engagement:

Standardize call-to-action text and placement. E.g., on each content page, consider adding a CTA block at end:

After an exercise: “How did you feel? Learn more about this technique in [an article] or try another exercise.”

After an article: “Ready to practice? Try this 2-min exercise now” linking to a relevant exercise.

After video: “If you enjoyed this, subscribe to our YouTube or check related content below.”

These CTAs can increase user engagement and time on site, and help users navigate content in a purposeful way (not just via random related posts).

Ensure related content suggestions remain performant (see caching above) and relevant. If some related queries yield empty, consider a fallback (we have one: a search based on title
GitHub
).

Dark mode contrast: Make sure CTAs (often buttons) are still eye-catching in dark mode. E.g., our gold button on dark background still stands out but check if color tweaks needed.

404 and navigation:

Implement a helpful custom 404 page (if not done). Perhaps page-help-404.html exists as a pattern. Ensure when someone hits a wrong URL they see a friendly message and suggestions (like a search bar or link to library).

Navigation clarity: Possibly add a link to “Library” or “All Content” if new users might not know where to start. We have a “Start Here” button which goes to a welcome page – ensure that page is up-to-date and guides new users (maybe embed an introductory video or a recommended first exercise).

Include language switcher in nav and ensure it's clearly visible (it is, but on mobile it might drop below – test that).

Footer navigation: Consider adding quick links in the footer for accessibility (some users scroll to footer for Contact, Privacy, etc.). If our footer lacks such links, consider adding them. Also include the emergency resources link in footer as a constant presence for those in need.

By addressing these design and UX elements, we ensure the platform is not only technically sound but also welcoming, easy to use, and accessible to all users (including those with disabilities or those on mobile devices).

5) Security and Privacy Hardening (Ongoing)

(Security is an ongoing concern; implement these safeguards throughout phases as appropriate.)

Updates and footprint: Keep WordPress core, the parent theme, and all plugins updated to their latest versions. Remove any unused plugins or themes to minimize attack surface. Our repository code (child theme & plugin) should be periodically reviewed for compatibility with the latest WP (e.g., any deprecations in WP 6.x should be handled).

Also keep an eye on our dependencies (e.g., if we used any external library or API, keep those updated or check their security status).

Enforce strong passwords & 2FA: If there are user accounts (the site might have only admins, since no user login for general visitors), ensure admin accounts have strong passwords. If possible, enable two-factor authentication for the admin login (maybe via a plugin or the hosting provider).

Login throttling: Consider installing a plugin or custom code for limiting login attempts to deter brute force. Since eMINDy likely has very few logins (maybe just admin), this is low risk, but still good practice.

HTTPS/HSTS: Already the site uses HTTPS. Add an HSTS header via server or security plugin to enforce SSL.

Surface reduction:

Disable file editing in WP admin (define('DISALLOW_FILE_EDIT', true)) so if an admin account were compromised, they can't modify plugin/theme code from the editor.

Consider restricting XML-RPC if not needed (XML-RPC can be used for brute force; if the mobile app or others aren't using it, disable via filter or plugin).

Limit user roles: If other team members need access, give them only roles needed (e.g., author for content writers, not admin).

Remove any example or default content that isn't used (like default WP sample page or Hello World post, to appear more professional and give less info to bots scanning).

Data handling:

The newsletter collects personal data (email, name). Document internally what we do with that data, how a user can request deletion (comply with GDPR etc., if applicable).

Implement double opt-in (as noted before) if we start getting many subscribers or to comply with certain laws (some jurisdictions require it).

For analytics, consider anonymizing IP addresses (currently we log full IP and user agent in analytics table
GitHub
). Perhaps hash the IP or at least clarify retention policy (maybe we can safely truncate older analytics data every 6 months since it's not mission-critical).

Provide a privacy policy page on the site, linking how data from newsletter and assessments is used. E.g., clarify that assessment answers are not stored, only the optional share result if user chooses, and even that is just a score plus timestamp.

Implementing these will further secure user trust and protect the site from common vulnerabilities. Many are best practices that we might have partially done, but formalizing them is key (especially as site usage grows).

6) Internationalization and Multilingual Support (Phase 5)

(Some i18n tasks were done earlier but Phase 5 focuses on refining multilingual aspects.)

Strings and text domains: Ensure every user-facing string is wrapped in translation functions with the correct text domain (emindy for theme, emindy-core for plugin). This was addressed in Quick Wins but do a second pass. Use a tool like WP-CLI i18n make-pot to regenerate POT and see if any strings were skipped (it will warn if some text not in a function).

Check also JavaScript for any hard-coded text. E.g., the PHQ-9 form text currently appears to be output via PHP, which is translated. If any JS alert or console message (we likely have none that user sees), localize those too using wp_localize_script if needed.

Meta duplication: We must confirm that Polylang continues to copy all our custom meta when creating a new translation:

If we add any new meta keys in future (say we add em_feeling_after meta to store an exercise reflection), we need to update our pll_copy_post_metas filter to include it
GitHub
.

If any issues came up (e.g., maybe Polylang wasn’t copying em_primary_topic if we had such a thing), fix those by adding them to filter.

Test creating a translation of each post type after making improvements: When translating an Exercise, verify steps, times, etc., came over. Same for videos. If something doesn’t copy, adjust our filter accordingly.

Language UX:

Check the language switcher thoroughly: does it accurately reflect the current language (Polylang adds a class for current language in the menu; our shortcode prints that likely)? Ensure it's visible (we did in header).

Possibly add hreflang tags for SEO. Rank Math might handle it, but double-check. If not using Rank Math’s automatic hreflang, consider adding a <link rel="alternate" hreflang="en" href="..."> and one for Persian on each page to help search engines.

Possibly display language switcher in a more prominent way on mobile (if dropdown is hidden behind menu, maybe duplicate a language toggle in footer).

For screen reader users, ensure the language switcher indicates it’s a language selector (like an ARIA label or proper <label> if needed for dropdown).

Polylang & dynamic strings: Some dynamic strings like the severity band names (“Minimal”, “Mild”, etc.) in quiz results were output via __()
GitHub
. We should verify the Persian translations for those terms are correct domain and context (they might need context like “severity level of mood” vs generic).

Collaborate with a translator to ensure all content is translated naturally, not just literally. Possibly make a pass over Persian text on site to catch any awkward phrasing (outside scope of code, but a QA step).

Ensure that adding a new language in future is straightforward: Document somewhere (possibly in docs/LOCALIZATION.md if needed) how to generate a new .po file, which files to translate, etc. Not code, but helpful if, say, one day they add Arabic or Spanish.

7) Long-Term Refactors and Testing (Phases 4–6)

Separation of concerns: Over the long term, break the monolithic shortcodes class into more focused classes or components:

E.g., create class-emindy-shortcodes-assessment.php for quiz-related shortcodes and AJAX (PHQ-9, GAD-7, result, sign/email), class-emindy-shortcodes-content.php for related, search, transcript, etc., class-emindy-shortcodes-newsletter.php perhaps to isolate that form shortcode.

Adopt PSR-4 autoloading with Composer to avoid manual require_onces. This means reorganizing includes/ into subfolders and adding an autoloader in plugin (could be done in a future major version).

Standardize namespacing fully (we already have EMINDY\Core for plugin classes; maybe use EMINDY\Shortcodes sub-namespace if splitting).

Also consider moving some shared helper functions (like sanitize_json) into a Utils class or similar for clarity.

Extensibility: Add hooks (actions/filters) in strategic places so that future customizations or extensions can be done without modifying core code:

For example, after emindy_newsletter_handle subscribes someone, we already do do_action('emindy_newsletter_subscribed', $data)
GitHub
 – good extensibility.

We could add a filter for [em_related] results allowing a theme to modify query args or the output HTML if needed.

Provide filters for customizing email content (like filter the welcome email HTML before sending
GitHub
).

Essentially, identify points where someone might want to tweak behavior and insert a filter. Document them in code comments.

Automation: Introduce a testing pipeline and continuous integration:

Add PHPUnit tests for critical logic – e.g., test that emindy_core_polylang_copy_metas returns the expected array, test that Shortcodes::phq9() outputs a form with 9 questions, test that emindy_newsletter_handle properly inserts or updates DB (maybe using WP’s usingTransaction in test to rollback).

Integration tests: use WP CLI or Cypress to simulate front-end flows, especially for things that involve JS (like quiz submission).

Set up GitHub Actions to run these tests on each PR. This catches regressions early. For instance, if someone inadvertently breaks the quiz calculation, a test should catch that.

Also incorporate code sniffers (PHPCS) in CI to enforce coding standards automatically (fail the build if style issues).

Expand GitHub Actions build to also maybe deploy to a staging site if applicable (less likely needed, but an idea).

Documentation: Expand ARCHITECTURE.md (the file you're reading) and onboarding docs with any new local setup or build steps (like if we add Composer, note how to run composer install).

Maintain a TODO_PHASE1.md or similar file to track incremental steps from this plan and mark off completed items (the architecture doc suggests this; maybe create docs/TODO_PHASE1.md as a checklist and update as we go, for internal use).

By phase 7, eMINDy’s code should be much more modular, easier to maintain, and well-tested. This not only reduces chances of bugs in production but also makes it simpler to add new features (like new content types or tools) because the foundation is solid.
