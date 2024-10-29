 <?php

/**
 * Fired when the plugin is uninstalled.
 *
 * @link       https://authsafe.ai
 * @since      2.0.0
 *
 * @package    Authsafe
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// delete options
delete_option('ats_options');
delete_option('ats_policy_options');