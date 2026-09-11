<?php

add_action( 'wpmc_scan_once', 'wpmc_scan_once_ubermenu', 10, 0 );

function wpmc_scan_once_ubermenu() {
	global $wpmc, $wpdb;
	$wpmc->run_paged_parser( 'ubermenu-settings', function( $offset, $limit ) use ( $wpdb ) {
		return $wpdb->get_results( $wpdb->prepare( "SELECT meta_id, post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_ubermenu_settings' ORDER BY meta_id ASC LIMIT %d, %d", $offset, $limit ) );
	}, function( $rows ) use ( $wpmc ) {
		foreach ( $rows as $row ) {
			$meta = maybe_unserialize( $row->meta_value );
			if ( !is_array( $meta ) || empty( $meta['custom_content'] ) ) continue;
			$wpmc->add_reference_url( $wpmc->get_urls_from_html( $meta['custom_content'] ), 'MENU (URL)', (int) $row->post_id );
		}
	} );
}

?>
