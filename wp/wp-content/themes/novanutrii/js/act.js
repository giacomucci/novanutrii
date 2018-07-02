$(document).ready(function() {
	// MENU
	$('#show').click(function() {
	    $('#menu').slideDown("fast");  
	})
	
	$('#hide').click(function() {
	    $('#menu').slideUp("fast");    
	})
	
	$(function() {
		cbpHorizontalMenu.init();
	});
});
