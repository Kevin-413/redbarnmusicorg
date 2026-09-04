jQuery(document).ready(function() { // wait for page to finish loading 
	
	jQuery('.splite-dismissable').click( function(e) {
		
		e.preventDefault();
		$btnClicked = jQuery(this); 
		$parent = jQuery(this).parent(); 
		$parentBox = jQuery(this).closest('.notice'); 
		$parentSpinner = $parentBox.find('.spinner'); 
		
		$parentSpinner.addClass('is-active'); 
		
		jQuery.post(
			ajaxurl,
			{
				action : 'splite_notice_dismissable',
				dataBtn : $btnClicked.attr('data-btn'),
				security : $btnClicked.attr('data-nonce'),
			},
			function( response ) {				
				$parentSpinner.removeClass('is-active'); 
				if( response.success === true ) {					
					$parentBox.slideUp(); 
				}
				else {
					alert(response.data); 
				}								
			} 
		);
	});
	
	jQuery('.splite-btn-importer').click( function(e) {
		
		e.preventDefault();
		$btnClicked = jQuery(this); 
		$parent = jQuery(this).parent(); 
		$parentBox = jQuery(this).closest('.import-box'); 
		$loader = $parent.find('.sp-loader'); 
		$importResult = $parentBox.find('.import-box-result'); 
		
		//$btnClicked.addClass('animate'); 
		$loader.css({'visibility':'visible'}); //slideDown(); 		
		$importResult.html('').removeClass('error').removeClass('success').slideUp(); 
		$btnClicked.addClass('disable');

		jQuery.post(
			ajaxurl,
			{
				action : 'splite_action_importDemo',
				title : $btnClicked.attr('data-title'),
				security : $btnClicked.attr('data-nonce'),
			},
			function( response ) {				
				if( response.success === true ) {					
					$importResult.addClass('success').html(response.data.reason);
					if(response.data.reload)
						setTimeout(function() {location.reload();}, 1000);					 
				}
				else {
					$importResult.addClass('error').html(response.data.reason);
					if(response.data.reason.indexOf("exists")==0) {
					}						
				}
				$importResult.slideDown();
				$loader.css({'visibility':'hidden'}) //.slideUp(); 
			} 
		);
	});

	//Help and Support form submit button
	jQuery('.splite-submit-btn').click( function(e) {
		
		e.preventDefault();
		$btnClicked = jQuery(this); 
		$parentForm = jQuery(this).closest('form'); 
		$loader = $parentForm.find('.splite-loader'); 
		$importResult = $parentForm.find('.result-area'); 
		
		//$btnClicked.addClass('animate'); 
		$loader.css({'visibility':'visible'}); //slideDown(); 		
		$importResult.html('').removeClass('error').removeClass('success').slideUp(); 
		$btnClicked.addClass('disable');
		
		formFields = $parentForm.serialize(); 

		jQuery.post(
			ajaxurl,
			{
				action : 'action_splite_contact_support',
				fields : formFields,
			},
			function( response ) {				
				if( response.success === true ) {					
					$importResult.addClass('notice notice-success').html(response.data.reason);
					if(response.data.reload)
						setTimeout(function() {location.reload();}, 1000);		

					$parentForm[0].reset(); 
				}
				else {
					$importResult.addClass('error').html(response.data.reason);
					if(response.data.reason.indexOf("exists")==0) {
					}						
				}
				$importResult.slideDown();
				$loader.css({'visibility':'hidden'}) //.slideUp(); 
			} 
		);
	});
	
	jQuery(function() {
		let header = jQuery(".splite-layout__header");
		jQuery(window).scroll(function() {
			let scroll = jQuery(window).scrollTop();

			if (scroll >= 25) {
				header.addClass("is-scrolled");
			} else {
				header.removeClass("is-scrolled");
			}
		});
	});
	
});