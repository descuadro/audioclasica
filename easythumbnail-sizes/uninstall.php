<?php
//if uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

//drop a custom db table
global $wpdb;
$wpdb->query( "delete FROM $wpdb->options WHERE option_name like 'EasyThumbnailSizes%'" );

