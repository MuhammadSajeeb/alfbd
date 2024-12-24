<?php
	
defined( 'ABSPATH' ) || exit;

?>

<p>
    <p><?php printf( esc_html__( 'Hi Admin,', 'ultimate-post-pro' ) ); ?></p>
    <?php /* translators: %1$s: Site title, %2$s: Username, %3$s: My account link */ ?>
    <p><?php printf('A user %s, has submitted a new article <a href="%s">%s</a> for review.','<strong>'.$user_display_name.'</strong>', $edit_post_url, $post_title);// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    <p><?php printf('Please review this article and provide your feedback.');// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    <p> <?php printf( esc_html__( 'Thanks, ', 'ultimate-post-pro' ) ); ?> </p>
    <p> <?php echo esc_html($site_name);?> </p>
</p>
<?php

