<?php

add_action( 'wpmc_scan_once', 'wpmc_scan_once_wonderplugin_3dcarousel', 10, 0 );

function wpmc_scan_once_wonderplugin_3dcarousel() {
	global $wpdb, $wpmc;
	$table = $wpdb->prefix . 'wonderplugin_3dcarousel';
	$wpmc->run_paged_parser( 'wonderplugin-3dcarousel', function( $cursor, $limit ) use ( $wpdb, $table ) {
		return $wpdb->get_results( $wpdb->prepare( "SELECT id, data FROM $table WHERE id > %d ORDER BY id ASC LIMIT %d", $cursor, $limit ) );
	}, function( $rows ) use ( $wpmc ) {
	foreach ( $rows as $row ) {
		$data = json_decode( $row->data );
		if ( !$data || empty( $data->slides ) || ( !is_array( $data->slides ) && !is_object( $data->slides ) ) ) continue;
		$slides = is_array( $data->slides ) ? $data->slides : array_values( get_object_vars( $data->slides ) );
		$urls = array();
		foreach ( $slides as $slide ) {
			if ( is_array( $slide ) ) $slide = (object) $slide;
			if ( !is_object( $slide ) ) continue;
			if ( !empty( $slide->image ) ) {
				$urls[] = $wpmc->clean_url( $slide->image );
			}
			if ( !empty( $slide->thumbnail ) ) {
				$urls[] = $wpmc->clean_url( $slide->thumbnail );
			}
			if ( !empty( $slide->mp3 ) ) {
				$urls[] = $wpmc->clean_url( $slide->mp3 );
			}
			if ( !empty( $slide->mp4 ) ) {
				$urls[] = $wpmc->clean_url( $slide->mp4 );
			}
			if ( !empty( $slide->video ) ) {
				$urls[] = $wpmc->clean_url( $slide->video );
			}
		}
		$wpmc->add_reference_url( $urls, '3D CAROUSEL (URL)' );
	}
	}, 100, function( $rows, $cursor ) {
		$last = end( $rows );
		return $last ? (int) $last->id : $cursor;
	} );
}

?>
