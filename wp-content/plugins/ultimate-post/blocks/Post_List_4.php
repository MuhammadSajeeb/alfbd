<?php
namespace ULTP\blocks;

defined('ABSPATH') || exit;

class Post_List_4{

    public function __construct() {
        add_action('init', array($this, 'register'));
    }

    public function get_attributes() {

        return array(
            'blockId' =>  '',
            'previewImg' =>  '',
            //--------------------------
            //      Layout
            //--------------------------
            'layout' =>  'layout1',
            
            //--------------------------
            //      Query Setting
            //--------------------------
            'queryQuick' =>  '',
            'queryNumPosts' =>  (object)['lg'=>5],
            'queryNumber' =>  5,
            'queryType' =>  'post',
            'queryTax' =>  'category',
            'queryTaxValue' =>  '[]',
            'queryRelation' =>  'OR',
            'queryOrderBy' =>  'date',
            'metaKey' =>  'custom_meta_key',
            'queryOrder' =>  'desc',
            // Include Remove from Version 2.5.4
            'queryInclude' =>  '',
            'queryExclude' =>  '[]',
            'queryAuthor' =>  '[]',
            'queryOffset' =>  '0',
            'queryExcludeTerm' =>  '[]',
            'queryExcludeAuthor' =>  '[]',
            'querySticky' =>  true,
            'queryUnique' =>  '',
            'queryPosts' =>  '[]',
            'queryCustomPosts' =>  '[]',
            //--------------------------
            //      General Setting
            //--------------------------
            'titleShow' =>  true,
            'titleStyle' => 'none',
            'headingShow' =>  true,
            'excerptShow' =>  true,
            'catShow' =>  true,
            'metaShow' =>  true,
            'showImage' =>  true,
            'filterShow' =>  false,
            'paginationShow' =>  true,
            'readMore' =>  true,
            'contentTag' =>  'div',
            'openInTab' =>  false,
            'notFoundMessage' =>  'No Post Found',

            //--------------------------
            //      Heading Setting/Style
            //--------------------------
            'headingText' =>  'Post List #4',
            'headingURL' =>  '',
            'headingBtnText' =>   'View More',
            'headingStyle' =>  'style1',
            'headingTag' =>  'h2',
            'headingAlign' =>   'left',
            'subHeadingShow' =>  false,
            'subHeadingText' =>  'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer ut sem augue. Sed at felis ut enim dignissim sodales.',
            
            //--------------------------
            //      Title Setting/Style
            //--------------------------
            'titleTag' =>  'h3',
            'titlePosition' =>  true,
            'titleLength' =>  0,
            
            //--------------------------
            // Content Setting/Style
            //--------------------------
            
            'varticalAlign' =>  'middle',
            'showSeoMeta' =>  false,
            'showFullExcerpt' =>  false,
            'showFullExcerptLg' =>  false,
            'excerptLimitLg' =>  40,
            'showSmallExcerpt' => true,
            'excerptLimit' =>  30,
            
            //--------------------------
            // Category Setting/Style
            //--------------------------
            'maxTaxonomy'=>  '30',
            'taxonomy' =>  'category',
            'showSmallCat' =>  false,
            'catStyle' =>  'classic',
            'catPosition' =>  'aboveTitle',
            'customCatColor' =>  false,
            'seperatorLink' =>  admin_url( 'edit-tags.php?taxonomy=category' ),
            'onlyCatColor' =>  false,
            
            //--------------------------
            // Meta Setting/Style
            //--------------------------
            'showSmallMeta' =>  true,
            'metaPosition' =>  'top',
            'metaStyle' =>  'icon',
            'authorLink' =>  true,
            'metaSeparator' =>  'dot',
            'metaList' =>  '["metaAuthor","metaDate","metaRead"]',
            'metaMinText' =>  'min read',
            'metaAuthorPrefix' =>  'By',
            'metaDateFormat' =>  'M j, Y',
            'metaListSmall' =>  '["metaAuthor","metaDate","metaRead"]',
            
            //--------------------------
            // Image Setting/Style
            //--------------------------
            'mobileImageTop' =>  false,
            'imgFlip' =>  false,
            'imgCrop' =>  'full',
            'imgCropSmall' =>  (ultimate_post()->get_setting('disable_image_size') == 'yes' ? 'full' : 'ultp_layout_square'),
            'imgAnimation' =>  'opacity',
            'imgOverlay' => false,
            'imgOverlayType' =>  'default',
            'fallbackEnable' =>  true,
            'fallbackImg' =>  '',
            'imgSrcset' =>  false,
            'imgLazy' =>  false,

            //--------------------------
            // Video Setting/Style
            //--------------------------
            'vidIconEnable' =>  true,
            'popupAutoPlay' =>  true,
            'iconSize' =>  (object)['lg'=>'40', 'sm'=> '30', 'xs'=> '30', 'unit' => 'px'],
            // by default should be off
            'enablePopup' =>  false,
            'enablePopupTitle' =>  true,
            
            //--------------------------
            // Separator
            //--------------------------
            'separatorShow' =>  true,
            
            //--------------------------
            // Read more Setting/Style
            //--------------------------
            'showSmallBtn' =>  false,
            'readMoreText' =>  '',
            'readMoreIcon' =>  '',
            
            //--------------------------
            // Filter Setting/Style
            //--------------------------
            'filterBelowTitle' =>  false,
            'filterType' =>  'category',
            'filterText' =>  'all',
            'filterValue' =>  '[]',
            'filterMobile' =>   true,
            'filterMobileText' =>  'More',

            //--------------------------
            // Pagination Setting/Style
            //--------------------------
            'paginationType' =>  'pagination',
            'loadMoreText' =>  'Load More',
            'paginationText' =>  'Previous|Next',
            'paginationNav' =>  'textArrow',
            'paginationAjax' =>  true,
            'navPosition' =>  'topRight',
            
            //--------------------------
            //  Wrapper Style
            //--------------------------
            'advanceId' =>  '',
            'advanceZindex' =>  '',
            'hideExtraLarge' =>  false,
            'hideTablet' =>  false,
            'hideMobile' =>  false,
            'advanceCss' =>  '',
            'currentPostId' =>  '',

                        // --------------------------------
            // Advance Filter Block Compatibility
            // --------------------------------
            'advFilterEnable' => false,
            'querySearch' =>  '',

            // --------------------------------
            // Pagination block compatibility
            // --------------------------------
            'advPaginationEnable' => false,
            'defQueryTax' => array(),
            'advRelation' => 'AND',
            // 'paginationAjax' => true,
            // 'paginationText' =>  'Previous|Next',
            // 'loadMoreText' =>  'Load More',
            // 'queryNumPosts' =>  (object)['lg'=>5],
            // 'queryNumber2' => 6,
            // 'notFirstLoad' => false,

            /*============================
                Dynamic Content
            ============================*/
            'dcEnabled' => false,
            'dcFields' => array(),
            'dcSize' => 8,
        );
    }

    public function register() {
        register_block_type( 'ultimate-post/post-list-4',
            array(
                'editor_script' => 'ultp-blocks-editor-script',
                'editor_style'  => 'ultp-blocks-editor-css',
                'render_callback' =>  array($this, 'content')
            )
        );
    }

    public function content($attr, $noAjax) {
        $attr = wp_parse_args($attr, $this->get_attributes());
        global $unique_ID;

        if (!$noAjax) {
            $paged = is_front_page() ? get_query_var('page') : get_query_var('paged');
            $attr['paged'] = $paged ? $paged : 1;
        }
        if($attr['queryUnique'] && isset($attr['savedQueryUnique'])) {
            $unique_ID = $attr['savedQueryUnique'];
        }

        $block_name = 'post-list-4';
        $wraper_before = $wraper_after = $post_loop = '';
        $attr['queryNumber'] = ultimate_post()->get_post_number(5, $attr['queryNumber'], $attr['queryNumPosts']);
        $recent_posts = new \WP_Query( ultimate_post()->get_query( $attr ) );
        $pageNum = ultimate_post()->get_page_number($attr, $recent_posts->found_posts);
        // Dummy Img Url
        $dummy_url = ULTP_URL.'assets/img/ultp-fallback-img.png';

        // Loadmore and Unique content 
        if($attr['queryUnique'] && isset($attr['loadMoreQueryUnique']) && $attr['paginationShow'] && ($attr['paginationType'] == 'loadMore')) {
            $unique_ID = $attr['loadMoreQueryUnique'];
            $current_unique_posts = $attr['ultp_current_unique_posts'];
        }

        $attr['className'] = isset($attr['className']) && $attr['className'] ? preg_replace('/[^A-Za-z0-9_ -]/', '', $attr['className']) : '';
        $attr['align'] = isset($attr['align']) && $attr['align'] ? preg_replace('/[^A-Za-z0-9_ -]/', '', $attr['align']) : '';
        $attr['advanceId'] = isset($attr['advanceId']) ? sanitize_html_class( $attr['advanceId'] ) : '';
        $attr['blockId'] = isset($attr['blockId']) ? sanitize_html_class( $attr['blockId'] ) : '';
        $attr['contentTag'] = in_array( $attr['contentTag'],  ultimate_post()->ultp_allowed_block_tags() ) ? $attr['contentTag'] : 'div';
        $attr['layout'] = sanitize_html_class( $attr['layout'] );
        $attr['imgAnimation'] = sanitize_html_class( $attr['imgAnimation'] );
        $attr['imgOverlayType'] = sanitize_html_class( $attr['imgOverlayType'] );
        $attr['popupAutoPlay'] =  $attr['popupAutoPlay'] == true ;
        $attr['readMoreText'] = wp_kses($attr['readMoreText'], ultimate_post()->ultp_allowed_html_tags());
        $attr['varticalAlign'] = sanitize_html_class( $attr['varticalAlign'] );

        if ($recent_posts->have_posts()) {

            // Pagination Block Html
            include ULTP_PATH . 'blocks/template/pagination_block.php';
            
            $wraper_before .= '<div '.( $attr['advanceId']?'id="'.$attr['advanceId'].'" ':'' ).' class="ultp-post-grid-block wp-block-ultimate-post-'.$block_name.' ultp-block-'.$attr["blockId"].''.( $attr["align"] ? ' align' .$attr["align"]:'' ).''.( $attr["className"] ?' '.$attr["className"]:'' ) . '">';
                $wraper_before .= '<div class="ultp-block-wrapper">';

                    // Loading
                    $wraper_before .= ultimate_post()->postx_loading();

                    if ($attr['headingShow'] || $attr['filterShow'] || $attr['paginationShow']) {
                        $wraper_before .= '<div class="ultp-heading-filter">';
                            $wraper_before .= '<div class="ultp-heading-filter-in">';
                                
                                // Heading
                                include ULTP_PATH.'blocks/template/heading.php';
                                
                                if ($attr['filterShow'] || $attr['paginationShow']) {
                                    $wraper_before .= '<div class="ultp-filter-navigation">';

                                        // Filter
                                        if($attr['filterShow'] && $attr['queryType'] != 'posts' && $attr['queryType'] != 'customPosts') {
                                            include ULTP_PATH.'blocks/template/filter.php';
                                        }

                                        // Navigation
                                        if ($attr['paginationShow'] && ($attr['paginationType'] == 'navigation') && ($attr['navPosition'] == 'topRight')) {
                                            include ULTP_PATH.'blocks/template/navigation-before.php';
                                        }
                                    $wraper_before .= '</div>';
                                }

                            $wraper_before .= '</div>';
                        $wraper_before .= '</div>';
                    }
             
                    $wraper_before .= '<div class="ultp-block-items-wrap ultp-block-content-'.$attr['varticalAlign'].($attr['imgFlip'] ? ' ultp-block-content-true' : '').' ultp-'.$attr['layout'].'">';
                        $idx = 0; // $idx = $noAjax ? 1 : 0;
                        while ( $recent_posts->have_posts() ): $recent_posts->the_post();

                            $dcContent = array_fill( 0, $attr['dcSize'], '' );

                            if (ultimate_post()->is_dc_active($attr)) {
                                $dcContent = \ULTP\DCService::get_dc_content_for_block($attr, $dcContent);
                            }
                            
                            include ULTP_PATH.'blocks/template/data.php';

                            include ULTP_PATH.'blocks/template/category.php';

                            if ($attr['queryUnique']) {
                                $unique_ID[$attr['queryUnique']][] = $post_id;
                                $current_unique_posts[] = $post_id;
                            }
                            
                            $post_loop .= '<'.$attr['contentTag'].' class="ultp-block-item ultp-block-media post-id-'.$post_id.'">';
                                $post_loop .= '<div class="ultp-block-content-wrap'.((($idx===0 && $noAjax==false) || ($idx==0 && $noAjax && $attr['paginationType'] != 'loadMore')) ? ' ultp-first-postlist-2' : ' ultp-all-postlist-2').'">';
                                    if(($post_thumb_id || $attr['fallbackEnable']) && $attr['showImage']) {
                                        if($idx == 0 && $noAjax==false){
                                            $post_loop .= '<div class="ultp-block-image ultp-block-image-'.$attr['imgAnimation'].($attr["imgOverlay"] ? ' ultp-block-image-overlay ultp-block-image-'.$attr["imgOverlayType"].' ultp-block-image-'.$attr["imgOverlayType"].$idx : '' ).'">';
                                                $post_loop .= '<a href="'.$titlelink.'" '.($attr['openInTab'] ? 'target="_blank"' : '').'>';
                                                // Post Image Id
                                                $block_img_id = $post_thumb_id ? $post_thumb_id : ($attr['fallbackEnable'] && isset($attr['fallbackImg']['id']) ? $attr['fallbackImg']['id'] : '');
                                                // Post Image 
                                                if($post_thumb_id || ($attr['fallbackEnable'] && $block_img_id)) {
                                                    $post_loop .= ultimate_post()->get_image($block_img_id, $attr['imgCrop'], '', $title, $attr['imgSrcset'], $attr['imgLazy']);
                                                } else {
                                                    $video = ultimate_post()->get_youtube_id($post_video);
                                                    $post_loop .= '<img  src="'.($video ? 'https://img.youtube.com/vi/'.$video.'/0.jpg' : $dummy_url).'" alt="dummy-img" />';
                                                }
                                                $post_loop .= '</a>';
                                                if( ($attr['catPosition'] != 'aboveTitle') && ($noAjax==false ) && $attr['catShow'] ) {
                                                    $post_loop .= '<div class="ultp-category-img-grid">'.$category.'</div>';
                                                }
                                            $post_loop .= '</div>';
                                            if($post_video){
                                                $post_loop .= '<div enableAutoPlay="'.$attr['popupAutoPlay'].'" class="ultp-video-icon">'.ultimate_post()->get_svg_icon('play_line').'</div>';
                                            }
                                        } else if(($attr['layout'] === 'layout1') || ($attr['layout'] === 'layout4')) {
                                            $post_loop .= '<div class="ultp-block-image ultp-block-image-'.$attr['imgAnimation'].($attr["imgOverlay"] ? ' ultp-block-image-overlay ultp-block-image-'.$attr["imgOverlayType"].' ultp-block-image-'.$attr["imgOverlayType"].$idx : '' ).'">';
                                                $post_loop .= '<a href="'.$titlelink.'" '.($attr['openInTab'] ? 'target="_blank"' : '').'>'; 
                                                // Image
                                                if($post_thumb_id) {
                                                    $post_loop .= ultimate_post()->get_image($post_thumb_id, $attr['imgCropSmall'], '', $title, $attr['imgSrcset'], $attr['imgLazy']); 
                                                } elseif($attr['fallbackEnable']) {
                                                    if(isset($attr['fallbackImg']['id'])){
                                                        // User Define Fallback Image
                                                        $post_loop .= ultimate_post()->get_image($attr['fallbackImg']['id'], $attr['imgCropSmall'], '', $title, $attr['imgSrcset'], $attr['imgLazy']);
                                                    } else {
                                                        // Default Fallback Image
                                                        $video = ultimate_post()->get_youtube_id($post_video);
                                                        $post_loop .= '<img  src="'.($video ? 'https://img.youtube.com/vi/'.$video.'/0.jpg' : $dummy_url).'" alt="dummy-img" />';
                                                    }
                                                }
                                                $post_loop .= '</a>'; 
                                                if($post_video){
                                                    $post_loop .= '<div enableAutoPlay="'.$attr['popupAutoPlay'].'" class="ultp-video-icon">'.ultimate_post()->get_svg_icon('play_line').'</div>';
                                                }
                                                if( ($attr['catPosition'] != 'aboveTitle') && (($idx == 0 && $noAjax==false) || $attr['showSmallCat']) && $attr['catShow'] ) {
                                                    $post_loop .= '<div class="ultp-category-img-grid">'.$category.'</div>';
                                                }
                                            $post_loop .= '</div>';
                                        }
                                    }
                                    $post_loop .= '<div class="ultp-block-content">';

                                        $post_loop .= $dcContent[7];
                                        
                                        // Category
                                        if(($attr['catPosition'] == 'aboveTitle') && (($idx == 0 && $noAjax==false) || $attr['showSmallCat'] ) && $attr['catShow']) {
                                            $post_loop .= $category;
                                        }

                                        $post_loop .= $dcContent[6];

                                        // Title
                                        if ($title && $attr['titleShow'] && $attr['titlePosition'] == 1) {
                                            include ULTP_PATH.'blocks/template/title.php';
                                        }
                                        
                                        $post_loop .= $dcContent[5];

                                        // Meta Top
                                        if( $attr['metaShow'] && (($idx == 0 && $noAjax==false) || $attr['showSmallMeta']) && $attr['metaPosition'] =='top' ) {
                                            include ULTP_PATH.'blocks/template/meta.php';
                                        }

                                        $post_loop .= $dcContent[4];
                                        
                                        // Title
                                        if ($title && $attr['titleShow'] && $attr['titlePosition'] == 0) {
                                            include ULTP_PATH.'blocks/template/title.php';
                                        }

                                        $post_loop .= $dcContent[3];

                                        // Excerpt
                                        if ((($idx == 0 && $noAjax==false) || $attr['showSmallExcerpt']) && $attr['excerptShow']) {
                                            $showFullexcerpt = $idx == 0  ? $attr['showFullExcerptLg'] : $attr['showFullExcerpt'];

                                            $excptLimit = $idx == 0 ? $attr['excerptLimitLg'] : $attr['excerptLimit'];
                                            $post_loop .= '<div class="ultp-block-excerpt">'.ultimate_post()->get_excerpt($post_id, $attr['showSeoMeta'], $showFullexcerpt, $excptLimit).'</div>';
                                        }

                                        $post_loop .= $dcContent[2];

                                        // Read More
                                        if ($attr['readMore'] && (($idx == 0 && $noAjax==false) || $attr['showSmallBtn'])) {
                                            $post_loop .= '<div class="ultp-block-readmore"><a aria-label="'.$title.'" href="'.$titlelink.'" '.($attr['openInTab'] ? 'target="_blank"' : '').'>'.($attr['readMoreText'] ? $attr['readMoreText'] : esc_html__( "Read More", "ultimate-post" )).ultimate_post()->get_svg_icon($attr['readMoreIcon']).'</a></div>';
                                        }

                                        $post_loop .= $dcContent[1];

                                        // Meta Bottom
                                        if( $attr['metaShow'] && (($idx == 0 && $noAjax==false) || $attr['showSmallMeta']) && $attr['metaPosition'] =='bottom' ) {
                                            include ULTP_PATH.'blocks/template/meta.php';
                                        }

                                        $post_loop .= $dcContent[0];

                                    $post_loop .= '</div>';
                                $post_loop .= '</div>';
                                if($post_video && $attr['enablePopup'] && ($attr['layout'] !== 'layout2' && $attr['layout'] !== 'layout3' || $idx == 0)) {
                                    include ULTP_PATH.'blocks/template/video_popup.php';
                                }
                            $post_loop .= '</'.$attr['contentTag'].'>';
                            $idx ++;
                        endwhile;
                        if($attr['queryUnique']) {
                            $post_loop .= "<span style='display: none;' class='ultp-current-unique-posts' data-ultp-unique-ids= ".wp_json_encode($unique_ID)." data-current-unique-posts= ".wp_json_encode($current_unique_posts)."> </span>";
                        }
                        // if ( ( $attr['paginationShow'] || $attr['advPaginationEnable'] ) && ($attr['paginationType'] == 'loadMore')) {
                        //     $wraper_after .= '<span class="ultp-loadmore-insert-before"></span>';
                        // }
                        
                    $wraper_after .= '</div>';//ultp-block-items-wrap
                    
                    // Load More
                    if ($attr['paginationShow'] && ($attr['paginationType'] == 'loadMore')) {
                        include ULTP_PATH.'blocks/template/loadmore.php';
                    }

                    // Navigation
                    if ($attr['paginationShow'] && ($attr['paginationType'] == 'navigation') && ($attr['navPosition'] != 'topRight')) {
                        include ULTP_PATH.'blocks/template/navigation-after.php';
                    }

                    // Pagination
                    if ($attr['paginationShow'] && ($attr['paginationType'] == 'pagination')) {
                        include ULTP_PATH.'blocks/template/pagination.php';
                    }

                $wraper_after .= '</div>';
                $wraper_after .= $pagi_block_html;
            $wraper_after .= '</div>';

            wp_reset_query();
        }else {
            $wraper_before .= ultimate_post()->get_no_result_found_html( $attr['notFoundMessage'] );
        }

        return $noAjax ? $post_loop : $wraper_before.$post_loop.$wraper_after;
    }

}