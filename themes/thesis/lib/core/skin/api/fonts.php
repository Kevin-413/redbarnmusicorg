<?php
/*
Copyright 2015 DIYthemes, LLC. Patent pending. All rights reserved.

License: DIYthemes Software License Agreement
License URI: https://diythemes.com/thesis/rtfm/software-license-agreement/

TODO: Document the methods
*/
class thesis_skin_fonts {
	public function __construct() {
		global $thesis;
		// Timing ensures add-on components such as Plugins, Boxes, master.php, or custom.php can modify the font list
		add_action('init', array($this, 'init'), 11);
	}

	public function init() {
	 	$this->list = apply_filters('thesis_fonts', array(
			'arial' => array(
				'family' => 'Arial, "Helvetica Neue", Helvetica, sans-serif',
				'x' => 0.7209,
				'mu' => 2.26),
			'arial_black' => array(
				'name' => 'Arial Black',
				'family' => '"Arial Black", "Arial Bold", Arial, sans-serif',
				'x' => 0.7209,
				'mu' => 1.82),
			'arial_narrow' => array(
				'name' => 'Arial Narrow',
				'family' => '"Arial Narrow", Arial, "Helvetica Neue", Helvetica, sans-serif',
				'x' => 0.7209,
				'mu' => 2.75),
			'courier_new' => array(
				'name' => 'Courier New',
				'family' => '"Courier New", Courier, Verdana, sans-serif',
				'x' => 0.7427,
				'mu' => 1.67,
				'monospace' => true),
			'georgia' => array(
				'family' => 'Georgia, "Times New Roman", Times, serif',
				'x' => 0.6923,
				'mu' => 2.27),
			'times_new_roman' => array(
				'name' => 'Times New Roman',
				'family' => '"Times New Roman", Times, Georgia, serif',
				'x' => 0.6734,
				'mu' => 2.48),
			'trebuchet_ms' => array(
				'name' => 'Trebuchet MS',
				'family' => '"Trebuchet MS", "Lucida Grande", "Lucida Sans Unicode", "Lucida Sans", Arial, sans-serif',
				'x' => 0.7290,
				'mu' => 2.2),
			'verdana' => array(
				'family' => 'Verdana, sans-serif',
				'x' => 0.7523,
				'mu' => 1.96),
			'american_typewriter' => array(
				'name' => 'American Typewriter',
				'family' => '"American Typewriter", Georgia, serif',
				'x' => 0.7424,
				'mu' => 2.09),
			'andale' => array(
				'name' => 'Andale Mono',
				'family' => '"Andale Mono", Consolas, Monaco, Menlo, Courier, Verdana, sans-serif',
				'x' => 0.7379,
				'mu' => 1.67,
				'monospace' => true),
			'baskerville' => array(
				'family' => 'Baskerville, "Times New Roman", Times, serif',
				'x' => 0.5980,
				'mu' => 2.51),
			'calibri' => array(
				'family' => 'Calibri, "Helvetica Neue", Helvetica, Arial, Verdana, sans-serif'),
			'cambria' => array(
				'family' => 'Cambria, Georgia, "Times New Roman", Times, serif'),
			'candara' => array(
				'family' => 'Candara, Verdana, sans-serif'),
			'consolas' => array(
				'family' => 'Consolas, Menlo, Monaco, Courier, Verdana, sans-serif',
				'monospace' => true),
			'constantia' => array(
				'family' => 'Constantia, Georgia, "Times New Roman", Times, serif'),
			'corbel' => array(
				'family' => 'Corbel, "Lucida Grande", "Lucida Sans Unicode", Arial, sans-serif'),
			'gill_sans' => array(
				'name' => 'Gill Sans',
				'family' => '"Gill Sans", "Gill Sans MT", Calibri, "Trebuchet MS", sans-serif',
				'x' => 0.6537,
				'mu' => 2.47),
			'helvetica' => array(
				'name' => 'Helvetica Neue',
				'family' => '"Helvetica Neue", Helvetica, Arial, sans-serif',
				'x' => 0.7243,
				'mu' => 2.24),
			'hoefler' => array(
				'name' => 'Hoefler Text',
				'family' => '"Hoefler Text", Garamond, "Times New Roman", Times, sans-serif',
				'x' => 0.6098,
				'mu' => 2.39),
			'lucida_grande' => array(
				'name' => 'Lucida Grande',
				'family' => '"Lucida Grande", "Lucida Sans", "Lucida Sans Unicode", sans-serif',
				'x' => 0.7327,
				'mu' => 2.05),
			'menlo' => array(
				'family' => 'Menlo, Consolas, Monaco, "Andale Mono", Courier, Verdana, sans-serif',
				'x' => 0.7489,
				'mu' => 1.66,
				'monospace' => true),
			'monaco' => array(
				'family' => 'Monaco, Consolas, Menlo, Courier, Verdana, sans-serif',
				'x' => 0.7225,
				'mu' => 1.67,
				'monospace' => true),
			'palatino' => array(
				'family' => '"Palatino Linotype", Palatino, Georgia, "Times New Roman", Times, serif',
				'x' => 0.6553,
				'mu' => 2.26),
			'System' => array(
				'family' => 'unquote("-apple-system"), BlinkMacSystemFont, Roboto, "Segoe UI", Segoe, "Helvetica Neue", Tahoma, sans-serif',
				'x' => 0.7204,
				'mu' => 2.26),
			'tahoma' => array(
				'family' => 'Tahoma, Geneva, Verdana, sans-serif',
				'x' => 0.7523,
				'mu' => 2.26)));
		uksort($this->list, 'strnatcasecmp');
		foreach ($this->list as $id => $font)
			$this->select[$id] = !empty($font['name']) ? $font['name'] : ucfirst($id);
	}

	public function family($font) {
		return !empty($font) && !empty($this->list) && !empty($this->list[$font]) ?
			$this->list[$font]['family'] : false;
	}
}