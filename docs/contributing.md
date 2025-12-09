Contributing to eMINDy

Thank you for your interest in contributing to eMINDy, a mental wellness platform built on WordPress. We welcome contributions from the community and team members alike. This guide will help you get started with reporting issues, making code changes, and following our project’s best practices. By adhering to these guidelines, you ensure that your contributions are effective, consistent, and easy to integrate.

Filing Issues and Feature Requests

If you encounter a bug or have an idea for a new feature, please open a GitHub Issue to let us know. A good issue report or feature request includes:

A clear title and description: Summarize the problem or suggestion in the title, and provide detailed context in the description. Explain what is happening (or what you propose to happen), and why it’s important.

Steps to reproduce (for bugs): If reporting a bug, list the exact steps to reproduce the issue. Include what you expected to happen versus what actually happened. If possible, provide screenshots or error messages/logs.

Environment details: Mention relevant details like the WordPress version, browser, or device where you saw the issue. Also note if certain plugins (like Polylang or SEO plugins) were active, if relevant – since our project integrates with those.

Label appropriately: If you have permissions to add labels, label the issue as a bug, enhancement, question, etc., to help maintainers triage it. If not, maintainers will categorize it.

Avoid duplicates: Before opening a new issue, search existing issues (open and closed) to see if someone has already reported something similar. If so, you can add further details or a thumbs-up to that issue instead of creating a duplicate.

Security issues: If you believe you’ve found a security vulnerability, do not post it publicly. Instead, please contact the maintainers privately (you can find contact info in the README or by emailing the repository owner) so we can address it responsibly.

By providing thorough information in your issue, you help us address it faster. We aim to respond to new issues promptly and will ask for clarification if something isn’t clear.

Project Setup and Development Workflow

To contribute code, you’ll need a local copy of the project and a WordPress environment to test your changes:

Setting up the project: Follow the steps in our Installation & Setup guide to get eMINDy running locally. In brief, you should have a WordPress site (version 6.4+), install the Twenty Twenty-Five parent theme, then install and activate the eMINDy child theme and eMINDy Core plugin from this repository. Make sure to also install Polylang (for multi-language support) if your changes involve localization, and any other recommended plugins (like Rank Math for SEO, if relevant).

Working on an issue: If you’re addressing an open issue, comment on the issue to let others know you’re working on it (and avoid duplicate work). If it’s a large feature or change, it’s often a good idea to first discuss it in the issue or in a GitHub Discussion to confirm it aligns with the project’s goals. The maintainers can provide guidance before you invest a lot of time.

When you’re ready to start coding:

Fork the repository (if external): If you are an external contributor without direct write access, fork the eMINDy repository to your own GitHub account. Work on your fork and then open a pull request to the main repo (this is the typical GitHub workflow for outside contributions).

Or create a feature branch (if internal): If you are a core team member or have write access to the repository, create a new branch on the main repository rather than working on main directly. Give the branch a descriptive name that reflects the issue or feature. For example: feature/quiz-redesign or bugfix/newsletter-email-validation.

Branch naming: Use kebab-case (hyphen-separated) or another consistent style for branch names. Include a hint of the scope: e.g., theme/header-styling-fix or plugin/new-shortcode-emotion-tracker. This makes it easier to identify branches in the repository.

Keep changes focused: Try to make one branch per feature or fix. Don’t combine unrelated changes in the same pull request. This way, each PR addresses a single concern and is easier to review and test.

Coding Standards and Style Guide

We follow WordPress coding standards and some project-specific guidelines to ensure all code is clean, readable, and maintainable. Here are the key points:

General formatting: Adhere to the official WordPress Coding Standards. For PHP code, that means using tabs for indentation, and following naming conventions (snake_case for functions & variables, Capitalized_Class_Names with underscores for classes, etc.). For JavaScript, use 2-space indentation and camelCase for variables (UpperCamelCase for constructor functions). For CSS, use 2-space indentation and BEM-like class naming or clear, scoped class names (we often prefix classes with .em- to avoid conflicts).

Sanitization and escaping: Always sanitize user input and escape output. This is critical for security. For example, use sanitize_text_field(), absint(), sanitize_email(), etc., on data going into the database or being processed. When outputting variables in HTML, use escaping functions like esc_html(), esc_attr(), wp_kses() (for larger HTML content), etc. Our rule is: sanitize early, escape late. The codebase should not produce any PHP notices or warnings; if you see one (e.g., “undefined index”), fix the code to handle that case.

Translation (i18n): eMINDy is a bilingual project (English and Persian), so every user-facing string must be translatable. Wrap all strings in translation functions with the correct text domain. In the theme files use __( 'Text', 'emindy' ) (or related functions like _e, esc_html__, etc.), and in the plugin use the 'emindy-core' text domain. Even small bits of text like button labels, form error messages, alt text for images, etc., should go through these functions. If a string has context or could be ambiguous, use _x( 'string', 'context', 'text-domain') to provide translator context.

Accessibility (a11y): Keep HTML semantic and accessible. Use proper HTML elements (e.g., <button> for buttons, <label> for form labels tied to inputs). Include ARIA attributes or screen-reader text (<span class="screen-reader-text">...</span>) where appropriate for assistive technologies. Ensure keyboard navigation isn’t broken by your changes (for example, if you create a custom modal or dynamic content, manage focus accordingly). We aim for WCAG compliance as much as possible.

Performance: Be mindful of performance. Avoid heavy computations or database queries on each page load. If you need to fetch complex data, consider using WP transients or caching results when appropriate. Use functions like get_posts() with query args that minimize load (e.g., no_found_rows => true for queries where pagination isn’t needed). Only load assets (scripts/styles) when needed: for example, if you add a new script, enqueue it on the specific pages or conditions that require it, rather than everywhere.

Coding patterns: Follow the structure and patterns already used in the project. For instance, if adding a new custom post type or taxonomy, look at how existing ones are implemented in the plugin and try to follow the same approach (same file organization, naming, registration method). For front-end changes, if adding a new block pattern or page template, maintain consistency with existing templates. This makes the codebase internally consistent.

Documentation and comments: Write clear comments for complex logic. If you introduce a new function or class, include a PHPDoc block (with @param and @return tags explaining usage). Inline comments are encouraged to clarify non-obvious code sections. For example, if you use a particular filter or do a workaround for a known issue, comment why you did it. This helps future contributors (and the AI agent) understand the reasoning behind the code.

Following these style guidelines ensures that the code remains easy to read and maintain. We do run code reviews (and in the future, possibly automated linters) to catch style or security issues, but it saves time if you format and sanitize correctly from the start.

Commit Message Guidelines

We use informative commit messages so that everyone can understand the history of changes. Please format your commit messages like so:

Prefix with context: Start the commit message with a tag indicating the area of the code or the type of change. For example:

theme: ... for changes in the theme (front-end templates, style, etc.)

plugin: ... for changes in the plugin (back-end logic, shortcodes, data handling, etc.)

You can also use conventional commit prefixes like fix: ..., feat: ..., refactor: ... in addition to or instead of the above when appropriate. e.g., fix: plugin – resolve PHP notice on null meta value or feat: theme – add dark mode toggle.

Be concise but specific: After the prefix, clearly state the change. For example:

plugin: fix undefined index in newsletter signup

theme: adjust styles for RTL language support

feat: plugin – add [em_mood_tracker] shortcode for mood tracking

Issue references: If your commit addresses an issue, include a reference like Fixes #123 or Refs #456 in the commit message (usually at the end). GitHub will automatically link the issue and can close it if you use keywords like “Fixes”. For example: plugin: validate email input in newsletter form (fixes #42) will close issue #42 when merged.

One commit per logical change: It’s okay to have multiple commits in a pull request, but try to keep each commit focused. This makes it easier to review commit-by-commit if needed. Squash commits if they are “fix up” commits for the same issue, or leave them separate if they address different aspects (maintainers may also squash when merging, depending on project preference).

Clear commit messages help reviewers during the pull request process and aid future developers (or the AI agent) in understanding why a change was made by reading the git history.

Pull Requests and Code Review

Before you open a Pull Request (PR), double-check your changes: did you adhere to the coding standards? Did you test the functionality in both languages and in different scenarios (as applicable)? Once you’re confident, follow these PR guidelines:

Pull Request scope: Open a PR for each discrete change or feature. Don’t mix unrelated fixes or features in the same PR. If your branch had multiple loosely related changes, consider splitting them into separate PRs. This makes reviews faster and more focused.

PR Title and Description: Give the PR a clear title that sums up the change (often you can reuse the commit message title). In the description, explain the what and why of the change in a few sentences. If it’s a bug fix, describe the root cause of the bug and how your change fixes it. If it’s a new feature, describe how it works and mention any new UI elements or configuration. You should also list any follow-up steps needed after merging (e.g., “After merging, run the translation POT file generator” or “This will require re-saving permalinks in WP admin due to rewrite rule changes” if applicable).

Link issues: If your PR relates to or closes an issue, mention it in the description (e.g., “Closes #123”). This helps maintainers and testers know what context to look at.

Ensure CI/tests pass: Our project may have continuous integration checks (like a build or code linting via GitHub Actions). Make sure your branch passes these checks. If a check fails, troubleshoot the issue (it might be a code style issue or something that you need to adjust). If you’re unsure why a check is failing, ask for help in the PR comments.

Requesting review: Once your PR is ready, add reviewers (if you have permission) or ping the maintainers by commenting. The eMINDy team requires at least one approval from a maintainer or core team member before merging any PR. Be patient after submitting – maintainers will review as soon as they can.

During the review process, be open to feedback:

Code review feedback: It’s common for reviewers to suggest changes or ask questions. This might include style tweaks, asking for more comments, pointing out edge cases, or suggesting different approaches. Don’t be discouraged – this review helps maintain quality. Address the feedback by making new commits to your branch. You can respond in the PR comments to explain your thinking if needed.

Don’t take it personally: All code review comments are about the code, not you. We strive to be constructive and helpful. If something isn’t clear, feel free to ask for clarification. We’re all working toward the same goal of improving the project.

Approval and merging: Once the PR is approved by at least one maintainer (and any required checks are green), a maintainer will merge it. Typically, contributors should not merge their own PRs even after approval – this ensures one last look and gives maintainers control over the main branch’s history. (There may be exceptions for core team members, but in general we prefer the author of a PR is not the one to press the merge button.) We usually use “Squash and Merge” or “Rebase and Merge” to keep the commit history clean, unless your PR has multiple well-separated commits, in which case a normal merge might be used.

Post-merge: After merging, our workflow for deployment involves manually building and deploying the theme/plugin (see the Release guide). If your change is significant, you might be asked to help test it on a staging site or monitor the next production deployment for any issues.

By following these PR and review practices, we ensure that every change to eMINDy is vetted and of high quality. Code review is a great opportunity to learn and to maintain coding discipline.

Community and Communication

We encourage an open, collaborative environment:

Discuss major changes first: If you plan a big feature or architectural change, please open an issue or discussion thread to talk it through before writing a lot of code. This can save time by ensuring the idea is sound and fits the project’s direction. The maintainers can provide early feedback, suggest solutions, or give a go-ahead.

Ask questions: If you’re unsure about anything (where to find a part of the code, why something was implemented a certain way, how to approach a problem), feel free to ask. You can use GitHub Discussions (if enabled) or ask in an issue or even within a PR comment. It’s better to seek clarification than to guess and possibly head down the wrong path. The eMINDy team is friendly and will guide you.

Be respectful: Mental wellness is the focus of our project, and we value a positive and respectful tone in all interactions. When giving feedback or asking for changes, be kind and constructive. Likewise, if you receive feedback, interpret it in good faith. We have a shared goal of improving the platform to help end users.

AI assistance: If you use AI coding assistants (such as GitHub Copilot or others) while contributing, that’s fine – but you are responsible for the code you submit. Make sure to review AI-generated suggestions for security (especially sanitization and escaping) and correctness. All contributions, whether human- or AI-written, will be held to the same standard in code review. Our docs/AGENTIC.md (AI Agent Guidelines) outlines how AI is used in this project. In short, AI can help write code, but human oversight is key.

By following these contributing guidelines, you will help maintain the high quality of the eMINDy project and make the collaboration process smooth. We appreciate every contribution, whether it’s a bug report, a small fix, or a major feature. Thank you for helping improve eMINDy!
