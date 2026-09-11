<?php

add_action( 'wpmc_scan_once', 'wpmc_scan_once_connections_business_directory', 10, 0 );

function wpmc_scan_once_connections_business_directory() {
	global $wpdb, $wpmc;
	$table = $wpdb->prefix . 'connections';
	$wpmc->run_paged_parser( 'connections-directory', function( $offset, $limit ) use ( $wpdb, $table ) {
		return $wpdb->get_results( $wpdb->prepare( "SELECT id, options, notes, bio FROM $table ORDER BY id ASC LIMIT %d, %d", $offset, $limit ) );
	}, function( $rows ) use ( $wpmc ) {
		foreach ( $rows as $row ) {
			$option = json_decode( $row->options );
			$urls = array();
			if ( !empty( $option->logo->meta->url ) ) $urls[] = $wpmc->clean_url( $option->logo->meta->url );
			if ( !empty( $option->image->meta->original->url ) ) $urls[] = $wpmc->clean_url( $option->image->meta->original->url );
			$urls = array_merge( $urls, $wpmc->get_urls_from_html( $row->notes ), $wpmc->get_urls_from_html( $row->bio ) );
			$wpmc->add_reference_url( $urls, 'CONNECTIONS BUSINESS DIRECTORY (URL)' );
		}
	} );
}

?>
