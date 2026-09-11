<?php
// Adding action hooks for Media Cleaner plugin
add_action('wpmc_scan_once', 'wpmc_scan_once_w3_total_cache', 10, 0);


function wpmc_scan_once_w3_total_cache()
{
    global $wpmc;

    $wpmc->run_paged_parser( 'w3tc-image-service', function( $offset, $limit ) {
        return get_posts( array(
            'post_type' => 'attachment',
            'post_status' => 'any',
            'fields' => 'ids',
            'orderby' => 'ID',
            'order' => 'ASC',
            'offset' => $offset,
            'posts_per_page' => $limit,
            'no_found_rows' => true,
            'meta_query' => array( array( 'key' => 'w3tc_imageservice_file', 'value' => 'webp', 'compare' => 'LIKE' ) ),
        ) );
    }, function( $attachment_ids ) use ( $wpmc ) {
      $postmeta_images_ids = array();
      $postmeta_images_urls = array();
      foreach ( $attachment_ids as $attachment_id ) {
        $postmeta_images_ids[] = $attachment_id;
        $url = wp_get_attachment_url( $attachment_id );
        $url = $wpmc->clean_url( $url );
		if ( !$url ) continue;

        $path = '';
        $last_slash_pos = strrpos( $url, '/' );
        if ( $last_slash_pos !== false ) {
            $path = substr( $url, 0, $last_slash_pos + 1 );
        }
        
        $postmeta_images_urls[] = $url;

        $postmeta_images_sizes = array();
        $postmeta_images_sizes_ids = array();
		$meta = get_post_meta( $attachment_id, '_wp_attachment_metadata', true );
        $decoded = maybe_unserialize( $meta );
        if ( is_array( $decoded ) ) {
            $wpmc->array_to_ids_or_urls( $decoded, $postmeta_images_sizes_ids, $postmeta_images_sizes, true );
        }

        foreach( $postmeta_images_sizes as $filename ) {
            if( strpos( $filename, $path ) !== false ) {
                continue;
            }

            $postmeta_images_urls[] = $path . $filename;
        }
	  }
      $wpmc->add_reference_id( array_unique( $postmeta_images_ids ), 'W3 Total Cache (ID)' );
      $wpmc->add_reference_url( array_unique( $postmeta_images_urls ), 'W3 Total Cache (URL) {SAFE}' );
    } );
}
