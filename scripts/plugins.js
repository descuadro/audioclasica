// Avoid `console` errors in browsers that lack a console.
(function() {
    var method;
    var noop = function () {};
    var methods = [
        'assert', 'clear', 'count', 'debug', 'dir', 'dirxml', 'error',
        'exception', 'group', 'groupCollapsed', 'groupEnd', 'info', 'log',
        'markTimeline', 'profile', 'profileEnd', 'table', 'time', 'timeEnd',
        'timeStamp', 'trace', 'warn'
    ];
    var length = methods.length;
    var console = (window.console = window.console || {});

    while (length--) {
        method = methods[length];

        // Only stub undefined methods.
        if (!console[method]) {
            console[method] = noop;
        }
    }
}());

// Place any jQuery/helper plugins in here.


// Infinite Scroll
var count = 2;
$(window).scroll(function(){
  if  ($(window).scrollTop() == $(document).height() - $(window).height()){
    loadArticle(count);
    count++;
  }
});

function loadArticle(pageNumber){
  var ajaxUrl = window.location.pathname + 'wp-admin/admin-ajax.php',
      loader = $('a#inifiniteLoader'),
      isVisible = false;

  loader.show('fast');

  $.ajax({
    url: ajaxUrl,
    type:'POST',
    data: 'action=infinite_scroll&page_no='+ pageNumber + '&loop_file=loop',

    success: function(html){
      loader.hide('1000');
      $('#home-feed .pagination').before(html);

      if ( loader.is(":visible") ) { isVisible = true; };
      if ( loader.delay(2000).is(":visible") ) { $('nav#pagination').hide('1000'); };
    }
  });

  return false;
}