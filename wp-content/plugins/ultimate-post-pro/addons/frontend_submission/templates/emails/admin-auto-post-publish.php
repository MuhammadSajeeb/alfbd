<?php
	
defined( 'ABSPATH' ) || exit;

?>

<p>
    <p><?php printf( esc_html__( 'Hi Admin,', 'ultimate-post-pro' ) ); ?></p>
    <?php /* translators: %1$s: Site title, %2$s: Username, %3$s: My account link */ ?>
    <p><?php printf('A user %s, just published an article.','<strong>'.$user_display_name.'</strong>');// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    <p><?php printf('Check the article from here: <a href="%s">%s </a>',$post_permalink,$post_title );// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

    <p> <?php printf( esc_html__( 'Thanks, ', 'ultimate-post-pro' ) ); ?> </p>
    <p> <?php echo esc_html($site_name);?> </p>
</p>
<?php

