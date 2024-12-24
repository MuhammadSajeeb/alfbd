<?php
/**
 * Change Password Form
 */

defined( 'ABSPATH' ) || exit;

?>

<div class="ultp-fs-change-password-wrapper">
    <div class="ultp-fs-myaccount-posts-header ultp-fs-dashboard-heading">
        <div class="ultp-fs-myaccount-posts-header__left ultp-fs-dashboard-heading-text "> <?php echo __('Change Passsword','ultimate-post-pro'); ?></div>
        <!-- <p class="ultp-fs-myaccount-posts-header__right"> </p> -->
    </div>
    <form class="ultp-fs-change-password-form" action="" method="post" >
        <div class="ultp-fs-account-field ultp-fs-mb30">
            <label class="ultp-fs-input-label" for="current_password"><?php esc_html_e( 'Current password', 'ultimate-post-pro' ); ?></label>
            <input type="password" class="ultp-title-sm ultp-fs-input-field" name="current_password" id="current_password" autocomplete="off" />
        </div>
        <div class="ultp-fs-account-field ultp-fs-mb30">
            <label class="ultp-fs-input-label" for="new_password"><?php esc_html_e( 'New password', 'ultimate-post-pro' ); ?></label>
            <input type="password" class="ultp-title-sm ultp-fs-input-field" name="new_password" id="new_password" autocomplete="off" />
        </div>
        <div class="ultp-fs-account-field ultp-fs-mb30">
            <label class="ultp-fs-input-label" for="confirm_password"><?php esc_html_e( 'Confirm new password', 'ultimate-post-pro' ); ?></label>
            <input type="password" class="ultp-title-sm ultp-fs-input-field" name="confirm_password" id="confirm_password" autocomplete="off" />
        </div>
        <div>
            <?php wp_nonce_field( 'change_password', 'change-password-nonce' ); ?>
            <button type="submit" class="ultp-fs-btn ultp-title-sm" name="change_password" value="<?php esc_attr_e( 'Change Password', 'ultimate-post-pro' ); ?>"><?php esc_html_e( 'Save Changes', 'ultimate-post-pro' ); ?></button>
            <input type="hidden" name="action" value="change_password" />
        </div>
    </form>

</div>