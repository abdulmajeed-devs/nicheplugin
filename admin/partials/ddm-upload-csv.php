<?php
/**
 * Provide a admin area view for the CSV Upload page
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
?>

<div class="ddm-admin-wrap">
    <div class="ddm-header">
        <h1 class="ddm-title">
            <svg class="ddm-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="17,8 12,3 7,8"></polyline>
                <line x1="12" y1="3" x2="12" y2="15"></line>
            </svg>
            <?php echo esc_html(get_admin_page_title()); ?>
        </h1>
        <p class="ddm-subtitle"><?php _e("Import domain data from CSV files quickly and efficiently", "domain-data-manager"); ?></p>
    </div>

    <div id="ddm-upload-message" class="ddm-notice" style="display: none;"></div>

    <div class="ddm-upload-container">
        <div class="ddm-upload-card">
            <div class="ddm-card-header">
                <h2 class="ddm-card-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14,2 14,8 20,8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10,9 9,9 8,9"></polyline>
                    </svg>
                    <?php _e("Upload CSV File", "domain-data-manager"); ?>
                </h2>
                <p class="ddm-card-description">
                    <?php _e("Upload a CSV file containing domain data. The required columns are: Type, Domain, DA, Traffic, Age, EMD.", "domain-data-manager"); ?>
                </p>
            </div>
            <div class="ddm-card-content">
                <form id="ddm-upload-csv-form" method="post" enctype="multipart/form-data" class="ddm-upload-form">
                    <?php wp_nonce_field( 'ddm_admin_nonce', 'ddm_upload_nonce_field' ); ?>
                    
                    <div class="ddm-upload-area">
                        <div class="ddm-file-input-wrapper">
                            <input type="file" id="ddm-csv-file" name="csv_file" accept=".csv" required class="ddm-file-input">
                            <label for="ddm-csv-file" class="ddm-file-label">
                                <div class="ddm-upload-icon">
                                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="17,8 12,3 7,8"></polyline>
                                        <line x1="12" y1="3" x2="12" y2="15"></line>
                                    </svg>
                                </div>
                                <div class="ddm-upload-text">
                                    <span class="ddm-upload-main"><?php _e("Choose CSV file", "domain-data-manager"); ?></span>
                                    <span class="ddm-upload-sub"><?php _e("or drag and drop it here", "domain-data-manager"); ?></span>
                                </div>
                            </label>
                        </div>
                        <div class="ddm-file-info" style="display: none;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14,2 14,8 20,8"></polyline>
                            </svg>
                            <span class="ddm-file-name"></span>
                            <button type="button" class="ddm-file-remove">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="ddm-import-options">
                        <h4 class="ddm-options-title"><?php _e("Import Mode", "domain-data-manager"); ?></h4>
                        <div class="ddm-radio-group">
                            <label class="ddm-radio-option">
                                <input type="radio" name="import_mode" value="append" checked="checked">
                                <span class="ddm-radio-mark"></span>
                                <div class="ddm-radio-content">
                                    <span class="ddm-radio-title"><?php _e("Append only", "domain-data-manager"); ?></span>
                                    <span class="ddm-radio-desc"><?php _e("Add new entries, skip existing domains", "domain-data-manager"); ?></span>
                                </div>
                            </label>
                            <label class="ddm-radio-option">
                                <input type="radio" name="import_mode" value="update">
                                <span class="ddm-radio-mark"></span>
                                <div class="ddm-radio-content">
                                    <span class="ddm-radio-title"><?php _e("Update existing", "domain-data-manager"); ?></span>
                                    <span class="ddm-radio-desc"><?php _e("Update existing entries and add new ones", "domain-data-manager"); ?></span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="ddm-form-actions">
                        <button type="submit" class="ddm-btn ddm-btn-primary ddm-btn-large" id="ddm-upload-button">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="17,8 12,3 7,8"></polyline>
                                <line x1="12" y1="3" x2="12" y2="15"></line>
                            </svg>
                            <span><?php _e("Upload and Process CSV", "domain-data-manager"); ?></span>
                        </button>
                        <div class="ddm-upload-progress" style="display: none;">
                            <div class="ddm-progress-bar">
                                <div class="ddm-progress-fill"></div>
                            </div>
                            <span class="ddm-progress-text"><?php _e("Processing...", "domain-data-manager"); ?></span>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="ddm-info-card">
            <div class="ddm-card-header">
                <h3 class="ddm-card-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="l9,12 2,2 4,-4"></path>
                    </svg>
                    <?php _e("CSV Format Requirements", "domain-data-manager"); ?>
                </h3>
            </div>
            <div class="ddm-card-content">
                <div class="ddm-format-requirements">
                    <div class="ddm-requirement">
                        <div class="ddm-req-header">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20,6 9,17 4,12"></polyline>
                            </svg>
                            <strong><?php _e("Required Columns", "domain-data-manager"); ?></strong>
                        </div>
                        <p><?php _e("Type, Domain, DA, Traffic, Age, EMD", "domain-data-manager"); ?></p>
                    </div>
                    <div class="ddm-requirement">
                        <div class="ddm-req-header">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2v20M2 12h20"></path>
                            </svg>
                            <strong><?php _e("Unique Identifier", "domain-data-manager"); ?></strong>
                        </div>
                        <p><?php _e("Domain column is used as unique identifier", "domain-data-manager"); ?></p>
                    </div>
                    <div class="ddm-requirement">
                        <div class="ddm-req-header">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            </svg>
                            <strong><?php _e("File Format", "domain-data-manager"); ?></strong>
                        </div>
                        <p><?php _e("CSV files only, with headers in first row", "domain-data-manager"); ?></p>
                    </div>
                </div>
                
                <div class="ddm-sample-format">
                    <h4><?php _e("Sample CSV Format", "domain-data-manager"); ?>:</h4>
                    <div class="ddm-code-block">
                        <pre>Type,Domain,DA,Traffic,Age,EMD
Blog,example.com,45,15000,3,0
News,sample.org,72,85000,7,1</pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
