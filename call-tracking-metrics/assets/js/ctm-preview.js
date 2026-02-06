/**
 * CTM Form Preview Module
 * 
 * Unified preview functionality for both Import and Manage Forms tabs.
 * Handles modal display, AJAX requests, script sanitization, and error handling.
 * 
 * @since 2.0.0
 */

(function($) {
    'use strict';

    // Global CTMPreview object
    window.CTMPreview = {
        
        /**
         * Configuration options
         */
        config: {
            modalSelector: '#ctm-preview-modal',
            loadingSelector: '#ctm-modal-loading, #ctm-preview-loading',
            contentSelector: '#ctm-modal-content, #ctm-preview-content',
            errorSelector: '#ctm-preview-error',
            errorMessageSelector: '#ctm-preview-error-message',
            closeSelector: '#ctm-close-modal, #ctm-preview-close, #ctm-preview-close-btn, .ctm-close-modal',
            ajaxUrl: window.ajaxurl || ''
        },

        /**
         * Initialize the preview system
         */
        init: function() {
            this.bindEvents();
        },

        /**
         * Bind global events
         */
        bindEvents: function() {
            const self = this;
            
            // Close modal events
            $(document).on('click', this.config.closeSelector, function(e) {
                e.preventDefault();
                e.stopPropagation();
                self.closeModal();
            });

            // Close modal on backdrop click
            $(document).on('click', this.config.modalSelector, function(e) {
                if (e.target === this) {
                    self.closeModal();
                }
            });

            // Close modal on Escape key
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && $(self.config.modalSelector).is(':visible')) {
                    self.closeModal();
                }
            });
        },

        /**
         * Show preview modal for CTM form (Import Tab)
         * 
         * @param {Object} options - Preview options
         * @param {string} options.ctmFormId - CTM form ID
         * @param {string} options.targetType - Target type (cf7, gf)
         * @param {string} options.nonce - Security nonce (required)
         * @param {string} options.action - AJAX action (default: ctm_preview_form)
         * @param {boolean} options.tabbed - Whether to use tabbed interface (default: true)
         */
        showCTMPreview: function(options) {
            const defaults = {
                action: 'ctm_preview_form',
                tabbed: true
            };
            
            const settings = $.extend({}, defaults, options);
            
            if (!settings.ctmFormId || !settings.targetType) {
                this.showError('Please select both a form and target type.');
                return;
            }

            if (!settings.nonce) {
                this.showError('Security token is missing. Please refresh the page.');
                return;
            }

            this.openModal();
            this.showLoading();

            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: settings.action,
                    nonce: settings.nonce,
                    ctm_form_id: settings.ctmFormId,
                    target_type: settings.targetType
                },
                success: (response) => {
                    this.hideLoading();
                    
                    if (response.success && response.data) {
                        if (settings.tabbed) {
                            this.showTabbedContent(response.data);
                        } else {
                            this.showSimpleContent(response.data.rendered_form || response.data.preview);
                        }
                    } else {
                        const errorMessage = (response.data && response.data.message) 
                            ? response.data.message 
                            : (response.message || 'Unknown error occurred');
                        this.showModalError(errorMessage);
                    }
                },
                error: () => {
                    this.hideLoading();
                    this.showModalError('Network error occurred while generating preview.');
                }
            });
        },

        /**
         * Show preview modal for WordPress form (Manage Tab)
         * 
         * @param {Object} options - Preview options
         * @param {string} options.formId - WordPress form ID
         * @param {string} options.formType - Form type (cf7, gf)
         * @param {string} options.formTitle - Form title for display
         * @param {string} options.nonce - Security nonce (required)
         * @param {string} options.action - AJAX action (default: ctm_preview_wp_form)
         * @param {boolean} options.tabbed - Whether to use tabbed interface (default: false)
         */
        showWPPreview: function(options) {
            const defaults = {
                action: 'ctm_preview_wp_form',
                tabbed: false
            };
            
            const settings = $.extend({}, defaults, options);
            
            if (!settings.formId || !settings.formType) {
                this.showError('Missing form ID or type.');
                return;
            }

            if (!settings.nonce) {
                this.showError('Security token is missing. Please refresh the page.');
                return;
            }

            // Set form title if provided
            if (settings.formTitle) {
                $('#ctm-preview-form-title').text(settings.formTitle);
            }

            this.openModal();
            this.showLoading();

            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: settings.action,
                    nonce: settings.nonce,
                    form_id: settings.formId,
                    form_type: settings.formType
                },
                success: (response) => {
                    this.hideLoading();
                    
                    if (response.success && response.data && response.data.preview) {
                        if (settings.tabbed) {
                            this.showTabbedContent({
                                raw_code: response.data.raw_code || 'Raw code not available for existing WordPress forms',
                                rendered_form: response.data.preview
                            });
                        } else {
                            this.showSimpleContent(response.data.preview);
                        }
                    } else {
                        const errorMessage = (response.data && response.data.message) 
                            ? response.data.message 
                            : 'Unknown error occurred';
                        this.showModalError(errorMessage);
                    }
                },
                error: () => {
                    this.hideLoading();
                    this.showModalError('Network error occurred while loading preview.');
                }
            });
        },

        /**
         * Show tabbed content (Raw Code + Rendered Form)
         * 
         * @param {Object} data - Preview data
         * @param {string} data.raw_code - Raw form code
         * @param {string} data.rendered_form - Rendered form HTML
         */
        showTabbedContent: function(data) {
            const modalContent = $(this.config.contentSelector);
            
            // Create tabbed interface
            modalContent.html(`
                <!-- Tabs -->
                <div class="border-b border-gray-200 mb-6">
                    <nav class="flex space-x-8">
                        <button type="button" id="ctm-tab-raw" class="ctm-tab-button py-2 px-1 border-b-2 border-blue-500 text-blue-600 font-medium text-sm">
                            Raw Code
                        </button>
                        <button type="button" id="ctm-tab-rendered" class="ctm-tab-button py-2 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700 font-medium text-sm">
                            Rendered Form
                        </button>
                    </nav>
                </div>

                <!-- Raw Code Tab -->
                <div id="ctm-tab-raw-content" class="ctm-tab-content">
                    <div class="bg-gray-50 rounded-lg p-4 mb-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Raw Form Code</h4>
                        <p class="text-xs text-gray-500 mb-3">This is the raw code that will be generated for the form.</p>
                    </div>
                    <div id="ctm-raw-code" class="bg-gray-50 border border-gray-200 rounded p-3 font-mono text-xs overflow-x-auto text-gray-800 whitespace-pre-wrap"></div>
                </div>

                <!-- Rendered Form Tab -->
                <div id="ctm-tab-rendered-content" class="ctm-tab-content hidden">
                    <div class="bg-blue-50 rounded-lg p-4 mb-4">
                        <h4 class="text-sm font-medium text-blue-700 mb-2">Rendered Form Preview</h4>
                        <p class="text-xs text-blue-600 mb-3">This is how the form will appear after import. Note: This is a static preview - interactive features will work after import.</p>
                    </div>
                    <div id="ctm-rendered-form" class="bg-white border border-gray-200 rounded-lg p-6"></div>
                </div>
            `);

            // Set content with script sanitization
            $('#ctm-raw-code').text(data.raw_code || 'No raw code available');
            $('#ctm-rendered-form').html(this.sanitizeHTML(data.rendered_form || 'No preview available'));

            // Show content
            modalContent.removeClass('hidden');

            // Bind tab switching
            this.bindTabSwitching();
        },

        /**
         * Show simple content (single preview area)
         * 
         * @param {string} html - HTML content to display
         */
        showSimpleContent: function(html) {
            const modalContent = $(this.config.contentSelector);
            
            // Clean and set content
            const cleanedHTML = this.sanitizeHTML(html);
            modalContent.html(cleanedHTML).removeClass('hidden');
        },

        /**
         * Show error in modal content area
         * 
         * @param {string} message - Error message
         */
        showModalError: function(message) {
            const modalContent = $(this.config.contentSelector);
            const errorSelector = $(this.config.errorSelector);
            const errorMessageSelector = $(this.config.errorMessageSelector);
            
            // Try to use dedicated error elements first
            if (errorSelector.length && errorMessageSelector.length) {
                errorMessageSelector.text(message);
                errorSelector.show();
                modalContent.hide();
            } else {
                // Fallback: show error in content area
                modalContent.html(`
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="text-red-800 font-medium">Preview Error</div>
                        <div class="text-red-700 text-sm mt-2">${this.escapeHTML(message)}</div>
                    </div>
                `).removeClass('hidden');
            }
        },

        /**
         * Show inline error message (not in modal)
         * 
         * @param {string} message - Error message
         */
        showError: function(message) {
            // This would be implemented based on each tab's error display method
            if (window.showMessage) {
                window.showMessage('error', message);
            } else {
                alert(message);
            }
        },

        /**
         * Sanitize HTML content to remove problematic scripts
         * 
         * @param {string} html - HTML content to sanitize
         * @return {string} Sanitized HTML
         */
        sanitizeHTML: function(html) {
            if (!html || typeof html !== 'string') {
                return html;
            }

            let cleaned = html;
            
            // Remove Gravity Forms scripts that cause errors in admin context
            cleaned = cleaned.replace(/<script[^>]*gform[^>]*>[\s\S]*?<\/script>/gi, '');
            cleaned = cleaned.replace(/gform\.initializeOnLoaded[^;]*;?/gi, '');
            
            // Remove Contact Form 7 scripts that might cause similar issues
            cleaned = cleaned.replace(/<script[^>]*wpcf7[^>]*>[\s\S]*?<\/script>/gi, '');
            
            // Remove any other inline scripts that might cause issues
            cleaned = cleaned.replace(/<script[^>]*>[\s\S]*?<\/script>/gi, '');
            
            return cleaned;
        },

        /**
         * Escape HTML for safe display
         * 
         * @param {string} text - Text to escape
         * @return {string} Escaped text
         */
        escapeHTML: function(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },

        /**
         * Open the preview modal
         */
        openModal: function() {
            const modal = $(this.config.modalSelector);
            modal.removeClass('hidden').addClass('flex').css('display', 'flex');
            $('body').addClass('overflow-hidden');
        },

        /**
         * Close the preview modal
         */
        closeModal: function() {
            const modal = $(this.config.modalSelector);
            modal.addClass('hidden').removeClass('flex').css('display', 'none');
            $('body').removeClass('overflow-hidden');
            
            // Reset modal state
            this.hideLoading();
            $(this.config.contentSelector).addClass('hidden').html('');
            $(this.config.errorSelector).hide();
        },

        /**
         * Show loading state
         */
        showLoading: function() {
            $(this.config.loadingSelector).removeClass('hidden');
            $(this.config.contentSelector).addClass('hidden');
            $(this.config.errorSelector).hide();
        },

        /**
         * Hide loading state
         */
        hideLoading: function() {
            $(this.config.loadingSelector).addClass('hidden');
        },

        /**
         * Bind tab switching functionality
         */
        bindTabSwitching: function() {
            $('.ctm-tab-button').off('click').on('click', function() {
                const $this = $(this);
                const tabId = $this.attr('id');
                
                // Update button states
                $('.ctm-tab-button').removeClass('border-blue-500 text-blue-600').addClass('border-transparent text-gray-500');
                $this.removeClass('border-transparent text-gray-500').addClass('border-blue-500 text-blue-600');
                
                // Show/hide content
                $('.ctm-tab-content').addClass('hidden');
                $('#' + tabId + '-content').removeClass('hidden');
            });
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        CTMPreview.init();
    });

})(jQuery);