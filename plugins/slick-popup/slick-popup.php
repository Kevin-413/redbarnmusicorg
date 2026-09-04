<?php
/*
Plugin Name:  Slick Popup: Forminator Popup Plugin
Plugin URI:   http://www.omaksolutions.com
Description:  A lightweight plugin that opens editable popup content, including Forminator shortcodes and simple HTML links.
Author URI:   http://www.omaksolutions.com 
Author:       Om Ak Solutions 
Version:      1.7.16
Text Domain: slick-popup
*/

const SPLITE_VERSION = '1.7.16';
const SPLITE_REQUIRED_WP_VERSION = '3.0.1';
const SPLITE_PLUGIN = __FILE__;
const SPLITE_TXT_DOMAIN = 'slick-popup';

define( 'SPLITE_PLUGIN_BASENAME', plugin_basename( SPLITE_PLUGIN ) );
define( 'SPLITE_PLUGIN_NAME', trim( dirname( SPLITE_PLUGIN_BASENAME ), '/' ) );
define( 'SPLITE_PLUGIN_DIR', untrailingslashit( dirname( SPLITE_PLUGIN ) ) );
define( 'SPLITE_PLUGIN_URL', plugins_url( '' , __FILE__ ) );

const SPLITE_PLUGIN_IMG_URL = SPLITE_PLUGIN_URL . '/libs/admin/img';
const SPLITE_DEBUG = FALSE;
const SPLITE_REDUX_OPTION_NAME = "splite_opts";

require_once( SPLITE_PLUGIN_DIR . '/libs/admin-functions.php' );
require_once( SPLITE_PLUGIN_DIR . '/libs/admin-pages.php' );
require_once( SPLITE_PLUGIN_DIR . '/libs/extras.php' );
require_once( SPLITE_PLUGIN_DIR . '/libs/classes/splite-importer.php' );

	//require_once(ABSPATH.'wp-admin/includes/plugin.php');	
	if ( !class_exists( 'ReduxFramework' ) && file_exists( dirname( __FILE__ ) . '/libs/admin/redux-framework/redux-framework.php' ) ) {
		require_once( dirname( __FILE__ ) . '/libs/admin/redux-framework/redux-framework.php' );
	}

	if ( !isset( $redux_demo ) && file_exists( dirname( __FILE__ ) . '/libs/admin/admin-init.php' ) ) {
		require_once( dirname( __FILE__ ) . '/libs/admin/admin-init.php' );
	}

/////////////////////////////////////
// Activation Hook
/////////////////////////////////////
register_activation_hook(__FILE__, 'splite_on_activate'); 
function splite_on_activate(){
	// Empty Activation Hook
	update_option('splite_install_date', current_time('Y-m-d H:i:s')); 
	update_option('splite_delete_data', 0); 	
	set_transient( 'splite_activated', 1 );
}

/**
 * This function runs when WordPress completes its upgrade process
 * It iterates through each plugin updated to see if ours is included
 * @param $upgrader_object Array
 * @param $options Array
 */
function splite_upgrade_completed( $upgrader_object, $options ) {
	// The path to our plugin's main file
	$our_plugin = plugin_basename( __FILE__ );
	// If an update has taken place and the updated type is plugins and the plugins element exists
	if( $options['action'] == 'update' && $options['type'] == 'plugin' && isset( $options['plugins'] ) ) {
		// Iterate through the plugins being updated and check if ours is there
		foreach( $options['plugins'] as $plugin ) {
			if( $plugin == $our_plugin ) {
				// Set a transient to record that our plugin has just been updated
				set_transient( 'splite_updated', 1 );
			}
		}
	}
}
add_action( 'upgrader_process_complete', 'splite_upgrade_completed', 10, 2 );


/*
 * Save splite_delete_data option when redux settings are saved
 * Used in uninstall.php file to delete the options
*/
add_action ('redux/options/splite_opts/saved', 'splite_redux_option_saved');
function splite_redux_option_saved() {
	global $splite_opts; 	
	$delete_data = $splite_opts['delete_data'];	
	update_option('splite_delete_data', $delete_data); 		
}

/////////////////////////////////////
// Update Action using version compare
/////////////////////////////////////
//add_action( 'plugins_loaded', 'splite_update_db_check' );
//add_action( 'redux/options/splite_opts/register', 'splite_update_db_check' );
//add_action( 'redux/loaded', 'splite_update_db_check' );
function splite_update_db_check() {
	global $splite_opts; 
	if(version_compare(SPLITE_VERSION, '1.4') >= 0) {
        spplite_update_db(); 
    }
}


function spplite_update_db() {
	
	if( !get_option( 'cf7_id' ) OR !get_option( 'form_title' ) ) {
		// do nothing
		return; 
	}
	
	global $splite_opts;
	
	$options_array = array( 'cf7_id', 'primary_color', 'border_color', 'form_title', 'form_description', 'side_image' );
	
	$custom_theme_color = get_option('primary_color');
	$custom_border_color = get_option('border_color');	
	$form_id = get_option('cf7_id');
	$popup_heading = get_option('form_title');
	$popup_cta_text = get_option('form_description');
	$side_image = get_option('side_image');
	
	//Redux::setOption( $splite_opts, 'custom-theme-color',get_option('border_color') );	
	Redux::setOption( 'splite_opts', 'choose-color-scheme', 'custom_theme' );
	Redux::setOption( 'splite_opts', 'custom-theme-color', $custom_theme_color );
	
	Redux::setOption( 'splite_opts', 'form-id', $form_id );
	Redux::setOption( 'splite_opts', 'popup-heading', $popup_heading );
	Redux::setOption( 'splite_opts', 'popup-cta-text', $popup_cta_text );
	
	if( $side_image == 'have-a-query' )
		Redux::setOption( 'splite_opts', 'side-button-text', 'Have a query?' );
	elseif( $side_image == 'get-a-quote' )
		Redux::setOption( 'splite_opts', 'side-button-text', 'Get a Quote' );
	else 
		Redux::setOption( 'splite_opts', 'side-button-text', 'Contact Us' );
	
	foreach( $options_array as $option ) {
		delete_option( $option );
	}
}

/////////////////////////////////////
// Deactivation Hook
/////////////////////////////////////
register_deactivation_hook(__FILE__, 'splite_on_deactivate'); 
function splite_on_deactivate(){
	// Empty Deactivation Hook
}

/////////////////////////////////////
// Uninstall Hook
/////////////////////////////////////
register_uninstall_hook(__FILE__, 'splite_on_uninstall'); 
function splite_on_uninstall(){
	// Empty Activation Hook
	// Temporary Fix
	//delete_option( 'splite_opts' ); 		
}

/////////////////////////////////////////
// Initialise the plugin and scripts
/////////////////////////////////////////
add_action('template_redirect','splite_slick_popup_loaded');
function splite_slick_popup_loaded(){
    
	global $splite_opts, $post; 
	$current_post_id = isset($post->ID) ? strval($post->ID): "";
	$show = true; 
	
	if( ! isset( $splite_opts['plugin_state'] ) ) {
		// Temporary Fix For First Installation
		// Error Notice: Plugin State Variable Not Available
		// Is expected to be done in Activation Hook 
		$splite_opts['plugin_state'] = 1;
		$splite_opts['where_to_show'] = 'everywhere';
	}	
	
	if( $splite_opts['plugin_state'] != 1 ) {
		$show = false; 
	}
	else {
		$page_ids = isset($splite_opts['choose_pages']) ? $splite_opts['choose_pages'] : '';
		$page_ids = is_array($page_ids) ? $page_ids : array($page_ids);
		switch($splite_opts['where_to_show']) {			
			case 'everywhere': break; 
			case 'onselected': 
				if( isset($page_ids) AND is_array($page_ids) AND !in_array($current_post_id, $page_ids)) {
					$show = false; 
				}
				break; 
			case 'notonselected': 
				if( isset($page_ids) AND is_array($page_ids) AND in_array($current_post_id, $page_ids)) {
					$show = false; 
				}
				break; 
			default: break; 
		}
	}	
	
	$show = apply_filters( 'splite_dollar_show', $show );
	
	if( $show ) {		
		// If Plugin State is Enabled = 1
		// Let us Create the Beauty		
		splite_add_html_and_scripts();
		splite_add_html();
	}
	else {
		// So, it's Sunday. Don't Do Nothing!!
	}
	
}

/////////////////////////////////////////
// Enqueue Scripts and Custom CSS
/////////////////////////////////////////
function splite_add_html_and_scripts(){
	// Add Pop Up Scripts to Footer Here
	add_action( 'wp_enqueue_scripts', 'splite_enqueue_popup_scripts' );	
	add_action('wp_footer', 'splite_option_css');	
}

/////////////////////////////////////////
// Add Popup HTML To the Footer
/////////////////////////////////////////
function splite_add_html(){
	add_action('wp_footer', 'splite_add_my_popup');		
}

/////////////////////////////////////////
// Add Popup
/////////////////////////////////////////
function splite_add_my_popup() {
	
	if( !is_admin() ) {
		
		global $splite_opts;	
		if( is_user_logged_in() ) {
			if (current_user_can('manage_options')) {
				$user_is_admin = true;
			}			
		}

		$splite_opts = apply_filters('splite_options', $splite_opts);
		
		$choose_layout = $splite_opts['choose-layout'];		
		$color_scheme = $splite_opts['choose-color-scheme'];
		$custom_color_scheme = $splite_opts['custom-theme-color'];		
		$custom_text_color = $splite_opts['custom-text-color'];		
		
		$popup_heading = $splite_opts['popup-heading'];		
		$cta_text = $splite_opts['popup-cta-text'];			
		
		$side_button_scheme = $splite_opts['choose-side-button'];
		$submit_button_scheme = $splite_opts['choose-submit-button'];
		
		$side_button_text = !empty($splite_opts['side-button-text']) ? $splite_opts['side-button-text'] : 'Contact Us';
		$side_button_position = $splite_opts['side-button-position'] ?? 'left';
		$side_button_switch = isset($splite_opts['plugin_state_on_mobile']) ? 'enabled_on_mobile' : 'disabled_on_mobile';
		
		$activation_mode = array();
		$activation_mode['mode'] = $splite_opts['activation_mode'] ?? 'manually';
		$activation_mode['autopopup_delay'] = $splite_opts['autopopup-delay'] ?? 4;
		$activation_mode['onscroll_type'] = $splite_opts['onscroll-type'] ?? 'pixels';
		$activation_mode['onscroll_pixels'] = $splite_opts['onscroll-pixels'] ?? 300;
		$activation_mode['onscroll_percentage'] = $splite_opts['onscroll-percentage'] ?? 20;
		$activation_mode['onexit'] = $splite_opts['onexit'] ?? 'onexit';

		
		$popup_load_effect = $splite_opts['loader-animation'] ?? 'fadeIn';
		$popup_load_speed = $splite_opts['loader-speed'] ?? .75;
		$popup_unload_effect = $splite_opts['unloader-animation'] ?? 'fadeOut';
		$popup_unload_speed = $splite_opts['unloader-speed'] ?? .50;

		$external_selector = $splite_opts['external-selector'] ?? '';
		
		$autoclose = $splite_opts['autoclose'] ?? '';
		$autoclose_time = $splite_opts['autoclose_time'] ?? '';
		
		$redirect = $splite_opts['redirect'] ?? '';
		$redirect_url = $splite_opts['redirect_url'] ?? '';
		
		$popup_content = $splite_opts['form-content'] ?? '';
		$popup_content = apply_filters( 'splite_popup_content', $popup_content );

		$side_button_text = apply_filters( 'splite_dollar_side_button_text', $side_button_text );
		$popup_heading = apply_filters( 'splite_dollar_popup_heading', $popup_heading );
		$cta_text = apply_filters( 'splite_dollar_cta_text', $cta_text );
		
		$activation_mode = apply_filters( 'splite_dollar_activation_mode', $activation_mode );		
	
		// Check whether editable popup content exists.		
		$message = splite_check_popup_content($popup_content);
		
		$popup_box_style = ''; 
		
		?>
		
		<!-- SP Pro - Popup Box Curtain Arrangement -->
		<div id="splite_curtain" onClick="splite_unloader();" style=""></div>
		<div class="splite_popup_animator" data-loadspeed="<?php echo $popup_load_speed; ?>" data-loadeffect="<?php echo $popup_load_effect; ?>" data-unloadeffect="<?php echo $popup_unload_effect; ?>" data-activationmode="<?php echo $activation_mode['mode']; ?>" data-unloadspeed="<?php echo $popup_unload_speed; ?>" data-external_selectors="<?php echo $external_selector; ?>" data-form-source="shortcode-html" data-autoclose="<?php echo $autoclose; ?>" data-autoclose_time="<?php echo $autoclose_time; ?>" data-redirect="<?php echo $redirect; ?>" data-redirect_url="<?php echo $redirect_url; ?>"></div>
		<div id="splite_popup_box" class="<?php echo 'layout_'.$choose_layout; ?> manage">  			
			<?php if($popup_heading!='') { ?>
				<div id="splite_popup_title"><?php esc_html_e($popup_heading, SPLITE_TXT_DOMAIN); ?></div>
			<?php } ?>
			<div id="splite_form_container" class="">			
				<p id="splite_popup_description"><?php esc_html_e($cta_text, SPLITE_TXT_DOMAIN); ?></p>
				<?php 
					if( empty($message) ) { 
						echo '<div class="splite_popup_content">' . do_shortcode( wp_kses_post( $popup_content ) ) . '</div>'; 
					}
					else { 
						echo '<div class="splite_form no-form">'.wp_kses_post($message).'</div>'; 
					}
				?>
			</div>
			<!--<div class="success" style="display: none;">Successfully Submitted ...</div>-->
			<a id="splite_popupBoxClose" onClick="splite_unloader();">X</a>  
		</div>
		
		<?php if( $side_button_position != 'pos_none' ) { ?>
			<a onClick="splite_loader();" class="splite_sideEnquiry <?php _e($side_button_position, SPLITE_TXT_DOMAIN); ?> on_mobile <?php _e($side_button_switch, SPLITE_TXT_DOMAIN); ?>"><?php esc_html_e($side_button_text, SPLITE_TXT_DOMAIN); ?></a>
		<?php } ?>
		
		<!-- Slick Popup Lite Box and Curtain Arrangement -->		
		<?php splite_fire_activation_mode_script($activation_mode); ?>
<?php
	}
}

/////////////////////////////////////////
// Add CSS Based on Options
/////////////////////////////////////////
function splite_option_css() {
	
	global $splite_opts;

	$use_custom_width_height = false; // flag to use custom height width
	$splite_opts = apply_filters('splite_options', $splite_opts);
	
	$color_scheme = $splite_opts['choose-color-scheme'];
	$custom_color_scheme = $splite_opts['custom-theme-color'];	
	$custom_text_color = $splite_opts['custom-text-color'];	
	$custom_form_background_color = $splite_opts['custom-form-background-color'];	
	
	$popup_corners = $splite_opts['popup-corners'];
	$custom_popup_corners = $splite_opts['custom-popup-corners'] ?? '';

	$custom_popup_layout = $splite_opts['custom-popup-layout'];
	$popup_height = $splite_opts['popup-height'];
	$popup_width = $splite_opts['popup-width'];
		
	$heading_typography = $splite_opts['heading-typography'];  		
	$cta_typography = $splite_opts['cta-typography'];
		
	// Side Button
	$side_button_scheme = $splite_opts['choose-side-button'];
	$side_button_background = $splite_opts['side-button-background']['background-color'];
	$side_button_typography = $splite_opts['side-button-typography'];
	
	// Submit Button
	$submit_button_scheme = $splite_opts['choose-submit-button'];
	$submit_button_background = $splite_opts['submit-button-background']['background-color'];	
	$submit_button_typography = $splite_opts['submit-button-typography'];
	//$submit_button_border = $splite_opts['submit-button-border'];
  	
	// Custom CSS Code
	$custom_css_code = $splite_opts['custom-css-code'] ?? '';
	
	///////////////////////////////////////////
	// Set Submit Button Styles
	///////////////////////////////////////////
	if( $submit_button_scheme == 'themeinherit' ) {
		$submit_bg = '';
		$submit_typo_color = '';
	}
	elseif( $submit_button_scheme == 'inherit' ) {		
		$submit_bg = $custom_color_scheme;
		$submit_typo_color = $custom_text_color;
	}
	elseif ( $submit_button_scheme == 'custom' ) {
		$submit_bg = $submit_button_background;
	}
	
	// Get The Main Colors from the function
	$theme_colors = splite_get_theme_colors_values($color_scheme, $custom_color_scheme, $custom_text_color, $custom_form_background_color);	
	// Get The Border Options
	$popup_border = splite_get_popup_border_values($popup_corners);
	// Get Side Button Options
	$side_button = splite_get_side_button_values($side_button_scheme, $side_button_background);
	// Get Submit Button Options
	$submit_button = splite_get_submit_button_values($submit_button_scheme, $submit_button_background, $theme_colors['main-color']);
	
	$side_typo_color = $side_button_typography['color'];
	$side_typo_font_family = $side_button_typography['font-family'];
	$side_typo_font_size = $side_button_typography['font-size'];
	$side_typo_font_weight = $side_button_typography['font-weight'];
	$side_typo_line_height = $side_button_typography['line-height'];
	
	$submit_typo_color = $submit_button_typography['color'];
	$submit_typo_font_family = $submit_button_typography['font-family'];
	$submit_typo_font_size = $submit_button_typography['font-size'];
	$submit_typo_font_weight = $submit_button_typography['font-weight'];
	$submit_typo_line_height = $submit_button_typography['line-height'];
	
	$box_background_image = $theme_colors['background-image'] ?? '';
	$box_background_position = $theme_colors['background-position'] ?? '';
	$box_background_size = $theme_colors['background-size'] ?? 'cover';
	$box_background_repeat = $theme_colors['background-repeat'] ?? '';
	$box_background_media = $theme_colors['background-media'] ?? '';
	$box_background_color = $theme_colors['background-color'] ?? '';
	$box_background_attachment = $theme_colors['background-attachment'] ?? '';
	
	$image_background = $box_background_color.' url("'.$box_background_image.'") '.$box_background_repeat.' '.$box_background_position.' / '.$box_background_size; 
	$box_background = empty($box_background_image) ? $theme_colors['main-background-color'] : $image_background; 
	
	// Check if User wants to use Custom Height and Width
	// And set $use_custom_width_height to true
	$popup_height_width_styles = '';
	if($custom_popup_layout=='change') {
		$use_custom_width_height = true; 
	}
	
	// Create styles for Height and width if flag is true
	if($use_custom_width_height) {
		$popup_height_width_styles = '
			height: '.$popup_height['height'].';
			width: '.$popup_width['width'].';				
			max-height: 90%;
			max-width: 90%;
		';
	}
	
	if( !is_admin() ) { ?>
			<style>
			#splite_popup_box {
				background: <?php echo $box_background; ?>;
				border-bottom: 5px solid <?php echo $theme_colors['main-color']; ?>;
				border-radius: <?php echo $popup_border['radius']; ?>;
				<?php echo $popup_height_width_styles; ?>
			}
			#splite_popup_title,
			#splite_popup_box div.wpcf7-response-output,
			#splite_popup_box .forminator-response-message,
			a.splite_sideEnquiry {
				background-color: <?php echo $theme_colors['main-color']; ?>;
				color: <?php echo $theme_colors['main-text-color']; ?>;  
			}
			#splite_popup_description {  
				color: #959595;  
			}
			#splite_popupBoxClose {
				 color: <?php echo $theme_colors['main-text-color']; ?>;  
			}						
			#splite_popup_box  div.wpcf7 img.ajax-loader,
			#splite_popup_box div.wpcf7 span.ajax-loader.is-active {
				box-shadow: 0 0 5px 1px <?php echo $theme_colors['main-color']; ?>;
			}	
			a.splite_sideEnquiry {
				background: <?php echo $side_button['background-color']; ?>;				
			}
			
			<?php if( $box_background_image != '' ) { ?>
				#splite_popup_title  {
					background: transparent;   
				}
				#splite_popup_box  {
					border-bottom: 0; 
				}
			<?php } ?>
			<?php if( $submit_button['background-color'] != '' ) { ?>
				#splite_popup_box input.wpcf7-form-control.wpcf7-submit,
				#splite_popup_box .forminator-button-submit {
					background: <?php echo $submit_button['background-color']; ?>;
					letter-spacing: 1px;
					padding: 10px 15px;  
					text-align: center;
					border: 0; 
					box-shadow: none;   
				}
			<?php } ?>
			#splite_form_container {
				color: <?php echo $cta_typography['color'] ; ?>;
			}
			#splite_popup_title {
				color: <?php echo $heading_typography['color']; ?>;
				font-family: <?php echo $heading_typography['font-family']; ?>;
				font-size: <?php echo $heading_typography['font-size']; ?>;
				font-weight: <?php echo $heading_typography['font-weight']; ?>;
				line-height: <?php echo $heading_typography['line-height']; ?>;
			}
			#splite_popup_description {
				color: <?php echo $cta_typography['color'] ; ?>;
				font-family: <?php echo $cta_typography['font-family']; ?>;
				font-size: <?php echo $cta_typography['font-size']; ?>;
				font-weight: <?php echo $cta_typography['font-weight']; ?>;
				line-height: <?php echo $cta_typography['line-height']; ?>;
				text-align: <?php echo $cta_typography['text-align']; ?>;
			}
			a.splite_sideEnquiry {
				color: <?php echo $side_typo_color; ?>;
				font-family: <?php echo $side_typo_font_family; ?>;
				font-size: <?php echo $side_typo_font_size; ?>;
				font-weight: <?php echo $side_typo_font_weight; ?>;
				line-height: <?php echo $side_typo_line_height; ?>;
			}
			#splite_popup_box .wpcf7-form-control.wpcf7-submit,
			#splite_popup_box .forminator-button-submit {				
				color: <?php echo $submit_typo_color; ?>;
				font-family: <?php echo $submit_typo_font_family; ?>;
				font-size: <?php echo $submit_typo_font_size; ?>;
				font-weight: <?php echo $submit_typo_font_weight; ?>;
				line-height: <?php echo $submit_typo_line_height; ?>;
			}
			<?php echo $custom_css_code; ?>
		</style>
<?php	
	}
}

/**
 * Set Plugin URL Path (SSL/non-SSL)
 * @param  string - $path
 * @return string - $url 
 * Return https or non-https URL from path
 */
function splite_plugin_url( $path = '' ) {
	$url = plugins_url( $path, SPLITE_PLUGIN );

	if ( is_ssl() && 'http:' == substr( $url, 0, 5 ) ) {
		$url = 'https:' . substr( $url, 5 );
	}

	return $url;
}

add_action( 'redux/page/splite_opts/enqueue', 'splite_addAndOverridePanelCSS' );
/////////////////////////////////////////
// Override Redux Panel CSS (farbtastci)
/////////////////////////////////////////
function splite_addAndOverridePanelCSS() {
	wp_register_style( 'redux-custom-css', splite_plugin_url( '/libs/css/redux-admin.css' ), '', time(), 'all' );    
	wp_enqueue_style('redux-custom-css');
}

/////////////////////////////////////////
// Add Scripts for the Popup
/////////////////////////////////////////
function splite_enqueue_popup_scripts() {
	if ( !is_admin() ) {
		
		if(!wp_script_is('jquery', 'enqueued')) {			
			wp_enqueue_script('jquery');
		}
		
		wp_register_style( 'splite-animate', splite_plugin_url( '/libs/css/animate.css' ) );
		wp_enqueue_style( 'splite-animate' ); 
		
		wp_register_style( 'splite-css', splite_plugin_url( '/libs/css/styles.css' ) );
		wp_enqueue_style( 'splite-css' ); 
		
		wp_register_script( 'nicescroll-js', splite_plugin_url( '/libs/js/jquery.nicescroll.min.js', array('jquery'), null, true  ) );
		wp_enqueue_script( 'nicescroll-js' ); 
		
		wp_register_script( 'splite-js', splite_plugin_url( '/libs/js/custom.js', array('jquery'), null, true  ) );
		wp_enqueue_script( 'splite-js' );
	}
}

/**
 * Enqueue Admin Scripts
 * Once used for creating the copy button
 * Copy script is ready 
 */
add_action('admin_enqueue_scripts', 'splite_enqueue_admin_popup_scripts');
function splite_enqueue_admin_popup_scripts() {
	if ( is_admin() ) {
		wp_register_script( 'splite-admin-js', splite_plugin_url( '/libs/js/custom-admin.js', array('jquery'), null, true  ) );
		wp_enqueue_script( 'splite-admin-js' ); 
	}	
}

/**
 * Check Popup Content Availability
 * @param  string - Popup HTML/shortcode content
 * @return string - message for admin or front-end user
 */
function splite_check_popup_content($popup_content) {
	
	if( is_user_logged_in()  AND current_user_can('manage_options') )
		$user_is_admin = true; 
	
	$message = '';
	if( trim((string) $popup_content) === '') {
		if( isset($user_is_admin) ) { $message = __('No popup content entered. Please add Forminator shortcode/HTML content from <a target="_blank" href="'.admin_url('admin.php?page=slick-options').'">plugin options</a>.'); }
		else { $message = __('Form is not available. Please visit our contact page.'); }		
	}
	
	return $message;
}


add_action('redux/page/splite_opts/menu/after', 'splite_redux_after_menu');	
function splite_redux_after_menu($redux_object) {
	$output = ''; 
	
	$output .= '<div class="redux-info redux-info-field redux-field-info" style="margin: 5px; padding: 0 10px">';
		$output .= '<center><h3>There is so much you can do with Slick Popup and a ton of stuff more you can do with Slick Popup Pro.</h3></center>
						<p><ol>
							<li>More than one Popup on a Single Page</li>
							<li>Premium Support</li>
							<li>Access to new Features</li>
							<li>Unlimited Popups</li>
							<li>Import Demo Popups</li>
							<li>Exit Popup</li>
							<li>Entry Popup</li>
							<li>On-scroll Popup</li>
							<li>Insights for your Popups</li>
							<li>Login/Logout Feature</li>
							<li>and many more.....</li>
						</ol></p>';
	$output .= '</div>';
	
	echo $output; 
}

function splite_is_admin_page(){
    if(isset($_GET['page'])) {
        $page = sanitize_text_field($_GET['page']);
        return (isset($page) && ((strpos($page, 'splite') !== false)));
    }
    return false;
}

add_filter('admin_body_class', 'splite_body_class');
function splite_body_class($classes){
    if (splite_is_admin_page()) {
        $classes .= ' splite-page';
    }
    return $classes;
}

?>