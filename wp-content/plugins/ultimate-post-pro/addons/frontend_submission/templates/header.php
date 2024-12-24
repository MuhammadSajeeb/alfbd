<?php
/**
 * My Account Header
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
$user_id = get_current_user_id();
$user_data = get_userdata($user_id);
$profile_pic = get_avatar_url($user_id);
$display_name = $user_data->display_name;
$email = $user_data->user_email;
?>
<div class="ultp-fs-header ultp-fs-center"> 
    <div class="ultp-fs-header__img-wrapper"> <img src="<?php echo $profile_pic; ?>" alt="<?php echo $display_name; ?>" class="ultp-fs-header__img"/> </div>
    <div class="ultp-fs-header__data"> 
        <div class="ultp-fs-header__data-name"> <?php echo  $display_name; ?></div>
        <div class="ultp-fs-header__data-email"> <?php echo $email; ?> </div>
    </div>
</div>