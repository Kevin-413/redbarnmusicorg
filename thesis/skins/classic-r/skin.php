<?php
/*
Name: Classic Responsive
Author: Chris Pearson
Description: Elegant and versatile, the Classic Responsive Skin features clean lines and mathematical precision with an emphasis on typography.
Version: 1.6.1
Requires: 2.6
Class: thesis_classic_r
Docs: https://diythemes.com/thesis/rtfm/skins/classic-responsive/
Changelog: https://diythemes.com/thesis/rtfm/skins/classic-responsive/changelog/#section-v161
License: MIT

Copyright 2013 DIYthemes, LLC.

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/
class thesis_classic_r extends thesis_skin {
/*
	Skin API $functionality array. Enable or disable certain Skin features with ease.
	— https://diythemes.com/thesis/rtfm/api/skin/properties/functionality/
*/
	public $functionality = array(
		'css_preprocessor' => 'scss',
		'formatting_class' => 'grt',
		'fonts_google' => true,
		'header_image' => true,
		'logo' => true);

/*
	Skin API pseudo-constructor; place hooks, filters, and other initializations here.
	— https://diythemes.com/thesis/rtfm/api/skin/methods/construct/
*/
	protected function construct() {
		// implement display options that do not follow the normal pattern
		if (!empty($this->display['misc']['display']['braces'])) {
			add_filter('thesis_post_num_comments', array($this, 'num_comments'));
			add_filter('thesis_comments_intro', array($this, 'comments_intro'));
		}
		// the previous/next links (found on home, archive, and single templates) require special filtering based on page context
		add_filter('thesis_html_container_prev_next_show', array($this, 'prev_next'));
		// comment control icons (reply, link, and edit)
		if (!empty($this->display['comments']['display']['icons'])) {
			add_filter('thesis_comment_reply_text', array($this, 'comment_reply'));
			add_filter('thesis_comment_permalink_text', array($this, 'comment_link'));
			add_filter('thesis_comment_edit', array($this, 'comment_edit'));
		}
		// hook header image into the proper location for this Skin
		add_action('hook_bottom_header', array($this, 'header_image'));
	}

/*---:[ Implement custom options/output for this Skin ]:---*/

/*
	The following is a special filter to prevent the prev/next container from showing if a query only has 1 page of results.
*/
	public function prev_next() {
		global $wp_query;
		return (($wp_query->is_home || $wp_query->is_archive || $wp_query->is_search) && $wp_query->max_num_pages > 1) || ($wp_query->is_single && !empty($this->display['misc']['display']['prev_next'])) ? true : false;
	}

	public function num_comments($content) {
		return "<span class=\"bracket\">{</span> $content <span class=\"bracket\">}</span>";
	}

	public function comments_intro($text) {
		return "<span class=\"bracket\">{</span> $text <span class=\"bracket\">}</span>";
	}

	public function comment_reply($text) {
		return '&#8617;';
	}

	public function comment_link($text) {
		return '&#8734;';
	}

	public function comment_edit($text) {
		return '&#9998;';
	}

/*
	Output user-selected header image by referencing the Skin API header image object
	and its associated html() method.
	— https://diythemes.com/thesis/rtfm/api/skin/properties/functionality/header-image/
*/
	public function header_image() {
		$this->header_image->html();
	}

/*
	Skin API method for filtering the CSS output whenever the stylesheet is rewritten.
	Here, we are adding some CSS if the user has selected a logo or header image.
	— https://diythemes.com/thesis/rtfm/api/skin/methods/filter_css/
*/
	public function filter_css($css) {
		return $css.
		(!empty($this->logo->image) ?
			"\n#site_title {\n".
			"\tline-height: 0.1em;\n".
			"}\n".
			"\n#site_title a {\n".
			"\tdisplay: inline-block;\n".
			"}\n" : '').
		(!empty($this->header_image->image) ?
			"\n#header {\n".
			"\tpadding: 0;\n".
			"}\n".
			"#header #site_title a, #header #site_tagline {\n".
			"\tdisplay: none;\n".
			"}\n" : '');
	}

/*---:[ WooCommerce compatibility ]:---*/

/*
	Skin API method for initiating WooCommerce template compatibility. Relevant hooks,
	filters, and other actions that affect the front end should be included here.
	— https://diythemes.com/thesis/rtfm/api/skin/methods/woocommerce/
*/
	public function woocommerce() {
		if (is_woocommerce()) {
			// Do not show the prev/next container on WooCommerce shop, product, or archive pages
			add_filter('thesis_html_container_prev_next_show', '__return_false');
			// On the Shop page, suppress the Thesis Archive Intro area
			if (is_shop())
				add_filter('thesis_html_container_archive_intro_show', '__return_false');
			// On product archive pages, remove WooCommerce title in favor of the Thesis Skin archive title
			elseif (!is_singular('product'))
				add_filter('woocommerce_show_page_title', '__return_false');
		}
		// Suppress bylines and avatar pictures on Cart, Checkout, and My Account pages
		elseif (is_cart() || is_checkout() || is_account_page() || is_page('order-tracking')) {
			add_filter('thesis_post_author_avatar_loop_show', '__return_false');
			add_filter('thesis_html_container_byline_show', '__return_false');
		}
	}

/*---:[ Skin Display Options ]:---*/

/*
	Skin API method for initiating display options; return an array in Thesis Options API array format.
	— Display options: https://diythemes.com/thesis/rtfm/api/skin/display-options/
	— Options API array format: https://diythemes.com/thesis/rtfm/api/options/array-format/
*/
	protected function display() {
		return array( // use an options object set for simplified display controls
			'display' => array(
				'type' => 'object_set',
				'select' => __('Select content to display:', 'thesis_classic_r'),
				'objects' => array(
					'site' => array(
						'type' => 'object',
						'label' => __('Site Title &amp; Tagline', 'thesis_classic_r'),
						'fields' => array(
							'display' => array(
								'type' => 'checkbox',
								'options' => array(
									'title' => __('Site title', 'thesis_classic_r'),
									'tagline' => __('Site tagline', 'thesis_classic_r')),
								'default' => array(
									'title' => true,
									'tagline' => true)))),
					'loop' => array(
						'type' => 'object',
						'label' => __('Post/Page Output', 'thesis_classic_r'),
						'fields' => array(
							'display' => array(
								'type' => 'checkbox',
								'options' => array(
									'author' => __('Author', 'thesis_classic_r'),
									'avatar' => __('Author avatar', 'thesis_classic_r'),
									'description' => __('Author description (Single template)', 'thesis_classic_r'),
									'date' => __('Date', 'thesis_classic_r'),
									'wp_featured_image' => __('WP featured image', 'thesis_classic_r'),
									'image' => __('Thesis post image (Single, Page, and Landing Page templates)', 'thesis_classic_r'),
									'thumbnail' => __('Thesis thumbnail image (Home template)', 'thesis_classic_r'),
									'num_comments' => __('Number of comments (Home and Archive templates)', 'thesis_classic_r'),
									'cats' => __('Categories', 'thesis_classic_r'),
									'tags' => __('Tags', 'thesis_classic_r')),
								'default' => array(
									'author' => true,
									'date' => true,
									'wp_featured_image' => true,
									'num_comments' => true)))),
					'comments' => array(
						'type' => 'object',
						'label' => __('Comments', 'thesis_classic_r'),
						'fields' => array(
							'display' => array(
								'type' => 'checkbox',
								'options' => array(
									'post' => __('Comments on posts', 'thesis_classic_r'),
									'page' => __('Comments on pages', 'thesis_classic_r'),
									'date' => __('Comment date', 'thesis_classic_r'),
									'avatar' => __('Comment avatar', 'thesis_classic_r'),
									'icons' => __('Use icons for comment reply, link, and edit', 'thesis_classic_r')),
								'default' => array(
									'post' => true,
									'date' => true,
									'avatar' => true,
									'icons' => true)))),
					'sidebar' => array(
						'type' => 'object',
						'label' => __('Sidebar', 'thesis_classic_r'),
						'fields' => array(
							'display' => array(
								'type' => 'checkbox',
								'options' => array(
									'sidebar' => __('Sidebar', 'thesis_classic_r'),
									'text' => __('Sidebar Text Box', 'thesis_classic_r'),
									'widgets' => __('Sidebar Widgets', 'thesis_classic_r')),
								'default' => array(
									'sidebar' => true,
									'text' => true,
									'widgets' => true)))),
					'misc' => array(
						'type' => 'object',
						'label' => __('Miscellaneous', 'thesis_classic_r'),
						'fields' => array(
							'display' => array(
								'type' => 'checkbox',
								'options' => array(
									'braces' => __('Iconic Classic Responsive Skin curly braces', 'thesis_classic_r'),
									'prev_next' => __('Previous/next post links (single template)', 'thesis_classic_r'),
									'attribution' => __('Skin attribution', 'thesis_classic_r'),
									'wp_admin' => __('WP admin link', 'thesis_classic_r')),
								'default' => array(
									'braces' => true,
									'prev_next' => true,
									'attribution' => true,
									'wp_admin' => true)))))));
	}

/*
	Skin API method for automatic show/hide handling of elements with display options.
	Display options are defined in the display() method below.
	— https://diythemes.com/thesis/rtfm/api/skin/methods/display_elements/
*/
	public function display_elements() {
		return array( // Display options with filter references
			'site' => array(
				'title' => 'thesis_site_title',
				'tagline' => 'thesis_site_tagline'),
			'loop' => array( // 'loop' has been added as a programmatic ID to these Boxes
				'author' => 'thesis_post_author_loop',
				'avatar' => 'thesis_post_author_avatar_loop',
				'description' => 'thesis_post_author_description_loop',
				'date' => 'thesis_post_date_loop',
				'wp_featured_image' => 'thesis_wp_featured_image_loop',
				'cats' => 'thesis_post_categories_loop',
				'tags' => 'thesis_post_tags_loop',
				'num_comments' => 'thesis_post_num_comments_loop',
				'image' => 'thesis_post_image_loop',
				'thumbnail' => 'thesis_post_thumbnail_loop'),
			'comments' => array( // 'comments' has been added as a programmatic ID to the date and avatar Boxes
				'post' => 'thesis_html_container_post_comments',
				'page' => 'thesis_html_container_page_comments',
				'date' => 'thesis_comment_date_comments',
				'avatar' => 'thesis_comment_avatar_comments'),
			'sidebar' => array( // 'sidebar' is the hook name for 'sidebar' and the programmatic ID for text and widgets
				'sidebar' => 'thesis_html_container_sidebar',
				'text' => 'thesis_text_box_sidebar',
				'widgets' => 'thesis_wp_widgets_sidebar'),
			'misc' => array(
				'attribution' => 'thesis_attribution',
				'wp_admin' => 'thesis_wp_admin'));
	}

/*---:[ Skin Design Options ]:---*/

/*
	Skin API method for initiating design options; return an array in Thesis Options API array format.
	— Design options: https://diythemes.com/thesis/rtfm/api/skin/design-options/
	— Options API array format: https://diythemes.com/thesis/rtfm/api/options/array-format/
*/
	protected function design() {
		$css = $this->css_tools->options; // shorthand for all options available in the CSS API
		$fsc = $fs = $fc = $this->css_tools->font_size_color(); // the CSS API contains shorthand for font, size, and color options
		unset($fs['color']); // remove nav text color control
		unset($fc['font-size']); // remove code size control
		$links['default'] = 'DD0000'; // default link color
		$links['gray'] = $this->color->gray($links['default']); // array of 'hex' and 'rgb' values
		return array(
			'colors' => $this->color_scheme(array( // the Skin API contains a color_scheme() method for easy implementation
				'id' => 'colors',
				'colors' => array(
					'text1' => __('Primary Text', 'thesis_classic_r'),
					'text2' => __('Secondary Text', 'thesis_classic_r'),
					'links' => __('Links', 'thesis_classic_r'),
					'color1' => __('Borders &amp; Highlights', 'thesis_classic_r'),
					'color2' => __('Interior <abbr title="background">BG</abbr>s', 'thesis_classic_r'),
					'color3' => __('Site <abbr title="background">BG</abbr>', 'thesis_classic_r')),
				'default' => array(
					'text1' => '111111',
					'text2' => '888888',
					'links' => $links['default'],
					'color1' => 'DDDDDD',
					'color2' => 'EEEEEE',
					'color3' => 'FFFFFF'),
				'scale' => array(
					'links' => $links['gray']['hex'],
					'color1' => 'DDDDDD',
					'color2' => 'EEEEEE',
					'color3' => 'FFFFFF'))),
			'elements' => array( // this is an object set containing all other design options for this Skin
				'type' => 'object_set',
				'label' => __('Layout, Fonts, Sizes, and Colors', 'thesis_classic_r'),
				'select' => __('Select a design element to edit:', 'thesis_classic_r'),
				'objects' => array(
					'layout' => array(
						'type' => 'object',
						'label' => __('Layout &amp; Dimensions', 'thesis_classic_r'),
						'fields' => array(
							'columns' => array(
								'type' => 'select',
								'label' => __('Layout', 'thesis_classic_r'),
								'options' => array(
									1 => __('1 column', 'thesis_classic_r'),
									2 => __('2 columns', 'thesis_classic_r')),
								'default' => 2,
								'dependents' => array(2)),
							'order' => array(
								'type' => 'radio',
								'options' => array(
									'' => __('Content on the left', 'thesis_classic_r'),
									'right' => __('Content on the right', 'thesis_classic_r')),
								'parent' => array(
									'columns' => 2)),
							'width-content' => array(
								'type' => 'text',
								'width' => 'tiny',
								'label' => __('Content Width', 'thesis_classic_r'),
								'tooltip' => __('The default content column width is 617px. The value you enter here is the entire width of the column, including padding and borders. The resulting width of your text in this column is based on your selected font and font size. We recommend using Chrome Developer Tools or Firebug for Firefox to inspect the text width if you need to achieve a precise value.', 'thesis_classic_r'),
								'description' => 'px',
								'default' => 617),
							'width-sidebar' => array(
								'type' => 'text',
								'width' => 'tiny',
								'label' => __('Sidebar Width', 'thesis_classic_r'),
								'tooltip' => __('The default sidebar column width is 280px. The value you enter here is the entire width of the column, including padding. The resulting width of your text in this column is based on your selected font and font size. We recommend using Chrome Developer Tools or Firebug for Firefox to inspect the text width if you need to achieve a precise value.', 'thesis_classic_r'),
								'description' => 'px',
								'default' => 280,
								'parent' => array(
									'columns' => 2)))),
					'font' => array(
						'type' => 'object',
						'label' => __('Font &amp; Size (Primary)', 'thesis_classic_r'),
						'fields' => array(
							'family' => array_merge($css['font']['fields']['font-family'], array('default' => 'georgia')),
							'size' => array_merge($css['font']['fields']['font-size'], array('default' => 16)))),
					'headline' => array(
						'type' => 'group',
						'label' => __('Headlines', 'thesis_classic_r'),
						'fields' => $fsc),
					'subhead' => array(
						'type' => 'group',
						'label' => __('Sub-headlines', 'thesis_classic_r'). ' (&lt;h2&gt;, &lt;h3&gt;, &lt;h4&gt;)',
						'fields' => $fc),
					'blockquote' => array(
						'type' => 'group',
						'label' => __('Blockquotes', 'thesis_classic_r'),
						'fields' => $fc),
					'code' => array(
						'type' => 'group',
						'label' => __('Code: Inline &lt;code&gt;', 'thesis_classic_r'),
						'fields' => $fc),
					'pre' => array(
						'type' => 'group',
						'label' => __('Code: Pre-formatted &lt;pre&gt;', 'thesis_classic_r'),
						'fields' => $fc),
					'title' => array(
						'type' => 'object',
						'label' => __('Site Title', 'thesis_classic_r'),
						'fields' => $fsc),
					'tagline' => array(
						'type' => 'group',
						'label' => __('Site Tagline', 'thesis_classic_r'),
						'fields' => $fsc),
					'menu' => array(
						'type' => 'group',
						'label' => __('Nav Menu', 'thesis_classic_r'),
						'fields' => $fs),
					'sidebar' => array(
						'type' => 'group',
						'label' => __('Sidebar', 'thesis_classic_r'),
						'fields' => $fsc),
					'sidebar_heading' => array(
						'type' => 'group',
						'label' => __('Sidebar Headings', 'thesis_classic_r'),
						'fields' => $fc))));
	}

/*
	Skin API method for modifying CSS variables each time CSS is saved.
	Return an array of CSS variables, including units (if necessary), with their new values.
	Any variables not included in the return array will not be modified.
	— https://diythemes.com/thesis/rtfm/api/skin/methods/css_variables/
*/
	public function css_variables() {
		$columns = !empty($this->design['layout']['columns']) && is_numeric($this->design['layout']['columns']) ?
			$this->design['layout']['columns'] : 2;
		$order = !empty($this->design['layout']['order']) && $this->design['layout']['order'] == 'right' ? true : false;
		$px['w_content'] = !empty($this->design['layout']['width-content']) && is_numeric($this->design['layout']['width-content']) ?
			abs($this->design['layout']['width-content']) : 617;
		$px['w_sidebar'] = !empty($this->design['layout']['width-sidebar']) && is_numeric($this->design['layout']['width-sidebar']) ?
			abs($this->design['layout']['width-sidebar']) : 280;
		$px['w_total'] = $px['w_content'] + ($columns == 2 ? $px['w_sidebar'] : 0);
		// Primary content font, typographical scale, content width, and line height
		$vars['font'] = $this->fonts->family($font = !empty($this->design['font']['family']) ? $this->design['font']['family'] : 'georgia');
		$s['content'] = !empty($this->design['font']['size']) ? $this->design['font']['size'] : 16;
		$f['content'] = $this->typography->scale($s['content']);
/*
		The actual content column width, $w['content'], will be less than the value in the design options.
		This is because the padding must be deducted from both sides, and 1px border must be subtracted as well.
*/
		$w['content'] = $px['w_content'] - round(2 * $this->typography->height($s['content'], $px['w_content'] - round(2 * $this->typography->height($s['content'], false, $font)) - 1, $font)) - 1;
		$h['content'] = $this->typography->height($s['content'], $w['content'], $font);
		// Sidebar font size, typographical scale, width, and line height
		$sidebar_font = !empty($this->design['sidebar']['font']) ? $this->design['sidebar']['font'] : $font;
		$s['sidebar'] = !empty($this->design['sidebar']['font-size']) && is_numeric($this->design['sidebar']['font-size']) ?
			$this->design['sidebar']['font-size'] : $f['content']['f6'];
		$f['sidebar'] = $this->typography->scale($s['sidebar']);
		$w['sidebar'] = $px['w_sidebar'] - 2 * $h['content'];
		$h['sidebar'] = $this->typography->height($s['sidebar'], $w['sidebar'], $sidebar_font);
		// Set up an array containing numerical values that require a unit for CSS output
		$fonts = array(
			'f1' => !empty($this->design['title']['font-family']) ? $this->design['title']['font-family'] : $font,
			'f2' => !empty($this->design['headline']['font-family']) ? $this->design['headline']['font-family'] : $font,
			'f3' => !empty($this->design['subhead']['font-family']) ? $this->design['subhead']['font-family'] : $font,
			'f4' => !empty($this->design['subhead']['font-family']) ? $this->design['subhead']['font-family'] : $font);
		foreach ($f['content'] as $heading => $font_size) {
			$px[$heading] = $font_size;
			$px['h'. trim($heading, 'f')] = round($this->typography->height($font_size, $w['content'], !empty($fonts[$heading]) ?
				$fonts[$heading] : $font));
		}
		// Sidebar spacing variables
		$px['sf5'] = $s['sidebar'];
		$px['sh5'] = round($h['sidebar']);
		// Add the 'px' unit to the $px array constructed above
		$vars = is_array($px) ? array_merge($vars, $this->css_tools->unit($px)) : $vars;
		// Use the Colors API to set up proper CSS color references
		foreach (array('text1', 'text2', 'links', 'color1', 'color2', 'color3') as $color)
			$vars[$color] = !empty($this->design[$color]) ? $this->color->css($this->design[$color]) : false;
		// Set up a modification array for individual typograhical overrides
		$elements = array(
			'menu' => array(
				'font-family' => false,
				'font-size' => $f['content']['f6']),
			'title' => array(
				'font-family' => false,
				'font-size' => $f['content']['f1']),
			'tagline' => array(
				'font-family' => false,
				'font-size' => $f['content']['f5'],
				'color' => !empty($vars['text2']) ? $vars['text2'] : false),
			'headline' => array(
				'font-family' => false,
				'font-size' => $f['content']['f2']),
			'subhead' => array(
				'font-family' => false),
			'blockquote' => array(
				'font-family' => false,
				'color' => !empty($vars['text2']) ? $vars['text2'] : false),
			'code' => array(
				'font-family' => 'consolas',
				'color' => false),
			'pre' => array(
				'font-family' => 'consolas',
				'color' => false),
			'sidebar' => array(
				'font-family' => false,
				'font-size' => $f['sidebar']['f5'],
				'color' => false),
			'sidebar_heading' => array(
				'font-family' => false,
				'font-size' => $f['sidebar']['f3'],
				'color' => false));
		// Loop through the modification array to see if any fonts, sizes, or colors need to be overridden
		foreach ($elements as $name => $element) {
			foreach ($element as $p => $def)
				$e[$name][$p] = $p == 'font-family' ?
					(!empty($this->design[$name][$p]) ?
						"$p: ". $this->fonts->family($family[$name] = $this->design[$name][$p]). ';' : (!empty($def) ?
						"$p: ". $this->fonts->family($family[$name] = $def). ';' : false)) : ($p == 'font-size' ?
					(!empty($this->design[$name][$p]) && is_numeric($this->design[$name][$p]) ?
						"$p: ". ($size[$name] = $this->design[$name][$p]). "px;" : (!empty($def) ?
						"$p: ". ($size[$name] = $def). "px;" : false)) : ($p == 'color' ?
					(!empty($this->design[$name][$p]) ?
						"$p: ". $this->color->css($this->design[$name][$p]). ';' : (!empty($def) ?
						"$p: $def;" : false)) : false));
			$e[$name] = array_filter($e[$name]);
		}
		foreach ($e as $name => $element)
			$vars[$name] = implode("\n\t", $element);
		// Calculate custom line heights for elements that can have different fonts and/or sizes
		if (!empty($size['headline']))
			$vars['headline'] .= "\n\tline-height: ". ($line['headline'] = round($this->typography->height($size['headline'], $w['content'], !empty($family['headline']) ? $family['headline'] : $font))). "px;";
		foreach (array('sidebar', 'sidebar_heading') as $name)
			if (!empty($size[$name]))
				$vars[$name] .= "\n\tline-height: ". round($this->typography->height($size[$name], $w['sidebar'], !empty($family[$name]) ? $family[$name] : $sidebar_font)). "px;";
		// Determine multi-use color variables
		foreach (array('title', 'headline', 'subhead') as $name)
			$vars["{$name}_color"] = !empty($this->design[$name]['color']) ?
				$this->color->css($this->design[$name]['color']) : (!empty($vars['text1']) ? $vars['text1'] : false);
		// Set up property-value variables, which, unlike the other variables above, contain more than just a CSS value
		$vars['column1'] =
			"float: ". ($columns == 2 ? ($order ? 'right' : 'left') : 'none'). ";\n\t".
			"border-width: ". ($columns == 2 ? ($order ? '0 0 0 1px' : '0 1px 0 0') : '0'). ";";
		$vars['column2'] =
			"width: ". ($columns == 2 ? '$w_sidebar' : '100%'). ";\n\t".
			"float: ". ($columns == 2 ? ($order ? 'left' : 'right') : 'none'). ';'. ($columns == 1 ?
			"\n\tborder-top: 3px double \$color1;" : '');
		$w_submenu = round($this->typography->width($menu_size = !empty($size['menu']) ? $size['menu'] : $px['f6']) * (1 - (1 / $this->typography->phi)));
		$vars['menu_f'] = "{$menu_size}px";
		$vars['submenu'] = "{$w_submenu}px";
		$vars['menu'] .= "\n\tline-height: ". round($this->typography->height($menu_size, $w_submenu, !empty($family['menu']) ? $family['menu'] : $font)). "px;";
		$vars['pullquote'] =
			"font-size: ". $f['content']['f3']. "px;\n\t".
			"line-height: ". round($this->typography->height($f['content']['f3'], round(0.45 * $w['content'], 0), !empty($family['blockquote']) ? $family['blockquote'] : $font)). "px;";
		$vars['avatar'] =
			"width: ". ($avatar = $line['headline'] + $px['h6']). "px;\n\t".
			"height: {$avatar}px;";
		$vars['comment_avatar'] =
			"width: ". ($px['h5'] + $px['h6']). "px;\n\t".
			"height: ". ($px['h5'] + $px['h6']). "px;";
		foreach (array(2, 3, 4) as $factor)
			if (($bio_size = $factor * $px['h5']) <= 96)
				$bio = $bio_size;
		$vars['bio_avatar'] =
			"width: {$bio}px;\n\t".
			"height: {$bio}px;";
		$vars['woocommerce'] = defined('WC_PLUGIN_FILE') ? true : false;
		return $vars;
	}
}