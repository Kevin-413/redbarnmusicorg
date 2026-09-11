<?php

// Slider Revolution 6 and below store slides in the revslider_slides table.
add_action( 'wpmc_scan_once', 'wpmc_scan_once_revslider', 10, 0 );

// Slider Revolution 7 embeds sliders as blocks and stores them in the *7 tables.
add_action( 'wpmc_scan_post', 'wpmc_scan_html_revslider', 10, 2 );

function wpmc_scan_once_revslider() {
  global $wpmc;
  global $wpdb;

  $table = $wpdb->get_blog_prefix() . 'revslider_slides';
  if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table ) ) ) !== $table ) {
    return;
  }
  $wpmc->run_paged_parser( 'revslider-slides', function( $offset, $limit ) use ( $wpdb, $table ) {
    return $wpdb->get_results( $wpdb->prepare( "SELECT id, params, layers FROM $table ORDER BY id ASC LIMIT %d, %d", $offset, $limit ) );
  }, function( $rows ) use ( $wpmc ) {
    foreach ( $rows as $row ) {
      foreach ( array( $row->params, $row->layers ) as $slider ) {
        $data = json_decode( $slider );
        $ids = array();
        $urls = array();
        $wpmc->get_from_meta( $data, array( 'image', 'imageId' ), $ids, $urls );
        $wpmc->add_reference_id( $ids, 'SLIDER (ID)' );
        $wpmc->add_reference_url( $urls, 'SLIDER (URL)' );
      }
    }
  } );
}

function wpmc_scan_html_revslider( $html, $id ) {
  global $wpmc;
  global $wpdb;

  if ( empty( $html ) || strpos( $html, 'themepunch/revslider' ) === false ) {
    return;
  }

  // Get the "alias" parameter from the revslider block(s) in the HTML.
  $blocks = parse_blocks( $html );
  $aliases = array();
  foreach ( $blocks as $block ) {
    if ( $block['blockName'] === 'themepunch/revslider' && !empty( $block['attrs']['alias'] ) ) {
      $aliases[] = $block['attrs']['alias'];
    }
  }
  $aliases = array_unique( $aliases );
  if ( empty( $aliases ) ) {
    return;
  }

  $sliders_table = $wpdb->prefix . 'revslider_sliders7';
  $slides_table = $wpdb->prefix . 'revslider_slides7';
  static $has_rev7_tables = null;
  if ( $has_rev7_tables === null ) {
    $has_rev7_tables = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $slides_table ) ) ) === $slides_table;
  }
  if ( !$has_rev7_tables ) {
    return;
  }

  foreach ( $aliases as $alias ) {
    // Get the slider ID where alias = alias.
    $slider_id = $wpdb->get_var( $wpdb->prepare(
      "SELECT id FROM $sliders_table WHERE alias = %s LIMIT 1",
      $alias
    ) );

    if ( empty( $slider_id ) ) {
      continue;
    }

    // Get all the layers and params where slider_id = ID.
    $slides_rows = $wpdb->get_results( $wpdb->prepare(
      "SELECT layers, params FROM $slides_table WHERE slider_id = %d",
      $slider_id
    ), ARRAY_A );

    if ( empty( $slides_rows ) ) {
      continue;
    }

    // For each row, extract all the media references.
    foreach ( $slides_rows as $slide ) {
      $urls = array_merge(
        $wpmc->get_urls_from_string( (string) ( $slide['layers'] ?? '' ) ),
        $wpmc->get_urls_from_string( (string) ( $slide['params'] ?? '' ) )
      );

      $urls = array_unique( $urls );
      $wpmc->add_reference_url( $urls, 'Slider Revolution (URL)', $id );
    }
  }
}

?>
