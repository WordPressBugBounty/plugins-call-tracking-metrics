<?php
// General tab view

// Ensure required variables are defined with defaults
$apiStatus = $apiStatus ?? 'not_connected';
$apiKey = $apiKey ?? '';
$apiSecret = $apiSecret ?? '';
$apiBaseUrl = isset($apiBaseUrl) ? rtrim($apiBaseUrl, '/') : ctm_get_api_url();
$availableApiEndpoints = isset($availableApiEndpoints) && is_array($availableApiEndpoints)
    ? $availableApiEndpoints
    : ctm_get_available_api_endpoints();
$isApiUrlOverridden = $isApiUrlOverridden ?? false;

// API credentials are passed from the controller, no hardcoding needed
?>
<form method="post" action="" class="space-y-6">
    <?php wp_nonce_field('ctm_save_settings', 'ctm_settings_nonce'); ?>

    <?php if (isset($_GET['error']) && $_GET['error'] === 'api_required'): ?>
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.08 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <div>
                    <h3 class="text-sm font-medium text-red-800"><?php _e('API Connection Required', 'call-tracking-metrics'); ?></h3>
                    <p class="text-sm text-red-700 mt-1"><?php _e('You need to connect your API credentials before accessing that feature. Please enter your API Key and Secret below.', 'call-tracking-metrics'); ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($apiStatus !== 'connected'): ?>
        <!-- API Connection Only View (Not Connected) -->
        <div class="max-w-4xl mx-auto">
            <div class="bg-white p-8 rounded-lg shadow-lg border border-gray-200">
                <div class="text-center mb-6">
                    <div class="flex items-center justify-center mb-4">
                        <div class="bg-[#e6f7ff] p-4 rounded-full">
                           <img src="<?= plugin_dir_url(__DIR__) . '/assets/images/ctm_logo-mark_cyan_400x400.png' ?>" alt="CallTrackingMetrics Logo" class="h-10">
                        </div>
                    </div>
                    <h2 class="text-2xl font-bold text-[#16294f] mb-2 font-brand-heading"><?php _e('Connect to CallTrackingMetrics', 'call-tracking-metrics'); ?></h2>
                    <p class="text-gray-600 font-brand-body"><?php _e('Enter your API credentials to get started with CTM integration', 'call-tracking-metrics'); ?></p>
                </div>



                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block mb-2 text-gray-700 font-medium"><?php _e('API Key', 'call-tracking-metrics'); ?></label>
                        <div class="relative">
                            <input type="password" id="ctm_api_key" name="ctm_api_key" value="<?= esc_attr($apiKey) ?>" class="block w-full rounded border-gray-300 focus:ring-[#02bdf6] focus:border-[#02bdf6] pr-10" placeholder="<?php esc_attr_e('Enter your API key', 'call-tracking-metrics'); ?>" />
                            <button type="button" tabindex="-1" onclick="let f=document.getElementById('ctm_api_key');f.type=f.type==='password'?'text':'password';this.innerHTML=f.type==='password'?'<svg class=\'w-5 h-5\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M15 12a3 3 0 11-6 0 3 3 0 016 0z\'/><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z\'/></svg>':'<svg class=\'w-5 h-5\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.956 9.956 0 012.223-3.592m3.31-2.687A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.956 9.956 0 01-4.043 5.306M15 12a3 3 0 11-6 0 3 3 0 016 0z\'/><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M3 3l18 18\'/></svg>';" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#02bdf6] focus:outline-none">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block mb-2 text-gray-700 font-medium"><?php _e('API Secret', 'call-tracking-metrics'); ?></label>
                        <div class="relative">
                            <input type="password" id="ctm_api_secret" name="ctm_api_secret" value="<?= esc_attr($apiSecret) ?>" class="block w-full rounded border-gray-300 focus:ring-[#02bdf6] focus:border-[#02bdf6] pr-10" placeholder="<?php esc_attr_e('Enter your API secret', 'call-tracking-metrics'); ?>" />
                            <button type="button" tabindex="-1" onclick="let f=document.getElementById('ctm_api_secret');f.type=f.type==='password'?'text':'password';this.innerHTML=f.type==='password'?'<svg class=\'w-5 h-5\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M15 12a3 3 0 11-6 0 3 3 0 016 0z\'/><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z\'/></svg>':'<svg class=\'w-5 h-5\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.956 9.956 0 012.223-3.592m3.31-2.687A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.956 9.956 0 01-4.043 5.306M15 12a3 3 0 11-6 0 3 3 0 016 0z\'/><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M3 3l18 18\'/></svg>';" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#02bdf6] focus:outline-none">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mb-6">
                    <label for="ctm_api_base_url" class="block mb-2 text-gray-700 font-medium"><?php _e('API Endpoint', 'call-tracking-metrics'); ?></label>
                    <select id="ctm_api_base_url" name="ctm_api_base_url" class="block w-full rounded border-gray-300 focus:ring-[#02bdf6] focus:border-[#02bdf6] <?php echo $isApiUrlOverridden ? 'bg-gray-100 cursor-not-allowed text-gray-500' : ''; ?>" <?php echo $isApiUrlOverridden ? 'disabled' : ''; ?>>
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
                    <?php if ($isApiUrlOverridden): ?>
                        <p class="text-orange-600 text-sm mt-1">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <?php _e('This setting is currently overridden by the CTM_API_BASE_URL constant.', 'call-tracking-metrics'); ?>
                        </p>
                    <?php else: ?>
                        <p class="text-gray-500 text-sm mt-2"><?php _e('Choose the regional API endpoint that matches your CallTrackingMetrics account.', 'call-tracking-metrics'); ?></p>
                    <?php endif; ?>
                </div>






                <!-- Real-time API Test Logs -->
                <div id="api-test-logs" class="hidden mt-6 p-6 bg-gray-50 border border-gray-200 rounded-lg max-h-96 overflow-y-auto">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="font-semibold text-[#16294f] flex items-center gap-2 font-brand-heading">
                            <svg class="w-5 h-5 text-[#02bdf6] inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            <?php _e('Connection Test', 'call-tracking-metrics'); ?>
                        </h4>
                        <div class="flex gap-2">
                            <span id="test-duration" class="text-xs text-gray-500 bg-gray-200 px-2 py-1 rounded hidden"></span>
                            <button type="button" onclick="clearTestLogs()" class="text-xs text-gray-500 hover:text-gray-700 bg-gray-200 hover:bg-gray-300 px-2 py-1 rounded transition flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                <?php _e('Clear', 'call-tracking-metrics'); ?>
                            </button>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div id="test-progress" class="mb-4 hidden">
                        <div class="flex justify-between text-xs text-gray-600 mb-1">
                            <span><?php _e('Testing Progress', 'call-tracking-metrics'); ?></span>
                            <span id="progress-text">0%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div id="progress-bar" class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                        </div>
                    </div>

                    <div id="api-log-content" class="text-sm space-y-2"></div>

                    <!-- Account Summary (shown after successful connection) -->
                    <div id="account-summary" class="hidden mt-4 p-3 bg-green-50 border border-green-200 rounded">
                        <h5 class="font-semibold text-green-800 mb-2 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <?php _e('Account Summary', 'call-tracking-metrics'); ?>
                        </h5>
                        <div id="account-details" class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs"></div>
                    </div>

                    <!-- Technical Details (collapsible) -->
                    <div id="technical-details" class="hidden mt-4">
                        <button type="button" onclick="toggleTechnicalDetails()" class="flex items-center gap-2 text-xs text-gray-600 hover:text-gray-800 transition">
                            <svg id="tech-details-icon" class="w-3 h-3 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                            <?php _e('View Technical Details', 'call-tracking-metrics'); ?>
                        </button>
                        <div id="tech-details-content" class="hidden mt-2 p-3 bg-gray-100 rounded text-xs font-mono overflow-x-auto"></div>
                    </div>
                </div>

                <div class="mt-8 flex justify-center">
                    <button type="submit" id="save-api-btn" class="bg-blue-600 hover:bg-blue-700 !text-white font-bold px-8 py-4 rounded-lg shadow-lg transition text-lg flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <?php _e('Save API Credentials', 'call-tracking-metrics'); ?>
                    </button>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- Full Settings View (Connected) -->
        <input type="hidden" name="ctm_api_key" value="<?= esc_attr($apiKey) ?>">
        <input type="hidden" name="ctm_api_secret" value="<?= esc_attr($apiSecret) ?>">
        <div class="max-w-md mb-6">
            <label for="ctm_api_base_url" class="block mb-2 text-gray-700 font-medium"><?php _e('API Endpoint', 'call-tracking-metrics'); ?></label>
            <select id="ctm_api_base_url" name="ctm_api_base_url" class="block w-full rounded border-gray-300 focus:ring-[#02bdf6] focus:border-[#02bdf6] <?php echo $isApiUrlOverridden ? 'bg-gray-100 cursor-not-allowed text-gray-500' : ''; ?>" <?php echo $isApiUrlOverridden ? 'disabled' : ''; ?>>
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
            <?php if ($isApiUrlOverridden): ?>
                <p class="text-orange-600 text-sm mt-1">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <?php _e('This setting is currently overridden by the CTM_API_BASE_URL constant.', 'call-tracking-metrics'); ?>
                </p>
            <?php else: ?>
                <p class="text-gray-500 text-sm mt-2"><?php _e('Switch endpoints if your account is hosted in Europe.', 'call-tracking-metrics'); ?></p>
            <?php endif; ?>
        </div>
        <div class="mb-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mb-2">
                <div class="bg-blue-50 border-l-2 border-blue-400 p-2 rounded flex items-center gap-2 text-sm">
                    <span class="font-semibold"><?php _e('API Connection:', 'call-tracking-metrics'); ?></span>
                    <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded text-xs font-medium"><?php _e('Connected', 'call-tracking-metrics'); ?></span>
                </div>
                <div class="bg-blue-50 border-l-2 border-blue-400 p-2 rounded flex items-center gap-2 text-sm">
                    <span class="font-semibold"><?php _e('Integrations:', 'call-tracking-metrics'); ?></span>
                    <?php if ($cf7Enabled): ?><span class="bg-green-100 text-green-700 px-2 py-0.5 rounded text-xs font-medium">CF7</span><?php else: ?><span class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded text-xs font-medium">CF7</span><?php endif; ?>
                    <?php if ($gfEnabled): ?><span class="bg-green-100 text-green-700 px-2 py-0.5 rounded text-xs font-medium ml-1">GF</span><?php else: ?><span class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded text-xs font-medium ml-1">GF</span><?php endif; ?>
                </div>
                <div class="bg-blue-50 border-l-2 border-blue-400 p-2 rounded flex items-center gap-2 text-sm">
                    <span class="font-semibold"><?php _e('Dashboard Widget:', 'call-tracking-metrics'); ?></span>
                    <?php if ($dashboardEnabled): ?><span class="bg-green-100 text-green-700 px-2 py-0.5 rounded text-xs font-medium">Enabled</span><?php else: ?><span class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded text-xs font-medium">Disabled</span><?php endif; ?>
                </div>
                <div class="bg-blue-50 border-l-2 border-blue-400 p-2 rounded flex items-center gap-2 text-sm">
                    <span class="font-semibold"><?php _e('Debug Mode:', 'call-tracking-metrics'); ?></span>
                    <?php if ($debugEnabled): ?><span class="bg-green-100 text-green-700 px-2 py-0.5 rounded text-xs font-medium">Enabled</span><?php else: ?><span class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded text-xs font-medium">Disabled</span><?php endif; ?>
                </div>
            </div>

        </div>
        <!-- Settings Form Fields -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Tracking Script Section -->
            <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6 shadow-sm max-w-2xl" style="display: block !important; visibility: visible !important;">
                <label for="ctm_tracking_script" class="text-xl font-semibold mb-4 text-gray-700"><?php _e('Tracking Script', 'call-tracking-metrics'); ?></label>
                <p class="text-gray-500 text-sm mb-4"><?php _e('This script is automatically fetched from CallTrackingMetrics. You can override it if needed, but we recommend using the auto-fetched version for accuracy.', 'call-tracking-metrics'); ?></p>
                <div class="flex flex-col md:flex-row md:items-center gap-4 mb-4">
                    <?php
                    $tracking_script = get_option('call_track_account_script');
                    $is_override_enabled = get_option('ctm_tracking_override_checkbox');
                    ?>
                    <textarea id="ctm_tracking_script" name="call_track_account_script" rows="3"
                        class="p-2 block w-full rounded border border-gray-300 focus:ring-blue-500 focus:border-blue-500 transition bg-gray-100 text-gray-700 disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed read-only:bg-gray-100 read-only:text-gray-400 read-only:cursor-not-allowed shadow-sm"
                        style="min-height: 60px; font-family: monospace; font-size: 0.97em; resize: vertical;"
                        <?= $is_override_enabled ? '' : 'readonly' ?>><?= esc_textarea($tracking_script) ?></textarea>
                </div>
                <div class="flex items-center gap-2 mt-2">
                    <input type="checkbox" id="ctm_tracking_override_checkbox" name="ctm_tracking_override_checkbox" value="1" class="mr-2" <?= checked(get_option('ctm_tracking_override_checkbox'), 1, false) ?>>
                    <label for="ctm_tracking_override_checkbox" class="text-gray-700 select-none cursor-pointer"><?php _e('Allow manual override of tracking code', 'call-tracking-metrics'); ?></label>
                </div>
                <div class="flex items-center gap-2 mt-4">
                    <input type="checkbox" id="ctm_auto_inject_tracking_script" name="ctm_auto_inject_tracking_script" value="1" class="mr-2" <?= checked(get_option('ctm_auto_inject_tracking_script'), 1, false) ?>>
                    <label for="ctm_auto_inject_tracking_script" class="text-gray-700 select-none cursor-pointer font-medium"><?php _e('Auto-inject tracking script into site <head>', 'call-tracking-metrics'); ?></label>
                </div>
                <p class="text-gray-500 text-xs mt-1 ml-6"><?php _e('If enabled, the tracking script above will be automatically inserted into your site\'s <head> on every page.', 'call-tracking-metrics'); ?></p>

                <!-- Inline error/success message container -->
                <div id="tracking-script-message" class="mt-4 p-3 rounded hidden"></div>

                <!-- Action buttons -->
                <div class="mt-4 flex gap-2">
                    <button type="button" onclick="fetchTrackingScriptNow()" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded transition">
                        <?php _e('Fetch Tracking Script', 'call-tracking-metrics'); ?>
                    </button>
                </div>

                <?php if (get_option('ctm_debug_enabled')): ?>
                    <!-- Debug: Test Duplicate Prevention -->
                    <div class="mt-3 p-3 bg-green-50 border border-green-200 rounded text-xs text-green-800">
                        <strong><?php _e('Debug: Test Duplicate Prevention', 'call-tracking-metrics'); ?></strong><br>
                        <button type="button" onclick="testDuplicatePrevention()" class="bg-green-600 hover:bg-green-700 text-white text-xs px-3 py-1 rounded mt-2">
                            <?php _e('Test Duplicate Prevention', 'call-tracking-metrics'); ?>
                        </button>
                        <div id="duplicate-prevention-test-result" class="mt-2 text-xs"></div>
                    </div>
                <?php endif; ?>
            </div>
            <!-- Debug Mode Section -->
            <div class="bg-white p-6 rounded-lg shadow border border-gray-200">
                <h2 class="text-xl font-semibold mb-4 text-gray-700"><?php _e('Options', 'call-tracking-metrics'); ?></h2>
                <div class="flex items-center gap-2 mb-2">
                    <input type="checkbox" id="ctm_debug_enabled" name="ctm_debug_enabled" value="1" class="mr-2" <?= checked(get_option('ctm_debug_enabled'), 1, false) ?>>
                    <label for="ctm_debug_enabled" class="text-gray-700 select-none cursor-pointer font-medium"><?php _e('Enable Debugging (show Debug tab)', 'call-tracking-metrics'); ?></label>
                </div>
                <div class="flex items-center gap-2 mb-2">
                    <input type="checkbox" id="ctm_dashboard_enabled" name="ctm_dashboard_enabled" value="1" class="mr-2" <?= checked(get_option('ctm_dashboard_enabled'), 1, false) ?>>
                    <label for="ctm_dashboard_enabled" class="text-gray-700 select-none cursor-pointer font-medium"><?php _e('Enable Dashboard Widget', 'call-tracking-metrics'); ?></label>
                </div>
            </div>

            <!-- Duplicate Prevention Section -->
            <div class="bg-white p-6 rounded-lg shadow border border-gray-200">
                <h2 class="text-xl font-semibold mb-4 text-gray-700"><?php _e('Duplicate Prevention', 'call-tracking-metrics'); ?></h2>
                <p class="text-gray-600 text-sm mb-4"><?php _e('Prevent duplicate form submissions using CTM session tracking and IP-based fallback.', 'call-tracking-metrics'); ?></p>

                <div class="flex items-center gap-2 mb-3">
                    <input type="checkbox" id="ctm_duplicate_prevention_enabled" name="ctm_duplicate_prevention_enabled" value="1" class="mr-2" <?= checked(get_option('ctm_duplicate_prevention_enabled', 1), 1, false) ?>>
                    <label for="ctm_duplicate_prevention_enabled" class="text-gray-700 select-none cursor-pointer font-medium"><?php _e('Enable duplicate submission prevention', 'call-tracking-metrics'); ?></label>
                </div>

                <div class="ml-6 space-y-3">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="ctm_duplicate_prevention_use_session" name="ctm_duplicate_prevention_use_session" value="1" class="mr-2" <?= checked(get_option('ctm_duplicate_prevention_use_session', 1), 1, false) ?>>
                        <label for="ctm_duplicate_prevention_use_session" class="text-gray-700 select-none cursor-pointer text-sm"><?php _e('Use CTM session ID for duplicate prevention', 'call-tracking-metrics'); ?></label>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="ctm_duplicate_prevention_fallback_ip" name="ctm_duplicate_prevention_fallback_ip" value="1" class="mr-2" <?= checked(get_option('ctm_duplicate_prevention_fallback_ip', 1), 1, false) ?>>
                        <label for="ctm_duplicate_prevention_fallback_ip" class="text-gray-700 select-none cursor-pointer text-sm"><?php _e('Fallback to IP-based prevention if session ID unavailable', 'call-tracking-metrics'); ?></label>
                    </div>

                    <div class="flex items-center gap-2">
                        <label for="ctm_duplicate_prevention_expiration" class="text-gray-700 text-sm"><?php _e('Prevention duration (days):', 'call-tracking-metrics'); ?></label>
                        <input type="number" id="ctm_duplicate_prevention_expiration" name="ctm_duplicate_prevention_expiration" value="<?= esc_attr(floor(get_option('ctm_duplicate_prevention_expiration', 604800) / 86400)) ?>" min="1" max="30" class="w-20 px-2 py-1 text-sm border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded text-xs text-blue-800">
                    <strong><?php _e('How it works:', 'call-tracking-metrics'); ?></strong> <?php _e('When a form is submitted, a unique key is created using the CTM session ID and form ID. If the same session/form combination is submitted again within the specified time period, the duplicate submission is blocked. If no CTM session ID is available, the system falls back to IP-based prevention.', 'call-tracking-metrics'); ?>
                </div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow border border-gray-200">
                <h2 class="text-xl font-semibold mb-4 text-gray-700"><?php _e('Integrations', 'call-tracking-metrics'); ?></h2>

                <!-- Contact Form 7 Integration -->
                <label class="flex items-center mb-2 <?= (!is_plugin_active('contact-form-7/wp-contact-form-7.php') && !class_exists('WPCF7_ContactForm') && !function_exists('wpcf7_contact_form')) ? 'opacity-50 cursor-not-allowed' : '' ?>">
                    <input type="checkbox"
                           name="ctm_api_cf7_enabled"
                           value="1"
                           <?= checked($cf7Enabled, 1, false) ?>
                           <?= (!is_plugin_active('contact-form-7/wp-contact-form-7.php') && !class_exists('WPCF7_ContactForm') && !function_exists('wpcf7_contact_form')) ? 'disabled' : '' ?>
                           class="mr-2 rounded border-gray-300 focus:ring-blue-500 <?= (!is_plugin_active('contact-form-7/wp-contact-form-7.php') && !class_exists('WPCF7_ContactForm') && !function_exists('wpcf7_contact_form')) ? 'opacity-50 cursor-not-allowed' : '' ?>" />
                    <span class="<?= (!is_plugin_active('contact-form-7/wp-contact-form-7.php') && !class_exists('WPCF7_ContactForm') && !function_exists('wpcf7_contact_form')) ? 'text-gray-400' : '' ?>"><?php _e('Enable Contact Form 7 Integration', 'call-tracking-metrics'); ?></span>
                    <?php if (!is_plugin_active('contact-form-7/wp-contact-form-7.php') && !class_exists('WPCF7_ContactForm') && !function_exists('wpcf7_contact_form')): ?>
                        <span class="ml-2 text-xs text-red-600 font-medium"><?php _e('(Plugin required)', 'call-tracking-metrics'); ?></span>
                    <?php endif; ?>
                </label>
                <!-- Integration notes for Contact Form 7 -->
                <div class="ml-6 text-xs text-gray-700 space-y-1 mb-3">
                    <div><strong><?php _e('Note:', 'call-tracking-metrics'); ?></strong> <?php _e('It is required to use a form that captures a telephone number (<code>input type="tel"</code>) in order for Contact Form 7 to integrate properly with our FormReactor. For more information, see', 'call-tracking-metrics'); ?> <a href="https://www.calltrackingmetrics.com/support/integrations/wordpress" target="_blank" rel="noopener" class="underline text-blue-700"><?php _e('Using the CallTrackingMetrics WordPress Plugin', 'call-tracking-metrics'); ?></a>.</div>
                    <div><strong><?php _e('Note:', 'call-tracking-metrics'); ?></strong> <?php _e('If you will request international (non-U.S.) phone numbers with your Contact Form 7 forms, we recommend using the plugin', 'call-tracking-metrics'); ?> <a href="https://wordpress.org/plugins/cf7-international-telephone-input/" target="_blank" rel="noopener" class="underline text-blue-700"><?php _e('International Telephone Input for Contact Form 7', 'call-tracking-metrics'); ?></a> <?php _e('to avoid possible formatting issues with our FormReactor. Both', 'call-tracking-metrics'); ?> <code>[tel]</code> <?php _e('and', 'call-tracking-metrics'); ?> <code>[intl_tel]</code> <?php _e('are now supported as phone inputs.', 'call-tracking-metrics'); ?></div>
                </div>

                <!-- Dismissible warning banner -->
                <?php if (!is_plugin_active('contact-form-7/wp-contact-form-7.php') && !class_exists('WPCF7_ContactForm') && !function_exists('wpcf7_contact_form')): ?>
                    <div id="cf7-notice" class="bg-yellow-50 border-l-2 border-yellow-400 text-yellow-800 p-1.5 rounded flex items-center justify-between gap-1 mb-3 mt-2 text-xs ml-6">
                        <div class="flex items-center justify-between w-full gap-1">
                            <span class="font-semibold"><?php _e('Contact Form 7 is not installed or activated.', 'call-tracking-metrics'); ?></span>
                            <a href="<?= esc_url(admin_url('plugin-install.php?s=contact+form+7&tab=search&type=term')) ?>" target="_blank" rel="noopener" class="ml-1 bg-blue-600 hover:bg-blue-700 text-white px-2 py-1 rounded shadow transition text-xs font-medium hover:text-white! text-white!"><?php _e('Install Contact Form 7', 'call-tracking-metrics'); ?></a>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Gravity Forms Integration -->
                <?php
                $gf_plugin_active = function_exists('is_plugin_active') ? is_plugin_active('gravityforms/gravityforms.php') : false;
                $gf_class_exists = class_exists('GFAPI');
                $gf_available = $gf_plugin_active || $gf_class_exists;
                ?>
                <label class="flex items-center mb-2 <?= (!$gf_available) ? 'opacity-50 cursor-not-allowed' : '' ?>">
                    <input type="checkbox"
                           name="ctm_api_gf_enabled"
                           value="1"
                           <?= checked($gfEnabled, 1, false) ?>
                           <?= (!$gf_available) ? 'disabled' : '' ?>
                           class="mr-2 rounded border-gray-300 focus:ring-blue-500 <?= (!$gf_available) ? 'opacity-50 cursor-not-allowed' : '' ?>" />
                    <span class="<?= (!$gf_available) ? 'text-gray-400' : '' ?>"><?php _e('Enable Gravity Forms Integration', 'call-tracking-metrics'); ?></span>
                    <?php if (!$gf_available): ?>
                        <span class="ml-2 text-xs text-red-600 font-medium"><?php _e('(Plugin required)', 'call-tracking-metrics'); ?></span>
                    <?php endif; ?>
                </label>
                <!-- Integration notes for Gravity Forms -->
                <div class="ml-6 text-xs text-gray-700 space-y-1 mb-3">
                    <div><strong><?php _e('Note:', 'call-tracking-metrics'); ?></strong> <?php _e('It is required to use a form that captures a telephone number (<code>input type="tel"</code>) in order for Gravity Forms to integrate properly with our FormReactor. For more information, see', 'call-tracking-metrics'); ?> <a href="https://www.calltrackingmetrics.com/support/integrations/wordpress" target="_blank" rel="noopener" class="underline text-blue-700"><?php _e('Using the CallTrackingMetrics WordPress Plugin', 'call-tracking-metrics'); ?></a>.</div>
                </div>

                <!-- Dismissible warning banner -->
                <?php if (!$gf_available): ?>
                    <div id="gf-notice" class="bg-yellow-50 border-l-2 border-yellow-400 text-yellow-800 p-1.5 rounded flex items-center justify-between gap-1 mb-3 mt-2 text-xs ml-6">
                        <div class="flex items-center justify-between w-full gap-1">
                            <span class="font-semibold"><?php _e('Gravity Forms is not installed or activated.', 'call-tracking-metrics'); ?></span>
                            <a href="<?= esc_url(admin_url('plugin-install.php?s=gravity+forms&tab=search&type=term')) ?>" target="_blank" rel="noopener" class="ml-1 bg-blue-600 hover:bg-blue-700 text-white px-2 py-1 rounded shadow transition text-xs font-medium hover:text-white! text-white!"><?php _e('Install Gravity Forms', 'call-tracking-metrics'); ?></a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="mt-8">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 !text-white px-6 py-2 rounded shadow font-semibold transition flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <?php _e('Save Settings', 'call-tracking-metrics'); ?>
            </button>
        </div>
    <?php endif; ?>
</form>

<script>
// Localize script data for general tab
var ctmGeneralData = Object.assign({}, window.ctmGeneralData || {}, {
    ajaxurl: '<?= admin_url('admin-ajax.php') ?>',
    dismissNonce: (window.ctmGeneralData && window.ctmGeneralData.dismissNonce) ? window.ctmGeneralData.dismissNonce : '<?= wp_create_nonce('ctm_dismiss_notice') ?>',
    generalNonce: (window.ctmGeneralData && window.ctmGeneralData.generalNonce) ? window.ctmGeneralData.generalNonce : '<?= wp_create_nonce('ctm_general_nonce') ?>',
    testNonce: '<?= wp_create_nonce('ctm_test_api_connection') ?>',
    apiBaseUrl: '<?= esc_js($apiBaseUrl); ?>'
});

function dismissNotice(type) {
    // Hide the notice immediately
    const noticeId = type + '-notice';
    const notice = document.getElementById(noticeId);
    if (notice) {
        notice.style.transition = 'opacity 0.3s ease';
        notice.style.opacity = '0';
        setTimeout(() => {
            notice.style.display = 'none';
        }, 300);
    }

    // Send AJAX request to save dismiss preference
    const formData = new FormData();
    formData.append('action', 'ctm_dismiss_notice');
    formData.append('notice_type', type);
    formData.append('nonce', ctmGeneralData.dismissNonce);

    fetch(ctmGeneralData.ajaxurl, {
        method: 'POST',
        body: formData
    }).catch(error => {
        console.error('Error dismissing notice:', error);
    });
}

function testApiConnection() {
    const apiKey = document.getElementById('ctm_api_key').value;
    const apiSecret = document.getElementById('ctm_api_secret').value;
    const apiEndpointSelect = document.getElementById('ctm_api_base_url');
    let apiBaseUrl = apiEndpointSelect ? apiEndpointSelect.value : (ctmGeneralData.apiBaseUrl || 'https://api.calltrackingmetrics.com');
    apiBaseUrl = apiBaseUrl.replace(/\/+$/, '');
    ctmGeneralData.apiBaseUrl = apiBaseUrl;
    const saveBtn = document.getElementById('save-api-btn');
    const apiTestLogs = document.getElementById('api-test-logs');
    const apiLogContent = document.getElementById('api-log-content');
    const testDuration = document.getElementById('test-duration');
    const testProgress = document.getElementById('test-progress');
    const progressBar = document.getElementById('progress-bar');
    const progressText = document.getElementById('progress-text');
    const accountSummary = document.getElementById('account-summary');
    const accountDetails = document.getElementById('account-details');
    const technicalDetails = document.getElementById('technical-details');
    const techDetailsContent = document.getElementById('tech-details-content');

    if (!apiKey || !apiSecret) {
        alert('Please enter both API Key and API Secret.');
        return false; // Prevent form submission
    }

    // Validate minimum length
    if (apiKey.length < 20 || apiSecret.length < 20) {
        alert('API Key and Secret must be at least 20 characters long.');
        return false; // Prevent form submission
    }

    // Initialize test state
    const startTime = Date.now();
    let currentStep = 0;
    const totalSteps = 5;

    saveBtn.disabled = true;
    saveBtn.textContent = 'Testing Connection...';
    apiTestLogs.classList.remove('hidden');
    apiLogContent.innerHTML = '';
    testDuration.classList.add('hidden');
    testProgress.classList.remove('hidden');
    accountSummary.classList.add('hidden');
    technicalDetails.classList.add('hidden');

    // Step 1: Validation
    updateProgress(++currentStep, totalSteps, 'Validating credentials...');
    appendLog('info', 'Starting API connection test...');
    appendLog('info', `API Key: ${apiKey.substring(0, 8)}...${apiKey.substring(apiKey.length - 4)}`);
    appendLog('info', `Target: ${apiBaseUrl}`);

    setTimeout(() => {
        // Step 2: Connection
        updateProgress(++currentStep, totalSteps, 'Establishing connection...');
        appendLog('info', 'Connecting to CTM API endpoint...');
        appendLog('info', 'Endpoint: /api/v1/accounts/current.json');

        const requestStart = Date.now();

        fetch(ctmGeneralData.ajaxurl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=ctm_test_api_connection&api_key=' + encodeURIComponent(apiKey) + '&api_secret=' + encodeURIComponent(apiSecret) + '&api_base_url=' + encodeURIComponent(apiBaseUrl) + '&nonce=' + ctmGeneralData.testNonce
        })
        .then(response => {
            const requestTime = Date.now() - requestStart;

            // Step 3: Response
            updateProgress(++currentStep, totalSteps, 'Processing response...');
            appendLog('info', `Response received in ${requestTime}ms`);
            appendLog('info', `HTTP Status: ${response.status} ${response.statusText}`);

            return response.json();
        })
        .then(data => {
            // Step 4: Analysis
            updateProgress(++currentStep, totalSteps, 'Analyzing account data...');

            const totalTime = Date.now() - startTime;
            testDuration.textContent = `${totalTime}ms`;
            testDuration.classList.remove('hidden');

            if (data.success) {
                // Step 5: Success
                updateProgress(++currentStep, totalSteps, 'Connection successful!');

                appendLog('success', '✓ API Connection Successful!');
                appendLog('success', `Total test duration: ${totalTime}ms`);

                // Show performance metrics
                if (data.performance) {
                    displayPerformanceMetrics(data.performance, data.connection_quality);
                }

                // Show detailed account information
                if (data.account_info && data.account_info.account) {
                    displayAccountSummary(data.account_info.account, data.account_details?.account, data.capabilities);
                }

                // Show technical details
                displayTechnicalDetails(data);

                appendLog('success', 'All tests completed successfully!');

                // Update button and allow form submission
                saveBtn.textContent = 'API Credentials Saved Successfully!';
                saveBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                saveBtn.classList.add('bg-green-600', 'hover:bg-green-700');

                // Submit the form after successful test
                setTimeout(() => {
                    const form = document.querySelector('form');
                    if (form) {
                        form.submit();
                    }
                }, 2000);

            } else {
                // Step 5: Error
                updateProgress(++currentStep, totalSteps, 'Connection failed!');

                appendLog('error', '✗ Failed to connect to CTM API: ' + data.message);
                appendLog('error', `Total test duration: ${totalTime}ms`);

                if (data.message) appendLog('error', `Error: ${data.message}`);
                if (data.details && Array.isArray(data.details)) {
                    data.details.forEach(detail => appendLog('warning', `• ${detail}`));
                }

                // Show technical details for debugging
                displayTechnicalDetails(data);

                appendLog('warning', 'Please check your API credentials and try again.');

                // Reset button
                saveBtn.disabled = false;
                saveBtn.textContent = 'Save API Credentials';

                return false; // Prevent form submission
            }
        })
        .catch(error => {
            const totalTime = Date.now() - startTime;
            testDuration.textContent = `${totalTime}ms`;
            testDuration.classList.remove('hidden');

            updateProgress(totalSteps, totalSteps, 'Network error');
            progressBar.classList.remove('bg-blue-600');
            progressBar.classList.add('bg-red-600');

            appendLog('error', '✗ Network Error: ' + error.message);
            appendLog('error', `Total test duration: ${totalTime}ms`);
            appendLog('warning', 'Please check your internet connection and try again.');

            // Reset button
            saveBtn.disabled = false;
            saveBtn.textContent = 'Save API Credentials';

            return false; // Prevent form submission
        });
    }, 800); // Small delay to show initial progress
}

function updateProgress(step, total, message) {
    const progressBar = document.getElementById('progress-bar');
    const progressText = document.getElementById('progress-text');

    const percentage = Math.round((step / total) * 100);
    progressBar.style.width = percentage + '%';
    progressText.textContent = `${percentage}% - ${message}`;
}

function appendLog(type, message) {
    const apiLogContent = document.getElementById('api-log-content');
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

    const logEntry = document.createElement('div');
    logEntry.className = `${colorClass} ${bgClass} p-2 rounded border-l-2 border-current flex gap-2 items-start`;
    logEntry.innerHTML = `
        <span class="text-gray-400 text-xs font-mono mt-0.5">[${timestamp}]</span>
        <span class="font-bold mt-0.5">${icon}</span>
        <span class="flex-1 font-medium">${message}</span>
    `;

    apiLogContent.appendChild(logEntry);
    apiLogContent.scrollTop = apiLogContent.scrollHeight;
}

function displayPerformanceMetrics(performance, quality) {
    if (quality) {
        const qualityColor = quality.color === 'green' ? 'text-green-600' :
                            quality.color === 'blue' ? 'text-blue-600' :
                            quality.color === 'yellow' ? 'text-yellow-600' : 'text-red-600';

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

function displayAccountSummary(account, details, capabilities) {
    const accountSummary = document.getElementById('account-summary');
    const accountDetails = document.getElementById('account-details');

    accountSummary.classList.remove('hidden');

    let summaryHTML = '';

    // Basic account info
    summaryHTML += `<div class="bg-white p-2 rounded border"><strong>Name:</strong> ${account.name || 'N/A'}</div>`;
    summaryHTML += `<div class="bg-white p-2 rounded border"><strong>Account ID:</strong> <code class="bg-gray-100 px-1 rounded">${account.id || 'N/A'}</code></div>`;

    if (account.email) {
        summaryHTML += `<div class="bg-white p-2 rounded border"><strong>Email:</strong> ${account.email}</div>`;
    }

    // API Capabilities
    if (capabilities) {
        const accessIcon = capabilities.account_access ? '✓' : '✗';
        const detailsIcon = capabilities.details_access ? '✓' : '✗';
        summaryHTML += `<div class="bg-white p-2 rounded border"><strong>API Access:</strong> <span class="text-green-600">${accessIcon}</span> Account, <span class="${capabilities.details_access ? 'text-green-600' : 'text-yellow-600'}">${detailsIcon}</span> Details</div>`;
        summaryHTML += `<div class="bg-white p-2 rounded border"><strong>API Version:</strong> ${capabilities.api_version}</div>`;
    }

    // Additional details if available
    if (details) {
        if (details.status) {
            const statusColor = details.status === 'active' ? 'text-green-600' : 'text-yellow-600';
            summaryHTML += `<div class="bg-white p-2 rounded border"><strong>Status:</strong> <span class="${statusColor} font-semibold">${details.status}</span></div>`;
        }
        if (details.timezone) {
            summaryHTML += `<div class="bg-white p-2 rounded border"><strong>Timezone:</strong> ${details.timezone}</div>`;
        }
        if (details.created_at) {
            const date = new Date(details.created_at).toLocaleDateString();
            summaryHTML += `<div class="bg-white p-2 rounded border"><strong>Created:</strong> ${date}</div>`;
        }
        if (details.phone) {
            summaryHTML += `<div class="bg-white p-2 rounded border"><strong>Phone:</strong> ${details.phone}</div>`;
        }
        if (details.website) {
            summaryHTML += `<div class="bg-white p-2 rounded border"><strong>Website:</strong> <a href="${details.website}" target="_blank" class="text-blue-600 underline">${details.website}</a></div>`;
        }
        if (details.plan || details.subscription) {
            summaryHTML += `<div class="bg-white p-2 rounded border"><strong>Plan:</strong> ${details.plan || details.subscription || 'Not specified'}</div>`;
        }
    }

    accountDetails.innerHTML = summaryHTML;

    appendLog('success', `Account verified: ${account.name} (${account.id})`);
}

function displayTechnicalDetails(data) {
    const technicalDetails = document.getElementById('technical-details');
    const techDetailsContent = document.getElementById('tech-details-content');

    technicalDetails.classList.remove('hidden');

    let detailsHTML = '';

    // Metadata Section
    if (data.metadata) {
        detailsHTML += `
            <div class="mb-4">
                <h6 class="font-semibold text-gray-700 mb-2 flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Request Metadata
                </h6>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div><strong>Request ID:</strong> <code class="bg-gray-200 px-1 rounded">${data.metadata.request_id?.substring(0, 8) || 'N/A'}</code></div>
                    <div><strong>Timestamp:</strong> ${data.metadata.timestamp || 'N/A'}</div>
                    <div><strong>WordPress:</strong> ${data.metadata.wordpress_version || 'N/A'}</div>
                    <div><strong>PHP:</strong> ${data.metadata.php_version || 'N/A'}</div>
                    <div><strong>Plugin:</strong> ${data.metadata.plugin_version || 'N/A'}</div>
                    <div><strong>Auth Method:</strong> ${data.metadata.auth_method || 'N/A'}</div>
                </div>
            </div>
        `;
    }

    // Performance Section
    if (data.performance) {
        detailsHTML += `
            <div class="mb-4">
                <h6 class="font-semibold text-gray-700 mb-2 flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    Performance Metrics
                </h6>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div><strong>Total Time:</strong> ${data.performance.total_execution_time}ms</div>
                    <div><strong>API Response:</strong> ${data.performance.api_response_time}ms</div>
                    <div><strong>Details Response:</strong> ${data.performance.details_response_time || 'N/A'}ms</div>
                    <div><strong>Network Overhead:</strong> ${data.performance.network_overhead?.toFixed(1) || 'N/A'}ms</div>
                </div>
            </div>
        `;
    }

    // API Endpoints Section
    if (data.metadata) {
        detailsHTML += `
            <div class="mb-4">
                <h6 class="font-semibold text-gray-700 mb-2 flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                    </svg>
                    API Endpoints
                </h6>
                <div class="space-y-1 text-xs">
                    <div><strong>Base URL:</strong> <code class="bg-gray-200 px-1 rounded">${data.metadata.api_endpoint || 'N/A'}</code></div>
                    <div><strong>Account:</strong> <code class="bg-gray-200 px-1 rounded">${data.metadata.account_endpoint || 'N/A'}</code></div>
                    ${data.metadata.details_endpoint ? `<div><strong>Details:</strong> <code class="bg-gray-200 px-1 rounded">${data.metadata.details_endpoint}</code></div>` : ''}
                </div>
            </div>
        `;
    }

    // Connection Quality
    if (data.connection_quality) {
        const quality = data.connection_quality;
        const qualityBadgeColor = quality.color === 'green' ? 'bg-green-100 text-green-800' :
                                  quality.color === 'blue' ? 'bg-blue-100 text-blue-800' :
                                  quality.color === 'yellow' ? 'bg-yellow-100 text-yellow-800' :
                                  'bg-red-100 text-red-800';
        detailsHTML += `
            <div class="mb-4">
                <h6 class="font-semibold text-gray-700 mb-2 flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    Connection Quality
                </h6>
                <div class="flex items-center gap-2 text-xs">
                    <span class="px-2 py-1 rounded ${qualityBadgeColor} font-semibold">${quality.rating.toUpperCase()}</span>
                    <span class="text-gray-600">${Number(quality.total_time).toFixed(2)}ms total response time</span>
                </div>
            </div>
        `;
    }

    // Raw Response Data (collapsible)
    const sanitizedData = JSON.parse(JSON.stringify(data));

    // Remove sensitive information
    if (sanitizedData.account_info?.account) {
        delete sanitizedData.account_info.account.api_key;
        delete sanitizedData.account_info.account.api_secret;
    }

    const jsonString = JSON.stringify(sanitizedData, null, 2);

    detailsHTML += `
        <div class="mb-2">
            <button type="button" onclick="toggleRawData()" class="flex items-center gap-1 text-xs text-gray-600 hover:text-gray-800 transition">
                <svg id="raw-data-icon" class="w-3 h-3 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
                Raw API Response (JSON)
            </button>
            <div id="raw-data-content" class="hidden mt-2 p-2 bg-gray-50 rounded border">
                <div class="mb-1 text-gray-600 text-xs"><strong>Complete API Response:</strong> (sensitive data removed)</div>
                <pre class="whitespace-pre-wrap text-xs overflow-x-auto">${escapeHtml(jsonString)}</pre>
            </div>
        </div>
    `;

    techDetailsContent.innerHTML = detailsHTML;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function clearTestLogs() {
    document.getElementById('api-log-content').innerHTML = '';
    document.getElementById('test-progress').classList.add('hidden');
    document.getElementById('account-summary').classList.add('hidden');
    document.getElementById('technical-details').classList.add('hidden');
    document.getElementById('test-duration').classList.add('hidden');

    // Reset progress bar
    const progressBar = document.getElementById('progress-bar');
    progressBar.style.width = '0%';
    progressBar.classList.remove('bg-red-600');
    progressBar.classList.add('bg-blue-600');
}

function toggleTechnicalDetails() {
    const techDetailsContent = document.getElementById('tech-details-content');
    const techDetailsIcon = document.getElementById('tech-details-icon');

    techDetailsContent.classList.toggle('hidden');
    techDetailsIcon.classList.toggle('rotate-90');
}

function toggleRawData() {
    const rawDataContent = document.getElementById('raw-data-content');
    const rawDataIcon = document.getElementById('raw-data-icon');

    rawDataContent.classList.toggle('hidden');
    rawDataIcon.classList.toggle('rotate-90');
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Override checkbox logic for readOnly
    const overrideCheckbox = document.getElementById('ctm_tracking_override_checkbox');
    const trackingTextarea = document.getElementById('ctm_tracking_script');
    if (overrideCheckbox && trackingTextarea) {
        overrideCheckbox.addEventListener('change', function() {
            trackingTextarea.readOnly = !this.checked;
        });
    }

    const apiEndpointSelect = document.getElementById('ctm_api_base_url');
    if (apiEndpointSelect) {
        ctmGeneralData.apiBaseUrl = apiEndpointSelect.value.replace(/\/+$/, '');
        apiEndpointSelect.addEventListener('change', function() {
            ctmGeneralData.apiBaseUrl = this.value.replace(/\/+$/, '');
        });
    }

    // Form submission handler for API credentials - only when API is not connected
    const apiForm = document.querySelector('form');
    const saveBtn = document.getElementById('save-api-btn');
    const apiTestLogs = document.getElementById('api-test-logs');

    // Only prevent form submission if we're on the API credentials form (not connected state)
    if (apiForm && saveBtn && apiTestLogs) {
        console.log('API credentials form detected, preventing normal submission');
        apiForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent default form submission

            // Run API test instead
            testApiConnection();
        });
    } else {
        console.log('Normal form submission allowed (API connected or other form)');
    }

    // Countdown timer for API test
    let countdownSeconds = 600; // 10 minutes
    const countdownDisplay = document.getElementById('api-test-countdown');
    function updateCountdownDisplay() {
        if (countdownDisplay) {
            const mins = Math.floor(countdownSeconds / 60);
            const secs = countdownSeconds % 60;
            countdownDisplay.textContent = `${mins}:${secs.toString().padStart(2, '0')}`;
        }
    }
    updateCountdownDisplay();
    });

    // Helper function to show inline messages
    function showTrackingMessage(message, type = 'error') {
        const messageEl = document.getElementById('tracking-script-message');
        if (!messageEl) return;

        messageEl.classList.remove('hidden', 'bg-red-50', 'border-red-200', 'text-red-800', 'bg-green-50', 'border-green-200', 'text-green-800', 'bg-blue-50', 'border-blue-200', 'text-blue-800');

        if (type === 'error') {
            messageEl.classList.add('bg-red-50', 'border', 'border-red-200', 'text-red-800');
        } else if (type === 'success') {
            messageEl.classList.add('bg-green-50', 'border', 'border-green-200', 'text-green-800');
        } else {
            messageEl.classList.add('bg-blue-50', 'border', 'border-blue-200', 'text-blue-800');
        }

        messageEl.textContent = message;
    }

    // Helper function to extract error message from response
    function extractErrorMessage(data) {
        if (data.data) {
            if (typeof data.data === 'string') {
                return data.data;
            } else if (data.data.message) {
                return data.data.message;
            } else if (data.message) {
                return data.message;
            }
        } else if (data.message) {
            return data.message;
        }
        return 'Unknown error - check console for details';
    }

    // Function to fetch tracking script
    function fetchTrackingScriptNow() {
        const button = event.target;
        const originalText = button.textContent;
        button.textContent = 'Fetching...';
        button.disabled = true;

        showTrackingMessage('Fetching tracking script from API...', 'info');

        // Make AJAX call to fetch tracking script
        fetch(ajaxurl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=ctm_fetch_tracking_script&nonce=' + ctmGeneralData.generalNonce
        })
        .then(response => response.json())
        .then(data => {
            console.log('API Response:', data);
            if (data.success) {
                const trackingTextarea = document.getElementById('ctm_tracking_script');
                const overrideCheckbox = document.getElementById('ctm_tracking_override_checkbox');

                // Update textarea
                trackingTextarea.value = data.data.script;

                // Uncheck override and set readonly
                if (overrideCheckbox) {
                    overrideCheckbox.checked = false;
                    trackingTextarea.readOnly = true;
                }

                showTrackingMessage('Success! Tracking script fetched. Click Save Settings to apply.', 'success');
            } else {
                let errorMsg = extractErrorMessage(data);
                console.error('Error details:', data);
                showTrackingMessage('Failed to fetch: ' + errorMsg, 'error');
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            showTrackingMessage('Network error: ' + error.message, 'error');
        })
        .finally(() => {
            button.textContent = originalText;
            button.disabled = false;
        });
    }

    // Function to test duplicate prevention
    function testDuplicatePrevention() {
        const button = event.target;
        const originalText = button.textContent;
        button.textContent = 'Testing...';
        button.disabled = true;

        const resultDiv = document.getElementById('duplicate-prevention-test-result');
        resultDiv.innerHTML = 'Testing duplicate prevention...';

        // Make AJAX call to test duplicate prevention
        fetch(ajaxurl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=ctm_test_duplicate_prevention&nonce=' + ctmGeneralData.generalNonce
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const result = data.data;
                let html = '<div class="mt-2 p-2 bg-white border rounded">';
                html += '<strong>Test Results:</strong><br>';
                html += `First submission: ${result.first_submission ? 'Blocked (duplicate)' : 'Allowed'}<br>`;
                html += `Second submission: ${result.second_submission ? 'Blocked (duplicate)' : 'Allowed'}<br>`;
                html += `Duplicate prevention working: ${result.duplicate_prevention_working ? '✓ YES' : '✗ NO'}<br>`;
                html += `Session ID: ${result.session_id}<br>`;
                html += `Form ID: ${result.form_id}<br>`;
                html += `Settings: ${JSON.stringify(result.settings)}`;
                html += '</div>';
                resultDiv.innerHTML = html;
            } else {
                resultDiv.innerHTML = '<div class="mt-2 p-2 bg-red-100 border border-red-300 rounded text-red-700">Test failed: ' + (data.data || 'Unknown error') + '</div>';
            }
        })
        .catch(error => {
            resultDiv.innerHTML = '<div class="mt-2 p-2 bg-red-100 border border-red-300 rounded text-red-700">Error: ' + error.message + '</div>';
        })
        .finally(() => {
            // Reset button state
            button.textContent = originalText;
            button.disabled = false;
        });
    }
</script>
