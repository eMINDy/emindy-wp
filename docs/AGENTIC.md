AGENTIC INSTRUCTIONS – eMINDy Project

These instructions define the scope and rules for the AI development agent working on the eMINDy WordPress project. The agent’s goal is to make safe, high-quality improvements to the codebase. It should follow the same standards as human contributors and interact with the team through pull requests.

1. Scope – Where the Agent May Edit

✅ Allowed: The agent has write access to all parts of the codebase that pertain to the theme and plugin development. It may modify:

Theme files: wp-content/themes/emindy/** (templates, parts, styles like style.css, theme.json, CSS/JS assets, patterns, etc.)

Plugin files: wp-content/plugins/emindy-core/** (custom post types & taxonomies, shortcodes, schema output, assessment logic such as PHQ-9/GAD-7, helper classes, REST API endpoints, hooks and filters, etc.)

CI Workflow files: .github/workflows/** (continuous integration configurations, build scripts, etc., for example to update or add build/test steps). Changes to CI should be made cautiously and only to improve automation or fix broken processes.

❌ Forbidden (without explicit human approval): The agent should NOT edit certain areas unless a human explicitly requests it, to avoid unintended side effects or breaches of project policy:

Repository meta-files: LICENSE, README.md, and this AGENTIC.md instructions file itself should not be changed by the agent. (These are maintained by humans.)

Outside wp-content: Any core WordPress files or other server configuration files outside the theme/plugin directories (e.g. WordPress settings, root configuration) are off-limits.

Credentials/Secrets: Never add or modify credentials, API keys, or any secrets. The agent must not hardcode sensitive information anywhere in the code.

By sticking to these boundaries, the agent ensures it only affects the intended parts of the system (theme and plugin code, plus CI) and nothing beyond the project’s scope.

2. Project Principles

When making changes, the AI agent must uphold the project’s core principles to maintain quality and consistency:

Accessibility (a11y): Use semantic HTML elements and proper heading structure. Ensure all interactive elements are keyboard-navigable. Add appropriate ARIA attributes or screen-reader text for UI controls so that the site remains accessible to all users.

Multilingual Support: All user-facing strings must be translatable. Use WordPress internationalization functions (__(), _e(), _x(), etc.) with the correct text domain (use emindy for theme and emindy-core for plugin strings). The agent should never hardcode English or Persian text directly into templates or plugin outputs – always wrap text for translation.

SEO & Schema: Preserve and respect existing SEO metadata and schema markup. For example, if the plugin outputs structured data (JSON-LD for HowTo, Article, VideoObject, etc.), ensure those remain intact or are updated correctly. Do not remove or break <meta> tags, Open Graph tags, or structured data provided by the theme/plugin that search engines rely on.

Performance & Clean Code: Avoid introducing expensive operations. Do not add heavy database queries inside loops or un-cached routines that could slow down the site. Use WordPress APIs and caching mechanisms when appropriate. Follow WordPress coding standards for all PHP, JavaScript, HTML, and CSS (proper indentation, escaping output, sanitizing inputs, etc.). Keep the code style consistent (use the project’s existing patterns for naming, spacing, and formatting).

Stability (Safety for Live Site): Remember that changes in this repository will be deployed to a live production site manually. Any bug introduced by the agent could affect real users. Test changes thoroughly to avoid breaking the site. Do not introduce fatal errors or breaking changes. When in doubt, err on the side of caution and ask for guidance (via comments on a pull request) rather than making risky changes.

These principles ensure that any code the agent produces is accessible, localized, search-engine friendly, performant, and safe for production use.

3. Workflow for the AI Agent

When a human requests a feature or bug fix, the AI agent should use the following workflow to implement changes in a controlled manner:

Understand the request: First, the agent should carefully read the human’s request or issue description. Determine which part of the project it concerns (theme vs. plugin, or both). Clarify requirements if needed (the agent can include a question in the pull request description if something is ambiguous).

Locate relevant files: Identify which files are related to the change.

If it’s about front-end layout or styling, it will likely involve the theme files (wp-content/themes/emindy/...).

If it’s about functionality like custom post types, data handling, assessments, or back-end logic, it will involve the plugin files (wp-content/plugins/emindy-core/...).
Sometimes a change may require edits in both (for example, adding a new feature might need a template change in the theme plus a new shortcode or API in the plugin).

Plan the change: Before writing code, the agent should outline a brief plan. Which files will be modified? What is the minimal change required to implement the request? The agent should aim to make focused, minimal changes that solve the problem without affecting unrelated parts. This plan can be summarized in the eventual pull request description to inform reviewers.

Apply the changes: Implement the plan by editing the necessary files.

Only modify the parts of code necessary for the fix/feature. Do not gratuitously refactor other areas unless it’s part of the request or improves clarity/safety.

Adhere to coding standards while editing (formatting, proper escaping and sanitization, comments for complex logic, etc.).

Include code comments for any non-obvious logic or calculations to help human reviewers understand the change. For instance, if adding a new shortcode or filter, document what it’s for.

Wrap any new user-facing text in translation functions as noted (never assume a single language).

Self-review the diff: After making changes, the agent should simulate a code review on its own work:

Check for any syntax errors or typos.

Make sure any data output is properly escaped, and any input is validated/sanitized.

Ensure no stray debugging statements (var_dump, console.log, temporary test code) are left in the code.

Verify that the change doesn’t break other functionality (for example, if modifying a shared function, ensure other uses of it still work as expected).

Commit with a clear message: Commit the changes in a new branch. Use a descriptive commit message following the project’s convention:

Prefix the message with the area of change, such as theme: ... for theme-related updates, or plugin: ... for plugin changes.

If it’s a bug fix or refactor, you can prefix with fix: ... or refactor: ... (or feat: ... for a new feature), possibly in addition to the component. For example:

theme: fix responsive layout issue on homepage hero

plugin: feat add new shortcode for user progress

Keep the commit message concise but specific. If the change addresses a GitHub issue or ticket, mention the issue number (e.g., “fixes #123”) to link it.

Open a Pull Request: Push the branch to the repository (the agent should never push directly to the main branch). Then create a pull request targeting the main branch:

In the PR title, summarize the change (similar to the commit message).

In the PR description, provide context: explain why the change was made and what it does. If it’s fixing a bug, describe the root cause and how you fixed it. If it’s a new feature, briefly describe how it works. Include any important notes for reviewers (e.g., “This touches the email sending logic, please pay extra attention to sanitization there”).

If the change is not straightforward, the agent can use this space to ask for specific feedback or highlight areas of uncertainty. This is the proper way for the agent to interact with human developers – via PR discussions.

Respond to feedback: Once the pull request is open, the human maintainers will review it. The agent should be prepared to make additional commits to the same branch if changes are requested. Always address code review comments promptly and politely. If a human suggests a different approach, the agent should incorporate that or discuss it constructively in the PR.

After these steps, the human maintainers will handle merging the pull request once it’s approved. Deployment to production is done manually by the team: typically, after a PR is merged, the maintainer will run the “Build eMINDy theme & plugin ZIPs” GitHub Action workflow to package the updated theme and plugin, then download those ZIP files and upload them to the live WordPress site. (The agent does not deploy to the live site directly.) The agent’s responsibility ends with proposing the code change via PR.

4. Forbidden Actions (Things the Agent Must Not Do)

To ensure safety and maintain trust, the AI agent must avoid certain actions entirely (unless explicitly told otherwise by a project maintainer):

No direct database or live-site changes: The agent should never attempt to connect to or modify the production database or any live site content. All changes go through code only. (For example, do not write code that tries to fetch or push data to the live site’s database or API endpoints outside the scope of the WordPress codebase.)

No managing infrastructure or deployment: The agent is not allowed to make changes related to server configuration, hosting settings, or perform deployments on its own. It should not create or modify files like Docker configs, .htaccess, or anything related to how the site is hosted, unless explicitly asked. Deployment is a manual human task.

No adding external dependencies without approval: If the agent believes a new PHP library or WordPress plugin or an external API is needed, it must first seek confirmation. Do not bloat the project with new dependencies (composer packages, external scripts) without discussion. Often, a simpler in-house solution is preferable for maintainability.

No altering project identity or content structure: The agent should not change the project’s branding, tone of voice, or content strategy. For example, do not rename significant user-facing terms, modify site logos, or restructure content types/pages, unless the request specifically involves doing so.

Do not override human oversight: The agent should always operate under the review system. It must not merge its own pull requests or make changes outside the workflow described. The agent’s role is to assist, not to take full control.

By refraining from these actions, the agent will focus on writing quality code and leave critical decisions (infrastructure, dependencies, content direction, final merges) to the human project owners. This ensures stability and maintainability of eMINDy. Always prioritize clarity, stability, and maintaining the team’s trust.

Note: These AGENTIC guidelines should be maintained as a single source of truth. You can keep this content in docs/AGENTIC.md for reference, and update it as needed when the agent’s role or permissions change. (If a copy exists at the repository root AGENTIC.md for the agent’s use, ensure both are kept in sync or simply link one to the other.)
