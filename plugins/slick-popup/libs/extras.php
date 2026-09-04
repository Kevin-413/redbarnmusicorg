<?php

// Deprecated Extra Functions


/**
 * Get Color Scheme Options  
 * Since Version 1.0
 * @param string $color_scheme (chosen color scheme)
 * @param string $custom_color_scheme (chosen color when custom scheme)
 * @param string $custom_text_color (chosen text color when custom scheme)
 * Since Version 1.4.0
 * @param string $custom_form_background_color (chosen form background color when custom scheme)
 
 * @return array() $colors (keys: main-text-color, main-color, main-background-color)
 * Called in splite_option_css() to generate custom CSS
 */
function splite_get_theme_colors_values($color_scheme, $custom_color_scheme="", $custom_text_color="", $custom_form_background_color="") {
	$colors = array();
	$colors['main-text-color'] = '#EFEFEF'; 
	$colors['main-background-color'] = '#EFEFEF'; 	
	switch($color_scheme) {
		case 'master_red' :
			$colors['main-color'] = '#ED1C24'; 			
			break; 
		case 'creamy_orange' :
			$colors['main-color'] = '#EE5921'; 
			break; 
		case 'cool_green' :
			$colors['main-color'] = '#00A560'; 
			break; 
		case 'light_blue' :
			$colors['main-color'] = '#08ADDC'; 
			break; 
		case 'custom_theme' : 
			$colors['main-color'] = $custom_color_scheme; 
			$colors['main-text-color'] = $custom_text_color; 
			$colors['main-background-color'] = $custom_form_background_color['background-color']; 
			$colors['background-image'] = $custom_form_background_color['background-image']; 
			$colors['background-repeat'] = $custom_form_background_color['background-repeat']; 
			$colors['background-size'] = $custom_form_background_color['background-size']; 
			$colors['background-position'] = $custom_form_background_color['background-position']; 
			$colors['background-attachment'] = $custom_form_background_color['background-attachment'];  
			$colors['background-media'] = isset($custom_form_background_color['background-media']) ? $custom_form_background_color['background-media'] : '';  
			break; 
		case 'light' :
			$colors['main-color'] = '#BBB'; 
			$colors['main-text-color'] = '#484848'; 
			break; 
		case 'dark' :
		default :
			$colors['main-color'] = '#484848'; 
			$colors['main-text-color'] = '#DDDDDD'; 
			break; 
	}
	
	return apply_filters( 'splite_dollar_colors', $colors );
		
}

/**
 * Get Popup Border Options
 * Since Version 1.0
 * @param string $popup_corners (chosen popup border radius)
 
 * @return array() $borders (keys: width(radius))
 * Called in splite_option_css() to generate custom CSS
 */
function splite_get_popup_border_values($popup_corners) {
	
	if( SPLITE_DEBUG ) {
		echo '<br/>Corners in Popup: '. $popup_corners; 
	}
	
	global $splite_opts; 	
	$borders = array();	
	$custom_popup_corners = isset($splite_opts['custom-popup-border']) ? $splite_opts['custom-popup-border'] : array('width'=>'20px');
	
	switch($popup_corners) {
		case 'square':
			$border_radius_value = '0px';
			break;
		case 'rounded':
			$border_radius_value = '20px';
			break;
		case 'custom':
			$border_radius_value = $custom_popup_corners['width'];
			break;
		default: 
			$border_radius_value = '0px';
	}
	
	$borders['radius'] = $border_radius_value; 
	return $borders; 
}

/**
 * Get Side Button Options
 * Since Version 1.0
 * @param string $side_button_scheme (chosen scheme for side button (inherit,custom))
 * @param string $side_button_background (chosen color when scheme is custom)
 
 * @return array() $side_button (keys: background-color)
 * Called in splite_option_css() to generate custom CSS
 */
function splite_get_side_button_values($side_button_scheme, $side_button_background) {
	
	if( SPLITE_DEBUG ) {
		echo '<br/>';
		echo '<br/>Side Button Scheme: '. $side_button_scheme; 
		echo '<br/>Side Button Background: '. $side_button_background; 
	}
	
	global $splite_opts; 	
	$side_button = array();	
	$side_button['background-color'] = '';
	
	if($side_button_scheme=='custom') {
		$side_button['background-color'] = $side_button_background;
	}
	
	return $side_button; 
}

/**
 * Get Submit Button Options
 * Since Version 1.0
 * @param string $submit_button_scheme (chosen scheme for submit button (inherit,custom))
 * @param string $submit_button_background (chosen color when scheme is custom)
 
 * @return array() $submit_button (keys: background-color)
 * Called in splite_option_css() to generate custom CSS
 */
function splite_get_submit_button_values($submit_button_scheme, $submit_button_background, $choose_theme_main_color) {
	
	if( SPLITE_DEBUG ) {
		echo '<br/>';
		echo '<br/>Submit Button Scheme: '. $submit_button_scheme; 
		echo '<br/>Submit Button Background: '. $submit_button_background; 
	}
	
	global $splite_opts; 	
	$submit_button = array();	
	$submit_button['background-color'] = '';
	
	if($submit_button_scheme=='custom') {
		$submit_button['background-color'] = $submit_button_background; 
	}
	elseif($submit_button_scheme=='inherit_from_color_scheme') {
		$submit_button['background-color'] = $choose_theme_main_color;
	}
	elseif($submit_button_scheme=='inherit_from_theme') {
		$submit_button['background-color'] = '';
	}
	
	if( SPLITE_DEBUG ) {
		echo '<br/>Submit Button: ';
		print_r($submit_button);
	}
	
	return $submit_button; 
}


/**
 * Get fire_activation_mode_script
 * Since Version 1.2
 * @param string $activation_mode (manually,autopopup,onscroll,onexit) 
 
 * @return none
 * Echo the script for activation mode chosen
 * Called in splite_add_my_popup() 
 */
function splite_fire_activation_mode_script($activation_mode) {
	
	switch($activation_mode['mode']) {
		case 'autopopup':
			echo '<script>
					setTimeout(function () { splite_loader(); }, '.($activation_mode['autopopup_delay'] * 1000).');
				</script>';
			break; 
		case 'onscroll':
			if( $activation_mode['onscroll_type'] == 'pixels'  ) {
				echo '<script>
					var eventFired = false;
					jQuery(window).on("scroll", function() {
						var currentPosition = jQuery(document).scrollTop();
						if (currentPosition > '.$activation_mode['onscroll_pixels'].' && eventFired == false) {
							eventFired = true;
							//console.log( "scrolled" );
							splite_loader(); 
						}
					});
				</script>';
			}
			if( $activation_mode['onscroll_type'] == 'percentage'  ) {
				echo '<script>
						var eventFired = false;
						jQuery(window).on("scroll", function() {
							var currentPosition = jQuery(document).scrollTop();
							if (currentPosition > jQuery(document).height()* '.($activation_mode['onscroll_percentage']/100).' && eventFired == false) {
								eventFired = true;
								//console.log( "scrolled" );
								splite_loader(); 
							}
						});
					</script>';
			}
			break; 
		case 'onexit':
			echo '<script>
					//console.log(event.pageY==0 || event.pageY==1 || event.pageY < jQuery(document).scrollTop());
					jQuery( "body" ).on( "mouseout", function( event ) {
					  	if (event.pageY==0 || event.pageY==1 || event.pageY < jQuery(document).scrollTop() ) {
					  		if(jQuery("#splite_popup_box").hasClass("manage")){
					  			splite_loader();
					  		}
					  	}
					});
					//splite_loader();
				</script>';						
			break;
		default: break; 
	}
}


//add_action('admin_menu', 'splite_all_settings_link'); 
/////////////////////////////////////////
// Link to Go To options.PHP (All Settings)
////////////////////////////////////////
function splite_all_settings_link() {
	add_options_page(__('All Settings'), __('All Settings'), 'administrator', 'options.php');
}

function splite_sanitize_array($arr){
    if(is_array($arr)){
        foreach ( array_keys( $arr ) as $field ) {
            $arr[ $field ] = sanitize_text_field( $field );
        }
    }
    return $arr;
}

/**
 * Recursive sanitation for text or array
 *
 * @param $args (array|string)
 * @return mixed
 */
function splite_sanitize_arr_str($args) {
    if( is_string($args) ){
        $args = sanitize_text_field($args);
    }elseif( is_array($args) ){
        foreach ( $args as $key => &$value ) {
            if ( is_array( $value ) ) {
                $value = splite_sanitize_arr_str($value);
            }
            else {
                $value = sanitize_text_field( $value );
            }
        }
    }
    return $args;
}


?>