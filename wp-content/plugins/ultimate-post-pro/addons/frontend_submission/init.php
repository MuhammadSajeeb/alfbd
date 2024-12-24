<?php
defined( 'ABSPATH' ) || exit;

add_action( 'init', 'ultp_frontend_submission_init' );
function ultp_frontend_submission_init() {
	$settings = isset( $GLOBALS['ultp_settings'] ) ? $GLOBALS['ultp_settings'] : array();
	if ( isset( $settings['ultp_frontend_submission'] ) ) {
		add_filter( 'ultp_settings', 'get_frontend_submission_settings', 10, 1 );
	}
}

function ultp_frontend_submission_run() {
	$ultp_settings= get_option('ultp_options',array());
	if(isset( $ultp_settings['ultp_frontend_submission'] ) && 'true' == $ultp_settings['ultp_frontend_submission'] ) {
		
		require_once ULTP_PRO_PATH.'/addons/frontend_submission/Frontend_Submission.php';
		require_once ULTP_PRO_PATH.'/addons/frontend_submission/Frontend_Account_Shortcode.php';
		require_once ULTP_PRO_PATH.'/addons/frontend_submission/Frontend_Block_Editor_Shortcode.php';
		require_once ULTP_PRO_PATH.'/addons/frontend_submission/Editor.php';

		if( isset($ultp_settings['ultp_fs_enable_recaptcha']) && 'yes' == $ultp_settings['ultp_fs_enable_recaptcha'] && !empty($ultp_settings['ultp_fs_recaptcha_site_key']) && !empty($ultp_settings['ultp_fs_recaptcha_secret_key']) ) {
			require_once ULTP_PRO_PATH.'/addons/frontend_submission/reCaptcha.php';
			new \ULTP_PRO\reCaptcha();
		}

		new \ULTP_PRO\Frontend_Submission();
		new \ULTP_PRO\Frontend_Block_Editor_Shortcode();
		new \ULTP_PRO\Editor();
	}
}

ultp_frontend_submission_run();

function get_frontend_submission_settings( $config ) {
	$pages  = get_pages( array('sort_column'=>'post_title') );
	$pages_option = array();
	foreach ($pages as $page) {
		$pages_option[$page->ID]=$page->post_title;
	}

    $fs_users = get_users(array('role' => 'ultp_frontend_submission'));
    $users_option = array(
        'all' => 'All Users'
    );
    // Loop through the users
    foreach ($fs_users as $user) {
        $users_option[$user->ID] = $user->display_name; 
    }

	$arr = array(
		'ultp_frontend_submission' => array(
			'label' => __( 'Frontend Submission', 'ultimate-post-pro' ),
			'attr'  => array(
				'ultp_fs_heading'    => array(
					'type'  => 'heading',
					'label' => __( 'Frontend Submission Settings', 'ultimate-post-pro' ),
				),
				'ultp_fs_my_account_shortcode'=> array(
					'type'=>'shortcode',
					'label'=>__('My Account Dashboard Shortcode','ultimate-post-pro'),
					'value' => 'ultp_frontend_account',
					'tooltip'=>__('Create new dashboard using the shortcode','ultimate-post-pro')
				),
                'ultp_fs_seo_support'=> array(
					'type'=>'switch',
					'label'=>__('SEO Support (RankMath)','ultimate-post-pro'),
					'value' => 'yes'==ultimate_post_pro()->get_setting('ultp_fs_seo_support')?'yes':'no',
					'desc'=>__('Enabled','ultimate-post-pro'),
					'tooltip'=>__('Enable SEO Support','ultimate-post-pro')
				),
                'ultp_fs_seo_support_allowed_users'=> array(
					'type'=>'multiselect',
					'label'=>__('Allowed Users for SEO Support','ultimate-post-pro'),
					'value' => ultimate_post_pro()->get_setting('ultp_fs_seo_support'),
					'options'=> $users_option,
					'desc'=>__('Select users who gets SEO support','ultimate-post-pro'),
					'tooltip'=>__('Select users who gets SEO support','ultimate-post-pro'),
					'depends_on' => array(
						array('key'=>'ultp_fs_seo_support','value'=>'yes','condition'=>'=='),
					)
				),
				'ultp_fs_allow_tag_create'=> array(
					'type'=>'switch',
					'label'=>__('Enable Tag Creation','ultimate-post-pro'),
					'value' => 'yes'==ultimate_post_pro()->get_setting('ultp_fs_allow_tag_create')?'yes':'no',
					'desc'=>__('Enabled','ultimate-post-pro'),
					'tooltip'=>__('Users can create new tags','ultimate-post-pro')
				),
				'ultp_fs_tag_create_allowed_user'=> array(
					'type'=>'multiselect',
					'label'=>__('Allowed Users for Tag Creation','ultimate-post-pro'),
					'value' => ultimate_post_pro()->get_setting('ultp_fs_tag_create_allowed_user'),
                    'options' => $users_option,
					'tooltip'=>__('Select users who can create new tags','ultimate-post-pro'),
					'depends_on' => array(
						array('key'=>'ultp_fs_allow_tag_create','value'=>'yes','condition'=>'==')
					)
				),
				'ultp_fs_allow_publish_post'=> array(
					'type'=>'switch',
					'label'=>__('Enable Post Publishing','ultimate-post-pro'),
					'value' => 'yes'==ultimate_post_pro()->get_setting('ultp_fs_allow_publish_post')?'yes':'no',
					'desc'=>__('Enabled','ultimate-post-pro'),
					'tooltip'=>__('Allowed users can publish post.','ultimate-post-pro')
				),
				'ultp_fs_allow_publish_post_allowed_users'=> array(
					'type'=>'multiselect',
					'label'=>__('Allowed Users for Post Publishing','ultimate-post-pro'),
					'value' => ultimate_post_pro()->get_setting('ultp_fs_allow_publish_post_allowed_users'),
                    'options' => $users_option,
					'desc'=>__('Enabled','ultimate-post-pro'),
					'tooltip'=>__('Select users who can publish posts directly','ultimate-post-pro'),
					'depends_on' => array(
						array('key'=>'ultp_fs_allow_publish_post','value'=>'yes','condition'=>'==')
					)
				),
				'ultp_fs_allow_edit_post_after_publish'=> array(
					'type'=>'switch',
					'label'=>__('Enable Post Editing After Publish','ultimate-post-pro'),
					'value' => 'yes'==ultimate_post_pro()->get_setting('ultp_fs_allow_edit_post_after_publish')?'yes':'no',
					'desc'=>__('Enabled','ultimate-post-pro'),
					'tooltip'=>__('Users can edit posts after publishing','ultimate-post-pro')
				),
				'ultp_fs_allow_edit_post_after_publish_allowed_users'=> array(
					'type'=>'multiselect',
					'label'=>__('Allowed Users for Published Post Editing','ultimate-post-pro'),
					'value' => ultimate_post_pro()->get_setting('ultp_fs_allow_edit_post_after_publish_allowed_users'),
                    'options' => $users_option,
					'desc'=>__('Enabled','ultimate-post-pro'),
					'tooltip'=>__('Select users who can edit posts after publishing','ultimate-post-pro'),
					'depends_on' => array(
						array('key'=>'ultp_fs_allow_edit_post_after_publish','value'=>'yes','condition'=>'==')
					)
				),
				'ultp_fs_media_access'=> array(
					'type'=>'switch',
					'label'=>__('Enable Featured Image Attachment Access','ultimate-post-pro'),
					'value' => 'yes'==ultimate_post_pro()->get_setting('ultp_fs_media_access')?'yes':'no',
					'desc'=>__('Enabled','ultimate-post-pro'),
					'tooltip'=>__('Users can upload featured image','ultimate-post-pro')
				),
				'ultp_fs_media_access_allowed_users'=> array(
					'type'=>'multiselect',
					'label'=>__('Allowed Users who have image attachment access','ultimate-post-pro'),
					'value' => ultimate_post_pro()->get_setting('ultp_fs_media_access_allowed_users'),
                    'options' => $users_option,
					'desc'=>__('Enabled','ultimate-post-pro'),
					'tooltip'=>__('Select users who can attach featured image','ultimate-post-pro'),
					'depends_on' => array(
						array('key'=>'ultp_fs_media_access','value'=>'yes','condition'=>'==')
					)
				),
				'ultp_fs_max_file_size'=> array(
					'type'=>'text',
					'label'=>__('Max File Size','ultimate-post-pro'),
					'default' => '2MB',
					'value' => ultimate_post_pro()->get_setting('ultp_fs_max_file_size'),
					'tooltip'=>__('Limit uploading media file size','ultimate-post-pro')
				),
				'ultp_fs_post_delete'=> array(
					'type'=>'switch',
					'label'=>__('Enable Post Deletion','ultimate-post-pro'),
					'value' => 'yes'==ultimate_post_pro()->get_setting('ultp_fs_post_delete')?'yes':'no',
					'desc'=>__('Enabled','ultimate-post-pro'),
					'tooltip'=>__('Users can delete their posts','ultimate-post-pro')
				),
                'ultp_fs_post_delete_allowed_users'=> array(
					'type'=>'multiselect',
					'label'=>__('Allowed Users who can Delete Posts','ultimate-post-pro'),
					'value' => ultimate_post_pro()->get_setting('ultp_fs_post_delete_allowed_users'),
                    'options' => $users_option,
					'desc'=>__('Enable media access','ultimate-post-pro'),
					'tooltip'=>__('Select users who can delete their posts','ultimate-post-pro'),
					'depends_on' => array(
						array('key'=>'ultp_fs_post_delete','value'=>'yes','condition'=>'==')
					)
				),
				'ultp_fs_enable_recaptcha'=> array(
					'type'=>'switch',
					'label'=>__('Enable reCAPTCHA','ultimate-post-pro'),
					'value' => 'yes'==ultimate_post_pro()->get_setting('ultp_fs_enable_recaptcha')?'yes':'no',
					'desc'=>__('Enabled','ultimate-post-pro'),
					'tooltip'=>__('Enable Google reCAPTCHA for security','ultimate-post-pro')
				),
				'ultp_fs_recaptcha_type'=> array(
					'type'=>'radio',
					'label'=>__('reCAPTCHA  Type','ultimate-post-pro'),
					'value' => ultimate_post_pro()->get_setting('ultp_fs_recaptcha_type'),
					'options'=> array(
						'v2' => __('V2','ultimate-post-pro'),
						'v3' => __('V3','ultimate-post-pro')
					),
					'tooltip'=>__('Select Google reCAPTCHA version','ultimate-post-pro'),
					'depends_on' => array(
						array('key'=>'ultp_fs_enable_recaptcha','value'=>'yes','condition'=>'==')
					)
				),
				'ultp_fs_recaptcha_site_key'=> array(
					'type'=>'text',
					'label'=>__('reCAPTCHA Site Key','ultimate-post-pro'),
					'default' => '',
					'value' => ultimate_post_pro()->get_setting('ultp_fs_recaptcha_site_key'),
					'tooltip'=>__('Field to add your reCAPTCHA site key','ultimate-post-pro'),
					'depends_on' => array(
						array('key'=>'ultp_fs_enable_recaptcha','value'=>'yes','condition'=>'==')
					)
				),
				'ultp_fs_recaptcha_secret_key'=> array(
					'type'=>'text',
					'label'=>__('reCAPTCHA Secret Key','ultimate-post-pro'),
					'default' => '',
					'value' => ultimate_post_pro()->get_setting('ultp_fs_recaptcha_secret_key'),
					'tooltip'=>__('Field to add your reCAPTCHA secret key','ultimate-post-pro'),
					'depends_on' => array(
						array('key'=>'ultp_fs_enable_recaptcha','value'=>'yes','condition'=>'==')
					)
				),
				'ultp_fs_email_admin_new_post_received'=> array(
					'type'=>'switch',
					'label'=>__('New Post Received Email Status','ultimate-post-pro'),
					'value' => 'yes'==ultimate_post_pro()->get_setting('ultp_fs_email_admin_new_post_received')?'yes':'no',
					'desc'=>__('Enabled','ultimate-post-pro'),
					'tooltip'=>__('Enable email notification for admins when users submit new post','ultimate-post-pro')
				),
				'ultp_fs_email_admin_new_user_registered'=> array(
					'type'=>'switch',
					'label'=>__('New User Registered Email Status','ultimate-post-pro'),
					'value' => 'yes'==ultimate_post_pro()->get_setting('ultp_fs_email_admin_new_user_registered')?'yes':'no',
					'desc'=>__('Enabled','ultimate-post-pro'),
					'tooltip'=>__('Enable email notification to admin after an user successful registration','ultimate-post-pro')
				),
				'ultp_fs_email_registration_successful'=> array(
					'type'=>'switch',
					'label'=>__('Registration Successful Email Status','ultimate-post-pro'),
					'value' => 'yes'==ultimate_post_pro()->get_setting('ultp_fs_email_registration_successful')?'yes':'no',
					'desc'=>__('Enabled','ultimate-post-pro'),
					'tooltip'=>__('Enable email notification to users after successful registration','ultimate-post-pro')
				),
				'ultp_fs_email_admin_new_post_published'=> array(
					'type'=>'switch',
					'label'=>__('New Post Published Email Status','ultimate-post-pro'),
					'value' => 'yes'==ultimate_post_pro()->get_setting('ultp_fs_email_admin_new_post_published')?'yes':'no',
					'desc'=>__('Enabled','ultimate-post-pro'),
					'tooltip'=>__('Enable email notification for admins when users publish new post','ultimate-post-pro')
				),
				'ultp_fs_email_user_new_post_submitted'=> array(
					'type'=>'switch',
					'label'=>__('New Post Submitted for Review Email Status','ultimate-post-pro'),
					'value' => 'yes'==ultimate_post_pro()->get_setting('ultp_fs_email_user_new_post_submitted')?'yes':'no',
					'desc'=>__('Disabled','ultimate-post-pro'),
					'tooltip'=>__('Enable email notification for admins when users publish new post','ultimate-post-pro')
				),
				'ultp_fs_email_user_new_post_reviewed'=> array(
					'type'=>'switch',
					'label'=>__('Post Reviewed and Need Changes Email Status','ultimate-post-pro'),
					'value' => 'yes'==ultimate_post_pro()->get_setting('ultp_fs_email_user_new_post_reviewed')?'yes':'no',
					'desc'=>__('Enabled','ultimate-post-pro'),
					'tooltip'=>__('Enable email notification to admin when users submit new posts for review','ultimate-post-pro')
				),
				'ultp_fs_email_user_new_post_published'=> array(
					'type'=>'switch',
					'label'=>__('Post Published after Review Email Status','ultimate-post-pro'),
					'value' => 'yes'==ultimate_post_pro()->get_setting('ultp_fs_email_user_new_post_published')?'yes':'no',
					'desc'=>__('Enabled','ultimate-post-pro'),
					'tooltip'=>__('Enable email notification to users when their posts has been reviewed and published','ultimate-post-pro')
				),
				'ultp_fs_enable_guest_user_post_submit'=> array(
					'type'=>'switch',
					'label'=>__('Enable Guest User Post Submission','ultimate-post-pro'),
					'value' => 'yes'==ultimate_post_pro()->get_setting('ultp_fs_enable_guest_user_post_submit')?'yes':'no',
					'desc'=>__('Enabled','ultimate-post-pro'),
					'tooltip'=>__('Guest users can submit new posts','ultimate-post-pro')
				),
				'ultp_fs_guest_user_editor_shortcode'=> array(
					'type'=>'shortcode',
					'label'=>__('Guest User Block Editor Shortcode','ultimate-post-pro'),
					'value' => 'ultp_fs_block_editor',
					'tooltip'=>__('Use this shortcode to create block editor for guest users','ultimate-post-pro'),
					'depends_on' => array(array('key'=>'ultp_fs_enable_guest_user_post_submit','condition'=>'==','value'=>'yes'))

				),
				'ultp_fs_guest_form_submitted_redirect'=> array(
					'type'=>'select',
					'label'=>__('Redirection Page After Submitting Post (For Guest Users)','ultimate-post-pro'),
					'default'=> ultimate_post_pro()->get_setting('ultp_fs_guest_form_submitted_redirect')?:get_home_url(),
					'options' => $pages_option,
					'desc'=>__('Select the page, where guest user will be redirect after post submission','ultimate-post-pro'),
					'tooltip'=>__('Guest users to be redirected to this page after submitting posts','ultimate-post-pro'),
					'depends_on' => array(array('key'=>'ultp_fs_enable_guest_user_post_submit','condition'=>'==','value'=>'yes'))
				),
                'ultp_fs_logout_redirect'=> array(
					'type'=>'select',
					'label'=>__('Redirection Page After Logout','ultimate-post-pro'),
					'default'=> ultimate_post_pro()->get_setting('ultp_fs_logout_redirect')?:get_home_url(),
					'options' => $pages_option,
					'desc'=>__('Select the page, where user will be redirected after logged out.','ultimate-post-pro'),
					'tooltip'=>__('Users to be redirected to this page after logout','ultimate-post-pro')
				)
			),
		),
	);
	return array_merge( $config, $arr );
}