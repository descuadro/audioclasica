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

// Infinite Scroll
console.log('loading')
$('.feed').infinitescroll({
  loading: {
    finished: undefined,
    finishedMsg: "<em>Enhorabuena, ha llegado usted al final de Internet!</em>",
                img: null,
    msg: null,
    msgText: "<em>Cargando historias...</em>",
    selector: null,
    speed: 'fast',
    start: undefined
  },
  state: {
    isDuringAjax: false,
    isInvalidPage: false,
    isDestroyed: false,
    isDone: false, // For when it goes all the way through the archive.
    isPaused: false,
    currPage: 1
  },
  behavior: undefined,
  binder: $(window), // used to cache the selector for the element that will be scrolling
  nextSelector: ".older",
  navSelector: ".pagination",
  contentSelector: null, // rename to pageFragment
  extraScrollPx: 150,
  itemSelector: "article.story",
  animate: false,
  pathParse: undefined,
  dataType: 'html',
  appendCallback: true,
  bufferPx: 40,
  errorCallback: function () { console.log('error') },
  infid: 0, //Instance ID
  pixelsFromNavToBottom: undefined,
  path: undefined, // Can either be an array of URL parts (e.g. ["/page/", "/"]) or a function that accepts the page number and returns a URL
  maxPage:undefined // to manually control maximum page (when maxPage is undefined, maximum page limitation is not work)
});