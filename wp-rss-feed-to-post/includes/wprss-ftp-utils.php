<?php

/**
 * General, utility and helper functions, to make code more shorter and more readable.
 *
 * @since 1.0
 */

if ( !class_exists( 'WPRSS_FTP_Settings' ) ) {
	require_once ( WPRSS_FTP_INC . 'wprss-ftp-settings.php' );
}


final class WPRSS_FTP_Utils {


	/**
	 * Used internally to log error messages to a log file.
	 * 
	 * @since 1.0
	 */
	public static function log( $message, $src = 'Feed to Post' ) {
		// check if the logging function exists in the core
		if ( function_exists( 'wprss_log' ) ) {
			wprss_log( $message, $src );
		} else {
			$date =  date( 'd-m-Y H:i:s' );
			$source = 'Feed to Post' . ( ( strlen( $src ) > 0 )? " ($src)" : '' ) ;
			$str = "[$date] $source: '$message'\n";
			file_put_contents( WPRSS_FTP_LOG_FILE , $str, FILE_APPEND );
		}
	}


	/**
	 * Calls the log function with a print_r of the given object
	 * 
	 * @since 1.0
	 */
	public static function log_object( $message, $obj, $src = 'Feed to Post' ) {
		WPRSS_FTP_Utils::log( "$message " . print_r( $obj, TRUE ), $src );
	}


	/**
	 * Clears the log file
	 * 
	 * @since 1.9
	 */
	public static function clear_log() {
		file_put_contents(  WPRSS_FTP_LOG_FILE , '' );
	}


	/**
	 * Returns the contents of the log file.
	 * If the log file does not exists, creates it.
	 * 
	 * @since 1.9
	 */
	public static function get_log() {
		if ( !file_exists( WPRSS_FTP_LOG_FILE ) ) {
			WPRSS_FTP_Utils::clear_log();
		}
		return file_get_contents(  WPRSS_FTP_LOG_FILE , '' );
	}


	/**
	 * Updates the posts content, taking care to remove KSES filters where needed.
	 * 
	 * @since 2.5
	 */
	public static function update_post_content( $post_id, $content, $title = NULL ) {
		// Get the post's soruce
		$source = WPRSS_FTP_Meta::get_instance()->get_meta( $post_id, 'feed_source' );
		// Check if embedded content is allowed
		$allow_embedded_content = WPRSS_FTP_Meta::get_instance()->get_meta( $source, 'allow_embedded_content' );

		// If embedded content is allowed, remove KSES filtering
		if ( WPRSS_FTP_Utils::multiboolean( $allow_embedded_content ) === TRUE ) {
			kses_remove_filters();
		}

		// Prepare the args
		$args = array(
			'ID'			=>	$post_id,
			'post_content'	=>	$content
		);
		// If the title is given, add it to the args
		if ( $title !== NULL ) {
			$args['post_title'] = $title;
		}
		// Update the post
		wp_update_post( $args );

		// If embedded content is allowed, re-add KSES filtering
		if ( WPRSS_FTP_Utils::multiboolean( $allow_embedded_content ) === TRUE ) {
			kses_init_filters();
		}
	}


	/**
	 * Checks if a remote file exists, by pinging it and checking the status code.
	 * 
	 * @param $url The url of the remote resource
	 * @since 1.3
	 */
	public static function remote_file_exists( $url ) {
		$exists = FALSE;

		$curl = curl_init($url);
		// ping the page
		curl_setopt( $curl, CURLOPT_NOBODY, true );
		$response = curl_exec($curl);
		// if the response is not FALSE
		if ( $response !== FALSE ) {
			// check the response status code
			$statusCode = curl_getinfo( $curl, CURLINFO_HTTP_CODE );
			// If recieved a status code of OK ( 200 )
			if ( $statusCode == 200 ) {
				$exists = TRUE;
			}
		}
		// Close the curl instance
		curl_close( $curl );

		return $exists;
	}


	/**
	 * Encodes the given URL, parses it and returns its components.
	 * 
	 * @since 1.0
	 */
	public static function encode_and_parse_url( $url ) {
		$encodedUrl = @preg_replace( '%[^:/?#&=\.]+%usDe', 'urlencode(\'$0\')', $url );
	    $components = parse_url( $encodedUrl );
	    foreach ( $components as &$component ) {
	        $component = urldecode($component);
	    }
	    return $components;
	}


	/**
	 * Checks if multisite is enabled.
	 * 
	 * @since 1.7
	 */
	public static function is_multisite() {
		if ( function_exists( 'wp_get_sites' ) && function_exists( 'is_multisite' ) )
			return is_multisite();
		else
			return FALSE;
	}


	/**
	 * Returns TRUE if using WP multisite and the current user is the super admin, FALSE if not,
	 * and a message for output if wp_is_large_network() returns TRUE.
	 * 
	 * @since 1.7
	 */
	public static function is_multisite_and_main_site() {
		if ( self::is_multisite() === FALSE )
			return FALSE;
		else
			return ( count( wp_get_sites() ) === 0 )?
				'We could not retrieve the list of sites, because the network has no sites or is too large!'
			:	( is_multisite() && is_main_site() );
	}


	/**
	 * Returns a list of site names. Used for dropdowns in metaboxes
	 * 
	 * @since 1.7
	 */
	public static function get_sites() {
		$site_objects = wp_get_sites();
		$sites = array();
		foreach ( $site_objects as $i => $obj ) {
			$text = $obj['path'];
			if ( $text == '/' ) $text = $obj['domain'];
			$sites[ $obj['blog_id'] ] = $text;
		}
		return $sites;
	}


	/**
	 * Returns an array of radio elements for the given associative array.
	 * Array _must_ be associative.
	 * 
	 * @since 1.0
	 */
	public static function array_to_radio_buttons( $array, $pArgs = array() ) {
		// Merge the passed parameter arguments with the defaults
		$defaults = array(
			'id'					=>	'',
			'class' 				=> 	NULL,
			'name'					=>	NULL,
			'checked'				=>	NULL
		);
		$args = wp_parse_args( $pArgs, $defaults );

		// Prepare the variables
		$class = ( $args['class'] === NULL )? '' : ' class="'.$args['class'].'"';
		$name = ( $args['name'] === NULL )? '' : ' name="'.$args['name'].'"';

		$radios = array();
		$i = 0;
		foreach( $array as $key => $value ) {
			$id = $args['id'] . '-' . $i++;
			$checked = ( $args['checked'] !== NULL && $args['checked'] === $key )? 'checked="checked"': '';
			$radios[] = "<input type='radio' value='$key' id='$id' $name $class $checked /><label for='$id'>$value</label> ";
		}

		return $radios;
	}


	/**
	 * Returns a select element for the given associative array.
	 * Array _must_ be associative.
	 *
	 * @since 1.0
	 */
	public static function array_to_select( $array, $pArgs = array() ) {
		// Merge the passed parameter arguments with the defaults
		$defaults = array(
			'id'					=>	NULL,
			'class' 				=> 	NULL,
			'name'					=>	NULL,
			'selected'				=>	NULL,
			'options_only'			=>	FALSE,
			'add_default_option'	=>	FALSE,
			'multiple'				=>	FALSE,
			'disabled'				=>	FALSE,
		);
		$args = wp_parse_args( $pArgs, $defaults );

		// Prepare the variables
		$id = ( $args['id'] === NULL )? '' : ' id="'.$args['id'].'"';
		$class = ( $args['class'] === NULL )? '' : ' class="'.$args['class'].'"';
		$name = ( $args['name'] === NULL )? '' : ' name="'.$args['name'].'"';
		$disabled = ( $args['disabled'] === FALSE )? '' : 'disabled="disabled"';
		// Check multiple tag
		$multiple = '';
		if ( $args['multiple'] === TRUE ) {
			$multiple = ' multiple="multiple"';
			// If using a multiple tag, set the name to an array to accept multiple values
			if ( $args['name'] !== NULL ) {
				$name = ' name="'.$args['name'].'[]"';
			}
		}
		// WP MP6 responsiveness fix - set height to auto
		$fix = ( $args['multiple'] === TRUE )? 'style="height:auto;"' : '';
		
		$select = '';
		// Generate the select elements
		if ( $args['options_only'] !== TRUE )
			$select = "<select $id $class $name $fix $multiple $disabled>";
		if ( $args['add_default_option'] === TRUE ){
			$array = array_merge( array( '' => 'Use Default' ), $array );
		}
		foreach ( $array as $key => $value ) {
			if ( is_array($value) ) {
				$select .= "<optgroup label='$key'>";
				$recursionArgs = $pArgs;
				$recursionArgs['options_only'] = TRUE;
				$select .= self::array_to_select( $value, $recursionArgs );
				$select .= "</optgroup>";
				continue;
			}
			$selected = FALSE;
			if ( is_array( $args['selected'] ) ) {
				$selected = in_array( $key, $args['selected'] );
			}
			else $selected = ( $args['selected'] !== NULL && $args['selected'] == $key );
			$selected = ( $selected == TRUE )? 'selected="selected"': '';

			$select .= "<option value='$key' $selected>$value</option>";
		}
		if ( $args['options_only'] !== TRUE )
			$select .= "</select>";

		// Return the generated select element.
		return $select;
	}


	/**
	 * Returns an <input> checkbox element for the given boolean.
	 * The booleam determines whether the checkbox will be checked or not.
	 * The boolean can also be a string.
	 *
	 * @since 1.0
	 */
	public static function boolean_to_checkbox( $pBool, $pArgs ) {
		// Merge the passed parameter arguments with the defaults
		$defaults = array(
			'id'					=>	NULL,
			'class' 				=> 	NULL,
			'name'					=>	NULL,
			'value'					=>	NULL,
			'disabled'				=>	FALSE,
		);
		$args = wp_parse_args( $pArgs, $defaults );
		// Check if the parameter boolean is a string
		$bool = ( is_string( $pBool ) )? WPRSS_FTP_Utils::multiboolean( $pBool ) : $pBool;
		// Prepare the variables
		$id = ( $args['id'] === NULL )? '' : 'id="'.$args['id'].'"';
		$class = ( $args['class'] === NULL )? '' : 'class="'.$args['class'].'"';
		$name = ( $args['name'] === NULL )? '' : 'name="'.$args['name'].'"';
		$value = ( $args['value'] === NULL )? '' : 'value="'.$args['value'].'"';
		$checked = ( $bool === FALSE )? '' : 'checked="checked"';
		$disabled = ( $args['disabled'] === FALSE )? '' : 'disabled="disabled"';

		return "<input type='hidden' $name value='false' /><input type='checkbox' $id $name $value $class $checked $disabled>";
	}


	/**
	 * Returns whether or not the given boolean string is a known
	 * 'true' value.
	 *
	 * @since 1.0
	 */
	public static function multiboolean( $pBool ) {
		$pBool = ( is_string( $pBool ) === TRUE )? strtolower( $pBool ) : $pBool;
		return in_array(
			$pBool,
			array (
				'true',
				'open',
				'yes',
				'on',
				'y',
				't'
			)
		);
	}


	/**
	 * Performs a mass replace on the given string
	 *
	 * @since 1.0
	 */
	public static function str_mass_replace( $string, $replacements) {
		$new_str = $string;
		foreach ($replacements as $old => $new) {
			$new_str = str_replace( $old, $new, $new_str );
		}
		return $new_str;
	}


	/**
	 * Uses mass replace to template the given string
	 *
	 * @since 1.0
	 */
	public static function template( $template, $replacements ) {
		$new_replacements = array();
		foreach ( $replacements as $key => $value ) {
			$new_replacements['{{'.$key.'}}'] = $value;
		}
		return self::str_mass_replace( $template, $new_replacements );
	}


	/**
	 * Returns a dropdown with the object taxonomies on the 'post_type' parameter in
	 * the POST request, to the client. The <select> element returned differs according
	 * to the 'source' in the POST request.
	 * 
	 * @since 1.0
	 */
	public static function generate_taxonomy_dropdown() {
		$TAX_IGNORE = array(
			'post_format'
		);
		$settings = WPRSS_FTP_Settings::get_instance();
		$source = isset( $_POST['source'] )? $_POST['source'] : '';
		$post_id = isset( $_POST['post_id'] )? $_POST['post_id'] : NULL;

		$selected = WPRSS_FTP_Meta::get_instance()->get_meta( $post_id, 'post_taxonomy' );
		if ( $selected === '' ) $selected = $settings->get( 'post_taxonomy' );

		$post_type = isset( $_POST['post_type'] )? $_POST['post_type'] : $settings->get('post_type');
		$taxonomy = $settings->get( 'post_taxonomy' );
		$taxonomies = get_object_taxonomies( $post_type, 'object' );
		$keys = array_keys( $taxonomies );
		$vals = array_map( array( 'WPRSS_FTP_Utils', 'get_tax_name' ), $taxonomies );

		$id = ( $source === 'meta' )? WPRSS_FTP_Meta::META_PREFIX . 'post_taxonomy' : 'ftp-post-taxonomy';
		$name = ( $source === 'meta' )? WPRSS_FTP_Meta::META_PREFIX . 'post_taxonomy' : WPRSS_FTP_Settings::OPTIONS_NAME . '[post_taxonomy]';

		if ( $taxonomies === NULL || count( $taxonomies ) === 0 ) {
			echo '<p id="'. $id .'">No taxonomies for the selected post type were found!</p>';
			echo '<input type="hidden" name="' . WPRSS_FTP_Settings::OPTIONS_NAME . '[post_taxonomy]" value="" />';
			die();
		}

		$taxonomies = array_combine( $keys, $vals );
		foreach ( $TAX_IGNORE as $ignore ) {
			if ( isset( $taxonomies[$ignore] ) ) {
				unset( $taxonomies[$ignore] );
			} 
		}
		
		# Generate the taxonomy dropdown
		$args = array(
			'id'		=>	$id,
			'name'		=>	$name,
			'selected'	=>	$selected
		);
		# Print the taxonomy dropdown
		echo self::array_to_select( $taxonomies, $args );

		# Re-print the description
		$tax_meta_fields = WPRSS_FTP_Meta::get_instance()->get_meta_fields('tax');
		echo '<br><span class="description">'. $tax_meta_fields['post_taxonomy']['desc'] .'</span>';

		# End AJAX
		die();
	}


	public static function generate_tax_terms_dropdown() {
		$settings = WPRSS_FTP_Settings::get_instance();
		$taxonomy = isset( $_POST['taxonomy'] )? $_POST['taxonomy'] : $settings->get('post_taxonomy');
		$post_id = isset( $_POST['post_id'] )? $_POST['post_id'] : NULL;
		$source = isset( $_POST['source'] )? $_POST['source'] : '';

		$id = ( $source === 'meta' )? WPRSS_FTP_Meta::META_PREFIX . 'post_terms' : 'ftp-post-terms';
		$name = ( $source === 'meta' )? $id : WPRSS_FTP_Settings::OPTIONS_NAME . '[post_terms]';

		if ( $taxonomy === NULL || $taxonomy === '' ) {
			echo '<p id="'.$id.'">No terms were found for this taxonomy.</p>';
			echo '<input type="hidden" name="' . WPRSS_FTP_Settings::OPTIONS_NAME . '[post_terms]" value="" />';
			die();
		}

		# Get the terms for the given taxonomy
		$terms = $settings->get_term_names(
			$taxonomy,
			array(
				'hide_empty'	=>	false,
				'order_by'		=>	'name'
			)
		);

		if ( $terms === NULL || count( $terms ) === 0 ) {
			echo '<p id="'.$id.'">No terms were found for this taxonomy.</p>';
			echo '<input type="hidden" name="' . WPRSS_FTP_Settings::OPTIONS_NAME . '[post_terms]" value="" />';
			die();
		}
		else {
			# Print the terms dropdown
			$args = array(
				'id'		=>	$id,
				'name'		=>	$name,
				'selected'	=>	$settings->get('post_terms'),
				'multiple'	=>	TRUE
			);
			if ( $source === 'meta' ) {
				if ( $post_id !== NULL )
					$args['selected'] = WPRSS_FTP_Meta::get_instance()->get_meta( $post_id, 'post_terms' );
				if ( $args['selected'] === '' )
					$args['selected'] = $settings->get('post_terms');
			}
			echo self::array_to_select( $terms, $args );
			
			# Re-print the description
			$tax_meta_fields = WPRSS_FTP_Meta::get_instance()->get_meta_fields('tax');
			echo '<br><span class="description">'. $tax_meta_fields['post_terms']['desc'] .'</span>';
		}

		die();
	}


	public static function collapse_metabox_for_user( $user_ID, $page, $box_ID ) {
		// Get the current option
		$optionName = "closedpostboxes_$page";
		$closed = get_user_option( $optionName, $user_ID );
		// Turn string into an array and add the new metabox ID
		//$closeIds = explode( ',', $close );
		//$closeIds[] = $box_ID;
		$closed[] = $box_ID;
		// Remove duplicate IDs
		//$closeIds = array_unique( $closeIds );
		$closedUnique = array_unique( $closed );
		// Turn back to a string
		//$close = implode( ',', $closeIds );
		// Update the option
		update_user_option( $user_ID, $optionName, $closedUnique, TRUE );
	}


	public static function close_ftp_metabox_for_user_by_default( $user_ID, $box_ID ) {
		$page = 'wprss_feed';
		$optionName = "closedpostboxes_$page";
		$closed = get_user_option( $optionName, $user_ID );
		// Close the meta box
		self::collapse_metabox_for_user( $user_ID, $page, $box_ID );
	}


	public static function get_wpml_languages() {
		if ( !defined( 'ICL_SITEPRESS_VERSION' ) ) return array();
		$languages_before = icl_get_languages();
		$languages_after = array();
		foreach ( $languages_before as $key => $value ) {
			$languages_after[$key] = $value['native_name'];
		}
		return $languages_after;
	}


	public static function get_tax_name( $item ) {
		return $item->label;
	}


	/*===== UPDATE FUNCTIONS =======================================*/


	/**
	 * Renders the notice regarding the source link update.
	 * 
	 * @since 2.4
	 */
	public static function source_link_update_notice() {
		?>
		<div class="updated">
			<p>
				<b>WP RSS Aggregator (Feed to Post):</b> The 'Source Link Text' option has been replaced with the 'Append to Content' option.
				Your saved settings for this old option has been automatically converted and added to the new option, for all feed sources that had it saved.<br/>
				Read more about it <a href="http://www.wprssaggregator.com/docs/source-link-text-option-removed-v2-4/" target="_blank">here</a>.
			</p>
		</div>
		<?php
	}


	/**
	 * Disables the link to source option for each feed source
	 * 
	 * @since 2.4
	 */
	public static function source_link_update() {
		// Get the meta class instance
		$meta = WPRSS_FTP_Meta::get_instance();

		if ( ! function_exists( "wprss_get_all_feed_sources" ) ) return;

		// Get all feed sources
		$feed_sources = wprss_get_all_feed_sources();

		// Keep a count of feed sources that got updated
		$count = 0;

		// Iterate all feed sources
		while ( $feed_sources->have_posts() ) {
			// Prepare loop variables
			$feed_sources->the_post();
			$ID = get_the_ID();

			// Get the source link enable and text meta for the feed sources
			$source_link = $meta->get_meta( $ID, 'source_link' );
			$source_link_text = $meta->get_meta( $ID, 'source_link_text' );
			// Get the post append text
			$post_append = $meta->get_meta( $ID, 'post_append' );
			
			// If the post's feed source has source_link enabled ...
			if ( WPRSS_FTP_Utils::multiboolean( $source_link ) === TRUE ) {

				// Disable the source link option
				update_post_meta( $ID, WPRSS_FTP_Meta::META_PREFIX . 'source_link', 'false' );

				// Increment the count
				$count++;
				
				// If an asterisk is found in the source link text, use regex to generate the linked phrase
				if ( stripos( $source_link_text, '*') !== FALSE ) {
					// Prepare the replacement <a> tag with the placeholder for feed_url
					$feed_url_link = "<a target=\"_blank\" href=\"{{feed_url}}\">$1</a>";
					// Replace the string in double asteriks into the <a> tag
					$linked_text = preg_replace(
						'/\*\*(.*?)\*\*/',									// The regex pattern to search for
						$feed_url_link,										// The replacement text
						$source_link_text									// The text to which to search in
					);
					// Prepare the replacement <a> tag with the placeholder for post_url
					$post_url_link = "<a target=\"_blank\" href=\"{{original_post_url}}\">$1</a>";
					// Replace the string in single asteriks into the <a> tag
					$linked_text = preg_replace(
						'/\*(.*?)\*/',										// The regex pattern to search for
						$post_url_link,										// The replacement text
						$linked_text										// The text to which to search in
					);

					
					// Update the post append text
					if ( strlen( $post_append ) > 0 ) {
						$post_append .= '<br/>';
					}
					$post_append .= $linked_text;
					update_post_meta( $ID, WPRSS_FTP_Meta::META_PREFIX . 'post_append', $post_append );

				}


			} // END OF SOURCLE LINK ENABLED CHECK

		} // END OF WHILE LOOP

		if ( $count > 0 ) {
			set_transient( 'wprss_ftp_admin_notices', array( 'WPRSS_FTP_Utils', 'source_link_update_notice' ), 0 );
		}

		// Restore the $post global to the current post in the main query
		wp_reset_postdata();

	} // END OF source_link_update() 


	/**
	 * Detects the namespaces used the in feed.
	 * 
	 * @since 2.8
	 */
	public static function get_namespaces_from_feed() {
		// Get the feed source from POST data
		$feed_source = ( isset($_POST['feed_source']) )? $_POST['feed_source'] : NULL;
		// If no feed source is given, or an empty feed source is given, print an error message
		if ( $feed_source === '' || $feed_source === NULL ) {
			die( 'Invalid feed source given.' );
		}

		// Read the feed source
		$feed = @file_get_contents( $feed_source );
		// Show an error 
		if ( $feed === FALSE ) {
			die( 'Failed to read feed source XML. Check that your URL is a valid feed source URL' );
		}

		try {
			// Parse the XML
			$xml = new SimpleXmlElement($feed);
			// Get the namespaces
			$namespaces = $xml->getNameSpaces(true);
			// Unset the standard RSS and XML namespaces
			unset( $namespaces[''] );
			unset( $namespaces['xml'] );
			// Print the remaining namespaces as an encoded JSON string
			die( json_encode( $namespaces ) );
		}
		catch( Exception $e ) {
			die( 'Failed to parse the RSS feed XML. The feed may contain errors or is not a valid feed source.' );
		}
	}
}


/* AJAX hook for taxonomy dropdown */
add_action( 'wp_ajax_ftp_get_object_taxonomies', array( 'WPRSS_FTP_Utils', 'generate_taxonomy_dropdown' ) );
add_action( 'wp_ajax_ftp_get_taxonomy_terms', array( 'WPRSS_FTP_Utils', 'generate_tax_terms_dropdown' ) );

/* AJAX hook for namespace auto detector */
add_action( 'wp_ajax_ftp_detect_namespaces', array( 'WPRSS_FTP_Utils', 'get_namespaces_from_feed' ) );
