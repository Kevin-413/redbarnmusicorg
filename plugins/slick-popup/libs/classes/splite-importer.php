<?php 

/*
* splite_action_importDemo
* New import feature to set up individual Popups
*/
add_action( 'wp_ajax_splite_action_importDemo', 'splite_action_importDemo' );
function splite_action_importDemo() {

    $ajaxy = array();
	
	if(!current_user_can('manage_options')) {
		$ajaxy['reason'] = __('You do not have sufficient permissions to perform this action.', 'slick-popup'); 
	}
	
	// If Nothing is posted through AJAX
	if( !isset($_POST) OR !isset($_POST['title']) ) {
		$ajaxy['reason'] = 'Try again. Nothing Sent to server.'; 		
	}
	
	if(!isset($_POST['security'])) {
		$ajaxy['reason'] = 'Security check failed, please refresh and try again.'; 
	}
	
	if(!wp_verify_nonce($_POST['security'], 'import_demo_'.$_POST['title']) || !current_user_can('manage_options')) {
		$ajaxy['reason'] = 'Security check failed, please refresh and try again.'; 
	}
	
	// If error reason is send, the return error
	if(isset($ajaxy['reason'])) {
		wp_send_json_error($ajaxy); 
		wp_die(); 
	}
	
	$title = sanitize_text_field($_POST['title']);
	$formId = splite_import_cf7_demo(array('title'=>$title));
	
	if($formId) {
		$edit_link = '<a target="_blank" href="'.admin_url('/admin.php?page=wpcf7&post='.$formId.'&action=edit').'"><strong>Edit Form</strong></a>';
		$global_options = '<a target="_blank" href="'.admin_url('/admin.php?page=slick-options').'"><strong>Set Popup</strong></a>';
		$ajaxy['reason'] = 'Imported.<br>'.$edit_link. ' - '.$global_options; 		
		wp_send_json_success($ajaxy); 
		wp_die(); 
	}
	
	$ajaxy['reason'] = __('Could not be imported.', 'slick-popup');
	wp_send_json_success($ajaxy); 
	wp_die(); 
}

function splite_import_cf7_demo($args=array()) {	
	
	if(!sizeof($args) or !isset($args['title']) or empty($args['title']) ) return false; 
	
	extract($args);

    $query = new WP_Query(
        array(
            'post_type'              => 'wpcf7_contact_form',
            'title'                  => $title,
            'post_status'            => 'all',
            'posts_per_page'         => 1,
            'update_post_term_cache' => false,
            'update_post_meta_cache' => false,
            'orderby'                => 'post_date ID',
            'order'                  => 'ASC',
        )
    );

    if ( ! empty( $query->post ) ) {
        $form = $query->post;
		$ajaxy['form_id'] = $form->ID;

		$edit_link = '<a target="_blank" href="'.admin_url('/admin.php?page=wpcf7&post='.$form->ID.'&action=edit').'"><strong>' . __('Edit Form', 'slick-popup') . '</strong></a>';
		$global_options = '<a target="_blank" href="'.admin_url('/admin.php?page=slick-options').'"><strong>' . __('Set Popup', 'slick-popup') . '</strong></a>';
        $ajaxy['reason'] =  __('Already exists.', 'slick-popup') . '<br>' .$edit_link. ' - '.$global_options;

		wp_send_json_error($ajaxy);
		wp_die();
	}
	
	$contact_form = WPCF7_ContactForm::get_template( array(
		'title' => ucwords(str_replace('-', ' ',$title))
	));	
	
	$form = $contact_form->prop( 'form' );
	$mail = $contact_form->prop( 'mail' );
	$messages = $contact_form->prop( 'messages' );
	
	$messages['invalid_required'] = 'X'; 
	$messages['invalid_email'] = 'X'; 	
	$messages['invalid_number'] = 'X'; 	
	$mail['use_html'] = true;

	switch($title) {
		case 'basic-enquiry': 
			$form = '
<div class="spp-row">
	<div class="spp-left-col"><label>Full Name [text* your-name placeholder "Full name"]</label></div>
	<div class="spp-right-col"><label>Phone [tel* your-phone placeholder "Phone"]</label></div>
	<div class="spp-clear"></div>
	<div class="spp-full-col"><label>Email [email* your-email placeholder "Email"]</label></div>
	<div class="spp-full-col"><label>Message [textarea your-message placeholder "Message"]</label></div>
	<div class="spp-full-col">[submit "SUBMIT"]</div>
</div>';
			$mail['subject'] = "There is an enquiry from [your-name], [your-email].";

			$mail['sender'] = "[your-name] <[your-email]>";

			$mail['body'] = "
Hello admin, 

A customer has put up an enquiry. Here are the details and content of the enquiry. 
<strong>Name:</strong> [your-name]
<strong>Email:</strong> [your-email]
<strong>Phone:</strong> [your-phone]

<strong>Message Body:</strong>
[your-message]


The admin is advised to go through the customer's enquiry and revert him soon.";
			break;
		
		case 'subscribe':
			$form = '
<div class="spp-row">
	<div class="spp-left-col"><label>First Name [text* first-name placeholder "First Name"]</label></div>
	<div class="spp-right-col"><label>Last Name [text* last-name placeholder "Last Name"]</label></div>
	<div class="spp-clear"></div>
	<div class="spp-full-col"><label>Email [email* your-email placeholder "Email"]</label></div>
	<div class="spp-full-col">[submit "SUBSCRIBE"]</div>
</div>'; 
		
			$mail['subject'] = "[last-name], [first-name] has subscribed to your newsletter.";

			$mail['sender'] = "[first-name] <[your-email]>";

			$mail['body'] = '
Hello admin

A customer has subscribed to your updates.
<strong>Name:</strong> [last-name], [first-name]
<strong>Email:</strong> [your-email]

The admin is advised to check the users profile.';
			break; 

		case 'unsubscribe':
			$form = '
<div class="spp-row">
	<div class="spp-full-col"><label> [select* unsubscribe-reason "Unsubscribe Reason" "Too many emails" "Content irrelevant" "Didn’t know you were subscribing " "Too much or too little content"] </label></div>
	<div class="spp-full-col"><label> [email* your-email placeholder "Email"] </label></div>
	<div class="spp-full-col">[submit "UNSUBSCRIBE"]</div>
</div>';

			$mail['subject'] = "You have been unsubscribed from [your-email].";

			$mail['sender'] = "<[your-email]>";
		
			$mail['body'] = '
Hello admin

A customer has unsubscribed to your updates.
<strong>Email:</strong> [your-email]
<strong>Reason: </strong> [unsubscribe-reason]

The admin is advised to check the users profile.';

			break;	
		
		case 'booking':
			$form = '
<div class="spp-row">
	<div class="spp-left-col"><label>Name [text* your-name placeholder "Name*"]</label></div>
	<div class="spp-right-col"><label>Email [email* your-email placeholder "Email*"]</label></div>
	<div class="spp-clear"></div>
	<div class="spp-left-col"><label>Phone [tel* your-phone placeholder "Phone*"]</label></div>
	<div class="spp-right-col"><label>Street [text* your-street placeholder "Street*"]</label></div>
	<div class="spp-clear"></div>
	<div class="spp-left-col"><label>City [text* your-city placeholder "City*"]</label></div>
	<div class="spp-right-col"><label>State [text* your-state placeholder "State*"]</label></div>
	<div class="spp-clear"></div>
	<div class="spp-left-col"><label>Country [text* your-country placeholder "Country*"]</label></div>
	<div class="spp-right-col"><label>Postal Code [text* your-postalcode placeholder "Postal Code*"]</label></div>
	<div class="spp-clear"></div>
	<div class="spp-left-col"><label>Date of Arrival [date* your-arrive placeholder "Arrive Date"]</label></div>
	<div class="spp-right-col"><label>Occupants* [select* your-occupents include_blank "1" "2" "3" "4" "5" "6" "7" "8" "9" "10"]</label></div>
	<div class="spp-clear"></div>
	<div class="spp-left-col"><label>No. of Nights [select*  your-nights include_blank "1" "2" "3" "4" "5" "6" "7" "8" "9" "10"]</label></div>
	<div class="spp-right-col"><label>No. of Rooms [select* your-rooms include_blank "1" "2" "3" "4" "5" "6" "7" "8" "9" "10"]</label></div>
	<div class="spp-clear"></div>
	<div class="spp-full-col"><label>Additional Information: [textarea your-additionalInfo placeholder "Additional Info"]</label></div>
	<div class="spp-full-col">[submit "BOOK NOW"]</div>
</div>';

			$mail['subject'] = "There has been a booking from [your-name], [your-email].";

			$mail['sender'] = "[your-name] <[your-email]>";
		
			$mail['body'] = '
Hello admin

A customer has put a booking request.<br>
<strong>Name:</strong> [your-name]
<strong>Email:</strong> [your-email]
<strong>Phone:</strong> [your-phone]
<strong>Street:</strong> [your-street]
<strong>City:</strong> [your-city]
<strong>State:</strong> [your-state]
<strong>Country:</strong> [your-country]
<strong>Postal code:</strong> [your-postalcode]
<strong>Arrival Date:</strong> [your-arrive]
<strong>No. of Occupents:</strong> [your-occupents]
<strong>No. of Nights:</strong> [your-nights]
<strong>No. of Rooms:</strong> [your-rooms]
<strong>Additional Info:</strong> [your-additionalInfo]

The admin is advised to check the following details.';

			break;	

		case 'get-a-quote':
			$form = '
<div class="spp-row">
	<div class="spp-left-col"><label>First Name [text* your-fname placeholder "First Name*"]</label></div>
	<div class="spp-right-col"><label>Last Name [text* your-lname placeholder "Last Name*"]</label></div>
	<div class="spp-clear"></div>
	<div class="spp-left-col"><label>Email [email* your-email placeholder "Email*"]</label></div>
	<div class="spp-right-col"><label>City [text* your-city placeholder "City*"]</label></div>
	<div class="spp-clear"></div>
	<div class="spp-left-col"><label>State [text* your-state placeholder "State*"]</label></div>
	<div class="spp-right-col"><label>Country [text* your-country placeholder "Country*"]</label></div>
	<div class="spp-clear"></div>
	<div class="spp-left-col"><label>Select estimated project due date [date* your-estimate]</label></div>
	<div class="spp-right-col"><label>Indicate urgency of your request* [select* your-request include_blank "Low" "Normal" "High"]</label></div>
	<div class="spp-clear"></div>
	<div class="spp-full-col"><label>Send me a price quotation for the following service: [checkbox checkbox label_first "Installation‎"][checkbox checkbox label_first "Maintenance‎"]</label></div>
	<div class="spp-clear"></div>
	<div class="spp-full-col"><label>Provide us any further information you think may be important: [textarea* your-Info]</label></div>
	<div class="spp-full-col">[submit "Submit"]</div>
</div>';

			$mail['subject'] = "There has been a Quote request from [your-fname], [your-email].";

			$mail['sender'] = "[your-fname] <[your-email]>";
		
			$mail['body'] = '
Hello admin, 

A customer has contacted us. Here are the details and content of the request. 

<strong>First Name:</strong> [your-fname]
<strong>Last Name:</strong> [your-lname]
<strong>Email:</strong> [your-email]
<strong>City:</strong> [your-city]
<strong>State:</strong> [your-state]
<strong>Country:</strong> [your-country]
<strong>Estimate Project Date:</strong> [your-estimate]
<strong>Urgency Request:</strong> [your-request]
<strong>Service:</strong> [checkbox]
<strong>Further Info:</strong> [your-Info]

The admin is advised to go through the customers request and revert him back soon.';

			break;

		case 'survey':
			$form = '
<div class="spp-row">
	<strong style="font-style:italic;display:block"><label>Please help us to serve you better by completing this survey. It should take around 5 minutes to complete.</label></strong>
	<div class="spp-left-col"><label>Full Name [text* your-name placeholder "Full Name"]</label></div>
	<div class="spp-right-col"><label>Email [email* your-email placeholder "Email"]</label></div>
	<div class="spp-clear"></div>
	<ol class="survey" type="I">
		<li> <label>Overall, how satisfied are you with our product / service? [radio survey-satisfied "Very Satisfied" "Satisfied" "Neutral" "Unsatisfied" "Very Unsatisfied"]</label> </li>
		<li> <label>Would you recommend our product / service to others? [radio survey-recommend "Definitely" "Probably" "Not Sure" "Probably Not" "Definitely Not"]</label> </li>
		<li> <label>How long have you used our product / service? [radio survey-timeofusage "Less than a month" "1-6 months" "1-3 years" "Over 3 Years" "Never used"]</label> </li>
		<li> <label>How often do you use our product / service? [radio survey-useourproduct "Once a week" "2 to 3 times a month" "Once a month" "Less than once a month"]</label> </li>
		<li> <label> What aspect of the product / service were you most satisfied by? [radio survey-aspectoftheproduct "Quality" "Price" "Purchase Experience" "Usage Experience" "Customer Service"] </label> </li>
		<li> <label> Overall, the product / service met my expectations? [radio survey-Overallofproduct "Strongly Agree" "Agree" "Neutral" "Disagree" "Strongly Disagree" "Dont Know"]</label> </li>
		<li> <label>Thinking of similar products / services offered by others, how would you compare the product / service offered by us? <br />[radio survey-similarproduct "Much Better" "Somewhat Better" "About the Same" "Somewhat Worse" "Much Worse" "Dont Know"]</label> </li>
	</ol>
	<div class="spp-clear"></div>
	<div class="spp-full-col"><label>What do you like about the product / service? [textarea survey-aboutproduct]</label></div>
	<div class="spp-full-col">[submit "SUBMIT"]</div>
</div>';

			$mail['subject'] = "[your-name] has filled out the survey form for you.";

			$mail['sender'] = "[your-name] <[your-email]>";

			$mail['body'] = '
Hello admin, 

A customer Submit survey. Here are the details and content of the request. 

<strong>Full Name:</strong> [your-name]
<strong>Email:</strong> [your-email]
<strong>How Satisfied:</strong> [survey-satisfied]
<strong>Recommend:</strong> [survey-recommend]
<strong>Time of Usage:</strong> [survey-timeofusage]
<strong>Use Product:</strong> [survey-useourproduct]
<strong>Aspect of Product:</strong> [survey-aspectoftheproduct]
<strong>Overall Product:</strong> [survey-Overallofproduct]
<strong>Think Similar Product:</strong> [survey-similarproduct]


<strong>Message Body:</strong>
[survey-aboutproduct]';

			break;		

		default: 
			$form = '
<div class="spp-row">
	<h2 style="text-align:center;line-height:1.5em;margin-bottom:20px;">Happy to <strong>Help</h2>
	<div class="spp-left-col">[text* your-name placeholder "Name"]</div>
	<div class="spp-right-col">[email* your-email placeholder "Email"]</div>
	<div class="spp-full-col">[textarea your-message placeholder "Message"]</div>
	<div class="spp-full-col">[submit "SUBMIT"]</div>
</div>';

			$mail['subject'] = "You have recieved a message from [your-name], [your-email].";

			$mail['sender'] = "<[your-email]>";

			$mail['body'] = "
Hello admin, 

A customer has contacted us. Here are the details and content of the request. 

<strong>Full Name:</strong> [full-name]
<strong>Email:</strong> [your-email]

<strong>Message Body:</strong>
[your-message]


The admin is advised to go through the customer's request and revert him back soon.

</div>";
	}
	
	//wp_die(print_r(array($title, $form, $mail), true)); 
	$contact_form->set_properties( array( 'mail' => $mail, 'form' => $form, 'messages' => $messages ) );
	
	$formId = $contact_form->save();

    // Update Redux Options as per the new Imported Popup
//    Redux::set_option( SPLITE_REDUX_OPTION_NAME, 'where_to_show', "everywhere" );
//    Redux::set_option( SPLITE_REDUX_OPTION_NAME, 'side-button-position', "pos_right" );
//
//    Redux::set_option( SPLITE_REDUX_OPTION_NAME, 'form-id', $formId );
//    Redux::set_option( SPLITE_REDUX_OPTION_NAME, 'popup-heading', "new heading text" );
//    Redux::set_option( SPLITE_REDUX_OPTION_NAME, 'popup-cta-text', "new heading text" );

    update_option('splite_last_import', time());
	return $formId; 
}
