Development Guide

This document outlines the coding standards, workflows, and best practices for developing the eMINDy WordPress project. It is intended for developers (including internal team members and AI coding assistants) working on the eMINDy theme and plugin. Adhering to these guidelines ensures code consistency, maintainability, and a smooth integration process within the project.

1. Coding Standards

eMINDy follows WordPress Coding Standards for PHP, HTML, CSS, and JavaScript:

PHP Standards: Use proper indentation (tabs), spacing, naming conventions as per WordPress PHP coding standards. Functions and variables are snake_case. Class names are Capitalized_Words with underscores (we use class-emindy-*.php file naming and EMINDY\Core\Class_Name in code). Always include PHP docblocks for functions, with descriptions and @param/@return annotations for clarity.

Sanitization & Escaping: Every time you output data in HTML, escape it appropriately (esc_html, esc_attr, wp_kses for larger HTML, etc.). Every time you receive data (POST, GET, etc.), sanitize or validate it (sanitize_text_field, absint, sanitize_email, etc.). This project handles user input (newsletter sign-ups, quiz responses) so security is paramount. For example, before inserting an email into the database, we use sanitize_email
GitHub
, and when printing user-provided summary text in an email, we strip all tags.

Translation: All user-facing strings must be wrapped in translation functions with the correct text domain. Use __() for return strings, _e() for echo, esc_html__()/esc_html_e() for strings that need escaping on output. Provide context with _x() if needed. E.g. esc_html__( 'Subscribe', 'emindy-core' ). Ensure text domain is emindy for theme files and emindy-core for plugin files. The project is bilingual, so every string should be prepared for translation. This includes even small things like button labels, form error messages, etc.

Accessibility (a11y): Follow accessible markup practices. Use proper HTML elements (e.g., use <button> for clickable actions, not just anchors or divs). Include aria-label or screen-reader text where needed (the theme, for example, adds aria-label on the dark mode toggle to announce its purpose
GitHub
, and uses screen-reader-text class for skip links
GitHub
). When adding new features, consider keyboard navigation and focus management. For instance, if you introduce a modal, ensure focus trapping and ESC to close.

CSS Standards: Use BEM-like class naming or at least scoped names (we prefix many classes with .em- to avoid conflicts). Indent CSS with two spaces as WP recommends. Use relative units (rem, em) for typography when possible and consult theme.json for color variables or spacing variables instead of hardcoding values (the theme provides CSS custom properties like --em-teal, --em-border for consistency
GitHub
). Write CSS with responsiveness in mind (the theme already has some mobile adjustments, continue that pattern).

JavaScript Standards: We primarily have some JS for dark mode toggle and quiz logic. Follow WordPress JS coding standards: 2-space indent, camelCase for variables, upper CamelCase for constructors. Use strict equality. Wrap code in IIFE or ensure not polluting global namespace (the plugin’s JS attaches to window.emindyAssess or similar, which is fine under one global object). If adding new JS, consider enqueuing it properly via wp_enqueue_script with dependencies (jQuery, etc.) as needed. All new JS should ideally be ES5 (for broad compatibility) or if ES6, transpiled – currently our code is simple enough not to need build steps, and we might keep it that way to reduce complexity.

Docs & Comments: Use clear in-line comments to explain non-obvious code sections. For any complex logic (e.g., the algorithm in [em_related] shortcode or Polylang meta copy filter), there's likely already a comment – maintain those and update if logic changes. For any functions or classes you create, include a descriptive docblock. The docblock for shortcodes, for example, should note what the shortcode does and any parameters.

We recommend setting up a PHPCS with the WordPress Coding Standard ruleset in your editor/IDE to automatically flag style issues. (The project plans to integrate automated code style checks in CI in the future.)

2. Project Structure and Naming

Understand the repository structure (see README.md Structure section for overview). Place new files in appropriate locations:

Theme (emindy): Template files (.html) for block templates, .php for patterns (containing serialized block content), .css in assets for additional styles, .js in assets for scripts (like dark-mode-toggle.js). If adding a new template or pattern, follow naming convention (single-<posttype>.html, pattern-name.php). For global assets, add and enqueue via functions.php (we enqueue one CSS and one JS for theme customizations
GitHub
; if adding more, consider whether it can be part of those).

Plugin (emindy-core): Class files are in includes/ prefix with class-emindy- and use the EMINDY\Core namespace. If adding a new class, name it descriptively and update emindy-core.php to require it (keeping alphabetic or logical order of requires). For instance, if adding a class to handle a new CPT or feature, follow existing patterns.

Add new shortcodes to the Shortcodes class if related, or if it makes the class too large, you can propose splitting classes (coordinate with team if large refactor).

If adding a settings page or WP-Admin UI, consider using the existing Admin class or create a new one in includes/admin/ perhaps.

File/Folder Naming: We use lowercase with hyphens for file names (no spaces). Keep names short but clear. E.g., a new taxonomy "mood" would go in class-emindy-taxonomy.php as another function, rather than a new file, since it’s small. But a bigger feature like "class-emindy-quiz.php" could house quiz-specific logic if Shortcodes class is too crowded.

Branching Strategy: Use feature branches for any changes. Do not commit directly to main. Name branches by component or issue: e.g., feature/related-shortcode-refactor or bugfix/newsletter-consent. This helps in code reviews and isolating changes.

Commit Messages: Write clear, concise commit messages prefixed with the area of change:

Use conventional commits style where possible or at least a similar format:

theme: ... for theme-related changes,

plugin: ... for plugin changes,

or more specific like fix: ... for bug fixes, feat: ... for new features, refactor: ... for code refactoring.

Example: plugin: fix undefined index in newsletter consent handling or theme: adjust styles for RTL language support.

If a commit closes an issue or relates to one, mention (#issue-number) in the message for traceability.

Pull Requests: When your feature branch is ready, open a PR to merge into main. In the PR description:

Summarize what the change does, referencing any issue/ticket.

Note any changes to documentation or configuration needed.

Ensure CI checks (if any, like code style or build) pass before requesting review.

We require at least one reviewer approval before merging (even if it's an AI assistant, a human should glance over or vice versa).

Do not merge your own PR; allow repository maintainers to do it after review.

3. Build & Deployment Workflow

eMINDy uses GitHub Actions to package the theme and plugin into ZIP files for release (see docs/RELEASE.md for details). As a developer:

You don’t need to run any complex build for PHP; just ensure your code is committed.

If you add images or other assets, they will be included in the zip (just commit them).

If you add any external libraries or dependencies, discuss with the team first. We currently have no Composer or npm dependencies to keep things simple. If you think a composer package is needed, weigh it against just writing a small function (we prefer zero dependencies unless absolutely necessary for security or heavy functionality).

WP-CLI commands: We use WP-CLI for tasks like generating POT files for translation:

For example, after adding many new strings, run wp i18n make-pot ./wp-content/plugins/emindy-core ./wp-content/plugins/emindy-core/languages/emindy-core.pot to update plugin POT, and similarly for theme. If you do this, commit the updated POT files.

If you create new post types or taxonomies and want default terms via WP-CLI, you can test with wp term list <taxonomy> or similar.

We haven’t built custom WP-CLI commands for the plugin (no WP_CLI::add_command yet). If a need arises (like bulk operations or data fixes), we can add them under a condition if WP_CLI in plugin code.

GitHub Actions CI: The repo has an action build-zips.yml which runs on demand (not on every push, currently triggered manually via Actions tab or maybe on tag). It essentially zips up emindy/ and emindy-core/. If you add files at unusual locations, ensure the workflow includes them (likely it does by zipping the folders). Also, update the version numbers in style.css and emindy-core.php before triggering a release build, and update docs/CHANGELOG.md.

For development/staging deployment, typically you might push the repo to a test server or run it locally. You can use something like composer install if we had, but since not, just ensure WordPress picks up changes (in local dev, have debug on to see errors).

4. GitHub and Issue Tracking

The project likely uses GitHub Issues to track tasks, bugs, and feature requests. When working on an issue, assign it to yourself (or comment that you are taking it, if no direct assignment).

If an issue is about a bug, strive to reproduce it in a development environment first. Write a failing test if possible (though we have no PHPUnit tests yet, you can simulate manually).

When committing a fix, mention “Fixes #X” in the commit or PR to auto-close the issue when merged.

Use issue labels to categorize (e.g., bug, enhancement, question). This helps the team prioritize.

If during development you encounter a bug or have an idea, open an issue for it if one doesn’t exist. Do not fix things silently without tracking if it could impact end users.

5. Testing & QA

Manual Testing: Because this project is user-facing (mental health content), be diligent in testing UI changes:

After changes, navigate through the site in both languages to ensure nothing broke.

Test on a mobile view if possible (the design is responsive; if you adjust CSS, verify it on smaller screens).

For any forms (newsletter, self-tests), test the full flow. E.g., try subscribing with a valid email, an invalid email, see if error messages or success messages show correctly. The newsletter form should show a thank-you and the user should get an email (use a real address or check mail logs).

For quizzes, fill them out and see if result calculation is correct and consistent (if you know PHQ-9 scoring, verify band output). Try the "Email me the summary" and ensure it arrives and looks okay.

If Polylang is active, test switching languages on various pages, including ones you added or changed. Does content show properly? Are templates picking up the correct translated or default content? Check especially pages like Assessments or Newsletter on both languages.

If SEO changes, use the browser DevTools or page source to confirm schema is present or meta tags changed as expected. E.g., if you modify how schema outputs something, check a page’s source for the JSON-LD block.

Automated Testing: Currently there are no automated tests, but we plan to incorporate:

PHPUnit: for server-side logic (like maybe testing the emindy_newsletter_handle function with various inputs, or testing the emindy_schema builders output the expected arrays given sample inputs).

Integration tests: If feasible, using WP’s WP_UnitTestCase to simulate creating a post with steps meta and ensuring [em_player] returns expected HTML, etc.

End-to-end tests: Possibly with a tool like Cypress or Playwright to simulate a user taking a quiz or submitting the newsletter. This is aspirational but could be valuable for critical flows (so we don’t accidentally break the forms).
For now, rely on thorough manual testing and code review to catch issues.

Debugging Tips:

Enable WP_DEBUG_LOG in your config. If something goes wrong (blank page or error), check wp-content/debug.log. Our code should not generate any PHP warnings or notices; if you see some (e.g., “undefined index”), fix them. Even if it doesn’t break functionality, we treat warnings as issues to fix for clean code.

Use error_log() for quick debugging if needed, but remove or comment it out after (don’t leave stray logging in production code).

For JavaScript, use the browser console. The quiz code likely logs to console for errors (like if nonce fails, etc.). If you add JS, you can console.log for debug but remove those logs in final commits to keep console clean.

Performance Considerations:

Keep an eye on performance when developing. eMINDy runs on a typical PHP hosting; heavy operations should be minimized.

Avoid un-cached complex queries in shortcodes that run on every page load. For example, [em_related] uses no_found_rows=true and limits results
GitHub
 to lighten the query. Follow that example if writing custom queries. If you ever need to do something heavy, consider caching results in a transient.

Don’t load large libraries unless necessary. So far, no external JS libraries except what WordPress provides (like jQuery which is default).

Use WordPress APIs properly (like wp_schedule_event for cron tasks, if we ever add scheduled newsletter sending perhaps).

Security and performance sometimes trade-off (like nonces every request vs static). Always lean secure, then optimize if needed.

6. Collaboration and Code Reviews

Pull Request Reviews: Every PR should be reviewed by at least one other developer. If you’re the only dev, at least do a self-review: read through your diff on GitHub and add comments for context if needed. This often helps catch mistakes (missing sanitization, etc.) before merging.

Feedback: When reviewing, be constructive. If something isn’t following standards, point it out and reference these guidelines or WP Handbook. If code works but could be refactored for clarity, suggest it (but don’t nitpick minor stylistic differences if code meets standard and is clear).

Continuous Improvement: If you find an area of the code that can be improved (refactored for efficiency or clarity) while working on something else, consider addressing it, but communicate. For example, “While fixing this bug I noticed the Shortcodes class function X is inefficient; I refactored it in a separate commit.” This isolates changes and makes them easier to test.

GitHub Issues for larger changes: For major changes (like adding a new content type or overhauling the design), open an issue or discussion first. Outline the plan, get feedback from the team. Because eMINDy is actively used (presumably), we want to ensure big changes align with product goals and don’t inadvertently remove needed functionality.

AI Agent Collaboration: If using an AI agent (like GitHub Copilot or an internal Agentic AI):

Ensure it adheres to our scope rules (see docs/AGENTIC.md for what the agent is allowed to do).

Always double-check AI-generated code for security (the agent might not fully sanitize inputs unless prompted).

The AGENTIC instructions specify the agent’s workflow: it plans changes, makes minimal diffs, and uses commit prefixes. If you’re working alongside an AI, maintain those practices so that human and AI contributions are cohesive. E.g., you might see commits like “theme: adjust pattern spacing [by Agentic]” – keep the style similar in your commits (clear messages, etc.).

AI is a tool, not an authority. If it suggests something that contradicts our standards or you suspect it’s wrong, trust your knowledge or discuss with a team member. For example, if it suggests directly querying the database without $wpdb->prepare, that’s a no-go.

7. Deployment Etiquette

The production site is updated manually via the zip artifacts (as described in docs/RELEASE.md). That means changes merged to main won’t go live until someone triggers a build and deploy.

Therefore, try to bundle related changes into one release to minimize site updates. E.g., instead of deploying one minor CSS tweak every day, accumulate a few minor changes and do a release (unless an immediate hotfix is needed).

Once changes are deployed, sanity-check the live site (the site owner likely will, but as developer you might have access to a staging or production to verify).

If a problem occurs in production after deploy (e.g., a shortcode error due to environment difference), be prepared to act quickly: either revert the change or patch it and help get a new zip out. Downtime or broken functionality can harm user trust, especially on a mental wellness platform where users come for help.

Keep CHANGELOG.md updated with human-readable summaries of changes. This is important for internal tracking and for anyone (site admin) to see what’s new or fixed in each version. The format in CHANGELOG is simple:

Use ## [version] – YYYY-MM-DD, then a bullet list.

E.g., “- Fixed newsletter form not showing success message on error.” or “- Added Spanish translation files (contributed by ...).”

Keep it concise but clear. The changelog is not only for devs but for any stakeholder to see progress.

8. Additional Guidelines

Design Consistency: If your code change has UI implications (front-end), try to match existing design patterns. For example, if adding a new button somewhere, use the same classes the theme uses for buttons (like wp-block-button__link is-style-fill with our theme’s color variables) so it looks consistent. Copy patterns from similar elements.

Feature Flags / Options: Currently, eMINDy doesn’t have a lot of user-configurable options in WP Admin (no settings screen except Polylang/Rank Math). If introducing a feature that some might want to toggle, consider whether to add a theme mod or an option. For instance, if making the number of related posts shown configurable, you could make the shortcode accept an attribute or use a filter or option.

If an option is needed, add an entry in the WP Customizer or an options page under Settings. We have class-emindy-admin.php which currently only handles meta boxes and scripts, but could be extended to add an Admin menu for eMINDy settings if necessary.

Backward Compatibility: This project has some legacy shortcodes for backward compatibility as noted. When changing something, ensure we don’t break existing content:

E.g., if we decided to remove [em_video_player], we’d first ensure no content still uses it (or if some does, maybe auto-migrate it).

Or if you change the output structure of a shortcode, consider if any custom CSS or JS relied on the old structure (some coordination may be needed).

We aim to keep things stable for end users. That said, internal code can be refactored as long as it doesn’t alter expected outputs.

Contact and help: If you’re unsure about anything (e.g., “Should this be implemented in theme or plugin?” or “What is the best way to integrate with Polylang here?”), don’t hesitate to reach out to the team via our Slack/Teams channel or by creating a discussion issue on GitHub. It’s better to get alignment before coding a large feature in the wrong direction.

Following this Development Guide will ensure that our contributions are consistent and high-quality. We are building a platform that people rely on for calm and clarity – our code should embody the same reliability and clarity. Happy coding!
