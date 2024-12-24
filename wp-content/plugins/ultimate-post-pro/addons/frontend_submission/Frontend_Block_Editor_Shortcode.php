<?php
/**
 * Block Editor Shortcodes
 */
namespace ULTP_PRO;


defined( 'ABSPATH' ) || exit;

/**
 * Block Editor Shortcode
 */
class Frontend_Block_Editor_Shortcode {
    /**
	 * Editor object
	 *
	 * @var Editor|null
	 */
	protected $editor = null;

	/**
	 * Editor settings
	 *
	 * @var array
	 */
	protected $settings = [];

	/**
	 * Record the do_blocks hook
	 *
	 * @var string|null
	 */
	private $doing_hook = null;

	/**
	 * Whether the assets have been registered already.
	 */
	public static $registered_assets = false;


    public function __construct()
    {
		add_action('wp_ajax_nopriv_ultp_frontend_submission_post_action',array($this,'ultp_frontend_submission_post_action'));
		add_action('wp_ajax_ultp_frontend_submission_post_action',array($this,'ultp_frontend_submission_post_action'));
    }



	public function get_editor_type() {
		return 'core';
	}


	/**
	 * Get a list of allowed blocks by looking at the allowed comment tags
	 *
	 * @return string[]
	 */
	protected function get_allowed_blocks() {
		global $allowedtags;

		$allowed = [ 'core/paragraph', 'core/list', 'core/code', 'core/list-item','core/heading','core/image','core/table','core/video','core/table-of-contents','ultimate-post/table-of-content' ];
		$convert = [
			'blockquote' => 'core/quote',
			'h1' => 'core/heading',
			'h2' => 'core/heading',
			'h3' => 'core/heading',
			'img' => 'core/image',
			'ul' => 'core/list',
			'ol' => 'core/list',
			'pre' => 'core/code',
			'table' => 'core/table',
			'video' => 'core/video',
		];

		foreach ( array_keys( $allowedtags ) as $tag ) {
			if ( isset( $convert[ $tag ] ) ) {
				$allowed[] = $convert[ $tag ];
			}
		}

		return apply_filters( 'ultp_frontend_submission_allowed_blocks', array_unique( $allowed ), $this->get_editor_type() );
	}
	/**
	 * Get the default settings for the editor
	 *
	 * @return array
	 */
	private function get_default_settings() {
		// Settings for the editor
		$default_settings = [
			'editor' => [],
			'iso' => [
				'blocks' => [
					'allowBlocks' => $this->get_allowed_blocks(),
				],
				'header'=>true,
				'footer'=>false,
				'moreMenu' =>false,
				'sidebar' => [
					'inserter' => true,
					'inspector' => true,
				],
				'toolbar' => [
					'navigation' => true,
					'inserter'=> true,
					'navigation' => true,
					'undo' => true,
					'selectorTool' => true,
					'documentInspector' => 'Author Details',
					'inspector'=>true
				],
				'defaultPreferences' => [
					'fixedToolbar'    => false,
					'isComplementaryAreaVisible' => true
				],
				'allowEmbeds' => [
					'youtube',
					'vimeo',
					'wordpress',
					'wordpress-tv',
					'crowdsignal',
					'imgur',
				],
			],
			'editorType' => $this->get_editor_type(),
			'allowUrlEmbed' => false,
			'pastePlainText' => false,
			'replaceParagraphCode' => false,
			'patchEmoji' => false,
			'pluginsUrl' => plugins_url( '', __DIR__ ),
			'version' => ULTP_PRO_VER,
			'autocompleter' => true,
            'setFeatureImage' => false,
			'ajax' => admin_url('admin-ajax.php')
		];

		if(!is_user_logged_in()) {
			$default_settings['setFeatureImage'] = false;
			$page_id =  ultimate_post()->get_setting('ultp_fs_guest_form_submitted_redirect')?:0;
			$permalink = 0 < $page_id ? get_permalink( $page_id ) : '';
			if ( ! $permalink ) {
				$permalink =  get_home_url();
			}
			$default_settings['redirectAfterSubmission'] = $permalink;
		}

		return apply_filters( 'ultp_frontend_submission_editor_settings', $default_settings );
	}

	/**
	 * Load Gutenberg if a comment form is enabled
	 *
	 * @return void
	 */
	public function load_editor( $textarea, $container = null ) {
		$this->editor = new Editor();

		$settings = $this->get_default_settings();
		$settings['editor']       = array_merge( $settings['editor'], $this->editor->get_editor_settings() );
		$settings['saveTextarea'] = $textarea;
		$settings['container']    = $container;

		$this->editor->load( $settings );
		$this->settings = $settings;
		$settings['nonce'] = wp_create_nonce('ultp_fronend_submission_action');

        wp_enqueue_script('ultp_fronend_submission_editor', ULTP_PRO_URL.'assets/js/ultp_fs_editor.js',array('lodash', 'react', 'wp-a11y', 'wp-api-fetch', 'wp-block-editor', 'wp-block-library', 'wp-blocks', 'wp-components', 'wp-compose', 'wp-data', 'wp-deprecated', 'wp-dom', 'wp-dom-ready', 'wp-editor', 'wp-element', 'wp-format-library', 'wp-hooks', 'wp-i18n', 'wp-is-shallow-equal', 'wp-keyboard-shortcuts', 'wp-keycodes', 'wp-media-utils', 'wp-plugins', 'wp-polyfill', 'wp-preferences', 'wp-primitives', 'wp-rich-text', 'wp-viewport'),ULTP_VER,true);

		// Enqueue settings separately to allow for dynamic loading.
		wp_register_script( 'ultp-fs-settings', '', [], $settings['version'], true );
		wp_add_inline_script( 'ultp-fs-settings', 'const ultpFsBlockEditorSettings = ' . wp_json_encode( $settings ), 'before' );
		wp_enqueue_script( 'ultp-fs-settings' );

	}

	
	

	/**
	 * Callback to show admin editor
	 *
	 * @param string $hook Hook.
	 * @return boolean
	 */
	public function can_show_admin_editor( $hook ) {
		return false;
	}


	/**
	 * Output the shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 */
	public function output( $atts ) {
		if('yes' ===ultimate_post_pro()->get_setting('ultp_fs_enable_guest_user_post_submit')) {
			global $wp_version;
			if(version_compare($wp_version,'6.4','>=')) {
				$this->load_editor('#ultp-fronend-submission-block-editor','.ultp-fronend-submission-block-editor');
				return "<div id='ultp-fronend-submission-block-editor' class='ultp-fs-block-editor__wrappper'> </div>";
			} else {
				return __('For Creating Guest Editor, you need at least WordPress version 6.4.1','ultimate-post-pro');
			}
		} else {
			return __('For Creating Guest Editor, You Have to enable Guest User Post Submission','ultimate-post-pro');
		}
	}


	public function ultp_frontend_submission_post_action() {
		
        $author = isset($_POST['author'])?sanitize_text_field($_POST['author']):'Guest';
        $email = isset($_POST['author_email'])?sanitize_email($_POST['author_email']):'';
        $url = isset($_POST['author_url'])?esc_url_raw($_POST['author_url']):'';
        $post_arr = array();
        $post_arr['post_title'] = isset($_POST['title'])?sanitize_text_field( $_POST['title'] ):'';
        $post_arr['post_excerpt'] = isset($_POST['excerpt'])?sanitize_textarea_field( $_POST['excerpt'] ):'';
        $post_arr['post_status'] = apply_filters( 'ultp_frontend_submission_guest_default_post_status','pending' );
        $post_arr['meta_input'] = array(
			'ultp_fs_post_created_by_guest' => true,
            'ultp_fs_post_author' => $author,
            'ultp_fs_post_email' => $email,
            'ultp_fs_post_author_url' => $url,
            'ultp_fs_post_created' => true,
        );
        $post_arr['post_content'] = wp_kses_post( $_POST['content'] );

        $post_id = wp_insert_post( $post_arr );

        do_action( 'ultp_fs_guest_post_created', $post_id, $email,$author, $post_arr );

        $post_submitted_message = apply_filters( 'ultp_fs_guest_post_submitted_notice', __('Your Post Is Submitted Successful.','ultimate-post-pro') );

        wp_send_json_success( array('message'=>$post_submitted_message,'post_id'=>$post_id)  );
	}
}
