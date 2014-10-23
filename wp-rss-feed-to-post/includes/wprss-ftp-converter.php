<?php
/**
 * This file contains functions relating to converting wprss_feed_item posts to
 * standard WP posts
 * 
 * @since 1.0
 */


/**
 * The Converter class. This class containts methods that
 * convert feed items to WordPress posts
 * 
 * @since 1.0
 */
final class WPRSS_FTP_Converter {

	public static function get_existing_permalinks() {
		global $wpdb;

		$existing_permalinks = $wpdb->get_col("
			SELECT meta_value
			FROM $wpdb->postmeta
			WHERE meta_key = 'wprss_item_permalink'
		");

		return $existing_permalinks;
	}


	/**
	 * Converts a single wprss_feed_item to a post.
	 * 
	 * @param feed 		The wprss_feed_item object to convert
	 * @param source 	The wprss_feed id of the feed item. Used to retrieve settings for conversion.
	 * @since 1.0
	 */
	public static function convert_to_post( $item, $source, $permalink ) {
		$error_source = 'convert_to_post';
		$source_obj = get_post( $source );

		// If the feed source does no exist, exit
		if ( $source_obj === null || $source === '' ) {
			// unschedule any scheduled updates
			wprss_feed_source_update_stop_schedule( $source );
			return NULL;
		} else {
			// If the feed source exists, but is trashed or paused, exit
			if ( $source_obj->post_status !== 'trash' && !wprss_is_feed_source_active( $source ) ) {
				return NULL;
			}
		}

		# If we got NULL, pass it on
		if ( $item === NULL ) {
			return NULL;
		}
		# If the item has an empty permalink, log an error message
		if ( empty( $permalink ) ){
			WPRSS_FTP_Utils::log( 'Encounted feed item with no permalink for feed source "' . $source_obj->post_title . '". Possibly a corrupt RSS feed.', $error_source );
		}

		# check existence of permalink
		$existing_permalinks = self::get_existing_permalinks( $source );

		# If permalink exists, do nothing
		if ( in_array( $permalink, $existing_permalinks ) ) return NULL;

		# Get the computed options ( global settings merged against individual settings )
		$options = WPRSS_FTP_Settings::get_instance()->get_computed_options( $source );

		if ( $options['post_type'] === 'wprss_feed_item' ) {
			return $item;
		}
		

		/*==============================================
		 * 1) DETERMINE THE POST AUTHOR USER
		 */

		// Get author-related options from meta, or from global settings, if not found
		$def_author = $options['def_author'];
		$fallback_author = $options['fallback_author'];
		$author_fallback_method = $options['author_fallback_method'];
		$fallback_user = get_user_by( 'login', $fallback_author )->ID;
		$no_author_found = $options['no_author_found'];

		// Determined user. Start with NULL
		$user = NULL;

		// If using an existing user, we are done.
		if ( $def_author !== '.' ) {
			$user = $def_author;
		}
		// If getting feed from author, determine the user to assign to the post
		else {
			/* Get the author from the feed
			 * If author not found - use fallback user
			 */
			if ( $author = $item->get_author() ) {
			    $has_author_name = $author->get_name() !== '' && is_string( $author->get_name() );
			    $has_author_email = $author->get_email() !== '' && is_string( $author->get_email() );
			}
			else {
			    $has_author_name = $has_author_email = false;
			}
			
			// Author NOT found
			if ( $author === NULL || !( $has_author_name || $has_author_email ) ) {
				// If option to use fallback when no author found, use fallback
				if ( $no_author_found === 'fallback' ) {
					$user = $fallback_user;
				}
				// Otherwise, skip the post
				else {
					return NULL;
				}
			}
			// Author found
			else {
				$author_name = $author->get_name();
				$author_email = $author->get_email();

				// No author name fix
				if ( !$has_author_name && $has_author_email ) {
					// "Email is actually the name"" fix
					if ( filter_var( $author_email, FILTER_VALIDATE_EMAIL ) === FALSE ) {
						// Set the name to the email, and reset the email
						$author_name = $author_email;
						$author_email = '';
						// Set the flags appropriately
						$has_author_name = TRUE;
						$has_author_email = FALSE;
					}
					else {
						$parts = explode("@", $author_email);
						$author_name = $parts[0];
						$has_author_name = TRUE;
					}
				}
				
				// No author email fix
				if ( !$has_author_email && $has_author_name ) {
					// Get rid of wwww and everything before it
					$domain_name =  preg_replace( '/^www\./', '', $_SERVER['SERVER_NAME'] );
					// Lowercase the author name, remove the spaces
					$email_username = str_replace( ' ', '', strtolower($author_name) );
					// For domains with no TLDN suffix (such as localhost)
					if ( stripos( $domain_name, '.' ) === FALSE ) $domain_name .= '.com';
					// Generate the email
					$author_email = "$email_username@$domain_name";
					$has_author_email = TRUE;
				}

				$user_obj = FALSE;

				// If email is available, check if a user with this email exists
				$user_obj = get_user_by( 'email', $author_email );
				// If search by email failed, search the email for the login
				if ( $user_obj === NULL ) {
					$user_obj = get_user_by( 'login', $author_email );
				}
				// If search by email failed, search by name
				if ( $user_obj === NULL ) {
					$user_obj = get_user_by( 'login', $author_name );
				}

				// Feed author has a user on site
				if ( $user_obj !== FALSE && isset( $user_obj->ID ) ) {
					$user = $user_obj->ID;
				}
				// Author has no user on site
				else {
					$new_user_id = NULL;

					// Fallback method: create user
					if ( $author_fallback_method === 'create' ) {
						$random_password = wp_generate_password( $length = 12, $include_standard_special_chars = false );
						$new_user_id = wp_create_user( $author_name, $random_password, $author_email );
					}

					// Fallback method: existing user
					// OR creating a user failed
					if ( $new_user_id === NULL ) {
						$new_user_id = $fallback_user;
					}

					$user = $new_user_id;
				}
			}
		}

		// Get WordPress' GMT offset in hours, and PHP's timezone
		$wp_tz = function_exists('wprss_get_timezone_string') ? wprss_get_timezone_string() : get_option( 'timezone_string ' );
		$php_tz = date_default_timezone_get();
		// Set Timezone to WordPress'
		date_default_timezone_set( $wp_tz );
		
		// Prepare the rest of the post data
		$date_timestamp = ( $options['post_date'] === 'original' ) ? $item->get_date( 'U' ) : date( 'U' );
		// If the time of the feed is in the future, trim it down to the present
		$date_timestamp = ( $date_timestamp > date( 'U' ) ) ? date( 'U' ) : $date_timestamp;
		// Prepare post dates
		$post_date		= date( 'Y-m-d H:i:s', $date_timestamp );
		$post_date_gmt	= gmdate( 'Y-m-d H:i:s', $date_timestamp );

		// Reset Timezone to PHP's
		date_default_timezone_set( $php_tz );
		
		// Prepare the post tags
		$tags_str = WPRSS_FTP_Meta::get_instance()->get_meta( $source, 'post_tags' );
		$tags = array_map( 'trim', explode( ',', $tags_str ) );


		/*==============================================
		 * 2) APPLY FILTERS TO POST FIELDS
		 */

		$post_title		= apply_filters( 'wprss_ftp_converter_post_title',		$item->get_title(), $source );
		$post_content	= apply_filters( 'wprss_ftp_converter_post_content',	$item->get_content(), $source );
		$post_status 	= apply_filters( 'wprss_ftp_converter_post_status',		$options['post_status'], $source );
		$post_comments 	= apply_filters( 'wprss_ftp_converter_post_comments',	$options['comment_status'], $source );
		$post_type 		= apply_filters( 'wprss_ftp_converter_post_type',		$options['post_type'], $source );
		$post_format 	= apply_filters( 'wprss_ftp_converter_post_format',		$options['post_format'], $source );
		$post_terms 	= apply_filters( 'wprss_ftp_converter_post_terms',		$options['post_terms'], $source );
		$post_taxonomy 	= apply_filters( 'wprss_ftp_converter_post_taxonomy',	$options['post_taxonomy'], $source );
		$permalink 		= apply_filters( 'wprss_ftp_converter_permalink',		$permalink, $source );
		$post_author 	= apply_filters( 'wprss_ftp_converter_post_author',		$user, $source );
		$post_date		= apply_filters( 'wprss_ftp_converter_post_date',		$post_date, $source );
		$post_date_gmt	= apply_filters( 'wprss_ftp_converter_post_date_gmt',	$post_date_gmt, $source );
		$post_tags		= apply_filters( 'wprss_ftp_converter_post_tags',		$tags, $source );
		$post_language 	= apply_filters( 'wprss_ftp_converter_post_language',	$options['post_language'], $source );
		$post_site		= apply_filters( 'wprss_ftp_converter_post_site',		$options['post_site'], $source );

		# Apply WordPress filters
		# @todo Add more...
		$post_title		= apply_filters( 'the_title',	$post_title, NULL );
		$post_content	= apply_filters( 'the_content',	$post_content );

		$post_comments = ( WPRSS_FTP_Utils::multiboolean( $post_comments ) === TRUE )? 'open' : 'close';


		/*==============================================
		 * 3) CREATE THE POST
		 */
		$post = array(
			'post_title'		=>	$post_title,
			'post_content'		=>	$post_content,
			'post_date'			=>	$post_date,
			'post_date_gmt'		=>	$post_date_gmt,
			'post_status'		=>	$post_status,
			'post_type'			=>	$post_type,
			'post_author'		=>	$post_author,
			'tags_input'		=>	implode( ', ' , $post_tags ),
			'comment_status'	=>	$post_comments
		);

		/**
		 * Filter the post args.
		 * @var array $post		Array containing the post fields
		 * @var WP_Post $source		An post that represents the feed source
		 * @var SimplePie_Item $item    The feed item currently being processed
		 */
		$post = apply_filters( 'wprss_ftp_post_args', $post, $source, $item );
		

		/*==============================================
		 * 4) INSERT THE POST
		 */
		if ( defined('ICL_SITEPRESS_VERSION') )
			@include_once( WP_PLUGIN_DIR . '/sitepress-multilingual-cms/inc/wpml-api.php' );
		if ( defined('ICL_LANGUAGE_CODE') )
			$_POST['icl_post_language'] = $language_code = ICL_LANGUAGE_CODE;


		// check for multisite option - and switch blogs if necessaray
		$switch_success = FALSE;
		if ( WPRSS_FTP_Utils::is_multisite() && $post_site !== '' ) {
			global $switched;
   			$switch_success = switch_to_blog( $post_site );
		}

		// Check if embedded content is allowed
		$allow_embedded_content = WPRSS_FTP_Meta::get_instance()->get_meta( $source, 'allow_embedded_content' );

		// If embedded content is allowed, remove KSES filtering
		if ( WPRSS_FTP_Utils::multiboolean( $allow_embedded_content ) === TRUE ) {
			kses_remove_filters();
		}

		// Insert the post
		$inserted_id = wp_insert_post( $post );

		// If embedded content is allowed, re-add KSES filtering
		if ( WPRSS_FTP_Utils::multiboolean( $allow_embedded_content ) === TRUE ) {
			kses_init_filters();
		}

		if ( !is_wp_error( $inserted_id ) ) {

			if ( is_object( $inserted_id ) ) {
				if ( isset( $inserted_id['ID'] ) ) {
					$inserted_id = $inserted_id['ID'];
				}
				elseif ( isset( $inserted_id->ID ) ) {
					$inserted_id = $inserted_id->ID;
				}
			}

			if ( $user === NULL ) {
				WPRSS_FTP_Utils::log( 'Failed to determine a user for post #$inserted_id', $error_source );
			}

			// Update the post format
			set_post_format( $inserted_id, $post_format );

			if ( function_exists( 'wpml_update_translatable_content' ) ) {
				if ( $post_language === '' || $post_language === NULL ) {
					$post_language = ICL_LANGUAGE_CODE;
				}
				// Might be needed by WPML?
				$_POST['icl_post_language '] = $post_language;
				// Update the translation for the created post
				wpml_add_translatable_content( 'post_' . $post_type, $inserted_id, $post_language );
				wpml_update_translatable_content( 'post_' . $post_type, $inserted_id, $post_language );
				icl_cache_clear($post_type.'s_per_language');
			}


			/*==============================================
			 * 5) ADD THE POST META DATA
			 */
			$thumbnail = '';
			$enclosure_image = '';
			if ( $enclosure = $item->get_enclosure() ) {
				$thumbnail = $enclosure->get_thumbnail();
				$enclosure_image = $enclosure->get_link();
			}

			// Prepare the post meta, and pass though the wprss_ftp_post_meta filter.
			// Note: Prepend '!' to ignore the 'wprss_ftp_' prefix
			$post_meta_data = apply_filters(
				'wprss_ftp_post_meta',
				array(
					'!wprss_item_permalink'		=>	$permalink,
					'feed_source'				=>	$source,
					'media:thumbnail'			=>	$thumbnail,
					'enclosure:thumbnail'		=>	$enclosure_image,
					'enclosure_link'			=>	$enclosure_image, // Included twice for code readablity
					'import_date'				=>	time(),
					'!wprss_item_date'			=>	$date_timestamp, // Required by core
				),
				$inserted_id,
				$source
			);

			// Insert the post meta
			WPRSS_FTP_Meta::get_instance()->add_meta( $inserted_id, $post_meta_data );



			/*==============================================
			 * 6) ADD THE TAXONOMY TERMS
			 */
			
			$all_post_terms = ( !is_array( $post_terms ) )? array() : $post_terms;

			// Check if the source auto creates taxonomy terms
			$auto_create_terms = WPRSS_FTP_Meta::get_instance()->get_meta( $source, 'post_auto_tax_terms' );
			// If yes ...
			if ( WPRSS_FTP_Utils::multiboolean( $auto_create_terms ) === TRUE ) {
				// Get the feed categories
				$categories = $item->get_categories();
				
				if ( is_array( $categories ) && count( $categories ) > 0 ) {
					// For each category in the feed item

					// Turn the categories into an array
					$new_categories = array();
					foreach( $categories as $cat ) {
						$new_categories[] = array(
							'name'	=>	$cat->get_label(),
							'args'	=>	array(),
						);
					}

					// Filter the categories
					$categories = apply_filters( 'wprss_auto_create_terms', $new_categories, $post_taxonomy, $source );

					foreach ( $categories as $category_obj ) {
						$category = $category_obj['name'];
						// Find the term that matches that category
						$cat_term = term_exists( $category , $post_taxonomy );

						// If the term does not exist create it
						if ( $cat_term === 0 || $cat_term === NULL ) {

							// check if parent field exists, and turn the slug into an id
							if ( isset( $category_obj['args']['parent'] ) ) {
								// Get the slug, and find the term by the slug
								$parent_slug = $category_obj['args']['parent'];
								$parent_term = get_term_by( 'slug', $parent_slug, $post_taxonomy, 'ARRAY_A' );
								// If term not found, removed the parent arg
								if ( $parent_term === FALSE ) {
									unset( $category_obj['args']['parent'] );
								}
								// Otherwise, change the slug to the id
								else $category_obj['args']['parent'] = intval( $parent_term['term_id'] );
							}

							// Insert the term
							$cat_term = wp_insert_term( $category, $post_taxonomy, $category_obj['args'] );
							delete_option($post_taxonomy."_children"); // clear the cache
						}
						$term_id = $cat_term['term_id'];

						$term_obj = get_term_by( 'id', $term_id, $post_taxonomy, 'ARRAY_A' );

						if ( $term_obj !== FALSE && $term_obj !== NULL ) {

							if ( !is_array($all_post_terms) ) {
								WPRSS_FTP_Utils::log_object( 'The $all_post_terms variable is not an array:', $all_post_terms, $error_source );
							} else {
								// Add it to the list of terms to add
								$all_post_terms[] = $term_obj['slug'];
							}

						}
					}
				}
			}

			$wp_categories_return = wp_set_object_terms( $inserted_id, $all_post_terms, $post_taxonomy, FALSE );
			if ( !is_array( $wp_categories_return ) ) {
				WPRSS_FTP_Utils::log_object( "Possible error while inserting taxonomy terms for post #$inserted_id:", $all_post_terms );
			}


			/*==============================================
			 * 8) CUSTOM FIELD MAPPING
			 */

			// Get the namespaces
			$cfm_namespaces = WPRSS_FTP_Meta::get_instance()->get_meta( $source, 'rss_namespaces' );
			$cfm_namespaces = ( $cfm_namespaces === '' )? array() : $cfm_namespaces;
			// Get the tags
			$cfm_tags = WPRSS_FTP_Meta::get_instance()->get_meta( $source, 'rss_tags' );
			$cfm_tags = ( $cfm_tags === '' )? array() : $cfm_tags;
			// Get the custom fields
			$cfm_fields = WPRSS_FTP_Meta::get_instance()->get_meta( $source, 'custom_fields' );
			$cfm_fields = ( $cfm_fields === '' )? array() : $cfm_fields;

			// For each custom field mapping
			for ( $i = 0; $i < count( $cfm_namespaces ); $i++ ) {
				// Get the URL of the namespace
				$namespace_url = WPRSS_FTP_Settings::get_namespace_url( $cfm_namespaces[$i] );
				// If the namespace url is NULL (namespace no longer exists in the settings), skip to next mapping
				if ( $namespace_url === NULL ) continue;

				// Match the syntax "tagname[attrib]" in the tag name
				preg_match('/([^\[]+) (\[ ([^\]]+) \])?/x', $cfm_tags[$i], $m);
				// If no matches, stop. Tag name is not correct. Possibly empty
				if ( !is_array($m) || count($m) < 2 ) continue;
				// Get the tag and attribute from the matches
				$tag_name = $m[1];
				$attrib = ( isset( $m[3] ) )? $m[3] : NULL;

				// Get the tag from the feed item
				$item_tags = $item->get_item_tags( $namespace_url, $tag_name );
				// Check if the tag exists. If not, skip to next mapping
				if ( !isset( $item_tags[0] ) ) continue;

				// Get the first tag found, and get its data contents
				$item_tag = $item_tags[0];
				$attribs = $item_tag['attribs'][''];
				// If not using an attribute, simply get the text data
				if ( $attrib === NULL ) {
					$data = $item_tag['data'];
				}
				// Otherwise, check if the attribute exists
				elseif ( isset( $attribs[ $attrib ] ) ) {
					$data = $attribs[ $attrib ];
				}
				// Otherwise do nothing
				else {
					continue;
				}

				// Put the data in the inserted post's meta, using the custom field as the meta key
				update_post_meta( $inserted_id, $cfm_fields[$i], $data );
			}

			$post = get_post( $inserted_id );
			if ( $post === NULL || $post === FALSE ) {
				$title = $item->get_title();
				WPRSS_FTP_Utils::log("An error occurred while converting a feed item into a post \"$title\". Kindly report this error to support@wprssaggregator.com");
			}
			else do_action( 'wprss_ftp_converter_inserted_post', $inserted_id, $source );
		}
		else {
			WPRSS_FTP_Utils::log( 'Failed to insert post. $inserted_id = ' . $inserted_id, $error_source );
		}

		// If multisite and blog was switched, switch back to current blog
		if ( WPRSS_FTP_Utils::is_multisite() && $switch_success === TRUE ) {
			restore_current_blog();
		}

		return TRUE;
	}


	/**
	 * Checks if the feed source uses the force full content option or meta option, and
	 * returns the fulltextrss url if so.
	 * 
	 * @since 1.0
	 */
	public static function check_force_full_content( $feed_url, $feed_ID ) {
		if ( wprss_ftp_using_feed_items( $feed_ID ) ) {
			return $feed_url;
		}
		// Get the computed settings / meta options for the feed source
		$options = WPRSS_FTP_Settings::get_instance()->get_computed_options( $feed_ID );

		// If using force full content option / meta
		if ( WPRSS_FTP_Utils::multiboolean( $options['force_full_content'] ) === TRUE ) {
			
			$service = WPRSS_FTP_Settings::get_instance()->get('full_text_rss_service');
			switch( $service ) {
				case 'free':
					$key = WPRSS_FTP_FULL_TEXT_RSS_KEY;
					$API_HASH = sha1( $key . $feed_url );
					// Remove http:// and www from url ( fulltextrss uses the url without these to provide the feed with full content )
					$stripped_url = urlencode( preg_replace( '#^http(s)?://#', '', $feed_url ) );
					// Prepare the fulltext sources
					$full_text_sources = apply_filters(
						'wprss_ftp_full_text_sources',
						array(
							"http://fulltext.wprssaggregator.com/makefulltextfeed.php?key=1&hash=$API_HASH&links=preserve&exc=1&url=",
							"http://ftr-premium.fivefilters.org/makefulltextfeed.php?key=1920&hash=$API_HASH&max=10&links=preserve&exc=1&url=",
							//"http://fulltext-fallback.wprssaggregator.com/makefulltextfeed.php?key=1&hash=$API_HASH&links=preserve&exc=1&url=",
						)
					);
					// Start with no feed to use
					$feed_url_to_use = NULL;

					// Load Simple Pie
					require_once ( ABSPATH . WPINC . '/class-feed.php' );

					// For each source ...
					foreach ( $full_text_sources as $full_text_source ) {
						// Prepare the feed
						$full_text_feed_url = $full_text_source . $stripped_url;
						$feed = wprss_fetch_feed( $full_text_feed_url, $feed_ID );

						// If the feed has no errors, the we will use this feed
						if ( !is_wp_error( $feed ) && !$feed->error() ) {
							$feed_url_to_use = $full_text_source . $stripped_url;
							break;
						}
					}
					
					// If after trying all the sources, the feed to use is still NULL, then no source was valid.
					// Return the same url passed as parameter, Otherwise, return the full text rss feed url
					if ( $feed_url_to_use === NULL ) {
						WPRSS_FTP_Utils::log( 'Failed to find a working full text rss service.', 'check_force_full_content' );
					}
					return ( $feed_url_to_use === NULL )? $feed_url : $feed_url_to_use;

				case 'feeds_api':
					$api_key = WPRSS_FTP_Settings::get_instance()->get('feeds_api_key');
					$encoded_url = urlencode( $feed_url );

					$feeds_api_feed_url = WPRSS_FTP_Utils::template(
						WPRSS_FTP_FEEDS_API_REQUEST_FORMAT,
						array(
							'url'	=>	$encoded_url,
							'key'	=>	$api_key,
						)
					);
					// Attempt to fetch the feed
					$feed = wprss_fetch_feed( $feeds_api_feed_url, $feed_ID );

					// If an error was encountered
					if (  is_wp_error( $feed ) || $feed->error() ) {
						// Request the error message and log it
						$response = wp_remote_get( $feeds_api_feed_url );
						WPRSS_FTP_Utils::log( "FeedsAPI failed to return a feed, and responded with: \"{$response['body']}\"" );
						// Return the original parameter url
						return $feed_url;
					}

					// Return the feeds api if no error was encountered.
					return $feeds_api_feed_url;

				// For other services
				default:
					return apply_filters( 'wprss_ftp_misc_full_text_url', $feed_url, $feed_ID, $service );
			}
		}
		// Otherwise, return back the given url
		else return $feed_url;
	}


	/**
	 * Checks the post_word_limit setting and trims the post content accordingly.
	 * 
	 * @deprecated
	 * @since 1.8
	 * @todo implement trimming
	 */
	public static function trim_post_content( $post_content ) {
		// Get the option
		$post_word_limit = WPRSS_FTP_Settings::get_instance()->get( 'post_word_limit' );

		// Check if the option is empty or is not a valid integer, in which case we return
		// the post content without modifications
		if ( empty( $post_word_limit ) || intval( $post_word_limit ) === FALSE ) {
			return $post_content;
		}

		// Otherwise, get the integer value of the setting, and prepare to trim
		$post_word_limit = intval( $post_word_limit );

		// Get the excerpt more suffix from WordPress
		$excerpt_more = apply_filters( 'excerpt_more', ' ' . '[&hellip;]' );

		// Return the trimmed version
		$trimmed = wp_trim_words( $post_content, $post_word_limit, $excerpt_more );

		return $trimmed;
	}


	/**
	 * Changes the tags to be stripped from the feed item.
	 * 
	 * @since 
	 */
	public static function feed_tags_to_strip( $tags, $source ) {

		if ( $source !== NULL ) {			

			$allow_embedded_content = WPRSS_FTP_Meta::get_instance()->get_meta( $source, 'allow_embedded_content' );

			if ( WPRSS_FTP_Utils::multiboolean( $allow_embedded_content ) === TRUE ) {
				// Remove the following from the list
				unset( $tags[ array_search('object', $tags) ] );
				unset( $tags[ array_search('param', $tags) ] );
				unset( $tags[ array_search('embed', $tags) ] );
				unset( $tags[ array_search('iframe', $tags) ] );
				$tags = array_values( $tags );
			}
		}

		return $tags;
	}


	/**
	 * Returns the post word limit setting. Used by the trim_post_content() function in
	 * the WPRSS_FTP_Converter class as a filter for 'excerpt_length'
	 * 
	 * @deprecated Check why it's no longer used, and if it is important
	 * @since 1.8
	 */
	public static function get_post_word_limit( $length ) {
		return WPRSS_FTP_Settings::get_instance()->get( 'post_word_limit' );
	}
}
