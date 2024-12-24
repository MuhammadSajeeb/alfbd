<?php
namespace ULTP_PRO;

defined( 'ABSPATH' ) || exit;

/**
 * Plugin Initialization
 */
class ULTP_PRO_Initialization{

    /**
     * Plugin Constructor
     */
    public function __construct(){
        $this->requires();
        add_filter( 'plugin_action_links_' . ULTP_PRO_BASE, array( $this, 'plugin_action_links_callback' ) );
        add_action( 'activated_plugin', array( $this, 'activation_redirect' ) );
    }

    /**
	 * Require File for PostX
     * 
     * @since v.1.0.0
	 * @return NULL
	 */
    public function requires() {
        if ( ultimate_post_pro()->is_ultp_free_ready() ) {
            $this->include_addons();
        } else {
            require_once ULTP_PRO_PATH.'classes/Notice.php';
            new \ULTP_PRO\Notice();
        }
        require_once ULTP_PRO_PATH.'classes/updater/License.php';
        new \ULTP_PRO\License();
    }

    /**
	 * Include Addons directory
     * 
     * @since v.1.0.0
	 * @return NULL
	 */
	public function include_addons() {
		$addons_dir = array_filter( glob( ULTP_PRO_PATH . 'addons/*' ), 'is_dir' );
		if ( count( $addons_dir ) > 0 ) {
			foreach ( $addons_dir as $key => $value ) {
				$addon_dir_name = str_replace( dirname( $value ) . '/', '', $value );
				$file_name = ULTP_PRO_PATH . 'addons/' . $addon_dir_name . '/init.php';
				if ( file_exists( $file_name ) ) {
					include_once $file_name;
				}
			}
		}
    }

    /**
	 * Add Settings Link to in plugin.php Page
     * 
     * @since v.1.6.5
	 * @return ARRAY
	 */
    public function plugin_action_links_callback( $links ) {
        $setting_link = array();
        if ( defined( 'ULTP_VER' ) ) {
            $setting_link['ultp_settings'] = '<a href="'. esc_url( admin_url( 'admin.php?page=ultp-settings#settings' ) ) .'">' . esc_html__( 'Settings', 'ultimate-post-pro' ) . '</a>';
        }
        return array_merge( $setting_link, $links );
    }

    /**
	 * Redirect after Plugin is Activated to License Page
     * 
     * @since v.1.6.5
	 * @return NULL
	 */
    public function activation_redirect( $plugin ) {
        if ( $plugin == 'ultimate-post-pro/ultimate-post-pro.php' ) {
            ultimate_post_pro()->activate_postx_plugin();
        }
    }
}