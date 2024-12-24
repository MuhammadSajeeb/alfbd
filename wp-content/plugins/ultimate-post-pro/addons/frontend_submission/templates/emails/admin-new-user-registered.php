<?php
	
defined( 'ABSPATH' ) || exit;

?>

<p>
    <p><?php printf( esc_html__( 'Hi Admin,', 'ultimate-post-pro' ) ); ?></p>
    <?php /* translators: %1$s: Site title, %2$s: Username, %3$s: My account link */ ?>
    <p><?php printf('A user %s, Has been registered.','<strong>'.$user_display_name.'</strong>');// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    <p> <?php printf( esc_html__( 'Regards,, ', 'ultimate-post-pro' ) ); ?> </p>
    <p> <?php echo esc_html($site_name);?> </p>
</p>
<?php
