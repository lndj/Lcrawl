// FLEX SLIDE 
$(window).load(function() {
    $('.flexslider2').flexslider({
      animation: 'fade', // Change Animation Type to slide
      smoothHeight: false,
      animationLoop: true,
      touch: true,
      directionNav: false,
      slideshowSpeed: 7000, // Slide Intervals
      animationSpeed: 300, // Animation Speeds/times
      slideshow: true,
      pauseOnAction: false, 
      controlNav: false,
      controlsContainer: '.flex-container'
    });
  });
  
  
  

  
  
  
  
  // RESPONSIVE MENU 
  var ww = document.body.clientWidth;
  
  $(document).ready(function() {
  	$(".nav li a").each(function() {
  		if ($(this).next().length > 0) {
  			$(this).addClass("parent");
  		};
  	})
  	
  	$(".toggleMenu").click(function(e) {
  		e.preventDefault();
  		$(this).toggleClass("active");
  		$(".nav").toggle();
  	});
  	adjustMenu();
  })
  
  $(window).bind('resize orientationchange', function() {
  	ww = document.body.clientWidth;
  	adjustMenu();
  });
  
  var adjustMenu = function() {
  	if (ww < 770) {
  		$(".toggleMenu").css("display", "inline-block");
  		if (!$(".toggleMenu").hasClass("active")) {
  			$(".nav").hide();
  		} else {
  			$(".nav").show();
  		}
  		$(".nav li").unbind('mouseenter mouseleave');
  		$(".nav li a.parent").unbind('click').bind('click', function(e) {
  			// must be attached to anchor element to prevent bubbling
  			e.preventDefault();
  			$(this).parent("li").toggleClass("hover");
  		});
  	} 
  	else if (ww >= 770) {
  		$(".toggleMenu").css("display", "none");
  		$(".nav").show();
  		$(".nav li").removeClass("hover");
  		$(".nav li a").unbind('click');
  		$(".nav li").unbind('mouseenter mouseleave').bind('mouseenter mouseleave', function() {
  		 	// must be attached to li so that mouseleave is not triggered when hover over submenu
  		 	$(this).toggleClass('hover');
  		});
  	}
  }
  
  
  // SUPER SLIDE 
$(document).ready(function() {
    $('#slides').superslides({
        play: true,
        slide_easing: 'easeInOutCubic',
        slide_speed: 800,
        delay: 6500,
        pagination: true
    });
    $('#slides').on('dragstart', function (event) {
        event.preventDefault();
        switch(event.direction){
            case "left":
                $('#slides').superslides.api.next();
                break;
            case "right":
                $('#slides').superslides.api.prev();
                break;
        };
       
    });
});
  
  
 
  
  
  // PAGE SCROLL   
$("document").ready(function() {			
	$('.feat').click(function(){
		$('html, body').animate({
		scrollTop: $(".section").offset().top
		}, 650);				   
					 
		});			
});


 
  
