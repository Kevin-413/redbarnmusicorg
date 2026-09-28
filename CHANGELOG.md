# Changelog

## 2026-09-28 (Three new pages: Studio Tour, Recitals & Events, Student & Parent Resources)
- Created three new native Avada pages, each as page header + one
  `[fusion_global]` Library reference (same pattern as prior conversions):
  - **Studio Tour** (page 20978, `/studio-tour/`) → Library item "Studio Tour"
    (20975): embeds the existing studio-tour video (`https://youtu.be/Qzsbsh0dyIA`)
    via `fusion_youtube`, plus buttons to Directions, Faculty, and Lessons
    Inquiry. Added to the nav menu under **About**.
  - **Recitals & Events** (page 20979, `/recitals-events/`) → blank
    placeholder Library item "Recitals & Events (Placeholder)" (20976),
    "Content coming soon." No event content invented.
  - **Student & Parent Resources** (page 20980, `/student-parent-resources/`)
    → blank placeholder Library item "Student & Parent Resources
    (Placeholder)" (20977), "Content coming soon." No resource content
    invented.
- Verified all three pages return HTTP 200 and render as expected (video ID,
  buttons, and placeholder text confirmed on live pages).
- No other pages, menus, or global settings were changed.

## 2026-09-27 (Three new pages created from docs, as native Avada Library content)
- Created three new native Avada pages from source docs in `docs/`, each as
  page header + `[fusion_global]` reference to a matching new Library item
  (native `fusion_builder_container`/`fusion_text`, same pattern as Reviews):
  - **Start Here / How Lessons Work** (page 20972, `/start-here-how-lessons-work/`)
    → Library item "Start Here - How Lessons Work" (20969), from
    `docs/0927-2152-0927-Start Here : How Lessons Work.txt`.
  - **Tuition & Financial Aid** (page 20973, `/tuition-financial-aid/`)
    → Library item "Tuition and Financial Aid" (20970), from
    `docs/0927-2152-Tuition & Financial Aid.txt`.
  - **Frequently Asked Questions** (page 20974, `/frequently-asked-questions/`)
    → Library item "Frequently Asked Questions" (20971), from
    `docs/0927-2152-Frequently Asked Questions.txt`.
- Internal references (Instruments, Faculty, Lessons Inquiry, Rentals,
  Contact us) were linked to their existing page URLs.
- All three pages use the same page template as other native pages
  (`templates/rbm-standard-page-no-sidebar.php`) and verified HTTP 200 with
  expected headings rendering.
- Not yet added to site navigation menus — pages exist and are published
  but are not linked from the menu yet.

## 2026-09-27 (Reviews page: moved to Avada Library element)
- Moved the Reviews page's self-contained CSS/HTML/JS widget (star ratings,
  review cards, More/Less toggle script) unchanged into a new Avada Library
  item, "Reviews - Display" (post 20962), using a `[fusion_code]` element so
  the `<style>`/`<script>` blocks are stored base64-encoded and bypass
  `wpautop`/`wptexturize` corruption that blocked an earlier attempt to wrap
  this content in `fusion_text`.
- The Reviews page (20856) content is now just the page header plus
  `[fusion_global id="20962"]` — the CSS/HTML/JS itself was not rewritten,
  only relocated, per the "move unchanged first" request.
- Verified equivalence by rendering the real page object (matching its actual
  post author, since Avada's code-block sanitizer behavior depends on
  author capability) through the full `the_content` pipeline before and
  after: `<style>`/`<script>` blocks are byte-identical, visible text matches
  exactly (post-`wptexturize`), and review-card/toggle counts match (4 cards,
  10 toggle references).
- Backed up the pre-change page content to
  `docs/0927-1710-Reviews-20856-Backup.txt`. No other pages, navigation, or
  global Avada settings were changed.

## 2026-09-27 (Home page: extract embedded sections to the Avada Library)
- Replaced the six raw `fusion_builder_container` blocks embedded directly in
  Gutenberg reusable block 20440 ("Home Body (Synced)", used by Home 20046 and
  Home - LEGACY 20947) with `[fusion_global id="…"]` references to their
  matching existing Library items: Lessons Starting Now (20865), Open House
  (20866), Block Party (20867), Gallery Spring Recital (20868), Scholarships
  (20869), and What Families Say (20870).
- Five of the six sections already matched their Library item byte-for-byte;
  only Lessons Starting Now was missing a scoped layout `<style>` fix present
  on the live page, so Library item 20865 was updated to include it before
  the swap, keeping the visible layout unchanged.
- Verified equivalence by rendering the reusable block's content through the
  full `the_content` filter pipeline (including `wptexturize`) before and
  after the change: output is visually identical, all six section headings
  render, and no leftover `[fusion_global]` shortcode tags remain unprocessed.
- Backed up the pre-edit content to
  `docs/0927-1611-Home-Reusable-Block-20440-Backup.txt` and
  `docs/0927-1611-Library-20865-Lessons-Starting-Now-Backup.txt` for rollback.
  No public page content, navigation, or global Avada settings were changed.

## 2026-09-26 (Gallery import package prepared)
- Created `docs/0926-1257-Gallery-Library-Container-Import.xml`, a single-item
  WXR import file for the existing Gallery Library Container. Cleared only the
  Local `image_ids` selection in this import copy so live-site image IDs can be
  selected manually after import; the populated Local container and original
  export remain unchanged.
- Validated the XML and confirmed it contains one `fusion_element` with the
  Masonry settings, intro, and photographer credit. No live upload or content
  changes were made.
- Details: `docs/0926-1257-Copilot-REPLY-Transfer-Gallery-To-Live.txt`.

## 2026-09-26 (native Avada Gallery finalized and exported)
- Replaced the temporary image selections in Avada Library Container 20890
  with the 18 uploaded `recital-rachel-01.webp` through
  `recital-rachel-18.webp` attachments. Kept its existing Masonry layout,
  responsive columns, spacing, lightbox, and Rachel Bellenoit Photography
  credit.
- Verified 18 images with no broken assets or horizontal overflow at desktop,
  tablet, and mobile widths; checked native lightbox open, next/previous, and
  close behavior.
- Exported only Library Container 20890 to
  `docs/0926-1240-Gallery-Library-Container.2026-09-26.xml` (one WXR item).
  No live-site content or Media Library files were changed.
- Full attachment ID list, final settings, tests, and transfer notes:
  `docs/0926-1240-Copilot-REPLY-Implement-Modern-Native-Avada-Gallery.txt`.

## 2026-09-23 (Baritone Horn image selection)
- Restored the requested non-`-1` Baritone Horn image as attachment 20839,
  updated Instrument 20547 to use it, and removed the former `-1` attachment
  and file. Verified the restored file against the untouched source hash.

## 2026-09-23 (Baritone Horn media consolidation)
- Retained the active Baritone Horn attachment (20760), normalized the
  Instrument filename metadata, and removed unused attachment 20761 and its
  file. Verified the Instrument has one remaining image URL.

## 2026-09-23 (legacy attachment metadata cleanup)
- Cleaned contaminated metadata on four legacy Media Library records:
  `eyewink`, `Tristan Sullivan`, `Tom Williams`, and `Susan Altabet`.
  Removed concatenated teacher text, restored record-specific descriptions and
  alt text where appropriate, cleared copied captions/excerpts, and verified
  the image files were unchanged.

## 2026-09-23 (upright-piano media duplicate consolidation)
- Assigned the existing upright-piano attachment (20818) to the `upright-piano`
  Instrument (20570), assigned the intended Piano image (20804) to the `piano`
  Instrument (20564), and removed the newly uploaded exact duplicate attachment
  (20838). Verified both intended files remain present and no reference remains
  to the deleted record.

## 2026-09-21 (Avada presentation-layer check on redbarnmusic-live: already in parity, CSS cache regenerated)
- Per docs/0921-2346-Copilot-REQUEST-FINAL-Complete-Avada-Website-Transfer-To-Live-Clone.txt:
  compared the complete Avada presentation layer (child theme files, Avada Theme Options,
  Additional CSS, widgets/sidebars, nav menus) between `redbarnmusicorg-avada` (source) and
  `redbarnmusic-live` (destination). LOCAL-TO-LOCAL only; redbarnmusic.com production was never
  accessed or touched.
- Findings: the two sites' presentation layer was already effectively in parity, not missing -
  Avada Child Theme files were byte-identical except line-endings (CRLF vs LF, no functional
  difference); Avada Theme Options (`fusion_options`, 1,052 keys) differed only in the logo URL's
  domain (already correctly self-referential on each site) and one unrelated cache-clear flag;
  both Additional CSS posts (20363, 19713) were identical; the main nav menu was identical
  (12 items); sidebar/footer widget positions matched, with destination's own widget content
  already more current than source's (real Fusion shortcodes/images vs. source's older plain
  HTML) - left untouched.
- The one real, concrete difference found: Avada's compiled dynamic CSS/JS cache
  (`wp-content/uploads/fusion-styles/`, `fusion-scripts/`) on `redbarnmusic-live` was stale from
  before the backup restore. Backed up the existing compiled files, cleared the cache and the
  `fusion_dynamic_css_posts` option, then triggered regeneration by loading pages. Flushed rewrite
  rules and object cache afterward.
- Confirmed via side-by-side browser screenshots that Home renders pixel-identical between the
  two sites, and Directions (previously rebuilt) renders its full header/nav/footer/mobile-bar
  chrome correctly on the destination.
- No theme files, database records, menus, or widgets were changed - only the regenerable Avada
  CSS/JS cache. Full findings in
  docs/0921-2346-Copilot-REPLY-FINAL-Complete-Avada-Website-Transfer-To-Live-Clone.txt.

## 2026-09-21 (selective Local-to-Local page transfer: redbarnmusicorg-avada -> redbarnmusic-live)
- Per docs/0921-2315-Copilot-REQUEST-Implement-Avada-Pages-In-Live-Clone.txt: after the site
  owner restored `redbarnmusic-live` from the Sept 21, 2026 production backup, selectively
  transferred completed page work into that clone (LOCAL-TO-LOCAL only; redbarnmusic.com
  production was never accessed or touched).
- Fixed `redbarnmusic-live/app/public/wp-config.php` DB_HOST (was missing its Local MySQL socket
  path, so wp-cli could not connect) to match the working convention already used by
  `redbarnmusicorg-avada`'s wp-config.php.
- Backed up `redbarnmusic-live`'s full database before any change (see reply doc for path).
- Compared all 11 in-scope pages against their `redbarnmusic-live` counterparts (matched 1:1 by
  identical post ID/slug - both sites share the same lineage) before changing anything. Found
  `redbarnmusic-live` already had its own current, authoritative content for the Home page
  (newer Open House/Block Party dates, native-block Gallery/Scholarships sections) and for
  Community Links and Policies - left all three untouched.
- Updated 8 `redbarnmusic-live` pages that were missing only the standard page-title header
  banner or had a real, documented gap: About (2), Calendars (18136), Gallery (934), Rentals
  (117), Lessons Inquiry (19278) - added the header banner only, kept each page's own existing
  Forminator form ID/content unchanged; Online Privacy Policy (20264) - replaced its ad-hoc bold
  text title with the standard header banner; Contact us (126) - added header banner, replaced
  the exposed plain-text phone number with the `[rbm_phone]` scrambler shortcode (verified
  `rbm-contact-scrambler` already active there with the matching number), and fixed a pre-existing
  malformed `href` (missing closing quote); Directions (91) - was effectively empty
  (whitespace-only), replaced with the completed Local version (map, CTA button/image, no
  domain-specific IDs to remap).
- Verified via HTTP smoke test: all 11 pages return 200, each updated page shows exactly one page
  title banner, the Contact page's phone renders through the scrambler markup (not plain text),
  and the Directions map element renders. No PHP fatal errors observed. Flushed rewrite rules and
  object cache afterward.
- Full manifest, before/after checksums, and rollback instructions are in
  docs/0921-2315-Copilot-REPLY-Implement-Avada-Pages-In-Live-Clone.txt.

## 2026-09-21 (set up secure, allowlisted LIVE deployment tooling)
- Per the site owner's explicit authorization (docs/0921-1418-Copilot-REQUEST-Set-Up-Secure-
  Direct-Live-Deployment.txt), added a new "Live Deployment (redbarnmusic.org)" section to
  .github/copilot-instructions.md: LIVE access stays off by default, permitted only per explicit
  request, and only through deploy-tools/rbm-deploy.sh - never ad hoc FTP/SSH.
- New deploy-tools/rbm-deploy.sh: SSH/SFTP-based tool with test-connection, inventory (read-only),
  diff (checksum compare), deploy (dry run unless --apply), verify, and rollback commands. Only
  paths listed in deploy-tools/allowlist.txt (starting with just mu-plugins/rbm-site-settings.php)
  can ever be deployed or rolled back; every applied deploy backs up the current live file first
  and verifies the upload by checksum before reporting success; missing config fails closed;
  logs are timestamped under deploy-tools/logs/ with credential-like values redacted.
- New deploy-tools/.env.example (blank credential template) and deploy-tools/OPERATOR-GUIDE.txt
  (copy/paste commands for setup, test, inventory, dry-run, apply, verify, rollback).
- .gitignore: added deploy-tools/.env, deploy-tools/backups/, deploy-tools/logs/ so no credentials,
  backups, or logs can be committed.
- Tested against a local mock "live" directory (no real credentials used): confirmed dry-run
  deploy makes no changes, --apply backs up the old file then uploads and verifies by checksum,
  rollback restores the pre-deploy backup, a non-allowlisted path is refused, missing .env fails
  closed with a clear message, and the redaction pattern masks KEY/TOKEN/SECRET/PASS-like values.
  No real live connection was attempted or configured; per the request, no pending changes were
  deployed during this setup.

## 2026-09-21 (fix RBM Site Settings URL fields rejecting relative paths)
- `mu-plugins/rbm-site-settings.php`: URL-type fields (Contact-Page URL, Directions/Map URL,
  Lessons Inquiry/Registration/Contact Form/Student Login/Teacher Login URLs, Donation URL,
  Scholarship URL, all social URLs) rendered as `<input type="url">`, which browsers refuse to
  submit unless the value is an absolute URL with a scheme - silently blocking Save for the
  intentionally relative values (e.g. `/contact/`) with a "Please enter a URL" tooltip and no
  server-side error. Changed to `type="text"`; server-side sanitization (`esc_url_raw`) is
  unchanged and already accepts both relative and absolute values correctly.
- Verified via `rbm_site_settings_render_field()` directly: field now renders `type="text"`.

## 2026-09-21 (link the embedded map's marker to Google Maps)
- Directions (91): the embedded OpenStreetMap marker's popup content now includes a "Get
  Directions on Google Maps" link (same destination as the button/image above it), so clicking
  the pin on the bottom map also links out to Google Maps, not just the static image above it.

## 2026-09-21 (add linked map image to Directions)
- Directions (91): added the requested image (hotlinked from
  `https://redbarnmusic.com/wp-content/uploads/2026/09/409-main-st-amherst-ma-google-maps.png`,
  not imported into the local Media Library) between the CTA button and the embedded map, linked
  to the same Google Maps destination as the button (`https://maps.app.goo.gl/Nobei4zRn79uue4G7`).

## 2026-09-21 (embed a working map on Directions)
- Tried embedding Google's free no-API-key iframe (`maps.google.com/maps?q=...&output=embed`,
  then the underlying `google.com/maps/embed?...&pb=...` endpoint directly) - both requests were
  aborted by the browser (`net::ERR_ABORTED`) and never rendered; no Google Maps API key is
  configured anywhere in this install (checked Avada's own native Google Map option, `gmap_api`,
  which is also blank), so a reliable Google embed isn't available without one.
- Used Avada's own native, no-API-key map element instead: `[fusion_openstreetmap]` /
  `[fusion_openstreetmap_marker]` (Leaflet + OpenStreetMap tiles, already bundled with this Avada
  install). Looked up real coordinates for 409 Main St, Amherst, MA 01002 via OpenStreetMap
  Nominatim (42.3753245, -72.5120998) and passed them directly as `latitude`/`longitude` - the
  shortcode's own `address` attribute silently geocoded to the wrong place (fell back to the
  plugin's hardcoded NYC-area default coordinates) so explicit coordinates were used instead.
- Kept the existing "Get Directions on Google Maps" button (still links out to the real Google
  Maps app/site) above the new embedded map.
- Verified at 1440px and 375px: map renders centered on the correct Amherst, MA location with
  working zoom controls, no PHP/debug-log errors, no browser console errors.

## 2026-09-21 (fix Directions page; investigate Gallery/Scholarships blockers)
- Directions (91): was empty content relying on a dead `_thesis_redirect` postmeta (Thesis theme
  removed, nothing reads that meta anymore, page returned 200 with a blank body). Added the
  standard header/title wrapper plus one "Get Directions on Google Maps" button linking to the
  saved RBM Site Settings `directions_url` (`https://maps.app.goo.gl/Nobei4zRn79uue4G7`), and
  deleted the now-unused `_thesis_redirect` meta. Verified live at 1440px and 375px, link opens
  the correct Maps destination, no PHP/debug-log/console errors beyond pre-existing benign font-
  preload warnings.
- Gallery (934) investigated, not changed: `[envira-gallery id="20120"]` is registered and
  executing correctly, but the gallery's own `_eg_gallery_data` postmeta has zero images in its
  `gallery` array (confirmed via direct shortcode invocation and gallery data dump) — it is
  configured (title, slug, display settings) but was never populated with photos. This is a
  content gap, not a code/shortcode bug. Only one loosely-related image exists in the Media
  Library (ID 20637, the same single collage graphic already used elsewhere as a teaser), not a
  set of individual recital photos suitable for populating the gallery grid. Left unchanged and
  reporting the blocker rather than guessing at real gallery content.
- Scholarships image investigated, not changed: searched the Media Library for a clean
  replacement for the current photo (ID 20639, `fotrb-banner-pic-CROP-1.jpg`); the only other
  candidate found (ID 481, `fotrb-banner-pic-CROP.jpg`) is the same collage with the same baked-in
  "PayPal donate now" graphic. No clean replacement exists. Left unchanged and reporting.

## 2026-09-20 (convert remaining Legacy/Mixed pages to the standard Avada page header)
- Prepended the site's standard `<div class="thesis-page-header"><h1 class="thesis-page-title">`
  wrapper to 8 pages that previously had no page-title banner: Contact us (126), About (2), Online
  Privacy Policy (20264), Rentals (117), Calendars (18136), Community Links (20632), Gallery (934),
  Lessons Inquiry (19278). IDs, slugs, published status, and all existing text/links/shortcodes/
  forms preserved unchanged below the new banner.
  - Online Privacy Policy: removed one redundant `<strong>Online Privacy Policy</strong>` line
    that duplicated the new banner text; no other wording changed.
  - Community Links: reused the page's own existing `<h1>Community Music Links in Western
    Massachusetts</h1>` text as the new banner title (preserves the visible headline exactly)
    and removed the now-redundant original `<h1>` tag.
- Home Body (Synced Pattern, post 20440, shared by Home 20046 and its redirect twin 255):
  converted the still-Gutenberg "Gallery" and "Scholarships" sections to the same Avada Builder
  structure (fusion_builder_container/row/column, fusion_title, fusion_imageframe, fusion_text,
  fusion_button) already used for the Lessons/Open House/Block Party sections above them. Text,
  links, and displayed images preserved exactly. Corrected two stale attachment-ID references in
  the original wp:image blocks (20152 and 19649, both pointing at the wrong media item) to the
  real attachment IDs for the images actually shown (20637 gallery photo, 20639 scholarships
  photo). No button was added to Scholarships since none existed in the original content.
- Directions (91): left unchanged. Its content is empty; the only related data is a dead
  `_thesis_redirect` postmeta (Thesis theme removed, meta no longer honored by any current code)
  pointing to `https://goo.gl/maps/hnj4GT5hRjk`. There is no existing copy to move into the
  standard template, so no mechanical conversion was possible without inventing new content.
- Orphaned pages (duplicate Contact Us 20335, Student Registration "reg" 6069, draft Thank You
  pages 6336/13185): left unchanged, per request, status-only.
- Verified: all 8 converted pages return HTTP 200 with exactly one `<h1>`, forms/shortcodes still
  render (Contact form 20710, Lessons Inquiry form 20709, Community Links content, Calendars PDF
  links), Home and its twin both show the new Gallery/Scholarships sections correctly on desktop
  (1440px) and mobile (375px) with no leaked shortcode text, and no PHP/debug-log errors.
- Found, not fixed (pre-existing, out of scope for this mechanical pass): the Gallery page's
  `[envira-gallery id="20120"]` shortcode renders no output even though the gallery post exists;
  the Scholarships section's photo has an outdated "PayPal donate now" graphic baked into the
  static image itself.

## 2026-09-20 (fix broken Contact form)
- Contact page (ID 126): replaced the broken `[forminator_form id="20334"]` shortcode (form 20334
  does not exist) with `[forminator_form id="20710"]`, the existing "Contact Us form" (Parent/
  Adult Name, Student Name, Email, Phone, Preferred Contact Method, Message, captcha; notifies
  `{admin_email}`). Verified /contact/ renders all fields, submitted a real test entry (confirmed
  in `wp_frmt_form_entry`, then deleted), and confirmed [rbm_phone] still renders correctly next
  to the form. No PHP or debug-log errors.

## 2026-09-20 (finish RBM Site Settings for live: verified values, Contact page phone, Global Sign Up URL)
- Populated `rbm_site_settings` with verified real values only: school_name, street_address,
  city, state, zip, contact_page_url (/contact/), directions_url (existing Google Maps link),
  lessons_inquiry_url (/lessons-inquiry/), registration_url (/reg/, an existing published
  Student Registration page not otherwise linked from any menu), contact_form_url (/contact/),
  and facebook_url. donation_url/donation_button_label/donation_new_tab left untouched.
  student_login_url, teacher_login_url, scholarship_url, and the other social fields remain
  blank \u2014 no verified destination exists for them.
- Contact us page (ID 126): replaced the hardcoded plain-text phone number with the [rbm_phone]
  shortcode. Verified the page now renders the scrambler's protected markup instead of raw text.
- Set RBM Instruments' Global Sign Up URL (`rbm_msch_global_signup_url`, previously blank) to the
  Lessons Inquiry URL. This was a real, live bug: all 13 published Teacher records have no custom
  Sign Up URL of their own and were rendering an empty `href=""` Sign Up button (the Teacher
  template has no empty-URL guard, unlike the Instrument template). Verified a Teacher profile
  page now renders a working Sign Up button that still preserves `?instrument=`/`&teacher=`
  context; verified an Instrument page's own custom Sign Up URL is unaffected.
- Found and did not fix (out of scope, reported to site owner): the Contact page's own embedded
  Forminator form shortcode references form ID 20334, which does not exist as a post \u2014 the
  contact form on the live Contact page is currently broken independently of this work.

## 2026-09-20 (add RBM Site Settings page)
- New `mu-plugins/rbm-site-settings.php`: Settings > RBM Site Settings, storing one namespaced
  option (`rbm_site_settings`) via the Settings API, with a `rbm_site_setting( $key, $default )`
  helper for reading values elsewhere. Four sections: Contact Information (school name/address/
  contact-page/directions URLs — no phone/email fields, links to RBM Contact Scrambler instead),
  Registration Links (Lessons Inquiry/Registration/Contact Form/Student Login/Teacher Login URLs —
  no general Sign Up URL field, links to RBM Instruments' existing Global Sign Up URL instead),
  Donations and Scholarships (Donation URL, button label, scholarship URL, open-in-new-tab), and
  Social Media (Facebook/Instagram/YouTube/TikTok/LinkedIn).
- Saved the approved Zeffy campaign as the Donation URL and added a `widget_text` filter that
  swaps the hardcoded `friendsoftheredbarn.org` donate.gif link in Text widgets 2 and 6 for the
  saved Donation URL (widget 4 "Connect" has no donation link, only Facebook/Contact links, so it
  is untouched). Verified live on the homepage: the sidebar donate link now opens the Zeffy
  campaign and the old `friendsoftheredbarn.org` href no longer appears in the rendered page.
- All fields sanitize on save (`esc_url_raw` for URLs, `sanitize_text_field` for text, boolean for
  the checkbox) and escape on output; empty fields render as empty inputs, not broken links.
- Access requires `manage_options`; verified anonymous requests to the new admin page redirect to
  `wp-login.php`, and the Settings API form saves and reloads correctly when logged in.

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
