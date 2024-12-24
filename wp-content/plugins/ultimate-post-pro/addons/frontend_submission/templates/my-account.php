<?php
/**
 * My Account page
 *
 */

defined( 'ABSPATH' ) || exit;

do_action('ultp_frontend_submission_account_header');
/**
 * My Account navigation.
 *
 */
?>
<div class="ultp_frontend_submission_my_account"> <?php
do_action( 'ultp_frontend_submission_account_navigation' ); ?>

<div class="ultp-frontend-submission-myaccount-content">
	<?php
		/**
		 * My Account content.
		 *
		 */
		do_action( 'ultp_frontend_submission_account_content' );
	?>
</div>
</div>
