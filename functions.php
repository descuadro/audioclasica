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
		'name' => 'Categorias',
		'id'   => 'sidebar-1',
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
		get_template_directory_uri() . '/_bower_components/jquery/jquery.js',
		array(),
		'1.10.2',
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
		get_template_directory_uri() . '/_bower_components/modernizr/modernizr.js'
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



//Custom settings in associative array

?>