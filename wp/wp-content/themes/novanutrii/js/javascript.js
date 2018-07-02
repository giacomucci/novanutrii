$(document).ready(function() {

	var scroll, wresize, mobile;
	var headerPos = $('.header-content').offset().top;
	var once = true;
	var init = false;
	var show, go;
	
	(scroll = function() {
		
		if(mobile != true && $('.header-content').css('position') != 'fixed') {
			var scrollPos = $(document).scrollTop();
			
			if(scrollPos > headerPos) {
				clearTimeout(show);
				init = true;
				if(once === true) {
					once = false;
					$('.header .cover').hide();
					go = setTimeout(function() {
						$('.header .cover').show();
					}, 400);
				}
				
				$('.header-content').addClass('attached').css({'top' : (scrollPos-headerPos)+'px'});
				
			} else if(init === true) {
				
				clearTimeout(go);
				
				$('.header-content').removeClass('attached').css({'top' : '0px'});
				once = true;
				$('.header .cover').hide();
				show = setTimeout(function() {
					$('.header .cover').show();
				}, 400);
				
				init = false;
			}
		}
		
	})();
	
	window.addEventListener('touchstart', function() {
		mobile = true;
	});
	
	(wresize = function() {
		msize = $('.header').width();
		$('.attached').width(msize);
	});
	
	$(document).scroll(scroll);
	$(window).resize(wresize);
	
});