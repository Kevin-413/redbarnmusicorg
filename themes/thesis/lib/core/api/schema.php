<?php
/*
Copyright 2013 DIYthemes, LLC. Patent pending. All rights reserved.
License: DIYthemes Software License Agreement
License URI: https://diythemes.com/thesis/rtfm/software-license-agreement/

For more information about the Schema API, please see:
— https://diythemes.com/thesis/rtfm/api/schema/
*/
class thesis_schema {
	public $schema = array(
		'Article',
		'BlogPosting',
		'CreativeWork',
		'Event',
		'NewsArticle',
		'Product',
		'Recipe',
		'Review',
		'WebPage');
	public $types = array();

	public function __construct() {
		add_action('init', array($this, 'init'), 12);
	}

	public function init() {
		$this->schema = is_array($schema = apply_filters('thesis_schema', $this->schema)) ? $schema : array();
		foreach ($this->schema as $type)
			if (!empty($type))
				$this->types[strtolower($type)] = "https://schema.org/$type";
		add_action('thesis_post_meta', array($this, 'post_meta'));
	}

	public function post_meta($post_meta) {
		$options['thesis_schema'] = array(
			'title' => __('Markup Schema', 'thesis'),
			'fields' => array(
				'schema' => $this->select(true)));
		return is_array($post_meta) ?
			array_merge($post_meta, $options) :
			$options;
	}

	public function select($override = false) {
		$options = array();
		foreach ($this->schema as $type)
			if (!empty($type))
				$options[strtolower($type)] = $type;
		ksort($options);
		$options = array_merge(empty($override) ? array(
			'' => __('No Schema', 'thesis')) : array(
			'' => __('Skin Default', 'thesis'),
			'no_schema' => __('No Schema', 'thesis')), $options);
		return array(
			'type' => 'select',
			'label' => __('Schema', 'thesis'),
			'tooltip' => sprintf(__('Enrich your pages by adding a <a href="%1$s" target="_blank" rel="noopener">markup schema</a> that is universally recognized by search engines. Don&rsquo;t see the schema you want to use? You can <a href="%2$s">add any schema you like to Thesis</a>.', 'thesis'), 'https://schema.org/', 'https://diythemes.com/thesis/rtfm/api/schema/#section-add-schema'),
			'options' => $options);
	}

	public function get_post_meta($post_id) {
		return empty($post_id) || !is_numeric($post_id) || !is_array($post_meta = get_post_meta($post_id, '_thesis_schema', true)) || empty($post_meta['schema']) ? false : $post_meta['schema'];
	}
}