<?php
/**
 * Form Import Tab View
 * 
 * This view file displays the form import tab in the CallTrackingMetrics admin interface, allowing users to import and sync forms from CTM.
 * 
 * @package     CallTrackingMetrics
 * @subpackage  Views
 * @author      CallTrackingMetrics Team
 * @copyright   2024 CallTrackingMetrics
 * @license     GPL-2.0+
 * @version     2.0.0
 * @link        https://calltrackingmetrics.com
 * @since       1.0.0
 */

// Form Import tab view
$has_api_credentials = !empty($apiKey) && !empty($apiSecret);
$has_form_plugins = $cf7_available || $gf_available;
?>
<div class="mb-12">
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-8 mb-8">
        <div class="flex items-center mb-6 border-b border-blue-100 pb-4">
            <svg class="w-7 h-7 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
            </svg>
            <h2 class="text-2xl font-bold text-blue-800 tracking-tight"><?php _e('Import Forms from CallTrackingMetrics', 'call-tracking-metrics'); ?></h2>
        </div>
        
        <?php if (!$has_api_credentials): ?>
            <!-- No API credentials -->
            <div class="text-center py-12">
                <div class="bg-yellow-50 rounded-lg p-8 max-w-md mx-auto">
                    <svg class="w-12 h-12 text-yellow-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    <h3 class="text-lg font-semibold text-yellow-800 mb-2"><?php _e('API Credentials Required', 'call-tracking-metrics'); ?></h3>
                    <p class="text-yellow-700 mb-6"><?php _e('You need to configure your CallTrackingMetrics API credentials to import forms.', 'call-tracking-metrics'); ?></p>
                    <a href="<?= esc_url(admin_url('admin.php?page=call-tracking-metrics&tab=api')) ?>" 
                       class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold px-6 py-3 rounded-lg shadow transition"><?php _e('Configure API Settings', 'call-tracking-metrics'); ?></a>
                </div>
            </div>
        <?php elseif (!$has_form_plugins): ?>
            <!-- No form plugins installed -->
            <div class="text-center py-12">
                <div class="bg-gray-50 rounded-lg p-8 max-w-md mx-auto">
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2"><?php _e('No Form Plugins Installed', 'call-tracking-metrics'); ?></h3>
                    <p class="text-gray-600 mb-6"><?php _e('To import forms, you need to install at least one supported form plugin.', 'call-tracking-metrics'); ?></p>
                    <div class="space-y-3">
                        <a href="<?= esc_url(admin_url('plugin-install.php?s=contact+form+7&tab=search&type=term')) ?>" 
                           class="block bg-blue-600 hover:bg-blue-700 text-white font-bold px-6 py-3 rounded-lg shadow transition"><?php _e('Install Contact Form 7', 'call-tracking-metrics'); ?></a>
                        <a href="https://www.gravityforms.com/" target="_blank" rel="noopener"
                           class="block bg-green-600 hover:bg-green-700 text-white font-bold px-6 py-3 rounded-lg shadow transition"><?php _e('Get Gravity Forms', 'call-tracking-metrics'); ?></a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Import interface -->
            <div class="space-y-8">
                <!-- Step 1: Load Available Forms -->
                <div class="bg-blue-50 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-blue-800 mb-4"><?php _e('Step 1: Load Available Forms', 'call-tracking-metrics'); ?></h3>
                    <p class="text-blue-700 mb-4"><?php _e('Click the button below to fetch available forms from your CallTrackingMetrics account.', 'call-tracking-metrics'); ?></p>
                    <button type="button" id="ctm-load-forms" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-6 py-3 rounded-lg shadow transition">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                        </svg>
                        <?php _e('Load Available Forms', 'call-tracking-metrics'); ?>
                    </button>
                    <div id="ctm-loading-forms" class="mt-4 hidden">
                        <div class="flex items-center text-blue-600">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <?php _e('Loading forms from CallTrackingMetrics...', 'call-tracking-metrics'); ?>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Select Form and Target -->
                <div id="ctm-form-selection" class="hidden">
                    <h3 class="text-lg font-semibold text-gray-800 mb-6"><?php _e('Step 2: Select Form and Import Target', 'call-tracking-metrics'); ?></h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                        <!-- Form Selection -->
                        <div>
                            <label class="block mb-3 text-gray-600 font-medium"><?php _e('Select Form to Import', 'call-tracking-metrics'); ?></label>
                            <select id="ctm-form-select" class="block w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 text-base min-h-[44px]">
                                <option value=""><?php _e('Choose a form...', 'call-tracking-metrics'); ?></option>
                            </select>
                        </div>

                        <!-- Target Type Selection -->
                        <div>
                            <label class="block mb-3 text-gray-600 font-medium"><?php _e('Import to', 'call-tracking-metrics'); ?></label>
                            <select id="ctm-target-type" class="block w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 text-base min-h-[44px]">
                                <option value=""><?php _e('Select target...', 'call-tracking-metrics'); ?></option>
                                <?php if ($cf7_available): ?>
                                    <option value="cf7"><?php _e('Contact Form 7', 'call-tracking-metrics'); ?></option>
                                <?php endif; ?>
                                <?php if ($gf_available): ?>
                                    <option value="gf"><?php _e('Gravity Forms', 'call-tracking-metrics'); ?></option>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Form Title (Hidden until form and target selected) -->
                    <div id="ctm-form-title-section" class="mt-8 hidden">
                        <label class="block mb-3 text-gray-600 font-medium"><?php _e('Form Title', 'call-tracking-metrics'); ?></label>
                        <input type="text" id="ctm-form-title" class="block w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 text-base min-h-[44px]" 
                               placeholder="<?php _e('Enter a title for the imported form...', 'call-tracking-metrics'); ?>">
                    </div>

                    <!-- Action Buttons (Hidden until form and target selected) -->
                    <div id="ctm-action-buttons" class="flex gap-4 mt-8 hidden">
                        <button type="button" id="ctm-preview-form" class="bg-blue-500 hover:bg-blue-600 text-white font-medium px-6 py-3 rounded-lg shadow transition">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <?php _e('Preview Form', 'call-tracking-metrics'); ?>
                        </button>
                        <button type="button" id="ctm-import-form" class="bg-green-600 hover:bg-green-700 text-white font-bold px-6 py-3 rounded-lg shadow transition">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                            </svg>
                            <?php _e('Import Form', 'call-tracking-metrics'); ?>
                        </button>
                    </div>
                </div>



                <!-- Import Status -->
                <div id="ctm-import-status" class="hidden">
                    <div id="ctm-import-loading" class="bg-blue-50 rounded-lg p-4 hidden">
                        <div class="flex items-center text-blue-600">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <?php _e('Importing form...', 'call-tracking-metrics'); ?>
                        </div>
                    </div>
                    <div id="ctm-import-success" class="bg-green-50 rounded-lg p-4 hidden">
                        <div class="flex items-center text-green-600">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span id="ctm-success-message"></span>
                        </div>
                    </div>
                    <div id="ctm-import-error" class="bg-red-50 rounded-lg p-4 hidden">
                        <div class="flex items-center text-red-600">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            <span id="ctm-error-message"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Help Section -->
            <div class="mt-8 pt-6 border-t border-gray-200">
                <h3 class="text-lg font-semibold text-gray-700 mb-4"><?php _e('How Form Import Works', 'call-tracking-metrics'); ?></h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-800 mb-2"><?php _e('Contact Form 7', 'call-tracking-metrics'); ?></h4>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li>• <?php _e('Creates a new CF7 form with all fields', 'call-tracking-metrics'); ?></li>
                            <li>• <?php _e('Generates email templates automatically', 'call-tracking-metrics'); ?></li>
                            <li>• <?php _e('Maps field types appropriately', 'call-tracking-metrics'); ?></li>
                            <li>• <?php _e('Includes validation messages', 'call-tracking-metrics'); ?></li>
                        </ul>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-800 mb-2"><?php _e('Gravity Forms', 'call-tracking-metrics'); ?></h4>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li>• <?php _e('Creates a new GF form with all fields', 'call-tracking-metrics'); ?></li>
                            <li>• <?php _e('Sets up notifications automatically', 'call-tracking-metrics'); ?></li>
                            <li>• <?php _e('Configures confirmations', 'call-tracking-metrics'); ?></li>
                            <li>• <?php _e('Maps field types and options', 'call-tracking-metrics'); ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Preview Modal - Outside of space-y-8 container to avoid spacing issues -->
<div id="ctm-preview-modal" class="fixed inset-0 bg-black bg-opacity-60 items-center justify-center hidden" style="z-index: 999999;">
    <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[95vh] overflow-hidden mx-4 my-4">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <div class="flex items-center">
                <svg class="w-6 h-6 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
                <h3 class="text-xl font-semibold text-gray-800">Form Preview</h3>
            </div>
            <button type="button" id="ctm-close-modal" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Modal Content -->
        <div class="p-6 overflow-y-auto max-h-[calc(95vh-160px)]">
            <!-- Loading State -->
            <div id="ctm-modal-loading" class="hidden">
                <div class="flex flex-col items-center justify-center py-12">
                    <svg class="animate-spin h-8 w-8 text-blue-600 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="text-gray-600 font-medium">Generating form preview...</p>
                    <p class="text-gray-500 text-sm mt-1">This may take a few seconds</p>
                </div>
            </div>

            <!-- Preview Content -->
            <div id="ctm-modal-content" class="hidden">
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
                        <p class="text-xs text-blue-600 mb-3">This is how the form will appear after import.</p>
                    </div>
                    <div id="ctm-rendered-form" class="bg-white border border-gray-200 rounded-lg p-6"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    let availableForms = [];
    const nonce = '<?php echo wp_create_nonce('ctm_form_import_nonce'); ?>';

    // Load available forms
    $('#ctm-load-forms').on('click', function() {
        const button = $(this);
        const loading = $('#ctm-loading-forms');
        
        button.prop('disabled', true);
        loading.removeClass('hidden');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'ctm_get_available_forms',
                nonce: nonce
            },
            success: function(response) {
                if (response.success) {
                    availableForms = response.data.forms;
                    populateFormSelect();
                    $('#ctm-form-selection').removeClass('hidden');
                    showMessage('success', response.data.message);
                } else {
                    showMessage('error', response.data.message);
                }
            },
            error: function() {
                showMessage('error', 'Failed to load forms. Please try again.');
            },
            complete: function() {
                button.prop('disabled', false);
                loading.addClass('hidden');
            }
        });
    });

    // Populate form select
    function populateFormSelect() {
        const select = $('#ctm-form-select');
        select.empty();
        select.append('<option value=""><?php _e('Choose a form...', 'call-tracking-metrics'); ?></option>');
        
        availableForms.forEach(function(form) {
            let optionText = form.name;
            let optionClass = '';
            
            // Check if form is already imported to any targets
            if (form.import_status && Array.isArray(form.import_status)) {
                const importTypes = form.import_status.map(importItem => {
                    return importItem.type === 'cf7' ? 'Contact Form 7' : 'Gravity Forms';
                });
                const uniqueTypes = [...new Set(importTypes)];
                optionText += ` ✓ (Imported to: ${uniqueTypes.join(', ')})`;
                optionClass = 'imported-form';
            } else if (form.import_status) {
                // Handle legacy single import status
                const importType = form.import_status.type === 'cf7' ? 'Contact Form 7' : 'Gravity Forms';
                optionText += ` ✓ (Already imported to ${importType})`;
                optionClass = 'imported-form';
            }
            
            const option = $(`<option value="${form.id}" data-form='${JSON.stringify(form)}'>${optionText}</option>`);
            if (optionClass) {
                option.addClass(optionClass);
            }
            select.append(option);
        });
    }

    // Check if both form and target are selected to show form title and action buttons
    function checkFormSelections() {
        const formId = $('#ctm-form-select').val();
        const targetType = $('#ctm-target-type').val();
        const formTitleSection = $('#ctm-form-title-section');
        const actionButtons = $('#ctm-action-buttons');
        
        // Clear any existing import status messages
        $('.ctm-import-status-notice').remove();
        
        if (formId && targetType) {
            formTitleSection.removeClass('hidden');
            actionButtons.removeClass('hidden');
            
            // Auto-populate form title when both selections are made
            const selectedOption = $('#ctm-form-select option:selected');
            if (selectedOption.length) {
                const formData = selectedOption.data('form');
                if (formData && formData.name) {
                    $('#ctm-form-title').val(formData.name);
                    
                    // Check if form is already imported to the selected target
                    let alreadyImportedToTarget = false;
                    let existingImport = null;
                    
                    if (formData.import_status) {
                        if (Array.isArray(formData.import_status)) {
                            // Multiple imports - check if target type is already imported
                            existingImport = formData.import_status.find(importItem => importItem.type === targetType);
                            alreadyImportedToTarget = !!existingImport;
                        } else {
                            // Legacy single import - check if it matches current target
                            alreadyImportedToTarget = formData.import_status.type === targetType;
                            existingImport = alreadyImportedToTarget ? formData.import_status : null;
                        }
                    }
                    
                    // Show import status notice if form is already imported to this target
                    if (alreadyImportedToTarget && existingImport) {
                        const importType = existingImport.type === 'cf7' ? 'Contact Form 7' : 'Gravity Forms';
                        const notice = $(`
                            <div class="ctm-import-status-notice bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-blue-400 mr-4 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <div>
                                        <h4 class="text-blue-800 font-medium mb-2 !mt-0">Form Already Imported to ${importType}</h4>
                                        <p class="text-blue-700 text-sm mb-4">This form has already been imported to ${importType} as "${existingImport.form_title}".</p>
                                        <a href="${existingImport.edit_url}" target="_blank" class="inline-flex items-center px-4 py-2 text-sm font-medium text-blue-600 bg-blue-100 rounded-md hover:bg-blue-200 transition-colors">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            Edit Existing Form
                                        </a>
                                    </div>
                                </div>
                            </div>
                        `);
                        formTitleSection.before(notice);
                    } else if (formData.import_status && Array.isArray(formData.import_status) && formData.import_status.length > 0) {
                        // Show notice that form is imported to other targets but not this one
                        const otherImports = formData.import_status.filter(importItem => importItem.type !== targetType);
                        if (otherImports.length > 0) {
                            const otherTypes = otherImports.map(importItem => importItem.type === 'cf7' ? 'Contact Form 7' : 'Gravity Forms');
                            const notice = $(`
                                <div class="ctm-import-status-notice bg-green-50 border border-green-200 rounded-lg p-6 mb-6">
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 text-green-400 mr-4 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <div>
                                            <h4 class="text-green-800 font-medium mb-2">Form Available for Additional Import</h4>
                                            <p class="text-green-700 text-sm mb-4">This form is already imported to: ${otherTypes.join(', ')}. You can import it to ${targetType === 'cf7' ? 'Contact Form 7' : 'Gravity Forms'} as well.</p>
                                        </div>
                                    </div>
                                </div>
                            `);
                            formTitleSection.before(notice);
                        }
                    }
                }
            }
        } else {
            formTitleSection.addClass('hidden');
            actionButtons.addClass('hidden');
            clearFormState();
        }
    }

    // Clear all form state when selections change
    function clearFormState() {
        
        // Only clear modal if it's not currently open
        const modal = $('#ctm-preview-modal');
        if (!modal.hasClass('hidden')) {
            return;
        }
        
        const modalContent = $('#ctm-modal-content');
        const modalLoading = $('#ctm-modal-loading');
        
        modal.addClass('hidden').removeClass('flex');
        $('body').removeClass('overflow-hidden');
        modalContent.addClass('hidden').html('');
        modalLoading.addClass('hidden');
        
        // Clear form title
        $('#ctm-form-title').val('');
        
        // Clear any success/error messages and import status notices
        $('.ctm-message').remove();
        $('.ctm-import-status-notice').remove();
        
        // Re-enable preview button if it was disabled
        $('#ctm-preview-form').prop('disabled', false).text('<?php _e('Preview Form', 'call-tracking-metrics'); ?>');
        
    }

    // Listen for changes in form and target selection
    $('#ctm-form-select, #ctm-target-type').on('change', checkFormSelections);

    // Preview form using unified preview system
    $('#ctm-preview-form').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const formId = $('#ctm-form-select').val();
        const targetType = $('#ctm-target-type').val();
        const button = $(this);
        
        // Disable button during preview
        button.prop('disabled', true).text('Generating...');
        
        // Use unified preview system
        CTMPreview.showCTMPreview({
            ctmFormId: formId,
            targetType: targetType,
            tabbed: true,
            nonce: nonce
        });
        
        // Re-enable button after a short delay
        setTimeout(function() {
            button.prop('disabled', false).html('<svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg><?php _e('Preview Form', 'call-tracking-metrics'); ?>');
        }, 1000);
    });

    // Modal events are now handled by the unified CTMPreview system

    // Import form
    $('#ctm-import-form').on('click', function() {
        const formId = $('#ctm-form-select').val();
        const targetType = $('#ctm-target-type').val();
        const formTitle = $('#ctm-form-title').val();
        
        if (!formId || !targetType || !formTitle) {
            showMessage('error', 'Please fill in all required fields.');
            return;
        }
        
        const button = $(this);
        const loading = $('#ctm-import-loading');
        const success = $('#ctm-import-success');
        const error = $('#ctm-import-error');
        
        button.prop('disabled', true);
        loading.removeClass('hidden');
        success.addClass('hidden');
        error.addClass('hidden');
        $('#ctm-import-status').removeClass('hidden');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'ctm_import_form',
                nonce: nonce,
                ctm_form_id: formId,
                target_type: targetType,
                form_title: formTitle
            },
            success: function(response) {
                if (response.success) {
                    // Check if duplicate was found
                    if (response.data.duplicate_found) {
                        // Show duplicate warning with options
                        const existingForm = response.data.existing_form;
                        const duplicateHtml = `
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-yellow-400 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                    </svg>
                                    <div class="flex-1">
                                        <h4 class="text-yellow-800 font-medium mb-2">Duplicate Form Detected</h4>
                                        <p class="text-yellow-700 text-sm mb-4">${response.data.message}</p>
                                        <div class="flex flex-wrap gap-2">
                                            <a href="${existingForm.edit_url}" target="_blank" class="inline-flex items-center px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-md hover:bg-blue-100 hover:text-blue-700 transition-colors">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                                Edit Existing Form
                                            </a>
                                            <button type="button" id="ctm-force-import" class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-orange-500 rounded-md hover:bg-orange-600 transition-colors">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                                </svg>
                                                Import Anyway
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        
                        $('#ctm-success-message').html(duplicateHtml);
                        success.removeClass('hidden');
                        
                        // Handle force import button
                        $('#ctm-force-import').on('click', function() {
                            // Re-run import with force flag
                            importFormWithForce();
                        });
                        
                        return;
                    }
                    
                    // Normal success - create a link to the imported form
                    let formLink = '';
                    if (response.data.form_id) {
                        const targetType = $('#ctm-target-type').val();
                        if (targetType === 'cf7') {
                            // Contact Form 7 edit link
                            formLink = `<br><br><a href="${ajaxurl.replace('admin-ajax.php', 'admin.php')}?page=wpcf7&post=${response.data.form_id}&action=edit" target="_blank" class="inline-flex items-center px-3 py-1 text-sm font-medium text-blue-600 bg-blue-50 rounded-md hover:bg-blue-100 hover:text-blue-700 transition-colors">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Edit Form in Contact Form 7
                            </a>`;
                        } else if (targetType === 'gf') {
                            // Gravity Forms edit link
                            formLink = `<br><br><a href="${ajaxurl.replace('admin-ajax.php', 'admin.php')}?page=gf_edit_forms&id=${response.data.form_id}" target="_blank" class="inline-flex items-center px-3 py-1 text-sm font-medium text-blue-600 bg-blue-50 rounded-md hover:bg-blue-100 hover:text-blue-700 transition-colors">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Edit Form in Gravity Forms
                            </a>`;
                        }
                    }
                    
                    $('#ctm-success-message').html(response.data.message + formLink);
                    success.removeClass('hidden');
                    // Reset form
                    $('#ctm-form-select').val('');
                    $('#ctm-target-type').val('');
                    $('#ctm-form-title').val('');
                    $('#ctm-preview-area').addClass('hidden');
                } else {
                    $('#ctm-error-message').text(response.data.message);
                    error.removeClass('hidden');
                }
            },
            error: function() {
                $('#ctm-error-message').text('Import failed. Please try again.');
                error.removeClass('hidden');
            },
            complete: function() {
                button.prop('disabled', false);
                loading.addClass('hidden');
            }
        });
    });

    // Force import function for when user chooses to import despite duplicates
    function importFormWithForce() {
        const formId = $('#ctm-form-select').val();
        const targetType = $('#ctm-target-type').val();
        const formTitle = $('#ctm-form-title').val();
        
        const button = $('#ctm-force-import');
        const loading = $('#ctm-import-loading');
        const success = $('#ctm-import-success');
        const error = $('#ctm-import-error');
        
        button.prop('disabled', true).text('Importing...');
        loading.removeClass('hidden');
        success.addClass('hidden');
        error.addClass('hidden');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'ctm_import_form',
                nonce: nonce,
                ctm_form_id: formId,
                target_type: targetType,
                form_title: formTitle,
                force_duplicate: 'true'
            },
            success: function(response) {
                if (response.success && response.data.form_id) {
                    // Create a link to the imported form
                    let formLink = '';
                    if (targetType === 'cf7') {
                        formLink = `<br><br><a href="${ajaxurl.replace('admin-ajax.php', 'admin.php')}?page=wpcf7&post=${response.data.form_id}&action=edit" target="_blank" class="inline-flex items-center px-3 py-1 text-sm font-medium text-blue-600 bg-blue-50 rounded-md hover:bg-blue-100 hover:text-blue-700 transition-colors">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit Form in Contact Form 7
                        </a>`;
                    } else if (targetType === 'gf') {
                        formLink = `<br><br><a href="${ajaxurl.replace('admin-ajax.php', 'admin.php')}?page=gf_edit_forms&id=${response.data.form_id}" target="_blank" class="inline-flex items-center px-3 py-1 text-sm font-medium text-blue-600 bg-blue-50 rounded-md hover:bg-blue-100 hover:text-blue-700 transition-colors">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit Form in Gravity Forms
                        </a>`;
                    }
                    
                    $('#ctm-success-message').html(response.data.message + formLink);
                    success.removeClass('hidden');
                    // Reset form
                    $('#ctm-form-select').val('');
                    $('#ctm-target-type').val('');
                    $('#ctm-form-title').val('');
                    $('#ctm-preview-area').addClass('hidden');
                } else {
                    $('#ctm-error-message').text(response.data.message || 'Import failed. Please try again.');
                    error.removeClass('hidden');
                }
            },
            error: function() {
                $('#ctm-error-message').text('Import failed. Please try again.');
                error.removeClass('hidden');
            },
            complete: function() {
                button.prop('disabled', false);
                loading.addClass('hidden');
            }
        });
    }

    // Auto-populate form title when form is selected
    $('#ctm-form-select').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        if (selectedOption.val()) {
            const formData = JSON.parse(selectedOption.attr('data-form'));
            $('#ctm-form-title').val(formData.name);
        }
    });

    // Show message helper
    function showMessage(type, message) {
        const alertClass = type === 'success' ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800';
        const alert = $(`<div class="rounded-lg p-4 mb-4 ${alertClass}">${message}</div>`);
        
        $('.bg-white').first().prepend(alert);
        
        setTimeout(function() {
            alert.fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
    }
});
</script>

<style>
.ctm-preview-cf7, .ctm-preview-gf {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

/* Smooth transitions for preview loading */
#ctm-preview-loading, #ctm-preview-content {
    transition: opacity 0.3s ease-in-out, transform 0.3s ease-in-out;
}

#ctm-preview-loading.hidden, #ctm-preview-content.hidden {
    opacity: 0;
    transform: translateY(-10px);
}

#ctm-preview-loading:not(.hidden), #ctm-preview-content:not(.hidden) {
    opacity: 1;
    transform: translateY(0);
}

.form-field {
    margin-bottom: 1.5rem;
}

.form-field label {
    display: block;
    margin-bottom: 0.75rem;
    font-weight: 500;
    color: #374151;
}

.form-field input,
.form-field textarea,
.form-field select {
    width: 100%;
    padding: 0.875rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    background-color: #f9fafb;
    color: #6b7280;
    min-height: 44px;
    line-height: 1.4;
}

.form-field button {
    background-color: #3b82f6;
    color: white;
    padding: 0.875rem 1.5rem;
    border: none;
    border-radius: 0.5rem;
    font-weight: 500;
    cursor: not-allowed;
    opacity: 0.6;
    min-height: 44px;
}

/* Styling for imported forms in select dropdown */
.imported-form {
    background-color: #f0f9ff !important;
    color: #0369a1 !important;
    font-weight: 500;
}

#ctm-form-select option.imported-form {
    background-color: #f0f9ff;
    color: #0369a1;
}

/* Improved dropdown styling */
#ctm-form-select, #ctm-target-type {
    text-overflow: ellipsis;
    white-space: nowrap;
    overflow: hidden;
}

#ctm-form-select option, #ctm-target-type option {
    padding: 8px 12px;
    white-space: normal;
    word-wrap: break-word;
    min-height: 44px;
    line-height: 1.4;
}

/* Better spacing for form elements */
.form-field {
    margin-bottom: 1.5rem;
}

.form-field label {
    display: block;
    margin-bottom: 0.75rem;
    font-weight: 500;
    color: #374151;
}

.form-field input,
.form-field textarea,
.form-field select {
    width: 100%;
    padding: 0.875rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    background-color: #f9fafb;
    color: #6b7280;
    min-height: 44px;
    line-height: 1.4;
}

.form-field button {
    background-color: #3b82f6;
    color: white;
    padding: 0.875rem 1.5rem;
    border: none;
    border-radius: 0.5rem;
    font-weight: 500;
    cursor: not-allowed;
    opacity: 0.6;
    min-height: 44px;
}

/* Improved spacing for status notices */
.ctm-import-status-notice {
    margin-bottom: 1.5rem;
    padding: 1.5rem;
    border-radius: 0.75rem;
}

.ctm-import-status-notice h4 {
    margin-bottom: 0.75rem;
    font-size: 1.125rem;
}

.ctm-import-status-notice p {
    margin-bottom: 1rem;
    line-height: 1.5;
}

.ctm-import-status-notice a,
.ctm-import-status-notice button {
    padding: 0.75rem 1rem;
    border-radius: 0.5rem;
    font-weight: 500;
    transition: all 0.2s ease;
}

/* Better spacing for action buttons */
#ctm-action-buttons {
    margin-top: 2rem;
    gap: 1rem;
}

#ctm-action-buttons button {
    padding: 0.875rem 1.5rem;
    min-height: 44px;
    font-weight: 500;
}

/* Improved preview area spacing */
#ctm-preview-area {
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e5e7eb;
}

#ctm-preview-area h4 {
    margin-bottom: 1rem;
    font-size: 1.125rem;
}

/* Better spacing for import status */
#ctm-import-status {
    margin-top: 1.5rem;
}

#ctm-import-loading,
#ctm-import-success,
#ctm-import-error {
    padding: 1rem 1.5rem;
    border-radius: 0.75rem;
    margin-bottom: 1rem;
}

/* Modal styling */
#ctm-preview-modal {
    backdrop-filter: blur(4px);
    display: flex !important;
}

#ctm-preview-modal.hidden {
    display: none !important;
}

#ctm-preview-modal .bg-white {
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    display: flex;
    flex-direction: column;
}

/* Tab styling */
.ctm-tab-button {
    transition: all 0.2s ease;
    cursor: pointer;
}

.ctm-tab-button:hover {
    color: #374151;
}

.ctm-tab-content {
    transition: opacity 0.3s ease;
}

/* Code display styling */
#ctm-raw-code {
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    line-height: 1.5;
    white-space: pre-wrap;
    word-wrap: break-word;
}

/* Rendered form styling */
#ctm-rendered-form {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

#ctm-rendered-form .wpcf7-form {
    max-width: none;
}

#ctm-rendered-form .gform_wrapper {
    max-width: none;
}

/* Modal content scrolling */
#ctm-preview-modal .overflow-y-auto {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e0 #f7fafc;
}

#ctm-preview-modal .overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

#ctm-preview-modal .overflow-y-auto::-webkit-scrollbar-track {
    background: #f7fafc;
    border-radius: 3px;
}

#ctm-preview-modal .overflow-y-auto::-webkit-scrollbar-thumb {
    background: #cbd5e0;
    border-radius: 3px;
}

#ctm-preview-modal .overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #a0aec0;
}
</style> 