<?php
	
defined( 'ABSPATH' ) || exit;

?>

<p>
    <p><?php printf( esc_html__( 'Hi,', 'ultimate-post-pro' ) ); ?></p>
    <p><?php printf('Congratulations! Your article <a href="%s"> %s</a> has been reviewed and published.',$post_permalink,$post_title);// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> </p>
    <p><?php printf('Thank you for your contribution.');// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    <p> <?php printf('Regards,'); ?> </p>
    <p> <?php echo esc_html($site_name);?> </p>
</p>
<?php

