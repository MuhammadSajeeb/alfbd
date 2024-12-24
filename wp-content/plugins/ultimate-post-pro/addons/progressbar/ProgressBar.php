<?php
namespace ULTP_PRO;

defined( 'ABSPATH' ) || exit;

/**
 * The ProgressBar Class
 */
class ProgressBar {

    /**
     * Progressbar Construct.
     */
    public function __construct() {
        add_action( 'wp_footer', array( $this, 'progressbar_render' ) );
    }

    /**
	 * Progress Bar Conditions
     * 
     * @since v.1.0.0
     * @param | NULL
	 * @return NULL
	 */
    public function progressbar_render() {
        $def = array(
            'progressbar_height',
            'progressbar_color',
            'progressbar_position',
            'progressbar_allpage',
            'progressbar_homepage',
            'choice_progressbar'
        );
        $settings = ultimate_post()->get_setting();

        foreach ( $def as $key => $val ) {
            if ( ! isset( $settings[$val] ) ) {
                $settings[$val] = '';
            }
        }

        if ( $settings['progressbar_allpage'] === 'yes' ) {
            $this->progressbar_html( $settings );
        } else {
            if ( 'yes' == $settings['progressbar_homepage'] && ( is_home() || is_front_page() ) ) {
                $this->progressbar_html( $settings );
            } else if ( is_singular() ) {
                global $post;
                $progressbar = $settings['choice_progressbar'];
                if ( ! is_array( $progressbar ) && $progressbar ) {
                    $progressbar = [$progressbar];
                }
                if ( ! empty( $progressbar ) && isset( $post->post_type ) ) {
                    if ( in_array( $post->post_type, $progressbar, true ) ) {
                        $this->progressbar_html( $settings );
                    }
                }
            }
        }
    }


    /**
	 * Progress Bar HTML CSS & JS
     * 
     * @since v.1.0.0
     * @param | NULL
	 * @return NULL
	 */
    public function progressbar_html( $settings ) {
        $css = 'position:fixed; width:0%; z-index: 9999; background-color:'.$settings['progressbar_color'].'; height:'.$settings['progressbar_height'].'px;';
        ?>
        <div id="ultp-progressbar" class="ultp-progressbar-<?php echo esc_attr( $settings['progressbar_position'] ); ?>" style="<?php echo esc_attr( $css ); ?>"></div>
        <script type="text/javascript">
            window.onscroll = function () { 
                const el = document.documentElement;
                const scroll = (( (document.body.scrollTop || el.scrollTop) / (el.scrollHeight - el.clientHeight)) * 100);
                document.getElementById('ultp-progressbar').style.width = scroll + "%";
            }
        </script>
        <?php
    }
}
