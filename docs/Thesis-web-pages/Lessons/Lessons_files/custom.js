jQuery(document).ready(function($) {

	jQuery('#splite_form_container').on('click' ,function() { splite_set_popup(); });

	$animator = jQuery('.splite_popup_animator');
	var cf7formid = $animator.attr('data-cf7formid'); 
	
	$inputs = jQuery("#splite_popup_box input,  #splite_popup_box textarea, #splite_popup_box select");
	if($inputs.length) {
		$inputs.each(function(e) {			
			jQuery(this).focus(function(f){
				jQuery(this).removeClass('wpcf7-not-valid').closest('.wpcf7-form-control-wrap').find('span.wpcf7-not-valid-tip').css({'display':'none'}); 
			});
		});
		jQuery(document).on('click', 'span.wpcf7-not-valid-tip', function(e) { 
			jQuery(this).css({'display':'none'}).prev('input').focus(); 
		});
	}
	
	var external_unloader = jQuery( '.splite-closepopup' );	
	if( external_unloader.length ) {
		//console.log("sa");
		external_unloader.click( function(e) {
			if( external_loader.tagName() == 'A' ) {
				e.preventDefault(); 
				splite_unloader();
			}
			else {
				splite_unloader();
			}
		});
	}
	
	/* var external_loader = jQuery( '.splite-showpopup' ); 
	if( external_loader.length ) {
		external_loader.click( function(e) {
			if( external_loader.tagName() == 'A' ) {
				e.preventDefault(); 
				splite_loader();
			}
			else {
				splite_loader();
			}
		});
	} */

	var external_selectors = {};
	$animators = jQuery('.splite_popup_animator');
	if($animators.length) {
		i = 0; 
		$animators.each(function() {
			if (!! jQuery(this).attr('data-external_selectors')) {
				$selectors_added = jQuery(this).attr('data-external_selectors');
				var $selectors_added = $selectors_added.split(','); 
				
				var selector_parts = [];
				jQuery.each($selectors_added, function(index, value) {
					 selector_parts.push(jQuery.trim(value));
				});
				
				external_selectors[i] = selector_parts.join(','); 
				i++; 
			}
			
		});
	}
	
	//console.log(external_selectors);	
	// Add a click function for all externa_selectors
	jQuery.each(external_selectors, function(index, value) {
		loaderButton = jQuery(value); 
		loaderButton.click(function() {
			splite_loader();			
			return false; 
		});
	});
	
	document.addEventListener( 'wpcf7submit', function( event ) {
		if ( jQuery.isNumeric(cf7formid) && cf7formid == event.detail.contactFormId ) {
			splite_set_popup(id);
		}
	}, false );

	document.addEventListener( 'wpcf7mailsent', function( event ) {
		getSubmittedForm = jQuery('.splite_popup_animator[data-cf7-formID='+event.detail.contactFormId+']');
		if(getSubmittedForm.length) {

			autoclose = jQuery('.splite_popup_animator').attr('data-autoclose');
			autoclose_time = jQuery('.splite_popup_animator').attr('data-autoclose_time'); 
			redirect = jQuery('.splite_popup_animator').attr('data-redirect'); 
			redirect_url = jQuery('.splite_popup_animator').attr('data-redirect_url');

			if(redirect=="1" && redirect_url!='') {
				location.replace(redirect_url); 
			}
			else if(autoclose=="1" && autoclose_time!='') {
				setTimeout(function() {
					splite_unloader(); 
				}, parseInt(autoclose_time) * 1000);
			}
		}	 
	}, false );


	$popupBox = jQuery('#splite_popup_box');
	if ($popupBox.length) {
		var activationmode = jQuery(this).attr('data-activationmode');
		if(activationmode=='onexit') {
			//console.log(event.pageY==0 || event.pageY==1 || event.pageY < jQuery(document).scrollTop());
			jQuery( "body" ).on( "mouseout", function( event ) {
			  	if (event.pageY==0 || event.pageY==1 || event.pageY < jQuery(document).scrollTop() ) {
			  		if(jQuery("#splite_popup_box").hasClass("manage")){
			  			splite_loader();
			  		}
			  	}
			});
			//splite_loader();						
		}
	}

	splite_set_popup();
});


/**
* Function: tagName 
* Returns the HTML tag of the element
* Used for determining the tag of external anchor tag
**/ 
jQuery.fn.tagName = function() {
	return this.prop("tagName");
};


/**
* Function: splite_unloader 
* Unloads the popup box
**/ 
function splite_unloader() {    

	$animator = jQuery('.splite_popup_animator');
	var ef = $animator.data('unloadeffect'); 
	var speed = $animator.data('unloadspeed'); 

	//console.log(ef,speed);
	jQuery("#splite_popup_box").addClass(ef + ' animated').css({"-webkit-animation-duration": speed+"s","animation-duration": speed+"s"}).one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function(){
		jQuery(this).addClass('manage').removeClass(ef);
	});

	jQuery('#splite_curtain').fadeOut("slow");
	jQuery(".splite_container").css({ 
		"opacity": "1" 
	});
	//jQuery(this).addClass('manage');
	
	jQuery('body').enableScroll();
	
	return false;	
}   


/**
* Function: splite_loader 
* Loads the popup box
**/ 
function splite_loader() {  
	
	$animator = jQuery('.splite_popup_animator');
	var ef = $animator.data('loadeffect'); 
	var speed = $animator.data('loadspeed'); 
	
	// Load the Popup
	var docHeight = jQuery(document).height();
	jQuery('#splite_curtain').height(docHeight);	
	jQuery('#splite_curtain').fadeIn("fast");			
	//jQuery('#splite_popup_box').fadeIn("slow");
	
	splite_set_popup(		
		jQuery("#splite_popup_box").removeClass('manage').addClass(ef + ' animated').
		css({"-webkit-animation-duration": speed+"s","animation-duration": speed+"s"}).
		one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function(){jQuery(this).removeClass(ef);})
	);
	
	jQuery('body').disableScroll();
	
	return false;
}


/**
* Function: window.resize 
* Take action when window is resized
* Used to splite_set_popup() on resize
**/ 
jQuery( window ).resize(function() {	
	splite_set_popup();
});


/**
* Function: splite_set_popup
* Sets the popup position and layout
* Important and need improvement 30072016
**/ 
function splite_set_popup() {
	$popup = jQuery('#splite_popup_box');
	$curtain = jQuery('#splite_curtain');
	
	$title = jQuery('#splite_popup_title');	
	$description = jQuery('#splite_popup_description');	
	$formArea = jQuery('#splite_form_container');
	
	$animator = jQuery('.splite_popup_animator');
	var loadspeed = $animator.data('loadspeed'); 
		
	if ( $popup.length ) {
		var popupHeight = $popup.outerHeight();
		var titleHeight = $title.outerHeight();
		var descriptionHeight = $description.outerHeight();		
		$closeButton = jQuery('#splite_popupBoxClose');
		
		// Set Close Button Line Height to Title Height
		$closeButton.css({ 'line-height':titleHeight + 'px', });	
		descrptionHeight = 0; 
		formHeight = popupHeight - titleHeight - 24;
		$formArea.outerHeight(formHeight);
		formAreaHeight = $formArea.outerHeight();
		//alert( formAreaHeight + ' + ' +  formHeight ); 
		if( formAreaHeight >= formHeight ) {
			//formArea.css({'overflow-y':'scroll'});			
			//setTimeout(function(){
				$formArea.niceScroll({touchbehavior:false,cursorcolor:"#757575",cursoropacitymax:0,cursorwidth:0,cursorborder:"1px solid #2848BE",cursorborderradius:"0px",background:"#ccc",autohidemode:"scroll"});//.cursor.css({"background-image":"url(img/mac6scroll.png)"}); // MAC like scrollbar	
			//},0);			
			$formArea.getNiceScroll().resize()
		}
		
		// Set Popup Left Position
		curtainWidth = $curtain.width();
		popupWidth = $popup.width();		
		popLeft = (curtainWidth - popupWidth)/2;
		//alert(popLeft);
		
		// Set Popup Top Position
		windowHeight = jQuery(window).height();
		popTop = (windowHeight - popupHeight)/2;
		
		$popup.css( 'left', popLeft+'px' );
		$popup.css( 'top', popTop+'px' );
	}
	
	splite_set_side_button();
}

jQuery('.nikhil').on('click' ,function() {
	alert("Hello")
	//splite_set_popup();
});

/**
* Function: enableScroll
* stackoverflow: /8701754/just-disable-scroll-not-hide-it
**/ 
jQuery.fn.enableScroll = function() {
    jQuery(window).off('scroll.scrolldisabler');
};

/**
* Function: disableScroll
* stackoverflow: /8701754/just-disable-scroll-not-hide-it
**/ 
jQuery.fn.disableScroll = function() {
    window.oldScrollPos = jQuery(window).scrollTop();

    jQuery(window).on('scroll.scrolldisabler',function ( event ) {
       jQuery(window).scrollTop( window.oldScrollPos );
       event.preventDefault();
    });
};
 
/**
* Function: set_side_button
* Sets the side button position
* Important and needs improvement 30072016
**/ 
function splite_set_side_button() {
	
	$side_button = jQuery('a.splite_sideEnquiry');
	$curtain = jQuery('#splite_curtain');
	
	var button_width = $side_button.outerWidth();
	var button_height = $side_button.outerHeight();
	
	if( button_width > button_height ) 
		var button_pull = button_height/2 - button_width/2;
	else 	
		var button_pull = button_width/2 - button_height/2;
	
	//alert(button_width + ' - ' + button_height + ' = ' + button_pull);
	
	// Adjust if button right
	var side_button_pos_right = jQuery('a.splite_sideEnquiry.pos_right');
	if( side_button_pos_right.length ) 
		side_button_pos_right.css( 'right', button_pull+ 'px' );
	
	// Adjust if button left
	var side_button_pos_left = jQuery('a.splite_sideEnquiry.pos_left');
	if( side_button_pos_left.length )
		side_button_pos_left.css( 'left', button_pull+ 'px' );

	
	//side_button_on_mobile = jQuery('a.splite_sideEnquiry.on_mobile');
}