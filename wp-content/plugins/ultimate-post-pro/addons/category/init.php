<?php
defined( 'ABSPATH' ) || exit;

add_action( 'init', 'ultp_category_init' );
function ultp_category_init() {
	if ( ultimate_post()->get_setting( 'ultp_category' ) == 'true' ) {
		require_once ULTP_PRO_PATH . '/addons/category/Category.php';
		new \ULTP_PRO\Category();
	}
	add_filter( 'ultp_settings', 'get_category_settings', 10, 1 );
}

function get_category_settings( $config ) {
	$arr = array(
		'ultp_category' => array(
			'label' => __('Taxonomy', 'ultimate-post-pro'),
			'attr' => array(
				'compare_heading' => array(
					'type'  => 'heading',
					'label' => __('Category Settings', 'ultimate-post-pro'),
				),
				'taxonomy_list' => array(
					'type' => 'multiselect',
					'label' => __('Select Taxonomy', 'ultimate-post-pro'),
					'options' => get_taxonomies(),
					'desc' => __('You Can Select Multiple Taxonomy.', 'ultimate-post-pro'),
					'default' => ['category']
				)
			)
		)
	);
	return array_merge($config, $arr);
}