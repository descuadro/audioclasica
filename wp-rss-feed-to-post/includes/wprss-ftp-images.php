<?php
/**
 * The WPRSS_FTP_Images class handles the caching of images retrieved from converted posts,
 * creating featured images for such posts, and adding the images to the media library.
 * 
 * @since 1.0
 */
final class WPRSS_FTP_Images {

	/**
	 * The singleton class instance
	 */
	private static $instance = NULL;


	/**
	 * The class constructor
	 *
	 * @since 1.0
	 */
	public function __construct() {
		if ( self::$instance === NULL ) {
			// Initialize
			add_action( 'wprss_ftp_converter_inserted_post', array( $this, 'save_images_locally_from_post' ), 10, 2 );
			add_action( 'wprss_ftp_saved_images_from_post', array( $this, 'determine_featured_image_for_post' ), 10, 3 );
		} else {
			wp_die( "WPRSS_FTP_Images class is a singleton class and cannot be redeclared." );
		}
	}


	/**
	 * Returns the singleton instance of the class
	 * 
	 * @since 1.0
	 */
	public static function get_instance() {
		if ( self::$instance === NULL ) {
			self::$instance = new WPRSS_FTP_Images();
		}
		return self::$instance;
	}

	
	/**
	 * Searches the post content for images.
	 * 
	 * @param post_id 	The ID of the converted post
	 * @param source 	The ID of the feed source
	 * @since 1.0
	 */
	public function save_images_locally_from_post( $post_ID, $source ) {
		// Get the post form the ID
		$post = get_post( $post_ID );
		// If the post is null, return null.
		if ( $post === NULL ) {
			WPRSS_FTP_Utils::log( 'Received incorrect or NULL post ID.', 'save_images_locally_from_post' );
			return NULL;
		}

		// Get the computed settings
		$options = WPRSS_FTP_Settings::get_instance()->get_computed_options( $source );


		//== SAVE IMAGES LOCALLY ===

		// Get the post content
		$content = $post->post_content;

		// Match all <img> tag src attributes ( the image source url )
		preg_match_all('|<img.*?src=[\'"](.*?)[\'"].*?>|i', $content, $matches);
		// Include the file and media libraries
		require_once( ABSPATH . 'wp-admin/includes/media.php' );
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/image.php' );

		/**
		 * DEVELOPER NOTE:
		 * 
		 * The below loop will iterate through all found image tags, and generate an associative array
		 * with the original image URLs as keys and the new local image URLs as values.
		 * 
		 * This array will be used as a map to find/replace the URLs, to link the images in the post content
		 * to the newly downloaded images.
		 */

		// Prepare the images array
		$images = array();

		// For each matching image tag in the post content
		foreach ( $matches[1] as $url ) {
			// Save the url found as the key to use the the $images array
			$key = $url;
			// Only proceed if the url is from an external source
			if ( ! wprss_ftp_is_url_local( $url ) ) {

				// Attempt to get a larger version, if image is from facebook
				$url = $this->attempt_to_get_large_fb_image( $url );

				// If the option to save images is enabled, download it
				if ( WPRSS_FTP_Utils::multiboolean( $options['save_images_locally'] ) === TRUE ) {
					// Use a large time limit, to accomodate for big file sizes
					set_time_limit( 600 );
					// Download it
					$img = wprss_ftp_media_sideload_image( $url, $post_ID );
					// If the download was successful
					if ( !is_wp_error( $img ) ) {
						// Set the new image to be the url of the downloaded attachment
						$images[$key] = wp_get_attachment_url( $img );
					}
					// If an error occured while downloading the file,
					// keep the image external
					else $images[$key] = $url;
				}
				// If not saving images locally, keep the image external
				else $images[$key] = $url;

			} // End of external image check
			// For local images
			else {
				$images[$key] = $url;
			}
		} // end of loop for all image tag urls found in the content

		// Now we have the images array built
		// We extract the original image URLs from the keys and the new image URLs from the values
		$old = array_keys( $images );
		$new = array_values( $images );

		// Check if the filter to strip images from post content returns TRUE
		if ( apply_filters( 'wprss_ftp_strip_images_from_post', FALSE ) === TRUE ) {
			// Replace all image tags with an empty string
			$content = preg_replace( "/<img[^>]+\>/i", '', $content ); 
			// Update the post content
			WPRSS_FTP_Utils::update_post_content( $post_ID, $content );
		}
		// Otherwise, if the option to save images is enabled, replace the old image urls with the new urls of the locally downloaded images
		elseif ( WPRSS_FTP_Utils::multiboolean( $options['save_images_locally'] ) === TRUE ) {
			// Now we have an associative array of old urls pointing to the new ones.
			// We perform a find and replace to use the new urls for the images
			$content = str_replace( $old, $new, $content );

			// Update the post content
			WPRSS_FTP_Utils::update_post_content( $post_ID, $content );
		}

		do_action( 'wprss_ftp_saved_images_from_post', $post_ID, $source, $new );

	}


	/**
	 * Determines the featured image for the imported post.
	 * 
	 * @since 2.7.4
	 */
	public function determine_featured_image_for_post( $post_ID, $source, $images ) {

		// Get the post form the ID
		$post = get_post( $post_ID );
		// If the post is null, return null.
		if ( $post === NULL ) {
			wprss_log( 'Received incorrect or NULL post ID.' );
			return NULL;
		}

		// Get the post content
		$content = $post->post_content;

		// Get the computed settings for the feed source
		$options = WPRSS_FTP_Settings::get_instance()->get_computed_options( $source );

		// If the featured image option is disabled, do NOT continue.
		if ( WPRSS_FTP_Utils::multiboolean( $options['use_featured_image'] ) === FALSE ) {
			return;
		}

		// Start by trimming whitespace from image URLs
		$images = array_map( 'trim', $images );

		// The URL of the determined featured image
		$featured_image_url = NULL;

		// Get the minimum image size settings
		$min_width = $options['image_min_width'];
		$min_height = $options['image_min_height'];

		// DETERMINED FEATURED IMAGE
		$featured_image = NULL;
		// WHETHER OR NOT USING THE FALLBACK IMAGE (used to skip most of the image processing in the function)
		$using_fallback = FALSE;

		// Check which featured image option is being used
		switch ( $options['featured_image'] ) {
			default:

			// FIRST/LAST image in post content
			case 'first':
			case 'last':
				
				// If using the Last Image option, reverse the images array
				if ( $options['featured_image'] === 'last' ) {
					$images = array_reverse( $images );
				}

				// Iterate through all the images
				for( $i = 0; $i < count( $images ); $i++ ) {
					// The the image URL is empty, or it does not obey the minimum size constraint, jump to next image
					if ( empty( $images[$i] ) || !$this->image_obeys_minimum_size( $images[$i], $min_width, $min_height ) ) continue;

					// Attempt to use this iamge as featured imafe
					$ft_image_found = $images[$i];
					$featured_image = $images[$i];

					// Check if the image URL is local
					if ( !wprss_ftp_is_url_local( $featured_image ) ) {
						// If not, download it and attach it to the post
						$featured_image = wprss_ftp_media_sideload_image( $featured_image, $post_ID, TRUE );
					}
					// If it is local, simply attach it to the post
					else {
						self::set_featured_image( $post_ID, $featured_image, TRUE );
					}

					// If no error was encountered, exit the loop
					// If an error was encountered, the next image will be tested.
					if ( !is_wp_error( $featured_image ) ){
						break;
					}
				}

				// Indicate that NO image was used as featured image
				if ( is_wp_error( $featured_image ) ) {
					$featured_image = NULL;
				}

				break; // END OF FIRST / LAST IMAGE CASE


			// FEED <MEDIA:THUMBNAIL> IMAGE / <ENCLOSURE> TAG
			case 'thumb':
			case 'enclosure':

				// Prepare the tag in which to look for the image
				$tag = ( $options['featured_image'] == 'thumb' )? 'media:thumbnail' : 'enclosure:thumbnail';
				// Get the media thumbnail from post meta ( converter class places the tag contents in post meta )
				$thumbnail = trim( WPRSS_FTP_Meta::get_instance()->get_meta( $post_ID, $tag, TRUE ) );

				// Check if the thumbnail is large enough to accept
				if ( $this->image_obeys_minimum_size( $thumbnail, $min_width, $min_height ) ) {
					// Download this image, attach it to the post and use it as the featured image
					$featured_image = wprss_ftp_media_sideload_image( $thumbnail, $post_ID, TRUE );
					// If an error was encountered, set the featured image to NULL
					if ( is_wp_error( $featured_image ) ) {
						$featured_image = NULL;
					}
				}

				break; // END OF MEDIA:THUMBNAIL / ENCLOSURE CASE


			// FALLBACK FEATURED IMAGE
			case 'fallback':

				// Get the fallback featured image
				$fallback_image = get_post_thumbnail_id( $source );

				// Check if the fallback featured image is set
				if ( !empty( $fallback_image ) ) {
					// If it is set, use it as featured image for the imported post
					self::set_featured_image( $post_ID, $fallback_image );
					// Indicate that the fallback was used
					$using_fallback = TRUE;
				}
				break;

		} // End of switch


		// If the fallback image was used, then we are done. Exit function
		if ( $using_fallback ) return;


		// If a featured image was determined
		if ( $featured_image !== NULL && !is_wp_error( $featured_image ) ) {
			// Check for filter to remove featured image from post
			$remove_ft_image = apply_filters( 'wprss_ftp_remove_ft_image_from_content', FALSE );
			// We remove the ft image, if the filter returns TRUE, or if it returns an array and the post source is in the array.
			$remove = $remove_ft_image === TRUE || ( is_array( $remove_ft_image ) && in_array( $source, $remove_ft_image ) );

			// If removing and the ft image is in the content (not media:thumbnail)
			// (Determined either by legacy filter or meta option)
			if ( $remove || WPRSS_FTP_Utils::multiboolean( $options['remove_ft_image'] ) === TRUE ) {
				$img_to_remove = $featured_image;
				if ( $options['featured_image'] === 'first' || $options['featured_image'] === 'last' ) {
					$img_to_remove = $ft_image_found;
				}
				// Prepare the img tag regex
				$tag_search = '<img.*?src=[\'"]' . preg_quote( esc_attr( $img_to_remove ) ) . '[\'"].*?>';
				// Replace the tag with an empty string, and get the new content
				$new_content = preg_replace( "|" . $tag_search ."|i", '', $content, 1 );
				// Update the post content
				WPRSS_FTP_Utils::update_post_content( $post_ID, $new_content );
			}
		}

		// However,
		// If NO featued image was determined
		else {
			$featured_image = NULL;

			// Get the user filter for using the feed image
			$user_filter = apply_filters( 'wprss_ftp_feed_image_fallback', FALSE, $post_ID, $source, $images );
			$user_filter_enabled = $user_filter === TRUE || ( is_array( $user_filter ) && in_array( $source, $user_filter ) );

			// Check if the core supports getting the feed image and if the user filter is enabled
			if ( function_exists( 'wprss_get_feed_image' ) && $user_filter_enabled ) {
				// Get the url of the feed image
				$feed_image = wprss_get_feed_image( $source );

				// Attempt to download it and attach it to the post
				$featured_image = wprss_ftp_media_sideload_image( $feed_image, $post_ID, TRUE );

				// If an error was encountered, indicate it by setting the featured image to NULL
				if ( is_wp_error( $featured_image ) || $featured_image === NULL ) {
					$featured_image = NULL;
				}
			}

			// If the feed image did not work, resort to using the fallback, if set
			if ( $featured_image == NULL ) {
				// Get the fallback image
				$fallback_image = get_post_thumbnail_id( $source );
				// If it is set, use it as the featured image for the post
				if ( !empty( $fallback_image ) ) {
					self::set_featured_image( $post_ID, $fallback_image );
				}
			}
		}
		do_action( 'wprss_ftp_determined_featured_image', $post_ID, $source );
	}


	/**
	 * Sets the image as a featured image to the post.
	 * 
	 * @since 1.9.2
	 *
	 * @param int $post_id The ID of the post
	 * @param int $image the ID of the image
	 */
	public static function set_featured_image( $post_id, $image, $is_url = FALSE ) {
		$thumbnail = ( $is_url )? self::get_attachment_id_from_url( $image ) : $image;
		set_post_thumbnail( $post_id, $thumbnail );

		$url = ( $is_url )? $image : wp_get_attachment_url( $image );

		// Check the featured image meta filter
		$featured_image_meta = apply_filters( 'wprss_ftp_featured_image_meta', FALSE );
		// check if it is false. If not, use the meta key to insert the image
		if ( $featured_image_meta !== FALSE && $featured_image_meta !== '' ) {
			update_post_meta( $post_id, $featured_image_meta, $url );
		}

		// Check the featured image meta ID filter
		$featured_image_meta_id = apply_filters( 'wprss_ftp_featured_image_meta_id', FALSE );
		// check if it is false. If not, use the meta key to insert the image ID
		if ( $featured_image_meta_id !== FALSE && $featured_image_meta_id !== '' ) {
			update_post_meta( $post_id, $featured_image_meta_id, $post_id );
		}
	}


	/**
	 * Returns the attachment ID of the image with the given source
	 * 
	 * @since 1.0
	 */
	public static function get_attachment_id_from_url( $image_src ) {
		global $wpdb;
		$query = "SELECT ID FROM {$wpdb->posts} WHERE guid='$image_src'";
		$id = $wpdb->get_var($query);
		return $id;
	}


	/**
	 * FACEBOOK IMAGE URL FIX.
	 * Attempts to return a larger image then the one included from Facebook.
	 *
	 * @since 1.8.2
	 */
	public function attempt_to_get_large_fb_image( $url ) {
		// Check if it is a facebook image url and if a larger size exists
		if ( stripos( $url,"fbcdn" ) > 0 ){
			$ext = strrchr( $url, "." );
			$fb_larger_img = str_replace( '_s' . $ext, '_n' . $ext , $url );
			$fb_even_larger_img = preg_replace( "/[s|l|m|o][\d]+x[\d]+\//i", "", $fb_larger_img );
			$fb_img = ( $fb_even_larger_img === NULL || is_array( $fb_even_larger_img ) )? $fb_larger_img : $fb_even_larger_img;
			// If the larger image exists, set the url to point to it
			if ( WPRSS_FTP_Utils::remote_file_exists( $fb_img ) ){
				return $fb_img;
			} else {
				return $url;
			}

		} else {
			return $url;
		}
	}


	/**
	 * Checks if the given image obeys the given minimum size contraints.
	 *
	 * @since 1.8.2
	 */
	public function image_obeys_minimum_size( $img, $min_width, $min_height ) {
		// Check for filter to skip the size checking
		$skip_size_check = apply_filters( 'wprss_ftp_skip_image_size_check', FALSE );
		if ( $skip_size_check === TRUE ) {
			return TRUE;
		}

		$img = trim( $img );
		$img = str_replace( ' ', '%20', $img );
		
		$size = @getimagesize( $img );
		if ( $size !== FALSE ) {
			list( $width, $height ) = $size;
		} else {
			WPRSS_FTP_Utils::log_object( 'Failed to get image dimensions. Image may not exist at ' . $img . ". Got: ", $size , 'image_obeys_minimum_size' );
			return FALSE;
		}

		$obeysMinimum = TRUE;
		if ( $min_width !== '' )
			$obeysMinimum = ( $obeysMinimum && ( $width >= $min_width ) );
		if ( $min_height !== '' )
			$obeysMinimum = ( $obeysMinimum && ( $height >= $min_height ) );

		return $obeysMinimum;
	}
}


add_filter( 'wprss_ftp_feed_image_fallback', 'wprss_ftp_feed_image_fallback', 10, 4 );
/**
 * Sets the fallback to feed image option to true.
 * 
 * @param bool $fallback Purpose of this argument is unknown.
 * @param int $post_ID ID of the post, for which to get the setting
 * @param int|WP_Post The source or source ID of the subject post
 * @param array $images Numeric array of URLs for images, that are candidates to become the subject posts's thumbnail
 * @since 1.3.1
 */
function wprss_ftp_feed_image_fallback( $fallback, $post_ID, $source, $images ) {
	$options = WPRSS_FTP_Settings::get_instance()->get_computed_options($source);
	return WPRSS_FTP_Utils::multiboolean(isset($options['fallback_to_feed_image']) ? $options['fallback_to_feed_image'] : null);
}


/**
 * Checks if the wprss_media_sideload_image function exists ( in the core ) and runs it.
 * Otherwise, the WP media_sideload_image function is used.
 * 
 * @since 1.4.1
 */
function wprss_ftp_media_sideload_image_deprecated( $url, $post_id ) {
	if ( function_exists( 'wprss_media_sideload_image' ) ) {
		return wprss_media_sideload_image( urldecode( $url ), $post_id );
	} else {
		return media_sideload_image( $url, $post_id );
	}
}


/**
 * Checks if the given url is a local or external one
 * 
 * @since 1.8.2
 */
function wprss_ftp_is_url_local( $url ) {
	// Get the site's url
	$siteurl = parse_url( get_option( 'siteurl' ) );
	// Parse the URL to get the URL host
	$parsedURL = WPRSS_FTP_Utils::encode_and_parse_url( $url );
	// Return true if the url host is set, and it is equal to the site's host.
	// Return false if either the url host is not set, or it is not equal to the site's host.
	return ( isset( $parsedURL['host'] ) && $parsedURL['host'] == $siteurl['host'] );
}


/**
 * Download an image from the specified URL and attach it to a post.
 * Modified version of core function media_sideload_image() in /wp-admin/includes/media.php  (which returns an html img tag instead of attachment ID)
 * Additional functionality: ability override actual filename, and to pass $post_data to override values in wp_insert_attachment (original only allowed $desc)
 *
 * Credits to somatic
 * http://wordpress.stackexchange.com/questions/30284/media-sideload-image-file-name/44115#44115
 *
 * @since 2.7.4
 *
 * @param string $url (required) The URL of the image to download
 * @param int $post_id (required) The post ID the media is to be associated with
 * @param bool $thumb (optional) Whether to make this attachment the Featured Image for the post (post_thumbnail)
 * @param string $filename (optional) Replacement filename for the URL filename (do not include extension)
 * @param array $post_data (optional) Array of key => values for wp_posts table (ex: 'post_title' => 'foobar', 'post_status' => 'draft')
 * @return int|object The ID of the attachment or a WP_Error on failure
 */
function wprss_ftp_media_sideload_image( $url = null, $post_id = null, $thumb = null, $filename = null, $post_data = array() ) {
	if ( !$url || !$post_id ) {
		return new WP_Error('missing', "Need a valid URL and post ID...");
	}

	require_once( ABSPATH . 'wp-admin/includes/file.php' );

	// Download file to temp location, returns full server path to temp file, ex; /home/user/public_html/mysite/wp-content/26192277_640.tmp
	$tmp = download_url( $url );
	// If error storing temporarily, return it
	if ( is_wp_error( $tmp ) ) {
		return $tmp;
	}

	// Extract filename from url for title (ignoring query string)
	// One of more character that is not a '?', followed by an image extension
	preg_match('/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/', $url, $matches);
	$url_filename = basename( urldecode( $matches[0] ) );
	// Check for extension. If not found, use last component of the URL
	if ( !isset($matches[1]) ) {
		$matches = array();
		// Get the path to the image, without the domain. ex. /news/132456/image
		preg_match_all('/[^:]+:\/\/[^\/]+\/(.+)/', $url, $matches);
		// If found
		if ( isset($matches[1][0]) ) {
			// Replace all '/' into '.' for the filename
			$url_filename = str_replace('/', '-', $matches[1][0]);
		}
		// If not found
		else {
			// Use a random string as a fallback, with length of 16 characters
			$url_filename = wprss_ftp_generate_random_string(16);
		}
	}
	// determine file type (ext and mime/type)
	$url_type = wp_check_filetype($url_filename);

	// override filename if given, reconstruct server path
	if ( !empty( $filename ) ) {
		$filename = sanitize_file_name($filename);
		// extract path parts
		$tmppath = pathinfo( $tmp );
		// build new path
		$new = $tmppath['dirname'] . "/". $filename . "." . $tmppath['extension'];
		// renames temp file on server
		rename($tmp, $new);
		// push new filename (in path) to be used in file array later
		$tmp = $new;
	}

	// assemble file data (should be built like $_FILES since wp_handle_sideload() will be using)
	// full server path to temp file
	$file_array['tmp_name'] = $tmp;

	if ( !empty( $filename ) ) {
		// user given filename for title, add original URL extension
		$file_array['name'] = $filename . "." . $url_type['ext'];
	} else {
		// just use original URL filename
		$file_array['name'] = $url_filename;
	}

	// set additional wp_posts columns
	if ( empty( $post_data['post_title'] ) ) {
		// just use the original filename (no extension)
		$post_data['post_title'] = basename($url_filename, "." . $url_type['ext']);
	}

	// make sure gets tied to parent
	if ( empty( $post_data['post_parent'] ) ) {
		$post_data['post_parent'] = $post_id;
	}

	// required libraries for media_handle_sideload
	require_once(ABSPATH . 'wp-admin/includes/file.php');
	require_once(ABSPATH . 'wp-admin/includes/media.php');
	require_once(ABSPATH . 'wp-admin/includes/image.php');

	// NO FILENAME FIX
	// WordPress does not allow file images that are not in the form of a filename
	// ex: http://domain.com/thoufiqadsjucpqwuamoshfjnax8mtrh/iorqhewufjasj

	if ( apply_filters( 'wprss_ftp_override_upload_security', TRUE ) === TRUE ) {
		// Attempt to get the type
		$data = @getimagesize( $url );
		// If we successfully retrieved the MIME type
		if ( $data !== FALSE && isset($data['mime']) && !empty($data['mime']) ) {
			// Prepare the MIME and file type
			global $wprss_ftp_ext_override;
			global $wprss_ftp_type_override;
			$wprss_ftp_type_override = $data['mime'];
			// Get MIME to extension mappings ( from WordPress wp_check_filetype_and_ext() function )
			$mime_to_ext = apply_filters(
				'getimagesize_mimes_to_exts', array(
					'image/jpeg' => 'jpg',
					'image/png'  => 'png',
					'image/gif'  => 'gif',
					'image/bmp'  => 'bmp',
					'image/tiff' => 'tif',
				)
			);
			// Get the ext
			if ( isset( $mime_to_ext[$wprss_ftp_type_override] ) ) {
				$wprss_ftp_ext_override = $mime_to_ext[$wprss_ftp_type_override];
			} else {
				$wprss_ftp_ext_override = 'png'; // Default to png (most common web image extension)
			}
			// Add a filter to ensure that the image ext and mime type get passed through
			add_filter('wp_check_filetype_and_ext', 'wprss_ftp_mime_override', 10, 4);
		}
	}

	// do the validation and storage stuff
	$att_id = media_handle_sideload( $file_array, $post_id, '', $post_data );             // $post_data can override the items saved to wp_posts table, like post_mime_type, guid, post_parent, post_title, post_content, post_status

	// If error storing permanently, unlink
	if ( is_wp_error($att_id) ) {
		wprss_log( sprintf( 'Error downloading image "%1$s" for post #%2$s: ', $url, $post_id ) . $att_id->get_error_message() );
		@unlink($file_array['tmp_name']);   // clean up
		return $att_id; // output wp_error
	}

	// set as post thumbnail if desired
	if ($thumb) {
		WPRSS_FTP_Images::set_featured_image( $post_id, $att_id );
	}

	return $att_id;
}


/** 
 * Overrides WordPress' security check if no image extension of MIME type is given.
 * 
 * @since 2.8.1
 */
function wprss_ftp_mime_override( $image, $file, $filename, $mimes ) {
	if ( empty($image['ext']) ) {
		global $wprss_ftp_ext_override;
		$image['ext'] = $wprss_ftp_ext_override;
	}
	if ( empty($image['type']) ) {
		global $wprss_ftp_type_override;
		$image['type'] = $wprss_ftp_type_override;
	}
	return $image;
}

add_filter( 'wprss_ftp_skip_image_size_check', '__return_true' );


/**
 * Generates a random string with a given length.
 *
 * @since 2.9.6
 * @param int $length The length of the generated string.
 * @return string The generated string
 */
function wprss_ftp_generate_random_string($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}