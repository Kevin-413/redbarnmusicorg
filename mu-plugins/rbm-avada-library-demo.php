<?php
/**
 * Plugin Name: RBM Avada Library Demo
 * Description: Renders a live catalog of saved Avada Library items for administrators.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_shortcode( 'rbm_avada_library_demo', 'rbm_avada_library_demo_shortcode' );
add_filter( 'wp_robots', 'rbm_avada_library_demo_robots' );

function rbm_avada_library_demo_shortcode() {
	static $is_rendering = false;

	if ( ! current_user_can( 'edit_pages' ) ) {
		return '';
	}

	if ( $is_rendering ) {
		return '<p class="rbm-avada-library-demo__error" role="alert">The Avada Library Demo shortcode cannot render recursively inside a Library item.</p>';
	}

	if ( ! shortcode_exists( 'fusion_global' ) ) {
		return '<p class="rbm-avada-library-demo__error" role="alert">Avada Library items could not be rendered because the fusion_global shortcode is unavailable.</p>';
	}

	$items = get_posts(
		array(
			'post_type'              => 'fusion_element',
			'post_status'            => array( 'publish', 'private', 'draft', 'pending', 'future' ),
			'posts_per_page'         => -1,
			'orderby'                => array(
				'title' => 'ASC',
				'ID'    => 'ASC',
			),
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'suppress_filters'       => true,
		)
	);

	if ( ! $items ) {
		return '<p>No saved Avada Library items were found.</p>';
	}

	$is_rendering = true;
	$output       = '<div class="rbm-avada-library-demo">';

	try {
		foreach ( $items as $item ) {
			$title   = get_the_title( $item );
			$output .= '<section class="rbm-avada-library-demo__item">';
			$output .= '<h2>' . esc_html( $title ) . '</h2>';

			$cycle = rbm_avada_library_demo_find_cycle( $item->ID );
			if ( $cycle ) {
				$output .= '<p class="rbm-avada-library-demo__error" role="alert">';
				$output .= esc_html(
					sprintf(
						'Could not render this item because it contains a recursive Library reference (IDs: %s).',
						implode( ' -> ', $cycle )
					)
				);
				$output .= '</p>';
			} else {
				try {
					$output .= do_shortcode( '[fusion_global id="' . absint( $item->ID ) . '"]' );
				} catch ( Throwable $error ) {
					$output .= '<p class="rbm-avada-library-demo__error" role="alert">';
					$output .= esc_html( sprintf( 'Could not render this item: %s', $error->getMessage() ) );
					$output .= '</p>';
				}
			}

			$output .= '</section>';
		}
	} finally {
		$is_rendering = false;
	}

	$output .= '</div>';

	return $output;
}

function rbm_avada_library_demo_find_cycle( $post_id, $path = array() ) {
	$post_id = absint( $post_id );

	if ( in_array( $post_id, $path, true ) ) {
		$path[] = $post_id;
		return $path;
	}

	$post = get_post( $post_id );
	if ( ! $post || 'fusion_element' !== $post->post_type ) {
		return array();
	}

	$path[] = $post_id;

	if ( has_shortcode( $post->post_content, 'rbm_avada_library_demo' ) ) {
		return $path;
	}

	$pattern = '/' . get_shortcode_regex( array( 'fusion_global' ) ) . '/s';
	if ( ! preg_match_all( $pattern, $post->post_content, $matches, PREG_SET_ORDER ) ) {
		return array();
	}

	foreach ( $matches as $match ) {
		if ( '[' === $match[1] || ']' === $match[6] ) {
			continue;
		}

		$attributes = shortcode_parse_atts( $match[3] );
		$referenced_id = is_array( $attributes ) && isset( $attributes['id'] )
			? absint( $attributes['id'] )
			: 0;

		if ( ! $referenced_id ) {
			continue;
		}

		if ( in_array( $referenced_id, $path, true ) ) {
			$path[] = $referenced_id;
			return $path;
		}

		$cycle = rbm_avada_library_demo_find_cycle( $referenced_id, $path );
		if ( $cycle ) {
			return $cycle;
		}
	}

	return array();
}

function rbm_avada_library_demo_robots( $robots ) {
	if ( is_page( 'demo-library' ) ) {
		$robots['noindex']  = true;
		$robots['nofollow'] = true;
	}

	return $robots;
}
