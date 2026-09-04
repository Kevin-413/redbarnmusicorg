<?php
/*
Copyright 2015 DIYthemes, LLC. Patent pending. All rights reserved.
License: DIYthemes Software License Agreement
License URI: https://diythemes.com/thesis/rtfm/software-license-agreement/
*/
class thesis_system_status {
	public function __construct() {
		add_filter('thesis_quicklaunch_menu', array($this, 'menu'), 70);
		add_filter('thesis_more_menu', array($this, 'menu'), 2);
		if (!empty($_GET['canvas']) && $_GET['canvas'] == 'system_status') {
			add_action('thesis_admin_canvas', array($this, 'admin'));
			add_action('admin_footer', array($this, 'js'));
		}
	}

	public function menu($menu) {
		$menu['system_status'] = array(
			'text' => __('System Status', 'thesis'),
			'url' => 'admin.php?page=thesis&canvas=system_status',
			'title' => __('Check your system for compatibility with Thesis.', 'thesis'));
		return $menu;
	}

/*
	System Status admin page
*/
	public function admin() {
		global $thesis, $wp_version, $wpdb;
		include_once(ABSPATH. '/wp-admin/includes/file.php');
		$table_status = $wpdb->get_results($wpdb->prepare("SHOW TABLE STATUS FROM $wpdb->dbname LIKE '%s'", $wpdb->options));
		$ver = mysqli_get_server_info($wpdb->dbh);
		$plugins = array();
		$all = get_plugins();
		$active = get_option('active_plugins');
		if (!empty($active))
			foreach ($active as $plugin)
				if (array_key_exists($plugin, $all) && !empty($all[$plugin]['Name']))
					$plugins[] = $all[$plugin]['Name'];
		$custom_css = get_option("{$thesis->skins->skin['class']}_css_custom");
		echo
			"\t\t<h3>", __('System Status', 'thesis'), "</h3>\n",
			"\t\t<div class=\"option_item option_field\">\n",
			"\t\t\t<p>\n",
			"\t\t\t\t<textarea id=\"t_system_status\" rows=\"25\">\n",
			__('Thesis &amp; Current Skin', 'thesis'), "\n",
			"===========================\n",
			sprintf(__('Thesis Version: %s', 'thesis'), esc_attr($thesis->version)), "\n",
			sprintf(__('Skin Name: %s', 'thesis'), esc_attr($thesis->skins->skin['name'])), "\n",
			sprintf(__('Skin Version: %s', 'thesis'), esc_attr($thesis->skins->skin['version'])), "\n",
			__('Skin CSS:', 'thesis'), ' ', (!is_file(THESIS_USER_SKIN. '/css.css') ? __('No Skin CSS File', 'thesis') : (is_writable(THESIS_USER_SKIN. '/css.css') ? 'YES &rarr; '. round(filesize(THESIS_USER_SKIN. '/css.css') / 1024, 1). 'kB on '. (date('m/d/Y \a\t H:i:s', filemtime(THESIS_USER_SKIN. '/css.css'))) : __('NO! The file is not writable by the server (permissions issue).', 'thesis'))), "\n",
			__('Skin Custom CSS:', 'thesis'), ' ', (!empty($custom_css) ? 'YES &rarr; '. round(strlen($custom_css) / 1024, 2). 'kB' : 'NO'), "\n",
			__('Skin Custom PHP:', 'thesis'), ' ', (is_file(THESIS_USER_SKIN. '/custom.php') ? 'YES &rarr; '. round(filesize(THESIS_USER_SKIN. '/custom.php') / 1024, 1). 'kB on '. (date('m/d/Y \a\t H:i:s', filemtime(THESIS_USER_SKIN. '/custom.php'))) : 'NO'), "\n\n",
			__('Thesis Filesystem', 'thesis'), "\n",
			"===========================\n",
			"wp-content/thesis: ", (is_dir(WP_CONTENT_DIR. '/thesis') ? 'YES' : 'NO'), "\n",
			"wp-content/thesis/skins: ", (is_dir(WP_CONTENT_DIR. '/thesis/skins') ? 'YES' : 'NO'), "\n",
			"wp-content/thesis/boxes: ", (is_dir(WP_CONTENT_DIR. '/thesis/boxes') ? 'YES' : 'NO'), "\n",
			"wp-content/thesis/master.php: ", (is_file(WP_CONTENT_DIR. '/thesis/master.php') ? 'YES &rarr; '. round(filesize(WP_CONTENT_DIR. '/thesis/master.php') / 1024, 1). 'kB on '. (date('m/d/Y \a\t H:i:s', filemtime(WP_CONTENT_DIR. '/thesis/master.php'))) : 'NO'), "\n\n",
			__('Thesis Skins and Boxes', 'thesis'), "\n",
			"===========================\n",
			sprintf(__('Installed Skins: %s', 'thesis'), esc_attr($this->installed_skins())), "\n",
			sprintf(__('Installed Boxes: %s', 'thesis'), esc_attr($this->installed_boxes())), "\n",
			sprintf(__('Active Boxes: %s', 'thesis'), esc_attr($this->installed_boxes(true))), "\n\n",
			__('WordPress', 'thesis'), "\n",
			"===========================\n",
			sprintf(__('WordPress Version: %s', 'thesis'), esc_attr($wp_version)), "\n",
			sprintf(__('Filesystem Method: %s', 'thesis'), get_filesystem_method()), "\n",
			sprintf(__('Using Multisite: %s', 'thesis'), (is_multisite() ? 'YES' : 'NO')), "\n",
			sprintf(__('Active Plugins: %s', 'thesis'), implode(', ', $plugins)), "\n\n",
			__('PHP', 'thesis'), "\n",
			"===========================\n",
			sprintf(__('Version: %s', 'thesis'), PHP_VERSION), "\n",
			sprintf(__('cURL: %s', 'thesis'), (function_exists('curl_init') ? 'YES' : 'NO')), "\n",
			sprintf(__('Max Upload (according to WP): %s', 'thesis'), size_format(wp_max_upload_size())), "\n",
			sprintf(__('Memory Limit (ini): %s', 'thesis'), esc_attr(ini_get('memory_limit'))), "\n",
			(function_exists('memory_get_usage') ?
				sprintf(__('Memory Limit (usage): %s', 'thesis'), size_format(memory_get_usage(true))) : ''), "\n",
			(defined('WP_MEMORY_LIMIT') ?
				sprintf(__('Memory Limit (WP): %s', 'thesis'), WP_MEMORY_LIMIT) : ''), "\n\n",
			__('Server/Database', 'thesis'), "\n",
			"===========================\n",
			sprintf(__('Site URL: %s', 'thesis'), esc_url(get_site_url())), "\n",
			sprintf(__('Server Software: %s', 'thesis'), esc_attr($_SERVER['SERVER_SOFTWARE'])), "\n",
			sprintf(__('Options Collation: %s', 'thesis'), esc_attr($table_status[0]->Collation)), "\n",
			sprintf(__('MySQL Version: %s', 'thesis'), esc_attr($ver)), "\n",
			sprintf(__('PHP Handler: %s', 'thesis'), esc_attr((function_exists('php_sapi_name') ? php_sapi_name() : 'Unknown'))), "\n\n",
			__('Browser', 'thesis'), "\n",
			"===========================\n",
			sprintf(__('User Agent: %s', 'thesis'), !empty($_SERVER['HTTP_USER_AGENT']) ? esc_attr($_SERVER['HTTP_USER_AGENT']) : __('None reported.', 'thesis')),
			"</textarea>\n",
			"\t\t\t</p>\n",
			"\t\t</div>\n";
	}

	public function installed_skins() {
		global $thesis;
		if (empty($thesis->skins->installed)) return;
		$skins = array();
		foreach($thesis->skins->installed as $installed_skin)
			$skins[] = $installed_skin['name'];
		return implode(', ', $skins);
	}

	public function installed_boxes($active_only = false) {
		global $thesis;
		$user_boxes = thesis_user_boxes::get_items();
		if (empty($user_boxes)) return;
		$active_boxes = is_array($boxes = $thesis->api->get_option('thesis_boxes')) ? $boxes : array();
		$active_box_classes = array_keys($active_boxes);
		$boxes = $active = array();
		foreach ($user_boxes as $installed_boxes) {
			$boxes[] = $installed_boxes['name'];
			if (in_array($installed_boxes['class'], $active_box_classes))
				$active[] = $installed_boxes['name'];
		}
		return implode(', ', !empty($active_only) ? $active : $boxes);
	}

/*
	Script to enable one-click highlighting for easy copy/paste of system status data
*/
	public function js() {
		echo
			"\t\t<script>\n",
			"\t\t\tjQuery(document).ready(function($){\n",
			"\t\t\t\tjQuery('#t_system_status').focus(function(){\n",
			"\t\t\t\t\tvar \$this = jQuery(this);\n",
			"\t\t\t\t\t\$this.select();\n",
			"\t\t\t\t\t\$this.mouseup(function() {\n",
			"\t\t\t\t\t\t\$this.unbind(\"mouseup\");\n",
			"\t\t\t\t\t\treturn false;\n",
			"\t\t\t\t\t});\n",
			"\t\t\t\t});\n",
			"\t\t\t});\n",
			"\t\t</script>\n";
	}
}