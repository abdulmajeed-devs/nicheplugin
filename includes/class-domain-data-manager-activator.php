<?php

/**
 * Fired during plugin activation
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Domain_Data_Manager
 * @subpackage Domain_Data_Manager/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Domain_Data_Manager
 * @subpackage Domain_Data_Manager/includes
 * @author     Manus AI <your-name@example.com>
 */
class Domain_Data_Manager_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'domain_data_manager_data';
        $log_table_name = $wpdb->prefix . 'domain_data_manager_logs';
        $charset_collate = $wpdb->get_charset_collate();

        // SQL to create the main data table
        $sql_data = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            type varchar(255) DEFAULT '' NOT NULL,
            domain varchar(255) NOT NULL,
            da int(11) DEFAULT 0 NOT NULL,
            traffic bigint(20) DEFAULT 0 NOT NULL,
            age int(11) DEFAULT 0 NOT NULL,
            emd tinyint(1) DEFAULT 0 NOT NULL,
            created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            updated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY domain (domain),
            KEY type_idx (type),
            KEY da_idx (da),
            KEY traffic_idx (traffic),
            KEY created_at_idx (created_at)
        ) $charset_collate;";

        // SQL to create the logs table
        $sql_logs = "CREATE TABLE $log_table_name (
            log_id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL,
            upload_timestamp datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            records_inserted int(11) DEFAULT 0 NOT NULL,
            records_updated int(11) DEFAULT 0 NOT NULL,
            status varchar(50) DEFAULT 'success' NOT NULL,
            message text,
            PRIMARY KEY  (log_id),
            KEY user_id_idx (user_id),
            KEY upload_timestamp_idx (upload_timestamp),
            KEY status_idx (status)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql_data );
        dbDelta( $sql_logs );

        // Add default options if they don't exist
        add_option('ddm_table_color_scheme', '#3b82f6'); // Modern blue
        add_option('ddm_table_borders', '1'); // Default to show borders
        add_option('ddm_table_row_styling', '0'); // Default to no alternating row styling
        
        // Add version option for future migrations
        add_option('ddm_db_version', DDM_VERSION);
        
        // Set activation timestamp
        add_option('ddm_activation_time', current_time('timestamp'));
	}

}
