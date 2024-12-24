<?php
defined( 'ABSPATH' ) || exit;

add_action( 'init', 'ultp_progressbar_init' );
function ultp_progressbar_init() {
    if ( ultimate_post()->get_setting( 'ultp_progressbar' ) == 'true' ) {
        require_once ULTP_PRO_PATH . '/addons/progressbar/ProgressBar.php';
        new \ULTP_PRO\ProgressBar();
    }
    add_filter( 'ultp_settings', 'get_porgressbar_settings', 10, 1 );
}

/**
 * ProgressBar Settings Field.
 *
 * @since v.
 * @param $config
 * @return array
 */
function get_porgressbar_settings( $config ) {
    $arr = array(
        'ultp_progressbar' => array(
            'label' => __('Progress Bar', 'ultimate-post-pro'),
            'attr' => array(
                'compare_heading' => array(
                    'type'  => 'heading',
                    'label' => __('Progress Bar Settings', 'ultimate-post-pro'),
                ),
                'progressbar_height' => array(
                    'type'     => 'number',
                    'label'   => __( 'Select Height', 'ultimate-post-pro' ),
                    'desc'    => __( 'Select Progress Bar height', 'ultimate-post-pro' ),
                    'default' => '5'
                ),
                'progressbar_color' => array(
                    'type'    => 'color',
                    'label'   => __( 'Select Color', 'ultimate-post-pro'),
                    'desc'    => __( 'Select Progress Bar Color', 'ultimate-post-pro' ),
                    'default' => '#037fff',
                ),
                'progressbar_position' => array(
                    'type'    => 'select',
                    'label'   => __( 'Progress Bar position', 'ultimate-post-pro' ),
                    'desc'    => __( 'Select your progress Bar position', 'ultimate-post-pro' ),
                    'options' => array(
                        'top' => __( 'Top','ultimate-post-pro' ),
                        'bottom' => __( 'Bottom','ultimate-post-pro' ),
                    ),
                ),
                'progressbar_allpage' => array(
                    'type'    => 'switch',
                    'label'   => __( 'All Page', 'ultimate-post-pro' ),
                    'desc'    => __( 'Show progress Bar in All page', 'ultimate-post-pro' ),
                ),
                'choice_progressbar' => array(
                    'type'    => 'multiselect',
                    'label'   => __( 'Choice Progress Bar', 'ultimate-post-pro' ),
                    'desc'    => __( 'Select progress bar where you want to show', 'ultimate-post-pro' ),
                    'options' => ultimate_post()->get_post_type(),
                    'default' => array('post'),
                ),
                'progressbar_homepage' => array(
                    'type'    => 'switch',
                    'label'   => __( 'Home Page', 'ultimate-post-pro' ),
                    'desc'    => __( 'Show progress Bar in home page', 'ultimate-post-pro' ),
                )
            )
        )
    );

    return array_merge( $config, $arr );
}

