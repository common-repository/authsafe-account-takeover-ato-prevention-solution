<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://authsafe.ai
 * @since      2.0.0
 *
 * @package    Authsafe
 * @subpackage Authsafe/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      2.0.0
 * @package    Authsafe
 * @subpackage Authsafe/includes
 * @author     Jinendra Khobare <jinendra@securelayer7.net>
 */
class Authsafe {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    2.0.0
	 * @access   protected
	 * @var      Authsafe_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    2.0.0
	 * @access   protected
	 * @var      string    $authsafe    The string used to uniquely identify this plugin.
	 */
	protected $authsafe;

	/**
	 * The current version of the plugin.
	 *
	 * @since    2.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * The core functionality of the AuthSafe plugin.
	 *
	 * @since    2.0.0
	 */
	public function __construct() {
		$this->constants();
		if ( defined( 'AUTHSAFE_VERSION' ) ) {
			$this->version = AUTHSAFE_VERSION;
		} else {
			$this->version = '2.0.0';
		}
		$this->authsafe = 'authsafe';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	public function constants() {
		
		if (!defined('AUTHSAFE_VERSION')) define('AUTHSAFE_VERSION', '2.0.0');
		if (!defined('AUTHSAFE_AUTHOR'))  define('AUTHSAFE_AUTHOR',  'Authsafe');
		if (!defined('AUTHSAFE_NAME'))    define('AUTHSAFE_NAME',    __('Authsafe', 'authsafe'));
		if (!defined('AUTHSAFE_PATH'))    define('AUTHSAFE_PATH',    'admin.php?page=authsafe');
		if (!defined('AUTHSAFE_API_URL')) define('AUTHSAFE_API_URL', 'http://a.authsafe.ai/v1');
		
	}
		
	public static function default_options() {
		
		$options = array(
			
			'ats_property_id'    => '',
			'ats_property_secret'=> '',
			'default_options' => 0
			
		);
		
		return apply_filters('ats_default_options', $options);
		
	}
		
	public static function default_policy_options() {
		
		$options = array(
			
			'ats_medium_email'    => '1',
			'ats_challenge_email'=> '1',
			'ats_deny_email'=> '1',
			'ats_allow_payment_email' => '1',
			'ats_challenge_payment_email' => '1',
			'ats_deny_payment_email' => '1'
			
		);
		
		return apply_filters('ats_default_policy_options', $options);
		
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Authsafe_Loader. Orchestrates the hooks of the plugin.
	 * - Authsafe_i18n. Defines internationalization functionality.
	 * - Authsafe_Admin. Defines all hooks for the admin area.
	 * - Authsafe_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    2.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-authsafe-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-authsafe-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-authsafe-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-authsafe-public.php';

		$this->loader = new Authsafe_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Authsafe_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    2.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Authsafe_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    2.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {


		$plugin_admin = new Authsafe_Admin( $this->get_authsafe(), $this->get_version() );
		
		$this->loader->add_action( 'admin_menu'			  , $plugin_admin, 'add_menu');
		$this->loader->add_filter( 'admin_init'			  , $plugin_admin, 'add_settings');
		$this->loader->add_filter( 'plugin_action_links'  , $plugin_admin, 'action_links', 10, 2);
		$this->loader->add_filter( 'login_message'  	  , $plugin_admin, 'as_login_message', 10, 2);
		$this->loader->add_action( 'admin_notices'        , $plugin_admin, 'add_dashboard_notification');
        $this->loader->add_action( 'wp_login'			  , $plugin_admin, 'wp_login_track', 10, 2);
		$this->loader->add_filter( 'woocommerce_before_checkout_form', $plugin_admin, 'payment_attempt', 10, 2);
		$this->loader->add_action( 'woocommerce_thankyou', $plugin_admin, 'payment_complete', 10, 2 );
		$this->loader->add_action( 'wp_login_failed'	  , $plugin_admin, 'wp_login_failed_track', 10, 2);
		$this->loader->add_action( 'after_password_reset' , $plugin_admin, 'password_reset_track', 10, 2);
		$this->loader->add_action( 'lostpassword_post'	  , $plugin_admin, 'password_reset_failed_track', 10, 2);
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    2.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		

		$plugin_public = new Authsafe_Public( $this->get_authsafe(), $this->get_version() );

		$this->loader->add_action( 'init', $plugin_public, 'get_user_verify');
	
		$this->loader->add_action( 'wp_enqueue_scripts'	  , $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts'	  , $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'login_enqueue_scripts', $plugin_public, 'enqueue_scripts_login' );
		$this->loader->add_action( 'woocommerce_before_cart', $plugin_public, 'enqueue_scripts_transaction_attempt' );
		$this->loader->add_action( 'woocommerce_cart_collaterals', $plugin_public, 'custom_cart_collaterals');
		$this->loader->add_action( 'woocommerce_before_checkout_form', $plugin_public, 'my_custom_script_on_checkout_page');
		$this->loader->add_action( 'woocommerce_checkout_before_order_review', $plugin_public, 'add_custom_checkout_field');
		$this->loader->add_action( 'woocommerce_checkout_update_order_meta', $plugin_public, 'save_custom_field_value_to_session');
		$this->loader->add_filter( 'template_redirect'	  , $plugin_public, 'as_logout', 10, 2);
		$this->loader->add_action( 'login_message'		  , $plugin_public, 'as_login_message' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    2.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     2.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_authsafe() {
		return $this->authsafe;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     2.0.0
	 * @return    Authsafe_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     2.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
