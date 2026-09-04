<?php

/**
 * Add Plugin's Admin Menu
 * Since Version 2.0  
 */	
add_action('admin_menu', 'splite_addmenu_page_in_admin', 99); 
function splite_addmenu_page_in_admin() {
	//add_options_page(__('All Settings'), __('All Settings'), 'administrator', 'options.php');
	global $_wp_last_object_menu;
	$_wp_last_object_menu++;

	global $splite_hook; 	
	$splite_hook = array();
	$icon = SPLITE_PLUGIN_URL . '/admin/img/menu_icon.png';
	
	$splite_hook[] = add_submenu_page( 'slick-options', 'Import Demos', 'Import Demos', 'manage_options', 'splite-import-demos', 'splite_import_demos' );
	$splite_hook[] = add_submenu_page( 'slick-options', 'Help and Support', 'Help and Support', 'manage_options', 'splite-help-and-support', 'splite_help_and_support' );
	//$hook = "load-".$splite_hook[0];
	
	//add_action($hook, 'splite_load_admin');	
}

add_action( 'admin_enqueue_scripts', 'splite_admin_enqueue_scripts' );
function splite_admin_enqueue_scripts( $hook_suffix ) {
	
    //Stylesheet for admin pages
	wp_enqueue_style( 'splite-admin-css', SPLITE_PLUGIN_URL . '/libs/css/admin-styles.css' );

	if ( false === strpos( $hook_suffix, 'slick' ) ) {
		return;
	}

	$bootstrap_4_pages = array(
		'splite-import-demos',
		'splite-help-and-support',
	);

	if (isset($_GET['page']) AND in_array($_GET['page'], $bootstrap_4_pages)) {
		wp_enqueue_style( 'bootstrap-min-css', splite_plugin_url( '/libs/css/bootstrap.min.css' ) );
		wp_enqueue_script( 'bootstrap-min-js', splite_plugin_url( '/libs/js/bootstrap.min.js' ) );
	}
}

/**
 * Import Demos Features
 * Since Version 2.0 - ToDo
 * @param none
 
 * @return none
 * Creates the post list table 
 */
function splite_import_demos() { ?>

	<div class="wrap">
		<div class="splite-layout__header">
            <div class="splite-layout__header-wrapper">
                <h6><?php echo esc_html__("Slick Popup - Import CF7 Demo Forms", 'slick-popup'); ?></h6>
            </div>
        </div>
        <div class="splite-layout__body">
			<div class="card col-md-12 mt-0">
				<div class="card-body">
					<span class="fs-115 text-info"><?php echo esc_html__("Choose a form and click import button, this will create a ", 'slick-popup'); ?><strong><em><a class="td-none" href="<?php echo admin_url('/admin.php?page=wpcf7/'); ?>">Contact Form 7</a></em></strong><?php echo esc_html__(" form with the desired layout. Once imported, you may want to change the To Email and Mail Body for the form.", 'slick-popup'); ?><br><?php echo esc_html__("For any kind of suppport please email us at: ", 'slick-popup'); ?><strong><em><a href="mailto:poke@slickpopup.com" class="td-none">poke@slickpopup.com</a></em></strong></span>
				</div>
			</div>
			<div class="notice-info settings-error notice is-dismissible mb-2 mt-3">
				<p class="font-weight-bold text-danger"><?php echo esc_html__("Note: This will just import the cf7 forms, you will have to create and edit the popups.", 'slick-popup'); ?></p>
			</div>
			<div class="card col-md-12">
				<span class="card-subtitle text-secondary font-weight-normal m-2 fs-2"><?php echo esc_html__("One-click Import for Contact Form 7", 'slick-popup'); ?></span>
				<div class="import-holder">
					<?php $demos = array(
						'basic-enquiry' => 'Basic Enquiry Form', 
						'subscribe' => 'Subscribe Form',
						'unsubscribe' => 	'Unsubscribe Form',				
						'get-a-quote' => 'Get a Quote Form',
						'survey' => 'Survey Form',
						'booking' => 'Booking Form',
					);
                    $output = '<div id="welcome-panel" class="welcome-panel" style="background:white">';
						foreach($demos as $label => $demo) {			
							$output .='<div class="import-box">';
								$output .='<img src="'.splite_plugin_url('/libs/js/img/'.$label.'.jpg').'" title="'.$demo.'">'; 
								// please do not change this class as this will effect the working of the plugin.
								$output .='<div class="import-box-result display-none"></div>';
								$output .='<div class="import-box-title">';
									$output .='<span class="splite-label">'.$demo.'</span>';
									$output .='<span class="splite-import-handle" style="margin-left:10px">';
										$output .='<span class="splite-loader v-hidden"><i class="fa fa-refresh fa-spin loader-fa-styles"></i></span>';						
										$output .='<span class="splite-btn button-link splite-btn-importer splite-btn-importer" data-nonce="'.wp_create_nonce("import_demo_" . $label).'" data-title="'.$label.'"><strong>'.esc_html__('Import','slick-popup').'</strong></span>';
									$output .='</span>';
								$output .='</div>';
							$output .='</div>';
						} 
					$output .='</div>';
					echo $output; 
					?>
				</div>
			</div>
			<div class="notice-info settings-error notice is-dismissible">
				<p class="font-weight-bold"><?php echo esc_html( __( "For any kind of suppport please email us at:", 'slick-popup' ) ); ?> 
					<em><a href="mailto:poke@slickpopup.com" class="td-none">poke@slickpopup.com</a></em>
				</p>
			</div>
		</div>
	</div>

<?php }

/**
 * Help and Support Page
 * Since Version 1.6.5 - ToDo
 * @param none
 
 * @return none
 * Creates the post list table 
 */
function splite_help_and_support() { ?>
	
	<?php 
		global $splite_opts; 
		$current_user =  wp_get_current_user();
		$username = isset($current_user->user_display_name) ? $current_user->user_display_name : ((isset($current_user->user_firstname) and !empty($current_user->user_firstname)) ? $current_user->user_firstname : $current_user->user_login);
		$useremail = $current_user->user_email; 
	?>

	<div class="wrap">
		<div class="splite-layout__header">
            <div class="splite-layout__header-wrapper">
                <h6><?php echo esc_html__("Slick Popup - Help and Support", 'slick-popup'); ?></h6>
            </div>
        </div>
        <div class="splite-layout__body">
			<div class="card col-md-12 mt-0">
				<div class="card-body m-2">
					<ul class="nav nav-tabs nav-justified lead font-weight-bold" role="tablist">
						<li class="nav-item">
							<a class="nav-link active menu-links text-dark" data-toggle="tab" href="#menu1"><?php echo esc_html__("Basics", 'slick-popup'); ?></a>
						</li>
						<li class="nav-item">
							<a class="nav-link menu-links text-dark" data-toggle="tab" href="#menu2"><?php echo esc_html__("Documentation", 'slick-popup'); ?></a>
						</li>
						<li class="nav-item">
							<a class="nav-link menu-links text-dark" data-toggle="tab" href="#menu3"><?php echo esc_html__("Premium Features", 'slick-popup'); ?></a>
						</li>
						<li class="nav-item">
							<a class="nav-link menu-links text-dark" data-toggle="tab" href="#menu4"><?php echo esc_html__("Support", 'slick-popup'); ?></a>
						</li>
					</ul>
					<div class="tab-content">
						<div id="menu1" class="container tab-pane active"><br>
							<div class="row">
								<div class="col-md-6">
									<span class="fs-175 d-block text-info font-weight-bold"><?php echo esc_html__("How to create a Popup?", 'slick-popup'); ?></span>
									<div class="text-body font-weight-normal">
										<?php echo esc_html__("Creating a Popup Form is very easy with Slick Popup.", 'slick-popup'); ?>
										<ol type="1">
											<li><?php echo esc_html__("Create a Form via Contact Form 7", 'slick-popup'); ?></li>
											<li><?php echo esc_html__("Go to Global Form Options", 'slick-popup'); ?></li>
											<li><?php echo esc_html__("Select your Contact Form", 'slick-popup'); ?></li>
											<li><?php echo esc_html__("Choose on which pages you want to show your Popup", 'slick-popup'); ?></li>
											<li><?php echo esc_html__("Add the Popup Styles", 'slick-popup'); ?></li>
											<li><?php echo esc_html__("Click on Save Changes and Checkout your Smart, Slick and Beautiful Popup Form", 'slick-popup'); ?></li>
										</ol>
									</div>
								</div>
								<div class="col-md-6">
									<span class="fs-175 d-block text-info font-weight-bold"><?php echo esc_html__("How to Import the Demo Forms?", 'slick-popup'); ?></span>
									<div class="text-body font-weight-normal">
										<?php echo esc_html__("To Import the Demo forms follow the following steps:", 'slick-popup'); ?>
										<ol type="4">
											<li><?php echo esc_html__("Go to Import Demos", 'slick-popup'); ?></li>
											<li><?php echo esc_html__( "Choose the desirable Popup Form", 'slick-popup'); ?></li>
											<li><?php echo esc_html__("Click on Import", 'slick-popup'); ?></li>
											<li><span class="text-danger font-weight-bold"><?php echo esc_html__("Note: It is recommended that you go through the default setting of the imported Forms:", 'slick-popup'); ?></span>
												<ol type="I" class="text-body font-weight-normal">
													<li><?php echo esc_html__("Click on ", 'slick-popup'); ?><span class="font-weight-bold"><?php echo esc_html__("Edit Form", 'slick-popup'); ?></span><?php echo esc_html__(" to edit the Contact Form 7 and make changes in the mail tab", 'slick-popup'); ?></li>
													<li><?php echo esc_html__("Click on ", 'slick-popup'); ?><span class="font-weight-bold"><?php echo esc_html__("Set Popup", 'slick-popup'); ?></span> <?php echo esc_html__("to add that form to your popup.", 'slick-popup'); ?></li>
												</ol>
											</li>
										</ol>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-6">
									<span class="fs-175 d-block text-info font-weight-bold"><?php echo esc_html__("How to Activate and Deactivate Slick Popup dynamically?", 'slick-popup'); ?></span>
									<?php echo esc_html__("There are many ways to Activate and Deactivate Slick Popup dynamically:", 'slick-popup'); ?>
									<ol type="circle">
										<li><span class="font-weight-bold"><?php echo esc_html__("Via Class:", 'slick-popup'); ?></span><?php echo esc_html__(" You can activate Slick Popup by using the class ", 'slick-popup'); ?><span class="font-weight-bold">"splite-showpoup"</span>.<br><?php echo esc_html__('For eg. <button class="splite-showpoup">Click Me</button>', 'slick-popup'); ?></li>
										<li><span class="font-weight-bold"><?php echo esc_html__("Via Href or Url:", 'slick-popup'); ?></span><?php echo esc_html__(" You can activate Slick Popup by giving the url or href element of the a tag ", 'slick-popup'); ?><span class="font-weight-bold">'javascript:splite_loader('id of the popup')'</span>.<br><?php echo esc_html__( 'For eg. <button url="javascript:splite_loader();">Click Me</button>', 'slick-popup' ); ?></li>
										<li><?php echo esc_html__("If you want ", 'slick-popup'); ?><span class="font-weight-bold"><?php echo esc_html__("unload", 'slick-popup'); ?></span><?php echo esc_html__(" the popup use ", 'slick-popup'); ?><span class="font-weight-bold">'javascript:splite_unloader('id of the popup')'</span>.<br><?php echo esc_html__( 'For eg. <button url="javascript:splite_unloader();">Click Me</button>', 'slick-popup' ); ?></li>
									</ol>
								</div>
								<div class="col-md-6">
									<span class="fs-175 d-block mb-40 text-info font-weight-bold"><?php echo esc_html( __( "Are there any filters available?", 'slick-popup' ) ); ?></span>
									<?php echo esc_html__( "There are alot of filters available for Slick Popup Pro some of them are listed below:", 'slick-popup'); ?>
									<ol type="circle">
										<li><span class="font-weight-bold">splite_dollar_cf7_id:</span><?php echo esc_html__( " You can choose which CF7 form to show on the popup", 'slick-popup'); ?></li>
										<li><span class="font-weight-bold">splite_dollar_side_button_text:</span><?php echo esc_html__( " You can add custom side button text", 'slick-popup' ); ?></li>
										<li><span class="font-weight-bold">splite_dollar_choose_layout:</span><?php echo esc_html__( " You can add custom layout to the popup", 'slick-popup' ); ?></li>
										<li><span class="font-weight-bold">splite_dollar_popup_load_effect:</span><?php echo esc_html__( " You can add custom load effect to the popup", 'slick-popup' ); ?></li>
										<li><span class="font-weight-bold">splite_dollar_popup_unload_effect:</span><?php echo esc_html__( " You can add custom unload effect to the popup", 'slick-popup' ); ?></li>
									</ol>
								</div>
							</div>
						</div>
						<div id="menu2" class="container tab-pane fade"><br>
							<div class="row">
								<div class="col-md-6">
									<span class="fs-175 d-block text-info font-weight-bold"><?php echo esc_html__( "Color Schemes", 'slick-popup' ); ?></span>
									<div class="text-body font-weight-normal">
										<?php echo esc_html__( "There are 5 built-in Color Schemes and you can always customise it according to your own requirements", 'slick-popup' ); ?>
										<ol type="1">
											<li><span class="font-weight-bold"><?php echo esc_html__( "Master Red:-", 'slick-popup' ); ?></span> <div class="master-red"></div></li>
											<li><span class="font-weight-bold"><?php echo esc_html__( "Creamy Orange:-", 'slick-popup' ); ?></span> <div class="creamy-orange"></div></li>
											<li><span class="font-weight-bold"><?php echo esc_html__( "Light Blue:-", 'slick-popup' ); ?></span> <div class="light-blue" ></div></li>
											<li><span class="font-weight-bold"><?php echo esc_html__( "Cool Green:-", 'slick-popup' ); ?></span> <div class="cool-green"></div></li>
											<li><span class="font-weight-bold"><?php echo esc_html__( "Classic Grey:-", 'slick-popup' ); ?></span> <div class="classic-grey"></div></li>
											<li><span class="font-weight-bold"><?php echo esc_html__( "Custom Color:-", 'slick-popup' ); ?></span> <div class="custom-color"></div></li>
										</ol>
									</div>
								</div>
								<div class="col-md-6">
									<span class="fs-175 d-block text-info font-weight-bold"><?php echo esc_html__( "Animations", 'slick-popup' ); ?></span>
									<div class="text-body font-weight-normal">
										<?php echo esc_html__( "There are more than 14 Loading Animations", 'slick-popup' ); ?>
										<ol>
											<div class="row">
												<div class="col-md-6">
													<li><?php echo esc_html__( "Fade", 'slick-popup' ); ?></li>
													<li><?php echo esc_html__( "Zoom", 'slick-popup' ); ?></li>
													<li><?php echo esc_html__( "Rotate", 'slick-popup' ); ?></li>
													<li><?php echo esc_html__( "Flip in X", 'slick-popup' ); ?>&nbsp;<span class="badge badge-success">Premium</span></li>
												</div>
												<div class="col-md-6">
													<li><?php echo esc_html__( "Pulse", 'slick-popup' ); ?>&nbsp;<span class="badge badge-success">Premium</span></li>
													<li><?php echo esc_html__( "Rubber Band", 'slick-popup' ); ?>&nbsp;<span class="badge badge-success">Premium</span></li>
													<li><?php echo esc_html__( "Shake", 'slick-popup' ); ?>&nbsp;<span class="badge badge-success">Premium</span></li>
													<li><?php echo esc_html__( "Swing", 'slick-popup' ); ?>&nbsp;<span class="badge badge-success">Premium</span></li>
												</div>
											</div>
										</ol>
									</div>
								</div>
								<div class="col-md-6">
									<span class="d-block fs-175 text-info font-weight-bold"><?php echo esc_html__( "Activation Modes", 'slick-popup' ); ?></span>
									<div class="text-body font-weight-normal">
										<?php echo esc_html__( "There are 5 Activation Modes:", 'slick-popup' ); ?>
										<ol type="1">
											<li><span class="font-weight-bold"><?php echo esc_html__( "On-Click: ", 'slick-popup' ); ?></span> <?php echo esc_html__( "Default is set to On-Click, The Popup will activate on the click of a Button or a HTML Element", 'slick-popup' ); ?></li>
											<li><span class="font-weight-bold"><?php echo esc_html__( "On-Exit Popup: ", 'slick-popup' ); ?></span> <?php echo esc_html__( "This will be activated whenever a user tries to Exit the page.", 'slick-popup' ); ?></li>
											<li><span class="font-weight-bold"><?php echo esc_html__( "Auto Popup: ", 'slick-popup' ); ?></span> <?php echo esc_html__( "This is the entry popup this is activated when the page is loaded.", 'slick-popup' ); ?>&nbsp;<span class="badge badge-success">Premium</span></li>
											<li><span class="font-weight-bold"><?php echo esc_html__( "Forced Popup: ", 'slick-popup' ); ?></span> <?php echo esc_html__( "This will not close until the user fills the complete form successfully", 'slick-popup' ); ?>&nbsp;<span class="badge badge-success">Premium</span></li>
											<li><span class="font-weight-bold"><?php echo esc_html__( "On-Scroll Popup: ", 'slick-popup' ); ?></span> <?php echo esc_html__( "This popup is activated when you scroll a certain amount of the page.", 'slick-popup' ); ?>&nbsp;<span class="badge badge-success">Premium</span></li>
										</ol>
									</div>
								</div>
								<div class="col-md-6">
									<span class="fs-175 d-block text-info font-weight-bold"><?php echo esc_html__( "Typography", 'slick-popup' ); ?></span>
									<div class="text-body font-weight-normal">
										<?php echo esc_html__( "There is a lot you can customise with typography in Slick Popup Pro", 'slick-popup' ); ?>
										<ol type="1">
											<li><span class="font-weight-bold"><?php echo esc_html__( "CTA text:", 'slick-popup' ); ?></span><?php echo esc_html__( " You have full control over the typography of the CTA text which is found over the top of the contact form", 'slick-popup' ); ?></li>
											<li><span class="font-weight-bold"><?php echo esc_html__( "Label text:", 'slick-popup' ); ?></span><?php echo esc_html__( " You can change the typography of the label text as well.", 'slick-popup' ); ?></li>
											<li><span class="font-weight-bold"><?php echo esc_html__( "Side Button text:", 'slick-popup' ); ?></span><?php echo esc_html__( " You can full customize the typography of the side button text.", 'slick-popup' ); ?></li>
										</ol>
									</div>
								</div>
								<div class="col-md-6">
									<span class="fs-175 d-block text-info font-weight-bold"><?php echo esc_html__( "Side Buttons", 'slick-popup' ); ?></span>
									<div class="text-body font-weight-normal">
										<?php echo esc_html__( "There are 8 pre-built Side Buttons:", 'slick-popup' ); ?>
										<ol>
											<div class="row">
												<div class="col-md-6">
													<li><?php echo esc_html__( "Right", 'slick-popup' ); ?></li>
													<li><?php echo esc_html__( "Left", 'slick-popup' ); ?></li>
													<li><?php echo esc_html__( "Top Left", 'slick-popup' ); ?>&nbsp;<span class="badge badge-success">Premium</span></li>
													<li><?php echo esc_html__( "Top Center", 'slick-popup' ); ?>&nbsp;<span class="badge badge-success">Premium</span></li>
												</div>
												<div class="col-md-6">
													<li><?php echo esc_html__( "Top Right", 'slick-popup' ); ?>&nbsp;<span class="badge badge-success">Premium</span></li>
													<li><?php echo esc_html__( "Bottom Left", 'slick-popup' ); ?>&nbsp;<span class="badge badge-success">Premium</span></li>
													<li><?php echo esc_html__( "Bottom Center", 'slick-popup' ); ?>&nbsp;<span class="badge badge-success">Premium</span></li>
													<li><?php echo esc_html__( "Bottom Right", 'slick-popup' ); ?>&nbsp;<span class="badge badge-success">Premium</span></li>
												</div>
											</div>
										</ol>
									</div>
								</div>
								<div class="col-md-6">
									<span class="fs-175 d-block text-info font-weight-bold"><?php echo esc_html__( "Layouts", 'slick-popup' ); ?></span>
									<div class="text-body font-weight-normal">
										<?php echo esc_html__( "There are more than 8 pre-built Side Buttons:", 'slick-popup' ); ?>
										<ol>
											<div class="row">
												<div class="col-md-6">
													<li><?php echo esc_html__( "Centered", 'slick-popup' ); ?></li>
													<li><?php echo esc_html__( "Full Height", 'slick-popup' ); ?></li>
													<li><?php echo esc_html__( "Top Left", 'slick-popup' ); ?>&nbsp;<span class="badge badge-success">Premium</span></li>
													<li><?php echo esc_html__( "Top Center", 'slick-popup' ); ?>&nbsp;<span class="badge badge-success">Premium</span></li>
												</div>
												<div class="col-md-6">
													<li><?php echo esc_html__( "Top Right", 'slick-popup' ); ?>&nbsp;<span class="badge badge-success">Premium</span></li>
													<li><?php echo esc_html__( "Bottom Center", 'slick-popup' ); ?>&nbsp;<span class="badge badge-success">Premium</span></li>
													<li><?php echo esc_html__( "Bottom Right", 'slick-popup' ); ?>&nbsp;<span class="badge badge-success">Premium</span></li>
													<li><?php echo esc_html__( "Full Page", 'slick-popup' ); ?>&nbsp;<span class="badge badge-success">Premium</span></li>
												</div>
											</div>
										</ol>
									</div>
								</div>
							</div>			
						</div>
						<div id="menu3" class="container tab-pane fade"><br>
							<div class="row">
								<div class="col-md-12">
									<span class="fs-2 d-block text-info text-center font-weight-bold mb-3"><?php echo esc_html__( "Slick Popup Pro", 'slick-popup' ); ?></span>
									<div class="text-body font-weight-normal">
										<p class="font-weight-bold lead"><?php echo esc_html__( "Here are a few features that will be available in Slick Popup Pro.", 'slick-popup' ); ?></p>
										<ol class="fs-115">
											<li class="text-secondary font-weight-bold"><?php echo esc_html( __( "Premium and Priority Support", 'slick-popup' ) ); ?></li>
											<li class="text-secondary font-weight-bold"><?php echo esc_html( __( "20+ animation effects to choose from", 'slick-popup' ) ); ?></li>
											<li class="text-secondary font-weight-bold"><?php echo esc_html( __( "Different popup for each woocommerce product", 'slick-popup' ) ); ?></li>
											<li class="text-secondary font-weight-bold"><?php echo esc_html( __( "Easy to create and insert shortcode", 'slick-popup' ) ); ?></li>
											<li class="text-secondary font-weight-bold"><?php echo esc_html( __( "Can be applied to link/image or HTML tag", 'slick-popup' ) ); ?></li>
											<li class="text-secondary font-weight-bold"><?php echo esc_html( __( "Multiple popups on single page", 'slick-popup' ) ); ?></li>
											<li class="text-secondary font-weight-bold"><?php echo esc_html( __( "Different Popups on different pages and posts", 'slick-popup' ) ); ?></li>
											<li class="text-secondary font-weight-bold"><?php echo esc_html( __( "Additional 3 activation modes", 'slick-popup' ) ); ?></li>
											<li class="text-secondary font-weight-bold"><?php echo esc_html( __( "Insights for all your popups", 'slick-popup' ) ); ?></li>
											<li class="text-secondary font-weight-bold"><?php echo esc_html( __( "reCAPTCHA Supported", 'slick-popup' ) ); ?></li>
											<li class="text-secondary font-weight-bold"><?php echo esc_html( __( "Scrollbar in Popup", 'slick-popup' ) ); ?></li>
											<li class="text-secondary font-weight-bold"><?php echo esc_html( __( "and many more......", 'slick-popup' ) ); ?></li>
										</ol>
									</div>
								</div>
							</div>	
						</div>
						<div id="menu4" class="container tab-pane fade"><br>
							<div class="row">
								<div class="offset-md-3 col-md-6">
									<span class="fs-175 d-block text-info text-center font-weight-bold pb-2"><?php echo esc_html__( "Contact Slick Popup Support", 'slick-popup' ); ?></span>
									<form method="post" class="splite-contact-support" action="">
										<div class="input-group mb-3">
											<div class="input-group-prepend">
											  <span class="input-group-text"><?php echo esc_html__( "Name", 'slick-popup' ); ?></span>
											</div>
											<input type="text" class="form-control" name="name" placeholder="<?php echo esc_html__( "Enter your Name", 'slick-popup' ); ?>" value="<?php echo $username; ?>" >
										</div>
										<div class="input-group mb-3">
											<div class="input-group-prepend">
											  <span class="input-group-text"><?php echo esc_html__( "Email", 'slick-popup' ); ?></span>
											</div>
											<input type="text" class="form-control" name="email" placeholder="<?php echo esc_html__( "Enter your Email", 'slick-popup' ); ?>" value="<?php echo $useremail; ?>" >
										</div>
										<div class="input-group mb-3">
											<div class="input-group-prepend">
											  <span class="input-group-text"><?php echo esc_html__( "Issue Subject", 'slick-popup' ); ?></span>
											</div>
											<input type="text" class="form-control" name="subject" placeholder="<?php echo esc_html__( "Enter your Issue Subject", 'slick-popup' ); ?>">
										</div>
										<div class="input-group mb-3">
											<div class="input-group-prepend">
											  <span class="input-group-text"><?php echo esc_html__( "Page URL", 'slick-popup' ); ?></span>
											</div>
												<?php 
													$args = array(
														'show_option_none' => 'All Pages',
														'name' => 'page_id',
														'class' => 'form-control',
													);
													wp_dropdown_pages($args); 
												?>
										</div>
										<div class="form-group mb-3">
										  <label for="message" class="font-weight-bold"><?php echo esc_html__( "Issue Details:", 'slick-popup' ); ?></label>
										  <textarea class="form-control" name="message" rows="6" placeholder="<?php echo esc_html__( "Please describe your issue in detail", 'slick-popup' ); ?>"></textarea>
										</div>
										<div class="input-group mb-1 mt-2">
											<input type="submit" name="Submit" class="btn btn-outline-info splite-submit-btn">	
											<input type="hidden" name="wp_nonce" value="<?php echo wp_create_nonce("splite_contact_support_nonce"); ?>">	
											<span class="splite-loader ml-1 splite-loader-styles"><i class="fa fa-refresh fa-spin splite-loader-fa-styles"></i></span>
										</div>
										<div class="input-group">
											<div class="result-area"></div>
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<script>
		jQuery(document).ready(function() {
			jQuery('.nav-link').click(function(e) {
				$btnClicked = jQuery(this); 
				toggle = $btnClicked.attr('data-toggle'); 
				if(toggle="tab") {
					href= $btnClicked.attr('href'); 
					tabs = jQuery('.tab-content .container'); 
					links = jQuery('.nav-tabs .nav-link'); 
					tabs.each(function(index) {
						jQuery(this).removeClass('active show'); 
					});
					links.each(function(index) {
						jQuery(this).removeClass('active show'); 
					});
					jQuery(href).addClass('active show'); 
					$btnClicked.addClass('active show'); 
				}
			});
		});
	</script>
<?php }

/**
 * Takes the pain to return the correct action
 */
function splite_current_action() {
	//return 'copy'; 
	if ( isset( $_REQUEST['action'] ) && -1 != $_REQUEST['action'] ) {
		return sanitize_text_field($_REQUEST['action']);
	}

	if ( isset( $_REQUEST['action2'] ) && -1 != $_REQUEST['action2'] ) {
		return sanitize_text_field($_REQUEST['action2']);
	}

	return false;
}

add_action( 'wp_ajax_action_splite_contact_support', 'action_splite_contact_support' );
function action_splite_contact_support() {
	$ajaxy = array(); 
	$errors = array(); 
	
	if(!current_user_can('manage_options')) {
		$ajaxy['reason'] = __('You do not have sufficient permissions to perform this action.', 'slick-popup'); 
	}
	
	if( !isset($_POST) OR !isset($_POST['fields']) OR empty($_POST['fields']) ) {
		$ajaxy['reason'] = 'Nothing sent to server, please retry.'; 
	}
	
	// Sanitize fields value postd as string
	$fields = sanitize_text_field($_POST['fields']); 	
	parse_str($fields, $posted);
	
	// Sanitize individual array value again
	$posted = splite_sanitize_arr_str($posted);	

	extract($posted); 
	
	if(!wp_verify_nonce($wp_nonce, 'splite_contact_support_nonce')) {
		$ajaxy['reason'] = 'Security check failed, please refresh and try again.'; 
	}
	
	if(!current_user_can('manage_options')) {
		$ajaxy['reason'] = 'You do not have sufficient permissions.'; 
	}
	
	// If error reason is send, the return error
	if(isset($ajaxy['reason'])) {
		wp_send_json_error($ajaxy); 
		wp_die(); 
	}

	// If Nothing is posted through AJAX
	if( !isset($name) OR empty($name) ) {
		$errors[] = 'Please enter your name'; 
	}
	if( !isset($email) OR empty($email) ) {
		$errors[] = 'Please enter your email'; 
	}
	if( !isset($subject) OR empty($subject) ) {
		$errors[] = 'Please enter a subject'; 
	}
	if( !isset($message) OR empty($message) ) {
		$errors[] = 'Please describe the issue your facing'; 
	}
	
	$pages = 'All Pages'; 
	if(!empty($page_id) AND is_numeric($page_id)) {
		$pages = '<a href="'.get_the_permalink($page_id).'" target="_blank">'.get_the_title($page_id).'</a>'; 
	}
	
	if(sizeof($errors)) {
		$ajaxy['reason'] = '<ol class="p-0">';
			foreach($errors as $error) { $ajaxy['reason'] .= '<li class="m-0">'.$error.'</li>'; }
		$ajaxy['reason'] .= '</ol>';
		
		wp_send_json_error($ajaxy); 
		wp_die(); 
	}
	
	// Always set content-type when sending HTML email
	$headers = "MIME-Version: 1.0" . "\r\n";
	$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
	$headers .= 'From: <'.$email.'>' . "\r\n";
	//$headers .= 'Cc: '.$email . "\r\n";
	
	$mail_subject = 'Support Required for Slick Popup Lite: ' . $name . ' - ' . $subject . ' (' . site_url(). ')';
	$mail_body = ''; 
	$mail_body .= '<b>Dear Team,<b><br><br>'; 
	$mail_body .= '<table border cellpadding="10">';
		$mail_body .= '<tr>';
			$mail_body .= '<th colspan="2">Slick Popup Lite</th>';
		$mail_body .= '</tr>';
		$mail_body .= '<tr>';
			$mail_body .= '<th>A new support request has been received from: </th><td>'.site_url().'</td>';
		$mail_body .= '</tr>';
		$mail_body .= '<tr>';	
			$mail_body .= '<th>Plugin Name: </th><td>Slick Popup Lite</td>';
		$mail_body .= '</tr>';
		$mail_body .= '<tr>';	
			$mail_body .= '<th>Plugin Version: </th><td>'.SPLITE_VERSION.'</td>';
		$mail_body .= '</tr>';
		$mail_body .= '<tr>';	
			$mail_body .= '<th>Email: </th><td>'.$email.'</td>';
		$mail_body .= '</tr>';
		$mail_body .= '<tr>';	
			$mail_body .= '<th>Message: </th><td>'.$message.'</td>';
		$mail_body .= '</tr>';
		$mail_body .= '<tr>';	
			$mail_body .= '<th>Page: </th><td>'.$pages.'</td>';
		$mail_body .= '</tr>';
	$mail_body .= '</table>';
	
	$mail = wp_mail('poke@slickpopup.com', $mail_subject, $mail_body, $headers ); 
	
	if($mail) {
		$ajaxy['reason'] = 'Your request has been sent to support team, if you think that this issue will require admin access for our then please grant access to the <strong>Support Team</strong> by clicking the button on the right. Please wait for a response by our team.'; 
		wp_send_json_success($ajaxy); 
		wp_die(); 
	}
	
	$ajaxy['reason'] = 'Could not contact support, please retry or send a direct email to poke@slickpopup.com';
	wp_send_json_error($ajaxy); 
	wp_die(); 
}
?>