<?php
/**
 * My Profile page
 *
 */

defined( 'ABSPATH' ) || exit;

?>

<div class="ultp-fs-myaccount-profile-wrapper">
    <div class="ultp-fs-myaccount-profile-header ultp-fs-dashboard-heading ">
        <p class="ultp-fs-myaccount-profile-header__left ultp-fs-dashboard-heading-text "> <?php echo __('My Profile','ultimate-post-pro'); ?></p>
     </div>
    <div class="ultp-fs-myaccount-profile"> 
        <?php foreach ( $profile_data as $data ) : ?>
            <div class="ultp-fs-myaccount-field">
                <label class="ultp-fs-myaccount-field-label ultp-fs-input-label"> <?php echo esc_html($data['label']); ?> </label>
                <span class="ultp-fs-myaccount-field-value ultp-title-sm">  <?php echo esc_html($data['value']); ?> </span>
            </div>
        <?php endforeach; ?>
    </div>
</div>