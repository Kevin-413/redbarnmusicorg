<?php
/*
Copyright 2015 DIYthemes, LLC. All rights reserved.

License: DIYthemes Software License Agreement
License URI: https://diythemes.com/thesis/rtfm/software-license-agreement/
*/
class thesis_skin_fonts_google {
	private $options = array();			// (array) current Skin Design options
	private $selected = array();		// (array) Google Fonts currently selected in Skin Design options
	public $fonts = array();			// (array) available Google Fonts, including any that have been added
	public $method = 'prefetch';		// prefetch, async, null
	public $src = 'https://ajax.googleapis.com/ajax/libs/webfont/1.6.26/webfont.js';	// (string) URL of WebFont loader

	public function __construct($options) {
		global $thesis;
		$this->options = is_array($options) ? $options : $this->options;
		$this->get_fonts();
		add_filter('thesis_fonts', array($this, 'add_fonts'));					// filter for adding fonts to Thesis dropdowns
		add_action('hook_font_prefetch', array($this, 'webfont_loader'));		// optimize rendering
		$this->method = apply_filters('thesis_google_fonts_method', $this->method);
		if ($thesis->environment != 'admin') return;
		$this->editor_styles();
	}

	public function get_fonts() {
		$this->fonts = $this->fonts();
		$this->find_fonts($this->options);	// See if any Google Fonts are selected in Skin Design options
	}

	/*
	This method is really just a recursive value search for multi-dimensional arrays...
	As such, it should probably become a general API candidate.
	*/
	public function find_fonts($options) {
		if (is_array($options))
			foreach ($options as $item)
				if (is_array($item))
					$this->find_fonts($item);
				elseif (is_string($item) && array_key_exists($item, $this->fonts))
					$this->selected[] = $item;
	}

	/*
	– $fonts: the master array of fonts registered for use within Thesis
	*/
	public function add_fonts($fonts) {
		return is_array($add_fonts = $this->fonts) ? (is_array($fonts) ? array_merge($fonts, $add_fonts) : $add_fonts) : $fonts;
	}

	public function webfont_loader() {
		$families = $this->verify($this->selected, $this->fonts);
		if (empty($families)) return;
		if ($this->method == 'prefetch') {
			echo
				"<link href=\"//fonts.googleapis.com\" rel=\"dns-prefetch\" />\n",
				"<link href=\"https://fonts.gstatic.com\" rel=\"preconnect\" crossorigin />\n",
				"<link href=\"https://fonts.googleapis.com/css?family=". str_replace(' ', '+', implode('|', $families)). "\" rel=\"stylesheet\" />\n";
			return;
		}
		echo
		($this->method == 'async' ? '' :
			"<script src=\"$this->src\"></script>\n"),
			"<script>",
		($this->method == 'async' ? 
			"WebFontConfig = {" :
			"WebFont.load({"),
			"google: { families: ['", implode("', '", $families), "'] }",
		($this->method == 'async' ?
			"};" :
			"});"),
		($this->method == 'async' ?
			"(function() {".
			"var wf = document.createElement('script');".
			"wf.src = '$this->src';".
			"wf.type = 'text/javascript';".
			"wf.async = 'true';".
			"var s = document.getElementsByTagName('script')[0];".
			"s.parentNode.insertBefore(wf, s);".
			"})(document);" : ''),
			"</script>\n";
	}

	/*
	Method for adding Google Fonts to the WP Post Editor
	*/
	public function editor_styles() {
		$families = $this->verify($this->selected, $this->fonts);
		if (empty($families)) return;
		add_editor_style('//fonts.googleapis.com/css?family='. str_replace(' ', '+', implode('|', $families)));
	}

	/*
	Operational method for verifying Google Fonts selected by the user and then returning the appropriate family references
	for use. If the user has selected a font, we want to make sure it's a Google Font before serving the JS and affecting
	performance in this manner.
	– $options: an array of options that *could* contain font selections
	– $fonts: an array of fonts following the format established in the fonts() method below
	– $js: if the resulting font families are to be served inside JS, set this to true
	*/
	public function verify($options, $fonts) {
		$verified = $families = array();
		if (is_array($options) && is_array($fonts))
			foreach ($options as $font)
				if (!empty($fonts[$font]) && !empty($fonts[$font]['styles']))
					$verified[$font] = $fonts[$font];
		if (empty($verified)) return false;
		foreach ($verified as $name => $font)
			if (!empty($font['styles'])) 
				$families[] = $font['styles'];
		return $families;
	}

	function fonts() {
		/*
		Each Google Font below contains 400, 400 italic, and 700 (bold) styles, making it suitable for use in primary content.
		Also, thesis_google_fonts is an array filter for adding any other Google Fonts to the current Skin.
		To construct your fonts array, follow this format, where $name is the proper name of the Google Font you wish to add:
		$fonts['$name'] = array(
			'styles' => '300,300i,900',		// (optional) include styles here; 400,400i,700 is the default
			'type' => $type,				// (optional) where $type = 'serif' or 'sans-serif'
			'x' => $x,						// (optional) include if you know the font x-height ratio
			'mu' => $mu);					// (optional) include if you know the numerical mu value (character constant) for this font
		*/
		$fonts = array();
		$primary_fonts = apply_filters('thesis_google_fonts', array(
			'Alegreya' => array(
				'type' => 'serif',
				'x' => 0.7120,
				'mu' => 2.49),
			'Alegreya SC' => array(
				'type' => 'serif',
				'x' => 0.7801,
				'mu' => 2.18),
			'Alegreya Sans' => array(
				'type' => 'sans-serif',
				'x' => 0.7135,
				'mu' => 2.64),
			'Alegreya Sans SC' => array(
				'type' => 'sans-serif',
				'x' => 0.7760,
				'mu' => 2.38),
			'Aleo' => array(
				'type' => 'serif',
				'x' => 0.7056,
				'mu' => 2.26),
			'Almendra' => array(
				'type' =>'serif',
				'x' => 0.7231,
				'mu' => 2.49),
			'Amaranth' => array(
				'type' => 'sans-serif',
				'x' => 0.7340,
				'mu' => 2.36),
			'Amiri' => array(
				'type' => 'serif',
				'x' => 0.6458,
				'mu' => 2.51),
			'Anonymous Pro' => array(
				'type' => 'sans-serif',
				'x' => 0.7120,
				'mu' => 1.83,
				'monospace' => true),
			'Archivo' => array(
				'type' => 'sans-serif',
				'x' => 0.7670,
				'mu' => 2.26),
			'Archivo Narrow' => array(
				'type' => 'sans-serif',
				'x' => 0.7670,
				'mu' => 2.76),
			'Arimo' => array(
				'type' => 'sans-serif',
				'x' => 0.7670,
				'mu' => 2.26),
			'Arsenal' => array(
				'type' => 'sans-serif',
				'x' => 0.7692,
				'mu' => 2.55),
			'Arvo' => array(
				'type' => 'serif',
				'x' => 0.6847,
				'mu' => 2.06),
			'Asap' => array(
				'type' => 'sans-serif',
				'x' => 0.7429,
				'mu' => 2.28),
			'Asap Condensed' => array(
				'type' => 'sans-serif',
				'x' => 0.7429,
				'mu' => 2.73),
			'Averia Libre' => array(
				'type' => 'serif',
				'x' => 0.6931,
				'mu' => 2.24),
			'Averia Sans Libre' => array(
				'type' => 'sans-serif',
				'x' => 0.6863,
				'mu' => 2.29),
			'Averia Serif Libre' => array(
				'type' => 'serif',
				'x' => 0.6798,
				'mu' => 2.19),
			'B612' => array(
				'type' => 'sans-serif',
				'x' => 0.7321,
				'mu' => 2.06),
			'B612 Mono' => array(
				'type' => 'sans-serif',
				'x' => 0.7321,
				'mu' => 1.54,
				'monospace' => true),
			'Bai Jamjuree' => array(
				'type' => 'sans-serif',
				'x' => 0.7129,
				'mu' => 2.2),
			'Barlow' => array(
				'type' => 'sans-serif',
				'x' => 0.7190,
				'mu' => 2.35),
			'Barlow Condensed' => array(
				'type' => 'sans-serif',
				'x' => 0.7190,
				'mu' => 2.96),
			'Barlow Semi Condensed' => array(
				'type' => 'sans-serif',
				'x' => 0.7190,
				'mu' => 2.62),
			'Bitter' => array(
				'type' => 'serif',
				'x' => 0.7476,
				'mu' => 2.08),
			'Cabin' => array(
				'type' => 'sans-serif',
				'x' => 0.7000,
				'mu' => 2.41),
			'Cambay' => array(
				'type' => 'sans-serif',
				'x' => 0.6942,
				'mu' => 2.28),
			'Cantarell' => array(
				'type' => 'sans-serif',
				'x' => 0.6971,
				'mu' => 2.16),
			'Cardo' => array(
				'type' => 'serif',
				'x' => 0.6408,
				'mu' => 2.37),
			'Caudex' => array(
				'type' => 'serif',
				'x' => 0.6602,
				'mu' => 2.23),
			'Chakra Petch' => array(
				'type' => 'sans-serif',
				'x' => 0.7081,
				'mu' => 2.21),
			'Chivo' => array(
				'type' => 'sans-serif',
				'x' => 0.7427,
				'mu' => 2.14),
			'Cormorant' => array(
				'type' => 'serif',
				'x' => 0.6183,
				'mu' => 2.59),
			'Cormorant Garamond' => array(
				'type' => 'serif',
				'x' => 0.6183,
				'mu' => 2.59),
			'Cormorant Infant' => array(
				'type' => 'serif',
				'x' => 0.6183,
				'mu' => 2.55),
			'Cousine' => array(
				'type' => 'sans-serif',
				'x' => 0.8020,
				'mu' => 1.67,
				'monospace' => true),
			'Crimson Text' => array(
				'type' => 'serif',
				'x' => 0.6492,
				'mu' => 2.57),
			'Cuprum' => array(
				'type' => 'sans-serif',
				'x' => 0.7143,
				'mu' => 2.63),
			'DM Sans' => array(
				'type' => 'sans-serif',
				'x' => 0.7081,
				'mu' => 2.18),
			'Droid Serif' => array(
				'type' => 'serif',
				'x' => 0.7477,
				'mu' => 2.1),
			'EB Garamond' => array(
				'type' => 'serif',
				'x' => 0.6218,
				'mu' => 2.65),
			'Economica' => array(
				'type' => 'sans-serif',
				'x' => 0.7454,
				'mu' => 3.28),
			'Exo' => array(
				'type' => 'sans-serif',
				'x' => 0.7240,
				'mu' => 2.18),
			'Exo 2' => array(
				'type' => 'sans-serif',
				'x' => 0.7053,
				'mu' => 2.22),
			'Expletus Sans' => array(
				'type' => 'sans-serif',
				'x' => 0.7110,
				'mu' => 2.19),
			'Fahkwang' => array(
				'type' => 'sans-serif',
				'x' => 0.6986,
				'mu' => 2),
			'Faustina' => array(
				'type' => 'serif',
				'x' => 0.7629,
				'mu' => 2.44),
			'Fira Sans' => array(
				'type' => 'sans-serif',
				'x' => 0.7670,
				'mu' => 2.22),
			'Fira Sans Condensed' => array(
				'type' => 'sans-serif',
				'x' => 0.7670,
				'mu' => 2.46),
			'Fira Sans Extra Condensed' => array(
				'type' => 'sans-serif',
				'x' => 0.7670,
				'mu' => 2.68),
			'Gentium Basic' => array(
				'type' => 'serif',
				'x' => 0.7391,
				'mu' => 2.44),
			'Gentium Book Basic' => array(
				'type' => 'serif',
				'x' => 0.7391,
				'mu' => 2.38),
			'Gudea' => array(
				'type' => 'sans-serif',
				'x' => 0.7143,
				'mu' => 2.37),
			'IBM Plex Mono' => array(
				'type' => 'serif',
				'x' => 0.7368,
				'mu' => 1.67,
				'monospace' => true),
			'IBM Plex Sans' => array(
				'type' => 'sans-serif',
				'x' => 0.7368,
				'mu' => 2.24),
			'IBM Plex Sans Condensed' => array(
				'type' => 'sans-serif',
				'x' => 0.7368,
				'mu' => 2.48),
			'IBM Plex Serif' => array(
				'type' => 'serif',
				'x' => 0.7368,
				'mu' => 2.1),
			'Istok Web' => array(
				'type' => 'sans-serif',
				'x' => 0.7333,
				'mu' => 2.23),
			'Josefin Sans' => array(
				'type' => 'sans-serif',
				'x' => 0.5714,
				'mu' => 2.23),
			'Josefin Slab' => array(
				'type' => 'serif',
				'x' => 0.5333,
				'mu' => 2.38),
			'Judson' => array(
				'type' => 'serif',
				'x' => 0.7543,
				'mu' => 2.37),
			'K2D' => array(
				'type' => 'sans-serif',
				'x' => 0.7129,
				'mu' => 2.24),
			'Kanit' => array(
				'type' => 'sans-serif',
				'x' => 0.7358,
				'mu' => 2.16),
			'Karla' => array(
				'type' => 'sans-serif',
				'x' => 0.7566,
				'mu' => 2.2),
			'Kodchasan' => array(
				'type' => 'sans-serif',
				'x' => 0.6986,
				'mu' => 2.02),
			'KoHo' => array(
				'type' => 'sans-serif',
				'x' => 0.6699,
				'mu' => 2.35),
			'Krub' => array(
				'type' => 'sans-serif',
				'x' => 0.7847,
				'mu' => 2.14),
			'Lato' => array(
				'type' => 'sans-serif',
				'x' => 0.7070,
				'mu' => 2.33),
			'Lekton' => array(
				'type' => 'serif',
				'x' => 0.7208,
				'mu' => 2),
			'Libre Baskerville' => array(
				'type' => 'serif',
				'x' => 0.6840,
				'mu' => 1.97),
			'Libre Franklin' => array(
				'type' => 'serif',
				'x' => 0.7085,
				'mu' => 2.18),
			'Lobster Two' => array(
				'type' => 'serif',
				'x' => 0.6787,
				'mu' => 2.78),
			'Lora' => array(
				'type' => 'serif',
				'x' => 0.7143,
				'mu' => 2.16),
			'Mali' => array(
				'type' => 'sans-serif',
				'x' => 0.7268,
				'mu' => 2.01),
			'Manuale' => array(
				'type' => 'serif',
				'x' => 0.8033,
				'mu' => 2.33),
			'Marvel' => array(
				'type' => 'sans-serif',
				'x' => 0.7143,
				'mu' => 2.91),
			'Merriweather' => array(
				'type' => 'serif',
				'x' => 0.7444,
				'mu' => 2.03),
			'Merriweather Sans' => array(
				'type' => 'sans-serif',
				'x' => 0.7489,
				'mu' => 2.05),
			'Montserrat' => array(
				'type' => 'sans-serif',
				'x' => 0.7524,
				'mu' => 2.01),
			'Montserrat Alternates' => array(
				'type' => 'sans-serif',
				'x' => 0.7524,
				'mu' => 1.97),
			'Muli' => array(
				'type' => 'sans-serif',
				'x' => 0.7056,
				'mu' => 2.18),
			'Neuton' => array(
				'type' => 'serif',
				'x' => 0.7033,
				'mu' => 2.74),
			'Niramit' => array(
				'type' => 'sans-serif',
				'x' => 0.6890,
				'mu' => 2.27),
			'Nobile' => array(
				'type' => 'sans-serif',
				'x' => 0.7403,
				'mu' => 2.1),
			'Noticia Text' => array(
				'type' => 'serif',
				'x' => 0.7980,
				'mu' => 2.14),
			'Noto Sans' => array(
				'type' => 'sans-serif',
				'x' => 0.7477,
				'mu' => 2.13),
			'Noto Serif' => array(
				'type' => 'serif',
				'x' => 0.7477,
				'mu' => 2.1),
			'Nunito' => array(
				'type' => 'sans-serif',
				'x' => 0.7056,
				'mu' => 2.25),
			'Nunito Sans' => array(
				'type' => 'sans-serif',
				'x' => 0.6869,
				'mu' => 2.25),
			'Old Standard TT' => array(
				'type' => 'serif',
				'x' => 0.6355,
				'mu' => 2.31),
			'Open Sans' => array(
				'type' => 'sans-serif',
				'x' => 0.7477,
				'mu' => 2.16),
			'Overlock' => array(
				'type' => 'sans-serif',
				'x' => 0.7100,
				'mu' => 2.5),
			'Overpass' => array(
				'type' => 'sans-serif',
				'x' => 0.7286,
				'mu' => 2.25),
			'Philosopher' => array(
				'type' => 'sans-serif',
				'x' => 0.7121,
				'mu' => 2.36),
			'Playfair Display' => array(
				'type' => 'serif',
				'x' => 0.7264,
				'mu' => 2.24),
			'Playfair Display SC' => array(
				'type' => 'serif',
				'x' => 0.8538,
				'mu' => 1.84),
			'Poppins' => array(
				'type' => 'sans-serif',
				'x' => 0.7736,
				'mu' => 2.03),
			'Prompt' => array(
				'type' => 'sans-serif',
				'x' => 0.6825,
				'mu' => 2.04),
			'Proza Libre' => array(
				'type' => 'sans-serif',
				'x' => 0.7209,
				'mu' => 2.08),
			'PT Sans' => array(
				'type' => 'sans-serif',
				'x' => 0.7143,
				'mu' => 2.35),
			'PT Serif' => array(
				'type' => 'serif',
				'x' => 0.7143,
				'mu' => 2.25),
			'Puritan' => array(
				'type' => 'sans-serif',
				'x' => 0.7760,
				'mu' => 2.38),
			'Quantico' => array(
				'type' => 'sans-serif',
				'x' => 0.7156,
				'mu' => 2.12),
			'Quattrocento Sans' => array(
				'type' => 'sans-serif',
				'x' => 0.6970,
				'mu' => 2.33),
			'Raleway' => array(
				'type' => 'sans-serif',
                'x' => 0.7324,
				'mu' => 2.18),
			'Rambla' => array(
				'type' => 'sans-serif',
				'x' => 0.7550,
				'mu' => 2.47),
			'Roboto' => array(
				'type' => 'sans-serif',
				'x' => 0.7418,
				'mu' => 2.28),
			'Roboto Condensed' => array(
				'type' => 'sans-serif',
				'x' => 0.7418,
				'mu' => 2.56),
			'Roboto Mono' => array(
				'type' => 'sans-serif',
				'x' => 0.7418,
				'mu' => 1.67,
				'monospace' => true),
			'Roboto Slab' => array(
				'type' => 'serif',
				'x' => 0.7418,
				'mu' => 2.12),
			'Rosario' => array(
				'type' => 'sans-serif',
				'x' => 0.6835,
				'mu' => 2.41),
			'Rubik' => array(
				'type' => 'sans-serif',
				'x' => 0.7429,
				'mu' => 2.17),
			'Sansita' => array(
				'type' => 'sans-serif',
				'x' => 0.7727,
				'mu' => 2.51),
			'Scada' => array(
				'type' => 'sans-serif',
				'x' => 0.7143,
				'mu' => 2.3),
			'Share' => array(
				'type' => 'sans-serif',
				'x' => 0.7143,
				'mu' => 2.5),
			'Source Sans Pro' => array(
				'type' => 'sans-serif',
				'x' => 0.7360,
				'mu' => 2.43),
			'Space Mono' => array(
				'type' => 'sans-serif',
				'x' => 0.7095,
				'mu' => 1.63,
				'monospace' => true),
			'Spectral' => array(
				'type' => 'serif',
				'x' => 0.6818,
				'mu' => 2.27),
			'Spectral SC' => array(
				'type' => 'serif',
				'x' => 0.8434,
				'mu' => 1.82),
			'Taviraj' => array(
				'type' => 'serif',
				'x' => 0.6714,
				'mu' => 2.17),
			'Thasadith' => array(
				'type' => 'sans-serif',
				'x' => 0.7273,
				'mu' => 2.56),
			'Tinos' => array(
				'type' => 'serif',
				'x' => 0.7005,
				'mu' => 2.48),
			'Titillium Web' => array(
				'type' => 'sans-serif',
				'x' => 0.7212,
				'mu' => 2.4),
			'Trirong' => array(
				'type' => 'serif',
				'x' => 0.7095,
				'mu' => 2.12),
			'Trochut' => array(
				'type' => 'sans-serif',
				'x' => 0.6845,
				'mu' => 2.86),
			'Ubuntu' => array(
				'type' => 'sans-serif',
				'x' => 0.7500,
				'mu' => 2.22),
			'Ubuntu Mono' => array(
				'type' => 'sans-serif',
				'x' => 0.7473,
				'mu' => 2,
				'monospace' => true),
			'Unna' => array(
				'type' => 'serif',
				'x' => 0.7039,
				'mu' => 2.52),
			'Volkhov' => array(
				'type' => 'serif',
				'x' => 0.7143,
				'mu' => 2.09),
			'Vollkorn' => array(
				'type' => 'serif',
				'x' => 0.6749,
				'mu' => 2.31),
			'Zilla Slab' => array(
				'type' => 'serif',
				'x' => 0.6837,
				'mu' => 2.34)));
		if (is_array($primary_fonts))
			foreach ($primary_fonts as $name => $font)
				if (!empty($name) && is_string($name))
					$fonts[$name] = array_filter(array(
						'name' => "$name (G)",
						'family' => "\"$name\"". (!empty($font['type']) ? ", {$font['type']}" : ''),
						'styles' => "$name:". (!empty($font['styles']) ? $font['styles'] : "400,400i,700"). (!empty($font['add_styles']) ? ",{$font['add_styles']}" : ''),
						'x' => !empty($font['x']) ? $font['x'] : false,
						'mu' => isset($font['mu']) && is_numeric($font['mu']) ? $font['mu'] : false,
						'monospace' => isset($font['monospace']) ? $font['monospace'] : false));
		return $fonts;
	}
}