<?php
/**
 * Edit account form
 */

defined( 'ABSPATH' ) || exit;

do_action( 'ultp_frontend_submission_before_edit_account_form' ); ?>

<form class="ultp-fs-edit-account-form" action="" method="post" >
	<div class="ultp-fs-myaccount-posts-header ultp-fs-dashboard-heading">
        <div class="ultp-fs-dashboard-heading-text "> <?php echo __('Edit Account','ultimate-post-pro'); ?></div>
    </div>
	<div class="ultp-fs-edit-account-form-field ultp-fs-mb30">
		<label for="first_name" class="ultp-fs-input-label"><?php esc_html_e( 'First name', 'ultimate-post-pro' ); ?>&nbsp;<span class="required">*</span></label>
		<input type="text" class="ultp-title-sm ultp-fs-input-field" name="first_name" id="first_name" autocomplete="first_name" value="<?php echo esc_attr( $user->first_name ); ?>" />
	</div>
	<div class="ultp-fs-edit-account-form-field ultp-fs-mb30">
		<label for="last_name" class="ultp-fs-input-label"><?php esc_html_e( 'Last name', 'ultimate-post-pro' ); ?>&nbsp;<span class="required">*</span></label>
		<input type="text" class="ultp-title-sm ultp-fs-input-field" name="last_name" id="last_name" autocomplete="last_name" value="<?php echo esc_attr( $user->last_name ); ?>" />
	</div>

	<div class="ultp-fs-edit-account-form-field ultp-fs-mb30">
		<label for="display_name" class="ultp-fs-input-label"><?php esc_html_e( 'Display name', 'ultimate-post-pro' ); ?>&nbsp;<span class="required">*</span></label>
		<input type="text" class="ultp-title-sm ultp-fs-input-field" name="display_name" id="display_name" value="<?php echo esc_attr( $user->display_name ); ?>" /> <span class="ultp-fs-input-notice ultp-title-sm"><em><?php esc_html_e( 'This will be how your name will be displayed in the account and author section.', 'ultimate-post-pro' ); ?></em></span>
	</div>

	<div class="ultp-fs-edit-account-form-field ultp-fs-mb30">
		<label for="email" class="ultp-fs-input-label"><?php esc_html_e( 'Email address', 'ultimate-post-pro' ); ?>&nbsp;<span class="required">*</span></label>
		<input type="email" class="ultp-title-sm ultp-fs-input-field" name="email" id="email" autocomplete="email" value="<?php echo esc_attr( $user->user_email ); ?>" />
	</div>

	<div>
		<?php wp_nonce_field( 'save_account_data', 'ultp-fs-nonce' ); ?>
		<button type="submit" class="ultp-fs-btn ultp-title-sm" name="save_account_data" value="<?php esc_attr_e( 'Save changes', 'ultimate-post-pro' ); ?>"><?php esc_html_e( 'Save changes', 'ultimate-post-pro' ); ?></button>
		<input type="hidden" name="action" value="save_account_data" />
	</div>
</form>

<?php do_action( 'ultimate-post_after_edit_account_form' ); ?>
