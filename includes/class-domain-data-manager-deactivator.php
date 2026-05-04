<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Domain_Data_Manager
 * @subpackage Domain_Data_Manager/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Domain_Data_Manager
 * @subpackage Domain_Data_Manager/includes
 * @author     Manus AI <your-name@example.com>
 */
class Domain_Data_Manager_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		// Clear any scheduled hooks or cron jobs
        wp_clear_scheduled_hook('ddm_cleanup_logs');
        wp_clear_scheduled_hook('ddm_analytics_update');
        
        // Clear any cached data
        wp_cache_delete('ddm_analytics_data');
        wp_cache_delete('ddm_domain_types');
        
        // Flush rewrite rules if needed
        flush_rewrite_rules();
        
        // Set deactivation timestamp
        update_option('ddm_deactivation_time', current_time('timestamp'));
	}

}
