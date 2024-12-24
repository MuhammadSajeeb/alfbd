<?php
/**
 * Frontend Submission Login form
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( is_user_logged_in() ) {
	return;
}
do_action('ultp_frontend_submission_before_login_form_render');

?>
<div class="ultp-fs-login-form-wrapper">
    <h2 class="ultp-fs-login-heading"> <?php echo esc_html__('Sign In','ultimate-post-pro'); ?></h2>
    <h2 class="ultp-fs-login-subheading"> 
        <?php echo sprintf(__('New Member? <a class="ultp-fs-register-url" href="%s"> Create New Account </a>','ultimate-post-pro'),$register_url); ?> 
    </h2>
    <form method="post" class="ultp-fs-recaptcha-form">
        <?php do_action( 'ultp_frontend_submission_login_form_start' ); ?>
        <div class="ultp-fs-login-form-field ultp-fs-mb30">
            <label class="ultp-fs-input-label" for="username"><?php esc_html_e( 'Username/Email', 'ultimate-post-pro' ); ?>&nbsp;<span class="required">*</span></label>
            <input type="text" class="ultp-title-sm ultp-fs-input-field" name="username" id="username" autocomplete="username" placeholder="Enter Your Name" />
        </div>
        <div class="ultp-fs-login-form-field ultp-fs-mb30">
            <label class="ultp-fs-input-label" for="password"><?php esc_html_e( 'Password', 'ultimate-post-pro' ); ?>&nbsp;<span class="required">*</span></label>
            <input class="ultp-title-sm ultp-fs-input-field" type="password" name="password" id="password" autocomplete="current-password" placeholder="Enter Your Password" />
        </div>
        <?php do_action( 'ultp_frontend_submission_login_form' ); ?>
        <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>" class="ultp-fs-lost_password ultp-fs-mb30"><?php esc_html_e( 'Forgot your password?', 'ultimate-post-pro' ); ?></a>
        <div>
            <?php wp_nonce_field( 'ultp-fs-login', 'ultp-fs-nonce' ); ?>
            <input type="hidden" name="redirect" value="<?php echo esc_url( $redirect_url ); ?>" />
            <button type="submit" class="ultp-fs-btn ultp-title-sm" name="login" value="<?php esc_attr_e( 'Sign in', 'ultimate-post-pro' ); ?>"><?php esc_html_e( 'Sign in', 'ultimate-post-pro' ); ?></button>
        </div>
        <?php do_action( 'ultp_frontend_submission_login_form_end' ); ?>
    </form>
</div>
