<?php
namespace ULTP_PRO;

defined('ABSPATH') || exit;

class Functions {

    /**
	 * Setup class.
	 *
	 * @since v.1.0.0
	 */
    public function __construct() {
        if ( ! isset( $GLOBALS['ultp_settings'])) {
            $GLOBALS['ultp_settings'] = get_option('ultp_options');
            $GLOBALS['ultp_settings']['date_format'] = get_option('date_format');
            $GLOBALS['ultp_settings']['time_format'] = get_option('time_format');
        }
    }

    public function get_yoast_meta( $post_id = 0 ) {
        return get_post_meta( $post_id, '_yoast_wpseo_metadesc', true );
    }

    public function get_rankmath_meta( $post_id = 0 ) {
        return get_post_meta( $post_id, 'rank_math_description', true );
    }
    
    public function get_aioseo_meta( $post_id = 0 ) {
        return get_post_meta( $post_id, '_aioseo_description', true );
    }

    public function get_seopress_meta( $post_id = 0 ) {
        return get_post_meta( $post_id, '_seopress_titles_desc', true );
    }
    
    public function get_squirrly_meta( $post_id = 0 ) {
        if ( $post_id ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare("SELECT * FROM `" . $wpdb->prefix . "qss` WHERE URL = %s OR URL = %s", urldecode_deep( get_permalink( $post_id ) ), get_permalink( $post_id ) ), OBJECT );
            if ( isset( $row->seo ) ) {
                $data = maybe_unserialize( $row->seo );
                if ( isset( $data['description'] ) ) {
                    return $data['description'];
                }
            }
        }
        return '';
    }
    

    // is Free Plugin Ready
    public function is_ultp_free_ready() {
        if ( file_exists( WP_PLUGIN_DIR . '/ultimate-post/ultimate-post.php' ) ) {
            $active_plugins = get_option( 'active_plugins', array() );
            if ( is_multisite() ) {
                $active_plugins = array_merge( $active_plugins, array_keys( get_site_option( 'active_sitewide_plugins', array() ) ) );
            }
            if ( in_array( 'ultimate-post/ultimate-post.php', $active_plugins ) ) {
                return true;
            }
        }
        return false;
    }
    

     /**
	 * Get Global Plugin Settings
     * 
     * @since v.1.0.0
     * @param STRING | Key of the Option
	 * @return ARRAY | STRING
	 */
    public function get_setting( $key = '' ) {
        $data = $GLOBALS['ultp_settings'];
        if ( $key != '' ) {
            return isset( $data[$key] ) ? $data[$key] : '';
        } else {
            return $data;
        }
    }


    /**
     * Get Option Value bypassing cache
     * Inspired By WordPress Core get_option
     * @since v.3.1.6
     * @param string $option Option Name.
     * @param boolean $default_value option default value.
     * @return mixed
     */
    public function get_option_without_cache( $option, $default_value = false ) {
        global $wpdb;

        if ( is_scalar( $option ) ) {
            $option = trim( $option );
        }
    
        if ( empty( $option ) ) {
            return false;
        }

        $value = $default_value;

        $row = $wpdb->get_row( $wpdb->prepare( "SELECT option_value FROM $wpdb->options WHERE option_name = %s LIMIT 1", $option ) );

        if ( is_object( $row ) ) {
            $value = $row->option_value;
        } else {
            return apply_filters( "ultp_default_option_{$option}", $default_value, $option );
        }

        return apply_filters( "ultp_option_{$option}", maybe_unserialize( $value ), $option );
    }


    /**
     * Set transient without adding to the cache
     * Inspired By WordPress Core set_transient
     * @since v.3.1.6
     * @param string $transient Transient Name.
     * @param mixed $value Transient Value.
     * @param integer $expiration Time until expiration in seconds.
     * @return bool
     */
    public function set_transient_without_cache( $transient, $value, $expiration = 0 ) {
        $expiration = (int) $expiration;
        $transient_timeout = '_transient_timeout_' . $transient;
		$transient_option  = '_transient_' . $transient;

        $result = false;

        if ( false === $this->get_option_without_cache( $transient_option ) ) {
			$autoload = 'yes';
			if ( $expiration ) {
				$autoload = 'no';
				$this->add_option_without_cache( $transient_timeout, time() + $expiration, 'no' );
			}
			$result = $this->add_option_without_cache( $transient_option, $value, $autoload );
		} else {
			/*
			 * If expiration is requested, but the transient has no timeout option,
			 * delete, then re-create transient rather than update.
			 */
			$update = true;

			if ( $expiration ) {
				if ( false === $this->get_option_without_cache( $transient_timeout ) ) {
					delete_option( $transient_option );
					$this->add_option_without_cache( $transient_timeout, time() + $expiration, 'no' );
					$result = $this->add_option_without_cache( $transient_option, $value, 'no' );
					$update = false;
				} else {
					update_option( $transient_timeout, time() + $expiration );
				}
			}

			if ( $update ) {
				$result = update_option( $transient_option, $value );
			}
		}

        return $result;

    }

    /**
     * Add option without adding to the cache
     * Inspired By WordPress Core set_transient
     * @since v.3.1.6
     * @param string $option option name.
     * @param string $value option value.
     * @param string $autoload whether to load wordpress startup.
     * @return bool
     */
    public function add_option_without_cache( $option, $value = '', $autoload = 'yes' ) {
        global $wpdb;
        
        if ( is_scalar( $option ) ) {
            $option = trim( $option );
        }
    
        if ( empty( $option ) ) {
            return false;
        }
    
        wp_protect_special_option( $option );
    
        if ( is_object( $value ) ) {
            $value = clone $value;
        }
    
        $value = sanitize_option( $option, $value );
    
        // Make sure the option doesn't already exist.    
        if ( apply_filters( "ultp_default_option_{$option}", false, $option, false ) !== $this->get_option_without_cache( $option ) ) {
            return false;
        }
    
        $serialized_value = maybe_serialize( $value );
        $autoload         = ( 'no' === $autoload || false === $autoload ) ? 'no' : 'yes';
    
        $result = $wpdb->query( $wpdb->prepare( "INSERT INTO `$wpdb->options` (`option_name`, `option_value`, `autoload`) VALUES (%s, %s, %s) ON DUPLICATE KEY UPDATE `option_name` = VALUES(`option_name`), `option_value` = VALUES(`option_value`), `autoload` = VALUES(`autoload`)", $option, $serialized_value, $autoload ) );
        
        if ( ! $result ) {
            return false;
        }
    
        return true;
    }


    /**
	 * Plugin Install and Active Action
     * 
     * @since v.1.6.8
	 * @return STRING | Redirect URL
	*/
    public function activate_postx_plugin() {
        if ( ! file_exists( WP_PLUGIN_DIR . '/ultimate-post/ultimate-post.php' ) ) {
            include(ABSPATH . 'wp-admin/includes/plugin-install.php');
            include(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');
    
            if ( ! class_exists( 'Plugin_Upgrader' ) ) {
                include( ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php');
            }
            // if ( ! class_exists( 'Plugin_Installer_Skin' ) ) {
            //     include( ABSPATH . 'wp-admin/includes/class-plugin-installer-skin.php' );
            // }
            if ( ! class_exists( 'WP_Ajax_Upgrader_Skin' ) ) {
                include ABSPATH . 'wp-admin/includes/class-wp-ajax-upgrader-skin.php';
            }
    
            $api = plugins_api( 'plugin_information', array(
                'slug' => 'ultimate-post',
                'fields' => array(
                    'short_description' => false,
                    'sections' => false,
                    'requires' => false,
                    'rating' => false,
                    'ratings' => false,
                    'downloaded' => false,
                    'last_updated' => false,
                    'added' => false,
                    'tags' => false,
                    'compatibility' => false,
                    'homepage' => false,
                    'donate_link' => false,
                )
            ) );

            if ( is_wp_error( $api ) ) {
                wp_die( $api ); //phpcs:ignore
            }

            // $upgrader = new \Plugin_Upgrader( new \Plugin_Installer_Skin() );
            $upgrader = new \Plugin_Upgrader( new \WP_Ajax_Upgrader_Skin( compact( 'title', 'url', 'nonce', 'plugin', 'api' ) ) );
            $install_result = $upgrader->install( $api->download_link );
            
            if ( ! is_wp_error( $install_result ) ) {
                activate_plugin( 'ultimate-post/ultimate-post.php' );
                die();
            }
        } else {
            activate_plugin( 'ultimate-post/ultimate-post.php' );
        }
        wp_redirect( admin_url( 'admin.php?page=ultp-settings#home' ) );
        die();
    }
}
