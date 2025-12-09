# Wiki Agent Playbook — eMINDy WordPress Project

This playbook defines how an AI development agent should work with the **eMINDy WordPress Wiki**.

It extends the rules in `AGENTIC.md` and focuses specifically on **when and how to update wiki pages**, so that documentation stays accurate, consistent, and safe for a mental-health project.

---

## 1. Purpose of this Playbook

The Wiki is:

- The **human- and agent-friendly map** of the eMINDy WordPress codebase.
- A place to document:
  - Architecture and data model
  - Theme & plugin behavior
  - Shortcodes and dynamic content
  - SEO & schema strategy
  - Internationalization and accessibility
  - Release and agentic workflows

The agent’s role is to:

- **Keep the wiki aligned with reality** (code + configuration).
- **Clarify**, not complicate: improve structure, fix outdated info, add missing details when needed.
- Always work within **safe, YMYL-aware** boundaries (no clinical promises, no unsafe advice).

---

## 2. Scope — What the Agent May and May Not Do

### ✅ Allowed

The agent **may**:

- Edit existing wiki pages to:
  - Fix outdated information.
  - Add missing technical details clearly connected to actual code/config changes.
  - Improve structure, phrasing, or clarity without changing the meaning.
- Create **new wiki pages** only when:
  - A clear topic exists (e.g. “Accessibility & UX Guidelines”, “QA & Checklists”).
  - The page fits into the existing wiki structure and is **linked from Home** or related pages.
- Update links between pages (cross-links) to keep navigation intuitive.
- Add small, accurate diagrams or pseudo-structures in text form (e.g. code layout, data flows).

### ❌ Not allowed

The agent **must not**:

- Invent features, APIs, or workflows that do not exist in the code or roadmap.
- Remove important safety, YMYL, or accessibility content.
- Turn the wiki into a changelog or noisy commit log.
- Translate the wiki into other languages (documentation stays **English-only** unless the human maintainer decides otherwise).
- Add any content that:
  - Makes diagnostic or clinical claims.
  - Promises “cures” or guaranteed outcomes.
  - Conflicts with YouTube/WordPress platform policies or YMYL best practices.

When in doubt, the agent should **leave a note in the PR** instead of editing the wiki.

---

## 3. Source of Truth & Priority

The agent must respect this **priority order**:

1. **Project architecture & roadmap docs** (e.g. high-level system plan, sprint plans, AGENTIC instructions).
2. **Repository code and configuration**  
   - `wp-content/plugins/emindy-core/**`
   - `wp-content/themes/emindy/**`
3. **Root docs** in the repo (e.g. `README.md`, `AGENTIC.md`, release docs).
4. **Wiki** (this is a human-/agent-friendly view built on top of the above).

The Wiki **summarizes and explains**; it does not override the code or core docs.

If a conflict is found:

- Prefer updating the **wiki** to match code + core docs.
- Do **not** silently change code to match the wiki without a clear task/issue.

---

## 4. When Should the Agent Update the Wiki?

The agent should consider updating the wiki when:

1. **A feature or behavior changes** in the code that is already described in the wiki.  
   - E.g. new shortcode attributes, changed schema fields, modified CPT behavior.

2. **New technical concepts are introduced** that affect architecture or workflows.  
   - E.g. new assessment type, new shortcode, new CPT or taxonomy, new release step.

3. **Configuration changes** affect environment, i18n, SEO, or accessibility.  
   - E.g. new Rank Math settings, new Polylang behavior, new accessibility helper.

4. **Documentation is clearly outdated or misleading.**  
   - URLs, names, file paths, or workflows that no longer exist.

5. **A human maintainer explicitly asks for wiki updates** in an issue or PR.

If the change is minor (typo, small clarification), the update can be small.  
If the change is substantial, the agent should:

- Update the relevant sections.
- Add a short **“Last updated by agent”** note in the PR description (not inside the wiki itself).

---

## 5. General Editing Rules

### 5.1 Language & Tone

- Always write in **clear, simple English**.
- Use a **calm, professional, supportive** tone.
- Avoid clinical jargon unless necessary; explain it briefly if used.
- Avoid hype, marketing language, and exaggerated promises.

### 5.2 Structure & Formatting

- Use Markdown headings (`#`, `##`, `###`) consistently.
- Prefer bullet lists for enumerations and checklists.
- Use code fences for:
  - Commands
  - File paths
  - Code snippets
  - JSON examples

Example:

```bash
wpdc rewrite flush --hard
php
Copy code
echo esc_html__( 'Start exercise', 'emindy-core' );
5.3 Linking Conventions
Link to other wiki pages using:

markdown
Copy code
[Architecture & Data Model](Architecture-&-Data-Model)
[eMINDy Core Plugin](eMINDy-Core-Plugin)
Keep the link text human-readable and meaningful.

Ensure new pages are linked from at least one existing page, preferably:

Home

Or a relevant parent page.

5.4 Page Creation vs. Modification
Prefer modifying existing pages rather than creating many tiny pages.

Create a new page only if:

The topic is clearly distinct (e.g. “Accessibility & UX Guidelines”).

It would make an existing page too long or complex.

There is a plan for where this page will be linked from.

6. Page-Specific Guidelines
The agent should treat each core page as having a clear mission:

6.1 Home
High-level overview of the project and wiki.

Table of contents linking to:

Environment & Local Setup

Architecture & Data Model

eMINDy Core Plugin

eMINDy Child Theme

Shortcodes & Dynamic Content

SEO & Schema

Internationalization & Localization

Release Process

Agentic Development Guide

Agent rule: Only adjust links and short descriptive text if sections are added/renamed. Keep Home concise.

6.2 Environment & Local Setup
Describes:

Docker-based local environment.

wpdc alias usage.

Which folders are tracked in git.

Agent rule:

Update this page if:

The local URL changes.

The Docker or WP-CLI usage changes.

The tracked paths in git change.

Do not turn this into a full Docker manual; keep it specific to this project.

6.3 Architecture & Data Model
Explains:

Plugin vs theme split.

CPTs: em_exercise, em_video, em_article.

Taxonomy: em_topic.

Important meta fields (e.g. em_chapters_json).

EN-first + Polylang model at a high level.

Agent rule:

Update if:

New CPTs or taxonomies are added.

Meta key names or structures change.

The data model meaningfully evolves.

Do not document internal helper classes in detail here; that belongs in plugin/theme pages if needed.

6.4 eMINDy Core Plugin
Summarizes what the plugin does:

CPTs & taxonomies

Shortcodes

Schema

Assessments

Content injection

Newsletter/analytics

Agent rule:

Update when:

New core features are added.

Behavior of existing features changes (e.g. different injection logic).

File structure meaningfully changes (new main classes, etc.).

Keep this as a feature overview, not a low-level API reference.

6.5 eMINDy Child Theme
Describes:

Role as a child of twentytwentyfive.

Design system (Calm, accessible, RTL/Dark).

Core templates & patterns.

Agent rule:

Update when:

New important templates/patterns are added for key flows.

Theme-level design system changes (e.g. new color system).

RTL/dark-mode behavior is adjusted.

Avoid including too many CSS details; keep it conceptual and example-based.

6.6 Shortcodes & Dynamic Content
Documents:

[em_player]

[em_video_chapters]

[em_phq9]

[em_related] (if implemented)

[em_newsletter] (if implemented)

Auto-injection via the_content filter.

Agent rule:

Update whenever:

Shortcode attributes change.

New shortcodes are added.

Auto-injection behavior changes.

Keep examples accurate and minimal, with clear explanations.

6.7 SEO & Schema
Explains:

Rank Math’s role.

Plugin-level HowTo & VideoObject schema.

YMYL and safety considerations.

Agent rule:

Update when:

Schema fields or strategy change.

New schema types are added.

Rank Math configuration significantly changes.

Never add schema recommendations that violate YMYL safety or Google guidelines.

6.8 Internationalization & Localization
Describes:

EN-first model.

Polylang usage.

Meta copy hooks (e.g. em_chapters_json).

RTL support.

Translation of strings in theme/plugin.

Agent rule:

Update when:

Polylang integration behavior changes.

New languages or language-specific behaviors are introduced.

Meta copy logic is adjusted.

6.9 Release Process
Documents:

Semantic versioning.

Packaging via GitHub Actions.

Manual deployment to production.

Agent rule:

Update when:

Versioning strategy changes.

Release workflow (ZIP building + deployment) changes.

Additional checks (e.g. schema validation, QA steps) become mandatory.

The agent must not document any automatic deployment to production unless it truly exists and is safe.

6.10 Agentic Development Guide
Summarizes:

Where the agent may edit.

Coding standards.

Workflow.

Safety & YMYL constraints.

Agent rule:

Update this page only if:

AGENTIC.md changes in a way that affects wiki instructions.

Keep it aligned with AGENTIC.md; do not invent new, conflicting rules.

7. Agent Workflow for Wiki Updates
When a code or config change suggests a wiki update, the agent should:

Detect impact

Identify which wiki pages are affected (e.g. new shortcode → Shortcodes & Dynamic Content + maybe eMINDy Core Plugin).

Plan the update

Decide which sections to:

Edit

Add

Leave unchanged

Keep the plan small and focused.

Apply changes

Edit only the necessary sections.

Maintain heading structure and formatting.

Update cross-links if needed.

Self-review

Confirm information matches the actual code/config.

Run a quick “sanity check” on:

Safety/YMYL wording.

Clarity for a new contributor.

Document in PR

In the PR description, include a short note such as:

Wiki updates:

Updated Shortcodes & Dynamic Content to describe new [em_xxx] shortcode.

Updated SEO & Schema to reflect new VideoObject fields.

The agent does not need to log changes inside the wiki pages themselves (no inline “Changelog” sections unless requested by the maintainer).

8. Safety, YMYL, and Mental Health Content
Because eMINDy is a mental wellness project:

All wiki descriptions must respect YMYL best practices.

The agent must:

Avoid clinical diagnoses or claims.

Use supportive, non-judgmental language.

Emphasize that assessments (e.g. PHQ-9) are not diagnostic tools, but self-reflection aids.

Encourage seeking professional support when relevant.

If the agent is unsure whether a phrasing is safe, prefer:

Neutral, descriptive wording.

Leaving a note in the PR for the human maintainer to review.

9. Quality Checklist Before Saving Wiki Changes
Before finalizing wiki edits, the agent should verify:

 The content matches the actual code/configuration.

 The language is clear, simple, and professional.

 No clinical claims or unsafe promises are present.

 Links to other wiki pages work and are meaningful.

 Headings and formatting follow existing patterns.

 Changes are small and focused, not sprawling.

If any box cannot be checked confidently, the agent should:

Reduce the scope of changes, or

Ask for human review via PR comments.

10. Summary for the Agent
Treat the wiki as a living, high-level map of the eMINDy WordPress system.

Update it only when you have clear, grounded reasons tied to real changes.

Keep it:

Accurate

Calm

Safe

Helpful for both humans and future agents.

If you are ever unsure, document your uncertainty in the PR instead of guessing inside the wiki.
