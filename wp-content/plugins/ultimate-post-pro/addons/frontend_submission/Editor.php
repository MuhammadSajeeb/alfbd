<?php

namespace ULTP_PRO;

/**
 * Provides functions to load Gutenberg assets
 */
class Editor {
	/**
	 * Can upload?
	 *
	 * @var boolean
	 */
	private $can_upload = false;

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'template_redirect', [ $this, 'setup_media' ] );
		add_filter( 'block_editor_settings_all', [ $this, 'block_editor_settings_all' ] );
		add_filter( 'should_load_block_editor_scripts_and_styles', '__return_false' );
	}

	/**
	 * Load Gutenberg
	 *
	 * Based on wp-admin/edit-form-blocks.php
	 *
	 * @param array $settings Plugin settings.
	 * @return void
	 */
	public function load( $settings ) {
		global $post;

		$this->can_upload = false;
		$this->load_extra_blocks();
		// Keep Jetpack out of things
		add_filter(
			'jetpack_blocks_variation',
			function() {
				return 'no-post-editor';
			}
		);

		// Only call the editor assets if we are not dynamically loading.
		if ( ! defined( '__EXPERIMENTAL_DYNAMIC_LOAD' ) ) {
			//do_action( 'enqueue_block_editor_assets' );
		}

		// Gutenberg styles
		wp_enqueue_script( 'wp-edit-post' );
		wp_enqueue_style( 'wp-edit-post' );
		wp_enqueue_style( 'wp-format-library' );

		$this->setup_rest_api();

		set_current_screen( 'front' );
		wp_styles()->done = array( 'wp-reset-editor-styles' );

		$categories = wp_json_encode( get_block_categories( $post ) );

		if ( $categories !== false ) {
			wp_add_inline_script(
				'wp-blocks',
				sprintf( 'wp.blocks.setCategories( %s );', $categories ),
				'after'
			);
		}

		/**
		 * @psalm-suppress PossiblyFalseOperand
		 */
		wp_add_inline_script(
			'wp-blocks',
			'wp.blocks.unstable__bootstrapServerSideBlockDefinitions(' . wp_json_encode( get_block_editor_server_block_settings() ) . ');'
		);

		$this->setup_media();

        

	}

	/**
	 * Load any third-party blocks
	 *
	 * @return void
	 */
	private function load_extra_blocks() {
		// phpcs:ignore
		$GLOBALS['hook_suffix'] = '';

		/**
		 * @psalm-suppress MissingFile
		 */
		require_once ABSPATH . 'wp-admin/includes/class-wp-screen.php';
		/**
		 * @psalm-suppress MissingFile
		 */
		require_once ABSPATH . 'wp-admin/includes/screen.php';
		/**
		 * @psalm-suppress MissingFile
		 */
		require_once ABSPATH . 'wp-admin/includes/post.php';

		// Fake a WP_Screen object so we can pretend we're in the block editor, and therefore other block libraries load
		set_current_screen();

		$current_screen = get_current_screen();
		if ( $current_screen ) {
			$current_screen->is_block_editor( true );
		}
	}

	/**
	 * Override some features that probably don't make sense in an isolated editor
	 *
	 * @param array $settings Settings array.
	 * @return array
	 */
	public function block_editor_settings_all( array $settings ) {
		$settings['availableLegacyWidgets']        = (object) [];
		$settings['hasPermissionsToManageWidgets'] = false;

		return $settings;
	}

	/**
	 * Set up Gutenberg editor settings
	 *
	 * @return Array
	 */
	public function get_editor_settings() {
		global $post_type, $post_type_object, $post, $title, $wp_meta_boxes;

		$supports_layout = false;

		if ( function_exists( 'wp_theme_has_theme_json' ) ) {
			$supports_layout = wp_theme_has_theme_json();
		}


		// phpcs:ignore
		$body_placeholder = apply_filters( 'write_your_story', null, $post );
		$block_editor_context = new \WP_Block_Editor_Context( array( 'post' => $post ) );

		$editor_settings = array(
			'availableTemplates'                   => [],
			'disablePostFormats'                   => ! current_theme_supports( 'post-formats' ),
			/** This filter is documented in wp-admin/edit-form-advanced.php */
			// phpcs:ignore
			'titlePlaceholder'                     => apply_filters( 'enter_title_here', __( 'Add title', 'ultimate-post-pro' ), $post ),
			'bodyPlaceholder'                      => $body_placeholder,
			'autosaveInterval'                     => AUTOSAVE_INTERVAL,
			'styles'                               => get_block_editor_theme_styles(),
			'richEditingEnabled'                   => user_can_richedit(),
			'postLock'                             => false,
			'supportsLayout'                       => $supports_layout,
			'hasFixedToolbar'                      => true,
			'hasInlineToolbar'                     => true,
			'__experimentalBlockPatterns'          => [],
			'__experimentalBlockPatternCategories' => [],
			'supportsTemplateMode'                 => current_theme_supports( 'block-templates' ),
			'enableCustomFields'                   => true,
			'generateAnchors'                      => true,
			'canLockBlocks'                        => false,
			'disableCustomColors'    => get_theme_support( 'disable-custom-colors' ),
			'disableCustomFontSizes' => get_theme_support( 'disable-custom-font-sizes' ),
			/** This filter is documented in wp-admin/edit-form-advanced.php */
			'allowedMimeTypes'       => [],
			'codeEditingEnabled'     => false,
			'__experimentalCanUserUseUnfilteredHTML' => false,
			'allowedBlockTypes' => get_allowed_block_types( $block_editor_context ),
			'blockCategories'   => get_block_categories( $block_editor_context ),
            'hasUploadPermissions' => false
		);

		$editor_settings['__unstableResolvedAssets'] = $this->wp_get_iframed_editor_assets();




		
		return get_block_editor_settings( $editor_settings, $block_editor_context );
	}

	/**
	 * Set up the Gutenberg REST API and preloaded data
	 *
	 * @return void
	 */
	public function setup_rest_api() {
		global $post;

		$post_type = 'post';

		// Preload common data.
		$preload_paths = array(
			array( '/wp/v2/blocks', 'OPTIONS' ),
		);

		/**
		 * @psalm-suppress TooManyArguments
		 */
		$preload_paths = apply_filters( 'block_editor_preload_paths', $preload_paths, $post );
		$preload_data  = array_reduce( $preload_paths, 'rest_preload_api_request', array() );

		$encoded = wp_json_encode( $preload_data );
		if ( $encoded !== false ) {
			wp_add_inline_script(
				'wp-editor',
				sprintf( 'wp.apiFetch.use( wp.apiFetch.createPreloadingMiddleware( %s ) );', $encoded ),
				'after'
			);
		}
	}

	/**
	 * Ensure media works in Gutenberg
	 *
	 * @return void
	 */
	public function setup_media() {
		if ( ! $this->can_upload ) {
			return;
		}

		// If we've already loaded the media stuff then don't do it again
		if ( did_action( 'wp_enqueue_media' ) > 0 ) {
			return;
		}

		/**
		 * @psalm-suppress MissingFile
		 */
		require_once ABSPATH . 'wp-admin/includes/media.php';

		wp_enqueue_media();
	}

	public function wp_get_iframed_editor_assets() {
		$script_handles = array();
		$style_handles  = array(
			'wp-block-editor',
			'wp-block-library',
			'wp-edit-blocks',
		);

		if ( current_theme_supports( 'wp-block-styles' ) ) {
			$style_handles[] = 'wp-block-library-theme';
		}

		$block_registry = \WP_Block_Type_Registry::get_instance();

		foreach ( $block_registry->get_all_registered() as $block_type ) {
			if ( ! empty( $block_type->style ) ) {
				$style_handles = array_merge( $style_handles, (array) $block_type->style );
			}

			if ( ! empty( $block_type->editor_style ) ) {
				$style_handles = array_merge( $style_handles, (array) $block_type->editor_style );
			}

			if ( ! empty( $block_type->script ) ) {
				$script_handles = array_merge( $script_handles, (array) $block_type->script );
			}

			if ( ! empty( $block_type->view_script ) ) {
				$script_handles = array_merge( $script_handles, (array) $block_type->view_script );
			}
		}

		$style_handles = apply_filters( 'ultp_fs_block_editor_styles', $style_handles );
		$style_handles = array_unique( $style_handles );
		$done          = wp_styles()->done;

		ob_start();

		// We do not need reset styles for the iframed editor.
		wp_styles()->done = array( 'wp-reset-editor-styles' );
		wp_styles()->do_items( $style_handles );
		wp_styles()->done = $done;

		$styles = ob_get_clean();

		$script_handles = array_unique( apply_filters( 'ultp_frontend_submission_editor_scripts', $script_handles ) );
		$done           = wp_scripts()->done;

		ob_start();

		wp_scripts()->done = array();
		wp_scripts()->do_items( $script_handles );
		wp_scripts()->done = $done;

		$scripts = ob_get_clean();

		return wp_json_encode(
			[
				'styles'  => $styles,
				'scripts' => $scripts,
			]
		);
	}
}
