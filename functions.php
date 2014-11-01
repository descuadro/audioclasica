<?php
/**
 * Theme functions
 *
 * Sets up the theme and provides some helper functions.
 *
 * @package themeHandle
 */


/* OEMBED SIZING
 ========================== */

if ( ! isset( $content_width ) )
	$content_width = 600;


/* THEME SETUP
 ========================== */

if ( ! function_exists( 'themeFunction_setup' ) ):
function themeFunction_setup() {

	// Available for translation
	load_theme_textdomain( 'themeTextDomain', get_template_directory() . '/languages' );

	// Add default posts and comments RSS feed links to <head>.
	add_theme_support( 'automatic-feed-links' );

	// Add custom nav menu support
	register_nav_menu( 'primary', __( 'Primary Menu', 'themeTextDomain' ) );

	// Add featured image support
	add_theme_support( 'post-thumbnails' );

	add_image_size('Banner 1 big',1204,600,true);
	add_image_size('Banner 2 big',1204,768,true);
	add_image_size('Banner 3 big',1204,768,true);

	add_image_size('Banner 1 small',350,332,true);
	add_image_size('Banner 2 small',350,450,true);
	add_image_size('Banner 3 small',350,526,true);

	add_image_size('Banner 1 mid',600,570,true);
	add_image_size('Banner 2 mid',600,770,true);
	add_image_size('Banner 3 mid',600,900,true);

	// Enable support for HTML5 markup.
	add_theme_support( 'html5', array(
		'comment-list',
		'search-form',
		'comment-form',
		'gallery',
	) );

	// Add custom image sizes
	// add_image_size( 'name', 500, 300 );
}
endif;
add_action( 'after_setup_theme', 'themeFunction_setup' );


/* SIDEBARS & WIDGET AREAS
 ========================== */
function themeFunction_widgets_init() {
	register_sidebar(array(
		'name' => 'Main Navigation',
		'id'   => 'sidebar-1',
		'description'   => 'This is a widgetized area.',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h4>',
		'after_title'   => '</h4>'
	));

	register_sidebar(array(
		'name' => 'Banners',
		'id'   => 'sidebar-2',
		'description'   => 'This is a widgetized area.',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h4>',
		'after_title'   => '</h4>'
	));
}
add_action( 'widgets_init', 'themeFunction_widgets_init' );


/* ENQUEUE SCRIPTS & STYLES
 ========================== */
function themeFunction_scripts() {
	// theme style.css file
	wp_enqueue_style( 'themeTextDomain-style', get_stylesheet_uri() );

	wp_deregister_script( 'jquery' );


	// Footer Scripts
	wp_register_script( 'jquery',
		get_template_directory_uri() . '/bower_components/jquery/jquery.js',
		array(),
		'1.10.2',
		true
	);

	wp_enqueue_script(
		'infinite-scroll',
		get_template_directory_uri() . '/bower_components/jquery-infinite-scroll/jquery.infinitescroll.js',
		array('jquery'),
		'2.1.0',
		true
	);

	wp_enqueue_script(
		'enquire',
		get_template_directory_uri() . '/bower_components/enquire/dist/enquire.js',
		array('jquery'),
		'2.1.0',
		true
	);

	wp_enqueue_script(
		'picturefill',
		get_template_directory_uri() . '/bower_components/picturefill/dist/picturefill.js',
		array('jquery'),
		'2.1.0',
		true
	);

	wp_enqueue_script(
		'imagesloaded',
		get_template_directory_uri() . '/bower_components/imagesloaded/imagesloaded.pkgd.js',
		array('jquery'),
		'2.1.0',
		true
	);

	wp_enqueue_script(
		'main',
		get_template_directory_uri() . '/scripts/main.js',
		array('jquery'),
		false,
		true
	);


	// Header Scripts
	wp_enqueue_script(
		'typekit', '//use.typekit.net/hxo6ukw.js'
	);
	wp_enqueue_script(
		'modernizr',
		get_template_directory_uri() . '/bower_components/modernizr/modernizr.js'
	);
		/*
		wp_enqueue_style(
		'fa',
		get_template_directory_uri() . '/maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css',
		array('style')
	);
		*/
}
add_action('wp_enqueue_scripts', 'themeFunction_scripts');


/* MISC EXTRAS
 ========================== */

// Comments & pingbacks display template
include('inc/functions/comments.php');

// Optional Customizations
// Includes: TinyMCE tweaks, admin menu & bar settings, query overrides
include('inc/functions/customizations.php');

/* AUDIOCLASIK BANNERS
 ========================== */

add_filter( 'image_size_names_choose', 'my_custom_sizes' );

function my_custom_sizes( $sizes ) {
    return array_merge( $sizes, array(
        'your-custom-size' => __('Your Custom Size Name'),
    ) );
}
echo wp_get_attachment_image( 42, 'custom-size' );


	function sgr_display_image_size_names_muploader( $sizes ) {

	    $new_sizes = array();

	    $added_sizes = get_intermediate_image_sizes();

	    // $added_sizes is an indexed array, therefore need to convert it
	    // to associative array, using $value for $key and $value
	    foreach( $added_sizes as $key => $value) {
	        $new_sizes[$value] = $value;
	    }

	    // This preserves the labels in $sizes, and merges the two arrays
	    $new_sizes = array_merge( $new_sizes, $sizes );

	    return $new_sizes;
	}
	add_filter('image_size_names_choose', 'sgr_display_image_size_names_muploader', 11, 1);

//Custom settings in associative array

# Pagination links
add_filter('next_posts_link_attributes', 'sdac_next_posts_link_attributes');
function sdac_next_posts_link_attributes(){
        return 'class="older"';
}

add_filter('previous_posts_link_attributes', 'sdac_previous_posts_link_attributes');
function sdac_previous_posts_link_attributes(){
        return 'class="newer"';
}

/**
 * Infinite Scroll
 */

function custom_infinite_scroll_js() {
	if( ! is_singular() ) { ?>
	<script>
		var infinite_scroll = {
			loading: {
				img: "<?php echo get_template_directory_uri(); ?>/assets/images/loading.gif",
				msgText: "<?php _e( 'Cargando más historias...', 'custom' ); ?>",
				finishedMsg: "<?php _e( 'Enhorabuena! Llegó usted al fondo del océano!', 'custom' ); ?>"
			},
			"nextSelector":".pagination .older",
			"navSelector":".pagination",
			"itemSelector":"article",
			"contentSelector":".feed",
			"animate":"true"
		};
		jQuery( infinite_scroll.contentSelector ).infinitescroll( infinite_scroll );
	</script>
	<?php
	}
}
add_action( 'wp_footer', 'custom_infinite_scroll_js',100 );

?>

