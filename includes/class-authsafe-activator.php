<?php

/**
 * Fired during plugin activation
 *
 * @link       https://authsafe.ai
 * @since      2.0.0
 *
 * @package    Authsafe
 * @subpackage Authsafe/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      2.0.0
 * @package    Authsafe
 * @subpackage Authsafe/includes
 * @author     Jinendra Khobare <jinendra@securelayer7.net>
 */
class Authsafe_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    2.0.0
	 */
	public static function activate() {
        $options = get_option('ats_options', Authsafe::default_options());

        require_once(AUTHSAFE_DIR."authsafe-php-sdk/AuthSafe/autoload.php");
        $obj = new AuthSafe\AuthSafe([
            'property_id' => $options['ats_property_id'],
            'property_secret' => $options['ats_property_secret']
        ]);
		
	}

}
