<?php
/*
Copyright 2012 DIYthemes, LLC. Patent pending. All rights reserved.
License: DIYthemes Software License Agreement
License URI: https://diythemes.com/thesis/rtfm/software-license-agreement/
*/
final class thesis_skin_manager {
	public $class = false;								// Skin class
	public $name = false;								// Skin name
	public $table_suffix = 'thesis_backups';			// Suffix to identify the Thesis Backups table
	public $table = false;								// Table name = WPDB prefix + $table_suffix
	public $options = array(
		'boxes',
		'templates',
		'css',
		'css_editor',
		'css_custom',
		'vars',
		'packages',
		'_design',
		'_display');

	public function __construct($skin = array()) {
		global $wpdb, $thesis;
		// allow in all environments except the front end
		if (empty($skin) || !$thesis->wp_customize && ($thesis->environment === false || !is_array($skin))) return;
		extract($skin); // $class, $name
		$this->class = !empty($class) ? trim($this->verify_class_name($class)) : false;
		$this->name = isset($name) ? $name : false;
		$this->table = $wpdb->prefix. $this->table_suffix;
		if (!get_option("{$this->class}_templates"))
			$this->defaults($thesis->skin->_skin, 'new');
		// TODO: The Exporter could probably be run through ajax like everything else...
		if ($thesis->environment == 'admin')
			add_action('admin_post_export_skin', array($this, 'export'));
		if ($thesis->environment == 'ajax') {
			add_action('wp_ajax_backup_skin', array($this, 'backup'));
			add_action('wp_ajax_update_backup_skin_table', array($this, 'update_backup'));
			add_action('wp_ajax_restore_skin_backup', array($this, 'restore_backup'));
			add_action('wp_ajax_delete_skin_backup', array($this, 'delete_backup'));
			add_action('wp_ajax_restore_skin_default', array($this, 'restore_default'));
		}
	}

/*---:[ Skin Manager interface ]:---*/

	public function editor() {
		global $thesis;
		$tab = str_repeat("\t", $depth = 2);
		$export = $thesis->api->form->fields(array(
			'export' => array(
				'type' => 'checkbox',
				'label' => __('Export the Following Skin Data:', 'thesis'),
				'tooltip' => __('Share your masterpiece, move your design, or get help from an expert by exporting your Skin in whole or in part. Choose the options you want to share, and Thesis will create a handy export file for you.', 'thesis'),
				'options' => array(
					'boxes' => __('Boxes', 'thesis'),
					'templates' => __('Templates', 'thesis'),
					'css' => sprintf(__('Skin %s', 'thesis'), $thesis->api->base['css']),
					'css_editor' => sprintf(__('Editor %s', 'thesis'), $thesis->api->base['css']),
					'css_custom' => sprintf(__('Custom %s', 'thesis'), $thesis->api->base['css']),
					'vars' => sprintf(__('%s Variables', 'thesis'), $thesis->api->base['css']),
					'_design' => __('Design Options', 'thesis'),
					'_display' => __('Display Options', 'thesis'),
					'packages' => sprintf(__('%s Packages (deprecated)', 'thesis'), $thesis->api->base['css'])),
				'default' => array(
					'boxes' => true,
					'templates' => true,
					'css' => true,
					'css_editor' => true,
					'css_custom' => true,
					'vars' => true,
					'_design' => true,
					'_display' => true,
					'packages' => true))), array(), 'thesis_export_', '', 900, 6);
		$default = $thesis->api->form->fields(array(
			'restore' => array(
				'type' => 'checkbox',
				'label' => __('Restore Default Settings:', 'thesis'),
				'tooltip' => __('Thesis allows you to restore individual parts of your Skin or the whole shebang.', 'thesis'),
				'options' => array(
					'boxes' => __('Boxes', 'thesis'),
					'templates' => __('Templates', 'thesis'),
					'css' => sprintf(__('Skin %s', 'thesis'), $thesis->api->base['css']),
					'css_editor' => sprintf(__('Editor %s', 'thesis'), $thesis->api->base['css']),
					'css_custom' => sprintf(__('Custom %1$s (this will delete your custom %1$s)', 'thesis'), $thesis->api->base['css']),
					'vars' => sprintf(__('%s Variables', 'thesis'), $thesis->api->base['css']),
					'_design' => __('Design Options', 'thesis'),
					'_display' => __('Display Options', 'thesis'),
					'packages' => sprintf(__('%s Packages (deprecated)', 'thesis'), $thesis->api->base['css'])),
				'default' => array(
					'boxes' => true,
					'templates' => true,
					'css' => true,
					'css_editor' => true,
					'css_custom' => false,
					'vars' => true,
					'_design' => true,
					'_display' => true,
					'packages' => true))), array(), 'thesis_export_', '', 900, 6);
		return
			"$tab<h3 id=\"t_manager_head\"><span>". sprintf(__('Manage %s Skin Data', 'thesis'), $this->name). "</span></h3>\n".
			"$tab<div class=\"t_manager_box\" data-style=\"box\">\n".
			"$tab\t<h4>". __('Backup Skin Data', 'thesis'). "</h4>\n".
			"$tab\t<p>". __('Create a Skin backup that you can restore at any time.', 'thesis'). "</p>\n".
			"$tab\t<button id=\"t_backup\" data-style=\"button save\">". $thesis->api->efn(__('Create New Backup', 'thesis')). "</button>\n".
			"$tab</div>\n".
			(current_user_can('manage_options') ?
			"$tab<div class=\"t_manager_box\" data-style=\"box\">\n".
			"$tab\t<h4>". __('Import Skin Data', 'thesis'). "</h4>\n".
			"$tab\t<p>". __('Import Skin data from a Thesis Skin export file.', 'thesis'). "</p>\n".
			"$tab\t<button id=\"t_import\" data-style=\"button action\">". $thesis->api->efn(__('Import Skin Data', 'thesis')). "</button>\n".
			"$tab</div>\n" : '').
			"$tab<div class=\"t_manager_box t_manager_default\" data-style=\"box\">\n".
			"$tab\t<h4>". __('Restore Default Data', 'thesis'). "</h4>\n".
			"$tab\t<p>". sprintf(__('Restore default data for the %s Skin.', 'thesis'), $this->name). "</p>\n".
			"$tab\t<button id=\"t_select_defaults\" data-style=\"button action\">". $thesis->api->efn(__('Restore Defaults', 'thesis')). "</button>\n".
			"$tab</div>\n".
			"$tab<div id=\"t_restore\">\n".
			"$tab\t<h3 id=\"t_restore_head\"><span>". $thesis->api->efn(sprintf(__('%s Skin Backups', 'thesis'), $this->name)). "</span></h3>\n".
			"$tab\t<div id=\"t_restore_table\">\n".
			$this->backup_table().
			"$tab\t</div>\n".
			"$tab</div>\n".
			$thesis->api->popup(array(
				'id' => 'export_skin',
				'title' => sprintf(__('Export %s Data', 'thesis'), $this->name),
				'depth' => $depth,
				'body' =>
					"$tab\t\t\t<form id=\"t_export_form\" method=\"post\" action=\"". (admin_url('admin-post.php?action=export_skin')). "\">\n".
					$export['output'].
					"$tab\t\t\t\t<input type=\"hidden\" id=\"t_export_id\" name=\"export[id]\" value=\"\" />\n".
					"$tab\t\t\t\t<button id=\"t_export\" data-style=\"button action\">". $thesis->api->efn(__('Export Skin Data', 'thesis')). "</button>\n".
					"$tab\t\t\t\t". wp_nonce_field('thesis-skin-export', '_wpnonce-thesis-skin-export', true, false). "\n".
					"$tab\t\t\t</form>\n")).
			(current_user_can('manage_options') ?
			$thesis->api->popup(array(
				'id' => 'import_skin',
				'title' => sprintf(__('Import %s Data', 'thesis'), $this->name),
				'depth' => $depth,
				'body' => $thesis->api->uploader('import_skin'))) : '').
			$thesis->api->popup(array(
				'id' => 'skin_default',
				'title' => sprintf(__('Restore %s Skin Defaults', 'thesis'), $this->name),
				'depth' => $depth,
				'body' => 
					"$tab\t\t\t<form id=\"t_default_form\" method=\"post\" action=\"\">\n".
					(file_exists(THESIS_USER_SKIN. '/default.php') ?
					$default['output'].
					"$tab\t\t\t\t<button id=\"t_restore_default\" data-style=\"button save\">". $thesis->api->efn(__('Restore Selected Defaults', 'thesis')). "</button>\n" :
					"$tab\t\t\t\t<p>". __('Your Skin does not have the ability to restore individual data components. Hit the button below to restore <strong>all</strong> default settings.', 'thesis'). "</p>\n".
					"$tab\t\t\t\t<button id=\"t_restore_default\" data-style=\"button save\">". $thesis->api->efn(__('Restore Defaults', 'thesis')). "</button>\n").
					"$tab\t\t\t\t". wp_nonce_field('thesis-restore-defaults', '_wpnonce-thesis-restore-defaults', true, false). "\n".
					"$tab\t\t\t</form>\n")).
			"$tab". wp_nonce_field('thesis-skin-manager', '_wpnonce-thesis-skin-manager', true, false). "\n";
	}

	public function backup_table() {
		global $thesis;
		if (!$this->table()) return '';
		$backups = '';
		foreach ((is_array($points = $this->get()) ? $points : array()) as $id => $backup) {
			$td = '';
			if (is_array($backup))
				foreach ($backup as $prop => $val) {
					$class = $prop == 'notes' ? ' class="t_backup_notes"' : '';
					$value = $prop == 'time' ? date('n/j/y H:i', $val) : ($prop == 'notes' ? trim($thesis->api->ef0($val)) : false);
					$td .= "\t\t\t\t\t\t<td$class>$value</td>\n";
				}
			$backups .=
				"\t\t\t\t\t<tr>\n".
				$td.
				"\t\t\t\t\t\t<td><button class=\"t_restore_backup\" data-style=\"button save\" data-id=\"$id\">". $thesis->api->efn(__('Restore', 'thesis')). "</button></td>\n".
				"\t\t\t\t\t\t<td><button class=\"t_export_backup\" data-style=\"button action\" data-id=\"$id\">". $thesis->api->efn(__('Export', 'thesis')). "</button></td>\n".
				"\t\t\t\t\t\t<td><button class=\"t_delete_backup\" data-style=\"button delete\" data-id=\"$id\">". $thesis->api->efn(__('Delete', 'thesis')). "</button></td>\n".
				"\t\t\t\t\t</tr>\n";
		}
		return
			"\t\t\t<table>\n".
			"\t\t\t\t<thead>\n".
			"\t\t\t\t\t<tr>\n".
			"\t\t\t\t\t\t<th>". __('Backup Date', 'thesis'). "</th>\n".
			"\t\t\t\t\t\t<th class=\"t_backup_notes\">". __('Notes', 'thesis'). "</th>\n".
			"\t\t\t\t\t\t<th>". __('Restore', 'thesis'). "</th>\n".
			"\t\t\t\t\t\t<th>". __('Export', 'thesis'). "</th>\n".
			"\t\t\t\t\t\t<th>". __('Delete', 'thesis'). "</th>\n".
			"\t\t\t\t\t</tr>\n".
			"\t\t\t\t</thead>\n".
			"\t\t\t\t<tbody>\n".
			$backups.
			"\t\t\t\t</tbody>\n".
			"\t\t\t</table>\n";
	}

/*---:[ Skin Manager core database actions ]:---*/

	public function add($notes = false, $skin = false) {
		global $wpdb;
		$data = array(); 												// start
		$skin = !empty($skin) ? $skin : $this->class;
		wp_cache_flush(); 												// make sure we have the latest by flushing the cache first
		foreach ($this->options as $option)
			$data[$option] = get_option("{$skin}_{$option}");			// fetch options
		$data = array_filter($data); 									// filter out empty options
		if (empty($data))
			return true;												// there are no options, so we don't need to save anything.
		if (!empty($notes)) 											// if we got to here, add notes, only if they're present
			$data['notes'] = $notes;
		$data = array_map('maybe_serialize', $data); 					// returns an array of serialized data
		$data['time'] = current_time('timestamp'); 						// add timestamp
		$data['class'] = $skin;											// add Skin class
		return (bool) $wpdb->insert($this->table, $data); 				// return true on success, false on failure
	}

	public function delete($id = false) {
		global $wpdb;
		if ($id === false || !is_integer($id) || !($check = $this->get_entry(abs($id))))
			return false;
		$where = array(
			'class' => esc_attr($this->class),
			'ID' => absint($id));
		return (bool) $wpdb->delete($this->table, $where);
	}

	public function get() {
		global $wpdb;
		$sql = $wpdb->prepare("SELECT ID,time,notes FROM {$this->table} WHERE class = %s", $this->class);
		if (!is_array($results = $wpdb->get_results($sql, ARRAY_A)))
			return false;
		$valid = array();
		foreach ($results as $result)
			if (is_array($result) && !empty($result['ID']) && !empty($result['time']))
				$valid[absint(maybe_unserialize($result['ID']))] = array(
					'time' => absint(maybe_unserialize($result['time'])),
					'notes' => !empty($result['notes']) ? maybe_unserialize($result['notes']) : false);
		krsort($valid);
		return empty($valid) ? array() : $valid;
	}

	public function get_entry($id = false) {
		global $wpdb;
		if (!is_object($this) || !is_integer($id) || empty($this->class))
			return false;
		$result = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table} WHERE ID = %d", absint($id)), ARRAY_A);
		return !empty($result['class']) && $result['class'] === $this->class ? $result : false;
	}

/*---:[ ajax data handling methods ]:---*/

	public function backup() {
		global $thesis;
		$thesis->wp->check();
		$thesis->wp->nonce($_POST['nonce'], 'thesis-skin-manager');
		echo $thesis->api->alert($this->add(stripslashes($_POST['note'])) === false ?
			__('Backup failed.', 'thesis') :
			__('Backup complete!', 'thesis'), 'manager_saved', true);
		if ($thesis->environment == 'ajax') die();
	}

/*
	— Called when the backup table is updated with a new entry
	— Echoes a new backup_table() based on the most recent (updated) data
*/
	public function update_backup() {
		global $thesis;
		$thesis->wp->check();
		$thesis->wp->nonce($_POST['nonce'], 'thesis-skin-manager');
		echo $this->backup_table();
		if ($thesis->environment == 'ajax') die();
	}

/*
	— Wholesale replaces all Skin options
	— Any options present in the requested backup state will be restored
	— Any options not present will be deleted (and will revert to defaults, if applicable)
*/
	public function restore_backup() {
		global $thesis;
		$thesis->wp->check();
		$thesis->wp->nonce($_POST['nonce'], 'thesis-skin-manager');
		if (!empty($_POST['id']) && is_array($result = $this->get_entry(absint($_POST['id']))) && !empty($result['class']) && $result['class'] == $this->class) {
			// Remove data that is not needed for the restoration
			unset($result['ID'], $result['time'], $result['class'], $result['notes']);
			if (!empty($_POST['backup']) && $_POST['backup'] == 'true')
				$this->automatic_backup(__('Restore Skin data', 'thesis'));
			$verified = array();
			foreach (array_filter($result) as $key => $check)
				if (in_array($key, $this->options) && ($value = maybe_unserialize($check)))
					$verified[$key] = $value;
			foreach ($this->options as $option) {
				if (!empty($verified[$option]))
					update_option("{$this->class}_$option", $verified[$option]);
				else
					delete_option("{$this->class}_$option");
			}
			wp_cache_flush();
			$thesis->skin->_write_css();
		}
		if ($thesis->environment == 'ajax') die();
	}

	public function delete_backup() {
		global $thesis;
		$thesis->wp->check();
		$thesis->wp->nonce($_POST['nonce'], 'thesis-skin-manager');
		echo $thesis->api->alert($this->delete((int) $_POST['id']) === false ?
			__('Deletion failed.', 'thesis') :
			__('Backup deleted!', 'thesis'), 'manager_saved', true);
		if ($thesis->environment == 'ajax') die();
	}

/*
	Export selected Skin data items
	— Generates a serialized Thesis Skin import file (.txt) with selected options (even if they are empty)
*/
	public function export() {
		global $thesis;
		$thesis->wp->check();
		$thesis->wp->nonce($_POST['_wpnonce-thesis-skin-export'], 'thesis-skin-export');
		if (!is_array($_POST['export']))
			$this->error(__('The export data was incomplete. Please revisit the Skin Data Manager and try again.', 'thesis'));
		elseif (($options = array_filter($_POST['export'])) && (empty($options) || empty($options['id'])))
			$this->error(__('No backup state was specified for export. Revisit the Skin Data Manager and try again.', 'thesis'));
		$id = (int) $options['id'];
		unset($options['id']);
		if (!($data = $this->get_entry($id)) || $data['class'] !== $this->class)
			$this->error(__('The requested export data does not match the current Skin. Please revisit the Skin Data Manager and try again.', 'thesis'));
		unset($data['ID']);
		unset($data['notes']);
		unset($data['time']);
		$new = array();
		foreach (array_intersect(array_keys($options), $this->options) as $option)
			if (isset($data[$option]))
				$new[$option] = maybe_unserialize($data[$option]);
		$new['class'] = $data['class'];
		if (empty($new) || !($serialized = serialize($new)))			// serialize the whole shebang
			$this->error(__('Export data serialization failed. Please revisit the Skin Data Manager and try again', 'thesis'));
		$md5 = md5($serialized);										// get hash of data
		$hash_added = array('data' => $new, 'checksum' => $md5);		// add hash
		if (!($out = serialize($hash_added)))							// serialize it all
			$this->error(__('Export data serialization (with hashing) failed. Please revisit the Skin Data Manager and try again.', 'thesis'));
		header('Content-Type: text/plain; charset='. get_option('blog_charset'));
		header('Content-Disposition: attachment; filename="'. str_replace('_', '-', $this->class). '-'. @date('Y\-m\-d\-H\-i'). '.txt"');
		printf('%s', $out);
		exit;
	}

/*
	Import a Thesis Skin data file
	— Any included (and valid) options will be overwritten, even if empty.
*/
	public function import($file, $action) {
		global $thesis;
		$thesis->wp->check();
		check_admin_referer($action, 'thesis_form_nonce');
		if (empty($_FILES[$file]))
			return __('The import file is not present!', 'thesis');
		elseif ($_FILES[$file]['error'] > 0)
		 	return __('The server encountered an error with the import file. No data will be imported.', 'thesis');
		elseif (!is_array($data = $this->verify_skin_data_file($_FILES[$file], $this->class)))
			return empty($data) || !is_string($data) ?
				__('The import file does not contain valid Thesis Skin data.', 'thesis') :
				$data;
		unset($data['class']);
		if (empty($data))
			return __('The import file does not contain any Skin data.', 'thesis');
		$this->automatic_backup(__('Import Skin data', 'thesis'));
		foreach ($data as $option => $value)
			if (in_array($option, $this->options))
				if (!empty($value))
					update_option("{$this->class}_$option", $value);
				else
					delete_option("{$this->class}_$option");
		wp_cache_flush();
		$thesis->skin->_write_css();
		return true;
	}

	public function restore_default() {
		global $thesis;
		$thesis->wp->check();
		$thesis->wp->nonce($_POST['nonce'], 'thesis-skin-manager');
		$form = array();
		if (!empty($_POST['form']))
			parse_str(stripslashes($_POST['form']), $form);
		echo $response = $this->defaults(
			$thesis->skin->_skin,																				// Skin
			!empty($form['restore']) ? $form['restore'] : array(),												// options
			!empty($_POST['backup']) && $_POST['backup'] == 'true' ? __('Restore defaults', 'thesis') : false) === true ?
				'true' :
				$thesis->api->alert($thesis->api->efn($response), 'default_not_saved', true);
		if ($thesis->environment == 'ajax') die();
	}

/*---:[ Manager action helper methods ]:---*/

	public function defaults($skin = array(), $restore = array(), $backup = false) {
		global $thesis;
		if (empty($skin) || empty($skin['class']) || empty($skin['folder']))
			return __('No Skin was selected for restoration', 'thesis');
		$restore = is_array($restore) ?
			array_filter(array_map('intval', $restore)) : ($restore == 'new' ?
			array_combine($this->options, array_fill(0, count($this->options), 1)) :
			array());
		if (empty($restore))
			return __('No data was selected for restoration!', 'thesis');
		$dir = defined('THESIS_USER_SKINS') && file_exists(THESIS_USER_SKINS. "/{$skin['folder']}") ?
			THESIS_USER_SKINS. "/{$skin['folder']}" : false;
		if (!empty($dir) && file_exists($dir. '/default.php')) {
			include_once($dir. '/default.php');
			if (function_exists($skin['class']. '_defaults')) {
				if (!is_array($default_data = call_user_func($skin['class']. '_defaults')))
					return __('The default Skin data is not in valid array format!', 'thesis');
				if (!empty($backup))
					$this->automatic_backup($backup, $skin['class']);
				if (isset($restore['css_custom'])) {
					unset($restore['css_custom']);
					delete_option("{$skin['class']}_css_custom");
				}
				foreach (array_keys($restore) as $option)
					if (isset($default_data[$option]))
						update_option("{$skin['class']}_$option", $default_data[$option]);
					else
						delete_option("{$skin['class']}_$option");
				wp_cache_flush();
			}
			else
				return __('This Skin does not have a valid default.php file.', 'thesis');
		}
		else
			return __('This Skin does not have a default.php file!', 'thesis');
		if ($skin['class'] == $thesis->skin->_class)
			$thesis->skin->_write_css();
		return true;
	}

	public function automatic_backup($state = false, $skin = false) {
		if (apply_filters('thesis_skin_manager_automatic_backups', true))
			$this->add('['. __('Automatic backup', 'thesis'). (!empty($state) ? ': '. $state : ''). ']', !empty($skin) ? $skin : false);
	}

	public function error($error) {
		global $thesis;
		echo
			'<p>', $thesis->api->efn($error), "</p>\n",
			'<p><a href="', esc_url(set_url_scheme(home_url('?thesis_editor=1'))), '">',
			__('Return to the Thesis Skin Editor', 'thesis'),
			'</a></p>';
		exit;
	}

/*---:[ Skin Manager database table ]:---*/

	private function table() {
		global $wpdb;
		$exists = $wpdb->get_var("SHOW TABLES LIKE '{$this->table}'");
		$return = true;
		if ($exists && !(bool) $wpdb->query("SHOW COLUMNS FROM {$this->table} LIKE '_design'"))
			$return = (bool) $wpdb->query("ALTER TABLE {$this->table} ADD _design longtext NOT NULL");
		if ($exists && !(bool) $wpdb->query("SHOW COLUMNS FROM {$this->table} LIKE '_display'"))
			$return = (bool) $wpdb->query("ALTER TABLE {$this->table} ADD _display longtext NOT NULL");
		if ($exists && !(bool) $wpdb->query("SHOW COLUMNS FROM {$this->table} LIKE 'css_editor'"))
			$return = (bool) $wpdb->query("ALTER TABLE {$this->table} ADD css_editor longtext NOT NULL");
		if (!empty($exists))
			return $return && true;
		else {											// make the table
			$sql = "CREATE TABLE {$this->table} (
				ID bigint(20) unsigned NOT NULL auto_increment,
				time bigint(20) NOT NULL,
				class varchar(200) NOT NULL,
				boxes longtext NOT NULL,
				templates longtext NOT NULL,
				packages longtext NOT NULL,
				vars longtext NOT NULL,
				css longtext NOT NULL,
				css_editor longtext NOT NULL,
				css_custom longtext NOT NULL,
				notes longtext NOT NULL,
				_design longtext NOT NULL,
				_display longtext NOT NULL,
				PRIMARY KEY (ID)
			) COLLATE utf8_general_ci;";				// force utf8 collation to avoid latin1: destroyer of worlds
			$query = $wpdb->query($sql);
			return (bool) $query && $return;
		}
	}

/*---:[ data verification ]:---*/

	public function verify_class_name($class) {
		return preg_match('/\A[a-zA-Z_]\w*\Z/', $class) ? $class : false;
	}

	public function verify_skin_data_file($file, $class) {
		$string = is_string($file);
		$array = is_array($file);
		$name = $string ? basename($file) : ($array ? $file['name'] : false);
		$location = $string ? $file : ($array ? $file['tmp_name'] : false);
		if (($array || $string) && !file_exists($location))
		 	return __('The import file does not exist. Please try again.', 'thesis');
		elseif (empty($class))
			return __('A valid Skin class name was not specified. No data was imported.', 'thesis');
		elseif (empty($name))
		 	return __('The file name is inadequate. No data will be imported.', 'thesis');
		elseif (!preg_match('/^[a-z0-9-]+\.txt$/', strtolower($name)))
			return __('The import file name did not pass a basic legitimacy test. No data was imported.', 'thesis');
		elseif (!($serialized = file_get_contents($location)))
			return __('Thesis could not read the specified import file.', 'thesis');
		elseif (!is_serialized($serialized))
			return __('The import file is not properly serialized. No data will be imported.', 'thesis');
		elseif (!($contents = unserialize($serialized)))
			return __('The import file could not be unserialized. No data will be imported.', 'thesis');
		elseif (empty($contents['checksum']))
		 	return __('The import file does not have a proper checksum value and cannot be trusted.', 'thesis');
		elseif (empty($contents['data']))
			return __('The import file does not contain any data.', 'thesis');
		elseif (!is_array($contents['data']))
			return __('The import file data is not formatted properly (it should be an array).', 'thesis');
		elseif ($contents['checksum'] !== md5(serialize($contents['data'])))
			return __('The import file checksum does not match the file data. No data will be imported.', 'thesis');
		elseif (empty($contents['data']['class']))
			return __('The import file does not specify the Skin class to which it applies. No data will be imported.', 'thesis');
		elseif ($contents['data']['class'] !== $class)
			return __('The import file does not apply to this Skin.', 'thesis');
		return !empty($contents['data']) ?
			$contents['data'] :
			__('The import file only included empty data, so nothing was imported.', 'thesis');
	}
}