# AGENTIC INSTRUCTIONS – eMINDy Project

You are working on the **eMINDy** mental wellness platform.  
This repository contains only the WordPress **child theme** and **core plugin** for the live site.

The live site is updated **manually via ZIPs**, never automatically from GitHub.  
Your job is to make safe, high-quality changes to the code in this repo.

---

## 1. Scope – Where you may edit

✅ You are allowed to edit:

- `wp-content/themes/emindy/**`
  - Block templates (`templates/`, `parts/`)
  - Theme styles (`style.css`, `theme.json`, CSS/JS assets)
  - Patterns, block styles, template parts

- `wp-content/plugins/emindy-core/**`
  - Custom post types & taxonomies
  - Shortcodes, schema, test/assessment logic (PHQ-9, GAD-7, etc.)
  - Helper classes, REST endpoints, filters, hooks

❌ Do **NOT** edit (unless explicitly requested by the human):

- `.github/workflows/**` (CI configuration)
- `LICENSE`
- `README.md`
- `AGENTIC.md` (this file)
- Anything outside `wp-content/`

Never add or hardcode credentials, API keys, or secrets.

---

## 2. Project principles

When you change code, always respect these core principles:

1. **Accessibility (a11y)**  
   - Use semantic HTML and proper heading structure.  
   - Ensure keyboard navigation works.  
   - Add `aria-*` attributes when appropriate.

2. **Multilingual (Polylang)**  
   - All user-facing strings must be translatable.  
   - Use `__()`, `_e()`, `_x()`, etc. with the correct text domain.  
   - Do not hardcode English or Persian text directly in templates.

3. **SEO & Schema**  
   - Keep existing schema (HowTo, Article, VideoObject) intact.  
   - Do not break meta tags or structured data already provided by the plugin.

4. **Performance & Clean Code**  
   - Avoid unnecessary queries or heavy loops.  
   - Use WordPress APIs and caching where appropriate.  
   - Follow WordPress coding standards (spacing, escaping, sanitization).

5. **Safety on the live site**  
   - The owner deploys manually using ZIPs built from this repo.  
   - Any change here will eventually run on the production site.  
   - Avoid breaking changes and fatal errors – test your logic carefully.

---

## 3. Typical workflow for you (Agent)

When the human asks for a change:

1. **Understand the request.**  
   - Identify whether it affects the theme, the plugin, or both.

2. **Locate the relevant files.**  
   - For front-end structure/layout → theme (`wp-content/themes/emindy/`).  
   - For CPT logic, assessments, schema → plugin (`wp-content/plugins/emindy-core/`).

3. **Plan the change.**  
   - Describe what files you will touch and why.  
   - Keep changes minimal and focused.

4. **Apply the change.**  
   - Edit only the necessary files.  
   - Add comments where logic is non-trivial.  
   - Ensure all strings are wrapped in translation functions.

5. **Review your diff.**  
   - Check for syntax errors.  
   - Check escaping/sanitization for any new input/output.  
   - Ensure no debug code or var_dumps are left.

6. **Commit with a clear message.**  
   - Use a prefix:
     - `theme: ...` for theme-related changes  
     - `plugin: ...` for plugin-related changes  
     - `fix: ...` or `refactor: ...` when appropriate

The human will then:

- Trigger the **Build theme & plugin ZIPs** workflow in GitHub Actions.  
- Download the ZIPs and deploy them manually to the live site.

---

## 4. Do not do

- Do not write to the live database.  
- Do not attempt to manage hosting or deployment directly.  
- Do not add external dependencies without explaining and confirming.  
- Do not change project branding, tone, or content structure unless asked.

Always prioritize stability, clarity, and maintainability.
