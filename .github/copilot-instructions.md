# Copilot Instructions for redbarnmusic.org

This repository contains the working `wp-content` source for the redbarnmusic.org WordPress site.

## Project Identity

- Project: `redbarnmusicorg`
- Expected Local workspace root:
  `/Users/macbookpro/Local Sites/redbarnmusicorg/app/public/wp-content/`
- GitHub repository:
  `https://github.com/Kevin-413/redbarnmusicorg.git`
- Canonical documentation directory:
  `docs/`

This is **not** the RBAdmin plugin repository. Do not import RBAdmin architecture, file paths, data models, REST routes, function prefixes, menu assumptions, or implementation patterns unless a current redbarnmusic.org file explicitly proves they apply.

## Mandatory Governance

Before acting on a Request, read and follow:

- `docs/0904-1343-CONTRACT-RedBarnMusicOrg-Copilot-Request-Standard.txt`
- `docs/0904-1343-Refined-Standard-Request-Format.txt`
- `docs/0904-1343-GIT-AIRLOCK-CONTRACT.txt`

These are the authoritative project-governance documents.

If a current Request conflicts with a general instruction, the explicit current Request controls unless it would require an unsafe/destructive action that needs user authorization.

## Git Airlock — Non-Negotiable

The Git Airlock applies before **any project-file modification**, including:

- PHP
- JavaScript
- CSS
- theme files
- plugin files
- configuration
- docs
- Requests/Replies
- `.github` files
- ignore files
- generated project artifacts

Before creating, editing, moving, renaming, or deleting a project file:

1. Confirm the exact repository and Git top-level path.
2. Confirm `remote.origin.url`.
3. Confirm the current branch and HEAD SHA.
4. Run `git status`.
5. Confirm a recoverable checkpoint exists.
6. Create/use a dedicated task branch.
7. Record the starting commit.
8. If unrelated uncommitted changes exist, STOP and return HOLD.

Never modify `main` directly unless the user explicitly authorizes that workflow.

Read-only inspection may proceed without a task branch only when no project file will change.

## Duplicate Request Hard Stop

Before implementation, check whether the Request duplicates or materially overlaps completed or active work.

If it does:

- STOP before changing files.
- Identify the overlapping Request/Reply, branch, commit, or existing behavior.
- Explain whether prior work is complete, partial, unresolved, or superseded.
- Wait for explicit direction.

Do not redo working functionality merely because another implementation seems cleaner.

## Request Structure

Use the required sections defined in:

`docs/0904-1343-CONTRACT-RedBarnMusicOrg-Copilot-Request-Standard.txt`

Missing sections must be written as `N/A`, not silently omitted.

Treat `SOURCE OF TRUTH`, `DO NOT CHANGE`, and `MINIMAL-CHANGE REQUIREMENT` as hard constraints.

## Request / Reply Pairing

Use America/New_York timestamps.

Request:
`MMDD-HHMM-Copilot-REQUEST-<Topic>.txt`

Reply:
`MMDD-HHMM-Copilot-REPLY-<Topic>.txt`

The Reply must reuse the exact timestamp and topic of its Request.

Save both under `docs/`.

The first line of each Request/Reply must be the exact filename.

Always create a Reply for completed work, including small fixes.

Place the Reply filename at the end of the visible Copilot response.

## Token Conservation

Keep context narrow.

- Inspect only files relevant to the Request.
- Prefer targeted file/search inspection over broad workspace scans.
- Do not inspect `uploads/` unless the Request concerns media.
- Do not inspect WordPress core unless explicitly required.
- Avoid reading entire plugin/theme trees when exact files are already known.
- Do not repeat large code blocks when a path, function name, or concise diff evidence is enough.
- Do not create unnecessary plans or extra handoff cycles when the Request is clear.

## WordPress / Site Safety

This workspace may contain third-party themes and plugins, including Thesis-related files.

Before changing third-party or framework code:

- inspect whether the change belongs in a custom/site-specific override instead;
- preserve working customizations;
- do not perform upgrades, dependency changes, theme replacement, plugin replacement, or modernization unless explicitly requested.

Do not modify:
- WordPress core;
- unrelated plugins;
- unrelated themes;
- uploads/media;
- caches/backups;
- credentials/secrets;
- database content

unless the current Request explicitly requires it.

## Verification

Use proportionate verification.

- PHP: syntax-check changed PHP when practical.
- JavaScript: syntax-check changed JS when practical.
- CSS/theme/UI: inspect the affected Local page in the browser when practical.
- Settings/data: verify actual saved/read behavior.
- Documentation: inspect the resulting file.

Do not claim verification that was not performed.

For visible behavior, browser results outrank source-level inference.

## Safety

Without explicit user authorization, do not use:

- `git reset --hard`
- `git clean -fd`
- `git clean -fdx`
- force push
- broad `git restore`
- destructive branch deletion
- history rewriting
- `rm -rf` against the project
- archive extraction over the active site
- wholesale source-tree replacement
- live deployment
- destructive database operations

## Completion

For file-modifying work, the Reply must include Git Airlock evidence:

- Repo
- Git top-level
- Remote
- Starting commit
- Task branch
- Pre-change git status
- Files changed
- `git diff --stat`
- Post-change git status
- Verification result
- Final commit
- Recovery/checkpoint note

Keep the visible completion summary concise and report real issues only.
