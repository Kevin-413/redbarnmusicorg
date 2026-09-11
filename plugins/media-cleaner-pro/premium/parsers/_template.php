<?php
/**
 * Media Cleaner Parser Template for PLUGIN_NAME
 * 
 * This parser enables Media Cleaner to detect media usage in PLUGIN_NAME.
 * 
 * Replace PLUGIN_NAME with the actual plugin name throughout this file.
 * Replace _plugin_meta_key with the actual meta key(s) used by the plugin.
 */

// Register parser hooks
add_action( 'wpmc_scan_once', 'wpmc_scan_once_PLUGIN_NAME', 10, 0 );
add_action( 'wpmc_scan_post', 'wpmc_scan_html_PLUGIN_NAME', 10, 2 );
add_action( 'wpmc_scan_postmeta', 'wpmc_scan_postmeta_PLUGIN_NAME', 10, 1 );

/**
 * Runs once at the beginning of each scan.
 * Use for: plugin global settings, options pages, theme settings, site icons, etc.
 */
function wpmc_scan_once_PLUGIN_NAME() {
    global $wpmc;
    $ids = [];
    $urls = [];

    // Example: Check plugin global settings
    $settings = get_option( 'plugin_name_settings', [] );
    if ( !empty( $settings ) ) {
        $wpmc->get_from_meta( 
            $settings, 
            ['logo', 'background', 'icon', 'image'],  // Keys to look for
            $ids, 
            $urls 
        );
    }

    // Register found references
    if ( !empty( $ids ) ) {
        $wpmc->add_reference_id( $ids, 'PLUGIN_NAME SETTINGS (ID)' );
    }
    if ( !empty( $urls ) ) {
        $wpmc->add_reference_url( $urls, 'PLUGIN_NAME SETTINGS (URL)' );
    }
}

/**
 * Runs for each post to scan its meta data.
 * Use for: custom fields, plugin-specific post meta, serialized data, etc.
 *
 * @param int $post_id The post ID.
 */
function wpmc_scan_postmeta_PLUGIN_NAME( $post_id ) {
    global $wpmc;
    $ids = [];
    $urls = [];

    // Get plugin's post meta data
    $data = get_post_meta( $post_id, '_plugin_meta_key', true );
    
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
    // Specify all attribute names the plugin uses for media
    $wpmc->get_from_meta( 
        $data, 
        ['id', 'url', 'image', 'thumbnail', 'background_image', 'src', 'mediaId'],
        $ids, 
        $urls 
    );

    // Register found references
    $wpmc->add_reference_id( $ids, 'PLUGIN_NAME (ID)', $post_id );
    $wpmc->add_reference_url( $urls, 'PLUGIN_NAME (URL)', $post_id );
}

/**
 * Runs for each post to scan its HTML content.
 * Use for: shortcodes, Gutenberg blocks, embedded media in content.
 *
 * @param string $html The post content HTML.
 * @param int $post_id The post ID.
 */
function wpmc_scan_html_PLUGIN_NAME( $html, $post_id ) {
    global $wpmc;
    $ids = [];
    $urls = [];

    if ( empty( $html ) ) {
        return;
    }

    // ═══════════════════════════════════════════════════════════════════════
    // METHOD 1: Simple shortcode rendering
    // Best for: Plugins that use standard shortcodes with rendered output
    // ═══════════════════════════════════════════════════════════════════════
    
    // Check if plugin shortcode exists before processing
    if ( has_shortcode( $html, 'plugin_name' ) ) {
        $rendered = do_shortcode( $html );
        $shortcode_urls = $wpmc->get_urls_from_string( $rendered );
        $urls = array_merge( $urls, $shortcode_urls );
    }

    // ═══════════════════════════════════════════════════════════════════════
    // METHOD 2: Parse nested shortcodes
    // Best for: Complex shortcode structures like [gallery][image id="1"/][/gallery]
    // ═══════════════════════════════════════════════════════════════════════
    
    // if ( strpos( $html, '[plugin_name' ) !== false ) {
    //     $nodes = $wpmc->nested_shortcodes_to_array( $html );
    //     $wpmc->array_to_ids_or_urls( 
    //         $nodes,
    //         $ids,
    //         $urls,
    //         true,  // Recursive
    //         ['src', 'ids', 'url', 'image', 'id']  // Attributes to extract
    //     );
    // }

    // ═══════════════════════════════════════════════════════════════════════
    // METHOD 3: Extract specific shortcode attributes
    // Best for: When you know exactly which attributes contain media
    // ═══════════════════════════════════════════════════════════════════════
    
    // $result = $wpmc->get_all_shortcodes_attributes( 
    //     $html,
    //     ['id', 'ids', 'image_id'],  // Attributes containing IDs
    //     ['src', 'url', 'image']     // Attributes containing URLs
    // );
    // $ids = array_merge( $ids, $result['ids'] );
    // $urls = array_merge( $urls, $result['urls'] );

    // ═══════════════════════════════════════════════════════════════════════
    // METHOD 4: Scan Gutenberg blocks
    // Best for: Plugins that provide custom Gutenberg blocks
    // ═══════════════════════════════════════════════════════════════════════
    
    // if ( strpos( $html, '<!-- wp:plugin-name/' ) !== false ) {
    //     $wpmc->get_from_blocks(
    //         $html,
    //         'plugin-name/',  // Block namespace prefix
    //         ['id', 'url', 'mediaId', 'imageUrl'],  // Block attributes to extract
    //         $urls,
    //         $ids
    //     );
    // }

    // ═══════════════════════════════════════════════════════════════════════
    // METHOD 5: Direct regex extraction (use sparingly)
    // Best for: Non-standard formats or when other methods don't work
    // ═══════════════════════════════════════════════════════════════════════
    
    // Extract all URLs from the raw HTML (includes images, links, etc.)
    // $raw_urls = $wpmc->get_urls_from_html( $html );
    // $urls = array_merge( $urls, $raw_urls );

    // Register found references
    $wpmc->add_reference_id( $ids, 'PLUGIN_NAME (ID)', $post_id );
    $wpmc->add_reference_url( $urls, 'PLUGIN_NAME (URL)', $post_id );
}

/**
 * OPTIONAL: Advanced scanning techniques
 * Uncomment and adapt these as needed for your plugin.
 */

// ═══════════════════════════════════════════════════════════════════════════
// Scan taxonomy terms (for plugins that attach media to categories/tags)
// ═══════════════════════════════════════════════════════════════════════════

// function wpmc_scan_taxonomy_PLUGIN_NAME() {
//     global $wpdb, $wpmc;
//     
//     $terms = get_terms([
//         'taxonomy' => 'your_taxonomy',
//         'hide_empty' => false,
//     ]);
//     
//     foreach ( $terms as $term ) {
//         $image_id = get_term_meta( $term->term_id, 'image_id', true );
//         if ( !empty( $image_id ) ) {
//             $wpmc->add_reference_id( $image_id, 'PLUGIN_NAME TERM (ID)', $term->term_id );
//         }
//     }
// }
// add_action( 'wpmc_scan_once', 'wpmc_scan_taxonomy_PLUGIN_NAME', 10, 0 );

// ═══════════════════════════════════════════════════════════════════════════
// Include thumbnails for referenced images
// Use when the plugin might resize images differently than WordPress defaults
// ═══════════════════════════════════════════════════════════════════════════

// foreach ( $ids as $id ) {
//     $thumbnail_urls = $wpmc->get_thumbnails_urls( $id );
//     $wpmc->add_reference_url( $thumbnail_urls, 'PLUGIN_NAME (URL) {SAFE}', $post_id );
// }

// ═══════════════════════════════════════════════════════════════════════════
// Get attachment ID from URL when only URL is available
// ═══════════════════════════════════════════════════════════════════════════

// foreach ( $urls as $url ) {
//     $attachment_id = $wpmc->custom_attachment_url_to_postid( $url );
//     if ( $attachment_id ) {
//         $wpmc->add_reference_id( $attachment_id, 'PLUGIN_NAME (ID)', $post_id );
//     }
// }

// ═══════════════════════════════════════════════════════════════════════════
// Debug logging (enable in Media Cleaner settings)
// ═══════════════════════════════════════════════════════════════════════════

// $wpmc->log( "PLUGIN_NAME: Found " . count($ids) . " IDs and " . count($urls) . " URLs in post $post_id" );
