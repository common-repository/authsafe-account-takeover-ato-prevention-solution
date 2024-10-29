<?php

/**
 * The public-facingfunctionality of the AuthSafe plugin.
 *
 * @link       https://authsafe.ai
 * @since      2.0.0
 *
 * @package    Authsafe
 * @subpackage Authsafe/public
 */

/**
 * The public-facingfunctionality of the AuthSafe plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Authsafe
 * @subpackage Authsafe/public
 * @author     Jinendra Khobare <jinendra@securelayer7.net>
 */
class Authsafe_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      string    $authsafe    The ID of this plugin.
	 */
	private $authsafe;

	/**
	 * The version of this plugin.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    2.0.0
	 * @param      string    $authsafe       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $authsafe, $version ) {

		$this->authsafe = $authsafe;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since   2.0.0
	 */
	public function enqueue_styles() {

		/**
		 * Include public-facing CSS files.
		 * 
		 */

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    2.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * Include public-facing Javascript files.
		 * 
		 */

		wp_enqueue_script( $this->authsafe.'-public', plugin_dir_url( __FILE__ ) . 'js/authsafe-public.js', array(), "1.0.9", true );
		
		extract($this->ats_settings_options());

		if ( (isset($ats_property_id) && !empty($ats_property_id)) &&
			 (isset($ats_property_secret) && !empty($ats_property_secret))
		) {
			wp_enqueue_script( $this->authsafe, PIXEL_URL.$ats_property_id, array(), '2.0.0', false );
			if(is_user_logged_in()) {
				wp_add_inline_script( $this->authsafe, '_authsafe("userInit", "'.esc_attr(get_current_user_id()).'");', 'after' );
			}
		}

	}

	public function enqueue_scripts_login()
	{
		extract($this->ats_settings_options());

		wp_enqueue_script( $this->authsafe, PIXEL_URL.$ats_property_id, array(), '2.0.0', false );

		wp_enqueue_script( $this->authsafe.'-login', plugin_dir_url( __FILE__ ) . 'js/authsafe-login.js', array(), "1.0.22", true );
	}

 
	/**
	 * The function "enqueue_scripts_transaction_attempt" is used to enqueue scripts for a transaction
	 * attempt if the current page is the cart page.
	 */

	public function enqueue_scripts_transaction_attempt()
	{
		if (is_cart()) 
		{
			extract($this->ats_settings_options());

			wp_enqueue_script( $this->authsafe, PIXEL_URL.$ats_property_id, array(), '2.0.0', false );

			wp_enqueue_script( $this->authsafe.'-cart', plugin_dir_url( __FILE__ ) . 'js/authsafe-cart.js', array(), "1.0.22", true );
	
		}
	}

	function custom_cart_collaterals() {
		echo '<form id="myForm" method="POST" action="">';
		echo '<div class="custom-input-wrapper">';
		echo '<input type="hidden" name="custom" id="custom"  />';
		echo '<button type="button" id="submitButton" style="display: none;" >Submit Form</button>';
		echo '</div>';
		echo '<form>';
        ?>
			<script type="text/javascript">

				function autoClickButton() {
					document.getElementById('submitButton').click();
					var ss = document.getElementById('custom').value;
					function setCookie(cookieName, cookieValue, daysToExpire) {
						let expires = '';
						if (daysToExpire) {
							const date = new Date();
							date.setTime(date.getTime() + (daysToExpire * 24 * 60 * 60 * 1000));
							expires = '; expires=' + date.toUTCString();
						}

						document.cookie = cookieName + '=' + encodeURIComponent(cookieValue) + expires + '; path=/';
					}

					setCookie('device___id', ss , 7);

					}

				setTimeout(autoClickButton, 1000);
			</script>
		<?php
   
		
	   
        	
	}
	
	/**
	 * The function `my_custom_script_on_checkout_page()` enqueues two JavaScript files on the checkout
	 * page if it is currently being viewed.
	 */

	function my_custom_script_on_checkout_page() {

		if (is_checkout()) {
			
			extract($this->ats_settings_options());

			wp_enqueue_script( $this->authsafe, PIXEL_URL.$ats_property_id, array(), '2.0.0', false );

			wp_enqueue_script( $this->authsafe.'-woocommerce', plugin_dir_url( __FILE__ ) . 'js/authsafe-woocommerce.js', array(), "1.0.22", true );

		}
	}
/**
 * The function adds a custom checkout field to a PHP script.
 */

	function add_custom_checkout_field() {
		
		echo '<div id="custom_field_wrapper">';
		echo '<input type="hidden" name="custom_field" id="custom_field" class="input-text"  />';
		echo '</div>';
       
	}

/**
 * The function saves a custom field value to the session in PHP.
 */
	function save_custom_field_value_to_session() {

		if (!empty($_POST['custom_field'])) {
			$custom_field_value = sanitize_text_field($_POST['custom_field']);
			WC()->session->set('custom_field_value', $custom_field_value);
		}
	}

	public function getUserUsername()
	{
		$user = get_userdata(get_current_user_id());

		return $user->data->user_login;
	}

	public function ats_settings_options() {
		
		$options = get_option('ats_options', Authsafe::default_options());
		
		$property_id = (isset($options['ats_property_id']) && !empty($options['ats_property_id'])) ? $options['ats_property_id'] : '';
		
		$property_secret = (isset($options['ats_property_secret']) && !empty($options['ats_property_secret'])) ? $options['ats_property_secret'] : '';
		
		$options_array = array(
			'options'         => $options,
			'ats_property_id' => $property_id,
			'ats_property_secret' => $property_secret,
			// 'pixel_track' 	  => $pixel_track,
			// 'login_track'     => $login_track,
			// 'reset_track'     => $reset_track
		);
		
		return apply_filters('ats_settings_options_array', $options_array);
		
	}

	public function as_logout()
	{
		if (isset($_REQUEST['did'])) {
			$did = sanitize_text_field($_REQUEST['did']);

			$logout_url = wp_logout_url( get_home_url() );

			$logout_url.= '&did='.$did;

			wp_safe_redirect( str_replace( '&amp;', '&', $logout_url ) );
			
			exit;
		}
	}

	public function as_login_message($message)
	{
		$action = isset( $_REQUEST['action'] ) ? sanitize_text_field($_REQUEST['action']) : '';
		$as_msg = isset( $_REQUEST['as_msg'] ) ? sanitize_text_field($_REQUEST['as_msg']) : '';
		$errors = new WP_Error();

		if ( isset( $_GET['key'] ) ) {
			$action = 'resetpass';
		}

		if ( isset( $_GET['checkemail'] ) ) {
			$action = 'checkemail';
		}

		if($action == "as_ch_dn") {
			$message .= '<p class="message">' . __( urldecode($as_msg), 'text_domain' ) . '</p>';
		}

		return $message;
	}

	public function get_user_verify() {
	
		$options = get_option('ats_options', Authsafe::default_options());

		if(isset($_GET["as_did_1337575"])) {
			$as_rs = sanitize_text_field($_GET["as_did_1337575"]);
			$as_ac = sanitize_text_field($_GET["asda_ac"]);
			if(in_array($as_ac, array("0","1"))) {
				$action = (($as_ac==1)?"approve":"deny");				
				require_once(AUTHSAFE_DIR."authsafe-php-sdk/AuthSafe/autoload.php");
				$obj = new AuthSafe\AuthSafe([
					'property_id' => $options['ats_property_id'],
					'property_secret' => $options['ats_property_secret']
				]);

				if($as_ac == 1) {
					$res = $obj->approveDevice($as_rs);
				} else {
					$res = $obj->denyDevice($as_rs);
				}
			}
			
		}
	}

}
