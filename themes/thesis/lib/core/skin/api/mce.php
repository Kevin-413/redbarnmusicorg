<?php
class thesis_mce {
	public function __construct() {
		add_filter('wp_mce_translation', array($this, 'mce_translation'));
		add_filter('mce_buttons', array($this, 'mce_buttons'));
		add_filter('mce_buttons_2', array($this, 'mce_buttons_2'));
		add_filter('tiny_mce_before_init', array($this, 'mce_init')); 
	}

	public function mce_translation($translation) {
		$translation['Formats'] = apply_filters('thesis_mce_formats_title', __('Thesis Content Styles', 'thesis'));
		return $translation;
	}

	public function mce_buttons($buttons) {
		$chunk = array_splice($buttons, -4, 4);
		array_push($buttons, 'styleselect');
		foreach ($chunk as $button)
			array_push($buttons, $button);
		return $buttons;
	}

	public function mce_buttons_2($buttons) {
		array_unshift($buttons, 'superscript', 'subscript');
		return $buttons;
	}

	public function mce_init($settings) {
		$settings['block_formats'] = apply_filters('thesis_mce_block_formats',
			'Paragraph=p;'.
			'Heading 1=h1;'.
			'Heading 2=h2;'.
			'Heading 3=h3;'.
			'Heading 4=h4;'.
			'Preformatted=pre');
		$settings['style_formats'] = json_encode(apply_filters('thesis_mce_formats', array(array(
			'title' => __('Callouts', 'thesis'),
			'items' => array(
				array(
					'title' => __('Alert', 'thesis'),
					'block' => 'div',
					'classes' => 'alert',
					'preview' => 'color background-color',
					'wrapper' => true),
				array(
					'title' => __('Box', 'thesis'),
					'block' => 'div',
					'classes' => 'box',
					'preview' => 'color background-color',
					'wrapper' => true),
				array(
					'title' => __('Note', 'thesis'),
					'block' => 'div',
					'classes' => 'note',
					'preview' => 'color background-color',
					'wrapper' => true))),
			array(
				'title' => __('Drop Cap', 'thesis'),
				'inline' => 'span',
				'classes' => 'drop_cap',
				'preview' => 'font-family font-weight font-style',
				'styles' => array()),
			array(
				'title' => __('Highlight', 'thesis'),
				'inline' => 'span',
				'preview' => 'color background-color',
				'classes' => 'highlight'),
			array(
				'title' => __('Caption', 'thesis'),
				'block' => 'p',
				'preview' => 'font-size color',
				'classes' => 'caption'),
			array(
				'title' => __('Footnotes', 'thesis'),
				'block' => 'div',
				'classes' => 'footnotes',
				'preview' => 'font-size color',
				'wrapper' => true),
			array(
				'title' => __('Code', 'thesis'),
				'inline' => 'code',
				'preview' => 'font-family color background-color'))));
		return $settings;
	}
}