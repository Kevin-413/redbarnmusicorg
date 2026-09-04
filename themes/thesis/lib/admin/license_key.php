<?php
/*
Copyright 2015 DIYthemes, LLC. Patent pending. All rights reserved.
License: DIYthemes Software License Agreement
License URI: https://diythemes.com/thesis/rtfm/software-license-agreement/
*/
class thesis_license_key {
	private $product = 148383;
	private $name = 'Thesis';
	private $class = 'thesis';
	private $admin_url = 'admin.php?page=thesis&canvas=license_key';
	private $response_key = 'thesis-update-response';
	private $license_key = false;

	public function __construct() {
		if (!current_user_can('manage_options')) return;
		$this->license_key = trim(get_option('thesis_license_key'));
		// Update filters
		add_filter('thesis_updates', array($this, 'thesis_update'));
		add_filter('thesis_license_key', array($this, 'get_license_key'));
		add_filter('thesis_license_key_nag', array($this, 'license_key_nag'));
		add_filter('site_transient_update_themes', array($this, 'theme_update_transient'));
		add_filter('delete_site_transient_update_themes', array($this, 'delete_theme_update_transient'));
		add_filter('http_request_args', array($this, 'disable_wporg_request'), 5, 2);
		// Update actions
		add_action('load-update-core.php', array($this, 'delete_theme_update_transient'));
		add_action('admin_notices', array($this, 'update_nag'));
		add_action('load-themes.php', array($this, 'delete_theme_update_transient'));
		// License key admin page
		add_filter('thesis_site_menu', array($this, 'thesis_menu'), 1);
		add_filter('thesis_quicklaunch_menu', array($this, 'quicklaunch_menu'), 60);
		add_action('admin_post_thesis_license_key', array($this, 'save'));
		if (!empty($_GET['canvas']) && $_GET['canvas'] == 'license_key') {
			add_action('admin_init', array($this, 'admin_js'));
			add_action('thesis_admin_canvas', array($this, 'admin'));
		}
	}

	public function thesis_menu($menu) {
		$menu['license'] = array(
			'id' => 't_license',
			'text' => __('Thesis License Key', 'thesis'),
			'url' => admin_url($this->admin_url),
			'description' => __('Enter a valid Thesis License Key to receive automatic updates', 'thesis'));
		return $menu;
	}

	public function quicklaunch_menu($menu) {
		$menu['license'] = array(
			'text' => __('Thesis License Key', 'thesis'),
			'url' => $this->admin_url,
			'title' => __('Enter a valid license key for automatic updates.', 'thesis'));
		return $menu;
	}

	public function get_license_key() {
		return $this->license_key;
	}

	public function thesis_update($updates) {
		$update_data = $this->check_for_update();
		if (!empty($update_data) && !empty($update_data['new_version']) && !empty($update_data['package']))
			$updates['core'] = $update_data['new_version'];
		return $updates;
	}

	public function license_key_nag($nag) {
		if (!empty($this->license_key)) return $nag;
		$tab = str_repeat("\t", 3);
		return $nag.
			"$tab<div class=\"t_update_alert\">\n".
			"$tab\t<h3 class=\"t_update_available\">". __('Please enter a Thesis license key!', 'thesis'). "</h3>\n".
			"$tab\t<p>". sprintf(__('You&rsquo;ll need to <a href="%1$s">enter a valid Thesis license key</a> to receive updates on Thesis, Skins, and Boxes.', 'thesis'), admin_url($this->admin_url)). "</p>\n".
			"$tab</div>\n";
	}

	public function admin() {
		global $thesis;
		$status = get_option("{$this->class}_license_key_status", false);
		$message = get_transient("{$this->class}_license_message");
if (empty($message) && !empty($this->license_key)) {
  $api_response = $thesis->api->check_license(
    $this->license_key,
    $this->product,
    $this->name,
    $this->class,
    $this->admin_url,
    $this->response_key
  );
  if (is_bool($api_response)) {
    // Handle the boolean response (e.g., log an error)
  } else {
    set_transient("{$this->class}_license_message", $api_response, 60*60*24);
  }
  $message = get_transient("{$this->class}_license_message");
}
		$license = $thesis->api->form->fields(array(
			'license_key' => array(
				'type' => 'text',
				'width' => 'long',
				'code' => true,
				'label' => __('Enter Your Thesis License Key', 'thesis'),
				'tooltip' => sprintf(__('Check out the <a href="%1$s" target="_blank" rel="noopener noreferrer">Thesis License Key documentation</a> for everything you need to know about license keys.', 'thesis'), esc_url('https://diythemes.com/thesis/rtfm/how-to/license-key/')))), !empty($this->license_key) ? array('license_key' => $this->license_key) : array(), 't_license_', false, 3, 3);
		echo
			"\t\t<h3>", __('Thesis License Key', 'thesis'), "</h3>\n",
			"\t\t<form class=\"thesis_options_form\" method=\"post\" action=\"", esc_url(admin_url('admin-post.php?action=thesis_license_key')), "\" id=\"t_license\">\n",
			"\t\t\t{$license['output']}\n",
			(!empty($message) ?
			"\t\t\t<div class=\"status\">$message</div>\n" : ''),
			"\t\t\t<button data-style=\"button save\" name=\"save_options\" value=\"1\"><span data-style=\"dashicon big squeeze\">&#xf147;</span> ", $thesis->api->efn(__('Save License Key', 'thesis')), "</button>\n",
			"\t\t\t<button data-style=\"button delete inline\" name=\"delete_options\" value=\"1\"><span data-style=\"dashicon\">&#xf153;</span> ", $thesis->api->efn(__('Delete License Key', 'thesis')), "</button>\n",
			wp_nonce_field('thesis_license_key', '_wpnonce', true, false),
			"\t\t</form>\n";
	}

	public function admin_js() {
		wp_enqueue_script('thesis-options');
	}

	public function save() {
		global $thesis;
		$thesis->wp->check();
		check_admin_referer('thesis_license_key');
		if (!empty($_POST['save_options']) && !empty($_POST['license_key']) && ($this->license_key = trim($_POST['license_key']))) {
			$thesis->api->delete_transients();
			$thesis->api->activate(
				$this->license_key,
				$this->product,
				$this->name,
				$this->class,
				$this->admin_url,
				$this->response_key);
		}
		elseif (empty($_POST['license_key']) || !empty($_POST['delete_options'])) {
			$old_key = $this->license_key;
			$this->license_key = false;
			$thesis->api->delete_transients();
			$thesis->api->deactivate(
				$old_key,
				$this->product,
				$this->name,
				$this->class,
				$this->admin_url,
				$this->response_key);
		}
		wp_redirect(admin_url($this->admin_url));
		exit;
	}

	public function update_nag() {
		global $thesis;
		$theme = wp_get_theme($this->class);
		$api_response = get_transient($this->response_key);
		if ($api_response === false)
			return;
		if (version_compare($thesis->version, $api_response->new_version, '<') && !empty($api_response->package)) {
			echo '<div id="update-nag">';
			printf(
				__('<strong>%1$s %2$s</strong> is available. <a href="%3$s" target="_blank" rel="noopener noreferrer">Check out what&rsquo;s new</a> or <a href="%5$s"%6$s>update now</a>.', 'thesis'),
				$theme->get('Name'),
				$api_response->new_version,
				"{$thesis->store}/thesis/rtfm/changelog/v". str_replace('.', '', $api_response->new_version). '/',
				$theme->get('Name'),
				wp_nonce_url('update.php?action=upgrade-theme&amp;theme='. urlencode($this->class), 'upgrade-theme_'. $this->class),
				' onclick="if (confirm(\''. esc_js(__("Proceed with Theme update? 'Cancel' to stop, 'OK' to update.", 'thesis')). '\')) {return true;}return false;"');
			echo '</div>';
			echo '<div id="'. $this->class. '_'. 'changelog" style="display:none;">';
			echo wpautop($api_response->sections['changelog']);
			echo '</div>';
		}
	}

	public function theme_update_transient($value) {
		$update_data = $this->check_for_update();
		if ($update_data) {
			$update_data['theme'] = $this->class;
			$value->response[$this->class] = $update_data;
		}
		return $value;
	}

	public function delete_theme_update_transient() {
		delete_transient($this->response_key);
	}

	public function check_for_update() {
		global $thesis;
		$update_data = get_transient($this->response_key);
		if ($update_data === false) {
			$failed = false;
			$response = $thesis->api->post($thesis->store, array(
				'edd_action' => 'get_version',
				'license' => $this->license_key,
				'item_id' => $this->product,
				'name' => $this->name,
				'slug' => $this->class,
				'version' => $thesis->version,
				'author' => 'Chris Pearson | DIYthemes',
				'beta' => false));
			$update_data = json_decode(wp_remote_retrieve_body($response));
			if (is_wp_error($response) || wp_remote_retrieve_response_code($response) != 200 || !is_object($update_data))
				$failed = true;
			if ($failed) {
				$data = new stdClass;
				$data->new_version = $thesis->version;
				set_transient($this->response_key, $data, strtotime('+30 minutes', time()));
				return false;
			}
			else {
				$update_data->sections = maybe_unserialize($update_data->sections);
				set_transient($this->response_key, $update_data, strtotime('+12 hours', time()));
			}
		}
		if (version_compare($thesis->version, $update_data->new_version, '>='))
			return false;
		return (array) $update_data;
	}

	public function disable_wporg_request($r, $url) {
		if (strpos($url, 'https://api.wordpress.org/themes/update-check/1.1/') !== 0)
			return $r;
		$themes = json_decode($r['body']['themes']);
		$parent = get_option('template');
		$child = get_option('stylesheet');
		unset($themes->themes->$parent);
		unset($themes->themes->$child);
		$r['body']['themes'] = json_encode($themes);
		return $r;
	}
}