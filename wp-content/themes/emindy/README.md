eMINDy Child Theme

The eMINDy child theme provides the presentation layer for the eMINDy mental wellness platform. It is built as a child of WordPress’s Twenty Twenty-Five block theme, inheriting its core layout features and extending them with custom templates, patterns, and styles tailored to a calm, accessible design. This theme handles the site’s appearance, including page structure, global styles (colors, fonts), dark mode support, and integration of dynamic content provided by the eMINDy Core plugin.

Requirements and Installation

Parent Theme: Ensure the WordPress default theme Twenty Twenty-Five (twentytwentyfive) is installed. The eMINDy theme is a child theme of Twenty Twenty-Five and will not activate without it.

WordPress: Requires WordPress 6.3+ (to have Twenty Twenty-Five and full Site Editor support).

Dependencies: While not strictly required, it’s strongly recommended to use the eMINDy Core plugin alongside this theme for full functionality (custom content types, shortcodes, and dynamic features).

Installation Steps:

Upload or copy the emindy theme folder into wp-content/themes/ (if using a ZIP, install via Appearance → Themes → Add New and upload the child theme zip).

Verify that the parent theme Twenty Twenty-Five is present. If not, download it from the WordPress repository.

Activate eMINDy child theme from Appearance → Themes in the WordPress admin.

(Optional) Install and activate the eMINDy Core plugin (wp-content/plugins/emindy-core). Many of the theme’s templates include dynamic placeholders (shortcodes) that rely on the plugin’s functionality.

After activation, the child theme will automatically load its custom templates for relevant pages and content types. You may use the Site Editor (Appearance → Editor) to view and adjust templates, or add/edit patterns as needed.

Features of the Child Theme

Block Theme Structure: eMINDy is a block-based theme leveraging Twenty Twenty-Five’s framework. It provides full-site editing templates and template parts. The theme defines custom templates for the eMINDy custom post types and special pages (e.g., custom templates for Exercises, Videos, Articles archives and singles, an Assessments page, Assessment Result page, Newsletter signup page, etc.).

Patterns: A set of ready-made block patterns are included under patterns/ to help build consistent layouts:

Library Hub Patterns: Pre-designed sections for Video Library, Exercise Library, Article Library, and an overarching “All Libraries” page. These patterns include hero sections, search bars, filter controls, and content grids to present content in an engaging, consistent way.

Front Page and Others: A custom front page pattern (front-page-emindy) featuring a welcoming hero, key benefits, featured content sections, and calls-to-action (tailored to encourage first-time visitors to explore or start a quick practice).

Global Styles and Theming: The theme defines a custom color palette and font sizes via theme.json, reflecting a calm and accessible aesthetic:

Colors like deep blue, teal, gold, etc., are provided for consistency with the eMINDy brand.

Custom CSS variables and styles in style.css implement both light and dark modes. Users can toggle dark mode using a button (🌙) in the header; the theme’s CSS and JS (assets/js/dark-mode-toggle.js) handle switching color variables.

Typography is adjusted for readability, and extra CSS classes ensure high contrast and accessible focus indicators (for example, a clearly visible focus outline on interactive elements).

Accessibility (a11y): The child theme follows a11y best practices:

It includes a “skip to content” link (output via a wp_body_open hook) for screen reader and keyboard users to bypass navigation
GitHub
.

The header and menus use semantic markup from block patterns, and focus styles are enhanced (outlined in theme CSS) for keyboard navigation clarity
GitHub
GitHub
.

All user-facing strings are translation-ready (text domain: emindy), and the theme supports right-to-left (RTL) layouts. When a visitor switches to Persian (Farsi) or any RTL language, the layout and text adjust accordingly (the theme’s CSS includes RTL tweaks such as adjusted padding/margins for RTL reading order
GitHub
).

Integration with Core Plugin: Many templates in the theme automatically include dynamic content from the eMINDy Core plugin:

Exercise single pages automatically insert a guided steps player and steps list.

Video single pages automatically show a chapters list (so users can jump to segments of the video) after the video content.

The Assessments page template provides a framework for PHQ-9 and GAD-7 self-check tools (which the plugin powers via shortcodes).

The Newsletter page template includes the newsletter sign-up form shortcode.

These integrations mean content editors don’t need to manually add shortcodes to each page – the theme templates and plugin Content Inject feature ensure they appear where needed.

Customization and Extension: Developers can modify this child theme just as any other:

Use the WordPress Site Editor to adjust block templates or add new templates. The child theme’s provided templates can be copied and edited from the Site Editor UI if minor changes are needed.

Additional custom CSS can be added via the Site Editor or by editing style.css. The theme’s design tokens (CSS variables defined for colors, spacing, etc.) can be reused to maintain consistency.

If deeper changes are required, you can edit or add PHP template parts in the theme directory (for example, adding a new template for a custom page or altering the header/footer parts).

Because eMINDy is a child theme, it inherits all functionality of Twenty Twenty-Five (such as responsiveness, block support, etc.), allowing you to focus on overriding styles or adding specific markup without rewriting basic theme features.

Parent Theme Link: For reference, see the Twenty Twenty-Five theme page for documentation on base features. The eMINDy child theme doesn’t remove any core features of the parent, but simply adds on with styling and content types specific to the platform.

Activation & Usage

After activating the eMINDy theme (and the core plugin), navigate to the site. Key pages and features should be available (assuming proper configuration):

The homepage (Front Page) will show the custom hero and featured sections as defined by the Front Page pattern.

Navigation menus will include the language switcher, search, and a “Start Here” call-to-action by default (you can edit these in the Site Editor navigation block).

Try navigating to an Exercise, Video, or Article content piece (you may need to create some content first). The child theme’s templates for these will display the content along with any dynamic blocks (e.g., exercise steps or video chapters) automatically.

If something appears missing (e.g., newsletter form or assessment pages), ensure the corresponding plugin features are configured and the necessary pages exist (see docs/configuration.md for setting up pages like “Newsletter” and “Assessments”).

Support & Localization

This theme is fully internationalized:

All strings in theme PHP files and patterns are wrapped in __() or related translation functions (text domain emindy). A starter POT file is provided in languages/emindy.pot
GitHub
. You can use wp-cli i18n make-pot to regenerate this if needed.

Right-to-left support is built-in. Switching the site language to a RTL language (like Persian) will automatically load WordPress’s RTL styles and the theme’s CSS is written to accommodate RTL layouts (e.g., resetting certain alignment/padding).

The theme uses units and font sizing that adapt well across devices, and it respects user preferences (e.g., it sets the color-scheme CSS property so that form controls render appropriately in dark mode).

For any feature that seems to be not working or if you need to extend the theme, please also consult docs/architecture.md (which details how the theme and plugin are structured) and docs/development.md (for coding guidelines specific to this project).
