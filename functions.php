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

	// New infinite scroll
	add_action('wp_ajax_infinite_scroll', 'wp_infinitepaginate');           // for logged in user
	add_action('wp_ajax_nopriv_infinite_scroll', 'wp_infinitepaginate');    // if user not logged in

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

// Remove images from excerpt
	function remove_images( $content ) {
   $postOutput = preg_replace('/<img[^>]+./','', $content);
   return $postOutput;
}
add_filter( 'the_content', 'remove_images', 100 );


/* SIDEBARS & WIDGET AREAS
 ========================== */
function themeFunction_widgets_init() {
	/***register_sidebar(array(
		*'name' => 'Main Navigation',
		*'id'   => 'sidebar-1',
		*'description'   => 'This is a widgetized area.',
		*'before_widget' => '<div id="%1$s" class="widget %2$s">',
		*'after_widget'  => '</div>',
		*'before_title'  => '<h4>',
		*'after_title'   => '</h4>'
	*));
	*/

	register_sidebar(array(
		'name' => 'Banner lateral pagina principal',
		'id'   => 'sidebar-2',
		'description'   => 'This is a widgetized area.',
		'before_widget' => '<div id="extra-1">',
		'after_widget'  => '',
		'before_title'  => '',
		'after_title'   => ''
	));
	/**
*register_sidebar(array(
		*'name' => 'Banner2 frontpage',
		*'id'   => 'sidebar-3',
		*'description'   => 'This is a widgetized area.',
		*'before_widget' => '<div id="extra-2">',
		*'after_widget'  => '',
		*'before_title'  => '',
		*'after_title'   => ''
	*));
	*register_sidebar(array(
		*'name' => 'Banner3 frontpage',
		*'id'   => 'sidebar-4',
		*'description'   => 'This is a widgetized area.',
		*'before_widget' => '<div id="extra-3">',
		*'after_widget'  => '',
		*'before_title'  => '',
		*'after_title'   => ''
	*));
	 */
	register_sidebar(array(
		'name' => 'Banner superior pagina principal',
		'id'   => 'banner-top',
		'description'   => 'This is a widgetized area.',
		'before_widget' => '<div class="extra-top">',
		'after_widget'  => '</div>',
		'before_title'  => '',
		'after_title'   => ''
	));

}
add_action( 'widgets_init', 'themeFunction_widgets_init' );

add_action( 'widgets_init', 'category_sidebars' );
/**
 * Create widgetized sidebars for each category
 *
 * This function is attached to the 'widgets_init' action hook.
 *
 * @uses	register_sidebar()
 * @uses	get_categories()
 * @uses	get_cat_name()
 */
function category_sidebars() {
	$categories = get_categories( array( 'hide_empty'=> 0 ) );

	foreach ( $categories as $category ) {
		if ( 0 == $category->parent )
			register_sidebar( array(
				'name' => $category->cat_name . ' lateral',
				'id' => $category->category_nicename . '-sidebar',
				'description' => 'This is the ' . $category->cat_name . ' widgetized area',
				'before_widget' => '<div id="extra-1">',
				'after_widget' => '',
				'before_title' => '',
				'after_title' => '',
			) );
	}
	foreach ( $categories as $category ) {
		if ( 0 == $category->parent )
			register_sidebar( array(
				'name' => $category->cat_name . ' superior',
				'id' => $category->category_nicename . '-top',
				'description' => 'This is the ' . $category->cat_name . ' widgetized area',
				'before_widget' => '<div id="extra-0">',
				'after_widget' => '',
				'before_title' => '',
				'after_title' => '',
			) );
	}
}


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
		'waypoints',
		get_template_directory_uri() . '/bower_components/jquery-waypoints/waypoints.min.js',
		array('jquery'),
		'2.1.0',
		true
	);

	//Main
	wp_enqueue_script(
		'plugins',
		get_template_directory_uri() . '/scripts/plugins.js',
		array('jquery'),
		false,
		true
	);

	//Main
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


// get the first category id
function get_first_category_ID() {
	$category = get_the_category();
	return $category[0]->cat_ID;
}

// Infinite Scroll new

function wp_infinitepaginate(){
    $loopFile        = $_POST['loop_file'];
    $paged           = $_POST['page_no'];
    $posts_per_page  = get_option('posts_per_page');

    # Load the posts
    query_posts(array('paged' => $paged ));
    get_template_part( $loopFile );

    exit;
}

?>
