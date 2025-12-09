# eMINDy Child Theme

The **eMINDy child theme** provides the presentation layer for the eMINDy mental wellness platform. It is built as a child of WordPress’s **Twenty Twenty-Five** block theme, inheriting its core layout features and extending them with custom templates, patterns, and styles tailored to a calm, accessible design.

This theme handles the site’s appearance, including:

- Page structure
- Global styles (colors, fonts, spacing)
- Dark mode support
- Integration of dynamic content provided by the **eMINDy Core** plugin

---

## Requirements and Installation

### Prerequisites

- **Parent Theme**  
  Ensure the WordPress default theme **Twenty Twenty-Five** (`twentytwentyfive`) is installed.  
  The eMINDy theme is a **child theme** of Twenty Twenty-Five and will not activate without it.

- **WordPress Version**  
  Requires **WordPress 6.3+** (to have Twenty Twenty-Five and full Site Editor support).

- **Dependencies (Recommended)**  
  While not strictly required, it is **strongly recommended** to use the **eMINDy Core plugin** alongside this theme for full functionality (custom content types, shortcodes, and dynamic features).

### Installation Steps

1. **Upload the Theme**
   - Upload or copy the `emindy` theme folder into `wp-content/themes/`.  
   - If using a ZIP, install via:  
     `Appearance → Themes → Add New → Upload Theme` and upload the child theme ZIP.

2. **Verify Parent Theme**
   - Confirm that the parent theme **Twenty Twenty-Five** is present.
   - If not, download it from the official WordPress theme repository.

3. **Activate the Child Theme**
   - Go to `Appearance → Themes` in the WordPress admin.
   - Activate the **eMINDy** child theme.

4. **(Optional but Recommended) Install the Core Plugin**
   - Install and activate the **eMINDy Core plugin** in `wp-content/plugins/emindy-core`.  
   - Many of the theme’s templates include dynamic placeholders (shortcodes) that rely on this plugin’s functionality.

5. **Adjust Templates via Site Editor (Optional)**
   - After activation, the child theme automatically loads its custom templates for relevant pages and content types.
   - Use the **Site Editor** via `Appearance → Editor` to:
     - View and adjust templates
     - Add or edit patterns as needed

---

## Features of the Child Theme

### Block Theme Structure

The eMINDy theme is a **block-based child theme** that leverages Twenty Twenty-Five’s framework. It provides **full-site editing** templates and template parts.

Key aspects include:

- Custom templates for the eMINDy custom post types:
  - Exercises
  - Videos
  - Articles
- Custom archive and single templates for:
  - Exercises
  - Videos
  - Articles
- Special page templates such as:
  - Assessments page
  - Assessment Result page
  - Newsletter signup page
  - Other content hubs related to eMINDy’s mental wellness flows

---

### Patterns

A set of ready-made **block patterns** is included under the `patterns/` directory to help build consistent layouts across the site.

#### Library Hub Patterns

Pre-designed sections for:

- **Video Library**
- **Exercise Library**
- **Article Library**
- An overarching **“All Libraries”** page

These patterns typically include:

- Hero sections
- Search bars
- Filter controls
- Content grids

They are designed to present content in an engaging, consistent way that fits the eMINDy brand and information architecture.

#### Front Page and Other Key Pages

A custom **front page pattern** (`front-page-emindy`) provides:

- A welcoming hero area
- Key benefits and value propositions
- Featured content sections
- Clear calls-to-action (e.g., “Start Here” or “Try a Quick Practice”)

This pattern is tailored to encourage first-time visitors to explore the platform and start a quick self-help practice.

---

### Global Styles and Theming

The theme heavily relies on **theme.json** and a set of CSS variables to maintain a calm, accessible visual identity.

Key elements:

- **Color Palette**
  - Colors like deep blue, teal, gold, and related tones are defined to match the **eMINDy brand**.
  - These colors are registered via `theme.json` and reinforced through CSS variables in `style.css`.

- **Light & Dark Mode**
  - Custom CSS variables and styles in `style.css` implement both **light** and **dark** modes.
  - Users can toggle dark mode using a button (🌙) in the header.
  - The theme’s CSS and JavaScript (`assets/js/dark-mode-toggle.js`) handle switching color variables.

- **Typography**
  - Typography is adjusted for readability:
    - Comfortable font sizes
    - Line heights suitable for long reading
  - Extra CSS classes provide:
    - High contrast where needed
    - Accessible focus indicators
    - Good legibility across devices

---

### Accessibility (a11y)

The child theme follows **a11y best practices** to support all users, including those using assistive technologies.

#### Skip to Content

- A **“skip to content”** link is included and output via a `wp_body_open` hook.
- This allows screen reader and keyboard users to **bypass navigation** and jump directly to the main content.  
- (Implementation details and further notes are available in the source code on GitHub.)

#### Semantic Header and Menus

- The **header** and **navigation menus** use semantic markup derived from WordPress block patterns.
- Focus styles are enhanced in the theme CSS, providing:
  - Clear focus outlines on interactive elements
  - Better **keyboard navigation clarity**  
  - (Focus styles and improvements can be reviewed in the theme’s CSS on GitHub GitHub.)

#### RTL and Translation

- All user-facing strings are **translation-ready**:
  - Text domain: `emindy`
- The theme supports **right-to-left (RTL)** layouts:
  - When a visitor switches to **Persian (Farsi)** or another RTL language, the layout and text adjust accordingly.
  - The theme’s CSS includes RTL tweaks such as:
    - Adjusted padding and margins
    - Alignment changes to support RTL reading order  
    - (RTL styling details are visible in the theme’s stylesheets on GitHub.)

---

### Integration with the eMINDy Core Plugin

Many templates in the child theme automatically include dynamic content supplied by the **eMINDy Core plugin**. This reduces manual configuration for editors.

Examples:

- **Exercise Single Pages**
  - Automatically insert a **guided steps player** and **steps list**.

- **Video Single Pages**
  - Automatically show a **chapters list** after the video content.
  - This allows users to **jump to specific segments** of the video.

- **Assessments Page Template**
  - Provides a layout framework for self-check tools such as:
    - **PHQ-9**
    - **GAD-7**
  - The plugin powers these via shortcodes, which are placed by templates or via the Content Inject feature.

- **Newsletter Page Template**
  - Includes the **newsletter sign-up form shortcode**.

Because of these integrations:

- Content editors **do not need to manually add** shortcodes to every page.
- Theme templates and the plugin’s **Content Inject** feature ensure the correct content appears where needed, based on context and post type.

---

### Customization and Extension

Developers and site builders can customize this child theme like any other WordPress block theme.

#### Using the Site Editor

- Use the **WordPress Site Editor** (`Appearance → Editor`) to:
  - Adjust existing block templates
  - Add new templates
  - Manage template parts (header, footer, etc.)
- The child theme’s provided templates can be **copied and edited** directly from the Site Editor if only minor layout changes are needed.

#### Custom CSS and Design Tokens

- Additional custom CSS may be added:
  - Via the Site Editor’s **Additional CSS** area
  - Or by editing `style.css` in the theme
- The theme defines **design tokens** (CSS variables for colors, spacing, etc.) that can be reused to maintain visual consistency across customizations.

#### Extending Templates via PHP

- For deeper changes, you can edit or add PHP template parts directly in the theme directory, for example:
  - Creating new templates for special pages
  - Adjusting header/footer behavior beyond what the Site Editor exposes

Because eMINDy is a child theme:

- It **inherits all functionality** of Twenty Twenty-Five (responsiveness, block support, etc.).
- You can focus on:
  - Overriding styles
  - Adding project-specific markup
  - Integrating with the eMINDy Core plugin  
  without having to re-implement basic theme features.

---

### Parent Theme Reference

For further details on the base features provided by the parent theme:

- See the **Twenty Twenty-Five** theme documentation page (WordPress.org).

The eMINDy child theme:

- Does **not remove** any core features of Twenty Twenty-Five.
- Adds:
  - Custom styling
  - Content-type-specific templates
  - Accessibility enhancements
  - Integrations tailored to the eMINDy platform.

---

## Activation & Usage

After activating the **eMINDy child theme** (and the **eMINDy Core plugin**, if installed), navigate through the site to verify key pages and flows.

You should see:

- **Homepage (Front Page)**
  - Displays a custom hero section and featured content areas based on the **Front Page pattern**.
  - Encourages new visitors to explore and start an exercise or video.

- **Navigation**
  - Menus typically include:
    - Language switcher
    - Search
    - A prominent **“Start Here”** call-to-action
  - These can be edited via the **Site Editor** navigation block.

- **Custom Post Types (Exercise, Video, Article)**
  - Once you create content for these types:
    - Exercise templates will display content plus dynamic blocks, such as **exercise steps**.
    - Video templates will show the main content followed by **video chapters** and related supporting information.
    - Article templates will present long-form content with consistent styling.

If something appears missing (for example, the newsletter form or assessment pages):

- Confirm that the corresponding plugin features are configured.
- Ensure that the necessary pages exist and are correctly assigned.  
  For detailed setup, refer to:
  - `docs/configuration.md` (for setting up pages like **“Newsletter”** and **“Assessments”**).

---

## Support & Localization

This theme is built to support **internationalization**, **localization**, and **responsive behavior** across languages and devices.

### Translation & Text Domain

- All strings in theme PHP files and patterns are wrapped in translation functions such as `__()`, `_e()`, etc.
- Text domain: **`emindy`**
- A starter POT file is provided at:
  - `languages/emindy.pot` (GitHub)
- You can regenerate translation templates using WP-CLI:
  ```bash
  wp i18n make-pot . languages/emindy.pot

RTL Support

Right-to-left layouts are fully supported:

Switching the site language to an RTL language (e.g., Persian/Farsi) triggers WordPress’s RTL styles.

The theme’s CSS is designed to accommodate RTL layouts via:

Adjusted alignments

Flipped margins and paddings where necessary

Other RTL-specific tweaks

System Preferences and Dark Mode

The theme uses units and font sizing that adapt well across devices.

It respects user preferences where possible, including:

Using the color-scheme CSS property so that:

Form controls

Other browser-native UI elements
render appropriately in dark mode.

Further Documentation

If any feature appears not to be working as expected, or if you need to extend the theme, consult the project documentation:

docs/architecture.md
Describes how the theme and eMINDy Core plugin are structured and how they interact.

docs/development.md
Provides coding guidelines, conventions, and development workflows specific to this project.

For implementation details, reference the theme and plugin source code in the project’s GitHub repository.
