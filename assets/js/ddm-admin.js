/**
 * Domain Data Manager Admin JavaScript
 */

(function($) {
    'use strict';

    // Global DDM Admin object
    window.DDMAdmin = {
        init: function() {
            this.bindEvents();
            this.initializeComponents();
            this.initializeCharts();
        },

        bindEvents: function() {
            // Form events
            $(document).on('click', '#ddm-add-new-button', this.showAddForm);
            $(document).on('click', '#ddm-cancel-button', this.hideForm);
            $(document).on('submit', '#ddm-data-form', this.handleFormSubmit);
            
            // Table events
            $(document).on('click', '.ddm-edit-button', this.handleEdit);
            $(document).on('click', '.ddm-delete-button', this.handleDelete);
            $(document).on('click', '.ddm-sortable', this.handleSort);
            
            // Upload events
            $(document).on('submit', '#ddm-upload-csv-form', this.handleCSVUpload);
            $(document).on('change', '#ddm-csv-file', this.handleFileSelection);
            $(document).on('click', '.ddm-file-remove', this.removeSelectedFile);
            
            // Copy functionality
            $(document).on('click', '.ddm-copy-btn', this.copyToClipboard);
            
            // Settings events
            $(document).on('change', '#ddm_table_color_scheme', this.previewColorScheme);
        },

        initializeComponents: function() {
            // Initialize file upload drag and drop
            this.initFileUpload();
            
            // Initialize tooltips
            this.initTooltips();
            
            // Initialize auto-hide messages
            this.initAutoHideMessages();
            
            // Initialize real-time validation
            this.initFormValidation();
        },

        // Form Management
        showAddForm: function() {
            $('#ddm-form-wrapper').slideDown(300);
            $('#ddm-form-title').html('<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>' + ddm_admin_ajax.strings.add_new_entry);
            $('#ddm-save-button').text(ddm_admin_ajax.strings.save_data);
            DDMAdmin.resetForm();
            $('#ddm-type').focus();
        },

        hideForm: function() {
            $('#ddm-form-wrapper').slideUp(300);
            DDMAdmin.resetForm();
        },

        resetForm: function() {
            $('#ddm-data-form')[0].reset();
            $('#ddm-edit-id').val('0');
            $('.ddm-input').removeClass('error');
            $('.ddm-error-message').remove();
        },

        handleFormSubmit: function(e) {
            e.preventDefault();
            
            const $form = $(this);
            const $submitBtn = $('#ddm-save-button');
            const formData = new FormData(this);
            const isEdit = $('#ddm-edit-id').val() !== '0';
            const action = isEdit ? 'ddm_update_data' : 'ddm_add_data';
            
            // Add AJAX action
            formData.append('action', action);
            
            // Validate form
            if (!DDMAdmin.validateForm($form)) {
                return;
            }
            
            // Show loading state
            $submitBtn.prop('disabled', true).addClass('ddm-loading');
            const originalText = $submitBtn.text();
            $submitBtn.text(ddm_admin_ajax.strings.processing);
            
            $.ajax({
                url: ddm_admin_ajax.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        DDMAdmin.showMessage(response.data.message, 'success');
                        DDMAdmin.hideForm();
                        DDMAdmin.refreshTable();
                        
                        // Show success animation
                        DDMAdmin.showSuccessAnimation();
                    } else {
                        DDMAdmin.showMessage(response.data.message, 'error');
                    }
                },
                error: function() {
                    DDMAdmin.showMessage(ddm_admin_ajax.strings.error, 'error');
                },
                complete: function() {
                    $submitBtn.prop('disabled', false).removeClass('ddm-loading').text(originalText);
                }
            });
        },

        // Edit functionality
        handleEdit: function(e) {
            e.preventDefault();
            
            const $btn = $(this);
            const id = $btn.data('id');
            
            // Show loading on button
            $btn.addClass('ddm-loading');
            
            $.ajax({
                url: ddm_admin_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'ddm_get_data',
                    id: id,
                    ddm_nonce_field: $('[name="ddm_nonce_field"]').val()
                },
                success: function(response) {
                    if (response.success) {
                        const data = response.data;
                        
                        // Populate form
                        $('#ddm-edit-id').val(data.id);
                        $('#ddm-type').val(data.type);
                        $('#ddm-domain').val(data.domain);
                        $('#ddm-da').val(data.da);
                        $('#ddm-traffic').val(data.traffic);
                        $('#ddm-age').val(data.age);
                        $('#ddm-emd').prop('checked', data.emd == '1');
                        
                        // Update form title
                        $('#ddm-form-title').html('<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="m18.5 2.5 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>Edit Domain Data');
                        $('#ddm-save-button').text('Update Data');
                        
                        // Show form
                        $('#ddm-form-wrapper').slideDown(300);
                        $('#ddm-type').focus();
                        
                        // Scroll to form
                        $('html, body').animate({
                            scrollTop: $('#ddm-form-wrapper').offset().top - 100
                        }, 500);
                    } else {
                        DDMAdmin.showMessage(response.data.message, 'error');
                    }
                },
                error: function() {
                    DDMAdmin.showMessage(ddm_admin_ajax.strings.error, 'error');
                },
                complete: function() {
                    $btn.removeClass('ddm-loading');
                }
            });
        },

        // Delete functionality
        handleDelete: function(e) {
            e.preventDefault();
            
            if (!confirm(ddm_admin_ajax.strings.confirm_delete)) {
                return;
            }
            
            const $btn = $(this);
            const id = $btn.data('id');
            const $row = $btn.closest('tr');
            
            // Show loading on button
            $btn.addClass('ddm-loading').prop('disabled', true);
            
            $.ajax({
                url: ddm_admin_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'ddm_delete_data',
                    id: id,
                    ddm_nonce_field: $('[name="ddm_nonce_field"]').val()
                },
                success: function(response) {
                    if (response.success) {
                        // Animate row removal
                        $row.addClass('ddm-fade-out');
                        setTimeout(function() {
                            $row.slideUp(300, function() {
                                $(this).remove();
                                DDMAdmin.updateTableCount();
                            });
                        }, 200);
                        
                        DDMAdmin.showMessage(response.data.message, 'success');
                    } else {
                        DDMAdmin.showMessage(response.data.message, 'error');
                    }
                },
                error: function() {
                    DDMAdmin.showMessage(ddm_admin_ajax.strings.error, 'error');
                },
                complete: function() {
                    $btn.removeClass('ddm-loading').prop('disabled', false);
                }
            });
        },

        // CSV Upload
        handleCSVUpload: function(e) {
            e.preventDefault();
            
            const $form = $(this);
            const $submitBtn = $('#ddm-upload-button');
            const $progressBar = $('.ddm-upload-progress');
            const formData = new FormData(this);
            
            // Validate file
            const fileInput = $('#ddm-csv-file')[0];
            if (!fileInput.files.length) {
                DDMAdmin.showMessage('Please select a CSV file.', 'error');
                return;
            }
            
            const file = fileInput.files[0];
            if (!file.name.toLowerCase().endsWith('.csv')) {
                DDMAdmin.showMessage('Please select a valid CSV file.', 'error');
                return;
            }
            
            // Add AJAX action
            formData.append('action', 'ddm_handle_csv_upload');
            
            // Show loading state
            $submitBtn.prop('disabled', true).addClass('ddm-loading');
            $progressBar.show();
            DDMAdmin.animateProgress();
            
            $.ajax({
                url: ddm_admin_ajax.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        const data = response.data;
                        DDMAdmin.showMessage(data.message, 'success');
                        
                        // Reset form
                        $form[0].reset();
                        DDMAdmin.hideFileInfo();
                        
                        // Show success stats
                        DDMAdmin.showUploadStats(data.inserted, data.updated);
                    } else {
                        DDMAdmin.showMessage(response.data.message, 'error');
                    }
                },
                error: function() {
                    DDMAdmin.showMessage('Upload failed. Please try again.', 'error');
                },
                complete: function() {
                    $submitBtn.prop('disabled', false).removeClass('ddm-loading');
                    $progressBar.hide();
                }
            });
        },

        // File upload handling
        initFileUpload: function() {
            const $uploadArea = $('.ddm-upload-area');
            const $fileInput = $('#ddm-csv-file');
            
            // Drag and drop
            $uploadArea.on('dragover dragenter', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).addClass('dragover');
            });
            
            $uploadArea.on('dragleave dragend drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('dragover');
            });
            
            $uploadArea.on('drop', function(e) {
                const files = e.originalEvent.dataTransfer.files;
                if (files.length) {
                    $fileInput[0].files = files;
                    DDMAdmin.handleFileSelection();
                }
            });
        },

        handleFileSelection: function() {
            const fileInput = $('#ddm-csv-file')[0];
            const $fileInfo = $('.ddm-file-info');
            const $fileName = $('.ddm-file-name');
            
            if (fileInput.files.length) {
                const file = fileInput.files[0];
                $fileName.text(file.name);
                $fileInfo.show();
            } else {
                DDMAdmin.hideFileInfo();
            }
        },

        removeSelectedFile: function() {
            $('#ddm-csv-file').val('');
            DDMAdmin.hideFileInfo();
        },

        hideFileInfo: function() {
            $('.ddm-file-info').hide();
            $('.ddm-file-name').text('');
        },

        // Sorting functionality
        handleSort: function(e) {
            e.preventDefault();
            
            const $th = $(this);
            const sortBy = $th.data('sort');
            let sortOrder = 'asc';
            
            // Toggle sort order if same column
            if ($th.hasClass('ddm-sorted-asc')) {
                sortOrder = 'desc';
                $th.removeClass('ddm-sorted-asc').addClass('ddm-sorted-desc');
            } else {
                $('.ddm-sortable').removeClass('ddm-sorted-asc ddm-sorted-desc');
                $th.addClass('ddm-sorted-asc');
            }
            
            // Sort table rows
            DDMAdmin.sortTable(sortBy, sortOrder);
        },

        sortTable: function(column, order) {
            const $tbody = $('#the-list');
            const $rows = $tbody.find('tr').not('.ddm-no-items');
            
            $rows.sort(function(a, b) {
                const aVal = DDMAdmin.getSortValue($(a), column);
                const bVal = DDMAdmin.getSortValue($(b), column);
                
                if (order === 'asc') {
                    return aVal < bVal ? -1 : aVal > bVal ? 1 : 0;
                } else {
                    return aVal > bVal ? -1 : aVal < bVal ? 1 : 0;
                }
            });
            
            $tbody.append($rows);
        },

        getSortValue: function($row, column) {
            const $cell = $row.find('td').eq(DDMAdmin.getColumnIndex(column));
            let value = $cell.text().trim();
            
            // Handle numeric columns
            if (['da', 'traffic', 'age'].includes(column)) {
                value = parseInt(value.replace(/,/g, '')) || 0;
            }
            
            return value;
        },

        getColumnIndex: function(column) {
            const columns = ['id', 'type', 'domain', 'da', 'traffic', 'age', 'emd', 'actions'];
            return columns.indexOf(column);
        },

        // Form validation
        validateForm: function($form) {
            let isValid = true;
            
            // Clear previous errors
            $('.ddm-input').removeClass('error');
            $('.ddm-error-message').remove();
            
            // Required fields
            $form.find('[required]').each(function() {
                const $field = $(this);
                const value = $field.val().trim();
                
                if (!value) {
                    DDMAdmin.showFieldError($field, 'This field is required.');
                    isValid = false;
                }
            });
            
            // Domain validation
            const domain = $('#ddm-domain').val().trim();
            if (domain && !DDMAdmin.isValidDomain(domain)) {
                DDMAdmin.showFieldError($('#ddm-domain'), 'Please enter a valid domain name.');
                isValid = false;
            }
            
            // DA validation (0-100)
            const da = parseInt($('#ddm-da').val());
            if (isNaN(da) || da < 0 || da > 100) {
                DDMAdmin.showFieldError($('#ddm-da'), 'DA must be between 0 and 100.');
                isValid = false;
            }
            
            // Traffic validation (non-negative)
            const traffic = parseInt($('#ddm-traffic').val());
            if (isNaN(traffic) || traffic < 0) {
                DDMAdmin.showFieldError($('#ddm-traffic'), 'Traffic must be 0 or greater.');
                isValid = false;
            }
            
            // Age validation (non-negative)
            const age = parseInt($('#ddm-age').val());
            if (isNaN(age) || age < 0) {
                DDMAdmin.showFieldError($('#ddm-age'), 'Age must be 0 or greater.');
                isValid = false;
            }
            
            return isValid;
        },

        isValidDomain: function(domain) {
            const regex = /^[a-zA-Z0-9][a-zA-Z0-9-_]*\.([a-zA-Z]{2,}|[a-zA-Z]{2,}\.[a-zA-Z]{2,})$/;
            return regex.test(domain);
        },

        showFieldError: function($field, message) {
            $field.addClass('error');
            $field.after('<div class="ddm-error-message">' + message + '</div>');
        },

        initFormValidation: function() {
            // Real-time validation
            $('#ddm-domain').on('blur', function() {
                const value = $(this).val().trim();
                if (value && !DDMAdmin.isValidDomain(value)) {
                    DDMAdmin.showFieldError($(this), 'Please enter a valid domain name.');
                } else {
                    $(this).removeClass('error').next('.ddm-error-message').remove();
                }
            });
            
            // Numeric field validation
            $('#ddm-da, #ddm-traffic, #ddm-age').on('input', function() {
                const $field = $(this);
                const value = parseInt($field.val());
                const fieldName = $field.attr('id').replace('ddm-', '').toUpperCase();
                
                $field.removeClass('error').next('.ddm-error-message').remove();
                
                if ($field.attr('id') === 'ddm-da' && (isNaN(value) || value < 0 || value > 100)) {
                    DDMAdmin.showFieldError($field, 'DA must be between 0 and 100.');
                } else if ($field.attr('id') !== 'ddm-da' && (isNaN(value) || value < 0)) {
                    DDMAdmin.showFieldError($field, fieldName + ' must be 0 or greater.');
                }
            });
        },

        // Charts initialization
        initializeCharts: function() {
            if (typeof Chart === 'undefined') {
                return; // Charts not loaded
            }
            
            // Only initialize on dashboard page
            if (!$('#ddm-da-chart').length) {
                return;
            }
            
            this.initDAChart();
            this.initTrafficChart();
        },

        initDAChart: function() {
            const ctx = document.getElementById('ddm-da-chart');
            if (!ctx || !window.ddmAnalytics) return;
            
            const data = window.ddmAnalytics.da_distribution;
            
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Low (0-20)', 'Fair (21-40)', 'Good (41-60)', 'Great (61-80)', 'Excellent (81-100)'],
                    datasets: [{
                        data: [
                            data.low || 0,
                            data.fair || 0,
                            data.good || 0,
                            data.great || 0,
                            data.excellent || 0
                        ],
                        backgroundColor: [
                            '#ef4444',
                            '#f97316',
                            '#eab308',
                            '#22c55e',
                            '#3b82f6'
                        ],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                    return `${label}: ${value} domains (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        },

        initTrafficChart: function() {
            const ctx = document.getElementById('ddm-traffic-chart');
            if (!ctx || !window.ddmAnalytics) return;
            
            const data = window.ddmAnalytics.traffic_by_da;
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Low (0-20)', 'Fair (21-40)', 'Good (41-60)', 'Great (61-80)', 'Excellent (81-100)'],
                    datasets: [{
                        label: 'Average Traffic',
                        data: [
                            data.low || 0,
                            data.fair || 0,
                            data.good || 0,
                            data.great || 0,
                            data.excellent || 0
                        ],
                        backgroundColor: [
                            'rgba(239, 68, 68, 0.8)',
                            'rgba(249, 115, 22, 0.8)',
                            'rgba(234, 179, 8, 0.8)',
                            'rgba(34, 197, 94, 0.8)',
                            'rgba(59, 130, 246, 0.8)'
                        ],
                        borderColor: [
                            '#ef4444',
                            '#f97316',
                            '#eab308',
                            '#22c55e',
                            '#3b82f6'
                        ],
                        borderWidth: 2,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const value = context.parsed.y;
                                    return `Average Traffic: ${value.toLocaleString()}`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        },

        // Utility functions
        showMessage: function(message, type) {
            const $messageDiv = $('#ddm-message');
            const iconMap = {
                success: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20,6 9,17 4,12"></polyline></svg>',
                error: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>',
                warning: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.73,18L12,2.73L2.27,18c-.26.45-.26,1.01,0,1.46.26.45.73.73,1.25.73h19.96c.52,0,.99-.28,1.25-.73.26-.45.26-1.01,0-1.46Z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>',
                info: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>'
            };
            
            $messageDiv
                .removeClass('notice-success notice-error notice-warning notice-info')
                .addClass('notice-' + type)
                .html('<div style="display: flex; align-items: center; gap: 8px;">' + iconMap[type] + '<span>' + message + '</span></div>')
                .slideDown(300);
            
            // Auto-hide after 5 seconds
            setTimeout(function() {
                $messageDiv.slideUp(300);
            }, 5000);
        },

        refreshTable: function() {
            // Reload the page to refresh table data
            // In a real implementation, you might want to use AJAX to refresh just the table
            window.location.reload();
        },

        updateTableCount: function() {
            const $tbody = $('#the-list');
            const count = $tbody.find('tr').not('.ddm-no-items').length;
            $('.ddm-count').text(count + ' entries');
            
            // Show empty state if no entries
            if (count === 0) {
                $tbody.html('<tr class="ddm-no-items"><td class="ddm-td" colspan="8"><div class="ddm-empty-state"><svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg><h4>No data found</h4><p>Start by adding your first domain entry or uploading a CSV file.</p></div></td></tr>');
            }
        },

        showSuccessAnimation: function() {
            // Create a success checkmark animation
            const $success = $('<div class="ddm-success-animation"><svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="20,6 9,17 4,12"></polyline></svg></div>');
            
            $('body').append($success);
            
            setTimeout(function() {
                $success.remove();
            }, 2000);
        },

        showUploadStats: function(inserted, updated) {
            const $stats = $('<div class="ddm-upload-stats"><h4>Upload Complete!</h4><p>' + inserted + ' records inserted, ' + updated + ' records updated.</p></div>');
            
            $('.ddm-upload-card .ddm-card-content').prepend($stats);
            
            setTimeout(function() {
                $stats.fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
        },

        animateProgress: function() {
            const $fill = $('.ddm-progress-fill');
            let width = 0;
            const interval = setInterval(function() {
                width += Math.random() * 15;
                if (width >= 90) {
                    width = 90;
                    clearInterval(interval);
                }
                $fill.css('width', width + '%');
            }, 200);
        },

        copyToClipboard: function(e) {
            e.preventDefault();
            
            const $btn = $(this);
            const textToCopy = $btn.prev('code').text() || $btn.data('copy');
            
            if (navigator.clipboard) {
                navigator.clipboard.writeText(textToCopy).then(function() {
                    DDMAdmin.showCopySuccess($btn);
                }).catch(function() {
                    DDMAdmin.fallbackCopyTextToClipboard(textToCopy, $btn);
                });
            } else {
                DDMAdmin.fallbackCopyTextToClipboard(textToCopy, $btn);
            }
        },

        fallbackCopyTextToClipboard: function(text, $btn) {
            const textArea = document.createElement("textarea");
            textArea.value = text;
            textArea.style.position = "fixed";
            textArea.style.left = "-999999px";
            textArea.style.top = "-999999px";
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                document.execCommand('copy');
                DDMAdmin.showCopySuccess($btn);
            } catch (err) {
                console.error('Fallback: Oops, unable to copy', err);
            }
            
            document.body.removeChild(textArea);
        },

        showCopySuccess: function($btn) {
            const originalHtml = $btn.html();
            $btn.html('<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20,6 9,17 4,12"></polyline></svg>');
            
            setTimeout(function() {
                $btn.html(originalHtml);
            }, 1500);
        },

        previewColorScheme: function() {
            const color = $(this).val();
            // You could add a live preview here
            console.log('Color scheme changed to:', color);
        },

        initTooltips: function() {
            // Simple tooltip implementation
            $('[title]').hover(
                function() {
                    const title = $(this).attr('title');
                    $(this).data('tipText', title).removeAttr('title');
                    $('<div class="ddm-tooltip"></div>')
                        .text(title)
                        .appendTo('body')
                        .fadeIn('slow');
                },
                function() {
                    $(this).attr('title', $(this).data('tipText'));
                    $('.ddm-tooltip').remove();
                }
            ).mousemove(function(e) {
                $('.ddm-tooltip').css({
                    top: e.pageY + 10,
                    left: e.pageX + 20
                });
            });
        },

        initAutoHideMessages: function() {
            // Auto-hide WordPress admin notices after 7 seconds
            setTimeout(function() {
                $('.notice:not(.notice-error)').fadeOut();
            }, 7000);
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        DDMAdmin.init();
    });

    // Add CSS for dynamic elements
    const dynamicCSS = `
        <style>
        .ddm-loading {
            opacity: 0.6;
            pointer-events: none;
        }
        
        .ddm-fade-out {
            opacity: 0.3;
            transform: scale(0.95);
            transition: all 0.3s ease;
        }
        
        .ddm-error-message {
            color: #dc2626;
            font-size: 12px;
            margin-top: 4px;
            font-weight: 500;
        }
        
        .ddm-input.error {
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }
        
        .ddm-success-animation {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            border-radius: 50%;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            z-index: 10000;
            color: #22c55e;
            animation: successPop 0.6s ease-out;
        }
        
        @keyframes successPop {
            0% { transform: translate(-50%, -50%) scale(0); opacity: 0; }
            50% { transform: translate(-50%, -50%) scale(1.1); opacity: 1; }
            100% { transform: translate(-50%, -50%) scale(1); opacity: 1; }
        }
        
        .ddm-upload-stats {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 20px;
            color: #047857;
        }
        
        .ddm-upload-stats h4 {
            margin: 0 0 8px 0;
            color: #047857;
        }
        
        .ddm-upload-stats p {
            margin: 0;
            font-size: 14px;
        }
        
        .ddm-upload-area.dragover {
            border-color: #3b82f6;
            background: rgba(59, 130, 246, 0.05);
        }
        
        .ddm-tooltip {
            position: absolute;
            z-index: 10000;
            background: #1f2937;
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 12px;
            pointer-events: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        </style>
    `;
    
    $('head').append(dynamicCSS);

})(jQuery);
