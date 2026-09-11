<?php

add_action( 'wpmc_scan_postmeta', 'wpmc_scan_postmeta_beaverbuilder', 10, 1 );

function wpmc_scan_postmeta_beaverbuilder( $id ) {
	global $wpmc;
	$postmeta_images_ids = array();
	$postmeta_images_urls = array();

	$data = get_post_meta( $id, '_fl_builder_data' );

	$meta_keys = ['id', 'bg_image_src', 'photo_src', 'bg_image_medium_src', 'bg_image_responsive_src' ];
	if ( !empty( $data ) ) {
		$wpmc->get_from_meta( $data, $meta_keys, $postmeta_images_ids, $postmeta_images_urls );
	}

	// srcset references have a list of urls instead a single string value, which "get_from_meta" wll not parse
	// so let's also make full url extract from the metadata
	$string_meta = json_encode( $data );
	$urls = $wpmc->get_urls_from_string( $string_meta );
	$postmeta_images_urls = array_merge( $postmeta_images_urls, $urls );

	$wpmc->add_reference_id( $postmeta_images_ids, 'Beaver Builder', $id );
	$wpmc->add_reference_url( $postmeta_images_urls, 'Beaver Builder', $id );
}

?>