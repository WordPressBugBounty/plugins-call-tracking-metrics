<?php
/**
 * Forms Management Tab Template
 * 
 * Displays existing Contact Form 7 and Gravity Forms with management options.
 * 
 * @since 2.0.0
 * @var bool $cf7_available Whether Contact Form 7 is available
 * @var bool $gf_available Whether Gravity Forms is available
 * @var array $cf7_forms Array of Contact Form 7 forms
 * @var array $gf_forms Array of Gravity Forms
 * @var string $apiKey The CTM API key
 * @var string $apiSecret The CTM API secret
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Helper function to truncate long CTM form IDs
if (!function_exists('truncate_ctm_id')) {
function truncate_ctm_id($id, $max_length = 20) {
    if (empty($id) || $id === 'N/A') {
        return $id;
    }
    
    if (strlen($id) <= $max_length) {
        return $id;
    }
    
    $start_length = 8;
    $end_length = 8;
    
    return substr($id, 0, $start_length) . '...' . substr($id, -$end_length);
}
}
?>

<div class="forms-management-container">
    <!-- Header Section -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-2"><?php _e('Manage Forms', 'call-tracking-metrics'); ?></h2>
        <p class="text-gray-600"><?php _e('View and manage your existing Contact Form 7 and Gravity Forms. Edit them in WordPress or link them to CallTrackingMetrics.', 'call-tracking-metrics'); ?></p>
    </div>

    <!-- Forms Status Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <!-- CF7 Status Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center mb-4">
                <div class="bg-blue-100 rounded-full p-3 mr-4">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-800"><?php _e('Contact Form 7', 'call-tracking-metrics'); ?></h3>
                    <p class="text-gray-500 text-sm">
                        <?php if ($cf7_available): ?>
                            <?php printf(_n('%d form found', '%d forms found', count($cf7_forms), 'call-tracking-metrics'), count($cf7_forms)); ?>
                        <?php else: ?>
                            <?php _e('Not installed', 'call-tracking-metrics'); ?>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            <?php if (!$cf7_available): ?>
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <p class="text-yellow-800 text-sm mb-3"><?php _e('Contact Form 7 is not installed or activated.', 'call-tracking-metrics'); ?></p>
                    <a href="<?php echo admin_url('plugin-install.php?s=contact+form+7&tab=search&type=term'); ?>" class="inline-flex items-center px-3 py-1 text-sm font-medium text-yellow-800 bg-yellow-100 rounded-md hover:bg-yellow-200 transition-colors">
                        <?php _e('Install Contact Form 7', 'call-tracking-metrics'); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- GF Status Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center mb-4">
                <div class="bg-green-100 rounded-full p-3 mr-4">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V9a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-800"><?php _e('Gravity Forms', 'call-tracking-metrics'); ?></h3>
                    <p class="text-gray-500 text-sm">
                        <?php if ($gf_available): ?>
                            <?php printf(_n('%d form found', '%d forms found', count($gf_forms), 'call-tracking-metrics'), count($gf_forms)); ?>
                        <?php else: ?>
                            <?php _e('Not installed', 'call-tracking-metrics'); ?>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            <?php if (!$gf_available): ?>
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <p class="text-yellow-800 text-sm mb-3"><?php _e('Gravity Forms is not installed or activated.', 'call-tracking-metrics'); ?></p>
                    <a href="https://www.gravityforms.com/" target="_blank" class="inline-flex items-center px-3 py-1 text-sm font-medium text-yellow-800 bg-yellow-100 rounded-md hover:bg-yellow-200 transition-colors">
                        <?php _e('Get Gravity Forms', 'call-tracking-metrics'); ?>
                        <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Contact Form 7 Forms List -->
    <?php if ($cf7_available && !empty($cf7_forms)): ?>
    <div class="mb-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <svg class="w-5 h-5 text-blue-600 mr-2 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <?php _e('Contact Form 7 Forms', 'call-tracking-metrics'); ?>
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($cf7_forms as $form): ?>
                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between mb-3">
                            <h4 class="font-medium text-gray-900 truncate mr-2"><?php echo esc_html($form['title']); ?></h4>
                            <div class="flex items-center space-x-2">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 whitespace-nowrap">
                                    CF7
                                </span>
                                <?php 
                                // Check if form has phone field
                                $cf7Service = new \CTM\Service\CF7Service();
                                $cf7Form = \WPCF7_ContactForm::get_instance($form['id']);
                                $hasPhoneField = $cf7Service->hasPhoneField($cf7Form);
                                if (!$hasPhoneField): ?>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800" title="Missing phone number field - form will not work with CTM">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                    No Phone
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="space-y-1 mb-4 text-sm text-gray-500">
                            <div class="flex justify-between">
                                <span><?php _e('WP ID:', 'call-tracking-metrics'); ?></span>
                                <span>#<?php echo esc_html($form['id']); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span><?php _e('CTM ID:', 'call-tracking-metrics'); ?></span>
                                <span title="<?php echo esc_attr($form['ctm_form_id'] ?: 'N/A'); ?>"><?php echo esc_html(truncate_ctm_id($form['ctm_form_id'] ?: 'N/A')); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span><?php _e('Imported:', 'call-tracking-metrics'); ?></span>
                                <span><?php echo esc_html(date('M j, Y', strtotime($form['ctm_import_date'] ?: $form['date_created']))); ?></span>
                            </div>
                        </div>
                        
                        <div class="space-y-3">
                            <!-- Primary Actions Row -->
                            <div class="grid grid-cols-3 gap-2">
                                <!-- Preview Form -->
                                <button type="button" 
                                        class="inline-flex items-center justify-center px-3 py-2 text-sm font-medium text-gray-600 bg-gray-50 rounded-md hover:bg-gray-100 hover:text-gray-700 transition-colors ctm-preview-wp-form"
                                        data-form-id="<?php echo esc_attr($form['id']); ?>" 
                                        data-form-type="cf7"
                                        data-form-title="<?php echo esc_attr($form['title']); ?>">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    <?php _e('Preview', 'call-tracking-metrics'); ?>
                                </button>
                                
                                <!-- Form Usage -->
                                <button type="button" 
                                        class="inline-flex items-center justify-center px-3 py-2 text-sm font-medium text-indigo-600 bg-indigo-50 rounded-md hover:bg-indigo-100 hover:text-indigo-700 transition-colors ctm-form-usage"
                                        data-form-id="<?php echo esc_attr($form['id']); ?>" 
                                        data-form-type="cf7"
                                        data-form-title="<?php echo esc_attr($form['title']); ?>"
                                        data-force-refresh="true">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                    <?php _e('Usage', 'call-tracking-metrics'); ?>
                                </button>
                                
                                <!-- Edit in WordPress -->
                                <a href="<?php echo admin_url('admin.php?page=wpcf7&post=' . $form['id'] . '&action=edit'); ?>" 
                                   class="inline-flex items-center justify-center px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-md hover:bg-blue-100 hover:text-blue-700 transition-colors">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    <?php _e('Edit', 'call-tracking-metrics'); ?>
                                </a>
                            </div>

                            <?php if (get_option('ctm_debug_enabled', false)): ?>
                            <!-- Debug Actions Row -->
                            <div class="border-t border-gray-100 pt-2">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Debug</span>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        Logs
                                    </span>
                                </div>
                                <!-- View Logs -->
                                <button type="button" 
                                        class="w-full inline-flex items-center justify-center px-3 py-2 text-sm font-medium text-purple-600 bg-purple-50 rounded-md hover:bg-purple-100 hover:text-purple-700 transition-colors ctm-view-form-logs"
                                        data-form-type="cf7"
                                        data-form-id="<?php echo esc_attr($form['id']); ?>"
                                        data-form-title="<?php echo esc_attr($form['title']); ?>">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <?php _e('View Logs', 'call-tracking-metrics'); ?>
                                </button>
                            </div>
                            <?php endif; ?>
                            
                            <!-- CTM Actions -->
                            <?php if ($apiKey && $apiSecret): ?>
                                <?php if ($form['ctm_form_id']): ?>
                                    <div class="flex space-x-2">
                                        <!-- Edit in CTM -->
                                        <button type="button" 
                                                class="flex-1 inline-flex items-center justify-center px-3 py-2 text-sm font-medium text-green-600 bg-green-50 rounded-md hover:bg-green-100 hover:text-green-700 transition-colors ctm-link-form"
                                                data-form-id="<?php echo esc_attr($form['id']); ?>" 
                                                data-form-type="cf7"
                                                data-form-title="<?php echo esc_attr($form['title']); ?>"
                                                data-ctm-form-id="<?php echo esc_attr($form['ctm_form_id']); ?>">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                                            </svg>
                                            <?php _e('Edit in CTM', 'call-tracking-metrics'); ?>
                                        </button>
                                        
                                        <!-- Update from CTM -->
                                        <button type="button" 
                                                class="flex-1 inline-flex items-center justify-center px-3 py-2 text-sm font-medium text-orange-600 bg-orange-50 rounded-md hover:bg-orange-100 hover:text-orange-700 transition-colors ctm-update-form"
                                                data-form-id="<?php echo esc_attr($form['id']); ?>" 
                                                data-form-type="cf7"
                                                data-form-title="<?php echo esc_attr($form['title']); ?>"
                                                data-ctm-form-id="<?php echo esc_attr($form['ctm_form_id']); ?>">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                            </svg>
                                            <?php _e('Update from CTM', 'call-tracking-metrics'); ?>
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <!-- Sync with CTM (not yet synced) -->
                                    <button type="button" 
                                            class="w-full inline-flex items-center justify-center px-3 py-2 text-sm font-medium text-purple-600 bg-purple-50 rounded-md hover:bg-purple-100 hover:text-purple-700 transition-colors ctm-sync-form"
                                            data-form-id="<?php echo esc_attr($form['id']); ?>" 
                                            data-form-type="cf7"
                                            data-form-title="<?php echo esc_attr($form['title']); ?>">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                        <?php _e('Sync with CTM', 'call-tracking-metrics'); ?>
                                    </button>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Gravity Forms List -->
    <?php if ($gf_available && !empty($gf_forms)): ?>
    <div class="mb-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <svg class="w-5 h-5 text-green-600 mr-2 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V9a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <?php _e('Gravity Forms', 'call-tracking-metrics'); ?>
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($gf_forms as $form): ?>
                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between mb-3">
                            <h4 class="font-medium text-gray-900 truncate mr-2"><?php echo esc_html($form['title']); ?></h4>
                            <div class="flex items-center space-x-2">
                                <?php if ($form['is_active']): ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 whitespace-nowrap">
                                        <?php _e('Active', 'call-tracking-metrics'); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 whitespace-nowrap">
                                        <?php _e('Inactive', 'call-tracking-metrics'); ?>
                                    </span>
                                <?php endif; ?>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 whitespace-nowrap">
                                    GF
                                </span>
                                <?php 
                                // Check if form has phone field
                                $gfService = new \CTM\Service\GFService();
                                $gfForm = \GFAPI::get_form($form['id']);
                                $hasPhoneField = $gfService->hasPhoneField($gfForm);
                                if (!$hasPhoneField): ?>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800" title="Missing phone number field - form will not work with CTM">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                    No Phone
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="space-y-1 mb-4 text-sm text-gray-500">
                            <div class="flex justify-between">
                                <span><?php _e('WP ID:', 'call-tracking-metrics'); ?></span>
                                <span>#<?php echo esc_html($form['id']); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span><?php _e('CTM ID:', 'call-tracking-metrics'); ?></span>
                                <span title="<?php echo esc_attr($form['ctm_form_id'] ?: 'N/A'); ?>"><?php echo esc_html(truncate_ctm_id($form['ctm_form_id'] ?: 'N/A')); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span><?php _e('Entries:', 'call-tracking-metrics'); ?></span>
                                <span><?php echo number_format($form['entries']); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span><?php _e('Imported:', 'call-tracking-metrics'); ?></span>
                                <span><?php echo esc_html(date('M j, Y', strtotime($form['ctm_import_date'] ?: $form['date_created']))); ?></span>
                            </div>
                        </div>
                        
                        <div class="space-y-3">
                            <!-- Primary Actions Row -->
                            <div class="grid grid-cols-3 gap-2">
                                <!-- Preview Form -->
                                <button type="button" 
                                        class="inline-flex items-center justify-center px-3 py-2 text-sm font-medium text-gray-600 bg-gray-50 rounded-md hover:bg-gray-100 hover:text-gray-700 transition-colors ctm-preview-wp-form"
                                        data-form-id="<?php echo esc_attr($form['id']); ?>" 
                                        data-form-type="gf"
                                        data-form-title="<?php echo esc_attr($form['title']); ?>">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    <?php _e('Preview', 'call-tracking-metrics'); ?>
                                </button>
                                
                                <!-- Form Usage -->
                                <button type="button" 
                                        class="inline-flex items-center justify-center px-3 py-2 text-sm font-medium text-indigo-600 bg-indigo-50 rounded-md hover:bg-indigo-100 hover:text-indigo-700 transition-colors ctm-form-usage"
                                        data-form-id="<?php echo esc_attr($form['id']); ?>" 
                                        data-form-type="gf"
                                        data-form-title="<?php echo esc_attr($form['title']); ?>"
                                        data-force-refresh="true">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                    <?php _e('Usage', 'call-tracking-metrics'); ?>
                                </button>
                                
                                <!-- Edit in WordPress -->
                                <a href="<?php echo admin_url('admin.php?page=gf_edit_forms&id=' . $form['id']); ?>" 
                                   class="inline-flex items-center justify-center px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-md hover:bg-blue-100 hover:text-blue-700 transition-colors">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    <?php _e('Edit', 'call-tracking-metrics'); ?>
                                </a>
                            </div>

                            <?php if (get_option('ctm_debug_enabled', false)): ?>
                            <!-- Debug Actions Row -->
                            <div class="border-t border-gray-100 pt-2">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Debug</span>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        Logs
                                    </span>
                                </div>
                                <!-- View Logs -->
                                <button type="button" 
                                        class="w-full inline-flex items-center justify-center px-3 py-2 text-sm font-medium text-purple-600 bg-purple-50 rounded-md hover:bg-purple-100 hover:text-purple-700 transition-colors ctm-view-form-logs"
                                        data-form-type="gf"
                                        data-form-id="<?php echo esc_attr($form['id']); ?>"
                                        data-form-title="<?php echo esc_attr($form['title']); ?>">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <?php _e('View Logs', 'call-tracking-metrics'); ?>
                                </button>
                            </div>
                            <?php endif; ?>
                            
                            <!-- CTM Actions -->
                            <?php if ($apiKey && $apiSecret): ?>
                                <?php if ($form['ctm_form_id']): ?>
                                    <div class="flex space-x-2">
                                        <!-- Edit in CTM -->
                                        <button type="button" 
                                                class="flex-1 inline-flex items-center justify-center px-3 py-2 text-sm font-medium text-green-600 bg-green-50 rounded-md hover:bg-green-100 hover:text-green-700 transition-colors ctm-link-form"
                                                data-form-id="<?php echo esc_attr($form['id']); ?>" 
                                                data-form-type="gf"
                                                data-form-title="<?php echo esc_attr($form['title']); ?>"
                                                data-ctm-form-id="<?php echo esc_attr($form['ctm_form_id']); ?>">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                                            </svg>
                                            <?php _e('Edit in CTM', 'call-tracking-metrics'); ?>
                                        </button>
                                        
                                        <!-- Update from CTM -->
                                        <button type="button" 
                                                class="flex-1 inline-flex items-center justify-center px-3 py-2 text-sm font-medium text-orange-600 bg-orange-50 rounded-md hover:bg-orange-100 hover:text-orange-700 transition-colors ctm-update-form"
                                                data-form-id="<?php echo esc_attr($form['id']); ?>" 
                                                data-form-type="gf"
                                                data-form-title="<?php echo esc_attr($form['title']); ?>"
                                                data-ctm-form-id="<?php echo esc_attr($form['ctm_form_id']); ?>">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                            </svg>
                                            <?php _e('Update from CTM', 'call-tracking-metrics'); ?>
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <!-- Sync with CTM (not yet synced) -->
                                    <button type="button" 
                                            class="w-full inline-flex items-center justify-center px-3 py-2 text-sm font-medium text-purple-600 bg-purple-50 rounded-md hover:bg-purple-100 hover:text-purple-700 transition-colors ctm-sync-form"
                                            data-form-id="<?php echo esc_attr($form['id']); ?>" 
                                            data-form-type="gf"
                                            data-form-title="<?php echo esc_attr($form['title']); ?>">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                        <?php _e('Sync with CTM', 'call-tracking-metrics'); ?>
                                    </button>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- No Forms Message -->
    <?php if (($cf7_available && empty($cf7_forms)) && ($gf_available && empty($gf_forms))): ?>
    <div class="text-center py-12">
        <div class="bg-gray-50 rounded-lg p-8">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2"><?php _e('No Forms Found', 'call-tracking-metrics'); ?></h3>
            <p class="text-gray-500 mb-4"><?php _e('You haven\'t imported any forms from CallTrackingMetrics yet. Get started by importing your first form.', 'call-tracking-metrics'); ?></p>
            <div class="flex justify-center">
                <a href="<?php echo admin_url('admin.php?page=call-tracking-metrics&tab=import'); ?>" class="inline-flex items-center px-6 py-3 text-sm font-medium !text-white bg-blue-600 rounded-md hover:bg-blue-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    <?php _e('Import Forms from CTM', 'call-tracking-metrics'); ?>
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- CTM Link Modal -->
<div id="ctm-link-modal" class="fixed inset-0 bg-black bg-opacity-60 overflow-y-auto h-full w-full hidden" style="z-index: 999999;">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                </svg>
            </div>
            <h3 class="text-lg leading-6 font-medium text-gray-900 mt-2"><?php _e('Edit Form in CallTrackingMetrics', 'call-tracking-metrics'); ?></h3>
            <div class="mt-4 px-6 py-3">
                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                    <div class="text-sm font-medium text-gray-900 mb-1" id="ctm-modal-form-title">Form Name</div>
                    <div class="text-xs text-gray-500">
                        <span class="font-medium">CTM ID:</span> 
                        <span id="ctm-modal-form-id" class="font-mono">Loading...</span>
                    </div>
                </div>
                <p class="text-sm text-gray-600 text-center">
                    <?php _e('This will open the form editor in CallTrackingMetrics in a new tab.', 'call-tracking-metrics'); ?>
                </p>
            </div>
            <div class="items-center px-4 py-3">
                <button id="ctm-link-confirm" class="px-4 py-2 bg-green-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-300">
                    <?php _e('Open Form Editor', 'call-tracking-metrics'); ?>
                </button>
                <button id="ctm-link-cancel" class="mt-3 px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    <?php _e('Cancel', 'call-tracking-metrics'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- CTM Sync Modal -->
<div id="ctm-sync-modal" class="fixed inset-0 bg-black bg-opacity-60 overflow-y-auto h-full w-full hidden" style="z-index: 999999;">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-purple-100">
                <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
            </div>
            <h3 class="text-lg leading-6 font-medium text-gray-900 mt-2"><?php _e('Sync Form with CallTrackingMetrics', 'call-tracking-metrics'); ?></h3>
            <div class="mt-4 px-6 py-3">
                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                    <div class="text-sm font-medium text-gray-900 mb-1" id="ctm-sync-form-title">Form Name</div>
                    <div class="text-xs text-gray-500 mb-3">
                        <span class="font-medium">WordPress Form Type:</span> 
                        <span id="ctm-sync-form-type" class="font-mono">Type</span>
                    </div>
                    <div class="text-xs text-gray-600">
                        <?php _e('Select a CallTrackingMetrics form to sync with this WordPress form:', 'call-tracking-metrics'); ?>
                    </div>
                </div>
                
                <div class="mb-4">
                    <select id="ctm-sync-form-select" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        <option value=""><?php _e('Loading CTM forms...', 'call-tracking-metrics'); ?></option>
                    </select>
                </div>
                
                <p class="text-sm text-gray-600 text-center">
                    <?php _e('This will link your WordPress form to the selected CallTrackingMetrics form for tracking.', 'call-tracking-metrics'); ?>
                </p>
            </div>
            <div class="items-center px-4 py-3">
                <button id="ctm-sync-confirm" class="px-4 py-2 bg-purple-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-purple-600 focus:outline-none focus:ring-2 focus:ring-purple-300 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                    <?php _e('Sync Forms', 'call-tracking-metrics'); ?>
                </button>
                <button id="ctm-sync-cancel" class="mt-3 px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    <?php _e('Cancel', 'call-tracking-metrics'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- CTM Update Confirmation Modal -->
<div id="ctm-update-modal" class="fixed inset-0 bg-black bg-opacity-60 overflow-y-auto h-full w-full hidden flex items-center justify-center" style="z-index: 999999;">
    <div class="relative mx-auto p-6 border w-[500px] shadow-lg rounded-lg bg-white">
        <div class="text-left">
            <!-- Header with Icon -->
            <div class="flex items-center mb-6">
                <div class="flex items-center justify-center h-12 w-12 rounded-full bg-orange-100 mr-4">
                    <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-semibold text-gray-900"><?php _e('⚠️ Warning: Form Update', 'call-tracking-metrics'); ?></h3>
                    <p class="text-sm text-gray-600 mt-1"><?php _e('This action cannot be undone', 'call-tracking-metrics'); ?></p>
                </div>
            </div>
            
            <!-- Warning Alert -->
            <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-orange-800">
                            <?php _e('This action will overwrite your local form changes!', 'call-tracking-metrics'); ?>
                        </p>
                        <p class="text-sm text-orange-700 mt-1">
                            <?php _e('Any modifications you made to this form will be lost.', 'call-tracking-metrics'); ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Form Details -->
            <div class="mb-6">
                <p class="text-sm text-gray-600 mb-3">
                    <?php _e('You are about to update this form with the latest content from CallTrackingMetrics:', 'call-tracking-metrics'); ?>
                </p>
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-700"><?php _e('Form Name', 'call-tracking-metrics'); ?></p>
                            <p class="text-sm text-gray-900 font-semibold" id="ctm-update-form-title"></p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-700"><?php _e('Form Type', 'call-tracking-metrics'); ?></p>
                            <span id="ctm-update-form-type" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex space-x-3">
                <button id="ctm-update-cancel" class="flex-1 px-4 py-2.5 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-300 transition-colors">
                    <?php _e('Cancel', 'call-tracking-metrics'); ?>
                </button>
                <button id="ctm-update-confirm" class="flex-1 px-4 py-2.5 bg-orange-500 text-white text-sm font-medium rounded-lg hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-300 transition-colors">
                    <?php _e('Yes, Update Form', 'call-tracking-metrics'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Form Preview Modal -->
<div id="ctm-preview-modal" class="fixed inset-0 bg-black bg-opacity-60 hidden items-center justify-center" style="z-index: 999999;">
    <div class="bg-white rounded-lg max-w-4xl w-full mx-4 max-h-[90vh] flex flex-col">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
                <h3 class="text-lg font-semibold text-gray-900"><?php _e('Form Preview', 'call-tracking-metrics'); ?></h3>
                <span class="ml-2 text-sm text-gray-500">-</span>
                <span id="ctm-preview-form-title" class="ml-1 text-sm font-medium text-gray-700"></span>
            </div>
            <button type="button" id="ctm-preview-close" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <!-- Modal Body -->
        <div class="flex-1 overflow-y-auto p-6">
            <!-- Loading State -->
            <div id="ctm-preview-loading" class="text-center py-12">
                <svg class="animate-spin h-8 w-8 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-gray-500"><?php _e('Loading form preview...', 'call-tracking-metrics'); ?></p>
            </div>
            
            <!-- Preview Content -->
            <div id="ctm-preview-content" class="hidden">
                <!-- Form preview will be loaded here -->
            </div>
            
            <!-- Error State -->
            <div id="ctm-preview-error" class="hidden text-center py-12">
                <svg class="w-12 h-12 text-red-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.35 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <p class="text-red-600 font-medium mb-2"><?php _e('Failed to load preview', 'call-tracking-metrics'); ?></p>
                <p id="ctm-preview-error-message" class="text-gray-500 text-sm"></p>
            </div>
        </div>
        
        <!-- Modal Footer -->
        <div class="border-t border-gray-200 px-6 py-4">
            <div class="flex justify-between items-center">
                <div class="text-xs text-gray-500">
                    <?php _e('This is a preview only. Changes made here will not be saved.', 'call-tracking-metrics'); ?>
                </div>
                <button type="button" id="ctm-preview-close-btn" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 transition-colors">
                    <?php _e('Close', 'call-tracking-metrics'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Form Usage Modal -->
<div id="ctm-usage-modal" class="fixed inset-0 bg-black bg-opacity-60 hidden items-center justify-center" style="z-index: 999999;">
    <div class="bg-white rounded-lg max-w-4xl w-full mx-4 max-h-[90vh] flex flex-col">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-indigo-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <h3 class="text-lg font-semibold text-gray-900"><?php _e('Form Usage', 'call-tracking-metrics'); ?></h3>
                <span class="ml-2 text-sm text-gray-500">-</span>
                <span id="ctm-usage-form-title" class="ml-1 text-sm font-medium text-gray-700"></span>
            </div>
            <button type="button" id="ctm-usage-close" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <!-- Modal Body -->
        <div class="flex-1 overflow-y-auto p-6">
            <!-- Loading State -->
            <div id="ctm-usage-loading" class="text-center py-12">
                <svg class="animate-spin h-8 w-8 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-gray-500"><?php _e('Searching for form usage...', 'call-tracking-metrics'); ?></p>
            </div>
            
            <!-- Usage Content -->
            <div id="ctm-usage-content" class="hidden">
                <!-- Form usage will be loaded here -->
            </div>
            
            <!-- Error State -->
            <div id="ctm-usage-error" class="hidden text-center py-12">
                <svg class="w-12 h-12 text-red-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.35 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <p class="text-red-600 font-medium mb-2"><?php _e('Failed to load usage data', 'call-tracking-metrics'); ?></p>
                <p id="ctm-usage-error-message" class="text-gray-500 text-sm"></p>
            </div>
        </div>
        
        <!-- Modal Footer -->
        <div class="border-t border-gray-200 px-6 py-4">
            <div class="flex justify-between items-center">
                <div class="text-xs text-gray-500">
                    <?php _e('This shows where the form is currently being used on your website.', 'call-tracking-metrics'); ?>
                </div>
                <button type="button" id="ctm-usage-close-btn" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 transition-colors">
                    <?php _e('Close', 'call-tracking-metrics'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Handle CTM Link buttons
    $('.ctm-link-form').on('click', function() {
        const formId = $(this).data('form-id');
        const formType = $(this).data('form-type');
        const formTitle = $(this).data('form-title');
        const ctmFormId = $(this).data('ctm-form-id');
        
        // Use truncated CTM ID for better display
        const truncatedCtmId = ctmFormId.length > 20 ? 
            ctmFormId.substring(0, 8) + '...' + ctmFormId.substring(ctmFormId.length - 8) : 
            ctmFormId;
        
        // Update modal content
        console.log('Updating modal with:', { formTitle, ctmFormId, truncatedCtmId });
        $('#ctm-modal-form-title').text(formTitle);
        $('#ctm-modal-form-id').text(truncatedCtmId).attr('title', ctmFormId);
        
        // Debug: Verify the update worked
        console.log('Modal form ID element content:', $('#ctm-modal-form-id').text());
        
        // Show modal and prevent body scrolling
        $('#ctm-link-modal').removeClass('hidden');
        $('body').addClass('overflow-hidden');
        
        // Store form data for the confirm action
        $('#ctm-link-confirm').data('form-id', formId).data('form-type', formType).data('form-title', formTitle).data('ctm-form-id', ctmFormId);
    });
    
    // Handle confirm link
    $('#ctm-link-confirm').on('click', function() {
        const formId = $(this).data('form-id');
        const formType = $(this).data('form-type');
        const formTitle = $(this).data('form-title');
        const ctmFormId = $(this).data('ctm-form-id');
        
        // Open CTM form editor in a new tab with specific form ID
        const ctmUrl = ctmFormId ? 
            'https://app.calltrackingmetrics.com/form_reactors/' + ctmFormId + '/edit' : 
            'https://app.calltrackingmetrics.com/form_reactors';
        window.open(ctmUrl, '_blank');
        
        $('#ctm-link-modal').addClass('hidden');
        $('body').removeClass('overflow-hidden');
        
        // Optional: Show success message
        $('body').append('<div class="fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded" style="z-index: 999999;" id="ctm-success-toast">' +
            '<span class="block sm:inline"><?php _e('CallTrackingMetrics opened in new tab. You can now link your form.', 'call-tracking-metrics'); ?></span>' +
            '</div>');
        
        setTimeout(function() {
            $('#ctm-success-toast').fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
    });
    
    // Handle cancel
    $('#ctm-link-cancel').on('click', function() {
        $('#ctm-link-modal').addClass('hidden');
        $('body').removeClass('overflow-hidden');
    });
    
    // Close modal when clicking outside
    $('#ctm-link-modal').on('click', function(e) {
        if (e.target === this) {
            $(this).addClass('hidden');
            $('body').removeClass('overflow-hidden');
        }
    });
    
    // Handle CTM Sync buttons
    $('.ctm-sync-form').on('click', function() {
        const formId = $(this).data('form-id');
        const formType = $(this).data('form-type');
        const formTitle = $(this).data('form-title');
        
        // Update sync modal content
        $('#ctm-sync-form-title').text(formTitle);
        $('#ctm-sync-form-type').text(formType.toUpperCase());
        
        // Show sync modal and prevent body scrolling
        $('#ctm-sync-modal').removeClass('hidden');
        $('body').addClass('overflow-hidden');
        
        // Load CTM forms for selection
        loadCTMFormsForSync();
        
        // Store form data for the sync action
        $('#ctm-sync-confirm').data('form-id', formId).data('form-type', formType).data('form-title', formTitle);
    });
    
    // Load CTM forms for sync selection
    function loadCTMFormsForSync() {
        const select = $('#ctm-sync-form-select');
        select.html('<option value=""><?php _e('Loading CTM forms...', 'call-tracking-metrics'); ?></option>');
        $('#ctm-sync-confirm').prop('disabled', true);
        
        // AJAX call to get available CTM forms
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'ctm_get_available_forms',
                nonce: '<?php echo wp_create_nonce('ctm_form_import_nonce'); ?>'
            },
            success: function(response) {
                if (response.success && response.data.forms) {
                    select.html('<option value=""><?php _e('Select a CTM form...', 'call-tracking-metrics'); ?></option>');
                    response.data.forms.forEach(function(form) {
                        const truncatedId = form.id.length > 20 ? 
                            form.id.substring(0, 8) + '...' + form.id.substring(form.id.length - 8) : 
                            form.id;
                        select.append(`<option value="${form.id}" title="${form.id}">${form.name} (${truncatedId})</option>`);
                    });
                } else {
                    select.html('<option value=""><?php _e('No CTM forms available', 'call-tracking-metrics'); ?></option>');
                }
            },
            error: function() {
                select.html('<option value=""><?php _e('Error loading CTM forms', 'call-tracking-metrics'); ?></option>');
            }
        });
    }
    
    // Enable/disable sync button based on form selection
    $('#ctm-sync-form-select').on('change', function() {
        $('#ctm-sync-confirm').prop('disabled', !$(this).val());
    });
    
    // Handle sync confirm
    $('#ctm-sync-confirm').on('click', function() {
        const formId = $(this).data('form-id');
        const formType = $(this).data('form-type');
        const formTitle = $(this).data('form-title');
        const ctmFormId = $('#ctm-sync-form-select').val();
        
        if (!ctmFormId) return;
        
        // Show loading state
        $(this).prop('disabled', true).text('<?php _e('Syncing...', 'call-tracking-metrics'); ?>');
        
        // AJAX call to sync the forms
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'ctm_sync_form',
                nonce: '<?php echo wp_create_nonce('ctm_form_import_nonce'); ?>',
                wp_form_id: formId,
                wp_form_type: formType,
                ctm_form_id: ctmFormId
            },
            success: function(response) {
                if (response.success) {
                    $('#ctm-sync-modal').addClass('hidden');
                    $('body').removeClass('overflow-hidden');
                    
                    // Show success message and reload page
                    $('body').append('<div class="fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded" style="z-index: 999999;" id="ctm-sync-success-toast">' +
                        '<span class="block sm:inline"><?php _e('Form synced successfully! Reloading page...', 'call-tracking-metrics'); ?></span>' +
                        '</div>');
                    
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    alert('<?php _e('Sync failed: ', 'call-tracking-metrics'); ?>' + (response.data ? response.data.message : '<?php _e('Unknown error', 'call-tracking-metrics'); ?>'));
                    $(this).prop('disabled', false).text('<?php _e('Sync Forms', 'call-tracking-metrics'); ?>');
                }
            },
            error: function() {
                alert('<?php _e('Sync failed. Please try again.', 'call-tracking-metrics'); ?>');
                $(this).prop('disabled', false).text('<?php _e('Sync Forms', 'call-tracking-metrics'); ?>');
            }
        });
    });
    
    // Handle sync cancel
    $('#ctm-sync-cancel').on('click', function() {
        $('#ctm-sync-modal').addClass('hidden');
        $('body').removeClass('overflow-hidden');
    });
    
    // Close sync modal when clicking outside
    $('#ctm-sync-modal').on('click', function(e) {
        if (e.target === this) {
            $(this).addClass('hidden');
            $('body').removeClass('overflow-hidden');
        }
    });
    
    // Handle CTM Update buttons
    $('.ctm-update-form').on('click', function() {
        const formId = $(this).data('form-id');
        const formType = $(this).data('form-type');
        const formTitle = $(this).data('form-title');
        const ctmFormId = $(this).data('ctm-form-id');
        
        // Show update confirmation modal
        $('#ctm-update-form-title').text(formTitle);
        $('#ctm-update-form-type').text(formType.toUpperCase());
        $('#ctm-update-modal').removeClass('hidden').addClass('flex');
        $('body').addClass('overflow-hidden');
        
        // Store form data for the update action
        $('#ctm-update-confirm').data('form-id', formId).data('form-type', formType).data('form-title', formTitle).data('ctm-form-id', ctmFormId);
    });
    
    // Handle update confirmation
    $('#ctm-update-confirm').on('click', function() {
        const formId = $(this).data('form-id');
        const formType = $(this).data('form-type');
        const formTitle = $(this).data('form-title');
        const ctmFormId = $(this).data('ctm-form-id');
        const originalButton = $('.ctm-update-form[data-form-id="' + formId + '"]');
        
        // Show loading state
        $(this).prop('disabled', true).html('<svg class="w-4 h-4 mr-1 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg><?php _e('Updating...', 'call-tracking-metrics'); ?>');
        originalButton.prop('disabled', true).html('<svg class="w-4 h-4 mr-1 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg><?php _e('Updating...', 'call-tracking-metrics'); ?>');
        
        // AJAX call to update the form
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'ctm_update_form',
                nonce: '<?php echo wp_create_nonce('ctm_form_import_nonce'); ?>',
                wp_form_id: formId,
                wp_form_type: formType,
                ctm_form_id: ctmFormId
            },
            success: function(response) {
                if (response.success) {
                    // Show success message and reload page
                    $('body').append('<div class="fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded" style="z-index: 999999;" id="ctm-update-success-toast">' +
                        '<span class="block sm:inline"><?php _e('Form updated successfully! Reloading page...', 'call-tracking-metrics'); ?></span>' +
                        '</div>');
                    
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    alert('<?php _e('Update failed: ', 'call-tracking-metrics'); ?>' + (response.data ? response.data.message : '<?php _e('Unknown error', 'call-tracking-metrics'); ?>'));
                    $(this).prop('disabled', false).html('<?php _e('Update from CTM', 'call-tracking-metrics'); ?>');
                    originalButton.prop('disabled', false).html('<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg><?php _e('Update from CTM', 'call-tracking-metrics'); ?>');
                }
            }.bind(this),
            error: function() {
                alert('<?php _e('Update failed. Please try again.', 'call-tracking-metrics'); ?>');
                $(this).prop('disabled', false).html('<?php _e('Update from CTM', 'call-tracking-metrics'); ?>');
                originalButton.prop('disabled', false).html('<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg><?php _e('Update from CTM', 'call-tracking-metrics'); ?>');
            }.bind(this)
        });
        
        // Close modal
        $('#ctm-update-modal').addClass('hidden').removeClass('flex');
        $('body').removeClass('overflow-hidden');
    });
    
    // Handle update cancel
    $('#ctm-update-cancel').on('click', function() {
        $('#ctm-update-modal').addClass('hidden').removeClass('flex');
        $('body').removeClass('overflow-hidden');
    });
    
    // Close update modal when clicking outside
    $('#ctm-update-modal').on('click', function(e) {
        if (e.target === this) {
            $(this).addClass('hidden').removeClass('flex');
            $('body').removeClass('overflow-hidden');
        }
    });
    
    // Handle Form Preview buttons using unified preview system
    $('.ctm-preview-wp-form').on('click', function() {
        const formId = $(this).data('form-id');
        const formType = $(this).data('form-type');
        const formTitle = $(this).data('form-title');
        
        // Use unified preview system
        CTMPreview.showWPPreview({
            formId: formId,
            formType: formType,
            formTitle: formTitle,
            tabbed: true, // Consistent tabbed interface with Raw Code + Rendered Form
            nonce: '<?php echo wp_create_nonce('ctm_form_import_nonce'); ?>'
        });
    });
    
    // Modal events are now handled by the unified CTMPreview system
    
    // Handle Form Usage buttons
    $('.ctm-form-usage').on('click', function() {
        const formId = $(this).data('form-id');
        const formType = $(this).data('form-type');
        const formTitle = $(this).data('form-title');
        const forceRefresh = $(this).data('force-refresh') || false;
        
        // Show modal and set form title
        $('#ctm-usage-form-title').text(formTitle);
        $('#ctm-usage-modal').removeClass('hidden').addClass('flex');
        $('#ctm-usage-modal').data('current-form-id', formId);
        $('#ctm-usage-modal').data('current-form-type', formType);
        $('#ctm-usage-modal').data('current-form-title', formTitle);
        $('body').addClass('overflow-hidden');
        
        // Reset modal states
        $('#ctm-usage-loading').show();
        $('#ctm-usage-content').hide().html('');
        $('#ctm-usage-error').hide();
        
        // Show cache status
        if (forceRefresh) {
            $('#ctm-usage-loading').html('<div class="text-center"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto mb-2"></div><p class="text-sm text-gray-600"><?php _e('Refreshing form usage data...', 'call-tracking-metrics'); ?></p></div>');
        } else {
            $('#ctm-usage-loading').html('<div class="text-center"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto mb-2"></div><p class="text-sm text-gray-600"><?php _e('Loading form usage data...', 'call-tracking-metrics'); ?></p></div>');
        }
        
        // AJAX call to get form usage data
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'ctm_get_form_usage',
                nonce: '<?php echo wp_create_nonce('ctm_form_usage_nonce'); ?>',
                form_id: formId,
                form_type: formType,
                force_refresh: forceRefresh
            },
            success: function(response) {
                $('#ctm-usage-loading').hide();
                
                if (response.success && response.data) {
                    let usageHtml = '';
                    
                    // Check for form existence error
                    if (response.data.error) {
                        usageHtml += '<div class="mb-4 p-4 bg-red-50 rounded-lg border border-red-200">';
                        usageHtml += '<div class="flex items-center mb-2">';
                        usageHtml += '<svg class="w-5 h-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
                        usageHtml += '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
                        usageHtml += '</svg>';
                        usageHtml += '<h4 class="text-sm font-medium text-red-800">Form Not Found</h4>';
                        usageHtml += '</div>';
                        usageHtml += '<p class="text-sm text-red-700">' + response.data.error_details + '</p>';
                        usageHtml += '<p class="text-xs text-red-600 mt-2">The form may have been deleted or the plugin may not be active. Enhanced search results below may still show cached references.</p>';
                        usageHtml += '</div>';
                    }
                    
                    // Show cache status
                    if (forceRefresh) {
                        usageHtml += '<div class="mb-4 p-3 bg-green-50 rounded-lg border border-green-200">';
                        usageHtml += '<p class="text-sm text-green-800"><?php _e('✓ Fresh data loaded (cache bypassed)', 'call-tracking-metrics'); ?></p>';
                        usageHtml += '</div>';
                    } else {
                        usageHtml += '<div class="mb-4 p-3 bg-blue-50 rounded-lg border border-blue-200">';
                        usageHtml += '<p class="text-sm text-blue-800"><?php _e('Data loaded (may be cached)', 'call-tracking-metrics'); ?></p>';
                        usageHtml += '<button id="ctm-force-refresh" class="mt-2 text-xs text-blue-600 hover:text-blue-800 underline"><?php _e('Force Refresh', 'call-tracking-metrics'); ?></button>';
                        usageHtml += '</div>';
                    }
                    
                    // Show enhanced pages first (more comprehensive search)
                    if (response.data.enhanced_pages && response.data.enhanced_pages.length > 0) {
                        usageHtml += '<div class="mb-6"><h4 class="text-sm font-medium text-gray-900 mb-3"><?php _e('Pages & Posts (Enhanced Search)', 'call-tracking-metrics'); ?></h4>';
                        usageHtml += '<div class="space-y-2">';
                        response.data.enhanced_pages.forEach(function(page) {
                            usageHtml += '<div class="flex items-center justify-between p-3 bg-green-50 rounded-lg border border-green-200">';
                            usageHtml += '<div><a href="' + page.edit_url + '" class="text-sm font-medium text-green-700 hover:text-green-800" target="_blank">' + page.title + '</a>';
                            usageHtml += '<p class="text-xs text-green-600">' + page.type + ' • ID: ' + page.id + ' • Match: ' + (page.match_type || 'unknown') + '</p>';
                            usageHtml += '<p class="text-xs text-green-500">' + page.url + '</p></div>';
                            usageHtml += '<div class="flex space-x-2">';
                            usageHtml += '<a href="' + page.view_url + '" class="text-xs text-green-600 hover:text-green-700" target="_blank"><?php _e('View', 'call-tracking-metrics'); ?></a>';
                            usageHtml += '<a href="' + page.edit_url + '" class="text-xs text-green-600 hover:text-green-700" target="_blank"><?php _e('Edit', 'call-tracking-metrics'); ?></a>';
                            usageHtml += '</div></div>';
                        });
                        usageHtml += '</div></div>';
                    }
                    
                    // Show custom post types
                    if (response.data.custom_post_types && response.data.custom_post_types.length > 0) {
                        usageHtml += '<div class="mb-6"><h4 class="text-sm font-medium text-gray-900 mb-3"><?php _e('Custom Post Types', 'call-tracking-metrics'); ?></h4>';
                        usageHtml += '<div class="space-y-2">';
                        response.data.custom_post_types.forEach(function(post) {
                            usageHtml += '<div class="flex items-center justify-between p-3 bg-purple-50 rounded-lg border border-purple-200">';
                            usageHtml += '<div><a href="' + post.edit_url + '" class="text-sm font-medium text-purple-700 hover:text-purple-800" target="_blank">' + post.title + '</a>';
                            usageHtml += '<p class="text-xs text-purple-600">' + post.type + ' • ID: ' + post.id + '</p>';
                            usageHtml += '<p class="text-xs text-purple-500">' + post.url + '</p></div>';
                            usageHtml += '<div class="flex space-x-2">';
                            usageHtml += '<a href="' + post.view_url + '" class="text-xs text-purple-600 hover:text-purple-700" target="_blank"><?php _e('View', 'call-tracking-metrics'); ?></a>';
                            usageHtml += '<a href="' + post.edit_url + '" class="text-xs text-purple-600 hover:text-purple-700" target="_blank"><?php _e('Edit', 'call-tracking-metrics'); ?></a>';
                            usageHtml += '</div></div>';
                        });
                        usageHtml += '</div></div>';
                    }
                    
                    if (response.data.pages && response.data.pages.length > 0) {
                        usageHtml += '<div class="mb-6"><h4 class="text-sm font-medium text-gray-900 mb-3"><?php _e('Pages & Posts (Exact Shortcode)', 'call-tracking-metrics'); ?></h4>';
                        usageHtml += '<div class="space-y-2">';
                        response.data.pages.forEach(function(page) {
                            usageHtml += '<div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">';
                            usageHtml += '<div><a href="' + page.edit_url + '" class="text-sm font-medium text-blue-600 hover:text-blue-800" target="_blank">' + page.title + '</a>';
                            usageHtml += '<p class="text-xs text-gray-500">' + page.type + ' • ID: ' + page.id + '</p></div>';
                            usageHtml += '<a href="' + page.view_url + '" class="text-xs text-gray-500 hover:text-gray-700" target="_blank"><?php _e('View', 'call-tracking-metrics'); ?></a>';
                            usageHtml += '</div>';
                        });
                        usageHtml += '</div></div>';
                    }
                    
                    if (response.data.widgets && response.data.widgets.length > 0) {
                        usageHtml += '<div class="mb-6"><h4 class="text-sm font-medium text-gray-900 mb-3"><?php _e('Widgets', 'call-tracking-metrics'); ?></h4>';
                        usageHtml += '<div class="space-y-2">';
                        response.data.widgets.forEach(function(widget) {
                            usageHtml += '<div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">';
                            usageHtml += '<div><span class="text-sm font-medium text-gray-900">' + widget.title + '</span>';
                            usageHtml += '<p class="text-xs text-gray-500">' + widget.area + '</p></div>';
                            usageHtml += '<a href="' + widget.edit_url + '" class="text-xs text-blue-600 hover:text-blue-800" target="_blank"><?php _e('Edit', 'call-tracking-metrics'); ?></a>';
                            usageHtml += '</div>';
                        });
                        usageHtml += '</div></div>';
                    }
                    
                    if (response.data.shortcodes && response.data.shortcodes.length > 0) {
                        usageHtml += '<div class="mb-6"><h4 class="text-sm font-medium text-gray-900 mb-3"><?php _e('Shortcode Usage', 'call-tracking-metrics'); ?></h4>';
                        usageHtml += '<div class="space-y-2">';
                        response.data.shortcodes.forEach(function(shortcode) {
                            usageHtml += '<div class="p-3 bg-gray-50 rounded-lg">';
                            usageHtml += '<div class="flex items-center justify-between mb-2">';
                            usageHtml += '<span class="text-sm font-medium text-gray-900">' + shortcode.location + '</span>';
                            usageHtml += '<span class="text-xs text-gray-500">' + shortcode.count + ' <?php _e('instances', 'call-tracking-metrics'); ?></span>';
                            usageHtml += '</div>';
                            usageHtml += '<code class="text-xs bg-gray-100 px-2 py-1 rounded">' + shortcode.code + '</code>';
                            usageHtml += '</div>';
                        });
                        usageHtml += '</div></div>';
                    }
                    
                    if (response.data.theme_files && response.data.theme_files.length > 0) {
                        usageHtml += '<div class="mb-6"><h4 class="text-sm font-medium text-gray-900 mb-3"><?php _e('Theme Files', 'call-tracking-metrics'); ?></h4>';
                        usageHtml += '<div class="space-y-2">';
                        response.data.theme_files.forEach(function(file) {
                            usageHtml += '<div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">';
                            usageHtml += '<div><span class="text-sm font-medium text-gray-900">' + file.name + '</span>';
                            usageHtml += '<p class="text-xs text-gray-500">' + file.path + '</p></div>';
                            usageHtml += '<span class="text-xs text-gray-500">' + file.count + ' <?php _e('references', 'call-tracking-metrics'); ?></span>';
                            usageHtml += '</div>';
                        });
                        usageHtml += '</div></div>';
                    }
                    
                    if (response.data.theme_files_forms && response.data.theme_files_forms.length > 0) {
                        usageHtml += '<div class="mb-6"><h4 class="text-sm font-medium text-gray-900 mb-3"><?php _e('Theme Files (Form Detection)', 'call-tracking-metrics'); ?></h4>';
                        usageHtml += '<div class="space-y-2">';
                        response.data.theme_files_forms.forEach(function(file) {
                            usageHtml += '<div class="flex items-center justify-between p-3 bg-orange-50 rounded-lg border border-orange-200">';
                            usageHtml += '<div><span class="text-sm font-medium text-orange-700">' + file.file + '</span>';
                            usageHtml += '<p class="text-xs text-orange-600">' + file.path + '</p>';
                            usageHtml += '<p class="text-xs text-orange-500">Match: ' + file.match_type + '</p></div>';
                            usageHtml += '<span class="text-xs text-orange-600"><?php _e('Theme File', 'call-tracking-metrics'); ?></span>';
                            usageHtml += '</div>';
                        });
                        usageHtml += '</div></div>';
                    }
                    
                    // Show broad Gravity Forms references if available
                    if (response.data.gravity_forms_references && response.data.gravity_forms_references.length > 0) {
                        usageHtml += '<div class="mb-6"><h4 class="text-sm font-medium text-gray-900 mb-3"><?php _e('Gravity Forms References (Broad Search)', 'call-tracking-metrics'); ?></h4>';
                        usageHtml += '<div class="space-y-2">';
                        response.data.gravity_forms_references.forEach(function(ref) {
                            usageHtml += '<div class="flex items-center justify-between p-3 bg-purple-50 rounded-lg border border-purple-200">';
                            usageHtml += '<div><a href="' + ref.edit_url + '" class="text-sm font-medium text-purple-700 hover:text-purple-800" target="_blank">' + ref.title + '</a>';
                            usageHtml += '<p class="text-xs text-purple-600">' + ref.type + ' • ID: ' + ref.id + ' • Match: ' + ref.match_details + '</p>';
                            usageHtml += '<p class="text-xs text-purple-500">' + ref.url + '</p></div>';
                            usageHtml += '<div class="flex space-x-2">';
                            usageHtml += '<a href="' + ref.view_url + '" class="text-xs text-purple-600 hover:text-purple-700" target="_blank"><?php _e('View', 'call-tracking-metrics'); ?></a>';
                            usageHtml += '<a href="' + ref.edit_url + '" class="text-xs text-purple-600 hover:text-purple-700" target="_blank"><?php _e('Edit', 'call-tracking-metrics'); ?></a>';
                            usageHtml += '</div></div>';
                        });
                        usageHtml += '</div></div>';
                    }
                    
                    if (!usageHtml || (response.data.pages.length === 0 && response.data.enhanced_pages.length === 0 && response.data.custom_post_types.length === 0 && response.data.widgets.length === 0 && response.data.theme_files.length === 0 && (!response.data.gravity_forms_references || response.data.gravity_forms_references.length === 0))) {
                        usageHtml = '<div class="text-center py-8"><svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>';
                        usageHtml += '<p class="text-gray-500 text-sm"><?php _e('No usage found for this form.', 'call-tracking-metrics'); ?></p>';
                        usageHtml += '<p class="text-gray-400 text-xs mt-2"><?php _e('The form may not be used anywhere yet, or usage detection is limited.', 'call-tracking-metrics'); ?></p>';
                        usageHtml += '<p class="text-gray-400 text-xs mt-1"><?php _e('Try checking the debug information above for more details.', 'call-tracking-metrics'); ?></p></div>';
                    }
                    
                    $('#ctm-usage-content').html(usageHtml).show();
                } else {
                    $('#ctm-usage-error').show();
                    $('#ctm-usage-error-message').text(response.data ? response.data.message : '<?php _e('Unknown error occurred', 'call-tracking-metrics'); ?>');
                }
            },
            error: function() {
                $('#ctm-usage-loading').hide();
                $('#ctm-usage-error').show();
                $('#ctm-usage-error-message').text('<?php _e('Network error occurred', 'call-tracking-metrics'); ?>');
            }
        });
    });
    
    // Handle usage modal close
    $('#ctm-usage-close, #ctm-usage-close-btn').on('click', function() {
        $('#ctm-usage-modal').addClass('hidden').removeClass('flex');
        $('body').removeClass('overflow-hidden');
    });
    
    // Close usage modal when clicking outside
    $('#ctm-usage-modal').on('click', function(e) {
        if (e.target === this) {
            $(this).addClass('hidden').removeClass('flex');
            $('body').removeClass('overflow-hidden');
        }
    });
    
    // Handle force refresh
    $(document).on('click', '#ctm-force-refresh', function() {
        const formId = $('#ctm-usage-modal').data('current-form-id');
        const formType = $('#ctm-usage-modal').data('current-form-type');
        const formTitle = $('#ctm-usage-modal').data('current-form-title');
        
        // Set force refresh and trigger usage check
        $('.ctm-form-usage[data-form-id="' + formId + '"][data-form-type="' + formType + '"]').data('force-refresh', true).click();
    });
    
    // Form Logs Functionality
    (function($) {
        'use strict';

        // Initialize form logs functionality
        function initFormLogs() {
            // Check if debug mode is enabled
            if (!window.ctmFormLogsData || !window.ctmFormLogsData.debug_enabled) {
                return; // Don't initialize if debug mode is disabled
            }
            
            // Handle escape key to close modal
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape') {
                    const $modal = $('#ctm-form-logs-modal');
                    if (!$modal.hasClass('hidden')) {
                        $modal.addClass('hidden');
                        $('body').removeClass('ctm-modal-open');
                    }
                }
            });
            
            // Handle view logs button clicks
            $(document).on('click', '.ctm-view-form-logs', function(e) {
                e.preventDefault();
                
                const $button = $(this);
                const formType = $button.data('form-type');
                const formId = $button.data('form-id');
                const formTitle = $button.data('form-title');
                
                // Show loading state
                $button.prop('disabled', true).html('<svg class="animate-spin w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Loading...');
                
                // Fetch form logs
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'ctm_get_form_logs',
                        form_type: formType,
                        form_id: formId,
                        nonce: ctmFormLogsData.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            showFormLogsModal(formType, formId, formTitle, response.data.logs);
                        } else {
                            showError('Failed to load form logs: ' + (response.data?.message || 'Unknown error'));
                        }
                    },
                    error: function() {
                        showError('Failed to load form logs. Please try again.');
                    },
                    complete: function() {
                        // Reset button state
                        $button.prop('disabled', false).html('<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>View Logs');
                    }
                });
            });

            // Handle clear logs button clicks
            $(document).on('click', '.ctm-clear-form-logs', function(e) {
                e.preventDefault();
                
                const $button = $(this);
                const formType = $button.data('form-type');
                const formId = $button.data('form-id');
                const formTitle = $button.data('form-title');
                
                if (!confirm(`Are you sure you want to clear all logs for "${formTitle}"? This action cannot be undone.`)) {
                    return;
                }
                
                // Show loading state
                $button.prop('disabled', true).text('Clearing...');
                
                // Clear form logs
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'ctm_clear_form_logs',
                        form_type: formType,
                        form_id: formId,
                        nonce: ctmFormLogsData.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            showSuccess('Form logs cleared successfully');
                            // Refresh the logs display
                            refreshFormLogs(formType, formId, formTitle);
                        } else {
                            showError('Failed to clear form logs: ' + (response.data?.message || 'Unknown error'));
                        }
                    },
                    error: function() {
                        showError('Failed to clear form logs. Please try again.');
                    },
                    complete: function() {
                        // Reset button state
                        $button.prop('disabled', false).text('Clear Logs');
                    }
                });
            });

            // Handle reload logs button clicks
            $(document).on('click', '.ctm-reload-form-logs', function(e) {
                e.preventDefault();
                
                const $button = $(this);
                const formType = $button.data('form-type');
                const formId = $button.data('form-id');
                const formTitle = $button.data('form-title');
                
                // Show loading state
                $button.prop('disabled', true).html('<svg class="animate-spin w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Loading...');
                
                // Reload form logs
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'ctm_get_form_logs',
                        form_type: formType,
                        form_id: formId,
                        nonce: ctmFormLogsData.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            const $modal = $('#ctm-form-logs-modal');
                            updateFormLogsContent($modal, formType, formId, formTitle, response.data.logs);
                            showSuccess('Logs refreshed successfully');
                        } else {
                            showError('Failed to reload logs: ' + (response.data?.message || 'Unknown error'));
                        }
                    },
                    error: function() {
                        showError('Failed to reload logs. Please try again.');
                    },
                    complete: function() {
                        // Reset button state
                        $button.prop('disabled', false).html('<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>Reload');
                    }
                });
            });
        }

        // Show form logs modal
        function showFormLogsModal(formType, formId, formTitle, logs) {
            const modalId = 'ctm-form-logs-modal';
            let $modal = $('#' + modalId);
            
            // Create modal if it doesn't exist
            if ($modal.length === 0) {
                $modal = createFormLogsModal(modalId);
                $('body').append($modal);
            }
            
            // Update modal content
            updateFormLogsContent($modal, formType, formId, formTitle, logs);
            
            // Hide WordPress admin menu and show modal
            $('body').addClass('ctm-modal-open');
            $modal.removeClass('hidden');
        }

        // Create form logs modal
        function createFormLogsModal(modalId) {
            return $(`
                <div id="${modalId}" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
                    <div class="relative top-10 mx-auto p-5 border w-11/12 max-w-5xl shadow-lg rounded-md bg-white" style="max-height: 90vh;">
                        <div class="mt-3">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900" id="modal-title">Form Logs</h3>
                                <button type="button" class="ctm-close-modal text-gray-400 hover:text-gray-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                            <div class="mb-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <span class="text-sm text-gray-600" id="modal-form-info"></span>
                                        <span class="text-sm text-gray-500" id="modal-log-count"></span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <button type="button" class="ctm-reload-form-logs inline-flex items-center px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-md hover:bg-blue-100 hover:text-blue-700 transition-colors">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                            </svg>
                                            Reload
                                        </button>
                                        <button type="button" class="ctm-clear-form-logs inline-flex items-center px-3 py-2 text-sm font-medium text-red-600 bg-red-50 rounded-md hover:bg-red-100 hover:text-red-700 transition-colors">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                            Clear Logs
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="overflow-y-auto" style="max-height: calc(90vh - 200px);">
                                <div id="modal-logs-content" class="space-y-3">
                                    <!-- Logs will be inserted here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `);
        }

        // Update form logs content
        function updateFormLogsContent($modal, formType, formId, formTitle, logs) {
            // Update modal title and info
            $modal.find('#modal-title').text(`Form Logs - ${formTitle}`);
            $modal.find('#modal-form-info').text(`${formType.toUpperCase()} Form ID: ${formId}`);
            $modal.find('#modal-log-count').text(`${logs.length} log entries`);
            
            // Set data attributes for buttons
            $modal.find('.ctm-clear-form-logs, .ctm-reload-form-logs').data({
                'form-type': formType,
                'form-id': formId,
                'form-title': formTitle
            });
            
            // Sort logs by most recent first
            const sortedLogs = sortLogsByDate(logs);
            
            // Generate logs content
            const logsHtml = generateLogsHtml(sortedLogs);
            $modal.find('#modal-logs-content').html(logsHtml);
            
            // Handle close button
            $modal.find('.ctm-close-modal, .ctm-modal-overlay').off('click').on('click', function() {
                $modal.addClass('hidden');
                $('body').removeClass('ctm-modal-open');
            });
        }

        // Generate logs HTML
        function generateLogsHtml(logs) {
            if (logs.length === 0) {
                return `
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="text-gray-500">No logs found for this form.</p>
                    </div>
                `;
            }
            
            return logs.map(log => `
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <div class="flex items-start justify-between mb-2">
                        <div class="flex items-center space-x-2">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${getLogTypeColor(log.type)}">
                                ${log.type}
                            </span>
                            <span class="text-sm text-gray-600">${formatTimestamp(log.timestamp)}</span>
                        </div>
                        <span class="text-xs text-gray-500">${log.form_type.toUpperCase()}</span>
                    </div>
                    <div class="text-sm text-gray-800 mb-2">${log.message}</div>
                    ${log.payload ? `
                        <details class="mb-2">
                            <summary class="text-sm text-gray-600 cursor-pointer hover:text-gray-800">View Payload</summary>
                            <pre class="mt-2 text-xs bg-gray-100 p-2 rounded overflow-x-auto">${JSON.stringify(log.payload, null, 2)}</pre>
                        </details>
                    ` : ''}
                    ${log.response ? `
                        <details class="mb-2">
                            <summary class="text-sm text-gray-600 cursor-pointer hover:text-gray-800">View Response</summary>
                            <pre class="mt-2 text-xs bg-gray-100 p-2 rounded overflow-x-auto">${JSON.stringify(log.response, null, 2)}</pre>
                        </details>
                    ` : ''}
                    ${log.context ? `
                        <details>
                            <summary class="text-sm text-gray-600 cursor-pointer hover:text-gray-800">View Context</summary>
                            <pre class="mt-2 text-xs bg-gray-100 p-2 rounded overflow-x-auto">${JSON.stringify(log.context, null, 2)}</pre>
                        </details>
                    ` : ''}
                </div>
            `).join('');
        }

        // Get log type color
        function getLogTypeColor(type) {
            const colors = {
                'form_submission': 'bg-green-100 text-green-800',
                'error': 'bg-red-100 text-red-800',
                'warning': 'bg-yellow-100 text-yellow-800',
                'info': 'bg-blue-100 text-blue-800',
                'debug': 'bg-gray-100 text-gray-800'
            };
            return colors[type] || colors.info;
        }

        // Format timestamp
        function formatTimestamp(timestamp) {
            return new Date(timestamp).toLocaleString();
        }

        // Sort logs by date (most recent first)
        function sortLogsByDate(logs) {
            return logs.sort((a, b) => {
                const dateA = new Date(a.timestamp);
                const dateB = new Date(b.timestamp);
                return dateB - dateA; // Most recent first
            });
        }

        // Refresh form logs
        function refreshFormLogs(formType, formId, formTitle) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'ctm_get_form_logs',
                    form_type: formType,
                    form_id: formId,
                    nonce: ctmFormLogsData.nonce
                },
                success: function(response) {
                    if (response.success) {
                        const $modal = $('#ctm-form-logs-modal');
                        updateFormLogsContent($modal, formType, formId, formTitle, response.data.logs);
                    }
                }
            });
        }

        // Show success message
        function showSuccess(message) {
            // You can implement your own toast notification here
            alert(message);
        }

        // Show error message
        function showError(message) {
            // You can implement your own toast notification here
            alert('Error: ' + message);
        }

        // Initialize when document is ready
        $(document).ready(function() {
            initFormLogs();
        });

    })(jQuery);
});
</script>