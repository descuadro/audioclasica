/*

Main!

*/

var body = $('body'),
		header = $('header[role="banner"]'),
		main = $('main[role="main"]');

var navOpen = false,
		toggleImage = $('[data-toggle="image-based"]');



// Init Icons

$('.navicon').click(function() {
	if ( !body.hasClass('navOpen') ) {
		body.addClass('navOpen');
	}

	else {
		body.removeClass('navOpen');
	}

	$(this).toggleClass('is-active');
});

$('.sorticon').click(function() {
	if ( !$('.catBanner').hasClass('is-open') ) { $('.catBanner').addClass('is-open');}
	else { $('.catBanner').removeClass('is-open');}
});


// Text / Image toggling

toggleImage.click(function(e) {
	if (e) { e.preventDefault(); };

	if ( $('html').hasClass('image-based') ) {
		$('html').removeClass('image-based').addClass('text-based');
	}

	else {
		$('html').removeClass('text-based').addClass('image-based');
	}
});


// Header Resizing

main.waypoint(function(direction) {
	header.toggleClass('big');
}, { offset: -50 });

main.waypoint(function(direction) {
	header.toggleClass('downer');
		if ( direction == 'down' ) {
			header.addClass('hide');
		}
}, { offset: -750 });

// Detecting scroll up
var lastScroll = 0;

$(window).scroll(function(event){
	if ( header.hasClass('downer') ) {
    //Sets the current scroll position
    var st = $(this).scrollTop();
    var hiddenHeader = true;
    //Determines up-or-down scrolling
    if (st > lastScroll){
       //Replace this with your function call for downward-scrolling
       if ( hiddenHeader = true ) {
       	header.addClass('hide');
       }
    }
    else {
       //Replace this with your function call for upward-scrolling
       header.removeClass('hide');
       hiddenHeader = false
    }
    //Updates scroll position
    lastScroll = st;
   }
});

// If reaching top aain
$('.page-container').waypoint(function(direction) {
	header.removeClass('hide');
}, { offset: -50 });