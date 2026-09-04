<?php
/*
Copyright 2018 DIYthemes, LLC. Patent pending. All rights reserved.
License: DIYthemes Software License Agreement
License URI: https://diythemes.com/thesis/rtfm/software-license-agreement/
*/
class thesis_optimizepress {
	public function __construct() {
		if (!defined('OP_PLUGIN_DIR')) return;
		add_action('template_redirect', array($this, 'templates'), 1);
	}

	public function templates() {
		global $post, $thesis;
		if (is_page() && get_post_meta($post->ID, '_optimizepress_pagebuilder', true)) {
			remove_filter('template_include', array($thesis->skin, '_skin'), 11);
			add_filter('template_include', 'op_template_include', 1);
		}
	}
}