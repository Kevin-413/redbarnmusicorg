<?php

add_action( 'wpmc_scan_once', 'wpmc_scan_once_wcfm_marketplace', 10, 0 );

function wpmc_scan_once_wcfm_marketplace() {
  global $wpmc, $wpdb;

  $wpmc->run_paged_parser( 'wcfm-profile-settings', function( $offset, $limit ) use ( $wpdb ) {
    return $wpdb->get_results( $wpdb->prepare( "SELECT umeta_id, meta_value FROM $wpdb->usermeta WHERE meta_key = 'wcfmmp_profile_settings' ORDER BY umeta_id ASC LIMIT %d, %d", $offset, $limit ) );
  }, function( $rows ) use ( $wpmc ) {
    foreach ( $rows as $row ) {
      $data = maybe_unserialize( $row->meta_value );
      $ids = array();
      $urls = array();
      $wpmc->get_from_meta( $data, array( 'gravatar', 'banner' ), $ids, $urls );
      $wpmc->add_reference_id( $ids, 'PAGE BUILDER META (ID)' );
      $wpmc->add_reference_url( $urls, 'PAGE BUILDER META (URL)' );
    }
  } );
}	

?>
