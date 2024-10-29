<?php

/**
 * @package           Authsafe
 *
 * Plugin Name:       Ecommerce Retailers Fraud Prevention - AuthSafe Intelligence
 * Plugin URI:        https://authsafe.ai
 * Description:       AushSafe plugin is meant for AuthSafe customer only for private use. This plugin helps AuthSafe customers to integrate AuthSafe SDK in their wordpress website and activate Pixel operations.
 * Version:           2.0.0
 * Author:            AuthSafe
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Text Domain:       authsafe
 * Requires at least: 3.0.1
 * Requires PHP:      5.6
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently AuthSafe version.
 */
define( 'AUTHSAFE_VERSION', '2.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-authsafe-activator.php
 */
function activate_authsafe() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-authsafe-activator.php';
	AuthSafe_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-authsafe-deactivator.php
 */
function deactivate_authsafe() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-authsafe-deactivator.php';
	AuthSafe_Deactivator::deactivate();
}

function table_create() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-authsafe-table-create.php';
	Authsafe_Table_Create::table_create();
}

function table_delete() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-authsafe-table-delete.php';
	Authsafe_Table_Delete::table_delete();
}


register_activation_hook( __FILE__, 'activate_authsafe' );
register_deactivation_hook( __FILE__, 'deactivate_authsafe' );
register_activation_hook( __FILE__, 'table_create' );
register_deactivation_hook( __FILE__, 'table_delete' );


/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-authsafe.php';

/**
 * Begins execution of the plugin.
 *
 * @since    2.0.0
 */
function run_authsafe() {

	$plugin = new AuthSafe();
	$plugin->run();
	if (!defined('AUTHSAFE_URL'))     define('AUTHSAFE_URL',     plugin_dir_url(__FILE__));
	if (!defined('AUTHSAFE_DIR'))     define('AUTHSAFE_DIR',     plugin_dir_path(__FILE__));
	if (!defined('AUTHSAFE_FILE'))    define('AUTHSAFE_FILE',    plugin_basename(__FILE__));
	if (!defined('AUTHSAFE_SLUG'))    define('AUTHSAFE_SLUG',    basename(dirname(__FILE__)));
	if (!defined('PIXEL_URL'))    define('PIXEL_URL', 'https://p.authsafe.ai/as.js?p=');

}
run_authsafe();



function your_plugin_activation_callback() {                                // Callback function for plugin activation
    
    if (get_option('activate_authsafe', true)) {                            // Check if it's the first activation of the plugin
       
        update_option('activate_authsafe', false);                          // Reset the first activation flag

        // Set a transient to indicate redirection is needed
		// set_transient -> It is often used to store temporary data or flags that need to be available for a short period.
        set_transient('your_plugin_redirect', true, 30);
    }
}


register_activation_hook(__FILE__, 'your_plugin_activation_callback');       // Hook the callback function to the plugin activation


function your_plugin_check_redirection() {                                   // Check for the transient and perform redirection
    if (get_transient('your_plugin_redirect')) {
       
        delete_transient('your_plugin_redirect');                            // Delete the transient to prevent further redirections
                                                              
        wp_safe_redirect(admin_url('admin.php?page=authsafe'));              // Redirect to the specified URL after plugin activation
    }
}
add_action('admin_init', 'your_plugin_check_redirection');





