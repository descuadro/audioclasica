<?php
	/*
    Plugin Name: WP RSS Aggregator - Keyword Filtering
    Plugin URI: http://www.wprssaggregator.com
    Description: Adds keyword filtering capabilities to WP RSS Aggregator
    Version: 1.5
    Author: Jean Galea
    Author URI: http://www.wprssaggregator.com
    License: GPLv3
    */

    /*  
    Copyright 2013 Jean Galea (email : info@jeangalea.com)
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU General Public License for more details.
    
    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
    */
	
	
	/* Set the version number of the plugin. */
    if( !defined( 'WPRSS_KF_VERSION' ) )
        define( 'WPRSS_KF_VERSION', '1.5', true );

    /* Set the database version number of the plugin. */
    if( !defined( 'WPRSS_KF_DB_VERSION' ) )
        define( 'WPRSS_KF_DB_VERSION', '1' );

    /* Set constant path to the plugin directory. */
    if( !defined( 'WPRSS_KF_DIR' ) )
        define( 'WPRSS_KF_DIR', plugin_dir_path( __FILE__ ) );

    /* Set constant path to the plugin includes directory. */
    if( !defined( 'WPRSS_KF_INC_DIR' ) )
        define( 'WPRSS_KF_INC_DIR', WPRSS_KF_DIR . 'includes/' );

    /* Set constant URI to the plugin URL. */
    if( !defined( 'WPRSS_KF_URI' ) )
        define( 'WPRSS_KF_URI', plugin_dir_url( __FILE__ ) );

    /* Set constant path to the main plugin file. */
    if( !defined( 'WPRSS_KF_PATH' ) )
        define( 'WPRSS_KF_PATH', __FILE__);        
    
	/* Set constant store URL */
	if( !defined( 'WPRSS_KF_SL_STORE_URL' ) )
		define( 'WPRSS_KF_SL_STORE_URL', 'http://www.wprssaggregator.com' ); 
    
	/* Set the constant item name of the plugin */
	if( !defined( 'WPRSS_KF_SL_ITEM_NAME' ) )
		define( 'WPRSS_KF_SL_ITEM_NAME', 'Keyword Filtering' ); 

    /* Set the constants for filtering modes */
    if( !defined( 'WPRSS_KF_NORMAL_FILTER_MODE' ) )
        define( 'WPRSS_KF_NORMAL_FILTER_MODE', 0 );

    if( !defined( 'WPRSS_KF_NOT_FILTER_MODE' ) )
        define( 'WPRSS_KF_NOT_FILTER_MODE', 1 );
	
    /* Load required files */
    require_once WPRSS_KF_INC_DIR . 'admin-settings.php';
    require_once WPRSS_KF_INC_DIR . 'admin-metaboxes.php';

    /* Licensing */
    require_once WPRSS_KF_INC_DIR . 'licensing.php';


	register_activation_hook( __FILE__ , 'wprss_kf_activate' );
    /**
     * Plugin activation procedure
     *
     * @since  1.0
     */  
    function wprss_kf_activate() {
        /* Prevents activation of plugin if compatible version of WordPress not found */
        if ( version_compare( get_bloginfo( 'version' ), '3.3', '<' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            deactivate_plugins ( basename( __FILE__ ));     // Deactivate plugin
            wp_die( __( 'The Keyword Filtering add-on requires WordPress version 3.3 or higher.' ), 'WP RSS Aggregator Keyword Filtering', array( 'back_link' => true ) );
        }  

        // Add the database version setting. 
        update_option( 'wprss_kf_db_version', WPRSS_KF_DB_VERSION );
        wprss_kf_licenses_settings_initialize();
    }
	

	add_action( 'plugins_loaded', 'wprss_kf_init' );
    /**
     * Initialize the module on plugins loaded, so WP RSS Aggregator should have set its constants and loaded its functions.
     * @since 1.0
     */
    function wprss_kf_init() {
        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        if ( !defined( 'WPRSS_VERSION' ) ) {
            deactivate_plugins ( basename( __FILE__ ));     // Deactivate plugin   
            add_action( 'all_admin_notices', 'wprss_kf_missing_error' );
        } 

        else {
            if ( version_compare( WPRSS_VERSION, '3.4.2', '<' ) ) {
                deactivate_plugins ( basename( __FILE__ ));     // Deactivate plugin   
                add_action( 'all_admin_notices', 'wprss_kf_update_notice' );
            }
            do_action( 'wprss_kf_init' );
        }
        
    }
	

	/**
     * Throw an error if WP RSS Aggregator is not installed.    
     * @since 1.0
	 * @todo needs to be be localised
     */
    function wprss_kf_missing_error() {
        echo '<div class="error"><p>Please <a href="' . admin_url( 'plugin-install.php?tab=search&type=term&s=wp+rss+aggregator&plugin-search-input=Search+Plugins' ) . '">install &amp; activate WP RSS Aggregator</a> for the Keyword Filtering add-on to work.</p></div>';
    }


    /**
     * Throw an error if WP RSS Aggregator is not updated to the latest version
     * @since 1.0
     */
    function wprss_kf_update_notice() {
        echo '<div class="error"><p>' . __( 'Please update WP RSS Aggregator to the latest version for the Keyword Filtering add-on to work properly.', 'wprss' ) . '</p></div>';
    }
	


    add_action( 'wprss_kf_init', 'wprss_kf_license_notification' );
    /**
     * Checks if a license code is entered. If not, shows a notification to remind the user.
     * Note: Does not check if the license code is valid!
     *
     * @since 1.2
     */
    function wprss_kf_license_notification() {
        if ( function_exists( 'wprss_check_addon_notice_option' ) === FALSE ) {
            add_action( 'all_admin_notices', 'wprss_kf_missing_error' );
            deactivate_plugins ( basename( __FILE__ ) );
            return;
        }
        $license_keys = get_option( 'wprss_settings_license_keys' ); 
        $kf_license_key = ( isset( $license_keys['kf_license_key'] ) ) ? $license_keys['kf_license_key'] : '';
        $option = wprss_check_addon_notice_option();
        if ( strlen( $kf_license_key) === 0 && isset( $option['keyword_filtering']['license'] ) === FALSE && is_main_site() ) {
            add_action( 'all_admin_notices', 'wprss_kf_license_notice' );
        }
    }


    /**
     * Prints the admin license notice
     * 
     * @since 1.1.1
     */
    function wprss_kf_license_notice() {
        echo '<div class="updated">
            <p>
                Remember to <a href="' . admin_url( 'edit.php?post_type=wprss_feed&page=wprss-aggregator-settings&tab=licenses_settings' ) . '">enter your plugin license code</a>
                for the WP RSS Aggregator <b>Keyword Filtering</b> add-on, to benefit from updates and support.
                <a href="#" class="ajax-close-addon-notice" style="float:right;" data-addon="keyword_filtering" data-notice="license">
                    Dismiss this notification
                </a>
            </p>
        </div>';
    }



	
	
    add_filter( 'wprss_insert_post_item_conditionals', 'wprss_kf_check_post_item_keywords', 10, 3 );
    /**
     * Checks the given item for the presence of any keywords set for the feed source
     * with ID $feed_ID. At least 1 keyword mst be found for the check to be successful.
     *
     * @return The item if at least 1 keywrod is found. NULL otherwise.
     * @param item      The item to be checked for keywords
     * @param feed_ID   The ID of the item's feed source
     * @since 1.0
     */
    function wprss_kf_check_post_item_keywords( $item, $feed_ID, $permalink ) {
        // If the item is NULL (i.e. already flagged as not being inserted into the DB) then return NULL.
        if ( $item === NULL ) return NULL;
    
        // Retrieve the keywords stored in the global settings
        $settings = get_option( 'wprss_settings_kf', array() );
        $settings = wp_parse_args( $settings, wprss_kf_default_options() );

        $filter_title = get_post_meta( $feed_ID, 'wprss_filter_title', TRUE );
        if ( $filter_title === '' ) $filter_title = 'true';

        $filter_content = get_post_meta( $feed_ID, 'wprss_filter_content', TRUE );
        if ( $filter_content === '' ) $filter_content = 'true';
        
        // Prepare the filtering options
        $filtering_opts = array();
        if ( $filter_title == 'true' ) $filtering_opts[] = 'title';
        if ( $filter_content == 'true' ) $filtering_opts[] = 'content';

        $settings_keywords = $settings['keywords'];
        $settings_keywords_any = $settings['keywords_any'];
        $settings_keywords_not = $settings['keywords_not'];
        $settings_keywords_tags = $settings['keywords_tags'];
        $settings_keywords_not_tags = $settings['keywords_not_tags'];

        // Retrieve the feed source's keywords meta data
        $post_keywords = get_post_meta( $feed_ID, 'wprss_keywords', true );
        $post_keywords_any = get_post_meta( $feed_ID, 'wprss_keywords_any', true );
        $post_keywords_not = get_post_meta( $feed_ID, 'wprss_keywords_not', true );
        $post_keywords_tags = get_post_meta( $feed_ID, 'wprss_keywords_tags', true );
        $post_keywords_not_tags = get_post_meta( $feed_ID, 'wprss_keywords_not_tags', true );
        

        //=== KEYWORDS ===========================================================
        //=== All keywords must be matched for the feed item to be imported ======

        // Generate an array, that explodes the comma separated keywords and trims each array entry
        // from leading / trailing whitespace.
        $settingsKeywordsArray = array_filter( array_map( 'trim', explode( ',', $settings_keywords ) ) );
        $postKeywordsArray = array_filter( array_map( 'trim', explode( ',', $post_keywords ) ) );
        $keywords = array_merge( $settingsKeywordsArray, $postKeywordsArray );

        if ( count( $keywords ) > 0 ) {
            // Set match to TRUE
            $match = TRUE;
            // For each keyword ...
            foreach ( $keywords as $keyword ) {
                // If the item obeys the filtering
                if ( wprss_kf_filter_item( $item, $keyword, $filtering_opts ) === FALSE ) {
                    // Set match to false and stop any further checking
                    $match = FALSE;
                    break;
               }
            }
        } else $match = TRUE;

        if ( $match === FALSE ) return NULL;


        //=== ANY KEYWORDS =============================================================
        //=== If at least one of the keywords is found, the feed item is imported ======


        // Generate an array, that explodes the comma separated keywords and trims each array entry
        // from leading / trailing whitespace.
        $settingsKeywordsArray = array_filter( array_map( 'trim', explode( ',', $settings_keywords_any ) ) );
        $postKeywordsArray = array_filter( array_map( 'trim', explode( ',', $post_keywords_any ) ) );
        $keywords = array_merge( $settingsKeywordsArray, $postKeywordsArray );

        if ( count( $keywords ) > 0 ) {
            // Set match to FALSE
            $match = FALSE;
            // For each keyword ...
            foreach ( $keywords as $keyword ) {
                // If the item obeys the filtering
                if ( wprss_kf_filter_item( $item, $keyword, $filtering_opts ) === TRUE ) {
                    // Set match to true and stop any further checking
                    $match = TRUE;
                    break;
                }
                // If matched, no point to continue checking. Break.
                if ( $match === TRUE ) break;
            }
        } else $match = TRUE;

        if ( $match === FALSE ) return NULL;



        //=== NOT KEYWORDS =================================================================
        //=== If at least one of the keywords is found, the feed item is not imported ======


        // Generate an array, that explodes the comma separated keywords and trims each array entry
        // from leading / trailing whitespace.
        $settingsKeywordsArray = array_filter( array_map( 'trim', explode( ',', $settings_keywords_not ) ) );
        $postKeywordsArray = array_filter( array_map( 'trim', explode( ',', $post_keywords_not ) ) );
        $keywords = array_merge( $settingsKeywordsArray, $postKeywordsArray );

        if ( count( $keywords ) > 0 ) {
            // Set match to TRUE
            $match = TRUE;
            // For each keyword ...
            foreach ( $keywords as $keyword ) {
                // If the item does not obeys the filtering (keyword found)
                if ( wprss_kf_filter_item( $item, $keyword, $filtering_opts ) === TRUE ) {
                    // Set match to false and stop any further checking
                    $match = FALSE;
                    break;
               }
            }
        }
        else $match = TRUE;

        if ( $match === FALSE ) return NULL;



        //=== TAGS ====================================================================
        //=== If at least one of the tags is found, the feed item is imported =========


        // Generate an array, that explodes the comma separated tags and trims each array entry
        // from leading / trailing whitespace.
        $settingsTagsArray = array_filter( array_map( 'trim', explode( ',', $settings_keywords_tags ) ) );
        $postTagsArray = array_filter( array_map( 'trim', explode( ',', $post_keywords_tags ) ) );
        $tags = array_merge( $settingsTagsArray, $postTagsArray );

        if ( count( $tags ) > 0 ) {
            // Set match to FALSE
            $match = FALSE;
            // For each tag ...
            foreach ( $tags as $tag ) {
                // Get the post tags
                $itemTagObjects = $item->get_categories();
                $itemTags = array_filter( array_map( 'wprss_kf_get_tag_label', $itemTagObjects ) );
                // If the tag is found
                $tagFound = in_array( strtolower($tag), $itemTags );

                // If the tag is found, set match to true and stop checking
                if ( $tagFound === TRUE ) {
                    $match = TRUE;
                    break;
                }
            }
        }
        else $match = TRUE;

        if ( $match === FALSE ) return NULL;


        //=== NOT TAGS ====================================================================
        //=== If at least one of the tags is found, the feed item is NOT imported =========

        // Generate an array, that explodes the comma separated tags and trims each array entry
        // from leading / trailing whitespace.
        $settingsNotTagsArray = array_filter( array_map( 'trim', explode( ',', $settings_keywords_not_tags ) ) );
        $postNotTagsArray = array_filter( array_map( 'trim', explode( ',', $post_keywords_not_tags ) ) );
        $notTags = array_merge( $settingsNotTagsArray, $postNotTagsArray );

        if ( count( $notTags ) > 0 ) {
            // Set match to TRUE
            $match = TRUE;
            // Get the post tags
            $itemTagObjects = $item->get_categories();
            // For each tag ...
            foreach ( $notTags as $tag ) {
                // Filter the item categories for just the labels
                $itemTags = array_filter( array_map( 'wprss_kf_get_tag_label', $itemTagObjects ) );
                // If the tag is found
                $tagFound = in_array( strtolower($tag), $itemTags );

                // If the tag is found, set match to false and stop checking
                if ( $tagFound === TRUE ) {
                    $match = FALSE;
                    break;
                }
            }
        }
        else $match = TRUE;

        if ( $match === FALSE ) return NULL;


        // If the item passed through all the filters, then return it to be imported.
        return $item;
    }


    /**
     * Returns the label of the given tag. Made to be used in array_map() calls.
     * 
     * @param $tag The SimplePie tag object
     * @return string The label of the tag
     * @since 1.3
     */
    function wprss_kf_get_tag_label( $tag ) {
        return strtolower( $tag->get_label() );
    }



    /**
     * Checks if the given feed item contains the keyword in any of the given filtering properties.
     * 
     * @param $item         The feed item
     * @param $keyword      The keyword
     * @param $filtering    The filtering properties to use. If none are specified, the function will return TRUE, as though filtering was ignored.
     * @return boolean      True if the item contains the keyword in any of the filtering properties. False otherwise.
     * @since 1.4
     */
    function wprss_kf_filter_item( $item, $keyword, $filtering = array() ) {
        if ( count( $filtering ) === 0 ) return TRUE;

        // For each filtering property
        foreach ( $filtering as $prop ) {
            switch( strtolower( $prop ) ) {
                case 'title':
                    $found = !( stripos( $item->get_title(), $keyword ) === FALSE );
                    if ( $found === TRUE ) return TRUE;
                    break;
                case 'content':
                    $found = !( stripos( $item->get_content(), $keyword ) === FALSE );
                    if ( $found === TRUE ) return TRUE;
                    break;
            }
        }

        return FALSE;
    }



    add_filter( 'wprss_process_shortcode_args', 'wprss_kf_add_shortcode_filtering', 10, 2 );
    /**
     * Adds the 'filter' argument to the shortcode, so that it is passed to the query args.
     * 
     * @since 1.4
     */
    function wprss_kf_add_shortcode_filtering( $query_args, $args ) {
        if ( isset( $args['filter'] ) ) {
            $query_args['filter'] = $args['filter'];
        }
        return $query_args;
    }

    add_filter( 'wprss_display_feed_items_query', 'wprss_kf_add_filtering_search_parameter', 10, 2 );
    /**
     * Turns the filter shortcode argument into the WordPress search parameter.
     *
     * @since 1.4
     */
    function wprss_kf_add_filtering_search_parameter( $feed_item_args, $query_args ) {
         if ( isset( $query_args['filter'] ) ) {
            $feed_item_args['s'] = $query_args['filter'];
        }
        return $feed_item_args;
    }


    // PressTrends WordPress Action
    add_action( 'admin_init', 'wprss_kf_presstrends_plugin' );  
    /**
     * Track plugin usage using PressTrends
     * 
     * @since  3.5
     * @return void     
     */  
    function wprss_kf_presstrends_plugin() {
        //$settings = get_option( 'wprss_settings_general', array() );
        //if ( ! isset( $settings['tracking'] ) || $settings['tracking'] != 1 ) return;
        
        // PressTrends Account API Key
        $api_key = 'znggu7vk7x2ddsiigkerzsca9q22xu1j53hp';
        $auth    = 'm1uqhekc8ngvimov2h37jgtnz7anrk2bu';
        // Start of Metrics
        global $wpdb;
        $data = get_transient( 'presstrends_cache_data' );
        if ( !$data || $data == '' ) {
            $api_base = 'http://api.presstrends.io/index.php/api/pluginsites/update?auth=';
            $url      = $api_base . $auth . '&api=' . $api_key . '';
            $count_posts    = wp_count_posts();
            $count_pages    = wp_count_posts( 'page' );
            $comments_count = wp_count_comments();
            if ( function_exists( 'wp_get_theme' ) ) {
                $theme_data = wp_get_theme();
                $theme_name = urlencode( $theme_data->Name );
            } else {
                $theme_data = get_theme_data( get_stylesheet_directory() . '/style.css' );
                $theme_name = $theme_data['Name'];
            }
            $plugin_name = '&';
            foreach ( get_plugins() as $plugin_info ) {
                $plugin_name .= $plugin_info['Name'] . '&';
            }
            // CHANGE __FILE__ PATH IF LOCATED OUTSIDE MAIN PLUGIN FILE
            $plugin_data         = get_plugin_data( __FILE__ );
            $posts_with_comments = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type='post' AND comment_count > 0" );
            $data                = array(
                'url'             => base64_encode(site_url()),
                'posts'           => $count_posts->publish,
                'pages'           => $count_pages->publish,
                'comments'        => $comments_count->total_comments,
                'approved'        => $comments_count->approved,
                'spam'            => $comments_count->spam,
                'pingbacks'       => $wpdb->get_var( "SELECT COUNT(comment_ID) FROM $wpdb->comments WHERE comment_type = 'pingback'" ),
                'post_conversion' => ( $count_posts->publish > 0 && $posts_with_comments > 0 ) ? number_format( ( $posts_with_comments / $count_posts->publish ) * 100, 0, '.', '' ) : 0,
                'theme_version'   => $plugin_data['Version'],
                'theme_name'      => $theme_name,
                'site_name'       => str_replace( ' ', '', get_bloginfo( 'name' ) ),
                'plugins'         => count( get_option( 'active_plugins' ) ),
                'plugin'          => urlencode( $plugin_name ),
                'wpversion'       => get_bloginfo( 'version' ),
            );
            foreach ( $data as $k => $v ) {
                $url .= '&' . $k . '=' . $v . '';
            }
            wp_remote_get( $url );
            set_transient( 'presstrends_cache_data', $data, 60 * 60 * 24 );
            }
        }  
    
    