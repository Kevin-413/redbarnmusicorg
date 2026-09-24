<?php
/**
 * RBM Site Settings — central Settings > RBM Site Settings page for public-site contact info,
 * registration links, donation/scholarship links, and social links. Does not store phone/email
 * (owned by RBM Contact Scrambler) or a general-purpose Sign Up URL (owned by RBM Instruments'
 * Global Sign Up URL) — see docs/0920-2159-PLAN-RBM-Website-Settings-Page.txt.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'RBM_SITE_SETTINGS_OPTION', 'rbm_site_settings' );

// Single schema used to render fields, sanitize on save, and validate helper lookups.
function rbm_site_settings_schema() {
	return array(
		'contact'      => array(
			'title'       => 'Contact Information',
			'description' => 'Phone and email are managed on the <a href="' . esc_url( admin_url( 'options-general.php?page=rbm-contact-scrambler' ) ) . '">RBM Contact Scrambler</a> settings page, not here.',
			'fields'      => array(
				'school_name'      => array( 'label' => 'School Name', 'type' => 'text' ),
				'street_address'   => array( 'label' => 'Street Address', 'type' => 'text' ),
				'city'             => array( 'label' => 'City', 'type' => 'text' ),
				'state'            => array( 'label' => 'State', 'type' => 'text' ),
				'zip'              => array( 'label' => 'ZIP Code', 'type' => 'text' ),
				'contact_page_url' => array( 'label' => 'Contact-Page URL', 'type' => 'url' ),
				'directions_url'   => array( 'label' => 'Directions/Map URL', 'type' => 'url' ),
			),
		),
		'registration' => array(
			'title'       => 'Registration Links',
			'description' => 'The general Sign Up URL is managed on the <a href="' . esc_url( admin_url( 'edit.php?post_type=msch_lesson&page=rbm-lessons-display-settings' ) ) . '">RBM Instruments</a> settings page, not here.',
			'fields'      => array(
				'lessons_inquiry_url' => array( 'label' => 'Lessons Inquiry URL', 'type' => 'url' ),
				'registration_url'    => array( 'label' => 'Registration URL', 'type' => 'url' ),
				'contact_form_url'    => array( 'label' => 'Contact Form URL', 'type' => 'url' ),
				'student_login_url'  => array( 'label' => 'Student Login URL', 'type' => 'url' ),
				'teacher_login_url'  => array( 'label' => 'Teacher Login URL', 'type' => 'url' ),
			),
		),
		'donations'    => array(
			'title'       => 'Donations and Scholarships',
			'description' => '',
			'fields'      => array(
				'donation_url'           => array( 'label' => 'Donation URL', 'type' => 'url' ),
				'donation_button_label'  => array( 'label' => 'Donation Button Label', 'type' => 'text', 'default' => 'Donate' ),
				'scholarship_url'        => array( 'label' => 'Scholarship Information-Page URL', 'type' => 'url' ),
				'donation_new_tab'       => array( 'label' => 'Open Donation Link in a New Tab', 'type' => 'checkbox', 'default' => 1 ),
			),
		),
		'social'       => array(
			'title'       => 'Social Media',
			'description' => 'Only fields with a saved URL are shown on the front end.',
			'fields'      => array(
				'facebook_url'  => array( 'label' => 'Facebook URL', 'type' => 'url' ),
				'instagram_url' => array( 'label' => 'Instagram URL', 'type' => 'url' ),
				'youtube_url'   => array( 'label' => 'YouTube URL', 'type' => 'url' ),
				'tiktok_url'    => array( 'label' => 'TikTok URL', 'type' => 'url' ),
				'linkedin_url'  => array( 'label' => 'LinkedIn URL', 'type' => 'url' ),
			),
		),
	);
}

// Safe getter for any field defined in the schema; returns $default when unset or empty.
function rbm_site_setting( $key, $default = '' ) {
	$options = get_option( RBM_SITE_SETTINGS_OPTION, array() );
	if ( isset( $options[ $key ] ) && '' !== $options[ $key ] ) {
		return $options[ $key ];
	}
	return $default;
}

add_action( 'admin_menu', 'rbm_site_settings_add_page' );
function rbm_site_settings_add_page() {
	add_options_page( 'RBM Site Settings', 'RBM Site Settings', 'manage_options', 'rbm-site-settings', 'rbm_site_settings_render_page' );
}

add_action( 'admin_init', 'rbm_site_settings_register' );
function rbm_site_settings_register() {
	register_setting( 'rbm_site_settings_group', RBM_SITE_SETTINGS_OPTION, array(
		'type'              => 'array',
		'sanitize_callback' => 'rbm_site_settings_sanitize',
		'default'           => array(),
	) );

	foreach ( rbm_site_settings_schema() as $section_key => $section ) {
		add_settings_section( 'rbm_site_settings_' . $section_key, $section['title'], function () use ( $section ) {
			if ( $section['description'] ) {
				echo '<p>' . wp_kses_post( $section['description'] ) . '</p>';
			}
		}, 'rbm-site-settings' );

		foreach ( $section['fields'] as $field_key => $field ) {
			add_settings_field( $field_key, esc_html( $field['label'] ), 'rbm_site_settings_render_field', 'rbm-site-settings', 'rbm_site_settings_' . $section_key, array(
				'key'   => $field_key,
				'field' => $field,
			) );
		}
	}
}

function rbm_site_settings_render_field( $args ) {
	$key     = $args['key'];
	$field   = $args['field'];
	$default = isset( $field['default'] ) ? $field['default'] : '';
	$value   = rbm_site_setting( $key, $default );

	if ( 'checkbox' === $field['type'] ) {
		printf(
			'<label><input type="checkbox" name="%1$s[%2$s]" value="1" %3$s> Yes</label>',
			esc_attr( RBM_SITE_SETTINGS_OPTION ),
			esc_attr( $key ),
			checked( $value, 1, false )
		);
		return;
	}

	printf(
		'<input type="text" class="regular-text" name="%1$s[%2$s]" value="%3$s">',
		esc_attr( RBM_SITE_SETTINGS_OPTION ),
		esc_attr( $key ),
		esc_attr( $value )
	);
}

function rbm_site_settings_sanitize( $input ) {
	$input   = is_array( $input ) ? $input : array();
	$output  = array();

	foreach ( rbm_site_settings_schema() as $section ) {
		foreach ( $section['fields'] as $key => $field ) {
			if ( 'checkbox' === $field['type'] ) {
				$output[ $key ] = ! empty( $input[ $key ] ) ? 1 : 0;
				continue;
			}
			$raw = isset( $input[ $key ] ) ? trim( (string) $input[ $key ] ) : '';
			$output[ $key ] = 'url' === $field['type'] ? esc_url_raw( $raw ) : sanitize_text_field( $raw );
		}
	}

	return $output;
}

function rbm_site_settings_render_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1>RBM Site Settings</h1>
		<form method="post" action="options.php">
			<?php
			settings_fields( 'rbm_site_settings_group' );
			do_settings_sections( 'rbm-site-settings' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}

// Applies the saved Donation URL to the two "Friends of the Red Barn" donate.gif links that are
// currently hardcoded inside Text widgets (widget_text option, widget IDs 2 and 6). Widget ID 4
// ("Connect") has no donation link in it, only Facebook and Contact Us links, so it is untouched.
// Uses 'widget_text' (fires unconditionally) rather than 'widget_text_content' (only fires for
// visual-mode Text widgets) because these widgets are saved in legacy/non-visual mode.
add_filter( 'widget_text', 'rbm_site_settings_apply_donation_link_to_widgets', 20, 3 );
function rbm_site_settings_apply_donation_link_to_widgets( $content, $instance, $widget ) {
	if ( empty( $widget->number ) || ! in_array( (int) $widget->number, array( 2, 6 ), true ) ) {
		return $content;
	}

	$donation_url = rbm_site_setting( 'donation_url' );
	if ( '' === $donation_url ) {
		return $content;
	}

	$new_tab = rbm_site_setting( 'donation_new_tab', 1 );
	$old_anchor = '<a href="http://friendsoftheredbarn.org/" target="_blank" rel="noopener">';
	$new_anchor = '<a href="' . esc_url( $donation_url ) . '"' . ( $new_tab ? ' target="_blank" rel="noopener"' : '' ) . '>';

	return str_replace( $old_anchor, $new_anchor, $content );
}
