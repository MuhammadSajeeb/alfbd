<?php
namespace ULTP_PRO;

use Exception;
use WP_Error;

defined( 'ABSPATH' ) || exit;

class Frontend_Submission {
	/**
	 * Query vars to add to wp.
	 *
	 * @var array
	 */
	public $query_vars = array();

	public $is_script_enqueued = false;

	public function __construct() {

		$this->init_query_vars();
		$this->create_frontend_submission_user_role_if_not_exist();
		add_action( 'init', array( $this, 'add_endpoints' ) );

		if ( ! is_admin() ) {
			add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );
			add_action( 'parse_request', array( $this, 'parse_request' ), 0 );
		}

		add_action( 'show_admin_bar', array( $this, 'hide_admin_bar' ), 999999999 );

		add_action( 'admin_init', array( $this, 'restrict_backend_access' ) );

		add_filter( 'wp_revisions_to_keep', array( $this, 'remove_revision_for_frontend_submission_user' ) );

		self::init_shortcode();

		add_action( 'ultp_frontend_submission_account_navigation', array( $this, 'myaccount_navigation' ) );

		add_action( 'ultp_frontend_submission_account_content', array( $this, 'myaccount_content' ) );

		add_action( 'ultp_frontend_submission_after_account_navigation_list', array( $this, 'add_submit_post_button' ) );

		add_action( 'ultp_frontend_submission_account_fs-my-posts_endpoint', array( $this, 'my_posts_content' ) );

		add_action( 'ultp_frontend_submission_account_fs-my-profile_endpoint', array( $this, 'my_profile_content' ) );

		add_action( 'ultp_frontend_submission_account_fs-change-password_endpoint', array( $this, 'change_password_content' ) );

		add_action( 'ultp_frontend_submission_account_fs-edit-account_endpoint', array( $this, 'edit_account_content' ) );

		add_action( 'ultp_frontend_submission_account_fs-dashboard_endpoint', array( $this, 'edit_account_content' ) );

		add_filter( 'allowed_block_types_all', array( $this, 'allowed_block_types' ), 10, 2 );

		add_action( 'template_redirect', array( $this, 'process_change_password' ) );

		add_filter( 'ultp_frontend_submission_logout_redirect_url', array( $this, 'set_logout_redirect_url' ) );

		// Non ajax login and registration
		add_action( 'wp_loaded', array( $this, 'process_login' ) );
		add_action( 'wp_loaded', array( $this, 'process_registration' ) );
		add_action( 'wp_loaded', array( $this, 'process_account_data_changes' ) );

		add_action( 'template_redirect', array( $this, 'process_account_data_changes' ) );

		add_filter( 'block_editor_settings_all', array( $this, 'increase_autosave_interval' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'footer_scripts' ) );

		add_action( 'add_meta_boxes', array( $this, 'remove_third_party_meta_boxes' ), 999999999, 2 );

		add_action( 'ultp_frontend_submission_account_header', array( $this, 'header_content' ) );

		add_action( 'wp_ajax_ultp_fs_fetch_post', array( $this, 'ultp_frontend_submission_fetch_post' ) );

		add_action( 'admin_menu', array( $this, 'remove_admin_menu' ) );

		add_action( 'admin_head', array( $this, 'hide_admin_menu' ), 9999999999999 );

		add_action( 'pre_insert_term', array( $this, 'prevent_user_create_tag' ), 10, 2 );

		add_filter( 'upload_size_limit', array( $this, 'set_upload_limit' ) );

		add_action( 'init', array( $this, 'register_custom_meta' ) );

		add_action( 'block_editor_rest_api_preload_paths', array( $this, 'guest_user_preload_path' ) );

		add_action( 'save_post_post', array( $this, 'action_after_post_inserted' ), 10, 3 );

		add_filter( 'manage_post_posts_columns', array( $this, 'add_fs_info_column' ) );

		add_action( 'manage_post_posts_custom_column', array( $this, 'fs_column_value' ), 10, 2 );

		add_filter( 'user_has_cap', array( $this, 'post_edit_after_publish' ), 10, 3 );

		add_action( 'transition_post_status', array( $this, 'actions_after_post_save' ), 10, 3 );

		add_action( 'pre_delete_post', array( $this, 'prevent_delete_posts' ), 10, 2 );

		add_action( 'rest_api_init', array( $this, 'editor_notice_rest_api' ) );

		add_action( 'admin_footer-post.php', array( $this, 'editor_notice_script' ) );

		add_action( 'admin_footer-post-new.php', array( $this, 'editor_notice_script' ) );

		add_filter( 'restrict_manage_posts', array( $this, 'add_post_submission_type_filter' ) );

		add_filter( 'pre_get_posts', array( $this, 'filter_posts' ) );

	}

	public function filter_posts( $query ) {
		$meta_query = array();

		$selected = isset( $_GET['ultp-fs-filter'] ) ? sanitize_text_field( $_GET['ultp-fs-filter'] ) : '';

		// Check for Pillar Content filter.
		if ( ! empty( $selected ) ) {
			switch ( $selected ) {
				case 'guest':
					$meta_query[] = array(
						'key'   => 'ultp_fs_post_created_by_guest',
						'value' => true,
					);
					break;
			}

			if ( empty( $meta_query ) ) {
				$query->set( 'author', $selected );
			} else {
				$query->set( 'meta_query', $meta_query );
			}
		}

		return $query;
	}
	/**
	 * Add Pos Submission Filter.
	 */
	public function add_post_submission_type_filter() {
		global $post_type;

		if ( 'post' !== $post_type ) {
			return;
		}

		$fs_users     = get_users( array( 'role' => 'ultp_frontend_submission' ) );
		$users_option = array(
			''      => '--Select User--',
			'guest' => 'Guest Users',
		);
		// Loop through the users
		foreach ( $fs_users as $user ) {
			$users_option[ $user->ID ] = $user->display_name;
		}

		$selected = isset( $_GET['ultp-fs-filter'] ) ? sanitize_text_field( $_GET['ultp-fs-filter'] ) : '';
		?>
		<select name="ultp-fs-filter" id="ultp-fs-filter">
			<?php foreach ( $users_option as $val => $option ) : ?>
				<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $selected, $val, true ); ?>><?php echo esc_html( $option ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	public function editor_notice_script() {
		global $post;
		if($post && $this->is_frontend_submission_user( $post->post_author ) ) {
			wp_enqueue_script( 'ultp_fs_editor_backend', ULTP_PRO_URL . 'assets/js/ultp_fs_editor_backend.js', array(), ULTP_PRO_VER, true );
			$settings    = array(
				'is_fs_user'    => true,
				'media_access'  => ultimate_post_pro()->get_setting( 'ultp_fs_media_access' ),
				'post_preview'  => ultimate_post_pro()->get_setting( 'ultp_fs_post_preview' ),
				'max_file_size' => ultimate_post_pro()->get_setting( 'ultp_fs_max_file_size' ),
			);
			$user_id     = get_current_user_id();
			$seo_support = ultimate_post_pro()->get_setting( 'ultp_fs_seo_support' );
			if ( $seo_support ) {
				$seo_allowed_users = ultimate_post_pro()->get_setting( 'ultp_fs_seo_support_allowed_users' );
				if ( ! empty( $seo_allowed_users ) && is_array( $seo_allowed_users ) && in_array( $user_id, $seo_allowed_users ) ) {
					$settings['seo_support'] = $seo_support;
				} else {
					$settings['seo_support'] = '';
				}
			}

			$allow_tag_create = ultimate_post_pro()->get_setting( 'ultp_fs_allow_tag_create' );
			if ( 'yes' === $allow_tag_create ) {
				$tag_allowed_users = ultimate_post_pro()->get_setting( 'ultp_fs_tag_create_allowed_user' );
				if ( ! empty( $tag_allowed_users ) && is_array( $tag_allowed_users ) && in_array( $user_id, $tag_allowed_users ) ) {
					$settings['allow_tag_create'] = true;
				} else {
					$settings['allow_tag_create'] = false;

				}
			}

			$allow_media_access = ultimate_post_pro()->get_setting( 'ultp_fs_media_access' );
			if ( 'yes' === $allow_media_access ) {
				$media_access_allowed_users = ultimate_post_pro()->get_setting( 'ultp_fs_media_access_allowed_users' );
				if ( ! empty( $media_access_allowed_users ) && is_array( $media_access_allowed_users ) && in_array( $user_id, $media_access_allowed_users ) ) {
					$settings['allow_media_access'] = true;
				} else {
					$settings['allow_media_access'] = false;

				}
			}

			$allow_post_delete = ultimate_post_pro()->get_setting( 'ultp_fs_post_delete' );
			if ( 'yes' === $allow_post_delete ) {
				$allowed_users = ultimate_post_pro()->get_setting( 'ultp_fs_post_delete_allowed_users' );
				if ( ! empty( $allowed_users ) && is_array( $allowed_users ) && in_array( $user_id, $allowed_users ) ) {
					$settings['allow_post_delete'] = true;
				} else {
					$settings['allow_post_delete'] = false;
				}
			}

			$script = wp_json_encode( $settings );
			wp_add_inline_script( 'ultp_fs_editor_backend', "const ultpFsSettings=$script" );

			if ( isset( $_GET['action'] ) && 'edit' != sanitize_text_field( $_GET['action'] ) ) {
				remove_all_actions( 'admin_footer' );
			}
		}
	}
	public function get_editor_notice() {
		if ( isset( $_GET['id'] ) ) {
			$id = sanitize_text_field(
				wp_unslash( $_GET['id'] )
			);

			$response = get_transient( 'ultp_fs_editor_notice_' . $id );

			$data = json_decode( $response );

			delete_transient( 'ultp_fs_editor_notice_' . $id );
			return new \WP_REST_Response(
				array(
					'code'    => $data->code,
					'message' => wp_unslash( $data->message ),
				)
			);

		}

	}

	public function editor_notice_rest_api() {
		$namespace = 'ultp/v2';
		$route     = 'fs_get_editor_notice';

		register_rest_route(
			$namespace,
			$route,
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_editor_notice' ),
				'permission_callback' => function() {
					return current_user_can( 'edit_posts' ); },
			)
		);
	}

	public function get_admin_display() {
		$super_admin = '';
		$super       = get_super_admins();
		if ( isset( $super[0] ) ) {
			$super       = get_user_by( 'login', $super[0] );
			$super_admin = isset( $super->data->display_name ) ? $super->data->display_name : '';
		}
		return $super_admin;
	}

	public function actions_after_post_save( $new_status, $old_status, $post ) {

		if ( $this->is_frontend_submission_user( $post->post_author ) ) {
			$user_data = get_userdata( $post->post_author );

			if ( ( 'new' == $old_status || 'auto-draft' == $old_status || 'draft' == $old_status ) && 'pending' == $new_status && ! get_post_meta( $post->ID, 'ultp_fs_pending_email_status', true ) ) {
				// Submitted For Review
				// From new, auto draft and draft
				$response = array(
					'code'    => 'success',
					'message' => 'Your post has been submitted for review.',
				);

				set_transient( 'ultp_fs_editor_notice_' . $post->ID, wp_json_encode( $response ), 300 );

				if ( 'yes' === ultimate_post_pro()->get_setting( 'ultp_fs_email_user_new_post_submitted' ) ) {
					ob_start();
					$post_title = $post->post_title;
					$site_name  = get_bloginfo( 'name' );
					include ULTP_PRO_PATH . '/addons/frontend_submission/templates/emails/submit-for-review.php';
					$content = ob_get_clean();
					$subject = __( 'Thanks for Your Submission', 'ultimate-post-pro' );

					if ( is_email( $user_data->user_email ) ) {
						wp_mail( $user_data->user_email, $subject, $content );
						update_post_meta( $post->ID, 'ultp_fs_pending_email_status', true );
					}
				}

				if ( 'yes' === ultimate_post_pro()->get_setting( 'ultp_fs_email_admin_new_post_received' ) ) {

					$content = '';
					ob_start();
					$post_title        = $post->post_title;
					$site_name         = get_bloginfo( 'name' );
					$user_display_name = $user_data->display_name;
					$post_title        = $post->post_title;
					$edit_post_url     = get_edit_post_link( $post->ID );
					include ULTP_PRO_PATH . '/addons/frontend_submission/templates/emails/admin-new-post-received-for-review.php';
					$content = ob_get_clean();
					$subject = __( 'New Article Submitted, Waiting for Review', 'ultimate-post-pro' );

					$admin_email = get_option( 'admin_email' );

					wp_mail( $admin_email, $subject, $content );
				}
			}

			if ( ( 'new' == $old_status || 'auto-draft' == $old_status || 'draft' == $old_status ) && 'publish' == $new_status && ! get_post_meta( $post->ID, 'ultp_fs_pending_email_status', true ) && get_current_user_id() == $post->post_author ) {
				update_post_meta( $post->ID, 'ultp_fs_pending_email_status', true );
				// Allow Post Publist
				$response = array(
					'code'    => 'success',
					'message' => 'Your post has been published successfully.',
				);

				set_transient( 'ultp_fs_editor_notice_' . $post->ID, wp_json_encode( $response ), 300 );
				if ( 'yes' === ultimate_post_pro()->get_setting( 'ultp_fs_email_admin_new_post_published' ) ) {

					$content = '';
					ob_start();
					$post_title        = $post->post_title;
					$site_name         = get_bloginfo( 'name' );
					$user_display_name = $user_data->display_name;
					$post_title        = $post->post_title;

					$post_permalink = get_permalink( $post->ID );
					include ULTP_PRO_PATH . '/addons/frontend_submission/templates/emails/admin-auto-post-publish.php';
					$content = ob_get_clean();
					$subject = __( 'New Article published by ', 'ultimate-post-pro' ) . $user_display_name;

					$admin_email = get_option( 'admin_email' );

					wp_mail( $admin_email, $subject, $content );
				}
			}

			if ( ( 'draft' === $old_status || 'pending' === $old_status ) && 'pending' === $new_status && get_current_user_id() == $post->post_author && get_post_meta( $post->ID, 'ultp_fs_pending_email_status', true ) ) {

				// resubmit
				if ( 'yes' === ultimate_post_pro()->get_setting( 'ultp_fs_email_user_new_post_submitted' ) ) {
					ob_start();
					$post_title = $post->post_title;
					$site_name  = get_bloginfo( 'name' );
					include ULTP_PRO_PATH . '/addons/frontend_submission/templates/emails/submit-for-review.php';
					$content = ob_get_clean();
					$subject = __( 'Thanks for Your Submission', 'ultimate-post-pro' );

					if ( is_email( $user_data->user_email ) ) {
						wp_mail( $user_data->user_email, $subject, $content );
						update_post_meta( $post->ID, 'ultp_fs_pending_email_status', true );
					}
				}

				if ( 'yes' === ultimate_post_pro()->get_setting( 'ultp_fs_email_admin_new_post_received' ) ) {

					$content = '';
					ob_start();
					$post_title        = $post->post_title;
					$site_name         = get_bloginfo( 'name' );
					$user_display_name = $user_data->display_name;
					$post_title        = $post->post_title;
					$edit_post_url     = get_edit_post_link( $post->ID );
					include ULTP_PRO_PATH . '/addons/frontend_submission/templates/emails/admin-new-post-received-for-review.php';
					$content = ob_get_clean();
					$subject = __( 'New Article Submitted, Waiting for Review', 'ultimate-post-pro' );

					$admin_email = get_option( 'admin_email' );

					wp_mail( $admin_email, $subject, $content );
				}

				$response = array(
					'code'    => 'success',
					'message' => 'Your post has been submitted for review.',
				);

				set_transient( 'ultp_fs_editor_notice_' . $post->ID, wp_json_encode( $response ), 300 );

			}

			if ( 'pending' === $old_status && 'pending' === $new_status && get_current_user_id() != $post->post_author && get_post_meta( $post->ID, 'ultp_fs_pending_email_status' ) ) {
				if ( 'yes' === ultimate_post_pro()->get_setting( 'ultp_fs_email_user_new_post_reviewed' ) ) {
					ob_start();
					$post_title = $post->post_title;
					$site_name  = get_bloginfo( 'name' );
					include ULTP_PRO_PATH . '/addons/frontend_submission/templates/emails/review-send-to-user.php';
					$content = ob_get_clean();
					$subject = __( 'Article Review Completed, Changes Required', 'ultimate-post-pro' );

					if ( is_email( $user_data->user_email ) ) {
						wp_mail( $user_data->user_email, $subject, $content );
					}
				}
				 $response = array(
					 'code'    => 'success',
					 'message' => 'Review successfully sent to ' . $user_data->display_name,
				 );

				 set_transient( 'ultp_fs_editor_notice_' . $post->ID, wp_json_encode( $response ), 300 );
			}

			if ( ( 'pending' === $old_status || 'draft' === $old_status ) && 'publish' === $new_status && get_current_user_id() != $post->post_author ) {
				if ( 'yes' === ultimate_post_pro()->get_setting( 'ultp_fs_email_user_new_post_published' ) ) {
					ob_start();
					$post_title     = $post->post_title;
					$post_permalink = get_permalink( $post->ID );
					$site_name      = get_bloginfo( 'name' );
					include ULTP_PRO_PATH . '/addons/frontend_submission/templates/emails/publish-post.php';
					$content = ob_get_clean();
					$subject = __( 'Your Article Has Been Published!', 'ultimate-post-pro' );

					if ( is_email( $user_data->user_email ) ) {
						wp_mail( $user_data->user_email, $subject, $content );
					}

					$response = array(
						'code'    => 'success',
						'message' => $user_data->display_name . "'s post has been published.",
					);

					set_transient( 'ultp_fs_editor_notice_' . $post->ID, wp_json_encode( $response ), 300 );
				}
			}
		}


		if ( ! is_user_logged_in() && 'yes' === ultimate_post_pro()->get_setting( 'ultp_fs_enable_guest_user_post_submit' ) ) {

			if ( 'yes' === ultimate_post_pro()->get_setting( 'ultp_fs_email_user_new_post_submitted' ) ) {
				ob_start();
				$post_title = $post->post_title;
				$site_name  = get_bloginfo( 'name' );
				include ULTP_PRO_PATH . '/addons/frontend_submission/templates/emails/submit-for-review.php';
				$content = ob_get_clean();
				$subject = __( 'Thanks for Your Submission', 'ultimate-post-pro' );

				$author_email = get_post_meta( $post->ID, 'ultp_fs_post_email', true );
				if ( is_email( $author_email ) ) {
					wp_mail( $author_email, $subject, $content );
					update_post_meta( $post->ID, 'ultp_fs_pending_email_status', true );
				}
			}

			if ( 'yes' === ultimate_post_pro()->get_setting( 'ultp_fs_email_admin_new_post_received' ) ) {

				$content = '';
				ob_start();
				$post_title        = $post->post_title;
				$site_name         = get_bloginfo( 'name' );
				$user_display_name = $user_data->display_name;
				$post_title        = $post->post_title;
				$edit_post_url     = get_edit_post_link( $post->ID );
				include ULTP_PRO_PATH . '/addons/frontend_submission/templates/emails/admin-new-post-received-for-review.php';
				$content = ob_get_clean();
				$subject = __( 'New Article Submitted, Waiting for Review', 'ultimate-post-pro' );

				$admin_email = get_option( 'admin_email' );

				wp_mail( $admin_email, $subject, $content );
			}
		}

	}

	public function fs_column_value( $column_key, $post_id ) {
		if ( 'ultp_fs_info' === $column_key ) {
			$is_fs_post = get_post_meta( $post_id, 'ultp_fs_post_created', true );
			if ( $is_fs_post ) {
				if ( get_post_meta( $post_id, 'ultp_fs_post_created_by_guest', true ) ) {
					$author_name  = get_post_meta( $post_id, 'ultp_fs_post_author', true ) . __( '(Guest)', 'ultimate-post-pro' );
					$author_email = get_post_meta( $post_id, 'ultp_fs_post_email', true );
				} else {
					$author_id    = get_post_field( 'post_author', $post_id );
					$author_data  = get_userdata( $author_id );
					$author_name  = $author_data->display_name;
					$author_email = $author_data->user_email;
				}
				?>
				<div class="ultp-fs-into-post-column-field"> 
					<strong> Name: </strong> <span><?php echo esc_html( $author_name ); ?></span>
				</div>
				<div class="ultp-fs-into-post-column-field"> 
					<strong> Email: </strong> <span><?php echo esc_html( $author_email ); ?></span>
				</div>
				<?php
			}
		}
	}

	public function add_fs_info_column( $columns ) {
		$columns = array_merge( $columns, array( 'ultp_fs_info' => __( 'Submission Info', 'ultimate-post-pro' ) ) );
		return $columns;
	}

	public function action_after_post_inserted( $post_id, $post, $update ) {
		$post_author = $post->post_author;
		if ( $this->is_frontend_submission_user( $post_author ) ) {
			if ( ! get_post_meta( $post_id, 'ultp_fs_post_created' ) ) {
				update_post_meta( $post_id, 'ultp_fs_post_created', true );
			}
		}
	}

	public function guest_user_preload_path( $paths ) {
		if ( ! is_user_logged_in() ) {
			return array();
		}
		return $paths;
	}

	public function register_custom_meta() {
		register_post_meta(
			'post',
			'ultp_fs_post_author',
			array(
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'string',
				'auth_callback' => function() {
					return current_user_can( 'edit_posts' );
				},
			)
		);
		register_post_meta(
			'post',
			'ultp_fs_post_email',
			array(
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'string',
				'auth_callback' => function() {
					return current_user_can( 'edit_posts' );
				},
			)
		);
		register_post_meta(
			'post',
			'ultp_fs_post_author_url',
			array(
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'string',
				'auth_callback' => function() {
					return current_user_can( 'edit_posts' );
				},
			)
		);
	}

	public function set_upload_limit( $size ) {
		if ( $this->is_frontend_submission_user() ) {
			// Default size 2MB
			$in_mb = intval( ultimate_post_pro()->get_setting( 'ultp_fs_max_file_size' ) ) ?: 2;
			if ( $in_mb >= 300 ) {
				$in_mb = 300;
			}
			$size = $in_mb * 1024 * 1024;
		}

		return $size;
	}

	public function prevent_user_create_tag( $term, $taxonomy ) {
		if ( $this->is_frontend_submission_user() && 'post_tag' === $taxonomy && ! $this->is_allowed_to_create_tag() ) {
			return new WP_Error(
				'disallow_insert_term',
				__( 'You dont have permission to insert new tag. Please Exising Tag.', 'ultimate-post-pro' ),
				array( 'status' => 403 )
			);
		}
		return $term;
	}

	public function remove_admin_menu() {
		if ( $this->is_frontend_submission_user() ) {
			global $menu;
			$menu = array();
			remove_all_actions( 'admin_menu' );
			remove_meta_box( 'postcustom', 'post', 'normal' );
		}
	}
	public function hide_admin_menu() {
		if ( $this->is_frontend_submission_user() ) {
			?>
					<style type="text/css">
						#wpcontent, #footer
						{ 
							margin-left: 0px !important;
						}
					#adminmenuback, #adminmenuwrap,#adminmenumain,#wpadminbar { display: none !important; }
					</style>
					<script type="text/javascript">
					jQuery(document).ready( function($) {
						$('#adminmenuback, #adminmenuwrap','#adminmenumain').remove();
					});
					jQuery(document).ready( function($) {
						$('#wpadminbar').remove();
					});
				</script>
			<?php
		}
	}

	public function header_content() {
		if ( is_user_logged_in() ) {
			include ULTP_PRO_PATH . '/addons/frontend_submission/templates/header.php';
		}
	}


	public function remove_third_party_meta_boxes( $post_type, $post ) {
		global $wp_meta_boxes;
		if ( $this->is_frontend_submission_user() ) {
			$allowed_meta_boxes = array(
				'core' =>
				array(
					'submitdiv'        =>
					array(
						'id'       => 'submitdiv',
						'title'    => 'Publish',
						'callback' => 'post_submit_meta_box',
						'args'     =>
						array(
							'__back_compat_meta_box' => true,
						),
					),
					'categorydiv'      =>
					array(
						'id'       => 'categorydiv',
						'title'    => 'Categories',
						'callback' => 'post_categories_meta_box',
						'args'     =>
						array(
							'taxonomy'               => 'category',
							'__back_compat_meta_box' => true,
						),
					),
					'tagsdiv-post_tag' =>
					array(
						'id'       => 'tagsdiv-post_tag',
						'title'    => 'Tags',
						'callback' => 'post_tags_meta_box',
						'args'     =>
						array(
							'taxonomy'               => 'post_tag',
							'__back_compat_meta_box' => true,
						),
					),
					'pageparentdiv'    =>
					array(
						'id'       => 'pageparentdiv',
						'title'    => 'Post Attributes',
						'callback' => 'page_attributes_meta_box',
						'args'     =>
						array(
							'__back_compat_meta_box' => true,
						),
					),
				),
				'low'  =>
				array(
					'postimagediv' =>
					array(
						'id'       => 'postimagediv',
						'title'    => 'Featured image',
						'callback' => 'post_thumbnail_meta_box',
						'args'     =>
						array(
							'__back_compat_meta_box' => true,
						),
					),
				),
			);

			unset( $wp_meta_boxes[ $post_type ]['side'] );
			$wp_meta_boxes[ $post_type ]['side'] = $allowed_meta_boxes;

			unset( $wp_meta_boxes[ $post_type ]['normal'] );

			$normal_allowed_mb = array(
				'core' => array(
					'postexcerpt'      =>
					array(
						'id'       => 'postexcerpt',
						'title'    => 'Excerpt',
						'callback' => 'post_excerpt_meta_box',
						'args'     =>
						array(
							'__back_compat_meta_box' => true,
						),
					),
					'trackbacksdiv'    =>
					array(
						'id'       => 'trackbacksdiv',
						'title'    => 'Send Trackbacks',
						'callback' => 'post_trackback_meta_box',
						'args'     =>
						array(
							'__back_compat_meta_box' => true,
						),
					),
					'postcustom'       =>
					array(
						'id'       => 'postcustom',
						'title'    => 'Custom Fields',
						'callback' => 'post_custom_meta_box',
						'args'     =>
						array(
							'__back_compat_meta_box' => true,
							'__block_editor_compatible_meta_box' => true,
						),
					),
					'commentstatusdiv' =>
					array(
						'id'       => 'commentstatusdiv',
						'title'    => 'Discussion',
						'callback' => 'post_comment_status_meta_box',
						'args'     =>
						array(
							'__back_compat_meta_box' => true,
						),
					),
					'slugdiv'          =>
					array(
						'id'       => 'slugdiv',
						'title'    => 'Slug',
						'callback' => 'post_slug_meta_box',
						'args'     =>
						array(
							'__back_compat_meta_box' => true,
						),
					),
				),
			);

			$wp_meta_boxes[ $post_type ]['normal'] = $normal_allowed_mb;
		}

	}

	public function is_allowed_to_create_tag() {
		$user_id           = get_current_user_id();
		$allow_post_delete = ultimate_post_pro()->get_setting( 'ultp_fs_allow_tag_create' );
		if ( 'yes' === $allow_post_delete ) {
			$allowed_users = ultimate_post_pro()->get_setting( 'ultp_fs_tag_create_allowed_user' );
			if ( ! empty( $allowed_users ) && is_array( $allowed_users ) && in_array( $user_id, $allowed_users ) ) {
				return true;
			}
		}
		return false;
	}

	public function is_allowed_to_edit_post_after_publish() {
		$user_id           = get_current_user_id();
		$allow_post_delete = ultimate_post_pro()->get_setting( 'ultp_fs_allow_edit_post_after_publish' );
		if ( 'yes' === $allow_post_delete ) {
			$allowed_users = ultimate_post_pro()->get_setting( 'ultp_fs_allow_edit_post_after_publish_allowed_users' );
			if ( in_array( ! empty( $allowed_users ) && is_array( $allowed_users ) && $user_id, $allowed_users ) ) {
				return true;
			}
		}
		return false;
	}

	public function is_allowed_to_publish_post() {
		$user_id           = get_current_user_id();
		$allow_post_delete = ultimate_post_pro()->get_setting( 'ultp_fs_allow_publish_post' );
		if ( 'yes' === $allow_post_delete ) {
			$allowed_users = ultimate_post_pro()->get_setting( 'ultp_fs_allow_publish_post_allowed_users' );
			if ( in_array( ! empty( $allowed_users ) && is_array( $allowed_users ) && $user_id, $allowed_users ) ) {
				return true;
			}
		}
		return false;
	}
	public function is_allowed_media_access() {
		$user_id           = get_current_user_id();
		$allow_post_delete = ultimate_post_pro()->get_setting( 'ultp_fs_media_access' );
		if ( 'yes' === $allow_post_delete ) {
			$allowed_users = ultimate_post_pro()->get_setting( 'ultp_fs_media_access_allowed_users' );
			if ( ! empty( $allowed_users ) && is_array( $allowed_users ) && in_array( $user_id, $allowed_users ) ) {
				return true;
			}
		}
		return false;
	}

	public function is_allowed_to_delete_post() {
		$user_id           = get_current_user_id();
		$allow_post_delete = ultimate_post_pro()->get_setting( 'ultp_fs_post_delete' );
		if ( 'yes' === $allow_post_delete ) {
			$allowed_users = ultimate_post_pro()->get_setting( 'ultp_fs_post_delete_allowed_users' );
			if ( ! empty( $allowed_users ) && is_array( $allowed_users ) && in_array( $user_id, $allowed_users ) ) {
				return true;
			}
		}
		return false;
	}

	public function footer_scripts() {

		if ( $this->is_frontend_submission_user() ) {
			wp_enqueue_script( 'ultp_fs_editor_backend', ULTP_PRO_URL . 'assets/js/ultp_fs_editor_backend.js', array(), ULTP_PRO_VER, true );
			$settings    = array(
				'is_fs_user'    => true,
				'media_access'  => ultimate_post_pro()->get_setting( 'ultp_fs_media_access' ),
				'post_preview'  => ultimate_post_pro()->get_setting( 'ultp_fs_post_preview' ),
				'max_file_size' => ultimate_post_pro()->get_setting( 'ultp_fs_max_file_size' ),
			);
			$user_id     = get_current_user_id();
			$seo_support = ultimate_post_pro()->get_setting( 'ultp_fs_seo_support' );
			if ( $seo_support ) {
				$seo_allowed_users = ultimate_post_pro()->get_setting( 'ultp_fs_seo_support_allowed_users' );
				if ( ! empty( $seo_allowed_users ) && is_array( $seo_allowed_users ) && in_array( $user_id, $seo_allowed_users ) ) {
					$settings['seo_support'] = $seo_support;
				} else {
					$settings['seo_support'] = '';
				}
			}

			$allow_tag_create = ultimate_post_pro()->get_setting( 'ultp_fs_allow_tag_create' );
			if ( 'yes' === $allow_tag_create ) {
				$tag_allowed_users = ultimate_post_pro()->get_setting( 'ultp_fs_tag_create_allowed_user' );
				if ( ! empty( $tag_allowed_users ) && is_array( $tag_allowed_users ) && in_array( $user_id, $tag_allowed_users ) ) {
					$settings['allow_tag_create'] = true;
				} else {
					$settings['allow_tag_create'] = false;

				}
			}

			$allow_media_access = ultimate_post_pro()->get_setting( 'ultp_fs_media_access' );
			if ( 'yes' === $allow_media_access ) {
				$media_access_allowed_users = ultimate_post_pro()->get_setting( 'ultp_fs_media_access_allowed_users' );
				if ( ! empty( $media_access_allowed_users ) && is_array( $media_access_allowed_users ) && in_array( $user_id, $media_access_allowed_users ) ) {
					$settings['allow_media_access'] = true;
				} else {
					$settings['allow_media_access'] = false;

				}
			}

			$allow_post_delete = ultimate_post_pro()->get_setting( 'ultp_fs_post_delete' );
			if ( 'yes' === $allow_post_delete ) {
				$allowed_users = ultimate_post_pro()->get_setting( 'ultp_fs_post_delete_allowed_users' );
				if ( ! empty( $allowed_users ) && is_array( $allowed_users ) && in_array( $user_id, $allowed_users ) ) {
					$settings['allow_post_delete'] = true;
				} else {
					$settings['allow_post_delete'] = false;
				}
			}

			$script = wp_json_encode( $settings );
			wp_add_inline_script( 'ultp_fs_editor_backend', "const ultpFsSettings=$script" );

			if ( isset( $_GET['action'] ) && 'edit' != sanitize_text_field( $_GET['action'] ) ) {
				remove_all_actions( 'admin_footer' );
			}
		}

	}

	public function my_profile_content() {
		$current_user = get_current_user_id();
		$user_data    = get_userdata( $current_user );
		$first_name   = $user_data->first_name;
		$last_name    = $user_data->last_name;
		$email        = $user_data->user_email;
		$username     = $user_data->user_login;
		$bio          = $user_data->description;

		$profile_data = array(
			array(
				'label' => __( 'First Name', 'ultimate-post-pro' ),
				'value' => $first_name,
			),
			array(
				'label' => __( 'Last Name', 'ultimate-post-pro' ),
				'value' => $last_name,
			),
			array(
				'label' => __( 'Email', 'ultimate-post-pro' ),
				'value' => $email,
			),
			array(
				'label' => __( 'Username', 'ultimate-post-pro' ),
				'value' => $username,
			),
			array(
				'label' => __( 'Bio', 'ultimate-post-pro' ),
				'value' => $bio,
			),
		);
		$profile_data = apply_filters( 'ultp_frontend_submission_user_profile_data', $profile_data );
		include ULTP_PRO_PATH . '/addons/frontend_submission/templates/profile.php';
	}

	public function change_password_content() {
		include ULTP_PRO_PATH . '/addons/frontend_submission/templates/change-password.php';
	}

	public function edit_account_content() {
		if ( is_user_logged_in() ) {
			$user_id = get_current_user_id();
			$user    = get_userdata( $user_id );
			include ULTP_PRO_PATH . '/addons/frontend_submission/templates/edit-account.php';
		}
	}

	public function get_allowed_blocks() {
		$allowed_block_types = array(
			'core/paragraph',
			'core/list',
			'core/code',
			'core/list-item',
			'core/heading',
			'core/image',
			'core/table',
			'core/video',
			'core/table-of-contents',
			'ultimate-post/table-of-content',
		);
		return $allowed_block_types;
	}

	public function allowed_block_types( $allowed_block_types, $block_editor_context ) {
		if ( $this->is_frontend_submission_user() ) {

			$allowed_block_types = $this->get_allowed_blocks();
		}

		return $allowed_block_types;
	}

	public function remove_revision_for_frontend_submission_user( $num ) {

		if ( $this->is_frontend_submission_user() ) {
			$num = 0;
		}
		return $num;
	}

	public function myaccount_content() {
		global $wp;

		if ( ! empty( $wp->query_vars ) ) {
			foreach ( $wp->query_vars as $key => $value ) {
				// Ignore pagename param.
				if ( 'pagename' === $key ) {
					continue;
				}

				if ( has_action( 'ultp_frontend_submission_account_' . $key . '_endpoint' ) ) {
					do_action( 'ultp_frontend_submission_account_' . $key . '_endpoint', $value );
					return;
				}
			}
		}

		// No endpoint found? Default to dashboard.
		$user_id = get_current_user_id();

		$publish_count = 0;

		$pending_count = 0;

		$draft_count = 0;

		$total_count = 0;

		global $wpdb;
		$user_id        = get_current_user_id();
		$prepared_query = $wpdb->prepare(
			"SELECT
            post_status,
            COUNT(*) AS post_count
        FROM $wpdb->posts
        WHERE post_author = %d
        AND post_type = 'post'
        GROUP BY post_status",
			$user_id
		);

		$results = $wpdb->get_results( $prepared_query );

		foreach ( $results as $res ) {
			if ( 'publish' == $res->post_status ) {
				$publish_count += (int) $res->post_count;
			}
			if ( 'pending' == $res->post_status ) {
				$pending_count += (int) $res->post_count;
			}
			if ( 'draft' == $res->post_status ) {
				$draft_count += (int) $res->post_count;
			}
		}
		$total_count = $publish_count + $pending_count + $draft_count;

		$posts_stats = array(
			array(
				'name'     => 'total_posts',
				'value'    => $total_count,
				'priority' => 10,
				'iconUrl'  => ULTP_PRO_URL . 'assets/img/frontend_submission/dashboard/all_posts.svg',
				'class'    => '',
				'label'    => __( 'Total Posts', 'ultimate-post-pro' ),
			),
			array(
				'name'     => 'pending_posts',
				'value'    => $pending_count,
				'priority' => 20,
				'iconUrl'  => ULTP_PRO_URL . 'assets/img/frontend_submission/dashboard/pending_posts.svg',
				'class'    => '',
				'label'    => __( 'Pending Posts', 'ultimate-post-pro' ),
			),
			array(
				'name'     => 'draft_posts',
				'value'    => $draft_count,
				'priority' => 30,
				'iconUrl'  => ULTP_PRO_URL . 'assets/img/frontend_submission/dashboard/draft_posts.svg',
				'class'    => 'ultp-fs-dollar_icon',
				'label'    => __( 'Draft Posts', 'ultimate-post-pro' ),
			),
			array(
				'name'     => 'published_posts',
				'value'    => $publish_count,
				'priority' => 40,
				'iconUrl'  => ULTP_PRO_URL . 'assets/img/frontend_submission/dashboard/published_posts.svg',
				'class'    => '',
				'label'    => __( 'Published Posts', 'ultimate-post-pro' ),
			),
		);

		include ULTP_PRO_PATH . '/addons/frontend_submission/templates/dashboard.php';

	}

	public function myaccount_navigation() {
		include ULTP_PRO_PATH . '/addons/frontend_submission/templates/navigation.php';
	}

	public function create_myaccount_page_if_not_created() {

		global $wpdb;

		$ultp_settings = get_option( 'ultp_options', array() );

		$slug         = esc_sql( 'fs-myaccount' );
		$page_title   = _x( 'Frontend Submission My account', 'Page title', 'ultimate-post-pro' );
		$option       = 'ultp_frontend_submission_myaccount_page_id';
		$post_parent  = 0;
		$post_status  = 'publish';
		$page_content = '<!-- wp:shortcode -->[ultp_frontend_account]<!-- /wp:shortcode -->';

		// $option_value = get_option( $option );
		$option_value = $ultp_settings[ $option ];

		if ( $option_value > 0 ) {
			$page_object = get_post( $option_value );

			if ( $page_object && 'page' === $page_object->post_type && ! in_array( $page_object->post_status, array( 'pending', 'trash', 'future', 'auto-draft' ), true ) ) {
				// Valid page is already in place.
				return $page_object->ID;
			}
		}

		if ( strlen( $page_content ) > 0 ) {
			// Search for an existing page with the specified page content (typically a shortcode).
			$shortcode        = str_replace( array( '<!-- wp:shortcode -->', '<!-- /wp:shortcode -->' ), '', $page_content );
			$valid_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status NOT IN ( 'pending', 'trash', 'future', 'auto-draft' ) AND post_content LIKE %s LIMIT 1;", "%{$shortcode}%" ) );
		} else {
			// Search for an existing page with the specified page slug.
			$valid_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status NOT IN ( 'pending', 'trash', 'future', 'auto-draft' )  AND post_name = %s LIMIT 1;", $slug ) );
		}

		if ( $valid_page_found ) {
			if ( $option ) {
				$data            = $GLOBALS['ultp_settings'];
				$data[ $option ] = $valid_page_found;
				update_option( 'ultp_options', $data );
				$GLOBALS['ultp_settings'] = $data;
			}
			return $valid_page_found;
		}

		// Search for a matching valid trashed page.
		if ( strlen( $page_content ) > 0 ) {
			// Search for an existing page with the specified page content (typically a shortcode).
			$trashed_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status = 'trash' AND post_content LIKE %s LIMIT 1;", "%{$page_content}%" ) );
		} else {
			// Search for an existing page with the specified page slug.
			$trashed_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status = 'trash' AND post_name = %s LIMIT 1;", $slug ) );
		}

		if ( $trashed_page_found ) {
			$page_id   = $trashed_page_found;
			$page_data = array(
				'ID'          => $page_id,
				'post_status' => $post_status,
			);
			wp_update_post( $page_data );
		} else {
			$page_data = array(
				'post_status'    => $post_status,
				'post_type'      => 'page',
				'post_author'    => 1,
				'post_name'      => $slug,
				'post_title'     => $page_title,
				'post_content'   => $page_content,
				'post_parent'    => $post_parent,
				'comment_status' => 'closed',
			);
			$page_id   = wp_insert_post( $page_data );

			/* phpcs:disable WooCommerce.Commenting.CommentHooks.MissingHookComment */
			do_action( 'ultp_frontend_submission_page_created', $page_id, $page_data );
			/* phpcs: enable */
		}

		if ( $option ) {
			$data            = $GLOBALS['ultp_settings'];
			$data[ $option ] = $page_id;
			update_option( 'ultp_options', $data );
			$GLOBALS['ultp_settings'] = $data;
		}
	}

	public static function init_shortcode() {
		$shortcodes = array(
			'ultp_frontend_account' => __CLASS__ . '::frontend_account_shortcode',
			'ultp_fs_block_editor'  => __CLASS__ . '::frontend_block_editor',
		);

		foreach ( $shortcodes as $shortcode => $function ) {
			add_shortcode( apply_filters( "{$shortcode}_shortcode_tag", $shortcode ), $function );
		}
	}

	public static function frontend_account_shortcode( $atts ) {
		if ( ! is_admin() ) {
			return self::shortcode_wrapper( array( '\ULTP_PRO\Frontend_Account_Shortcode', 'output' ), $atts );
		}
	}

	public static function frontend_block_editor( $atts ) {
		if ( ! is_admin() && ! wp_doing_ajax() ) {
			$frontend_editor = new \ULTP_PRO\Frontend_Block_Editor_Shortcode();
			return $frontend_editor->output( $atts );
		}
	}

	/**
	 * Shortcode Wrapper.
	 *
	 * @param string[] $function Callback function.
	 * @param array    $atts     Attributes. Default to empty array.
	 * @param array    $wrapper  Customer wrapper data.
	 *
	 * @return string
	 */
	public static function shortcode_wrapper(
		$function,
		$atts = array(),
		$wrapper = array(
			'class'  => 'ultp-frontend-submission',
			'before' => null,
			'after'  => null,
		)
	) {

		ob_start();

		// @codingStandardsIgnoreStart
		echo empty( $wrapper['before'] ) ? '<div class="' . esc_attr( $wrapper['class'] ) . '">' : $wrapper['before'];
		call_user_func( $function, $atts );
		echo empty( $wrapper['after'] ) ? '</div>' : $wrapper['after'];
		// @codingStandardsIgnoreEnd

		return ob_get_clean();
	}

	public function restrict_backend_access() {
		$current_user_id = get_current_user_id();
		$current_user    = get_userdata( $current_user_id );
		$user_roles      = $current_user->roles;

		$prevent_access = false;

		// Do not interfere with admin-post or admin-ajax requests.
		$exempted_paths = array( 'admin-post.php', 'admin-ajax.php', 'post.php', 'post-new.php' );

		if ( $this->is_allowed_media_access() ) {
			$exempted_paths[] = 'async-upload.php';
		}

		if ( isset( $_SERVER['SCRIPT_FILENAME'] )
			&& ! in_array( basename( sanitize_text_field( wp_unslash( $_SERVER['SCRIPT_FILENAME'] ) ) ), $exempted_paths, true )
		) {
			if ( in_array( 'ultp_frontend_submission', $user_roles ) ) {
				$prevent_access = true;
			}
		}
		if($this->is_frontend_submission_user()) {
			remove_all_actions( 'enqueue_block_editor_assets' );
			add_action( 'enqueue_block_editor_assets', 'wp_enqueue_registered_block_scripts_and_styles' );
			add_action( 'enqueue_block_editor_assets', 'enqueue_editor_block_styles_assets' );
			add_action( 'enqueue_block_editor_assets', 'wp_enqueue_editor_block_directory_assets' );
			add_action( 'enqueue_block_editor_assets', 'wp_enqueue_editor_format_library_assets' );
			add_action( 'enqueue_block_editor_assets', 'wp_enqueue_global_styles_css_custom_properties' );
			add_action( 'enqueue_block_editor_assets', array($this,'ultp_block_editor_register_scripts') );
		}
		if ( $prevent_access && $this->is_frontend_submission_user() ) {
			
			wp_safe_redirect( $this->get_endpoint_url( 'dashboard' ) );
			exit;
		}

	}

	public function is_frontend_submission_user( $current_user_id = '' ) {
		if ( ! $current_user_id ) {
			$current_user_id = get_current_user_id();
		}
		if ( $current_user_id ) {
			$current_user = get_userdata( $current_user_id );
			$user_roles   = $current_user->roles;
			if ( in_array( 'ultp_frontend_submission', $user_roles ) ) {
				return true;
			}
		}
		return false;
	}

	public function hide_admin_bar( $status ) {
		if ( $this->is_frontend_submission_user() ) {
			$status = false;
		}
		return $status;
	}

	public function create_frontend_submission_user_role_if_not_exist() {
		// Frontend user role id.
		$role_id = 'ultp_frontend_submission';
		if ( ! wp_roles()->is_role( $role_id ) ) {
			add_role( $role_id, __( 'Frontend Submission User', 'ultimate-post-pro' ), array( 'read' => true ) );
		}
	}

		/**
		 * Add query vars.
		 *
		 * @param array $vars Query vars.
		 * @return array
		 */
	public function add_query_vars( $vars ) {
		foreach ( $this->get_query_vars() as $key => $var ) {
			$vars[] = $key;
		}
		return $vars;
	}


	/**
	 * Get query vars.
	 *
	 * @return array
	 */
	public function get_query_vars() {
		return apply_filters( 'ultp_frontend_submission_get_query_vars', $this->query_vars );
	}

	/**
	 * Init query vars by settings
	 */
	public function init_query_vars() {
		// Query vars to add to WP.
		$this->query_vars = array(
			'frontend_submission' => ultimate_post_pro()->get_setting( 'editor_endpoint' ) ?: 'frontend_submission',
			'fs-edit-account'     => ultimate_post_pro()->get_setting( 'ultp_frontend_submission_edit_account_endpoint' ) ?: 'fs-edit-account',
			'fs-my-posts'         => ultimate_post_pro()->get_setting( 'ultp_frontend_submission_my_posts_endpoint' ) ?: 'fs-my-posts',
			// 'fs-my-profile' => ultimate_post_pro()->get_setting('ultp_frontend_submission_my_profile_endpoint')?:'fs-my-profile',
			'fs-change-password'  => ultimate_post_pro()->get_setting( 'ultp_frontend_submission_change_password_endpoint' ) ?: 'fs-change-password',
			'fs-logout'           => ultimate_post_pro()->get_setting( 'ultp_frontend_submission_logout_endpoint' ) ?: 'fs-logout',
			'fs-register'         => ultimate_post_pro()->get_setting( 'ultp_frontend_submission_register_endpoint' ) ?: 'fs-register',
		);
	}

	/**
	 * Add endpoints for query vars.
	 */
	public function add_endpoints() {
		global $wp;

		$mask = EP_ROOT | EP_PAGES;
		foreach ( $this->get_query_vars() as $key => $var ) {
			if ( ! empty( $var ) ) {
				add_rewrite_endpoint( $var, $mask );
			}
		}
		wp_enqueue_style( 'ultp_fs', ULTP_PRO_URL . 'assets/css/frontend_submission.css', array(), ULTP_PRO_VER );
		$this->create_myaccount_page_if_not_created();

	}

	/**
	 * Get query current active query var.
	 *
	 * @return string
	 */
	public function get_current_endpoint() {
		global $wp;

		foreach ( $this->get_query_vars() as $key => $value ) {
			if ( isset( $wp->query_vars[ $key ] ) ) {
				return $key;
			}
		}
		return '';
	}


	/**
	 * Parse the request and look for query vars - endpoints may not be supported.
	 */
	public function parse_request() {
		global $wp;

		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		// Map query vars to their keys, or get them if endpoints are not supported.
		foreach ( $this->get_query_vars() as $key => $var ) {
			if ( isset( $_GET[ $var ] ) ) {
				$wp->query_vars[ $key ] = sanitize_text_field( wp_unslash( $_GET[ $var ] ) );
			} elseif ( isset( $wp->query_vars[ $var ] ) ) {
				$wp->query_vars[ $key ] = $wp->query_vars[ $var ];
			}
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
	}


	public function template_include( $template ) {
		$current_endpoint = $this->get_current_endpoint();
		$editor_endpoint  = ultimate_post_pro()->get_setting( 'ultp_fs_editor_endpoint' ) ?: 'frontend_submission';

		switch ( $current_endpoint ) {
			case $editor_endpoint:
				wp_head();
				$test = new Frontend_Block_Editor_Shortcode();
				$test->output( array() );
				wp_footer();
				return;

			default:
				// code...
				break;
		}

		return $template;

	}

	public function get_endpoint_url( $endpoint = '' ) {
		$page_id   = apply_filters( 'ultp_frontend_submission_get_dashboard_page_id', ultimate_post_pro()->get_setting( 'ultp_frontend_submission_myaccount_page_id' ) );
		$permalink = 0 < $page_id ? get_permalink( $page_id ) : '';
		if ( ! $permalink ) {
			$permalink = get_home_url();
		}

		if ( 'dashboard' === $endpoint ) {
			return $permalink;
		}
		$query_vars = $this->query_vars;
		$endpoint   = ! empty( $query_vars[ $endpoint ] ) ? $query_vars[ $endpoint ] : $endpoint;

		if ( get_option( 'permalink_structure' ) ) {
			if ( strstr( $permalink, '?' ) ) {
				$query_string = '?' . wp_parse_url( $permalink, PHP_URL_QUERY );
				$permalink    = current( explode( '?', $permalink ) );
			} else {
				$query_string = '';
			}
			$url = trailingslashit( $permalink );

			$url .= user_trailingslashit( $endpoint );

			$url .= $query_string;
		} else {
			$url = add_query_arg( $endpoint, $permalink );
		}

		return apply_filters( 'ultp_frontend_submission_get_endpoint_url', $url, $endpoint, $permalink );

	}

	public function add_submit_post_button() {
		?>
		<div class="ultp-fs-account-submit-post"> 
			<a href="<?php echo admin_url( 'post-new.php' ); ?>" class="ultp-fs-btn ultp-title-sm"> <?php echo __( 'Submit Posts', 'ultimate-post-pro' ); ?> </a>
		</div>
		<?php
	}

	public function my_posts_content() {
		include ULTP_PRO_PATH . '/addons/frontend_submission/templates/my-posts.php';
	}


	public function registration_page_content() {
		$redirect_url = apply_filters( 'ultp_frontend_submission_registration_redirect_url', '' );
		include ULTP_PRO_PATH . '/addons/frontend_submission/templates/registration-form.php';
	}

	public function prevent_delete_posts( $status, $post ) {
		if($this->is_frontend_submission_user()) {
			if ( get_current_user_id() == $post->post_author && $this->is_allowed_to_delete_post() ) {
				return $status;
			}
			return false;
		}
		return $status;
	}

	public function process_change_password() {
		if ( ! ( isset( $_POST['change-password-nonce'] ) && wp_verify_nonce( $_POST['change-password-nonce'], 'change_password' ) ) ) {
			return;
		}

		$user_id = get_current_user_id();

		if ( $user_id <= 0 ) {
			return;
		}

		$current_password = ! empty( $_POST['current_password'] ) ? $_POST['current_password'] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$new_password     = ! empty( $_POST['new_password'] ) ? $_POST['new_password'] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$confirm_password = ! empty( $_POST['confirm_password'] ) ? $_POST['confirm_password'] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$change_pass      = true;

		$current_user = get_user_by( 'id', $user_id );

		$user     = new \stdClass();
		$user->ID = $user_id;

		if ( ! empty( $current_password ) && empty( $new_password ) && empty( $confirm_password ) ) {
			$change_pass = false;
		} elseif ( ! empty( $new_password ) && empty( $pass_cur ) ) {
			$change_pass = false;
		} elseif ( ! empty( $new_password ) && empty( $confirm_password ) ) {
			$change_pass = false;
		} elseif ( ( ! empty( $new_password ) || ! empty( $confirm_password ) ) && $new_password !== $confirm_password ) {
			$change_pass = false;
		} elseif ( ! empty( $new_password ) && ! wp_check_password( $current_password, $current_user->user_pass, $current_user->ID ) ) {
			$change_pass = false;
		}

		if ( $new_password && $change_pass ) {
			$user->user_pass = $new_password;

			wp_update_user( $user );
		}

	}

	public function set_logout_redirect_url( $redirect = '' ) {
		$redirect = $this->get_endpoint_url( 'dashboard' );
		if ( ultimate_post_pro()->get_setting( 'ultp_fs_logout_redirect' ) ) {
			$redirect = get_permalink( ultimate_post_pro()->get_setting( 'ultp_fs_logout_redirect' ) );
		}
		return $redirect;
	}


	public function process_login() {
		if ( ! ( isset( $_POST['ultp-fs-nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['ultp-fs-nonce'] ), 'ultp-fs-login' ) ) ) {
			return;
		}
		if ( isset( $_POST['login'], $_POST['username'], $_POST['password'] ) ) {

			$error_msg = '';

			try {
				$creds = array(
					'user_login'    => trim( wp_unslash( $_POST['username'] ) ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					'user_password' => $_POST['password'], // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
				);

				$validation_error = new WP_Error();
				$validation_error = apply_filters( 'ultp_frontend_submission_process_login_errors', $validation_error, $creds['user_login'], $creds['user_password'] );

				if ( $validation_error->get_error_code() ) {
					throw new \Exception( '<strong>' . __( 'Error:', 'ultimate-post-pro' ) . '</strong> ' . $validation_error->get_error_message() );
				}

				if ( empty( $creds['user_login'] ) ) {
					throw new \Exception( '<strong>' . __( 'Error:', 'ultimate-post-pro' ) . '</strong> ' . __( 'Username is required.', 'ultimate-post-pro' ) );
				}

				// On multisite, ensure user exists on current site, if not add them before allowing login.
				if ( is_multisite() ) {
					$user_data = get_user_by( is_email( $creds['user_login'] ) ? 'email' : 'login', $creds['user_login'] );

					if ( $user_data && ! is_user_member_of_blog( $user_data->ID, get_current_blog_id() ) ) {
						add_user_to_blog( get_current_blog_id(), $user_data->ID, 'ultp_frontend_submission' );
					}
				}

				do_action( 'ultp_frontend_submission_before_login', $_POST );

				// Peform the login.
				$user = wp_signon( apply_filters( 'ultp_frontend_submission_login_credentials', $creds ), is_ssl() );

				if ( is_wp_error( $user ) ) {
					throw new \Exception( $user->get_error_message() );
				} else {

					$redirect = '';
					if ( ! empty( $_POST['redirect'] ) ) {
						$redirect = wp_unslash( $_POST['redirect'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					}

					if ( empty( $redirect ) ) {
						$redirect = $this->get_endpoint_url( 'dashboard' );

					}

					$redirect = apply_filters( 'ultp_frontend_submission_login_redirect_url', $redirect, $user );

					add_filter(
						'ultp_fs_registration_successful',
						function() {
							return true;
						}
					);

					wp_safe_redirect( $this->get_endpoint_url('dashboard') ); // phpcs:ignore
					exit;
				}
			} catch ( \Exception $e ) {
				// wc_add_notice( apply_filters( 'login_errors', $e->getMessage() ), 'error' );
				// echo $e->getMessage();
				$error_msg = $e->getMessage();

				add_filter(
					'ultp_fs_notice',
					function() use ( $error_msg ) {
						return $error_msg;
					}
				);

				do_action( 'ultp_frontend_submission_login_failed' );

			}
		}
	}

	public function process_registration() {
		if ( ! ( isset( $_POST['ultp-fs-nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['ultp-fs-nonce'] ), 'ultp-fs-registration' ) ) ) {
			return;
		}

		if ( isset( $_POST['username'], $_POST['email'], $_POST['password'], $_POST['confirm_password'] ) ) {
			$username         = sanitize_user( wp_unslash( $_POST['username'] ) );
			$email            = sanitize_email( wp_unslash( $_POST['email'] ) );
			$password         = $_POST['password']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			$confirm_password = $_POST['confirm_password']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash

			try {
				if ( $password !== $confirm_password ) {
					throw new Exception( __( 'Password and Confirm password should be matched.', 'ultimate-post-pro' ) );
				}
				if ( empty( $email ) || ! is_email( $email ) ) {
					throw new Exception( __( 'Please provide a valid email address.', 'ultimate-post-pro' ) );
				}

				if ( email_exists( $email ) ) {
					throw new Exception( __( 'An account is already registered with your email address.', 'ultimate-post-pro' ) );
				}

				if ( empty( $username ) || ! validate_username( $username ) ) {
					throw new Exception( __( 'Please enter a valid account username.', 'ultimate-post-pro' ) );
				}

				if ( username_exists( $username ) ) {
					throw new Exception( __( 'An account is already registered with that username. Please choose another.', 'ultimate-post-pro' ) );
				}

				$new_customer_data = apply_filters(
					'ultp_frontend_submission_new_customer_data',
					array(
						'user_login' => $username,
						'user_pass'  => $password,
						'user_email' => $email,
						'role'       => 'ultp_frontend_submission',
					)
				);

				if ( isset( $_POST['first_name'] ) ) {
					$new_customer_data['first_name'] = sanitize_text_field( $_POST['first_name'] );
				}
				if ( isset( $_POST['last_name'] ) ) {
					$new_customer_data['last_name'] = sanitize_text_field( $_POST['last_name'] );
				}

				do_action( 'ultp_frontend_submission_before_new_user_register', $_POST );
				$customer_id = wp_insert_user( $new_customer_data );

				do_action( 'ultp_frontend_submission_new_user_registered', $customer_id, $new_customer_data );

				$redirect = '';
				if ( ! empty( $_POST['redirect'] ) ) {
					$redirect = wp_unslash( $_POST['redirect'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				}

				$redirect = apply_filters( 'ultp_frontend_submission_registration_redirect_url', $redirect, $customer_id );

				if ( empty( $redirect ) ) {
					$redirect_url = $this->get_endpoint_url( 'dashboard' );
				} else {
					$redirect_url = wp_validate_redirect( $redirect, $this->get_endpoint_url( 'dashboard' ) );
				}

				$redirect_url = add_query_arg(
					array(
						'fs_notice' => 'rs',
					),
					$redirect_url
				);

				if ( 'yes' === ultimate_post_pro()->get_setting( 'ultp_fs_email_registration_successful' ) ) {
					ob_start();
					$site_name         = get_bloginfo( 'name' );
					$user_display_name = $new_customer_data['first_name'] . ' ' . $new_customer_data['last_name'];
					include ULTP_PRO_PATH . '/addons/frontend_submission/templates/emails/registration-successful.php';
					$content = ob_get_clean();
					$subject = __( 'Registration Successful', 'ultimate-post-pro' );

					if ( is_email( $email ) ) {
						wp_mail( $email, $subject, $content );
					}
				}

				if ( 'yes' === ultimate_post_pro()->get_setting( 'ultp_fs_email_admin_new_user_registered' ) ) {

					$content = '';
					ob_start();
					$user_display_name = $new_customer_data['first_name'] . ' ' . $new_customer_data['last_name'];
					$site_name         = get_bloginfo( 'name' );
					include ULTP_PRO_PATH . '/addons/frontend_submission/templates/emails/admin-new-user-registered.php';
					$content = ob_get_clean();
					$subject = __( 'New User Registered', 'ultimate-post-pro' );

					$admin_email = get_option( 'admin_email' );

					wp_mail( $admin_email, $subject, $content );
				}

				wp_redirect( $redirect_url ); // phpcs:ignore
				exit;

			} catch ( \Exception $e ) {
				// throw $th;
				do_action( 'ultp_frontend_submission_registration_failed' );

				$error_msg = $e->getMessage();

				add_filter(
					'ultp_fs_notice',
					function() use ( $error_msg ) {
						return $error_msg;
					}
				);

			}
		}
	}



	public function process_account_data_changes() {
		if ( ! ( isset( $_POST['ultp-fs-nonce'] ) && wp_verify_nonce( $_POST['ultp-fs-nonce'], 'save_account_data' ) ) ) {
			return;
		}

		$user_id = get_current_user_id();

		if ( $user_id <= 0 ) {
			return;
		}

		$first_name   = ! empty( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '';
		$last_name    = ! empty( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : '';
		$display_name = ! empty( $_POST['display_name'] ) ? sanitize_text_field( wp_unslash( $_POST['display_name'] ) ) : '';

		// New user data.
		$user               = new \stdClass();
		$user->ID           = $user_id;
		$user->first_name   = $first_name;
		$user->last_name    = $last_name;
		$user->display_name = $display_name;

		// Prevent display name to be changed to email.
		if ( is_email( $display_name ) ) {
			echo __( 'Display name cannot be changed to email address due to privacy concern.', 'ultimate-post-pro' );
		}

		wp_update_user( $user );

		echo 'Account details changed successfully.';
		wp_safe_redirect( $this->get_endpoint_url( 'dashboard' ) );
		exit;
	}


	public function render_block_editor() {
		$frontend_submission_endpoint = ultimate_post_pro()->get_setting( 'editor_endpoint' ) ?: 'frontend_submission';
		if ( $this->get_current_endpoint() === $frontend_submission_endpoint ) {
			wp_enqueue_script( 'ultp_fs_editor', ULTP_PRO_URL . 'assets/js/ultp_fs_editor.js', array( 'wp-block-editor', 'wp-block-library', 'wp-blocks', 'wp-components', 'wp-compose', 'wp-data', 'wp-deprecated', 'wp-dom-ready', 'wp-editor', 'wp-element', 'wp-format-library', 'wp-i18n', 'wp-keyboard-shortcuts', 'wp-media-utils', 'wp-preferences' ), ULTP_PRO_VER, true );
			wp_enqueue_style( 'ultp_fs_editor', ULTP_PRO_URL . 'assets/css/block_editor.css', array( 'wp-edit-blocks' ), ULTP_PRO_VER );
			\do_action( 'enqueue_block_assets' );
			?>
				<div id="ultp_frontend_submission_block_editor"> </div>
			<?php

		}
	}

	public function increase_autosave_interval( $settings ) {
		global $post;
		if ( $post && $this->is_frontend_submission_user( $post->post_author ) ) {
			$settings['autosaveInterval']      = 86400;
			$settings['localAutosaveInterval'] = 86400;
			$settings['preferences']           = false;
			unset( $settings['autosave'] );
			unset( $settings['availableTemplates'] );

			if ( isset( $settings['enableCustomFields'] ) ) {
				unset( $settings['enableCustomFields'] );
			}
		}

		return $settings;
	}

	public function ultp_frontend_submission_fetch_post() {
		$post_id = $_POST['post_id'];
		$post    = get_post( $post_id );
		wp_send_json_success( $post );
	}

	public function is_rank_math_support() {
		$user_id  = get_current_user_id();
		$is_allow = ultimate_post_pro()->get_setting( 'ultp_fs_seo_support' );
		if ( 'yes' == $is_allow ) {
			$allowed_users = ultimate_post_pro()->get_setting( 'ultp_fs_seo_support_allowed_users' );

			if ( ! empty( $allowed_users ) && is_array( $allowed_users ) && in_array( $user_id, $allowed_users ) ) {
				return true;
			}
		}
		return false;
	}


	public function post_edit_after_publish( $allcaps, $cap, $args ) {

		if ( $this->is_frontend_submission_user() ) {
			$allcaps['edit_posts'] = true;

			if ( isset( $_GET['action'] ) && 'edit' != sanitize_text_field( $_GET['action'] ) ) {
				$allcaps['edit_posts'] = false;
			}
		}

		if ( $this->is_frontend_submission_user() && $this->is_allowed_to_edit_post_after_publish() ) {
			$allcaps['edit_published_posts'] = true;
		}
		if ( $this->is_frontend_submission_user() && $this->is_allowed_to_publish_post() ) {
			$allcaps['publish_posts'] = true;
		}

		if ( $this->is_frontend_submission_user() && $this->is_rank_math_support() ) {
			$allcaps['rank_math_link_builder']    = true;
			$allcaps['rank_math_onpage_general']  = true;
			$allcaps['rank_math_onpage_analysis'] = true;
		}

		if ( $this->is_frontend_submission_user() && $this->is_allowed_to_delete_post() ) {
			$allcaps['delete_posts'] = true;
		}

		if ( $this->is_frontend_submission_user() && $this->is_allowed_media_access() ) {
			$allcaps['upload_files'] = true;
		}

		return $allcaps;
	}


	public function ultp_block_editor_register_scripts() {
        ultimate_post()->register_scripts_common();
        global $pagenow;
        $depends = 'wp-editor';
        if ($pagenow === 'widgets.php' ) {
            $depends = 'wp-edit-widgets';
        }
        wp_enqueue_script('ultp-blocks-editor-script', ULTP_URL.'assets/js/editor.blocks.js', array('wp-i18n', 'wp-element', 'wp-blocks', 'wp-components', $depends ), ULTP_VER, true);
        wp_enqueue_style('ultp-blocks-editor-css', ULTP_URL.'assets/css/blocks.editor.css', array(), ULTP_VER);
        if (is_rtl()) { 
            wp_enqueue_style('ultp-blocks-editor-rtl-css', ULTP_URL.'assets/css/rtl.css', array(), ULTP_VER); 
        }
        $is_active = ultimate_post()->is_lc_active();
        $post_type = get_post_type();
        wp_localize_script('ultp-blocks-editor-script', 'ultp_data', array(
            'url' => ULTP_URL,
            'ajax' => admin_url('admin-ajax.php'),
            'security' => wp_create_nonce('ultp-nonce'),
            'hide_import_btn' => ultimate_post()->get_setting('hide_import_btn'),
            'upload' => wp_upload_dir()['basedir'] . '/ultp',
            'premium_link' => ultimate_post()->get_premium_link(),
            'license' => $is_active ? get_option('edd_ultp_license_key') : '',
            'active' => $is_active,
            'archive' => ultimate_post()->is_archive_builder(),
            'settings' => ultimate_post()->get_setting(),
            'post_type' => $post_type == 'premade' ? 'ultp_builder' : $post_type, // premade used for ultp.wpxpo.com
            'date_format' => get_option('date_format'),
            'time_format' => get_option('time_format'),
            'blog' => get_current_blog_id(),
            'archive_child' => ultimate_post()->is_archive_child_builder(),
            'affiliate_id' => apply_filters( 'ultp_affiliate_id', FALSE ),
            'category_url' =>admin_url( 'edit-tags.php?taxonomy=category' ),
            'disable_image_size' => ultimate_post()->get_setting('disable_image_size')
        ));
        wp_set_script_translations( 'ultp-blocks-editor-script', 'ultimate-post', ULTP_PATH . 'languages/' );
    }

}
