<?php

    // retrieve our license key from the DB
    $licenses = get_option( 'wprss_settings_license_keys' );
    $kf_license_key = ( isset( $licenses['kf_license_key'] ) ) ? $licenses['kf_license_key'] : FALSE; 
     
    // setup the updater
    if ( !class_exists( 'EDD_SL_Plugin_Updater' ) )        
        // load our custom updater
        include ( WPRSS_KF_INC_DIR . 'libraries/EDD_licensing/EDD_SL_Plugin_Updater.php' ); 
        
        $edd_updater = new EDD_SL_Plugin_Updater( WPRSS_KF_SL_STORE_URL, WPRSS_KF_PATH, array( 
            'version'   => '1.5',                   // current version number
            'license'   => $kf_license_key,         // license key (used get_option above to retrieve from DB)
            'item_name' => WPRSS_KF_SL_ITEM_NAME,   // name of this plugin
            'author'    => 'Jean Galea'             // author of this plugin
        )
    );


    add_action( 'admin_init', 'wprss_kf_activate_deactivate_license' );    
    /**
     * Handles the activation/deactivation process 
     * 
     * @since 1.0
     */
    function wprss_kf_activate_deactivate_license() {
  
        // listen for our activate button to be clicked
        if( isset( $_POST['wprss_kf_license_activate'] ) || isset( $_POST['wprss_kf_license_deactivate'] ) ) {

            // run a quick security check 
            if( ! check_admin_referer( 'wprss_kf_license_nonce', 'wprss_kf_license_nonce' ) )   
                return; // get out if we didn't click the Activate/Deactivate button
             
            // retrieve the license keys and statuses from the database
            $license_keys = get_option( 'wprss_settings_license_keys' );
            $kf_license = trim( $license_keys['kf_license_key'] );
            $license_statuses = get_option( 'wprss_settings_license_statuses' );
     
            if ( isset( $_POST['wprss_kf_license_activate'] ) ) {

                // data to send in our API request
                $api_params = array( 
                    'edd_action'=> 'activate_license', 
                    'license'   => $kf_license, 
                    'item_name' => urlencode( WPRSS_KF_SL_ITEM_NAME ) // the name of our product in EDD
                );
            }

            else if ( isset( $_POST['wprss_kf_license_deactivate'] ) ) {

                // data to send in our API request 
                $api_params = array( 
                    'edd_action'=> 'deactivate_license', 
                    'license'   => $kf_license, 
                    'item_name' => urlencode( WPRSS_KF_SL_ITEM_NAME ) // the name of our product in EDD
                );
            }

            // Call the custom API.
            $response = wp_remote_get( add_query_arg( $api_params, WPRSS_KF_SL_STORE_URL ) );

            // make sure the response came back okay
            if ( is_wp_error( $response ) )
                return false;
            
            // decode the license data
            $license_data = json_decode( wp_remote_retrieve_body( $response ) );
     
            // $license_data->license will be either "active" or "inactive"
         
            $license_statuses['kf_license_status'] = $license_data->license;
            update_option( 'wprss_settings_license_statuses', $license_statuses );
        }
    }


    /**
     * Returns an array of the default license settings. Used for plugin activation.
     *
     * @since 1.0
     *
     */
    function wprss_kf_get_default_settings_licenses() {

        // Set up the default license settings
        $settings = apply_filters( 
            'wprss_kf_get_default_settings_licenses',
            array(
                'kf_license_key' => FALSE,  
                'kf_license_status' => 'invalid'
            )
        );

        // Return the default settings
        return $settings;
    }    


    /**
     * Initialize settings to default ones if they are not yet set
     *
     * @since 1.0
     */
    function wprss_kf_licenses_settings_initialize() {
        // Get the settings from the database, if they exist
        $license_keys = get_option( 'wprss_settings_license_keys' );
        $license_statuses = get_option( 'wprss_settings_license_statuses' );
        $default_kf_license_settings = wprss_kf_get_default_settings_licenses();

        if ( FALSE == $license_keys && FALSE == $license_statuses ) { 
            $license_keys['kf_license_key'] = $default_kf_license_settings['kf_license_key'];
            $license_statuses['kf_license_status'] = $default_kf_license_settings['kf_license_status'];
            
            update_option( 'wprss_settings_license_keys', $license_keys );
            update_option( 'wprss_settings_license_statuses', $license_statuses );
        }

        else {

            if ( ! isset( $license_keys['kf_license_key'] ) ) {
                $license_keys['kf_license_key'] = $default_kf_license_settings['kf_license_key']; 
            }
            if ( ! isset( $license_statuses['kf_license_status'] ) ) {
                $license_statuses['kf_license_status'] = $default_kf_license_settings['kf_license_status']; 
            }

            // Update the plugin settings.
            update_option( 'wprss_settings_license_keys', $license_keys );  
            update_option( 'wprss_settings_license_statuses', $license_statuses );  
        }     
    }