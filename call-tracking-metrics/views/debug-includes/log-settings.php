<?php
/**
 * Log Management Component
 * Combined log settings and daily logs display
 */

// Get current settings
$retention_days = (int) get_option('ctm_log_retention_days', 7);
$auto_cleanup = get_option('ctm_log_auto_cleanup', true);

// Ensure variables are available from parent context
$available_dates = $available_dates ?? [];
$log_stats = $log_stats ?? [];
?>

<div class="bg-white rounded-lg shadow border border-gray-200">
    <div class="flex items-center justify-between p-3 cursor-pointer hover:bg-gray-50 transition-colors" onclick="togglePanel('log-settings')">
        <div class="flex items-center gap-2">
            <svg id="log-settings-icon" class="w-4 h-4 text-orange-600 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="transform: rotate(180deg);">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
            <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"/>
            </svg>
            <h3 class="text-base font-semibold text-gray-900"><?php _e('Log Management', 'call-tracking-metrics'); ?></h3>
        </div>
    </div>
    
    <div id="log-settings-content" class="border-t border-gray-200 p-4">
        <!-- Log Settings Section -->
        <div class="mb-4">
            <h4 class="text-sm font-semibold text-gray-800 mb-3 flex items-center">
                <svg class="w-4 h-4 text-blue-600 mr-2 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <?php _e('Settings', 'call-tracking-metrics'); ?>
            </h4>
            
            <form id="log-settings-form" class="space-y-2">
                <div class="flex items-center gap-3">
                    <label for="log_retention_days" class="text-sm text-gray-700"><?php _e('Retention (Days)', 'call-tracking-metrics'); ?></label>
                    <input type="number" id="log_retention_days" name="log_retention_days" min="1" max="365" value="<?= $retention_days ?>" class="w-20 px-2 py-1 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500 text-sm">
                    <input type="checkbox" id="log_auto_cleanup" name="log_auto_cleanup" <?= $auto_cleanup ? 'checked' : '' ?> class="ml-4 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="log_auto_cleanup" class="ml-1 text-sm text-gray-900"><?php _e('Auto-cleanup logs', 'call-tracking-metrics'); ?></label>
                </div>
                <div class="flex gap-2 pt-2 border-t border-gray-200 mt-2">
                    <button type="button" onclick="updateLogSettings()" id="update-log-settings-btn" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-3 py-1.5 rounded flex items-center gap-1 text-sm">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <?php _e('Update', 'call-tracking-metrics'); ?>
                    </button>
                    <button type="button" onclick="clearAllLogs()" id="clear-all-logs-btn" class="bg-red-600 hover:bg-red-700 text-white font-medium px-3 py-1.5 rounded flex items-center gap-1 text-sm">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        <?php _e('Clear All', 'call-tracking-metrics'); ?>
                    </button>
                </div>
            </form>
        </div>

        <!-- Daily Logs Section -->
        <div>
            <h4 class="text-sm font-semibold text-gray-800 mb-3 flex items-center">
                <svg class="w-4 h-4 text-blue-600 mr-2 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <?php _e('Daily Logs', 'call-tracking-metrics'); ?>
            </h4>

            <?php if (empty($available_dates)): ?>
                <div class="text-center py-6 bg-gray-50 rounded-lg">
                    <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <h3 class="mt-3 text-base font-medium text-gray-900"><?php _e('No debug logs found', 'call-tracking-metrics'); ?></h3>
                    <p class="mt-1 text-sm text-gray-500"><?php _e('Enable debug mode to start logging plugin activity.', 'call-tracking-metrics'); ?></p>
                </div>
            <?php else: ?>
                <div class="space-y-3" id="logs-container">
                    <?php foreach ($available_dates as $date): ?>
                        <?php
                        // Use the new database system to get grouped logs
                        $grouped_logs = [];
                        $logs = [];
                        if (isset($loggingSystem) && $loggingSystem) {
                            $grouped_logs = $loggingSystem->getGroupedLogsForDate($date);
                            $logs = $loggingSystem->getLogsForDate($date);
                        }
                        if (empty($logs)) continue;
                        
                        // Calculate statistics from grouped logs
                        $error_count = 0;
                        $warning_count = 0;
                        $info_count = 0;
                        $debug_count = 0;
                        $api_count = 0;
                        $total_size = 0;
                        
                        foreach ($grouped_logs as $group) {
                            switch ($group['type']) {
                                case 'error':
                                    $error_count += $group['count'];
                                    break;
                                case 'warning':
                                    $warning_count += $group['count'];
                                    break;
                                case 'info':
                                    $info_count += $group['count'];
                                    break;
                                case 'debug':
                                    $debug_count += $group['count'];
                                    break;
                                case 'api':
                                    $api_count += $group['count'];
                                    break;
                            }
                        }
                        
                        // Calculate total size (approximate)
                        $total_size = strlen(json_encode($logs));
                        
                        // Show total count
                        $total_count = count($logs);
                        $count_display = $total_count;
                        
                        // Format size for display
                        $size_display = function_exists('size_format') ? size_format($total_size) : round($total_size / 1024, 1) . ' KB';
                        ?>
                        <div class="bg-gradient-to-r from-white to-gray-50 border border-gray-200 rounded-lg p-3 hover:shadow-md transition-all duration-200 hover:border-blue-200">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center space-x-3">
                                    <!-- Date with modern styling -->
                                    <div class="flex items-center">
                                        <div class="bg-blue-50 p-1.5 rounded mr-2">
                                            <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                        <h5 class="text-base font-bold text-gray-900"><?= esc_html($date) ?></h5>
                                    </div>
                                    
                                    <!-- Modern badge design -->
                                    <div class="flex flex-wrap gap-1">
                                        <?php if ($error_count > 0): ?>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700 border border-red-200">
                                                <span class="w-2 h-2 bg-red-500 rounded-full mr-1"></span>
                                                <?= $error_count ?> error<?= $error_count > 1 ? 's' : '' ?>
                                            </span>
                                        <?php endif; ?>
                                        
                                        <?php if ($warning_count > 0): ?>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700 border border-yellow-200">
                                                <span class="w-2 h-2 bg-yellow-500 rounded-full mr-1"></span>
                                                <?= $warning_count ?> warning<?= $warning_count > 1 ? 's' : '' ?>
                                            </span>
                                        <?php endif; ?>
                                        
                                        <?php if ($api_count > 0): ?>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-700 border border-purple-200">
                                                <span class="w-2 h-2 bg-purple-500 rounded-full mr-1"></span>
                                                <?= $api_count ?> API call<?= $api_count > 1 ? 's' : '' ?>
                                            </span>
                                        <?php endif; ?>
                                        
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-200">
                                            <span class="w-2 h-2 bg-gray-500 rounded-full mr-1"></span>
                                            <?= $count_display ?> total
                                        </span>
                                        
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 border border-blue-200">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                                            </svg>
                                            <?= $size_display ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <!-- Action buttons -->
                                <div class="flex gap-1">
                                    <button class="ctm-toggle-log-view bg-blue-600 hover:bg-blue-700 text-white px-2 py-1 rounded text-xs transition flex items-center gap-1" data-date="<?= esc_attr($date) ?>">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <?php _e('View', 'call-tracking-metrics'); ?>
                                    </button>
                                    <button onclick="clearDebugLogs('debug_single', '<?= esc_js($date) ?>')" class="ctm-clear-log bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded text-xs transition flex items-center gap-1" data-date="<?= esc_attr($date) ?>">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        <?php _e('Clear', 'call-tracking-metrics'); ?>
                                    </button>
                                </div>
                            </div>

                            <!-- Log entries (hidden by default) -->
                            <div id="log-<?= esc_attr($date) ?>" class="hidden mt-3 space-y-2">
                                <?php foreach ($grouped_logs as $group): ?>
                                    <?php
                                    $group_key = $group['key'];
                                    $type = $group['type'];
                                    $count = $group['count'];
                                    $first_seen = $group['first_seen'];
                                    $last_seen = $group['last_seen'];
                                    $examples = $group['examples'];
                                    
                                    // Get type-specific styling
                                    $type_colors = [
                                        'error' => 'text-red-800 bg-red-50 border-red-200',
                                        'warning' => 'text-yellow-800 bg-yellow-50 border-yellow-200',
                                        'info' => 'text-blue-800 bg-blue-50 border-blue-200',
                                        'debug' => 'text-gray-800 bg-gray-50 border-gray-200',
                                        'api' => 'text-purple-800 bg-purple-50 border-purple-200',
                                        'config' => 'text-indigo-800 bg-indigo-50 border-indigo-200',
                                        'system' => 'text-green-800 bg-green-50 border-green-200'
                                    ];
                                    $color_class = $type_colors[$type] ?? 'text-gray-800 bg-gray-50 border-gray-200';
                                    ?>
                                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                                        <!-- Group header -->
                                        <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-3">
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold <?= $color_class ?>">
                                                        <?= strtoupper($type) ?>
                                                    </span>
                                                    <span class="text-sm font-medium text-gray-700"><?= $count ?> calls</span>
                                                    <span class="text-xs text-gray-500">
                                                        <?php
                                                        $first_date = date('M j, Y g:i A', strtotime($first_seen));
                                                        $last_date = date('M j, Y g:i A', strtotime($last_seen));
                                                        if ($first_date === $last_date) {
                                                            echo $first_date;
                                                        } else {
                                                            echo $first_date . ' - ' . $last_date;
                                                        }
                                                        ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Example entries -->
                                        <div class="space-y-2 p-4" id="group-<?= esc_attr($group_key) ?>-entries">
                                            <?php foreach ($group['examples'] as $example): ?>
                                                <div class="bg-gray-50 rounded p-3 border-l-4 border-gray-300">
                                                    <div class="flex items-center justify-between mb-1">
                                                        <span class="text-xs text-gray-500 font-mono"><?= esc_html($example['timestamp']) ?></span>
                                                    </div>
                                                    <p class="text-gray-700 text-sm"><?= esc_html($example['message']) ?></p>
                                                    <?php if (!empty($example['context'])): ?>
                                                        <details class="mt-2">
                                                            <summary class="text-xs text-gray-500 cursor-pointer hover:text-gray-700 font-medium flex items-center">
                                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                                </svg>
                                                                Context Details
                                                            </summary>
                                                            <pre class="mt-2 text-xs text-gray-600 bg-gray-100 p-2 rounded overflow-x-auto border"><?= esc_html(print_r($example['context'], true)) ?></pre>
                                                        </details>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        
                                        <?php if ($group['count'] > count($group['examples'])): ?>
                                            <div class="mt-3 text-center p-4 border-t border-gray-200">
                                                <button class="ctm-load-more-group-entries bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm transition-colors duration-200" 
                                                        data-group-key="<?= esc_attr($group_key) ?>" 
                                                        data-date="<?= esc_attr($date) ?>" 
                                                        data-current-count="<?= count($group['examples']) ?>" 
                                                        data-total-count="<?= $group['count'] ?>">
                                                    <?php _e('Load More Entries', 'call-tracking-metrics'); ?>
                                                </button>
                                                <p class="text-xs text-gray-500 mt-1">
                                                    <?php printf(__('Showing %d of %d entries', 'call-tracking-metrics'), count($group['examples']), $group['count']); ?>
                                                </p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function updateLogSettings() {
        const button = document.getElementById('update-log-settings-btn');
        const originalText = button.textContent;
        
        button.disabled = true;
        button.textContent = '<?php _e('Updating...', 'call-tracking-metrics'); ?>';
        
        const formData = new FormData();
        formData.append('action', 'ctm_update_log_settings');
        formData.append('log_retention_days', document.getElementById('log_retention_days').value);
        formData.append('log_auto_cleanup', document.getElementById('log_auto_cleanup').checked ? '1' : '0');
        formData.append('nonce', '<?= wp_create_nonce('ctm_update_log_settings') ?>');
        
        fetch('<?= admin_url('admin-ajax.php') ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data && data.success) {
                ctmShowToast('Settings updated successfully', 'success');
            } else {
                ctmShowToast('Failed to update settings', 'error');
            }
        })
        .catch(error => {
            console.error('Error updating log settings:', error);
            ctmShowToast('Network error occurred', 'error');
        })
        .finally(() => {
            button.disabled = false;
            button.textContent = originalText;
        });
    }

    function clearAllLogs() {
        if (!confirm('<?php _e('Are you sure you want to clear all logs? This action cannot be undone.', 'call-tracking-metrics'); ?>')) {
            return;
        }
        
        const button = document.getElementById('clear-all-logs-btn');
        const originalText = button.textContent;
        
        button.disabled = true;
        button.textContent = '<?php _e('Clearing...', 'call-tracking-metrics'); ?>';
        
        const formData = new FormData();
        formData.append('action', 'ctm_clear_all_logs');
        formData.append('nonce', '<?= wp_create_nonce('ctm_clear_all_logs') ?>');
        
        fetch('<?= admin_url('admin-ajax.php') ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                ctmShowToast(data.data.message, 'success');
                // Reload the page to reflect changes
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                ctmShowToast(data.data.message || 'Failed to clear logs', 'error');
            }
        })
        .catch(error => {
            console.error('Error clearing logs:', error);
            ctmShowToast('Network error occurred while clearing logs', 'error');
        })
        .finally(() => {
            button.disabled = false;
            button.textContent = originalText;
        });
    }

    function toggleLogView(date) {
        const logDiv = document.getElementById('log-' + date);
        
        if (logDiv) {
            if (logDiv.classList.contains('hidden')) {
                logDiv.classList.remove('hidden');
                // Update button text to indicate it's now open
                const button = document.querySelector(`button.ctm-toggle-log-view[data-date="${date}"]`);
                if (button) {
                    button.innerHTML = `
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <?php _e('Hide', 'call-tracking-metrics'); ?>
                    `;
                }
            } else {
                logDiv.classList.add('hidden');
                // Update button text to indicate it's now closed
                const button = document.querySelector(`button.ctm-toggle-log-view[data-date="${date}"]`);
                if (button) {
                    button.innerHTML = `
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <?php _e('View', 'call-tracking-metrics'); ?>
                    `;
                }
            }
        } else {
            console.error('Log div not found for date:', date);
        }
    }

    function clearDebugLogs(logType, logDate) {
        const button = event.target;
        const originalText = button.textContent;
        
        const confirmMessage = logType === 'debug_all' 
            ? 'Are you sure you want to clear all debug logs? This action cannot be undone.'
            : `Are you sure you want to clear the debug log for ${logDate}? This action cannot be undone.`;
        
        if (!confirm(confirmMessage)) {
            return;
        }
        
        // Disable button and show loading state
        button.disabled = true;
        button.textContent = 'Clearing...';
        
        // Prepare form data
        const formData = new FormData();
        if (logType === 'debug_all') {
            formData.append('action', 'ctm_clear_all_logs');
            formData.append('nonce', '<?= wp_create_nonce('ctm_clear_all_logs') ?>');
        } else {
            // For single day clearing, we need to use a different approach
            // Since there's no AJAX handler, we'll submit a regular form
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = window.location.href;
            
            const clearInput = document.createElement('input');
            clearInput.type = 'hidden';
            clearInput.name = 'clear_single_log';
            clearInput.value = '1';
            form.appendChild(clearInput);
            
            const dateInput = document.createElement('input');
            dateInput.type = 'hidden';
            dateInput.name = 'log_date';
            dateInput.value = logDate;
            form.appendChild(dateInput);
            
            const nonceInput = document.createElement('input');
            nonceInput.type = 'hidden';
            nonceInput.name = '_wpnonce';
            nonceInput.value = '<?= wp_create_nonce('ctm_admin_action') ?>';
            form.appendChild(nonceInput);
            
            document.body.appendChild(form);
            form.submit();
            return;
        }
        
        // Send AJAX request
        fetch('<?= admin_url('admin-ajax.php') ?>', {
            method: 'POST',
            body: formData
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                ctmShowToast(data.data.message, 'success');
                
                // Remove the specific log container
                const logContainer = button.closest('.bg-gradient-to-r.from-white.to-gray-50.border.border-gray-200.rounded-xl.p-6.hover\\:shadow-lg.transition-all.duration-200.hover\\:border-blue-200');
                if (logContainer) {
                    logContainer.style.transition = 'opacity 0.5s ease';
                    logContainer.style.opacity = '0';
                    setTimeout(function() {
                        logContainer.remove();
                        
                        // Check if there are any remaining logs
                        const remainingLogs = document.querySelectorAll('.bg-gradient-to-r.from-white.to-gray-50.border.border-gray-200.rounded-xl.p-6.hover\\:shadow-lg.transition-all.duration-200.hover\\:border-blue-200');
                        if (remainingLogs.length === 0) {
                            // Show "no logs" message
                            const container = document.querySelector('.space-y-4');
                            if (container) {
                                container.innerHTML = `
                                    <div class="text-center py-12">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <h3 class="mt-4 text-lg font-medium text-gray-900"><?php _e('No debug logs found', 'call-tracking-metrics'); ?></h3>
                                        <p class="mt-2 text-gray-500"><?php _e('Enable debug mode to start logging plugin activity.', 'call-tracking-metrics'); ?></p>
                                    </div>
                                `;
                            }
                        }
                    }, 500);
                }
            } else {
                ctmShowToast(data.data.message || 'Failed to clear logs', 'error');
            }
        })
        .catch(function() {
            ctmShowToast('Network error occurred while clearing logs', 'error');
        })
        .finally(function() {
            // Re-enable button if it still exists
            if (button && button.parentNode) {
                button.disabled = false;
                button.textContent = originalText;
            }
        });
    }

    // Event delegation for dynamically created elements
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('ctm-toggle-log-view')) {
            const date = e.target.dataset.date;
            toggleLogView(date);
        }
        
        if (e.target.classList.contains('ctm-clear-log')) {
            const date = e.target.dataset.date;
            clearDebugLogs('debug_single', date);
        }
        
        if (e.target.classList.contains('ctm-load-more-group-entries')) {
            const groupKey = e.target.dataset.groupKey;
            const date = e.target.dataset.date;
            const currentCount = parseInt(e.target.dataset.currentCount) || 0;
            const totalCount = parseInt(e.target.dataset.totalCount) || 0;
            loadMoreGroupEntries(groupKey, date, currentCount, totalCount);
        }
    });

    function loadMoreGroupEntries(groupKey, date, currentCount, totalCount) {
        // Find the load more button and replace it with loading state
        const loadMoreButton = document.querySelector(`[data-group-key="${groupKey}"][data-date="${date}"]`);
        const loadMoreContainer = loadMoreButton.closest('.text-center');
        const originalContent = loadMoreContainer.innerHTML;
        
        // Show loading state
        loadMoreContainer.innerHTML = `
            <div class="flex items-center justify-center space-x-3 py-4">
                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                <div class="text-blue-600 text-sm font-medium"><?php _e('Loading more entries...', 'call-tracking-metrics'); ?></div>
            </div>
        `;
        
        // AJAX call to load more entries for a specific group
        const formData = new FormData();
        formData.append('action', 'ctm_load_more_group_entries');
        formData.append('nonce', '<?= wp_create_nonce('ctm_load_more_group_entries') ?>');
        formData.append('date', date);
        formData.append('group_key', groupKey);
        formData.append('offset', currentCount);
        formData.append('limit', 10); // Load 10 more entries at a time
        
        fetch('<?= admin_url('admin-ajax.php') ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Add new entries to the group
                const entriesContainer = document.getElementById(`group-${groupKey}-entries`);
                if (entriesContainer && data.data.entries) {
                    data.data.entries.forEach(entry => {
                        const entryHtml = createLogEntryHtml(entry);
                        entriesContainer.insertAdjacentHTML('beforeend', entryHtml);
                    });
                }
                
                // Update the load more button or remove it if all entries are loaded
                const newCount = currentCount + (data.data.entries ? data.data.entries.length : 0);
                if (newCount >= totalCount) {
                    // All entries loaded, remove the load more button
                    loadMoreContainer.innerHTML = `
                        <p class="text-xs text-gray-500 mt-1">
                            <?php _e('All entries loaded', 'call-tracking-metrics'); ?>
                        </p>
                    `;
                } else {
                    // Update button with new count
                    loadMoreContainer.innerHTML = `
                        <button class="ctm-load-more-group-entries bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm transition-colors duration-200" 
                                data-group-key="${groupKey}" 
                                data-date="${date}" 
                                data-current-count="${newCount}" 
                                data-total-count="${totalCount}">
                            <?php _e('Load More Entries', 'call-tracking-metrics'); ?>
                        </button>
                        <p class="text-xs text-gray-500 mt-1">
                            <?php printf(__('Showing %d of %d entries', 'call-tracking-metrics'), '${newCount}', '${totalCount}'); ?>
                        </p>
                    `;
                }
                
                ctmShowToast('More entries loaded successfully', 'success');
            } else {
                ctmShowToast(data.data.message || 'Failed to load more entries', 'error');
                // Restore original button
                loadMoreContainer.innerHTML = originalContent;
            }
        })
        .catch(error => {
            console.error('Error loading more group entries:', error);
            ctmShowToast('Network error occurred while loading entries', 'error');
            // Restore original button
            loadMoreContainer.innerHTML = originalContent;
        });
    }



    function createLogEntryHtml(entry) {
        const typeColors = {
            'error': 'text-red-800 bg-red-50 border-red-200',
            'warning': 'text-yellow-800 bg-yellow-50 border-yellow-200',
            'info': 'text-blue-800 bg-blue-50 border-blue-200',
            'debug': 'text-gray-800 bg-gray-50 border-gray-200',
            'api': 'text-purple-800 bg-purple-50 border-purple-200',
            'config': 'text-indigo-800 bg-indigo-50 border-indigo-200',
            'system': 'text-green-800 bg-green-50 border-green-200'
        };
        
        const colorClass = typeColors[entry.type] || 'text-gray-800 bg-gray-50 border-gray-200';
        
        let contextHtml = '';
        if (entry.context && Object.keys(entry.context).length > 0) {
            contextHtml = `
                <details class="mt-2">
                    <summary class="text-xs text-gray-500 cursor-pointer hover:text-gray-700 font-medium flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                        Context Details
                    </summary>
                    <pre class="mt-2 text-xs text-gray-600 bg-gray-100 p-2 rounded overflow-x-auto border">${JSON.stringify(entry.context, null, 2)}</pre>
                </details>
            `;
        }
        
        return `
            <div class="bg-gray-50 rounded p-3 border-l-4 border-gray-300">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs text-gray-500 font-mono">${entry.timestamp}</span>
                </div>
                <p class="text-gray-700 text-sm">${entry.message}</p>
                ${contextHtml}
            </div>
        `;
    }
</script>