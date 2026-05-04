<?php
/**
 * Provide a admin dashboard view for the plugin
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

// Get analytics data
$db = new Domain_Data_Manager_Db();
$analytics = $db->get_analytics_data();

?>

<div class="ddm-admin-wrap">
    <div class="ddm-header">
        <h1 class="ddm-title">
            <svg class="ddm-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="20" x2="18" y2="10"></line>
                <line x1="12" y1="20" x2="12" y2="4"></line>
                <line x1="6" y1="20" x2="6" y2="14"></line>
            </svg>
            <?php echo esc_html(get_admin_page_title()); ?>
        </h1>
        <p class="ddm-subtitle"><?php _e("Overview of your domain data and analytics", "domain-data-manager"); ?></p>
    </div>

    <!-- Stats Overview -->
    <div class="ddm-stats-grid">
        <div class="ddm-stat-card">
            <div class="ddm-stat-icon ddm-stat-primary">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                </svg>
            </div>
            <div class="ddm-stat-content">
                <div class="ddm-stat-number"><?php echo number_format($analytics['total_domains']); ?></div>
                <div class="ddm-stat-label"><?php _e("Total Domains", "domain-data-manager"); ?></div>
                <div class="ddm-stat-change ddm-positive">+12% <?php _e("this month", "domain-data-manager"); ?></div>
            </div>
        </div>

        <div class="ddm-stat-card">
            <div class="ddm-stat-icon ddm-stat-success">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="22,12 18,12 15,21 9,3 6,12 2,12"></polyline>
                </svg>
            </div>
            <div class="ddm-stat-content">
                <div class="ddm-stat-number"><?php echo number_format($analytics['avg_da']); ?></div>
                <div class="ddm-stat-label"><?php _e("Average DA", "domain-data-manager"); ?></div>
                <div class="ddm-stat-change <?php echo $analytics['da_trend'] >= 0 ? 'ddm-positive' : 'ddm-negative'; ?>">
                    <?php echo ($analytics['da_trend'] >= 0 ? '+' : '') . $analytics['da_trend']; ?>% <?php _e("from last update", "domain-data-manager"); ?>
                </div>
            </div>
        </div>

        <div class="ddm-stat-card">
            <div class="ddm-stat-icon ddm-stat-info">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                    <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                </svg>
            </div>
            <div class="ddm-stat-content">
                <div class="ddm-stat-number"><?php echo number_format($analytics['total_traffic']); ?></div>
                <div class="ddm-stat-label"><?php _e("Total Traffic", "domain-data-manager"); ?></div>
                <div class="ddm-stat-change ddm-positive">+8% <?php _e("growth", "domain-data-manager"); ?></div>
            </div>
        </div>

        <div class="ddm-stat-card">
            <div class="ddm-stat-icon ddm-stat-warning">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
                </svg>
            </div>
            <div class="ddm-stat-content">
                <div class="ddm-stat-number"><?php echo $analytics['emd_count']; ?></div>
                <div class="ddm-stat-label"><?php _e("EMD Domains", "domain-data-manager"); ?></div>
                <div class="ddm-stat-percentage"><?php echo round(($analytics['emd_count'] / max($analytics['total_domains'], 1)) * 100, 1); ?>% <?php _e("of total", "domain-data-manager"); ?></div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="ddm-charts-grid">
        <div class="ddm-chart-card">
            <div class="ddm-card-header">
                <h3 class="ddm-card-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="20" x2="18" y2="10"></line>
                        <line x1="12" y1="20" x2="12" y2="4"></line>
                        <line x1="6" y1="20" x2="6" y2="14"></line>
                    </svg>
                    <?php _e("Domain Authority Distribution", "domain-data-manager"); ?>
                </h3>
                <div class="ddm-chart-legend">
                    <div class="ddm-legend-item">
                        <div class="ddm-legend-color" style="background: #ef4444;"></div>
                        <span><?php _e("Low (0-20)", "domain-data-manager"); ?></span>
                    </div>
                    <div class="ddm-legend-item">
                        <div class="ddm-legend-color" style="background: #f97316;"></div>
                        <span><?php _e("Fair (21-40)", "domain-data-manager"); ?></span>
                    </div>
                    <div class="ddm-legend-item">
                        <div class="ddm-legend-color" style="background: #eab308;"></div>
                        <span><?php _e("Good (41-60)", "domain-data-manager"); ?></span>
                    </div>
                    <div class="ddm-legend-item">
                        <div class="ddm-legend-color" style="background: #22c55e;"></div>
                        <span><?php _e("Great (61-80)", "domain-data-manager"); ?></span>
                    </div>
                    <div class="ddm-legend-item">
                        <div class="ddm-legend-color" style="background: #3b82f6;"></div>
                        <span><?php _e("Excellent (81-100)", "domain-data-manager"); ?></span>
                    </div>
                </div>
            </div>
            <div class="ddm-chart-container">
                <canvas id="ddm-da-chart" width="400" height="200"></canvas>
            </div>
        </div>

        <div class="ddm-chart-card">
            <div class="ddm-card-header">
                <h3 class="ddm-card-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="22,12 18,12 15,21 9,3 6,12 2,12"></polyline>
                    </svg>
                    <?php _e("Traffic Analysis by DA Range", "domain-data-manager"); ?>
                </h3>
                <div class="ddm-chart-info">
                    <p><?php _e("Average traffic volume across different DA ranges", "domain-data-manager"); ?></p>
                </div>
            </div>
            <div class="ddm-chart-container">
                <canvas id="ddm-traffic-chart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Domain Types Overview -->
    <div class="ddm-types-section">
        <div class="ddm-form-card">
            <div class="ddm-card-header">
                <h3 class="ddm-card-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                        <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                    </svg>
                    <?php _e("Domain Types Overview", "domain-data-manager"); ?>
                </h3>
            </div>
            <div class="ddm-card-content">
                <div class="ddm-types-grid">
                    <?php foreach ($analytics['types_breakdown'] as $type => $data): ?>
                    <div class="ddm-type-card">
                        <div class="ddm-type-header">
                            <span class="ddm-badge ddm-badge-<?php echo sanitize_html_class(strtolower($type)); ?>">
                                <?php echo esc_html($type); ?>
                            </span>
                            <span class="ddm-type-count"><?php echo $data['count']; ?></span>
                        </div>
                        <div class="ddm-type-stats">
                            <div class="ddm-type-stat">
                                <span class="ddm-stat-label"><?php _e("Avg DA", "domain-data-manager"); ?></span>
                                <span class="ddm-stat-value"><?php echo round($data['avg_da']); ?></span>
                            </div>
                            <div class="ddm-type-stat">
                                <span class="ddm-stat-label"><?php _e("Total Traffic", "domain-data-manager"); ?></span>
                                <span class="ddm-stat-value"><?php echo number_format($data['total_traffic']); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Pass analytics data to JavaScript
window.ddmAnalytics = <?php echo wp_json_encode($analytics); ?>;
</script>
