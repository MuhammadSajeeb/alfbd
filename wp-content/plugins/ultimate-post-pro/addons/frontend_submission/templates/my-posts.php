<?php
/**
 * My Posts page
 *
 */

defined( 'ABSPATH' ) || exit;

$page_url = $this->get_endpoint_url($this->get_current_endpoint());

$current_page = ( isset( $_GET[ 'current_page' ] ) && $_GET[ 'current_page' ] ) ? absint( $_GET[ 'current_page' ] ) : 1;


$search = isset($_GET['ultp_fs_search_posts'])?sanitize_text_field( $_GET['ultp_fs_search_posts']):'';
$current_user = get_current_user_id();

$args = array(
    'author'        =>  get_current_user_id(),
    'orderby'       =>  'post_date',
    'order'         =>  'DESC',
    'posts_per_page' => 10,
    'post_status' => 'any',
    'paged'=>$current_page
    );
if(!empty($search)) {
    $args['s'] = $search;
}
$query = new WP_Query($args);

?>
<script type="text/javascript">



</script>
<div class="ultp-fs-myaccount-posts-wrapper">
    <div class="ultp-fs-myaccount-posts-header ultp-fs-dashboard-heading">
        <h2 class="ultp-fs-myaccount-posts-header__left ultp-fs-dashboard-heading-text"> <?php echo __('My Posts','ultimate-post-pro'); ?></h2>
        <div class="ultp-fs-myaccount-posts-header__right">
            <form method="get"> 
                <div class="ultp-fs-myaccount-posts-search ultp-fs-center">
                    <!-- <select class="ults-fs-mypost-select ultp-title-sm">
                        <option>Showing 1-20</option>
                        <option>Option 1</option>
                        <option>Option 2</option>
                        <option>Option 3</option>
                    </select> -->
                    <!-- <select class="ults-fs-mypost-select ultp-title-sm" name="ultp_fs_post_sort">
                        <option value="latest">Sort by latest</option>
                        <option value="oldest"> Sort by Oldest</option>
                    </select> -->
                    <input type='search' name="ultp_fs_search_posts" id="ultp_fs_search_posts" class="ultp-title-sm ultp-fs-input-field" placeholder="Search..." />
                    <input type='hidden' name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'ultp_fs_create_post' )); ?>" />
                    <button type="submit"  id="ultp_fs_search_post_action_btn" class="ultp-fs-search-btn ultp-fs-btn ultp-title-sm"> <?php echo __('Search','ultimate-post-pro'); ?></button>
                </div>
            </form>
        </div>
    </div>

    

    <div class="ultp-fs-myaccount-posts"> 

    <div class="ultp-fs-my-posts-modal-wrapper" id="ultp-fs-delete-post-modal"><div class="ultp-fs-my-posts-modal" tabindex="-1" aria-hidden="true"><div class="ultp-fs-my-posts-modal__content"><div class="ultp-fs-my-posts-modal__header"><div class="ultp-fs-my-posts-modal__title">Confirm Delete</div><span class="icon-modal-delete dashicons dashicons-no-alt"></span></div><div class="ultp-fs-my-posts-modal__body">Do You Want to the delete this post?  Be careful, this procedure is irreversible. <p style="margin-top:20px; margin-bottom: 0px;"> Still want to proceed? </p></div><div class="ultp-fs-my-posts-modal__footer"><button class="btn-modal-cancel" id="ultp-fs-delete-post-modal-cancel">Cancel</button><a class="btn-modal-delete" href="#" id="ultp-fs-delete-post-link">Delete</a></div></div></div></div>
        <?php 
        while( $query->have_posts() ) : $query->the_post();
        $post = $query->post;

		?>
        <div class="ultp-fs-post-card ultp-fs-center">
            <!-- <?php echo get_the_post_thumbnail_url($post->ID,'thumbnail'); ?> -->
                <div class="ultp-fs-post-media">
                    <?php if(has_post_thumbnail( $post->ID)) {?> 
                        <img src="<?php echo get_the_post_thumbnail_url($post->ID,'thumbnail'); ?>"  class="ultp-fs-post-card-img"/>
                    <?php }  else { ?> 
                        <div class="ultp-fs-post-fallback ultp-fs-center"></div>
                    <?php }  ?> 
                    <div class="ultp-fs-post-overlay ultp-fs-center">
                        <a href="<?php echo get_permalink($post->ID); ?>" target="_blank">VIEW POST</a>
                    </div>
                </div>
                <div class="ultp-fs-post-content">
                    <div class="ultp-fs-post-info">
                        <div class="ultp-fs-post-title ultp-title-sm">  
                            <?php echo $post->post_title; ?> 
                        </div>
                        <div class="ultp-fs-post-meta"> 
                            <span class="ultp-fs-post-status"> 
                                <?php echo ucwords($post->post_status); ?> </span> - <span class="ultp-fs-post-date"><?php echo  date ('F j, Y', strtotime($post->post_date)); ?> 
                            </span>
                        </div>
                    </div>
                    
                    <?php 
                    $show_edit_link = ('publish' === $post->post_status && $this->is_allowed_to_edit_post_after_publish()) || 'publish' !== $post->post_status;
                    $show_delete_link = 'publish' !== $post->post_status && $this->is_allowed_to_delete_post();
                    
                    if($show_delete_link || $show_edit_link ) {
                        ?>
                        <div class="ultp-fs-post-action ultp-fs-center"> 
                            <?php if($show_edit_link) {
                                ?>
                                <a href="<?php echo get_edit_post_link($post->ID); ?>" class="ultp-fs-edit-post ultp-fs-center"> <span class="dashicons dashicons-edit-page ultp-title-sm"></span> <?php echo __('Edit Post','ultimate-post-pro'); ?> </a>
                                <?php
                            } 
                            if($show_delete_link) {
                                ?>
                                <button class="ultp-fs-delete-post-btn ultp-fs-delete-post ultp-fs-center" data-id="<?php echo esc_attr($post->ID); ?>" data-url="<?php echo esc_attr(get_delete_post_link($post->ID)); ?>"> <?php echo __('Delete Post','ultimate-post-pro'); ?> </button>
                                <?php
                            }
                            ?>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
        <?php
        endwhile;
        ?>
    </div>
    
    <div class="ultp-fs-my-posts-pagination"> 
    <?php

    if( $query->max_num_pages > 1 ) {
        echo paginate_links(
            array(
                'total' => $query->max_num_pages,
                'current' => $current_page,
                'base' => $page_url . '%_%',
                'format' => '?' . 'current_page' . '=%#%'
            )
        );
    }
    ?>
    </div>
    <?php
    if( !$query->have_posts()) {
        ?>
        <div class="ultp-fs-no-posts">
            <?php echo esc_html_e('No Posts Available in your account.','ultimate-post-pro'); ?> <a href="<?php echo admin_url('post-new.php'); ?>"> <?php echo esc_html__('Click here','ultimate-post-pro'); ?> </a> <?php echo esc_html__('to submit new posts','ultimate-post-pro'); ?>
        </div>
    <?php 
        
    }
        
    ?>
</div>