<?php

/*
Plugin Name: Easy Thumbnail Sizes
Plugin URI: http://www.wordpress.org/plugins/easythumbnail-sizes
Description: The easiest way to add custom sized thumbnails to any installed theme. No coding required.
Author: Lutz Schr&ouml;er
Version: 1.0.1
Author URI: http;//www.elektroelch.net
*/

add_action( 'plugins_loaded', 'EasyThumbnailSizes_load_textdomain');
function EasyThumbnailSizes_load_textdomain()
{
	load_plugin_textdomain('EasyThumbnailSizes', false, dirname(plugin_basename(__FILE__)) . '/languages' );
}

require_once( 'EasyThumbnailSizes_options.php' );

$option_name = 'EasyThumbnailSizes-' . str_replace( ' ', '-', wp_get_theme()->get( 'Name' ) );
$imagesizes  = get_option( $option_name );
if (! $imagesizes)
	$imagesizes = array();

add_action( 'admin_init', 'EasyThumbnailSizes_add_image_sizes' );
function EasyThumbnailSizes_add_image_sizes() {
	global $imagesizes;
	if ( function_exists( 'add_image_size' ) ) {
		foreach ( $imagesizes as $imagesize ) {
			add_image_size( $imagesize['slug'], $imagesize['width'], $imagesize['height'], $imagesize['crop'] == true );
		} //foreach
	} //if
} //EasyThumbnailSizes_add_image_sizes()

add_filter( 'image_size_names_choose', 'EasyThumbnailSizes_image_sizes' );
function EasyThumbnailSizes_image_sizes( $sizes ) {
	global $imagesizes;
	$add_sizes = array();
	foreach ( $imagesizes as $imagesize ) {
		$add_sizes[ $imagesize['slug'] ] = $imagesize['name'];
	} //foreach

	return array_merge( $sizes, $add_sizes );
} //EasyThumbnailSizes_image_sizes()
