<?php

// ACF Widget plugin
// https://acfwidgets.com
// Added by Mike Meinz

// Each field is a separate row in the wp_options table

add_action( 'wpmc_scan_widget', 'wpmc_scan_widget_acf_widgets', 10, 1 );

function wpmc_scan_widget_acf_widgets( $widget ) {
	if ( !is_array( $widget ) || !isset( $widget['callback'][0] ) || !is_object( $widget['callback'][0] ) || !isset( $widget['callback'][0]->id ) ) return;
	$acfwidget = (string) $widget['callback'][0]->id;
	if ( strlen( $acfwidget ) > 11 && substr( $acfwidget, 0, 11 ) === 'acf_widget_' ) get_images_from_acfwidgets( $acfwidget );
}

function get_images_from_acfwidgets( $widget) {
	global $wpmc;
	global $wpdb;
	// $widget starts with: acf_widget_ and looks like this: acf_widget_15011-2
	$LikeKey = $wpdb->esc_like( 'widget_' . $widget . '_' ) . '%';
	$label = 'acf-widget-' . md5( $LikeKey );
	$wpmc->run_paged_parser( $label, function( $cursor, $limit ) use ( $wpdb, $LikeKey ) {
		return $wpdb->get_results( $wpdb->prepare( "SELECT option_id, option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE %s AND option_id > %d ORDER BY option_id ASC LIMIT %d", $LikeKey, $cursor, $limit ), ARRAY_A );
	}, function( $OptionRows ) use ( $wpmc ) {
		$ACFWidget_ids = array();
		$ACFWidget_urls = array();
		foreach( $OptionRows as $row ) {
			//$row[0] = option_name from wp_options
			//$row[1] = option_value from wp_options
			// Three if statements in priority order (image ids, link fields, text fields)
			// *** An image field containing a post id for the image or is it???
			if ( strpos( $row['option_name'], 'image' ) !== false || strpos( $row['option_name'], 'icon' ) !== false ) {
				if ( is_numeric( $row['option_value'] ) ) {
					array_push( $ACFWidget_ids, $row['option_value'] );
				}
			}

			// No else here because sometimes image or icon is present in the option_name and link is also present
			// Example: widget_acf_widget_15011-2_link_1_link_icon
			// Example: widget_acf_widget_15216-3_widget_image_link

			// *** A link field may contain a link or be empty
			if ( strpos( $row['option_name'], 'link' ) !== false || strpos( $row['option_name'], 'url' ) !== false ) {
				if ( $wpmc->is_url( $row['option_value'] ) ) {
					$url = $wpmc->clean_url( $row['option_value'] );
					if (!empty($url)) {
						array_push($ACFWidget_urls, $url);
					}
				}
			}

			// *** A text field may contain HTML
			if ( strpos( $row['option_name'], 'text' ) !== false || strpos( $row['option_name'], 'html' ) !== false ) {
				if ( !empty( $row['option_value'] ) ) {
					$ACFWidget_urls = array_merge( $ACFWidget_urls, $wpmc->get_urls_from_html( $row['option_value'] ) );
				}
			}
		}
		if ( !empty( $ACFWidget_ids ) ) {
			$wpmc->add_reference_id( $ACFWidget_ids , 'ACF WIDGET (ID)' );
		}
		if ( !empty( $ACFWidget_urls ) ) {
			$wpmc->add_reference_url( $ACFWidget_urls , 'ACF WIDGET (URL)' );
		}
	}, 100, function( $rows, $cursor ) {
		$last = end( $rows );
		return $last ? (int) $last['option_id'] : $cursor;
	} );
}

?>
