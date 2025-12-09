eMINDy Architecture Overview

Last updated: 2025-12-08

The eMINDy project is a WordPress-based mental wellness platform.
This document describes the current structure of the custom child theme and core plugin as they exist in this repository. It is meant to guide future refactors and new features by explaining how different pieces fit together and adhere to WordPress paradigms.

1. Theme: emindy (wp-content/themes/emindy)

Type: Block-based child theme built on a core block theme (WordPress Twenty Twenty-Five).

Purpose: Provides all site presentation (templates, styles, patterns) optimized for eMINDy’s content (exercises, videos, articles) and design principles (calm aesthetic, accessibility, bilingual support).

1.1 Templates

Key templates under wp-content/themes/emindy/templates/ and their purposes:

single-em_exercise.html – Template for a single Exercise post. It shows breadcrumbs (if provided by parent or pattern), the exercise title and meta, and automatically includes the interactive practice player and steps list. Specifically, it leverages the plugin to inject [em_player] at the top and uses the [em_exercise_steps] shortcode to list out the exercise steps for screen-readers or printing
GitHub
. After the steps, it can include an editable “tips” or follow-up content section (like additional guidance or a prompt to record how you felt). It may also end with a newsletter signup prompt or related content suggestions.

single-em_video.html – Template for a single Video post. It renders the video content: likely shows the title, metadata (like duration or topics), the video embed itself (this could be placed in content by editors or possibly via [em_video_player] shortcode if used). After the main content, this template appends a chapters section (output by [em_video_chapters] shortcode) to list the timestamps of the video
GitHub
. It might also set up containers for a transcript or key takeaways; from the code we know [em_transcript] was a thing, but now probably just using the post content for transcripts if needed.

single-em_article.html – Template for a single Article post. This likely follows a simpler layout: show the article title, the content (text, images as authored), and at the end possibly a Newsletter sign-up CTA and a Related articles section
GitHub
. The newsletter CTA could be a pattern or shortcode inserted to encourage readers to subscribe if they enjoyed the article. Related content might be handled by [em_related] shortcode or a block query for other articles in the same topic.

archive-em_exercise.html – Archive template for Exercises. Likely used at URL /exercise-library/. It includes a header (maybe a page title like “Exercises Library”), potentially some descriptive text about what these exercises are. Then a Query Loop listing exercise posts (with perhaps filters). The archive might incorporate a topic filter and a search bar – for example, a dropdown of topics to narrow exercises. The architecture notes mention quick filters, which might be implemented via shortcode or theme PHP adjusting the query. We know from theme functions that it adjusts the pre_get_posts for em_video archive for topic filtering
GitHub
GitHub
, likely similar concept can apply for exercises. The exercise archive template probably includes pattern blocks for consistent layout of the list (e.g., cards with exercise title, short description, duration). There might also be a CTA at the bottom (like inviting to subscribe or check videos), indicated by a “CTA footer” in design
GitHub
.

archive-em_video.html – Archive template for Videos, at /video-library/. It likely mirrors the structure of exercise archive: a hero or title section (e.g., “Video Library”), followed by a grid or list of video posts (thumbnails, titles, lengths). The code suggests it also has a filter and search UI similar to exercise archive
GitHub
. Possibly it uses the [em_video_filters] shortcode (though deprecated) or has been replaced by blocks. In any case, the template ensures users can filter videos by taxonomy or search term. A CTA or note at the bottom might be present as well.

archive-em_article.html – Archive template for Articles, at /article-library/. This would list all article posts (or could be the blog equivalent). It likely includes pagination if many articles, and might highlight categories or topics for browsing. Given articles might be numerous like a blog, it perhaps uses a simpler list with excerpts. The note in architecture says a CTA banner is included
GitHub
, possibly inviting to try an exercise or subscribe for updates after reading through articles.

page-archive-library.html – A static page template intended to show a unified “Library” view of all content. According to the code, it loads the archive-library pattern
GitHub
. The archive-library pattern (see below) might combine multiple sections: e.g., “Latest Videos”, “Latest Exercises”, “Latest Articles” with links to each archive. The idea is an entry point where a user can see an overview and jump into any category. This page would be used if an admin sets up a page with slug “library” and assigns this template (the template is auto-selected by slug matching).

page-assessment-result.html – Template for the dedicated Assessment Result page (/assessment-result/). This template likely provides a clean layout to display the result of a quiz. From architecture
GitHub
, we know it renders a “reassuring hero” (perhaps a message like “Your Results” with a calming graphic or background) plus the [em_assessment_result] shortcode. The shortcode outputs the actual score and severity text inside a nicely styled container. This page template ensures that even if the content editor leaves the page blank, the user sees a structured result. It’s critical that this page not be repurposed for anything else by admins, as the logic depends on it.

page-assessments.html – Template for the main Assessments hub page (/assessments/). This page serves as an introduction to the self-tests available. The template might include a short welcome (“Take a moment to check in with yourself…”) and then either automatically lists the available assessments or expects the editor to put links. The note says it combines an intro with editable content for links
GitHub
, implying the template might have a predefined heading and some placeholder text, but the content area is meant for the admin to perhaps list the assessments like:

“PHQ-9 – Mood self-assessment (link to a page or maybe opens directly in-page via script).

GAD-7 – Anxiety self-assessment.”

Possibly the actual quizzes aren’t embedded on this page (since they have their shortcodes to show the questions, which might be too much to have both on one page). Instead, this page might link to each quiz’s start or open them modally. But given no separate pages for each quiz were defined, likely the quizzes themselves are included right on this page. Another approach is they could hide/show via tabs or accordions on this page. The architecture isn’t explicit, but since [em_phq9] and [em_gad7] are shortcodes, an admin could place them both on the Assessments page content, one after the other, allowing the user to scroll through or just pick which to fill. This might be left to content design. The template just ensures a consistent layout and message.

Additional default templates: The theme also includes generic templates:

index.html – fallback for any context not covered (maybe if someone visits an archive of a taxonomy). It might just be a basic “nothing found” or list posts.

search.html – template for search results, which likely shows results with excerpts and highlights (the theme’s functions highlight query terms in excerpts via filter
GitHub
).

404.html – custom 404 page. Possibly a user-friendly message like “You look lost in thought… This page isn’t here. Try searching or go to the library.” The theme might include a search form or link to helpful sections here.

home.html – the blog home if not using a static front page (in WP terms, home.html often serves as blog posts index if a static front page is set; in this case, maybe not heavily used if we use article archives for blog).

Some specialized page templates: page-no-title.html (maybe for landing pages where you don’t want to show the page title block), page-help-404.html (perhaps a predesigned page that actually is used as 404 content or guidance), page-newsletter.html (as described above), page-articles.html, page-exercises.html, page-videos.html, which likely load respective patterns.

front-page.html – the template for the site’s front page. This uses either a pattern like front-page-emindy.php (which is mentioned in patterns below) to display a curated homepage with hero, featured sections for each content type, etc.

In summary, the child theme defines templates specifically for each custom content type’s single and archive, ensuring that those contents are presented in a tailored way rather than default blog styling. It also sets up necessary static pages (assessments, results, newsletter, library) via templates for dynamic integration with plugin features.

1.2 Patterns and Hub Layouts

The theme includes a set of block Patterns in wp-content/themes/emindy/patterns/ that provide quickly insertable layouts, especially for “hub” pages and repeatable sections. Key patterns and their usage:

video-hub.php – Pattern for the Video Library hub. This pattern likely contains a pre-designed layout for a video listing page: perhaps a full-width hero banner at top (maybe with a representative image or icon for videos, plus title “Video Library”), a search input and a dropdown for filtering by topic (if not handled by a shortcode), and then a grid of video thumbnail cards. The pattern might use a Query Loop block (set to query em_video CPT) or placeholder that dynamic query would fill. Essentially it gives the page a nice structure and styling without needing manual design each time
GitHub
.

exercise-hub.php – Similar concept for Exercise Library. It would have appropriate text (“Explore Practices” or “Exercise Library”), filters (maybe by technique or duration), and a grid/list of exercise cards showing title and short excerpt or estimated time. Because exercises have unique meta like duration and level, the pattern might incorporate those (e.g., showing a little “5 min” label on each card if available). This pattern is used for the exercise archive or dedicated page
GitHub
.

article-hub.php – Pattern for Article Library. Possibly features a hero section with a featured article or just a title like “Articles & Insights”. Could include a search bar to search articles, category filter (topic filter might apply here too since articles also have topic taxonomy). Then a list of latest articles, maybe broken into categories. Because articles might be numerous, the pattern might be simpler (like just a two-column list with dates). It’s intended for a page listing articles (if using a static page approach instead of archive page, or to provide an intro on archive page)
GitHub
.

libraries-hub.php – A special pattern that acts as an overview of all content libraries. The architecture notes this pattern is loaded by the page-archive-library template
GitHub
. It likely contains sections for each content type:

e.g., three columns or rows: “Videos” with an icon and brief description + a link button to Video library, “Exercises” with icon/description + link, “Articles” similarly.

Possibly also a global search that searches across all (though cross-post-type search is just the normal WP search, which the header search already covers).

It might highlight counts (like “X videos, Y exercises available” if such dynamic data inserted – but likely static text unless coded).

The goal is to provide a one-page reference to all content for users who want to browse everything from one place.

archive-library.php – Possibly a pattern specifically combining content from multiple post types. If so, it might actually query some of each:

For example, show the 3 latest videos, 3 latest exercises, and 3 latest articles in separate sections. This would indeed be a unified archive view, which could be what the page-archive-library.html uses rather than just static links.

If that’s the case, this pattern would contain multiple Query Loop blocks with queries like “post type = em_video, 3 posts” then another “post type = em_exercise” and so on.

The architecture text says unified archive pattern loaded by that page
GitHub
, implying a real listing of content entries from all types.

front-page-emindy.php – Pattern for the Front Page layout. This is used by the front-page template to lay out the homepage. It likely includes:

A hero section (maybe a welcome message and a call-to-action button “Start Here”).

A features/benefits section (since they mention benefits on front page).

Sections that highlight each library: e.g., a row with “Videos – try our guided videos” with maybe thumbnails of a couple popular videos, “Exercises – quick practices” similarly, and “Articles – read latest insights”.

A prompt to take an assessment (“Check Your Mood – try our 2-min self-check quizzes”) possibly as a card linking to Assessments page.

It might also integrate a newsletter signup on the front page.

Essentially an overview of everything eMINDy offers to quickly direct different user interests. The pattern ensures consistent design for these blocks without requiring manual assembly each time.

Blueprint/Modern variants: It’s mentioned that patterns like exercise-modern.php, video-modern.php, article-modern.php, home-modern.php exist as blueprint or alternative designs
GitHub
. These could be prototypes or alternative styling (perhaps a more minimalist or a different layout style) for the hubs and homepage. They might not be actively used unless the admin chooses them. Possibly they were created to test different layouts or to offer a quick switch if the design is updated. For example, modern might feature a different hero style or different typography.

“page blueprints” suggests maybe patterns for generic page building blocks as well (maybe not directly referenced by templates, but available in block inserter if one wants to create a new page with a consistent style).

These additional patterns are noted with commit references, likely indicating where they were added in code
GitHub
, but not deeply described. We know their existence implies the theme has some flexibility or was exploring multiple design options.

Overall, patterns in eMINDy serve to reduce repetition and enforce consistency across the site’s various sections, especially the content “hubs”. They allow the small team or AI assistant to insert complex layouts (with search blocks, query loops, etc.) with one click, instead of manually configuring each block, thereby saving time and avoiding design drift.

It is noted that there was overlap in these hub patterns – for instance, video-hub, exercise-hub, article-hub, libraries-hub share similar structure (hero, search, filters, list) just for different content types
GitHub
. This indicates future refactoring might consolidate them or ensure changes in one (like style improvements) propagate to others for consistency. At the moment, they are separate, which might cause duplication. For now, developers should be mindful that if you improve one (say change how the search bar is styled in video-hub), consider updating the others similarly.

1.3 Template Parts

The theme defines reusable template parts (in wp-content/themes/emindy/parts/) for sections like the header and footer, which are included in multiple templates:

header.html – The site header part. It contains the top navigation bar structure. As described in code comments
GitHub
, it’s a flex container with three sections:

Branding (Left): The site title and tagline are displayed here (the code shows they use get_bloginfo('name') and get_bloginfo('description') within <p> tags
GitHub
, so it’s not a static image logo by default, but text for quick and accessible branding with styling applied).

Navigation (Center): A WordPress Navigation block, likely pulling the primary menu. The code confirms a wp:navigation block with horizontal layout
GitHub
. It’s styled to have some gap and is centered. This will output the menu items defined in WP menus.

Actions (Right): A group of interactive elements including:

Language switcher shortcode [em_lang_switcher] as a dropdown
GitHub
.

Search button or search form. The code snippet shows a wp:search block with a custom placeholder “Search eMINDy” and an icon button
GitHub
.

A CTA button (class “is-style-fill”) with text “Start Here” linking to the Start Here page
GitHub
. This is that rounded gold button that stands out for new users.

A Dark Mode toggle button (implemented as a raw HTML block) with a moon icon and appropriate ARIA labels for accessibility
GitHub
. The script toggles data attributes to switch theme variables (the CSS for dark mode is in style.css).

All these are wrapped in a flex group with small gap
GitHub
. This grouping and order can be adjusted in the editor if needed, but out-of-box is configured for eMINDy’s needs.

The header’s design balances utility (multi-language, search) with call-to-action (start here, dark mode). It is also responsive: the block markup indicates it wraps on small screens (flex with wrap and spacing). On mobile, presumably the navigation might collapse – Twenty Twenty-Five’s native behaviour might handle the nav block as a burger menu if configured to overlay, but here overlayMenu is “never”
GitHub
 meaning it stays as a normal menu with wrap. So on mobile it likely wraps the menu items below the branding.

The header also integrates Polylang’s output elegantly via shortcode rather than default Polylang menu item, which is fine.

footer.html – The site footer part. It likely contains:

A newsletter CTA section (the architecture mention says the footer has a newsletter CTA
GitHub
). Possibly something like “Stay updated: [Newsletter form]” or a link to Newsletter page. Or maybe just a prompt to subscribe that links to the newsletter page rather than form (since form is big, probably only on dedicated page).

Quick links: This might be a menu or manually placed links like “About – Contact – Privacy Policy – Terms”. Usually footers have that. Possibly the theme expects the user to edit the footer part to add those, or maybe it includes a secondary nav block for a footer menu.

Social links: maybe icons linking to eMINDy’s social profiles (if any). The comment suggests social links included
GitHub
.

Without the code in front, we assume it’s a typical informative footer. The mention of insertion via <wp:template-part> in templates
GitHub
 is just how WP includes it.

Given these template parts:

They are referenced by templates like <!-- wp:template-part {"slug":"header"} /--> in templates, meaning they can be edited globally in one place.

They ensure consistency across pages (e.g., language switcher in header appears everywhere, no duplicates of code).

If one needed to customize the header or footer for a specific page, WP’s Site Editor could allow a specific template to use a different header variant or hide something.

The header and footer underscore key integration points:

Accessibility: skip link is output just after <body> open via wp_body_open action in theme functions
GitHub
, which is separate from these parts but complements them.

SEO: The header includes the site title in a <p> rather than <h1> because on the home page we likely have a separate H1, and repeated site title on every page doesn’t need to be H1 (could be an accessibility decision).

Dark Mode: Interplay of the button and CSS variables is an architectural decision – minimal JS, just toggling attributes, and letting CSS do the rest, which is a good approach.

2. Plugin: emindy-core (wp-content/plugins/emindy-core)

The emindy-core plugin provides all custom functionality: custom post types, taxonomies, meta fields, shortcodes, content injection, and structured data handling. Essentially, it transforms a vanilla WordPress into the tailored experience needed for eMINDy. Below is the breakdown of its components:

2.1 Custom Post Types (CPTs)

Registered in includes/class-emindy-cpt.php:

em_exercise – Represents guided practice exercises (How-to style content).

Labels: “Exercises” (plural), “Exercise” (singular) in the UI
GitHub
.

Public: true (accessible on front-end, has archive)
GitHub
.

Show in REST: true (allowing future headless or Gutenberg editor to access it)
GitHub
.

Menu Position: 21 (so it appears near the top of admin menu).

Menu Icon: dashicon of a universal access or accessibility (specifically dashicons-universal-access-alt)
GitHub
, indicating exercises often involve physical/mental tasks.

Supports: title, editor, excerpt, thumbnail, revisions
GitHub
. (No comments, as decided).

Taxonomies: attached to all 7 custom taxonomies (topic, technique, etc.)
GitHub
.

Rewrite: Custom structure:

Slug: “exercise” (singular) for single URLs.

No front: means it doesn’t prepend category or anything, it’s at root or after site domain (except if WP is in a subdirectory).

Archive: the archive page is at /exercise-library/ (explicitly set)
GitHub
.

So an example path: https://site.com/exercise/deep-breathing/ for a single, and https://site.com/exercise-library/ for archive.

The archive being named “exercise-library” is a design choice to avoid conflict with any page that might have slug “exercise” and to clearly indicate it’s a listing.

em_video – Represents video content (could be YouTube videos).

Labels: “Videos”, “Video”
GitHub
.

Other settings essentially mirror em_exercise:

Menu Position: 22 (likely right below exercises).

Menu Icon: dashicons-video-alt3 (a video camera icon)
GitHub
.

Supports: same fields (videos might not need excerpt, but it’s included, possibly used for short description on cards)
GitHub
.

Taxonomies: same set (topics, etc.) for cross-consistency
GitHub
.

Rewrite:

Slug: “video”.

Archive: “video-library”
GitHub
.

So URLs like /video/some-video/ and archive /video-library/.

em_article – Represents articles/blog posts.

Labels: “Articles”, “Article”
GitHub
.

Menu Position: 23 (so all eMINDy CPTs group together).

Menu Icon: dashicons-media-text (icon with text, suited for articles)
GitHub
.

Supports: title, editor, excerpt, thumbnail, revisions
GitHub
 (which is basically all one needs for a blog post, plus excerpt for summary).

Taxonomies: same 7 custom taxonomies
GitHub
. This means even blog articles can be tagged with topics, techniques, etc. This is great for relating content across types (e.g., an article and a video on “sleep” both share topic “Sleep & Focus”).

Rewrite:

Slug: “article”.

Archive: “article-library”
GitHub
.

So structure /article/<slug>/, archive /article-library/.

All CPTs are:

Public (meaning they’ll appear in search results and can be navigated openly).

Shown in REST (so the block editor or any REST-based operations can handle them, also enabling potential future front-end usage via REST).

They share similar support fields to keep UI consistent (so an editor sees a similar interface for all).

They share taxonomies which simplifies the query for related content (you can query across post types by taxonomy).

The chosen rewrite slugs are short and intuitive. The “-library” archives ensure no clash with singular slugs (since if archive were /exercises/ and someone named a single “exercises” it could conflict, but that’s a minor risk — the design probably also to reflect a collective noun differently).

The plugin flushes rewrite rules on activation (as noted in the code comment)
GitHub
, so these become active immediately after activation.

All CPTs being REST-enabled suggests the possibility of building Gutenberg templates or patterns that query these or even using the Site Editor’s Query block to get them (which might not be fully working if they are not in the “Site Editor post types” list, but enabling REST is the first step).

2.2 Taxonomies

Registered in includes/class-emindy-taxonomy.php and attached to all CPTs:

The plugin defines seven custom taxonomies to classify content along different dimensions:

topic – Hierarchical taxonomy (like categories).

Represents high-level topics/themes of content (e.g., Stress Relief, Sleep & Focus, Anxiety & Clarity, Confidence & Growth, etc.).

Being hierarchical means topics can have subtopics (though initial ones might be top-level broad categories).

Purpose: help users find content by subject matter of interest, and allow related content grouping. For example, a video and an exercise both about “stress relief” share topic=Stress Relief and can be surfaced together.

Default terms (seeded in code) include likely ones as described: stress relief, anxiety & clarity, confidence & growth, sleep & focus, etc.
GitHub
. This matches typical wellness domains.

technique – Hierarchical.

The method or type of practice used in content (e.g., Breathing, Body Scan, Journaling, Affirmations, Visualization, Mindful Walking, etc.).

Terms cover actual techniques. Many exercises revolve around a technique, and videos might demonstrate a technique. Articles might discuss a technique.

Hierarchical here might allow grouping techniques (maybe grouping all breathing-related under “breathing techniques”, though each is granular anyway).

Seeded terms could be like breathing, body scan, grounding, journaling, affirmations, visualization, etc.
GitHub
.

duration – Non-hierarchical (flat, like tags).

Represents approximate duration ranges of content (especially exercises or videos).

Terms might include “30s”, “1m”, “2-5m”, “6-10m”, “10m+”
GitHub
.

These are used primarily for user filtering (e.g., someone has only 2 minutes, they filter exercises <=2m).

As flat taxonomy, terms are singular values and one content could have one duration category (the plugin might assign one automatically if total_seconds is known, or the content creator picks one).

format – Non-hierarchical.

The content format/type beyond just CPT. The default suggestion shows terms like video, article, worksheet, exercise, test, audio, checklist
GitHub
.

Some overlap with CPT (video CPT will always be “video” format, exercise CPT “exercise” format), but format taxonomy allows mixing types in queries. For example, one could tag a normal blog Post with “article” to integrate with eMINDy content even if it wasn’t in the CPT, or identify a subset of articles as “worksheet” or “audio” if they include an audio file.

It may not be heavily used yet, but future-proofing if they have, say, audio-only exercises or printable worksheets as content items (which might still be stored as an Article CPT but flagged format=worksheet).

use_case – Hierarchical.

Situational contexts where the content is useful. E.g., morning, bedtime, work break, commute, before sleep, study focus, social situation, etc.
GitHub
.

Helps users find content for specific scenarios (like “I have trouble sleeping, show me exercises for bedtime”).

Hierarchical possibly because you might group by time of day vs activity? But likely each use case stands alone. Possibly parent categories like “Time of Day” and children like morning/night, but not sure if implemented that way.

The plugin seeds likely common scenarios as separate terms (morning, bedtime, etc.).

level – Non-hierarchical.

Intensity or difficulty level of the content. Terms suggested: beginner, gentle, intermediate, deep
GitHub
.

This is akin to difficulty ratings (or depth of practice – a “deep” might mean very introspective or advanced practice).

Useful to guide users on where to start or to filter out too advanced content.

Content creators would choose one level per content.

a11y_feature – Non-hierarchical.

Accessibility features flags. Terms might include: captions, transcript, keyboard-friendly, low-vision-friendly, no-music version
GitHub
.

This taxonomy allows tagging content with special accessibility options. For example, a video that has closed captions can be tagged “captions”, or an exercise that includes a transcript (for those who can’t hear audio) tagged “transcript”.

Users (especially those with disabilities) could filter content that meets their needs (maybe in future a UI filter or just to mark content visually).

It’s likely multiple terms can apply to one content (e.g., a video might have both captions and transcript available).

All these taxonomies are registered with:

public => true (so archives exist for them and can be navigated or indexed; e.g., you can click “Breathing” technique and see all breathing exercises/videos).

show_in_rest likely true (so Gutenberg shows them in sidebars, and any headless use possible).

Default terms are inserted for each taxonomy on plugin activation. The code likely does:

For each taxonomy, define an array of default terms (with translations if needed).

On activation, call term_exists to check each by slug and insert if not exists
GitHub
.

It mentions idempotent seeding via term_exists to avoid duplicates
GitHub
. This means running the activation code twice won’t double-create terms (they check by slug).

It also mentions translation: likely the names of default terms are in both languages. Possibly they create the terms in the current locale and rely on Polylang to translate them manually. Or they might even create separate terms for Persian if they had a way, but Polylang usually handles taxonomy translations differently (with term metas).

Notably, all default terms are inserted with sanitized slugs and no duplicates even if run again, which is good for iterative deployments.

Attaching to all CPTs means a single taxonomy like topic has its scope across different post types. WordPress allows this. It means, for instance, the “Stress Relief” topic term aggregates posts from em_video, em_exercise, em_article that have that term. The theme or search can then retrieve cross-type content easily. This is powerful for Related content and perhaps for a unified search by topic page.

One must be careful: if WordPress default search doesn’t cover CPT well, one might create pages for taxonomy archives to ensure visibility. But with block theme, taxonomy archives have their own template (if not, index handles it). Possibly eMINDy theme didn’t explicitly create taxonomy-topic.html template, so they might use the default archive template for taxonomy, which would list posts of all types mixed (WordPress’s taxonomy query can include multiple CPTs, which by default it does if taxonomy is shared among them). By default, a taxonomy archive URL like /topic/stress-relief/ will show posts of any type that have that term, sorted by date (this is default WP behavior if no query_var customizations). That’s fine but maybe needs some design — maybe they rely on index.html to handle that.

2.3 Meta Fields

Registered in includes/class-emindy-meta.php:

The plugin defines custom post meta fields to store structured data not easily captured by content or taxonomies:

Structured JSON fields:

em_steps_json – Stores a JSON string of steps for exercises
GitHub
. This field contains an array describing each step (text and optionally duration, maybe other attributes). It’s used by the [em_player] and [em_exercise_steps] shortcodes to render the exercise flow. Keeping it in meta means it’s separate from the human-readable post content (which could be narrative or explanation) – a good design to not mix structured step data into the main post editor. Also, it allows easier reuse across languages (the plugin copies it to translations) and easier extraction for schema (HowTo schema needs steps list).

em_chapters_json – Stores JSON for video chapters
GitHub
. Similar logic: an array of timestamp + title entries that define sections of the video. Used by [em_video_chapters] to list them. It could also be used for schema (VideoObject’s chapter markup if one wanted).

Numeric meta for timing:

em_total_seconds – integer, total duration of an exercise in seconds
GitHub
. (For videos, maybe they rely on YouTube API for length, but for exercises, the author defines it or sum of steps durations).

em_prep_seconds – preparation time needed (for HowTo schema)
GitHub
. E.g., “10” if it takes 10 sec to get ready or 10 minutes to gather things – though seconds is unit here.

em_perform_seconds – perform time (actual active time)
GitHub
. HowTo schema splits prep vs perform vs total.

String meta for HowTo details:

em_supplies – any supplies needed (e.g., “Yoga mat, Pillow”)
GitHub
.

em_tools – any tools required (maybe similar to supplies, but in schema HowTo, supplies vs tools could differ; e.g., supplies are consumable or items needed, tools could be equipment).

em_yield – expected yield or output (in HowTo, yield might be like “a cup of tea” for a recipe; in a wellness context, not sure – maybe something like “state of relaxation” but that’s abstract. Possibly they included it for completeness or in case of things like journaling yields an actual journal entry).

All these fields are:

Single-value fields (not repeatable arrays except the JSON strings themselves encapsulate multiple values).

Sanitized on save (the plugin likely uses a central sanitize function: e.g., Meta::sanitize_json for JSON fields, and standard sanitizers for others)
GitHub
GitHub
.

Exposed to REST with appropriate permission (the code says they share a single capability callback so only editors can mutate them via REST)
GitHub
. That likely means when registering meta, they provided 'show_in_rest' => true, 'auth_callback' => function(...){ current_user_can('edit_post') } or similar.

The HowTo fields align with schema output needs. E.g., for an exercise, they can build a HowTo JSON-LD that includes total time, prep time, perform time, list of steps (with text and maybe durations), plus supplies/tools if any, and yields if any. This positions eMINDy well for search engines (like if someone searches “How to calm down in 1 minute”, Google might pick up the HowTo and show steps directly if schema is present).

2.4 Shortcodes

Defined in includes/class-emindy-shortcodes.php:

The shortcodes are central to front-end dynamic content. Summarizing from code and earlier notes:

Practice & Assessment Shortcodes:

[em_player] – Renders the interactive player UI for exercises
GitHub
. It uses em_steps_json of the current post to build the HTML (which likely includes a list of steps, controls for prev/play/next, and some time displays). The code snippet in Shortcodes class shows output with HTML structure and uses translations for labels “Guided Practice”, “Step X/Y”, “Step time”, “Total” etc.
GitHub
. It enqueues some JS (the plugin enqueued assess-core.js and possibly that handles the countdown and controls).

Called automatically in content injection for singular exercises
GitHub
, so authors usually don’t need to place it. But it can be manually used too (if one wanted to embed an exercise player in another page).

[em_exercise_steps] – Outputs an ordered list of steps (read-only) from em_steps_json
GitHub
. This is essentially the textual fallback or print view of the steps. The code collects the steps, normalizes them (ensures each has text and maybe parse durations if present)
GitHub
, then outputs an <ol> list with each step text in a <li>
GitHub
. It does nothing if no steps meta.

Used likely in templates (the single-em_exercise might include this after the player as a visible list).

This separate shortcode ensures that if JS is not working or user wants a quick view of all steps at once, they have it. Also useful for screen readers (though the interactive player is also keyboard accessible, presumably).

[em_phq9] – Renders the PHQ-9 form (9 questions with radio options)
GitHub
. The code snippet shows it generating HTML form with class em-phq9, a description (non-diagnostic disclaimer), an ordered list of 9 <fieldset> each with a <legend> question and 4 radio options (0=Not at all, 1=Several days, 2=More than half..., 3=Nearly every day)
GitHub
GitHub
. It then adds submit and reset buttons, and a hidden result region that will show after submission (via JS).

This form is static HTML delivered by shortcode. The actual scoring is handled by front-end JS (the snippet in the result region shows an empty <p class="em-phq9__score"></p> which will be filled by JS calculation, and share buttons for print/copy/link/email
GitHub
).

The shortcode does not handle result computation on server side at all. This is intentional for privacy; all answers stay on client and only if user clicks “Get shareable link” or “Email me” do their score and type go to server.

[em_gad7] – Similarly, renders the GAD-7 form (7 questions). The structure is similar to PHQ-9 (the class uses the same em-phq9 em-gad7 classes on the form to maybe share styles)
GitHub
. Options too from 0 to 3. The script likely handles it analogously.

The code generation might be omitted in snippet, but it's effectively the same logic but with 7 questions content.

[em_assessment_result] – Displays the result summary based on query parameters. It expects ?type=phq9&score=X&sig=Y in URL. The shortcode verifies the HMAC signature to ensure the score wasn’t tampered with
GitHub
. If valid, it then maps the numeric score to a severity band:

For PHQ-9: 0-4 Minimal, 5-9 Mild, 10-14 Moderate, 15-19 Moderately severe, 20-27 Severe
GitHub
 (the code likely covers up to 27).

For GAD-7: 0-4 Minimal, 5-9 Mild, 10-14 Moderate, 15-21 Severe
GitHub
.

It then prints a line like “Score: X / 27 — Mild” using a translation-friendly sprintf
GitHub
. And below it a note: “This check is educational, not a diagnosis… if unsafe, visit Emergency page.”
GitHub
.

If signature check fails or missing params, it returns “Invalid or missing result.” inside a styled div
GitHub
.

This output is included in the template so the user sees it after redirect from form submission (the JS triggers redirect when you hit submit on PHQ/GAD form, presumably).

This shortcode ensures even if JS did calculation, the user ends up with a shareable page that can be viewed again or shared with a counselor (the HMAC prevents altering the score in URL without knowing secret).

Video & Chapters Shortcodes:

[em_video_chapters] – Renders a list of chapters for video posts
GitHub
. It likely takes the em_chapters_json meta (which might be an array of objects with maybe time stamps and text). The code likely outputs an ordered or unordered list with each chapter. Possibly it converts timestamps to clickable links (like anchor linking to youtube.com/watch?v=XYZ&t=90 for 1:30 if integrated, but since embedding is via iframe, maybe not easily controlling that).

It might also show them just as text with times. The architecture says “optionally linking to YouTube timestamps”
GitHub
, implying it might detect if the embed uses YouTube that you can link target. If using youtube-nocookie iframe, adding ?start= param might work for each link. Possibly the shortcode generates links that, when clicked, call player API via JS to seek. Not sure if implemented.

Nonetheless, it provides structured listing to navigate video content. Good for SEO as well (YouTube chapters are recognized by Google but nice to have on page too).

Discovery & Related Shortcodes:

[em_related] – Displays a grid of related posts to the current post
GitHub
. The function in code fetches current post, then by default tries to find others with shared taxonomy terms:

It has attributes to override behavior (post_type filter, specific taxonomy to match on, count, etc.)
GitHub
GitHub
.

It prioritizes the current post’s “primary topic” if defined (maybe they designate one topic meta as primary) to get more relevant results
GitHub
GitHub
.

Then queries for posts in those terms, possibly with fallback to a text search on title/excerpt if not enough found (the code mentioned deterministic fallback search with no user input).

It is aware of Polylang: if Polylang is active, it will restrict to same language by adding lang param to query or using pll_get_post_language
GitHub
.

The output is an HTML grid (likely using the same card styles as archive patterns). Possibly included images, titles, maybe excerpt.

If used in multiple contexts (video page might show related videos or mix with other type?), they gave ability to specify post_type attribute. The default is to use current post type unless overridden.

They also alias [em_related_posts] to this with a doing_it_wrong notice (to inform devs to use [em_related])
GitHub
.

Additionally, architecture mentions “Helpers for popular content, sitemap-like lists and report links appear alongside related logic”
GitHub
. Possibly in the Shortcodes class file there are other internal shortcodes or functions that list e.g. “most popular posts” or “full sitemap of posts grouped by category” for internal use or future features. These aren’t detailed, but might exist (maybe shortcodes not explicitly documented but present).

Search & Utility Shortcodes:

The architecture references shortcodes for search UI helpers:

[em_search_bar], [em_search_query], [em_search_section], etc. Perhaps these output a search form or highlight the search query on a page, or provide UI in patterns. They mention sanitization of search terms and use of no_found_rows for performance in popular queries listing
GitHub
.

Possibly [em_search_section] might output something like “Results in Videos (count)” splitting search results by type.

These might have been experimental or used to create a search results page that segments content by type (instead of default mix). If not fully utilized now, they might exist for potential advanced search page.

Without the exact code, we deduce they emphasize security (sanitizing any user input in these shortcodes thoroughly).

Transcript helpers, share links, etc.: The text hints at utility shortcodes like transcripts, table of contents, share links:

[em_transcript] – we know exists and is marked deprecated
GitHub
. Originally, maybe used to output the post content as a formatted transcript (like wrap content in some stylized box). They now rely on just writing transcript in content and styling via theme, so they deprecated the shortcode but left it for backward compatibility.

[em_video_filters] – outputs a filter UI for video archives (search + dropdown). Marked deprecated because they likely replaced it with block pattern or theme code. Still in code for backward compat (maybe early site version used a shortcode in a page for filters).

[em_video_player] – embed a YouTube player for current post’s em_youtube_id. Deprecated because block editor or core embed can handle it, or they moved to just including link in content. But left for older content.

There might be share link shortcodes not mentioned explicitly, but possibly code added something like [em_share_twitter] etc. Or maybe not; maybe share buttons are directly in theme patterns.

It mentions “search terms are sanitized before WP_Query” and “popular queries set no_found_rows” – maybe they had a shortcode to list trending search queries (just speculation from that text).

Newsletter Shortcode:

[em_newsletter] – This one is explicitly mentioned as wrapping the newsletter form output
GitHub
. In code, they actually implement [em_newsletter_form] which returns the HTML form (we saw in newsletter.php code)
GitHub
, and the [em_newsletter] shortcode calls that function or just returns the same output. They specifically mention the plugin wraps the form to allow filtering via hooks (the comment in Shortcodes file was to prefer direct function call to avoid nested shortcodes and allow hooks).

So likely [em_newsletter] simply does if function_exists('em_newsletter_form') return em_newsletter_form(); else maybe fallback to placeholder.

The idea being one can place [em_newsletter] anywhere to get the subscribe form easily. They included it likely to decouple the concept of newsletter (maybe to allow swapping out form easier).

Deprecated or legacy shortcodes (transcript, video_filters, video_player) remain for backward compatibility and are marked with @deprecated in code and possibly a _doing_it_wrong call when used
GitHub
. This tells developers those will be removed and they should update templates to not use them. The presence of duplicate [em_related] definitions was noted – probably an accidental duplication in class that they plan to clean up (the architecture notes shortcodes class is “monolithic with mix of responsibilities that could be separated in future”
GitHub
, so a refactor might split it into e.g. Shortcodes_Assessment, Shortcodes_Content, etc. using PSR-4 in future).

2.5 Content Injection

In includes/class-emindy-content-inject.php, a filter is registered on the_content with priority 9 (to run before default autop maybe)
GitHub
. It checks:

If not in the main loop or not singular, it returns content unchanged
GitHub
.

If it’s a singular em_exercise, it prepends [em_player] plus two line breaks to the content
GitHub
.

If it’s a singular em_video, it appends two line breaks + [em_video_chapters] to the content
GitHub
.

Then returns the modified content.

This ensures:

Exercise pages always start with the interactive player before whatever content the author put (the author likely doesn’t need to put anything at top, maybe they put explanatory text below which now comes after the player).

Video pages always end with a chapters list, so authors don’t need to manually insert it (they might just put their transcript or description in content, and the shortcode comes after automatically).

They did not inject the newsletter shortcode anywhere (that is up to templates).
They did not inject related content via content filter (they probably use either template or block for related; a shortcode exists but not auto-inserted).

2.6 Schema / Structured Data

The plugin has two layers for structured data (to avoid duplicating Rank Math):

Central Schema Builders (class-emindy-schema.php):

Provides static methods to build JSON-LD arrays for each CPT type:

HowTo schema for exercises (using title, steps, tools, duration meta).

VideoObject schema for videos (using video title, description, thumbnail, upload date, YouTube ID as video content URL).

Article schema for articles (using title, author, date, maybe articleSection if topics used).

Each returns either a structured array or null if required data missing (e.g., if exercise has no steps, maybe skip HowTo).

These are meant to be reused by both Rank Math integration and fallback.

Rank Math integration (primary path):

In includes/schema.php, they hook into Rank Math’s filter that allows altering the JSON-LD it generates
GitHub
.

They add:

Organization schema (probably eMINDy as an Organization with name, logo).

WebSite with potential SearchAction (allowing Google a search box in results).

On content pages, they detect if it’s a CPT and inject the relevant schema built from the central builders into Rank Math’s data
GitHub
.

They also ensure using safe types like CollectionPage for archives to avoid misusing WebPage type, etc., to keep Google happy (the architecture mention “safe archive CollectionPage/ItemList nodes”
GitHub
).

The idea is that Rank Math already outputs base schema (like webpage, article if it thinks content is article, etc.), but likely doesn’t know about our custom types fully. By integrating, we avoid double schema which could be penalized.

The plugin likely disables Rank Math’s own attempts for Video schema (Rank Math might add VideoObject if it sees a video embed, but we override with ours to include chapters etc., or maybe we integrate by adding to it).

Fallback Schema (when Rank Math not active):

The class-emindy-schema has an output_jsonld() function
GitHub
. On pages, the plugin’s emindy_core_output_schema_fallback hooked to wp_head at priority 99 does:

If Rank Math plugin class exists, return (don’t output anything)
GitHub
.

Else call Schema::output_jsonld() which will:

Determine if current page is a singular of our CPTs, build the appropriate structured data array (HowTo/Video/Article).

If it got something, json_encode it and print in a <script type="application/ld+json">.

This ensures even without SEO plugin, search engines get the rich data. They limit it to singular CPT pages, not archives, likely to not conflict or because archives schema is less crucial. Possibly they skip on archives or output a simpler ItemList.

The fallback ensures not duplicating if Rank Math is present (via the guard).

A code comment in plugin main suggests they had a simpler filter for Rank Math earlier and removed it in favor of this full approach
GitHub
 (the Persian comment in code around L264-L269 explains the old filter was removed and replaced by the fuller includes/schema.php approach to avoid duplication issues).

2.7 Newsletter & Analytics

includes/newsletter.php:

Handles creating the emindy_newsletter table on activation (with id, email, name, consent, ip, ua, timestamp)
GitHub
.

Provides the shortcode [em_newsletter_form] (which we covered) that prints HTML form including a hidden nonce field and action admin-post.php target
GitHub
.

Hooks into admin_post_em_newsletter_subscribe and admin_post_nopriv_em_newsletter_subscribe to handle form submissions in function emindy_newsletter_handle
GitHub
. This function:

Verifies nonce.

Sanitizes email and name, and records consent as 1/0.

If email invalid, redirects back with success=0 (which the form checks to perhaps display an error).

If valid, inserts into newsletter table (using ON DUPLICATE KEY UPDATE to avoid duplicates but update name/consent if email re-used)
GitHub
.

Sends admin notification email with subscriber info if admin email exists
GitHub
.

Fires do_action('emindy_newsletter_subscribed', [...]) with subscriber data
GitHub
 for integrations.

Sends welcome email to subscriber (using wp_mail with a formatted HTML message built in code, with a link to a 1-minute break content)
GitHub
.

Redirects to /newsletter/?success=1 after done
GitHub
.

The newsletter’s design is minimal local storage but gives hooks to connect external ESP if needed.

includes/class-emindy-analytics.php:

Creates emindy_analytics table on activation (id, time, type, label, value, post_id, ip, user_agent)
GitHub
.

Register AJAX actions for emindy_track for logged-in and not (nopriv)
GitHub
.

track() checks nonce 'emindy_assess' (they reuse the same nonce from assessments for simplicity – not ideal naming but it is what it is)
GitHub
.

Sanitizes type (a short key like 'video_play', 'quiz_complete'), label and value (text fields) from POST, post id if passed.

Requires at least type else sends error.

Then inserts a row in table with these fields plus remote IP and UA (capped length)
GitHub
GitHub
.

If insert fails, responds JSON error; if success, JSON success true.

The front-end JS likely calls this for events:

For example, in video player JS, when play is clicked, do jQuery AJAX POST to admin-ajax.php with action=emindy_track, type='video_play', post=current video ID.

For assessments, maybe track on result completion with type='phq9_complete' value=score, etc.

Possibly not heavily used yet, but structure is ready.

This table can be reviewed to see usage patterns (no UI given, but one can query DB or build one later).

Rate limiting: It’s not explicitly in track (the assessment email uses transient to rate limit emails 5/hour in Ajax class send_assessment)
GitHub
, but track itself might not have a rate limit – however, since it collects mostly non-personal data (except IP, UA), spam isn’t too risky unless someone maliciously floods it. Could be extended later if needed.

includes/class-emindy-ajax.php:

Registers AJAX emindy_send_assessment and emindy_sign_result for quiz:

sign_result: we saw earlier, takes type and score, calculates HMAC and returns the URL (which front-end uses to redirect user to results page)
GitHub
GitHub
.

send_assessment: handles emailing the summary to user-provided email
GitHub
:

Checks nonce, sanitizes email, summary text, and kind (phq9/gad7)
GitHub
.

Validates email & fields.

Rate-limits by IP using a transient (key emindy_rate_<md5(ip)>, counts up and if >=5 in last hour, block)
GitHub
.

If not rate-limited, composes an email (subject "Your PHQ9 summary", body just the summary text plus site link)
GitHub
.

Sends via wp_mail. If success, returns JSON success true; if fail, JSON error.

This allows user on result page to type their email and get a copy. The summary likely contains the score line and note, as rendered on page (the JS probably grabs the result container text for summary).

So, section 2.7 highlights that beyond core content, the plugin covers features like user engagement (newsletter, tracking) and the needed restrictions on them (security and rate limiting for emailing to avoid abuse).

3. Content Injection & Polylang Integration

This summarises how plugin and theme ensure content appears where needed and works with multilingual:

Automatic content injection: (As described above) via the_content filter, [em_player] and [em_video_chapters] are injected into exercises and videos respectively
GitHub
. This keeps authors from having to remember to add those shortcodes, giving a consistent presentation. If needed, authors can still add additional text above players by using post content, but typically we expect them to just fill the body with the exercise description (which will appear below player) or video description (above chapters but below embed).

Language switcher shortcode [em_lang_switcher]: implemented in Shortcodes class
GitHub
, likely using Polylang’s pll_the_languages under the hood. Possibly allows attributes like dropdown=1, show_flags, etc., as we saw in header usage. This integration ensures we have a customizable language switcher independent of Polylang’s default menu item (Polylang has a template tag pll_the_languages which can output a list or dropdown given parameters).

The code would fetch Polylang’s function if exists. If Polylang is absent, the shortcode likely outputs nothing (since then only one language).

In header template, they set dropdown="1" show_names="1" show_flags="0" which the shortcode code uses to call pll_the_languages(array('dropdown'=>true,'display_names_as'=>'name','show_flags'=>false)) or similar.

Related content queries with language: The [em_related] shortcode and possibly other queries ensure Polylang context is respected. The code snippet for related shows:

If Polylang is active, they get current post language code and likely pass an argument to WP_Query to filter by that (maybe Polylang offers lang param).

This prevents showing related content in other language which might confuse users (one wouldn’t want an English article recommending a Persian video, ideally).

So each Polylang language’s content network stays mostly siloed unless translation relationships used (Polylang can link translations of a post, but related content typically should be same language).

Meta copying on translation: In emindy_core_plugins_loaded, they check if Polylang is present, then add a filter pll_copy_post_metas to include our custom meta keys
GitHub
. The function emindy_core_polylang_copy_metas returns an array merged with existing to-copy list with:

'em_steps_json','em_chapters_json','em_total_seconds','em_prep_seconds','em_perform_seconds','em_supplies','em_tools','em_yield'
GitHub
.

So when a translator duplicates a post to another language, Polylang will copy these meta values to the new post. This is crucial so that, e.g., an exercise translated to Persian retains the same steps JSON (the steps text likely still in English, so translator will then edit the JSON text by hand to Persian in the meta box – unfortunately Polylang doesn’t auto translate content – but at least the structure and durations carry over).

This ensures consistency and saves time (don’t have to re-enter durations or re-upload steps).

For videos, similarly chapters JSON copied so translator only needs to translate the chapter titles in the meta box to Persian.

If new meta keys are added in future (like if we add another meta for something), we should update this list to copy those as well.

All these integration points mean the theme and plugin were built with multilingual in mind from the start (which is somewhat rare and great). They also emphasize accessibility and stable content injection to reduce manual errors.

4. File Reference Index

For ease of navigation in development, here’s a quick index of key files and their roles:

Custom Types & Taxonomies:

wp-content/plugins/emindy-core/includes/class-emindy-cpt.php – CPT registration (Exercises, Videos, Articles).

wp-content/plugins/emindy-core/includes/class-emindy-taxonomy.php – Taxonomy registration (Topic, Technique, etc., plus seeding default terms).

Meta & Shortcodes:

wp-content/plugins/emindy-core/includes/class-emindy-meta.php – Meta fields (JSON meta and others for HowTo data), with sanitization helpers.

wp-content/plugins/emindy-core/includes/class-emindy-shortcodes.php – All shortcode definitions (players, quizzes, related content, etc.).

Content Behavior:

wp-content/plugins/emindy-core/includes/class-emindy-content-inject.php – Hooks to inject shortcodes into content automatically.

SEO Schema:

wp-content/plugins/emindy-core/includes/schema.php – Integration with Rank Math’s schema output (adds Org/WebSite and CPT schema).

wp-content/plugins/emindy-core/includes/class-emindy-schema.php – Standalone schema builders and fallback JSON-LD output.

Newsletter & AJAX:

wp-content/plugins/emindy-core/includes/newsletter.php – Newsletter form shortcode, form handler (subscribe logic), and table creation.

wp-content/plugins/emindy-core/includes/class-emindy-ajax.php – AJAX endpoints for signing quiz results and emailing summaries.

wp-content/plugins/emindy-core/includes/class-emindy-analytics.php – Analytics event logging (table creation and AJAX track handler).

Theme Templates (for reference with above logic):

wp-content/themes/emindy/templates/single-em_exercise.html – Shows an exercise, expecting [em_player] at top and [em_exercise_steps] likely included.

wp-content/themes/emindy/templates/single-em_video.html – Shows a video, expecting video embed and [em_video_chapters] appended for chapters.

wp-content/themes/emindy/templates/single-em_article.html – Shows an article, simpler content plus maybe related or newsletter block.

wp-content/themes/emindy/templates/archive-em_exercise.html – List of exercises (with filter UI in pattern).

wp-content/themes/emindy/templates/archive-em_video.html – List of videos (with search/filter UI).

wp-content/themes/emindy/templates/archive-em_article.html – List of articles.

wp-content/themes/emindy/templates/page-archive-library.html – The unified library page pattern inclusion.

Key Patterns under wp-content/themes/emindy/patterns/ – e.g., video-hub.php, exercise-hub.php, article-hub.php, libraries-hub.php, archive-library.php, front-page-emindy.php, etc. These correspond to section layouts used in the above templates.

Understanding these components and their interplay is crucial for extending or debugging the eMINDy platform. For instance, if a shortcode isn’t outputting expected content, check class-emindy-shortcodes.php and see if the data (meta fields) it relies on are present (maybe a meta not saved or Polylang not copying it). Or if a schema is not showing, verify if Rank Math integration in schema.php is functioning (Rank Math might update their filter names or such, requiring adjustment).

The architecture as described is quite modular: display logic in theme (with simple placeholders), heavy logic in plugin (shortcodes, data registration), and integration glue for multi-language and SEO.

Future improvements (as noted in optimization plan) might include:

Adopting an autoloader (PSR-4) for classes to remove manual require_once from main plugin file.

Splitting that giant Shortcodes class into multiple smaller ones grouped by domain (which could then be loaded only when needed or at least organized).

Adding tests (they mentioned introducing PHPUnit in phases).

Possibly turning some shortcodes into blocks as WordPress moves toward full block editing paradigm (though shortcodes work fine and can be inserted via shortcode block, having native blocks might provide better preview in editor).

And simplifying pattern overlaps (maybe one unified “library hub” block that is parameterized by post type, instead of multiple patterns, but patterns are fine too).

This concludes the architecture deep dive, which should serve as a reference when working on eMINDy’s codebase or planning enhancements.
