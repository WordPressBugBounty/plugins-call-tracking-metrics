<?php
/**
 * Daily Logs Component
 * Displays daily debug logs with filtering, viewing, and management options
 * OPTIMIZED for performance with lazy loading and pagination
 */

// Ensure variables are available from parent context
$available_dates = $available_dates ?? [];
$log_stats = $log_stats ?? [];
?>

<div class="space-y-6">
    <!-- Daily Logs Content Section -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
            <svg class="inline-block w-6 h-6 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <?php _e('Daily Debug Logs', 'call-tracking-metrics'); ?>
        </h3>

        <?php if (empty($available_dates)): ?>
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900"><?php _e('No debug logs found', 'call-tracking-metrics'); ?></h3>
                <p class="mt-2 text-gray-500"><?php _e('Enable debug mode to start logging plugin activity.', 'call-tracking-metrics'); ?></p>
            </div>
        <?php else: ?>
            <div class="space-y-4" id="logs-container">
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
                    <div class="bg-gradient-to-r from-white to-gray-50 border border-gray-200 rounded-xl p-6 hover:shadow-lg transition-all duration-200 hover:border-blue-200">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-4">
                                <!-- Date with modern styling -->
                                <div class="flex items-center">
                                    <div class="bg-blue-50 p-2 rounded-lg mr-3">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <h4 class="text-xl font-bold text-gray-900"><?= esc_html($date) ?></h4>
                                </div>
                                
                                <!-- Modern badge design -->
                                <div class="flex flex-wrap gap-2">
                                    <?php if ($error_count > 0): ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700 border border-red-200">
                                            <span class="w-2 h-2 bg-red-500 rounded-full mr-2"></span>
                                            <?= $error_count ?> error<?= $error_count > 1 ? 's' : '' ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($warning_count > 0): ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700 border border-yellow-200">
                                            <span class="w-2 h-2 bg-yellow-500 rounded-full mr-2"></span>
                                            <?= $warning_count ?> warning<?= $warning_count > 1 ? 's' : '' ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($info_count > 0): ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 border border-blue-200">
                                            <span class="w-2 h-2 bg-blue-500 rounded-full mr-2"></span>
                                            <?= $info_count ?> info
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($debug_count > 0): ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-200">
                                            <span class="w-2 h-2 bg-gray-500 rounded-full mr-2"></span>
                                            <?= $debug_count ?> debug
                                        </span>
                                    <?php endif; ?>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-200">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                        <?= $count_display ?> total
                                    </span>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-700 border border-purple-200">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                                        </svg>
                                        <?= $size_display ?>
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Modern action buttons -->
                            <div class="flex items-center space-x-3">
                                <button class="ctm-toggle-log-view inline-flex items-center px-4 py-2 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition-colors duration-200 text-sm font-medium" data-date="<?= $date ?>">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <?php _e('View Details', 'call-tracking-metrics'); ?>
                                </button>
                                

                                
                                <button type="button" class="ctm-clear-log inline-flex items-center px-4 py-2 bg-red-50 text-red-700 rounded-lg hover:bg-red-100 transition-colors duration-200 text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed" data-date="<?= $date ?>" id="clear-single-<?= $date ?>-btn">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    <?php _e('Clear', 'call-tracking-metrics'); ?>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Collapsible grouped log content with modern styling -->
                        <div id="log-<?= $date ?>" class="hidden mt-6">
                            <div class="space-y-4">
                                <?php foreach ($grouped_logs as $group_key => $group): ?>
                                    <?php
                                    $type_colors = [
                                        'error' => 'text-red-800 bg-red-50 border-red-200',
                                        'warning' => 'text-yellow-800 bg-yellow-50 border-yellow-200',
                                        'info' => 'text-blue-800 bg-blue-50 border-blue-200',
                                        'debug' => 'text-gray-800 bg-gray-50 border-gray-200',
                                        'api' => 'text-purple-800 bg-purple-50 border-purple-200',
                                        'config' => 'text-indigo-800 bg-indigo-50 border-indigo-200',
                                        'system' => 'text-green-800 bg-green-50 border-green-200'
                                    ];
                                    $color_class = $type_colors[$group['type']] ?? 'text-gray-800 bg-gray-50 border-gray-200';
                                    
                                    $type_icons = [
                                        'error' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                                        'warning' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>',
                                        'info' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                                        'debug' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
                                        'api' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>',
                                        'config' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
                                        'system' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>'
                                    ];
                                    $type_icon = $type_icons[$group['type']] ?? $type_icons['debug'];
                                    
                                    // Format dates for better display
                                    $first_date = date('M j, Y g:i A', strtotime($group['first_seen']));
                                    $last_date = date('M j, Y g:i A', strtotime($group['last_seen']));
                                    $date_range = $first_date === $last_date ? $first_date : "{$first_date} - {$last_date}";
                                    ?>
                                    <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-all duration-200 hover:border-gray-300">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <div class="flex items-center space-x-3 mb-3">
                                                    <span class="<?= $color_class ?> px-3 py-1 text-xs font-semibold rounded-full border flex items-center space-x-1">
                                                        <?= $type_icon ?>
                                                        <span><?= esc_html(strtoupper($group['type'])) ?></span>
                                                    </span>
                                                    <span class="bg-blue-100 text-blue-800 px-2 py-1 text-xs font-semibold rounded-full">
                                                        <?= $group['count'] ?> calls
                                                    </span>
                                                    <span class="text-sm text-gray-500 font-mono"><?= esc_html($date_range) ?></span>
                                                </div>
                                                
                                                <!-- Group description -->
                                                <div class="mb-3">
                                                    <h4 class="text-sm font-semibold text-gray-900 mb-1">
                                                        <?php if (strpos($group_key, 'api:') === 0): ?>
                                                            <?= esc_html(str_replace('api:', '', $group_key)) ?>
                                                        <?php else: ?>
                                                            <?= esc_html($group['examples'][0]['message'] ?? 'Log Group') ?>
                                                        <?php endif; ?>
                                                    </h4>
                                                </div>
                                                
                                                <!-- Example entries -->
                                                <div class="space-y-2" id="group-<?= esc_attr($group_key) ?>-entries">
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
                                                    <div class="mt-3 text-center">
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
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Load More Days Button -->
            <?php if (isset($log_stats['total_available_days']) && $log_stats['total_available_days'] > count($available_dates)): ?>
                <div class="text-center py-6">
                    <button id="load-more-days-btn" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-medium">
                        <?php _e('Load More Days', 'call-tracking-metrics'); ?>
                    </button>
                    <p class="text-sm text-gray-500 mt-2">
                        <?php printf(__('Showing %d of %d available days', 'call-tracking-metrics'), count($available_dates), $log_stats['total_available_days']); ?>
                    </p>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>


<script>
    let currentLoadedDays = <?= count($available_dates) ?>;
    let totalAvailableDays = <?= isset($log_stats['total_available_days']) ? $log_stats['total_available_days'] : 0 ?>;
    
    document.addEventListener('DOMContentLoaded', function() {
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
            
            if (e.target.classList.contains('ctm-load-more-entries')) {
                const date = e.target.dataset.date;
                const currentCount = parseInt(e.target.dataset.currentCount) || 0;
                loadMoreLogs(date, currentCount);
            }
            
            if (e.target.classList.contains('ctm-load-more-group-entries')) {
                const groupKey = e.target.dataset.groupKey;
                const date = e.target.dataset.date;
                const currentCount = parseInt(e.target.dataset.currentCount) || 0;
                const totalCount = parseInt(e.target.dataset.totalCount) || 0;
                loadMoreGroupEntries(groupKey, date, currentCount, totalCount);
            }
            
            if (e.target.classList.contains('ctm-load-more-days')) {
                loadMoreDays();
            }
        });

        // Load more days button listener
        const loadMoreDaysBtn = document.getElementById('load-more-days-btn');
        if (loadMoreDaysBtn) {
            loadMoreDaysBtn.addEventListener('click', function() {
                loadMoreDays();
            });
        }
    });

    function toggleLogView(date) {
        const logDiv = document.getElementById('log-' + date);
        if (logDiv.classList.contains('hidden')) {
            logDiv.classList.remove('hidden');
        } else {
            logDiv.classList.add('hidden');
        }
    }

    function loadMoreLogs(date, currentCount) {
        // Find the load more button and replace it with loading state
        const loadMoreContainer = document.querySelector(`#log-${date} .text-center.pt-4`);
        const originalContent = loadMoreContainer.innerHTML;
        
        // Show loading state
        loadMoreContainer.innerHTML = `
            <div class="flex items-center justify-center space-x-3 py-4">
                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                <div class="text-blue-600 text-sm font-medium"><?php _e('Loading more entries...', 'call-tracking-metrics'); ?></div>
            </div>
        `;
        
        // Debug: Log the request parameters
        console.log('LoadMoreLogs Request:', {
            date: date,
            currentCount: currentCount,
            nonce: '<?= wp_create_nonce('ctm_load_more_logs') ?>'
        });
        
        // AJAX call to load more log entries for a specific date
        const formData = new FormData();
        formData.append('action', 'ctm_load_more_logs');
        formData.append('nonce', '<?= wp_create_nonce('ctm_load_more_logs') ?>');
        formData.append('date', date);
        formData.append('offset', currentCount);
        formData.append('limit', 20);

        fetch('<?= admin_url('admin-ajax.php') ?>', {
            method: 'POST',
            body: formData
        })
        .then(function(response) { 
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            return response.json(); 
        })
        .then(function(data) {
            console.log('Response data:', data);
            if (data.success) {
                const logContainer = document.querySelector(`#log-${date} .space-y-3`);
                const loadMoreBtn = logContainer.querySelector('.text-center.pt-4');
                
                // Add new entries
                data.data.entries.forEach(function(entry) {
                    const entryHtml = createLogEntryHtml(entry);
                    const entryElement = document.createElement('div');
                    entryElement.innerHTML = entryHtml;
                    logContainer.insertBefore(entryElement.firstElementChild, loadMoreBtn);
                });
                
                // Update or remove load more button
                if (data.data.has_more) {
                    const newCount = currentCount + data.data.entries.length;
                    loadMoreContainer.innerHTML = `
                        <button class="ctm-load-more-entries bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm transition-colors duration-200" data-date="${date}" data-current-count="${newCount}">
                            <?php _e('Load More Entries', 'call-tracking-metrics'); ?>
                        </button>
                        <p class="text-xs text-gray-500 mt-1"><?php _e('Showing', 'call-tracking-metrics'); ?> ${newCount} <?php _e('of', 'call-tracking-metrics'); ?> ${data.data.total} <?php _e('entries', 'call-tracking-metrics'); ?></p>
                    `;
                } else {
                    loadMoreContainer.innerHTML = `
                        <p class="text-xs text-gray-500"><?php _e('All entries loaded', 'call-tracking-metrics'); ?></p>
                    `;
                }
            } else {
                console.error('Server error:', data);
                // Show error state
                loadMoreContainer.innerHTML = `
                    <div class="text-center py-4">
                        <div class="text-red-600 text-sm mb-2"><?php _e('Failed to load more entries', 'call-tracking-metrics'); ?></div>
                        <div class="text-xs text-gray-500 mb-2">${data.data ? data.data.message : 'Unknown error'}</div>
                        <button class="ctm-load-more-entries bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm transition-colors duration-200" data-date="${date}" data-current-count="${currentCount}">
                            <?php _e('Try Again', 'call-tracking-metrics'); ?>
                        </button>
                    </div>
                `;
            }
        })
        .catch(function(error) {
            console.error('Network error:', error);
            // Show error state
            loadMoreContainer.innerHTML = `
                <div class="text-center py-4">
                    <div class="text-red-600 text-sm mb-2"><?php _e('Network error occurred', 'call-tracking-metrics'); ?></div>
                    <div class="text-xs text-gray-500 mb-2">${error.message}</div>
                    <button class="ctm-load-more-entries bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm transition-colors duration-200" data-date="${date}" data-current-count="${currentCount}">
                        <?php _e('Try Again', 'call-tracking-metrics'); ?>
                    </button>
                </div>
            `;
        });
    }

    function loadMoreDays() {
        const loadMoreBtn = document.getElementById('load-more-days-btn');
        const originalContent = loadMoreBtn.innerHTML;
        
        // Show loading state
        loadMoreBtn.innerHTML = `
            <div class="flex items-center justify-center space-x-3">
                <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
                <span><?php _e('Loading more days...', 'call-tracking-metrics'); ?></span>
            </div>
        `;
        loadMoreBtn.disabled = true;
        
        // AJAX call to load more days
        const formData = new FormData();
        formData.append('action', 'ctm_load_more_days');
        formData.append('nonce', '<?= wp_create_nonce('ctm_load_more_days') ?>');
        formData.append('offset', currentLoadedDays);
        formData.append('limit', 5);

        fetch('<?= admin_url('admin-ajax.php') ?>', {
            method: 'POST',
            body: formData
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                const logsContainer = document.getElementById('logs-container');
                
                // Add new day entries
                data.data.days.forEach(function(dayData) {
                    const dayHtml = createDayLogHtml(dayData);
                    logsContainer.insertAdjacentHTML('beforeend', dayHtml);
                });
                
                // Update current loaded days count
                currentLoadedDays += data.data.days.length;
                
                // Update or remove load more button
                if (data.data.has_more) {
                    loadMoreBtn.innerHTML = originalContent;
                    loadMoreBtn.disabled = false;
                    
                    // Update the count text
                    const countText = loadMoreBtn.nextElementSibling;
                    if (countText) {
                        countText.innerHTML = `<?php _e('Showing', 'call-tracking-metrics'); ?> ${currentLoadedDays} <?php _e('of', 'call-tracking-metrics'); ?> ${totalAvailableDays} <?php _e('available days', 'call-tracking-metrics'); ?>`;
                    }
                } else {
                    loadMoreBtn.innerHTML = `
                        <span class="text-gray-500"><?php _e('All days loaded', 'call-tracking-metrics'); ?></span>
                    `;
                    loadMoreBtn.disabled = true;
                }
            } else {
                // Show error state
                loadMoreBtn.innerHTML = `
                    <div class="flex items-center justify-center space-x-3">
                        <span class="text-red-600"><?php _e('Network error occurred', 'call-tracking-metrics'); ?></span>
                        <button class="ctm-load-more-days bg-gray-600 hover:bg-gray-700 text-white px-3 py-1 rounded text-xs">
                            <?php _e('Try Again', 'call-tracking-metrics'); ?>
                        </button>
                    </div>
                `;
                loadMoreBtn.disabled = false;
            }
        })
        .catch(function(error) {
            console.error('Error loading more days:', error);
            // Show error state
            loadMoreBtn.innerHTML = `
                <div class="flex items-center justify-center space-x-3">
                    <span class="text-red-600"><?php _e('Network error occurred', 'call-tracking-metrics'); ?></span>
                    <button class="ctm-load-more-days bg-gray-600 hover:bg-gray-700 text-white px-3 py-1 rounded text-xs">
                        <?php _e('Try Again', 'call-tracking-metrics'); ?>
                    </button>
                </div>
            `;
            loadMoreBtn.disabled = false;
        });
    }

    function createLogEntryHtml(entry) {
        const typeColors = {
            'error': 'text-red-800 bg-red-100',
            'warning': 'text-yellow-800 bg-yellow-100',
            'info': 'text-blue-800 bg-blue-100',
            'debug': 'text-gray-800 bg-gray-100',
            'api': 'text-purple-800 bg-purple-100',
            'config': 'text-indigo-800 bg-indigo-100',
            'system': 'text-green-800 bg-green-100'
        };
        const colorClass = typeColors[entry.type] || 'text-gray-800 bg-gray-100';
        
        let contextHtml = '';
        if (entry.context && Object.keys(entry.context).length > 0) {
            contextHtml = `
                <details class="mt-2">
                    <summary class="text-xs text-gray-500 cursor-pointer hover:text-gray-700">Context Details</summary>
                    <pre class="mt-1 text-xs text-gray-600 bg-gray-50 p-2 rounded overflow-x-auto">${JSON.stringify(entry.context, null, 2)}</pre>
                </details>
            `;
        }
        
        return `
            <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-all duration-200 hover:border-gray-300">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-3 mb-2">
                            <span class="${colorClass} px-3 py-1 text-xs font-semibold rounded-full border flex items-center space-x-1">
                                <span>${entry.type.toUpperCase()}</span>
                            </span>
                            <span class="text-sm text-gray-500 font-mono">${entry.timestamp}</span>
                        </div>
                        <p class="text-gray-900 text-sm leading-relaxed">${entry.message}</p>
                        ${contextHtml}
                    </div>
                </div>
            </div>
        `;
    }

    // Create day log HTML (for dynamically loaded days)
    function createDayLogHtml(dayData) {
        const { date, logs, error_count, warning_count, info_count, debug_count, total_count } = dayData;
        
        let badgesHtml = '';
        if (error_count > 0) {
            badgesHtml += `<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700 border border-red-200">
                <span class="w-2 h-2 bg-red-500 rounded-full mr-2"></span>
                ${error_count} error${error_count > 1 ? 's' : ''}
            </span>`;
        }
        if (warning_count > 0) {
            badgesHtml += `<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700 border border-yellow-200">
                <span class="w-2 h-2 bg-yellow-500 rounded-full mr-2"></span>
                ${warning_count} warning${warning_count > 1 ? 's' : ''}
            </span>`;
        }
        if (info_count > 0) {
            badgesHtml += `<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 border border-blue-200">
                <span class="w-2 h-2 bg-blue-500 rounded-full mr-2"></span>
                ${info_count} info
            </span>`;
        }
        if (debug_count > 0) {
            badgesHtml += `<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-200">
                <span class="w-2 h-2 bg-gray-500 rounded-full mr-2"></span>
                ${debug_count} debug
            </span>`;
        }
        badgesHtml += `<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-200">
            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            ${total_count} total
        </span>`;
        
        return `
            <div class="bg-gradient-to-r from-white to-gray-50 border border-gray-200 rounded-xl p-6 hover:shadow-lg transition-all duration-200 hover:border-blue-200">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-4">
                        <!-- Date with modern styling -->
                        <div class="flex items-center">
                            <div class="bg-blue-50 p-2 rounded-lg mr-3">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <h4 class="text-xl font-bold text-gray-900">${date}</h4>
                        </div>
                        
                        <!-- Modern badge design -->
                        <div class="flex flex-wrap gap-2">
                            ${badgesHtml}
                        </div>
                    </div>
                    
                    <!-- Modern action buttons -->
                    <div class="flex items-center space-x-3">
                        <button class="ctm-toggle-log-view inline-flex items-center px-4 py-2 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition-colors duration-200 text-sm font-medium" data-date="${date}">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <?php _e('View Details', 'call-tracking-metrics'); ?>
                        </button>
                        

                        
                        <button type="button" class="ctm-clear-log inline-flex items-center px-4 py-2 bg-red-50 text-red-700 rounded-lg hover:bg-red-100 transition-colors duration-200 text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed" data-date="${date}" id="clear-single-${date}-btn">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            <?php _e('Clear', 'call-tracking-metrics'); ?>
                        </button>
                    </div>
                </div>
                
                <!-- Collapsible log content with modern styling -->
                <div id="log-${date}" class="hidden">
                    <div class="bg-white rounded-lg border border-gray-200 p-6 max-h-96 overflow-y-auto">
                        <div class="space-y-4">
                            <!-- Log entries will be loaded when view details is clicked -->
                            <div class="text-center py-8">
                                <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="text-gray-500"><?php _e('Click "View Details" to load log entries', 'call-tracking-metrics'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }




    function clearDebugLogs(logType, logDate = '') {
        const buttonId = logType === 'debug_all' ? 'clear-debug-all-btn' : `clear-single-${logDate}-btn`;
        const button = document.getElementById(buttonId);
        const originalText = button.textContent;
        
        // Confirm action
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