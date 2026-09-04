<?php

    // Load the TGM init if it exists
    if ( !class_exists( 'TGM_Plugin_Activation' ) ) {
        require_once dirname( __FILE__ ) . '/../thetgm/class-tgm-plugin-activation.php';
		require_once( dirname( __FILE__ ) . '/tgm-init.php' );
    }

    // Load the embedded Redux Framework
    if ( file_exists( dirname( __FILE__ ).'/redux-framework/redux-framework.php' ) ) {
        require_once dirname(__FILE__).'/redux-framework/redux-framework.php';
    }

    // Load the theme/plugin options
    if ( file_exists( dirname( __FILE__ ) . '/options-init.php' ) ) {
        require_once dirname( __FILE__ ) . '/options-init.php';
    }

    // Load Redux extensions
    //if ( file_exists( dirname( __FILE__ ) . '/redux-extensions/extensions-init.php' ) ) {
        //require_once dirname( __FILE__ ) . '/redux-extensions/extensions-init.php';
    //}
	
?>