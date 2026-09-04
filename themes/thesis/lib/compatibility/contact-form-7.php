<?php
/*
Copyright 2018 DIYthemes, LLC. Patent pending. All rights reserved.
License: DIYthemes Software License Agreement
License URI: https://diythemes.com/thesis/rtfm/software-license-agreement/
*/
class thesis_contact_form_7 {
	public $exclude = array(
		'wpcf7_contact_form');

	public function __construct() {
		if (!defined('WPCF7_PLUGIN')) return;
		foreach (array('template', 'query') as $type)
			add_filter("thesis_exclude_{$type}_types", array($this, 'exclude_post_types'));
	}

	public function exclude_post_types($post_types) {
		foreach ($this->exclude as $type)
			$post_types[] = $type;
		return $post_types;
	}
}