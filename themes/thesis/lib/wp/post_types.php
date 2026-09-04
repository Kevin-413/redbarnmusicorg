<?php
/*
Copyright 2018 DIYthemes, LLC. Patent pending. All rights reserved.
License: DIYthemes Software License Agreement
License URI: https://diythemes.com/thesis/rtfm/software-license-agreement/
*/
class thesis_post_types {
	public $type = 'thesis_modular';
	public $hide = array();

	public function __construct() {
		$this->modular_content();
		add_filter('hidden_meta_boxes', array($this, 'hide_post_meta'), 10, 3);
		add_filter('thesis_post_meta', array($this, 'modular_content_post_meta'));
	}

	private function modular_content() {
		$name = __('Modular Content', 'thesis');
		if (!apply_filters($this->type, true)) return;
		$this->hide[] = $this->type;
		register_post_type($this->type, apply_filters("{$this->type}_args", array(
			'description' => sprintf(__('Create %s for Thesis and deploy it anywhere in your design.', 'thesis'), $name),
			'exclude_from_search' => true,
			'label' => sprintf(__('%s for Thesis', 'thesis'), $name),
			'labels' => array(
				'name' => sprintf(__('%s for Thesis', 'thesis'), $name),
				'singular_name' => $name,
				'add_new' => sprintf(__('Add New %s', 'thesis'), $name),
				'add_new_item' => sprintf(__('Add New %s', 'thesis'), $name),
				'edit_item' => sprintf(__('Edit %s', 'thesis'), $name),
				'new_item' => sprintf(__('New %s', 'thesis'), $name),
				'all_items' => sprintf(__('All %s', 'thesis'), $name),
				'view_item' => sprintf(__('View %s', 'thesis'), $name),
				'search_items' => sprintf(__('Search %s', 'thesis'), $name),
				'not_found' =>  sprintf(__('No %s found', 'thesis'), $name),
				'not_found_in_trash' => sprintf(__('No %s found in trash', 'thesis'), $name),
				'menu_name' => $name),
			'menu_icon' => 'dashicons-format-aside',
			'menu_position' => apply_filters('thesis_modular_content_menu_position', apply_filters('thesis_menu_position', 31) + 1),
			'public' => true,
			'publicly_queryable' => false,
			'query_var' => false,
			'rewrite' => false,
			'show_in_nav_menus' => false)));
	}

	public function modular_content_post_meta($post_meta) {
		return array_merge($post_meta, array(
			'thesis_modular_content_formatting' => array(
				'title' => __('Modular Content Formatting Options', 'thesis'),
				'fields' => array(
					'format' => array(
						'type' => 'checkbox',
						'options' => array(
							'no-autop' => __('disable automatic <code>&lt;p&gt;</code> tags for this Modular Content', 'thesis'))))),
			'thesis_modular_content_shortcode' => array(
				'title' => __('Modular Content Shortcode', 'thesis'),
				'context' => 'side',
				'fields' => array(
					'shortcode' => array(
						'type' => 'custom',
						'output' => "<p class=\"option_field\" id=\"{$this->type}_shortcode\"></p>\n".
									"<a href=\"https://diythemes.com/thesis/rtfm/modular-content/#section-shortcodes\" target=\"_blank\" rel=\"noopener noreferrer\">See MC shortcode options &nearr;</a>\n")))));
	}

	public function hide_post_meta($hidden, $screen, $use_defaults) {
		global $wp_meta_boxes;
		$hide = array();
		if (!empty($this->hide))
			foreach ($this->hide as $cpt)
				if ($cpt === $screen->id && isset($wp_meta_boxes[$cpt])) {
					foreach ($wp_meta_boxes[$cpt] as $context_key => $context_item)
						foreach ($context_item as $priority_key => $priority_item)
							foreach ($priority_item as $meta_key => $meta_item)
								if ($meta_key != 'submitdiv' && $meta_key != 'thesis_modular_content_formatting' && $meta_key != 'thesis_modular_content_shortcode')
									$hide[] = $meta_key;
				}
		if (empty($hide)) {
			$hide[] = 'thesis_modular_content_formatting';
			$hide[] = 'thesis_modular_content_shortcode';
		}
		return array_merge($hidden, $hide);
	}
}