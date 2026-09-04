<?php
/*
Copyright 2015 DIYthemes, LLC. All rights reserved.

License: DIYthemes Software License Agreement
License URI: https://diythemes.com/thesis/rtfm/software-license-agreement/
Requires: Core Thesis object

About this class:
=============================
If the user has selected a logo, it will replace the site title in the HTML output. Please note that
the Site Title Box must be included in your Skin's templates for this functionality to work. You may
also need to add CSS to handle the resulting image output in a precise manner.
*/
class thesis_skin_logo {
	public $image = array();	// (array) array containing logo image src, width, height, and id
	private $skin = false;		// (string) class name of the Skin employing this functionality
	private $name = false;		// (string) Skin name

/*
	TODO: Why are Skin values not passed to these items when they are activated? A more OO approach
	would be to pass the Skin values via the constructor.
*/
	public function __construct() {
		global $thesis;
		$this->skin = !empty($thesis) && !empty($thesis->skins->skin['class']) ? $thesis->skins->skin['class'] : $this->skin;
		$this->name = !empty($thesis) && !empty($thesis->skins->skin['name']) ? "{$thesis->skins->skin['name']} " : $this->name;
		$this->image = is_array($logo = $thesis->api->get_option("{$this->skin}_logo")) ? $logo : $this->image;
		add_filter('thesis_site_title_logo', array($this, 'html'));
		if (empty($this->skin) || empty($this->name) || empty($thesis->environment)) return;
		// Add a Logo item to the Thesis Skin and WordPress dashboard (quicklaunch) menus
		add_filter('thesis_skin_menu', array($this, 'menu'), 11);
		add_filter('thesis_quicklaunch_menu_skin', array($this, 'menu'), 11);
		// Add an options page to the Thesis Admin
		if (!empty($_GET['canvas']) && $_GET['canvas'] == "{$this->skin}_logo") {
			add_action('admin_init', array($this, 'admin_init'));
			add_action('thesis_admin_canvas', array($this, 'admin'));
		}
		// Handle the saving of data on the newly-added options page
		add_action("admin_post_{$this->skin}_logo", array($this, 'save'));
	}

/*
	Filter method for adding an item to the WordPress Dashboard (quicklaunch) menu
*/
	public function menu($menu) {
		$menu["{$this->skin}_logo"] = array(
			'text' => __('Logo', 'thesis'),
			'url' => "admin.php?page=thesis&canvas={$this->skin}_logo",
			'description' => __('Add a Logo to your design', 'thesis'));
		return $menu;
	}

/*
	Initialize CSS, JS, and the WordPress Media Uploader for the logo admin page
*/
	public function admin_init() {
		wp_enqueue_style('thesis-options');
		wp_enqueue_media();
		wp_enqueue_script('custom-header');
	}

/*
	Logo admin page output
*/
	public function admin() {
		global $thesis;
		$action = "{$this->skin}_logo";
		$docs = !empty($thesis->skin) && !empty($thesis->skin->docs) && !empty($thesis->skin->docs['logo']) ?
			' <a data-style="dashicon" href="'. esc_url($thesis->skin->docs['logo']). '" title="'. __('See Documentation', 'thesis'). '" target="_blank" rel="noopener">&#xf348;</a>' : '';
		echo
			"\t\t<h3>", __("{$this->name}Skin Logo", 'thesis'), "$docs</h3>\n",
			"\t\t<div class=\"option_item\" id=\"t_header_image_container\">\n",
			(!empty($this->image) ?
			"\t\t\t<img src=\"". esc_url($thesis->api->url_current($this->image['src'])). "\" height=\"". (int) $this->image['height']. "\" width=\"". (int) $this->image['width']. "\"/>\n".
			"\t\t\t<p style=\"font-size: 14px; color: #888;\">". sprintf(__('Current logo is %1$dpx wide by %2$dpx tall.', 'thesis'), (int) $this->image['width'], (int) $this->image['height']). "</p>\n" : ''),
			"\t\t</div>\n",
			"\t\t<p>\n",
			"\t\t<button id=\"choose-from-library-link\" data-style=\"save button\" data-update-link=\"", esc_url(add_query_arg(array('action' => $action, '_wpnonce' => wp_create_nonce($action)), admin_url("admin-post.php"))), "\" data-choose=\"", __('Select a Logo', 'thesis'), "\" data-update=\"", __('Set Logo', 'thesis'), "\"><span data-style=\"dashicon\">&#xf332;</span> ", $thesis->api->efn(__('Select Logo', 'thesis')), "</button>\n",
			(!empty($this->image) ?
			"\t\t<a id=\"t_delete_header_image\" data-style=\"button delete inline\" href=\"". esc_url(add_query_arg(array('action' => $action, '_wpnonce' => wp_create_nonce($action), 'delete' => 'true'), admin_url("admin-post.php"))). "\"><span data-style=\"dashicon\">&#xf153;</span> ". $thesis->api->efn(__('Remove Logo', 'thesis')). "</a>\n" : ''),
			"\t\t</p>\n";
	}

/*
	Save the data from the Logo admin page
*/
	public function save() {
		global $thesis;
		$thesis->wp->check();
		$thesis->wp->nonce($_GET['_wpnonce'], "{$this->skin}_logo");
		if (!empty($_GET['delete']) && $_GET['delete'] === 'true') {
			delete_option("{$this->skin}_logo");
			$this->image = array();
		}
		else {
			$id = (int) $_GET['file'];
			$image = wp_get_attachment_metadata($id);
			update_option("{$this->skin}_logo",
				$this->image = array(
					'src' => esc_url_raw(wp_get_attachment_url($id)),
					'height' => (int) $image['height'],
					'width' => (int) $image['width'],
					'id' => $id));
		}
		wp_cache_flush();
		$thesis->skin->_write_css();
		wp_redirect(admin_url("admin.php?page=thesis&canvas={$this->skin}_logo"));
		exit;
	}

/*
	Filter the site title, if necessary
*/
	public function html($title) {
		global $thesis;
		$ref = esc_attr(apply_filters('thesis_logo_image', 'thesis_logo_image'));
		return empty($this->image) ? false :
			"<img id=\"$ref\" src=\"". esc_url($thesis->api->url_current($this->image['src'])). "\" alt=\"$title\" width=\"{$this->image['width']}\" height=\"{$this->image['height']}\" title=\"". __('visit the home page', 'thesis'). "\" />";
	}
}