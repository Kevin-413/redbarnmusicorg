<?php

add_action( 'wpmc_scan_once', 'wpmc_scan_once_fusionbuilder', 10, 0 );
add_action( 'wpmc_scan_post', 'wpmc_scan_html_fusionbuilder', 10, 2 );
add_action( 'wpmc_scan_postmeta', 'wpmc_scan_postmeta_fusionbuilder', 10, 2 );

function wpmc_scan_once_fusionbuilder_get_option( $option ) {
	$res = get_option( $option );
	return is_array( $res ) ? $res : array();
}

function wpmc_scan_once_fusionbuilder() {
	global $wpmc;
	$options = array();

	$default_languages = [ '', 'en', 'all' ];
	$languages = $wpmc->get_languages();
	$languages = array_merge( $languages, $default_languages );

	$options[] = wpmc_scan_once_fusionbuilder_get_option( 'fusion_options' );
	foreach ( $languages as $language ) {
		$options[] = wpmc_scan_once_fusionbuilder_get_option( 'fusion_options_' . $language );
	}
	
	foreach ( $options as $option ) {
		$postmeta_images_ids = array();
		$postmeta_images_urls = array();
		//error_log( print_r( $option, 1 ) );
		$wpmc->get_from_meta( $option, array( 'url', 'thumbnail' ), $postmeta_images_ids, $postmeta_images_urls );
		$wpmc->add_reference_id( $postmeta_images_ids, 'Avada (Options)' );
		$wpmc->add_reference_url( $postmeta_images_urls, 'Avada (Options)' );
		//error_log( print_r( $postmeta_images_urls ) );
	}
}

function wpmc_scan_postmeta_fusionbuilder( $id ) {
  global $wpmc;
  $postmeta_images_ids = array();
	$postmeta_images_urls = array();
	$data = get_post_meta( $id, '_fusion' );

	// FusionBuilder is doing this horrible thing, not using an array to store the IDs used in the portfolio
	// but named attributes. It's limited to 30 (!?) so let's just look into all this.
	$attributes = array();
	for ( $c = 0; $c < 30; $c++ ) {
		array_push( $attributes, 'kd_featured-image-' . ($c + 1) . '_avada_portfolio_id' );
	}
	$wpmc->get_from_meta( $data, $attributes, $postmeta_images_ids, $postmeta_images_urls );
	$wpmc->add_reference_id( $postmeta_images_ids, 'Avada (Post Meta)', $id );
	$wpmc->add_reference_url( $postmeta_images_urls, 'Avada (Post Meta)', $id );
}




function wpmc_scan_html_fusionbuilder( $html, $id ) {
	global $wpmc;

	$ids = array();
	$urls = array();

	$keys = [
		'link',
		'background_image',
		'background_image_id',
		'image',
		'image_ids',
		'image_id',
		'background_image_front',
		'background_image_id_front',
		'background_image_back',
		'background_image_id_back',
		'background_image_medium',
		'background_image_id_medium',
		'background_image_small',
		'background_image_id_small',
		'video_preview_image',
		'background_slider_images'
	];

	$plain_content = '';
	$nodes = $wpmc->nested_shortcodes_to_array( $html, 0, $plain_content );
	$wpmc->array_to_ids_or_urls( $nodes, $ids, $urls, true, $keys );

	$plain_urls = $wpmc->get_urls_from_string( $plain_content );

	$wpmc->add_reference_url( $urls, 'Avada (Nested Shortcodes)', $id );
	$wpmc->add_reference_url( $plain_urls, 'Avada (Content)', $id );
	$wpmc->add_reference_id( $ids, 'Avada (Nested Shortcodes)', $id );
}

?>