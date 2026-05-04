<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Domain_Data_Manager
 * @subpackage Domain_Data_Manager/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for the admin area functionality
 * of the site.
 *
 * @package    Domain_Data_Manager
 * @subpackage Domain_Data_Manager/admin
 * @author     Manus AI <your-name@example.com>
 */
class Domain_Data_Manager_Admin {

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
     * Logger instance.
     *
     * @since    1.0.0
     * @access   private
     * @var      Domain_Data_Manager_Logger $logger Logger instance.
     */
    private $logger;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->db = new Domain_Data_Manager_Db();
        $this->logger = new Domain_Data_Manager_Logger();

    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style( $this->plugin_name, DDM_PLUGIN_URL . 'assets/css/ddm-admin.css', array(), $this->version, 'all' );
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script( $this->plugin_name, DDM_PLUGIN_URL . 'assets/js/ddm-admin.js', array( 'jquery' ), $this->version, true );

        // Localize script with data for AJAX
        wp_localize_script( $this->plugin_name, 'ddm_admin_ajax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'ddm_admin_nonce' ),
            'strings'  => array(
                'confirm_delete' => __( 'Are you sure you want to delete this entry?', 'domain-data-manager' ),
                'processing'     => __( 'Processing...', 'domain-data-manager' ),
                'error'          => __( 'An error occurred. Please try again.', 'domain-data-manager' ),
                'success'        => __( 'Operation completed successfully.', 'domain-data-manager' ),
                'upload_success' => __( 'CSV uploaded successfully!', 'domain-data-manager' ),
                'upload_error'   => __( 'Upload failed. Please check your file and try again.', 'domain-data-manager' ),
            )
        ));

        // Enqueue Chart.js for dashboard
        if (isset($_GET['page']) && $_GET['page'] === 'ddm-dashboard') {
            wp_enqueue_script( 'chart-js', 'https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js', array(), '3.9.1', true );
        }
    }

    /**
     * Add plugin admin menu.
     *
     * @since    1.0.0
     */
    public function add_plugin_admin_menu() {
        // Main menu page
        add_menu_page(
            __( 'Domain Data Manager', 'domain-data-manager' ),
            __( 'Domain Data', 'domain-data-manager' ),
            'manage_options',
            'domain-data-manager',
            array( $this, 'display_plugin_admin_page' ),
            'dashicons-networking',
            30
        );

        // Dashboard submenu
        add_submenu_page(
            'domain-data-manager',
            __( 'Dashboard', 'domain-data-manager' ),
            __( 'Dashboard', 'domain-data-manager' ),
            'manage_options',
            'ddm-dashboard',
            array( $this, 'display_dashboard_page' )
        );

        // Rename the first submenu item to "Manage Data"
        add_submenu_page(
            'domain-data-manager',
            __( 'Manage Data', 'domain-data-manager' ),
            __( 'Manage Data', 'domain-data-manager' ),
            'manage_options',
            'domain-data-manager',
            array( $this, 'display_plugin_admin_page' )
        );

        // CSV Upload submenu
        add_submenu_page(
            'domain-data-manager',
            __( 'Upload CSV', 'domain-data-manager' ),
            __( 'Upload CSV', 'domain-data-manager' ),
            'manage_options',
            'ddm-upload-csv',
            array( $this, 'display_csv_upload_page' )
        );

        // Logs submenu
        add_submenu_page(
            'domain-data-manager',
            __( 'Activity Logs', 'domain-data-manager' ),
            __( 'Activity Logs', 'domain-data-manager' ),
            'manage_options',
            'ddm-logs',
            array( $this, 'display_logs_page' )
        );

        // Settings submenu
        add_submenu_page(
            'domain-data-manager',
            __( 'Settings', 'domain-data-manager' ),
            __( 'Settings', 'domain-data-manager' ),
            'manage_options',
            'ddm-settings',
            array( $this, 'display_settings_page' )
        );
    }

    /**
     * Render the admin page for this plugin.
     *
     * @since    1.0.0
     */
    public function display_plugin_admin_page() {
        require_once DDM_PLUGIN_DIR . 'admin/partials/ddm-admin-display.php';
    }

    /**
     * Render the dashboard page.
     *
     * @since    1.0.0
     */
    public function display_dashboard_page() {
        require_once DDM_PLUGIN_DIR . 'admin/partials/ddm-dashboard-display.php';
    }

    /**
     * Render the CSV upload page.
     *
     * @since    1.0.0
     */
    public function display_csv_upload_page() {
        require_once DDM_PLUGIN_DIR . 'admin/partials/ddm-upload-csv.php';
    }

    /**
     * Render the logs page.
     *
     * @since    1.0.0
     */
    public function display_logs_page() {
        require_once DDM_PLUGIN_DIR . 'admin/partials/ddm-logs-display.php';
    }

    /**
     * Render the settings page.
     *
     * @since    1.0.0
     */
    public function display_settings_page() {
        require_once DDM_PLUGIN_DIR . 'admin/partials/ddm-settings-display.php';
    }

    /**
     * Register settings.
     *
     * @since    1.0.0
     */
    public function register_settings() {
        // Register settings
        register_setting(
            'ddm_settings_group',
            'ddm_table_color_scheme',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_hex_color',
                'default' => '#f8f9fa'
            )
        );

        register_setting(
            'ddm_settings_group',
            'ddm_table_borders',
            array(
                'type' => 'boolean',
                'sanitize_callback' => 'rest_sanitize_boolean',
                'default' => true
            )
        );

        register_setting(
            'ddm_settings_group',
            'ddm_table_row_styling',
            array(
                'type' => 'boolean',
                'sanitize_callback' => 'rest_sanitize_boolean',
                'default' => false
            )
        );

        // Add settings section
        add_settings_section(
            'ddm_table_appearance',
            __( 'Table Appearance Settings', 'domain-data-manager' ),
            array( $this, 'settings_section_callback' ),
            'ddm_settings_group'
        );

        // Add settings fields
        add_settings_field(
            'ddm_table_color_scheme',
            __( 'Header Color Scheme', 'domain-data-manager' ),
            array( $this, 'color_scheme_field_callback' ),
            'ddm_settings_group',
            'ddm_table_appearance'
        );

        add_settings_field(
            'ddm_table_borders',
            __( 'Show Table Borders', 'domain-data-manager' ),
            array( $this, 'borders_field_callback' ),
            'ddm_settings_group',
            'ddm_table_appearance'
        );

        add_settings_field(
            'ddm_table_row_styling',
            __( 'Alternating Row Colors', 'domain-data-manager' ),
            array( $this, 'row_styling_field_callback' ),
            'ddm_settings_group',
            'ddm_table_appearance'
        );
    }

    /**
     * Settings section callback.
     *
     * @since    1.0.0
     */
    public function settings_section_callback() {
        echo '<p>' . __( 'Configure how your domain data table appears on the frontend.', 'domain-data-manager' ) . '</p>';
    }

    /**
     * Color scheme field callback.
     *
     * @since    1.0.0
     */
    public function color_scheme_field_callback() {
        $value = get_option( 'ddm_table_color_scheme', '#f8f9fa' );
        echo '<div class="ddm-form-group">';
        echo '<input type="color" id="ddm_table_color_scheme" name="ddm_table_color_scheme" value="' . esc_attr( $value ) . '" class="ddm-color-input">';
        echo '<p class="ddm-field-description">' . __( 'Choose the background color for table headers.', 'domain-data-manager' ) . '</p>';
        echo '</div>';
    }

    /**
     * Borders field callback.
     *
     * @since    1.0.0
     */
    public function borders_field_callback() {
        $value = get_option( 'ddm_table_borders', '1' );
        echo '<div class="ddm-form-group">';
        echo '<label class="ddm-checkbox-wrapper">';
        echo '<input type="checkbox" id="ddm_table_borders" name="ddm_table_borders" value="1" ' . checked( 1, $value, false ) . ' class="ddm-checkbox">';
        echo '<span class="ddm-checkbox-mark"></span>';
        echo '<span class="ddm-checkbox-label">' . __( 'Display borders around table cells', 'domain-data-manager' ) . '</span>';
        echo '</label>';
        echo '</div>';
    }

    /**
     * Row styling field callback.
     *
     * @since    1.0.0
     */
    public function row_styling_field_callback() {
        $value = get_option( 'ddm_table_row_styling', '0' );
        echo '<div class="ddm-form-group">';
        echo '<label class="ddm-checkbox-wrapper">';
        echo '<input type="checkbox" id="ddm_table_row_styling" name="ddm_table_row_styling" value="1" ' . checked( 1, $value, false ) . ' class="ddm-checkbox">';
        echo '<span class="ddm-checkbox-mark"></span>';
        echo '<span class="ddm-checkbox-label">' . __( 'Apply alternating background colors to rows', 'domain-data-manager' ) . '</span>';
        echo '</label>';
        echo '</div>';
    }

    /**
     * Handle CSV upload via AJAX.
     *
     * @since    1.0.0
     */
    public function handle_csv_upload() {
        check_ajax_referer( 'ddm_admin_nonce', 'ddm_upload_nonce_field' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'domain-data-manager' ) ) );
        }

        if ( ! isset( $_FILES['csv_file'] ) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK ) {
            wp_send_json_error( array( 'message' => __( 'No file uploaded or upload error.', 'domain-data-manager' ) ) );
        }

        $uploaded_file = $_FILES['csv_file'];
        $import_mode = isset( $_POST['import_mode'] ) ? sanitize_text_field( $_POST['import_mode'] ) : 'append';

        // Validate file type
        if ( pathinfo( $uploaded_file['name'], PATHINFO_EXTENSION ) !== 'csv' ) {
            wp_send_json_error( array( 'message' => __( 'Please upload a valid CSV file.', 'domain-data-manager' ) ) );
        }

        // Process CSV
        $result = $this->process_csv_file( $uploaded_file['tmp_name'], $import_mode );

        if ( $result['success'] ) {
            // Log the upload
            $this->logger->log_upload(
                $result['inserted'],
                $result['updated'],
                'success',
                sprintf( __( 'CSV processed successfully. %d inserted, %d updated.', 'domain-data-manager' ), $result['inserted'], $result['updated'] )
            );

            wp_send_json_success( array(
                'message' => sprintf(
                    __( 'CSV processed successfully! %d records inserted, %d records updated.', 'domain-data-manager' ),
                    $result['inserted'],
                    $result['updated']
                ),
                'inserted' => $result['inserted'],
                'updated' => $result['updated']
            ) );
        } else {
            // Log the failure
            $this->logger->log_upload( 0, 0, 'failure', $result['error'] );
            wp_send_json_error( array( 'message' => $result['error'] ) );
        }
    }

    /**
     * Process CSV file.
     *
     * @since    1.0.0
     * @param    string $file_path Path to uploaded CSV file.
     * @param    string $mode Import mode ('append' or 'update').
     * @return   array Processing result.
     */
    private function process_csv_file( $file_path, $mode = 'append' ) {
        $inserted_count = 0;
        $updated_count = 0;
        $errors = array();

        if ( ( $handle = fopen( $file_path, 'r' ) ) !== FALSE ) {
            $header = fgetcsv( $handle );
            
            // Validate header
            $expected_headers = array( 'type', 'domain', 'da', 'traffic', 'age', 'emd' );
            $normalized_header = array_map( 'strtolower', array_map( 'trim', $header ) );
            
            foreach ( $expected_headers as $expected ) {
                if ( ! in_array( $expected, $normalized_header ) ) {
                    return array(
                        'success' => false,
                        'error' => sprintf( __( 'Missing required column: %s', 'domain-data-manager' ), $expected )
                    );
                }
            }

            $row_number = 1;
            while ( ( $data = fgetcsv( $handle ) ) !== FALSE ) {
                $row_number++;
                
                if ( count( $data ) < count( $expected_headers ) ) {
                    $errors[] = sprintf( __( 'Row %d: Insufficient columns', 'domain-data-manager' ), $row_number );
                    continue;
                }

                // Map data to associative array
                $row_data = array();
                foreach ( $expected_headers as $index => $header ) {
                    $header_index = array_search( $header, $normalized_header );
                    $row_data[$header] = isset( $data[$header_index] ) ? trim( $data[$header_index] ) : '';
                }

                // Validate required fields
                if ( empty( $row_data['domain'] ) ) {
                    $errors[] = sprintf( __( 'Row %d: Domain is required', 'domain-data-manager' ), $row_number );
                    continue;
                }

                // Sanitize and validate data
                $clean_data = array(
                    'type'    => sanitize_text_field( $row_data['type'] ),
                    'domain'  => sanitize_text_field( $row_data['domain'] ),
                    'da'      => max( 0, min( 100, intval( $row_data['da'] ) ) ),
                    'traffic' => max( 0, intval( $row_data['traffic'] ) ),
                    'age'     => max( 0, intval( $row_data['age'] ) ),
                    'emd'     => in_array( strtolower( $row_data['emd'] ), array( '1', 'true', 'yes' ) ) ? 1 : 0
                );

                // Check if domain exists
                $existing = $this->db->get_data_by_domain( $clean_data['domain'] );

                if ( $existing ) {
                    if ( $mode === 'update' ) {
                        if ( $this->db->update_data_by_domain( $clean_data['domain'], $clean_data ) ) {
                            $updated_count++;
                        } else {
                            $errors[] = sprintf( __( 'Row %d: Failed to update domain %s', 'domain-data-manager' ), $row_number, $clean_data['domain'] );
                        }
                    }
                    // Skip if mode is 'append'
                } else {
                    if ( $this->db->insert_data( $clean_data ) ) {
                        $inserted_count++;
                    } else {
                        $errors[] = sprintf( __( 'Row %d: Failed to insert domain %s', 'domain-data-manager' ), $row_number, $clean_data['domain'] );
                    }
                }
            }
            fclose( $handle );
        } else {
            return array(
                'success' => false,
                'error' => __( 'Could not read the uploaded file.', 'domain-data-manager' )
            );
        }

        return array(
            'success'  => true,
            'inserted' => $inserted_count,
            'updated'  => $updated_count,
            'errors'   => $errors
        );
    }

    /**
     * Handle add data via AJAX.
     *
     * @since    1.0.0
     */
    public function handle_add_data() {
        check_ajax_referer( 'ddm_admin_nonce', 'ddm_nonce_field' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'domain-data-manager' ) ) );
        }

        $data = array(
            'type'    => sanitize_text_field( $_POST['type'] ),
            'domain'  => sanitize_text_field( $_POST['domain'] ),
            'da'      => max( 0, min( 100, intval( $_POST['da'] ) ) ),
            'traffic' => max( 0, intval( $_POST['traffic'] ) ),
            'age'     => max( 0, intval( $_POST['age'] ) ),
            'emd'     => isset( $_POST['emd'] ) ? 1 : 0
        );

        // Check if domain already exists
        $existing = $this->db->get_data_by_domain( $data['domain'] );
        if ( $existing ) {
            wp_send_json_error( array( 'message' => __( 'Domain already exists.', 'domain-data-manager' ) ) );
        }

        $result = $this->db->insert_data( $data );

        if ( $result ) {
            wp_send_json_success( array( 
                'message' => __( 'Data added successfully.', 'domain-data-manager' ),
                'id' => $result
            ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Failed to add data.', 'domain-data-manager' ) ) );
        }
    }

    /**
     * Handle update data via AJAX.
     *
     * @since    1.0.0
     */
    public function handle_update_data() {
        check_ajax_referer( 'ddm_admin_nonce', 'ddm_nonce_field' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'domain-data-manager' ) ) );
        }

        $id = intval( $_POST['id'] );
        $data = array(
            'type'    => sanitize_text_field( $_POST['type'] ),
            'domain'  => sanitize_text_field( $_POST['domain'] ),
            'da'      => max( 0, min( 100, intval( $_POST['da'] ) ) ),
            'traffic' => max( 0, intval( $_POST['traffic'] ) ),
            'age'     => max( 0, intval( $_POST['age'] ) ),
            'emd'     => isset( $_POST['emd'] ) ? 1 : 0
        );

        $result = $this->db->update_data( $id, $data );

        if ( $result !== false ) {
            wp_send_json_success( array( 'message' => __( 'Data updated successfully.', 'domain-data-manager' ) ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Failed to update data.', 'domain-data-manager' ) ) );
        }
    }

    /**
     * Handle delete data via AJAX.
     *
     * @since    1.0.0
     */
    public function handle_delete_data() {
        check_ajax_referer( 'ddm_admin_nonce', 'ddm_nonce_field' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'domain-data-manager' ) ) );
        }

        $id = intval( $_POST['id'] );
        $result = $this->db->delete_data( $id );

        if ( $result ) {
            wp_send_json_success( array( 'message' => __( 'Data deleted successfully.', 'domain-data-manager' ) ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Failed to delete data.', 'domain-data-manager' ) ) );
        }
    }

    /**
     * Handle get data via AJAX (for editing).
     *
     * @since    1.0.0
     */
    public function handle_get_data() {
        check_ajax_referer( 'ddm_admin_nonce', 'ddm_nonce_field' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'domain-data-manager' ) ) );
        }

        $id = intval( $_POST['id'] );
        $data = $this->db->get_data_by_id( $id );

        if ( $data ) {
            wp_send_json_success( $data );
        } else {
            wp_send_json_error( array( 'message' => __( 'Data not found.', 'domain-data-manager' ) ) );
        }
    }

    /**
     * Handle export data.
     *
     * @since    1.0.0
     */
    public function handle_export_data() {
        if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( $_GET['nonce'], 'ddm_admin_nonce' ) ) {
            wp_die( __( 'Security check failed.', 'domain-data-manager' ) );
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'Insufficient permissions.', 'domain-data-manager' ) );
        }

        $data = $this->db->get_data( array( 'per_page' => -1 ) );

        if ( empty( $data ) ) {
            wp_die( __( 'No data to export.', 'domain-data-manager' ) );
        }

        // Set headers for CSV download
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=domain-data-export-' . date( 'Y-m-d' ) . '.csv' );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );

        // Create file pointer connected to output stream
        $output = fopen( 'php://output', 'w' );

        // Output CSV headers
        fputcsv( $output, array( 'Type', 'Domain', 'DA', 'Traffic', 'Age', 'EMD' ) );

        // Output data
        foreach ( $data as $row ) {
            fputcsv( $output, array(
                $row['type'],
                $row['domain'],
                $row['da'],
                $row['traffic'],
                $row['age'],
                $row['emd'] ? 'Yes' : 'No'
            ) );
        }

        fclose( $output );
        exit();
    }
}
