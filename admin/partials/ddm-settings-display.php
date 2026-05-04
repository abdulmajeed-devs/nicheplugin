<?php
/**
 * Provide a admin area view for the Settings page
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
                <circle cx="12" cy="12" r="3"></circle>
                <path d="m12 1 2.09 7.26L22 10l-7.26 2.09L13 20l-2.09-7.26L3 11l7.26-2.09L12 1Z"></path>
            </svg>
            <?php echo esc_html(get_admin_page_title()); ?>
        </h1>
        <p class="ddm-subtitle"><?php _e("Configure display options for your domain data tables", "domain-data-manager"); ?></p>
    </div>

    <div class="ddm-settings-container">
        <form method="post" action="options.php" class="ddm-settings-form">
            <?php
            // This prints out all hidden setting fields
            settings_fields( 'ddm_settings_group' );
            ?>
            
            <div class="ddm-settings-grid">
                <div class="ddm-form-card">
                    <div class="ddm-card-header">
                        <h2 class="ddm-card-title">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="9" y1="9" x2="15" y2="9"></line>
                                <line x1="9" y1="15" x2="15" y2="15"></line>
                            </svg>
                            <?php _e("Table Appearance", "domain-data-manager"); ?>
                        </h2>
                        <p class="ddm-card-description">
                            <?php _e("Customize how your data tables look on the frontend", "domain-data-manager"); ?>
                        </p>
                    </div>
                    <div class="ddm-card-content">
                        <?php do_settings_sections( 'ddm_settings_group' ); ?>
                    </div>
                </div>
                
                <div class="ddm-form-card">
                    <div class="ddm-card-header">
                        <h2 class="ddm-card-title">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2v20M2 12h20"></path>
                            </svg>
                            <?php _e("Usage Instructions", "domain-data-manager"); ?>
                        </h2>
                    </div>
                    <div class="ddm-card-content">
                        <div class="ddm-info-block">
                            <h4><?php _e("Shortcode Usage", "domain-data-manager"); ?></h4>
                            <p><?php _e("Use the following shortcode to display your domain data table on any page or post:", "domain-data-manager"); ?></p>
                            <div class="ddm-code-block">
                                <code>[domain_data_table]</code>
                                <button type="button" class="ddm-copy-btn" onclick="navigator.clipboard.writeText('[domain_data_table]')">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                        <path d="m5 15h4a2 2 0 0 1 2 2v4"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        
                        <div class="ddm-info-block">
                            <h4><?php _e("CSV Upload Format", "domain-data-manager"); ?></h4>
                            <p><?php _e("Your CSV file should contain the following columns in this order:", "domain-data-manager"); ?></p>
                            <ul class="ddm-csv-format">
                                <li><strong>Type</strong> - <?php _e("Domain type or category", "domain-data-manager"); ?></li>
                                <li><strong>Domain</strong> - <?php _e("Domain name (unique identifier)", "domain-data-manager"); ?></li>
                                <li><strong>DA</strong> - <?php _e("Domain Authority (0-100)", "domain-data-manager"); ?></li>
                                <li><strong>Traffic</strong> - <?php _e("Monthly traffic volume", "domain-data-manager"); ?></li>
                                <li><strong>Age</strong> - <?php _e("Domain age in years", "domain-data-manager"); ?></li>
                                <li><strong>EMD</strong> - <?php _e("1 for Yes, 0 for No (Exact Match Domain)", "domain-data-manager"); ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ddm-form-actions ddm-settings-actions">
                <?php submit_button(__('Save Settings', 'domain-data-manager'), 'ddm-btn ddm-btn-primary ddm-btn-large', 'submit', false); ?>
            </div>
        </form>
    </div>
</div>
