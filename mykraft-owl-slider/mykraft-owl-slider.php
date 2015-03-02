<?php
/*
Plugin Name: Mykraft Owl Slider
Plugin URI: http://megakrafts.com/mykraft-owl-slider-plugin
Description: Mykraft Owl Slider is a simple, responsive WordPress slider plugin ideal for template homepage. It is based on OWL Carousel, touch enabled jQuery plugin. Slider works as a custom post type with featured images, excerpt as a slide description and each slide can have a link with an option to open a link in a new tab.
Version: 1.1
Author: Vitomir Gojak
Author Email: mykraftmail@gmail.com
License: GPLv2 or later
*/

//	Slider Post Thumbnail
	if ( function_exists( 'add_theme_support' ) ) { 
	add_theme_support( 'post-thumbnails' );
	}
	
//  Add Custom Owl Slider Script
	function owl_script() {
    wp_register_script('owlscript', plugins_url('/mykraft-owl-slider/owl-carousel/owl.script.js'));
    wp_enqueue_script('owlscript');
	}
    add_action('wp_print_scripts', 'owl_script');

//  Add Owl Sider Styles
	function slider_register_styles() {
    wp_register_style('sliderstyle1', plugin_dir_url( __FILE__ ) . 'owl-carousel/owl.carousel.css');
    wp_register_style('sliderstyle2', plugin_dir_url( __FILE__ ) . 'owl-carousel/owl.theme.css');
    wp_enqueue_style('sliderstyle1');
    wp_enqueue_style('sliderstyle2');
	}
	add_action('wp_print_styles', 'slider_register_styles');

//  Add Owl Slider Script
	function slider_register_script() {
    wp_register_script('sliderscript', plugin_dir_url( __FILE__ ) . 'owl-carousel/owl.carousel.min.js');
    wp_enqueue_script('sliderscript');
	}
	add_action('wp_print_scripts', 'slider_register_script');
	
//  Slider Post Type
	function register_slides_posttype() {
		$labels = array(
			'name' 				=> _x( 'Slides', 'post type general name','mykraft' ),
			'singular_name'		=> _x( 'Slide', 'post type singular name','mykraft' ),
			'add_new' 			=> __( 'Add New Slide','mykraft' ),
			'add_new_item' 		=> __( 'Add New Slide','mykraft' ),
			'edit_item' 		=> __( 'Edit Slide','mykraft' ),
			'new_item' 			=> __( 'New Slide','mykraft' ),
			'view_item' 		=> __( 'View','mykraft'),
			'search_items' 		=> __( 'Search Slides','mykraft' ),
			'not_found' 		=> __( 'Slide','mykraft' ),
			'not_found_in_trash'=> __( 'Slide','mykraft' ),
			'parent_item_colon' => __( 'Slide','mykraft' ),
			'menu_name'			=> __( 'Slider','mykraft' )
		);
		$taxonomies = array();
		$supports = array('title','excerpt','thumbnail');
		$post_type_args = array(
			'labels' 			=> $labels,
			'singular_label' 	=> __('Slide','mykraft'),
			'public' 			=> true,
			'show_ui' 			=> true,
			'publicly_queryable'=> true,
			'can_export'        => true,
			'query_var'			=> true,
			'capability_type' 	=> 'post',
			'has_archive' 		=> false,
			'hierarchical' 		=> true,
			'rewrite' 			=> array('slug' => 'slides', 'with_front' => false ),
            'supports'          => array( 'title', 'excerpt', 'thumbnail' ),
			'menu_position' 	=> 27,
			'menu_icon' 		=> plugin_dir_url( __FILE__ ) . '/images/slider-icon.png',
			'taxonomies'		=> $taxonomies
		 );
		 register_post_type('slides',$post_type_args);
	}
	add_action('init', 'register_slides_posttype');
	
//  Slider Meta Box
	$slidelink_2_metabox = array( 
		'id' => 'slidelink',
		'title' => 'Slide Link',
		'page' => array('slides'),
		'context' => 'normal',
		'priority' => 'default',
		'fields' => array(
                		array(
						'name' 			=> 'URL',
						'desc' 			=> '',
						'id' 			=> 'mykraft_slideurl',
						'class' 		=> 'mykraft_slideurl',
						'type' 			=> 'text',
						'rich_editor' 	=> 0,
						'std'          	=> '',
						'max' 			=> 0
						),
						array(
						'name' 			=> 'Open slide link in new tab:',
						'desc' 			=> '',
						'id' 			=> 'mykraft_slidetarget',
						'class' 		=> 'mykraft_slidetarget',
						'type' 			=> 'checkbox'
						),
					)
	);	
	
	// add meta box			
	add_action('admin_menu', 'mykraft_add_slidelink_2_meta_box');
	function mykraft_add_slidelink_2_meta_box() {
		global $slidelink_2_metabox;		
		foreach($slidelink_2_metabox['page'] as $page) {
			add_meta_box($slidelink_2_metabox['id'], $slidelink_2_metabox['title'], 'mykraft_show_slidelink_2_box', $page, 'normal', 'default', $slidelink_2_metabox);
		}
	}
	
	// show meta boxes
	function mykraft_show_slidelink_2_box()	{
		global $post;
		global $slidelink_2_metabox;
		global $mykraft_prefix;
		global $wp_version;
		
	//  nonce for verification
		echo '<input type="hidden" name="mykraft_slidelink_2_meta_box_nonce" value="', wp_create_nonce(basename(__FILE__)), '" />';
		echo '<table class="form-table">';
		foreach ($slidelink_2_metabox['fields'] as $field) {
			// get current post meta data
			$meta = get_post_meta($post->ID, $field['id'], true);
			echo '<tr>',
					'<th style="width:20%"><label for="', $field['id'], '">', stripslashes($field['name']), '</label></th>',
					'<td class="mykraft_field_type_' . str_replace(' ', '_', $field['type']) . '">';
			switch ($field['type']) {
				case 'text':
					echo '<input type="text" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : $field['std'], '" size="30" style="width:97%" /><br/>', '', stripslashes($field['desc']);
					break;
				case 'checkbox':
					echo '<input type="checkbox" name="', $field['id'], '" id="', $field['id'], '"', $meta ? ' checked="checked"' : '', ' />';
					break;
			}
			echo    '<td>',
				'</tr>';
		}
		echo '</table>';
	}	
	
    // url target
	function mykraft_targetlink() {
	$meta = get_post_meta( get_the_ID(), 'mykraft_slidetarget', true );
    if ($meta == '') {
        echo '_self';
    } else {
        echo '_blank';
      }
	}

	// attachment
	if ( 'post_type' == 'slider' && post_status == 'publish' ) {
    $attachments = get_posts(array(
        'post_type' => 'attachment',
        'posts_per_page' => -1,
        'post_parent' => $post->ID,
        'exclude'     => get_post_thumbnail_id()
    ));
        if ($attachments) {
            foreach ($attachments as $attachment) {
            $thumbimg = wp_get_attachment_link( $attachment->ID, 'thumbnail-size', true );
            echo $thumbimg;
            }
        }
    }

// Save data from meta box
	add_action('save_post', 'mykraft_slidelink_2_save');
	function mykraft_slidelink_2_save($post_id) {
		global $post;
		global $slidelink_2_metabox;
		// verify nonce
		if ( !isset( $_POST['mykraft_slidelink_2_meta_box_nonce'] ) || !wp_verify_nonce( $_POST['mykraft_slidelink_2_meta_box_nonce'], basename( __FILE__ ) ) ) {
			return $post_id;
		}
		// check autosave
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return $post_id;
		}
		// check permissions
		if ('page' == $_POST['post_type']) {
			if (!current_user_can('edit_page', $post_id)) {
				return $post_id;
			}
		} elseif (!current_user_can('edit_post', $post_id)) {
			return $post_id;
		}
		foreach ($slidelink_2_metabox['fields'] as $field) {
			$old = get_post_meta($post_id, $field['id'], true);
			$new = $_POST[$field['id']];
			if ($new && $new != $old) {
				if($field['type'] == 'date') {
					$new = mykraft_format_date($new);
					update_post_meta($post_id, $field['id'], $new);
				} else {
					if(is_string($new)) {
						$new = $new;
					} 
					update_post_meta($post_id, $field['id'], $new);
				}
			} elseif ('' == $new && $old) {
				delete_post_meta($post_id, $field['id'], $old);
			}
		}
	}

//  Construct Home Page Slider
	function homepage_slider() {
	?>
		<div id="slider-container" style="background-image: url(<?php if (get_theme_mod( 'image_slider_bg' )) : echo get_theme_mod( 'image_slider_bg'); else: echo plugin_dir_url( __FILE__ ) . '/images/slider-background.png'; endif; ?>);">
			<div id="owl-container" class="owl-carousel owl-theme">
				<?php
					$args = array('post_type' => 'slides', 'posts_per_page' => -1);
					$loop = new WP_Query($args);
					while ($loop->have_posts()) : $loop->the_post();
				    ?>
					<div class="owl-background">
		   			<?php 
						if ( get_post_meta( get_the_id(), 'mykraft_slideurl', true) != '' ) { ?>
							<a href="<?php echo esc_url( get_post_meta( get_the_id(), 'mykraft_slideurl', true ) ); ?>" target="<?php echo mykraft_targetlink(); ?>">
							<?php
							    if ( ! has_excerpt() ) { 
								} else { ?>
								<div class="owl-item-excerpt-background">
    		   						<div class="owl-item-excerpt"><?php the_excerpt(); ?></div>
								</div>
		   			   		    <?php }
		   					    the_post_thumbnail('slider', array('class' => 'owl-featured-image')); ?>
 		   	       			</a>			
					<?php
    					} else { 
						if ( ! has_excerpt() ) {  
					        } else { ?>	
							<div class="owl-item-excerpt-background">
		   						<div class="owl-item-excerpt"><?php the_excerpt(); ?></div>				
		   					</div>
		   			   		<?php }  
		   					the_post_thumbnail('slider', array('class' => 'owl-featured-image'));		
		   			   	} ?>
				    </div><!--.owl-background--> 
	                <?php endwhile; ?>	
            </div><!--#owl-container--> 	
		</div><!--#slider-container--> 
	<?php
	}
	add_action( 'wp_enqueue', 'homepage_slider' );

//  Slider Pagination Background for WP Customizer
	function slider_pagination() { ?>
		<style type="text/css">
			.owl-pagination {
			background: <?php if (get_theme_mod( 'color_sliderpagination' )) : echo get_theme_mod( 'color_sliderpagination');  endif; ?>;
			background-image: url(<?php if (get_theme_mod( 'image_sliderpagination' )) : echo get_theme_mod( 'image_sliderpagination');  endif; ?>);
			}
    	</style>
	<?php }
    add_action('wp_head', 'slider_pagination');
	
//  Disable Slider Pagination
	function slider_hidepagination() { 
    	if (get_theme_mod( 'checkbox_sliderpagination' )) :
		echo'<style type="text/css"> .owl-pagination { display: none; } </style>';
		else:
		echo'<style type="text/css"> .owl-pagination { display: block; } </style>';
		endif;
	}
    add_action('wp_head', 'slider_hidepagination');

//  Slider Width for WP Customizer
	function slider_width_size() { ?>
		<style type="text/css">
			.owl-background {
			max-width: <?php if (get_theme_mod( 'slider_width' )) : echo get_theme_mod( 'slider_width');  endif; ?>px;
			}
    	</style>
	<?php }
    add_action('wp_head', 'slider_width_size');
	
//  WP Customizer Menu Slider Options
    new slider_theme_customizer();
	class slider_theme_customizer {
        public function __construct() {
            add_action( 'customize_register', array(&$this, 'customize_manager_slider' ));
		}
        public function customize_manager_slider( $wp_manager ) {
            $this->slider_theme_section( $wp_manager );
		}
		public function slider_theme_section( $wp_manager ) {
            $wp_manager->add_section( 'customiser_slider_theme_section', array(
                'title'          => 'Slider Customizer',
                'priority'       => 172,
			) );

            // Slider Background Area Image
            $wp_manager->add_setting( 'image_slider_bg', array(
                'default'        => '',
            ) );
            $wp_manager->add_control( new WP_Customize_Image_Control( $wp_manager, 'image_slider_bg', array(
                'label'   => 'Slider Area Background',
                'section' => 'customiser_slider_theme_section',
                'settings'   => 'image_slider_bg',
                'priority' => 1
            ) ) );

		    // Slider Pagination Background Image
            $wp_manager->add_setting( 'image_sliderpagination', array(
                'default'        => '',
            ) );
            $wp_manager->add_control( new WP_Customize_Image_Control( $wp_manager, 'image_sliderpagination', array(
                'label'   => 'Slider Pagination Background',
                'section' => 'customiser_slider_theme_section',
                'settings'   => 'image_sliderpagination',
                'priority' => 2
            ) ) );
			
			// Slider Pagination Background Color
			$wp_manager->add_setting( 'color_sliderpagination', array(
                'default'        => '#2B3542',
			) );

			$wp_manager->add_control( new WP_Customize_Color_Control( $wp_manager, 'color_sliderpagination', array(
                'label'   => 'Slider Pagination Background Color',
				'section' => 'customiser_slider_theme_section',
				'settings'   => 'color_sliderpagination',
				'priority' => 3
			) ) );
			
			// Disable slider pagination
        	$wp_manager->add_setting( 'checkbox_sliderpagination', array(
         	   'default'        => '',
        	) );

        	$wp_manager->add_control( 'checkbox_sliderpagination', array(
        	    'label'   => 'Disable Slider Pagination',
        	    'section' => 'customiser_slider_theme_section',
        	    'type'    => 'checkbox',
        	    'priority' => 4
        	) );
			
			// Slider Width
			$wp_manager->add_setting( 'slider_width', array(
                'default'        => '',
			) );
			
			$wp_manager->add_control( 'slider_width', array(
                'label'   => 'Slider Maximum Width - numeric value (e.g. 1400), without px',
				'section' => 'customiser_slider_theme_section',
				'type'    => 'text',
				'priority' => 5
			) );		
        }
    }
?>