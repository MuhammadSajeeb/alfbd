<?php
/**
 * My Account Dashboard
 */

use ULTP\Frontend_Block_Editor_Shortcode;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
$current_page = ( isset( $_GET[ 'current_page' ] ) && $_GET[ 'current_page' ] ) ? absint( $_GET[ 'current_page' ] ) : 1;
?>

<div class="ultp-fs-dashboard-wrapper"> 
    <div class="ultp-fs-dashboard-heading">
        <h2 class="ultp-fs-dashboard-heading-text">
            <?php esc_html_e('Post Submission Status','ultimate-post-pro'); ?>
        </h2>
    </div>
    <div class="ultp-fs-dashboard-content"> 
        <ul>
            <?php foreach ( $posts_stats as $post_stats ) : ?>
                <li class="<?php echo esc_attr($post_stats['class']); ?>">
                    <div class="ultp-fs-dashboard-stats-card"> 
                            <span class="ultp-fs-center">
                                <?php  
                                    // if(file_exists($post_stats['iconUrl'])){
                                        echo  file_get_contents($post_stats['iconUrl']); 
                                ?>
                            </span>
                        
                        <div class="ultp-fs-dashboard-stats-card__content"> 
                            <span class="ultp-fs-dashboard-stats-card__card_title ultp-title-sm"> <?php echo esc_html($post_stats['label']); ?> </span>
                            <span class="ultp-fs-dashboard-stats-card__card_value"> <?php echo esc_html($post_stats['value']); ?> </span>
                        </div> 
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>