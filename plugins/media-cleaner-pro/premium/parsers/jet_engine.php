<?php

add_action( 'wpmc_scan_once', 'wpmc_scan_once_jet_engine', 10, 0 );
add_action( 'wpmc_scan_postmeta', 'wpmc_scan_postmeta_jet_engine', 10, 1 );


function wpmc_jet_engine_handle_post_slugs_defs( &$wpmc_jet_engine_post_slugs_def, $slug, $meta_fields ) {
  if ( empty( $meta_fields ) ) {
    return;
  }
  foreach ( $meta_fields as $meta_field ) {
    if ( $meta_field['type'] === 'media' || $meta_field['type'] === 'gallery' ) {
      if ( !isset( $wpmc_jet_engine_post_slugs_def[$slug] ) ) {
        $wpmc_jet_engine_post_slugs_def[$slug] = [];
      }
      array_push( $wpmc_jet_engine_post_slugs_def[$slug], 
        array(
          'name' => $meta_field['name'],
          'type' => $meta_field['type']
          )
        );
    }
  }
}

function wpmc_scan_once_jet_engine() {
  global $wpdb, $wpmc;

  // Post Types
  $build_key = 'wpmc_jet_engine_defs_build_' . $wpmc->get_run_id();
  $wpmc_jet_engine_post_slugs_def = get_transient( $build_key );
  if ( !is_array( $wpmc_jet_engine_post_slugs_def ) ) {
    $wpmc_jet_engine_post_slugs_def = array();
    if ( $wpmc->runs && $wpmc->get_run_id() > 0 ) {
      $definition_work = $wpmc->runs->get_work( $wpmc->get_run_id(), 'parserPages', 'jet-engine-post-types' );
      if ( $definition_work && (int) $definition_work->cursor_value > 0 ) {
        if ( !$wpmc->runs->update_work( $definition_work->id, 'pending', 0 ) ) {
          throw new RuntimeException( __( 'Media Cleaner could not restart the JetEngine definition checkpoint.', 'media-cleaner' ) );
        }
      }
    }
  }
  $jet_post_types_table = $wpdb->prefix . "jet_post_types";
  $wpmc->run_paged_parser( 'jet-engine-post-types', function( $cursor, $limit ) use ( $wpdb, $jet_post_types_table ) {
    return $wpdb->get_results( $wpdb->prepare( "SELECT id, slug, meta_fields FROM {$jet_post_types_table} WHERE status = 'publish' AND id > %d ORDER BY id ASC LIMIT %d", $cursor, $limit ), ARRAY_A );
  }, function( $rows ) use ( &$wpmc_jet_engine_post_slugs_def, $build_key ) {
    foreach ( $rows as $row ) {
      $slug = $row['slug'];
      if ( !isset( $wpmc_jet_engine_post_slugs_def[$slug] ) ) $wpmc_jet_engine_post_slugs_def[$slug] = array();
      wpmc_jet_engine_handle_post_slugs_defs( $wpmc_jet_engine_post_slugs_def, $slug, maybe_unserialize( $row['meta_fields'] ) );
    }
    set_transient( $build_key, $wpmc_jet_engine_post_slugs_def, DAY_IN_SECONDS );
  }, 100, function( $rows, $cursor ) {
    $last = end( $rows );
    return $last ? (int) $last['id'] : $cursor;
  } );
  $wpmc_jet_engine_post_slugs = array_keys( $wpmc_jet_engine_post_slugs_def );

  // Meta Boxes
  $metaboxes = get_option( 'jet_engine_meta_boxes' );
  if ( !empty( $metaboxes ) ) {
    foreach ( $metaboxes as $metabox ) {
      $slugs = empty( $metabox['args']['allowed_post_type'] ) ? [] : $metabox['args']['allowed_post_type'];
      if ( !empty( $metabox['args']['allowed_posts'] ) ) {
        $slugs[] = 'post';
        if ( !in_array( 'post', $wpmc_jet_engine_post_slugs, true ) ) {
          $wpmc_jet_engine_post_slugs[] = 'post';
        }
      }
      foreach ( $slugs as $slug ) {
        wpmc_jet_engine_handle_post_slugs_defs( $wpmc_jet_engine_post_slugs_def, $slug, $metabox['meta_fields'] );
      }
    }
  }

  // Term Meta Media
  $termmeta_table = $wpdb->prefix . 'termmeta';
  $wpmc->run_paged_parser( 'jet-engine-term-media', function( $cursor, $limit ) use ( $wpdb, $termmeta_table ) {
    return $wpdb->get_results( $wpdb->prepare( "SELECT meta_id, meta_value FROM {$termmeta_table} WHERE meta_key = 'meta-media' AND meta_id > %d ORDER BY meta_id ASC LIMIT %d", $cursor, $limit ) );
  }, function( $rows ) use ( $wpmc ) {
    foreach ( $rows as $row ) {
      if ( is_numeric( $row->meta_value ) ) $wpmc->add_reference_id( (int) $row->meta_value, 'Jet Engine (Term Meta)' );
    }
  }, 100, function( $rows, $cursor ) {
    $last = end( $rows );
    return $last ? (int) $last->meta_id : $cursor;
  } );

  set_transient( 'wpmc_jet_engine_post_types_def', $wpmc_jet_engine_post_slugs_def, MONTH_IN_SECONDS );
  delete_transient( $build_key );
}	



function wpmc_scan_postmeta_jet_engine( $id ) {
  global $wpmc;

  $wpmc_jet_engine_post_slugs_def = get_transient( 'wpmc_jet_engine_post_types_def' );
  if ( !is_array( $wpmc_jet_engine_post_slugs_def ) ) {
    return;
  }
  $wpmc_jet_engine_post_slugs     = array_keys( $wpmc_jet_engine_post_slugs_def );

  $type = get_post_type( $id );
  if ( !in_array( $type, $wpmc_jet_engine_post_slugs, true ) ) {
    return;
  }

  $postmeta_images_ids  = array( );
  $postmeta_images_urls = array( );

  $jet_engine_post_type_def = $wpmc_jet_engine_post_slugs_def[$type];

  foreach ( $jet_engine_post_type_def as $field ) {
    $meta = get_post_meta( $id, $field['name'], true );

    switch( $field['type'] ) {
      case 'media':
        if ( is_numeric( $meta ) ) {
          $postmeta_images_ids[] = intval( $meta );
        } elseif ( filter_var( $meta, FILTER_VALIDATE_URL ) ) {
          $postmeta_images_urls[] = $wpmc->clean_url( $meta );
        }
        break;

      case 'gallery':
        $gallery_items = is_array( $meta ) ? $meta : explode( ',', $meta );
        foreach ( $gallery_items as $item ) {
          if ( is_numeric( $item ) ) {
            $postmeta_images_ids[] = intval( $item );
          } elseif ( filter_var( $item, FILTER_VALIDATE_URL ) ) {
            $postmeta_images_urls[] = $wpmc->clean_url( $item );
          }
        }
        break;
    }
  }

      // Add image references to the Media Cleaner
    $wpmc->add_reference_id(  $postmeta_images_ids,  'Jet Engine (ID)',  $id );
    $wpmc->add_reference_url( $postmeta_images_urls, 'Jet Engine (URL)', $id );
}

?>
