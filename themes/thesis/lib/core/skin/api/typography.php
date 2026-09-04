<?php
/*
Copyright 2015 DIYthemes, LLC. All rights reserved.

License: DIYthemes Software License Agreement
License URI: https://diythemes.com/thesis/rtfm/software-license-agreement/
Uses: Thesis object (and more specifically, the active Thesis Skin object)

About this class:
=================
Use this class to calculate dynamic typographical values in your design. You can (and should!) use these values to
determine both layout spacing and typographical characteristics. For the most finely-tuned results possible, be
sure to include a font list that contains mu (character constant) values and, if available, x-height correction
information.
*/
class thesis_skin_typography {
	public $phi = false;			// Golden Ratio value
	public $cpl = false;			// Characters per line value for tuning typography
	public $mu = false;				// Default mu value for tuning typography
	public $factor = false;			// Width factor, hold constant for use in font tuning
	public $fonts = array(); 		// array of available fonts in Thesis Font Array Format

	public function __construct() {
		global $thesis;
		$this->phi = (1 + sqrt(5)) / 2;
		$this->cpl = apply_filters('thesis_skin_typography_cpl', 75);		// Default CPL of 75
		$this->mu = apply_filters('thesis_skin_typography_mu', 2.25);		// Default mu of 2.25
		$this->factor = $this->cpl / $this->mu;								// Default of 33.3333
		add_action('init', array($this, 'get_fonts'), 12); 					// Timing ensures fonts are fully-loaded
	}

	/*
	Attempt to use the Thesis font list for precision tuning
	*/
	public function get_fonts() {
		global $thesis;
		$this->fonts = is_object($thesis) && is_object($thesis->skin) && is_object($thesis->skin->fonts) && !empty($thesis->skin->fonts->list) && is_array($thesis->skin->fonts->list) ?
			$thesis->skin->fonts->list : $this->fonts;
	}

	/*
	Determine the appropriate line height for a given font size and context
	– $size: font size that will serve as the basis for the line height calculation
	– $width: (optional) for precise line height tuning, supply a content width here (use the same units as your font size)
	– $font: (optional) for maximum precision, indicate the font being used (note: $font must match an array key in the $this->fonts list)
	*/
	public function height($size, $width = false, $font = false) {
		$a = 1 / (2 * $this->phi);
		return !empty($size) && is_numeric($size) ?
			$size * (!empty($width) && is_numeric($width) ?
				1 + $a + $a * ($width / ($size * $this->factor)) :
				$this->phi) + (!empty($font) && !empty($this->fonts[$font]) && !empty($this->fonts[$font]['x']) && is_numeric($this->fonts[$font]['x']) ? 2 * ($this->phi * $this->fonts[$font]['x'] - 1) : 0) :
			false;
	}

	/*
	Determine an appropriate content width for a known font size
	– $size: font size that will serve as the basis for the width calculation
	– $font: (optional) for maximum precision, indicate the font being used (note: $font must match an array key in the $this->fonts list)
	– $height: (optional) optimal line height will be used unless you specify a particular line height here
	*/
	public function width($size, $font = false, $height = false) {
		if (empty($size) || !is_numeric($size)) return false;
		$b = 2 * $this->phi;
		$h = (!empty($height) ? $height : $size * $this->phi) + (!empty($font) && !empty($this->fonts[$font]) && !empty($this->fonts[$font]['x']) && is_numeric($this->fonts[$font]['x']) ?
			2 * ($this->phi * $this->fonts[$font]['x'] - 1) : 0);
		return $b * $this->factor * ($h - $size - ($size / $b));
	}

	/*
	Determine an approximate CPL based on a font size and content width
	– $size: font size to use in the CPL calculation
	— $width: content width to use in the CPL calculation
	– $font: (optional) for maximum precision, indicate the font being used (note: $font must match an array key in the $this->fonts list)
	*/
	public function cpl($size, $width, $font = false) {
		if (empty($size) || empty($width) || !is_numeric($size) || !is_numeric($width)) return false;
		return $width * (!empty($font) && !empty($this->fonts[$font]) && !empty($this->fonts[$font]['mu']) ? $this->fonts[$font]['mu'] : $this->mu) / $size;
	}

	/*
	Use your primary font size to determine a typographical scale for your design
	Note: In the return array, index f5 is your primary font size.
	*/
	public function scale($size) {
		return empty($size) || !is_numeric($size) ? false : array(
			'f1' => round($size * pow($this->phi, 2)),			// title
			'f2' => round($size * pow($this->phi, 1.5)),		// headlines 1, h1
			'f3' => round($size * $this->phi),					// headlines 2, h2
			'f4' => round($size * sqrt($this->phi)),			// headlines 3, h3
			'f5' => $size,										// primary content size, h4
			'f6' => round($size * (1 / sqrt($this->phi))));		// auxiliary text
	}

	/*
	The spacing() method, which uses golden sections to subdivide a primary design unit, will
	probably make the space() method obsolete.
	*/
	public function spacing($unit) {
		return empty($unit) || !is_numeric($unit) ? false : array(
			'x1' => $unit,
			'x02' => $x2 = round($unit / $this->phi),
			'x03' => $x3 = round($x2 / $this->phi),
			'x04' => round($x3 / $this->phi));
	}

	/*
	Use your primary line height to determine the various units of spacing in your design.
	Never use arbitrary padding/margin/spacing values again! Instead, use a spatial scale
	based on the primary line height, and all the spacing in your design will be related.
	*/
	public function space($height) {
		return empty($height) || !is_numeric($height) ? false : array(
			'x1' => ($height = round($height)),					// single
			'x05' => ($half = round($height / 2)),				// half
			'x025' => round($height / 4),						// quarter
			'x15' => $height + $half,							// one-and-a-half
			'x2' => $height * 2,								// double
			'x25' => $height + $height + $half,					// two-and-a-half
			'x3' => $height * 3);								// triple
	}
}