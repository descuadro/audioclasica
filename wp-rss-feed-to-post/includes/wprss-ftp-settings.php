<?php
/**
 * This file handles registering the add-on settings and rendering the settings tab
 *
 * @since 1.0
 */


/**
 * Handles the registration of settings and sections, and the rendering of the settings page.
 * 
 * @since 1.0
 */
final class WPRSS_FTP_Settings {

/*===== CONSTANTS AND STATIC MEMBERS ======================================================================*/

	/**
	 * The name of the options array, as stored in the database.
	 */
	const OPTIONS_NAME = 'wprss_settings_ftp';

	/**
	 * FTP Settings tab slug
	 */
	const TAB_SLUG = 'ftp_settings';

	/**
	 * The Singleton instance
	 */
	private static $instance;


/*===== CONSTRUCTOR AND SINGLETON GETTER ==================================================================*/


	/**
	 * Constructor
	 * 
	 * @since 1.0
	 */
	public function __construct() {
		if ( self::$instance === NULL ) {
			# Initialize
			add_action( 'wprss_admin_init', array( $this, 'register_settings' ) );
		} else {
			wp_die("WPRSS_FTP_Settings class is a singleton class and cannot be redeclared.");
		}
	}

	/**
	 * Returns the singleton instance
	 * 
	 * @return WPRSS_FTP_Settings
	 */
	public static function get_instance() {
		if ( self::$instance === NULL ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


/*===== SETTINGS GETTERS =================================================================================*/

	/**
	 * Returns the default settings.
	 * 
	 * @return array An associative array of key => value setting pairs.
	 * @since 1.0
	 */
	public function get_defaults() {
		$wp_comment_status = get_option( 'default_comment_status', 'open' );
		return apply_filters('wprss_ftp_default_settings', array(
			'post_site'					=>	'',						# The post site in which to import posts
			'post_type'					=>	'post',					# The Post type to use
			'post_status'				=>	'publish',				# The status to assign to imported posts
			'post_format'				=>	'standard',				# The format to assign to imported posts
			'post_date'					=>	'original',				# The post's publish date
			'comment_status'			=>	$wp_comment_status,		# The post's comment status
			'source_link'				=>	'false',				# Whether or not to link back to the original post
			'source_link_text'			=>	'This *post* was originally published on **this site**',
			'force_full_content'		=>	'false',				# The flag that determines whether or not get forcefully retrieval the full feed content.
			'post_word_limit'			=>	'',						# The default word limit for post content. Empty value disables the limit
			'canonical_link'			=>	'false',				# The default value for wether or not to include a rel="canonical" link in the page head for imported posts

			'def_author'				=>	'',						# The default author to use for imported posts
			'author_fallback_method'	=>	'use_existing',			# The method to use when the feed author is not found
			'fallback_author'			=>	'admin',				# The author to fall back upon when 'using existing'
			'no_author_found'			=>	'fallback',				# The action to take when no author is found. Either 'fallback' or 'skip'

			'post_taxonomy'				=>	'category',				# The default post taxonomy
			'post_terms'				=>	array(),				# The default post taxonomy terms
			'post_auto_tax_terms'		=>	FALSE, 					# The default setting for whether or not to auto create tax terms for feed categories
			'post_tags'					=>	'',						# The default tags to attach to posts

			'use_featured_image'		=>	'true',					# Whether or not to use featured images
			'featured_image'			=>	'first',				# Which image to use as a featured image. 'first', 'last' or 'thumb'
			'fallback_to_feed_image'	=>	'true',					# Whether or not to fallback to the image provided by the feed
			'remove_ft_image'			=>	'false',				# Whether or not to remove the chosen featured image from post content
			'image_min_width'			=>	'80',					# The minimum width of images to import
			'image_min_height'			=>	'80',					# The minimum height of images to import
			'save_images_locally'		=>	'true',					# If true, images are saved locally in the media. If false, they are linked from the source.

			'post_language'				=>	'en',					# The post language - only applies if WPML is active

			'post_prepend'				=>	'',						# The default text to prepend to posts
			'post_append'				=>	'',						# The default text to append to posts

			'extraction_rules'			=>	array(),				# The CSS selectors for the elements to remove from post content
			'extraction_rules_types'	=>	array(),				# The manipulation types for each extraction rule

			'affiliate_link'			=>	'false',				# The affiliate link suffix to add
			'allow_embedded_content'	=>	'false',				# Allowing of embedded content in posts

			'full_text_rss_service'		=>	'free',					# Full text RSS service type. 'free' or 'feeds_api'
			'feeds_api_key'				=>	'',						# The user's FeedsAPI key

			'user_feed_namespaces'		=>	array(					# The feed namespaces added by the user.
				'names'	=>	array(),
				'urls'	=>	array()
			),
			'custom_fields'				=>	array(),				# The custom field mappings default value
			//'allow_local_requests'		=>	'false',				# Allowing requests to URLs, which would normally be blocked by wp_http_validate_url()
			
			'legacy_enabled'			=>	'false'					# Allows the use of wprss_feed_item when set to TRUE
		));
	}


	/**
	 * Returns the default value for the given setting.
	 * Will throw an exception if the name given is not found.
	 * 
	 * @param $option	The name of the option whose default to return.
	 * @return mixed	The value of the option for the given option name
	 * @since 1.0
	 */
	public function get_default( $option ) {
		# Add code ...
		$all = $this->get_defaults();
		return $all[$option];
	}


	/**
	 * Returns an option of sub-option form the database.
	 *
	 * @param array 	Optional. The key of the sub option to retrieve.
	 * @param mixed		The value to return if the option was not found. Ommit or
	 *					use '!default' to get the default value from get_default()
	 * @return mixed 	The value of the (sub)option with the key(s)
	 * @since 1.0
	 */
	public function get( $sub_option = NULL, $default = '!default' ) {
		$option = get_option( self::OPTIONS_NAME, $this->get_defaults() );
		if ( $sub_option !== NULL ) {
			if ( array_key_exists( $sub_option, $option ) )
				return $option[$sub_option];
			elseif ( strtolower( $default ) === '!default' )
				return $this->get_default( $sub_option );
			else return $default;
		} else {
			$final_options = array();
			foreach( $this->get_defaults() as $key => $value ) {
				if ( isset( $option[$key] ) )
					$value = $option[$key];
				$final_options[ $key ] = $value;
			}
			return $final_options;
		}
	}


	/**
	 * Returns the final, computed options for a feed.
	 * These settings are the general settings, merged against the feed's own meta data settings.
	 *
	 * @since 1.0
	 */
	public function get_computed_options( $post_id ) {
		$post = get_post( $post_id );

		$meta_fields = WPRSS_FTP_Meta::get_instance()->get_meta_fields('all');
		$meta = array();
		foreach ( $meta_fields as $key => $value ) {
			$meta_value = WPRSS_FTP_Meta::get_instance()->get_meta( $post_id, $key );
			if ( ( is_string( $meta_value ) && strlen( $meta_value ) > 0 ) || is_array( $meta_value ) )
				$meta[$key] = $meta_value;
		}
		$options = $this->get();

		return wp_parse_args( $meta, $options );
	}


#== SETTINGS REGISTRATION =====================================================================================

	/**
	 * Registers the settings page, sections and fields.
	 * 
	 * @since 1.0
	 */
	public function register_settings() {
		// Register Page
		register_setting(
			self::OPTIONS_NAME,							// A settings group name.
			self::OPTIONS_NAME,							// The name of an option to sanitize and save.
			array( $this, 'validate_settings' )			// The function that sanitizes the option's value.
		);

		// Register Sections

		add_settings_section(   
			'wprss_settings_ftp_general_section',		// ID to identify this section
			__( 'General Settings', 'wprss' ),			// Title to be displayed on the administration page
			array( $this, 'render_general_section' ),	// Callback that renders the description of the section
			'wprss_settings_ftp'						// Page on which to add this section of options
		);

		add_settings_section(   
			'wprss_settings_ftp_taxonomies_section',		// ID to identify this section
			__( 'Taxonomies', 'wprss' ),					// Title to be displayed on the administration page
			array( $this, 'render_taxonomies_section' ),	// Callback that renders the description of the section
			'wprss_settings_ftp'							// Page on which to add this section of options
		);

		add_settings_section(   
			'wprss_settings_ftp_authors_section',		// ID to identify this section
			__( 'Authors', 'wprss' ),					// Title to be displayed on the administration page
			array( $this, 'render_authors_section' ),	// Callback that renders the description of the section
			'wprss_settings_ftp'						// Page on which to add this section of options
		);

		add_settings_section(   
			'wprss_settings_ftp_images_section',		// ID to identify this section
			__( 'Images', 'wprss' ),					// Title to be displayed on the administration page
			array( $this, 'render_images_section' ),	// Callback that renders the description of the section
			'wprss_settings_ftp'						// Page on which to add this section of options
		);

		add_settings_section(   
			'wprss_settings_ftp_full_text_section',		// ID to identify this section
			__( 'Full Text RSS', 'wprss' ),				// Title to be displayed on the administration page
			array( $this, 'render_full_text_section' ),	// Callback that renders the description of the section
			'wprss_settings_ftp'						// Page on which to add this section of options
		);

		add_settings_section(   
			'wprss_settings_ftp_namespaces_section',	// ID to identify this section
			__( 'Custom Namespaces', 'wprss' ),			// Title to be displayed on the administration page
			array( $this, 'render_namespaces_section' ),// Callback that renders the description of the section
			'wprss_settings_ftp'						// Page on which to add this section of options
		);

		
		#== GENERAL SECTION ==========

		// POST TYPE
		add_settings_field( 
			'wprss-settings-ftp-post-type',				// ID used to identify the field
			__( 'Post Type', 'wprss' ),					// The label to the left of the option element
			array( $this, 'render_post_type' ),			// The function that renders the option interface
			'wprss_settings_ftp',						// The page on which this option will be displayed
			'wprss_settings_ftp_general_section'		// The section to which this field belongs
		);

		// POST STATUS
		add_settings_field( 
			'wprss-settings-ftp-post-status',			// ID used to identify the field
			__( 'Post Status', 'wprss' ),				// The label to the left of the option element
			array( $this, 'render_post_status' ),		// The function that renders the option interface
			'wprss_settings_ftp',						// The page on which this option will be displayed
			'wprss_settings_ftp_general_section'		// The section to which this field belongs
		);

		// POST FORMAT
		add_settings_field( 
			'wprss-settings-ftp-post-format',			// ID used to identify the field
			__( 'Post Format', 'wprss' ),				// The label to the left of the option element
			array( $this, 'render_post_format' ),		// The function that renders the option interface
			'wprss_settings_ftp',						// The page on which this option will be displayed
			'wprss_settings_ftp_general_section'		// The section to which this field belongs
		);

		// POST DATE
		add_settings_field( 
			'wprss-settings-ftp-post-date',				// ID used to identify the field
			__( 'Post Date', 'wprss' ),					// The label to the left of the option element
			array( $this, 'render_post_date' ),			// The function that renders the option interface
			'wprss_settings_ftp',						// The page on which this option will be displayed
			'wprss_settings_ftp_general_section'		// The section to which this field belongs
		);

		// ENABLE COMMENTS
		add_settings_field( 
			'wprss-settings-ftp-comment-status',		// ID used to identify the field
			__( 'Enable Comments', 'wprss' ),			// The label to the left of the option element
			array( $this, 'render_comment_status' ),	// The function that renders the option interface
			'wprss_settings_ftp',						// The page on which this option will be displayed
			'wprss_settings_ftp_general_section'		// The section to which this field belongs
		);

		// SOURCE LINK
		add_settings_field( 
			'wprss-settings-ftp-source-link',			// ID used to identify the field
			__( 'Link back to source?', 'wprss' ),		// The label to the left of the option element
			array( $this, 'render_source_link' ),		// The function that renders the option interface
			'wprss_settings_ftp',						// The page on which this option will be displayed
			'wprss_settings_ftp_general_section'		// The section to which this field belongs
		);

		// SOURCE LINK TEXT
		add_settings_field( 
			'wprss-settings-ftp-source-link-text',		// ID used to identify the field
			__( 'Source Link Text', 'wprss' ),			// The label to the left of the option element
			array( $this, 'render_source_link_text' ),	// The function that renders the option interface
			'wprss_settings_ftp',						// The page on which this option will be displayed
			'wprss_settings_ftp_general_section'		// The section to which this field belongs
		);

		// OPEN LINKS BEHAVIOUR
		/* Removed setting - not needed in add-on
		add_settings_field( 
			'wprss-settings-ftp-open-dd',				// ID used to identify the field
			__( 'Open Links Behaviour', 'wprss' ),		// The label to the left of the option element
			'wprss_setting_open_dd_callback',			// The function that renders the option interface
			'wprss_settings_ftp',						// The page on which this option will be displayed
			'wprss_settings_ftp_general_section'		// The section to which this field belongs
		);
		*/

		// SET LINKS NO FOLLOW
		/* Removed setting - not needed in add-on
		add_settings_field( 
			'wprss-settings-ftp-follow-dd',				// ID used to identify the field
			__( 'Set links as nofollow', 'wprss' ),		// The label to the left of the option element
			'wprss_setting_follow_dd_callback',			// The function that renders the option interface
			'wprss_settings_ftp',						// The page on which this option will be displayed
			'wprss_settings_ftp_general_section'		// The section to which this field belongs
		);
		*/

		// VIDEO LINKS
		/* Removed setting - not needed in add-on
		add_settings_field( 
			'wprss-settings-ftp-video-links',			// ID used to identify the field
			__( 'For video feed items use', 'wprss' ),	// The label to the left of the option element
			'wprss_setting_video_links_callback',		// The function that renders the option interface
			'wprss_settings_ftp',						// The page on which this option will be displayed
			'wprss_settings_ftp_general_section'		// The section to which this field belongs
		);
		*/

		// FORCE FULL CONTENT
		/* Removed setting - not needed in add-on
		add_settings_field( 
			'wprss-settings-ftp-force-full-content',		// ID used to identify the field
			__( 'Force full Content', 'wprss' ),			// The label to the left of the option element
			array( $this, 'render_force_full_content' ),	// The function that renders the option interface
			'wprss_settings_ftp',							// The page on which this option will be displayed
			'wprss_settings_ftp_general_section'			// The section to which this field belongs
		);
		*/

		// POST WORD LIMIT
		/* @tag trim_content
		add_settings_field( 
			'wprss-settings-ftp-post-word-limit',			// ID used to identify the field
			__( 'Post content word limit', 'wprss' ),		// The label to the left of the option element
			array( $this, 'render_post_word_limit' ),		// The function that renders the option interface
			'wprss_settings_ftp',							// The page on which this option will be displayed
			'wprss_settings_ftp_general_section'			// The section to which this field belongs
		);*/


		// CANONICAL LINK
		add_settings_field( 
			'wprss-settings-ftp-canonical-link',			// ID used to identify the field
			__( 'Canonical Link', 'wprss' ),				// The label to the left of the option element
			array( $this, 'render_canonical_link' ),		// The function that renders the option interface
			'wprss_settings_ftp',							// The page on which this option will be displayed
			'wprss_settings_ftp_general_section'			// The section to which this field belongs
		);



		#== TAXONOMIES SECTION ==========

		// POST TAXONOMY
		add_settings_field( 
			'wprss-settings-ftp-post-taxonomy',					// ID used to identify the field
			__( "Taxonomies and Tags", 'wprss' ),				// The label to the left of the option element
			array( $this, 'render_post_taxonomy' ),				// The function that renders the option interface
			'wprss_settings_ftp',								// The page on which this option will be displayed
			'wprss_settings_ftp_taxonomies_section'				// The section to which this field belongs
		);


		// POST TERMS
		add_settings_field( 
			'wprss-settings-ftp-post-terms',					// ID used to identify the field
			__( "Post Terms", 'wprss' ),						// The label to the left of the option element
			array( $this, 'render_post_terms' ),				// The function that renders the option interface
			'wprss_settings_ftp',								// The page on which this option will be displayed
			'wprss_settings_ftp_taxonomies_section'				// The section to which this field belongs
		);


		#== AUTHORS SECTION ==========

		// DEFAULT AUTHOR
		add_settings_field( 
			'wprss-settings-ftp-def-author',			// ID used to identify the field
			__( 'Author for imported items', 'wprss' ),		// The label to the left of the option element
			array( $this, 'render_def_author' ),		// The function that renders the option interface
			'wprss_settings_ftp',						// The page on which this option will be displayed
			'wprss_settings_ftp_authors_section'		// The section to which this field belongs
		);


		// FALLBACK AUTHOR METHOD
		/*
		add_settings_field( 
			'wprss-settings-ftp-author-fallback-method',		// ID used to identify the field
			__( "If feed author does not exist", 'wprss' ),		// The label to the left of the option element
			array( $this, 'render_author_fallback_method' ),	// The function that renders the option interface
			'wprss_settings_ftp',								// The page on which this option will be displayed
			'wprss_settings_ftp_authors_section'				// The section to which this field belongs
		);
		*/

		
		#== IMAGES SECTION ==========

		// USE FEATURED IMAGE
		add_settings_field( 
			'wprss-settings-ftp-use-featured-image',		// ID used to identify the field
			__( 'Use a featured image', 'wprss' ),			// The label to the left of the option element
			array( $this, 'render_use_featured_image' ),	// The function that renders the option interface
			'wprss_settings_ftp',							// The page on which this option will be displayed
			'wprss_settings_ftp_images_section'				// The section to which this field belongs
		);


		// FEATURED IMAGE TO USE
		add_settings_field( 
			'wprss-settings-ftp-featured-image',			// ID used to identify the field
			__( "Featured image to use", 'wprss' ),			// The label to the left of the option element
			array( $this, 'render_featured_image' ),		// The function that renders the option interface
			'wprss_settings_ftp',							// The page on which this option will be displayed
			'wprss_settings_ftp_images_section'				// The section to which this field belongs
		);
		
		
		// FALLBACK TO FEED IMAGE
		add_settings_field( 
			'wprss-settings-ftp-fallback-to-feed-image',		// ID used to identify the field
			__( 'Fallback to Feed Image', 'wprss' ),			// The label to the left of the option element
			array( $this, 'render_fallback_to_feed_image' ),	// The function that renders the option interface
			'wprss_settings_ftp',							// The page on which this option will be displayed
			'wprss_settings_ftp_images_section'				// The section to which this field belongs
		);

		// FEATURED IMAGE TO USE
		add_settings_field( 
			'wprss-settings-ftp-image-min-size',			// ID used to identify the field
			__( "Image minimum size", 'wprss' ),			// The label to the left of the option element
			array( $this, 'render_image_minimum_size' ),	// The function that renders the option interface
			'wprss_settings_ftp',							// The page on which this option will be displayed
			'wprss_settings_ftp_images_section'				// The section to which this field belongs
		);

		// SAVE IMAGES LOCALLY
		add_settings_field( 
			'wprss-settings-ftp-save-images-locally',		// ID used to identify the field
			__( "Save Images Locally", 'wprss' ),			// The label to the left of the option element
			array( $this, 'render_save_images_locally' ),	// The function that renders the option interface
			'wprss_settings_ftp',							// The page on which this option will be displayed
			'wprss_settings_ftp_images_section'				// The section to which this field belongs
		);

		// ALLOW LOCAL REQUESTS
		/*
		add_settings_field( 
			'wprss-settings-ftp-allow-local-requests',		// ID used to identify the field
			__( 'Allow local requests', 'wprss' ),			// The label to the left of the option element
			array( $this, 'render_allow_local_requests' ),		// The function that renders the option interface
			'wprss_settings_ftp',					// The page on which this option will be displayed
			'wprss_settings_ftp_images_section'			// The section to which this field belongs
		);*/

		
		#== FULL TEXT RSS SECTION ==========

		add_settings_field( 
			'wprss-settings-ftp-full-text-rss-service',		// ID used to identify the field
			__( "Full Text RSS service", 'wprss' ),			// The label to the left of the option element
			array( $this, 'render_full_text_rss_service' ),	// The function that renders the option interface
			'wprss_settings_ftp',							// The page on which this option will be displayed
			'wprss_settings_ftp_full_text_section'			// The section to which this field belongs
		);

		add_settings_field( 
			'wprss-settings-ftp-feeds-api-key',				// ID used to identify the field
			__( "FeedsAPI Key", 'wprss' ),					// The label to the left of the option element
			array( $this, 'render_feeds_api_key' ),			// The function that renders the option interface
			'wprss_settings_ftp',							// The page on which this option will be displayed
			'wprss_settings_ftp_full_text_section'			// The section to which this field belongs
		);


		#== CUSTOM NAMESPACES SECTION ==========

		add_settings_field( 
			'wprss-settings-ftp-custom-namespaces',			// ID used to identify the field
			__( "Namespaces", 'wprss' ),					// The label to the left of the option element
			array( $this, 'render_custom_namespaces' ),		// The function that renders the option interface
			'wprss_settings_ftp',							// The page on which this option will be displayed
			'wprss_settings_ftp_namespaces_section'			// The section to which this field belongs
		);
		
		#== LICENSE SETTINGS ==========
		if ( version_compare(WPRSS_VERSION, '4.5', '<') ) {
			add_settings_section(
				'wprss_settings_ftp_licenses_section',
				__( 'Feed to Post License', 'wprss' ),
				array( $this, 'license_section_callback' ),
				'wprss_settings_license_keys'
			);

			add_settings_field(
				'wprss-settings-license',
				__( 'License Key', 'wprss' ),
				array( $this, 'license_callback' ),
				'wprss_settings_license_keys',
				'wprss_settings_ftp_licenses_section'
			);

			add_settings_field(
				'wprss-settings-license-activation',
				__( 'Activate License', 'wprss' ),
				array( $this, 'license_activation_callback' ),
				'wprss_settings_license_keys',
				'wprss_settings_ftp_licenses_section'
			);
		}

		// Add tab to Aggregator Settings page
		add_action( 'wprss_options_tabs', array( $this, 'add_tab' ) );
		// Add action to register field sections to tab
		add_action( 'wprss_add_settings_fields_sections', array( $this, 'render_settings_page' ), 10, 1 );
		
		do_action('wprss_ftp_after_settings_register', $this);
	}


#== SECTION RENDERERS ===============================================================================


	/**
	 * General Section
	 * @since 1.0
	 */
	public function render_general_section() {
		echo '<p>' . __( 'General settings about imported posts', 'wprss' ) . '</p>';
	}
	
	/**
	 * Authors Section
	 * @since 1.0
	 */
	public function render_authors_section() {
		echo '<p>' . __( 'Settings about post authors and users.', 'wprss' ) . '</p>';
	}


	/**
	 * Taxonomies Section
	 * @since 1.0
	 */
	public function render_taxonomies_section() {
		echo '<p>' . __( 'Settings about post taxonomies and tags.', 'wprss' ) . '</p>';
	}


	/**
	 * Images Section
	 * @since 1.0
	 */
	public function render_images_section() {
		echo '<p>' . __( 'Configure how to handle images found in feeds.', 'wprss' ) . '</p>';
	}


	/**
	 * Full Text RSS Section
	 * @since 1.0
	 */
	public function render_full_text_section() {
		echo '<p>' . __( 'Configure your full text RSS options.', 'wprss' ) . '</p>';
	}


	/**
	 * Custom Namespaces Section
	 * @since 1.0
	 */
	public function render_namespaces_section() {
		echo '<p>' . __( 'Manage your RSS feed Namespaces.', 'wprss' ) . '</p>';
	}


#== FIELD RENDERERS =================================================================================

	#== General Section ========================

	/**
	 * Renders the post_type dropdown
	 *
	 * @since 1.0
	 */
	public function render_post_type( $args ) {
		$post_type = $this->get( 'post_type' );
		$all_post_types = self::get_post_types();
		echo WPRSS_FTP_Utils::array_to_select( $all_post_types, array(
			'id'		=>	'ftp-post-type',
			'name'		=>	self::OPTIONS_NAME . '[post_type]',
			'selected'	=>	$post_type,
		));
		echo '<label class="description" for="ftp-post-type">Select a post type</label>';
	}


	/**
	 * Renders the post_status dropdown
	 *
	 * @since 1.0
	 */
	public function render_post_status( $args ) {
		$post_status = $this->get( 'post_status' );
		$post_statuses = self::get_post_statuses();
		echo WPRSS_FTP_Utils::array_to_select( $post_statuses, array(
			'id'		=>	'ftp-post-status',
			'name'		=>	self::OPTIONS_NAME . '[post_status]',
			'selected'	=>	$post_status,
		));
		echo '<label class="description" for="ftp-post-status">The post status that post get assigned when imported.</label>';
	}


	/**
	 * Renders the post_format dropdown
	 *
	 * @since 1.0
	 */
	public function render_post_format( $args ) {
		$post_format = $this->get( 'post_format' );
		$post_formats = self::get_post_formats();
		echo WPRSS_FTP_Utils::array_to_select( $post_formats, array(
			'id'		=>	'ftp-post-format',
			'name'		=>	self::OPTIONS_NAME . '[post_format]',
			'selected'	=>	$post_format,
		));
		echo '<label class="description" for="ftp-post-format">The format that post get assigned when imported.</label>';
	}


	/**
	 * Renders the post_date dropdown
	 *
	 * @since 1.0
	 */
	public function render_post_date( $args ) {
		$post_date = $this->get( 'post_date' );
		$options = self::get_post_date_options();
		echo WPRSS_FTP_Utils::array_to_select( $options, array(
			'id'		=>	'ftp-post-date',
			'name'		=>	self::OPTIONS_NAME . '[post_date]',
			'selected'	=>	$post_date,
		));
		echo '<label class="description" for="ftp-post-format">Choose the date to use for imported posts.</label>';
	}


	/**
	 * Renders the comment status checkbox
	 * 
	 * @since 1.4.1
	 */
	public function render_comment_status( $args ) {
		$comment_status = $this->get( 'comment_status' );
		echo WPRSS_FTP_Utils::boolean_to_checkbox(
			WPRSS_FTP_Utils::multiboolean( $comment_status ),
			array(
				'id'		=>	'ftp-comment-status',
				'name'		=>	self::OPTIONS_NAME . '[comment_status]',
				'value'		=>	'true',
			)
		);
		echo '<label class="description" for="ftp-comment-status">Tick this this box to enable comments for imported posts.</label>';
	}


	/**
	 * Renders the source_link checkbox
	 *
	 * @since 1.0
	 */
	public function render_source_link( $args ) {
		$source_link = $this->get( 'source_link' );
		echo WPRSS_FTP_Utils::boolean_to_checkbox(
			WPRSS_FTP_Utils::multiboolean( $source_link ),
			array(
				'id'		=>	'ftp-source-link',
				'name'		=>	self::OPTIONS_NAME . '[source_link]',
				'value'		=>	'true',
			)
		);
		echo '<label class="description" for="ftp-source-link">Tick this box to add a link back to the original post, at the end of the post\'s content</label>';
	}


	/**
	 * Renders the source_link_text text field
	 *
	 * @since 1.0
	 */
	public function render_source_link_text( $args ) {
		$source_link_text = $this->get( 'source_link_text' );
		echo '<input type="text" name="'.self::OPTIONS_NAME.'[source_link_text]" id="ftp-source-link-text" placeholder="Source link text" value="'.$source_link_text.'"/>';
		echo '<br/>';
		$general_meta_fields = WPRSS_FTP_Meta::get_instance()->get_meta_fields('general');
		echo '<label class="description" for="ftp-source-link-text">
		Enter the text to use when linking back to the original post source.
		<br/>Wrap a phrase in asterisk symbols ( <b>*link to post*</b> ) to turn it into the link to the <b>original post</b>,
		<br/>or in double asterisk symbols ( <b>**link to source**</b> ) to turn it into a link to the post <b>feed source</b>
		</label>';
	}


	/**
	 * Renders the full_content checkbox
	 *
	 * @since 1.0
	 */
	public function render_force_full_content( $args ) {
		$force_full_content = $this->get( 'force_full_content' );
		echo WPRSS_FTP_Utils::boolean_to_checkbox(
			WPRSS_FTP_Utils::multiboolean( $force_full_content ),
			array(
				'id'		=>	'ftp-force-full-content',
				'name'		=>	self::OPTIONS_NAME . '[force_full_content]',
				'value'		=>	'true'
			)
		);
		echo '<label class="description" for="ftp-force-full-content">Check this box to forcefully attempt to retrieve the full feed content, if the feed only provides excerpts.</label>';
	}


	/**
	 * Renders the full_content checkbox
	 *
	 * @since 1.0
	 */
	public function render_post_word_limit( $args ) {
		$post_word_limit = $this->get( 'post_word_limit' );
		?>
		<input type="number" min="0" placeholder="No limit" class="wprss-number-roller" id="ftp-post-word-limit" name="<?php echo self::OPTIONS_NAME; ?>[post_word_limit]" value="<?php echo $post_word_limit ?>" />
		<label class="description" for="ftp-post-word-limit">
			Enter the maximum number of words to import for posts. Leave blank to use no limit.
		</label>
		<?php
	}


	/**
	 * Renders the canonical_link checkbox
	 *
	 * @since 1.8
	 */
	public function render_canonical_link( $args ) {
		$canonical_link = $this->get( 'canonical_link' );
		echo WPRSS_FTP_Utils::boolean_to_checkbox(
			WPRSS_FTP_Utils::multiboolean( $canonical_link ),
			array(
				'id'		=>	'ftp-canonical-link',
				'name'		=>	self::OPTIONS_NAME . '[canonical_link]',
				'value'		=>	'true'
			)
		);
		?>
		<label class="description" for="ftp-canonical-link">
			Check this box to add a rel="canonical" link to the head of imported posts.
			<a href="http://webdesign.about.com/od/seo/a/rel-canonical.htm" target="_blank">Learn more about canonical pages.</a>
		</label>
		<?php
	}


	#== Authors Section ========================

	/*
	 * Renders the def_author dropdown
	 *
	 * @since 1.0
	 *
	public function render_def_author( $args ) {
		$def_author = $this->get( 'def_author' );
		$users = self::get_users();
		$users_dropdown = array_merge( array( '.' => 'Use author in feed' ), $users );
		echo WPRSS_FTP_Utils::array_to_select( $users_dropdown, array(
			'id'		=>	'ftp-def-author',
			'name'		=>	self::OPTIONS_NAME . '[def_author]',
			'selected'	=>	$def_author,
		));
		echo '<label class="description" for="ftp-def-author">You can choose a user to use as author, or get the author from the feed.</label>';
	}
	*/


	/**
	 * Renders the author settings
	 *
	 * @since 1.9.3
	 */
	public function render_def_author( $args ) {
		$this->render_author_options();
	}


	public function render_author_fallback_method( $args ) {
	}
	/*
	 * Renders the author_fallback_method dropdown
	 *
	 * @since 1.0
	 *
	public function render_author_fallback_method( $args ) {
		$author_fallback_method = $this->get( 'author_fallback_method' );
		echo WPRSS_FTP_Utils::array_to_select( array( 'existing' => 'Use existing', 'create' => 'Create new' ), array(
			'id'		=>	'ftp-author-fallback-method',
			'name'		=>	self::OPTIONS_NAME . '[author_fallback_method]',
			'selected'	=>	$author_fallback_method,
		));
		$this->render_fallback_author( $args );
		?>
		<script type="text/javascript">
			(function($) {
				wprss_ftp_show_authors = function( ) {
					value = $('#ftp-author-fallback-method').val();
					if ( value !== 'existing' ) {
						$('#ftp-fallback-author').hide();
					} else $('#ftp-fallback-author').show();
				}

				$('#ftp-author-fallback-method').on('change', wprss_ftp_show_authors );
				$(window).load( wprss_ftp_show_authors );
			})(jQuery);
		</script>
		<?php
	}
	 */


	/**
	 * Renders the fallback_author dropdown
	 *
	 * @since 1.0
	 */
	public function render_fallback_author( $args ) {
		$fallback_author = $this->get( 'fallback_author' );
		$users = array_map( array( 'WPRSS_FTP_Settings', 'wprss_ftp_user_login' ), get_users() );
		$users_dropdown = array_combine( $users, $users );
		echo WPRSS_FTP_Utils::array_to_select( $users_dropdown, array(
			'id'		=>	'ftp-fallback-author',
			'name'		=>	self::OPTIONS_NAME . '[fallback_author]',
			'selected'	=>	$fallback_author,
		));
	}


	#== Taxonomies Section ========================

	/**
	 * Renders the taxonomies dropdown
	 * 
	 * @since 1.0
	 */
	public function render_post_taxonomy( $args ) {
		echo '<p id="ftp-post-taxonomy">Loading taxonomies ...</p>';
	}


	/**
	 * Renders the taxonomy terms dropdown
	 * 
	 * @since 1.0
	 */
	public function render_post_terms( $args ) {
		echo '<p id="ftp-post-terms">Loading taxonomy terms ...</p>';
	}


	/**
	 * Renders the post tags text input field
	 * 
	 * @since 1.0
	 */
	function render_post_tags( $args ) {
		$post_tags = $this->get('post_tags');
		echo '<input id="post_tags" name="'.self::OPTIONS_NAME.'[post_tags]" value="'.$post_tags.'" autocomplete="off" placeholder="Post tags, comma separated" type="text" />';
		echo '<br/><label for="post_tags" class="description">Enter the post tags, comma separated, to attach to all imported posts.</label>';
	}


	#== Images Section ========================


	/**
	 * Renders the dropdown for using featured images
	 * 
	 * @since 1.0
	 */ 
	public function render_use_featured_image( $args ) {
		$use_featured_image = $this->get( 'use_featured_image' );
		echo WPRSS_FTP_Utils::boolean_to_checkbox(
			WPRSS_FTP_Utils::multiboolean( $use_featured_image ),
			array(
				'id'		=>	'ftp-use-featured-image',
				'name'		=>	self::OPTIONS_NAME . '[use_featured_image]',
				'value'		=>	'true'
			)
		);
		echo '<label class="description" for="ftp-use-featured-image">Check this box to enable featured images for imported posts.</label>';
	}


	/**
	 * 
	 * 
	 * @since 1.0
	 */
	public function render_featured_image( $args ) {
		$featured_image = $this->get( 'featured_image' );
		$options = WPRSS_FTP_Meta::get_instance()->get_meta_fields('images');
		$options = $options['featured_image']['options'];
		echo WPRSS_FTP_Utils::array_to_select( $options,
			array(
				'id'		=>	'ftp-featured-image',
				'name'		=>	self::OPTIONS_NAME . '[featured_image]',
				'selected'	=>	$featured_image,
			)
		);
	}


	/**
	 * Renders the dropdown for using featured images
	 * 
	 * @since 1.0
	 */ 
	public function render_fallback_to_feed_image( $args ) {
		$fallback_to_featured_image = $this->get( 'fallback_to_feed_image' );
		echo WPRSS_FTP_Utils::boolean_to_checkbox(
			WPRSS_FTP_Utils::multiboolean( $fallback_to_featured_image ),
			array(
				'id'		=>	'ftp-fallback-to-feed-image',
				'name'		=>	self::OPTIONS_NAME . '[fallback_to_feed_image]',
				'value'		=>	'false'
			)
		);
		echo '<label class="description" for="ftp-fallback-to-feed-image">' . __("Check this box to use the feed channel's image, if available, before resorting to the source fallback image") . '</label>';
	}

	
	/**
	 * Renders the two dropdowns for the minimum dimensions for the images to import
	 * 
	 * @since 1.0
	 */
	public function render_image_minimum_size( $args ) {
		$min_width = $this->get( 'image_min_width' );
		$min_height = $this->get( 'image_min_height' );
		echo '<p>';
		echo '<input class="wprss-number-roller" type="number" id="ftp-min-width" name="'.self::OPTIONS_NAME . '[image_min_width]'.'" min="0" placeholder="Ignore" value="'.$min_width.'" />';
		echo '<span class="dimension-divider">x</span>';
		echo '<input class="wprss-number-roller" type="number" id="ftp-min-height" name="'.self::OPTIONS_NAME . '[image_min_height]'.'" min="0" placeholder="Ignore" value="'.$min_height.'" />';
		echo '</p>';
		echo '<label class="description">Images in posts are imported into the media library only if they are larger than the above dimensions (width and height in pixels).</label>';
	}


	/**
	 * Renders the checkbox for the option to save images locally
	 * 
	 * @since 1.3
	 */
	public function render_save_images_locally( $args ) {
		$save_images_locally = $this->get( 'save_images_locally' );
		echo WPRSS_FTP_Utils::boolean_to_checkbox(
			WPRSS_FTP_Utils::multiboolean( $save_images_locally ),
			array(
				'id'		=>	'ftp-save-images-locally',
				'name'		=>	self::OPTIONS_NAME . '[save_images_locally]',
				'value'		=>	'true'
			)
		);
		echo '<label class="description" for="ftp-save-images-locally">Check this box to save images in the local media library.</label>';
	}
	
	
	/**
	 * Renders the checkbox for the option to allow local requests
	 * 
	 * @since 2.8.6
	 * @deprecated 3.0
	 */
	public function render_allow_local_requests( $args ) {
		$name = 'allow_local_requests';
		$id = 'ftp-' . str_replace('_', '-', $name);
		$allow_local_requests = $this->get( $name );
		echo WPRSS_FTP_Utils::boolean_to_checkbox(
			WPRSS_FTP_Utils::multiboolean( $allow_local_requests ),
			array(
				'id'		=>	$id,
				'name'		=>	sprintf('%2$s[%1$s]', $name, self::OPTIONS_NAME),
				'value'		=>	'true'
			)
		);
		?>
		<label class="description" for="<?php echo $id ?>"><?php _e('Check this box if having trouble saving feed item images locally. This allows requests to local IPs.', 'wprss') ?></label>
		<?php
	}


	/**
	 * Renders the dropdown to choose the full text RSS service type
	 * 
	 * @since 2.7
	 */
	public function render_full_text_rss_service( $args ) {
		// Get the saved option value, and the dropdown options
		$selected = $this->get( 'full_text_rss_service' );
		$options = self::get_full_text_rss_service_options();
		// Render the dropdown
		echo WPRSS_FTP_Utils::array_to_select( $options,
			array(
				'id'		=>	'ftp-full-text-rss-service',
				'name'		=>	self::OPTIONS_NAME . '[full_text_rss_service]',
				'selected'	=>	$selected,
			)
		);
		?>
		<label class="description" for="ftp-full-text-rss-service">
			Choose a full text RSS service.
			<br/>
			Free services are available for use instantly, with no registration required, but are known to occasionally be unreliable and slow.<br/>
			Paid and premium services provide maximum reliability and performance, and will require you to obtain an <b>API key</b>.
		</label>
		<?php
	}
	

	/**
	 * Renders the dropdown to configure the chosen full_text_rss_service
	 * 
	 * @since 2.7
	 */
	public function render_feeds_api_key( $args ) {
		$value = $this->get('feeds_api_key');
		?>
		<input type="password" name="<?php echo self::OPTIONS_NAME.'[feeds_api_key]'; ?>" id="wprss-ftp-feeds-api-key" value="<?php echo $value; ?>"/>
		<label class="description" for="wprss-ftp-feeds-api-key">
			Enter your FeedsAPI key. Don't have one? <a target="_blank" href="https://www.feedsapi.org/wpmayor.htm">Get one here (at a special discounted rate)</a>.
		</label>
		<?php
	}


	/**
	 * Renders the custom namespaces list option
	 * 
	 * @since 2.8
	 */
	public function render_custom_namespaces( $args ) {
		// Get the option value
		$namespaces = $this->get('user_feed_namespaces');

		// Parse with default values
		$namespaces = wp_parse_args(
			$namespaces,
			array(
				'names'	=>	array(),
				'urls'	=>	array()
			)
		);

		// PRINT SAVED NAMESPACES
		$remove_btn = '<button type="button" class="button-secondary wprss-ftp-namespace-remove"><i class="fa fa-trash-o"></i></button>';

		for ( $i = 0; $i < count( $namespaces['names'] ); $i++ ) {
			$name = $namespaces['names'][$i];
			$url = $namespaces['urls'][$i];
		
			echo '<div class="wprss-ftp-namespace-section">';
				echo '<input type="text" name="' . self::OPTIONS_NAME . '[user_feed_namespaces][names][]" value="' . esc_attr( $name ) . '" placeholder="Name" />';
				echo '<input type="text" name="' . self::OPTIONS_NAME . '[user_feed_namespaces][urls][]" value="' . esc_attr( $url ) . '" class="wprss-ftp-namespace-url" placeholder="URL" />';
				echo $remove_btn;
			echo '</div>';
		}
		?>
		
		<span id="wprss-ftp-namespaces-marker"></span>

		<button type="button" id="wprss-ftp-add-namespace" class="button-secondary">
			Add Another Namespace
		</button>

		<?php // Print the field template and the remove btn as a script variables
			$field_template = '<input type="text" name="'.self::OPTIONS_NAME.'[user_feed_namespaces]" value="" placeholder="" />';
		?>

		<p class="description">
			These namespaces are used for mapping RSS data into imported posts' meta data, in the <strong>Custom Fields</strong> section when creating/editing feed sources.
		</p>

		<script type="text/javascript">
			var wprss_namespace_input_template = "<?php echo addslashes( $field_template ); ?>";
			var wprss_namespace_remove_btn = "<?php echo addslashes( $remove_btn ); ?>";
		</script>
		
		<?php
	}


	/**
	 * Renders the license section text.
	 * 
	 * @since 1.0
	 */
	public function license_section_callback() {
		// Do nothing
	}


	/**
	 * Renders the license text field.
	 * 
	 * @since 1.0
	 */
	public function license_callback() {
		$license_keys = get_option( 'wprss_settings_license_keys' ); 
		$ftp_license_key = ( isset( $license_keys['ftp_license_key'] ) ) ? $license_keys['ftp_license_key'] : '';      
		echo "<input id='wprss-ftp-license-key' name='wprss_settings_license_keys[ftp_license_key]' type='text' value='" . esc_attr( $ftp_license_key ) ."' />";
		echo "<label class='description' for='wprss-ftp-license-key'>" . __( 'Enter your license key', 'wprss' ) . '</label>';
	}


	/**
	 * Renders the 'Activate License' button
	 * 
	 * @since 1.0
	 */
	public function license_activation_callback2() {
		$license_keys = get_option( 'wprss_settings_license_keys' ); 
		$license_statuses = get_option( 'wprss_settings_license_statuses' ); 
		$ftp_license_key = ( isset( $license_keys['ftp_license_key'] ) ) ? $license_keys['ftp_license_key'] : FALSE;
		$ftp_license_status = ( isset( $license_statuses['ftp_license_status'] ) ) ? $license_statuses['ftp_license_status'] : FALSE;
	
	   if( $ftp_license_status != FALSE && $ftp_license_status == 'valid' ) : ?>
			<span style="color:green;"><?php _e( 'active', 'wprss' ); ?></span>
			<?php wp_nonce_field( 'wprss_ftp_license_nonce', 'wprss_ftp_license_nonce' ); ?>
			<input type="submit" class="button-secondary" name="wprss_ftp_license_deactivate" value="<?php _e( 'Deactivate License', 'wprss' ); ?>"/>
		<?php else :
			wp_nonce_field( 'wprss_ftp_license_nonce', 'wprss_ftp_license_nonce' ); ?>
			<input type="submit" class="button-secondary" name="wprss_ftp_license_activate" value="<?php _e( 'Activate License', 'wprss' ); ?>"/>
		<?php endif;
	}


	/**
	 * Renders the activate/deactivate license button.
	 * 
	 * @since 1.0
	 */
	public function license_activation_callback() {
		$status = WPRSS_FTP::get_instance()->get_license_status();
		if ( $status === 'site_inactive' ) $status = 'inactive';
		
		$valid = $status == 'valid';
		$btn_text = $valid ? 'Deactivate License' : 'Activate License';
		$btn_name = 'wprss_ftp_license_' . ( $valid? 'deactivate' : 'activate' );
		wp_nonce_field( 'wprss_ftp_license_nonce', 'wprss_ftp_license_nonce' ); ?>

		<input type="submit" class="button-secondary" name="<?php echo $btn_name; ?>" value="<?php _e( $btn_text, 'wprss' ); ?>" />
		<span id="wprss-ftp-license-status-text">
			<strong>Status:
			<span class="wprss-ftp-license-<?php echo $status; ?>">
					<?php _e( ucfirst($status), 'wprss' ); ?>
					<?php if ( $status === 'valid' ) : ?>
						<i class="fa fa-check"></i>
					<?php elseif( $status === 'invalid' ): ?>
						<i class="fa fa-times"></i>
					<?php elseif( $status === 'inactive' ): ?>
						<i class="fa fa-warning"></i>
					<?php endif; ?>
				</strong>
			</span>
		</span>

		<style type="text/css">
			.wprss-ftp-license-valid {
				color: green;
			}
			.wprss-ftp-license-invalid {
				color: #b71919;
			}
			.wprss-ftp-license-inactive {
				color: #d19e5b;
			}
			#wprss-ftp-license-status-text {
				margin-left: 8px;
				line-height: 27px;
				vertical-align: middle;
			}
		</style>
	
		<?php
	}


#== SETTINGS VALIDATOR =================================================================================	

	public function validate_settings( $input ) {
		/**
		 * @todo Santize options
		 */
		$output = $input;

		// Check if the core settings are included in the POST data
		if ( isset( $_POST['wprss_settings_general'] ) && is_array( $_POST['wprss_settings_general'] ) ) {
			// get the option in the database
			$db_option = get_option( 'wprss_settings_general', array() );
			// update each suboption
			foreach( $_POST['wprss_settings_general'] as $key => $value ) {
				$db_option[$key] = $value;
			}
			// Update the option
			update_option( 'wprss_settings_general', $db_option );
		}

		// Check for missing values
		foreach ( $this->get_defaults() as $key => $def_value ) {
			if ( !array_key_exists( $key, $input ) ) {
				$output[$key] = 'false';
			}
		}

		return $output;
	}


#== CUSTOM RENDERERS ======================================================================================

	/**
	 * Renders the author settings
	 * 
	 * @since 1.9.3
	 */
	public function render_author_options( $post_id = NULL, $meta_row_title = '', $meta_label_for = '' ) {
		// Get the options
		$options = WPRSS_FTP_Settings::get_instance()->get_computed_options( $post_id );
		$def_author = ( $post_id !== NULL ) ? $options['def_author'] : $this->get( 'def_author' );
		$author_fallback_method = ( $post_id !== NULL ) ? $options['author_fallback_method'] : $this->get( 'author_fallback_method' );
		$fallback_author = ( $post_id !== NULL ) ? $options['fallback_author'] : $this->get( 'fallback_author' );
		$no_author_found = ( $post_id !== NULL ) ? $options['no_author_found'] : $this->get( 'no_author_found' );
		// Prepare required data
		$users = array_map( array( 'WPRSS_FTP_Settings', 'wprss_ftp_user_login' ), get_users() );
		$users_dropdown = array_combine( $users, $users );
		
		// Set the HTML tag ids
		$ids = array(
			'def_author'				=>	'ftp-def-author',
			'author_fallback_method'	=>	'ftp-author-fallback-method',
			'fallback_author'			=>	'ftp-fallback-author',
			'no_author_found'			=>	'ftp-no-author-skip'
		);
		// If in meta, copy the keys into the values
		if ( $post_id !== NULL ) {
			foreach ( $ids as $field => $id ) {
				$ids[$field] = $field;
			}
		}
		// Set the HTML tag names
		$names = array(
			'def_author'				=>	'def_author',
			'author_fallback_method'	=>	'author_fallback_method',
			'fallback_author'			=>	'fallback_author',
			'no_author_found'			=>	'no_author_found',
		);
		// Set the names appropriately according to the page, meta or settings
		foreach( $names as $field => $name) {
			$names[$field] = ( $post_id !== NULL )? WPRSS_FTP_Meta::META_PREFIX . $name : self::OPTIONS_NAME . "[$name]";
		}

		// If in meta, print the table row
		if ( $post_id !== NULL ) : ?>
			<tr>
				<th>
					<label for="<?php echo $meta_label_for; ?>">
						<?php echo $meta_row_title; ?>
					</label>
				</th>
				<td>
		<?php endif; ?>

		<!-- Author to use -->
		<span id="wprss-ftp-authors-options">
			<label for="<?php echo $ids['def_author']; ?>">Use </label>
			<?php echo WPRSS_FTP_Utils::array_to_select( WPRSS_FTP_Meta::get_users_array(), array(
					'id'		=>	$ids['def_author'],
					'name'		=>	$names['def_author'],
					'selected'	=>	$def_author,
			)); ?>
		</span>
		
		<!-- Separator -->
		<?php if ( $post_id !== NULL ) : ?>
			</td></tr>
			<tr class="wprss-tr-hr wprss-ftp-authors-hide-if-using-existing">
				<th>
				</th>
				<td>
		<?php endif; ?>
		
		<!-- Section that hides when using an existing user -->
		<span class="wprss-ftp-authors-hide-if-using-existing">
			
			<!-- Radio group if author has no user -->
			<span class="ftp-author-using-in-feed">
				<label for="<?php echo $ids['author_fallback_method']; ?>">
					<?php _e('If the author in the feed is not an existing user','wprss'); ?>:
				</label>
				<br/>
				<?php
					echo implode('', WPRSS_FTP_Utils::array_to_radio_buttons(
						array(
							'existing'	=> 'Use the fallback user',
							'create'	=> 'Create a user for the author'
						),
						array(
							'id'		=>	$ids['author_fallback_method'],
							'name'		=>	$names['author_fallback_method'],
							'checked'	=>	$author_fallback_method,
						)
					));
				?>
			</span>
			
			<!-- Radio group if author not found in feed -->
			<span class="ftp-author-using-in-feed">
				<label for="<?php echo $ids['no_author_found']; ?>">
					<?php _e('If the author is missing from the feed', 'wprss'); ?>
				</label>
				<br/>
				<?php
					echo implode( WPRSS_FTP_Utils::array_to_radio_buttons(
						array(
							'fallback'	=> 'Use the fallback user',
							'skip'		=> 'Do not import the post'
						),
						array(
							'id'		=>	$ids['no_author_found'],
							'name'		=>	$names['no_author_found'],
							'checked'	=>	$no_author_found,
						)
					));
				?>
			</span>
		</span>
		
		
		<?php if ( $post_id !== NULL ) : ?>
			</td></tr>
			<tr class="wprss-tr-hr wprss-ftp-authors-hide-if-using-existing">
				<th>
					<label for="<?php echo $ids['fallback_author']; ?>">Fallback User</label>
				</th>
				<td>
		<?php endif; ?>
		
		<!-- Section that hides when using an existing user -->
		<span class="wprss-ftp-authors-hide-if-using-existing">
			<?php if ( $post_id === NULL ) : ?>
			<label for="<?php echo $ids['fallback_author']; ?>">Fallback user:</label>
			<?php endif; ?>
			<?php
				echo WPRSS_FTP_Utils::array_to_select( $users_dropdown, array(
					'id'		=>	$ids['fallback_author'],
					'name'		=>	$names['fallback_author'],
					'selected'	=>	$fallback_author,
				));
			?>
			<br/><label class="description">This user is used if the plugin fails to determine an author or user.</label>
		</span>
					
		<?php // Add scripts ?>

		<script type="text/javascript">
			(function($){
				$(document).ready( function(){

					// Set a pointer to the dropdowns
					var dropdown1 = $('#<?php echo $ids['def_author']; ?>');

					// Create the function that shows/hides the second section
					var authorsSection2UI = function(){
						// Show second section only if the option to use the author in the feed is chosen
						$('.wprss-ftp-authors-hide-if-using-existing').toggle( dropdown1.val() === '.' );
					}

					// Set the on change handlers
					dropdown1.change( authorsSection2UI );

					// Run the function at least once
					authorsSection2UI();

				});
			})(jQuery);
		</script>
		<?php // End of scripts

		// If in meta, close the table row
		if ( $post_id !== NULL ) {
			?></td></tr><?php
		}
	}


#== PAGE RENDERER ======================================================================================


	/** 
	* Add settings fields and sections
	* @since 1.0
	*/
	public function render_settings_page( $active_tab ) {
		if ( $active_tab === self::TAB_SLUG ) {
			# Render all sections for this page
			settings_fields( 'wprss_settings_ftp' );
			do_settings_sections( 'wprss_settings_ftp' );
		}
	}


#== ADD AGGREGATOR TAB =================================================================================

	/** 
	* Add a settings tabs for the Feed-to-Post add-on on the Settings page
	*
	* @since 1.0
	*/
	public function add_tab( $args ) {
		$args['ftp'] = array(
			'label' => __( 'Feed to Post', 'wprss' ),
			'slug' => self::TAB_SLUG
		);  
		return $args;
	}


#== MISC ===============================================================================================
	
	/**
	 * Returns the registered post types.
	 *
	 * @since 2.9.5
	 */
	public static function get_post_types() {
		// Get all post types, as objects
		$post_types = get_post_types( array(), 'objects' );
		// Remove the blacklist CPT
		unset( $post_types['wprss_blacklist'] );
		// If not using the legacy feed items, remove them
		if ( !wprss_ftp_using_feed_items() ) {
			unset( $post_types['wprss_feed_item'] );
		}
		// Return the list, mapping the post type objects to their singular name
		return array_map( array( __CLASS__, 'post_type_singular_name' ), $post_types );
	}


	/**
	 * Returns the singular name for the given post type object.
	 * Used as a callback for array_map calls.
	 *
	 * @since 2.9.5
	 */
	public static function post_type_singular_name( $post_type ) {
		return $post_type->labels->singular_name;
	}
	
	
	public static function get_post_formats() {
		return array(
			'standard'	=>	'Standard',
			'aside'		=>	'Aside',
			'chat'		=>	'Chat',
			'link'		=>	'Link',
			'quote'		=>	'Quote',
			'status'	=>	'Status',
			'audio'		=>	'Audio',
			'image'		=>	'Image',
			'video'		=>	'Video',
			'gallery'	=>	'Gallery'
		);
	}


	public static function get_post_statuses() {
		return array(
			'publish'	=>	'Published',
			'draft'		=>	'Draft',
			'private'	=>	'Private',
			'pending'	=>	'Pending Review'
		);
	}


	/* The following functions are used as filters for array_map function calls, to return specific user data. */
	private static function wprss_ftp_user_id( $user ) {
		return $user->ID;
	}
	private static function wprss_ftp_user_login( $user ) {
		return $user->user_login;
	}
	private static function wprss_ftp_term_slug( $term ) {
		return $term->slug;
	}
	private static function wprss_ftp_term_name( $term ) {
		return $term->name;
	}


	/**
	 * Returns an array of users on the site.
	 *
	 * Rewritten as of 2.0, due to various bugs.
	 * 
	 * @param $assoc	boolean		If true, an associative array of user ids pointing to user logins is
	 *								returned. If false, a regular array of user logins is returned.
	 * @since 2.0
	 */
	public static function get_users( $assoc = true ) {
		// Get all users
		$users = get_users();
		// Get the user logins and ids
		$user_logins = array_map( array( 'WPRSS_FTP_Settings', 'wprss_ftp_user_login' ), $users );
		$user_ids = array_map( array( 'WPRSS_FTP_Settings', 'wprss_ftp_user_id' ), $users );

		// If the assoc param is true, return an associative array of user keys pointing to their logins.
		// Otherwise, return just an array with the user logins.
		$user_array = ( $assoc === TRUE )? array_combine( $user_ids, $user_logins ) : $user_logins;

		return $user_array;
	}


	public static function get_term_names( $taxonomy, $args = array(), $assoc = true ) {
		$args['fields'] = 'all';
		$term_objs = get_terms( $taxonomy, $args );
		if ( is_wp_error( $term_objs ) ) {
			return NULL;
		}

		$term_slugs = array_map(  array( 'WPRSS_FTP_Settings', 'wprss_ftp_term_slug' ) , $term_objs );
		$term_names =  array_map(  array( 'WPRSS_FTP_Settings', 'wprss_ftp_term_name' ) , $term_objs );

		if ( $assoc === true ) {
			if ( is_array( $term_names ) && count( $term_names ) > 0 ) {
				$term_names = array_combine( $term_slugs, $term_names );
			}
			else {
				$term_names = array();
			}
		}
		return $term_names;
	}


	public static function get_post_date_options() {
		return array(
			'original'		=>	'Original post date',
			'imported'		=>	'Feed import date'
		);
	}


	/**
	 * Returns the options for the full_text_rss_service option
	 *
	 * @since 2.7
	 */
	public static function get_full_text_rss_service_options() {
		return apply_filters(
			'wprss_ftp_full_text_rss_service_options',
			array(
				'free'			=>	'Free Services',
				'feeds_api'		=>	'FeedsAPI service'
			)
		);
	}


	/**
	 * Returns the array of default namespaces
	 * 
	 * @since 2.8
	 */
	public static function get_default_namespaces() {
		return apply_filters(
			'wprss_ftp_default_namespaces',
			array(
				'None'		=>	'',
			)
		);
	}


	/**
	 * Returns the array of namespaces available.
	 * 
	 * @since 2.8
	 */
	public static function get_namespaces() {
		// The default namespaces
		$def_namespaces = self::get_default_namespaces();

		// Change the array into the same format as the user saved namespaces
		$def_namespaces = array(
			'names' =>	array_keys( $def_namespaces ),
			'urls'	=>	array_values( $def_namespaces ),
		);

		// Get the namespaces added by the user
		$user_namespaces = self::get_instance()->get( 'user_feed_namespaces' );
		if ( !is_array($user_namespaces) || count($user_namespaces) === 0 ) {
			$user_namespaces = self::get_instance()->get_default( 'user_feed_namespaces' );
		}

		// Return both as 1 array
		return array(
			'names'		=>	array_merge( $def_namespaces['names'], $user_namespaces['names'] ),
			'urls'		=>	array_merge( $def_namespaces['urls'], $user_namespaces['urls'] ),
		);
	}


	/**
	 * Gets the Namespace URL for the given Namespae Name
	 * 
	 * @since 2.8
	 */
	public static function get_namespace_url( $namespace ) {
		// Get the namespaces array setting
		$namespaces = self::get_namespaces();

		// Search for the index of the namespace name given in the 'names' subarray
		$i = array_search( $namespace, $namespaces['names'] );
		// Return null if the namespace was not found
		if ( $i === FALSE ) return NULL;

		// Use the index to return the URL from the 'urls' subarray
		return ( !isset( $namespaces['urls'][$i] ) )? NULL : $namespaces['urls'][$i];
	}

} // End of Settings Class
