<?php
/**
 * API Tab View
 * 
 * This view file displays the API configuration tab in the CallTrackingMetrics
 * admin interface, allowing users to configure API credentials and test connections.
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

// API Activity tab view
// Data is passed from Options.php: $apiKey, $apiSecret, $apiStatus, $accountInfo
$api_connected = ($apiStatus === 'connected');
$apiBaseUrl = isset($apiBaseUrl) ? rtrim($apiBaseUrl, '/') : ctm_get_api_url();
$availableApiEndpoints = isset($availableApiEndpoints) && is_array($availableApiEndpoints)
    ? $availableApiEndpoints
    : ctm_get_available_api_endpoints();
?>
<?php // Add nonces for AJAX actions
$ctm_change_api_nonce = wp_create_nonce('ctm_change_api_keys');
$ctm_disable_api_nonce = wp_create_nonce('ctm_disable_api');
?>

<div class="space-y-6">
    
    <?php if ($api_connected && !empty($accountInfo)): ?>
        
        <!-- Header Section -->
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
            <div class="px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-6 h-6 bg-blue-500 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-xl font-semibold text-gray-900"><?php _e('API Dashboard', 'call-tracking-metrics'); ?></h1>
                            <p class="text-gray-600 text-sm"><?php _e('Monitor your Call Tracking Metrics API connection and account status', 'call-tracking-metrics'); ?></p>
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        <button id="ctm-change-api-btn" class="bg-blue-50 hover:bg-blue-100 text-blue-700 px-4 py-2 rounded-lg font-medium transition-all duration-200 flex items-center space-x-2 border border-blue-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span><?php _e('Change Keys', 'call-tracking-metrics'); ?></span>
                        </button>
                        <button id="ctm-disable-api-btn" class="bg-red-50 hover:bg-red-100 text-red-700 px-4 py-2 rounded-lg font-medium transition-all duration-200 flex items-center space-x-2 border border-red-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"/>
                            </svg>
                            <span><?php _e('Disable API', 'call-tracking-metrics'); ?></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Account Information Card -->
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center space-x-3">
                        <div class="w-6 h-6 bg-blue-500 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900"><?php _e('Account Information', 'call-tracking-metrics'); ?></h3>
                        <span class="ml-auto px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full"><?php _e('Active', 'call-tracking-metrics'); ?></span>
                    </div>
                </div>
                
                <div class="p-6">
                    <?php if (isset($accountInfo['account']) && is_array($accountInfo['account'])): ?>
                        <?php $account = $accountInfo['account']; ?>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-600 text-sm"><?php _e('Account Name', 'call-tracking-metrics'); ?></span>
                                <span class="font-medium text-gray-900"><?= esc_html($account['name'] ?? 'N/A') ?></span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-600 text-sm"><?php _e('Account ID', 'call-tracking-metrics'); ?></span>
                                <span class="font-mono font-medium text-gray-900"><?= esc_html($account['id'] ?? 'N/A') ?></span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-600 text-sm"><?php _e('Status', 'call-tracking-metrics'); ?></span>
                                <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full flex items-center space-x-1">
                                    <div class="w-1.5 h-1.5 bg-green-500 rounded-full"></div>
                                    <span><?= esc_html(ucfirst($account['status'] ?? 'active')) ?></span>
                                </span>
                            </div>
                            <?php if (!empty($account['phone'])): ?>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-600 text-sm"><?php _e('Phone', 'call-tracking-metrics'); ?></span>
                                <span class="font-medium text-gray-900"><?= esc_html($account['phone']) ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($account['website'])): ?>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-600 text-sm"><?php _e('Website', 'call-tracking-metrics'); ?></span>
                                <a href="<?= esc_url($account['website']) ?>" target="_blank" class="text-blue-600 hover:text-blue-800 underline font-medium text-sm"><?= esc_html($account['website']) ?></a>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($account['plan'])): ?>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-600 text-sm"><?php _e('Plan', 'call-tracking-metrics'); ?></span>
                                <span class="font-medium text-gray-900"><?= esc_html($account['plan']) ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="text-gray-500 text-sm"><?php _e('Account information not available', 'call-tracking-metrics'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- API Connection Card -->
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center space-x-3">
                        <div class="w-6 h-6 bg-gray-500 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900"><?php _e('API Connection', 'call-tracking-metrics'); ?></h3>
                        <span class="ml-auto px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full"><?php _e('Connected', 'call-tracking-metrics'); ?></span>
                    </div>
                </div>
                
                <div class="p-6">
                    <div class="space-y-3">
                        <div class="flex justify-between items-center py-2">
                            <span class="text-gray-600 text-sm"><?php _e('API Key', 'call-tracking-metrics'); ?></span>
                            <span class="font-mono font-medium text-gray-900 bg-gray-50 px-2 py-1 rounded text-sm"><?= esc_html(substr($apiKey ?? '', 0, 8)) ?>••••••••</span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-gray-600 text-sm"><?php _e('Base URL', 'call-tracking-metrics'); ?></span>
                            <span id="ctm-api-base-url-display" class="font-mono font-medium text-gray-900 text-sm break-all"><?= esc_html($apiBaseUrl) ?></span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-gray-600 text-sm"><?php _e('Last Connected', 'call-tracking-metrics'); ?></span>
                            <span class="font-medium text-gray-900"><?= date('M j, Y g:i A') ?></span>
                        </div>
                    </div>
                    <div class="mt-6">
                        <div class="bg-gray-50 border border-gray-200 rounded-xl p-5">
                            <div class="flex flex-col gap-4">
                                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                                    <div>
                                        <h4 class="text-base font-semibold text-gray-900"><?php _e('Switch API Endpoint', 'call-tracking-metrics'); ?></h4>
                                        <p class="text-sm text-gray-500 mt-1"><?php _e('Select the regional CallTrackingMetrics API that best fits your account.', 'call-tracking-metrics'); ?></p>
                                    </div>
                                    <span class="inline-flex items-center text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-full px-4 py-1.5 shadow-sm">
                                        <span class="uppercase tracking-wide text-xs text-gray-400 mr-2"><?php _e('Current', 'call-tracking-metrics'); ?></span>
                                        <span class="font-mono text-gray-700"><?= esc_html(parse_url($apiBaseUrl, PHP_URL_HOST)); ?></span>
                                    </span>
                                </div>
                                <div class="flex flex-col md:flex-row md:items-end gap-3">
                                    <div class="md:flex-1 md:max-w-md">
                                        <label for="ctm_api_base_url_select" class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide"><?php _e('Endpoint', 'call-tracking-metrics'); ?></label>
                                        <select id="ctm_api_base_url_select" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm">
                                            <?php foreach ($availableApiEndpoints as $endpointUrl => $label): ?>
                                                <option value="<?= esc_attr($endpointUrl); ?>" <?= selected($apiBaseUrl, rtrim($endpointUrl, '/'), false); ?>>
                                                    <?= esc_html($label); ?> (<?= esc_html(parse_url($endpointUrl, PHP_URL_HOST)); ?>)
                                                </option>
                                            <?php endforeach; ?>
                                            <?php if (!array_key_exists($apiBaseUrl, $availableApiEndpoints)): ?>
                                                <option value="<?= esc_attr($apiBaseUrl); ?>" selected>
                                                    <?= sprintf(__('Custom (%s)', 'call-tracking-metrics'), esc_html($apiBaseUrl)); ?>
                                                </option>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                    <button type="button" id="ctm-update-endpoint-btn" class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-sm font-semibold rounded-full shadow-sm transition-all duration-200 gap-2 self-start">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                        </svg>
                                        <span><?php _e('Update', 'call-tracking-metrics'); ?></span>
                                    </button>
                                </div>
                                <div id="ctm-endpoint-update-message" class="text-xs"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Connection Monitor Card - Full Width -->
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-6 h-6 bg-green-500 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900"><?php _e('Connection Monitor', 'call-tracking-metrics'); ?></h3>
                        <span id="ctm-status-badge" class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full"><?php _e('Connected', 'call-tracking-metrics'); ?></span>
                    </div>
                    <div class="text-right">
                        <div id="ctm-last-test" class="text-xs text-gray-500"><?= date('g:i:s A') ?></div>
                    </div>
                </div>
            </div>
            
            <div class="p-6">
                <div class="flex flex-wrap gap-3 mb-6">
                    <button id="ctm-test-api-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-all duration-200 flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span><?php _e('Test Connection', 'call-tracking-metrics'); ?></span>
                    </button>
                </div>
                
                <!-- Progress Bar -->
                <div id="ctm-progress-container" class="hidden mb-6">
                    <div class="flex justify-between text-sm text-gray-600 mb-2">
                        <span class="font-medium"><?php _e('Connection Test Progress', 'call-tracking-metrics'); ?></span>
                        <span id="ctm-progress-percent" class="font-semibold">0%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                        <div id="ctm-progress-bar" class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                </div>
                
                <!-- Test Logs -->
                <div id="ctm-test-logs" class="bg-gray-50 border border-gray-200 rounded-lg p-4 h-48 overflow-y-auto font-mono text-sm mt-4">
                    <div class="text-gray-500 italic"><?php _e('Click "Test Connection" to see real-time logs...', 'call-tracking-metrics'); ?></div>
                </div>
            </div>
        </div>

        <!-- Account Summary Card -->
        <div id="ctm-account-summary" class="hidden bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center space-x-3">
                    <div class="w-6 h-6 bg-green-500 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900"><?php _e('Test Results', 'call-tracking-metrics'); ?></h3>
                    <span class="ml-auto px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full"><?php _e('Success', 'call-tracking-metrics'); ?></span>
                </div>
            </div>
            
            <div class="p-6">
                <div id="ctm-account-details" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Populated by JavaScript -->
                </div>
            </div>
        </div>

        <!-- Technical Details Card -->
        <div id="ctm-technical-details" class="hidden bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-6 h-6 bg-gray-500 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900"><?php _e('Technical Details', 'call-tracking-metrics'); ?></h3>
                    </div>
                    <button type="button" id="ctm-toggle-technical" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-5 h-5 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <div id="ctm-technical-content" class="hidden p-6">
                <div id="ctm-tech-details-content" class="space-y-6">
                    <!-- Populated by JavaScript -->
                </div>
            </div>
        </div>

        <!-- Modals -->
        <!-- Change API Keys Modal -->
        <div id="ctm-change-api-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm hidden">
            <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4 relative border border-gray-200">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-6 h-6 bg-blue-500 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900"><?php _e('Change API Keys', 'call-tracking-metrics'); ?></h3>
                </div>
                <form id="ctm-change-api-form">
                    <div class="mb-4">
                        <label for="ctm_new_api_key" class="block text-gray-700 font-medium mb-2"><?php _e('API Key', 'call-tracking-metrics'); ?></label>
                        <input type="text" id="ctm_new_api_key" name="ctm_new_api_key" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" required autocomplete="off">
                    </div>
                    <div class="mb-6">
                        <label for="ctm_new_api_secret" class="block text-gray-700 font-medium mb-2"><?php _e('API Secret', 'call-tracking-metrics'); ?></label>
                        <input type="text" id="ctm_new_api_secret" name="ctm_new_api_secret" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" required autocomplete="off">
                    </div>
                    <div class="mb-6">
                        <label for="ctm_new_api_base_url" class="block text-gray-700 font-medium mb-2"><?php _e('API Endpoint', 'call-tracking-metrics'); ?></label>
                        <select id="ctm_new_api_base_url" name="ctm_new_api_base_url" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                            <?php foreach ($availableApiEndpoints as $endpointUrl => $label): ?>
                                <option value="<?= esc_attr($endpointUrl); ?>" <?= selected($apiBaseUrl, rtrim($endpointUrl, '/'), false); ?>>
                                    <?= esc_html($label); ?> (<?= esc_html(parse_url($endpointUrl, PHP_URL_HOST)); ?>)
                                </option>
                            <?php endforeach; ?>
                            <?php if (!array_key_exists($apiBaseUrl, $availableApiEndpoints)): ?>
                                <option value="<?= esc_attr($apiBaseUrl); ?>" selected>
                                    <?= sprintf(__('Custom (%s)', 'call-tracking-metrics'), esc_html($apiBaseUrl)); ?>
                                </option>
                            <?php endif; ?>
                        </select>
                        <p class="text-gray-500 text-xs mt-2"><?php _e('Pick the regional endpoint that matches where your CTM account is hosted.', 'call-tracking-metrics'); ?></p>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" id="ctm-cancel-change-api" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-medium transition-all duration-200 flex items-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            <span><?php _e('Cancel', 'call-tracking-metrics'); ?></span>
                        </button>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-all duration-200 flex items-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span><?php _e('Save Changes', 'call-tracking-metrics'); ?></span>
                        </button>
                    </div>
                </form>
                <button id="ctm-close-change-api" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 text-xl leading-none transition-colors">&times;</button>
            </div>
        </div>

        <!-- Disable API Modal -->
        <div id="ctm-disable-api-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm hidden">
            <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4 relative border border-gray-200">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-6 h-6 bg-red-500 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-red-700"><?php _e('Disable API', 'call-tracking-metrics'); ?></h3>
                </div>
                <div class="bg-red-50 rounded-lg p-4 border border-red-200 mb-6">
                    <div class="flex items-start space-x-3">
                        <svg class="w-5 h-5 text-red-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.08 15.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                        <p class="text-red-800 text-sm leading-relaxed"><?php _e('Are you sure you want to disable the API and clear all credentials? This action cannot be undone.', 'call-tracking-metrics'); ?></p>
                    </div>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" id="ctm-cancel-disable-api" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-medium transition-all duration-200 flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        <span><?php _e('Cancel', 'call-tracking-metrics'); ?></span>
                    </button>
                    <button type="button" id="ctm-confirm-disable-api" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-all duration-200 flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        <span><?php _e('Disable API', 'call-tracking-metrics'); ?></span>
                    </button>
                </div>
                <button id="ctm-close-disable-api" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 text-xl leading-none transition-colors">&times;</button>
            </div>
        </div>
        
    <?php else: ?>
        <!-- Not Connected State -->
        <div class="bg-white border border-red-200 rounded-lg shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-red-200">
                <div class="flex items-center space-x-3">
                    <div class="w-6 h-6 bg-red-500 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.08 15.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                    </div>
                    <h2 class="text-lg font-semibold text-gray-900"><?php _e('API Not Connected', 'call-tracking-metrics'); ?></h2>
                </div>
            </div>
            
            <div class="p-6 text-center">
                <div class="w-16 h-16 mx-auto mb-4 bg-red-100 rounded-lg flex items-center justify-center">
                    <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.08 15.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2"><?php _e('API Connection Required', 'call-tracking-metrics'); ?></h3>
                <p class="text-gray-600 mb-6 text-sm leading-relaxed max-w-md mx-auto">
                    <?php _e('You need to configure your API credentials to view account information and API activity.', 'call-tracking-metrics'); ?>
                </p>
                <a href="?page=call-tracking-metrics&tab=general" class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-all duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <?php _e('Configure API Settings', 'call-tracking-metrics'); ?>
                </a>
            </div>
        </div>
    <?php endif; ?>
    
</div>

<script>
// Localize script data for API tab
var ctmApiData = {
    api_key: '<?= esc_js($apiKey ?? '') ?>',
    api_secret: '<?= esc_js($apiSecret ?? '') ?>',
    nonce: '<?= wp_create_nonce('ctm_test_api_connection') ?>',
    change_api_nonce: '<?= wp_create_nonce('ctm_change_api_keys') ?>',
    disable_api_nonce: '<?= wp_create_nonce('ctm_disable_api') ?>',
    api_base_url: '<?= esc_js($apiBaseUrl); ?>',
    api_endpoints: <?= wp_json_encode($availableApiEndpoints); ?>,
    custom_endpoint_label: '<?= esc_js(__('Custom (%s)', 'call-tracking-metrics')); ?>',
    endpoint_update_success: '<?= esc_js(__('API endpoint updated successfully.', 'call-tracking-metrics')); ?>',
    endpoint_update_error: '<?= esc_js(__('Failed to update API endpoint. Please try again.', 'call-tracking-metrics')); ?>',
    endpoint_update_in_progress: '<?= esc_js(__('Updating...', 'call-tracking-metrics')); ?>',
    initial_connected: <?= ($api_connected && !empty($accountInfo)) ? 'true' : 'false' ?>
};

jQuery(document).ready(function($) {
    // Only initialize if we're on the API tab
    if ($('#ctm-test-logs').length === 0) {
        return; // Not on API tab, exit early
    }
    
    let isTestInProgress = false;
    
    // Update status indicators
    function updateStatus(success, message = '') {
        const indicator = $('#ctm-status-indicator');
        const badge = $('#ctm-status-badge');
        const lastTest = $('#ctm-last-test');
        
        if (success) {
            indicator.removeClass('bg-red-500 bg-yellow-500').addClass('bg-green-500');
            badge.removeClass('bg-red-100 text-red-800 bg-yellow-100 text-yellow-800').addClass('bg-green-100 text-green-800').text('Connected');
        } else {
            indicator.removeClass('bg-green-500 bg-yellow-500').addClass('bg-red-500');
            badge.removeClass('bg-green-100 text-green-800 bg-yellow-100 text-yellow-800').addClass('bg-red-100 text-red-800').text('Failed');
        }
        
        lastTest.text('Last test: ' + new Date().toLocaleTimeString());
    }
    

    
    // Perform API test
    function performApiTest() {
        if (isTestInProgress) return;
        
        isTestInProgress = true;
        const button = $('#ctm-test-api-btn');
        const logs = $('#ctm-test-logs');
        const progressContainer = $('#ctm-progress-container');
        const progressBar = $('#ctm-progress-bar');
        const progressPercent = $('#ctm-progress-percent');
        
        // Update button state
        button.prop('disabled', true).html('<svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Testing...');
        
        // Add test log
        appendLog('info', 'Starting API connection test...');
        progressContainer.removeClass('hidden');
        
        // AJAX call to test API
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'ctm_test_api_connection',
                api_key: ctmApiData.api_key,
                api_secret: ctmApiData.api_secret,
                nonce: ctmApiData.nonce,
                api_base_url: ctmApiData.api_base_url
            },
            success: function(response) {
                if (response.success) {
                    updateStatus(true);
                    
                    progressBar.css('width', '100%');
                    progressPercent.text('100%');
                    
                    let logHtml = '';
                    if (response.data.logs) {
                        response.data.logs.forEach(log => {
                            let bgClass = 'bg-gray-50';
                            let textClass = 'text-gray-700';
                            let icon = '•';
                            
                            if (log.includes('✓') || log.includes('Success')) {
                                bgClass = 'bg-green-50 border-l-4 border-green-500';
                                textClass = 'text-green-700';
                                icon = '✓';
                            } else if (log.includes('✗') || log.includes('Error')) {
                                bgClass = 'bg-red-50 border-l-4 border-red-500';
                                textClass = 'text-red-700';
                                icon = '✗';
                            } else if (log.includes('→') || log.includes('Sending')) {
                                bgClass = 'bg-blue-50 border-l-4 border-blue-500';
                                textClass = 'text-blue-700';
                                icon = '→';
                            }
                            
                            logHtml += `<div class="mb-2 p-2 rounded ${bgClass}"><span class="${textClass}">${icon} ${log}</span></div>`;
                        });
                    }
                    logs.html(logHtml);
                    
                    // Show duration if available
                    if (response.data.duration) {
                        logs.append(`<div class="mt-3 p-2 bg-gray-100 rounded text-center"><span class="text-gray-600">Total test duration: ${response.data.duration}ms</span></div>`);
                    }
                    
                    displayAccountSummary(response.data.account_info.account, response.data.account_details?.account, response.data.capabilities);
                    displayTechnicalDetails(response.data);
                    displayPerformanceMetrics(response.data.performance, response.data.connection_quality);
                    if (response.data.metadata && response.data.metadata.api_endpoint) {
                        updateApiBaseUrl(response.data.metadata.api_endpoint);
                    }
                } else {
                    updateStatus(false, response.data || 'Test failed');
                    appendLog('error', `❌ ${response.data || 'Test failed'}`);
                }
            },
            error: function() {
                updateStatus(false, 'AJAX request failed');
                appendLog('error', '❌ AJAX request failed');
            },
            complete: function() {
                isTestInProgress = false;
                button.prop('disabled', false).html('<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>Test Now');
            }
        });
    }
    
    // Event handlers
    $('#ctm-test-api-btn').on('click', function() {
        performApiTest();
    });
    
    // Clear Logs Button
    $('#ctm-clear-logs-btn').on('click', function() {
        clearTestLogs();
    });
    
    // Technical Details Toggle
    $('#ctm-toggle-technical').on('click', function() {
        const content = $('#ctm-technical-content');
        const icon = $(this).find('svg');
        content.toggleClass('hidden');
        icon.toggleClass('rotate-90');
    });
    
    // Quick endpoint update button
    $('#ctm-update-endpoint-btn').on('click', function() {
        const newBase = $('#ctm_api_base_url_select').val();
        if (!newBase) {
            return;
        }
        const $button = $(this);
        const originalText = $button.text();
        showEndpointMessage('', '');
        $button.prop('disabled', true).addClass('opacity-75 cursor-wait').text(ctmApiData.endpoint_update_in_progress || originalText);
        $.post(ajaxurl, {
            action: 'ctm_change_api_keys',
            api_key: '',
            api_secret: '',
            api_base_url: newBase,
            nonce: ctmApiData.change_api_nonce
        }, function(resp) {
            if (resp.success) {
                updateApiBaseUrl(newBase);
                showEndpointMessage('success', ctmApiData.endpoint_update_success);
            } else {
                const errorMessage = resp.data && resp.data.message ? resp.data.message : ctmApiData.endpoint_update_error;
                showEndpointMessage('error', errorMessage);
            }
        }).fail(function() {
            showEndpointMessage('error', ctmApiData.endpoint_update_error);
        }).always(function() {
            $button.prop('disabled', false).removeClass('opacity-75 cursor-wait').text(originalText);
        });
    });
    
    // Modal logic for Change API Keys
    $('#ctm-change-api-btn').on('click', function() {
        ensureEndpointOption(ctmApiData.api_base_url);
        $('#ctm_new_api_base_url').val(ctmApiData.api_base_url);
        $('#ctm-change-api-modal').removeClass('hidden');
    });
    $('#ctm-close-change-api, #ctm-cancel-change-api').on('click', function() {
        $('#ctm-change-api-modal').addClass('hidden');
    });
    
    // AJAX submit for Change API Keys
    $('#ctm-change-api-form').on('submit', function(e) {
        e.preventDefault();
        var apiKey = $('#ctm_new_api_key').val();
        var apiSecret = $('#ctm_new_api_secret').val();
        var nonce = ctmApiData.change_api_nonce;
        var apiBaseUrl = $('#ctm_new_api_base_url').val();
        var $modal = $('#ctm-change-api-modal');
        var $saveBtn = $(this).find('button[type=submit]');
        $saveBtn.prop('disabled', true).text('Saving...');
        $.post(ajaxurl, {
            action: 'ctm_change_api_keys',
            api_key: apiKey,
            api_secret: apiSecret,
            api_base_url: apiBaseUrl,
            nonce: nonce
        }, function(resp) {
            $saveBtn.prop('disabled', false).text('Save');
            if (resp.success) {
                $modal.addClass('hidden');
                alert('API keys updated successfully.');
                location.reload();
            } else {
                alert(resp.data && resp.data.message ? resp.data.message : 'Failed to update API keys.');
            }
        });
    });
    
    // Modal logic for Disable API
    $('#ctm-disable-api-btn').on('click', function() {
        $('#ctm-disable-api-modal').removeClass('hidden');
    });
    $('#ctm-close-disable-api, #ctm-cancel-disable-api').on('click', function() {
        $('#ctm-disable-api-modal').addClass('hidden');
    });
    
    // AJAX for Disable API
    $('#ctm-confirm-disable-api').on('click', function() {
        var nonce = ctmApiData.disable_api_nonce;
        var $modal = $('#ctm-disable-api-modal');
        var $btn = $(this);
        $btn.prop('disabled', true).text('Disabling...');
        $.post(ajaxurl, {
            action: 'ctm_disable_api',
            nonce: nonce
        }, function(resp) {
            $btn.prop('disabled', false).text('Disable API');
            if (resp.success) {
                $modal.addClass('hidden');
                alert('API credentials cleared.');
                location.reload();
            } else {
                alert(resp.data && resp.data.message ? resp.data.message : 'Failed to disable API.');
            }
        });
    });
    
    // Add initial connection status
    if (ctmApiData.initial_connected) {
        appendLog('success', `✓ [${new Date().toLocaleTimeString()}] API connection verified`);
    }
    

    
    function ensureEndpointOption(value) {
        if (!value) {
            return;
        }
        const selects = $('#ctm_new_api_base_url, #ctm_api_base_url_select');
        if (!selects.length) {
            return;
        }
        selects.each(function() {
            const select = $(this);
            if (select.find('option[value="' + value + '"]').length === 0) {
                const label = ctmApiData.api_endpoints && ctmApiData.api_endpoints[value]
                    ? ctmApiData.api_endpoints[value]
                    : (ctmApiData.custom_endpoint_label || 'Custom (%s)').replace('%s', value);
                const option = $('<option>').attr('value', value).text(label);
                select.append(option);
            }
        });
    }
    
    function updateApiBaseUrl(newUrl) {
        if (!newUrl) {
            return;
        }
        const normalized = newUrl.replace(/\/+$/, '');
        ctmApiData.api_base_url = normalized;
        $('#ctm-api-base-url-display').text(normalized);
        ensureEndpointOption(normalized);
        $('#ctm_new_api_base_url').val(normalized);
        $('#ctm_api_base_url_select').val(normalized);
    }
    
    function showEndpointMessage(type, message) {
        const messageEl = $('#ctm-endpoint-update-message');
        messageEl.removeClass();
        messageEl.addClass('text-xs mt-2');
        if (!message) {
            messageEl.text('');
            return;
        }
        if (type === 'success') {
            messageEl.addClass('px-3 py-2 rounded border bg-green-50 border-green-200 text-green-700');
        } else {
            messageEl.addClass('px-3 py-2 rounded border bg-red-50 border-red-200 text-red-700');
        }
        messageEl.text(message);
    }
    
    function appendLog(type, message) {
        const logs = $('#ctm-test-logs');
        const timestamp = new Date().toLocaleTimeString();
        let colorClass = 'text-gray-600';
        let bgClass = 'bg-gray-50';
        let icon = '•';
        switch(type) {
            case 'success':
                colorClass = 'text-green-700';
                bgClass = 'bg-green-50';
                icon = '✓';
                break;
            case 'error':
                colorClass = 'text-red-700';
                bgClass = 'bg-red-50';
                icon = '✗';
                break;
            case 'warning':
                colorClass = 'text-yellow-700';
                bgClass = 'bg-yellow-50';
                icon = '⚠';
                break;
            case 'info':
            default:
                colorClass = 'text-blue-700';
                bgClass = 'bg-blue-50';
                icon = 'ⓘ';
                break;
        }
        const logEntry = $(`
            <div class="${colorClass} ${bgClass} p-2 rounded border-l-2 border-current flex gap-2 items-start">
                <span class="text-gray-400 text-xs font-mono mt-0.5">[${timestamp}]</span>
                <span class="font-bold mt-0.5">${icon}</span>
                <span class="flex-1 font-medium">${message}</span>
            </div>
        `);
        logs.append(logEntry);
        logs.scrollTop(logs[0].scrollHeight);
    }
    
    function displayAccountSummary(account, details, capabilities) {
        const accountSummary = $('#ctm-account-summary');
        const accountDetails = $('#ctm-account-details');
        accountSummary.removeClass('hidden');
        let summaryHTML = '';
        summaryHTML += `<div class="bg-gray-50 p-3 rounded border border-gray-200 text-sm"><span class="font-medium text-gray-700">Name:</span> <span class="text-gray-900">${account.name || 'N/A'}</span></div>`;
        summaryHTML += `<div class="bg-gray-50 p-3 rounded border border-gray-200 text-sm"><span class="font-medium text-gray-700">Account ID:</span> <code class="bg-gray-100 px-2 py-1 rounded text-xs font-mono">${account.id || 'N/A'}</code></div>`;
        if (account.email) {
            summaryHTML += `<div class="bg-gray-50 p-3 rounded border border-gray-200 text-sm"><span class="font-medium text-gray-700">Email:</span> <span class="text-gray-900">${account.email}</span></div>`;
        }
        if (capabilities) {
            const accessIcon = capabilities.account_access ? '✓' : '✗';
            const detailsIcon = capabilities.details_access ? '✓' : '✗';
            summaryHTML += `<div class="bg-gray-50 p-3 rounded border border-gray-200 text-sm"><span class="font-medium text-gray-700">API Access:</span> <span class="text-green-600">${accessIcon}</span> Account, <span class="${capabilities.details_access ? 'text-green-600' : 'text-yellow-600'}">${detailsIcon}</span> Details</div>`;
            summaryHTML += `<div class="bg-gray-50 p-3 rounded border border-gray-200 text-sm"><span class="font-medium text-gray-700">API Version:</span> <span class="text-gray-900">${capabilities.api_version}</span></div>`;
        }
        if (details) {
            if (details.status) {
                const statusColor = details.status === 'active' ? 'text-green-600' : 'text-yellow-600';
                summaryHTML += `<div class="bg-gray-50 p-3 rounded border border-gray-200 text-sm"><span class="font-medium text-gray-700">Status:</span> <span class="${statusColor} font-semibold">${details.status}</span></div>`;
            }
            if (details.timezone) {
                summaryHTML += `<div class="bg-gray-50 p-3 rounded border border-gray-200 text-sm"><span class="font-medium text-gray-700">Timezone:</span> <span class="text-gray-900">${details.timezone}</span></div>`;
            }
            if (details.created_at) {
                const date = new Date(details.created_at).toLocaleDateString();
                summaryHTML += `<div class="bg-gray-50 p-3 rounded border border-gray-200 text-sm"><span class="font-medium text-gray-700">Created:</span> <span class="text-gray-900">${date}</span></div>`;
            }
            if (details.phone) {
                summaryHTML += `<div class="bg-gray-50 p-3 rounded border border-gray-200 text-sm"><span class="font-medium text-gray-700">Phone:</span> <span class="text-gray-900">${details.phone}</span></div>`;
            }
            if (details.website) {
                summaryHTML += `<div class="bg-gray-50 p-3 rounded border border-gray-200 text-sm"><span class="font-medium text-gray-700">Website:</span> <a href="${details.website}" target="_blank" class="text-blue-600 underline">${details.website}</a></div>`;
            }
            if (details.plan || details.subscription) {
                summaryHTML += `<div class="bg-gray-50 p-3 rounded border border-gray-200 text-sm"><span class="font-medium text-gray-700">Plan:</span> <span class="text-gray-900">${details.plan || details.subscription || 'Not specified'}</span></div>`;
            }
        }
        accountDetails.html(summaryHTML);
        appendLog('success', `Account verified: ${account.name} (${account.id})`);
    }
    
    function displayTechnicalDetails(data) {
        const technicalDetails = $('#ctm-technical-details');
        const techDetailsContent = $('#ctm-tech-details-content');
        technicalDetails.removeClass('hidden');
        
        let detailsHTML = '';
        
        // Request Metadata Section
        if (data.metadata) {
            detailsHTML += `
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
                    <div class="flex items-center space-x-2 mb-3">
                        <div class="w-5 h-5 bg-blue-500 rounded-full flex items-center justify-center">
                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h4 class="text-sm font-semibold text-gray-900">Request Metadata</h4>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 font-medium">Request ID:</span>
                            <span class="font-mono font-medium text-gray-900 bg-gray-100 px-2 py-1 rounded text-xs">${data.metadata.request_id?.substring(0, 8) || 'N/A'}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 font-medium">WordPress:</span>
                            <span class="font-medium text-gray-900">${data.metadata.wordpress_version || 'N/A'}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 font-medium">Plugin:</span>
                            <span class="font-medium text-gray-900">${data.metadata.plugin_version || 'N/A'}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 font-medium">Timestamp:</span>
                            <span class="font-medium text-gray-900">${data.metadata.timestamp || 'N/A'}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 font-medium">PHP:</span>
                            <span class="font-medium text-gray-900">${data.metadata.php_version || 'N/A'}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 font-medium">Auth Method:</span>
                            <span class="font-medium text-gray-900">${data.metadata.auth_method || 'N/A'}</span>
                        </div>
                    </div>
                </div>
            `;
        }
        
        // Performance Metrics Section
        if (data.performance) {
            detailsHTML += `
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
                    <div class="flex items-center space-x-2 mb-3">
                        <div class="w-5 h-5 bg-green-500 rounded-full flex items-center justify-center">
                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <h4 class="text-sm font-semibold text-gray-900">Performance Metrics</h4>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 font-medium">Total Time:</span>
                            <span class="font-medium text-gray-900">${data.performance.total_execution_time}ms</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 font-medium">Details Response:</span>
                            <span class="font-medium text-gray-900">${data.performance.details_response_time || 'N/A'}ms</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 font-medium">API Response:</span>
                            <span class="font-medium text-gray-900">${data.performance.api_response_time}ms</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 font-medium">Network Overhead:</span>
                            <span class="font-medium text-gray-900">${data.performance.network_overhead?.toFixed(1) || 'N/A'}ms</span>
                        </div>
                    </div>
                </div>
            `;
        }
        
        // API Endpoints Section
        if (data.metadata) {
            detailsHTML += `
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
                    <div class="flex items-center space-x-2 mb-3">
                        <div class="w-5 h-5 bg-purple-500 rounded-full flex items-center justify-center">
                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                            </svg>
                        </div>
                        <h4 class="text-sm font-semibold text-gray-900">API Endpoints</h4>
                    </div>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 font-medium">Base URL:</span>
                            <span class="font-mono font-medium text-gray-900 bg-gray-100 px-2 py-1 rounded text-xs">${data.metadata.api_endpoint || 'N/A'}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 font-medium">Account:</span>
                            <span class="font-mono font-medium text-gray-900 bg-gray-100 px-2 py-1 rounded text-xs">${data.metadata.account_endpoint || 'N/A'}</span>
                        </div>
                        ${data.metadata.details_endpoint ? `
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 font-medium">Details:</span>
                            <span class="font-mono font-medium text-gray-900 bg-gray-100 px-2 py-1 rounded text-xs">${data.metadata.details_endpoint}</span>
                        </div>
                        ` : ''}
                    </div>
                </div>
            `;
        }
        
        // Connection Quality Section
        if (data.connection_quality) {
            const quality = data.connection_quality;
            const qualityBadgeColor = quality.color === 'green' ? 'bg-green-100 text-green-800' :
                                      quality.color === 'blue' ? 'bg-blue-100 text-blue-800' :
                                      quality.color === 'yellow' ? 'bg-yellow-100 text-yellow-800' : 
                                      'bg-red-100 text-red-800';
            detailsHTML += `
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
                    <div class="flex items-center space-x-2 mb-3">
                        <div class="w-5 h-5 bg-orange-500 rounded-full flex items-center justify-center">
                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <h4 class="text-sm font-semibold text-gray-900">Connection Quality</h4>
                    </div>
                    <div class="flex items-center space-x-3 text-sm">
                        <span class="px-3 py-1 rounded ${qualityBadgeColor} font-medium text-xs">${quality.rating.toUpperCase()}</span>
                        <span class="text-gray-600 font-medium">${quality.total_time}ms total response time</span>
                    </div>
                </div>
            `;
        }
        
        // Raw Response Data (collapsible)
        const sanitizedData = JSON.parse(JSON.stringify(data));
        if (sanitizedData.account_info?.account) {
            delete sanitizedData.account_info.account.api_key;
            delete sanitizedData.account_info.account.api_secret;
        }
        const jsonString = JSON.stringify(sanitizedData, null, 2);
        detailsHTML += `
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <button type="button" id="ctm-toggle-raw-data" class="flex items-center space-x-2 text-xs text-gray-600 hover:text-gray-800 transition-colors w-full text-left">
                    <svg id="ctm-raw-data-icon" class="w-3 h-3 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                    <span>Raw API Response (JSON)</span>
                </button>
                <div id="ctm-raw-data-content" class="hidden mt-3">
                    <div class="bg-gray-50 border border-gray-200 rounded p-3">
                        <div class="text-xs text-gray-600 mb-2">Complete API Response (sensitive data removed)</div>
                        <pre class="whitespace-pre-wrap text-xs overflow-x-auto text-gray-800">${escapeHtml(jsonString)}</pre>
                    </div>
                </div>
            </div>
        `;
        
        techDetailsContent.html(detailsHTML);
        
        // Add event handler for raw data toggle
        $('#ctm-toggle-raw-data').on('click', function() {
            const content = $('#ctm-raw-data-content');
            const icon = $('#ctm-raw-data-icon');
            content.toggleClass('hidden');
            icon.toggleClass('rotate-90');
        });
    }
    
    function escapeHtml(text) {
        return $('<div/>').text(text).html();
    }
    
    function displayPerformanceMetrics(performance, quality) {
        if (quality) {
            appendLog('info', `${quality.description}`);
            appendLog('info', `API Response: ${performance.api_response_time}ms`);
            if (performance.details_response_time) {
                appendLog('info', `Details Response: ${performance.details_response_time}ms`);
            }
            if (performance.network_overhead > 0) {
                appendLog('info', `Network Overhead: ${performance.network_overhead.toFixed(1)}ms`);
            }
        }
    }
});
</script> 