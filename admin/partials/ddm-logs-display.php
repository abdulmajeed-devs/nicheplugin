<?php
/**
 * Provide a admin area view for the Logs page
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Domain_Data_Manager
 * @subpackage Domain_Data_Manager/admin/partials
 */

// Ensure this file is loaded within WordPress admin
if (!defined("ABSPATH")) {
    exit;
}

// Instantiate Logger class to fetch logs
$logger = new Domain_Data_Manager_Logger();

// Pagination parameters
$per_page = 20;
$current_page = isset($_GET["paged"]) ? max(1, absint($_GET["paged"])) : 1;
$offset = ($current_page - 1) * $per_page;
$total_items = $logger->count_logs();
$total_pages = ceil($total_items / $per_page);

$logs = $logger->get_logs(["per_page" => $per_page, "offset" => $offset]);

?>

<div class="ddm-admin-wrap">
    <div class="ddm-header">
        <h1 class="ddm-title">
            <svg class="ddm-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14,2 14,8 20,8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10,9 9,9 8,9"></polyline>
            </svg>
            <?php echo esc_html(get_admin_page_title()); ?>
        </h1>
        <p class="ddm-subtitle"><?php _e("Track CSV upload activities and system events", "domain-data-manager"); ?></p>
    </div>

    <div class="ddm-stats-grid">
        <div class="ddm-stat-card">
            <div class="ddm-stat-icon ddm-stat-info">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14,2 14,8 20,8"></polyline>
                </svg>
            </div>
            <div class="ddm-stat-content">
                <div class="ddm-stat-number"><?php echo esc_html($total_items); ?></div>
                <div class="ddm-stat-label"><?php _e("Total Logs", "domain-data-manager"); ?></div>
            </div>
        </div>
    </div>

    <div class="ddm-table-card">
        <div class="ddm-table-header">
            <h3 class="ddm-table-title">Activity Logs</h3>
            <div class="ddm-table-info">
                <span class="ddm-count"><?php printf(_n('%s entry', '%s entries', $total_items, 'domain-data-manager'), number_format_i18n($total_items)); ?></span>
            </div>
        </div>
        <div class="ddm-table-container">
            <table class="ddm-table ddm-logs-table">
                <thead>
                    <tr>
                        <th class="ddm-th"><?php _e("Log ID", "domain-data-manager"); ?></th>
                        <th class="ddm-th"><?php _e("User", "domain-data-manager"); ?></th>
                        <th class="ddm-th"><?php _e("Timestamp", "domain-data-manager"); ?></th>
                        <th class="ddm-th"><?php _e("Inserted", "domain-data-manager"); ?></th>
                        <th class="ddm-th"><?php _e("Updated", "domain-data-manager"); ?></th>
                        <th class="ddm-th"><?php _e("Status", "domain-data-manager"); ?></th>
                        <th class="ddm-th"><?php _e("Message", "domain-data-manager"); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($logs)) : ?>
                        <?php foreach ($logs as $log) : ?>
                            <tr class="ddm-tr">
                                <td class="ddm-td ddm-td-mono">#<?php echo esc_html($log["log_id"]); ?></td>
                                <td class="ddm-td">
                                    <div class="ddm-user-info">
                                        <div class="ddm-user-avatar">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                                <circle cx="12" cy="7" r="4"></circle>
                                            </svg>
                                        </div>
                                        <span><?php echo esc_html($log["user_name"] ?? "N/A"); ?></span>
                                    </div>
                                </td>
                                <td class="ddm-td ddm-td-timestamp">
                                    <?php echo esc_html(date('M j, Y \a\t H:i', strtotime($log["upload_timestamp"]))); ?>
                                </td>
                                <td class="ddm-td">
                                    <span class="ddm-number-badge ddm-insert-count">
                                        <?php echo esc_html($log["records_inserted"]); ?>
                                    </span>
                                </td>
                                <td class="ddm-td">
                                    <span class="ddm-number-badge ddm-update-count">
                                        <?php echo esc_html($log["records_updated"]); ?>
                                    </span>
                                </td>
                                <td class="ddm-td">
                                    <span class="ddm-status-badge ddm-status-<?php echo esc_attr($log["status"]); ?>">
                                        <?php if ($log["status"] === 'success'): ?>
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="20,6 9,17 4,12"></polyline>
                                            </svg>
                                        <?php else: ?>
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <line x1="15" y1="9" x2="9" y2="15"></line>
                                                <line x1="9" y1="9" x2="15" y2="15"></line>
                                            </svg>
                                        <?php endif; ?>
                                        <?php echo esc_html(ucfirst($log["status"])); ?>
                                    </span>
                                </td>
                                <td class="ddm-td ddm-td-message">
                                    <?php if (!empty($log["message"])): ?>
                                        <div class="ddm-message-text" title="<?php echo esc_attr($log["message"]); ?>">
                                            <?php echo esc_html(wp_trim_words($log["message"], 10, '...')); ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="ddm-text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr class="ddm-no-items">
                            <td class="ddm-td" colspan="7">
                                <div class="ddm-empty-state">
                                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14,2 14,8 20,8"></polyline>
                                    </svg>
                                    <h4><?php _e("No logs found", "domain-data-manager"); ?></h4>
                                    <p><?php _e("Upload activities and system events will appear here.", "domain-data-manager"); ?></p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1) : ?>
            <div class="ddm-pagination-wrap">
                <div class="ddm-pagination">
                    <?php
                    echo paginate_links(array(
                        "base" => add_query_arg("paged", "%#%"),
                        "format" => "",
                        "prev_text" => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15,18 9,12 15,6"></polyline></svg>' . __("Previous"),
                        "next_text" => __("Next") . '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9,18 15,12 9,6"></polyline></svg>',
                        "total" => $total_pages,
                        "current" => $current_page,
                        "mid_size" => 2,
                        "end_size" => 1,
                    ));
                    ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
