<?php

add_action( 'wpmc_scan_postmeta', 'wpmc_scan_postmeta_elementor', 10, 1 );

function wpmc_scan_postmeta_elementor( $id ) {
	global $wpmc;
	$ids = array();
	$urls = array();

  	$data = get_post_meta( $id, '_elementor_data' );
	if ( isset( $data[0] ) ) {

		if ( is_array( $data[0] ) ) {
			error_log( "Media Cleaner: Elementor data is an array (not supported yet), Post ID: $id" );
		}
		else {
			$decoded = json_decode( $data[0] );
			$wpmc->get_from_meta( $decoded, array( 'id', 'url', 'background_image' ), $ids, $urls );
			wpmc_elementor_dynamic_decoding( $decoded, $id );
		}
	}

	$settings = get_post_meta( $id, '_elementor_page_settings', true );
	if ( !empty( $settings ) && is_array( $settings ) ) {
		$wpmc->get_from_meta( $settings, array( 'id', 'url', 'background_image' ), $ids, $urls );
	}


	$wpmc->add_reference_id( $ids, 'ELEMENTOR (ID)', $id );
	$wpmc->add_reference_url( $urls, 'ELEMENTOR (URL)', $id );
}

function wpmc_elementor_dynamic_decoding( $data, $parent_id ) {
	global $wpmc;
	$ids = [];
	$urls = [];

	$id_keys = [ 'attachment_id', 'id' ];
	$url_keys = [ 'url' ];

	$iterator = function( $item ) use ( &$iterator, &$ids, &$urls, $id_keys, $url_keys ) {
		if ( is_array( $item ) ) {
			foreach ( $item as $key => $value ) {
				if ( $key === '__dynamic__' ) {
					wpmc_elementor_extract_dynamic_values( $value, $id_keys, $url_keys, $ids, $urls );
				} else {
					$iterator( $value );
				}
			}
		} elseif ( is_object( $item ) ) {
			foreach ( $item as $key => $value ) {
				if ( $key === '__dynamic__' ) {
					wpmc_elementor_extract_dynamic_values( $value, $id_keys, $url_keys, $ids, $urls );
				} else {
					$iterator( $value );
				}
			}
		}
	};

	$iterator( $data );

	if ( !empty( $ids ) ) {
		$wpmc->add_reference_id( $ids, 'Elementor Dynamic', $parent_id );
	}
	if ( !empty( $urls ) ) {
		$wpmc->add_reference_url( $urls, 'Elementor Dynamic', $parent_id );
	}
}

function wpmc_elementor_extract_dynamic_values( $dynamic_data, $id_keys, $url_keys, &$ids, &$urls ) {
	foreach ( (array)$dynamic_data as $dynamic_value ) {
		if ( !is_string( $dynamic_value ) || strpos( $dynamic_value, '[elementor-' ) !== 0 ) {
			continue;
		}
		if ( !preg_match( '/settings="([^"]+)"/', $dynamic_value, $matches ) ) {
			continue;
		}
		$settings = json_decode( urldecode( $matches[1] ), true );
		if ( !is_array( $settings ) ) {
			continue;
		}
		foreach ( $id_keys as $key ) {
			if ( isset( $settings[$key] ) && is_numeric( $settings[$key] ) ) {
				$ids[] = (int)$settings[$key];
			}
		}
		foreach ( $url_keys as $key ) {
			if ( isset( $settings[$key] ) && is_string( $settings[$key] ) && !empty( $settings[$key] ) ) {
				$urls[] = $settings[$key];
			}
		}
	}
}

?>