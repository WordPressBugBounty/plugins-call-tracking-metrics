<?php
namespace CTM\Admin;

/**
 * Handles debug logging and log management functionality
 */
class LoggingSystem
{
    /**
     * Check if debug mode is enabled
     */
    public function isDebugEnabled(): bool
    {
        return (bool) \get_option('ctm_debug_enabled', false);
    }

    /**
     * Enhanced logging system with daily logs and categorization
     */
    public function logActivity(string $message, string $type = 'info', array $context = []): void
    {
        if (!self::isDebugEnabled()) {
            return; // Only log when debug mode is enabled
        }

        $log_entry = [
            'timestamp' => \current_time('mysql'),
            'type' => $type, // info, error, warning, debug, api, config, system
            'message' => $message,
            'context' => $context,
            'user_id' => \get_current_user_id(),
            'ip_address' => $this->getUserIP(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true)
        ];

        // Ensure all required keys are present to prevent undefined array key warnings
        $required_keys = ['timestamp', 'type', 'message', 'context', 'user_id', 'ip_address', 'user_agent', 'memory_usage', 'memory_peak'];
        foreach ($required_keys as $key) {
            if (!array_key_exists($key, $log_entry)) {
                $log_entry[$key] = null;
            }
        }

        $this->writeToLog($log_entry);
        
        // Auto-cleanup old logs
        $this->cleanupOldLogs();
    }

    /**
     * Write log entry to database table
     */
    private function writeToLog(array $log_entry): void
    {
        // Ensure table exists before writing
        if (!$this->ensureTableExists()) {
            return;
        }
        
        global $wpdb;
        $table_name = $this->getLogTableName();
        
        // Prepare data for database insertion
        $data = [
            'timestamp' => $log_entry['timestamp'] ?? null,
            'type' => $log_entry['type'] ?? null,
            'message' => $log_entry['message'] ?? null,
            'context' => !empty($log_entry['context']) ? json_encode($log_entry['context']) : null,
            'user_id' => $log_entry['user_id'] ?? null,
            'ip_address' => $log_entry['ip_address'] ?? null,
            'user_agent' => $log_entry['user_agent'] ?? null,
            'memory_usage' => $log_entry['memory_usage'] ?? null,
            'memory_peak' => $log_entry['memory_peak'] ?? null,
            'form_type' => $log_entry['form_type'] ?? null,
            'form_id' => $log_entry['form_id'] ?? null
        ];
        
        // Insert into database
        $result = $wpdb->insert($table_name, $data);
        
        if ($result === false) {
            return;
        }
        
        // Cleanup old logs periodically (every 100th insert to avoid performance impact)
        if (rand(1, 100) === 1) {
            $this->cleanupOldLogs();
        }
    }

    /**
     * Enforce log size limits to prevent database bloat
     * 
     * @since 2.0.0
     * @param string $log_date The log date
     * @param array $daily_logs The daily logs array (passed by reference)
     */
    private function enforceLogSizeLimits(string $log_date, array &$daily_logs): void
    {
        // Get size limits from options
        $max_entries = (int) \get_option('ctm_max_log_entries_per_day', 300);
        $max_size_mb = (float) \get_option('ctm_max_log_size_mb', 5.0);
        $max_total_size_mb = (float) \get_option('ctm_max_total_log_size_mb', 50.0);
        
        // Limit entries per day
        if (count($daily_logs) > $max_entries) {
            $daily_logs = array_slice($daily_logs, -$max_entries);
            // Don't log this to prevent recursion
        }
        
        // Check individual day size
        $day_size_mb = strlen(serialize($daily_logs)) / 1024 / 1024;
        if ($day_size_mb > $max_size_mb) {
            // Remove oldest entries until under limit
            while (count($daily_logs) > 50 && $day_size_mb > $max_size_mb) {
                array_shift($daily_logs);
                $day_size_mb = strlen(serialize($daily_logs)) / 1024 / 1024;
            }
            // Don't log this to prevent recursion
        }
        
        // Check total log size across all days
        $this->enforceTotalLogSizeLimit($max_total_size_mb);
    }

    /**
     * Enforce total log size limit across all days
     * 
     * @since 2.0.0
     * @param float $max_total_size_mb Maximum total log size in MB
     */
    private function enforceTotalLogSizeLimit(float $max_total_size_mb): void
    {
        // Check memory limit first
        $memory_limit = ini_get('memory_limit');
        $current_memory = memory_get_usage(true);
        $memory_limit_bytes = $this->parseMemoryLimit($memory_limit);
        
        // If we're using more than 80% of available memory, skip this operation
        if ($memory_limit_bytes && ($current_memory / $memory_limit_bytes) > 0.8) {
            // Don't log this to prevent recursion
            return;
        }
        
        $log_index = \get_option('ctm_log_index', []);
        if (!is_array($log_index) || empty($log_index)) {
            return;
        }
        
        $total_size = 0;
        $day_sizes = [];
        
        // Calculate size of each day's logs more efficiently
        foreach ($log_index as $date) {
            // Check memory usage before processing each day
            if (memory_get_usage(true) > ($memory_limit_bytes * 0.9)) {
                // Don't log this to prevent recursion
                break;
            }
            
            $logs = \get_option("ctm_daily_log_{$date}", []);
            if (is_array($logs)) {
                // Use a more memory-efficient size calculation
                $size = $this->calculateArraySize($logs) / 1024 / 1024; // Size in MB
                $day_sizes[$date] = $size;
                $total_size += $size;
            }
        }
        
        // If total size exceeds limit, remove oldest days
        if ($total_size > $max_total_size_mb) {
            // Sort by date (oldest first)
            ksort($day_sizes);
            
            $cleaned_count = 0;
            $cleaned_size = 0;
            
            foreach ($day_sizes as $date => $size) {
                if ($total_size <= $max_total_size_mb) {
                    break;
                }
                
                // Delete this day's logs
                \delete_option("ctm_daily_log_{$date}");
                $total_size -= $size;
                $cleaned_count++;
                $cleaned_size += $size;
                
                // Remove from index
                $log_index = array_filter($log_index, function($d) use ($date) {
                    return $d !== $date;
                });
            }
            
            // Update log index
            \update_option('ctm_log_index', array_values($log_index));
            
            if ($cleaned_count > 0) {
                // Don't log this to prevent recursion
            }
        }
    }

    /**
     * Parse memory limit string to bytes
     * 
     * @param string $memory_limit Memory limit string (e.g., "256M", "1G")
     * @return int|null Memory limit in bytes or null if invalid
     */
    private function parseMemoryLimit(string $memory_limit): ?int
    {
        $memory_limit = trim($memory_limit);
        $last = strtolower($memory_limit[strlen($memory_limit) - 1]);
        $value = (int) $memory_limit;
        
        switch ($last) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }
        
        return $value;
    }
    
    /**
     * Calculate array size more efficiently without full serialization
     * 
     * @param array $array The array to calculate size for
     * @return int Approximate size in bytes
     */
    private function calculateArraySize(array $array): int
    {
        $size = 0;
        $count = 0;
        $max_count = 1000; // Limit to prevent memory issues
        
        foreach ($array as $key => $value) {
            $count++;
            if ($count > $max_count) {
                // Estimate remaining size based on average
                $avg_size = $size / $count;
                $remaining = count($array) - $count;
                $size += $avg_size * $remaining;
                break;
            }
            
            // Calculate key size
            $size += strlen($key);
            
            // Calculate value size
            if (is_string($value)) {
                $size += strlen($value);
            } elseif (is_array($value)) {
                $size += $this->calculateArraySize($value);
            } elseif (is_numeric($value)) {
                $size += 8; // Approximate size for numbers
            } elseif (is_bool($value)) {
                $size += 1;
            } else {
                $size += strlen((string) $value);
            }
        }
        
        return $size;
    }

    /**
     * Update the log index to track available log dates
     */
    private function updateLogIndex(string $log_date): void
    {
        $log_index = \get_option('ctm_log_index', []);
        if (!is_array($log_index)) {
            $log_index = [];
        }
        
        if (!in_array($log_date, $log_index)) {
            $log_index[] = $log_date;
            // Keep index sorted
            sort($log_index);
            \update_option('ctm_log_index', $log_index);
        }
    }

    /**
     * Get available log dates
     */
    public function getAvailableLogDates(): array
    {
        // Ensure table exists before querying
        if (!$this->ensureTableExists()) {
            return [];
        }
        
        global $wpdb;
        $table_name = $this->getLogTableName();
        
        $sql = "SELECT DISTINCT DATE(timestamp) as log_date 
                FROM $table_name 
                ORDER BY log_date DESC";
        
        $results = $wpdb->get_col($sql);
        
        return $results ?: [];
    }

    /**
     * Get logs for a specific date
     */
    public function getLogsForDate(string $date): array
    {
        // Ensure table exists before querying
        if (!$this->ensureTableExists()) {
            return [];
        }
        
        global $wpdb;
        $table_name = $this->getLogTableName();
        
        $sql = $wpdb->prepare(
            "SELECT * FROM $table_name 
             WHERE DATE(timestamp) = %s 
             ORDER BY timestamp DESC",
            $date
        );
        
        $results = $wpdb->get_results($sql, ARRAY_A);
        
        if (empty($results)) {
            return [];
        }
        
        // Process results to match expected format
        $logs = [];
        foreach ($results as $row) {
            $log_entry = [
                'timestamp' => $row['timestamp'],
                'type' => $row['type'],
                'message' => $row['message'],
                'context' => !empty($row['context']) ? json_decode($row['context'], true) : [],
                'user_id' => $row['user_id'],
                'ip_address' => $row['ip_address'],
                'user_agent' => $row['user_agent'],
                'memory_usage' => $row['memory_usage'],
                'memory_peak' => $row['memory_peak']
            ];
            $logs[] = $log_entry;
        }
        
        return $logs;
    }

    /**
     * Get grouped logs for a specific date
     * 
     * Groups logs by type and API call patterns for better organization
     * 
     * @since 2.0.0
     * @param string $date The date to get logs for
     * @return array Grouped logs with counts and examples
     */
    public function getGroupedLogsForDate(string $date): array
    {
        $logs = $this->getLogsForDate($date);
        
        if (empty($logs)) {
            return [];
        }
        
        $grouped = [];
        
        foreach ($logs as $log) {
            $group_key = $this->getLogGroupKey($log);
            
            if (!isset($grouped[$group_key])) {
                $grouped[$group_key] = [
                    'type' => $log['type'],
                    'group_key' => $group_key,
                    'count' => 0,
                    'examples' => [],
                    'first_seen' => $log['timestamp'],
                    'last_seen' => $log['timestamp'],
                    'total_logs' => []
                ];
            }
            
            $grouped[$group_key]['count']++;
            $grouped[$group_key]['last_seen'] = $log['timestamp'];
            $grouped[$group_key]['total_logs'][] = $log;
            
            // Keep up to 3 examples
            if (count($grouped[$group_key]['examples']) < 3) {
                $grouped[$group_key]['examples'][] = $log;
            }
        }
        
        // Sort by count (highest first) then by last seen (newest first)
        uasort($grouped, function($a, $b) {
            if ($a['count'] !== $b['count']) {
                return $b['count'] - $a['count'];
            }
            return strcmp($b['last_seen'], $a['last_seen']);
        });
        
        return $grouped;
    }

    /**
     * Generate a group key for log entries
     * 
     * Creates consistent grouping keys for similar log entries
     * 
     * @since 2.0.0
     * @param array $log The log entry
     * @return string A group key for this log entry
     */
    private function getLogGroupKey(array $log): string
    {
        $type = $log['type'];
        $message = $log['message'];
        $context = $log['context'] ?? [];
        
        // Special handling for API calls
        if ($type === 'api' && isset($context['api_call_key'])) {
            return 'api:' . $context['api_call_key'];
        }
        
        // For other types, group by message pattern
        if (strpos($message, 'API Request - URL:') === 0) {
            // Extract URL and method for API requests
            if (preg_match('/API Request - URL: ([^,]+), Method: (\w+)/', $message, $matches)) {
                $url = $matches[1];
                $method = $matches[2];
                
                // Normalize URL for grouping
                $parsed_url = parse_url($url);
                $path = $parsed_url['path'] ?? '';
                
                // Normalize common patterns
                $normalized_path = $this->normalizeApiPath($path);
                return 'api:' . strtoupper($method) . ':' . $normalized_path;
            }
        }
        
        // For other message types, group by first part of message
        $words = explode(' ', $message);
        $first_word = $words[0] ?? '';
        return $type . ':' . $first_word;
    }

    /**
     * Normalize API path for consistent grouping
     * 
     * @since 2.0.0
     * @param string $path The API path
     * @return string Normalized path
     */
    private function normalizeApiPath(string $path): string
    {
        // Remove trailing slash
        $path = rtrim($path, '/');
        
        // Normalize common patterns
        $patterns = [
            '/\d+/' => '{id}',           // Replace numeric IDs
            '/[a-f0-9-]{36}/' => '{uuid}', // Replace UUIDs
            '/[a-f0-9]{8,}/' => '{hash}',  // Replace hashes
        ];
        
        foreach ($patterns as $pattern => $replacement) {
            $path = preg_replace($pattern, $replacement, $path);
        }
        
        return $path;
    }

    /**
     * Clear logs for a specific date
     */
    public function clearDayLog(string $date): void
    {
        // Ensure table exists before clearing
        if (!$this->ensureTableExists()) {
            return;
        }
        
        global $wpdb;
        $table_name = $this->getLogTableName();
        
        $sql = $wpdb->prepare(
            "DELETE FROM $table_name WHERE DATE(timestamp) = %s",
            $date
        );
        
        $wpdb->query($sql);
        
        // Log the cleanup action
        $this->logActivity("Cleared logs for date: $date", 'system', [
            'cleared_date' => $date,
            'rows_affected' => $wpdb->rows_affected
        ]);
    }

    /**
     * Clear all logs
     */
    public function clearAllLogs(): void
    {
        // Ensure table exists before clearing
        if (!$this->ensureTableExists()) {
            return;
        }
        
        global $wpdb;
        $table_name = $this->getLogTableName();
        
        // Get count before deletion for logging
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        
        // Clear all logs
        $wpdb->query("DELETE FROM $table_name");
        
        // Log the cleanup action
        $this->logActivity("Cleared all logs", 'system', [
            'rows_affected' => $count
        ]);
    }
    
    /**
     * Clear all logs using a more aggressive approach when memory is limited
     */
    private function clearAllLogsAggressive(): void
    {
        global $wpdb;
        
        // Clear daily logs using direct SQL
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'ctm_daily_log_%'");
        
        // Clear form logs using direct SQL
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'ctm_form_log_%'");
        
        // Clear other log-related options
        \delete_option('ctm_log_index');
        \delete_option('ctm_debug_log');
        
        // Don't log this to prevent recursion
    }
    
    /**
     * Clear all form logs in batches
     */
    private function clearAllFormLogs(): void
    {
        global $wpdb;
        
        $memory_limit = ini_get('memory_limit');
        $memory_limit_bytes = $this->parseMemoryLimit($memory_limit);
        
        // Get all form log option names
        $form_log_options = $wpdb->get_col(
            "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE 'ctm_form_log_%'"
        );
        
        if (empty($form_log_options)) {
            return;
        }
        
        $count = 0;
        $max_per_batch = 20;
        
        foreach ($form_log_options as $option_name) {
            $count++;
            \delete_option($option_name);
            
            // Check memory usage every batch
            if ($count % $max_per_batch === 0) {
                if ($memory_limit_bytes && memory_get_usage(true) > ($memory_limit_bytes * 0.95)) {
                    // Don't log this to prevent recursion
                    break;
                }
            }
        }
    }

    /**
     * Email log for a specific date
     */
    public function emailLog(string $date, string $email_to): bool
    {
        $logs = $this->getLogsForDate($date);
        
        if (empty($logs)) {
            return false;
        }

        $site_name = \get_bloginfo('name');
        $subject = "CTM Debug Log for {$date} - {$site_name}";
        
        $message = "Debug log for {$date}\n";
        $message .= "Site: {$site_name}\n";
        $message .= "Generated: " . \current_time('mysql') . "\n";
        $message .= str_repeat('=', 60) . "\n\n";
        
        foreach ($logs as $entry) {
            $message .= "[{$entry['timestamp']}] [{$entry['type']}] {$entry['message']}\n";
            
            if (!empty($entry['context'])) {
                $message .= "Context: " . print_r($entry['context'], true) . "\n";
            }
            
            $message .= "User: " . ($entry['user_id'] ? \get_userdata($entry['user_id'])->user_login : 'Anonymous') . "\n";
            $message .= "IP: {$entry['ip_address']}\n";
            $message .= "Memory: " . \size_format($entry['memory_usage']) . " (Peak: " . \size_format($entry['memory_peak']) . ")\n";
            $message .= str_repeat('-', 40) . "\n\n";
        }

        $headers = [
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . \get_option('admin_email')
        ];

        return \wp_mail($email_to, $subject, $message, $headers);
    }

    /**
     * Clean up old logs based on retention settings
     */
    private function cleanupOldLogs(): void
    {
        // Ensure table exists before cleanup
        if (!$this->ensureTableExists()) {
            return;
        }
        
        $retention_days = (int) \get_option('ctm_log_retention_days', 7);
        $cutoff_date = date('Y-m-d', strtotime("-{$retention_days} days"));
        
        global $wpdb;
        $table_name = $this->getLogTableName();
        
        // Get count before deletion for logging
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE DATE(timestamp) < %s",
            $cutoff_date
        ));
        
        if ($count > 0) {
            // Delete old logs
            $wpdb->query($wpdb->prepare(
                "DELETE FROM $table_name WHERE DATE(timestamp) < %s",
                $cutoff_date
            ));
            
            // Log the cleanup action
            $this->logActivity("Cleaned up old logs", 'system', [
                'retention_days' => $retention_days,
                'cutoff_date' => $cutoff_date,
                'rows_deleted' => $count
            ]);
        }
    }

    /**
     * Get user IP address
     */
    private function getUserIP(): string
    {
        $ip_keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    }

    /**
     * Get log statistics
     */
    public function getLogStatistics(): array
    {
        $log_index = $this->getAvailableLogDates();
        $total_entries = 0;
        $total_size = 0;
        $type_counts = [];
        
        foreach ($log_index as $date) {
            $logs = $this->getLogsForDate($date);
            $total_entries += count($logs);
            $total_size += strlen(serialize($logs));
            
            foreach ($logs as $log) {
                $type = $log['type'] ?? 'unknown';
                $type_counts[$type] = ($type_counts[$type] ?? 0) + 1;
            }
        }
        
        return [
            'total_days' => count($log_index),
            'total_entries' => $total_entries,
            'total_size' => $total_size,
            'type_counts' => $type_counts,
            'oldest_log' => !empty($log_index) ? end($log_index) : null,
            'newest_log' => !empty($log_index) ? reset($log_index) : null
        ];
    }

    /**
     * Initialize the logging system (call this once from main plugin file)
     */
    public function initializeLoggingSystem(): void
    {
        if (!\wp_next_scheduled('ctm_daily_log_cleanup')) {
            \wp_schedule_event(time(), 'daily', 'ctm_daily_log_cleanup');
        }
        \add_action('ctm_daily_log_cleanup', [$this, 'performScheduledLogCleanup']);
    }

    /**
     * Handle plugin activation - create database table and set defaults
     */
    public static function onPluginActivation(): void
    {
        // Create the log entries table
        self::createLogTable();
        
        // Set default log settings if not already set
        if (\get_option('ctm_log_retention_days') === false) {
            \update_option('ctm_log_retention_days', 7);
        }
        
        if (\get_option('ctm_log_auto_cleanup') === false) {
            \update_option('ctm_log_auto_cleanup', true);
        }
        
        if (\get_option('ctm_log_email_notifications') === false) {
            \update_option('ctm_log_email_notifications', false);
        }
        
        if (\get_option('ctm_log_notification_email') === false) {
            \update_option('ctm_log_notification_email', \get_option('admin_email'));
        }
        
        // Initialize logging system
        self::initializeLoggingSystemStatic();
        
        // Log activation
        self::logActivityStatic('CTM Plugin activated', 'system', [
            'wp_version' => get_bloginfo('version'),
            'php_version' => PHP_VERSION,
            'memory_limit' => ini_get('memory_limit')
        ]);
    }

    /**
     * Create the log entries table
     */
    public static function createLogTable(): void
    {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ctm_log_entries';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            timestamp datetime NOT NULL,
            type varchar(20) NOT NULL DEFAULT 'info',
            message text NOT NULL,
            context longtext,
            user_id bigint(20) unsigned DEFAULT NULL,
            ip_address varchar(45) DEFAULT NULL,
            user_agent text DEFAULT NULL,
            memory_usage bigint(20) unsigned DEFAULT NULL,
            memory_peak bigint(20) unsigned DEFAULT NULL,
            form_type varchar(50) DEFAULT NULL,
            form_id bigint(20) unsigned DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_timestamp (timestamp),
            KEY idx_type (type),
            KEY idx_user_id (user_id),
            KEY idx_form_type_id (form_type, form_id),
            KEY idx_created_at (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Add version option to track table schema
        \update_option('ctm_log_table_version', '1.0');
    }

    /**
     * Get the log entries table name
     */
    private function getLogTableName(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'ctm_log_entries';
    }

    /**
     * Ensure the log table exists before performing operations
     */
    private function ensureTableExists(): bool
    {
        global $wpdb;
        
        $table_name = $this->getLogTableName();
        
        // Check if table exists
        $table_exists = $wpdb->get_var($wpdb->prepare(
            "SHOW TABLES LIKE %s",
            $table_name
        )) === $table_name;
        
        if (!$table_exists) {
            // Create the table
            self::createLogTable();
            
            // Check again if it was created successfully
            $table_exists = $wpdb->get_var($wpdb->prepare(
                "SHOW TABLES LIKE %s",
                $table_name
            )) === $table_name;
        }
        
        return $table_exists;
    }

    /**
     * Handle plugin deactivation - clean up scheduled tasks
     */
    public static function onPluginDeactivation(): void
    {
        // Remove scheduled cleanup
        \wp_clear_scheduled_hook('ctm_daily_log_cleanup');
        
        // Log plugin deactivation
        self::logActivityStatic('CTM Plugin deactivated', 'system');
    }

    /**
     * Static version of initializeLoggingSystem for activation hook
     */
    private static function initializeLoggingSystemStatic(): void
    {
        if (!\wp_next_scheduled('ctm_daily_log_cleanup')) {
            \wp_schedule_event(time(), 'daily', 'ctm_daily_log_cleanup');
        }
    }

    /**
     * Static version of logActivity for activation hook
     * Uses database logging instead of file-based logging
     */
    private static function logActivityStatic(string $message, string $type = 'info', array $context = []): void
    {
        // Check if debug mode is enabled
        if (!(bool) \get_option('ctm_debug_enabled', false)) {
            return; // Only log when debug mode is enabled
        }

        $log_entry = [
            'timestamp' => \current_time('mysql'),
            'type' => $type,
            'message' => $message,
            'context' => $context,
            'user_id' => \get_current_user_id(),
            'ip_address' => self::getUserIPStatic(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true)
        ];

        // Ensure all required keys are present to prevent undefined array key warnings
        $required_keys = ['timestamp', 'type', 'message', 'context', 'user_id', 'ip_address', 'user_agent', 'memory_usage', 'memory_peak'];
        foreach ($required_keys as $key) {
            if (!array_key_exists($key, $log_entry)) {
                $log_entry[$key] = null;
            }
        }

        // Use database logging instead of file-based logging
        $instance = new self();
        $instance->writeToLog($log_entry);
    }

    /**
     * Static version of getUserIP for activation hook
     */
    private static function getUserIPStatic(): string
    {
        $ip_keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    }

    /**
     * Perform scheduled log cleanup
     */
    public function performScheduledLogCleanup(): void
    {
        $this->performInstanceLogCleanup();
    }

    /**
     * Instance method for log cleanup
     */
    protected function performInstanceLogCleanup(): void
    {
        if (!\get_option('ctm_log_auto_cleanup', true)) {
            return;
        }

        $retention_days = (int) \get_option('ctm_log_retention_days', 7);
        $cutoff_date = date('Y-m-d', strtotime("-{$retention_days} days"));
        
        $log_index = \get_option('ctm_log_index', []);
        if (!is_array($log_index)) {
            return;
        }

        $cleaned_count = 0;
        $cleaned_size = 0;
        
        foreach ($log_index as $key => $date) {
            if ($date < $cutoff_date) {
                $log_data = \get_option("ctm_daily_log_{$date}", []);
                $cleaned_size += strlen(serialize($log_data));
                
                \delete_option("ctm_daily_log_{$date}");
                unset($log_index[$key]);
                $cleaned_count++;
            }
        }

        if ($cleaned_count > 0) {
            \update_option('ctm_log_index', array_values($log_index));
            
            // Log cleanup activity
            $this->logActivity('Automatic log cleanup completed', 'system', [
                'cleaned_days' => $cleaned_count,
                'cleaned_size' => \size_format($cleaned_size),
                'retention_days' => $retention_days,
                'cutoff_date' => $cutoff_date
            ]);
            
            // Send email notification if enabled
            if (\get_option('ctm_log_email_notifications', false)) {
                $this->sendCleanupNotification($cleaned_count, $cleaned_size, $retention_days);
            }
        }
    }

    /**
     * Send email notification about log cleanup
     */
    private function sendCleanupNotification(int $cleaned_count, int $cleaned_size, int $retention_days): void
    {
        $notification_email = \get_option('ctm_log_notification_email', \get_option('admin_email'));
        if (empty($notification_email)) {
            return;
        }

        $site_name = \get_bloginfo('name');
        $subject = "CTM Log Cleanup Report - {$site_name}";
        
        $message = "Call Tracking Metrics Plugin - Log Cleanup Report\n\n";
        $message .= "Site: {$site_name}\n";
        $message .= "Date: " . \current_time('Y-m-d H:i:s') . "\n";
        $message .= str_repeat('=', 50) . "\n\n";
        
        $message .= "Cleanup Summary:\n";
        $message .= "- Days cleaned: {$cleaned_count}\n";
        $message .= "- Data cleaned: " . \size_format($cleaned_size) . "\n";
        $message .= "- Retention period: {$retention_days} days\n\n";
        
        $remaining_stats = $this->getLogStatistics();
        $message .= "Remaining Logs:\n";
        $message .= "- Total days: {$remaining_stats['total_days']}\n";
        $message .= "- Total entries: " . number_format($remaining_stats['total_entries']) . "\n";
        $message .= "- Storage size: " . \size_format($remaining_stats['total_size']) . "\n\n";
        
        $message .= "This is an automated notification from the CTM plugin.\n";
        $message .= "You can adjust log settings in the WordPress admin panel.\n";

        $headers = [
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . \get_option('admin_email')
        ];

        \wp_mail($notification_email, $subject, $message, $headers);
    }

    /**
     * Legacy debug logging method for backwards compatibility
     */
    public function logDebug($message): void
    {
        $this->logActivity(
            is_string($message) ? $message : print_r($message, true),
            'debug'
        );
    }

    /**
     * Log form submission activity with form-specific tracking
     * 
     * @since 2.0.0
     * @param string $form_type The form type (cf7, gf)
     * @param int $form_id The WordPress form ID
     * @param string $form_title The form title
     * @param array $payload The submission payload
     * @param array $response The API response
     * @param array $additional_context Additional context data
     */
    public function logFormSubmission(string $form_type, int $form_id, string $form_title, array $payload, array $response = [], array $additional_context = []): void
    {
        if (!self::isDebugEnabled()) {
            return;
        }

        $log_entry = [
            'timestamp' => \current_time('mysql'),
            'type' => 'form_submission',
            'form_type' => $form_type,
            'form_id' => $form_id,
            'form_title' => $form_title,
            'message' => "Form submission processed for {$form_title} (ID: {$form_id})",
            'payload' => $payload,
            'response' => $response,
            'user_id' => \get_current_user_id(),
            'ip_address' => $this->getUserIP(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
            'context' => $additional_context
        ];

        // Ensure all required keys are present to prevent undefined array key warnings
        $required_keys = ['timestamp', 'type', 'message', 'context', 'user_id', 'ip_address', 'user_agent', 'memory_usage', 'memory_peak', 'form_type', 'form_id'];
        foreach ($required_keys as $key) {
            if (!array_key_exists($key, $log_entry)) {
                $log_entry[$key] = null;
            }
        }

        $this->writeToLog($log_entry);
        
        // Also store in form-specific log
        $this->writeToFormSpecificLog($form_type, $form_id, $log_entry);
    }

    /**
     * Write log entry to form-specific log storage
     * 
     * @since 2.0.0
     * @param string $form_type The form type (cf7, gf)
     * @param int $form_id The WordPress form ID
     * @param array $log_entry The log entry data
     */
    private function writeToFormSpecificLog(string $form_type, int $form_id, array $log_entry): void
    {
        $form_log_key = "ctm_form_log_{$form_type}_{$form_id}";
        $form_logs = \get_option($form_log_key, []);
        
        if (!is_array($form_logs)) {
            $form_logs = [];
        }
        
        $form_logs[] = $log_entry;
        
        // Enforce form log size limits
        $this->enforceFormLogSizeLimits($form_log_key, $form_logs);
        
        \update_option($form_log_key, $form_logs);
    }

    /**
     * Enforce form log size limits
     * 
     * @since 2.0.0
     * @param string $form_log_key The option key for the form logs
     * @param array $form_logs The form logs array (passed by reference)
     */
    private function enforceFormLogSizeLimits(string $form_log_key, array &$form_logs): void
    {
        // Get form log limits from options
        $max_form_entries = (int) \get_option('ctm_max_form_log_entries', 150);
        $max_form_size_mb = (float) \get_option('ctm_max_form_log_size_mb', 2.0);
        
        // Limit entries per form
        if (count($form_logs) > $max_form_entries) {
            $form_logs = array_slice($form_logs, -$max_form_entries);
        }
        
        // Check form log size
        $form_size_mb = strlen(serialize($form_logs)) / 1024 / 1024;
        if ($form_size_mb > $max_form_size_mb) {
            // Remove oldest entries until under limit
            while (count($form_logs) > 25 && $form_size_mb > $max_form_size_mb) {
                array_shift($form_logs);
                $form_size_mb = strlen(serialize($form_logs)) / 1024 / 1024;
            }
        }
    }

    /**
     * Get form-specific logs
     * 
     * @since 2.0.0
     * @param string $form_type The form type (cf7, gf)
     * @param int $form_id The WordPress form ID
     * @return array Array of log entries for the specific form
     */
    public function getFormLogs(string $form_type, int $form_id): array
    {
        $form_log_key = "ctm_form_log_{$form_type}_{$form_id}";
        $form_logs = \get_option($form_log_key, []);
        return is_array($form_logs) ? $form_logs : [];
    }

    /**
     * Clear form-specific logs
     * 
     * @since 2.0.0
     * @param string $form_type The form type (cf7, gf)
     * @param int $form_id The WordPress form ID
     */
    public function clearFormLogs(string $form_type, int $form_id): void
    {
        $form_log_key = "ctm_form_log_{$form_type}_{$form_id}";
        \delete_option($form_log_key);
        
        // Log the clear action
        $this->logActivity("Form logs cleared for {$form_type} form ID {$form_id}", 'system');
    }

    /**
     * Get all form logs for a specific form type
     * 
     * @since 2.0.0
     * @param string $form_type The form type (cf7, gf)
     * @return array Array of all form logs for the type
     */
    public function getAllFormLogs(string $form_type): array
    {
        global $wpdb;
        
        // Check memory usage before processing
        $memory_limit = ini_get('memory_limit');
        $current_memory = memory_get_usage(true);
        $memory_limit_bytes = $this->parseMemoryLimit($memory_limit);
        
        // If we're using more than 80% of available memory, return empty array
        if ($memory_limit_bytes && ($current_memory / $memory_limit_bytes) > 0.8) {
            // Don't log this to prevent recursion
            return [];
        }
        
        $form_logs = [];
        $pattern = "ctm_form_log_{$form_type}_%";
        
        $options = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE %s",
                $pattern
            )
        );
        
        $count = 0;
        $max_forms = 50; // Limit number of forms to process
        
        foreach ($options as $option) {
            $count++;
            if ($count > $max_forms) {
                // Don't log this to prevent recursion
                break;
            }
            
            // Check memory usage before processing each form
            if ($memory_limit_bytes && memory_get_usage(true) > ($memory_limit_bytes * 0.9)) {
                // Don't log this to prevent recursion
                break;
            }
            
            $form_id = str_replace("ctm_form_log_{$form_type}_", '', $option->option_name);
            $logs = maybe_unserialize($option->option_value);
            if (is_array($logs)) {
                $form_logs[$form_id] = $logs;
            }
        }
        
        return $form_logs;
    }

    /**
     * Get form log statistics
     * 
     * @since 2.0.0
     * @return array Statistics about form logs
     */
    public function getFormLogStatistics(): array
    {
        $cf7_logs = $this->getAllFormLogs('cf7');
        $gf_logs = $this->getAllFormLogs('gf');
        
        $cf7_count = 0;
        $gf_count = 0;
        
        foreach ($cf7_logs as $form_logs) {
            $cf7_count += count($form_logs);
        }
        
        foreach ($gf_logs as $form_logs) {
            $gf_count += count($form_logs);
        }
        
        return [
            'cf7' => [
                'total_forms' => count($cf7_logs),
                'total_entries' => $cf7_count
            ],
            'gf' => [
                'total_forms' => count($gf_logs),
                'total_entries' => $gf_count
            ]
        ];
    }

    /**
     * Get recent error logs for display
     * 
     * @since 2.0.0
     * @param int $limit Maximum number of errors to return
     * @return array Array of recent error logs
     */
    public function getRecentErrors(int $limit = 10): array
    {
        $errors = [];
        $log_index = \get_option('ctm_log_index', []);
        
        if (!is_array($log_index)) {
            return $errors;
        }
        
        // Get logs from the last 7 days
        $recent_dates = array_slice(array_reverse($log_index), 0, 7);
        
        foreach ($recent_dates as $date) {
            $logs = \get_option("ctm_daily_log_{$date}", []);
            if (is_array($logs)) {
                foreach ($logs as $log_entry) {
                    if (isset($log_entry['type']) && $log_entry['type'] === 'error') {
                        $errors[] = [
                            'date' => $date,
                            'timestamp' => $log_entry['timestamp'] ?? '',
                            'message' => $log_entry['message'] ?? '',
                            'context' => $log_entry['context'] ?? [],
                            'user_id' => $log_entry['user_id'] ?? 0,
                            'ip_address' => $log_entry['ip_address'] ?? '',
                            'memory_usage' => $log_entry['memory_usage'] ?? 0
                        ];
                        
                        if (count($errors) >= $limit) {
                            break 2;
                        }
                    }
                }
            }
        }
        
        return $errors;
    }

    /**
     * Get error rate statistics
     * 
     * @since 2.0.0
     * @return array Error rate statistics
     */
    public function getErrorRateStats(): array
    {
        $log_index = \get_option('ctm_log_index', []);
        $total_logs = 0;
        $error_logs = 0;
        
        if (!is_array($log_index)) {
            return ['error_rate' => 0, 'total_logs' => 0, 'error_logs' => 0];
        }
        
        // Get logs from the last 7 days
        $recent_dates = array_slice(array_reverse($log_index), 0, 7);
        
        foreach ($recent_dates as $date) {
            $logs = \get_option("ctm_daily_log_{$date}", []);
            if (is_array($logs)) {
                $total_logs += count($logs);
                foreach ($logs as $log_entry) {
                    if (isset($log_entry['type']) && $log_entry['type'] === 'error') {
                        $error_logs++;
                    }
                }
            }
        }
        
        $error_rate = $total_logs > 0 ? ($error_logs / $total_logs) * 100 : 0;
        
        return [
            'error_rate' => round($error_rate, 2),
            'total_logs' => $total_logs,
            'error_logs' => $error_logs
        ];
    }
} 