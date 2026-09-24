# Red Barn Music Organization — Project Handoff

**Repository:** `Kevin-413/redbarnmusicorg`  
**Scope:** `redbarnmusicorg-avada` WordPress `wp-content` source  
**Snapshot date:** 2026-09-24  
**Current branch:** `0904-1425-avada-safe-check`

## Evidence and confidence

**Verified:** This handoff is based on the checked-out source, Git history, `CHANGELOG.md`, tracked custom plugins and mu-plugins, and the current Git remote/branch state. The working tree was already dirty before this file was created; those unrelated changes are intentionally not summarized as completed work.

**Not available:** No direct Copilot Memory entries were accessible in this session. This file does not claim to preserve Copilot Memory.

## Architecture

- WordPress content is the repository boundary; WordPress core, uploads, and database state are outside this repository's intended source-of-truth.
- The active site uses the Avada theme with `themes/Avada-Child-Theme` for site-specific templates and styling.
- Site-specific PHP is concentrated in:
  - `mu-plugins/` for always-loaded site behavior, redirects, settings, maintenance helpers, and compatibility behavior.
  - `plugins/rbm-contact-scrambler/` for contact-data presentation/scrambling behavior.
  - `plugins/rbm-faculty/` for teacher/faculty records, display, forms, and Avada integration.
  - `plugins/rbm-instruments/` for lesson/instrument taxonomy, lesson displays, sign-up settings, forms, and media-backed instrument content.
  - `plugins/rbm-test-monitor/` for local/test monitoring helpers.
- Forminator, Avada, Fusion Builder/Core, SEO, and other third-party plugins are installed site dependencies, not custom architecture.

## Important decisions

- Keep custom behavior in site-specific mu-plugins and `rbm-*` plugins rather than modifying third-party theme/plugin code.
- Use the Avada child theme for theme overrides; do not edit the parent Avada theme.
- Contact information ownership is intentionally separated: contact scrambling owns phone/email presentation, while the RBM Site Settings and RBM Instruments components own their documented settings.
- Legacy routes are handled by explicit redirects rather than restoring retired page implementations.
- The repository branch has been pushed without force-pushing; `main` was not changed by the recent preservation work.

## Working features

The current source includes the public Avada site, lessons inquiry/contact/community pages, faculty/teacher displays and forms, instrument/lesson displays and media, contact scrambling, site settings, redirects, and test-monitor support. Recent committed work also includes standard page templates, site-width header/navigation adjustments, and child-theme documentation.

## Unfinished work

- Review and intentionally reconcile the existing dirty worktree before any future broad commit.
- Decide how the large pending RBM instruments/lessons media and code changes should be handled; do not infer that every deletion is safe to publish.
- Review remaining local documentation, deployment-tool, generated/private media-cleaner, and third-party-plugin changes before committing anything else.
- Confirm Local-to-live deployment separately; repository commits do not prove that live WordPress options, content, uploads, or database state match.

## Known problems and cautions

- The working tree contains substantial pre-existing modifications, deletions, and untracked files across third-party plugins, documentation, generated/private folders, and deployment artifacts.
- WordPress database/content and uploads are not represented by this code handoff.
- The active site depends on configured WordPress options and Forminator/Avada state that cannot be reconstructed from PHP alone.
- `mu-plugins/_retired-rbm-faculty/` is retained historical code; it is not the primary active faculty implementation.

## First steps for a future maintainer

1. Confirm repository, branch, remote, and `git status` before editing.
2. Treat only custom `mu-plugins`, `rbm-*` plugins, and the Avada child theme as the default review scope.
3. Check the relevant changelog and browser behavior before changing public-site behavior.
4. Keep database/content/uploads and third-party plugin updates out of source commits unless explicitly scoped.
