<?php

add_action( 'wpmc_scan_once', 'wpmc_scan_once_mailpoet', 10, 0 );

function wpmc_parse_mailpoet_blocks( $blocks, &$images ) {
  foreach ( $blocks as $block ) {
    if ( $block['type'] == 'image' ) {
      $images[] = $block['src'];
    }
    if ( isset( $block['blocks'] ) ) {
      wpmc_parse_mailpoet_blocks( $block['blocks'], $images );
    }
  }
}

function wpmc_scan_once_mailpoet() {
	global $wpdb, $wpmc;
	$table = $wpdb->prefix . 'mailpoet_newsletters';
	$wpmc->run_paged_parser( 'mailpoet-newsletters', function( $offset, $limit ) use ( $wpdb, $table ) {
		return $wpdb->get_results( $wpdb->prepare( "SELECT id, body FROM $table ORDER BY id ASC LIMIT %d, %d", $offset, $limit ) );
	}, function( $results ) use ( $wpmc ) {
		foreach ( $results as $result ) {
			$data = json_decode( $result->body, true );
			$blocks = isset( $data['content']['blocks'] ) && is_array( $data['content']['blocks'] ) ? $data['content']['blocks'] : array();
			$images = array();
			wpmc_parse_mailpoet_blocks( $blocks, $images );
			$urls = array();
			foreach ( $images as $image ) {
				$clean_url = $wpmc->clean_url( $image );
				if ( !empty( $clean_url ) ) $urls[] = $clean_url;
			}
			$wpmc->add_reference_url( $urls, 'MAILPOET (URL)' );
		}
	} );
}
