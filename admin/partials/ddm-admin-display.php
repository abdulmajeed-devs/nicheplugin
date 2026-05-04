<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
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

// Instantiate DB class to fetch data
$db = new Domain_Data_Manager_Db();
$data = $db->get_data(['per_page' => -1]); // Get all data for admin view initially

?>

<div class="ddm-admin-wrap">
    <div class="ddm-header">
        <h1 class="ddm-title">
            <svg class="ddm-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                <polyline points="3.27,6.96 12,12.01 20.73,6.96"></polyline>
                <line x1="12" y1="22.08" x2="12" y2="12"></line>
            </svg>
            <?php echo esc_html(get_admin_page_title()); ?>
        </h1>
        <p class="ddm-subtitle">Manage your domain data with ease</p>
    </div>

    <div id="ddm-message" class="ddm-notice" style="display: none;"></div>

    <!-- Add New / Edit Form (Initially Hidden) -->
    <div id="ddm-form-wrapper" class="ddm-form-card" style="display: none;">
        <div class="ddm-card-header">
            <h2 id="ddm-form-title" class="ddm-card-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="16"></line>
                    <line x1="8" y1="12" x2="16" y2="12"></line>
                </svg>
                <?php _e("Add New Domain Data", "domain-data-manager"); ?>
            </h2>
        </div>
        <div class="ddm-card-content">
            <form id="ddm-data-form" class="ddm-form">
                <input type="hidden" id="ddm-edit-id" name="id" value="0">
                <?php wp_nonce_field( 'ddm_admin_nonce', 'ddm_nonce_field' ); ?>
                
                <div class="ddm-form-grid">
                    <div class="ddm-form-group">
                        <label for="ddm-type" class="ddm-label">
                            <?php _e("Type", "domain-data-manager"); ?>
                        </label>
                        <input type="text" id="ddm-type" name="type" class="ddm-input" required>
                    </div>
                    
                    <div class="ddm-form-group">
                        <label for="ddm-domain" class="ddm-label">
                            <?php _e("Domain", "domain-data-manager"); ?>
                        </label>
                        <input type="text" id="ddm-domain" name="domain" class="ddm-input" required>
                    </div>
                    
                    <div class="ddm-form-group">
                        <label for="ddm-da" class="ddm-label">
                            <?php _e("DA", "domain-data-manager"); ?>
                        </label>
                        <input type="number" id="ddm-da" name="da" class="ddm-input" min="0" max="100" step="1" required>
                    </div>
                    
                    <div class="ddm-form-group">
                        <label for="ddm-traffic" class="ddm-label">
                            <?php _e("Traffic", "domain-data-manager"); ?>
                        </label>
                        <input type="number" id="ddm-traffic" name="traffic" class="ddm-input" min="0" step="1" required>
                    </div>
                    
                    <div class="ddm-form-group">
                        <label for="ddm-age" class="ddm-label">
                            <?php _e("Age", "domain-data-manager"); ?>
                        </label>
                        <input type="number" id="ddm-age" name="age" class="ddm-input" min="0" step="1" required>
                    </div>
                    
                    <div class="ddm-form-group ddm-checkbox-group">
                        <label class="ddm-checkbox-wrapper">
                            <input type="checkbox" id="ddm-emd" name="emd" value="1" class="ddm-checkbox">
                            <span class="ddm-checkbox-mark"></span>
                            <span class="ddm-checkbox-label"><?php _e("EMD (Exact Match Domain)", "domain-data-manager"); ?></span>
                        </label>
                    </div>
                </div>

                <div class="ddm-form-actions">
                    <button type="submit" class="ddm-btn ddm-btn-primary" id="ddm-save-button">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="m9 12 2 2 4-4"></path>
                            <path d="M21 12c.552 0 1-.448 1-1V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v14c0 1.1.9 2 2 2h14a2 2 0 0 0 2-2v-3"></path>
                        </svg>
                        <?php _e("Save Data", "domain-data-manager"); ?>
                    </button>
                    <button type="button" class="ddm-btn ddm-btn-secondary" id="ddm-cancel-button">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                        <?php _e("Cancel", "domain-data-manager"); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="ddm-actions">
        <button id="ddm-add-new-button" class="ddm-btn ddm-btn-primary ddm-btn-large">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="16"></line>
                <line x1="8" y1="12" x2="16" y2="12"></line>
            </svg>
            <?php _e("Add New Entry", "domain-data-manager"); ?>
        </button>
        <a href="<?php echo esc_url(admin_url('admin-ajax.php?action=ddm_export_data&nonce=' . wp_create_nonce('ddm_admin_nonce'))); ?>" 
           id="ddm-export-button" class="ddm-btn ddm-btn-outline">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="7,10 12,15 17,10"></polyline>
                <line x1="12" y1="15" x2="12" y2="3"></line>
            </svg>
            <?php _e("Export All Data (CSV)", "domain-data-manager"); ?>
        </a>
    </div>

    <!-- Data Table -->
    <div class="ddm-table-card">
        <div class="ddm-table-header">
            <h3 class="ddm-table-title">Domain Data</h3>
            <div class="ddm-table-info">
                <span class="ddm-count"><?php echo count($data); ?> entries</span>
            </div>
        </div>
        <div class="ddm-table-container">
            <table class="ddm-table" id="ddm-data-table">
                <thead>
                    <tr>
                        <th class="ddm-th ddm-sortable ddm-sorted-asc" data-sort="id">
                            <div class="ddm-th-content">
                                <span><?php _e("ID", "domain-data-manager"); ?></span>
                                <svg class="ddm-sort-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="m7 15 5 5 5-5"></path>
                                    <path d="m7 9 5-5 5 5"></path>
                                </svg>
                            </div>
                        </th>
                        <th class="ddm-th ddm-sortable" data-sort="type">
                            <div class="ddm-th-content">
                                <span><?php _e("Type", "domain-data-manager"); ?></span>
                                <svg class="ddm-sort-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="m7 15 5 5 5-5"></path>
                                    <path d="m7 9 5-5 5 5"></path>
                                </svg>
                            </div>
                        </th>
                        <th class="ddm-th ddm-sortable" data-sort="domain">
                            <div class="ddm-th-content">
                                <span><?php _e("Domain", "domain-data-manager"); ?></span>
                                <svg class="ddm-sort-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="m7 15 5 5 5-5"></path>
                                    <path d="m7 9 5-5 5 5"></path>
                                </svg>
                            </div>
                        </th>
                        <th class="ddm-th ddm-sortable" data-sort="da">
                            <div class="ddm-th-content">
                                <span><?php _e("DA", "domain-data-manager"); ?></span>
                                <svg class="ddm-sort-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="m7 15 5 5 5-5"></path>
                                    <path d="m7 9 5-5 5 5"></path>
                                </svg>
                            </div>
                        </th>
                        <th class="ddm-th ddm-sortable" data-sort="traffic">
                            <div class="ddm-th-content">
                                <span><?php _e("Traffic", "domain-data-manager"); ?></span>
                                <svg class="ddm-sort-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="m7 15 5 5 5-5"></path>
                                    <path d="m7 9 5-5 5 5"></path>
                                </svg>
                            </div>
                        </th>
                        <th class="ddm-th ddm-sortable" data-sort="age">
                            <div class="ddm-th-content">
                                <span><?php _e("Age", "domain-data-manager"); ?></span>
                                <svg class="ddm-sort-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="m7 15 5 5 5-5"></path>
                                    <path d="m7 9 5-5 5 5"></path>
                                </svg>
                            </div>
                        </th>
                        <th class="ddm-th ddm-sortable" data-sort="emd">
                            <div class="ddm-th-content">
                                <span><?php _e("EMD", "domain-data-manager"); ?></span>
                                <svg class="ddm-sort-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="m7 15 5 5 5-5"></path>
                                    <path d="m7 9 5-5 5 5"></path>
                                </svg>
                            </div>
                        </th>
                        <th class="ddm-th"><?php _e("Actions", "domain-data-manager"); ?></th>
                    </tr>
                </thead>
                <tbody id="the-list">
                    <?php if (!empty($data)) : ?>
                        <?php foreach ($data as $row) : ?>
                            <tr id="ddm-row-<?php echo esc_attr($row['id']); ?>" class="ddm-tr">
                                <td class="ddm-td"><?php echo esc_html($row['id']); ?></td>
                                <td class="ddm-td">
                                    <span class="ddm-badge ddm-badge-<?php echo sanitize_html_class(strtolower($row['type'])); ?>">
                                        <?php echo esc_html($row['type']); ?>
                                    </span>
                                </td>
                                <td class="ddm-td ddm-td-domain"><?php echo esc_html($row['domain']); ?></td>
                                <td class="ddm-td">
                                    <div class="ddm-da-score ddm-da-<?php echo $row['da'] >= 80 ? 'high' : ($row['da'] >= 60 ? 'good' : ($row['da'] >= 40 ? 'medium' : ($row['da'] >= 20 ? 'low' : 'very-low'))); ?>">
                                        <?php echo esc_html($row['da']); ?>
                                    </div>
                                </td>
                                <td class="ddm-td ddm-td-traffic"><?php echo number_format(esc_html($row['traffic'])); ?></td>
                                <td class="ddm-td"><?php echo esc_html($row['age']); ?></td>
                                <td class="ddm-td">
                                    <span class="ddm-emd-badge <?php echo $row['emd'] ? 'ddm-emd-yes' : 'ddm-emd-no'; ?>">
                                        <?php echo $row['emd'] ? __('Yes', 'domain-data-manager') : __('No', 'domain-data-manager'); ?>
                                    </span>
                                </td>
                                <td class="ddm-td ddm-td-actions">
                                    <div class="ddm-action-buttons">
                                        <button class="ddm-btn ddm-btn-small ddm-btn-edit ddm-edit-button" data-id="<?php echo esc_attr($row['id']); ?>" title="<?php _e('Edit', 'domain-data-manager'); ?>">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                <path d="m18.5 2.5 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                            </svg>
                                        </button>
                                        <button class="ddm-btn ddm-btn-small ddm-btn-danger ddm-delete-button" data-id="<?php echo esc_attr($row['id']); ?>" title="<?php _e('Delete', 'domain-data-manager'); ?>">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="3,6 5,6 21,6"></polyline>
                                                <path d="m19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr class="ddm-no-items">
                            <td class="ddm-td" colspan="8">
                                <div class="ddm-empty-state">
                                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <h4><?php _e("No data found", "domain-data-manager"); ?></h4>
                                    <p><?php _e("Start by adding your first domain entry or uploading a CSV file.", "domain-data-manager"); ?></p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
