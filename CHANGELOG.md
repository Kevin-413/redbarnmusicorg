# Changelog

## 2026-09-19 (fix #page-title anchor slide-then-bounce)
- Root cause found via instrumented click testing: Avada's own `fusion-scroll-to-anchor.js`
  intercepts every link click, rewrites the URL hash to a non-existent `#_<id>` (so the browser's
  native anchor jump never fires), then on the destination page animates an eased ~700ms scroll to
  the real target and swaps the hash back afterward — visibly overshooting before correcting, which
  is the reported "slide then bounce." A direct address-bar load of the same `#page-title` URL was
  confirmed clean (no rewrite, no animation, instant native jump), isolating the cause to Avada's
  click-time interception rather than the anchor/CSS itself.
- `mu-plugins/rbm-interior-page-title-anchor.php`: added `rbm_page_title_anchor_disable_smooth_scroll()`
  (hooked to `wp_footer`), a capture-phase `click` listener scoped to links containing `#page-title`
  that calls `stopPropagation()` before Avada's own bubble-phase delegated handler can see the
  event — capture always precedes bubble regardless of script load order, so this reliably wins
  without needing to locate or modify Avada's minified handler. Native navigation and the browser's
  own instant anchor jump (governed by the existing `scroll-margin-top`) proceed unaffected.
- No banners, titles, or page structure were touched; `scroll-margin-top` unchanged.
- Verified stable (scrollY identical from ~150ms to ~850ms post-navigation, hash stays `#page-title`,
  never `#_page-title`) across: main nav, footer "Quick Links"/"Connect", mobile bottom bar; About,
  Faculty, Instruments, Contact; desktop, 375px, and 320px; logged-in (with admin bar) and logged-
  out. Confirmed no duplicate `#page-title` elements on any tested page.

## 2026-09-19 (extend #page-title anchor to Teacher profile links)
- `plugins/rbm-faculty/includes/rbm-teachers.php`: Teacher profile links now also carry the
  `#page-title` landing anchor. `rbm_msch_teacher_card_html()` appends it to the shared card/View
  Profile link (after any `?instrument=` query context, so it stays a fragment, not a query arg) —
  covers the Faculty grid, filtered results, and the standalone `[rbm_teacher]` shortcode, since
  all three render through this one function. `rbm_msch_teacher_post_nav_arrow()` also appends it
  to the Teacher profile page's own Previous/Next links (other Teacher pages). Reuses the shared
  `rbm_page_title_anchor_should_append()` safety check from `mu-plugins/rbm-interior-page-title-
  anchor.php` (guarded by `function_exists()`) instead of duplicating the exclusion logic.
  Verified: card/View Profile links, instrument-filtered card links, and Previous/Next links all
  carry `#page-title`; a real click-through landed correctly below the header with no console
  errors.

## 2026-09-19 (wire #page-title anchor into site navigation)
- `mu-plugins/rbm-interior-page-title-anchor.php`: the existing `#page-title` anchor (added
  previously but never linked to) is now appended to real internal navigation links: the main/
  mobile/sticky header menu, and the two footer Text widgets ("Quick Links" and "Connect").
  Excludes the Home link, external links, and anything that already carries its own fragment.
  Two implementation notes found only by testing against the live render (not visible from code
  alone): (1) Avada's header actually calls `wp_nav_menu()` with a direct `menu` ID, not
  `theme_location` (both are now checked, resolving the location→ID mapping dynamically); (2) core's
  `widget_text_content` filter never fires for these footer widgets, so `widget_display_callback`
  is used instead to rewrite the widget's saved text before output. `themes/Avada-Child-Theme/
  functions.php`: the 3 real links in the mobile bottom quick-action bar (Lessons/Sign Up/Contact)
  now append `#page-title` directly. Verified via direct HTML fetch and a real click-through: Home
  link and the external Facebook link are unaffected; homepage still has no `#page-title` element.

## 2026-09-19 (Faculty page: Choose Instrument and Sign Up share one row)
- `plugins/rbm-faculty/includes/rbm-teachers.php` (`rbm_msch_teachers_shortcode()`, compact mode):
  on the unfiltered Faculty view, the "Choose Instrument" select and "Sign Up" button are now
  wrapped in one `.msch-teacher-controls-row` flex container (Choose Instrument left, Sign Up
  right) instead of stacking in separate blocks. The filtered-results view is unchanged — it never
  showed Choose Instrument alongside Sign Up, so there was no second row to combine there.
  `plugins/rbm-faculty/assets/css/rbm-faculty.css`: new responsive rules for `.msch-teacher-
  controls-row` — same row down to 481px, stacks (Choose Instrument above Sign Up, both full width)
  at 480px and narrower. Verified no overlap or clipping at 1280/768/480/375/320px; existing filter
  behavior, Sign Up URLs/query params, and prefill context unchanged.

## 2026-09-19 (Instruments We Teach list: show all records, fix misleading page count)
- `plugins/rbm-instruments/includes/rbm-lessons.php`: the Instruments We Teach admin list view
  only skipped pagination when the Status filter was "Active" (drag-and-drop scope); under
  "All"/"Inactive" it fell back to the default per-page limit and cut off any records beyond it.
  `rbm_msch_lesson_filter_iwt_view()` now always sets `posts_per_page => -1` for this view
  regardless of Status filter. Also added `rbm_msch_lesson_iwt_unpaginated_per_page()` (filters
  `edit_msch_lesson_per_page`) so the "Page X of Y" display — which WordPress calculates from a
  separate Screen Options value, not the actual query limit — no longer shows a misleading extra
  page when every matching row is already on the one page. Read-only list-display change; no
  Instrument data affected.

## 2026-09-19 (Instruments list: Slug and URL Mode columns)
- Implemented docs/0919-1441-Copilot-REQUEST-Add-Slug-And-URL-Mode-Columns.txt. Added read-only
  "Slug" and "URL Mode" columns to the Instruments admin list (`plugins/rbm-instruments/includes/
  rbm-lessons.php`), positioned Icon | Title | Status | Display Order | Category | **Slug | URL
  Mode** | IWT | Instruments We Teach. Slug shows the exact saved `post_name` (monospace, em dash
  if blank) and is sortable ascending/descending. URL Mode shows a small "Global"/"Custom" badge
  using the same safe-upgrade default rule as the Edit Instrument form. No Teachers column was
  added (explicitly out of scope). Both headings have a small accessible info-icon popover (hover,
  keyboard focus, and click/tap all reveal it; Escape or moving focus away dismisses it) explaining
  Slug vs. URL Mode, added to `assets/css/rbm-instruments-admin.css` and `assets/js/rbm-instruments-
  admin.js`. Read-only, admin-only — no stored data, public page, Faculty, Sign-Up, or Forminator
  behavior changed. See docs/0919-1441-Copilot-REPLY-Add-Slug-And-URL-Mode-Columns.txt.

## 2026-09-19 (permanent Clear All Custom Sign Up URLs control)
- Implemented docs/0919-1354-Copilot-REQUEST-Add-Clear-All-Custom-Sign-Up-URLs.txt. Added a new,
  destructive "Clear All Custom URLs" button to `plugins/rbm-instruments/includes/rbm-lessons-
  display-settings.php`, styled with WordPress's `button-link-delete` destructive-action class and
  kept in its own `<form>`/nonce, fully separate from "Save Changes" and "Reset All to Global URL".
  New handler `rbm_clear_all_custom_signup_urls()` permanently `delete_post_meta()`s every
  Instrument's and Teacher's `_msch_lesson_signup_url`/`_msch_teacher_signup_url` (only when
  non-empty, so the reported count reflects real changes and a repeat run safely reports 0/0), sets
  every record's mode meta to `'global'`, and never touches the `rbm_msch_global_signup_url` option.
  Confirmed on Local: Cancel makes zero DB changes; Confirm deletes the saved custom URL entirely
  (switching a cleared record back to "Custom URL" shows a genuinely empty field, not a stale
  value); Global Sign Up URL setting unaffected; front-end Sign Up links still resolve correctly.
  See docs/0919-1354-Copilot-REPLY-Add-Clear-All-Custom-Sign-Up-URLs.txt.

## 2026-09-19 (Global Sign Up URL reset control repositioned near the field)
- Implemented docs/0919-1348-Copilot-REQUEST-Add-Global-Sign-Up-URL-Reset.txt. The existing "Set
  All Records to Global" bulk action (`plugins/rbm-instruments/includes/rbm-lessons-display-
  settings.php`, `rbm_set_all_signup_records_global()`, added under docs/0919-1126-...) already did
  what this request needed, so rather than duplicate it, it was moved to sit directly after the
  Global Sign Up URL field (instead of its own separate section further down the page), relabeled
  "Reset All to Global URL" with the requested confirmation wording, and its handler now only
  counts/reports Instrument and Teacher records whose mode actually changed (previously always
  reported the full record count regardless of prior state) so repeating the reset safely reports
  0/0 once nothing is left in Custom mode. Still preserves every saved Custom URL. No change to
  meta keys, individual record editing, URL construction, or Forminator prefilling. See
  docs/0919-1348-Copilot-REPLY-Add-Global-Sign-Up-URL-Reset.txt.

## 2026-09-19 (Instrument slug duplicate checker and warning popup)
- Implemented docs/0919-1305-Copilot-REQUEST-Add-Instrument-Slug-Duplicate-Checker.txt. Added an
  editable Slug field to the Add/Edit Instrument form (`plugins/rbm-instruments/includes/rbm-
  lesson-form.php`) and authoritative server-side duplicate-slug validation in
  `rbm_save_lesson_form_submit()`: before any `wp_insert_post()`/`wp_update_post()` call, a new
  `rbm_msch_lesson_find_slug_conflict()` checks other non-trashed Instruments for the same slug
  (excluding the record being edited). A conflict blocks the save entirely — no record is created,
  updated, or WordPress-suffixed (e.g. `piano-2`) — and the submitted values plus the conflicting
  Instrument's title/ID/status/slug/edit link are stored in a short-lived per-user transient, then
  redirected back to the same form so it redisplays everything with a warning: an always-visible
  `.notice.notice-error` (no-JS fallback) plus a native `<dialog>` modal (opened via
  `plugins/rbm-instruments/assets/js/rbm-instruments-admin.js`) offering "Edit Existing Instrument"
  (new tab) and "Return and Change Slug" (closes the dialog, focuses the Slug field). No Override/
  Save Anyway action exists. Confirmed no image-filename-to-slug coupling exists in this code, so
  replacing an Instrument's image never touches its slug. Does not repair the existing Piano/
  Upright Piano records. See docs/0919-1308-Copilot-REPLY-Add-Instrument-Slug-Duplicate-Checker.txt.

## 2026-09-19 (fix Faculty page Choose Instrument filter regression)
- Fixed docs/0919-1135-Copilot-REQUEST-Verify-Faculty-And-Sign-Up-Workflows.txt: the Faculty page's
  green "Choose Instrument" select stopped filtering Teacher cards after the Sign Up CTA `<p>` was
  inserted between it and the card grid (docs/0919-0141-...). `plugins/rbm-faculty/assets/js/rbm-
  faculty.js` assumed the grid was the filter's immediate next sibling; it now looks up the
  `.thesis-lesson-card-grid` within the enclosing `#teachers` container instead, so it no longer
  breaks when other markup sits between them. Verified: instrument filtering works on desktop and
  mobile widths with no console errors; Faculty → Category → Teacher → Sign Up and Global/Custom
  Sign Up URL resolution were spot-checked and are unaffected. See docs/0919-1135-Copilot-REPLY-
  Verify-Faculty-And-Sign-Up-Workflows.txt.

## 2026-09-19 (Universal Avada Home pilot - shared green pill button standard)
- Implemented docs/0919-1003-Copilot-REQUEST-Implement-Universal-Avada-Home-Pilot.txt. Added one
  `fusion_button` green solid pill ("GALLERY" -> `/gallery/`) to the Gallery section of the shared
  Gutenberg Synced Pattern "Home Body (Synced)" (post 20440, read by both Home post 20046 and
  redirect post 255), reusing the exact button attributes already proven in the Lessons section of
  the same pattern. Open House, Block Party, and Scholarships were left unchanged — no safe,
  unambiguous existing destination to reuse (see Reply for detail). Documented the reused
  `fusion_button` template as the standing "Green Solid Pill Button" standard in
  docs/0906-1649-STANDARD-Home-Page-Container-Formats.txt (new section 9). No PHP, database
  schema, RBM, or Forminator changes. See docs/0919-1003-Copilot-REPLY-Implement-Universal-Avada-
  Home-Pilot.txt.

## 2026-09-19 (preserve Instrument context through Teacher profile)
- Extended the Faculty → Sign Up workflow (docs/0919-0916-Copilot-REQUEST-Preserve-Instrument-
  Context-Through-Teacher-Profile.txt) so a visitor's specific-Instrument context survives one
  more hop, from an Instrument-filtered Teacher card through to that Teacher's own profile page,
  and from there into the Lessons Inquiry Sign Up link (`plugins/rbm-faculty/includes/rbm-
  teachers.php`). `rbm_msch_teacher_card_html()` now appends the already-validated Instrument
  slug as `?instrument=` onto the profile link/"View Profile" link (only when present).
  `rbm_msch_teacher_single_content()` reads that incoming `?instrument=`, validates it against a
  real published `msch_lesson` slug (never inferred from the Teacher's own assigned Instruments/
  Categories, and never a broad Category slug), and passes it into the existing
  `rbm_msch_signup_url()` alongside the Teacher slug. A direct profile visit, an invalid
  Instrument value, or a Category-only path all continue to produce a teacher-only Sign Up link
  exactly as before. No Forminator, rbm-instruments, or Teacher/Instrument assignment changes.

## 2026-09-19 (prepare LIVE deployment package for Lessons Inquiry prefill)
- No code or Local Forminator changes in this entry — Local (form 20709, `select-1`/`textarea-2`)
  was already verified complete per the prior entry below. This entry only prepares the manual
  LIVE deployment package per docs/0919-0258-Copilot-REQUEST-Promote-Lessons-Inquiry-Prefill-To-
  Live.txt: a reviewable diff of `plugins/rbm-faculty/includes/rbm-teachers.php` (everything
  uncommitted since the last commit, `78b9458`), a checklist of every numeric Form ID/field ID to
  verify or change on LIVE, and step-by-step Forminator configuration instructions, all for you to
  apply manually. Per your instruction, LIVE was not accessed and no LIVE change was made by this
  task. See docs/0919-0258-Copilot-REPLY-Promote-Lessons-Inquiry-Prefill-To-Live.txt.

## 2026-09-19 (correct context-aware prefill to current Inquiry form)
- Corrected the prior context-aware Sign Up prefill work (docs/0919-0212 and 0919-0224 REQUESTs)
  after discovering the original 0919-0154 task had targeted an obsolete demo Forminator form.
  You imported updated forms during this task, creating new form posts 20709 (RBM Lesson Inquiry),
  20710 (Contact Us), and 20711 (Student Registration); the old demo form 20330 was deleted as a
  side effect of that import (not by this task) and was not restored, per your instruction, since
  20709 supersedes it. `/lessons-inquiry/` was repointed from `[forminator_form id="20330"]` to
  `id="20709"`.
  - Updated `select-1` ("Select Instrument") on form 20709 from the old 9 broad categories to the
    24 specific Instrument choices (Acoustic Guitar through Voice), using each option's real
    `msch_lesson` post_name as its value so the existing `?instrument=<slug>` contract resolves
    correctly. Enabled Forminator's native Pre-populate on `select-1` (query parameter:
    `instrument`).
  - The imported form 20709 already has a real "Preferred Teacher" field (`textarea-2`), so no
    custom field was created. `rbm_msch_signup_prefill_teacher_name()` (rbm-teachers.php) now
    targets `textarea-2` instead of the deleted demo form's `text-1`.
  - No PHP logic changes were needed for the Category-tile rule: `rbm_msch_teachers_shortcode()`
    already only ever passes a specific Instrument slug to `rbm_msch_signup_url()`, never a
    Category slug, so Category-only Sign Up links already correctly omit `?instrument=`.
  - Re-verified all scenarios end-to-end on the corrected form: specific-Instrument-only,
    Category-only (Instrument left blank), Teacher-only, Instrument+Teacher combined, invalid
    values (both fields left blank, no errors), and one real test submission (stored
    `select-1 = Mandolin`, `textarea-2 = Catherine Bell`), then deleted the test entry.

## 2026-09-19 (context-aware Sign Up prefill)
- Extended the Sign Up workflow (`docs/0919-0154-Copilot-REQUEST-Add-Context-Aware-Sign-Up-Prefill.txt`) to carry visitor context into the existing Forminator Lesson Inquiry form (`plugins/rbm-faculty/includes/rbm-teachers.php`). `rbm_msch_signup_url()` now accepts optional Instrument/Teacher slugs and appends them as a small stable query-string contract (`?instrument=<lesson-slug>&teacher=<teacher-slug>`) onto the same canonical base URL as before. Filtered-Faculty Sign Up carries the visitor's original specific Instrument (preserved even through Category fallback, never the fallback Category itself); teacher cards and Teacher-profile buttons carry the Teacher slug (cards also carry Instrument when shown inside an Instrument-filtered view); general Faculty/Lessons Sign Up remain plain links. On the Forminator side: enabled the existing "Preferred Instrument" field's (`select-1`) native Pre-populate feature (query param `instrument`) — sufficient on its own since its option values are already canonical slugs, so an unrecognized Instrument slug is safely left unselected with no custom code needed. Added one new plain "Preferred Teacher" field (`text-1`) to the form, and one small script (Local page-scoped to `/lessons-inquiry/`) that resolves an incoming `?teacher=<slug>` into the matching published Teacher's canonical display name and fills that field, since Forminator's native Prefill can't do slug-to-name translation on its own. No Forminator submission/notification behavior changed; no dynamic Instrument-list sync was added; the Forminator "Preferred Instrument" field remains entirely separate from any registered/teacher-assignment Instrument data.

## 2026-09-19 (prominent Sign Up placement)
- Added Sign Up CTAs to the four rbm-faculty-owned locations identified in `docs/0919-0127-PLAN-Prominent-Sign-Up-Placement-And-Flow.txt` (`plugins/rbm-faculty/includes/rbm-teachers.php`): a prominent button directly under the dynamic heading on filtered Faculty views (`?instrument=`/`?category=`), a general Sign Up button near the top of the normal `/faculty/` page (below Choose Instrument), a "View Profile | Sign Up" action row on every Teacher card (the card is now a `<div>` wrapper with an inner profile link, instead of one whole-card `<a>`, so both are valid separate links), and Sign Up buttons near the top and bottom of each Teacher profile. All reuse the existing site-wide `.thesis-cta-button` style (defined in Avada's global Additional CSS) — no new button style. Added one small shared `rbm_msch_signup_url()` helper that reads the destination from an existing published Instrument's `_msch_lesson_signup_url` meta (falling back to the Lessons Inquiry page) instead of hardcoding another `/lessons-inquiry/` string. No context-aware prefill yet (a later phase); no Forminator changes; the Lessons page's existing page-level Sign Up button and the mobile bottom bar's persistent Sign Up button were verified already correct and left unchanged. This closely follows a 2026-09-13 Faculty signup implementation that was built and then reverted the same day for undocumented (non-technical) reasons — reused its proven card-refactor/helper pattern.

## 2026-09-18 (keep contact scrambler tiny)
- Trimmed `plugins/rbm-contact-scrambler/` to the minimum production structure per `docs/0918-1838-Copilot-REQUEST-Keep-RBM-Contact-Scrambler-Tiny.txt`: removed the empty `assets/css/rbm-contact-scrambler.css` placeholder and its `wp_enqueue_style()` call (added, then found unnecessary, in the prior task). Final plugin is exactly `rbm-contact-scrambler.php` + `assets/js/rbm-contact-scrambler.js` + `README.txt` — no CSS, no migration code (already removed previously), no dependencies. No behavior change: `[rbm_phone]`/`[rbm_text]`/`[rbm_email]` in the footer and Slick Popup re-verified working.

## 2026-09-18 (contact scrambler plugin)
- Built a standalone `plugins/rbm-contact-scrambler/` plugin (`docs/0918-1649-Copilot-REQUEST-Build-Standalone-Contact-Scrambler-Plugin.txt`), superseding the prior Avada Child Theme phone-link implementation. Independent of Avada/Slick Popup/Forminator/RBAdmin. One WP Settings API page (Settings > RBM Contact Scrambler) owns `rbm_contact_phone` and `rbm_contact_email`; `[rbm_phone]`, `[rbm_text]`, `[rbm_email]` shortcodes (with optional `text="..."`/`link="no"` attributes) render lightweight placeholder markup that `assets/js/rbm-contact-scrambler.js` fills in client-side (base64-encoded config, not plain digits, per the anti-scraping ask). Cut over the Avada footer widget and Slick Popup's Contact Us popup content from the old `.rbm-phone-link`/`.rbm-text-link` class placeholders to the new shortcodes, then removed the now-superseded phone code from `themes/Avada-Child-Theme/functions.php` and deleted its orphaned `js/rbm-phone-links.js`. No existing public canonical email address was found on the site, so `rbm_contact_email` starts empty by design. (Activation-time migration of the legacy `rbm_phone_number` option was removed per follow-up request; `rbm_contact_phone` is set manually via the settings page.)

## 2026-09-18 (phone links)
- Centralized the Red Barn public phone number (`docs/0918-1620-Copilot-REQUEST-Centralize-And-Scramble-Phone-Text-Links (1).txt`): added one WP option (`rbm_phone_number`, editable at Settings > Red Barn Phone) as the single source of truth, plus a site-wide `themes/Avada-Child-Theme/js/rbm-phone-links.js` script (enqueued/localized from `functions.php`) that fills in any `.rbm-phone-link`/`.rbm-text-link` anchor's `tel:`/`sms:` href and display text client-side. Replaced the hard-coded "413-256-8899" in the footer's "Amherst Red Barn Music School" text widget, and the old id-based `#rbm-text-phone`/`#rbm-call-phone` spans in Slick Popup's Contact Us popup content (Redux option `splite_opts`), with the new placeholder anchors. Also restored `plugins/slick-popup/libs/js/custom.js`, which the plugin registers/enqueues but was missing entirely from this Local workspace (present on LIVE) — recreated from the last-known content minus its old hardcoded-split-string phone scrambler, now superseded by the shared site-wide script. No changes to Forminator, Slick Popup's open/close behavior, or footer layout/styling.

## 2026-09-18
- Filtered Faculty results view (`docs/0918-1532-PLAN-Filtered-Faculty-And-Interior-Page-Title-Landing.txt`): `/faculty/?instrument=<slug>#teachers` and `/faculty/?category=<slug>#teachers` now land directly at the teacher results with a canonical `<Instrument> Teachers` / `<Category> Teachers` heading (`plugins/rbm-faculty/includes/rbm-teachers.php`), hide the "Choose Instrument" control only while filtered, and keep the existing "View All Faculty" link/category-fallback note as-is. Both Faculty link generators in `plugins/rbm-instruments/includes/rbm-lessons.php` (`rbm_msch_faculty_filter_url()`, `rbm_msch_faculty_category_url()`) now append `#teachers`. Added `#teachers{scroll-margin-top:80px;}` to `rbm-faculty.css` so Avada's sticky header (measured 63px) doesn't cover the heading. No changes to the exact-Instrument → Category-fallback → Lessons-terminal-fallback chain, teacher CPT, or card markup.
- Site-wide interior-page landing anchor: added `mu-plugins/rbm-interior-page-title-anchor.php`, a small reusable `id="page-title"` anchor (with `scroll-margin-top`) printed on every front-end interior page via Avada's existing `avada_after_page_title_bar` hook, excluding the homepage. Rewriting the site's actual navigation links (registered nav menu, hand-coded footer Quick Links, hand-coded mobile quick-action bar) to append `#page-title` was intentionally NOT done — per the PLAN's own scope control, that spans multiple independent link-rendering systems and would be a broad navigation change, not a small reusable one; reported in `docs/0918-1532-Copilot-REPLY-Implement-Filtered-Faculty-And-Interior-Page-Title-Landing.txt` instead of implemented.

## 2026-09-17
- Extracted reusable inline CSS/JS out of PHP in both `plugins/rbm-instruments/` and `plugins/rbm-faculty/` into proper enqueued asset files (`docs/0917-1053-PLAN-Extract-Inline-JS-CSS-From-RBM-Plugins.txt`): new `assets/css/`+`assets/js/` folders in each plugin (`rbm-instruments.css/js`, `rbm-instruments-admin.css/js`, `rbm-faculty.css/js`, `rbm-faculty-admin.js`), replacing the `<style>`/`<script>` blocks previously echoed inline from PHP (Instruments/Categories admin lists, Edit Category screen, Instrument/Teacher/Category Media Library pickers, Copy Shortcode/Copy List clipboard controls, the public Instruments page's category grid/tile filtering, the Faculty filter select, and the Teacher Profile page). The one genuinely dynamic value (the Choose Instrument pill Show/Hide CSS rule) now uses a `msch-lessons--pill-show|hide` modifier class instead of inline PHP-interpolated CSS; the two nearly-identical admin drag-and-drop reorder scripts (Instruments/Categories lists) were consolidated into one shared `rbmInitDragSort()` helper, with only the small per-request nonce/scope config passed via `wp_add_inline_script()`; the three duplicated "Copy List" clipboard scripts were consolidated into the same handler already used for "Copy Shortcode", switched to `data-copy-target`/`data-status-target` attributes. PHP still owns all markup, hooks, and enqueue decisions; assets are only enqueued on the specific admin screens/front-end contexts that need them (never loaded globally). No visual or behavioral change intended — verified in-browser (admin lists, Edit Category, Instrument/Teacher/Category edit screens, Instruments and Faculty public pages, Teacher Profile pages, and all three standalone shortcodes) across both Category Click Destination modes.
  - Caught and fixed during browser verification (both pre-existing, not introduced by the CSS/JS extraction itself, but only surfaced by it): the "Edit Category" top Save button targeted a `#submit` id that doesn't exist on that WP screen (fixed to a `#edittag [type="submit"]` selector); merging the Instruments-list and Categories-list "move Add-button into the top-right slot" scripts into one file initially made both fire on each other's screen, producing a duplicate button (fixed by scoping each to its own WP-provided body class). Also fixed one true regression introduced during the extraction itself: the Icon-preview-modal click handler was attached before its overlay markup (printed by a different PHP hook) existed in the DOM, so it silently never worked — moved inside `DOMContentLoaded`.
- Added three reusable RBM shortcodes for manual placement on any Avada page/content area (`docs/0917-1034-Copilot-REQUEST-Implement-Reusable-RBM-Shortcodes-And-Copy-Buttons.txt`): `[rbm_teacher id="123"]`, `[rbm_instrument id="456"]`, `[rbm_instrument_category slug="guitar"]`. Each reuses the existing card/tile renderers rather than duplicating markup — `rbm_msch_lesson_card_html()` was already reusable; `rbm_msch_teacher_card_html()` and the Category tile's icon/name markup + CSS were extracted from their existing inline locations into small shared helpers used by both the original grids and the new shortcodes. The standalone Category shortcode is a plain link (no in-page filter/grid reproduced) honoring the existing Category Click Destination setting: Faculty Group Page routes via the existing `rbm_msch_faculty_category_url()`; Show Instrument Tiles deep-links to the canonical Instruments/Lessons page with `?category=<slug>` (already parsed server-side). A missing/unpublished target fails silently (empty string), no admin diagnostic. Added a "Copy Shortcode" clipboard control (reusing the existing "Copy List" UI pattern) to the Teacher, Instrument, and Category Edit screens (Edit only, not Add-new). No CPTs, taxonomy, IDs, or existing Faculty/Instruments page behavior changed.
- Added a "Category Click Destination" setting to Instruments -> Page Display -> Settings (`plugins/rbm-instruments/includes/rbm-lessons-display-settings.php`): "Show Instrument Tiles" (default, unchanged existing behavior) or "Faculty Group Page". When set to Faculty Group Page, clicking a public Instrument Category tile (`plugins/rbm-instruments/includes/rbm-lessons.php`) links straight to the Faculty page filtered to that Category's full group via a new `?category=<msch_instrument term slug>` parameter, instead of switching to that Category's Instrument tiles. Added the smallest additive support in `plugins/rbm-faculty/includes/rbm-teachers.php` for the new `?category=` parameter (a direct, exact Category-to-Teachers match, reusing the shared `msch_instrument` taxonomy; no new mapping data) alongside the existing `?instrument=<lesson slug>` exact/fallback behavior, which is unchanged.

## 2026-09-13
- Consolidated project documentation: moved all Request/Reply and planning docs dated 0905 and later from `redbarnmusicorg/app/public/wp-content/docs/` into this repo's `docs/` folder (files with matching names were replaced by the moved copies). `redbarnmusicorg.local` was a frozen pre-Avada-migration snapshot last committed 2026-09-04 and was never updated with subsequent plugin/theme work, so it no longer reflects the active site. Going forward, all Request/Reply docs and changelog entries are written only here, in `redbarnmusicorg-avada/app/public/wp-content/`.
- Rebuilt the Instruments and Categories admin lists (`plugins/rbm-instruments/`) around a single canonical numeric Display Order per Instrument/Category: added View By/Status filter rows, a "Reset Display Order" control that clears stale values site-wide and renumbers the enabled scope alphabetically, drag-and-drop reordering (jQuery UI Sortable, AJAX-saved, server-validated against the exact enabled scope) for Instruments (Lessons We Teach + Active) and Categories (Active), and generic per-user list-state persistence (remembers whatever filters/sort a user last used, no hardcoded filter list). Added a small "<<" control to jump between the two related admin screens.
- Activated the existing (previously alphabetical-only) public Display Order settings so the admin's canonical Instrument/Category order now drives the public Lessons page tiles directly, including Category-filtered and Direct Display Instrument views (one shared order, no per-Category duplicate ordering). Fixed a related bug where a Category manually marked Inactive could still appear publicly if it had a published Lesson.
- Added Faculty (`plugins/rbm-faculty/`) admin Status filter controls and the same generic list-state persistence pattern used on Instruments/Categories.
- Added public Teacher-by-Instrument filtering: a new "Filter Instruments" field on each Teacher (separate from the display-only Instruments Taught text, auto-filled from the teacher's Faculty category and editable for exceptions), a `/faculty/?instrument=<slug>` public filter with a full fallback chain (exact Instrument match → Faculty-category fallback → safe same-site return to the Lessons page for an invalid Instrument or no matches at all), and "Meet the Teachers" links added to each public Instrument tile.
- Rollback note: a Faculty Signup Workflow experiment (shared signup URL helper, Faculty/Teacher-card/Teacher-profile Sign Up CTAs) was implemented on 2026-09-13 and then fully reverted the same day to this frozen baseline (`plugins/rbm-faculty/includes/rbm-teachers.php` restored, no residual code).

## 2026-09-10 (fix)
- Fixed the `rbm-test-monitor` "Report a Problem" popup: `#rbm-test-monitor-overlay{ display:flex }` in feedback.css (an ID selector) was overriding the browser's own `[hidden]{ display:none }` rule (lower-specificity attribute selector), so the `hidden` attribute set by both the initial closed state and `closeModal()` after a successful submit never actually took visual effect — the overlay stayed visibly open regardless, most noticeable after a hard refresh. Added `#rbm-test-monitor-overlay[hidden]{ display:none; }` to restore correct hide/show behavior. Verified in-browser: overlay is `display:none` on page load and again after a successful submission (previously `display:flex` in both cases).

## 2026-09-10
- Added a new, self-contained `rbm-test-monitor` plugin implementing the RBM Testing Feedback & Activity Monitor (docs/0910-0344-PLAN-RBM-Testing-Feedback-Activity-Monitor.txt, docs/0910-0346-Copilot-REQUEST-Implement-RBM-Testing-Feedback-Activity-Monitor.txt): two new DB tables (`wp_rbm_test_activity`, `wp_rbm_test_feedback`), a shared fail-safe logger, a per-browser test-session cookie, two authenticated REST routes (event logging + feedback submission with optional screenshot upload), a front-end "Report a Problem" control (logged-in testers only) with non-invasive Lessons instrumentation (page_loaded/category_selected/view_changed/client_error), and two admin pages (Activity, Feedback with recent-activity context, CSV export, retention/clear actions). Enabled by default on local/development/staging only, fully inert on production unless explicitly overridden; verified fail-safe (Lessons page keeps working if logging storage is unavailable) and zero front-end footprint when disabled. Deviated from the PLAN's literal "RBAdmin > Testing > ..." menu suggestion — this project's own governance explicitly forbids importing RBAdmin menu assumptions, and no such menu exists in this codebase, so the admin pages live under a new top-level "Testing Monitor" menu instead. No existing Lessons behavior (two-state navigation, deep links, responsive image delivery) was touched.
- Hardened plugin-local image delivery for the `[msch_lessons]` lesson and category tiles: generated 320/640/1024px responsive derivatives (via macOS `sips`, one-time, no new dependency) alongside each existing master in `plugins/rbm-lessons/assets/images/` and `assets/category-icons/`; added one centralized `rbm_msch_responsive_tile_image()` helper that builds `srcset`/`sizes`/`width`/`height`/`alt`/`loading`/`decoding` only from files that actually exist, with the master as the `src` fallback; wired it into both the lesson-tile and category-icon renderers (previously only plain `<img src="master">`). No Media Library attachments, no taxonomy/record changes, no behavior change to the two-state navigation.
- Hardened the `[msch_lessons]` Lessons module (`plugins/rbm-lessons/includes/rbm-lessons.php`) against fragility without redesigning it: a valid `?category=` deep link (or `?category=all`) now renders the correct Instrument View state server-side (select option, nav enabled/disabled state, category heading, lesson-tile visibility, category grid hidden) instead of always defaulting to Category View and relying on JS; an invalid `?category=` value is stripped from the URL via `history.replaceState` instead of lingering; added `aria-live="polite"` to the category heading region; the deep-link JS handler is now scoped per `.msch-lessons` instance instead of a single `document.querySelector`. No taxonomy, lesson records, images, card/tile design, or legacy category-page navigation were touched.

## 2026-09-08
- Added public individual Teacher profile pages for the `msch_teacher` CPT: canonical `/teacher/<slug>/` permalinks, a thin single-teacher renderer (photo, name, instruments, long bio, optional website link) reusing existing helper functions and the existing Teacher/Bio Container Standard (`thesis-teacher-row`/`thesis-teacher-photo`/`thesis-teacher-bio`), and a one-time rewrite-rule flush. Faculty page and Lesson-page teacher output are unchanged.

## 2026-09-04
- Initialized the redbarnmusicorg wp-content repository and installed the project governance files.
- Added the canonical Copilot instructions at .github/copilot-instructions.md and preserved the request/contract docs in docs/.
- Added repository ignore rules for generated files, backups, logs, and local secrets while preserving active site source.
- Committed the working redbarnmusic.org wp-content source for the current Local site while continuing to exclude uploads, Updraft artifacts, backups, caches, logs, and secrets.
