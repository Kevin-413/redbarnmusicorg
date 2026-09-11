<?php

add_action( 'wpmc_scan_once', 'wpmc_scan_once_divi', 10, 0 );
add_action( 'wpmc_scan_post', 'wpmc_scan_html_divi', 10, 2 );

function wpmc_scan_once_divi() {
  global $wpmc;

  $et_divi = get_option( 'et_divi', '' );
  if ( !empty( $et_divi ) ) {
    $postmeta_images_ids = array();
	$postmeta_images_urls = array();
    $wpmc->get_from_meta( $et_divi, array( 'divi_logo' ), $postmeta_images_ids, $postmeta_images_urls );
    $wpmc->add_reference_id( $postmeta_images_ids, 'THEME (ID)' );
	$wpmc->add_reference_url( $postmeta_images_urls, 'THEME (URL)' );
  }
}	

function wpmc_scan_html_divi( $html, $id ) {
	global $wpmc;

	$d4_posts_images_urls    = array();
	$d4_galleries_images_ids = array();

	$d5_posts_images_urls    = array();
	$d5_galleries_images_ids = array();


	if ( empty( $html ) ) {
		return;
	}

	//$is_divi_5 = defined( 'ET_CORE_VERSION' ) && version_compare( ET_CORE_VERSION, '5.0.0', '>=' );

	// Divi 5 posts might be using Legacy Divi 4 blocks, so we need to scan for both versions.
	wpmc_scan_html_divi_5( $wpmc, $html, $d5_galleries_images_ids, $d5_posts_images_urls );
	wpmc_scan_html_divi_4( $wpmc, $html, $d4_galleries_images_ids, $d4_posts_images_urls );

	// Dynamic content (@ET-DC@...@) never contains a URL: the media is named by the post_id
	// inside the encoded settings, whatever the field (button_url, background_image, src...).
	$dynamic_ids = wpmc_scan_html_divi_dynamic( $html );
	foreach ( $dynamic_ids as $dynamic_id ) {
		$wpmc->add_reference_url( $wpmc->get_thumbnails_urls( $dynamic_id ), 'DIVI (URL) {SAFE}', $id );
	}
	$wpmc->add_reference_id( $dynamic_ids, 'DIVI DYNAMIC (ID)', $id );


	// Set all the Divi 5 References first, as they are the most recent version.

	$wpmc->add_reference_url( $d5_posts_images_urls, 'DIVI 5 (URL)', $id );
	foreach( $d5_posts_images_urls as $key => $url ) {
		$attachement_id = $wpmc->custom_attachment_url_to_postid( $url );
		$urls = $wpmc->get_thumbnails_urls_from_srcset( $attachement_id );

		$wpmc->add_reference_url( $urls, 'DIVI 5 (URL) {SAFE}', $id );
	 }

	foreach( $d5_galleries_images_ids as $key => $image_id ) {
		$urls = $wpmc->get_thumbnails_urls( $image_id );

		$wpmc->add_reference_url( $urls, 'DIVI 5 (URL) {SAFE}', $id );
	}

	$wpmc->add_reference_id( $d5_galleries_images_ids, 'DIVI 5 (ID)', $id );

	


	
	

	// Now set all the Divi 4 References
	foreach( $d4_galleries_images_ids as $key => $image_id ) {
		$urls = $wpmc->get_thumbnails_urls( $image_id );
		$wpmc->add_reference_url( $urls, 'DIVI 4 (URL) {SAFE}', $id );
	}

	//* No SAFE for URLs in Divi 4, as they are often just references to the full size image.
	// foreach( $d4_posts_images_urls as $key => $url ) {
	// 	$attachement_id = $wpmc->custom_attachment_url_to_postid( $url );
	// 	$urls = $wpmc->get_thumbnails_urls_from_srcset( $attachement_id );

	// 	$wpmc->add_reference_url( $urls, 'DIVI 4 (URL) {SAFE}', $id );
	// }

	$wpmc->add_reference_url( $d4_posts_images_urls, 'DIVI 4 (URL)', $id );
	$wpmc->add_reference_id( $d4_galleries_images_ids, 'DIVI 4 (ID)', $id );
}


// Divi dynamic content: @ET-DC@<base64 json>@ -> { content: 'post_link_url_attachment',
// settings: { post_id: '1032' } }. Returns every post_id found; non-attachment ids simply
// never match an entry, so they cost nothing.
function wpmc_scan_html_divi_dynamic( $html ) {
	$ids = array();
	if ( !preg_match_all( '/@ET-DC@([A-Za-z0-9+\/=]+)@/', $html, $matches ) ) {
		return $ids;
	}
	foreach ( $matches[1] as $encoded ) {
		$data = json_decode( base64_decode( $encoded ), true );
		if ( !empty( $data['settings']['post_id'] ) ) {
			$ids[] = (int) $data['settings']['post_id'];
		}
	}
	return array_unique( $ids );
}

function  wpmc_scan_html_divi_5($wpmc, $html, &$ids, &$urls ) {


	// "value" is used arrays of IDs in Divi 5 blocks
	$wpmc->get_from_blocks(
        $html,
        'divi/',
        ['src', 'url', 'value', 'linkUrl', 'mp4', 'webm'],
        $urls,
        $ids
    );

}

function  wpmc_scan_html_divi_4($wpmc, $html, &$ids, &$urls ) {
	
	$nodes = $wpmc->nested_shortcodes_to_array( $html );
	$wpmc->array_to_ids_or_urls( $nodes, $ids, $urls, true, [
		"src",
		"image",
		"url",
		"gallery_ids",
		"image__hover",
		"background_image",
		"img_src",
	]);

}

?>