<?php
/*
Copyright 2012 DIYthemes, LLC. Patent pending. All rights reserved.
License: DIYthemes Software License Agreement
License URI: https://diythemes.com/thesis/rtfm/software-license-agreement/

NOTE: This version of the Fonts API is deprecated! The new version is
part of the Skin API and can be accessed via $this->fonts from within
the Skin object.
*/
class thesis_fonts {
	public function __construct() {
		add_action('init', array($this, 'init'), 11);
	}

	public function init() {
	 	$this->list = apply_filters('thesis_fonts', array(
			'arial' => array(
				'family' => 'Arial, "Helvetica Neue", Helvetica, sans-serif',
				'mu' => 2.26),
			'arial_black' => array(
				'name' => 'Arial Black',
				'family' => '"Arial Black", "Arial Bold", Arial, sans-serif',
				'mu' => 1.82),
			'arial_narrow' => array(
				'name' => 'Arial Narrow',
				'family' => '"Arial Narrow", Arial, "Helvetica Neue", Helvetica, sans-serif',
				'mu' => 2.76),
			'courier_new' => array(
				'name' => 'Courier New',
				'family' => '"Courier New", Courier, Verdana, sans-serif',
				'mu' => 1.67),
			'georgia' => array(
				'family' => 'Georgia, "Times New Roman", Times, serif',
				'mu' => 2.27),
			'times_new_roman' => array(
				'name' => 'Times New Roman',
				'family' => '"Times New Roman", Times, Georgia, serif',
				'mu' => 2.48),
			'trebuchet_ms' => array(
				'name' => 'Trebuchet MS',
				'family' => '"Trebuchet MS", "Lucida Grande", "Lucida Sans Unicode", "Lucida Sans", Arial, sans-serif',
				'mu' => 2.2),
			'verdana' => array(
				'family' => 'Verdana, sans-serif',
				'mu' => 1.96),
			'american_typewriter' => array(
				'name' => 'American Typewriter',
				'family' => '"American Typewriter", Georgia, serif',
				'mu' => 2.09),
			'andale' => array(
				'name' => 'Andale Mono',
				'family' => '"Andale Mono", Consolas, Monaco, Menlo, Courier, Verdana, sans-serif',
				'mu' => 1.67),
			'baskerville' => array(
				'family' => 'Baskerville, "Times New Roman", Times, serif',
				'mu' => 2.51),
			'calibri' => array(
				'family' => 'Calibri, "Helvetica Neue", Helvetica, Arial, Verdana, sans-serif'),
			'cambria' => array(
				'family' => 'Cambria, Georgia, "Times New Roman", Times, serif'),
			'candara' => array(
				'family' => 'Candara, Verdana, sans-serif'),
			'consolas' => array(
				'family' => 'Consolas, Menlo, Monaco, Courier, Verdana, sans-serif'),
			'constantia' => array(
				'family' => 'Constantia, Georgia, "Times New Roman", Times, serif'),
			'corbel' => array(
				'family' => 'Corbel, "Lucida Grande", "Lucida Sans Unicode", Arial, sans-serif'),
			'gill_sans' => array(
				'name' => 'Gill Sans',
				'family' => '"Gill Sans", "Gill Sans MT", Calibri, "Trebuchet MS", sans-serif',
				'mu' => 2.47),
			'helvetica' => array(
				'name' => 'Helvetica Neue',
				'family' => '"Helvetica Neue", Helvetica, Arial, sans-serif',
				'mu' => 2.24),
			'hoefler' => array(
				'name' => 'Hoefler Text',
				'family' => '"Hoefler Text", Garamond, "Times New Roman", Times, sans-serif',
				'mu' => 2.39),
			'lucida_grande' => array(
				'name' => 'Lucida Grande',
				'family' => '"Lucida Grande", "Lucida Sans", "Lucida Sans Unicode", sans-serif',
				'mu' => 2.05),
			'menlo' => array(
				'family' => 'Menlo, Consolas, Monaco, "Andale Mono", Courier, Verdana, sans-serif',
				'mu' => 1.66),
			'monaco' => array(
				'family' => 'Monaco, Consolas, Menlo, Courier, Verdana, sans-serif',
				'mu' => 1.67),
			'palatino' => array(
				'family' => '"Palatino Linotype", Palatino, Georgia, "Times New Roman", Times, serif',
				'mu' => 2.26),
			'tahoma' => array(
				'family' => 'Tahoma, Geneva, Verdana, sans-serif',
				'mu' => 2.25)));
		uksort($this->list, 'strnatcasecmp');
		foreach ($this->list as $id => $font)
			$this->select[$id] = !empty($font['name']) ? $font['name'] : ucfirst($id);
	}

	public function family($font) {
		return !empty($font) && !empty($this->list) && !empty($this->list[$font]) ?
			$this->list[$font]['family'] : false;
	}
}