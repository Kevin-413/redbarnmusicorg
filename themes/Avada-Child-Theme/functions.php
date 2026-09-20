<?php

function theme_enqueue_styles() {
    wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', [] );
}
add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles', 20 );

// Phone/text click-to-call links are now handled by the standalone rbm-contact-scrambler plugin
// (docs/0918-1649-Copilot-REQUEST-Build-Standalone-Contact-Scrambler-Plugin.txt), which superseded
// this child theme's rbm_phone_number option + js/rbm-phone-links.js + .rbm-phone-link/.rbm-text-
// link placeholders. See that plugin for [rbm_phone]/[rbm_text]/[rbm_email].

function avada_lang_setup() {
	$lang = get_stylesheet_directory() . '/languages';
	load_child_theme_textdomain( 'Avada', $lang );
}
add_action( 'after_setup_theme', 'avada_lang_setup' );
/**
 * Fix: Avada's own AWB_Widget_Framework hooks - set_sidebar_body_classes()
 * ('wp' priority 15) and add_sidebars() ('wp' priority 20) - resolve the
 * per-page sidebar setting (pages_sidebar / default_sidebar_pos) incorrectly
 * on their first run for some page requests, causing the sidebar to be
 * silently skipped (both the 'has-sidebar' body class and the rendered
 * widget area) even though the page's saved sidebar setting is correct.
 * Re-running the same two native Avada methods again later in the same
 * request resolves them correctly. No Avada core files are modified.
 */
function redbarn_fix_sidebar_rendering_timing() {
	if ( function_exists( 'AWB_Widget_Framework' ) ) {
		AWB_Widget_Framework()->set_sidebar_body_classes();
		AWB_Widget_Framework()->add_sidebars();
	}
}
add_action( 'wp', 'redbarn_fix_sidebar_rendering_timing', 21 );

/**
 * RBM Standard Page templates ("templates/rbm-standard-page-no-sidebar.php"
 * and "templates/rbm-standard-page-right-sidebar.php").
 *
 * These two selectable Page Attributes templates enforce Avada's standard
 * shared site-width layout, and, for the sidebar variant, the "Sidebar"
 * widget area in the right position — deterministically from code,
 * regardless of a page's own saved Avada Sidebars settings (pages_sidebar /
 * default_sidebar_pos postmeta) or the site's global Sidebars Theme
 * Option. Root cause this replaces: Avada's legacy "100% Width" page
 * template (100-width.php) both stretches #content to 100% and skips the
 * avada_after_content hook entirely, so a page using it can never show a
 * matching-width column or a sidebar no matter what its settings say.
 * Both templates require() Avada's own unmodified page.php for all actual
 * markup/hooks; nothing here duplicates parent-theme template code.
 *
 * REQUIRED for the Right Sidebar template: a widget area registered with
 * the ID in RBM_STD_PAGE_SIDEBAR_ID must exist and contain widgets on
 * whichever site uses this template (Avada shows it as "Sidebar" under
 * Appearance > Widgets, via Avada's Multiple Sidebars feature). If that
 * widget area is missing or empty, this fails safe: the page silently
 * renders as the No Sidebar layout instead of a blank/broken column.
 *
 * Both templates are fully optional and selected per-page via Page
 * Attributes like any other template — including Home, which must have
 * one of them assigned explicitly; there is no automatic sidebar/width
 * override for any specific page.
 */
define( 'RBM_STD_PAGE_NO_SIDEBAR_TPL', 'templates/rbm-standard-page-no-sidebar.php' );
define( 'RBM_STD_PAGE_RIGHT_SIDEBAR_TPL', 'templates/rbm-standard-page-right-sidebar.php' );
define( 'RBM_STD_PAGE_SIDEBAR_ID', 'avada-custom-sidebar-sidebar' );

function rbm_std_page_is_right_sidebar_template() {
	return is_page_template( RBM_STD_PAGE_RIGHT_SIDEBAR_TPL );
}

function rbm_std_page_is_no_sidebar_template() {
	return is_page_template( RBM_STD_PAGE_NO_SIDEBAR_TPL );
}

function rbm_std_page_right_sidebar_active() {
	return rbm_std_page_is_right_sidebar_template() && is_active_sidebar( RBM_STD_PAGE_SIDEBAR_ID );
}

add_filter(
	'avada_has_sidebar',
	function ( $has_sidebar ) {
		if ( rbm_std_page_is_no_sidebar_template() ) {
			return false;
		}
		if ( rbm_std_page_is_right_sidebar_template() ) {
			return rbm_std_page_right_sidebar_active();
		}
		return $has_sidebar;
	}
);

add_filter(
	'avada_has_double_sidebars',
	function ( $has_double ) {
		if ( rbm_std_page_is_no_sidebar_template() || rbm_std_page_is_right_sidebar_template() ) {
			return false;
		}
		return $has_double;
	}
);

add_filter(
	'avada_sidebar_context',
	function ( $sidebar, $page_id, $nr, $global ) {
		if ( 1 !== $nr ) {
			return $sidebar;
		}
		if ( rbm_std_page_is_no_sidebar_template() ) {
			return '';
		}
		if ( rbm_std_page_right_sidebar_active() ) {
			return [ RBM_STD_PAGE_SIDEBAR_ID ];
		}
		return $sidebar;
	},
	10,
	4
);

/**
 * Avada's own sidebar setup resolves the ACTUAL rendered widget-area ID
 * and position from the page's saved postmeta (not from the
 * avada_sidebar_context filter above, which only drives the has-sidebar
 * body class / has_sidebar() detection). Re-assert the intended widget
 * area + right position directly after redbarn_fix_sidebar_rendering_timing()
 * re-runs Avada's sidebar setup at priority 21.
 */
function rbm_std_page_enforce_sidebar_widget_area() {
	if ( ! rbm_std_page_right_sidebar_active() || ! function_exists( 'AWB_Widget_Framework' ) ) {
		return;
	}
	$framework = AWB_Widget_Framework();
	if ( ! isset( $framework->sidebars ) || ! is_array( $framework->sidebars ) ) {
		return;
	}
	$framework->sidebars['sidebar_1'] = RBM_STD_PAGE_SIDEBAR_ID;
	$framework->sidebars['position']  = 'right';
}
add_action( 'wp', 'rbm_std_page_enforce_sidebar_widget_area', 22 );

/**
 * Mobile-only 4-button bottom action bar (Site Frame Plan, mobile bottom
 * action bar). Real destinations only: Lessons landing page, the site's
 * current Lessons Inquiry sign-up flow, the Contact page, and a Menu
 * button that opens Avada's own mobile nav (triggers the existing
 * .awb-menu__m-toggle button already in the header, rather than
 * duplicating the menu). Hidden on desktop/tablet via CSS (Additional
 * CSS post 20363); shown only below the existing mobile breakpoint.
 * Not a plugin; markup output only.
 */
function redbarn_mobile_bottom_action_bar() {
	?>
	<nav class="rbm-mobile-bottom-bar" aria-label="Quick actions">
		<a class="rbm-mobile-bottom-bar-btn" href="<?php echo esc_url( home_url( '/lessons/#page-title' ) ); ?>">
			<span class="rbm-mobile-bottom-bar-icon" aria-hidden="true">&#9834;</span>
			<span class="rbm-mobile-bottom-bar-label">Lessons</span>
		</a>
		<a class="rbm-mobile-bottom-bar-btn" href="<?php echo esc_url( home_url( '/lessons-inquiry/#page-title' ) ); ?>">
			<span class="rbm-mobile-bottom-bar-icon" aria-hidden="true">&#9998;</span>
			<span class="rbm-mobile-bottom-bar-label">Sign Up</span>
		</a>
		<a class="rbm-mobile-bottom-bar-btn" href="<?php echo esc_url( home_url( '/contact/#page-title' ) ); ?>">
			<span class="rbm-mobile-bottom-bar-icon" aria-hidden="true">&#9993;</span>
			<span class="rbm-mobile-bottom-bar-label">Contact</span>
		</a>
		<button type="button" class="rbm-mobile-bottom-bar-btn" id="rbm-mobile-menu-trigger" aria-label="Menu">
			<span class="rbm-mobile-bottom-bar-icon" aria-hidden="true">&#9776;</span>
			<span class="rbm-mobile-bottom-bar-label">Menu</span>
		</button>
	</nav>
	<script>
	( function () {
		var btn = document.getElementById( 'rbm-mobile-menu-trigger' );
		if ( ! btn ) {
			return;
		}
		btn.addEventListener( 'click', function () {
			var toggle = document.querySelector( '.awb-menu__m-toggle' );
			if ( toggle ) {
				toggle.click();
			}
		} );
	} )();
	</script>
	<?php
}

/**
 * Redirect true frontend 404s to post 255 ("Welcome to the Red Barn - you
 * were redirected") instead of rendering Avada's default 404 template.
 * Temporary (302) redirect while on local dev; resolves post 255's own
 * current permalink rather than hard-coding the hostname. Skips admin,
 * REST/API, cron, feeds, and any non-404 request so valid pages, post 255
 * itself, and asset/API requests are never touched (no redirect loop).
 */
function redbarn_redirect_404_to_post_255() {
	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() || is_feed() ) {
		return;
	}
	if ( ! is_404() ) {
		return;
	}
	$target = get_permalink( 255 );
	if ( ! $target ) {
		return;
	}
	wp_safe_redirect( $target, 302 );
	exit;
}
add_action( 'template_redirect', 'redbarn_redirect_404_to_post_255' );
add_action( 'wp_footer', 'redbarn_mobile_bottom_action_bar', 20 );

/**
 * Retired: sitewide test-announcement strip (public testing period ended).
 * Function kept for quick reinstatement; hook removed so it no longer renders.
 */
function rbm_test_site_notice() {
	echo '<div class="rbm-test-site-notice"><strong>We’re testing our new website!</strong> If you notice anything confusing or broken, please use the <strong>Suggestions</strong> button at the bottom and let us know. Thank you!</div>';
}

/**
 * Forminator Lessons Inquiry — Adult Student Name Sync
 *
 * PURPOSE
 * When "An Adult Student" is selected in Forminator field `radio-1`,
 * keep Student Name (`name-1`) synchronized with Parent / Guardian Name
 * (`name-2`). For parent/minor submissions, the two name fields remain
 * independent.
 *
 * CURRENT FORMINATOR FIELD MAP
 * - `radio-1` = "I am..." choice
 * - `name-1`  = Student Name
 * - `name-2`  = Parent / Guardian Name
 *
 * FUTURE DEVELOPER NOTES
 * 1. If the Forminator form is rebuilt, duplicated, or its field IDs change,
 *    update the three selectors below to match the new Forminator field names.
 * 2. The Adult option is detected first by checking whether the selected radio
 *    value contains the word "adult". A label-text fallback is included in case
 *    Forminator stores a different internal value.
 * 3. Keep this behavior limited to Adult Student submissions. Do not copy the
 *    Parent / Guardian Name into Student Name for under-18 students.
 * 4. The script dispatches both `input` and `change` events after copying the
 *    value so Forminator's own validation/conditional logic can see the update.
 * 5. If this logic is later moved into a dedicated JS file or plugin, remove
 *    this footer-injected version to avoid running the sync twice.
 * 6. After any change, test both workflows:
 *      - Adult Student: name-2 should populate and stay synced to name-1.
 *      - Parent of Student: name-1 and name-2 should remain independent.
 *
 * This is intentionally a small child-theme helper; it does not modify
 * Forminator plugin files.
 */
function redbarn_forminator_adult_student_name_sync() {
	?>
	<script>
	( function () {
		function isAdultStudentSelected() {
			var selected = document.querySelector( 'input[name="radio-1"]:checked' );

			if ( ! selected ) {
				return false;
			}

			if ( String( selected.value ).toLowerCase().indexOf( 'adult' ) !== -1 ) {
				return true;
			}

			var label = selected.closest( 'label' );
			return !! ( label && label.textContent.toLowerCase().indexOf( 'adult student' ) !== -1 );
		}

		function syncAdultStudentName() {
			if ( ! isAdultStudentSelected() ) {
				return;
			}

			var parentName  = document.querySelector( 'input[name="name-2"]' );
			var studentName = document.querySelector( 'input[name="name-1"]' );

			if ( ! parentName || ! studentName ) {
				return;
			}

			if ( studentName.value === parentName.value ) {
				return;
			}

			studentName.value = parentName.value;
			studentName.dispatchEvent( new Event( 'input', { bubbles: true } ) );
			studentName.dispatchEvent( new Event( 'change', { bubbles: true } ) );
		}

		document.addEventListener( 'change', function ( event ) {
			if ( event.target.matches( 'input[name="radio-1"]' ) ) {
				syncAdultStudentName();
			}
		} );

		document.addEventListener( 'input', function ( event ) {
			if ( event.target.matches( 'input[name="name-2"]' ) ) {
				syncAdultStudentName();
			}
		} );
	} )();
	</script>
	<?php
}
add_action( 'wp_footer', 'redbarn_forminator_adult_student_name_sync', 30 );

