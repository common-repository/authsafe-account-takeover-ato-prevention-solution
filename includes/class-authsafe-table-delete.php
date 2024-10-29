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
class Authsafe_Table_Delete {

	/**
	 *
	 * @since    2.0.0
	 */
	public static function table_delete() {
       
        global $wpdb;
        $table_name = $wpdb->prefix . 'authsafe_sleep';
        $sql = "DROP TABLE IF EXISTS $table_name;";
        $wpdb->query( $sql );

		
	}

}
