<?php
	
defined( 'ABSPATH' ) || exit;

?>

<p>
    <p><?php printf( esc_html__( 'Hi,', 'ultimate-post-pro' ) ); ?></p>
    <p><?php printf('Your article  %s has been submitted.',$post_title);// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> </p>
    <p><?php printf("We've received your article submission.Our team will review it, and get back to you with feedback soon.");// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    <p> <?php printf('Regards,'); ?> </p>
    <p> <?php echo esc_html($site_name);?> </p>
</p>
<?php
