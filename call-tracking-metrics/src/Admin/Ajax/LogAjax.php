<?php
/**
 * Log AJAX Handler
 * 
 * This file contains the LogAjax class which handles AJAX requests related to
 * logging operations including log retrieval, clearing logs, and log management.
 * 
 * @package     CallTrackingMetrics
 * @subpackage  Admin\Ajax
 * @author      CallTrackingMetrics Team
 * @copyright   2024 CallTrackingMetrics
 * @license     GPL-2.0+
 * @version     2.0.0
 * @link        https://calltrackingmetrics.com
 * @since       1.0.0
 */

namespace CTM\Admin\Ajax;

use CTM\Admin\LoggingSystem;

class LogAjax {
    private LoggingSystem $loggingSystem;

    public function __construct(LoggingSystem $loggingSystem) {
        $this->loggingSystem = $loggingSystem;
    }

    public function registerHandlers() {
        add_action('wp_ajax_ctm_get_form_logs', [$this, 'ajaxGetFormLogs']);
        add_action('wp_ajax_ctm_clear_form_logs', [$this, 'ajaxClearFormLogs']);
        add_action('wp_ajax_ctm_get_form_log_stats', [$this, 'ajaxGetFormLogStats']);
        add_action('wp_ajax_ctm_clear_all_logs', [$this, 'ajaxClearAllLogs']);
        add_action('wp_ajax_ctm_get_recent_errors', [$this, 'ajaxGetRecentErrors']);
        add_action('wp_ajax_ctm_get_error_rate_stats', [$this, 'ajaxGetErrorRateStats']);
        add_action('wp_ajax_ctm_update_log_settings', [$this, 'ajaxUpdateLogSettings']);
        add_action('wp_ajax_ctm_load_more_group_entries', [$this, 'ajaxLoadMoreGroupEntries']);
    }

    /**
     * Get form-specific logs
     * 
     * @since 2.0.0
     */
    public function ajaxGetFormLogs(): void
    {
        check_ajax_referer('ctm_form_logs', 'nonce');
        
        $form_type = sanitize_text_field($_POST['form_type'] ?? '');
        $form_id = (int) ($_POST['form_id'] ?? 0);
        
        if (empty($form_type) || empty($form_id)) {
            wp_send_json_error(['message' => 'Form type and form ID are required']);
        }
        
        try {
            $logs = $this->loggingSystem->getFormLogs($form_type, $form_id);
            wp_send_json_success([
                'logs' => $logs,
                'count' => count($logs)
            ]);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => 'Failed to get form logs: ' . $e->getMessage()]);
        }
    }

    /**
     * Clear form-specific logs
     * 
     * @since 2.0.0
     */
    public function ajaxClearFormLogs(): void
    {
        check_ajax_referer('ctm_form_logs', 'nonce');
        
        $form_type = sanitize_text_field($_POST['form_type'] ?? '');
        $form_id = (int) ($_POST['form_id'] ?? 0);
        
        if (empty($form_type) || empty($form_id)) {
            wp_send_json_error(['message' => 'Form type and form ID are required']);
        }
        
        try {
            $this->loggingSystem->clearFormLogs($form_type, $form_id);
            wp_send_json_success(['message' => 'Form logs cleared successfully']);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => 'Failed to clear form logs: ' . $e->getMessage()]);
        }
    }

    /**
     * Get form log statistics
     * 
     * @since 2.0.0
     */
    public function ajaxGetFormLogStats(): void
    {
        check_ajax_referer('ctm_form_logs', 'nonce');
        
        try {
            $stats = $this->loggingSystem->getFormLogStatistics();
            wp_send_json_success($stats);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => 'Failed to get form log statistics: ' . $e->getMessage()]);
        }
    }

    /**
     * Clear all logs (daily logs, form logs, and log history)
     * 
     * @since 2.0.0
     */
    public function ajaxClearAllLogs(): void
    {
        check_ajax_referer('ctm_clear_all_logs', 'nonce');
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions to clear all logs']);
        }
        
        try {
            // Log the action before clearing (so it gets recorded)
            $this->loggingSystem->logActivity('All logs cleared via AJAX request', 'system');
            
            // Clear all logs
            $this->loggingSystem->clearAllLogs();
            
            wp_send_json_success([
                'message' => 'All logs cleared successfully',
                'timestamp' => current_time('mysql')
            ]);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => 'Failed to clear all logs: ' . $e->getMessage()]);
        }
    }

    /**
     * Get recent error logs for display
     * 
     * @since 2.0.0
     */
    public function ajaxGetRecentErrors(): void
    {
        check_ajax_referer('ctm_form_logs', 'nonce');
        
        try {
            $limit = (int) ($_POST['limit'] ?? 10);
            $errors = $this->loggingSystem->getRecentErrors($limit);
            
            wp_send_json_success([
                'errors' => $errors,
                'count' => count($errors)
            ]);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => 'Failed to get recent errors: ' . $e->getMessage()]);
        }
    }

    /**
     * Get error rate statistics
     * 
     * @since 2.0.0
     */
    public function ajaxGetErrorRateStats(): void
    {
        check_ajax_referer('ctm_form_logs', 'nonce');
        
        try {
            $stats = $this->loggingSystem->getErrorRateStats();
            wp_send_json_success($stats);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => 'Failed to get error rate stats: ' . $e->getMessage()]);
        }
    }

    /**
     * Update log settings
     * 
     * @since 2.0.0
     */
    public function ajaxUpdateLogSettings(): void
    {
        check_ajax_referer('ctm_update_log_settings', 'nonce');
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions to update log settings']);
        }
        
        try {
            // Get form data
            $retention_days = (int) ($_POST['log_retention_days'] ?? 30);
            $auto_cleanup = (bool) ($_POST['log_auto_cleanup'] ?? false);
            
            // Validate retention days
            if ($retention_days < 1 || $retention_days > 365) {
                wp_send_json_error(['message' => 'Retention days must be between 1 and 365']);
            }
            
            // Update WordPress options
            update_option('ctm_log_retention_days', $retention_days);
            update_option('ctm_log_auto_cleanup', $auto_cleanup);
            
            // Log the settings update
            $this->loggingSystem->logActivity('Log settings updated via AJAX', 'system');
            
            wp_send_json_success([
                'message' => 'Log settings updated successfully',
                'settings' => [
                    'retention_days' => $retention_days,
                    'auto_cleanup' => $auto_cleanup
                ]
            ]);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => 'Failed to update log settings: ' . $e->getMessage()]);
        }
    }

    /**
     * Load more entries for a specific group
     * 
     * @since 2.0.0
     */
    public function ajaxLoadMoreGroupEntries(): void
    {
        check_ajax_referer('ctm_load_more_group_entries', 'nonce');
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions to load more entries']);
        }
        
        try {
            $date = sanitize_text_field($_POST['date'] ?? '');
            $group_key = sanitize_text_field($_POST['group_key'] ?? '');
            $offset = (int) ($_POST['offset'] ?? 0);
            $limit = (int) ($_POST['limit'] ?? 10);
            
            if (empty($date) || empty($group_key)) {
                wp_send_json_error(['message' => 'Date and group key are required']);
            }
            
            // Get all logs for the date
            $all_logs = $this->loggingSystem->getLogsForDate($date);
            
            // Filter logs by group key
            $grouped_logs = [];
            foreach ($all_logs as $log) {
                $log_group_key = $this->getLogGroupKey($log);
                if ($log_group_key === $group_key) {
                    $grouped_logs[] = $log;
                }
            }
            
            // Get the requested slice of entries
            $requested_entries = array_slice($grouped_logs, $offset, $limit);
            
            wp_send_json_success([
                'entries' => $requested_entries,
                'offset' => $offset,
                'limit' => $limit,
                'total_in_group' => count($grouped_logs),
                'has_more' => ($offset + $limit) < count($grouped_logs)
            ]);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => 'Failed to load more entries: ' . $e->getMessage()]);
        }
    }

    /**
     * Generate a group key for log entries (copied from LoggingSystem for consistency)
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
     * Normalize API path for consistent grouping (copied from LoggingSystem for consistency)
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
} 