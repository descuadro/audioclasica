<?php

    // retrieve our license key from the DB
    $licenses = get_option( 'wprss_settings_license_keys' );
    $ftp_license_key = ( isset( $licenses['ftp_license_key'] ) ) ? $licenses['ftp_license_key'] : FALSE; 
     
    // setup the updater
    if ( !class_exists( 'EDD_SL_Plugin_Updater' ) )
        // load our custom updater
        include ( WPRSS_FTP_INC . 'libraries/EDD_licensing/EDD_SL_Plugin_Updater.php' ); 

        $edd_updater = new EDD_SL_Plugin_Updater( WPRSS_FTP_SL_STORE_URL, WPRSS_FTP_PATH, array( 
            'version'   => WPRSS_FTP_VERSION,		// current version number
            'license'   => $ftp_license_key,        // license key (used get_option above to retrieve from DB)
            'item_name' => WPRSS_FTP_SL_ITEM_NAME,  // name of this plugin
            'author'    => 'Jean Galea'             // author of this plugin
        )
    );


    add_action( 'admin_init', 'wprss_ftp_activate_deactivate_license' );    
    /**
     * Handles the activation/deactivation process 
     * 
     * @since 1.0
     */
    function wprss_ftp_activate_deactivate_license() {

        // listen for our activate button to be clicked
        if( isset( $_POST['wprss_ftp_license_activate'] ) || isset( $_POST['wprss_ftp_license_deactivate'] ) ) {

            // run a quick security check 
            if( ! check_admin_referer( 'wprss_ftp_license_nonce', 'wprss_ftp_license_nonce' ) )   
                return; // get out if we didn't click the Activate/Deactivate button

            // retrieve the license keys and statuses from the database
            $license_keys = get_option( 'wprss_settings_license_keys' );
            $ftp_license = trim( $license_keys['ftp_license_key'] );
            $license_statuses = get_option( 'wprss_settings_license_statuses' );            
     
            if ( isset( $_POST['wprss_ftp_license_activate'] ) ) {

                // data to send in our API request
                $api_params = array( 
                    'edd_action'=> 'activate_license', 
                    'license'   => $ftp_license, 
                    'item_name' => urlencode( WPRSS_FTP_SL_ITEM_NAME ) // the name of our product in EDD
                );
            }

            else if ( isset( $_POST['wprss_ftp_license_deactivate'] ) ) {

                // data to send in our API request 
                $api_params = array( 
                    'edd_action'=> 'deactivate_license', 
                    'license'   => $ftp_license, 
                    'item_name' => urlencode( WPRSS_FTP_SL_ITEM_NAME ) // the name of our product in EDD
                );
            }

            // Call the custom API.
            $response = wp_remote_get( add_query_arg( $api_params, WPRSS_FTP_SL_STORE_URL ) );

            // make sure the response came back okay
            if ( is_wp_error( $response ) )
                return false;
            
            // decode the license data
            $license_data = json_decode( wp_remote_retrieve_body( $response ) );
     
            // $license_data->license will be either "active" or "inactive"
         
            $license_statuses['ftp_license_status'] = $license_data->license;
            update_option( 'wprss_settings_license_statuses', $license_statuses );
        }
    }    


    /**
     * Initialize settings to default ones if they are not yet set
     *
     * @since 1.0
     */
    function wprss_ftp_licenses_settings_initialize() {
        // Get the settings from the database, if they exist
        $license_keys = get_option( 'wprss_settings_license_keys' );
        $license_statuses = get_option( 'wprss_settings_license_statuses' );
        $default_ftp_license_settings = WPRSS_FTP::get_instance()->get_default_settings_licenses();

        if ( FALSE == $license_keys && FALSE == $license_statuses ) { 
            $license_keys['ftp_license_key'] = $default_ftp_license_settings['ftp_license_key'];
            $license_statuses['ftp_license_status'] = $default_ftp_license_settings['ftp_license_status'];
            
            update_option( 'wprss_settings_license_keys', $license_keys );
            update_option( 'wprss_settings_license_statuses', $license_statuses );
        }

        else {

            if ( ! isset( $license_keys['ftp_license_key'] ) ) {
                $license_keys['ftp_license_key'] = $default_ftp_license_settings['ftp_license_key']; 
            }
            if ( ! isset( $license_statuses['ftp_license_status'] ) ) {
                $license_statuses['ftp_license_status'] = $default_ftp_license_settings['ftp_license_status']; 
            }

            // Update the plugin settings.
            update_option( 'wprss_settings_license_keys', $license_keys );  
            update_option( 'wprss_settings_license_statuses', $license_statuses );  
        }     
    }
