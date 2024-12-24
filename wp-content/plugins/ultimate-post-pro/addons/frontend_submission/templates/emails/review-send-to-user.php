<?php
	
defined( 'ABSPATH' ) || exit;

?>

<p>
    <p><?php printf( esc_html__( 'Hi,', 'ultimate-post-pro' ) ); ?></p>
    <p><?php printf('Your article <a href="%s"> %s</a> has been reviewed.',$edit_post_url,$post_title);// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> </p>
    <p><?php printf('Please make the suggested changes and resubmit for further review.');// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    <p> <?php printf('Regards,'); ?> </p>
    <p> <?php echo esc_html($site_name);?> </p>
</p>
<?php
