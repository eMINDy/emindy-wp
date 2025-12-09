Release Process for eMINDy

This document explains how to prepare and publish a new release of the eMINDy theme and plugin. Following a consistent release process ensures that updates are packaged correctly and that the live site can be updated safely.

1. Versioning and Changelog

eMINDy uses semantic versioning for the theme and plugin (e.g., v1.2.3). Before creating a release, decide the new version number based on the changes:

Bug fixes or minor improvements: increment the patch version (0.1.0 -> 0.1.1).

New features that are backward-compatible: increment the minor version (0.1.0 -> 0.2.0).

Major changes or incompatible updates: increment the major version (1.0.0 -> 2.0.0). (At this stage of the project, we might be in 0.x versions; once we reach 1.0.0, consider this more strictly.)

Update the version numbers in the code:

Theme style.css: Open wp-content/themes/emindy/style.css and update the Version: header to the new version.

Plugin main file: Open wp-content/plugins/emindy-core/emindy-core.php and update the version number (there might be a constant or plugin header Version there).

Update the Changelog: In docs/CHANGELOG.md, add a new entry for the version with the date. List the notable changes: features added, bugs fixed, any important notes for upgrading. For example:

## [v0.2.0] - 2025-12-15
### Added
- New shortcode [em_mood_tracker] for tracking user mood over time.
- Front-page pattern redesigned with latest content sections.

### Fixed
- Newsletter signup form now validates email properly (fixes an undefined index PHP notice).
- Video player shortcode now displays error message if video ID is invalid.

### Changed
- Updated Polylang integration to automatically duplicate new custom post types across languages.


Keep the tone of changelog entries user-focused when applicable (especially if this file is public), but also informative for developers. If the project is internal, the changelog can be technical. Ensure every PR merged since the last release is accounted for in at least a brief entry (grouped into Added/Fixed/Changed sections as needed).

2. Preparation and Testing

Before packaging a release, make sure all desired changes are merged into the main branch and that main is passing all tests/CI checks. It’s wise to do a quick sanity test of the main branch in a local or staging environment:

Local smoke test: Pull the latest main code to a local dev site. Activate the updated theme and plugin. Click through key functionality: view a few pages (exercises, videos, articles), submit the newsletter form, take a quiz, switch languages, etc., to ensure nothing obvious is broken. Check browser console for errors and WP debug log for any warnings.

Update version references: Double-check that version numbers were updated (as per step 1). Also update any documentation references if needed (for example, if README or docs mention an older version number or date, update those).

GitHub Actions workflow check: Ensure that the GitHub Actions workflow for building release ZIPs (.github/workflows/build-zips.yml) is in place and up-to-date. This workflow should zip the contents of the theme and plugin directories. No changes are usually needed here, but if we added new top-level directories or files that need to be included in the zips, update the workflow accordingly.

3. Building the Release Packages

eMINDy is distributed as two ZIP files (one for the theme and one for the plugin). We use GitHub Actions to automate this:

Trigger the build workflow: Go to the repository’s Actions tab on GitHub and manually run the “Build eMINDy Theme & Plugin ZIPs” workflow. (If this workflow is configured to run on tags, you can alternatively push a new git tag for the version, e.g., git tag v0.2.0 && git push --tags, which might trigger the action. Check the workflow configuration for triggers.)

Download artifacts: Once the GitHub Action completes successfully, it will produce artifacts (or attach them to a GitHub Release if using the tagging approach). Download the generated files: e.g., emindy-theme-v0.2.0.zip and emindy-core-v0.2.0.zip (naming may vary but they should be clearly the theme and plugin).

Verify contents: Open the ZIPs locally (or use a tool) to quickly verify they contain the expected files. The theme ZIP should have the emindy/ theme folder with all templates, assets, etc. The plugin ZIP should have the emindy-core/ folder with all plugin PHP files, languages, etc. Make sure no unnecessary files (like .gitignore, docs, etc.) are inside – the build script usually excludes those. Also verify the version numbers inside style.css and plugin file in the zips are correct.

If for some reason the GitHub Action fails or is not available, you can build the ZIPs manually:

Create a ZIP of the wp-content/themes/emindy/ directory (contents of that folder) and name it with the version (for example emindy-theme-v0.2.0.zip).

Create a ZIP of the wp-content/plugins/emindy-core/ directory and name it emindy-core-v0.2.0.zip.
Be careful to not include the entire repository in the zip, only the specific folder contents for theme or plugin. Manual zipping is prone to mistakes, so using the automated script is preferred.

4. Releasing on GitHub

It’s good practice to create a Release on GitHub so that others can easily see what’s changed and download the packaged code:

Create a GitHub Release: Navigate to the Releases page and “Draft a new release”. Tag it with the new version (if you haven’t already pushed a tag, you can create one here). Title the release with the version number (e.g., v0.2.0 – “Minor feature updates and bug fixes”).

Release description: Paste the relevant section of the changelog into the release description, or write a summary of changes. Highlight any important upgrade notes (e.g., “You must update the parent theme to Twenty Twenty-Five v1.1” if that was required, etc.).

Attach the ZIP files: If the build workflow didn’t automatically attach artifacts, upload the theme and plugin ZIPs to the release so users (or the team) can download them easily.

Publish the release: Once everything looks good, publish it. This will mark the git tag and make the release visible. Team members watching the repo may get a notification.

(If this project is internal and not meant for public consumption, you might skip the formal GitHub Release and just distribute the ZIPs internally. But it’s still useful to tag the release in git and keep a changelog for internal reference.)

5. Deploying to Production

With the release packages in hand, deployment to the live WordPress site can proceed. Deployment is done manually to ensure control and allow for any necessary precautions:

Backup first: Always take a backup of the live site’s database and files before updating. This is critical. Even though eMINDy’s updates are mostly confined to theme and plugin, it’s possible for changes to affect data or site behavior. A backup ensures you can roll back if something unexpected occurs.

Put site in maintenance mode (optional): For a brief window during deployment, you might enable maintenance mode on WordPress (there are plugins or manual methods to show a maintenance page) so that users don’t see errors if they land on the site while files are updating. For small changes this is often not necessary, but for larger releases it’s a good idea.

Update the plugin: In WP Admin, go to Plugins and deactivate “eMINDy Core” temporarily (to avoid any errors during file replacement). Then delete the old “eMINDy Core” plugin via the Plugins page (this does not remove its data or settings, it just removes files). Next, click “Add New” -> “Upload Plugin” and upload the new emindy-core-vX.Y.Z.zip. Activate the plugin.

Alternative: If you have FTP/SFTP access, you can overwrite the plugin files directly on the server (wp-content/plugins/emindy-core/). But using the WP admin upload ensures proper activation steps run.

Update the theme: In WP Admin, go to Appearance → Themes. Switch from eMINDy to another theme temporarily (like the parent Twenty Twenty-Five or a default theme) to unlock the files. Then delete the old eMINDy theme from the Themes page. Next, click “Add New” -> “Upload Theme” and upload the new emindy-theme-vX.Y.Z.zip. Once uploaded, activate the eMINDy theme again (make sure Twenty Twenty-Five is still present as it’s the parent theme).

Alternative: Via FTP, you could overwrite wp-content/themes/emindy/ with the new files. If doing so, still be cautious and do it quickly to minimize the time the site might be referencing half-updated files.

Post-deployment steps: After activating the new plugin and theme, go to Settings → Permalinks and hit “Save Changes” (this flushes rewrite rules; our plugin might add custom post types/taxonomies that need fresh rules). Also check Plugins if any database updates are needed (WordPress sometimes shows a notice “Database update required” if needed for custom tables – for eMINDy Core, it might handle new tables on activation automatically).

Smoke test the live site: Immediately test key pages on the live site. Click through a few exercises, videos, articles. Submit a test email for the newsletter (perhaps using a test address) to ensure it still works. If using Polylang, switch languages to confirm both versions of content are okay. Essentially, verify the site is functioning as expected with the new code.

Monitoring: Keep an eye on the site’s error logs (if accessible) or analytics for any spikes in errors. If anything goes wrong (site error, important functionality broken), be prepared to roll back by restoring the backup or quickly re-installing the previous version theme/plugin. Then investigate the issue offline.

6. Communication

Notify the team: Once the release is deployed, let the team (and any stakeholders) know. For example, send a message or email summarizing “We’ve updated the live site to eMINDy vX.Y.Z, which includes A, B, C improvements. Please report if you notice any issues.”

Gather feedback: After a release, it’s useful to gather any user or admin feedback. If new features were added, are they being used as expected? If any bugs slipped through, prioritize fixing them in a patch release.

Plan next version: Clean up the GitHub issues by closing those fixed in the release, and triage remaining ones for the next milestone. Update the project board (if any) to reflect the release is done.

By following this release process, we ensure that updates to eMINDy are done methodically and safely. Each release is tagged and documented, and the live site is updated with minimal disruption. This helps maintain the platform’s reliability for end users. Always err on the side of caution during releases — taking the time to double-check steps can save a lot of trouble. Happy releasing!
