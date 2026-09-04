<?php
/*
Copyright 2012 DIYthemes, LLC. Patent pending. All rights reserved.
License: DIYthemes Software License Agreement
License URI: https://diythemes.com/thesis/rtfm/software-license-agreement/
*/
final class thesis_admin {
	public $updates = array();

	public function __construct() {
		if (!is_admin()) return;
		require_once(THESIS_ADMIN. '/home.php');
		require_once(THESIS_ADMIN. '/license_key.php');
		require_once(THESIS_ADMIN. '/system_status.php');
		require_once(THESIS_ADMIN. '/upgrade.php');
		add_action('wp_loaded', array($this, 'get_updates'));
		add_action('admin_menu', array($this, 'menu'), 5); #wp
		add_action('admin_head', array($this, 'admin_css'));
		add_action('thesis_quicklaunch_menu', array($this, 'menu_break'), 55);
		new thesis_admin_home;
		new thesis_license_key;
		new thesis_system_status;
		new thesis_upgrade;
		if (empty($_GET['page']) || !($_GET['page'] == 'thesis')) return;
		add_action('init', array($this, 'admin_queue'));
		add_action('admin_footer', array($this, 'menu_highlight'));
		add_action('admin_footer', array($this, 'update_js'));
		add_filter('screen_options_show_screen', '__return_false'); // suppress screen option pulldown on Thesis admin pages
	}

	public function get_updates() {
		do_action('thesis_check_updates');
		$this->updates = array();
		foreach (array('skins', 'boxes') as $type)
			if (($update = get_transient("thesis_{$type}_update")) && !empty($update))
				$this->updates[$type] = $update;
		$this->updates = apply_filters('thesis_updates', $this->updates);
	}

/*
	Set up the Thesis-specific WordPress Dashboard quicklaunch menu.
	This applies admin-wide, regardless of whether or not the current page is a Thesis admin page.
*/
	public function menu() {
		global $thesis;
		$icons = preg_match('/a|b/i', $thesis->version) ? __(' <span class="t_beta">Beta!</span>', 'thesis') : '';
		if (!empty($this->updates)) {
			$count = 0;
			foreach ($this->updates as $type => $updates)
				$count += $type == 'core' ? 1 : absint(count($updates));
			$icons .= " <span class=\"update-plugins count-$count\"><span class=\"t_update update-count\">$count</span></span>";
		}
		add_menu_page("Thesis{$icons}", "Thesis{$icons}", 'manage_options', 'thesis', array($this, 'admin'), THESIS_IMAGES_URL. '/favicon.ico', apply_filters('thesis_menu_position', 31)); #wp
		$quicklaunch = apply_filters('thesis_quicklaunch_menu', array(
			'thesis_home' => array(
				'text' => __('Thesis Home', 'thesis'). (!empty($this->updates['core']) ? ' <span class="update-plugins"><span>1</span></span>' : ''),
				'url' => 'thesis'),
			'break_home' => array(
				'text' => '––––––––––––',
				'url' => '#')));
		if (!empty($quicklaunch) && is_array($quicklaunch))
			foreach ($quicklaunch as $key => $link)
				if (!empty($link) && !empty($link['text']) && !empty($link['url']))
					add_submenu_page('thesis', (isset($link['title']) ? $link['title'] : 'Thesis'), $link['text'], 'manage_options', $link['url']);
	}

	public function menu_break($menu) {
		$menu['break_more'] = array(
			'text' => '––––––––––––',
			'url' => '#');
		return $menu;
	}

/*
	Adds current item highlighting to WP dashboard quicklaunch menu
*/
	public function menu_highlight() {
		if (!empty($_GET['canvas']))
			echo
				"<script>\n",
				"\tjQuery(document).ready(function($){\n",
				"\t\t$('#toplevel_page_thesis .wp-submenu li a').each(function(){\n",
				"\t\t\t$(this).parent('li').removeClass('current');\n",
				"\t\t\tif (new RegExp(\"", esc_attr($_GET['canvas']), "(?!\w)\").test($(this).attr('href')))\n",
				"\t\t\t\t$(this).parent('li').addClass('current');\n",
				"\t\t});\n",
				"\t});\n",
				"</script>\n";
	}

	public function admin_css() {
		global $thesis;
		echo
			"<style>\n",
			"#adminmenu .toplevel_page_thesis a[href=\"#\"] { cursor: default; pointer-events: none; }\n",
			(preg_match('/a|b/i', $thesis->version) ?
			"#adminmenu .toplevel_page_thesis .t_beta { color: orange; }\n" : ''),
			"</style>\n";
	}

/*---:[ Thesis Admin pages ]:---*/

/*
	Queue up scripts and stylesheets for Thesis admin pages.
	Note: Most stylesheets and scripts do not appear on Thesis admin pages by default,
	but they are available to be called upon if needed.
*/
	public function admin_queue() {
		global $thesis;
		if (!empty($_GET['canvas']) && $_GET['canvas'] == 'skin-editor-quicklaunch' && wp_verify_nonce($_GET['_wpnonce'], 'thesis-skin-editor-quicklaunch')) {
			wp_redirect(set_url_scheme(home_url('?thesis_editor=1')));
			exit;
		}
		else {
			$styles = array(
				'thesis-admin' => array(
					'url' =>  'admin.css'),
				'thesis-home' => array(
					'url' => 'home.css',
					'deps' => array('thesis-admin')),
				'thesis-options' => array(
					'url' => 'options.css',
					'deps' => array('thesis-admin')),
				'thesis-objects' => array(
					'url' => 'objects.css',
					'deps' => array('thesis-options')),
				'thesis-box-form' => array(
					'url' => 'box_form.css',
					'deps' => array('thesis-options')),
				'codemirror' => array(
					'url' => 'codemirror.css'));
			foreach ($styles as $name => $atts)
				wp_register_style($name, THESIS_CSS_URL. "/{$atts['url']}", (!empty($atts['deps']) ? $atts['deps'] : array()), $thesis->version);
			$scripts = array(
				'thesis-menu' => array(
					'url' => 'menu.js'),
				'thesis-options' => array(
					'url' => 'options.js',
					'deps' => array('thesis-menu')),
				'thesis-objects' => array(
					'url' => 'objects.js',
					'deps' => array('thesis-menu')),
				'codemirror' => array(
					'url' => 'codemirror.js'));
			foreach ($scripts as $name => $atts)
				wp_register_script($name, THESIS_JS_URL. "/{$atts['url']}", (!empty($atts['deps']) ? $atts['deps'] : array()), $thesis->version, true);
			wp_enqueue_style('thesis-admin'); #wp
			wp_enqueue_script('thesis-menu'); #wp
			if (empty($_GET['canvas']))
				wp_enqueue_style('thesis-home');
			elseif (in_array($_GET['canvas'], array('system_status', 'license_key')))
				wp_enqueue_style('thesis-options');
		}
	}

/*
	HTML wrapper for all Thesis admin pages
*/
	public function admin() {
		global $thesis;
		$php = version_compare(PHP_VERSION, '5.6', '>=') ? '' :
			"\t\t<p>". sprintf(__('<strong>Warning!</strong> This server is running a <em>very</em> old version of PHP (%1$s). To ensure both WordPress and Thesis function properly, you should ask your web host to upgrade this server to PHP 5.6 or higher.', 'thesis'), PHP_VERSION). "</p>\n";
		$caching = !empty($thesis->caching) && apply_filters('thesis_caching_notification', true) ?
			"\t\t<p>". sprintf(__('<strong>Attention!</strong> You are currently using a caching Plugin. Please <a href="%1$s" target="_blank" rel="noopener">follow these instructions</a> to use Thesis successfully with this Plugin.', 'thesis'), esc_url('https://diythemes.com/thesis/rtfm/troubleshooting/caching/')). "</p>\n" : '';
		$notify = !empty($php) || !empty($caching) ?
			"\t<div id=\"t_notify\">$php$caching</div>\n" : '';
		echo
			"\n<div id=\"t_admin\"", (is_rtl() ? ' class="rtl"' : ''), ">\n", #wp
			$notify,
			"\t<div id=\"t_header\">\n",
			"\t\t<a id=\"t_logo\" href=\"", admin_url('admin.php?page=thesis'), "\">Thesis</a>\n",
			$this->nav(),
			"\t</div>\n",
			"\t<div id=\"t_canvas\">\n";
		do_action('thesis_admin_canvas');
		echo
			"\t</div>\n",
			"</div>\n";
	}

/*
	Thesis admin nav menu
*/
	private function nav() {
		global $thesis;
		$menu = '';
		$box_updates = !empty($this->updates['boxes']) ?
			' <span class="count" title="'. __('Box updates are available', 'thesis'). '">'. count($this->updates['boxes']). '</span>' : '';
		$links = array(
			'skin_menu' => array(
				'text' => __('Skin', 'thesis'),
				'url' => false,
				'updates' => !empty($this->updates['skins']) ? '<span class="count" title="'. __('Skin updates are available', 'thesis'). '">'. count($this->updates['skins']). '</span>' : false,
				'submenu' => apply_filters('thesis_skin_menu', array())),
			'site_menu' => array(
				'text' => __($thesis->api->strings['site'], 'thesis'),
				'url' => false,
				'submenu' => apply_filters('thesis_site_menu', array())),
			'box_menu' => array(
				'text' => __('Boxes', 'thesis'),
				'url' => false,
				'updates' => $box_updates,
				'submenu' => array_merge(array(
					'boxes' => array(
						'text' => __('Manage Boxes', 'thesis'). $box_updates,
						'url' => admin_url('admin.php?page=thesis&canvas=boxes'))),
					is_array($boxes = $thesis->api->sort_by(apply_filters('thesis_boxes_menu', array()), 'text', true)) && !empty($boxes) ?
						array_merge(array(
							'manage_break' => array(
								'text' => '––––––––––––',
								'url' => '#')), $boxes) : array())));
		if (isset($_GET['show_packages']))
			$links['package_menu'] = array(
				'text' => __('Packages', 'thesis'),
				'url' => false,
				'submenu' => apply_filters('thesis_packages_menu', array()));
		$links['more'] = array(
			'text' => __('More', 'thesis'),
			'url' => false,
			'class' => 'more_menu',
			'submenu' => array_merge(apply_filters('thesis_more_menu', array()), array(
				'rtfm' => array(
					'text' => __('Documentation', 'thesis'),
					'url' => 'https://diythemes.com/thesis/rtfm/',
					'title' => __('Documentation, tutorials, and how-tos that will help you get the most out of Thesis.', 'thesis')),
				'forums' => array(
					'text' => __('Support Center', 'thesis'),
					'url' => 'https://diythemes.com/support/',
					'title' => __('Stuck? Submit a support request or visit the community forums.', 'thesis')),
				'blog' => array(
					'text' => __('DIYthemes Blog', 'thesis'),
					'url' => 'https://diythemes.com/blog/',
					'title' => __('Tutorials and advice from the man who makes Thesis!', 'thesis')),
/*				'aff' => array(
					'text' => __('Affiliate Program', 'thesis'),
					'url' => 'https://diythemes.com/affiliate-program/',
					'title' => __('Join the Thesis Affiliate Program and earn money selling Thesis!', 'thesis')),*/
				'version' => array(
					'id' => 't_version',
					'text' => sprintf(__('Version %s', 'thesis'), $thesis->version)))));
		$links['view_site'] = array(
			'text' => __('View Site', 'thesis'),
			'url' => home_url(),
			'title' => __('Check out your site!', 'thesis'),
			'class' => 'view_site',
			'icon' => '&#59392;');
		foreach ($links as $name => $link) {
			$submenu = '';
			$id = !empty($link['id']) ? " id=\"{$link['id']}\"" : '';
			$classes = !empty($link['class']) ? array($link['class']) : array();
			if (!empty($_GET['canvas']) && $name == $_GET['canvas']) $classes[] = 'current';
			if (isset($link['submenu'])) $classes[] = 'topmenu';
			$classes = is_array($classes) ? ' class="'. implode(' ', $classes). '"' : '';
			if (!empty($link['submenu']) && is_array($link['submenu'])) {
				foreach ($link['submenu'] as $item_name => $item) {
					$id = !empty($item['id']) ? " id=\"{$item['id']}\"" : '';
					$current = !empty($_GET['canvas']) && $item_name == $_GET['canvas'] ? ' class="current"' : '';
					$title = !empty($item['title']) ? " title=\"{$item['title']}\"" : '';
					$text = (!empty($item['icon']) ?
						'<span class="icon">'. $thesis->api->efh($item['icon']). '</span> ' : '').
						$item['text']. (!empty($item['updates']) ?
						" {$item['updates']}" : '');
					$submenu .=
						"\t\t\t\t\t<li$current>". (!empty($item['url']) ?
						"<a$id href=\"{$item['url']}\"$title>$text</a>" :
						"<span$id>$text</span>"). "</li>\n";
				}
				$menu .=
					"\t\t\t<li$classes><a$id class=\"topitem\"". (!empty($link['url']) ? " href=\"{$link['url']}\"" : ''). ">{$link['text']}". (!empty($link['updates']) ? " {$link['updates']}" : ''). "<span class=\"arrow_down\">&#9662;</span></a>\n".
					"\t\t\t\t<ul class=\"submenu\">\n".
					$submenu.
					"\t\t\t\t</ul>\n".
					"\t\t\t</li>\n";
			}
			else
				$menu .=
					"\t\t\t<li$classes><a$id class=\"toplink\" href=\"{$link['url']}\">". (!empty($link['icon']) ? 
					"<span class=\"icon\">". $thesis->api->efh($link['icon']). "</span> " : '').
					"{$link['text']}</a></li>\n";
		}
		return
			"\t\t<ul id=\"t_nav\">\n".
			$menu.
			"\t\t</ul>\n";
	}

/*
	The presence of this script allows the user to update Thesis components from any Thesis admin page
*/
	public function update_js() {
		if (empty($this->updates)) return;
		echo
			"<script>\n",
			(!empty($this->updates['core']) ?
			"\tfunction thesis_update_core() {\n".
			"\t\treturn confirm('". __('Are you sure you want to update? Core Thesis files will be overwritten, but your customizations will remain intact. Hit OK to continue or cancel to quit.', 'thesis'). "');\n".
			"\t}\n" : ''),
			(!empty($this->updates['skins']) ?
			"\tfunction thesis_update_skins() {\n".
			"\t\treturn confirm('". __('Are you sure you want to update? Thesis will backup all Skin data before updating (in the Skin Data Manager), so your customizations will not be compromised. Hit OK to continue or cancel to quit.', 'thesis'). "');\n".
			"\t}\n" : ''),
			(!empty($this->updates['boxes']) ?
			"\tfunction thesis_update_boxes() {\n".
			"\t\treturn confirm('". __('Are you sure you want to update? Box files will be overwritten, but your customizations will remain intact. Hit OK to continue or cancel to quit.', 'thesis'). "');\n".
			"\t}\n" : ''),
			"</script>\n";
	}
}