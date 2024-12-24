<?php
/**
 * My Account navigation
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$my_account_nav = array(
    array('id'=>'dashboard','endpoint'=>'','label'=>__('Dashboard','ultimate-post-pro'),'iconClass'=>'','class'=>'', 'iconUrl'=> ULTP_PRO_URL . 'assets/img/frontend_submission/navigation/dashboard.svg'),
    array('id'=>'edit_account','endpoint' => ultimate_post_pro()->get_setting('ultp_frontend_submission_myaccount_edit_account_endpoint')?:'fs-edit-account','label'=>__('Edit Account','ultimate-post-pro'),'iconClass'=>'','class'=>'', 'iconUrl'=> ULTP_PRO_URL . 'assets/img/frontend_submission/navigation/edit_account.svg'),
    array('id'=>'my_posts','endpoint' => ultimate_post_pro()->get_setting('ultp_frontend_submission_myaccount_my_posts_endpoint')?:'fs-my-posts','label'=>__('My Posts','ultimate-post-pro'),'iconClass'=>'','class'=>'', 'iconUrl'=> ULTP_PRO_URL . 'assets/img/frontend_submission/navigation/my_posts.svg'),
    array('id'=>'change_password','endpoint' => ultimate_post_pro()->get_setting('ultp_frontend_submission_myaccount_change_password_endpoint')?:'fs-change-password','label'=> __('Change Password','ultimate-post-pro'),'iconClass'=>'','class'=>'', 'iconUrl'=> ULTP_PRO_URL . 'assets/img/frontend_submission/navigation/change_pass.svg'),
    array('id'=>'logout','endpoint' =>'fs-logout','label'=> __('Log out','ultimate-post-pro'),'iconClass'=>'','class'=>'', 'iconUrl'=> ULTP_PRO_URL . 'assets/img/frontend_submission/navigation/logout.svg'),
);


do_action( 'ultp_frontend_submission_before_account_navigation' );

?>

<nav class="ultp-frontend-submission-myaccount-navigation">
	<ul>
		<?php foreach ( $my_account_nav as $list ) : ?>
			<li class="ultp-title-sm <?php echo $list['endpoint'] ==  $this->get_current_endpoint()?  'active ' : ''; echo esc_attr($list['class']); ?>">
				<a href="<?php echo $this->get_endpoint_url($list['endpoint']);?>" class="ultp-fs-center">
				<?php 
					// echo ultimate_post_pro()->svg_icon('media_document');

					echo  file_get_contents( $list['iconUrl']); 

					echo esc_html( $list['label'] ); 
				?>
				<!-- <?php ?> -->
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
    <?php do_action( 'ultp_frontend_submission_after_account_navigation_list' ); ?>
</nav>

<?php do_action( 'ultp_frontend_submission_after_account_navigation' ); ?>
