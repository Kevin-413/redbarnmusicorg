<?php
/**
 * Avada Builder integration for Music School Lessons (Stage 4 public tile grid).
 * UI layer only: passes settings through to the existing [msch_lessons] renderer in rbm-lessons.php.
 * Mirrors plugins/rbm-faculty/includes/rbm-teachers-avada-element.php.
 */

add_shortcode('rbm_msch_lessons_element', 'rbm_msch_lessons_element_shortcode');
function rbm_msch_lessons_element_shortcode($atts) {
    $atts = shortcode_atts([
        'instrument' => '',
        'order'      => 'name',
    ], $atts, 'rbm_msch_lessons_element');

    return do_shortcode(sprintf(
        '[msch_lessons instrument="%s" order="%s"]',
        esc_attr($atts['instrument']),
        esc_attr($atts['order'])
    ));
}

// mu-plugins load before regular plugins, so function_exists('fusion_builder_map') can't be checked at file-load time.
add_action('fusion_builder_before_init', 'rbm_msch_lessons_map_element');

function rbm_msch_lessons_map_element() {
    if (!function_exists('fusion_builder_map')) {
        return;
    }
    fusion_builder_map([
        'name'            => 'Music School Lessons',
        'shortcode'       => 'rbm_msch_lessons_element',
        'icon'            => 'fusiona-single',
        'allow_generator' => true,
        'params'          => [
            [
                'type'       => 'select',
                'heading'    => 'Category',
                'param_name' => 'instrument',
                'default'    => '',
                'value'      => rbm_msch_lessons_instrument_choices(),
            ],
            [
                'type'       => 'select',
                'heading'    => 'Sort Order',
                'param_name' => 'order',
                'default'    => 'name',
                'value'      => [
                    'name'   => 'Name',
                    'manual' => 'Manual Order',
                ],
            ],
        ],
    ]);
}

// '' key ("All Lessons") first, then dynamic terms so new categories appear automatically.
function rbm_msch_lessons_instrument_choices() {
    // fusion_builder_before_init can fire before WP 'init' (mu-plugins load pre-plugins), so the
    // taxonomy may not be registered yet; register it defensively (idempotent) before querying.
    if (!taxonomy_exists('msch_instrument') && function_exists('rbm_register_instrument_taxonomy')) {
        rbm_register_instrument_taxonomy();
    }
    $choices = ['' => 'All Lessons'];
    $terms = get_terms([
        'taxonomy'   => 'msch_instrument',
        'hide_empty' => false,
    ]);
    if (is_array($terms)) {
        foreach ($terms as $term) {
            $choices[$term->slug] = $term->name;
        }
    }
    return $choices;
}
