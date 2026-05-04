<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the file is called correctly
 * - See if the plugin is actually being uninstalled
 * - Run a compatibility check
 * - Sanitize user input
 * - Database operation
 * - All information related to the plugin should be deleted
 *
 * @link       https://devdoseo.com
 * @since      1.0.0
 *
 * @package    Domain_Data_Manager
 */

// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Check if the user has permissions to uninstall plugins
if (!current_user_can('activate_plugins')) {
    return;
}

// Verify uninstall intent
if (__FILE__ != WP_UNINSTALL_PLUGIN) {
    return;
}

// Load WordPress database layer
global $wpdb;

/**
 * Define table names
 */
$data_table_name = $wpdb->prefix . 'domain_data_manager_data';
$log_table_name = $wpdb->prefix . 'domain_data_manager_logs';

/**
 * Check if we should preserve data on uninstall
 * This gives users the option to keep their data
 */
$preserve_data = get_option('ddm_preserve_data_on_uninstall', false);

if (!$preserve_data) {
    /**
     * Drop custom database tables
     */
    $wpdb->query("DROP TABLE IF EXISTS {$data_table_name}");
    $wpdb->query("DROP TABLE IF EXISTS {$log_table_name}");
    
    /**
     * Delete all plugin options from the options table
     */
    $options_to_delete = array(
        'ddm_table_color_scheme',
        'ddm_table_borders',
        'ddm_table_row_styling',
        'ddm_db_version',
        'ddm_activation_time',
        'ddm_deactivation_time',
        'ddm_preserve_data_on_uninstall'
    );
    
    foreach ($options_to_delete as $option) {
        delete_option($option);
    }
    
    /**
     * Delete user meta related to the plugin
     */
    $wpdb->query("DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE 'ddm_%'");
    
    /**
     * Delete transients
     */
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_ddm_%' OR option_name LIKE '_transient_timeout_ddm_%'");
    
    /**
     * Clear any cached data
     */
    wp_cache_delete('ddm_analytics_data');
    wp_cache_delete('ddm_domain_types');
    wp_cache_delete('ddm_recent_activity');
    
    /**
     * Delete any uploaded files (if any were stored)
     */
    $upload_dir = wp_upload_dir();
    $ddm_upload_path = $upload_dir['basedir'] . '/domain-data-manager';
    
    if (is_dir($ddm_upload_path)) {
        $files = array_diff(scandir($ddm_upload_path), array('.', '..'));
        foreach ($files as $file) {
            $file_path = $ddm_upload_path . '/' . $file;
            if (is_file($file_path)) {
                unlink($file_path);
            }
        }
        rmdir($ddm_upload_path);
    }
}

/**
 * Clear scheduled events regardless of data preservation setting
 */
wp_clear_scheduled_hook('ddm_cleanup_logs');
wp_clear_scheduled_hook('ddm_analytics_update');

/**
 * Remove custom capabilities from all roles
 */
$roles = array('administrator', 'editor', 'author', 'contributor');
$capabilities = array(
    'ddm_manage_data',
    'ddm_upload_csv',
    'ddm_export_data',
    'ddm_view_analytics'
);

foreach ($roles as $role_name) {
    $role = get_role($role_name);
    if ($role) {
        foreach ($capabilities as $cap) {
            $role->remove_cap($cap);
        }
    }
}

/**
 * Flush rewrite rules to clean up any custom endpoints
 */
flush_rewrite_rules();

/**
 * Log uninstallation for debugging purposes (if logging is enabled)
 */
if (defined('WP_DEBUG') && WP_DEBUG) {
    error_log('Domain Data Manager: Plugin uninstalled successfully at ' . current_time('mysql'));
}

/**
 * Optional: Send uninstall feedback to plugin author
 * Only if user has opted in to usage tracking
 */
$allow_tracking = get_option('ddm_allow_usage_tracking', false);
if ($allow_tracking) {
    $uninstall_data = array(
        'site_url' => get_site_url(),
        'plugin_version' => defined('DDM_VERSION') ? DDM_VERSION : '1.0.0',
        'wp_version' => get_bloginfo('version'),
        'php_version' => PHP_VERSION,
        'uninstall_date' => current_time('mysql'),
        'preserve_data' => $preserve_data
    );
    
    // Send anonymous data to help improve the plugin
    wp_remote_post('https://devdoseo.com/api/plugin-uninstall-feedback', array(
        'body' => $uninstall_data,
        'timeout' => 5,
        'blocking' => false
    ));
}

/**
 * Clean up any remaining plugin-specific data
 */

// Remove any plugin-specific cron jobs
$cron_jobs = _get_cron_array();
if (!empty($cron_jobs)) {
    foreach ($cron_jobs as $timestamp => $cron) {
        foreach ($cron as $hook => $dings) {
            if (strpos($hook, 'ddm_') === 0) {
                wp_unschedule_event($timestamp, $hook);
            }
        }
    }
}

// Clean up any remaining database entries
$wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE 'ddm_%'");
$wpdb->query("DELETE FROM {$wpdb->posts} WHERE post_type = 'ddm_%'");

// Remove any custom taxonomy terms (if any were created)
$custom_taxonomies = array('ddm_domain_type', 'ddm_domain_category');
foreach ($custom_taxonomies as $taxonomy) {
    if (taxonomy_exists($taxonomy)) {
        $terms = get_terms(array(
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
        ));
        
        if (!is_wp_error($terms)) {
            foreach ($terms as $term) {
                wp_delete_term($term->term_id, $taxonomy);
            }
        }
    }
}

/**
 * Final cleanup - remove any orphaned data
 */

// Clean up site transients
$wpdb->query("DELETE FROM {$wpdb->sitemeta} WHERE meta_key LIKE 'ddm_%' OR meta_key LIKE '_site_transient_ddm_%' OR meta_key LIKE '_site_transient_timeout_ddm_%'");

// Clean up comment meta (in case plugin stored data there)
$wpdb->query("DELETE FROM {$wpdb->commentmeta} WHERE meta_key LIKE 'ddm_%'");

// Clean up term meta (in case plugin stored data there)
$wpdb->query("DELETE FROM {$wpdb->termmeta} WHERE meta_key LIKE 'ddm_%'");

/**
 * Optimize database tables after cleanup
 */
if (!$preserve_data) {
    $wpdb->query("OPTIMIZE TABLE {$wpdb->options}");
    $wpdb->query("OPTIMIZE TABLE {$wpdb->usermeta}");
    $wpdb->query("OPTIMIZE TABLE {$wpdb->postmeta}");
}

/**
 * Create uninstall log entry for site admin
 */
if (current_user_can('manage_options')) {
    $uninstall_message = sprintf(
        __('Domain Data Manager plugin was uninstalled on %s. %s', 'domain-data-manager'),
        current_time('F j, Y \a\t g:i a'),
        $preserve_data ? __('Data was preserved as requested.', 'domain-data-manager') : __('All plugin data was removed.', 'domain-data-manager')
    );
    
    // Store as a temporary option that will be shown on next admin login
    set_transient('ddm_uninstall_notice', $uninstall_message, DAY_IN_SECONDS);
}

/**
 * Cleanup complete - log final message
 */
if (defined('WP_DEBUG') && WP_DEBUG) {
    $cleanup_type = $preserve_data ? 'partial' : 'complete';
    error_log("Domain Data Manager: Uninstall cleanup ({$cleanup_type}) completed successfully");
}

// End of uninstall script
