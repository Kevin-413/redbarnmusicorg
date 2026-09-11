<?php


add_action( 'wpmc_scan_once', 'wpmc_scan_once_wprm' );
add_action( 'wpmc_scan_postmeta', 'wpmc_scan_postmeta_wprm', 10, 1 );

function wpmc_scan_once_wprm() {
	global $wpmc;

	wpmc_wprm_scan_term_images( $wpmc );
}

function wpmc_scan_postmeta_wprm( $recipe_id ) {
	if ( get_post_type( $recipe_id ) !== 'wprm_recipe' ) {
		return;
	}

	global $wpmc;

	$ids  = array();
	$urls = array();

	// Featured / main image.
	$thumb_id = get_post_thumbnail_id( $recipe_id );
	if ( $thumb_id ) {
		$ids[] = (int) $thumb_id;
	}

	// Pinterest pin image (single attachment ID).
	$pin_id = get_post_meta( $recipe_id, 'wprm_pin_image_id', true );
	if ( $pin_id ) {
		$ids[] = (int) $pin_id;
	}

	// Self-hosted recipe video (attachment ID).
	$video_id = get_post_meta( $recipe_id, 'wprm_video_id', true );
	if ( $video_id ) {
		$ids[] = (int) $video_id;
	}

	// Step images + step videos, nested in the instructions array.
	$instructions = get_post_meta( $recipe_id, 'wprm_instructions', true );
	if ( is_array( $instructions ) ) {
		foreach ( $instructions as $group ) {
			if ( empty( $group['instructions'] ) || ! is_array( $group['instructions'] ) ) {
				continue;
			}

			foreach ( $group['instructions'] as $instruction ) {
				// Step image (attachment ID).
				if ( ! empty( $instruction['image'] ) ) {
					$ids[] = (int) $instruction['image'];
				}

				// Step video (self-hosted): id is an attachment, thumb can be an id or URL.
				if ( ! empty( $instruction['video'] ) && is_array( $instruction['video'] ) ) {
					if ( ! empty( $instruction['video']['id'] ) ) {
						$ids[] = (int) $instruction['video']['id'];
					}
					if ( ! empty( $instruction['video']['thumb'] ) ) {
						$thumb = $instruction['video']['thumb'];
						if ( is_numeric( $thumb ) ) {
							$ids[] = (int) $thumb;
						} elseif ( is_string( $thumb ) ) {
							$urls[] = $thumb;
						}
					}
				}
			}
		}
	}

	// Clean up and register.
	$ids  = array_values( array_unique( array_filter( array_map( 'intval', $ids ) ) ) );
	$urls = array_values( array_unique( array_filter( $urls ) ) );

	if ( ! empty( $ids ) ) {
		$wpmc->add_reference_id( $ids, 'WP RECIPE MAKER (ID)', $recipe_id );

		// Also register all generated sizes – a safety net for
		// sites that scan by file/URL rather than attachment ID.
		if ( method_exists( $wpmc, 'get_thumbnails_urls' ) ) {
			foreach ( $ids as $id ) {
				$thumbnail_urls = $wpmc->get_thumbnails_urls( $id );
				if ( ! empty( $thumbnail_urls ) ) {
					$wpmc->add_reference_url( $thumbnail_urls, 'WP RECIPE MAKER (URL) {SAFE}', $recipe_id );
				}
			}
		}
	}

	if ( ! empty( $urls ) ) {
		$wpmc->add_reference_url( $urls, 'WP RECIPE MAKER (URL)', $recipe_id );
	}
}


function wpmc_wprm_scan_term_images( $wpmc ) {
	// taxonomy => term meta key that stores the attachment ID.
	$taxonomy_image_keys = array(
		'wprm_ingredient'      => 'wprmp_ingredient_image_id',
		'wprm_equipment'       => 'wprmp_equipment_image_id',
		'wprm_course'          => 'wprmp_term_image_id',
		'wprm_cuisine'         => 'wprmp_term_image_id',
		'wprm_keyword'         => 'wprmp_term_image_id',
		'wprm_suitablefordiet' => 'wprmp_term_image_id',
	);

	// Add your own WPRM taxonomies without editing this function.
	$taxonomy_image_keys = apply_filters( 'wpmc_wprm_taxonomy_image_keys', $taxonomy_image_keys );

	foreach ( $taxonomy_image_keys as $taxonomy => $meta_key ) {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			continue;
		}

		$terms = get_terms( array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
			'fields'     => 'ids',
		) );

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			continue;
		}

		foreach ( $terms as $term_id ) {
			$image_id = get_term_meta( $term_id, $meta_key, true );
			if ( $image_id ) {
				$wpmc->add_reference_id( (int) $image_id, 'WP RECIPE MAKER TERM (ID)', $term_id );
			}
		}
	}
}