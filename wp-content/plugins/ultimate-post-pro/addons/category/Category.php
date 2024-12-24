<?php
namespace ULTP_PRO;

defined( 'ABSPATH' ) || exit;

/**
 * The Category Class
 */
class Category {
      
    /**
     * Category Construct.
     */
    public function __construct() {
        $taxonomy = ultimate_post()->get_setting('taxonomy_list');
        $taxonomy = $taxonomy ? $taxonomy : ['category'];
        if ( ! is_array( $taxonomy ) && $taxonomy ) {
            $taxonomy = [$taxonomy];
        }
        if ( $taxonomy ) {
            foreach ( $taxonomy as $val ) {
                if ( isset( $_GET['taxonomy'] ) ) {
                    add_action($val.'_add_form_fields', array( $this, 'add_taxonomy_image' ), 10, 2);
                    add_action($val.'_edit_form_fields', array( $this, 'taxonomy_image_html' ), 10, 2);
                }
                add_action('created_'.$val, array( $this, 'save_taxonomy_image' ), 10, 2);
                add_action('edited_'.$val, array( $this, 'updated_taxonomy_image' ), 10, 2);
            }
        }
        if ( isset( $_GET['taxonomy'] ) ) {
            add_action('admin_enqueue_scripts', array($this, 'load_media'));
            add_action('admin_footer', array($this, 'add_script'));
        }   
    }
  
    /**
	 * Load Media File
     * 
     * @since v.1.0.0
     * @param | NULL
	 * @return NULL
	 */
    public function load_media() {
        if ( ! isset( $_GET['taxonomy'] ) ) {
            return;
        }
        wp_enqueue_media();
        wp_enqueue_script( 'wp-color-picker' ); // Colorpicker Scripts
        wp_enqueue_style( 'wp-color-picker' ); // Colorpicker Styles   
    }

    /**
	 * Random Preset Color
     * 
     * @since v.1.0.0
     * @param | NULL
	 * @return NULL
	 */
    public function random_color() {
        $arr = array( '#D62E2E', '#17B4D4', '#CE2746', '#E16D07' );
        return $arr[ rand( 0, count($arr) - 1 ) ];
    }

    /**
	 * Add a form field in the new Category Page
     * 
     * @since v.1.0.0
     * @param | NULL
	 * @return NULL
	 */
    public function add_taxonomy_image( $taxonomy ) { ?>
        <div class="form-field term-group">
            <label for="ultp-category-image"><?php echo esc_html__( 'Image', 'ultimate-post-pro' ); ?></label>
            <input type="hidden" id="ultp-category-image" name="ultp_category_image" class="custom_media_url" value="">
            <div id="category-image-wrapper"></div>
            <p>
                <input type="button" class="button button-secondary ultp_media_button" id="ultp_media_button" name="ultp_media_button" value="<?php echo esc_html__( 'Add Image', 'ultimate-post-pro' ); ?>" />
                <input type="button" class="button button-secondary ultp_media_remove" id="ultp_media_remove" name="ultp_media_remove" value="<?php echo esc_html__( 'Remove Image', 'ultimate-post-pro' ); ?>" />
            </p>
            <?php $color = $this->random_color(); ?>
            <div class="form-field term-colorpicker-wrap">
                <label for="category-color"><?php echo esc_html__( 'Color', 'ultimate-post-pro' ); ?></label>
                <input name="ultp_category_color" value="<?php echo esc_attr($color); ?>" class="colorpicker" id="category-color" />
            </div>
        </div>
    <?php }

    /**
	 * Save Category Image
     * 
     * @since v.1.0.0
     * @param | NULL
	 * @return NULL
	 */
    public function save_taxonomy_image( $term_id, $tt_id ) {
        //phpcs:disable WordPress.Security.NonceVerification.Missing
        if ( isset( $_POST['ultp_category_image'] ) && '' !== sanitize_text_field($_POST['ultp_category_image']) ){
            add_term_meta( $term_id, 'ultp_category_image', absint( sanitize_text_field($_POST['ultp_category_image']) ), true );
        }
        if ( isset( $_POST['ultp_category_color'] ) && '' !== sanitize_text_field($_POST['ultp_category_color']) ){
            add_term_meta( $term_id, 'ultp_category_color', sanitize_text_field($_POST['ultp_category_color']), true );
        }
    }
  
    /**
	 * Edit the form field
     * 
     * @since v.1.0.0
     * @param | NULL
	 * @return NULL
	 */
    public function taxonomy_image_html( $term, $taxonomy ) {
        $image = get_term_meta( $term->term_id, 'ultp_category_image', true ); 
        $color = get_term_meta( $term->term_id, 'ultp_category_color', true ); 
        ?>
        <tr class="form-field term-group-wrap">
            <th scope="row">
                <label for="ultp-category-image"><?php echo esc_html__( 'Image', 'ultimate-post-pro' ); ?></label>
            </th>
            <td>
                <input type="hidden" id="ultp-category-image" name="ultp_category_image" value="<?php echo esc_attr($image); ?>">
                <div id="category-image-wrapper">
                    <?php if ( $image ) {
                        echo wp_get_attachment_image( $image, 'thumbnail' );
                    } ?>
                </div>
                <p>
                    <input type="button" class="button button-secondary ultp_media_button" id="ultp_media_button" name="ultp_media_button" value="<?php echo esc_html__( 'Add Image', 'ultimate-post-pro' ); ?>" />
                    <input type="button" class="button button-secondary ultp_media_remove" id="ultp_media_remove" name="ultp_media_remove" value="<?php echo esc_html__( 'Remove Image', 'ultimate-post-pro' ); ?>" />
                </p>
            </td>
        </tr>
        <tr class="form-field term-group-wrap">
            <th scope="row">
                <label for="category-color"><?php echo esc_html__( 'Color', 'ultimate-post-pro' ); ?></label>
            </th>
            <td>
                <input name="ultp_category_color" value="<?php echo esc_attr($color); ?>" class="colorpicker" id="category-color" />
            </td>
        </tr>
    <?php }
  
    /**
	 * Update the form field value
     * 
     * @since v.1.0.0
     * @param | NULL
	 * @return NULL
	 */
    public function updated_taxonomy_image( $term_id, $tt_id ) {
        $image = get_term_meta( $term_id, 'ultp_category_image', true );
        $color = get_term_meta( $term_id, 'ultp_category_color', true );

        if( isset($_POST['ultp_category_image']) ){
            update_term_meta( $term_id, 'ultp_category_image', absint( sanitize_text_field($_POST['ultp_category_image']) ) );
        }
        if( isset( $_POST['ultp_category_color'] ) ){
            update_term_meta( $term_id, 'ultp_category_color', sanitize_text_field($_POST['ultp_category_color']) );
        }
    }
   

    /**
	 * Enqueue styles and scripts
     * 
     * @since v.1.0.0
     * @param | NULL
	 * @return NULL
	 */
    public function add_script() {
        if ( ! isset( $_GET['taxonomy'] ) ) {
            return;
        } ?>
        <script> 
            jQuery(document).ready( function($) {
                _wpMediaViewsL10n.insertIntoPost = '<?php echo esc_html__( "Insert", "ultimate-post-pro" ); ?>';
                function ultp_media_upload(button_class) {
                    var _custom_media = true, _orig_send_attachment = wp.media.editor.send.attachment;
                    $('body').on( 'click', button_class, function(e) {
                        var button_id = '#'+$(this).attr('id');
                        var send_attachment_bkp = wp.media.editor.send.attachment;
                        var button = $(button_id);
                        _custom_media = true;
                        wp.media.editor.send.attachment = function(props, attachment) {
                            if ( _custom_media ) {
                                $('#ultp-category-image').val(attachment.id);
                                $('#category-image-wrapper').html('<img class="custom_media_image" src="" style="margin:0;padding:0;max-height:100px;float:none;" />');
                                $('#category-image-wrapper .custom_media_image').attr('src',attachment.url).css('display','block');
                            } else {
                                return _orig_send_attachment.apply( button_id, [props, attachment] );
                            }
                        }
                        wp.media.editor.open(button); return false;
                    });
                }
                ultp_media_upload('.ultp_media_button.button');
                $('body').on('click','.ultp_media_remove',function(){
                    $('#ultp-category-image').val('');
                    $('#category-image-wrapper').html('<img class="custom_media_image" src="" style="margin:0;padding:0;max-height:100px;float:none;" />');
                });
                // Color Picker Add
                $( '.colorpicker' ).wpColorPicker();
            });
        </script>
    <?php }
}