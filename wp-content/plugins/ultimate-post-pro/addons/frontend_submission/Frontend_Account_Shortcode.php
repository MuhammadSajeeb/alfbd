<?php
/**
 * My Account Shortcodes
 *
 * Shows the 'my account' section where the customer can view past orders and update their information.
 */
namespace ULTP_PRO;

defined( 'ABSPATH' ) || exit;

/**
 * Shortcode my account class.
 */
class Frontend_Account_Shortcode {

	/**
	 * Get the shortcode content.
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string
	 */
	public static function get( $atts ) {
		return Frontend_Submission::shortcode_wrapper( array( __CLASS__, 'output' ), $atts );
	}

	/**
	 * Output the shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 */
	public static function output( $atts ) {
		global $wp;

		wp_enqueue_script('ultp_fs_script',ULTP_PRO_URL.'assets/js/ultp_fs_dashboard.js',array('jquery'),ULTP_PRO_VER,true);
        $notice = apply_filters( 'ultp_fs_notice', '');
		// active
        echo '<div class=" ultp-fs-db-notice" id="ultp-fs-notice">'.$notice. '</div>';

		if(isset($_GET['fs_notice']) && 'rs'==sanitize_text_field($_GET['fs_notice']) ) {
			echo '<div class=" ultp-fs-db-notice active" id="ultp-fs-notice">Registration Successful. Please Login.</div>';
			unset($_GET['fs_notice']);
		}
		

		if ( ! is_user_logged_in() || isset( $wp->query_vars['ultp-lost-password'] ) ) {
            //Login or password reset form
			if(isset($wp->query_vars['ultp-lost-password'])) {

			} elseif(isset($wp->query_vars['fs-register'])) {				
				self::registration_form();
			} else {
				self::login_form();
			}
		} else {
			// Start output buffer since the html may need discarding for BW compatibility.
			ob_start();

			if ( isset( $wp->query_vars['fs-logout'] ) ) {
				$logout_redirect_url = apply_filters('ultp_frontend_submission_logout_redirect_url','');
				$logout_url = wp_logout_url( $logout_redirect_url );
				/* translators: %s: logout url */
				echo sprintf( __( '<div class="active ultp-fs-db-notice" id="ultp-fs-notice">Are you sure you want to log out?  <a class="ultp-fs-logut-btn" href="%s">Confirm and log out!</a></div>', 'ultimate-post-pro' ), $logout_url ) ;
				return;
			}

			// Output the new account page.
			self::my_account( $atts );

			// Send output buffer.
			ob_end_flush();
		}
	}

	/**
	 * My account page.
	 *
	 * @param array $atts Shortcode attributes.
	 */
	private static function my_account( $atts ) {
		require_once ULTP_PRO_PATH.'/addons/frontend_submission/templates/my-account.php';
	}


	/**
	 * Edit account details page.
	 */
	public static function edit_account() {
        require_once ULTP_PRO_PATH.'/addons/frontend_submission/templates/edit-account.php';
	}

	public static function login_form() {
		$Frontend_Submission = new Frontend_Submission();
		$redirect_url = apply_filters('ultp_frontend_submission_login_redirect_url','');
		$register_url = apply_filters('ultp_frontend_submission_register_url',$Frontend_Submission->get_endpoint_url(ultimate_post()->get_setting('ultp_frontend_submission_register_endpoint')?:'fs-register'));
		require_once ULTP_PRO_PATH.'/addons/frontend_submission/templates/login-form.php';
	}

	public static function registration_form() {
		$Frontend_Submission = new Frontend_Submission();

		$login_url = $Frontend_Submission->get_endpoint_url('dashboard');
		$redirect_url = apply_filters('ultp_frontend_submission_registration_redirect_url','');

		include ULTP_PRO_PATH.'/addons/frontend_submission/templates/registration-form.php';
	}

	

}
