<?php
namespace ULTP_PRO;

defined( 'ABSPATH' ) || exit;

/**
 * Plugin Notice
 */
class Notice {

	/**
     * Notice Constructor
     */
    public function __construct(){
		add_action( 'admin_notices', array( $this, 'install_notice_callback' ) );
		add_action( 'wp_ajax_ultp_install', array( ultimate_post_pro(), 'activate_postx_plugin' ) );
		add_action( 'wp_ajax_ultp_dismiss_notice', array( $this, 'dismiss_notice_callback' ) );
	}

	/**
	 * Dismiss Notice Callback
     * 
     * @since v.1.0.0
	 * @return NULL
	 */
	public function dismiss_notice_callback() {
		if ( ! ( isset( $_REQUEST['wpnonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST['wpnonce'] ) ), 'ultp-nonce' ) ) ) {
			return ;
		}
		update_option( 'ultp_dismiss_notice', 'yes' );
	}

	/**
	 * Installation Notice HTML
     * 
     * @since v.1.0.0
	 * @return STRING | HTML
	 */
	public function install_notice_callback() {
		if ( ! get_option( 'ultp_dismiss_notice' ) ) {
			$this->notice_css();
			$this->notice_js();
			?>
			<div class="wc-install ultp-pro-notice">
				<img width="150" src="<?php echo esc_url(ULTP_PRO_URL.'assets/img/ultp.png'); ?>" alt="logo" />
				<div class="wc-install-body">
					<a class="wc-dismiss-notice" data-security=<?php echo esc_attr(wp_create_nonce('ultp-nonce')); ?>  data-ajax=<?php echo esc_url(admin_url('admin-ajax.php')); ?> href="#"><span class="dashicons dashicons-no-alt"></span> <?php echo esc_html__('Dismiss', 'ultimate-post-pro'); ?></a>
					<h3><?php echo esc_html__('Welcome to PostX Pro.', 'ultimate-post-pro'); ?></h3>
					<div><?php echo esc_html__('PostX Pro is a Gutenberg block plugin. To use this plugins you have to install and activate PostX Free.', 'ultimate-post-pro'); ?></div>
					<a class="ultp-install-btn button button-primary button-hero" href="<?php echo esc_url(add_query_arg(array('action' => 'ultp_install'), admin_url())); ?>"><span class="dashicons dashicons-image-rotate"></span><?php echo esc_html__('Install & Activate PostX', 'ultimate-post-pro'); ?></a>
					<div id="installation-msg"></div>
				</div>
			</div>
			<?php
		}
	}

	/**
	 * Installation Notice CSS
     * 
     * @since v.1.0.0
	 * @return STRING || CSS
	 */
	public function notice_css() {
		?>
		<style type="text/css">
            .ultp-pro-notice.wc-install {
                display: -ms-flexbox;
                display: flex;
                align-items: center;
                background: #fff;
                margin-top: 40px;
                width: calc(100% - 50px);
                border: 1px solid #ccd0d4;
                padding: 15px;
                border-radius: 4px;
            }   
            .ultp-pro-notice.wc-install img {
                margin-right: 20px; 
            }
            .ultp-pro-notice .wc-install-body {
                -ms-flex: 1;
                flex: 1;
            }
            .ultp-pro-notice .wc-install-body > div {
                max-width: 450px;
                margin-bottom: 20px;
            }
            .ultp-pro-notice .wc-install-body h3 {
                margin-top: 0;
                font-size: 24px;
                margin-bottom: 15px;
            }
            .ultp-install-btn {
                margin-top: 15px;
                display: inline-block;
            }
			.ultp-pro-notice.wc-install .dashicons{
				display: none;
				animation: dashicons-spin 1s infinite;
				animation-timing-function: linear;
			}
			.ultp-pro-notice.wc-install.loading .dashicons {
				display: inline-block;
				margin-top: 12px;
				margin-right: 5px;
			}
			@keyframes dashicons-spin {
				0% {
					transform: rotate( 0deg );
				}
				100% {
					transform: rotate( 360deg );
				}
			}
			.ultp-pro-notice .wc-dismiss-notice {
				position: relative;
				text-decoration: none;
				float: right;
				right: 26px;
			}
			.ultp-pro-notice .wc-dismiss-notice .dashicons{
				display: inline-block;
    			text-decoration: none;
				animation: none;
			}
		</style>
		<?php
	}

	/**
	 * Installation Notice JS
     * 
     * @since v.1.0.0
	 * @return STRING | JS
	 */
	public function notice_js() {
		?>
		<script type="text/javascript">
			jQuery(document).ready(function($){
				'use strict';
				$(document).on('click', '.ultp-install-btn', function(e) {
					e.preventDefault();
					const $that = $(this);
					$.ajax({
						type: 'POST',
						url: ajaxurl,
						data: { 
							action: 'ultp_install'
						},
						beforeSend: function(){
                                $that.parents('.wc-install').addClass('loading');
                        },
						success: function (res) {
							$that.parents('.wc-install').remove();
							if ( res.data ) {
								window.location.replace( res.data );
							} else {
								window.location.reload();
							}
						},
						complete: function () {
							$that.parents('.wc-install').removeClass('loading');
						}
					});
				});

				// Dismiss notice
				$(document).on('click', '.wc-dismiss-notice', function(e) {
					e.preventDefault();
					const that = $(this);
					$.ajax({
						url: that.data('ajax'),
						type: 'POST',
						data: { 
							action: 'ultp_dismiss_notice', 
							wpnonce: that.data('security')
						},
						success: function (data) {
							that.parents('.wc-install').hide("slow", function() { that.parents('.wc-install').remove(); });
						},
						error: function(xhr) {
							console.log('Error occured. Please try again' + xhr.statusText + xhr.responseText );
						},
					});
				});
				
			});
		</script>
		<?php
	}

}