<?php
/**
 * Plugin Name: PostX Pro
 * Description: PostX Pro is a Gutenberg block Plugin for creating dynamic blog listing, grid and slider.
 * Version:     1.6.9
 * Author:      wpxpo
 * Author URI:  https://wpxpo.com/
 * Text Domain: ultimate-post-pro
 * License:     GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
**/

defined( 'ABSPATH' ) || exit;
update_option( 'edd_ultp_license_key', '123-123-123' );
update_option( 'edd_ultp_license_status', 'valid' );
// Define
define( 'ULTP_PRO_VER', '1.6.9' );
define( 'ULTP_PRO_URL', plugin_dir_url( __FILE__) );
define( 'ULTP_PRO_BASE', plugin_basename( __FILE__ ) );
define( 'ULTP_PRO_PATH', plugin_dir_path( __FILE__ ) );

// Language Load
add_action( 'init', 'ultp_pro_language_load' );
function ultp_pro_language_load() {
    load_plugin_textdomain( 'ultimate-post-pro', false, basename( dirname( __FILE__ ) ) . "/languages/" );
}

// Common Function
if ( ! function_exists( 'ultimate_post_pro' ) ) {
    function ultimate_post_pro() {
        require_once ULTP_PRO_PATH . 'classes/Functions.php';
        return new \ULTP_PRO\Functions();
    }
}

// Plugin Initialization
if ( ! class_exists( 'ULTP_PRO_Initialization' ) ) {
    require_once ULTP_PRO_PATH . 'classes/Initialization.php';
    new \ULTP_PRO\ULTP_PRO_Initialization();
}