<?php
/**
 * Avada Builder integration for Music School Teachers.
 * UI layer only: passes settings through to the existing [msch_teachers] renderer in rbm-teachers.php.
 * No teacher query, card markup, or taxonomy logic is duplicated here.
 */

add_shortcode('rbm_msch_teachers_element', 'rbm_msch_teachers_element_shortcode');
function rbm_msch_teachers_element_shortcode($atts) {
    $atts = shortcode_atts([
        'mode'       => 'compact',
        'instrument' => '',
        'order'      => 'name',
        'bio'        => '',
        'website'    => 'yes',
        'columns'    => 'auto',
    ], $atts, 'rbm_msch_teachers_element');

    return do_shortcode(sprintf(
        '[msch_teachers mode="%s" instrument="%s" order="%s" bio="%s" website="%s" columns="%s"]',
        esc_attr($atts['mode']),
        esc_attr($atts['instrument']),
        esc_attr($atts['order']),
        esc_attr($atts['bio']),
        esc_attr($atts['website']),
        esc_attr($atts['columns'])
    ));
}

// mu-plugins load before regular plugins, so function_exists('fusion_builder_map') can't be checked at file-load time.
add_action('fusion_builder_before_init', 'rbm_msch_teachers_map_element');

function rbm_msch_teachers_map_element() {
    if (!function_exists('fusion_builder_map')) {
        return;
    }
    fusion_builder_map([
        'name'            => 'Music School Teachers',
        'shortcode'       => 'rbm_msch_teachers_element',
        'icon'            => 'fusiona-single',
        'allow_generator' => true,
        'params'          => [
            [
                'type'       => 'select',
                'heading'    => 'Display Mode',
                'param_name' => 'mode',
                'default'    => 'compact',
                'value'      => [
                    'compact' => 'Compact',
                    'full'    => 'Full',
                ],
            ],
            [
                'type'       => 'select',
                'heading'    => 'Instrument',
                'param_name' => 'instrument',
                'default'    => '',
                'value'      => rbm_msch_teachers_instrument_choices(),
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
            [
                'type'       => 'select',
                'heading'    => 'Bio Display',
                'param_name' => 'bio',
                'default'    => '',
                'value'      => [
                    ''      => 'Mode Default (Compact: Short, Full: Long)',
                    'none'  => 'None',
                    'short' => 'Short Bio',
                    'long'  => 'Long Bio',
                ],
            ],
            [
                'type'       => 'select',
                'heading'    => 'Show Website Link',
                'param_name' => 'website',
                'default'    => 'yes',
                'value'      => [
                    'yes' => 'Yes',
                    'no'  => 'No',
                ],
            ],
            [
                'type'       => 'select',
                'heading'    => 'Columns (Compact mode only)',
                'param_name' => 'columns',
                'default'    => 'auto',
                'value'      => [
                    'auto' => 'Auto',
                    '1'    => '1',
                    '2'    => '2',
                    '3'    => '3',
                    '4'    => '4',
                ],
                'dependency' => [
                    [
                        'element'  => 'mode',
                        'value'    => 'full',
                        'operator' => '!=',
                    ],
                ],
            ],
        ],
    ]);
}

// '' key ("All Teachers") first, then dynamic terms so new instruments appear automatically.
function rbm_msch_teachers_instrument_choices() {
    // fusion_builder_before_init can fire before WP 'init' (mu-plugins load pre-plugins), so the
    // taxonomy may not be registered yet; register it defensively (idempotent) before querying.
    if (!taxonomy_exists('msch_instrument') && function_exists('rbm_register_instrument_taxonomy')) {
        rbm_register_instrument_taxonomy();
    }
    $choices = ['' => 'All Teachers'];
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
