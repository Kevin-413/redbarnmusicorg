<?php

    /**
     * For full documentation, please visit: http://docs.reduxframework.com/
     * For a more extensive sample-config file, you may look at:
     * https://github.com/reduxframework/redux-framework/blob/master/sample/sample-config.php
     */

    if ( ! class_exists( 'Redux' ) ) {
        return;
    }
	if ( ! class_exists( 'Redux' ) ) {
	  // Delete tgmpa dismiss flag
	  delete_user_meta( get_current_user_id(), 'tgmpa_dismissed_notice_myarcadetheme' );
	  return;
	}

	/** remove redux menu under the tools **/
	function splite_remove_redux_menu() {
	  remove_submenu_page('tools.php','redux-about');
	}
	add_action( 'admin_menu', 'splite_remove_redux_menu', 12 );

	// Deactivate News Flash
	$GLOBALS['redux_notice_check'] = 0;

    // This is your option name where all the Redux data is stored.
    $opt_name = SPLITE_REDUX_OPTION_NAME;

    /**
     * ---> SET ARGUMENTS
     * All the possible arguments for Redux.
     * For full documentation on arguments, please refer to: https://github.com/ReduxFramework/ReduxFramework/wiki/Arguments
     * */

    $theme = wp_get_theme(); //For use with some settings. Not necessary.
	require_once(ABSPATH.'wp-admin/includes/plugin.php');	
	$plugin = get_plugin_data( plugin_dir_path( __FILE__ ) .'../../slick-popup.php' );

    $args = array(
        'opt_name' => 'splite_opts',
        'dev_mode' => false,
        'show_options_object' => false,
		'ajax_save' => true,
		'allow_tracking' => false,
		'tour' => false,  
        'use_cdn' => true,
        'display_name' => $plugin['Name'] . ' Lite',
        'display_version' => $plugin['Version'],
        'page_slug' => 'slick-options',
        'page_title' => $plugin['Name'] . ' Options',
        'intro_text' => $plugin['Description'],
        'footer_text' => __('We will continue to innovate new features, if you have a suggestion just let us know.', 'slick-popup' ),
        'admin_bar' => false,
		//'page_parent' => 'sp-lite',
		'page_priority' => '80',
        'menu_type' => 'menu',
        'menu_type' => 'menu',
        'menu_icon' => plugins_url( 'img/menu_icon.png', __FILE__ ),
        'menu_title' => 'Slick Popup Lite',
        'allow_sub_menu' => false,
        'page_parent_post_type' => '',
        'default_show' => TRUE,
        'default_mark' => '*',
        'google_api_key' => 'AIzaSyB8QWjiiDqvVuTgOP1F394771EHteUu2CU',
        'class' => 'splite_container',
		
        'hints' => array(
            'icon' => 'el el-question-sign',
			'icon_position' => 'right',
			'icon_color' => '#23282D',
			'icon_size' => 'normal',
            'tip_style' => array(
				'color'   => 'red',
				'shadow'  => true,
				'rounded' => false,
				'style'   => 'cluetip',
			),
            'tip_position' => array(
                'my' => 'top left',
                'at' => 'bottom right',
            ),
            'tip_effect' => array(
                'show' => array(
                    'effect'   => 'fade',
					'duration' => '50',
					'event'    => 'mouseover',
                ),
                'hide' => array(
                    'effect'   => 'fade',
					'duration' => '50',
					'event'    => 'click mouseleave',
                ),
            ),
        ),
        'output' => TRUE,
        'output_tag' => TRUE,
        'settings_api' => TRUE,
        'compiler' => TRUE,
        'page_permissions' => 'manage_options',
        'save_defaults' => TRUE,
        'show_import_export' => FALSE,
        'database' => 'options',
        'transient_time' => '3600',
        'network_sites' => TRUE,
        'hide_reset' => TRUE,
		'footer_credit' => 'Slick Popup Lite by <a href="http://www.slickpopup.com/">Om Ak Solutions</a>',
    );

    
    // ADMIN BAR LINKS -> Setup custom links in the admin bar menu as external items.
    $args['admin_bar_links'] = array(); 
	$args['admin_bar_links'][] = array(
        'id'    => 'sp-demo',
        'href'  => 'http://www.slick-popup.omaksolutions.com/',
        'title' => __( 'Demo', 'slick-popup' ),
    );

    $args['admin_bar_links'][] = array(
        'id'    => 'sp-support',
        'href'  => 'https://wordpress.org/support/plugin/slick-popup',
        'title' => __( 'Support', 'slick-popup' ),
    );

    $args['admin_bar_links'][] = array(
        'id'    => 'sp-docs',
        'href'  => 'http://www.slick-popup.omaksolutions.com/docs',
        'title' => __( 'Documentation', 'slick-popup' ),
    );

    // SOCIAL ICONS -> Setup custom links in the footer for quick links in your panel footer icons.
    $args['share_icons'] = array(); 
    $args['share_icons'][] = array(
        'url'   => 'https://www.facebook.com/pages/OmAkSolutions',
        'title' => __('Like us on Facebook', 'slick-popup' ),
        'icon'  => 'el el-facebook'
    );
    $args['share_icons'][] = array(
        'url'   => 'http://twitter.com/OmAkSolutions',
        'title' => __('Follow us on Twitter', 'slick-popup' ),
        'icon'  => 'el el-twitter'
    );
    $args['share_icons'][] = array(
        'url'   => 'http://www.linkedin.com/company/Om-Ak-Solutions',
        'title' => __('Find us on LinkedIn', 'slick-popup' ),
        'icon'  => 'el el-linkedin'
    );

    // Panel Intro text -> before the form
    if ( ! isset( $args['global_variable'] ) || $args['global_variable'] !== false ) {
        if ( ! empty( $args['global_variable'] ) ) {
            $v = $args['global_variable'];
        } else {
            $v = str_replace( '-', '_', $args['opt_name'] );
        }
        $args['intro_text'] = sprintf( __( '', 'slick-popup' ), $v );
    } else {
        $args['intro_text'] = __( '', 'slick-popup' );
    }
	
	// Intro Text Emptied
	$args['intro_text'] = sprintf( __( '', 'slick-popup' ), $v );	
	
    // Add content after the form.
    $args['footer_text'] = __( '<p>We will continue to innovate new features, if you have a suggestion just let us know at <strong>poke@slickpopup.com</strong></p>', 'slick-popup' );

    Redux::set_args( $opt_name, $args );

    /*
     * ---> END ARGUMENTS
     */

    /*
     * ---> START HELP TABS
     */

    $tabs = array(
        array(
            'id'      => 'sp-pro-helptab-1',
            'title'   => __( 'Support', 'slick-popup' ),
            'content' => __( '<p>If you face any issues using the plugin, please shoot us an e-mail at: poke@slickpopup.com</p>', 'slick-popup' )
        ),
		array(
            'id'      => 'sp-pro-helptab-2',
            'title'   => __( 'Support', 'slick-popup' ),
            'content' => __( '<p>If you face any issues using the plugin, please shoot us an e-mail at: poke@slickpopup.com</p>', 'slick-popup' )
        ),
    );
	unset( $tabs[1] );
    Redux::set_help_tab( $opt_name, $tabs );

    // Set the help sidebar
    $content = __( '<p><strong>We are mostly online at Skype: ak.singla47</strong></p>', 'slick-popup' );
    Redux::set_help_sidebar( $opt_name, $content );


    /*
     * <--- END HELP TABS
     */


    /*
     *
     * ---> START SECTIONS
     *
     */

    /*

        As of Redux 3.5+, there is an extensive API. This API can be used in a mix/match mode allowing for


     */
	
	/////////////////////////////////////////////////
	// SECTION: Configuration
	/////////////////////////////////////////////////
	if ( 1 ) {
    	Redux::setSection( $opt_name, array(
			'title'  => __( 'Configuration', 'slick-popup' ),
			'id'     => 'configuration-settings',
			'desc'   => __( '', 'slick-popup' ),
			'icon'   => 'el el-cog',
			'fields' => array(						
				array(
					'id'       => 'plugin_state',
					'type'     => 'switch',
					'title'    => __( 'Plugin State', 'slick-popup' ),
					'subtitle' => __( 'The power switch.', 'slick-popup' ),
					'default'  => 1,
					'on'       => __('Enable', 'slick-popup' ),
					'off'      => __('Disable', 'slick-popup' ),
				),				
				array(
					'id'       => 'plugin_state_on_mobile',
					'type'     => 'switch',
					'required' => array( 'plugin_state', '=', '1' ),
					'title'    => __( 'Mobile State', 'slick-popup' ),
					'subtitle' => __( 'Enable/Disable on Mobile View.', 'slick-popup' ),
					'default' => __( '<b>Default:</b> Enable', 'slick-popup' ),
					'default'  => 1,
					'on'       => __('Enable', 'slick-popup' ),
					'off'      => __('Disable', 'slick-popup' ),
					'hint'     => array(
						'title'     => 'Mobile State',
						'content'   => 'Disable - will complete switch off all functionality of the plugin on the front-end.',
					),
				),					
				array(
					'id'       => 'delete_data',
					'type'     => 'switch',
					'required' => array( 'plugin_state', '=', '1' ),
					'title'    => __( 'Keep Settings', 'slick-popup' ),
					'subtitle' => __( 'Keep/Delete plugin settings after uninstallation.', 'slick-popup' ),
					'default'  => 0,
					'on'       => __('Delete', 'slick-popup' ),
					'off'      => __('Keep', 'slick-popup' ),
					'hint'     => array(
						'title'     => __('Keep Settings', 'slick-popup' ),
						'content'   => __('Choose <b>Keep</b> if you do not plan to copmletely remove the plugin settings after uninstallation.', 'slick-popup' ),
					),
				),		
			)
		) );
	} // endif 1

	/////////////////////////////////////////////////
	// SECTION: Popup Settings
	/////////////////////////////////////////////////
	if ( 1 ) {
		Redux::setSection( $opt_name, array(
			'title' => __( 'Popup Settings', 'slick-popup' ),
			'id'    => 'popup-styles',
			'desc'  => __( '', 'slick-popup' ),
			'icon'  => 'el el-comment',
			'fields'     => array(
				/////////////////////////////////////////////////
				// Section: Layout & Color Scheme (layout)
				////////////////////////////////////////////////
					array(
						'id'       => 'basic-config',
						'type'     => 'section',				
						'title'    => __( 'Basic Configuration', 'slick-popup' ),
						'subtitle' => __( 'Choose contact form and where to show', 'slick-popup' ),
						'indent'   => true, // Indent all options below until the next 'section' option is set.
					),
						array(
							'id'            => 'form-content',
							'type'          => 'textarea',
							'required' 		=> array( 'plugin_state', '=', '1' ),
							'title'         => __( 'Popup HTML / Shortcode Content', 'slick-popup' ),
							'subtitle'      => __( 'Paste your Forminator shortcode and any simple HTML links that should appear inside the popup.', 'slick-popup' ),
							'desc'          => __( 'Example: [forminator_form id="123"] plus TEXT/CALL/Privacy Policy links. The popup renders this field with safe HTML and shortcodes.', 'slick-popup' ),
							'default'       => '<div class="rbm-contact-popup-content">\n\n  [forminator_form id="123"]\n\n  <div class="rbm-contact-popup-links">\n    <p><strong>TEXT us at</strong> <a href="sms:4132568899">413-256-8899</a></p>\n    <p><strong>CALL us at</strong> <a href="tel:4132568899">413-256-8899</a></p>\n    <p><a href="/privacy-policy/">PRIVACY POLICY</a></p>\n  </div>\n\n</div>',
							'hint'     => array(
								'title'     => 'Popup HTML / Shortcode Content',
								'content'   => 'Use this as an editable content box. Replace the Forminator ID before going live.',
							),
						),		
						array(
							'id'            => 'where_to_show',
							'type'          => 'select',
							'required' 		=> array( 'plugin_state', '=', '1' ),
							'title'         => __( 'Where to show the form?', 'slick-popup' ),
							'subtitle'      => __( 'Choose the display of the popup form.', 'slick-popup' ),
							'desc'          => __( '', 'slick-popup' ),
							'options'  => array(
										'everywhere' => 'Everywhere',
										'onselected' => 'Only Selected Pages',
										'notonselected' => __('Not On Selected Pages', 'slick-popup' ),
									),
							'hint'     => array(
								'title'     => 'Where do you want the Popup to be displayed?',
								'content'   => 'Either it can be on specific pages or on all pages.',
							),
							'default'  => 'everywhere'
						),
						array(
							'id'            => 'choose_pages',
							'type'          => 'select',
							'multi'          => true,
							'data' 			=> 'pages',
		                    'args' 			=> array( 'posts_per_page' => -1),
							'required' 		=> array( array('plugin_state', '=', '1'), array('where_to_show', '!=', 'everywhere') ),
							'title'         => __( 'Choose Your Pages', 'slick-popup' ),
							'subtitle'      => __( 'Select the pages to exclude or include for popup form display.', 'slick-popup' ),
							'desc'          => __( '<a target="_blank" href="', 'slick-popup' ) .admin_url( '/edit.php?post_type=page' ). __( '">See all Pages</a>', 'slick-popup' ),
							'hint'     => array(
								'title'     => 'Select your pages',
								'content'   => 'Choose the pages where you want to display/not show the popup.',
							),
						),
				/////////////////////////////////////////////////
				// Section: Layout & Color Scheme (layout)
				////////////////////////////////////////////////
					array (
						'id'       => 'section-layout',
						'type'     => 'section',				
						'title'    => __( 'Layout & Color Scheme', 'slick-popup' ),
						'subtitle' => __( 'Choose your desired layout and color scheme.', 'slick-popup' ),
						'indent'   => true, // Indent all options below until the next 'section' option is set.
					),
						array(
							'id'       => 'choose-layout',
							//'type'     => 'select',
							'type'     => 'image_select',
							'title'    => __( 'Choose Layout', 'slick-popup' ),
							'subtitle' => __( 'Choose one of the three layouts available.', 'slick-popup' ),
							'desc'     => __( '', 'slick-popup' ),
							//Must provide key => value pairs for select options
							'options'  => array(								
								'centered' => array(
									'alt' => __('Centered Layout', 'slick-popup' ),
									'img' => SPLITE_PLUGIN_IMG_URL . '/layout-centered.png',
									'title' => __('Centered', 'slick-popup' ),
								),
								'full' => array(
									'alt' => __('Full Height', 'slick-popup' ),
									'img' => SPLITE_PLUGIN_IMG_URL . '/layout-full.png',
									'title' => __('Full Height', 'slick-popup' ),
								),
							),
							'default'  => 'centered',
							'hint'     => array(
								'title'     => __('Choose Layout', 'slick-popup' ),
								'content'   => __('Currently two layouts available: <b>Full</b> and <b>Centered</b>. Full means full height popup in the center of the screen, and Centered has some space above and below the popup.', 'slick-popup' ),
							),
						),
					array(
							'id'       => 'custom-popup-layout',
							'type'     => 'select',
							'title'    => __( 'Height & Width', 'slick-popup' ),
							'subtitle' => __( 'Use pre-defined layouts or set your own height and width.', 'slick-popup' ),
							'desc'     => __( '', 'slick-popup' ),
							//Must provide key => value pairs for select options
							'options'  => array(
								'predefined' => __( 'As Per chosen Layout', 'slick-popup' ),
								'change' => __( 'Set Your Own Height and Width', 'slick-popup' ),								
							),
							'hint'     => array(
								'title'     => 'Adjust the height and width of your popup',
								'content'   => 'Enter the values in px for an exact representation of your popup.',
							),
							'default'  => 'predefined'
						),	
							array(
								'id'       => 'popup-width',
								'type'     => 'dimensions',
								//'units'    => array('em','px','%'),							
								'units'    => array('px','%'),
								'required' => array( 'custom-popup-layout', '=', 'change' ),
								'title'    => __('Popup Width', 'slick-popup'),
								'subtitle' => __('Set width of the popup.', 'slick-popup'),
								'desc'     => __('Demo forms have width: 600px', 'slick-popup'),
								'height' 	=> false,
								'default'  => array(
									'width'  => '600'
								),
							),
							array(
								'id'       => 'popup-height',
								'type'     => 'dimensions',
								'units'    => array('px','%'),
								'required' => array( 'custom-popup-layout', '=', 'change' ),
								'title'    => __('Popup Height', 'slick-popup'),
								'subtitle' => __('Set height of the popup.', 'slick-popup'),
								'desc'     => __('It is suggested that you choose a percent based height.', 'slick-popup'),
								'width' 	=> false,
								'default'  => array(
									'height'  => '450'
								),
							),	
						array(
							'id'       => 'popup-corners',
							'type'     => 'select',
							'title'    => __( 'Popup Corners', 'slick-popup' ),
							'subtitle' => __( 'Choose the radius of the popup border.', 'slick-popup' ),
							'desc'     => __( '<b>Default:</b> Square (Zero roundness)', 'slick-popup' ),
							//Must provide key => value pairs for select options
							'options'  => array(
								'square' => 'Square',					
								'rounded' => 'Rounded',
								'custom' => __('Set Your Own', 'slick-popup' ),
							),
							'hint'     => array(
								'title'     => 'Border Radius of the popup',
								'content'   => 'You can either choose rounded or can set a custom it is not adviced that you choose a value which is greater than 20px.',
							),
							'default'  => 'square'
						),		
							array(
								'id'       => 'custom-popup-border',
								'type'           => 'dimensions',             
								'required' => array( 'popup-corners', '=', 'custom' ),
								'output'   => array( '' ),
								'units'          => array( 'px', '%' ),    // You can specify a unit value. Possible: px, em, %
								'units_extended' => 'true',  // Allow users to select any type of unit
								'title'          => __( 'Popup Border Radius', 'slick-popup' ),
								'subtitle'       => __( 'Set a border radius property for the popup.', 'slick-popup' ),
								'desc'           => __( 'Units: px or % (50% is max).', 'slick-popup' ),
								'height'         => false,
								'default'        => array(
									'width'  => 20,
									'height' => 100,
								)
							),	
				/////////////////////////////////////////////////
				// Section: Activation Mode
				////////////////////////////////////////////////
				array(
					'id'       => 'section-activation-mode',
					'type'     => 'section',				
					'title'    => __( 'Activation Mode', 'slick-popup' ),
					'subtitle' => __( '', 'slick-popup' ),
					'indent'   => true, // Indent all options below until the next 'section' option is set.
				),
					array(
						'id'            => 'activation_mode',
						'type'          => 'select',
						'title'         => __( 'How to activate popup?', 'slick-popup' ),
						'subtitle'      => __( 'Choose how the popup should activate.', 'slick-popup' ),
						'desc'          => __( 'Premium: Auto Popup, On-scroll Popup, Forced Popup', 'slick-popup' ),
						'options'  => array(
							'manually' => __('On-Click (Default)', 'slick-popup' ),
							'onexit'  => __('On-Exit Intent', 'slick-popup'),	
						),
						'default'  => 'manually'
					),
					array(
						'id'     => 'notice-splite-activation-mode',
						'type'   => 'info',
						'style'   => 'warning',
						'notice' => false,
						'desc'   => __( '<span style="font-weight:bold;font-size:1.1em;">More features in Slick Popup Pro. <a href="https://codecanyon.net/item/slick-popup-pro-/16115931?ref=OmAkSols">Buy Now</a></span>', 'slick-popup' )
					),						
						
				/////////////////////////////////////////////////
				// Section: Animations
				////////////////////////////////////////////////				
				array(
					'id'       => 'section-animations',
					'type'     => 'section',				
					'title'    => __( 'Animations settings', 'slick-popup' ),
					'subtitle' => __( '', 'slick-popup' ),
					'indent'   => true, // Indent all options below until the next 'section' option is set.
				),
					array(
						'id'       => 'loader-animation',
						'type'     => 'select',
						'title'    => __( 'onLoad Effect', 'slick-popup' ),
						'subtitle' => __( 'Animation when loading popup', 'slick-popup' ),
						'desc'     => __( '', 'slick-popup' ),
						'default'  => 'fadeInDown',
						'hint'     => array(
							'title'     => 'How popup will load when the button is clicked.',
							'content'   => 'This is the animation as the popup appears when you click on the button.',
						),
						'options'  => array(
							'FadeIn Effects' => array(
								'fadeIn' => 'fadeIn',
								'fadeInDown' => 'fadeInDown',
								'fadeInUp' => 'fadeInUp',
								'fadeInRight' => 'fadeInRight',
								'fadeInLeft' => 'fadeInLeft',
							),
							'Attention Seekers' => array(
								'zoomIn' => 'zoomIn',
								'rotateIn' => 'rotateIn',
							),
						),
					),
					array(
						'id'       => 'loader-speed',
						'type'     => 'slider', 
						'title'    => __('onLoad Speed', 'slick-popup'),
						'subtitle' => __('Set Popup load speed','slick-popup'),
						'desc'     => __('Min:0.1, Max:5, Best: 0-1' , 'slick-popup'),
						'default' => .7,
						'min' => 0,
						'step' => .1,
						'max' => 5,
						'resolution' => 0.01,
						'display_value' => 'text',
						'hint'     => array(
							'title'     => 'Loading Time.',
							'content'   => 'This gives time to the Loading animation.',
						),
					),	
					array(
						'id'       => 'unloader-animation',
						'type'     => 'select',
						'title'    => __( 'unLoad Effect', 'slick-popup' ),
						'subtitle' => __( 'Animation when unloading popup', 'slick-popup' ),
						'desc'     => __( '', 'slick-popup' ),
						'default'  => 'fadeOutDown',	
						'hint'     => array(
							'title'     => 'How popup will unload when the popup is closed.',
							'content'   => 'This animation occurs when you close the popup.',
						),					
						'options'  => array(
							'FadeOut Effects' => array(
								'fadeOut' => 'fadeOut',
								'fadeOutDown' => 'fadeOutDown',
								'fadeOutUp' => 'fadeOutUp',
								'fadeOutRight' => 'fadeOutRight',
								'fadeOutLeft' => 'fadeOutLeft',
							),
							'Attention Seekers' => array(
								'zoomOut' => 'zoomOut',
								'rotateOut' => 'rotateOut',
							),
						),
					),
					array(
						'id'       => 'unloader-speed',
						'type'     => 'slider', 
						'title'    => __('unload Speed', 'slick-popup'),
						'subtitle' => __('Set Popup unload speed','slick-popup'),
						'desc'     => __('Min:0.1, Max:5, Best: 0-1' , 'slick-popup'),
						'default' => .3,
						'min' => 0,
						'step' => .1,
						'max' => 5,
						'resolution' => 0.01,
						'display_value' => 'text',
						'hint'     => array(
							'title'     => 'Unloading Time.',
							'content'   => 'This gives time to the Unloading animation.',
						),
					),		
				) // end fields array
			)
		);
	}
	
	/////////////////////////////////////////////////
	// SECTION: Popup Heading
	/////////////////////////////////////////////////
	if ( 1 ) {
		
		Redux::setSection( $opt_name, array(
			'title' => __( 'Popup Heading', 'slick-popup' ),
			'id'    => 'edit-popup-heading',
			'desc'  => __( 'Edit Popup Heading Text and Styles', 'slick-popup' ),
			'icon'  => 'el el-iphone-home',
			'fields'     => array(
				/////////////////////////////////////////////////
				// Section: Popup Heading Text
				////////////////////////////////////////////////				
						array(
							'id'       => 'heading-text',
							'type'     => 'section',				
							'title'    => __( 'Popup Heading Text', 'slick-popup' ),
							'indent'   => true, // Indent all options below until the next 'section' option is set.
						),
							array(
								'id'       => 'popup-heading',
								'type'     => 'text',
								'title'    => __( 'Heading', 'slick-popup' ),
								'subtitle' => __( 'Main heading on the popup.', 'slick-popup' ),
								'desc'     => __( 'Leave empty if you do not want popup heading ', 'slick-popup' ),
								'default'  => 'STILL NOT SURE WHAT TO DO?',
								'hint'      => array(
									'title'     => 'Popup Heading',
									'content'   => 'Main heading of the popup. If you leave you heading field blank please do save the settings and check whether the popup looks nice or not.',
								),
							),	
				/////////////////////////////////////////////////
				// Section: Popup Heading Typograpghy
				////////////////////////////////////////////////			
						array (
							'id'       => 'heading-typography',
							'type'     => 'section',				
							'title'    => __( 'Popup Heading Typography', 'slick-popup' ),
							'indent'   => true, // Indent all options below until the next 'section' option is set.
						),	
						array (
							'id'       => 'heading-typography',
							'type'     => 'typography',
							//'required' => array( 'use_heading_font', '=', 1 ),
							'title'    => __( 'Heading Font', 'slick-popup' ),
							'subtitle' => __( 'Specify the heading font properties.', 'slick-popup' ),
							'desc'		=> __('Font Color is important to look good with your chosen color scheme.', 'slick-popup' ),
							'google'   => true,
							'default'  => array(
								'color'       => 	'#F1F1F1',
								'font-size'   => 	'28px',
								'line-height' =>	'32px',
								'font-family' => 	'Open Sans',
								'font-weight' => 	'900',
							),
							'text-align'	=> false,
							'font-subsets'	=> false,
						),	
						array (
							'id'       => 'custom-text-color',
							'type'     => 'color',
							//'required' => array( 'choose-color-scheme', '=', 'custom_theme' ),
							'output'   => array( '' ),
							'title'    => __( 'Close button Color', 'slick-popup' ),
							'subtitle' => __( 'Pick a color for close button.', 'slick-popup' ),
							'desc' => __( 'This also applies to <strong>Close Icon "X"</strong> and <strong>form submission response.</strong>', 'slick-popup' ),
							'hint'     => array(
								'title'     => 'How your Close button will look.',
								'content'   => "The 'X' button will appear on the top right corner you can change the color of the popup.",
							),
							'default'  => '#EFEFEF',
						),		
			)
		) );
	}

	/////////////////////////////////////////////////
	// SECTION: Popup Body
	/////////////////////////////////////////////////
	if ( 1 ) {
		
		Redux::setSection( $opt_name, array(
			'title' => __( 'Popup Body', 'slick-popup' ),
			'id'    => 'ledit-popup-body',
			'desc'  => __( 'Edit Popup Body Text and Styles', 'slick-popup' ),
			'icon'  => 'el el-iphone-home',
			'fields'     => array(
				/////////////////////////////////////////////////
				// Section: Popup Body Text
				////////////////////////////////////////////////				
						array(
							'id'       => 'body-text',
							'type'     => 'section',				
							'title'    => __( 'Popup Body Text', 'slick-popup' ),
							'indent'   => true, // Indent all options below until the next 'section' option is set.
						),	
							array(
								'id'       => 'popup-cta-text',
								'type'     => 'editor',
								'title'    => __( 'Call To Action', 'slick-popup' ),
								'subtitle' => __( 'Main description that will actually make your visitor to fill up the form.', 'slick-popup' ),
								'desc'     => __( '<b>Default:</b> We are glad that you preferred to contact us. Please fill our short form and one of our friendly team members will contact you back shortly.', 'slick-popup' ),
								'default'  => 'We are glad that you preferred to contact us. Please fill our short form and one of our friendly team members will contact you back.',
								'hint'      => array(
									'title'     => 'Call To Action',
									'content'   => 'This text will appear above the form. Choose something that encourages user to fill up the form. You can add text,image or video it your choice.',
								),
							),
				/////////////////////////////////////////////////
				// Section: Popup Body Typography
				////////////////////////////////////////////////			
						array(
							'id'       => 'body-typography',
							'type'     => 'section',				
							'title'    => __( 'Popup Body Typography', 'slick-popup' ),
							'indent'   => true, // Indent all options below until the next 'section' option is set.
						),	
							array(
								'id'       => 'cta-typography',
								'type'     => 'typography',
								//'required' => array( 'use_cta_font', '=', 1 ),
								'title'    => __( 'Call To Action Font', 'slick-popup' ),
								'subtitle' => __( 'Specify these font properties.', 'slick-popup' ),
								'google'   => true,
								'default'  => array(
									'color'       => '#484848',
									'font-size'   => '13px',
									'line-height'   => '21px',
									'font-family' => 'Noto Sans',
									'font-weight' => 	'normal',
									'text-align' => 	'center',
								),
								'font-subsets'	=> false,
							),	
				/////////////////////////////////////////////////
				// Section: Popup Body Colors
				////////////////////////////////////////////////			
						array(
							'id'       => 'body-color',
							'type'     => 'section',				
							'title'    => __( 'Popup Body Colors', 'slick-popup' ),
							'indent'   => true, // Indent all options below until the next 'section' option is set.
						),	
							array(
								'id'       => 'choose-color-scheme',
								'type'     => 'image_select',
								'title'    => __( 'Color Scheme', 'slick-popup' ),
								'subtitle' => __( 'Choose your desired cover scheme.', 'slick-popup' ),
								'desc'     => __( '<span style="font-weight:bold;font-size:1.1em;">Choose one of our pre-defined color schemes or set your own. <a href="https://codecanyon.net/item/slick-popup-pro-/16115931?ref=OmAkSols">More in Pro</a></span>', 'slick-popup' ),
								'hint'     => array(
									'title'     => 'Color Scheme of the Popup',
									'content'   => 'You can set a different color for Popup header and body or you can choose the same. The predefined color schemes have different colors for header and body. If you want the same color for header and body then choose set your own option. Which also has the option to set image as the background.',
								),
								'options'  => array(
									'master_red' => array(
										'alt' => __('Master Red', 'slick-popup' ),
										'img' => SPLITE_PLUGIN_IMG_URL . '/scheme-master-red.png',
										'title' => __('Master Red', 'slick-popup' ),
									),
									'creamy_orange' => array(
										'alt' => __('Creamy Orange', 'slick-popup' ),
										'img' => SPLITE_PLUGIN_IMG_URL . '/scheme-creamy-orange.png',
										'title' => __('Creamy Orange', 'slick-popup' ),
									),
									'light_blue' => array(
										'alt' => __('Light Blue', 'slick-popup' ),
										'img' => SPLITE_PLUGIN_IMG_URL . '/scheme-light-blue.png',
										'title' => __('Light Blue', 'slick-popup' ),
									),
									'cool_green' => array(
										'alt' => __('Cool Green', 'slick-popup' ),
										'img' => SPLITE_PLUGIN_IMG_URL . '/scheme-cool-green.png',
										'title' => __('Cool Green', 'slick-popup' ),
									),						
									'dark' => array(
										'alt' => __('Classic Grey', 'slick-popup' ),
										'img' => SPLITE_PLUGIN_IMG_URL . '/scheme-classic-grey.png',
										'title' => __('Classic Grey', 'slick-popup' ),
									),
									'custom_theme' => array(
										'alt' => __('Set Your Own', 'slick-popup' ),
										'img' => SPLITE_PLUGIN_IMG_URL . '/scheme-custom-theme.png',
										'title' => __('Set Your Own', 'slick-popup' ),
									),
								),
								'default'  => 'cool_green'
							),
								// If Color Scheme = custom_theme
								array(
									'id'       => 'custom-theme-color',
									'type'     => 'color',
									'required' => array( 'choose-color-scheme', '=', 'custom_theme' ),
									'output'   => array( '' ),
									'title'    => __( 'Heading Color', 'slick-popup' ),
									'subtitle' => __( 'Pick a color for theme of your popup.', 'slick-popup' ),
									'desc' => __( 'This color will be used to create theme of your popup.', 'slick-popup' ),
									'hint'     => array(
										'title'     => 'Header background color.',
										'content'   => 'The color chosen will be in the background of the popup.',
									),
									'default'  => '#333',
								),
								array(
									'id'       => 'custom-form-background-color',
									'type'     => 'background',
									'required' => array( 'choose-color-scheme', '=', 'custom_theme' ),
									'output'   => array( '' ),
									'title'    => __( 'Popup Background', 'slick-popup' ),
									'subtitle' => __( 'Please style the background for the form area in popup.', 'slick-popup' ),
									'hint'     => array(
										'title'     => 'Body Background Color.',
										'content'   => 'You can either choose a color or an image.',
									),
									'desc' => __( 'Note: If you choose an image background then the title area will be transparent.', 'slick-popup' ),
									'default'  => array(
										'background-color' => '#EFEFEF',
										'background-image' => '',
										'background-size' => 'cover',
										'background-position' => 'center center',
										'background-attachment' => '',
										'background-repeat' => 'no-repeat',
										'background-media' => '',
									),
								),
								array(
									'id'     => 'notice-splite-cover',
									'type'   => 'info',
									'style'  => 'normal',
									'notice' => false,
									'desc'   => __( 'The Options for the Curtain (i.e., Background of the popup which is slightly transparent) is available in Slick Popup Pro. You can overwrite the options by using Custom CSS (div#splite_curtain {Your CSS Code})', 'slick-popup' ),
								),								
			)
		) );
	}
	
	/////////////////////////////////////////////////
	// SECTION: Side Button
	/////////////////////////////////////////////////
	if ( 1 ) {
		
		Redux::setSection( $opt_name, array(
			'title' => __( 'Side Button', 'slick-popup' ),
			'id'    => 'side-button-settings',
			'desc'  => __( 'Options to change position and color scheme for the side button.', 'slick-popup' ),
			'icon'  => 'el el-iphone-home',
			'fields'     => array(				
				array(
					'id'       => 'side-button-position',
					'type'     => 'select',
					'title'    => __( 'Choose Position', 'slick-popup' ),
					'subtitle' => __( 'Choose the position of side button.', 'slick-popup' ),
					'hint'     => array(
						'title'     => 'Position of the Side Button.',
						'content'   => 'You can choose either Left or Right. You choose none if you are setting your popup on the click of a specific button.',
					),
					//Must provide key => value pairs for select options
					'options'  => array(
						'pos_right' => __( 'Right', 'slick-popup' ),
						'pos_left' => __( 'Left', 'slick-popup' ),
						'pos_none' => __( 'None (Hide)', 'slick-popup' ),
					),
					'default'  => 'pos_right'
				),				
				array(
					'id'       => 'side-button-text',
					'type'     => 'text',
					'title'    => __( 'Button Text', 'slick-popup' ),
					'subtitle'     => __( 'What should your button say?', 'slick-popup' ),
					'hint'     => array(
						'title'     => 'Side Button Text.',
						'content'   => 'The text of the side button usually describes the header of the popup in a 1-2 words.',
					),
					'desc' => __( '<b>Suggestions:</b> "Need Help?" "Subscribe" "Get a quote!" "Have a query?"<br/><b>Default:</b> Contact Us', 'slick-popup' ),
					'default'  => 'CONTACT US',
				),	
				array(
					'id'       => 'side-button-typography',
					'type'     => 'typography',
					//'required' => array( 'use_side_button_font', '=', 1 ),
					'title'    => __( 'Side Button Font', 'slick-popup' ),
					'subtitle' => __( 'Typography and Font properties.', 'slick-popup' ),
					'google'   => true,
					'default'  => array(
						'font-family' 	=> 'Open Sans',
						'color'       => '#F1F1F1',
						'font-size'   => '14px',
						'line-height'   => '18px',
						'font-weight' 	=> '700',
					),				
					'text-align'	=> false,
					'font-subsets'	=> false,
				),
				array(
					'id'       => 'choose-side-button',
					'type'     => 'select',
					'title'    => __( 'Side Button Scheme', 'slick-popup' ),
					'subtitle' => __( 'Choose styles and appearance.', 'slick-popup' ),
					'hint'     => array(
						'title'     => 'Side button Color.',
						'content'   => 'You can either leave it as it is it will pick up the existing colors from the popup and choose a color or you can choose your on in set your own.',
					),
					'desc'     => __( '<b>Default:</b> Inherit From Color Scheme', 'slick-popup' ),
					//Must provide key => value pairs for select options
					'options'  => array(
						'inherit' => __('Inherit From Color Scheme', 'slick-popup' ),
						'custom' => __('Set Your Own', 'slick-popup' ),
					),
					'default'  => 'inherit'
				),			
				array(
					'id'       => 'side-button-background',
					'type'     => 'background',
					'required' => array( 'choose-side-button', '=', 'custom' ),
					'output'   => array( '' ),
					'title'    => __( 'Button Background', 'slick-popup' ),
					'subtitle' => __( 'Button background with image, color, etc.', 'slick-popup' ),
					'default'   => array( 
							'background-color' => '#333333',
						),
					'background-color'			=> true,
					'background-repeat'			=> false,
					'background-attachment'		=> false,
					'background-position'		=> false,
					'background-image'			=> false,
					'background-clip'			=> false,
					'background-origin'			=> false,
					'background-size'			=> false,
					'preview_media'				=> false,
					'preview'					=> false,
					'preview_height'			=> false,
					'transparent'			=> false,
				),
			)
		) );
	}

	/////////////////////////////////////////////////
	// SECTION: Submit Button
	/////////////////////////////////////////////////
	if ( 1 ) {
		
		Redux::setSection( $opt_name, array(
			'title' => __( 'Submit Button', 'slick-popup' ),
			'id'    => 'submit-button-settings',
			'desc'  => __( 'Options to change position and color scheme for the submit button.', 'slick-popup' ),
			'icon'  => 'el el-iphone-home',
			'fields'     => array(								
				array(
					'id'       => 'submit-button-typography',
					'type'     => 'typography',
					//'required' => array( 'use_submit_button_font', '=', 1 ),
					'title'    => __( 'Submit Button Font', 'slick-popup' ),
					'subtitle' => __( 'Specify the submit button font properties.', 'slick-popup' ),
					'google'   => true,
					'default'  => array(						
						'font-family' 	=> 'Open Sans',
						'color'       	=> '#F1F1F1',
						'font-size'   	=> '22px',
						'line-height'   => '24px',
						'font-weight' 	=> '700',
					),				
					'text-align'	=> false,
					'font-subsets'	=> false,
				),	
				array(
						'id'       => 'choose-submit-button',
						'type'     => 'select',
						'title'    => __( 'Submit Button Styles', 'slick-popup' ),
						'subtitle' => __( 'Choose appearance of the form <b>Submit</b> button.', 'slick-popup' ),
						'desc'     => __( '<b>Default:</b> Inherit', 'slick-popup' ),
						//Must provide key => value pairs for select options
						'options'  => array(
							'inherit_from_theme' => __('Use styles from theme', 'slick-popup' ),
							'inherit_from_color_scheme' => __('Inherit from color scheme', 'slick-popup' ),
							'custom' => __('Set your own colors', 'slick-popup' ),
						),
						'default'  => 'inherit_from_color_scheme',
						'hint'      => array(
							'title'     => __( 'Color Scheme', 'slick-popup' ),
							'content'   =>  __( 'Choose one of the pre-packed color themes or create your own.', 'slick-popup' ),
						),
					),	
						// If choose-submit-button = custom
						array(
							'id'       => 'submit-button-background',
							'type'     => 'background',
							'required' => array( 'choose-submit-button', '=', 'custom' ),
							'output'   => array( '' ),
							'title'    => __( 'Button Background', 'slick-popup' ),
							'subtitle' => __( 'Choose background color for the "Submit" button.', 'slick-popup' ),
							'default'   => array( 
								'background-color' => '#333333',
							),
							'background-color'			=> true,
							'background-repeat'			=> false,
							'background-attachment'		=> false,
							'background-position'		=> false,
							'background-image'			=> false,
							'background-clip'			=> false,
							'background-origin'			=> false,
							'background-size'			=> false,
							'preview_media'				=> false,
							'preview'					=> false,
							'preview_height'			=> false,
							'transparent'				=> false,
						),	
			)
		) );
	}

	/////////////////////////////////////////////////
	// SECTION: Advanced Settings
	/////////////////////////////////////////////////
	if ( 1 ) {
		
		Redux::setSection( $opt_name, array(
			'title' => __( 'Advanced Settings', 'slick-popup' ),
			'id'    => 'advance-settings-settings',
			'desc'  => __( 'Advanced Settings for your Popup', 'slick-popup' ),
			'icon'  => 'el el-cog',
			'fields'     => array(
				/////////////////////////////////////////////////
				// Section: Heading & Description (typography)
				////////////////////////////////////////////////				
				array(
					'id'       => 'custom-css-code',
					'type'     => 'ace_editor',
					'title'    => __( 'CSS Code', 'slick-popup' ),
					'subtitle' => __( 'Paste your CSS code here.', 'slick-popup' ),
					'mode'     => 'css',
					'theme'    => 'monokai',
				),

				array(
					'id'		=> 'external-selector',
					'type'		=> 'text',
					'title'		=> __( 'External Selector', 'slick-popup' ),
					'subtitle'	=> __( 'Enter class or id of External Selector', 'slick-popup' ),
					'default'	=> '.splite-showpopup',
					'hint'      => array(
							'title'     => __( 'External Selector', 'slick-popup' ),
							'content'   =>  __( 'Put the class or id from which you want the popup to appear', 'slick-popup' ),
						),
				),

				array(
					'id'		=> 'autoclose',
					'type'		=> 'switch',
					'title'		=> 'Close after Submission',
					'default'	=> 0,
					'on'       => __('Enable', 'slick-popup' ),
					'off'      => __('Disable', 'slick-popup' ),
					'hint'     => array(
						'title'     => 'Autoclose Popup',
						'content'   => 'This option will autoclose Popup after X seconds',
					), 
				),

				array(
					'id'		=> 'autoclose_time',
					'type'		=> 'text',
					'title'		=> 'Close after seconds',
					'desc'		=> 'Auto close after X seconds',
					'required'   => array( 'autoclose', '=', 1 ),
					'default'	=> 5, 
				),

				array(
					'id'		=> 'redirect',
					'type'		=> 'switch',
					'title'		=> 'Redirect after submission',
					'default'	=> 0,
					'on'       => __('Enable', 'slick-popup' ),
					'off'      => __('Disable', 'slick-popup' ),
					'hint'     => array(
						'title'     => 'Redirect Popup',
						'content'   => 'This will redirect the Popup to a url after submission',
					), 
				),

				array(
					'id'		=> 'redirect_url',
					'type'		=> 'text',
					'title'		=> 'Redirect URL',
					'desc'		=> __('With https:// or http://', 'slick-popup'),
					'subtitle'	=> 'Enter redirect URL',
					'required'	=> array( 'redirect', '=', 1 ),
					'default'	=> '', 
				),	
			)
		) );
	}