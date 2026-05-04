/**
 * Domain Data Manager Public JavaScript - Professional Theme
 * All class names use 'ddm-plugin-' prefix to avoid WordPress theme conflicts
 */

(function($) {
    'use strict';

    // Global DDM Public object
    window.DDMPublic = {
        searchTimeout: null,
        currentSort: { column: 'domain', order: 'asc' },
        isLoading: false,
        currentFilters: {
            search: '',
            da_min: 0,
            da_max: 100,
            traffic_min: 0,
            traffic_max: 1000000,
            contains: '',
            length_min: 1,
            length_max: 63
        },
        
        init: function() {
            this.bindEvents();
            this.initializeComponents();
            this.setupResponsiveTable();
        },

        bindEvents: function() {
            // Search functionality
            $(document).on('input', '#ddm-search-input', this.handleSearch.bind(this));
            $(document).on('click', '.ddm-plugin-search-clear', this.clearSearch.bind(this));
            
            // Sorting functionality
            $(document).on('click', '.ddm-plugin-sortable', this.handleSort.bind(this));
            
            // Copy domain functionality
            $(document).on('click', '.ddm-plugin-copy-domain', this.copyDomain.bind(this));
            
            // Filter functionality
            $(document).on('click', '#ddm-filter-toggle', this.toggleFilters.bind(this));
            $(document).on('click', '#ddm-apply-filters', this.applyFilters.bind(this));
            $(document).on('click', '#ddm-reset-filters', this.resetFilters.bind(this));
            $(document).on('click', '.ddm-plugin-preset-btn', this.handleLengthPreset.bind(this));
            
            // Filter input handling
            $(document).on('input', '.ddm-plugin-range-input, .ddm-plugin-text-input', this.handleFilterInput.bind(this));
            
            // Prevent filter panel clicks from closing the panel
            $(document).on('click', '.ddm-plugin-filter-panel', function(e) {
                e.stopPropagation();
            });
            
            // Mobile responsive handlers
            $(window).on('resize', this.handleResize.bind(this));
            
            // Keyboard navigation
            $(document).on('keydown', this.handleKeyNavigation.bind(this));
            
            // Close dropdown when clicking outside
            $(document).on('click', this.handleOutsideClick.bind(this));
        },

        initializeComponents: function() {
            this.initializeSearch();
            this.initializeFilters();
            this.animateOnLoad();
            this.setupAccessibility();
        },

        initializeSearch: function() {
            const $searchInput = $('#ddm-search-input');
            const $clearBtn = $('.ddm-plugin-search-clear');
            
            // Hide clear button initially
            $clearBtn.hide();
            
            // Add ARIA labels
            $searchInput.attr('aria-label', 'Search domains');
            $clearBtn.attr('aria-label', 'Clear search');
        },

        initializeFilters: function() {
            // Set up filter panel
            const $panel = $('#ddm-filter-panel');
            $panel.removeClass('active');
            
            // Initialize filter values
            this.updateFilterDisplay();
        },

        animateOnLoad: function() {
            // Add fade-in animation to table rows
            $('.ddm-plugin-table-row').addClass('ddm-plugin-fade-in');
        },

        setupAccessibility: function() {
            // Add proper ARIA attributes
            $('.ddm-plugin-sortable').attr('role', 'button');
            $('.ddm-plugin-sortable').attr('tabindex', '0');
            
            // Add keyboard support for sortable headers
            $('.ddm-plugin-sortable').on('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    $(this).click();
                }
            });
        },

        setupResponsiveTable: function() {
            // Handle responsive table behavior
            this.handleResize();
        },

        handleResize: function() {
            const windowWidth = $(window).width();
            const $table = $('.ddm-plugin-table');
            
            if (windowWidth < 768) {
                $table.addClass('ddm-plugin-mobile');
            } else {
                $table.removeClass('ddm-plugin-mobile');
            }
        },

        // Search functionality
        handleSearch: function(e) {
            const $input = $(e.target);
            const searchTerm = $input.val().trim();
            const $clearBtn = $('.ddm-plugin-search-clear');
            
            // Show/hide clear button
            if (searchTerm.length > 0) {
                $clearBtn.show();
            } else {
                $clearBtn.hide();
            }
            
            // Debounce search
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.performSearch(searchTerm);
            }, 300);
        },

        performSearch: function(searchTerm) {
            if (this.isLoading) return;
            
            this.isLoading = true;
            this.showLoading();
            
            const tableWrapper = $('.ddm-plugin-wrapper');
            // Update current filters with search term from input
            this.currentFilters.search = searchTerm;
            
            // Base request parameters that are always sent
            const requestParams = {
                action: 'ddm_get_public_data', 
                // *** FIX STARTS HERE: Added the security nonce ***
                nonce: ddm_public_ajax.nonce, 
                // *** FIX ENDS HERE ***
                orderby: this.currentSort.column,
                order: this.currentSort.order.toUpperCase(),
                da_min: this.currentFilters.da_min,
                da_max: this.currentFilters.da_max,
                traffic_min: this.currentFilters.traffic_min,
                traffic_max: this.currentFilters.traffic_max,
                length_min: this.currentFilters.length_min,
                length_max: this.currentFilters.length_max
            };

            // Conditionally add parameters ONLY if they have a value.
            if (this.currentFilters.search) {
                requestParams.search = this.currentFilters.search;
            }
            
            if (this.currentFilters.contains) {
                requestParams.contains = this.currentFilters.contains;
            }

            // Conditionally add shortcode attributes
            const limit = tableWrapper.data('limit');
            if (limit && limit > 0) {
                requestParams.limit = limit;
            }

            const type = tableWrapper.data('type');
            if (type && type.trim() !== '') {
                requestParams.type = type;
            }
            
            $.ajax({
                 url: ddm_public_ajax.ajax_url,
                 type: 'GET',
                 data: requestParams,
                 success: (response) => {
                     if (response.success) {
                         this.updateTable(response.data.data);
                         this.updateStats(response.data.stats);
                     } else {
                         this.showError(response.data.message || ddm_public_ajax.strings.error_loading);
                     }
                 },
                 error: (xhr, status, error) => {
                    console.error("AJAX request failed. See details below.");
                    console.error("Status:", status);
                    console.error("Error Thrown:", error);
                    console.error("Response Text:", xhr.responseText);
                    this.showError(ddm_public_ajax.strings.error_loading);
                 },
                 complete: () => {
                     this.hideLoading();
                     this.isLoading = false;
                 }
            });
        },

        clearSearch: function(e) {
            e.preventDefault();
            $('#ddm-search-input').val('').trigger('input').focus();
        },

        // Sorting functionality
        handleSort: function(e) {
            e.preventDefault();
            
            if (this.isLoading) return;
            
            const $th = $(e.currentTarget);
            const column = $th.data('sort');
            let order = 'asc';
            
            // Toggle order if same column
            if (this.currentSort.column === column && this.currentSort.order === 'asc') {
                order = 'desc';
            }
            
            // Update sort indicators
            $('.ddm-plugin-sortable').removeClass('ddm-plugin-sorted-asc ddm-plugin-sorted-desc');
            $th.addClass('ddm-plugin-sorted-' + order);
            
            // Update current sort
            this.currentSort = { column: column, order: order };
            
            // Perform search with new sort
            const searchTerm = $('#ddm-search-input').val().trim();
            this.performSearch(searchTerm);
        },

        // Copy domain functionality
        copyDomain: function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const $btn = $(e.currentTarget);
            const domain = $btn.closest('.ddm-plugin-domain-cell').find('.ddm-plugin-domain-name').text().trim();
            
            if (navigator.clipboard) {
                navigator.clipboard.writeText(domain).then(() => {
                    this.showCopySuccess();
                    this.logCopyAction(domain);
                }).catch(() => {
                    this.fallbackCopyToClipboard(domain);
                });
            } else {
                this.fallbackCopyToClipboard(domain);
            }
        },

        fallbackCopyToClipboard: function(text) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.top = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                document.execCommand('copy');
                this.showCopySuccess();
                this.logCopyAction(text);
            } catch (err) {
                this.showCopyError();
            }
            
            document.body.removeChild(textArea);
        },

        logCopyAction: function(domain) {
            // Optional: Log copy action for analytics
        },

        showCopySuccess: function() {
            this.showNotification(ddm_public_ajax?.strings?.copied || 'Domain copied to clipboard!', 'success');
        },

        showCopyError: function() {
            this.showNotification(ddm_public_ajax?.strings?.copy_failed || 'Failed to copy domain', 'error');
        },

        // Filter functionality
        toggleFilters: function(e) {
            e.preventDefault();
            e.stopPropagation(); 
            
            const $toggle = $('#ddm-filter-toggle');
            const $panel = $('#ddm-filter-panel');
            
            $toggle.toggleClass('active');
            $panel.toggleClass('active');
            
            const isOpen = $panel.hasClass('active');
            $toggle.attr('aria-expanded', isOpen);
            
            if (isOpen) {
                $panel.find('input').first().focus();
            }
        },

        applyFilters: function(e) {
            e.preventDefault();
            
            this.currentFilters = {
                search: $('#ddm-search-input').val().trim(),
                da_min: parseInt($('#ddm-da-min').val()) || 0,
                da_max: parseInt($('#ddm-da-max').val()) || 100,
                traffic_min: parseInt($('#ddm-traffic-min').val()) || 0,
                traffic_max: parseInt($('#ddm-traffic-max').val()) || 1000000,
                contains: $('#ddm-contains-input').val().trim(),
                length_min: parseInt($('#ddm-length-min').val()) || 1,
                length_max: parseInt($('#ddm-length-max').val()) || 63
            };
            
            this.closeFilterPanel();
            this.updateFilterToggle();
            this.performSearch(this.currentFilters.search);
        },

        resetFilters: function(e) {
            e.preventDefault();
            
            $('#ddm-da-min').val(0);
            $('#ddm-da-max').val(100);
            $('#ddm-traffic-min').val(0);
            $('#ddm-traffic-max').val(1000000);
            $('#ddm-contains-input').val('');
            $('#ddm-length-min').val(1);
            $('#ddm-length-max').val(63);
            
            $('.ddm-plugin-preset-btn').removeClass('active');
            
            this.currentFilters = {
                search: $('#ddm-search-input').val().trim(),
                da_min: 0,
                da_max: 100,
                traffic_min: 0,
                traffic_max: 1000000,
                contains: '',
                length_min: 1,
                length_max: 63
            };
            
            this.updateFilterToggle();
            this.performSearch(this.currentFilters.search);
        },

        handleLengthPreset: function(e) {
            e.preventDefault();
            
            const $btn = $(e.currentTarget);
            const min = parseInt($btn.data('min'));
            const max = parseInt($btn.data('max'));
            
            $('#ddm-length-min').val(min);
            $('#ddm-length-max').val(max);
            
            $('.ddm-plugin-preset-btn').removeClass('active');
            $btn.addClass('active');
        },

        handleFilterInput: function(e) {
            setTimeout(() => {
                this.updateFilterToggle();
            }, 10);
        },

        updateFilterToggle: function() {
            const $toggle = $('#ddm-filter-toggle');
            const hasFilters = this.hasActiveFilters();
            
            if (hasFilters) {
                $toggle.addClass('has-filters');
            } else {
                $toggle.removeClass('has-filters');
            }
        },

        hasActiveFilters: function() {
            const da_min = parseInt($('#ddm-da-min').val()) || 0;
            const da_max = parseInt($('#ddm-da-max').val()) || 100;
            const traffic_min = parseInt($('#ddm-traffic-min').val()) || 0;
            const traffic_max = parseInt($('#ddm-traffic-max').val()) || 1000000;
            const contains = $('#ddm-contains-input').val().trim();
            const length_min = parseInt($('#ddm-length-min').val()) || 1;
            const length_max = parseInt($('#ddm-length-max').val()) || 63;
            
            return da_min > 0 || da_max < 100 || 
                   traffic_min > 0 || traffic_max < 1000000 ||
                   contains !== '' ||
                   length_min > 1 || length_max < 63;
        },

        closeFilterPanel: function() {
            const $toggle = $('#ddm-filter-toggle');
            const $panel = $('#ddm-filter-panel');
            
            $toggle.removeClass('active');
            $panel.removeClass('active');
            $toggle.attr('aria-expanded', false);
        },

        updateFilterDisplay: function() {
            $('#ddm-da-min').val(this.currentFilters.da_min);
            $('#ddm-da-max').val(this.currentFilters.da_max);
            $('#ddm-traffic-min').val(this.currentFilters.traffic_min);
            $('#ddm-traffic-max').val(this.currentFilters.traffic_max);
            $('#ddm-contains-input').val(this.currentFilters.contains);
            $('#ddm-length-min').val(this.currentFilters.length_min);
            $('#ddm-length-max').val(this.currentFilters.length_max);
        },

        updateTable: function(data) {
            const $tbody = $('#ddm-table-body');
            
            if (!data || data.length === 0) {
                $tbody.html(this.getEmptyStateHTML());
                return;
            }
            
            let html = '';
            data.forEach((row) => {
                html += this.generateRowHTML(row);
            });
            
            $tbody.html(html);
            this.animateRows();
        },

        generateRowHTML: function(row) {
            const daClass = row.da_class || 'low';
            const typeClass = row.type_class || 'default';
            
            return `
                <tr class="ddm-plugin-table-row ddm-plugin-fade-in">
                    <td class="ddm-plugin-td ddm-plugin-td-type" data-label="${ddm_public_ajax?.strings?.type || 'Type'}">
                        <span class="ddm-plugin-type-badge ddm-plugin-type-${typeClass}">
                            ${this.escapeHtml(row.type)}
                        </span>
                    </td>
                    <td class="ddm-plugin-td ddm-plugin-td-domain" data-label="${ddm_public_ajax?.strings?.domain || 'Domain'}">
                        <div class="ddm-plugin-domain-cell">
                            <span class="ddm-plugin-domain-name">${this.escapeHtml(row.domain)}</span>
                            <button type="button" class="ddm-plugin-copy-domain" title="${ddm_public_ajax?.strings?.copy_domain || 'Copy domain'}">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                    <path d="m5 15h4a2 2 0 0 1 2 2v4"></path>
                                </svg>
                            </button>
                        </div>
                    </td>
                    <td class="ddm-plugin-td ddm-plugin-td-da" data-label="${ddm_public_ajax?.strings?.da || 'DA'}">
                        <div class="ddm-plugin-da-container">
                            <div class="ddm-plugin-da-score ddm-plugin-da-${daClass}">
                                <span class="ddm-plugin-da-number">${row.da}</span>
                            </div>
                            <div class="ddm-plugin-da-bar">
                                <div class="ddm-plugin-da-fill" style="width: ${row.da}%;"></div>
                            </div>
                        </div>
                    </td>
                    <td class="ddm-plugin-td ddm-plugin-td-traffic" data-label="${ddm_public_ajax?.strings?.traffic || 'Traffic'}">
                        <div class="ddm-plugin-traffic-container">
                            <span class="ddm-plugin-traffic-number">${row.traffic_formatted}</span>
                            <div class="ddm-plugin-traffic-indicator">
                                ${this.getTrafficIcon(row.traffic)}
                            </div>
                        </div>
                    </td>
                    <td class="ddm-plugin-td ddm-plugin-td-age" data-label="${ddm_public_ajax?.strings?.age || 'Age'}">
                        <div class="ddm-plugin-age-container">
                            <span class="ddm-plugin-age-number">${row.age}</span>
                            <span class="ddm-plugin-age-unit">${ddm_public_ajax?.strings?.years || 'years'}</span>
                        </div>
                    </td>
                    <td class="ddm-plugin-td ddm-plugin-td-emd" data-label="${ddm_public_ajax?.strings?.emd || 'EMD'}">
                        <span class="ddm-plugin-emd-badge ${row.emd ? 'ddm-plugin-emd-yes' : 'ddm-plugin-emd-no'}">
                            ${row.emd ? 
                                '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20,6 9,17 4,12"></polyline></svg>' : 
                                '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>'
                            }
                        </span>
                    </td>
                </tr>
            `;
        },

        getTrafficIcon: function(traffic) {
            if (traffic > 100000) {
                return '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22,12 18,8 18,16 22,12"></polyline><polyline points="8,12 4,8 4,16 8,12"></polyline></svg>';
            } else if (traffic > 10000) {
                return '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15,10 20,15 15,20"></polyline></svg>';
            } else {
                return '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9,10 4,15 9,20"></polyline></svg>';
            }
        },

        getEmptyStateHTML: function() {
            return `
                <tr>
                    <td colspan="6" class="ddm-plugin-td">
                        <div class="ddm-plugin-empty-state">
                            <div class="ddm-plugin-empty-icon">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <path d="m21 21-4.35-4.35"></path>
                                </svg>
                            </div>
                            <h3 class="ddm-plugin-empty-title">${ddm_public_ajax?.strings?.no_results || 'No domains found'}</h3>
                            <p class="ddm-plugin-empty-text">Try adjusting your search or filter criteria</p>
                        </div>
                    </td>
                </tr>
            `;
        },

        updateStats: function(stats) {
            if (!stats) return;
            
            $('.ddm-plugin-stat-card').each(function(index) {
                const $number = $(this).find('.ddm-plugin-stat-number');
                const $label = $(this).find('.ddm-plugin-stat-label').text().toLowerCase();
                
                if ($label.includes('domain')) {
                    $number.text(stats.total_domains || 0);
                } else if ($label.includes('avg') || $label.includes('da')) {
                    $number.text(stats.avg_da || 0);
                } else if ($label.includes('traffic')) {
                    $number.text(stats.total_traffic ? stats.total_traffic.toLocaleString() : 0);
                }
            });
        },

        animateRows: function() {
            setTimeout(() => {
                $('.ddm-plugin-table-row').addClass('ddm-plugin-fade-in');
            }, 10);
        },

        handleKeyNavigation: function(e) {
            if (e.key === 'Escape') {
                const $panel = $('#ddm-filter-panel');
                if ($panel.hasClass('active')) {
                    this.closeFilterPanel();
                }
            }
        },

        handleOutsideClick: function(e) {
            const $target = $(e.target);
            const $filterDropdown = $('.ddm-plugin-filter-dropdown');
            const $panel = $('#ddm-filter-panel');
            
            if ($panel.hasClass('active') && !$target.closest($filterDropdown).length) {
                this.closeFilterPanel();
            }
        },

        showLoading: function() {
            const $tbody = $('#ddm-table-body');
            $tbody.html(`
                <tr>
                    <td colspan="6" class="ddm-plugin-td">
                        <div class="ddm-plugin-loading">
                            <span class="ddm-plugin-spinner"></span>
                            ${ddm_public_ajax?.strings?.loading || 'Loading...'}
                        </div>
                    </td>
                </tr>
            `);
        },

        hideLoading: function() {
            // Loading will be replaced by actual content or empty state
        },

        showError: function(message) {
            const $tbody = $('#ddm-table-body');
            $tbody.html(`
                <tr>
                    <td colspan="6" class="ddm-plugin-td">
                        <div class="ddm-plugin-empty-state">
                            <div class="ddm-plugin-empty-icon">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="15" y1="9" x2="9" y2="15"></line>
                                    <line x1="9" y1="9" x2="15" y2="15"></line>
                                </svg>
                            </div>
                            <h3 class="ddm-plugin-empty-title">Error</h3>
                            <p class="ddm-plugin-empty-text">${this.escapeHtml(message)}</p>
                        </div>
                    </td>
                </tr>
            `);
        },

        showNotification: function(message, type = 'success') {
            const $notification = $(`
                <div class="ddm-plugin-notification ${type}">
                    ${this.escapeHtml(message)}
                </div>
            `);
            
            $('body').append($notification);
            
            setTimeout(() => {
                $notification.addClass('show');
            }, 10);
            
            setTimeout(() => {
                $notification.removeClass('show');
                setTimeout(() => {
                    $notification.remove();
                }, 300);
            }, 3000);
        },

        escapeHtml: function(text) {
            if (typeof text !== 'string' && typeof text !== 'number') {
                return '';
            }
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.toString().replace(/[&<>"']/g, function(m) { return map[m]; });
        }
    };

    $(document).ready(function() {
        DDMPublic.init();
    });

})(jQuery);
