<?php
/*
Copyright 2012 DIYthemes, LLC. Patent pending. All rights reserved.
License: DIYthemes Software License Agreement
License URI: https://diythemes.com/thesis/rtfm/software-license-agreement/

TODO: Reorganize thesis_asset_handler into more appropriate classes and instantiate them in a manner consistent with the rest of the program.
TODO: Break off the WP upgrader class extension into its own file
*/
new thesis_asset_handler;
class thesis_asset_handler {
	public function __construct() {
		global $pagenow;
		if ($pagenow == 'update.php') {
			require_once(ABSPATH. 'wp-admin/includes/class-wp-upgrader.php');
			add_action('update-custom_thesis_update_objects', array($this, 'update')); 		// updates
			add_action('update-custom_thesis-install-components', array($this, 'install')); // initial install
			add_action('update-custom_thesis_delete_object', array($this, 'delete')); 		// delete stuff
			add_action('update-custom_thesis_generate_skin', array($this, 'generate_zip'));	// generate .zip
		}
		// Update methods
		if (is_dir(WP_CONTENT_DIR. '/thesis'))
			add_action('thesis_check_updates', array($this, 'get_updates'), 1);
		add_filter('update_theme_complete_actions', array($this, 'admin_update_complete'), 10, 2);
		// Installation
		add_action('after_switch_theme', array($this, 'install_branch'));
		if (defined('THESIS_USER_SKINS') && is_dir(THESIS_USER_SKINS) && !is_dir(WP_CONTENT_DIR. '/thesis/classic-r') && is_dir(THESIS_SKINS. '/classic-r'))
			$this->install_classic_r();
	}

/*---:[ Update methods ]:---*/

	public function transients_check() {
		$transient = get_site_transient('update_themes');
		if (is_object($transient) && property_exists($transient, 'response') && isset($transient->response['thesis']) && property_exists($transient, 'checked') && isset($transient->checked['thesis']) && version_compare($transient->response['thesis']['new_version'], $transient->checked['thesis'], '<=')) {
			$all = array('thesis_callout', 'update_themes', 'thesis_skins_update', 'thesis_boxes_update');
			foreach ($all as $update) {
				delete_transient($update);
				delete_site_transient($update);
			}
			wp_cache_flush();
		}
	}

	public function get_updates() {
		global $thesis;
		$thesis_key = apply_filters('thesis_license_key', false);
		if (empty($thesis_key) || get_transient('thesis_callout'))
			return;
		set_transient('thesis_callout', time(), 60*60*24);
		$objects = array(
			'skins' => thesis_skins::get_items(),
			'boxes' => thesis_user_boxes::get_items());
		$transients = array(
			'skins' => 'thesis_skins_update',
			'boxes' => 'thesis_boxes_update');
		$all = array();
		foreach ($objects as $type => $object)
			if (!empty($object) && is_array($object))
				foreach ($object as $class => $data)
					$all[$type][$class] = $data['version'];
		$all['thesis'] = $thesis->version;
		foreach ($transients as $key => $transient)
			if (get_transient($transient))
				unset($all[$key]);
		if (empty($all))
			return;
		$body = array(
			'data' => $all,
			'wp' => $GLOBALS['wp_version'],
			'php' => phpversion(),
			'thesis_key' => $thesis_key,
			'user-agent' => "WordPress/{$GLOBALS['wp_version']};". home_url());
		$focus_key = trim(get_option('thesis_focus_license_key', false));
		if (!empty($focus_key)) {
			$focus_response = $thesis->api->post($thesis->store, array(
				'edd_action' => 'check_license',
				'license' => $focus_key,
				'item_id' => 148293,
				'item_name' => 'Focus',
				'url' => home_url()));
			if (!is_wp_error($focus_response) && wp_remote_retrieve_response_code($focus_response) === 200) {
				$license_data = json_decode(wp_remote_retrieve_body($focus_response));
				if (!empty($license_data) && isset($license_data->license) && $license_data->license === 'valid')
					$body['thesis_focus'] = $license_data->license;
				elseif (!empty($body['skins']) && !empty($body['skins']['thesis_focus']))
					unset($body['skins']['thesis_focus']);
			}
		}
		$body['data'] = serialize($body['data']);
		$url = apply_filters('thesis_update_url', 'http://thesisapi.com/updates/update.php');
		$post = $thesis->api->post($url, $body);
		if (is_wp_error($post) || empty($post['body']))
			return;
		$returned = @unserialize($post['body']);
		if (!is_array($returned))
			return;
		foreach ($returned as $type => $data) // will only return the data we need to update
			if (in_array("thesis_{$type}_update", $transients))
				set_transient("thesis_{$type}_update", $returned[$type], 60*60*24);
	}

	public function transients_reset() {
		global $thesis;
		$thesis->api->delete_transients();
	}

	public function update() {
		global $thesis;
		if (!current_user_can('manage_options') || !wp_verify_nonce($_REQUEST['_wpnonce'], 'thesis-update-objects'))
			wp_die(__('You are not allowed to update Thesis objects.', 'thesis'));
		require_once(ABSPATH. 'wp-admin/admin-header.php');
		$update = new thesis_update_objects(new thesis_update_objects_skin(array(
			'title' => sprintf(__('Update Thesis %1$s: %2$s', 'thesis'), $thesis->api->efh(ucfirst($_GET['type'])), $thesis->api->efh($_GET['name'])))));
		$update->update();
		$thesis->api->delete_transients();
		if ($_GET['type'] == 'skin' && !empty($_GET['class']))
			$thesis->api->hook('thesis_after_update_skin', array_filter(array(
				'class' => !empty($_GET['class']) ? $_GET['class'] : false,
				'version' => !empty($_GET['version']) ? $_GET['version'] : false,
				'folder' => !empty($_GET['folder']) ? $_GET['folder'] : false)));
		elseif ($_GET['type'] == 'box')
			$thesis->api->hook('thesis_after_update_box');
		include(ABSPATH. 'wp-admin/admin-footer.php');
	}

	public function admin_update_complete($update_actions, $theme) {
		global $thesis;
		$thesis->api->delete_transients();
		return $theme == 'thesis' ?
			'<a class="button button-primary button-large" href="'. wp_nonce_url(admin_url('admin.php?page=thesis'), 'thesis_did_update'). '">'. __('Return to Thesis &rarr;', 'thesis'). '</a>' :
			$update_actions;
	}

/*---:[ Install/delete methods ]:---*/

	public function install_branch() {
		include_once(ABSPATH. '/wp-admin/includes/file.php');
		if (get_filesystem_method() === 'direct' && !is_dir(WP_CONTENT_DIR. '/thesis') && is_dir(THESIS_SKINS)) {
			// first, set up wp_filesystem
			WP_Filesystem();
			$f = $GLOBALS['wp_filesystem'];
			// directories
			$directories = array(
				'thesis/',
				'thesis/boxes/',
				'thesis/skins/');
			foreach ($directories as $dir)
				$f->mkdir($f->wp_content_dir(). $dir);
			// master.php
			$f->put_contents($f->wp_content_dir(). 'thesis/master.php',
				"<?php\n".
				"/*\n".
				"Any hooks or filters you add here will affect your site, regardless of the Skin you’re using.\n".
				"Also, you can use this Thesis master.php file to affect every site on your network.\n".
				"*/");
			// move Skins
			$from = trailingslashit($f->find_folder(THESIS_SKINS));
			$to = $f->wp_content_dir(). 'thesis/skins/';
			$skins = array_keys($f->dirlist($from));
			foreach ($skins as $skin) {
				$f->move($from. $skin, $to. $skin);
				if (!$f->exists($to.$skin.'/images'))
					$f->mkdir($to.$skin.'/images');
				if (!$f->exists($to.$skin.'/custom.php'))
					$f->put_contents($to.$skin.'/custom.php',
						"<?php\n".
						"/*\n".
						"This file is for Skin-specific customizations. Do not change your Skin’s skin.php file,\n".
						"as that will be upgraded in the future and your work will be lost.\n\n".
						"If you are comfortable with PHP, you can make more powerful customizations by using the\n".
						"Thesis Box system to create elements you can interact with in the Thesis HTML Editor.\n\n".
						"For more information, please visit: https://diythemes.com/thesis/rtfm/api/box/\n".
						"*/");
			}
			// clean up
			if (($lib = array_keys($f->dirlist(THESIS_SKINS))) && empty($lib))
				$f->delete(THESIS_SKINS);
		}
	}

	public function install() {
		global $thesis;
		if (!current_user_can('manage_options') || !wp_verify_nonce($_REQUEST['_wpnonce'], 'thesis-install'))
			wp_die(__('You are not allowed to install Thesis.', 'thesis'));
		require_once(ABSPATH. 'wp-admin/admin-header.php');
		$install = new thesis_install(new WP_Upgrader_Skin(array(
			'title' => __('Install Thesis', 'thesis'))));
		$install->run();
		$thesis->api->delete_transients();
		include(ABSPATH. 'wp-admin/admin-footer.php');
	}

	public function delete() {
		global $thesis;
		if (!current_user_can('manage_options'))
			wp_die(__('You are not allowed to delete Thesis or its components.', 'thesis'));
		check_admin_referer('thesis-delete-object');
		if (empty($_GET['thesis_object_class']) || empty($_GET['thesis_object_name']) || empty($_GET['thesis_object_type']) || !in_array($_GET['thesis_object_type'], array('skin', 'box', 'package')))
			wp_die(__('The passed object data is either incorrect or incomplete.', 'thesis'));
		add_action('admin_head', array('thesis_upload', 'admin_css'));
		require_once(ABSPATH. 'wp-admin/admin-header.php');
		$delete = new thesis_delete(new thesis_delete_skin(array(
			'title' => sprintf(__('Delete Thesis %1$s: %2$s', 'thesis'), ucfirst(esc_attr($_GET['thesis_object_type'])), ucfirst(esc_attr($_GET['thesis_object_name']))))));
		$delete->delete_object($_GET['thesis_object_type'], $_GET['thesis_object_class']);
		$thesis->api->delete_transients();
		include(ABSPATH. 'wp-admin/admin-footer.php');
	}

	public function install_classic_r() {
		include_once(ABSPATH. '/wp-admin/includes/file.php');
		if (get_filesystem_method() === 'direct') {
			if (empty($GLOBALS['wp_filesystem']) || !is_object($GLOBALS['wp_filesystem']))
				WP_Filesystem();
			$f = $GLOBALS['wp_filesystem'];
			if (!$f->move(THESIS_SKINS. '/classic-r', THESIS_USER_SKINS. '/classic-r'))
				return;
		}
	}

	public function generate_zip() {
		if (!current_user_can('manage_options'))
			wp_die(__('You are not allowed to create .zip files.', 'thesis'));
		check_admin_referer('thesis-generate-skin');
		if (empty($_GET['skin']))
			wp_die(__('The Skin class was passed as empty.', 'thesis'));
		require_once(ABSPATH. 'wp-admin/admin-header.php');
		$generate = new thesis_generate(new thesis_generate_skin(array(
			'title' => __('Create Skin .zip File', 'thesis'))));
		$generate->generate();
		include(ABSPATH. 'wp-admin/admin-footer.php');
	}
}
/*
For skins/pkgs/boxes:
- handle an upload (either through the interface or when presented a URL)
- handle an upgrade via URL ONLY
- handle bulk upgrade? Maybe, but not for now?
- handle deletion? Not sure if I like this idea, but it would be consistent with the WP experience.
*/
if (class_exists('WP_Upgrader')):
	class thesis_update_objects extends WP_Upgrader {
		public $updates;
		public $object_type;
		public $path_to_download;

/*
		These strings are used throughout the process by various parts of the routine.
		We must provide relevant status messages as WordPress does not.
*/
		function upgrade_strings() {
			$strings = array(
				'up_to_date' => sprintf(__('Congrats! Your %s is up to date.', 'thesis'), ($type = esc_html(ucfirst($_GET['type'])))),
				'no_package' => sprintf(__('The %s update is not currently available.', 'thesis'), $type),
				'downloading_package' => sprintf(__('Fetching the updated %s&#8230;', 'thesis'), $type),
				'unpack_package' => sprintf(__('Decompressing the downloaded %s&#8230;', 'thesis'), $type),
				'remove_old'=> sprintf(__('Deleting the old %s&#8230;', 'thesis'), $type),
				'remove_old_failed' => sprintf(__('We couldn&#8217;t remove the old %s&#8230;', 'thesis'), $type),
				'process_failed' => sprintf(__('The %s update failed.', 'thesis'), $type),
				'process_success' => sprintf(__('Yes! The %s update was successful!', 'thesis'), $type));
			$this->strings = $strings + $this->strings;
		}

		public function update() {
			global $wp_filesystem;
			$this->plural_name = esc_html($_GET['type']). ($_GET['type'] == 'skin' ? 's' : 'es');
			$this->init();						// initialize wp_upgrader
			$this->upgrade_strings();			// strings
			$this->skin->header();				// call Skin header
			@$this->fs_connect();				// do fs biznass
			if (is_object($wp_filesystem)) {
				$this->do_updates();
				$this->skin->footer();			// WP update screen "footer"
			}
		}

		public function do_updates() {
			$types = array('box', 'skin');	// valid types
			if (empty($_GET['type']) || !in_array($_GET['type'], $types))
				$this->skin->feedback(__('Invalid update type was passed.', 'thesis'));
			else {
				$dirs = array(
					'box' => THESIS_USER_BOXES,
					'skin' => THESIS_USER_SKINS);
				$class = !empty($_GET['class']) ? esc_attr($_GET['class']) : false;
				$this->all_items = $_GET['type'] == 'skin' ?
					thesis_skins::get_items() : ($_GET['type'] == 'box' ?
					thesis_user_boxes::get_items() : false);
				$this->updates = get_transient("thesis_{$this->plural_name}_update"); // current updates in the db
				add_filter('upgrader_source_selection', array($this, 'prepare_object'));
				if (empty($this->all_items[$class])) return;
				$this->raw_path = $dirs[$_GET['type']]. (!empty($this->all_items[$class]['folder']) ? "/{$this->all_items[$class]['folder']}" : '');
				if ($this->raw_path === $dirs[$_GET['type']]) return; // bail if no folder given
				$run = array(
					'package' => $this->updates[$class]['url'], // this will ONLY be a URL
					'destination' => $this->raw_path, // will be deleted then remade
					'clear_destination' => true, // should fire AFTER we have processed the Skin with upgrader_source_selection
					'clear_working' => true,
					'is_multi' => true);
				$this->current_class = $class;
				$this->run($run);
				remove_filter('upgrader_source_selection', array($this, 'prepare_object'));
			}
		}

/*
		Check that a box/skin.php file exists.
		Check that the reported classes are the same.
*/
		public function prepare_object($source) {
			global $thesis, $wp_filesystem;
			$this->path_to_download = untrailingslashit($source);	// $source is the downloaded folder
			if (!file_exists("{$this->path_to_download}/{$_GET['type']}.php"))
				return new WP_Error('no_php', sprintf(__('There is no %s.php file.', 'thesis'), $_GET['type']));
			$this->file_data = get_file_data("{$this->path_to_download}/{$_GET['type']}.php", array(
				'name' => 'Name',
				'class' => 'Class',
				'author' => 'Author',
				'description' => 'Description',
				'version' => 'Version',
				'requires' => 'Requires',
				'docs' => 'Docs',
				'changelog' => 'Changelog'));
			if (!$this->file_data['class'] == $this->current_class)
				return new WP_Error('class_not_same', sprintf(__('The class for your %s does not match the class of the update.', 'thesis'), $_GET['type']));
			if (!empty($this->file_data['requires']) && version_compare($this->file_data['requires'], $thesis->version, '>'))
				return new WP_Error('thesis_version', sprintf(__('<strong>You need Thesis version %s or higher to run this update.</strong>', 'thesis'), $this->file_data['requires']));
			if ($_GET['type'] == 'skin')
				$this->prepare_skin();
			// allow objects to override this or add things
			if (file_exists($this->path_to_download. '/upgrade.php'))
				include_once($this->path_to_download. '/upgrade.php');
			$this->complete_success = apply_filters('thesis_install_object', true, $this->path_to_download, $this->raw_path, $wp_filesystem);
			return $this->complete_success === true ? trailingslashit($source) : false;
		}

		public function prepare_skin() {
			global $wp_filesystem;
			$source = $wp_filesystem->find_folder($this->path_to_download);
			$skin = $wp_filesystem->find_folder($this->raw_path);
			// if both have an images folder
			if ($wp_filesystem->is_dir("$source/images") && $wp_filesystem->is_dir("$skin/images")) {
				// 1. get images from download and Skin
				$images = $wp_filesystem->dirlist("$source/images"); // images in download
				$installed_images = $wp_filesystem->dirlist("$skin/images"); // images currently in skin/images
				// 2. see what the installed Skin has that the download doesn't
				$images_to_move = array_diff_key($installed_images, $images);
				// 3. move the installed Skin images that aren't present in the download to the download
				foreach ($images_to_move as $image_name => $image_data)
					// move the images from the install to the download
					if (!$wp_filesystem->move("$skin/images/$image_name", "$source/images/$image_name"))
						$this->skin->feedback(sprintf(__('Could not move %s.', 'thesis'), $image_name));
			}
			// if there is not an images folder, attempt to make one
			elseif ($wp_filesystem->is_dir("$source/images") && !$wp_filesystem->mkdir("$source/images"))
				$this->skin->feedback(__('Could not make /images folder. (This is probably a permissions issue.)', 'thesis'));
			// if the existing Skin has an images folder but the update does not, move the existing images folder wholesale
			elseif ($wp_filesystem->is_dir("$skin/images") && !$wp_filesystem->is_dir("$source/images"))
				if (!$wp_filesystem->move("$skin/images", "$source/images"))
					$this->skin->feedback(__('Could not move /images folder.', 'thesis'));
			if ($wp_filesystem->exists("$skin/css.css"))
				if (!$wp_filesystem->move("$skin/css.css", "$source/css.css", true))
					$this->skin->feedback(__('Could not move CSS file. <strong>You will need to save your CSS options in the Thesis Skin Editor.</strong>.', 'thesis'));
			// if the Skin was shipped with a custom file, and we have an installed one, delete the shipped
			if ($wp_filesystem->exists("$source/custom.php") && $wp_filesystem->exists("$skin/custom.php"))
				if (!$wp_filesystem->delete("$source/custom.php", true)) {
					$this->skin->feedback(__('Could not delete custom.php', 'thesis'));
					die();
				}
			// attempt to move the custom.php file
			if ($wp_filesystem->exists("$skin/custom.php")) {
				if (!$wp_filesystem->move("$skin/custom.php", "$source/custom.php")) {
					$this->skin->feedback(__('Could not move custom.php', 'thesis'));
					die();
				}
			}
			else
				$wp_filesystem->put_contents("$source/custom.php",
					"<?php\n".
					"/*\n".
					"This file is for Skin-specific customizations. Do not change your Skin’s skin.php file,\n".
					"as that will be upgraded in the future and your work will be lost.\n\n".
					"If you are comfortable with PHP, you can make more powerful customizations by using the\n".
					"Thesis Box system to create elements you can interact with in the Thesis HTML Editor.\n\n".
					"For more information, please visit: https://diythemes.com/thesis/rtfm/api/box/\n".
					"*/");
		}
	}

	class thesis_update_objects_skin extends WP_Upgrader_Skin {
		public function footer() {
			$canvas = $this->upgrader->plural_name == 'skins' ? 'select_skin' : $this->upgrader->plural_name;
			echo
				"<p><a class=\"button button-primary button-large\" href=\"", wp_nonce_url(admin_url("admin.php?page=thesis&canvas={$canvas}&t_write_css=true"), 'thesis_did_update'), "\">", __('Return to Thesis &rarr;', 'thesis'), "</a></p>\n",
				"</div>\n";
		}
	}

	class thesis_install extends WP_Upgrader {
		public function run($args = array()) {
			global $wp_filesystem;
			$this->init();
			$this->skin->header();
			@$this->fs_connect();
			if (is_object($wp_filesystem)) {
				$this->skin->feedback('Beginning installation&hellip;');
				if (!$this->directories() || !$this->move_skins() || !$this->make_master()) {
					$fail = true;
					$this->skin->feedback('Installation failed.');
				}
				$this->clean_skins();
				if (empty($fail))
					$this->skin->feedback('Installation was successful!');
				$this->skin->feedback("<a class=\"button button-primary button-large\" href=\"". admin_url("admin.php?page=thesis"). "\">". __('Return to Thesis &rarr;', 'thesis'). "</a>");
			}
			$this->skin->footer();
		}

		public function make_master() {
			global $wp_filesystem;
			$file = trailingslashit($wp_filesystem->wp_content_dir()). 'thesis/master.php';
			if (!$wp_filesystem->exists($file) && ! $wp_filesystem->put_contents($file,
				"<?php\n".
				"/*\n".
				"Any hooks or filters you add here will affect your site, regardless of the Skin you’re using.\n".
				"Also, you can use this Thesis master.php file to affect every site on your network.\n".
				"*/"))
				return false;
			else
				return true;
		}

		public function clean_skins() {
			global $wp_filesystem;
			$s = $wp_filesystem->find_folder(THESIS_SKINS);
			$d = $wp_filesystem->dirlist($s);
			if ($wp_filesystem->is_dir($s) && empty($d))
				$wp_filesystem->delete($s);
		}

		public function directories() {
			global $wp_filesystem;
			$directories = array(
				'thesis/',
				'thesis/boxes/',
				'thesis/skins/');
			$this->skin->feedback('Making primary folder structure.');
			foreach ($directories as $d) {
				$location = $wp_filesystem->wp_content_dir(). "$d";
				$this->skin->feedback("Making wp-content/$d");
				if (!$wp_filesystem->mkdir($location)){
					$this->skin->feedback("Unable to make wp-content/$d");
					$this->skin->feedback('Installation halted. Please check the file permissions for the /wp-content directory.');
					return false;
				}
			}
			return true;
		}

		public function move_skins() {
			global $wp_filesystem;
			$wp_skins = untrailingslashit($wp_filesystem->wp_content_dir()). '/thesis/skins';
			if (!$wp_filesystem->is_dir($wp_skins))
				return false;
			$lib_skins = untrailingslashit($wp_filesystem->find_folder(THESIS_SKINS));
			$lib_skins_content = $wp_filesystem->dirlist($lib_skins);

			$this->skin->feedback('Preparing default Skins to move.');
			foreach ($lib_skins_content as $skin => $data) {
				if (!$wp_filesystem->exists("$lib_skins/$skin/custom.php"))
					if (!$wp_filesystem->put_contents("$lib_skins/$skin/custom.php",
						"<?php\n".
						"/*\n".
						"This file is for Skin-specific customizations. Do not change your Skin’s skin.php file,\n".
						"as that will be upgraded in the future and your work will be lost.\n\n".
						"If you are comfortable with PHP, you can make more powerful customizations by using the\n".
						"Thesis Box system to create elements you can interact with in the Thesis HTML Editor.\n\n".
						"For more information, please visit: https://diythemes.com/thesis/rtfm/api/box/\n".
						"*/")) {
						$this->skin->feedback('Could not create custom.php file. Please check your folder and file permissions.');
						return false;
					}
				if (!$wp_filesystem->exists("$lib_skins/$skin/images"))
					if (!$wp_filesystem->mkdir("$lib_skins/$skin/images")) {
						$this->skin->feedback('Unable to make /images folder. Please check file permissions.');
						return false;
					}
				$this->skin->feedback("Moving $skin.");
				if (!$wp_filesystem->move("$lib_skins/$skin", "$wp_skins/$skin")) {
					$this->skin->feedback("Unable to move $skin");
					return false;
				}
				$this->skin->feedback("$skin successfully installed.");
			}
			$this->skin->feedback('Default Skins installed successfully.');
			return true;
		}
	}

	class thesis_delete extends WP_Upgrader {
		public $done = false;

		public function delete_object($type, $class) {
			global $wp_filesystem;
			$this->init();
			if (!in_array($type, array('box', 'skin', 'package')))
				return new WP_Error('wrong_type', __('Object type not recognized.', 'thesis'));
			if (empty($class))
				wp_die(__('Object class not passed.', 'thesis'));
			$this->thesis_type = $type;
			$this->thesis_class = $class;
			$items = false;
			$delete = false;
			if ($type == 'skin') {
				$items = thesis_skins::get_items();
				$delete = THESIS_USER_SKINS;
			}
			elseif ($type == 'package') {
				$items = thesis_user_packages::get_items();
				$delete = THESIS_USER_PACKAGES;
			}
			elseif ($type == 'box') {
				$items = thesis_user_boxes::get_items();
				$delete = THESIS_USER_BOXES;
			}
			$delete = !empty($items[$class]['folder']) ? $delete. "/{$items[$class]['folder']}" : false;
			$this->skin->header();
			@$this->fs_connect();
			if (is_object($wp_filesystem) && $delete) {
				$delete = $wp_filesystem->find_folder($delete);
				if (! !!$items || ! !!$delete)
					$this->skin->feedback(__('Could not find the requested object.', 'thesis'));
				elseif (!$wp_filesystem->delete($delete, true))
					$this->skin->feedback('Could not delete '. esc_attr($items[$class]['name']));
				else {
					$this->skin->feedback(esc_attr($items[$class]['name']). ' has been deleted.');
					$this->done = true;
				}
				$this->skin->footer($type);
			}
			elseif (empty($delete))
				$this->skin->feedback(sprintf(__('The %s you are trying to delete could not be located.', 'thesis'), ucfirst($type)));
		}
	}

	class thesis_delete_skin extends WP_Upgrader_Skin {
		public function footer($type = '') {
			if ($this->upgrader->done === true) {
				$canvas = $type == 'skin' ? 'skins' : ($type == 'box' ? 'boxes' : ($type == 'package' ? 'packages' : ''));
				$options = $canvas !== 'skins' ? get_option("thesis_$canvas") : array();
				if (isset($options[$this->upgrader->thesis_class])) {
					unset($options[$this->upgrader->thesis_class]);
					update_option("thesis_$canvas", $options);
					wp_cache_flush();
				}
				echo "<script>parent.jQuery('#", esc_attr($this->upgrader->thesis_type), "_", esc_attr($this->upgrader->thesis_class), "').remove();</script>";
			}
			echo '</div>';
		}
	}

	class thesis_generate extends WP_Upgrader {
		public $zip_url = false;
		public $class = false;
		public $skin_data = array();

		public function generate() {
			global $wp_filesystem;
			$this->init();
			$this->skin->header();
			// error suppression is necessary here until WordPress fixes its insane 0 index check
			@$this->fs_connect();
			if (is_object($wp_filesystem)) {
				$this->begin();
				$this->skin->footer();
			}
		}

		private function begin() {
			if (!empty($_GET['skin']))
				$this->class = urldecode($_GET['skin']);
			else
				wp_die(__('The Skin class was not found.', 'thesis'));
			$this->setup_skin_data();		// fills skin data with info
			$seed = $this->create_seed();	// returns true if seed file created
			if (!! $seed)
				$this->start_zip();
			else
				wp_die(__('Unable to create options file.', 'thesis'));
		}

		private function setup_skin_data() {
			$skins = thesis_skins::get_items();	// skin list and check if class exists
			if (!isset($skins[$this->class]) || empty($skins[$this->class]['folder']))
				wp_die(__('Could not find the specified Skin.', 'thesis'));
			else
				$this->skin->feedback(__('Setting up Skin.', 'thesis'));
			$this->skin_data = $skins[$this->class];
		}

		private function create_seed() {
			global $wp_filesystem;
			$options = array();
			$entries = array(
				'boxes',
				'templates',
				'css',
				'css_editor',
				'vars',
				'packages',
				'_design',
				'_display');
			$skin_dir = trailingslashit($wp_filesystem->find_folder(THESIS_USER_SKINS. '/'. $this->skin_data['folder']));
			if (! !!$skin_dir)
				wp_die(__('Skin not found in filesystem.', 'thesis'));
			foreach ($entries as $entry)
				if ($option = get_option($this->class. '_'. $entry))
					$options[$entry] = $option;
			$code =
				"<?php\n\n".
				"function ". $this->class. "_defaults() {\n".
				"\treturn ". var_export($options, true). ";\n".
				"}\n";
			if (!$wp_filesystem->put_contents($skin_dir. 'default.php', $code))
				wp_die(__('Defaults file not created.', 'thesis'));
			$this->skin->feedback(__('Defaults file created.', 'thesis'));
			return true;
		}

		private function start_zip() {
			if (file_exists(ABSPATH. 'wp-admin/includes/class-pclzip.php'))
				require_once(ABSPATH. 'wp-admin/includes/class-pclzip.php');
			if (!class_exists('PclZip'))
				wp_die(__('Unable to load the PclZip class that is normally packaged with WordPress. Please contact your server administrator.', 'thesis'));
			$this->skin->feedback(sprintf(__('Creating zip file in %s', 'thesis'), THESIS_USER_SKINS));
			$zip_name = THESIS_USER_SKINS. '/'. $this->skin_data['folder']. '.zip';
			$a = new PclZip($zip_name);
			$add = $a->create(THESIS_USER_SKINS. '/'. $this->skin_data['folder'], PCLZIP_OPT_REMOVE_PATH, THESIS_USER_SKINS);
			if ($add === 0)
				wp_die(sprintf(__('Unspecified error encountered while creating .zip file. Please contact your server administrator and reference %s', 'thesis'), 'wp-admin/includes/class-pclzip.php'));
			else
				$this->skin->feedback(sprintf(__('Successfully created .zip file for %s.', 'thesis'), esc_attr($this->skin_data['name'])));
			$this->zip_url = THESIS_USER_SKINS_URL. '/'. basename($zip_name);
		}
	}

	class thesis_generate_skin extends WP_Upgrader_Skin {
		public function footer() {
			if ($this->upgrader->zip_url !== false)
				echo
					"<p><a class=\"button button-primary button-large\" href=\"", esc_url($this->upgrader->zip_url), "\">", __('Download .zip file.', 'thesis'), "</a></p>\n";
			echo
				"<p><a href=\"". admin_url('admin.php?page=thesis&canvas=select_skin'). "\">". __('Return to Thesis &rarr;', 'thesis'). "</a></p>\n".
				"</div>";
		}
	}
endif;