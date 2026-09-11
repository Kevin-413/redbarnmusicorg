<?php

// POST TYPES: academy_courses

// TABLE: academy_lessons, let's look in the lesson_content

// TABLE academy_lessonmeta, let's look at the meta_value for meta_key = featured_media, or attachment.

add_action( 'wpmc_scan_once', 'wpmc_scan_once_academy_lms', 10, 0 );

function wpmc_scan_once_academy_lms() {
  global $wpdb, $wpmc;
  $table = $wpdb->prefix . 'academy_lessons';
  $wpmc->run_paged_parser( 'academy-lessons', function( $offset, $limit ) use ( $wpdb, $table ) {
    return $wpdb->get_results( $wpdb->prepare( "SELECT ID, lesson_content FROM $table ORDER BY ID ASC LIMIT %d, %d", $offset, $limit ) );
  }, function( $rows ) use ( $wpmc ) {
    foreach ( $rows as $row ) {
      $wpmc->add_reference_url( $wpmc->get_urls_from_html( $row->lesson_content ), 'ACADEMY LMS (URL)' );
    }
  } );

  // Check academy_lessonmeta
  $table = $wpdb->prefix . 'academy_lessonmeta';
  $wpmc->run_paged_parser( 'academy-lessonmeta', function( $offset, $limit ) use ( $wpdb, $table ) {
    return $wpdb->get_results( $wpdb->prepare( "SELECT meta_id, meta_value FROM $table WHERE meta_key IN ('featured_media', 'attachment') ORDER BY meta_id ASC LIMIT %d, %d", $offset, $limit ) );
  }, function( $rows ) use ( $wpmc ) {
    foreach ( $rows as $row ) {
      if ( is_numeric( $row->meta_value ) ) $wpmc->add_reference_id( (int) $row->meta_value, 'ACADEMY LMS (ID)' );
      else if ( is_string( $row->meta_value ) ) $wpmc->add_reference_url( $row->meta_value, 'ACADEMY LMS (URL)' );
    }
  } );
}
