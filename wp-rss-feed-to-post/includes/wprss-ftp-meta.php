<?php
class WPRSS_FTP_Meta {
	
/*===== CONSTANTS AND STATIC MEMBERS ======================================================================*/

	/**
	 * The Meta data field prefix
	 */
	const META_PREFIX = 'wprss_ftp_';


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
			// Check if WPML is active, and add a multi language meta box
			if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
				add_filter( 'wprss_ftp_meta_fields', array( $this, 'add_multilanguage_metabox' ), 10 , 1 );
			}
			# Initialize
			add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
			add_action( 'save_post', array( $this, 'save_post_meta' ), 5, 2 );
			# Change core meta option
			add_filter( 'wprss_fields', array( $this, 'change_core_meta_fields' ) );
		} else {
			wp_die( "WPRSS_FTP_Meta class is a singleton class and cannot be redeclared." );
		}
	}

	/**
	 * Returns the singleton instance
	 */
	public static function get_instance() {
		if ( self::$instance === NULL ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


#=== META SETTERS / GETTERS ===============================================================================


	/**
	 * Returns the FTP related meta data
	 * 
	 * @since 1.0
	 */
	public function get( $post_id, $meta_key, $use_prefix = true ) {
		return $this->get_meta( $post_id, $meta_key, $use_prefix );
	}
	public function get_meta( $post_id, $meta_key, $use_prefix = true ) {
		$prefix = ( $use_prefix === TRUE )? WPRSS_FTP_Meta::META_PREFIX : '';
		return get_post_meta( $post_id,  $prefix . $meta_key, TRUE );
	}


	/**
	 * Adds the given meta data to a post.
	 * Prepend an exclamation mark to the key to exclude the meta prefix.
	 * 
	 * @since 1.0
	 */
	public function add_meta( $post_id, $meta, $value = '' ) {
		if ( is_array( $meta ) ){
			foreach ( $meta as $key => $value) {
				# If the key starts with a '!', do not add the prefix to the key
				$meta_key = ( $key[0] === '!' )? substr( $key, 1 ) : WPRSS_FTP_Meta::META_PREFIX . $key;
				# Add the meta to the database
				update_post_meta( $post_id, $meta_key, $value );
			}
		}
		else {
			update_post_meta( $post_id, WPRSS_FTP_Meta::META_PREFIX . $meta, $value );
		}
	}


#=== META BOXES REGISTRATION =============================================================================

	/**
	 * Registers the meta boxes for the 'New Feed Source' page.
	 *
	 * @since 1.0
	 */
	public function add_meta_boxes() {
		#add_meta_box(
		#	'wprss-ftp-general-metabox',						// $id
		#	__( 'Feed to Post - General', 'wprss' ),			// $title 
		#	array( $this, 'render_general_metabox' ),			// $callback
		#	'wprss_feed',										// $page
		#	'normal',											// $context
		#	'high'                                   			// $priority
		#);
		#add_meta_box(
		#	'wprss-ftp-images-metabox',							// $id
		#	__( 'Feed to Post - Images', 'wprss' ),				// $title
		#	array( $this, 'render_images_metabox' ),			// $callback
		#	'wprss_feed',										// $page
		#	'normal',											// $context
		#	'high'                                   			// $priority
		#);
		add_meta_box(
			'wprss-ftp-taxonomy-metabox',						// $id
			__( 'Taxonomias', 'wprss' ),			// $title 
			array( $this, 'render_taxonomy_metabox' ),			// $callback
			'wprss_feed',										// $page
			'normal',											// $context
			'high'                                   			// $priority
		);
		#add_meta_box(
		#	'wprss-ftp-author-metabox',							// $id
		#	__( 'Feed to Post - Author', 'wprss' ),				// $title 
		#	array( $this, 'render_author_metabox' ),			// $callback
		#	'wprss_feed',										// $page
		#	'normal',											// $context
		#	'high'												// $priority
		#);
		#add_meta_box(
		#	'wprss-ftp-prepend-metabox',						// $id
		#	__( 'Feed to Post - Prepend To Content', 'wprss' ),		// $title 
		#	array( $this, 'render_prepend_metabox' ),			// $callback
		#	'wprss_feed',										// $page
		#	'normal',											// $context
		#	'low'												// $priority
		#);
		#add_meta_box(
		#	'wprss-ftp-append-metabox',							// $id
		#	__( 'Feed to Post - Append To Content', 'wprss' ),	// $title 
		#	array( $this, 'render_append_metabox' ),			// $callback
		#	'wprss_feed',										// $page
		#	'normal',											// $context
		#	'low'												// $priority
		#);
		#add_meta_box(
		#	'wprss-ftp-extraction-metabox',						// $id
		#	__( 'Feed to Post - Extraction Rules', 'wprss' ),	// $title 
		#	array( $this, 'render_extraction_metabox' ),		// $callback
		#	'wprss_feed',										// $page
		#	'normal',											// $context
		#	'low'												// $priority
		#);
		#add_meta_box(
		#	'wprss-ftp-custom-fields-metabox',					// $id
		#	__( 'Feed to Post - Custom Field Mapping', 'wprss' ),		// $title 
		#	array( $this, 'render_custom_fields_metabox' ),		// $callback
		#	'wprss_feed',										// $page
		#	'normal',											// $context
		#	'low'												// $priority
		#);
		if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
			add_meta_box(
				'wprss-ftp-multi-language-metabox',					// $id
				__( 'Feed to Post - Multi Language', 'wprss' ),		// $title 
				array( $this, 'render_multi_language_metabox' ),	// $callback
				'wprss_feed',										// $page
				'normal',											// $context
				'high'                                   			// $priority
			);
		}


		// Removes the 'Featured Image' meta box, and re-adds it with custom text
		#remove_meta_box( 'postimagediv', 'wprss_feed', 'side' );    
        #add_meta_box(
        #	'postimagediv',
        #	__( 'Default Thumbnail' ),
        #	array( $this, 'post_thumbnail_meta_box' ),
        #	'wprss_feed',
        #	'side',
        #	'low'
        #);
	}


#=== META BOXES AND META FIELDS =============================================================================

	/**
	 * Returns the Meta fields used in the meta boxes
	 *
	 * @since 1.0
	 */
	public function get_meta_fields( $what = '', $source_id = null ) {
		$fields = array(
			#== General Metabox fields ===================
			'general'	=> 	array(
				#== Post Site ==
				'post_site' => array(
					'label'		=>	'Post to site',
					'desc'		=>	'Choose the site to import posts to',
					'custom_render'	=>	array( $this, 'render_post_site_dropdown' )
				),
				#== Post Type ==
				'post_type' => array(
					'label'		=>	'Post Type',
					'desc'		=>	'Choose the post type you want to import the imported feeds to',
					'type'		=>	'dropdown',
					'options'	=>	self::get_post_types($source_id)
				),
				#== Post Status =====
				'post_status' => array(
					'label'		=>	'Post Status',
					'desc'		=>	'Choose the initial post status for imported feeds',
					'type'		=>	'dropdown',
					'options'	=>	WPRSS_FTP_Settings::get_post_statuses()
				),
				#== Post Format =====
				'post_format' => array(
					'label'		=>	'Post Format',
					'desc'		=>	'Choose the post format to assign to imported feeds.',
					'type'		=>	'dropdown',
					'options'	=>	WPRSS_FTP_Settings::get_post_formats()
				),
				#== Post Date =====
				'post_date' => array(
					'label'		=>	'Post Date',
					'desc'		=>	'Choose the date to use for imported posts.',
					'type'		=>	'dropdown',
					'options'	=>	WPRSS_FTP_Settings::get_post_date_options()
				),
				#== Comment Status =====
				'comment_status' => array(
					'label'		=>	'Enable Comments',
					'desc'		=>	"Tick this box to enable comments for imported posts from this source",
					'type'		=>	'checkbox'
				),
				/* @deprecated
				#== Source Link =====
				'source_link' => array(
					'label'		=>	'Link back to source?',
					'desc'		=>	"Tick this box to add a link back to the original post, at the end of the post's content",
					'type'		=>	'checkbox',
				),
				#== Source Link Text =====
				'source_link_text' => array(
					'label'		=>	'Source Link Text',
					'desc'		=>	"Enter the text to use when linking back to the original post source.
									<br/>Wrap a phrase in asterisk symbols ( <b>*link to Generalpost*</b> ) to turn it into the link to the <b>original post</b>,
									<br/>or in double asterisk symbols ( <b>**link to source**</b> ) to turn it into a link to the post <b>feed source</b>.",
					'type'		=>	'text'
				),
				#== Source Link Text =====
				'source_link_text' => array(
					'label'		=>	'Source Link Text',
					'desc'		=>	"<del>Enter the text to use when linking back to the original post source.</del><br/>
									<span style='color: #aa4444'>This option has been replaced by the <b>Append to Content</b> option.<br/>
									Read more <a target='_blank' href='http://wprssaggregator.com'>here</a>.</span>",
					'type'		=>	'text',
					'properties'=>	'disabled',
				),
				*/
				#== Force full content =====
				'force_full_content' => array(
					'label'		=>	'Force full content',
					'desc'		=>	'Check this box to forcefully attempt to retrieve the full feed content, if the feed only provides excerpts.',
					'type'		=>	'checkbox'
				),
				#== Allow embedded content =====
				'allow_embedded_content' => array(
					'label'		=>	'Allow Embedded Content',
					'desc'		=>	'Check this box to allow embedded content in posts (<code>iframe</code>, <code>embed</code> and <code>object</code> content).',
					'type'		=>	'checkbox'
				),
			),

			#== Taxonomy and Terms Metabox fields ===================
			'tax'	=>	array(
				#== Post Taxonomy ==========
				'post_taxonomy' => array(
					'label'		=>	'Tipo',
					'desc'		=>	'',
					'type'		=>	'msg',
					'text'		=>	'Aguarda un ratin ...'
				),
				#== Post Taxonomy ==========
				'post_terms' => array(
					'label'		=>	'',
					'desc'		=>	'',
					'type'		=>	'msg',
					'text'		=>	'Aguarda un ratin ...'
				),
				'post_tags' => array(
					'label'			=>	'Tags',
					'desc'			=>	'',
					'type'			=>	'text',
					'placeholder'	=>	'',
					'settings'		=>	FALSE		# Ignore the value in the global settings
				),
				#'post_auto_tax_terms' => array(
				#	'label'		=>	'Auto create terms',
				#	'desc'		=>	'Check this box to automatically create terms for the taxonomy selected above.',
				#	'type'		=>	'checkbox',
				#),
			),

			#== Author Metabox fields ===================
			'author'	=>	array(
				#== Default Author Type =====
				'def_author' => array(
					'label'			=>	'Post Author',
					'desc'			=>	'Choose the author to use for imported feeds',
					'type'			=>	'dropdown',
					'ignore'		=>	TRUE,
				),
				#== Author Fallback Method =====
				'author_fallback_method' => array(
					'label'		=>	'If feed author does not exist',
					'desc'		=>	'If the above option is set to get the author from the feed, choose what to do when the feed author is not a user on the site.',
					'type'		=>	'none',
					'ignore'	=>	TRUE,
				),
				#== No Author Found =====
				'no_author_found' => array(
					'label'		=>	'Fallback Author',
					'desc'		=>	'',
					'type'		=>	'none',
					'ignore'	=>	TRUE,
				),
				#== Fallback Author =====
				'fallback_author' => array(
					'label'		=>	'Fallback Author',
					'desc'		=>	'Choose the user to use if the above option is set to "Use Existing", or if the feed does not specify an author.',
					'type'		=>	'dropdown',
					'options'	=>	WPRSS_FTP_Settings::get_users(),
					'ignore'	=>	TRUE,
				)
			),


			#== Prepend / Append Metabox fields ===================
			'prepend'	=>	array(
				'post_prepend' => array(
					'label'			=>	'Prepend text to post content',
					'desc'			=>	'Use the following placeholders to replace with the post\'s details: <br />(Hover over them for a description of what they represent)',
					'type'			=>	'editor',
					'custom_render'	=>	array( $this, 'render_post_prepend_editor' ),
					'settings'		=>	FALSE		# Ignore the value in the global settings
				),
			),
			'append'	=>	array(
				'post_append' => array(
					'label'			=>	'Append text to post content',
					'desc'			=>	'Use the following placeholders to replace with the post\'s details: <br />(Hover over them for a description of what they represent)',
					'type'			=>	'editor',
					'custom_render'	=>	array( $this, 'render_post_append_editor' ),
					'settings'		=>	FALSE		# Ignore the value in the global settings
				),
			),


			#== Extraction Rules Metabox fields ===================
			'extraction'	=>	array(
				'extraction_rules'	=>	array(
					'label'				=>	'Extraction Rules',
					'desc'				=>	'For each extraction rule, you will need to enter the:

											<ul id="wprss-ftp-extraction-rules-desc">
												<li>
													<strong>CSS Selector:</strong>
													Enter the CSS selector(s) for the HTML element(s) you want to manipulate
												</li>
												<li>
													<strong>Manipulation:</strong>
													Choose what you want to do with the matching element(s)
												</li>
											</ul>
											',
					'type'				=>	'none',
					'custom_render'		=>	array( $this, 'render_extraction_rules' ),
					'settings'			=>	FALSE,
					'manip_options'		=>	array(
						'remove'				=>	'Remove the matches element(s)',
						'remove_keep_children'	=>	'Remove element(s), but keep contents',
						'keep'					=>	'Keep only the matched element(s)'
					),
				),
				'extraction_rules_types' => array(
					'ignore'			=>	TRUE,
				),
			),

			#== Custom Field Mapping Metabox fields ===================
			'custom_fields'	=>	array(
				// The RSS tags to get from RSS
				'rss_tags'			=>	array(
					'ignore'		=>	TRUE,
				),
				// The RSS Namespaces to use for each tag
				'rss_namespaces'	=>	array(
					'ignore'		=>	TRUE,
				),
				// The custom meta field names to which to import
				'custom_fields'		=>	array(
					'label'			=>	'Custom Field Mapping',
					'desc'			=>	'For each mapping, you will need to enter the:

										<ul id="wprss-ftp-custom-fields-desc">
											<li>
												<strong>Namespace:</strong>
												Choose the namespace of the tag that you wish to read.<br/>
												You can add or manage your namespaces from the
												<a href="' . admin_url() . 'edit.php?post_type=wprss_feed&page=wprss-aggregator-settings&tab=ftp_settings#wprss-ftp-add-namespace" target="wprss_ftp_settings">
													Feed to Post settings page.
												</a>
											</li>

											<li>
												<strong>RSS Tag:</strong>
												Enter the name of the tag in the RSS feed, excluding the namespace prefix.<br/>
												<em>For instance,</em> for iTunes artist tag, use just <code>artist</code>, <strong>not</strong> <code>im:artist</code>.
											</li>

											<li>
												<strong>Meta field name:</strong>
												Enter the name of the post meta field, where you wish to store the data.
											</li>
										</ul>',
					'type'			=>	'none',
					'custom_render'	=>	array( $this, 'render_custom_fields' ),
					'settings'		=>	FALSE,
					'namespaces'	=>	WPRSS_FTP_Settings::get_instance()->get('user_feed_namespaces')
				)
			)
		);
		$fields = apply_filters( 'wprss_ftp_meta_fields', $fields );

		if ( $what !== '' && $what !== 'all' )
			return $fields[$what];

		if ( strtolower( $what ) === 'all' ) {
			$flattened = array();
			foreach ( $fields as $id => $section_fields ) {
				$flattened += $section_fields;
			}
			return $flattened;
		}

		return $fields;
	}


	/**
	 * Adds a multi language metabox
	 * 
	 * @since 1.3
	 */
	public function add_multilanguage_metabox( $fields ) {
		$languages = WPRSS_FTP_Utils::get_wpml_languages();
		$fields['multi-language'] = array(
			'post_language'	=>	array(
				'label'			=>	'Post Language',
				'desc'			=>	'Choose the language to assign to posts imported from this source',
				'type'			=>	'dropdown',
				'options'		=>	$languages
			)
		);
		return $fields;
	}


#== META BOX RENDERERS =================================================================================


	public function render_general_metabox(){ $this->render_metabox('general'); }
	public function render_taxonomy_metabox() { $this->render_metabox('tax');  }
	public function render_images_metabox() { $this->render_metabox('images');  }
	public function render_prepend_metabox() { $this->render_metabox('prepend');  }
	public function render_append_metabox() { $this->render_metabox('append');  }
	public function render_extraction_metabox() { $this->render_metabox('extraction');  }
	public function render_custom_fields_metabox() { $this->render_metabox('custom_fields');  }
	public function render_multi_language_metabox() { $this->render_metabox('multi-language');  }
	public function render_author_metabox() {
		//$this->render_metabox('author');
		global $post;
		?>
			<table class="form-table wprss-form-table">
				<tbody>
					<?php WPRSS_FTP_Settings::get_instance()->render_author_options( $post->ID, 'Post Author', 'def_author' ); ?>
				</tbody>
			</table>
		<?php
	}

	/**
	 * Renders the meta box specified by the parameter.
	 * The function will use the get_meta_fields function to retrieve the fields for
	 * that particular meta box and render them.
	 *
	 * @since 1.0
	 */
	public function render_metabox( $metabox ) {
		global $post;

		# The main metabox template
		ob_start(); ?>
			<table class="form-table wprss-form-table">
				<tbody>
					{{fields}}
				</tbody>
			</table>
		<?php $template = ob_get_clean();
		

		# The field template to use for all fields
		ob_start(); ?>
			<tr {{hr}}>
				<th><label for="{{id}}">{{label}}</label></th>
				<td>
					{{input}}
					{{separator}}<label class="description" for="{{id}}">{{desc}}</label>
				</td>
			</tr>
		<?php $field_template = ob_get_clean();


		# Generate the fields HTML using the template
		$meta_fields = $this->get_meta_fields( $metabox, $post->ID );
		$fields = '';
		$options = WPRSS_FTP_Settings::get_instance()->get_computed_options( $post->ID );

		# Render each field
		foreach ( $meta_fields as $field_id => $field ) {
			if ( isset( $field['ignore']) && $field['ignore'] === TRUE ) {
				continue;
			}

			$hr = ( isset( $field['add_hr'] ) && $field['add_hr'] === TRUE )? 'class="wprss-tr-hr"': '';

			$field_html = '';

			$id = self::META_PREFIX . $field_id;
			# Get the meta value for this field, if it exists
			$nid = substr( $id, strlen( self::META_PREFIX ) );
			$meta = $options[ $nid ];
			if ( isset( $field['settings'] ) && $field['settings'] === FALSE ) {
				$meta = get_post_meta( $post->ID, $id, true );
			}
			if ( isset( $field['default'] ) && $meta === '' ) {
				$meta = $field['default'];
			}

			if ( isset( $field['custom_render'] ) && !empty( $field['custom_render'] ) ) {
				$field_html = call_user_func_array( $field['custom_render'], array( $post, $field_id, $field, $meta ) );
			}
			else {

				$separator = '<br/>';

				# Generate the field input
				$field_input = '';

				$field_type_templates = array(
					'text'		=> '<input type="text" id="{{id}}" name="{{name}}" value="{{value}}" placeholder="{{placeholder}}" {{properties}}/>',
					'number' 	=> '<input class="wprss-number-roller" type="number" id="{{id}}" name={{name}} min="0" placeholder="{{placeholder}}" value="{{value}}" {{properties}}/>',
					'textarea'	=> '<textarea id="{{id}}" name="{{name}}" cols="60" rows="4" {{properties}}>{{value}}</textarea>'
				);

				switch( $field['type'] ) {
					default:
					case 'text':
					case 'number':
					case 'textarea':
						// If the field is a textarea, and the meta value saved in DB is an array
						if ( $field['type'] === 'textarea' && is_array( $meta ) ) {
							// split the array into strings
							$new_meta = '';
							foreach ( $meta as $entry ) {
								$new_meta .= $entry . "\n";
							}
							$meta = $new_meta;
						}
						$substitutions = array(
							'id'			=>	$id,
							'name'			=>	$id,
							'value'			=>	trim( esc_attr( $meta ) ),
							'placeholder'	=>	( isset( $field['placeholder'] )? $field['placeholder'] : 'Default' ),
							'properties'	=>	( isset( $field['properties'] )? $field['properties'] : '' ),
						);
						$field_input = WPRSS_FTP_Utils::template( $field_type_templates[ $field['type'] ], $substitutions );
						break;
					case 'checkbox':
						$field_input = WPRSS_FTP_Utils::boolean_to_checkbox(
							WPRSS_FTP_Utils::multiboolean( $meta ),
							array(
								'id'		=>	$id,
								'name'		=>	$id,
								'class'		=>	'meta-checkbox',
								'value'		=>	'true',
								'disabled'	=>	( isset( $field['disabled'] )? $field['disabled'] : FALSE ),
							)
						);
						$separator = '';
						break;
					case 'msg':
						$field_input = '<p id="'.$id.'">'.$field['text'].'</p>';
						break;
					case 'dropdown':
						$field_input = WPRSS_FTP_Utils::array_to_select(
							$field['options'],
							array(
								'id'		=> $id,
								'name'		=> $id,
								'selected'	=> $meta,
								'disabled'	=>	( isset( $field['disabled'] )? $field['disabled'] : FALSE ),
							)
						);
						break;
				}
				# Finish the field using the template
				$field_html = WPRSS_FTP_Utils::template(
					$field_template,
					array(
						'id'		=>	$id,
						'input'		=>	$field_input,
						'label'		=>	$field['label'],
						'desc'		=>	$field['desc'],
						'separator'	=>	$separator,
						'hr'		=>	$hr,
					)
				);

			} // End of if statement that checks if using a custom renderer

			$fields .= $field_html;
		}

		echo str_replace( '{{fields}}', $fields, $template );
		echo '<span data-post-id="'.$post->ID.'" id="wprss-ftp-post-id"></span>';
	}


	/**
	 * Renders the image minimum dimensions meta fields.
	 * 
	 * @since 1.0
	 */ 
	public function render_image_min_dimensions( $post ) {
		ob_start();
		$options = WPRSS_FTP_Settings::get_instance()->get_computed_options( $post->ID );
		$width = $options['image_min_width'];
		$height = $options['image_min_height'];
		$width_name = self::META_PREFIX . 'image_min_width';
		$height_name = self::META_PREFIX . 'image_min_height';
		?>

		<tr>
			<th>
				<label>Image minimum dimensions</label>
			</th>

			<td>
				<input class="wprss-number-roller" type="number" id="<?php echo $width_name; ?>"  name="<?php echo $width_name; ?>" min="0" placeholder="Default" value="<?php echo $width ;?>" />
				x
				<input class="wprss-number-roller" type="number" id="<?php echo $height_name; ?>" name="<?php echo $height_name; ?>" min="0" placeholder="Default" value="<?php echo $height ;?>" />
				<br/>
				<label class="description">
					Images chosen to be featured images must be equal to or larger than the size given above (width and height in pixels).
				</label>				
			</td>
		</tr>

		<script type="text/javascript">
			(function($){
				$(document).ready( function(){
					
					// Get pointers to elements
					var wprss_ftp_use_featured_image = $( '#<?php echo self::META_PREFIX; ?>use_featured_image' );
					var wprss_ftp_featured_image = $( '#<?php echo self::META_PREFIX; ?>featured_image' );
					var wprss_ftp_dimension_fields = $( '#<?php echo $width_name; ?>, #<?php echo $height_name; ?>' );
					var wprss_ftp_remove_ft_image = $( '#<?php echo self::META_PREFIX; ?>remove_ft_image' );

					// Prepare the check function
					var wprss_ftp_image_dimensions_check_function = function(){
						var is_unchecked = !wprss_ftp_use_featured_image.is( ':checked' );
						// disabled the fields
						wprss_ftp_featured_image.prop( 'disabled', is_unchecked );
						wprss_ftp_dimension_fields.prop( 'disabled', is_unchecked );
						wprss_ftp_remove_ft_image.prop( 'disabled', is_unchecked );
						// Grey out the other elements
						wprss_ftp_featured_image.parent().toggleClass( 'wprss-ftp-inactive-meta', is_unchecked ).prev().toggleClass( 'wprss-ftp-inactive-meta', is_unchecked );
						wprss_ftp_dimension_fields.parent().toggleClass( 'wprss-ftp-inactive-meta', is_unchecked ).prev().toggleClass( 'wprss-ftp-inactive-meta', is_unchecked );
						wprss_ftp_remove_ft_image.parent().toggleClass( 'wprss-ftp-inactive-meta', is_unchecked ).prev().toggleClass( 'wprss-ftp-inactive-meta', is_unchecked );
					}

					// run the check on page load
					wprss_ftp_image_dimensions_check_function();

					// run the check when the use_featured_image checkbox is clicked
					wprss_ftp_use_featured_image.click( wprss_ftp_image_dimensions_check_function );

				});
			})(jQuery);
		</script>

		<?php
		return ob_get_clean();
	}


	/**
	 * Renders the prepend to post editor
	 * 
	 * @since 1.6
	 */
	public function render_post_prepend_editor( $post, $field_id, $field, $meta ) {
		ob_start();
		$prepend = $this->get_meta( $post->ID, 'post_prepend' );
		?>
			<tr>
				<td>

					<!-- PREPENDER -->
					<div id="post-prepend-container">
						<div id="post-prepend-editor-container">
							<p>Add text at the beginning of posts' content.</p>
							<?php
								$editor_settings = array(
									'media_buttons'		=>	FALSE,
									'textarea_name'		=>	self::META_PREFIX . 'post_prepend',
								);
								wp_editor( $prepend, 'wprsspostprepend', $editor_settings ); ?>
						</div>
					</div>

					<div id="post-prepend-placeholder">
						<p><span class="description"><?php echo $field['desc']; ?></span></p>
						<br/>
						<?php $this->get_append_placeholders(); ?>
					</div>

				</td>
			</tr>
		<?php
		return ob_get_clean();
	}


	/**
	 * Renders the append to post editor
	 * 
	 * @since 1.6
	 */
	public function render_post_append_editor( $post, $field_id, $field, $meta ) {
		ob_start();
		$append = $this->get_meta( $post->ID, 'post_append' );
		?>
			<tr>
				<td>

					<!-- APPENDER -->
					<div id="post-append-container" class="hidden">
						<div id="post-append-editor-container">
							<p>Add text at the end of posts' content.</p>
							<?php
								$editor_settings = array(
									'media_buttons'		=>	FALSE,
									'textarea_name'		=>	self::META_PREFIX . 'post_append',
								);
								wp_editor( $append, 'wprsspostappend', $editor_settings ); ?>
						</div>
					</div>

					<div id="post-prepend-placeholder">
						<p><span class="description"><?php echo $field['desc']; ?></span></p>
						<br/>
						<?php $this->get_append_placeholders(); ?>
					</div>

				</td>
			</tr>
		<?php
		return ob_get_clean();
	}


	/**
	 * Renders the extraction rules settings
	 * 
	 * @since 2.6
	 */
	public function render_extraction_rules( $post, $field_id, $field, $meta ) {
		$id = self::META_PREFIX . $field_id;
		$desc = $field['desc'];

		if ( !is_array( $meta ) ) {
			$meta = WPRSS_FTP_Extractor::get_extraction_rules( $post->ID );
		}

		// Set the rules to an empty array, if there is only 1 empty rule
		if ( count($meta) === 1 && $meta[0] === '' ) {
			$meta = array();
		}

		// Get the manipulation types meta value
		$manip_types_meta = self::get_meta( $post->ID, 'extraction_rules_types' );
		// If it is not an array, set it to an empty array
		if ( ! is_array( $manip_types_meta ) ) {
			$manip_types_meta = array();
		}

		//== PREPARE THE HTML ===

		// The class of a each section
		$input_section_class = 'wprss-ftp-extraction-rule-section';
		// The text field for each section
		$input_field = '<input type="text" name="' . self::META_PREFIX . 'extraction_rules[]" value="{{value}}" /> ';
		// The button for each section
		$remove_btn = '<button type="button" class="button-secondary wprss-ftp-extraction-rule-remove"><i class="fa fa-trash-o"></i></button>';
		// The manipulation type dropdown
		$manip_dropdown = '{{manip_types}}';
		// The whole section
		$input_section = "<div class='$input_section_class'>$input_field $manip_dropdown $remove_btn</div>";


		// For each extraction rule, print out a section
		$input = '';
		$i = 0;
		foreach ( $meta as $rule ) {
			// Get the type for this rule
			$type = ( isset( $manip_types_meta[$i] ) )? $manip_types_meta[$i] : 'remove';
			// Generate the dropdown
			$manip_types = WPRSS_FTP_Utils::array_to_select(
				$field['manip_options'],
				array(
					'class'		=>	'wprss-ftp-extraction-rules-manipulation-type',
					'name'		=>	self::META_PREFIX . 'extraction_rules_types[]',
					'selected'	=>	$type,
				)
			);
			// Generate the final input field
			$input .= WPRSS_FTP_Utils::template(
				$input_section,
				array(
					'value'			=> esc_attr( $rule ),
					'manip_types'	=> $manip_types,
				)
			);
			// Increment counter
			$i++;
		}


		// Replace the {{manip_types}} placeholder with the default dropdown, to be used as a template in JS, when adding new fields
		$input_section = WPRSS_FTP_Utils::template(
			$input_section,
			array(
				'manip_types'	=>	WPRSS_FTP_Utils::array_to_select(
					$field['manip_options'],
					array(
						'class'		=>	'wprss-ftp-extraction-rules-manipulation-type',
						'name'		=>	self::META_PREFIX . 'extraction_rules_types[]',
						'selected'	=>	'remove',
					)
				),
			)
		);

		// Show the field row, with the prepare input fields for the extraction rules
		ob_start(); ?>
			<tr>
				<th><label for="<?php echo $id; ?>"><?php echo $field['label']; ?></label></th>
				<td>
					<?php echo $input; ?>

					<span id="wprss-ftp-extraction-rules-end"></span>
					<button type="button" class="button-primary wprss-ftp-add-extraction-rule">
						<i class="fa fa-plus"></i> Add New
					</button>

					<label class="description"><?php echo $desc; ?></label>

					<script type="text/javascript">
						var wprss_input_field_template = "<?php echo addslashes( $input_section ); ?>";
					</script>
				</td>
			</tr>
		<?php
		return ob_get_clean();
	}


	/**
	 * Renders the custom fields mapping option
	 * 
	 * @since 2.8
	 */
	public function render_custom_fields( $post, $field_id, $field, $meta ) {
		$META_PREFIX = self::META_PREFIX;
		$id = $META_PREFIX . $field_id;

		// Get the RSS tags, RSS namespaces options and the custom fields
		//$saved_namespaces = WPRSS_FTP_Settings::get_instance()->get( 'user_feed_namespaces' );
		$saved_namespaces = WPRSS_FTP_Settings::get_instance()->get_namespaces();

		?>
		<p>
		This allows you to retrieve data from any RSS tag in feed items, then store it in a custom meta field in imported posts.
		</p>
		<?php

		// If there are no saved namespaces, show a message and exit function
		if ( !is_array( $saved_namespaces ) || count( $saved_namespaces ) === 0 ) {
			?>
				<p>
					To use this option, you first need to add the namespaces that you want to use in the
					<a href="<?php echo admin_url() . 'edit.php?post_type=wprss_feed&page=wprss-aggregator-settings&tab=ftp_settings#wprss-ftp-add-namespace'; ?>" target="wprss_ftp_settings">
						Feed to Post settings page
					</a>.
				</p>
			<?php return;
		}

		// Get the meta values
		$rss_namespaces = $this->get_meta( $post->ID, 'rss_namespaces' );
		$rss_namespaces = ( $rss_namespaces === '' )? array() : $rss_namespaces;
		$rss_tags = $this->get_meta( $post->ID, 'rss_tags' );
		$rss_tags = ( $rss_tags === '' )? array() : $rss_tags;
		$custom_fields = $this->get_meta( $post->ID, 'custom_fields' );
		$custom_fields = ( $custom_fields === '' )? array() : $custom_fields;

		// Prepare the array of namespaces
		// Add an entry to selected a namespace
		// And make each entry use the name as both value and label
		$namespaces_array = array_merge(
			array( '' => 'Choose a namespace' ),
			array_combine( $saved_namespaces['names'], $saved_namespaces['names'] )
		);

		// Generate field templates to use in loop below and in JS
		$namespace_dropdown_template = array(
			'options'		=>	$namespaces_array,
			'attributes'	=>	array(
				'name'			=>	self::META_PREFIX . 'rss_namespaces[]',
				'selected'		=>	''	
			)
		);
		$rss_tag_field = "<input type='text' name='{$META_PREFIX}rss_tags[]' value='{{value}}' placeholder='RSS Tag' />";
		$custom_field_field = "<input type='text' name='{$META_PREFIX}custom_fields[]' value='{{value}}' placeholder='Meta field name' />";
		$section_class = "wprss-ftp-custom-fields-section";

		$remove_btn = '<button type="button" class="button-secondary wprss-ftp-remove-custom-mapping"><i class="fa fa-trash-o"></i></button>';

		ob_start();

		// Print a section for each 
		for ( $i = 0; $i < count( $rss_namespaces ); $i++ ) {
			// Get the data for the current custom field entry
			$namespace = $rss_namespaces[$i];
			$tag = $rss_tags[$i];
			$custom_field = $custom_fields[$i];
			// Prepare the dropdown
			$namespace_dropdown = $namespace_dropdown_template;
			$namespace_dropdown['attributes']['selected'] = $namespace;
			
			echo "<div class='$section_class'>";
			echo WPRSS_FTP_Utils::array_to_select( $namespace_dropdown['options'], $namespace_dropdown['attributes'] );
			echo WPRSS_FTP_Utils::template( $rss_tag_field, array( 'value' => $tag ) );
			echo WPRSS_FTP_Utils::template( $custom_field_field, array( 'value' => $custom_field ) );
			echo $remove_btn;
			echo "</div>";
		}
		$saved_custom_mappings = ob_get_clean();

		// Prepare the dropdown template for JS
		$namespace_dropdown_template = WPRSS_FTP_Utils::array_to_select(
			$namespace_dropdown_template['options'],
			$namespace_dropdown_template['attributes']
		);

		// Show the field row, with the prepare input fields for the extraction rules
		ob_start(); ?>
			<tr>
				<th><label for="<?php echo $id; ?>"><?php echo $field['label']; ?></label></th>
				<td>
					<?php echo $saved_custom_mappings; ?>

					<span id="wprss-ftp-custom-fields-marker"></span>

					<button type="button" id="wprss-ftp-add-custom-mapping" class="button-primary">
						<i class="fa fa-plus"></i> Add New
					</button>

					<label class="description"><?php echo $field['desc']; ?></label>

					<script type="text/javascript">
						var wprss_ftp_custom_mappings_section_class = "<?php echo addslashes( $section_class ); ?>";
						var wprss_ftp_namespaces_dropdown = "<?php echo addslashes( $namespace_dropdown_template ); ?>";
						var wprss_ftp_rss_tag_field = "<?php echo addslashes( $rss_tag_field ); ?>";
						var wprss_ftp_custom_field_field = "<?php echo addslashes( $custom_field_field ); ?>";
						var wprss_ftp_remove_custom_mapping = "<?php echo addslashes( $remove_btn ); ?>";
					</script>
				</td>
			</tr>

			<tr>
				<th>
					<label for="wprss-ftp-namespace-detector-refresh">Namespace Detector</label>
				</th>

				<td>
					<label class="description" for="wprss-ftp-namespace-detector-refresh">
						Use this button to detect the namespaces being used by this feed source.
					</label>
					<button type="button" id="wprss-ftp-namespace-detector-refresh" class="button-secondary">
						 <i class="fa fa-search"></i> Detect namespaces in Feed Source
					</button>

					<br/>

					<div id="wprss-ftp-namespace-detector-results"></div>
				</td>
			</tr>
		<?php
		return ob_get_clean();
	}


	/**
	 * Renders the post site dropdown.
	 * 
	 * @since 1.7
	 */
	public function render_post_site_dropdown( $post, $field_id, $field, $meta ) {
		$id = self::META_PREFIX . $field_id;
		$desc = $field['desc'];
		$multisite_and_main_site = WPRSS_FTP_Utils::is_multisite_and_main_site();
		$input = '';
		// If the return value of WPRSS_FTP_Utils::is_multisite_and_main_site()
		// is a string, then it is an error message. Set the description to it.
		if ( is_string( $multisite_and_main_site ) ) {
			$desc = $multisite_and_main_site;
		}
		// Otherwise, if it is boolean TRUE
		elseif ( $multisite_and_main_site === TRUE) {
			// Get the sites and generate a dropdown
			$sites = WPRSS_FTP_Utils::get_sites();
			$input = WPRSS_FTP_Utils::array_to_select(
				$sites,
				array(
					'id'		=> $id,
					'name'		=> $id,
					'selected'	=> $meta,
				)
			);
		}
		// If using multisite but not the main site, simply show the site name.
		elseif ( $multisite_and_main_site === FALSE && is_multisite() ) {
			$current_site = get_bloginfo('name');
			$meta = get_current_blog_id();
			$input = "<input type='hidden' name='$id' id='$id' value='$meta' /><b>{$current_site}</b>";
			$desc = '';
		}
		// If neither multisite nor main site, do not show the row
		else return '';

		// Show the field row
		ob_start(); ?>
			<tr>
				<th><label for="<?php echo $id; ?>"><?php echo $field['label']; ?></label></th>
				<td>
					<?php echo $input; ?>
					<br/><label class="description" for="<?php echo $id; ?>"><?php echo $desc; ?></label>
				</td>
			</tr>
		<?php
		return ob_get_clean();
	}


	/**
	 * Renders the custom default featured image metabox
	 */
	public function post_thumbnail_meta_box() {
		$args = func_get_args();
		call_user_func_array( 'post_thumbnail_meta_box', $args );

		?>
		<script type="text/javascript">

		(function($){
			$(document).ready(function(){
				// Default featured image metabox fix
				$('#postimagediv > h3.hndle > span').text( 'Fallback Featured Image' );
			});
		})(jQuery);

		</script>
		<?php
	}


#== META DATA SAVING ===================================================================================

	/**
	 * Saves the post's meta data, using the known meta fields in the get_meta_fields() method.
	 *
	 * @since 1.0
	 */
	public function save_post_meta( $post_id, $post ) {
		# Get all meta fields
		$meta_fields = self::get_meta_fields( 'all' );

		# For each meta field ...
		foreach ( $meta_fields as $id => $field ) {

			# Get the ID with the prefix
			$id = self::META_PREFIX . $id;

			# Get the current meta value, and the new value from POST
			$old = get_post_meta( $post_id, $id, true );

			$new = '';
			// If the meta field is not found in the POST request
			if ( isset( $_POST[ $id ] ) )
				$new = $_POST[ $id ];

			if ( $id === 'wprss_ftp_post_terms' && !isset( $_POST[ $id ] ) )
				$new = array();

			// Check if meta data is updated or deleted
			if ( $new && $new != $old ) {
				update_post_meta( $post_id, $id, $new );
			} elseif ( $new === '' && $old ) {
				delete_post_meta( $post_id, $id, $old );
			}
		}
	}


#== CORE OVERRIDES ============================================================================

	/**
	 * Override the core's meta fields.
	 *
	 * @since 2.8
	 */
	public function change_core_meta_fields( $fields ) {
		// Change the enclosure description, to match its new functionality
		$fields['enclosure']['desc'] = __( '', 'wprss' );
		// Return the fields
		return $fields;
	}


#== MISC ======================================================================================

	/**
	 * Returns the user array that creates the user dropdown in the authors metabox.
	 *
	 * Developer note: Using array_merge was reordering the array keys, causing the keys,
	 * which signified the user id, to cause the plugin to assign incorrect authors.
	 * This copying of arrays by brute force ensures that the order of elements and the
	 * numbering of keys is retained.
	 * 
	 * @since 2.0
	 */
	public static function get_users_array() {
		// Get the users
		$users =  WPRSS_FTP_Settings::get_users();

		// Create a new array
		$return_array = array( '.' => 'Author in feed' );
		$return_array['Existing user'] = array();
		// Copy all usres into this array
		foreach ( $users as $key => $value ) {
			$return_array['Existing user'][$key] = $value;
		}

		// Return the array
		return $return_array;
	}


	/**
	 * Returns the append placeholders for post_append
	 * 
	 * @since 1.6
	 */
	public function get_append_placeholders() {
		$placeholders = WPRSS_FTP_Appender::get_placeholders();
		$s = ceil( count( $placeholders ) / 3 );
		$i = 0;
		?>
		<table id="wprss-ftp-placeholders-table" cellpadding="1">
			<tbody>
				<tr>
					<?php foreach ($placeholders as $placeholder => $desc) : ?>
						<td title="<?php echo esc_attr($desc); ?>"><?php echo $placeholder; ?></td>
						<?php if ( (++$i % $s) === 0 ) echo '</tr><tr>'; ?>
					<?php endforeach; ?>
				</tr>
				<tr>
					<td title="The custom meta field 'xyz' of the imported post. Change 'xyz' to the name of any meta field you want.">{{meta : xyz}}</td>
					<td title="The custom meta field 'xyz' of this feed source. Change 'xyz' to the name of any meta field you want.">{{source_meta : xyz}}</td>
				</tr>
			</tbody>
		</table>
		<?php
	}


	/**
	 * Fixed incorrect meta value for multisite option for all existing feed sources
	 * 
	 * @since 1.8.3
	 */
	public static function multisite_fix() {
		if ( is_multisite() ) {
			global $switched;
			$current_site_id = get_current_blog_id();
			$site_ids = array_keys( WPRSS_FTP_Utils::get_sites() );

			for( $i = 0; $i < count( $site_ids ); $i++ ) {
				$site_id = $site_ids[$i];
   				$switch_success = switch_to_blog( $site_id );
   				if ( $switch_success === FALSE ) continue;

				$feed_sources = wprss_get_all_feed_sources();

				if( $feed_sources->have_posts() ) {
					while ( $feed_sources->have_posts() ) {
						$feed_sources->the_post();

						$post_site = get_post_meta( get_the_ID(), WPRSS_FTP_Meta::META_PREFIX . 'post_site', TRUE );

						if ( $post_site === '' || $post_site === FALSE || strtolower( strval( $post_site ) ) == 'false' ) {
							update_post_meta( get_the_ID(), WPRSS_FTP_Meta::META_PREFIX . 'post_site', get_current_blog_id() );
						}

					}

					// Restore the $post global to the current post in the main query
					wp_reset_postdata();

				} // end of have_posts()
   				
			} // End of site loop
			switch_to_blog( $current_site_id );
		} // End of multisite check
	}

	public function get_post_types( $source_id = null ) {
		$post_types = WPRSS_FTP_Settings::get_post_types();
		if ( is_null($source_id) ) {
			return $post_types;
		}
		
		$assigned_type = self::get_instance()->get_meta( $source_id, 'post_type' );
		if( $assigned_type && !isset($post_types[$assigned_type]) ) {
			$post_types[$assigned_type] = sprintf( '%1$s (%2$s)', $assigned_type, __('Not Registered', 'wprss') );
		}
		
		return $post_types;
	}
}
