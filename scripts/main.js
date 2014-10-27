/*

Main!

*/

var body = $('body');

var navicon = new Marka('.navicon'),
		sorticon = new Marka('.sorticon'),
		navOpen = false;



// Init Icons

navicon.set('bars').size(30);

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


sorticon.set('sort').size(30);
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