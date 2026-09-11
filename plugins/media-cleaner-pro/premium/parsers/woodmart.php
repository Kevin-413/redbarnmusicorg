<?php

add_action( 'wpmc_scan_once', 'wpmc_scan_once_woodmart' );

function wpmc_scan_once_woodmart() {
	global $wpdb, $wpmc;
	$wpmc->run_paged_parser( 'woodmart-title-images', function( $offset, $limit ) use ( $wpdb ) {
		return $wpdb->get_results( $wpdb->prepare( "SELECT meta_id, meta_value FROM {$wpdb->termmeta} WHERE meta_key = 'title_image' AND meta_value <> '' ORDER BY meta_id ASC LIMIT %d, %d", $offset, $limit ) );
	}, function( $rows ) use ( $wpmc ) {
		$urls = array();
		foreach ( $rows as $row ) {
			$url = $wpmc->clean_url( $row->meta_value );
			if ( !empty( $url ) ) $urls[] = $url;
		}
		$wpmc->add_reference_url( $urls, 'WOODMART (URL)' );
	} );
}


?>
