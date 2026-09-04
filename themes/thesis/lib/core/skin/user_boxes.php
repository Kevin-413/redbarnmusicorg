<?php
/*
Copyright 2012 DIYthemes, LLC. Patent pending. All rights reserved.
License: DIYthemes Software License Agreement
License URI: https://diythemes.com/thesis/rtfm/software-license-agreement/
*/
class thesis_user_boxes {
	private $boxes = array();		// (array) format: ('box_class' => 'current_folder')
	public $active = array();		// (array) all active user box classes
	public $updates = array();		// (array) box update notifications

	public function __construct() {
		global $thesis;
		$this->boxes = is_array($boxes = $thesis->api->get_option('thesis_boxes')) ? $boxes : $this->boxes;
		$this->active = array_keys($this->boxes);
		$this->include_boxes();
		if ($thesis->environment == 'admin' || $thesis->environment == 'thesis') {
			add_action('wp_loaded', array($this, 'updates'));
			add_filter('thesis_quicklaunch_menu', array($this, 'quicklaunch'), 40);
		}
		if ($thesis->environment == 'admin') {
			new thesis_upload(array(
				'title' => __('Thesis Upload Box', 'thesis'),
				'prefix' => 'thesis_box_uploader',
				'file_type' => 'zip',
				'folder' => 'box'));
			add_action('admin_post_save_boxes', array($this, 'save'));
		}
		if ($thesis->environment == 'thesis')
			add_action('admin_init', array($this, 'admin_init'));
		if ($thesis->environment == 'ajax')
			add_action('wp_ajax_delete_box', array($this, 'delete'));
	}

	public function include_boxes() {
		foreach ($this->boxes as $class => $folder)
			if (file_exists(THESIS_USER_BOXES. "/$folder/box.php")) {
				define(strtoupper($class), THESIS_USER_BOXES. "/$folder");
				define(strtoupper($class). '_URL', THESIS_USER_BOXES_URL. "/$folder");
				include_once(THESIS_USER_BOXES. "/$folder/box.php");
			}
	}

	public function updates() {
		global $thesis;
		$this->updates = !empty($thesis->admin->updates['boxes']) ? $thesis->admin->updates['boxes'] : $this->updates;
	}

	public function quicklaunch($menu) {
		$q['boxes'] = array(
			'text' => __('Manage Boxes', 'thesis'). (!empty($this->updates) ? ' <span class="update-plugins"><span>'. count($this->updates). '</span></span>' : ''),
			'url' => 'admin.php?page=thesis&canvas=boxes');
		return !empty($q) ? (is_array($menu) ? array_merge($menu, $q) : $q) : $menu;
	}

	public function admin_init() {
		if (!empty($_GET['canvas']) && $_GET['canvas'] == 'boxes') {
			wp_enqueue_style('thesis-objects');
			wp_enqueue_script('thesis-objects');
			add_action('thesis_admin_canvas', array($this, 'canvas'));
		}
	}

	public static function get_items() {
		if (!is_dir(THESIS_USER_BOXES))
			return array();
		$boxes = array();
		$path = THESIS_USER_BOXES;
		$directory = scandir($path);
		$default_headers = array(
			'name' => 'Name',
			'class' => 'Class',
			'author' => 'Author',
			'description' => 'Description',
			'version' => 'Version',
			'docs' => 'Docs',
			'changelog' => 'Changelog');
		foreach ($directory as $dir) {
			if (in_array($dir, array('.', '..')) || strpos($dir, '.') === 0 || ! is_dir("$path/$dir") || ! @file_exists("$path/$dir/box.php")) continue;
			$box = get_file_data("$path/$dir/box.php", $default_headers);
			$box['folder'] = $dir;
			$boxes[$box['class']] = $box;
		}
		return $boxes;
	}

	public function canvas() {
		global $thesis;
		$tab = str_repeat("\t", $depth = 2);
		$update_nag = !empty($this->updates) ? ' <span class="t_updates" title="'. __('Updates are available for your Boxes.', 'thesis'). '">'. count($this->updates). '</span>' : '';
		$boxes = $this->get_items();
		$settings = apply_filters('thesis_class_admin_links', array());
		$sort = array();
		$list = '';
		foreach ($boxes as $class => $box) {
			if (!empty($settings[$class]))
				$boxes[$class]['settings'] = $settings[$class];
			$sort[$class] = $box['name'];
		}
		natcasesort($sort);
		foreach ($sort as $class => $name)
			$list .= $this->item_info($boxes[$class], $this->active, $this->updates, $depth);
		echo
			$thesis->api->alert(__('Saving Boxes&hellip;', 'thesis'), 'saving_objects', true, false, $depth),
			(!empty($_GET['saved']) ? $thesis->api->alert(($_GET['saved'] === 'yes' ?
			__('Boxes saved!', 'thesis') :
			__('Boxes not saved. Please try again.', 'thesis')), 'objects_saved', true, false, $depth) : ''),
			"$tab<h3>", __('Thesis Boxes', 'thesis'), "$update_nag",
			(current_user_can('manage_options') ? " <span id=\"object_upload\" data-style=\"button action\" title=\"". __('upload a new box', 'thesis'). "\"><span data-style=\"dashicon\">&#xf317;</span> ". __('Upload a New Box', 'thesis'). "</span>" : ''),
			"</h3>\n",
			"$tab<p class=\"object_primer\">",
			sprintf(__('<strong>Note:</strong> The boxes you select here will be activated and, if applicable, added to the <a href="%1$s">Skin %2$s Editor</a>, where you can add them to your templates. If your box is designed for use in the document <code>&lt;head&gt;</code>, it will be added to the <a href="%3$s">%2$s Head Editor</a>.', 'thesis'), set_url_scheme(home_url('?thesis_editor=1')), $thesis->api->base['html'], admin_url('admin.php?page=thesis&canvas=head')),
			"</p>\n",
			"$tab<form id=\"select_objects\" method=\"post\" action=\"", admin_url('admin-post.php?action=save_boxes'), "\">\n", #wp
			"$tab\t<div class=\"object_list\">\n",
			$list,
			"$tab\t</div>\n",
			"$tab\t", wp_nonce_field('thesis-update-boxes', '_wpnonce-thesis-update-boxes', true, false), "\n",
			"$tab\t<button data-style=\"button save top-right\" id=\"save_objects\" value=\"1\"><span data-style=\"dashicon big squeeze\">&#xf147;</span> ", $thesis->api->efn(__('Save Boxes', 'thesis')), "</button>\n",
			"$tab</form>\n",
			(current_user_can('manage_options') ?
			$thesis->api->popup(array(
				'id' => 'object_uploader',
				'title' => __('Upload a Thesis Box', 'thesis'),
				'body' => $thesis->api->uploader('thesis_box_uploader'))) : '');
	}

	public static function item_info($box, $active = array(), $updates = array(), $depth = 0) {
		global $thesis;
		$tab = str_repeat("\t", $depth);
		$id = esc_attr($box['class']);
		$checked = in_array($box['class'], $active) ? ' checked="checked"' : '';
		$version = '<span class="object_version">v '. (!empty($box['changelog']) ?
			'<a href="'. esc_url($box['changelog']). '" target="_blank" rel="noopener">'. $thesis->api->efh($box['version']). '</a>' :
			$thesis->api->efh($box['version'])). '</span>';
		$author = !empty($box['author']) ?
			" <span class=\"object_by\">". __('by', 'thesis'). "</span> <span class=\"object_author\">". $thesis->api->efh($box['author']). "</span>" : '';
		$settings = !empty($box['settings']) ?
			'<a data-style="button action" href="'. admin_url($box['settings']). '"><span data-style="dashicon">&#xf111;</span> '. __('Settings', 'thesis'). '</a>' : '';
		$docs = !empty($box['docs']) ?
			' <a data-style="dashicon" href="'. esc_url($box['docs']). '" title="'. __('See Documentation', 'thesis'). '" target="_blank" rel="noopener">&#xf348;</a>' : '';
		$update = !empty($updates[$box['class']]) && version_compare($updates[$box['class']]['version'], $box['version'], '>') ?
			"<a onclick=\"if(!thesis_update_boxes()) return false;\" data-style=\"button update\" href=\"". wp_nonce_url(admin_url("update.php?action=thesis_update_objects&type=box&class=$id&name=". urlencode($thesis->api->efh($box['name']))), 'thesis-update-objects'). '"><span data-style="dashicon">&#xf463;</span> '. sprintf(__('Update %s', 'thesis'), $thesis->api->efh($box['name'])). '</a>' : '';
		return
			"$tab\t\t<div id=\"box_$id\" class=\"object". (!empty($checked) ? ' active_object' : ''). "\">\n".
			"$tab\t\t\t<label for=\"$id\">". $thesis->api->efh($box['name']). " $version$author$docs</label>\n".
			"$tab\t\t\t<p class=\"object_description\">". $thesis->api->efa($box['description']). "</p>\n".
			(!empty($update) || !empty($settings) || !empty($docs) ?
			"$tab\t\t\t<p>". (!empty($update) ? $update : ''). (!empty($settings) ? $settings : ''). "</p>\n" :'').
			"$tab\t\t\t<input type=\"checkbox\" class=\"select_object\" id=\"$id\" name=\"boxes[$id]\" value=\"1\"$checked />\n".
			"$tab\t\t\t<button data-style=\"button delete\" class=\"delete_object\" data-type=\"box\" data-class=\"$id\" data-name=\"". $thesis->api->efh($box['name']). "\"><span data-style=\"dashicon\">&#xf153;</span> ". $thesis->api->efn(__('Delete Box', 'thesis')). "</button>\n".
			"$tab\t\t</div>\n";
	}

	public function save() {
		global $thesis;
		$thesis->wp->check();
		$thesis->wp->nonce($_POST['_wpnonce-thesis-update-boxes'], 'thesis-update-boxes');
		$saved = 'no';
		if (is_array($form = $_POST)) {
			$boxes = array();
			$installed = $this->get_items();
			if (!empty($form['boxes']) && is_array($form['boxes']))
				foreach ($form['boxes'] as $class => $on)
					if ($on && is_array($installed[$class]) && !empty($installed[$class]['folder']))
						$boxes[$class] = $installed[$class]['folder'];
			if (empty($boxes))
				delete_option('thesis_boxes'); #wp
			else
				update_option('thesis_boxes', $boxes); #wp
			$saved = 'yes';
		}
		wp_redirect("admin.php?page=thesis&canvas=boxes&saved=$saved");
		exit;
	}

	public function delete() {
		global $thesis;
		$thesis->wp->check();
		if (empty($_POST['class']) || empty($_POST['name'])) return;
		echo $thesis->api->popup(array(
			'id' => 'delete_'. esc_attr($_POST['class']),
			'title' => __('Delete Box', 'thesis'),
			'body' =>
				"<iframe style=\"width:100%; height:100%;\" frameborder=\"0\" src=\"". wp_nonce_url(admin_url("update.php?action=thesis_delete_object&thesis_object_type=box&thesis_object_class=". esc_attr($_POST['class']). "&thesis_object_name=". urlencode($_POST['name'])), 'thesis-delete-object'). "\" id=\"thesis_delete_". esc_attr($_POST['class']). "\"></iframe>\n"));
		if ($thesis->environment == 'ajax') die();
	}
}