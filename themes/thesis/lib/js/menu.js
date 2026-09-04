/*
Copyright 2012 DIYthemes, LLC. Patent pending. All rights reserved.
License: DIYthemes Software License Agreement
License URI: https://diythemes.com/thesis/rtfm/software-license-agreement/
*/
jQuery(document).ready(function($) {
	$(document).click(function(event) { 
		if (!$(event.target).closest('#t_nav .submenu').length)
			if ($('#t_nav .submenu').is(':visible')) {
				$('#t_nav .submenu').hide();
				$('.topitem').removeClass('active');
			}
	});
	$('.topitem').click(function() {
		$('#t_nav .submenu').hide();
		$(this).toggleClass('active');
		$(this).parent('.topmenu').siblings('.topmenu').children('.topitem').removeClass('active');
		if ($(this).hasClass('active'))
			$(this).siblings('.submenu').slideDown(100);
		return false;
	});
});