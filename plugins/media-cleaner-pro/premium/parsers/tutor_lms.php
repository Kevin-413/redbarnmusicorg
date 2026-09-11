<?php

add_action('wpmc_scan_once', 'wpmc_scan_once_tutor_lms', 10, 0);
add_action('wpmc_scan_post', 'wpmc_scan_html_tutor_lms', 10, 2);


function wpmc_scan_once_tutor_lms()
{
    global $wpdb, $wpmc;

    $wpmc->run_paged_parser( 'tutor-lms-thumbnails', function( $offset, $limit ) use ( $wpdb ) {
        return $wpdb->get_results( $wpdb->prepare( "SELECT pm.meta_id, pm.meta_value FROM {$wpdb->postmeta} pm INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID WHERE pm.meta_key = '_thumbnail_id' AND p.post_type IN ('courses', 'lesson', 'quiz') ORDER BY pm.meta_id ASC LIMIT %d, %d", $offset, $limit ) );
    }, function( $rows ) use ( $wpmc ) {
        $ids = array_map( function( $row ) { return (int) $row->meta_value; }, $rows );
        $urls = array_map( function( $id ) use ( $wpmc ) { return $wpmc->clean_url( wp_get_attachment_url( $id ) ); }, $ids );
        $wpmc->add_reference_id( $ids, 'Tutor LMS ( ID )' );
        $wpmc->add_reference_url( array_filter( $urls ), 'Tutor LMS ( URL )' );
    } );
}

function wpmc_scan_html_tutor_lms( $html, $id )
{

    global $wpmc;

    $post_types = array( 'courses', 'lesson', 'quiz' );
    $post_type = get_post_type( $id );

    if ( ! in_array( $post_type, $post_types ) ) {
        return;
    }
    
    // Skip the encoding since we receive the HTML as a string
    $urls = $wpmc->get_urls_from_html( $html );

    array_map( function( $url ) use ( $wpmc ) {
        $url = $wpmc->clean_url( $url );
        return $url;
    }, $urls );


    $wpmc->add_reference_url( $urls, 'Tutor LMS ( URL )', $id );
}


?>
