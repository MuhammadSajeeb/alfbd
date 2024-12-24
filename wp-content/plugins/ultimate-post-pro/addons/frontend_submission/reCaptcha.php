<?php
namespace ULTP_PRO;

defined('ABSPATH') || exit;

class reCaptcha {


    public function __construct() {
        add_action('plugins_loaded', array($this,'run_recaptcha'));
    }

    public function run_recaptcha() {
        $recaptcha_type = ultimate_post_pro()->get_setting('ultp_fs_recaptcha_type');
        if('v2' === $recaptcha_type) {
            // Loaded V2
            add_action('ultp_frontend_submission_before_registration_form_render',array(__CLASS__,'add_recaptcha_v2_script'));
            add_action('ultp_frontend_submission_registration_form',array($this,'add_recaptcha_checkbox'));
            add_action('ultp_frontend_submission_before_new_user_register', array($this,'process_recaptcha_v2'));

            add_action('ultp_frontend_submission_before_login_form_render',array(__CLASS__,'add_recaptcha_v2_script'));
            add_action('ultp_frontend_submission_login_form',array($this,'add_recaptcha_checkbox'));
            add_action('ultp_frontend_submission_before_login', array($this,'process_recaptcha_v2'));

        } elseif('v3' === $recaptcha_type) {
            add_action('ultp_frontend_submission_before_registration_form_render',array(__CLASS__,'add_recaptcha_v3_script'));
            add_action('ultp_frontend_submission_before_new_user_register', array($this,'process_recaptcha_v3'));

            add_action('ultp_frontend_submission_before_login_form_render',array(__CLASS__,'add_recaptcha_v3_script'));
            add_action('ultp_frontend_submission_before_login', array($this,'process_recaptcha_v3'));
        
        }
    }

    public function add_recaptcha_checkbox() {
        $site_key = ultimate_post_pro()->get_setting( 'ultp_fs_recaptcha_site_key' );

        ?>
        <script>
        jQuery(document).ready(function() { 
            let submitButton = jQuery("form.ultp-fs-recaptcha-form button[type=submit]");
            submitButton.attr('disabled',true);
        });
        function ultpRecaptchaSubmissionEnable(){
            let submitButton = jQuery("form.ultp-fs-recaptcha-form button[type=submit]");
            submitButton.attr('disabled',false);
        }
        function ultpRecaptchaSubmissionDisable(){
            let submitButton = jQuery("form.ultp-fs-recaptcha-form button[type=submit]");
            submitButton.attr('disabled',true);
        }
        </script>
        <p>
        <div class="g-recaptcha" data-sitekey="<?php echo $site_key; ?>" data-callback="ultpRecaptchaSubmissionEnable" data-expired-callback="ultpRecaptchaSubmissionDisable" data-error-callback="ultpRecaptchaSubmissionDisable" ></div>
        
        </p>
        <?php
    }

    public function process_recaptcha_v3($data) {
        if(isset($data['token']) && !empty($data['token'])) {
            $parsed_response = $this->parse_recaptcha_response( $data['token'] );
            if ( ! ( isset( $parsed_response['success'] ) && $parsed_response['success'] && $parsed_response['score'] >= 0.5 ) ) {
                throw new \Exception( __( 'reCaptcha V3: '.$this->recaptcha_error_message( $parsed_response['error-codes'][0] ), 'ultimate-post-pro' ) );
            }
        }
    }
    
    public function process_recaptcha_v2($data) {        
        if(isset($data['g-recaptcha-response']) && !empty($data['g-recaptcha-response'])) {
            $parsed_response = $this->parse_recaptcha_response( $data['g-recaptcha-response'] );
            if ( ! ( isset( $parsed_response['success'] ) && $parsed_response['success']) ) {
                throw new \Exception( __( 'reCaptcha V2: '.$this->recaptcha_error_message( $parsed_response['error-codes'][0] ), 'ultimate-post-pro' ) );
            }
        }
    }


    /**
	 * Recaptcha Error Code
	 *
	 * @param string $code recaptcha error code.
	 * @return string Error Message.
	 * @since 1.0.0
	 */
	private function recaptcha_error_message( $code ) {
		switch ( $code ) {
			case 'missing-input-secret':
				return __( 'The secret parameter is missing.', 'ultimate-post-pro' );
			case 'invalid-input-secret':
				return __( 'The secret parameter is invalid or malformed.', 'ultimate-post-pro' );
			case 'missing-input-response':
				return __( 'The response parameter is missing.', 'ultimate-post-pro' );
			case 'invalid-input-response':
				return __( 'The response parameter is invalid or malformed.', 'ultimate-post-pro' );
			case 'bad-request':
				return __( 'The request is invalid or malformed.', 'ultimate-post-pro' );
			case 'timeout-or-duplicate':
				return __( 'The response is no longer valid: either is too old or has been used previously.', 'ultimate-post-pro' );
			default:
				return __( 'Unknown!', 'ultimate-post-pro' );
		}
	}

    /**
	 * Parse recaptcha Response
	 *
	 * @param string $response reCaptcha response token.
	 * @return array
	 */
	private function parse_recaptcha_response( $response='') {
		$secret_key           = ultimate_post_pro()->get_setting( 'ultp_fs_recaptcha_secret_key' );
		$recaptcha_verify_api = sprintf( 'https://www.google.com/recaptcha/api/siteverify?secret=%s&response=%s', $secret_key, $response );
		$response             = (array) wp_remote_get( $recaptcha_verify_api );
		$error                = array(
			'success'     => false,
			'error-codes' => array( 'unknown' ),
		);

		return isset( $response['body'] ) ? json_decode( $response['body'], true ) : $error;
	}

    /**
	 * Add Recaptcha Script in user registration form
	 *
	 */
	public static function add_recaptcha_v3_script() {
		$site_key = ultimate_post_pro()->get_setting( 'ultp_fs_recaptcha_site_key' );
		wp_enqueue_script( 'ultp_fs_recaptcha_v3', 'https://www.google.com/recaptcha/api.js?render=' . $site_key, array(), ULTP_VER, false );
        $init_script = <<<JS
                            (function($) {
                                    $(document).ready(function() {
                                        $("form.ultp-fs-recaptcha-form ").submit(function(e) {
                                            e.preventDefault();
                                            let curState = this;
                                            if(typeof grecaptcha !== 'undefined') {
                                                grecaptcha.ready(function() {
                                                try {
                                                    grecaptcha
                                                    .execute('%s', {
                                                        action: "submit",
                                                    })
                                                    .then(function(token) {
                                                        $("<input>")
                                                            .attr({
                                                                name: "token",
                                                                id: "token",
                                                                type: "hidden",
                                                                value: token,
                                                            })
                                                            .appendTo("form");
                                                        curState.submit();
                                                    });
                                                } catch (error) {

                                                }
                                            });
                                            }
                                        });
                                    });
                            })(jQuery);
                            JS;
        $script = sprintf(
            $init_script,
            $site_key
        );
        wp_add_inline_script( 'ultp_fs_recaptcha_v3',$script  );
	}

    public static function add_recaptcha_v2_script() {
        $site_key = ultimate_post_pro()->get_setting( 'ultp_fs_recaptcha_site_key' );
		wp_enqueue_script( 'ultp_fs_recaptcha_v2', 'https://www.google.com/recaptcha/api.js', array(), ULTP_VER, true );
        $init_script = <<<JS
                            (function($) {
                                    $(document).ready(function() {
                                        if(typeof grecaptcha !== 'undefined') {
                                            grecaptcha.ready(()=>{
                                                // Prevent form submission if the reCAPTCHA response is empty.
                                                $('form.ultp-fs-recaptcha-form').submit(function(event) {
                                                    if (!grecaptcha.getResponse()) {
                                                        console.log("Recaptcha Failed!");
                                                        event.preventDefault();
                                                    }
                                                });

                                            });
                                        }
                                    });
                            })(jQuery);
                            JS;
        $script = sprintf(
            $init_script,
            $site_key
        );
        wp_add_inline_script( 'ultp_fs_recaptcha_v2',$script  );
    }

}