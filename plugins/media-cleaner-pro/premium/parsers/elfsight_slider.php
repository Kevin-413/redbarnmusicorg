<?php

add_action( 'wpmc_scan_once', 'wpmc_scan_once_elfsight_slider', 10, 0 );

function wpmc_scan_once_elfsight_slider() {
  global $wpmc;
  global $wpdb;

  $table = $wpdb->get_blog_prefix() . 'elfsight_slider_widgets';
  $wpmc->run_paged_parser( 'elfsight-slider', function( $offset, $limit ) use ( $wpdb, $table ) {
    return $wpdb->get_results( $wpdb->prepare( "SELECT id, options FROM $table ORDER BY id ASC LIMIT %d, %d", $offset, $limit ) );
  }, function( $rows ) use ( $wpmc ) {
    foreach ( $rows as $row ) {
      $data = json_decode( $row->options );
      $ids = array();
      $urls = array();
      $wpmc->get_from_meta( $data, array( 'media' ), $ids, $urls );
      $wpmc->add_reference_id( $ids, 'SLIDER (ID)' );
      $wpmc->add_reference_url( $urls, 'SLIDER (URL)' );
    }
  } );
}

?>
