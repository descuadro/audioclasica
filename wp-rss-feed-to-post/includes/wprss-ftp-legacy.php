<?php

/**
 * Contains all functions relating to the use of the legacy wprss_feed_item CPT.
 * 
 * @since 2.9.5
 */


/**
 * Returns TRUE if using the legacy imported feed items, FALSE otherwise.
 * If a source ID is given, it returns TRUE if the source is using the wprss_feed_item
 * post type, FALSE for any other.
 * 
 * @since 2.9.5
 * @param int|string $source_id The ID of the feed source
 */
function wprss_ftp_using_feed_items( $source_id = NULL ) {
	if ( $source_id === NULL ) {
		return WPRSS_FTP_Utils::multiboolean( WPRSS_FTP_Settings::get_instance()->get('legacy_enabled') );
	} else {
		return WPRSS_FTP_Meta::get_instance()->get( $source_id, 'post_type' ) == 'wprss_feed_item';
	}
}


add_action( 'wprss_ftp_after_settings_register', 'wprss_ftp_legacy_settings_section' );
/**
 * Registers the settings for the legacy compatability section.
 * 
 * @since 2.9.5
 */
function wprss_ftp_legacy_settings_section() {
	add_settings_section(
		'wprss_settings_legacy_section',
		'Feed to Post Compatability',
		'wprss_settings_legacy_callback',
		'wprss_settings_ftp'
	);
	
	add_settings_field(
		'wprss-settings-enable-legacy-cpt',
		'Legacy Feed Items',
		'wprss_ftp_legacy_enable_option',
		'wprss_settings_ftp',
		'wprss_settings_legacy_section'
	);
}


/**
 * Prints the description for the legacy callback section.
 * 
 * @since 2.9.5
 */
function wprss_settings_legacy_callback() {
	?><p>Change how Feed to Post works with WP RSS Aggregator</p><?php
}


/**
 * Prints the checkbox settings for enabling the legacy feed item.
 * 
 * @since 2.9.5
 */
function wprss_ftp_legacy_enable_option() {
	$legacy_enabled = WPRSS_FTP_Settings::get_instance()->get('legacy_enabled');
	echo WPRSS_FTP_Utils::boolean_to_checkbox(
		WPRSS_FTP_Utils::multiboolean( $legacy_enabled ),
		array(
			'id'	=>	'wprss-ftp-legacy-enabled',
			'name'	=>	WPRSS_FTP_Settings::OPTIONS_NAME . '[legacy_enabled]',
			'value'	=>	'true',
		)
	);
	?>
	<label for="wprss-ftp-legacy-enabled">
		Tick this box to re-enable the <strong>Feed Items</strong> and turn off post conversion for some feed sources.
		<br/>
		<small>This will also allow you to activate the <strong>Categories</strong> and <strong>Excerpts &amp; Thumbnails</strong> add-ons.</small>
	</label>
	<?php
}