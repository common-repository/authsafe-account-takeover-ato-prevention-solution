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
class Authsafe_Table_Create {

	/**
	 *
	 * @since    2.0.0
	 */
	public static function table_create() {
       
        global $wpdb;
        $table_name = $wpdb->prefix . 'authsafe_sleep'; 

        $charset_collate = $wpdb->get_charset_collate();

        // Define the SQL query to create the table
        $sql = "CREATE TABLE $table_name (
            id INT NOT NULL AUTO_INCREMENT,
            cid INT NOT NULL,
            c_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
        
	}

}
