<?php
/**
 * Frontend Submission Registration form
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( is_user_logged_in() ) {
	return;
}
do_action('ultp_frontend_submission_before_registration_form_render');
?>
<div class="ultp-fs-reg-form-wrapper">
        <h2 class="ultp-fs-reg-heading"> <?php echo esc_html__('Register New Account','ultimate-post-pro'); ?> </h2>
        <h2 class="ultp-fs-reg-subheading"> 
            <?php echo sprintf(__('Already have an account? <a class="ultp-fs-reg-url" href="%s"> Sign in </a>','ultimate-post-pro'),$login_url); ?>
        </h2>
    <form method="post" class="ultp-fs-recaptcha-form">
        <?php do_action( 'ultp_frontend_submission_registration_form_start' ); ?>
        <div class="ultp-fs-reg-form-field ultp-fs-mb30">
            <label for="username" class="ultp-fs-input-label"><?php esc_html_e( 'Username', 'ultimate-post-pro' ); ?>&nbsp;<span class="required">*</span></label>
            <input type="text" class="ultp-title-sm ultp-fs-input-field"  name="username" id="username" autocomplete="username" placeholder="Enter Your Username..." value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>"  required/>
        </div>
        <div class="ultp-fs-reg-form-field ultp-fs-mb30">
            <label for="first_name" class="ultp-fs-input-label"><?php esc_html_e( 'First Name', 'ultimate-post-pro' ); ?></label>
            <input type="text" class="ultp-title-sm ultp-fs-input-field"  name="first_name" id="first_name" autocomplete="first_name" value="<?php echo ( ! empty( $_POST['first_name'] ) ) ? esc_attr( wp_unslash( $_POST['first_name'] ) ) : ''; ?>" placeholder="Enter Your First Name..." />
        </div>
        <div class="ultp-fs-reg-form-field ultp-fs-mb30">
            <label for="last_name" class="ultp-fs-input-label"><?php esc_html_e( 'Last Name', 'ultimate-post-pro' ); ?></label>
            <input type="text" class="ultp-title-sm ultp-fs-input-field"  name="last_name" id="last_name" autocomplete="last_name" value="<?php echo ( ! empty( $_POST['last_name'] ) ) ? esc_attr( wp_unslash( $_POST['last_name'] ) ) : ''; ?>" placeholder="Enter Your Last Name..." />
        </div>
        <div class="ultp-fs-reg-form-field ultp-fs-mb30">
            <label for="email" class="ultp-fs-input-label"><?php esc_html_e( 'Email', 'ultimate-post-pro' ); ?>&nbsp;<span class="required">*</span></label>
            <input type="text" class="ultp-title-sm ultp-fs-input-field"  name="email" id="email" autocomplete="email" value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>" placeholder="Enter Your Email..." required/>
        </div>
        <div class="ultp-fs-reg-form-field ultp-fs-mb30">
            <label for="password" class="ultp-fs-input-label"><?php esc_html_e( 'Password', 'ultimate-post-pro' ); ?>&nbsp;<span class="required">*</span></label>
            <input type="password" class="ultp-title-sm ultp-fs-input-field"  name="password" id="password" autocomplete="password" placeholder="Enter Password..." required />
        </div>
        <div class="ultp-fs-reg-form-field ultp-fs-mb30">
            <label for="confirm_password" class="ultp-fs-input-label">
                <?php esc_html_e( 'Confirm Password', 'ultimate-post-pro' ); ?>&nbsp;<span class="required">*</span>
            </label>
            <input type="password" class="ultp-title-sm ultp-fs-input-field"  name="confirm_password" id="confirm_password" autocomplete="confirm_password" placeholder="Confirm Password..." required/>
        </div>
        <?php do_action( 'ultp_frontend_submission_registration_form' ); ?>
        <div>
            <?php wp_nonce_field( 'ultp-fs-registration', 'ultp-fs-nonce' ); ?>
            <input type="hidden" name="redirect" value="<?php echo esc_url( $redirect_url ); ?>" />
            <button type="submit" class="ultp-fs-btn ultp-title-sm" name="registration" value="<?php esc_attr_e( 'Registration', 'ultimate-post-pro' ); ?>"><?php esc_html_e( 'Registration', 'ultimate-post-pro' ); ?></button>
        </div>
        <?php do_action( 'ultp_frontend_submission_registration_form_end' ); ?>
    
    </form>
</div>
