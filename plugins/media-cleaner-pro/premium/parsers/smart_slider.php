<?php

add_action('wpmc_scan_once', 'wpmc_scan_once_smartslider3', 10, 0 );

function wpmc_scan_once_smartslider3() {
  global $wpdb;
  global $wpmc;

  $table_slides = $wpdb->prefix . "nextend2_smartslider3_slides";
  
  $wpmc->run_paged_parser( 'smartslider-slides', function( $offset, $limit ) use ( $wpdb, $table_slides ) {
    return $wpdb->get_results( $wpdb->prepare( "SELECT id, params, slide FROM $table_slides ORDER BY id ASC LIMIT %d, %d", $offset, $limit ) );
  }, function( $rows ) use ( $wpmc ) {
    foreach ( $rows as $row ) {
      foreach ( array( $row->params, $row->slide ) as $desc ) {
        preg_match_all('#\$upload\$[^,\s()]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $desc, $match);
        $urls = str_replace('$upload$/', '', str_replace("\\/", '/', $match[0]));
        $wpmc->add_reference_url( $urls, 'SMART SLIDER 3 (URL)' );
      }
    }
  } );
}

?>
