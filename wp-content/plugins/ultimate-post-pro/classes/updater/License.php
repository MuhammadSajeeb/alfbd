<?php
namespace ULTP_PRO;

defined( 'ABSPATH' ) || exit;

class License {

    private $page_slug   = 'ultp-settings#license';
    private $server_url  = 'https://www.wpxpo.com';
    private $item_id     = 181;
    private $name        = 'PostX Pro';
    private $version     = ULTP_PRO_VER;
    private $slug        = 'ultimate-post-pro/ultimate-post-pro.php';

    public function __construct() {
        add_action( 'admin_init',    array( $this, 'edd_license_updater' ) );
        add_action( 'admin_init',    array( $this, 'server_check_callback' ) );
    }
    
    public function server_check_callback() {
        $day = date("d");
        if ( 14 == $day || 28 == $day ) { // Every Day Number 14 or 28 Check
            $check = ultimate_post_pro()->get_option_without_cache( '_transient_wpxpo_license_server_check' );
            if ( $check != 'checked' ) {
                ultimate_post_pro()->set_transient_without_cache( 'wpxpo_license_server_check', 'checked', MONTH_IN_SECONDS ); // every 30 days
                $license_key = trim( ultimate_post_pro()->get_option_without_cache( 'edd_ultp_license_key' ) );
                $this->edd_activate_license( $license_key );
            }
        }
    }

    public function edd_license_updater() {
        if ( ! class_exists( 'EDD_SL_Plugin_Updater' ) ) {
            require_once ULTP_PRO_PATH.'classes/updater/EDD_SL_Plugin_Updater.php';
        }

        $license_key = trim( ultimate_post_pro()->get_option_without_cache( 'edd_ultp_license_key' ) );

        $edd_updater = new \EDD_SL_Plugin_Updater(
            $this->server_url,
            $this->slug,
            array(
                'version' => $this->version,
                'license' => $license_key,
                'item_id' => $this->item_id,
                'author'  => $this->name,
                'url'     => home_url(),
                'beta'    => false,
            )
        );
    }


    public function edd_activate_license( $license = '' ) {
        if ( $license ) {
            if ( $license != '******************' ) {
                update_option( 'edd_ultp_license_key', $license );
                $api_params = array(
                    'edd_action' => 'activate_license',
                    'license'    => $license,
                    'item_id'    => $this->item_id,
                    'url'        => home_url()
                );
        
                $response = wp_remote_post( $this->server_url, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

                if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
                    $message =  ( is_wp_error( $response ) && ! empty( $response->get_error_message() ) ) ? $response->get_error_message() : __('An error occurred, please try again.', 'ultimate-post-pro');
                } else {
                    $license_data = json_decode( wp_remote_retrieve_body( $response ) );
                    update_option( 'edd_ultp_license_status', $license_data->license );
                    update_option( 'edd_ultp_license_expire', $license_data->expires );
                    wp_redirect( admin_url( 'admin.php?page=' . $this->page_slug ) );
                    exit();
                }
            }
        }
    }


}