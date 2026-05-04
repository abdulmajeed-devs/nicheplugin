<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://devdoseo.com
 * @since             1.0.0
 * @package           Domain_Data_Manager
 *
 * @wordpress-plugin
 * Plugin Name:       Domain Data Manager
 * Plugin URI:        https://devdoseo.com/plugins/domain-data-manager
 * Description:       A professional WordPress plugin for managing domain data with advanced analytics, CSV import/export, and responsive frontend display. Features include DA tracking, traffic analysis, sortable tables, and mobile-friendly design.
 * Version:           1.0.0
 * Author:            Zaviyaan
 * Author URI:        https://devdoseo.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       domain-data-manager
 * Domain Path:       /languages
 * Requires at least: 5.0
 * Tested up to:      6.4
 * Requires PHP:      7.4
 * Network:           false
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('DDM_VERSION', '1.0.0');

/**
 * Define plugin constants for paths and URLs
 */
define('DDM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DDM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('DDM_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Database version for managing schema updates
 */
define('DDM_DB_VERSION', '1.0.0');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-domain-data-manager-activator.php
 */
function activate_domain_data_manager() {
    require_once DDM_PLUGIN_DIR . 'includes/class-domain-data-manager-activator.php';
    Domain_Data_Manager_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-domain-data-manager-deactivator.php
 */
function deactivate_domain_data_manager() {
    require_once DDM_PLUGIN_DIR . 'includes/class-domain-data-manager-deactivator.php';
    Domain_Data_Manager_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_domain_data_manager');
register_deactivation_hook(__FILE__, 'deactivate_domain_data_manager');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require DDM_PLUGIN_DIR . 'includes/class-domain-data-manager.php';

/**
 * Check for plugin dependencies and requirements
 */
function ddm_check_requirements() {
    // Check PHP version
    if (version_compare(PHP_VERSION, '7.4', '<')) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>';
            echo __('Domain Data Manager requires PHP 7.4 or higher. Your current PHP version is ', 'domain-data-manager') . PHP_VERSION;
            echo '</p></div>';
        });
        return false;
    }

    // Check WordPress version
    global $wp_version;
    if (version_compare($wp_version, '5.0', '<')) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>';
            echo __('Domain Data Manager requires WordPress 5.0 or higher. Please update WordPress.', 'domain-data-manager');
            echo '</p></div>';
        });
        return false;
    }

    return true;
}

/**
 * Initialize the plugin only if requirements are met
 */
function run_domain_data_manager() {
    if (!ddm_check_requirements()) {
        return;
    }

    $plugin = new Domain_Data_Manager();
    $plugin->run();
}

/**
 * Add plugin action links
 */
function ddm_add_action_links($links) {
    $action_links = array(
        'dashboard' => '<a href="' . admin_url('admin.php?page=ddm-dashboard') . '">' . __('Dashboard', 'domain-data-manager') . '</a>',
        'settings' => '<a href="' . admin_url('admin.php?page=ddm-settings') . '">' . __('Settings', 'domain-data-manager') . '</a>',
    );
    
    return array_merge($action_links, $links);
}
add_filter('plugin_action_links_' . DDM_PLUGIN_BASENAME, 'ddm_add_action_links');

/**
 * Add plugin meta links
 */
function ddm_add_meta_links($links, $file) {
    if ($file === DDM_PLUGIN_BASENAME) {
        $meta_links = array(
            'docs' => '<a href="https://devdoseo.com/docs/domain-data-manager" target="_blank">' . __('Documentation', 'domain-data-manager') . '</a>',
            'support' => '<a href="https://devdoseo.com/support" target="_blank">' . __('Support', 'domain-data-manager') . '</a>',
            'rate' => '<a href="https://wordpress.org/plugins/domain-data-manager/#reviews" target="_blank">' . __('Rate Plugin', 'domain-data-manager') . '</a>',
        );
        $links = array_merge($links, $meta_links);
    }
    return $links;
}
add_filter('plugin_row_meta', 'ddm_add_meta_links', 10, 2);

/**
 * Load plugin textdomain for internationalization
 */
function ddm_load_plugin_textdomain() {
    load_plugin_textdomain(
        'domain-data-manager',
        false,
        dirname(DDM_PLUGIN_BASENAME) . '/languages/'
    );
}
add_action('plugins_loaded', 'ddm_load_plugin_textdomain');

/**
 * Add admin notices for plugin updates or important information
 */
function ddm_admin_notices() {
    // Check if this is a new installation
    if (get_transient('ddm_activation_redirect')) {
        delete_transient('ddm_activation_redirect');
        if (!isset($_GET['activate-multi'])) {
            wp_redirect(admin_url('admin.php?page=ddm-dashboard&welcome=1'));
            exit;
        }
    }

    // Show welcome message on dashboard
    if (isset($_GET['welcome']) && $_GET['welcome'] == '1' && current_user_can('manage_options')) {
        echo '<div class="notice notice-success is-dismissible">';
        echo '<p><strong>' . __('Welcome to Domain Data Manager!', 'domain-data-manager') . '</strong></p>';
        echo '<p>' . __('Thank you for installing Domain Data Manager. Get started by uploading your domain data or adding entries manually.', 'domain-data-manager') . '</p>';
        echo '<p>';
        echo '<a href="' . admin_url('admin.php?page=ddm-upload-csv') . '" class="button button-primary">' . __('Upload CSV Data', 'domain-data-manager') . '</a> ';
        echo '<a href="' . admin_url('admin.php?page=domain-data-manager') . '" class="button button-secondary">' . __('Add Data Manually', 'domain-data-manager') . '</a>';
        echo '</p>';
        echo '</div>';
    }
}
add_action('admin_notices', 'ddm_admin_notices');

/**
 * Set activation redirect transient
 */
function ddm_activation_redirect() {
    set_transient('ddm_activation_redirect', true, 30);
}
register_activation_hook(__FILE__, 'ddm_activation_redirect');

/**
 * Check for database updates
 */
function ddm_check_version() {
    $installed_version = get_option('ddm_db_version', '0.0.0');
    
    if (version_compare($installed_version, DDM_DB_VERSION, '<')) {
        // Run database update
        activate_domain_data_manager();
        update_option('ddm_db_version', DDM_DB_VERSION);
        
        // Add admin notice about update
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p>' . __('Domain Data Manager database has been updated successfully.', 'domain-data-manager') . '</p>';
            echo '</div>';
        });
    }
}
add_action('plugins_loaded', 'ddm_check_version');

/**
 * Add custom capabilities for role-based access
 */
function ddm_add_capabilities() {
    $role = get_role('administrator');
    if ($role) {
        $role->add_cap('ddm_manage_data');
        $role->add_cap('ddm_upload_csv');
        $role->add_cap('ddm_export_data');
        $role->add_cap('ddm_view_analytics');
    }
    
    $role = get_role('editor');
    if ($role) {
        $role->add_cap('ddm_manage_data');
        $role->add_cap('ddm_view_analytics');
    }
}
register_activation_hook(__FILE__, 'ddm_add_capabilities');

/**
 * Remove custom capabilities on deactivation
 */
function ddm_remove_capabilities() {
    $roles = ['administrator', 'editor'];
    $caps = ['ddm_manage_data', 'ddm_upload_csv', 'ddm_export_data', 'ddm_view_analytics'];
    
    foreach ($roles as $role_name) {
        $role = get_role($role_name);
        if ($role) {
            foreach ($caps as $cap) {
                $role->remove_cap($cap);
            }
        }
    }
}
register_deactivation_hook(__FILE__, 'ddm_remove_capabilities');

/**
 * Add custom post states for pages using shortcode
 */
function ddm_display_post_states($post_states, $post) {
    if (has_shortcode($post->post_content, 'domain_data_table')) {
        $post_states['ddm_shortcode'] = __('Domain Data Table', 'domain-data-manager');
    }
    return $post_states;
}
add_filter('display_post_states', 'ddm_display_post_states', 10, 2);

/**
 * Add shortcode button to classic editor
 */
function ddm_add_shortcode_button() {
    if (!current_user_can('edit_posts') && !current_user_can('edit_pages')) {
        return;
    }

    add_filter('mce_external_plugins', 'ddm_add_tinymce_plugin');
    add_filter('mce_buttons', 'ddm_register_mce_button');
}

function ddm_add_tinymce_plugin($plugin_array) {
    $plugin_array['ddm_shortcode'] = DDM_PLUGIN_URL . 'assets/js/ddm-tinymce.js';
    return $plugin_array;
}

function ddm_register_mce_button($buttons) {
    array_push($buttons, 'ddm_shortcode');
    return $buttons;
}
add_action('admin_head', 'ddm_add_shortcode_button');

/**
 * Register REST API endpoints for external integrations
 */
function ddm_register_rest_routes() {
    register_rest_route('ddm/v1', '/domains', array(
        'methods' => 'GET',
        'callback' => 'ddm_rest_get_domains',
        'permission_callback' => function() {
            return current_user_can('ddm_view_analytics');
        }
    ));
    
    register_rest_route('ddm/v1', '/analytics', array(
        'methods' => 'GET',
        'callback' => 'ddm_rest_get_analytics',
        'permission_callback' => function() {
            return current_user_can('ddm_view_analytics');
        }
    ));
}
add_action('rest_api_init', 'ddm_register_rest_routes');

/**
 * REST API callback for domains endpoint
 */
function ddm_rest_get_domains($request) {
    $db = new Domain_Data_Manager_Db();
    $args = array(
        'per_page' => $request->get_param('per_page') ?: 20,
        'offset' => $request->get_param('offset') ?: 0,
        'search' => $request->get_param('search') ?: '',
        'orderby' => $request->get_param('orderby') ?: 'domain',
        'order' => $request->get_param('order') ?: 'ASC'
    );
    
    $data = $db->get_data($args);
    $total = $db->count_data($args['search']);
    
    return new WP_REST_Response(array(
        'data' => $data,
        'total' => $total,
        'page' => ceil($args['offset'] / $args['per_page']) + 1,
        'pages' => ceil($total / $args['per_page'])
    ), 200);
}

/**
 * REST API callback for analytics endpoint
 */
function ddm_rest_get_analytics($request) {
    $db = new Domain_Data_Manager_Db();
    $analytics = $db->get_analytics_data();
    
    return new WP_REST_Response($analytics, 200);
}

/**
 * Add security headers
 */
function ddm_add_security_headers() {
    if (is_admin() && isset($_GET['page']) && strpos($_GET['page'], 'ddm') === 0) {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
    }
}
add_action('send_headers', 'ddm_add_security_headers');

/**
 * Schedule cleanup tasks
 */
function ddm_schedule_cleanup() {
    if (!wp_next_scheduled('ddm_cleanup_logs')) {
        wp_schedule_event(time(), 'weekly', 'ddm_cleanup_logs');
    }
}
add_action('wp', 'ddm_schedule_cleanup');

/**
 * Cleanup old logs
 */
function ddm_cleanup_old_logs() {
    $logger = new Domain_Data_Manager_Logger();
    $logger->cleanup_old_logs(90); // Keep logs for 90 days
}
add_action('ddm_cleanup_logs', 'ddm_cleanup_old_logs');

/**
 * Handle plugin deactivation cleanup
 */
function ddm_deactivation_cleanup() {
    // Clear scheduled events
    wp_clear_scheduled_hook('ddm_cleanup_logs');
    
    // Clear any cached data
    wp_cache_delete('ddm_analytics_data');
    wp_cache_delete('ddm_domain_types');
    
    // Flush rewrite rules
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'ddm_deactivation_cleanup');

/**
 * Add dashboard widget for quick stats
 */
function ddm_add_dashboard_widget() {
    if (current_user_can('ddm_view_analytics')) {
        wp_add_dashboard_widget(
            'ddm_stats_widget',
            __('Domain Data Manager - Quick Stats', 'domain-data-manager'),
            'ddm_dashboard_widget_content'
        );
    }
}
add_action('wp_dashboard_setup', 'ddm_add_dashboard_widget');

/**
 * Dashboard widget content
 */
function ddm_dashboard_widget_content() {
    $db = new Domain_Data_Manager_Db();
    $analytics = $db->get_analytics_data();
    
    echo '<div class="ddm-dashboard-widget">';
    echo '<div class="ddm-widget-stats">';
    echo '<div class="ddm-widget-stat"><span class="ddm-stat-number">' . number_format($analytics['total_domains']) . '</span><span class="ddm-stat-label">' . __('Total Domains', 'domain-data-manager') . '</span></div>';
    echo '<div class="ddm-widget-stat"><span class="ddm-stat-number">' . number_format($analytics['avg_da']) . '</span><span class="ddm-stat-label">' . __('Average DA', 'domain-data-manager') . '</span></div>';
    echo '<div class="ddm-widget-stat"><span class="ddm-stat-number">' . number_format($analytics['total_traffic']) . '</span><span class="ddm-stat-label">' . __('Total Traffic', 'domain-data-manager') . '</span></div>';
    echo '</div>';
    echo '<p><a href="' . admin_url('admin.php?page=ddm-dashboard') . '" class="button button-primary">' . __('View Full Dashboard', 'domain-data-manager') . '</a></p>';
    echo '</div>';
    
    echo '<style>
    .ddm-dashboard-widget .ddm-widget-stats { display: flex; gap: 1rem; margin-bottom: 1rem; }
    .ddm-widget-stat { text-align: center; flex: 1; }
    .ddm-stat-number { display: block; font-size: 1.5em; font-weight: bold; color: #3b82f6; }
    .ddm-stat-label { display: block; font-size: 0.8em; color: #6b7280; margin-top: 0.25rem; }
    </style>';
}

/**
 * Add custom CSS for admin areas
 */
function ddm_admin_head() {
    if (isset($_GET['page']) && strpos($_GET['page'], 'ddm') === 0) {
        echo '<style>
        .ddm-plugin-header { background: linear-gradient(135deg, #3b82f6, #2563eb); color: white; padding: 1rem; margin: -10px -20px 20px -20px; }
        .ddm-plugin-header h1 { color: white; margin: 0; }
        </style>';
    }
}
add_action('admin_head', 'ddm_admin_head');

/**
 * Begin execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
run_domain_data_manager();
