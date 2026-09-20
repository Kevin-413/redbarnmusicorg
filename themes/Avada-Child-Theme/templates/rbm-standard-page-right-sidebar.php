<?php
/**
 * Template Name: RBM Standard Page — Right Sidebar
 *
 * Standard shared-site-width Avada page with a right sidebar, enforced
 * from code (see functions.php) regardless of this page's saved Avada
 * Sidebars settings. Requires a widget area registered with the ID in
 * RBM_STD_PAGE_SIDEBAR_ID (functions.php) to contain widgets; if it does
 * not, the page fails safe to the same layout as the No Sidebar template.
 * Delegates all actual rendering to Avada's own page.php so nothing here
 * duplicates parent-theme template code.
 */

require get_template_directory() . '/page.php';
