<?php
/*
Copyright 2017 DIYthemes, LLC. Patent pending. All rights reserved.
License: DIYthemes Software License Agreement
License URI: https://diythemes.com/thesis/rtfm/software-license-agreement/
*/
class thesis_woocommerce {
	public $post_class = false;			// The active Skin can provide a post class for valet formatting purposes
	public $exclude = array(
		'post_types' => array(
			'payment_retry',		// WooCommerce Subscriptions (WCS)
			'product_variation',
			'scheduled-action',
			'shop_coupon',
			'shop_order',
			'shop_order_refund',
			'shop_subscription'),	// WCS
		'taxonomies' => array(
			'action-group',
			'pa_color',
			'product_type',
			'product_visibility',
			'product_shipping_class'));

	public function __construct() {
		if (!function_exists('is_woocommerce')) return;
		add_theme_support('woocommerce');
		add_filter('thesis_exclude_template_types', array($this, 'exclude_template_types'));
		add_filter('thesis_exclude_query_types', array($this, 'exclude_query_types'));
		add_action('template_redirect', array($this, 'woocommerce'));
	}

	public function exclude_template_types($template_types) {
		foreach (array_merge($this->exclude['post_types'], $this->exclude['taxonomies']) as $type)
			$template_types[] = $type;
		return $template_types;
	}

	public function exclude_query_types($query_types) {
		foreach ($this->exclude['post_types'] as $type)
			$query_types[] = $type;
		return $query_types;
	}

	public function woocommerce() {
		if (apply_filters('thesis_woocommerce_gallery_zoom', true))
			add_theme_support('wc-product-gallery-zoom');
		if (apply_filters('thesis_woocommerce_gallery_lightbox', true))
			add_theme_support('wc-product-gallery-lightbox');
		if (apply_filters('thesis_woocommerce_gallery_slider', true))
			add_theme_support('wc-product-gallery-slider');
/*
		If the current page is not a WooCommerce page, optionally remove performance-crushing styles
		and scripts and then exit the compatibility script.
*/
		if (!(is_woocommerce() || is_cart() || is_checkout() || is_account_page())) {
			if (apply_filters('thesis_woocommerce_optimized', false)) {
				add_filter('woocommerce_enqueue_styles', '__return_false');
				add_action('wp_enqueue_scripts', array($this, 'strip_scripts'), 11);
			}
			return;
		}
/*
		WooCommerce delivers pages in two ways: [1] from WooCommerce templates or [2] via shortcodes.
		Some compatibility items need to be applied to both types of pages, while other items only apply
		to pages delivered from WooCommerce templates.
		
		The following HTML body and Post Box class filters apply to both types of pages:
*/
		global $thesis;
		add_filter('thesis_use_wp_body_classes', '__return_true');
		if (is_object($thesis) && is_object($thesis->skin) && !empty($thesis->skin->functionality)) {
			$this->post_class = !empty($thesis->skin->functionality['formatting_class']) ?
				trim(esc_attr($thesis->skin->functionality['formatting_class'])) : (!empty($thesis->skin->functionality['editor_grt']) ?
				'grt' : false);
			if (!empty($this->post_class))
				add_filter('post_class', array($this, 'post_class'));
		}
/*
		And these items only apply to WooCommerce template pages, which simply replace everything inside
		the Thesis WP Loop Box:
*/
		if (!is_woocommerce()) return;
		// Notify Thesis that WooCommerce will override the WP Loop
		add_filter('thesis_use_custom_loop', '__return_true');
		// Replace the WP Loop contents on WooCommerce pages
		add_action('thesis_custom_loop', 'woocommerce_content');
		// Reject the Thesis comments template in favor of WooCommerce
		add_filter('thesis_comments_template', '__return_false');
/*
		WooCommerce archive pages will have results, pagination, and sorting in both top and bottom
		locations (and in that order per WooCommerce CSS defaults).
*/
		add_action('woocommerce_before_shop_loop', 'woocommerce_pagination', 35);
		add_action('woocommerce_after_shop_loop', 'woocommerce_catalog_ordering', 9);
		add_action('woocommerce_after_shop_loop', 'woocommerce_result_count', 5);
		add_filter('thesis_html_body_class', array($this, 'body_class'));
	}

	public function strip_scripts() {
		wp_dequeue_script('wc_price_slider');
		wp_dequeue_script('wc-single-product');
		wp_dequeue_script('wc-add-to-cart');
		wp_dequeue_script('wc-cart-fragments');
		wp_dequeue_script('wc-checkout');
		wp_dequeue_script('wc-add-to-cart-variation');
		wp_dequeue_script('wc-single-product');
		wp_dequeue_script('wc-cart');
		wp_dequeue_script('wc-chosen');
		wp_dequeue_script('woocommerce');
		wp_dequeue_script('prettyPhoto');
		wp_dequeue_script('prettyPhoto-init');
		wp_dequeue_script('jquery-blockui');
		wp_dequeue_script('jquery-placeholder');
		wp_dequeue_script('fancybox');
		wp_dequeue_script('jqueryui');
	}

	public function post_class($classes) {
		$classes[] = $this->post_class;
		return $classes;
	}

	public function body_class($classes) {
		if (is_shop())
			$classes[] = 'woocommerce-shop';
		elseif (!is_singular('product'))
			$classes[] = 'woocommerce-archive';
		return $classes;
	}
}