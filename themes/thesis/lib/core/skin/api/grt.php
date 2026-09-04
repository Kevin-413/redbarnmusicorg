<?php
/*
Copyright 2018 DIYthemes, LLC. All rights reserved.

License: DIYthemes Software License Agreement
License URI: https://diythemes.com/thesis/rtfm/software-license-agreement/
Uses: Thesis object (and more specifically, the active Thesis Skin object)

About this class:
=================
Use this class to add Golden Ratio Typography to your design. You can determine
a typographic scale, associated line heights, and even layout spacing.
*/
class thesis_grt {
	public $phi = false;			// Golden Ratio value
	public $factor = 34;			// Width factor, hold constant for use in font tuning
	public $fonts = array(); 		// array of available fonts in Thesis Font Array Format

	public function __construct() {
		global $thesis;
		$this->phi = (1 + sqrt(5)) / 2;
		$this->factor = apply_filters('thesis_grt_width_factor', $this->factor);
		add_action('init', array($this, 'get_fonts'), 12);	// Timing ensures fonts are fully-loaded
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
	Use your primary font size to determine a typographical scale for your design
	Note: In the return array, index f5 is your primary font size.
*/
	public function scale($size) {
		return empty($size) || !is_numeric($size) ? false : array(
			'f1' => round($size * pow($this->phi, 2)),
			'f2' => round($size * pow($this->phi, 1.5)),
			'f3' => round($size * $this->phi),
			'f4' => round($size * sqrt($this->phi)),
			'f5' => $size,
			'f6' => round($size * (1 / sqrt($this->phi))));
	}

/*
	Determine the appropriate line height for a given font size and associated context
	– $size: font size that will serve as the basis for the line height calculation
	– $width: (optional) for precise line height tuning, supply a content width here (use the same units as your font size)
	– $font: (optional) for maximum precision, indicate the font being used
*/
	public function height($size = false, $width = false, $font = false) {
		$x = !empty($font) && !empty($this->fonts[$font]) && !empty($this->fonts[$font]['x']) && is_numeric($this->fonts[$font]['x']) ? ($this->fonts[$font]['x'] - $this->phi + 1) / $this->phi : 0;
		return !empty($size) && is_numeric($size) ?
			$size * ((3 - $this->phi) + (2 * $this->phi - 3) * (!empty($width) && is_numeric($width) ? $width / ($size * $this->factor) : 1) + $x) :
			false;
	}

/*
	Determine layout spacing values based on a primary spacing unit.
	NOTE: The line height of the primary text is the preferred primary spacing unit.
*/
	public function spacing($unit) {
		return empty($unit) || !is_numeric($unit) ? false : array(
			'x1' => round($unit * $this->phi),
			'x2' => $unit,
			'x3' => $x2 = round($unit / $this->phi),
			'x4' => $x3 = round($x2 / $this->phi),
			'x5' => $x5 = round($x3 / $this->phi),
			'x6' => round($x5 / $this->phi));
	}
}