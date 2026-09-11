<?php
// Adding action hooks for Media Cleaner plugin
add_action('wpmc_scan_once', 'wpmc_scan_once_layerslider', 10, 0);


/**
 * Runs once at the beginning of the scan.
 * Can be used to check images usage in general settings, in a theme, like a favicon, etc.
 */
function wpmc_scan_once_layerslider()
{
    global $wpmc;
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'layerslider';

    $query = $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name );
    if ( $wpdb->get_var( $query ) != $table_name ) {
        return;
    }

	$wpmc->run_paged_parser( 'layerslider', function( $offset, $limit ) use ( $wpdb, $table_name ) {
		return $wpdb->get_results( $wpdb->prepare( "SELECT id, data FROM $table_name ORDER BY id ASC LIMIT %d, %d", $offset, $limit ), ARRAY_A );
	}, function( $results ) use ( $wpmc ) {
		foreach ( $results as $row ) {
        $json_data = json_decode( $row['data'], true );
        $slider_name = "Slider: " . ( isset( $json_data['properties']['title'] ) ? $json_data['properties']['title'] : 'UNKNOWN' );

        $urls = $wpmc->get_urls_from_string( $row['data'] );
        $urls = array_unique( $urls );
        $wpmc->add_reference_url( $urls, 'LayerSlider', $slider_name );

        foreach ( $urls as $url ) {
            $srcset_urls = $wpmc->get_thumbnails_urls_from_srcset( $url );
            $srcset_urls = array_unique( $srcset_urls );

            $wpmc->add_reference_url( $srcset_urls, 'LayerSlider {SAFE}', $slider_name );
        }

		}
	} );
}
