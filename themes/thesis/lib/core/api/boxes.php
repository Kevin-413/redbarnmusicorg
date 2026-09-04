<?php
/*
Copyright 2012 DIYthemes, LLC. Patent pending. All rights reserved.
License: DIYthemes Software License Agreement
License URI: https://diythemes.com/thesis/rtfm/software-license-agreement/
*/
class thesis_home_seo extends thesis_box {
	public $type = false;
	public $filters = array(
		'menu' => 'site',
		'docs' => 'https://diythemes.com/thesis/rtfm/admin/site/blog-page-seo/',
		'priority' => 30,
		'canvas_left' => true);

	public function translate() {
		global $thesis;
		$this->title = sprintf(__('Blog Page %s', 'thesis'), $thesis->api->base['seo']);
		$this->filters['description'] = __('Enhance the SEO of your main blog page', 'thesis');
	}

	protected function class_options() {
		global $thesis;
		return array(
			'title' => array(
				'type' => 'text',
				'width' => 'full',
				'label' => __($thesis->api->strings['title_tag'], 'thesis'),
				'counter' => __($thesis->api->strings['title_counter'], 'thesis')),
			'description' => array(
				'type' => 'textarea',
				'rows' => 2,
				'label' => __($thesis->api->strings['meta_description'], 'thesis'),
				'counter' => __($thesis->api->strings['description_counter'], 'thesis')),
			'keywords' => array(
				'type' => 'text',
				'width' => 'full',
				'label' => __($thesis->api->strings['meta_keywords'], 'thesis'),
				'tooltip' => sprintf(__('Please note that keywords will not appear unless you also include the Meta Keywords Box in your <a href="%s">HTML Head template</a>.', 'thesis'), admin_url('admin.php?page=thesis&canvas=head'))));
	}
}

class thesis_meta_verify extends thesis_box {
	public $type = false;
	protected $filters = array(
		'menu' => 'site',
		'docs' => 'https://diythemes.com/thesis/rtfm/admin/site/site-verification/',
		'canvas_left' => true);
	private $allowed = array(
		'meta' => array(
			'name' => array(),
			'content' => array()));
	private $analytics = 'thesis_google_analytics';
	private $override = false;

	protected function translate() {
		$this->title = __('Site Verification', 'thesis');
		$this->filters['description'] = __('Verify your site with Google, Bing, and Pinterest', 'thesis');
	}

	protected function class_options() {
		$tooltip = __('For optimal search engine performance, we recommend verifying your site with', 'thesis');
		$options = array(
			'google' => array(
				'type' => 'text',
				'width' => 'full',
				'label' => __('Google Site Verification', 'thesis'),
				'tooltip' => sprintf(__('%1$s <a href="%2$s" target="_blank" rel="noopener">Google Webmaster Tools</a>. Copy and paste the unique <code>content=&quot;[this is what you want to copy]&quot;</code> value into this field.', 'thesis'), $tooltip, 'https://www.google.com/webmasters/tools/'),
				'description' => __('Note: This option will disappear if you are using Google Analytics (which automatically verifies your site)', 'thesis')),
			'bing' => array(
				'type' => 'text',
				'width' => 'full',
				'label' => __('Bing Site Verification', 'thesis'),
				'tooltip' => sprintf(__('%1$s <a href="%2$s" target="_blank" rel="noopener">Bing Webmaster Tools</a>. Copy and paste the unique <code>content=&quot;[this is what you want to copy]&quot;</code> value into this field.', 'thesis'), $tooltip, 'https://www.bing.com/toolbox/webmaster')),
			'pinterest' => array(
				'type' => 'text',
				'width' => 'full',
				'label' => __('Pinterest Site Verification', 'thesis'),
				'tooltip' => sprintf(__('Verify your site with <a href="%1$s" target="_blank" rel="noopener">Pinterest</a>. Copy and paste the unique <code>&lt;meta&gt;</code> tag content as shown here: <code>content=&quot;[this is what you want to copy]&quot;</code>', 'thesis'), 'https://help.pinterest.com/en/articles/claim-your-website')));
		if ($this->override)
			unset($options['google']);
		return $options;
	}

	protected function construct() {
		global $thesis;
		$override = $thesis->api->get_option($this->analytics);
		$this->override = is_array($override) && !empty($override['ga']) ? true : false;
		add_action('hook_head_bottom', array($this, 'html'), 1);
	}

	public function html() {
		if (!is_front_page()) return;
		$also_verify = apply_filters("{$this->_class}_html", false);
		echo
			(empty($this->override) && !empty($this->options['google']) ? (preg_match('/<meta/', $this->options['google']) ?
			trim(wp_kses($this->options['google'], $this->allowed)). "\n" :
			"<meta name=\"google-site-verification\" content=\"". trim(esc_attr($this->options['google'])). "\" />\n") : ''),
			(!empty($this->options['bing']) ? (preg_match('/<meta/', $this->options['bing']) ?
			trim(wp_kses($this->options['bing'], $this->allowed)). "\n" :
			"<meta name=\"msvalidate.01\" content=\"". trim(esc_attr($this->options['bing'])). "\" />\n") : ''),
			(!empty($this->options['pinterest']) ? (preg_match('/<meta/', $this->options['pinterest']) ?
			trim(wp_kses($this->options['pinterest'], $this->allowed)). "\n" :
			"<meta name=\"p:domain_verify\" content=\"". trim(esc_attr($this->options['pinterest'])). "\" />\n") : ''),
			(!empty($also_verify) ? trim(wp_kses($also_verify, $this->allowed)). "\n" : '');
	}
}

class thesis_google_analytics extends thesis_box {
	public $type = false;
	protected $filters = array(
		'menu' => 'site',
		'docs' => 'https://diythemes.com/thesis/rtfm/admin/site/google-analytics/');

	protected function translate() {
		$this->title = __('Google Analytics', 'thesis');
		$this->filters['description'] = __('Add Google Analytics to your site', 'thesis');
	}

	protected function class_options() {
		return array(
			'ga' => array(
				'type' => 'text',
				'width' => 'medium',
				'label' => __('Google Analytics Tracking ID', 'thesis'),
				'tooltip' => sprintf(__('To add Google Analytics tracking to Thesis, simply enter your Tracking ID here. This number takes the general form <code>UA-XXXXXXX-Y</code> and can be found via the Home link in your <a href="%s">Google Analytics dashboard</a> (login required).', 'thesis'), 'https://www.google.com/analytics/')));
	}

	protected function construct() {
		if (is_admin() && ($update = get_option('thesis_analytics')) && !empty($update)) {
			update_option($this->_class, ($this->options = array('ga' => $update)));
			delete_option('thesis_analytics');
			wp_cache_flush();
		}
		add_action('hook_head_top', array($this, 'html'), apply_filters("{$this->_class}_hook_priority", 1));
	}

	public function html() {
		if (empty($this->options['ga'])) return;
		$tracking = trim(esc_attr($this->options['ga']));
		$events = apply_filters("{$this->_class}_gtag_events", false);
		echo
			(apply_filters("{$this->_class}_gtag_indicator", true) ?
			"<!-- Global Site Tag (gtag.js) - Google Analytics -->\n" : ''),
			"<script src=\"https://www.googletagmanager.com/gtag/js?id=$tracking\" async></script>\n",
			"<script>\n",
			"\twindow.dataLayer = window.dataLayer || [];\n",
			"\tfunction gtag(){dataLayer.push(arguments);}\n",
			"\tgtag('js', new Date());\n",
			"\t". apply_filters("{$this->_class}_gtag_config", "gtag('config', '$tracking');", $tracking). "\n",
			(!empty($events) ?
			"\t$events\n" : ''),
			"</script>\n";
	}
}

class thesis_twitter_profile extends thesis_box {
	protected function translate() {
		$this->title = __('Twitter Profile Link', 'thesis');
	}

	protected function html_options() {
		return array(
			'display' => array(
				'type' => 'radio',
				'label' => __('Display name as:', 'thesis'),
				'tooltip' => sprintf(__('Choose how the author&#8217;s Twitter profile link will be presented. You can edit each author&#8217;s %1$s on their <a href="%2$s">user profile page</a>.', 'thesis'), $this->title, admin_url('users.php')),
				'options' => array(
					'handle' => __('Twitter handle (@YourUsername)', 'thesis'),
					'text' => __('Call-to-action text (&#8220;Follow me on Twitter here.&#8221;)', 'thesis')),
				'default' => 'handle'));
	}

	protected function construct() {
		add_filter('user_contactmethods', array($this, 'add_twitter'));
	}

	public function add_twitter($contacts) {
		$contacts['twitter'] = $this->title;
		return $contacts;
	}

	public function html($args = array()) {
		global $post;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", !empty($depth) ? $depth : 0);
		$twitter = trim(esc_attr(str_replace('@', '', get_user_option('twitter', $post->post_author))));
		if (empty($twitter)) return;
		$url = "https://twitter.com/$twitter";
		echo
			"$tab<span class=\"twitter_profile\">",
			(!empty($this->options['display']) ?
				sprintf(apply_filters("{$this->_class}_text", __('Follow me on Twitter <a href="%s">here</a>.', 'thesis')), $url) :
				"<a href=\"$url\">@$twitter</a>"),
			"</span>\n";
	}
}