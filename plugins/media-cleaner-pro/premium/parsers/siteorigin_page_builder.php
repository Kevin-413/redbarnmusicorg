<?php

add_action( 'wpmc_scan_postmeta', 'wpmc_scan_postmeta_siteorigin', 10, 1 );
add_action( 'wpmc_scan_post', 'wpmc_scan_html_siteorigin', 10, 2 );

// SiteOrigin's media attributes, shared between panels_data and the rendered HTML.
const WPMC_SITEORIGIN_KEYS = [ 'url', 'image', 'thumbnail', 'background_image', 'background_image_attachment', 'src', 'mediaId', 'ids' ];

function wpmc_scan_html_siteorigin( $html, $id ) {
    global $wpmc;

    $urls = $wpmc->get_urls_from_string( $html );
    $ids = [];

    // Rendered rows/widgets keep their settings as HTML-encoded JSON in data-style,
    // where background_image_attachment is an attachment ID with no URL anywhere in the markup.
    if ( preg_match_all( '/data-style="([^"]*)"/i', $html, $matches ) ) {
        foreach ( $matches[1] as $json ) {
            $data = json_decode( html_entity_decode( $json, ENT_QUOTES, 'UTF-8' ), true );
            if ( is_array( $data ) ) {
                $wpmc->get_from_meta( $data, WPMC_SITEORIGIN_KEYS, $ids, $urls, array_keys: ['ids'] );
            }
        }
    }

    $wpmc->add_reference_id( $ids, 'SiteOrigin HTML', $id );
    $wpmc->add_reference_url( $urls, 'SiteOrigin HTML', $id );
}

function wpmc_scan_postmeta_siteorigin( $post_id ) {
    global $wpmc;
    $ids = [];
    $urls = [];

    // Get plugin's post meta data
    $data = get_post_meta( $post_id, 'panels_data', true );

    if ( empty( $data ) ) {
        return;
    }

    // Handle JSON-encoded data
    if ( is_string( $data ) && !empty( $data ) ) {
        $decoded = json_decode( $data, true );
        if ( json_last_error() === JSON_ERROR_NONE ) {
            $data = $decoded;
        }
    }

    // Extract IDs and URLs from the meta data
    $wpmc->get_from_meta(
        $data,
        WPMC_SITEORIGIN_KEYS,
        $ids,
        $urls,
        array_keys: ['ids'] // Specify that 'ids' key should be treated as an array of IDs
    );

    // Register found references
    $wpmc->add_reference_id( $ids, 'SiteOrigin Panel\'s Data', $post_id );
    $wpmc->add_reference_url( $urls, 'SiteOrigin Panel\'s Data', $post_id );
}
