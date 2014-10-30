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
		navicon.set('times');
	}

	else {
		body.removeClass('navOpen');
		navicon.set('bars');
	}
});

$('.sorticon').click(function() {
	if ( !$('.catBanner').hasClass('is-open') ) {
		$('.catBanner').addClass('is-open');
		sorticon.set('times');
	}

	else {
		$('.catBanner').removeClass('is-open');
		sorticon.set('sort');
	}
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


/*
var $container = $('section.feed.text-based');


// Init Isotope
$container.isotope({
  itemSelector: '.story',
  layoutMode: 'masonry',
  masonry: {
  }
})
*/