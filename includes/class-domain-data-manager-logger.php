<?php

/**
 * Handles logging for the Domain Data Manager plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Domain_Data_Manager
 * @subpackage Domain_Data_Manager/includes
 */

class Domain_Data_Manager_Logger {

    private $db;

    public function __construct() {
        $this->db = new Domain_Data_Manager_Db();
    }

    /**
     * Logs a CSV upload event.
     *
     * @since 1.0.0
     * @param int    $inserted Count of inserted records.
     * @param int    $updated  Count of updated records.
     * @param string $status   Status of the upload ("success" or "failure").
     * @param string $message  Optional message (e.g., error details).
     * @return int|false The ID of the inserted log or false on failure.
     */
    public function log_upload($inserted = 0, $updated = 0, $status = 'success', $message = '') {
        $log_data = array(
            'user_id'          => get_current_user_id(),
            'upload_timestamp' => current_time('mysql'),
            'records_inserted' => intval($inserted),
            'records_updated'  => intval($updated),
            'status'           => sanitize_text_field($status),
            'message'          => sanitize_textarea_field($message)
        );
        return $this->db->insert_log($log_data);
    }

    /**
     * Log a general event.
     *
     * @since 1.0.0
     * @param string $action The action performed.
     * @param string $status Status of the action.
     * @param string $message Optional message.
     * @param array $context Additional context data.
     * @return int|false The ID of the inserted log or false on failure.
     */
    public function log_event($action, $status = 'success', $message = '', $context = array()) {
        $log_data = array(
            'user_id'          => get_current_user_id(),
            'upload_timestamp' => current_time('mysql'),
            'records_inserted' => 0,
            'records_updated'  => 0,
            'status'           => sanitize_text_field($status),
            'message'          => sanitize_textarea_field($action . ': ' . $message)
        );

        // Add context to message if provided
        if (!empty($context)) {
            $log_data['message'] .= ' | Context: ' . wp_json_encode($context);
        }

        return $this->db->insert_log($log_data);
    }

    /**
     * Log data operations (add, update, delete).
     *
     * @since 1.0.0
     * @param string $operation The operation type (add, update, delete).
     * @param int $record_id The ID of the affected record.
     * @param array $data Optional data context.
     * @return int|false The ID of the inserted log or false on failure.
     */
    public function log_data_operation($operation, $record_id, $data = array()) {
        $message = sprintf(
            'Data %s operation for record ID: %d',
            $operation,
            $record_id
        );

        if (!empty($data['domain'])) {
            $message .= ' (Domain: ' . $data['domain'] . ')';
        }

        $log_data = array(
            'user_id'          => get_current_user_id(),
            'upload_timestamp' => current_time('mysql'),
            'records_inserted' => $operation === 'add' ? 1 : 0,
            'records_updated'  => $operation === 'update' ? 1 : 0,
            'status'           => 'success',
            'message'          => $message
        );

        return $this->db->insert_log($log_data);
    }

    /**
     * Get log entries.
     *
     * @since 1.0.0
     * @param array $args Arguments for filtering, sorting, pagination.
     * @return array|object|null Database query results.
     */
    public function get_logs($args = array()) {
        return $this->db->get_logs($args);
    }

    /**
     * Count total log entries.
     *
     * @since 1.0.0
     * @return int Total number of log entries.
     */
    public function count_logs() {
        return $this->db->count_logs();
    }

    /**
     * Clean up old logs (keep only recent entries).
     *
     * @since 1.0.0
     * @param int $days_to_keep Number of days to keep logs.
     * @return int Number of deleted log entries.
     */
    public function cleanup_old_logs($days_to_keep = 90) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'domain_data_manager_logs';
        
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$days_to_keep} days"));
        
        $result = $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$table_name} WHERE upload_timestamp < %s",
                $cutoff_date
            )
        );

        if ($result > 0) {
            $this->log_event(
                'log_cleanup',
                'success',
                sprintf('Cleaned up %d old log entries older than %d days', $result, $days_to_keep)
            );
        }

        return $result;
    }

    /**
     * Get log statistics.
     *
     * @since 1.0.0
     * @return array Log statistics.
     */
    public function get_log_stats() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'domain_data_manager_logs';
        
        $stats = $wpdb->get_row(
            "SELECT 
                COUNT(*) as total_logs,
                SUM(records_inserted) as total_inserted,
                SUM(records_updated) as total_updated,
                SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as successful_operations,
                SUM(CASE WHEN status = 'failure' THEN 1 ELSE 0 END) as failed_operations
             FROM {$table_name}",
            ARRAY_A
        );

        return array(
            'total_logs' => intval($stats['total_logs']),
            'total_inserted' => intval($stats['total_inserted']),
            'total_updated' => intval($stats['total_updated']),
            'successful_operations' => intval($stats['successful_operations']),
            'failed_operations' => intval($stats['failed_operations']),
            'success_rate' => $stats['total_logs'] > 0 ? round(($stats['successful_operations'] / $stats['total_logs']) * 100, 2) : 0
        );
    }
}
