<?php


// Register parser hooks
add_action( 'wpmc_scan_postmeta', 'wpmc_scan_postmeta_soliloquy', 10, 1 );


function wpmc_scan_postmeta_soliloquy( $post_id ) {
    global $wpmc;
    $ids = [];
    $urls = [];

    // Get plugin's post meta data
    $data = get_post_meta( $post_id, '_sol_slider_data', true );
    
    if ( empty( $data ) ) {
        return;
    }

    $wpmc->get_from_meta( 
        $data, 
        ['attachment_id', 'src'],
        $ids, 
        $urls 
    );

    // Register found references
    $wpmc->add_reference_id( $ids, 'Soliloquy', $post_id );
    $wpmc->add_reference_url( $urls, 'Soliloquy', $post_id );
}

