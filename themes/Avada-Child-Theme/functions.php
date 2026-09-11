<?php

function theme_enqueue_styles() {
    wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', [] );
}
add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles', 20 );

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
 * Mobile-only 3-button bottom action bar (Site Frame Plan, mobile bottom
 * action bar). Real destinations only: Lessons landing page, the site's
 * current Lessons Inquiry sign-up flow, and the Contact page. Hidden on
 * desktop/tablet via CSS (Additional CSS post 20363); shown only below
 * the existing mobile breakpoint. Not a plugin; markup output only.
 */
function redbarn_mobile_bottom_action_bar() {
	?>
	<nav class="rbm-mobile-bottom-bar" aria-label="Quick actions">
		<a class="rbm-mobile-bottom-bar-btn" href="<?php echo esc_url( home_url( '/lessons/' ) ); ?>">
			<span class="rbm-mobile-bottom-bar-icon" aria-hidden="true">&#9834;</span>
			<span class="rbm-mobile-bottom-bar-label">Lessons</span>
		</a>
		<a class="rbm-mobile-bottom-bar-btn" href="<?php echo esc_url( home_url( '/lessons-inquiry/' ) ); ?>">
			<span class="rbm-mobile-bottom-bar-icon" aria-hidden="true">&#9998;</span>
			<span class="rbm-mobile-bottom-bar-label">Sign Up</span>
		</a>
		<a class="rbm-mobile-bottom-bar-btn" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">
			<span class="rbm-mobile-bottom-bar-icon" aria-hidden="true">&#9993;</span>
			<span class="rbm-mobile-bottom-bar-label">Contact</span>
		</a>
	</nav>
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