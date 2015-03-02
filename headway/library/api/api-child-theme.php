<?php
class HeadwayChildThemeAPI {
	
	
	public static function init() {

		/* Set the HEADWAY_CHILD_THEME_ID constant to the stylesheet option if it hasn't been set. */
		if ( !defined('HEADWAY_CHILD_THEME_ID') )
			define('HEADWAY_CHILD_THEME_ID', get_option('stylesheet'));

	}

	
	public static function register_block_style(array $args) {
				
		return _deprecated_function(__FUNCTION__, '3.7');
				
	}
	
	
	public static function get_block_style_classes() {
		
		return _deprecated_function(__FUNCTION__, '3.7');

	}
		
	
}