<?php
/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Domain_Data_Manager
 * @subpackage Domain_Data_Manager/public/partials
 */

// Ensure this file is loaded within WordPress
if (!defined("ABSPATH")) {
    exit;
}

// Handle undefined variables with defaults
$color_scheme = isset($color_scheme) ? $color_scheme : '';
$show_borders = isset($show_borders) ? $show_borders : true;
$row_styling = isset($row_styling) ? $row_styling : true;
$initial_data = isset($initial_data) ? $initial_data : array();

// Build table classes with unique prefixes
$table_classes = ["ddm-plugin-table"];
if ($show_borders) {
    $table_classes[] = "ddm-plugin-bordered";
}
if ($row_styling) {
    $table_classes[] = "ddm-plugin-striped";
}

// Process initial data for display
$processed_data = array();
foreach ($initial_data as $row) {
    $processed_row = array(
        'id' => intval($row['id']),
        'type' => esc_html($row['type']),
        'domain' => esc_html($row['domain']),
        'da' => intval($row['da']),
        'traffic' => intval($row['traffic']),
        'traffic_formatted' => number_format($row['traffic']),
        'age' => intval($row['age']),
        'emd' => intval($row['emd']),
        'da_class' => $row['da'] >= 80 ? 'excellent' : ($row['da'] >= 60 ? 'great' : ($row['da'] >= 40 ? 'good' : ($row['da'] >= 20 ? 'fair' : 'low'))),
        'type_class' => sanitize_html_class(strtolower($row['type'])),
        'created_at' => $row['created_at'],
        'updated_at' => $row['updated_at']
    );
    $processed_data[] = $processed_row;
}

// Calculate stats
$total_domains = count($processed_data);
$avg_da = !empty($processed_data) ? round(array_sum(array_column($processed_data, 'da')) / count($processed_data)) : 0;
$total_traffic = array_sum(array_column($processed_data, 'traffic'));

?>

<div class="ddm-plugin-wrapper">
    <!-- Professional Header Section -->
    <div class="ddm-plugin-header-section">
        <div class="ddm-plugin-header-content">
            <div class="ddm-plugin-header-title">
                <h2 class="ddm-plugin-title"><?php _e("Domain Portfolio", "domain-data-manager"); ?></h2>
                <p class="ddm-plugin-subtitle"><?php _e("Professional domain data management", "domain-data-manager"); ?></p>
            </div>
            </div>
        </div>
    </div>

    <!-- Clean Controls Section -->
    <div class="ddm-plugin-controls-section">
        <div class="ddm-plugin-controls-wrapper">
            <!-- Search Bar -->
            <div class="ddm-plugin-search-container">
                <div class="ddm-plugin-search-wrapper">
                    <svg class="ddm-plugin-search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                    <input type="text" 
                           id="ddm-search-input" 
                           class="ddm-plugin-search-input"
                           placeholder="<?php _e("Search domains...", "domain-data-manager"); ?>"
                           autocomplete="off"
                           aria-label="<?php _e("Search domains", "domain-data-manager"); ?>">
                    <button type="button" class="ddm-plugin-search-clear" style="display: none;" aria-label="<?php _e("Clear search", "domain-data-manager"); ?>">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Filter Dropdown -->
            <div class="ddm-plugin-filter-dropdown">
                <button type="button" class="ddm-plugin-filter-toggle" id="ddm-filter-toggle" aria-expanded="false" aria-haspopup="true">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                    </svg>
                    <span><?php _e("Filters", "domain-data-manager"); ?></span>
                    <svg class="ddm-plugin-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </button>
                
                <div class="ddm-plugin-filter-panel" id="ddm-filter-panel" role="dialog" aria-labelledby="ddm-filter-toggle">
                    <div class="ddm-plugin-filter-content">
                        <!-- DA Range -->
                        <div class="ddm-plugin-filter-row">
                            <label class="ddm-plugin-filter-label" for="ddm-da-min"><?php _e("Domain Authority Range", "domain-data-manager"); ?></label>
                            <div class="ddm-plugin-range-inputs">
                                <input type="number" id="ddm-da-min" class="ddm-plugin-range-input" min="0" max="100" value="0" placeholder="0" aria-label="<?php _e("Minimum DA", "domain-data-manager"); ?>">
                                <span class="ddm-plugin-range-separator">–</span>
                                <input type="number" id="ddm-da-max" class="ddm-plugin-range-input" min="0" max="100" value="100" placeholder="100" aria-label="<?php _e("Maximum DA", "domain-data-manager"); ?>">
                            </div>
                        </div>
                        
                        <!-- Traffic Range -->
                        <div class="ddm-plugin-filter-row">
                            <label class="ddm-plugin-filter-label" for="ddm-traffic-min"><?php _e("Monthly Traffic Range", "domain-data-manager"); ?></label>
                            <div class="ddm-plugin-range-inputs">
                                <input type="number" id="ddm-traffic-min" class="ddm-plugin-range-input" min="0" value="0" placeholder="0" aria-label="<?php _e("Minimum traffic", "domain-data-manager"); ?>">
                                <span class="ddm-plugin-range-separator">–</span>
                                <input type="number" id="ddm-traffic-max" class="ddm-plugin-range-input" min="0" value="1000000" placeholder="<?php _e("Max", "domain-data-manager"); ?>" aria-label="<?php _e("Maximum traffic", "domain-data-manager"); ?>">
                            </div>
                        </div>
                        
                        <!-- Domain Contains -->
                        <div class="ddm-plugin-filter-row">
                            <label class="ddm-plugin-filter-label" for="ddm-contains-input"><?php _e("Contains Keywords", "domain-data-manager"); ?></label>
                            <input type="text" id="ddm-contains-input" class="ddm-plugin-text-input" placeholder="<?php _e("e.g., tech, blog, shop", "domain-data-manager"); ?>" aria-label="<?php _e("Keywords to search for", "domain-data-manager"); ?>">
                        </div>
                        
                        <!-- Character Length -->
                        <div class="ddm-plugin-filter-row">
                            <label class="ddm-plugin-filter-label" for="ddm-length-min"><?php _e("Domain Name Length", "domain-data-manager"); ?></label>
                            <div class="ddm-plugin-range-inputs">
                                <input type="number" id="ddm-length-min" class="ddm-plugin-range-input" min="1" max="63" value="1" placeholder="1" aria-label="<?php _e("Minimum length", "domain-data-manager"); ?>">
                                <span class="ddm-plugin-range-separator">–</span>
                                <input type="number" id="ddm-length-max" class="ddm-plugin-range-input" min="1" max="63" value="63" placeholder="63" aria-label="<?php _e("Maximum length", "domain-data-manager"); ?>">
                            </div>
                        </div>
                        
                        <!-- Length Presets -->
                        <div class="ddm-plugin-filter-row">
                            <div class="ddm-plugin-preset-buttons" role="group" aria-label="<?php _e("Length presets", "domain-data-manager"); ?>">
                                <button type="button" class="ddm-plugin-preset-btn" data-min="1" data-max="5"><?php _e("Short", "domain-data-manager"); ?></button>
                                <button type="button" class="ddm-plugin-preset-btn" data-min="6" data-max="10"><?php _e("Medium", "domain-data-manager"); ?></button>
                                <button type="button" class="ddm-plugin-preset-btn" data-min="11" data-max="15"><?php _e("Long", "domain-data-manager"); ?></button>
                                <button type="button" class="ddm-plugin-preset-btn" data-min="16" data-max="63"><?php _e("Extra Long", "domain-data-manager"); ?></button>
                            </div>
                        </div>
                        
                        <!-- Filter Actions -->
                        <div class="ddm-plugin-filter-actions">
                            <button type="button" class="ddm-plugin-btn ddm-plugin-btn-primary" id="ddm-apply-filters">
                                <?php _e("Apply Filters", "domain-data-manager"); ?>
                            </button>
                            <button type="button" class="ddm-plugin-btn ddm-plugin-btn-secondary" id="ddm-reset-filters">
                                <?php _e("Reset All", "domain-data-manager"); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Professional Table Section -->
    <div class="ddm-plugin-table-wrapper">
        <div class="ddm-plugin-table-container">
            <div class="ddm-plugin-table-scroll">
                <table class="<?php echo esc_attr(implode(' ', $table_classes)); ?>" id="ddm-frontend-table" role="table">
                    <thead class="ddm-plugin-thead">
                        <tr class="ddm-plugin-header-row" role="row">
                            <th scope="col" class="ddm-plugin-th ddm-plugin-sortable" data-sort="type" role="columnheader" tabindex="0" aria-sort="none">
                                <div class="ddm-plugin-th-content">
                                    <span class="ddm-plugin-th-text"><?php _e("Type", "domain-data-manager"); ?></span>
                                    <svg class="ddm-plugin-sort-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="m7 15 5 5 5-5"></path>
                                        <path d="m7 9 5-5 5 5"></path>
                                    </svg>
                                </div>
                            </th>
                            <th scope="col" class="ddm-plugin-th ddm-plugin-sortable ddm-plugin-sorted-asc" data-sort="domain" role="columnheader" tabindex="0" aria-sort="ascending">
                                <div class="ddm-plugin-th-content">
                                    <span class="ddm-plugin-th-text"><?php _e("Domain Name", "domain-data-manager"); ?></span>
                                    <svg class="ddm-plugin-sort-icon ddm-plugin-sort-active" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="m7 15 5 5 5-5"></path>
                                        <path d="m7 9 5-5 5 5"></path>
                                    </svg>
                                </div>
                            </th>
                            <th scope="col" class="ddm-plugin-th ddm-plugin-sortable" data-sort="da" role="columnheader" tabindex="0" aria-sort="none">
                                <div class="ddm-plugin-th-content">
                                    <span class="ddm-plugin-th-text"><?php _e("Authority", "domain-data-manager"); ?></span>
                                    <svg class="ddm-plugin-sort-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="m7 15 5 5 5-5"></path>
                                        <path d="m7 9 5-5 5 5"></path>
                                    </svg>
                                </div>
                            </th>
                            <th scope="col" class="ddm-plugin-th ddm-plugin-sortable" data-sort="traffic" role="columnheader" tabindex="0" aria-sort="none">
                                <div class="ddm-plugin-th-content">
                                    <span class="ddm-plugin-th-text"><?php _e("Monthly Traffic", "domain-data-manager"); ?></span>
                                    <svg class="ddm-plugin-sort-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="m7 15 5 5 5-5"></path>
                                        <path d="m7 9 5-5 5 5"></path>
                                    </svg>
                                </div>
                            </th>
                            <th scope="col" class="ddm-plugin-th ddm-plugin-sortable" data-sort="age" role="columnheader" tabindex="0" aria-sort="none">
                                <div class="ddm-plugin-th-content">
                                    <span class="ddm-plugin-th-text"><?php _e("Age", "domain-data-manager"); ?></span>
                                    <svg class="ddm-plugin-sort-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="m7 15 5 5 5-5"></path>
                                        <path d="m7 9 5-5 5 5"></path>
                                    </svg>
                                </div>
                            </th>
                            <th scope="col" class="ddm-plugin-th ddm-plugin-sortable" data-sort="emd" role="columnheader" tabindex="0" aria-sort="none">
                                <div class="ddm-plugin-th-content">
                                    <span class="ddm-plugin-th-text"><?php _e("EMD", "domain-data-manager"); ?></span>
                                    <svg class="ddm-plugin-sort-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="m7 15 5 5 5-5"></path>
                                        <path d="m7 9 5-5 5 5"></path>
                                    </svg>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="ddm-plugin-tbody" id="ddm-table-body">
                        <?php if (empty($processed_data)) : ?>
                        <tr>
                            <td colspan="6" class="ddm-plugin-td">
                                <div class="ddm-plugin-empty-state">
                                    <div class="ddm-plugin-empty-icon">
                                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                            <circle cx="11" cy="11" r="8"></circle>
                                            <path d="m21 21-4.35-4.35"></path>
                                        </svg>
                                    </div>
                                    <h3 class="ddm-plugin-empty-title"><?php _e("No domains found", "domain-data-manager"); ?></h3>
                                    <p class="ddm-plugin-empty-text"><?php _e("Add domains to see them displayed here", "domain-data-manager"); ?></p>
                                </div>
                            </td>
                        </tr>
                        <?php else : ?>
                            <?php foreach ($processed_data as $row) : ?>
                            <tr class="ddm-plugin-table-row" role="row">
                                <td class="ddm-plugin-td ddm-plugin-td-type" data-label="<?php _e("Type", "domain-data-manager"); ?>" role="cell">
                                    <span class="ddm-plugin-type-badge ddm-plugin-type-<?php echo esc_attr($row['type_class']); ?>">
                                        <?php echo esc_html($row['type']); ?>
                                    </span>
                                </td>
                                <td class="ddm-plugin-td ddm-plugin-td-domain" data-label="<?php _e("Domain", "domain-data-manager"); ?>" role="cell">
                                    <div class="ddm-plugin-domain-cell">
                                        <span class="ddm-plugin-domain-name"><?php echo esc_html($row['domain']); ?></span>
                                        <button type="button" class="ddm-plugin-copy-domain" title="<?php _e("Copy domain", "domain-data-manager"); ?>" aria-label="<?php _e("Copy domain to clipboard", "domain-data-manager"); ?>">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                                <path d="m5 15h4a2 2 0 0 1 2 2v4"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                                <td class="ddm-plugin-td ddm-plugin-td-da" data-label="<?php _e("DA", "domain-data-manager"); ?>" role="cell">
                                    <div class="ddm-plugin-da-container">
                                        <div class="ddm-plugin-da-score ddm-plugin-da-<?php echo esc_attr($row['da_class']); ?>">
                                            <span class="ddm-plugin-da-number"><?php echo esc_html($row['da']); ?></span>
                                        </div>
                                        <div class="ddm-plugin-da-bar" role="progressbar" aria-valuenow="<?php echo esc_attr($row['da']); ?>" aria-valuemin="0" aria-valuemax="100" aria-label="<?php printf(__("Domain Authority: %d", "domain-data-manager"), $row['da']); ?>">
                                            <div class="ddm-plugin-da-fill" style="width: <?php echo esc_attr($row['da']); ?>%;"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="ddm-plugin-td ddm-plugin-td-traffic" data-label="<?php _e("Traffic", "domain-data-manager"); ?>" role="cell">
                                    <div class="ddm-plugin-traffic-container">
                                        <span class="ddm-plugin-traffic-number"><?php echo esc_html($row['traffic_formatted']); ?></span>
                                        <div class="ddm-plugin-traffic-indicator" aria-label="<?php printf(__("Traffic level indicator: %s", "domain-data-manager"), $row['traffic_formatted']); ?>">
                                            <?php
                                            if ($row['traffic'] > 100000) {
                                                echo '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22,12 18,8 18,16 22,12"></polyline><polyline points="8,12 4,8 4,16 8,12"></polyline></svg>';
                                            } elseif ($row['traffic'] > 10000) {
                                                echo '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15,10 20,15 15,20"></polyline></svg>';
                                            } else {
                                                echo '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9,10 4,15 9,20"></polyline></svg>';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="ddm-plugin-td ddm-plugin-td-age" data-label="<?php _e("Age", "domain-data-manager"); ?>" role="cell">
                                    <div class="ddm-plugin-age-container">
                                        <span class="ddm-plugin-age-number"><?php echo esc_html($row['age']); ?></span>
                                        <span class="ddm-plugin-age-unit"><?php _e("years", "domain-data-manager"); ?></span>
                                    </div>
                                </td>
                                <td class="ddm-plugin-td ddm-plugin-td-emd" data-label="<?php _e("EMD", "domain-data-manager"); ?>" role="cell">
                                    <span class="ddm-plugin-emd-badge <?php echo $row['emd'] ? 'ddm-plugin-emd-yes' : 'ddm-plugin-emd-no'; ?>" aria-label="<?php echo $row['emd'] ? __("Exact Match Domain: Yes", "domain-data-manager") : __("Exact Match Domain: No", "domain-data-manager"); ?>">
                                        <?php if ($row['emd']) : ?>
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="20,6 9,17 4,12"></polyline>
                                            </svg>
                                        <?php else : ?>
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                                <line x1="6" y1="6" x2="18" y2="18"></line>
                                            </svg>
                                        <?php endif; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
