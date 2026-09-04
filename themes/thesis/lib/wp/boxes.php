<?php
/*
Copyright 2012 DIYthemes, LLC. Patent pending. All rights reserved.
License: DIYthemes Software License Agreement
License URI: https://diythemes.com/thesis/rtfm/software-license-agreement/
*/
class thesis_feed_link extends thesis_box {
	public $type = false;
	protected $filters = array(
		'menu' => 'site',
		'docs' => 'https://diythemes.com/thesis/rtfm/admin/site/rss-feed/',
		'priority' => 25);

	protected function translate() {
		global $thesis;
		$this->title = sprintf(__('%s Feed', 'thesis'), $thesis->api->base['rss']);
		$this->filters['description'] = __('Manage your site&#8217;s RSS feed', 'thesis');
	}

	protected function class_options() {
		global $thesis;
		return array(
			'url' => array(
				'type' => 'text',
				'width' => 'long',
				'code' => true,
				'label' => sprintf(__('%1$s %2$s', 'thesis'), $this->title, $thesis->api->base['url']),
				'tooltip' => sprintf(__('If you don&#8217;t enter anything in this field, Thesis will use your default WordPress feed, <code>%1$s</code>. If you&#8217;d like to use any other feed, please enter the feed %2$s here.', 'thesis'), esc_url(get_bloginfo(get_default_feed(). '_url')), $thesis->api->base['url'])));
	}

	protected function construct() {
		add_action('hook_head_bottom', array($this, 'html'), 1);
	}

	public function html() {
		global $thesis;
		if (($url = apply_filters($this->_class, !empty($this->options['url']) ? $this->options['url'] : get_bloginfo(get_default_feed(). '_url'))) && is_string($url) && !empty($url))
			echo
				'<link href="', esc_url($url), '" rel="alternate" type="application/rss+xml" title="', trim($thesis->api->efh((!empty($thesis->api->options['blogname']) ?
				htmlspecialchars_decode($thesis->api->options['blogname'], ENT_QUOTES) : __('site', 'thesis')). ' '. __('feed', 'thesis'))), "\" />\n";
	}
}

class thesis_pingback_link extends thesis_box {
	public $type = false;

	protected function translate() {
		global $thesis;
		$this->title = sprintf(__('Pingback %s', 'thesis'), $thesis->api->base['url']);
	}

	protected function construct() {
		add_action('template_redirect', array($this, 'template_redirect'));
	}

	public function template_redirect() {
		if (apply_filters($this->_class, true))
			add_action('hook_head_bottom', array($this, 'html'), 1);
	}

	public function html() {
		echo '<link href="', esc_url(get_bloginfo('pingback_url')), "\" rel=\"pingback\" />\n";
	}
}

class thesis_wp_nav_menu extends thesis_box {
	protected function translate() {
		global $thesis;
		$this->name = __('Nav Menu', 'thesis');
		$this->title = sprintf('%1$s (%2$s)', $this->name, $thesis->api->base['wp']);
		$this->control = '≡ '. __('Menu', 'thesis');
	}

	protected function html_options() {
		global $thesis;
		return array(
			'menu_id' => array(
				'type' => 'text',
				'width' => 'medium',
				'code' => true,
				'label' => __($thesis->api->strings['html_id'], 'thesis'),
				'tooltip' => __($thesis->api->strings['id_tooltip'], 'thesis')),
			'menu_class' => array(
				'type' => 'text',
				'width' => 'medium',
				'code' => true,
				'label' => __($thesis->api->strings['html_class'], 'thesis'),
				'tooltip' => sprintf(__('By default, this menu will render with a %1$s of <code>menu</code>, but if you&#8217;d prefer to use a different %1$s, you can supply one here.%2$s', 'thesis'), $thesis->api->base['class'], __($thesis->api->strings['class_note'], 'thesis')),
				'placeholder' => 'menu'));
	}

	protected function options() {
		global $thesis;
		if (!$this->_display() || !($thesis->environment == 'editor' || $thesis->environment == 'ajax' || (!empty($_GET['canvas']) && ($_GET['canvas'] == $this->_id || $_GET['canvas'] == "{$thesis->skin->_class}__content")))) return;
		$menus[''] = __('Select a WP menu:', 'thesis');
		foreach (wp_get_nav_menus() as $menu)
			$menus[(int) $menu->term_id] = $thesis->api->efh($menu->name);
		$options = array(
			'menu' => array(
				'type' => 'select',
				'label' => __('Menu To Display', 'thesis'),
				'tooltip' => sprintf(__('Select a WordPress nav menu for this box to display. To edit your menus, visit the <a href="%s">WordPress nav menu editor</a>.', 'thesis'), admin_url('nav-menus.php')),
				'options' => $menus),
			'warning' => array(
				'type' => 'custom',
				'output' => '<p class="highlight">'. apply_filters("{$this->_class}_warning", false). '</p>'),
			'control' => array(
				'type' => 'checkbox',
				'label' => __('Responsive Menu Control', 'thesis'),
				'options' => array(
					'yes' => __('Output menu control button for responsive layouts', 'thesis')),
				'dependents' => array('yes')),
			'control_note' => array(
				'type' => 'custom',
				'output' =>
					'<p class="highlight">'.
						sprintf(__('<strong>Note:</strong> Enter your responsive menu control text below (scripts and links are not allowed). Alternatively, you can use a filter to supply control text for this menu: <code>thesis_wp_nav_menu_%scontrol_text</code>.', 'thesis'), trim(esc_attr(!empty($this->options['_id']) ? "{$this->options['_id']}_" : ''))).
					'</p>',
				'parent' => array(
					'control' => 'yes')),
			'control_text' => array(
				'type' => 'text',
				'width' => 'short',
				'label' => __('Menu Control Text', 'thesis'),
				'default' => $this->control,
				'parent' => array(
					'control' => 'yes')));
		if (!apply_filters("{$this->_class}_warning", false))
			unset($options['warning']);
		return $options;
	}

	public function preload() {
		global $thesis_wp_nav_menu_js;
		if (!empty($this->options['control']) && !empty($this->options['control']['yes']) && !$thesis_wp_nav_menu_js) {
			add_filter('thesis_footer_scripts', array($this, 'js'));
			$thesis_wp_nav_menu_js = true;
		}
		add_action('hook_head_bottom', array($this, 'scope'), 100);
	}

	public function html($args = array()) {
		global $thesis;
		extract($args = is_array($args) ? $args : array());
		add_filter('wp_page_menu', array($this, 'filter_menu'), 10, 2);
		$menu = wp_nav_menu(apply_filters($this->_class. (!empty($hook) ? "_$hook" : ''). '_args',
			array_merge($this->options, array('container' => false, 'echo' => false, 'thesis' => true))));
		remove_filter('wp_page_menu', array($this, 'filter_menu'), 10, 2);
		$hook = trim(esc_attr(!empty($this->options['_id']) ?
			$this->options['_id'] : (!empty($this->options['hook']) ?
			$this->options['hook'] : '')));
		echo
			str_repeat("\t", !empty($depth) ? $depth : 0),
			(!empty($this->options['control']) && !empty($this->options['control']['yes']) ?
			"<span class=\"menu_control\">".
				trim($thesis->api->efn(apply_filters(
					$this->_class. (!empty($hook) ? "_$hook" : ''). '_control_text',
					!empty($this->options['control_text']) ?
						$this->options['control_text'] :
						$this->control))). "</span>\n" : ''),
			apply_filters($this->_class. (!empty($hook) ? "_$hook" : ''), $menu), "\n";
	}

	public function filter_menu($menu, $args) {
		if (empty($args['thesis']))
			return $menu;
		preg_match('/<ul>(.*)<\/ul>/', $menu, $li);
		return !empty($li[1]) ? "<ul class=\"". esc_attr($args['menu_class']). "\">{$li[1]}</ul>" : '';
	}

	public function js($scripts) {
		$scripts[] =
			"<script>".
			// add getElementsByClassName support to stupid IE
			"(function(d,g){d[g]||(d[g]=function(g){return this.querySelectorAll(\".\"+g)},Element.prototype[g]=d[g])})(document,\"getElementsByClassName\");".
			"(function(){".
			"var classes = document.getElementsByClassName('menu_control');".
			"for (i = 0; i < classes.length; i++) {".
			"classes[i].onclick = function() {".
			"var menu = this.nextElementSibling;".
			"if (/show_menu/.test(menu.className))".
			"menu.className = menu.className.replace('show_menu', '').trim();".
			"else ".
			"menu.className += ' show_menu';".
			"if (/menu_control_triggered/.test(this.className))".
			"this.className = this.className.replace('menu_control_triggered', '').trim();".
			"else ".
			"this.className += ' menu_control_triggered';".
			"};".
			"}".
			"})();".
			"</script>";
		return $scripts;
	}

	public function scope() {
		if (!empty($this->options['control']) && !empty($this->options['control']['yes'])) {
			if (!empty($this->options['menu_class'])) {
				$class = explode(' ', $this->options['menu_class']);
				$class = array_pop($class);
			}
			else
				$class = 'menu';
			echo "<noscript><style>.". esc_attr($class). " { display: block; }</style></noscript>";
		}
	}
}

class thesis_wp_loop extends thesis_box {
	public $type = 'rotator';
	public $switch = true;

	protected function translate() {
		global $thesis;
		$this->title = sprintf(__('%s Loop', 'thesis'), $thesis->api->base['wp']);
	}

	protected function term_options() {
		global $thesis;
		return array(
			'posts_per_page' => array(
				'type' => 'text',
				'width' => 'tiny',
				'label' => __($thesis->api->strings['posts_to_show'], 'thesis'),
				'default' => $thesis->api->get_option('posts_per_page')));
	}

	protected function template_options() {
		global $thesis;
		return array(
			'title' => $this->title,
			'exclude' => array('single', 'page'),
			'fields' => array(
				'posts_per_page' => array(
					'type' => 'text',
					'width' => 'tiny',
					'label' => __($thesis->api->strings['posts_to_show'], 'thesis'),
					'default' => $thesis->api->get_option('posts_per_page'))));
	}

	protected function construct() {
		add_filter('thesis_query', array($this, 'query'));
	}

	public function query($query) {
		$posts_per_page = !empty($this->term_options['posts_per_page']) && is_numeric($this->term_options['posts_per_page']) ?
			$this->term_options['posts_per_page'] : (!empty($this->template_options['posts_per_page']) && is_numeric($this->template_options['posts_per_page']) ?
			$this->template_options['posts_per_page'] : false);
		if ($posts_per_page)
			$query->query_vars['posts_per_page'] = $posts_per_page;
		return $query;
	}

	public function html($args = array()) {
		global $thesis, $wp_query, $post;
		extract($args = is_array($args) ? $args : array());
		$post_count = 1;
		if ($wp_query->is_404)
			$wp_query = apply_filters('thesis_404', $wp_query);
		if (apply_filters('thesis_use_custom_loop', false))
			$thesis->api->hook('thesis_custom_loop', $args);
		else {
			if (have_posts())
				while (have_posts()) {
					the_post();
					if (!$wp_query->is_singular)
						do_action('thesis_init_post_meta', $post->ID);
					$this->rotator(array_merge($args, array('post_count' => $post_count)));
					$post_count++;
				}
			elseif (!$wp_query->is_404)
				$thesis->api->hook('thesis_empty_loop');
		}
	}
}

class thesis_wp_featured_image extends thesis_box {
	protected function translate() {
		global $thesis;
		$this->title = sprintf(__('%s Featured Image', 'thesis'), $thesis->api->base['wp']);
	}

	protected function html_options() {
		global $thesis, $_wp_additional_image_sizes;
		$options = array(
			'full' => __('Full size (default)', 'thesis'),
			'thumbnail' => __('Thumbnail', 'thesis'),
			'medium' => __('Medium', 'thesis'),
			'large' => __('Large', 'thesis'));
		if (!empty($_wp_additional_image_sizes))
			foreach ($_wp_additional_image_sizes as $size => $data)
				$options[$size] = $size;
		return array(
			'size' => array(
				'type' => 'select',
				'label' => __('Featured Image Size', 'thesis'),
				'tooltip' => sprintf(__('Choose the size of the Feature Image for this location. The list includes <a href="%s">WordPress standard image sizes</a> and any other registered image sizes.', 'thesis'), admin_url('options-media.php')),
				'options' => $options,
				'default' => 'full'),
			'alignment' => array(
				'type' => 'select',
				'label' => __($thesis->api->strings['alignment'], 'thesis'),
				'tooltip' => __($thesis->api->strings['alignment_tooltip'], 'thesis'),
				'options' => array(
					'' => __($thesis->api->strings['alignnone'], 'thesis'),
					'left' => __($thesis->api->strings['alignleft'], 'thesis'),
					'right' => __($thesis->api->strings['alignright'], 'thesis'),
					'center' => __($thesis->api->strings['aligncenter'], 'thesis'))),
			'link' => array(
				'type' => 'checkbox',
				'options' => array(
					'link' => __('Link image to post', 'thesis')),
				'default' => array(
					'link' => true)));
	}

	protected function construct() {
		global $thesis;
		if (!$this->_display()) return;
		add_theme_support('post-thumbnails');
		if (empty($thesis->_wp_featured_image_rss)) {
			add_filter('the_content', array($this, 'add_image_to_feed'));
			$thesis->_wp_featured_image_rss = true;
		}
	}

	public function html($args = array()) {
		global $post;
		extract($args = is_array($args) ? $args : array());
		$size = !empty($this->options['size']) ? $this->options['size'] : 'full';
		$alignment = !empty($this->options['alignment']) ? ($this->options['alignment'] == 'left' ?
			'alignleft' : ($this->options['alignment'] == 'right' ?
			'alignright' :
			'aligncenter')) : false;
		$image = get_the_post_thumbnail(
				$post->ID,
				$size,
				array_filter(array(
					'itemprop' => !empty($schema) ? 'image' : false,
					'class' => !empty($alignment) ? $alignment : false)));
		if (empty($image)) return;
		$html =
			str_repeat("\t", !empty($depth) ? $depth : 0).
			apply_filters('wp_featured_image',
				(!isset($this->options['link']) ?
				'<a class="featured_image_link" href="'. get_permalink(). '">' : '').
				$image.
				(!isset($this->options['link']) ?
				'</a>' : ''),
				get_post_thumbnail_id(),
				!empty($depth) ? $depth : 0);
		if (!empty($return))
			return "$html\n";
		else
			echo "$html\n";
	}

	public function add_image_to_feed($content) {
		if (!is_feed()) return $content;
		return $this->html(array('return' => true)). $content;
	}
}

class thesis_comments_intro extends thesis_box {
	public $templates = array('single', 'page');

	protected function translate() {
		global $thesis;
		$this->title = __('Comments Intro', 'thesis');
		$this->singular = __($thesis->api->strings['comment_singular'], 'thesis');
		$this->plural = __($thesis->api->strings['comment_plural'], 'thesis');
	}

	public function html($args = array()) {
		global $thesis, $post;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", !empty($depth) ? $depth : 0);
		$comments = wp_count_comments($post->ID);
		$number = is_object($comments) && !empty($comments->approved) ? $comments->approved : 0;
		if (comments_open())
			echo
				"$tab<div class=\"comments_intro\">",
				apply_filters($this->_class,
				"<span class=\"num_comments\">$number</span> ".
				trim($thesis->api->efh($number == 1 ? 
					apply_filters('comment_singular', $this->singular) :
					apply_filters('comment_plural', $this->plural))).
				"&#8230; <a href=\"#". esc_attr(apply_filters('comment_form_respond_id', 'commentform')). "\" rel=\"nofollow\">". trim($thesis->api->efh(apply_filters("{$this->_class}_add", __('add one', 'thesis')))). "</a>"),
				"</div>\n";
		elseif ($number > 0)
			echo "$tab<p class=\"comments_closed\">",
				trim($thesis->api->efh(apply_filters("{$this->_class}_closed", __('Comments on this entry are closed.', 'thesis')))), "</p>\n";
	}
}

class thesis_comments extends thesis_box {
	public $type = 'rotator';
	public $dependents = array(
		'thesis_comment_author',
		'thesis_comment_avatar',
		'thesis_comment_date',
		'thesis_comment_number',
		'thesis_comment_permalink',
		'thesis_comment_edit',
		'thesis_comment_text',
		'thesis_comment_reply');
	public $children = array(
		'thesis_comment_author',
		'thesis_comment_date',
		'thesis_comment_edit',
		'thesis_comment_text',
		'thesis_comment_reply');
	public $override = false;
	public $templates = array('single', 'page');

	protected function translate() {
		$this->title = $this->name = __('Comment List', 'thesis');
	}

	protected function html_options() {
		global $thesis;
		$html = $thesis->api->html_options(array(
			'ul' => 'ul',
			'ol' => 'ol',
			'div' => 'div',
			'section' => 'section'), 'ul');
		unset($html['id'], $html['class']);
		return $html;
/*		Note: Per page setting has been disabled per WP's f-up with version 4.4.
		return array_merge($html, array(
			'per_page' => array(
				'type' => 'text',
				'width' => 'tiny',
				'label' => __('Comments Per Page', 'thesis'),
				'tooltip' => sprintf(__('The default is set in the <a href="%s">WordPress General &rarr; Discussion options</a>, but you can override that here.', 'thesis'), admin_url('options-discussion.php')),
				'default' => $thesis->api->get_option('comments_per_page'))));*/
	}

	public function preload() {
		global $thesis;
		if (comments_open())
			wp_enqueue_script('comment-reply'); #wp
		if ($this->override = apply_filters('comments_template', false))
			return;
		if (!empty($GLOBALS['wp_query']->comments_by_type['comment']) && !(bool) $thesis->api->get_option('thread_comments')) {
			$GLOBALS['t_comment_counter'] = array();
			foreach ($GLOBALS['wp_query']->comments_by_type['comment'] as $number => $comment)
				$GLOBALS['t_comment_counter'][$comment->comment_ID] = $number + 1;
		}
	}

	public function html($args = array()) {
		global $thesis, $post;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", ($this->tab_depth = !empty($depth) ? $depth : 0));
		comments_template(false, true);
		if (!empty($this->override))
			return;
		elseif (post_password_required()) {
			echo "$tab\t<p class=\"password_required\">", __('This post is password protected. Enter the password to view comments.', 'thesis'), "</p>\n";
			return;
		}
		if (($comments = wp_count_comments($post->ID)) && is_object($comments) && !empty($comments->approved) && $comments->approved > 0) {
			$html = !empty($this->options['html']) ? esc_attr($this->options['html']) : 'ul';
			$this->child_html = in_array($html, array('ul', 'ol')) ? 'li' : 'div';
			$hook = trim(esc_attr(!empty($this->options['_id']) ?
				$this->options['_id'] : (!empty($this->options['hook']) ?
				$this->options['hook'] : '')));
			$args = array(
				'walker' => new thesis_comment_walker,
				'callback' => array($this, 'start'),
				'type' => 'comment',
				'style' => $html);
/*			if ((bool) $thesis->api->get_option('page_comments'))
				$args['per_page'] = (int) !empty($this->options['per_page']) ? $this->options['per_page'] : $thesis->api->get_option('comments_per_page');*/
			if (!empty($hook))
				$thesis->api->hook("hook_before_$hook");
			echo "$tab<$html class=\"comment_list\">\n";
			if (!in_array($html, array('ul', 'ol')) && !empty($hook))
				$thesis->api->hook("hook_top_$hook");
			wp_list_comments($args);
			if (!in_array($html, array('ul', 'ol')) && !empty($hook))
				$thesis->api->hook("hook_bottom_$hook");
			echo "$tab</$html>\n";
			if (!empty($hook))
				$thesis->api->hook("hook_after_$hook");
		}
	}

	public function start($comment, $args, $depth) {
		global $thesis;
		$GLOBALS['comment'] = $comment;
		echo
			str_repeat("\t", $this->tab_depth + 1),
			"<$this->child_html class=\"", esc_attr(implode(' ', get_comment_class())), "\" id=\"comment-", get_comment_ID(), "\">\n";
		$this->rotator(array('depth' => $this->tab_depth + 2));
	}
}

class thesis_comment_walker extends Walker_Comment {
	public function start_lvl(&$out, $depth = 0, $args = array()) {
		if (in_array($args['style'], array('ul', 'ol', 'div')))
			$out .= "<". esc_attr(strtolower($args['style'])). " class=\"children\">\n";
	}

	public function end_lvl(&$out, $depth = 0, $args = array()) {
		if (in_array($args['style'], array('ul', 'ol', 'div')))
			$out .= "</". esc_attr(strtolower($args['style'])). ">\n";
	}
}

class thesis_comment_author extends thesis_box {
	protected function translate() {
		$this->title = __('Comment Author', 'thesis');
	}

	public function html($args = array()) {
		extract($args = is_array($args) ? $args : array());
		echo
			str_repeat("\t", !empty($depth) ? $depth : 0),
			"<span class=\"comment_author\">",
			(apply_filters("{$this->_class}_link", true) ?
				get_comment_author_link() :
				get_comment_author()),
			"</span>\n";
	}
}

class thesis_comment_avatar extends thesis_box {
	protected function translate() {
		$this->title = __('Comment Avatar', 'thesis');
	}

	public function html($args = array()) {
		extract($args = is_array($args) ? $args : array());
		$avatar = get_avatar(get_comment_author_email(), apply_filters("{$this->_class}_size", 88));
		$author_url = get_comment_author_url();
		echo
			str_repeat("\t", !empty($depth) ? $depth : 0),
			"<span class=\"avatar\">",
			apply_filters($this->_class, empty($author_url) || $author_url == 'http://' ?
				$avatar :
				"<a href=\"$author_url\" rel=\"nofollow\">$avatar</a>"),
			"</span>\n";
	}
}

class thesis_comment_date extends thesis_box {
	protected function translate() {
		$this->title = __('Comment Date', 'thesis');
	}

	protected function html_options() {
		global $thesis;
		$html = $thesis->api->html_options();
		$html['class']['tooltip'] = sprintf(__('This box already contains a %1$as of <code>comment_date</code>. If you&#8217;d like to supply another %1$s, you can do that here.%2$s', 'thesis'), $thesis->api->base['class'], __($thesis->api->strings['class_note'], 'thesis'));
		unset($html['id']);
		return array_merge($html, array(
			'format' => array(
				'type' => 'text',
				'width' => 'short',
				'code' => true,
				'label' => __('Date Format', 'thesis'),
				'tooltip' => __($thesis->api->strings['date_tooltip'], 'thesis'),
				'default' => $thesis->api->get_option('date_format'). ', '. $thesis->api->get_option('time_format'))));
	}

	public function html($args = array()) {
		global $thesis;
		extract($args = is_array($args) ? $args : array());
		echo
			str_repeat("\t", !empty($depth) ? $depth : 0),
			'<span class="comment_date', (!empty($this->options['class']) ? ' '. trim(esc_attr($this->options['class'])) : ''), '">',
			apply_filters(
				$this->_class,
				get_comment_date(strip_tags(apply_filters("{$this->_class}_format",
					!empty($this->options['format']) ?
						$this->options['format'] :
						$thesis->api->get_option('date_format'). ', '. $thesis->api->get_option('time_format')))),
				get_comment_ID()),
			"</span>\n";
	}
}

class thesis_comment_number extends thesis_box {
	protected function translate() {
		$this->title = __('Comment Number', 'thesis');
	}

	public function html($args = array()) {
		global $thesis;
		if ((bool) $thesis->api->get_option('thread_comments')) return;
		extract($args = is_array($args) ? $args : array());
		$id = get_comment_ID();
		$number = '<span class="comment_number">'. (int) $GLOBALS['t_comment_counter'][$id]. '</span>';
		echo
			str_repeat("\t", !empty($depth) ? $depth : 0),
			apply_filters($this->_class, $number, $id), "\n";
	}
}

class thesis_comment_permalink extends thesis_box {
	protected function translate() {
		$this->title = __('Comment Permalink', 'thesis');
		$this->link = __('Link', 'thesis');
	}

	public function html($args = array()) {
		global $thesis;
		extract($args = is_array($args) ? $args : array());
		echo
			str_repeat("\t", !empty($depth) ? $depth : 0),
			'<a class="comment_permalink" href="#comment-', get_comment_ID(), "\" title=\"", trim($thesis->api->efh(__($thesis->api->strings['comment_permalink'], 'thesis'))), "\" rel=\"nofollow\">",
			trim($thesis->api->efh(apply_filters($this->_class, $this->link))),
			"</a>\n";
	}
}

class thesis_comment_edit extends thesis_box {
	protected function translate() {
		$this->title = __('Edit Comment Link', 'thesis');
	}

	public function html($args = array()) {
		global $thesis;
		$url = get_edit_comment_link();
		if (empty($url)) return;
		extract($args = is_array($args) ? $args : array());
		// Note: strtolower should not be used here but will be kept for compatibility purposes.
		echo
			str_repeat("\t", !empty($depth) ? $depth : 0),
			"<a class=\"comment_edit\" href=\"$url\" rel=\"nofollow\">",
			trim($thesis->api->efh(apply_filters($this->_class, strtolower(__($thesis->api->strings['edit'], 'thesis'))))),
			"</a>\n";
	}
}

class thesis_comment_text extends thesis_box {
	protected function translate() {
		$this->title = __('Comment Text', 'thesis');
	}

	protected function html_options() {
		global $thesis;
		$html = $thesis->api->html_options();
		unset($html['id']);
		return $html;
	}

	protected function construct() {
		global $thesis;
		$thesis->wp->filter($this->_class, array(
			'wptexturize' => false,
			'convert_chars' => false,
			'make_clickable' => 9,
			'force_balance_tags' => 25,
			'convert_smilies' => 20,
			'wpautop' => 30));
	}

	public function html($args = array()) {
		global $thesis;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", !empty($depth) ? $depth : 0);
		echo
			"$tab<div class=\"comment_text", (!empty($this->options['class']) ? ' '. trim(esc_attr($this->options['class'])) : ''), "\" id=\"comment-body-", get_comment_ID(), "\">\n",
			($GLOBALS['comment']->comment_approved == '0' ?
			"$tab\t<p class=\"comment_moderated\">". __('Your comment is awaiting moderation.', 'thesis'). "</p>\n" : ''),
			apply_filters($this->_class, get_comment_text()),
			"$tab</div>\n";
	}
}

class thesis_comment_reply extends thesis_box {
	protected function translate() {
		$this->title = __('Comment Reply Link', 'thesis');
		$this->text = __('Reply', 'thesis');
	}

	public function html($args = array()) {
		global $thesis;
		if (!$thesis->api->get_option('thread_comments')) return;
		extract($args = is_array($args) ? $args : array());
		echo
			str_repeat("\t", !empty($depth) ? $depth : 0),
			get_comment_reply_link(apply_filters("{$this->_class}_args", array(
				'add_below' => 'comment-body',
				'respond_id' => apply_filters('comment_form_respond_id', 'commentform'),
				'reply_text' => trim($thesis->api->efh(apply_filters($this->_class, $this->text))),
				'login_text' => __('Log in to reply', 'thesis'),
				'depth' => $GLOBALS['comment_depth'],
				'before' => apply_filters("{$this->_class}_before", ''),
				'after' => apply_filters("{$this->_class}_after", ''),
				'max_depth' => (int) $thesis->api->get_option('thread_comments_depth')))), "\n";
	}
}

class thesis_comments_nav extends thesis_box {
	public $templates = array('single', 'page');

	protected function translate() {
		$this->title = $this->name = __('Comment Navigation', 'thesis');
		$this->previous = __('Previous Comments', 'thesis');
		$this->next = __('Next Comments', 'thesis');
	}

	protected function html_options() {
		global $thesis;
		$html = $thesis->api->html_options();
		$html['class']['tooltip'] = sprintf(__('This box already contains a %1$s of <code>comment_nav</code>. If you&#8217;d like to supply another %1$s, you can do that here.%2$s', 'thesis'), $thesis->api->base['class'], __($thesis->api->strings['class_note'], 'thesis'));
		unset($html['id']);
		return $html;
	}

	public function html($args = array()) {
		global $thesis;
		if (!$thesis->api->get_option('page_comments')) return;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", !empty($depth) ? $depth : 0);
		$previous_link = get_previous_comments_link(trim($thesis->api->efh(
			apply_filters("{$this->_class}_previous", $this->previous))));
		$next_link = get_next_comments_link(trim($thesis->api->efh(
			apply_filters("{$this->_class}_next", $this->next))));
		if (empty($previous_link) && empty($next_link)) return;
		echo
			"$tab<div class=\"comment_nav", (!empty($this->options['class']) ? ' '. trim(esc_attr($this->options['class'])) : ''), "\">\n",
			(!empty($next_link) ?
			"$tab\t<span class=\"next_comments\">$next_link</span>\n" : ''),
			(!empty($previous_link) ?
			"$tab\t<span class=\"previous_comments\">$previous_link</span>\n" : ''),
			"$tab</div>\n";
	}
}

class thesis_wp_comment_form extends thesis_box {
	public $templates = array('single', 'page');

	protected function translate() {
		$this->title = __('Comment Form (WP)', 'thesis');
	}

	public function html($args = array()) {
		comment_form(apply_filters('wp_comment_form_args', array()));
	}
}

class thesis_comment_form extends thesis_box {
	public $type = 'rotator';
	public $dependents = array(
		'thesis_comment_form_title',
		'thesis_comment_form_cancel',
		'thesis_comment_form_name',
		'thesis_comment_form_email',
		'thesis_comment_form_url',
		'thesis_comment_form_comment',
		'thesis_comment_form_submit');
	public $children = array(
		'thesis_comment_form_title',
		'thesis_comment_form_cancel',
		'thesis_comment_form_name',
		'thesis_comment_form_email',
		'thesis_comment_form_url',
		'thesis_comment_form_comment',
		'thesis_comment_form_submit');
	public $templates = array('single', 'page');

	protected function translate() {
		$this->title = $this->name = __('Comment Form (Thesis)', 'thesis');
	}

	public function html($args = array()) {
		global $thesis, $user_ID, $post;
		if (!comments_open()) return;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", $depth = !empty($depth) ? $depth : 0);
		$hook = trim(esc_attr(!empty($this->options['_id']) ? $this->options['_id'] : 'comment_form'));
		if ($thesis->api->get_option('comment_registration') && !!!$user_ID) {
			echo
				"$tab<p class=\"login_alert\">",
				__('You must log in to post a comment.', 'thesis'),
				" <a href=\"", wp_login_url(get_permalink()), "\" rel=\"nofollow\">", __('Log in now.', 'thesis'), "</a></p>\n";
			$thesis->api->hook('comment_form_must_log_in_after');
		}
		else {
			if (!empty($hook))
				$thesis->api->hook("hook_before_$hook");
			$thesis->api->hook('comment_form_before');
			echo
				"$tab<div id=\"commentform\">\n",
				"$tab\t<form method=\"post\" action=\"", site_url('wp-comments-post.php'), "\">\n";
			if (!empty($hook))
				$thesis->api->hook("hook_top_$hook");
			$thesis->api->hook('comment_form_top');
			$thesis->api->hook('comment_form_before_fields');
			$this->rotator(array_merge($args, array('depth' => $depth + 2, 'req' => $thesis->api->get_option('require_name_email'))));
			$thesis->api->hook('comment_form_after_fields');
			if (!empty($hook))
				$thesis->api->hook("hook_bottom_$hook");
			comment_id_fields();
			$thesis->api->hook('comment_form', $post->ID);
			echo
				"$tab\t</form>\n",
				"$tab</div>\n";
			$thesis->api->hook('comment_form_after');
			if (!empty($hook))
				$thesis->api->hook("hook_after_$hook");
		}
	}
}

class thesis_comment_form_title extends thesis_box {
	protected function translate() {
		$this->title = __('Comment Form Title', 'thesis');
		$this->leave = __('Leave a Comment', 'thesis');
	}

	public function html($args = array()) {
		global $thesis;
		extract($args = is_array($args) ? $args : array());
		echo
			str_repeat("\t", !empty($depth) ? $depth : 0),
			"<p class=\"comment_form_title\">",
			trim($thesis->api->efh(apply_filters($this->_class, $this->leave))),
			"</p>\n";
	}
}

class thesis_comment_form_name extends thesis_box {
	protected function translate() {
		$this->title = __('Name Input', 'thesis');
	}

	protected function html_options() {
		global $thesis;
		return array(
			'label' => array(
				'type' => 'checkbox',
				'options' => array(
					'show' => __($thesis->api->strings['show_label'], 'thesis')),
				'default' => array(
					'show' => true)),
			'placeholder' => array(
				'type' => 'text',
				'width' => 'medium',
				'label' => __($thesis->api->strings['placeholder'], 'thesis'),
				'tooltip' => __($thesis->api->strings['placeholder_tooltip'], 'thesis')));
	}

	public function html($args = array()) {
		global $thesis, $user_ID, $user_identity, $req;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", !empty($depth) ? $depth : 0);
		$commenter = wp_get_current_commenter();
		if (!!$user_ID) // This should probably be moved to the comment form box to safeguard against unwanted display outcomes
			echo
				"$tab<p>", __('Logged in as', 'thesis'), ' <a href="', admin_url('profile.php'), "\" rel=\"nofollow\">$user_identity</a>. ",
				'<a href="', wp_logout_url(get_permalink()), '" rel="nofollow">', __('Log out &rarr;', 'thesis'), "</a></p>\n";
		else
			echo
				"$tab<p id=\"comment_form_name\">\n",
				(!empty($this->options['label']) && !empty($this->options['label']['show']) ? '' :
				"$tab\t<label for=\"author\">". __($thesis->api->strings['name'], 'thesis'). (!!$req ? " <span class=\"required\" title=\"". trim($thesis->api->efh(__($thesis->api->strings['required'], 'thesis'))). "\">*</span>" : ''). "</label>\n"),
				"$tab\t<input type=\"text\" id=\"author\" class=\"input_text\" name=\"author\" value=\"", trim($thesis->api->ef0($commenter['comment_author'])), '" ',
				(!empty($this->options['placeholder']) ?
				'placeholder="'. trim($thesis->api->ef0($this->options['placeholder'])). '" ' : ''),
				'tabindex="1"', ($req ? ' aria-required="true"' : ''), " />\n",
				"$tab</p>\n";
	}
}

class thesis_comment_form_email extends thesis_box {
	protected function translate() {
		$this->title = __('Email Input', 'thesis');
	}

	protected function html_options() {
		global $thesis;
		return array(
			'label' => array(
				'type' => 'checkbox',
				'options' => array(
					'show' => __($thesis->api->strings['show_label'], 'thesis')),
				'default' => array(
					'show' => true)),
			'placeholder' => array(
				'type' => 'text',
				'width' => 'medium',
				'label' => __($thesis->api->strings['placeholder'], 'thesis'),
				'tooltip' => __($thesis->api->strings['placeholder_tooltip'], 'thesis')));
	}

	public function html($args = array()) {
		global $thesis, $user_ID, $req;
		if (!!$user_ID) return;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", !empty($depth) ? $depth : 0);
		$commenter = wp_get_current_commenter();
		echo
			"$tab<p id=\"comment_form_email\">\n",
			(!empty($this->options['label']) && !empty($this->options['label']['show']) ? '' :
			"$tab\t<label for=\"email\">". trim($thesis->api->efh(__($thesis->api->strings['email'], 'thesis'))). "". (!!$req ? " <span class=\"required\" title=\"". trim($thesis->api->efh(__($thesis->api->strings['required'], 'thesis'))). "\">*</span>" : ''). "</label>\n"),
			"$tab\t<input type=\"text\" id=\"email\" class=\"input_text\" name=\"email\" value=\"", trim($thesis->api->ef($commenter['comment_author_email'])), '" ',
			(!empty($this->options['placeholder']) ?
			'placeholder="'. trim($thesis->api->efh($this->options['placeholder'])). '" ' : ''),
			'tabindex="2"', (!!$req ? ' aria-required="true"' : ''), " />\n",
			"$tab</p>\n";
	}
}

class thesis_comment_form_url extends thesis_box {
	protected function translate() {
		global $thesis;
		$this->title = sprintf(__('%s Input', 'thesis'), $thesis->api->base['url']);
	}

	protected function html_options() {
		global $thesis;
		return array(
			'label' => array(
				'type' => 'checkbox',
				'options' => array(
					'show' => __($thesis->api->strings['show_label'], 'thesis')),
				'default' => array(
					'show' => true)),
			'placeholder' => array(
				'type' => 'text',
				'width' => 'medium',
				'label' => __($thesis->api->strings['placeholder'], 'thesis'),
				'tooltip' => __($thesis->api->strings['placeholder_tooltip'], 'thesis')));
	}

	public function html($args = array()) {
		global $thesis, $user_ID;
		if (!!$user_ID) return;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", !empty($depth) ? $depth : 0);
		$commenter = wp_get_current_commenter();
		echo
			"$tab<p id=\"comment_form_url\">\n",
			(!empty($this->options['label']) && !empty($this->options['label']['show']) ? '' :
			"$tab\t<label for=\"url\">". trim($thesis->api->efh(__($thesis->api->strings['website'], 'thesis'))). "</label>\n"),
			"$tab\t<input type=\"text\" id=\"url\" class=\"input_text\" name=\"url\" value=\"", trim(esc_attr($commenter['comment_author_url'])), '" ',
			(!empty($this->options['placeholder']) ?
			'placeholder="'. trim(esc_attr($this->options['placeholder'])). '" ' : ''),
			"tabindex=\"3\" />\n",
			"$tab</p>\n";
	}
}

class thesis_comment_form_comment extends thesis_box {
	protected function translate() {
		$this->title = __('Comment Input', 'thesis');
	}

	protected function html_options() {
		global $thesis;
		return array(
			'label' => array(
				'type' => 'checkbox',
				'options' => array(
					'show' => __($thesis->api->strings['show_label'], 'thesis')),
				'default' => array(
					'show' => true)),
			'rows' => array(
				'type' => 'text',
				'width' => 'tiny',
				'label' => __('Number of Rows in Comment Input Box', 'thesis'),
				'tooltip' => __('The number of rows determines the height of the comment input box. The higher the number, the taller the input box.', 'thesis'),
				'default' => 6));
	}

	public function html($args = array()) {
		global $thesis;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", !empty($depth) ? $depth : 0);
		$rows = !empty($this->options['rows']) && is_numeric($this->options['rows']) ? (int) $this->options['rows'] : 6;
		echo
			"$tab<p id=\"comment_form_comment\">\n",
			(!empty($this->options['label']) && !empty($this->options['label']['show']) ? '' :
			"$tab\t<label for=\"comment\">". trim($thesis->api->efh(__($thesis->api->strings['comment'], 'thesis'))). "</label>\n"),
			"$tab\t<textarea name=\"comment\" id=\"comment\" class=\"input_text\" tabindex=\"4\" rows=\"$rows\"></textarea>\n",
			"$tab</p>\n";
	}
}

class thesis_comment_form_submit extends thesis_box {
	protected function translate() {
		$this->title = __('Submit Button', 'thesis');
	}

	public function html($args = array()) {
		global $thesis;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", !empty($depth) ? $depth : 0);
		$value = trim($thesis->api->ef0(apply_filters($this->_class, __($thesis->api->strings['submit'], 'thesis'))));
		echo
			apply_filters('comment_form_submit_field',
				"$tab<p id=\"comment_form_submit\">\n".
				"$tab\t<input type=\"submit\" id=\"submit\" class=\"input_submit\" name=\"submit\" tabindex=\"5\" value=\"$value\" />\n".
				"$tab</p>\n", $tab);
	}
}

class thesis_comment_form_cancel extends thesis_box {
	protected function translate() {
		$this->title = __('Cancel Reply Link', 'thesis');
		$this->cancel = __('Cancel reply', 'thesis');
	}

	public function html($args = array()) {
		global $thesis;
		extract($args = is_array($args) ? $args : array());
		echo str_repeat("\t", !empty($depth) ? $depth : 0);
		cancel_comment_reply_link(trim($thesis->api->efh(apply_filters($this->_class, $this->cancel))));
		echo "\n";
	}
}

class thesis_trackbacks extends thesis_box {
	public $type = 'rotator';
	public $dependents = array(
		'thesis_comment_author',
		'thesis_comment_date',
		'thesis_comment_text');
	public $children = array(
		'thesis_comment_author',
		'thesis_comment_date',
		'thesis_comment_text');
	public $templates = array('single', 'page');

	protected function translate() {
		$this->title = $this->name = __('Trackbacks', 'thesis');
	}

	public function preload() {
		if (!class_exists('thesis_comments_dummy'))
			comments_template('/comments.php', true);
	}

	public function html($args = array()) {
		global $thesis, $wp_query;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", $depth = !empty($depth) ? $depth : 0);
		if (empty($wp_query->comments_by_type)) // separate the comments and put them in wp_query if they aren't there already
			$wp_query->comments_by_type = &separate_comments($wp_query->comments);
		foreach ($wp_query->comments as $a)
			if ($a->comment_type == 'pingback' || $a->comment_type == 'trackback')
				$b[] = $a;
		if (empty($b)) return;
		$hook = trim(esc_attr(!empty($this->options['_id']) ? $this->options['_id'] : ''));
		/*---:[ begin HTML output ]:---*/
		if (!empty($hook))
			$thesis->api->hook("hook_before_$hook");
		echo "$tab<ul id=\"trackback_list\">\n";
		foreach ($b as $t) {
			$GLOBALS['comment'] = $t;
			echo "$tab\t<li>";
			$this->rotator(array_merge($args, array('depth' => $depth + 1, 't' => $t)));
			echo "</li>\n";
		}
		echo "$tab</ul>\n";
		if (!empty($hook))
			$thesis->api->hook("hook_after_$hook");
	}
}

class thesis_previous_post_link extends thesis_box {
	public $templates = array('single');

	protected function translate() {
		$this->title = __('Prev/Next: Previous Post Link', 'thesis');
	}

	protected function html_options() {
		global $thesis;
		$html = $thesis->api->html_options(array('div' => 'div', 'span' => 'span', 'p' => 'p'), 'span');
		unset($html['id'], $html['class']);
		return $html;
	}

	protected function options() {
		global $thesis;
		return array(
			'intro' => array(
				'type' => 'text',
				'width' => 'medium',
				'label' => __($thesis->api->strings['intro_text'], 'thesis'),
				'placeholder' => __('Previous Post:', 'thesis')));
	}

	public function html($args = array()) {
		global $thesis, $wp_query;
		if (!$wp_query->is_single || !get_previous_post()) return;
		extract($args = is_array($args) ? $args : array());
		$html = !empty($this->options['html']) ? $this->options['html'] : 'span';
		echo
			str_repeat("\t", !empty($depth) ? $depth : 0),
			"<$html class=\"previous_post\">";
			previous_post_link(
				trim($thesis->api->efh(
					apply_filters($this->_class, !empty($this->options['intro']) ? "{$this->options['intro']} " : ''). '%link')),
				'%title');
		echo
			"</$html>\n";
	}
}

class thesis_next_post_link extends thesis_box {
	public $templates = array('single');

	protected function translate() {
		$this->title = __('Prev/Next: Next Post Link', 'thesis');
	}

	protected function html_options() {
		global $thesis;
		$html = $thesis->api->html_options(array('div' => 'div', 'span' => 'span', 'p' => 'p'), 'span');
		unset($html['id'], $html['class']);
		return $html;
	}

	protected function options() {
		global $thesis;
		return array(
			'intro' => array(
				'type' => 'text',
				'width' => 'medium',
				'label' => __($thesis->api->strings['intro_text'], 'thesis'),
				'placeholder' => __('Next Post:', 'thesis')));
	}

	public function html($args = array()) {
		global $thesis, $wp_query;
		if (!$wp_query->is_single || !get_next_post()) return;
		extract($args = is_array($args) ? $args : array());
		$html = !empty($this->options['html']) ? esc_attr($this->options['html']) : 'span';
		echo
			str_repeat("\t", !empty($depth) ? $depth : 0),
			"<$html class=\"next_post\">";
			next_post_link(
				trim($thesis->api->efh(
					apply_filters($this->_class, !empty($this->options['intro']) ? "{$this->options['intro']} " : ''). '%link')),
				'%title');
		echo
			"</$html>\n";
	}
}

class thesis_previous_posts_link extends thesis_box {
	public $templates = array('home', 'archive');

	protected function translate() {
		$this->title = __('Prev/Next: Previous Posts Link', 'thesis');
		$this->previous = __('Previous Posts', 'thesis');
	}

	protected function html_options() {
		global $thesis;
		$html = $thesis->api->html_options(array('div' => 'div', 'span' => 'span', 'p' => 'p'), 'span');
		unset($html['id'], $html['class']);
		return $html;
	}

	protected function options() {
		global $thesis;
		return array(
			'text' => array(
				'type' => 'text',
				'width' => 'medium',
				'label' => __($thesis->api->strings['link_text'], 'thesis'),
				'placeholder' => $this->previous,
				'description' => __($thesis->api->strings['no_html'], 'thesis')));
	}

	public function html($args = array()) {
		global $thesis, $wp_query; #wp
		if (!(($wp_query->is_home || $wp_query->is_archive || $wp_query->is_search) && $wp_query->max_num_pages > 1 && ((!empty($wp_query->query_vars['paged']) ? $wp_query->query_vars['paged'] : 1) < $wp_query->max_num_pages))) return;
		extract($args = is_array($args) ? $args : array());
		$html = !empty($this->options['html']) ? $this->options['html'] : 'span';
		echo
			str_repeat("\t", !empty($depth) ? $depth : 0),
			"<$html class=\"previous_posts\">",
			get_next_posts_link(
				trim($thesis->api->efh(apply_filters($this->_class, !empty($this->options['text']) ?
					$this->options['text'] :
					$this->previous)))),
			"</$html>\n";
	}
}

class thesis_next_posts_link extends thesis_box {
	public $templates = array('home', 'archive');

	protected function translate() {
		$this->title = __('Prev/Next: Next Posts Link', 'thesis');
		$this->next = __('Next Posts', 'thesis');
	}

	protected function html_options() {
		global $thesis;
		$html = $thesis->api->html_options(array('div' => 'div', 'span' => 'span', 'p' => 'p'), 'span');
		unset($html['id'], $html['class']);
		return $html;
	}

	protected function options() {
		global $thesis;
		return array(
			'text' => array(
				'type' => 'text',
				'width' => 'medium',
				'label' => __($thesis->api->strings['link_text'], 'thesis'),
				'placeholder' => $this->next,
				'description' => __($thesis->api->strings['no_html'], 'thesis')));
	}

	public function html($args = array()) {
		global $thesis, $wp_query; #wp
		if (!(($wp_query->is_home || $wp_query->is_archive || $wp_query->is_search) && $wp_query->max_num_pages > 1 && ((!empty($wp_query->query_vars['paged']) ? $wp_query->query_vars['paged'] : 1) > 1))) return;
		extract($args = is_array($args) ? $args : array());
		$html = !empty($this->options['html']) ? $this->options['html'] : 'span';
		echo
			str_repeat("\t", !empty($depth) ? $depth : 0),
			"<$html class=\"next_posts\">",
			get_previous_posts_link(
				trim($thesis->api->efh(apply_filters($this->_class, !empty($this->options['text']) ?
					$this->options['text'] :
					$this->next)))),
			"</$html>\n";
	}
}

class thesis_wp_widgets extends thesis_box {
	private $tag = false;

	protected function translate() {
		$this->title = $this->name = __('Widgets', 'thesis');
	}

	protected function html_options() {
		global $thesis;
		$html = $thesis->api->html_options();
		unset($html['id']);
		return array_merge($html, array(
			'title_tag' => array(
				'type' => 'select',
				'label' => sprintf(__('Widget Title %s', 'thesis'), __($thesis->api->strings['html_tag'], 'thesis')),
				'options' => array(
					'h2' => 'h2',
					'h3' => 'h3',
					'h4' => 'h4',
					'h5' => 'h5',
					'p' => 'p'),
				'default' => 'p'),
			'div_wrap' => array(
				'type' => 'checkbox',
				'label' => __('Show Wrapping Element', 'thesis'),
				'options' => array('show' => __('Wrap widget with an HTML element.', 'thesis')),
				'dependents' => array('show'),
				'default' => array('show' => false)),
			'div' => array(
				'type' => 'select',
				'label' => __('Choose Wrapping Element', 'thesis'),
				'options' => array('div' => 'div', 'section' => 'section'),
				'parent' => array('div_wrap' => 'show')),
			'div_id' => array(
				'type' => 'text',
				'width' => 'medium',
				'label' => __('Wrapping Element HTML id', 'thesis'),
				'parent' => array('div_wrap' => 'show')),
			'div_class' => array(
				'type' => 'text',
				'width' => 'medium',
				'label' => __('Wrapping Element HTML class', 'thesis'),
				'parent' => array('div_wrap' => 'show'))));
	}

	protected function construct() {
		global $thesis;
		$this->tag = ($html = trim(esc_attr(apply_filters("{$this->_class}_html", 'div')))) && in_array($html, array('div', 'li', 'article', 'section')) ?
			$html : 'div';
		$title_tag = trim(esc_attr(($title_html = apply_filters("{$this->_class}_title_html", !empty($this->options['title_tag']) ? $this->options['title_tag'] : 'p')) && in_array($title_html, array('h1', 'h2', 'h3', 'h4', 'h5', 'p')) ?
			$title_html : 'p'));
		register_sidebar(array(
			'id' => $this->_id,
			'name' => $this->name. (!$this->_display() ? ' ('. __('hidden', 'thesis'). ')' : ''),
			'description' => !$this->_display() ?
				'<strong>'. __('Note', 'thesis'). ':</strong> '.
				sprintf(__('This widget area is currently hidden by the <a href="%s">Display Options</a>.', 'thesis'), admin_url('admin.php?page=thesis&canvas='. $thesis->skin->_class. '__content')) : '',
			'before_widget' => "<$this->tag class=\"widget %2\$s". (!empty($this->options['class']) ? ' '. trim(esc_attr($this->options['class'])) : ''). '" id="%1$s">',
			'after_widget' => "</$this->tag>",
			'before_title' => "<$title_tag class=\"widget_title\">",
			'after_title' => "</$title_tag>"));
	}

	public function html($args = array()) {
		global $thesis;
		if (!is_user_logged_in() && !is_active_sidebar($this->_id))
			return;
		extract($args = is_array($args) ? $args : array());
		$tab = str_repeat("\t", !empty($depth) ? $depth : 0);
		$hook = !empty($this->options['_id']) ? trim(esc_attr($this->options['_id'])) : false;
		if (!empty($this->options['div_wrap']) && !empty($this->options['div_wrap']['show']))
			echo
				"$tab<", esc_attr($this->options['div']),
				(!empty($this->options['div_id']) ? ' id="'. trim(esc_attr($this->options['div_id'])). '" ' : ''),
				' class="', (!empty($this->options['div_class']) ? trim(esc_attr($this->options['div_class'])) : 'widget_wrap'), '"',
				">";
		if ($list = $this->tag == 'li' ? true : false)
			echo "$tab\t<ul". (($class = apply_filters("{$this->_class}_ul_class", 'widget_list')) ? ' class="'. trim(esc_attr($class)). '"' : ''). ">\n";
		if (!empty($hook))
			$thesis->api->hook("hook_first_widget_$hook");
		if (!dynamic_sidebar($this->_id) && is_user_logged_in() && ($text = apply_filters("{$this->_class}_{$hook}_default_text", sprintf(__('This is a Widgets Box named <strong>%1$s</strong>. <a href="%2$s">Add a widget here</a>', 'thesis'), $this->name, admin_url('widgets.php')))))
			echo
				"$tab\t\t<$this->tag class=\"widget", (!empty($this->options['class']) ? ' '. trim(esc_attr($this->options['class'])) : ''), "\">\n",
				"$tab\t\t\t<p>$text</p>\n",
				"$tab\t\t</$this->tag>\n";
		if (!empty($hook))
			$thesis->api->hook("hook_last_widget_$hook");
		if ($list)
			echo "\n$tab\t</ul>\n";
		if (!empty($this->options['div_wrap']) && !empty($this->options['div_wrap']['show']))
			echo "</", esc_attr($this->options['div']), ">\n";
	}
}

class thesis_wp_admin extends thesis_box {
	protected function translate() {
		global $thesis;
		$this->title = sprintf(__('%s Admin Link', 'thesis'), $thesis->api->base['wp']);
	}

	public function html($args = array()) {
		global $thesis;
		extract($args = is_array($args) ? $args : array());
		echo str_repeat("\t", !empty($depth) ? $depth : 0),
			"<p><a href=\"", admin_url(), '">', sprintf(__('%s Admin', 'thesis'), $thesis->api->base['wp']), "</a></p>\n"; #wp
	}
}