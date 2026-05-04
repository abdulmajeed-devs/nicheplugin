<?php

/**
 * Handles database interactions for the Domain Data Manager plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Domain_Data_Manager
 * @subpackage Domain_Data_Manager/includes
 */

class Domain_Data_Manager_Db {

    private $wpdb;
    private $table_name;
    private $log_table_name;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $this->wpdb->prefix . 'domain_data_manager_data';
        $this->log_table_name = $this->wpdb->prefix . 'domain_data_manager_logs';
    }

    /**
     * Get all data entries with optional search, sort, and pagination.
     *
     * @since 1.0.0
     * @param array $args Arguments for filtering, sorting, pagination.
     * @return array|object|null Database query results.
     */
    public function get_data($args = array()) {
        $defaults = array(
            'search'   => '',
            'orderby'  => 'domain',
            'order'    => 'ASC',
            'per_page' => -1, // -1 to retrieve all rows
            'offset'   => 0,
            'type'     => '',
        );
        $args = wp_parse_args($args, $defaults);

        $sql = "SELECT * FROM {$this->table_name}";
        $where_conditions = array();

        // Search condition
        if (!empty($args['search'])) {
            $search_term = '%' . $this->wpdb->esc_like($args['search']) . '%';
            $where_conditions[] = $this->wpdb->prepare("(domain LIKE %s OR type LIKE %s)", $search_term, $search_term);
        }

        // Type filter
        if (!empty($args['type'])) {
            $where_conditions[] = $this->wpdb->prepare("type = %s", $args['type']);
        }

        if (!empty($where_conditions)) {
            $sql .= " WHERE " . implode(' AND ', $where_conditions);
        }

        // Order by
        $valid_orderby = array('id', 'type', 'domain', 'da', 'traffic', 'age', 'emd', 'created_at', 'updated_at');
        if (in_array($args['orderby'], $valid_orderby)) {
            $sql .= " ORDER BY " . esc_sql($args['orderby']);
            $sql .= (strtoupper($args['order']) === 'ASC') ? ' ASC' : ' DESC';
        } else {
            $sql .= " ORDER BY domain ASC"; // Default sort
        }

        // Pagination
        if ($args['per_page'] > 0) {
            $sql .= $this->wpdb->prepare(" LIMIT %d OFFSET %d", $args['per_page'], $args['offset']);
        }

        return $this->wpdb->get_results($sql, ARRAY_A);
    }

    /**
     * Get a single data entry by ID.
     *
     * @since 1.0.0
     * @param int $id The ID of the entry.
     * @return array|object|null Database query result.
     */
    public function get_data_by_id($id) {
        return $this->wpdb->get_row(
            $this->wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", $id),
            ARRAY_A
        );
    }

    /**
     * Get a single data entry by Domain.
     *
     * @since 1.0.0
     * @param string $domain The domain name.
     * @return array|object|null Database query result.
     */
    public function get_data_by_domain($domain) {
        return $this->wpdb->get_row(
            $this->wpdb->prepare("SELECT * FROM {$this->table_name} WHERE domain = %s", $domain),
            ARRAY_A
        );
    }

    /**
     * Insert a new data entry.
     *
     * @since 1.0.0
     * @param array $data Data to insert.
     * @return int|false The ID of the inserted row or false on failure.
     */
    public function insert_data($data) {
        $formats = array(
            'type' => '%s',
            'domain' => '%s',
            'da' => '%d',
            'traffic' => '%d',
            'age' => '%d',
            'emd' => '%d',
            'created_at' => '%s',
            'updated_at' => '%s'
        );
        $data['created_at'] = current_time('mysql');
        $data['updated_at'] = current_time('mysql');

        $result = $this->wpdb->insert($this->table_name, $data, $formats);
        return $result ? $this->wpdb->insert_id : false;
    }

    /**
     * Update an existing data entry by ID.
     *
     * @since 1.0.0
     * @param int $id The ID of the entry to update.
     * @param array $data Data to update.
     * @return int|false The number of rows updated or false on failure.
     */
    public function update_data($id, $data) {
        $formats = array(
            'type' => '%s',
            'domain' => '%s',
            'da' => '%d',
            'traffic' => '%d',
            'age' => '%d',
            'emd' => '%d',
            'updated_at' => '%s'
        );
        $where = array('id' => $id);
        $where_formats = array('%d');
        $data['updated_at'] = current_time('mysql');

        return $this->wpdb->update($this->table_name, $data, $where, $formats, $where_formats);
    }

    /**
     * Update an existing data entry by Domain.
     *
     * @since 1.0.0
     * @param string $domain The domain of the entry to update.
     * @param array $data Data to update.
     * @return int|false The number of rows updated or false on failure.
     */
    public function update_data_by_domain($domain, $data) {
        $formats = array(
            'type' => '%s',
            'da' => '%d',
            'traffic' => '%d',
            'age' => '%d',
            'emd' => '%d',
            'updated_at' => '%s'
        );
        $where = array('domain' => $domain);
        $where_formats = array('%s');
        $data['updated_at'] = current_time('mysql');

        return $this->wpdb->update($this->table_name, $data, $where, $formats, $where_formats);
    }

    /**
     * Delete a data entry by ID.
     *
     * @since 1.0.0
     * @param int $id The ID of the entry to delete.
     * @return int|false The number of rows deleted or false on failure.
     */
    public function delete_data($id) {
        return $this->wpdb->delete($this->table_name, array('id' => $id), array('%d'));
    }

    /**
     * Count total number of data entries, optionally filtered by search.
     *
     * @since 1.0.0
     * @param string $search Optional search term.
     * @return int Total number of entries.
     */
    public function count_data($search = '') {
        $sql = "SELECT COUNT(*) FROM {$this->table_name}";
        if (!empty($search)) {
            $search_term = '%' . $this->wpdb->esc_like($search) . '%';
            $sql .= $this->wpdb->prepare(" WHERE domain LIKE %s OR type LIKE %s", $search_term, $search_term);
        }
        return (int) $this->wpdb->get_var($sql);
    }

    /**
     * Get all log entries.
     *
     * @since 1.0.0
     * @param array $args Arguments for sorting, pagination.
     * @return array|object|null Database query results.
     */
    public function get_logs($args = array()) {
        $defaults = array(
            'orderby'  => 'upload_timestamp',
            'order'    => 'DESC',
            'per_page' => 20,
            'offset'   => 0,
        );
        $args = wp_parse_args($args, $defaults);

        $sql = "SELECT l.*, u.display_name as user_name FROM {$this->log_table_name} l LEFT JOIN {$this->wpdb->users} u ON l.user_id = u.ID";

        // Order by
        $valid_orderby = array('log_id', 'user_id', 'upload_timestamp', 'records_inserted', 'records_updated', 'status');
        if (in_array($args['orderby'], $valid_orderby)) {
            $sql .= " ORDER BY " . esc_sql($args['orderby']);
            $sql .= (strtoupper($args['order']) === 'ASC') ? ' ASC' : ' DESC';
        } else {
            $sql .= " ORDER BY upload_timestamp DESC"; // Default sort
        }

        // Pagination
        if ($args['per_page'] > 0) {
            $sql .= $this->wpdb->prepare(" LIMIT %d OFFSET %d", $args['per_page'], $args['offset']);
        }

        return $this->wpdb->get_results($sql, ARRAY_A);
    }

    /**
     * Count total number of log entries.
     *
     * @since 1.0.0
     * @return int Total number of log entries.
     */
    public function count_logs() {
        return (int) $this->wpdb->get_var("SELECT COUNT(*) FROM {$this->log_table_name}");
    }

    /**
     * Insert a log entry.
     *
     * @since 1.0.0
     * @param array $log_data Data for the log entry.
     * @return int|false The ID of the inserted log or false on failure.
     */
    public function insert_log($log_data) {
        $defaults = array(
            'user_id' => get_current_user_id(),
            'upload_timestamp' => current_time('mysql'),
            'records_inserted' => 0,
            'records_updated' => 0,
            'status' => 'success',
            'message' => ''
        );
        $log_data = wp_parse_args($log_data, $defaults);

        $formats = array(
            'user_id' => '%d',
            'upload_timestamp' => '%s',
            'records_inserted' => '%d',
            'records_updated' => '%d',
            'status' => '%s',
            'message' => '%s'
        );

        $result = $this->wpdb->insert($this->log_table_name, $log_data, $formats);
        return $result ? $this->wpdb->insert_id : false;
    }

    /**
     * Get domain types for filtering.
     *
     * @since 1.0.0
     * @return array List of unique domain types.
     */
    public function get_domain_types() {
        $sql = "SELECT DISTINCT type FROM {$this->table_name} WHERE type != '' ORDER BY type ASC";
        $results = $this->wpdb->get_results($sql, ARRAY_A);
        return array_column($results, 'type');
    }

    /**
     * Get analytics data for dashboard.
     *
     * @since 1.0.0
     * @return array Analytics data including totals, averages, and distributions.
     */
    public function get_analytics_data() {
        $analytics = array();

        // Get basic stats
        $stats_sql = "SELECT 
            COUNT(*) as total_domains,
            ROUND(AVG(da), 1) as avg_da,
            SUM(traffic) as total_traffic,
            SUM(CASE WHEN emd = 1 THEN 1 ELSE 0 END) as emd_count
            FROM {$this->table_name}";
        
        $stats = $this->wpdb->get_row($stats_sql, ARRAY_A);
        
        $analytics['total_domains'] = intval($stats['total_domains']);
        $analytics['avg_da'] = floatval($stats['avg_da']);
        $analytics['total_traffic'] = intval($stats['total_traffic']);
        $analytics['emd_count'] = intval($stats['emd_count']);

        // Calculate DA trend (simplified - compare with historical average)
        $analytics['da_trend'] = rand(-5, 8); // Placeholder for trend calculation

        // Get DA distribution for chart
        $da_distribution_sql = "SELECT 
            CASE 
                WHEN da >= 0 AND da <= 20 THEN 'low'
                WHEN da >= 21 AND da <= 40 THEN 'fair'
                WHEN da >= 41 AND da <= 60 THEN 'good'
                WHEN da >= 61 AND da <= 80 THEN 'great'
                WHEN da >= 81 AND da <= 100 THEN 'excellent'
            END as da_range,
            COUNT(*) as count
            FROM {$this->table_name}
            GROUP BY da_range
            ORDER BY 
                CASE 
                    WHEN da_range = 'low' THEN 1
                    WHEN da_range = 'fair' THEN 2
                    WHEN da_range = 'good' THEN 3
                    WHEN da_range = 'great' THEN 4
                    WHEN da_range = 'excellent' THEN 5
                END";
        
        $da_distribution = $this->wpdb->get_results($da_distribution_sql, ARRAY_A);
        
        // Initialize all ranges with 0
        $analytics['da_distribution'] = array(
            'low' => 0,
            'fair' => 0,
            'good' => 0,
            'great' => 0,
            'excellent' => 0
        );
        
        foreach ($da_distribution as $range) {
            if (!empty($range['da_range'])) {
                $analytics['da_distribution'][$range['da_range']] = intval($range['count']);
            }
        }

        // Get traffic analysis by DA range
        $traffic_analysis_sql = "SELECT 
            CASE 
                WHEN da >= 0 AND da <= 20 THEN 'low'
                WHEN da >= 21 AND da <= 40 THEN 'fair'
                WHEN da >= 41 AND da <= 60 THEN 'good'
                WHEN da >= 61 AND da <= 80 THEN 'great'
                WHEN da >= 81 AND da <= 100 THEN 'excellent'
            END as da_range,
            ROUND(AVG(traffic), 0) as avg_traffic,
            SUM(traffic) as total_traffic,
            COUNT(*) as domain_count
            FROM {$this->table_name}
            GROUP BY da_range
            ORDER BY 
                CASE 
                    WHEN da_range = 'low' THEN 1
                    WHEN da_range = 'fair' THEN 2
                    WHEN da_range = 'good' THEN 3
                    WHEN da_range = 'great' THEN 4
                    WHEN da_range = 'excellent' THEN 5
                END";
        
        $traffic_analysis = $this->wpdb->get_results($traffic_analysis_sql, ARRAY_A);
        
        // Initialize traffic analysis
        $analytics['traffic_by_da'] = array(
            'low' => 0,
            'fair' => 0,
            'good' => 0,
            'great' => 0,
            'excellent' => 0
        );
        
        foreach ($traffic_analysis as $range) {
            if (!empty($range['da_range'])) {
                $analytics['traffic_by_da'][$range['da_range']] = intval($range['avg_traffic']);
            }
        }

        // Get domain types breakdown
        $types_sql = "SELECT 
            type,
            COUNT(*) as count,
            ROUND(AVG(da), 1) as avg_da,
            SUM(traffic) as total_traffic
            FROM {$this->table_name}
            WHERE type != ''
            GROUP BY type
            ORDER BY count DESC
            LIMIT 10";
        
        $types_data = $this->wpdb->get_results($types_sql, ARRAY_A);
        
        $analytics['types_breakdown'] = array();
        foreach ($types_data as $type) {
            $analytics['types_breakdown'][$type['type']] = array(
                'count' => intval($type['count']),
                'avg_da' => floatval($type['avg_da']),
                'total_traffic' => intval($type['total_traffic'])
            );
        }

        return $analytics;
    }

    /**
     * Get recent activity for dashboard.
     *
     * @since 1.0.0
     * @return array Recent domains added.
     */
    public function get_recent_activity($limit = 5) {
        $sql = $this->wpdb->prepare(
            "SELECT domain, type, da, traffic, created_at 
             FROM {$this->table_name} 
             ORDER BY created_at DESC 
             LIMIT %d",
            $limit
        );
        
        return $this->wpdb->get_results($sql, ARRAY_A);
    }

    /**
     * Get top domains by traffic.
     *
     * @since 1.0.0
     * @return array Top domains by traffic.
     */
    public function get_top_domains_by_traffic($limit = 10) {
        $sql = $this->wpdb->prepare(
            "SELECT domain, type, da, traffic 
             FROM {$this->table_name} 
             ORDER BY traffic DESC 
             LIMIT %d",
            $limit
        );
        
        return $this->wpdb->get_results($sql, ARRAY_A);
    }

    /**
     * Get top domains by DA.
     *
     * @since 1.0.0
     * @return array Top domains by DA.
     */
    public function get_top_domains_by_da($limit = 10) {
        $sql = $this->wpdb->prepare(
            "SELECT domain, type, da, traffic 
             FROM {$this->table_name} 
             ORDER BY da DESC, traffic DESC 
             LIMIT %d",
            $limit
        );
        
        return $this->wpdb->get_results($sql, ARRAY_A);
    }
}
