/*

Main!

*/

var body = $('body');

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