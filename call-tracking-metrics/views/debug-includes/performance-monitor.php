<?php
/**
 * System & Performance Monitor Component
 * Comprehensive system information and real-time performance monitoring
 */

// Helper function to get system info as an array
function ctm_get_system_info_array() {
    global $wpdb;
    return [
        'php_version' => PHP_VERSION,
        'wp_version' => get_bloginfo('version'),
        'memory_usage' => size_format(memory_get_usage(true)),
        'db_queries' => get_num_queries(),
        'wordpress_env' => [
            'version' => get_bloginfo('version'),
            'language' => get_locale(),
            'debug_mode' => WP_DEBUG ? 'Enabled' : 'Disabled',
            'memory_limit' => WP_MEMORY_LIMIT,
            'multisite' => is_multisite() ? 'Yes' : 'No',
            'timezone' => get_option('timezone_string') ?: 'UTC',
        ],
        'server_env' => [
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'os' => PHP_OS,
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time') . 's',
            'upload_max_size' => ini_get('upload_max_filesize'),
        ],
        'database_info' => [
            'db_version' => $wpdb->db_version(),
            'db_host' => DB_HOST,
            'db_name' => DB_NAME,
            'table_prefix' => $wpdb->prefix,
            'db_charset' => DB_CHARSET,
            'current_queries' => get_num_queries(),
        ],
    ];
}

// Helper function to get system info as a report string
function ctm_get_system_info_report() {
    global $wpdb;
    $report = "=== SYSTEM INFORMATION REPORT ===\n";
    $report .= "Generated: " . current_time('Y-m-d H:i:s') . "\n";
    $report .= "Site: " . get_bloginfo('name') . "\n\n";
    $report .= "=== WORDPRESS ENVIRONMENT ===\n";
    $report .= "WordPress Version: " . get_bloginfo('version') . "\n";
    $report .= "Site URL: " . home_url() . "\n";
    $report .= "Admin URL: " . admin_url() . "\n";
    $report .= "WordPress Language: " . get_locale() . "\n";
    $report .= "WordPress Debug: " . (WP_DEBUG ? 'Enabled' : 'Disabled') . "\n";
    $report .= "WordPress Memory Limit: " . WP_MEMORY_LIMIT . "\n";
    $report .= "Multisite: " . (is_multisite() ? 'Yes' : 'No') . "\n\n";
    $report .= "=== SERVER ENVIRONMENT ===\n";
    $report .= "PHP Version: " . PHP_VERSION . "\n";
    $report .= "PHP SAPI: " . php_sapi_name() . "\n";
    $report .= "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "\n";
    $report .= "Operating System: " . PHP_OS . "\n";
    $report .= "Memory Limit: " . ini_get('memory_limit') . "\n";
    $report .= "Max Execution Time: " . ini_get('max_execution_time') . "s\n";
    $report .= "Max Input Vars: " . ini_get('max_input_vars') . "\n";
    $report .= "Upload Max Size: " . ini_get('upload_max_filesize') . "\n";
    $report .= "Post Max Size: " . ini_get('post_max_size') . "\n";
    $report .= "Max File Uploads: " . ini_get('max_file_uploads') . "\n\n";
    $report .= "=== DATABASE ===\n";
    $report .= "Database Version: " . $wpdb->db_version() . "\n";
    $report .= "Database Host: " . DB_HOST . "\n";
    $report .= "Database Name: " . DB_NAME . "\n";
    $report .= "Database Charset: " . DB_CHARSET . "\n";
    $report .= "Table Prefix: " . $wpdb->prefix . "\n\n";
    $report .= "=== PHP EXTENSIONS ===\n";
    $report .= "cURL: " . (function_exists('curl_init') ? 'Available' : 'Missing') . "\n";
    $report .= "OpenSSL: " . (extension_loaded('openssl') ? 'Available' : 'Missing') . "\n";
    $report .= "mbstring: " . (extension_loaded('mbstring') ? 'Available' : 'Missing') . "\n";
    $report .= "GD Library: " . (extension_loaded('gd') ? 'Available' : 'Missing') . "\n";
    $report .= "XML: " . (extension_loaded('xml') ? 'Available' : 'Missing') . "\n";
    $report .= "JSON: " . (extension_loaded('json') ? 'Available' : 'Missing') . "\n";
    $report .= "ZIP: " . (extension_loaded('zip') ? 'Available' : 'Missing') . "\n\n";
    $report .= "=== CALLTRACKINGMETRICS PLUGIN ===\n";
    $report .= "Plugin Version: 2.0\n";
    $report .= "Debug Mode: " . (get_option('ctm_debug_enabled') ? 'Enabled' : 'Disabled') . "\n";
    $report .= "API Key Configured: " . (get_option('ctm_api_key') ? 'Yes' : 'No') . "\n";
    $report .= "CF7 Integration: " . (get_option('ctm_api_cf7_enabled') ? 'Enabled' : 'Disabled') . "\n";
    $report .= "GF Integration: " . (get_option('ctm_api_gf_enabled') ? 'Enabled' : 'Disabled') . "\n\n";
    $report .= "=== THEME & PLUGINS ===\n";
    $report .= "Active Theme: " . wp_get_theme()->get('Name') . "\n";
    $report .= "Theme Version: " . wp_get_theme()->get('Version') . "\n";
    $report .= "Active Plugins: " . count(get_option('active_plugins', [])) . "\n";
    $report .= "Contact Form 7: " . (class_exists('WPCF7_ContactForm') ? 'Installed' : 'Not Installed') . "\n";
    $report .= "Gravity Forms: " . (class_exists('GFAPI') ? 'Installed' : 'Not Installed') . "\n\n";
    $report .= "=== CURRENT PERFORMANCE ===\n";
    $report .= "Memory Usage: " . size_format(memory_get_usage(true)) . "\n";
    $report .= "Peak Memory: " . size_format(memory_get_peak_usage(true)) . "\n";
    $report .= "Database Queries: " . get_num_queries() . "\n";
    $report .= "Admin Email: " . get_option('admin_email') . "\n";
    $report .= "Timezone: " . (get_option('timezone_string') ?: 'UTC') . "\n\n";
    $report .= "=== END REPORT ===";
    return $report;
}

// Get system info for static display
$system_info = ctm_get_system_info_array();
?>

<div class="bg-white rounded-xl shadow-lg border border-gray-200">
    <div class="flex items-center justify-between p-4 cursor-pointer hover:bg-gray-50 transition-colors rounded-t-xl" onclick="togglePanel('system-performance-monitor')">
        <div class="flex items-center gap-2">
            <svg id="system-performance-monitor-icon" class="w-5 h-5 text-indigo-600 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="transform: rotate(180deg);">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <h3 class="text-lg font-semibold text-gray-900"><?php _e('System & Performance Monitor', 'call-tracking-metrics'); ?></h3>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="event.stopPropagation(); copySystemInfo()" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-3 py-1 rounded-lg transition text-xs flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                <?php _e('Copy', 'call-tracking-metrics'); ?>
            </button>
            <button onclick="event.stopPropagation(); toggleAutoRefresh()" id="auto-refresh-btn" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium px-3 py-1 rounded-lg transition text-xs flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <?php _e('Auto', 'call-tracking-metrics'); ?>
            </button>
            <button onclick="event.stopPropagation(); refreshSystemPerformance()" id="refresh-system-performance-btn" class="bg-gray-600 hover:bg-gray-700 !text-white font-medium px-3 py-1 rounded-lg flex items-center gap-1 transition text-xs">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                <?php _e('Refresh', 'call-tracking-metrics'); ?>
            </button>
        </div>
    </div>
    <div id="system-performance-monitor-content" class="border-t border-gray-200 p-6">
    
        <!-- Quick Stats Grid -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-center" data-metric="memory_usage">
                <div class="flex items-center justify-center mb-1">
                    <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                    </svg>
                    <div class="text-2xl font-bold text-blue-600" data-field="ajax_memory_usage">--</div>
                </div>
                <div class="text-xs text-blue-700"><?php _e('Memory Usage', 'call-tracking-metrics'); ?></div>
            </div>
            <div class="bg-green-50 border border-green-200 rounded-lg p-3 text-center" data-metric="page_load_time">
                <div class="flex items-center justify-center mb-1">
                    <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <div class="text-2xl font-bold text-green-600" data-field="page_load_time">--</div>
                </div>
                <div class="text-xs text-green-700"><?php _e('Load Time', 'call-tracking-metrics'); ?></div>
            </div>
            <div class="bg-purple-50 border border-purple-200 rounded-lg p-3 text-center" data-metric="db_queries">
                <div class="flex items-center justify-center mb-1">
                    <svg class="w-5 h-5 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                    </svg>
                    <div class="text-2xl font-bold text-purple-600" data-field="ajax_db_queries">--</div>
                </div>
                <div class="text-xs text-purple-700"><?php _e('DB Queries', 'call-tracking-metrics'); ?></div>
            </div>
            <div class="bg-orange-50 border border-orange-200 rounded-lg p-3 text-center" data-metric="api_calls">
                <div class="flex items-center justify-center mb-1">
                    <svg class="w-5 h-5 text-orange-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <div class="text-2xl font-bold text-orange-600" data-field="api_calls">--</div>
                </div>
                <div class="text-xs text-orange-700"><?php _e('API Calls', 'call-tracking-metrics'); ?></div>
            </div>
        </div>

        <!-- System Information Section -->
        <div class="space-y-4 mb-6">
            <!-- WordPress Environment -->
            <div class="border border-gray-200 rounded-lg" data-section="wordpress-env">
                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                    <div class="flex items-center gap-1">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                        </svg>
                        <h4 class="text-lg font-bold text-gray-900"><?php _e('WordPress Environment', 'call-tracking-metrics'); ?></h4>
                    </div>
                </div>
                <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600"><?php _e('Version:', 'call-tracking-metrics'); ?></span>
                        <span class="font-medium" data-field="version"><?= esc_html($system_info['wordpress_env']['version']) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600"><?php _e('Language:', 'call-tracking-metrics'); ?></span>
                        <span class="font-medium" data-field="language"><?= esc_html($system_info['wordpress_env']['language']) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600"><?php _e('Debug Mode:', 'call-tracking-metrics'); ?></span>
                        <span class="font-medium <?= WP_DEBUG ? 'text-yellow-600' : 'text-green-600' ?>" data-field="debug_mode">
                            <?= esc_html($system_info['wordpress_env']['debug_mode']) ?>
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600"><?php _e('Memory Limit:', 'call-tracking-metrics'); ?></span>
                        <span class="font-medium" data-field="memory_limit"><?= esc_html($system_info['wordpress_env']['memory_limit']) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600"><?php _e('Multisite:', 'call-tracking-metrics'); ?></span>
                        <span class="font-medium" data-field="multisite"><?= esc_html($system_info['wordpress_env']['multisite']) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600"><?php _e('Timezone:', 'call-tracking-metrics'); ?></span>
                        <span class="font-medium" data-field="timezone"><?= esc_html($system_info['wordpress_env']['timezone']) ?></span>
                    </div>
                </div>
            </div>

            <!-- Server Environment -->
            <div class="border border-gray-200 rounded-lg" data-section="server-env">
                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                    <div class="flex items-center gap-1">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/>
                        </svg>
                        <h4 class="text-lg font-bold text-gray-900"><?php _e('Server Environment', 'call-tracking-metrics'); ?></h4>
                    </div>
                </div>
                <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600"><?php _e('PHP Version:', 'call-tracking-metrics'); ?></span>
                        <span class="font-medium" data-field="php_version"><?= esc_html($system_info['server_env']['php_version']) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600"><?php _e('Server Software:', 'call-tracking-metrics'); ?></span>
                        <span class="font-medium text-xs" data-field="server_software"><?= esc_html($system_info['server_env']['server_software']) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600"><?php _e('OS:', 'call-tracking-metrics'); ?></span>
                        <span class="font-medium" data-field="os"><?= esc_html($system_info['server_env']['os']) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600"><?php _e('Memory Limit:', 'call-tracking-metrics'); ?></span>
                        <span class="font-medium" data-field="memory_limit"><?= esc_html($system_info['server_env']['memory_limit']) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600"><?php _e('Max Execution Time:', 'call-tracking-metrics'); ?></span>
                        <span class="font-medium" data-field="max_execution_time"><?= esc_html($system_info['server_env']['max_execution_time']) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600"><?php _e('Upload Max Size:', 'call-tracking-metrics'); ?></span>
                        <span class="font-medium" data-field="upload_max_size"><?= esc_html($system_info['server_env']['upload_max_size']) ?></span>
                    </div>
                </div>
            </div>

            <!-- Database Information -->
            <div class="border border-gray-200 rounded-lg" data-section="database-info">
                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                    <div class="flex items-center gap-1">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                        </svg>
                        <h4 class="text-lg font-bold text-gray-900"><?php _e('Database Information', 'call-tracking-metrics'); ?></h4>
                    </div>
                </div>
                <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600"><?php _e('Version:', 'call-tracking-metrics'); ?></span>
                        <span class="font-medium" data-field="db_version"><?= esc_html($system_info['database_info']['db_version']) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600"><?php _e('Host:', 'call-tracking-metrics'); ?></span>
                        <span class="font-medium text-xs" data-field="db_host"><?= esc_html($system_info['database_info']['db_host']) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600"><?php _e('Name:', 'call-tracking-metrics'); ?></span>
                        <span class="font-medium" data-field="db_name"><?= esc_html($system_info['database_info']['db_name']) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600"><?php _e('Table Prefix:', 'call-tracking-metrics'); ?></span>
                        <span class="font-medium" data-field="table_prefix"><?= esc_html($system_info['database_info']['table_prefix']) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600"><?php _e('Charset:', 'call-tracking-metrics'); ?></span>
                        <span class="font-medium" data-field="db_charset"><?= esc_html($system_info['database_info']['db_charset']) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600"><?php _e('Current Queries:', 'call-tracking-metrics'); ?></span>
                        <span class="font-medium" data-field="current_queries"><?= esc_html($system_info['database_info']['current_queries']) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Performance Metrics -->
        <div class="space-y-4">
            <!-- Memory & Processing -->
            <div class="border border-gray-200 rounded-lg" data-section="memory-processing">
                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                    <div class="flex items-center gap-1">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                        </svg>
                        <h4 class="text-lg font-bold text-gray-900"><?php _e('Memory & Processing', 'call-tracking-metrics'); ?></h4>
                    </div>
                </div>
                <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600"><?php _e('Current Memory:', 'call-tracking-metrics'); ?></span>
                        <span class="font-medium" data-field="current_memory">--</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600"><?php _e('Memory Limit:', 'call-tracking-metrics'); ?></span>
                        <span class="font-medium" data-field="ajax_memory_limit">--</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600"><?php _e('Execution Time:', 'call-tracking-metrics'); ?></span>
                        <span class="font-medium" data-field="ajax_execution_time">--</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600"><?php _e('Time Limit:', 'call-tracking-metrics'); ?></span>
                        <span class="font-medium" data-field="ajax_time_limit">--</span>
                    </div>
                </div>
            </div>

            <!-- Database Performance -->
            <div class="border border-gray-200 rounded-lg" data-section="database-performance">
                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                    <div class="flex items-center gap-1">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                        </svg>
                        <h4 class="text-lg font-bold text-gray-900"><?php _e('Database Performance', 'call-tracking-metrics'); ?></h4>
                    </div>
                </div>
                <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600"><?php _e('Total Queries:', 'call-tracking-metrics'); ?></span>
                        <span class="font-medium" data-field="ajax_total_queries">--</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600"><?php _e('Query Time:', 'call-tracking-metrics'); ?></span>
                        <span class="font-medium" data-field="ajax_total_query_time">--</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600"><?php _e('Slow Queries:', 'call-tracking-metrics'); ?></span>
                        <span class="font-medium" data-field="ajax_slow_queries">--</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600"><?php _e('DB Version:', 'call-tracking-metrics'); ?></span>
                        <span class="font-medium" data-field="ajax_db_version">--</span>
                    </div>
                </div>
            </div>

            <!-- System Health -->
            <div class="border border-gray-200 rounded-lg" data-section="system-health">
                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                    <div class="flex items-center gap-1">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h4 class="text-lg font-bold text-gray-900"><?php _e('System Health', 'call-tracking-metrics'); ?></h4>
                    </div>
                </div>
                <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600"><?php _e('Server Load:', 'call-tracking-metrics'); ?></span>
                        <span class="font-medium" data-field="ajax_server_load">--</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600"><?php _e('Error Rate:', 'call-tracking-metrics'); ?></span>
                        <span class="font-medium" data-field="ajax_error_rate">--</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600"><?php _e('PHP Version:', 'call-tracking-metrics'); ?></span>
                        <span class="font-medium" data-field="ajax_php_version">--</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600"><?php _e('WordPress Version:', 'call-tracking-metrics'); ?></span>
                        <span class="font-medium" data-field="ajax_wp_version">--</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Alerts -->
        <div id="performance-alerts" class="space-y-2">
            <!-- Alerts will be populated by JavaScript -->
        </div>
    </div>
</div>

<script>
    let autoRefreshInterval = null;
    let autoRefreshEnabled = false;
    let refreshCount = 0;
    const MAX_REFRESH_COUNT = 10; // Limit auto-refresh to prevent excessive resource usage

    function toggleAutoRefresh() {
        const btn = document.getElementById('auto-refresh-btn');
        
        if (autoRefreshEnabled) {
            // Disable auto-refresh
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
                autoRefreshInterval = null;
            }
            autoRefreshEnabled = false;
            btn.textContent = '<?php _e('Auto-refresh: OFF', 'call-tracking-metrics'); ?>';
            btn.classList.remove('bg-green-200', 'hover:bg-green-300');
            btn.classList.add('bg-gray-200', 'hover:bg-gray-300');
        } else {
            // Enable auto-refresh with longer interval (30 seconds instead of 10)
            autoRefreshEnabled = true;
            refreshCount = 0;
            btn.textContent = '<?php _e('Auto-refresh: ON', 'call-tracking-metrics'); ?>';
            btn.classList.remove('bg-gray-200', 'hover:bg-gray-300');
            btn.classList.add('bg-green-200', 'hover:bg-green-300');
            
            // Start auto-refresh with 30-second interval (OPTIMIZED)
            autoRefreshInterval = setInterval(() => {
                refreshCount++;
                if (refreshCount >= MAX_REFRESH_COUNT) {
                    // Stop auto-refresh after 10 cycles to prevent resource abuse
                    toggleAutoRefresh();
                    ctmShowToast('Auto-refresh stopped after 10 cycles to conserve resources', 'info');
                    return;
                }
                refreshSystemPerformance();
            }, 30000); // 30 seconds instead of 10
            
            // Initial refresh
            refreshSystemPerformance();
        }
    }

    function refreshSystemPerformance() {
        const btn = document.getElementById('refresh-system-performance-btn');
        const originalText = btn.innerHTML;
        
        // Show loading state
        btn.disabled = true;
        btn.innerHTML = '<svg class="w-3 h-3 animate-spin mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg><?php _e('Refreshing...', 'call-tracking-metrics'); ?>';

        const formData = new FormData();
        formData.append('action', 'ctm_get_performance_metrics');

        fetch('<?= admin_url('admin-ajax.php') ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                // Update the UI with the data
                updateSystemPerformanceMetrics(data.data);
                btn.innerHTML = originalText;
                btn.disabled = false;
            } else {
                throw new Error('No data received');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }

    function updateSystemPerformanceMetrics(data) {
        // Update core metrics (quick stats)
        updateElementByDataField('ajax_memory_usage', data.memory_usage || '--');
        updateElementByDataField('page_load_time', data.page_load_time || '--');
        updateElementByDataField('ajax_db_queries', data.db_queries || '--');
        updateElementByDataField('api_calls', data.api_calls || '--');
        
        // Update detailed metrics
        updateElementByDataField('current_memory', data.current_memory || '--');
        updateElementByDataField('ajax_memory_limit', data.memory_limit || '--');
        updateElementByDataField('ajax_execution_time', data.execution_time || '--');
        updateElementByDataField('ajax_time_limit', data.time_limit || '--');
        updateElementByDataField('ajax_total_queries', data.total_queries || '--');
        updateElementByDataField('ajax_total_query_time', data.total_query_time || '--');
        updateElementByDataField('ajax_slow_queries', data.slow_queries || '--');
        updateElementByDataField('ajax_db_version', data.db_version || '--');
        updateElementByDataField('ajax_server_load', data.server_load || '--');
        updateElementByDataField('ajax_error_rate', data.error_rate || '--');
        updateElementByDataField('ajax_php_version', data.php_version || '--');
        updateElementByDataField('ajax_wp_version', data.wp_version || '--');
    }

    function updatePerformanceAlerts(data) {
        const alertsContainer = document.getElementById('performance-alerts');
        alertsContainer.innerHTML = '';

        const alerts = [];

        // Memory alerts
        if (data.memory_percentage && parseFloat(data.memory_percentage) > 80) {
            alerts.push({
                type: 'warning',
                message: 'High memory usage detected',
                icon: '⚠️'
            });
        }

        // Query time alerts
        if (data.query_time && data.query_time !== 'N/A') {
            const queryTime = parseFloat(data.query_time);
            if (queryTime > 1000) { // More than 1 second
                alerts.push({
                    type: 'error',
                    message: 'Slow database queries detected',
                    icon: '🐌'
                });
            }
        }

        // Page load time alerts
        if (data.page_load_time && data.page_load_time !== 'N/A') {
            const loadTime = parseFloat(data.page_load_time);
            if (loadTime > 3000) { // More than 3 seconds
                alerts.push({
                    type: 'error',
                    message: 'Slow page load time detected',
                    icon: '⏱️'
                });
            }
        }

        // Error rate alerts
        if (data.error_rate && data.error_rate.includes('High')) {
            alerts.push({
                type: 'error',
                message: 'High error rate detected',
                icon: '❌'
            });
        }

        // Display alerts
        alerts.forEach(alert => {
            const alertDiv = document.createElement('div');
            alertDiv.className = `p-3 rounded-lg border-l-4 ${
                alert.type === 'error' ? 'bg-red-50 border-red-400 text-red-700' :
                alert.type === 'warning' ? 'bg-yellow-50 border-yellow-400 text-yellow-700' :
                'bg-blue-50 border-blue-400 text-blue-700'
            }`;
            alertDiv.innerHTML = `
                <div class="flex items-center">
                    <span class="text-lg mr-2">${alert.icon}</span>
                    <span class="font-medium">${alert.message}</span>
                </div>
            `;
            alertsContainer.appendChild(alertDiv);
        });

        // Show "all good" message if no alerts
        if (alerts.length === 0) {
            const goodDiv = document.createElement('div');
            goodDiv.className = 'p-3 rounded-lg border-l-4 bg-green-50 border-green-400 text-green-700';
            goodDiv.innerHTML = `
                <div class="flex items-center">
                    <span class="text-lg mr-2">✅</span>
                    <span class="font-medium">All performance metrics are within normal ranges</span>
                </div>
            `;
            alertsContainer.appendChild(goodDiv);
        }
    }

    function updateElement(id, value) {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = value;
        }
    }

    function updateElementByDataField(fieldName, value) {
        const element = document.querySelector(`[data-field="${fieldName}"]`);
        if (element) {
            element.textContent = value;
        }
    }

    // Initial load
    document.addEventListener('DOMContentLoaded', function() {
        refreshSystemPerformance();
    });
</script>