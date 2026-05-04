<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Domain_Data_Manager
 * @subpackage Domain_Data_Manager/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for the public-facing side
 * of the site.
 *
 * @package    Domain_Data_Manager
 * @subpackage Domain_Data_Manager/public
 * @author     Manus AI <your-name@example.com>
 */
class Domain_Data_Manager_Public {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Database handler instance.
     *
     * @since    1.0.0
     * @access   private
     * @var      Domain_Data_Manager_Db $db Database handler.
     */
    private $db;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    string    $plugin_name       The name of the plugin.
     * @param    string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->db = new Domain_Data_Manager_Db();

    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        // Only enqueue on pages that contain the shortcode
        global $post;
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'domain_data_table')) {
            wp_enqueue_style( $this->plugin_name, DDM_PLUGIN_URL . 'assets/css/ddm-public.css', array(), $this->version, 'all' );
        }
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        // Only enqueue on pages that contain the shortcode
        global $post;
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'domain_data_table')) {
            wp_enqueue_script( $this->plugin_name, DDM_PLUGIN_URL . 'assets/js/ddm-public.js', array( 'jquery' ), $this->version, true );

            // Localize script with data needed for AJAX
            wp_localize_script( $this->plugin_name, 'ddm_public_ajax', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'ddm_public_nonce' ),
                'strings'  => array(
                    'loading'          => __( 'Loading...', 'domain-data-manager' ),
                    'no_results'       => __( 'No domains found', 'domain-data-manager' ),
                    'search_placeholder' => __( 'Search domains or types...', 'domain-data-manager' ),
                    'copied'           => __( 'Copied!', 'domain-data-manager' ),
                    'copy_failed'      => __( 'Copy failed', 'domain-data-manager' ),
                    'copy_domain'      => __( 'Copy domain', 'domain-data-manager' ),
                    'error_loading'    => __( 'Error loading data. Please try again.', 'domain-data-manager' ),
                    'type'             => __( 'Type', 'domain-data-manager' ),
                    'domain'           => __( 'Domain', 'domain-data-manager' ),
                    'da'               => __( 'DA', 'domain-data-manager' ),
                    'traffic'          => __( 'Traffic', 'domain-data-manager' ),
                    'age'              => __( 'Age', 'domain-data-manager' ),
                    'emd'              => __( 'EMD', 'domain-data-manager' ),
                    'years'            => __( 'years', 'domain-data-manager' ),
                )
            ));
        }
    }

    /**
     * Render the shortcode [domain_data_table].
     *
     * @since 1.0.0
     * @param array $atts Shortcode attributes.
     * @return string HTML output for the shortcode.
     */
    public function render_shortcode($atts) {
        // Extract shortcode attributes
        $atts = shortcode_atts( array(
            'limit' => -1,
            'type' => '',
            'min_da' => 0,
            'max_da' => 100,
            'show_search' => 'true',
            'default_sort' => 'domain',
            'default_order' => 'asc'
        ), $atts, 'domain_data_table' );

        // Sanitize attributes
        $limit = intval($atts['limit']);
        $type_filter = sanitize_text_field($atts['type']);
        $min_da = max(0, min(100, intval($atts['min_da'])));
        $max_da = max(0, min(100, intval($atts['max_da'])));
        $show_search = $atts['show_search'] !== 'false';
        $default_sort = sanitize_key($atts['default_sort']);
        $default_order = strtoupper($atts['default_order']) === 'DESC' ? 'DESC' : 'ASC';

        // Build query args
        $query_args = array(
            'per_page' => $limit,
            'orderby' => $default_sort,
            'order' => $default_order
        );

        // Add filters if specified
        if (!empty($type_filter)) {
            $query_args['type'] = $type_filter;
        }

        // Initial data load for SEO and first render
        $initial_data = $this->db->get_data($query_args);

        // Apply DA filters if needed
        if ($min_da > 0 || $max_da < 100) {
            $initial_data = array_filter($initial_data, function($row) use ($min_da, $max_da) {
                return $row['da'] >= $min_da && $row['da'] <= $max_da;
            });
        }

        // Get style settings
        $color_scheme = get_option('ddm_table_color_scheme', '#0073aa');
        $show_borders = get_option('ddm_table_borders', '1');
        $row_styling = get_option('ddm_table_row_styling', '1');

        // Generate unique ID for this table instance
        $table_id = 'ddm-table-' . wp_generate_uuid4();

        ob_start();
        require DDM_PLUGIN_DIR . 'public/partials/ddm-public-display.php';
        $output = ob_get_clean();

        // Add shortcode attributes as data attributes for JavaScript
        $data_attrs = array(
            'data-limit="' . esc_attr($limit) . '"',
            'data-type="' . esc_attr($type_filter) . '"',
            'data-min-da="' . esc_attr($min_da) . '"',
            'data-max-da="' . esc_attr($max_da) . '"',
            'data-show-search="' . esc_attr($show_search ? '1' : '0') . '"',
            'data-default-sort="' . esc_attr($default_sort) . '"',
            'data-default-order="' . esc_attr($default_order) . '"',
        );

        // Wrap output with data attributes
        return '<div id="' . esc_attr($table_id) . '" class="ddm-shortcode-wrapper" ' . implode(' ', $data_attrs) . '>' . $output . '</div>';
    }

    /**
     * AJAX handler for fetching public data (for search/sort).
     *
     * @since 1.0.0
     */
    public function handle_get_public_data() {
        // Verify nonce
        check_ajax_referer( 'ddm_public_nonce', 'nonce' );

        // *** FIX STARTS HERE: Changed all $_POST to $_GET to match the JavaScript request method ***
        // Sanitize and validate input parameters
        $search  = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
        $orderby = isset($_GET['orderby']) ? sanitize_key($_GET['orderby']) : 'domain';
        $order   = isset($_GET['order']) ? strtoupper(sanitize_key($_GET['order'])) : 'ASC';
        $type    = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : '';
        $limit   = isset($_GET['limit']) ? intval($_GET['limit']) : -1;
        
        // Additional filter parameters
        $da_min = isset($_GET['da_min']) ? max(0, min(100, intval($_GET['da_min']))) : 0;
        $da_max = isset($_GET['da_max']) ? max(0, min(100, intval($_GET['da_max']))) : 100;
        $traffic_min = isset($_GET['traffic_min']) ? max(0, intval($_GET['traffic_min'])) : 0;
        $traffic_max = isset($_GET['traffic_max']) ? max(0, intval($_GET['traffic_max'])) : 1000000;
        $contains = isset($_GET['contains']) ? sanitize_text_field($_GET['contains']) : '';
        $length_min = isset($_GET['length_min']) ? max(1, min(63, intval($_GET['length_min']))) : 1;
        $length_max = isset($_GET['length_max']) ? max(1, min(63, intval($_GET['length_max']))) : 63;
        // *** FIX ENDS HERE ***

        // Validate order
        if (!in_array($order, ['ASC', 'DESC'])) {
            $order = 'ASC';
        }
        
        // Validate orderby - allow only specific columns
        $allowed_orderby = ['id', 'type', 'domain', 'da', 'traffic', 'age', 'emd', 'created_at'];
        if (!in_array($orderby, $allowed_orderby)) {
            $orderby = 'domain';
        }

        // Build database query arguments
        $args = array(
            'search'   => $search,
            'orderby'  => $orderby,
            'order'    => $order,
            'per_page' => $limit
        );

        // Get data from database
        try {
            $data = $this->db->get_data($args);
        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => __('Database error occurred.', 'domain-data-manager')
            ));
            return;
        }

        // Apply additional filters
        if (!empty($type) || $da_min > 0 || $da_max < 100 || $traffic_min > 0 || $traffic_max < 1000000 || !empty($contains) || $length_min > 1 || $length_max < 63) {
            $data = array_filter($data, function($row) use ($type, $da_min, $da_max, $traffic_min, $traffic_max, $contains, $length_min, $length_max) {
                // Type filter
                $type_match = empty($type) || stripos($row['type'], $type) !== false;
                
                // DA filter
                $da_match = $row['da'] >= $da_min && $row['da'] <= $da_max;
                
                // Traffic filter
                $traffic_match = $row['traffic'] >= $traffic_min && $row['traffic'] <= $traffic_max;
                
                // Contains filter
                $contains_match = empty($contains) || stripos($row['domain'], $contains) !== false;
                
                // Length filter
                $domain_length = strlen($row['domain']);
                $length_match = $domain_length >= $length_min && $domain_length <= $length_max;
                
                return $type_match && $da_match && $traffic_match && $contains_match && $length_match;
            });
        }

        // Prepare data for JSON response
        $prepared_data = array();
        foreach ($data as $row) {
            $prepared_data[] = array(
                'id' => intval($row['id']),
                'type' => esc_html($row['type']),
                'domain' => esc_html($row['domain']),
                'da' => intval($row['da']),
                'traffic' => intval($row['traffic']),
                'traffic_formatted' => number_format($row['traffic']),
                'age' => intval($row['age']),
                'emd' => intval($row['emd']),
                'emd_display' => $row['emd'] ? __('Yes', 'domain-data-manager') : __('No', 'domain-data-manager'),
                'da_class' => $this->get_da_class(intval($row['da'])),
                'type_class' => sanitize_html_class(strtolower($row['type'])),
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at']
            );
        }

        // Calculate stats for the filtered results
        $stats = array(
            'total_domains' => count($prepared_data),
            'avg_da' => !empty($prepared_data) ? round(array_sum(array_column($prepared_data, 'da')) / count($prepared_data)) : 0,
            'total_traffic' => array_sum(array_column($prepared_data, 'traffic')),
            'emd_count' => count(array_filter($prepared_data, function($row) { return $row['emd']; }))
        );

        wp_send_json_success(array(
            'data' => array_values($prepared_data), // Re-index array after filtering
            'stats' => $stats,
            'total' => count($prepared_data)
        ));
    }

    /**
     * Get DA class based on value.
     *
     * @since 1.0.0
     * @param int $da Domain Authority value.
     * @return string CSS class name.
     */
    private function get_da_class($da) {
        if ($da >= 80) {
            return 'excellent';
        } elseif ($da >= 60) {
            return 'great';
        } elseif ($da >= 40) {
            return 'good';
        } elseif ($da >= 20) {
            return 'fair';
        } else {
            return 'low';
        }
    }

    /**
     * Get domain types for filtering.
     *
     * @since 1.0.0
     */
    public function handle_get_domain_types() {
        check_ajax_referer( 'ddm_public_nonce', 'nonce' );

        try {
            $types = $this->db->get_domain_types();
            wp_send_json_success($types);
        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => __('Error loading domain types.', 'domain-data-manager')
            ));
        }
    }

    /**
     * Handle domain copy action.
     *
     * @since 1.0.0
     */
    public function handle_copy_domain() {
        check_ajax_referer( 'ddm_public_nonce', 'nonce' );

        $domain = isset($_POST['domain']) ? sanitize_text_field($_POST['domain']) : '';
        
        if (empty($domain)) {
            wp_send_json_error(array('message' => __('Invalid domain', 'domain-data-manager')));
            return;
        }

        // Validate domain format
        if (!$this->is_valid_domain($domain)) {
            wp_send_json_error(array('message' => __('Invalid domain format', 'domain-data-manager')));
            return;
        }

        // Log the copy action (optional analytics)
        do_action('ddm_domain_copied', $domain, get_current_user_id());

        wp_send_json_success(array('message' => __('Domain copied to clipboard', 'domain-data-manager')));
    }

    /**
     * Validate domain format.
     *
     * @since 1.0.0
     * @param string $domain Domain to validate.
     * @return bool True if valid domain format.
     */
    private function is_valid_domain($domain) {
        // Basic domain validation
        return filter_var('http://' . $domain, FILTER_VALIDATE_URL) !== false;
    }
}
