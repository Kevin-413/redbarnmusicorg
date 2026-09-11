<?php
// Globally hides Avada's standard post-extras strip site-wide: post meta ("By | date | Comments Off"),
// the social sharing box, and the author info box.
add_filter('fusion_post_metadata_markup', '__return_empty_string');

// social_sharing_box is a boolean-mapped Avada theme option, so it bypasses the
// 'fusion_get_option' filter (early-return path); override it at the WP option level instead.
add_filter('option_fusion_options', function ($value) {
    if (is_array($value)) {
        $value['social_sharing_box'] = false;
    }
    return $value;
});

add_filter('fusion_get_page_option_override', function ($override, $post_id, $page_option) {
    return ('author_info' === $page_option) ? 'no' : $override;
}, 10, 3);
