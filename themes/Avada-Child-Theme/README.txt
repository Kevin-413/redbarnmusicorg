Avada Child Theme — README
===========================

REQUIREMENTS

- WordPress Avada parent theme active (this child theme was built and
  tested against Avada 7.12.1; requires the "awb-menu"/Header Layout
  Section markup and the AWB_Widget_Framework sidebar system present in
  Avada 7.x — not tested against older Avada major versions).

INSTALLATION / ACTIVATION

- Upload this entire folder to wp-content/themes/ as "Avada-Child-Theme"
  (keep the folder name if replacing an existing install so WordPress
  keeps the same theme reference).
- In Appearance > Themes, activate "Avada Child" if not already active.
- No database changes, plugin installs, or Theme Options changes are
  required by anything in this package.

RBM STANDARD PAGE TEMPLATES

Two selectable Page Attributes templates in templates/, both enforcing
Avada's standard shared site-width layout from code (functions.php),
independent of a page's own saved Avada Sidebars settings:

- "RBM Standard Page — No Sidebar"
  (templates/rbm-standard-page-no-sidebar.php)
  Standard width, no sidebar, regardless of the page's saved Sidebars
  metabox setting.

- "RBM Standard Page — Right Sidebar"
  (templates/rbm-standard-page-right-sidebar.php)
  Standard width plus a right-hand sidebar rendering Avada's "Sidebar"
  widget area (registered ID: avada-custom-sidebar-sidebar, created via
  Avada's Multiple Sidebars feature). REQUIRED: that widget area must
  exist and contain widgets, or the page fails safe to the No Sidebar
  layout instead of showing a blank column. Go to Appearance > Widgets,
  confirm/create a widget area named "Sidebar", and add widgets to it.

Both templates require() Avada's own unmodified page.php; neither
duplicates parent-theme template code.

HOW TO SELECT A PAGE TEMPLATE

Edit the page > Page Attributes panel (Settings sidebar in the block
editor, or the classic-editor metabox) > Template dropdown > choose
"RBM Standard Page — No Sidebar" or "RBM Standard Page — Right Sidebar"
> Update/Publish. This is a normal, per-page, reversible selection; nei-
ther template is applied automatically to any page.

DESKTOP HEADER / BANNER / MENU CENTERING

style.css adds one media-query block (min-width: 1025px) scoped to
Avada's Header Layout Section (.fusion-tb-header) only:
- centers the banner image within its column
- centers the full navigation menu as one group (overrides Avada's
  --awb-justify-content custom property to "center")
Banner size and menu item spacing are unchanged — only alignment.

MOBILE BREAKPOINT BEHAVIOR

The 1025px threshold matches this site's own mobile-menu breakpoint
(data-breakpoint="1024" on the nav element). At 1024px and below, Avada's
mobile/hamburger menu takes over and none of the desktop centering rules
apply — mobile header, mobile menu, and the mobile bottom action bar are
unchanged. Sidebar layout (Right Sidebar template) stacks the sidebar
below the main content on mobile, using Avada's native responsive
behavior (no custom mobile CSS added).

VERSION / DATE / COMMITS

- Package version: 2026-09-20
- Relevant commits (Local git history):
  - 6b1f869 — Adult Student name-sync + phone-scrambler superseded note
  - cdac764 — RBM Standard Page No-Sidebar / Right-Sidebar templates;
    Home set to Right Sidebar
  - 3813f70 — Desktop header banner/nav centering
- See docs/0920-1316-Copilot-REPLY-Home-Right-Sidebar-Templates-Deployment.txt
  for full verification details and the one required manual Live step.
