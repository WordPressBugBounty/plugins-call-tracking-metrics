<?php
/**
 * System AJAX Handler
 * 
 * This file contains the SystemAjax class which handles AJAX requests related to
 * system diagnostics, health checks, performance monitoring, and system information.
 * 
 * @package     CallTrackingMetrics
 * @subpackage  Admin\Ajax
 * @author      CallTrackingMetrics Team
 * @copyright   2024 CallTrackingMetrics
 * @license     GPL-2.0+
 * @version     2.0.0
 * @link        https://calltrackingmetrics.com
 * @since       2.0.0
 */

namespace CTM\Admin\Ajax;

use CTM\Admin\LoggingSystem;

class SystemAjax {

    private $loggingSystem;

    public function __construct(LoggingSystem $loggingSystem)
    {
        $this->loggingSystem = $loggingSystem;
    }

    public function registerHandlers() {
        add_action('wp_ajax_ctm_health_check', [$this, 'ajaxHealthCheck']);
        add_action('wp_ajax_ctm_analyze_issue', [$this, 'ajaxAnalyzeIssue']);
        add_action('wp_ajax_ctm_email_system_info', [$this, 'ajaxEmailSystemInfo']);
        add_action('wp_ajax_ctm_refresh_system_info', [$this, 'ajaxRefreshSystemInfo']);
        add_action('wp_ajax_ctm_auto_fix_issues', [$this, 'ajaxAutoFixIssues']);
        add_action('wp_ajax_ctm_full_diagnostic', [$this, 'ajaxFullDiagnostic']);
        add_action('wp_ajax_ctm_security_scan', [$this, 'ajaxSecurityScan']);
        add_action('wp_ajax_ctm_performance_analysis', [$this, 'ajaxPerformanceAnalysis']);
        add_action('wp_ajax_ctm_export_diagnostic_report', [$this, 'ajaxExportDiagnosticReport']);
    }


    /**
     * AJAX: Security Vulnerability Scan
     */
    public function ajaxSecurityScan(): void
    {
        \check_ajax_referer('ctm_security_scan', 'nonce');
        $score = 100;
        $vulnerabilities = [];
        $recommendations = [];
        $details = [];

        // 1. Security Headers
        $headers = [
            'Strict-Transport-Security',
            'X-Frame-Options',
            'X-Content-Type-Options',
            'Referrer-Policy',
            'Content-Security-Policy',
            'Permissions-Policy',
        ];
        $missing_headers = [];
        foreach ($headers as $header) {
            if (!array_key_exists($header, \headers_list())) {
                $missing_headers[] = $header;
            }
        }
        if (!empty($missing_headers)) {
            $score -= 10;
            $vulnerabilities[] = [
                'title' => 'Missing security headers',
                'description' => implode(', ', $missing_headers),
                'severity' => 'high'
            ];
            $recommendations[] = 'Add recommended security headers to your web server configuration.';
            $details['missing_headers'] = $missing_headers;
        }

        // 2. File Permissions (wp-config.php, .htaccess, uploads)
        $wp_config = ABSPATH . 'wp-config.php';
        $htaccess = ABSPATH . '.htaccess';
        $uploads = \wp_get_upload_dir()['basedir'];
        if (\file_exists($wp_config) && substr(sprintf('%o', \fileperms($wp_config)), -3) > 644) {
            $score -= 10;
            $vulnerabilities[] = [
                'title' => 'wp-config.php permissions are too loose',
                'description' => 'wp-config.php permissions should be 640 or 600.',
                'severity' => 'medium'
            ];
            $recommendations[] = 'Set wp-config.php permissions to 640 or 600.';
        }
        if (\file_exists($htaccess) && substr(sprintf('%o', \fileperms($htaccess)), -3) > 644) {
            $score -= 5;
            $vulnerabilities[] = [
                'title' => '.htaccess permissions are too loose',
                'description' => '.htaccess permissions should be 644.',
                'severity' => 'medium'
            ];
            $recommendations[] = 'Set .htaccess permissions to 644.';
        }
        if (\is_dir($uploads) && substr(sprintf('%o', \fileperms($uploads)), -3) > 755) {
            $score -= 5;
            $vulnerabilities[] = [
                'title' => 'Uploads directory permissions are too loose',
                'description' => 'Uploads directory permissions should be 755.',
                'severity' => 'medium'
            ];
            $recommendations[] = 'Set uploads directory permissions to 755.';
        }

        $wp_config_content = \file_exists($wp_config) ? \file_get_contents($wp_config) : '';
        if ($wp_config_content && strpos($wp_config_content, 'DISALLOW_FILE_EDIT') === false) {
            $score -= 5;
            $vulnerabilities[] = [
                'title' => 'DISALLOW_FILE_EDIT is not set',
                'description' => 'Add define(\'DISALLOW_FILE_EDIT\', true); to wp-config.php.',
                'severity' => 'medium'
            ];
            $recommendations[] = 'Add define(\'DISALLOW_FILE_EDIT\', true); to wp-config.php.';
        }
        if ($wp_config_content && strpos($wp_config_content, 'FORCE_SSL_ADMIN') === false) {
            $score -= 5;
            $vulnerabilities[] = [
                'title' => 'FORCE_SSL_ADMIN is not set',
                'description' => 'Add define(\'FORCE_SSL_ADMIN\', true); to wp-config.php.',
                'severity' => 'medium'
            ];
            $recommendations[] = 'Add define(\'FORCE_SSL_ADMIN\', true); to wp-config.php.';
        }

        $plugins = \get_plugins();
        foreach ($plugins as $plugin_file => $plugin_data) {
            // Check for known vulnerable plugins (example: hardcoded, real implementation would use an API)
            $vuln_plugins = [
                'hello.php' => 'Hello Dolly (example)',
            ];
            if (isset($vuln_plugins[$plugin_file])) {
                $score -= 20;
                $vulnerabilities[] = [
                    'title' => 'Vulnerable plugin detected',
                    'description' => $vuln_plugins[$plugin_file],
                    'severity' => 'high'
                ];
                $recommendations[] = 'Deactivate or remove vulnerable plugins.';
            }
        }

        // Clamp score
        $score = max(0, min(100, $score));

        \wp_send_json_success([
            'results' => [
                'security_score' => $score,
                'vulnerabilities' => $vulnerabilities,
                'recommendations' => $recommendations,
                'details' => $details
            ]
        ]);
    }


    /**
     * AJAX: Performance Analysis
     */
    public function ajaxPerformanceAnalysis(): void
    {
        \check_ajax_referer('ctm_performance_analysis', 'nonce');
        global $wpdb;
        $metrics = [];
        $optimizations = [];
        $score = 100;

        // 1. Page Load Time (approximate)
        $metrics['load_time'] = isset($_SERVER['REQUEST_TIME_FLOAT']) ? round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2) : null;
        if ($metrics['load_time'] > 2000) {
            $score -= 20;
            $optimizations[] = 'Optimize page load time (reduce to under 2s).';
        }

        // 2. Database Queries
        $metrics['db_queries'] = isset($wpdb->num_queries) ? $wpdb->num_queries : \get_num_queries();
        if ($metrics['db_queries'] > 100) {
            $score -= 10;
            $optimizations[] = 'Reduce the number of database queries.';
        }

        // 3. Memory Usage
        $metrics['memory_usage'] = round(\memory_get_usage(true) / 1024 / 1024, 2); // MB
        if ($metrics['memory_usage'] > 128) {
            $score -= 10;
            $optimizations[] = 'Optimize memory usage (keep under 128MB if possible).';
        }

        // 4. Cache Hit Rate (basic, if available)
        $cache_hit_rate = null;
        if (\function_exists('wp_cache_get_stats')) {
            $stats = \wp_cache_get_stats();
            if (isset($stats['hits']) && isset($stats['misses'])) {
                $total = $stats['hits'] + $stats['misses'];
                $cache_hit_rate = $total > 0 ? round($stats['hits'] / $total * 100, 1) : null;
            }
        }
        $metrics['cache_hit_rate'] = ($cache_hit_rate !== null) ? $cache_hit_rate : 'N/A';
        if (\is_numeric($cache_hit_rate) && $cache_hit_rate < 80) {
            $score -= 10;
            $optimizations[] = 'Improve cache hit rate (target 80%+).';
        }

        // 5. Plugin Load Time
        $metrics['plugin_load_time'] = method_exists($this, 'calculatePluginLoadTime') ? $this->calculatePluginLoadTime() : 'N/A';
        $plugin_load_time_val = is_numeric($metrics['plugin_load_time']) ? $metrics['plugin_load_time'] : (float) filter_var($metrics['plugin_load_time'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        if (is_numeric($plugin_load_time_val) && $plugin_load_time_val > 500) {
            $score -= 10;
            $optimizations[] = 'Reduce plugin load time.';
        }

        // 6. Theme Load Time
        $metrics['theme_load_time'] = method_exists($this, 'calculateThemeLoadTime') ? $this->calculateThemeLoadTime() : 'N/A';
        $theme_load_time_val = is_numeric($metrics['theme_load_time']) ? $metrics['theme_load_time'] : (float) filter_var($metrics['theme_load_time'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        if (is_numeric($theme_load_time_val) && $theme_load_time_val > 500) {
            $score -= 10;
            $optimizations[] = 'Reduce theme load time.';
        }

        // Clamp score
        $score = max(0, min(100, $score));

        wp_send_json_success([
            'results' => [
                'performance_score' => $score,
                'metrics' => $metrics,
                'optimizations' => $optimizations
            ]
        ]);
    }


    /**
     * Helper for export: run full diagnostic and return array (not JSON)
     */
    private function runFullDiagnosticForExport(): ?array
    {
        try {
            $diagnostic_results = [
                'passed_checks' => 0,
                'warning_checks' => 0,
                'failed_checks' => 0,
                'critical_issues' => [],
                'categories' => []
            ];
            $api_analysis = $this->analyzeApiCredentials();
            $api_score = isset($api_analysis['score']) && is_numeric($api_analysis['score']) ? $api_analysis['score'] : 0;
            $diagnostic_results['categories']['api_credentials'] = [
                'title' => 'API Credentials',
                'description' => 'Validation of CallTrackingMetrics API connectivity and authentication',
                'status' => isset($api_analysis['status']) ? $api_analysis['status'] : 'unknown',
                'score' => $api_score,
                'issues' => $api_analysis['issues'] ?? [],
                'recommendations' => $api_analysis['recommendations'] ?? []
            ];
            if ((isset($api_analysis['status']) ? $api_analysis['status'] : 'unknown') === 'healthy') {
                $diagnostic_results['passed_checks']++;
            } elseif ((isset($api_analysis['status']) ? $api_analysis['status'] : 'unknown') === 'warning') {
                $diagnostic_results['warning_checks']++;
            } else {
                $diagnostic_results['failed_checks']++;
                if ($api_score < 30) {
                    $diagnostic_results['critical_issues'][] = [
                        'title' => 'API Credentials Failed',
                        'description' => 'Cannot connect to CallTrackingMetrics API. Plugin functionality will be severely limited.',
                        'auto_fix_available' => false,
                        'fix_id' => 'api_credentials'
                    ];
                }
            }
            $form_analysis = $this->analyzeFormIntegration();
            $form_score = isset($form_analysis['score']) && is_numeric($form_analysis['score']) ? $form_analysis['score'] : 0;
            $diagnostic_results['categories']['form_integration'] = [
                'title' => 'Form Integration',
                'description' => 'Analysis of Contact Form 7 and Gravity Forms integration status',
                'status' => isset($form_analysis['status']) ? $form_analysis['status'] : 'unknown',
                'score' => $form_score,
                'issues' => $form_analysis['issues'] ?? [],
                'recommendations' => $form_analysis['recommendations'] ?? []
            ];
            if ((isset($form_analysis['status']) ? $form_analysis['status'] : 'unknown') === 'healthy') {
                $diagnostic_results['passed_checks']++;
            } elseif ((isset($form_analysis['status']) ? $form_analysis['status'] : 'unknown') === 'warning') {
                $diagnostic_results['warning_checks']++;
            } else {
                $diagnostic_results['failed_checks']++;
            }
            $network_analysis = $this->analyzeNetworkConnectivity();
            $network_score = isset($network_analysis['score']) && is_numeric($network_analysis['score']) ? $network_analysis['score'] : 0;
            $diagnostic_results['categories']['network_connectivity'] = [
                'title' => 'Network Connectivity',
                'description' => 'Testing network connectivity and DNS resolution for CTM services',
                'status' => isset($network_analysis['status']) ? $network_analysis['status'] : 'unknown',
                'score' => $network_score,
                'issues' => $network_analysis['issues'] ?? [],
                'recommendations' => $network_analysis['recommendations'] ?? []
            ];
            if ((isset($network_analysis['status']) ? $network_analysis['status'] : 'unknown') === 'healthy') {
                $diagnostic_results['passed_checks']++;
            } elseif ((isset($network_analysis['status']) ? $network_analysis['status'] : 'unknown') === 'warning') {
                $diagnostic_results['warning_checks']++;
            } else {
                $diagnostic_results['failed_checks']++;
                if ($network_score < 40) {
                    $diagnostic_results['critical_issues'][] = [
                        'title' => 'Network Connectivity Issues',
                        'description' => 'Cannot reach CallTrackingMetrics servers. Check firewall and DNS settings.',
                        'auto_fix_available' => false,
                        'fix_id' => 'network_connectivity'
                    ];
                }
            }
            $conflicts_analysis = $this->analyzePluginConflicts();
            $conflicts_score = isset($conflicts_analysis['score']) && is_numeric($conflicts_analysis['score']) ? $conflicts_analysis['score'] : 0;
            $diagnostic_results['categories']['plugin_conflicts'] = [
                'title' => 'Plugin Conflicts',
                'description' => 'Scanning for potential conflicts with other WordPress plugins',
                'status' => $conflicts_analysis['status'],
                'score' => $conflicts_score,
                'issues' => $conflicts_analysis['issues'] ?? [],
                'recommendations' => $conflicts_analysis['recommendations'] ?? []
            ];
            if ($conflicts_analysis['status'] === 'healthy') {
                $diagnostic_results['passed_checks']++;
            } elseif ($conflicts_analysis['status'] === 'warning') {
                $diagnostic_results['warning_checks']++;
            } else {
                $diagnostic_results['failed_checks']++;
            }
            $system_health = $this->runSystemHealthChecks();
            $system_score = isset($system_health['score']) && is_numeric($system_health['score']) ? $system_health['score'] : 0;
            $diagnostic_results['categories']['system_health'] = [
                'title' => 'System Health',
                'description' => 'WordPress environment and server configuration analysis',
                'status' => $system_health['status'],
                'score' => $system_score,
                'issues' => $system_health['issues'] ?? [],
                'recommendations' => $system_health['recommendations'] ?? []
            ];
            if ($system_health['status'] === 'healthy') {
                $diagnostic_results['passed_checks']++;
            } elseif ($system_health['status'] === 'warning') {
                $diagnostic_results['warning_checks']++;
            } else {
                $diagnostic_results['failed_checks']++;
                if ($system_score < 50) {
                    $diagnostic_results['critical_issues'][] = [
                        'title' => 'System Health Issues',
                        'description' => 'WordPress environment has configuration issues that may affect plugin performance.',
                        'auto_fix_available' => true,
                        'fix_id' => 'system_health'
                    ];
                }
            }
            return $diagnostic_results;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * AJAX: Export Diagnostic Report
     */
    public function ajaxExportDiagnosticReport(): void
    {
        check_ajax_referer('ctm_export_diagnostic_report', 'nonce');
        try {
            // Generate system info
            $system_info = $this->generateSystemInfoReport();
            // Run full diagnostic
            ob_start();
            $diagnostic_results = null;
            if (method_exists($this, 'ajaxFullDiagnostic')) {
                // Use the same logic as ajaxFullDiagnostic, but capture output
                $diagnostic_results = $this->runFullDiagnosticForExport();
            }
            $diagnostic_text = "=== DIAGNOSTIC RESULTS ===\n";
            if ($diagnostic_results && is_array($diagnostic_results)) {
                $diagnostic_text .= "Passed Checks: " . ($diagnostic_results['passed_checks'] ?? 0) . "\n";
                $diagnostic_text .= "Warning Checks: " . ($diagnostic_results['warning_checks'] ?? 0) . "\n";
                $diagnostic_text .= "Failed Checks: " . ($diagnostic_results['failed_checks'] ?? 0) . "\n";
                $diagnostic_text .= "\n";
                if (!empty($diagnostic_results['critical_issues'])) {
                    $diagnostic_text .= "Critical Issues:\n";
                    foreach ($diagnostic_results['critical_issues'] as $issue) {
                        $diagnostic_text .= "- " . ($issue['title'] ?? '') . ": " . ($issue['description'] ?? '') . "\n";
                    }
                    $diagnostic_text .= "\n";
                }
                if (!empty($diagnostic_results['categories'])) {
                    foreach ($diagnostic_results['categories'] as $cat) {
                        $diagnostic_text .= ($cat['title'] ?? 'Category') . ": " . ($cat['status'] ?? '') . " (Score: " . ($cat['score'] ?? 'N/A') . ")\n";
                        if (!empty($cat['issues'])) {
                            foreach ($cat['issues'] as $issue) {
                                $diagnostic_text .= "  - Issue: $issue\n";
                            }
                        }
                        if (!empty($cat['recommendations'])) {
                            foreach ($cat['recommendations'] as $rec) {
                                $diagnostic_text .= "  - Recommendation: $rec\n";
                            }
                        }
                        $diagnostic_text .= "\n";
                    }
                }
            } else {
                $diagnostic_text .= "No diagnostic results available.\n";
            }
            $diagnostic_text .= "=== END DIAGNOSTIC RESULTS ===\n";
            $report = $system_info . "\n" . $diagnostic_text;
            // Return as downloadable file (base64 for JS Blob)
            wp_send_json_success([
                'report' => $report
            ]);
        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => 'Failed to generate diagnostic report: ' . $e->getMessage()
            ]);
        }
    }


    /**
     * Run system health checks
     */
    private function runSystemHealthChecks(): array
    {
        $issues = [];
        $recommendations = [];
        $score = 100;
        
        // Check PHP version
        $php_version = PHP_VERSION;
        if (version_compare($php_version, '7.4', '<')) {
            $issues[] = 'PHP version is outdated (' . $php_version . '). Recommend PHP 7.4+';
            $score -= 20;
        } elseif (version_compare($php_version, '8.0', '<')) {
            $recommendations[] = 'Consider upgrading to PHP 8.0+ for better performance';
            $score -= 5;
        }
        
        // Check WordPress version
        $wp_version = get_bloginfo('version');
        if (version_compare($wp_version, '5.8', '<')) {
            $issues[] = 'WordPress version is outdated (' . $wp_version . '). Recommend WordPress 5.8+';
            $score -= 15;
        }
        
        // Check memory limit
        $memory_limit = wp_convert_hr_to_bytes(ini_get('memory_limit'));
        $recommended_memory = 256 * 1024 * 1024; // 256MB
        if ($memory_limit < $recommended_memory) {
            $issues[] = 'Memory limit is low (' . size_format($memory_limit) . '). Recommend 256MB+';
            $score -= 15;
        }
        
        // Check max execution time
        $max_execution_time = ini_get('max_execution_time');
        if ($max_execution_time > 0 && $max_execution_time < 30) {
            $issues[] = 'Max execution time is low (' . $max_execution_time . 's). Recommend 30s+';
            $score -= 10;
        }
        
        // Check SSL/HTTPS
        if (!is_ssl()) {
            $issues[] = 'Site is not using HTTPS. SSL is required for secure API communication';
            $score -= 25;
        }
        
        // Check file permissions
        $upload_dir = wp_upload_dir();
        if (!is_writable($upload_dir['basedir'])) {
            $issues[] = 'Upload directory is not writable. May affect log file storage';
            $score -= 20;
        }
        
        // Check required extensions
        $required_extensions = ['curl', 'json', 'mbstring'];
        foreach ($required_extensions as $ext) {
            if (!extension_loaded($ext)) {
                $issues[] = "Required PHP extension '{$ext}' is not loaded";
                $score -= 15;
            }
        }
        
        // Check debug mode
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $recommendations[] = 'WP_DEBUG is enabled. Consider disabling in production';
            $score -= 5;
        }
        
        // Determine status
        $status = 'healthy';
        if ($score < 70) {
            $status = 'critical';
        } elseif ($score < 85) {
            $status = 'warning';
        }
        
        return [
            'status' => $status,
            'score' => max(0, $score),
            'issues' => $issues,
            'recommendations' => $recommendations
        ];
    }

      /**
     * AJAX: Run Full Diagnostic
     */
    public function ajaxFullDiagnostic(): void
    {
        check_ajax_referer('ctm_full_diagnostic', 'nonce');
        
        try {
            $diagnostic_results = [
                'passed_checks' => 0,
                'warning_checks' => 0,
                'failed_checks' => 0,
                'critical_issues' => [],
                'categories' => []
            ];
            
            // Run API Credentials Analysis
            $api_analysis = $this->analyzeApiCredentials();
            $api_score = isset($api_analysis['score']) && is_numeric($api_analysis['score']) ? $api_analysis['score'] : 0;
            $diagnostic_results['categories']['api_credentials'] = [
                'title' => 'API Credentials',
                'description' => 'Validation of CallTrackingMetrics API connectivity and authentication',
                'status' => isset($api_analysis['status']) ? $api_analysis['status'] : 'unknown',
                'score' => $api_score,
                'issues' => $api_analysis['issues'] ?? [],
                'recommendations' => $api_analysis['recommendations'] ?? []
            ];
            
            // Update counters based on API analysis
            if ((isset($api_analysis['status']) ? $api_analysis['status'] : 'unknown') === 'healthy') {
                $diagnostic_results['passed_checks']++;
            } elseif ((isset($api_analysis['status']) ? $api_analysis['status'] : 'unknown') === 'warning') {
                $diagnostic_results['warning_checks']++;
            } else {
                $diagnostic_results['failed_checks']++;
                if ($api_score < 30) {
                    $diagnostic_results['critical_issues'][] = [
                        'title' => 'API Credentials Failed',
                        'description' => 'Cannot connect to CallTrackingMetrics API. Plugin functionality will be severely limited.',
                        'auto_fix_available' => false,
                        'fix_id' => 'api_credentials'
                    ];
                }
            }
            
            // Run Form Integration Analysis
            $form_analysis = $this->analyzeFormIntegration();
            $form_score = isset($form_analysis['score']) && is_numeric($form_analysis['score']) ? $form_analysis['score'] : 0;
            $diagnostic_results['categories']['form_integration'] = [
                'title' => 'Form Integration',
                'description' => 'Analysis of Contact Form 7 and Gravity Forms integration status',
                'status' => isset($form_analysis['status']) ? $form_analysis['status'] : 'unknown',
                'score' => $form_score,
                'issues' => $form_analysis['issues'] ?? [],
                'recommendations' => $form_analysis['recommendations'] ?? []
            ];
            
            // Update counters based on form analysis
            if ((isset($form_analysis['status']) ? $form_analysis['status'] : 'unknown') === 'healthy') {
                $diagnostic_results['passed_checks']++;
            } elseif ((isset($form_analysis['status']) ? $form_analysis['status'] : 'unknown') === 'warning') {
                $diagnostic_results['warning_checks']++;
            } else {
                $diagnostic_results['failed_checks']++;
            }
            
            // Run Network Connectivity Analysis
            $network_analysis = $this->analyzeNetworkConnectivity();
            $network_score = isset($network_analysis['score']) && is_numeric($network_analysis['score']) ? $network_analysis['score'] : 0;
            $diagnostic_results['categories']['network_connectivity'] = [
                'title' => 'Network Connectivity',
                'description' => 'Testing network connectivity and DNS resolution for CTM services',
                'status' => isset($network_analysis['status']) ? $network_analysis['status'] : 'unknown',
                'score' => $network_score,
                'issues' => $network_analysis['issues'] ?? [],
                'recommendations' => $network_analysis['recommendations'] ?? []
            ];
            
            // Update counters based on network analysis
            if ((isset($network_analysis['status']) ? $network_analysis['status'] : 'unknown') === 'healthy') {
                $diagnostic_results['passed_checks']++;
            } elseif ((isset($network_analysis['status']) ? $network_analysis['status'] : 'unknown') === 'warning') {
                $diagnostic_results['warning_checks']++;
            } else {
                $diagnostic_results['failed_checks']++;
                if ($network_score < 40) {
                    $diagnostic_results['critical_issues'][] = [
                        'title' => 'Network Connectivity Issues',
                        'description' => 'Cannot reach CallTrackingMetrics servers. Check firewall and DNS settings.',
                        'auto_fix_available' => false,
                        'fix_id' => 'network_connectivity'
                    ];
                }
            }
            
            // Run Plugin Conflicts Analysis
            $conflicts_analysis = $this->analyzePluginConflicts();
            $conflicts_score = isset($conflicts_analysis['score']) && is_numeric($conflicts_analysis['score']) ? $conflicts_analysis['score'] : 0;
            $diagnostic_results['categories']['plugin_conflicts'] = [
                'title' => 'Plugin Conflicts',
                'description' => 'Scanning for potential conflicts with other WordPress plugins',
                'status' => $conflicts_analysis['status'],
                'score' => $conflicts_score,
                'issues' => $conflicts_analysis['issues'] ?? [],
                'recommendations' => $conflicts_analysis['recommendations'] ?? []
            ];
            
            // Update counters based on conflicts analysis
            if ($conflicts_analysis['status'] === 'healthy') {
                $diagnostic_results['passed_checks']++;
            } elseif ($conflicts_analysis['status'] === 'warning') {
                $diagnostic_results['warning_checks']++;
            } else {
                $diagnostic_results['failed_checks']++;
            }
            
            // Additional System Health Checks
            $system_health = $this->runSystemHealthChecks();
            $system_score = isset($system_health['score']) && is_numeric($system_health['score']) ? $system_health['score'] : 0;
            $diagnostic_results['categories']['system_health'] = [
                'title' => 'System Health',
                'description' => 'WordPress environment and server configuration analysis',
                'status' => $system_health['status'],
                'score' => $system_score,
                'issues' => $system_health['issues'] ?? [],
                'recommendations' => $system_health['recommendations'] ?? []
            ];
            
            // Update counters based on system health
            if ($system_health['status'] === 'healthy') {
                $diagnostic_results['passed_checks']++;
            } elseif ($system_health['status'] === 'warning') {
                $diagnostic_results['warning_checks']++;
            } else {
                $diagnostic_results['failed_checks']++;
                if ($system_score < 50) {
                    $diagnostic_results['critical_issues'][] = [
                        'title' => 'System Health Issues',
                        'description' => 'WordPress environment has configuration issues that may affect plugin performance.',
                        'auto_fix_available' => true,
                        'fix_id' => 'system_health'
                    ];
                }
            }
            
            wp_send_json_success($diagnostic_results);
            
        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => 'Full diagnostic encountered an error: ' . $e->getMessage(),
                'error_details' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]
            ]);
        }
    }

    /**
     * AJAX: Auto-fix common issues detected by health check
     */
    public function ajaxAutoFixIssues(): void
    {
        check_ajax_referer('ctm_auto_fix_issues', 'nonce');
        
        $fixes = [];
        $fixed_count = 0;
        
        try {
            // Fix 1: Enable debug mode if disabled
            $debug_enabled = \get_option('ctm_debug_enabled', false);
            if (!$debug_enabled) {
                \update_option('ctm_debug_enabled', true);
                $fixes[] = [
                    'issue' => 'Debug Mode Disabled',
                    'status' => 'fixed',
                    'message' => 'Debug mode has been enabled for better troubleshooting'
                ];
                $fixed_count++;
            } else {
                $fixes[] = [
                    'issue' => 'Debug Mode',
                    'status' => 'skipped',
                    'message' => 'Debug mode was already enabled'
                ];
            }
            
            // Fix 2: Set default log retention if not configured
            $retention_days = \get_option('ctm_log_retention_days', null);
            if ($retention_days === null || $retention_days < 1) {
                \update_option('ctm_log_retention_days', 7);
                $fixes[] = [
                    'issue' => 'Log Retention Settings',
                    'status' => 'fixed',
                    'message' => 'Set log retention to 7 days (recommended default)'
                ];
                $fixed_count++;
            } else {
                $fixes[] = [
                    'issue' => 'Log Retention Settings',
                    'status' => 'skipped',
                    'message' => 'Log retention already configured'
                ];
            }
            
            // Fix 3: Enable auto-cleanup if disabled
            $auto_cleanup = \get_option('ctm_log_auto_cleanup', null);
            if ($auto_cleanup === null || !$auto_cleanup) {
                \update_option('ctm_log_auto_cleanup', true);
                $fixes[] = [
                    'issue' => 'Auto Log Cleanup',
                    'status' => 'fixed',
                    'message' => 'Enabled automatic log cleanup to prevent disk space issues'
                ];
                $fixed_count++;
            } else {
                $fixes[] = [
                    'issue' => 'Auto Log Cleanup',
                    'status' => 'skipped',
                    'message' => 'Auto cleanup already enabled'
                ];
            }
            
            // Fix 4: Clear old logs if too many exist
            $log_index = \get_option('ctm_log_index', []);
            if (is_array($log_index) && count($log_index) > 30) {
                // Keep only last 7 days of logs
                $logs_to_keep = array_slice($log_index, -7);
                $logs_to_remove = array_diff($log_index, $logs_to_keep);
                
                foreach ($logs_to_remove as $date) {
                    \delete_option("ctm_daily_log_{$date}");
                }
                
                \update_option('ctm_log_index', $logs_to_keep);
                $fixes[] = [
                    'issue' => 'Excessive Log Files',
                    'status' => 'fixed',
                    'message' => 'Cleaned up ' . count($logs_to_remove) . ' old log files to free disk space'
                ];
                $fixed_count++;
            } else {
                $fixes[] = [
                    'issue' => 'Log File Cleanup',
                    'status' => 'skipped',
                    'message' => 'Log files are within acceptable limits'
                ];
            }
            
            // Fix 5: Set notification email if empty
            $notification_email = \get_option('ctm_log_notification_email', '');
            $admin_email = \get_option('admin_email', '');
            if (empty($notification_email) && !empty($admin_email)) {
                \update_option('ctm_log_notification_email', $admin_email);
                $fixes[] = [
                    'issue' => 'Notification Email Missing',
                    'status' => 'fixed',
                    'message' => 'Set notification email to admin email: ' . $admin_email
                ];
                $fixed_count++;
            } else {
                $fixes[] = [
                    'issue' => 'Notification Email',
                    'status' => 'skipped',
                    'message' => 'Notification email already configured'
                ];
            }
            
            // Fix 6: Check and fix file permissions (if possible)
            $upload_dir = \wp_upload_dir();
            if (!empty($upload_dir['basedir']) && is_writable($upload_dir['basedir'])) {
                $fixes[] = [
                    'issue' => 'File Permissions',
                    'status' => 'skipped',
                    'message' => 'Upload directory permissions are correct'
                ];
            } else {
                $fixes[] = [
                    'issue' => 'File Permissions',
                    'status' => 'failed',
                    'message' => 'Upload directory is not writable - manual intervention required'
                ];
            }
            
            // Fix 7: Memory limit check
            $memory_limit = \wp_convert_hr_to_bytes(\ini_get('memory_limit'));
            $recommended_memory = 256 * 1024 * 1024; // 256MB
            
            if ($memory_limit < $recommended_memory) {
                $fixes[] = [
                    'issue' => 'Low Memory Limit',
                    'status' => 'failed',
                    'message' => 'Memory limit is ' . size_format($memory_limit) . '. Recommend increasing to 256MB+ in php.ini'
                ];
            } else {
                $fixes[] = [
                    'issue' => 'Memory Limit',
                    'status' => 'skipped',
                    'message' => 'Memory limit is adequate: ' . size_format($memory_limit)
                ];
            }
            
            // Fix 8: Check cURL availability
            if (!function_exists('curl_init')) {
                $fixes[] = [
                    'issue' => 'cURL Extension Missing',
                    'status' => 'failed',
                    'message' => 'cURL extension is not available - contact hosting provider'
                ];
            } else {
                $fixes[] = [
                    'issue' => 'cURL Extension',
                    'status' => 'skipped',
                    'message' => 'cURL extension is available'
                ];
            }
            
            wp_send_json_success([
                'message' => "Auto-fix completed: {$fixed_count} issues fixed",
                'fixes' => $fixes,
                'summary' => [
                    'total_checks' => count($fixes),
                    'fixed' => $fixed_count,
                    'skipped' => count(array_filter($fixes, fn($f) => $f['status'] === 'skipped')),
                    'failed' => count(array_filter($fixes, fn($f) => $f['status'] === 'failed'))
                ]
            ]);
            
        } catch (\Throwable $e) {
            wp_send_json_error([
                'message' => 'Auto-fix encountered an error: ' . $e->getMessage(),
                'fixes' => $fixes
            ]);
        }
    }

    /**
     * AJAX: Refresh System Information
     */
    public function ajaxRefreshSystemInfo(): void
    {
        check_ajax_referer('ctm_refresh_system_info', 'nonce');
        
        try {
            // Generate fresh system information
            $system_info = [
                'php_version' => PHP_VERSION,
                'wp_version' => get_bloginfo('version'),
                'memory_usage' => size_format(memory_get_usage(true)) . ' / ' . ini_get('memory_limit'),
                'db_queries' => get_num_queries(),
                'wordpress_env' => [
                    'version' => get_bloginfo('version'),
                    'language' => get_locale(),
                    'debug_mode' => WP_DEBUG ? 'Enabled' : 'Disabled',
                    'memory_limit' => WP_MEMORY_LIMIT,
                    'multisite' => is_multisite() ? 'Yes' : 'No',
                    'timezone' => \get_option('timezone_string') ?: 'UTC'
                ],
                'server_env' => [
                    'php_version' => PHP_VERSION,
                    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                    'operating_system' => PHP_OS,
                    'memory_limit' => ini_get('memory_limit'),
                    'max_execution_time' => ini_get('max_execution_time') . 's',
                    'upload_max_size' => ini_get('upload_max_filesize')
                ],
                'database_info' => [
                    'version' => $GLOBALS['wpdb']->db_version(),
                    'host' => DB_HOST,
                    'charset' => DB_CHARSET,
                    'table_prefix' => $GLOBALS['wpdb']->prefix
                ]
            ];
            
            wp_send_json_success([
                'message' => 'System information refreshed successfully',
                'system_info' => $system_info,
                'timestamp' => current_time('mysql')
            ]);
            
        } catch (\Throwable $e) {
            wp_send_json_error([
                'message' => 'Failed to refresh system information: ' . $e->getMessage()
            ]);
        }
    }


    /**
     * AJAX: Email System Information Report
     */
    public function ajaxEmailSystemInfo(): void
    {
        try {
            $this->loggingSystem->logActivity('Email system info request received', 'debug');
            check_ajax_referer('ctm_email_system_info', 'nonce');
            
            $email_to = sanitize_email($_POST['email_to'] ?? '');
            $subject = sanitize_text_field($_POST['subject'] ?? '');
            $additional_message = sanitize_textarea_field($_POST['message'] ?? '');
            
            $this->loggingSystem->logActivity("Email system info - To: {$email_to}, Subject: {$subject}", 'debug');
            
            if (!$email_to) {
                $this->loggingSystem->logActivity('No email address provided', 'debug');
                wp_send_json_error(['message' => 'Email address is required']);
                return;
            }
            
            if (!is_email($email_to)) {
                $this->loggingSystem->logActivity("Invalid email address: {$email_to}", 'debug');
                wp_send_json_error(['message' => 'Invalid email address']);
                return;
            }
            
            if (!$subject) {
                $subject = 'System Information Report - ' . \get_bloginfo('name');
            }
            
            $this->loggingSystem->logActivity("Generating system info report", 'debug');
            
            // Generate comprehensive system information (HTML)
            $system_info_html = $this->generateSystemInfoReport(true);

            // Prepare email content as HTML
            $email_body = '<div style="font-family: Arial, sans-serif; color: #222; background: #f9f9f9; padding: 24px;">';
            $email_body .= '<div style="max-width: 700px; margin: 0 auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); padding: 32px;">';
            $email_body .= '<h2 style="color: #2563eb; margin-top: 0;">System Information Report</h2>';
            $email_body .= '<p style="color: #666; font-size: 14px; margin-bottom: 24px;">Generated: <strong>' . esc_html(current_time('Y-m-d H:i:s')) . '</strong></p>';
            
            if ($additional_message) {
                $email_body .= '<div style="margin-bottom: 24px; padding: 16px; background: #f1f5f9; border-left: 4px solid #2563eb; border-radius: 4px;">';
                $email_body .= '<strong>Message from sender:</strong><br>';
                $email_body .= nl2br(esc_html($additional_message));
                $email_body .= '</div>';
            }

            $email_body .= $system_info_html;
            $email_body .= '</div></div>';

            // Send email as HTML
            $headers = [
                'Content-Type: text/html; charset=UTF-8',
                'From: ' . \get_bloginfo('name') . ' <' . \get_option('admin_email') . '>'
            ];
            
            $this->loggingSystem->logActivity("Attempting to send system info email to {$email_to}", 'debug');
            
            $sent = wp_mail($email_to, $subject, $email_body, $headers);
            
            $this->loggingSystem->logActivity("wp_mail result for system info: " . ($sent ? 'true' : 'false'), 'debug');
            
            if ($sent) {
                wp_send_json_success([
                    'message' => 'System information email sent successfully to ' . $email_to
                ]);
            } else {
                wp_send_json_error([
                    'message' => 'Failed to send email. Please check your email configuration.'
                ]);
            }
            
        } catch (\Exception $e) {
            $this->loggingSystem->logActivity("Exception while emailing system info: " . $e->getMessage(), 'error');
            wp_send_json_error(['message' => 'Error sending email: ' . $e->getMessage()]);
        }
    }

     /**
     * Generate comprehensive system information report
     * @param bool $as_html If true, returns HTML. Otherwise, returns plain text.
     */
    public function generateSystemInfoReport($as_html = false): string
    {
        $nl = $as_html ? '<br>' : "\n";
        $section_title = function($title) use ($as_html) {
            return $as_html
                ? '<h3 style="color:#2563eb; margin-top:32px; margin-bottom:8px; font-size:18px;">' . esc_html($title) . '</h3>'
                : "=== {$title} ===\n";
        };
        $row = function($label, $value) use ($as_html) {
            return $as_html
                ? '<tr><td style="padding:4px 12px 4px 0; color:#444; font-weight:500;">' . esc_html($label) . '</td><td style="padding:4px 0; color:#222;">' . esc_html($value) . '</td></tr>'
                : "{$label}: {$value}\n";
        };

        if ($as_html) {
            $report = '<table style="width:100%; border-collapse:collapse; font-size:14px;">';
        } else {
            $report = "=== SYSTEM INFORMATION REPORT ===\n";
            $report .= "Generated: " . current_time('Y-m-d H:i:s') . "\n";
            $report .= "Site: " . get_bloginfo('name') . "\n\n";
        }

        // WordPress Environment
        if ($as_html) $report .= '</table>' . $section_title('WordPress Environment') . '<table style="width:100%; border-collapse:collapse; font-size:14px;">';
        $report .= $row('WordPress Version', get_bloginfo('version'));
        $report .= $row('Site URL', home_url());
        $report .= $row('Admin URL', admin_url());
        $report .= $row('WordPress Language', get_locale());
        $report .= $row('WordPress Debug', WP_DEBUG ? 'Enabled' : 'Disabled');
        $report .= $row('WordPress Memory Limit', WP_MEMORY_LIMIT);
        $report .= $row('Multisite', is_multisite() ? 'Yes' : 'No');
        if (!$as_html) $report .= $nl;

        // Server Environment
        if ($as_html) $report .= '</table>' . $section_title('Server Environment') . '<table style="width:100%; border-collapse:collapse; font-size:14px;">';
        $report .= $row('PHP Version', PHP_VERSION);
        $report .= $row('PHP SAPI', php_sapi_name());
        $report .= $row('Server Software', $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown');
        $report .= $row('Operating System', PHP_OS);
        $report .= $row('Memory Limit', ini_get('memory_limit'));
        $report .= $row('Max Execution Time', ini_get('max_execution_time') . 's');
        $report .= $row('Max Input Vars', ini_get('max_input_vars'));
        $report .= $row('Upload Max Size', ini_get('upload_max_filesize'));
        $report .= $row('Post Max Size', ini_get('post_max_size'));
        $report .= $row('Max File Uploads', ini_get('max_file_uploads'));
        if (!$as_html) $report .= $nl;

        // Database
        if ($as_html) $report .= '</table>' . $section_title('Database') . '<table style="width:100%; border-collapse:collapse; font-size:14px;">';
        $report .= $row('Database Version', $GLOBALS['wpdb']->db_version());
        $report .= $row('Database Host', DB_HOST);
        $report .= $row('Database Name', DB_NAME);
        $report .= $row('Database Charset', DB_CHARSET);
        $report .= $row('Table Prefix', $GLOBALS['wpdb']->prefix);
        if (!$as_html) $report .= $nl;

        // PHP Extensions
        if ($as_html) $report .= '</table>' . $section_title('PHP Extensions') . '<table style="width:100%; border-collapse:collapse; font-size:14px;">';
        $extensions = [
            'cURL' => function_exists('curl_init'),
            'OpenSSL' => extension_loaded('openssl'),
            'mbstring' => extension_loaded('mbstring'),
            'GD Library' => extension_loaded('gd'),
            'XML' => extension_loaded('xml'),
            'JSON' => extension_loaded('json'),
            'ZIP' => extension_loaded('zip')
        ];
        foreach ($extensions as $name => $available) {
            $report .= $row($name, $available ? 'Available' : 'Missing');
        }
        if (!$as_html) $report .= $nl;

        // CallTrackingMetrics Plugin
        if ($as_html) $report .= '</table>' . $section_title('CallTrackingMetrics Plugin') . '<table style="width:100%; border-collapse:collapse; font-size:14px;">';
        $report .= $row('Plugin Version', '2.0');
        $report .= $row('Debug Mode', \get_option('ctm_debug_enabled') ? 'Enabled' : 'Disabled');
        $report .= $row('API Key Configured', \get_option('ctm_api_key') ? 'Yes' : 'No');
        $report .= $row('CF7 Integration', \get_option('ctm_api_cf7_enabled') ? 'Enabled' : 'Disabled');
        $report .= $row('GF Integration', \get_option('ctm_api_gf_enabled') ? 'Enabled' : 'Disabled');
        if (!$as_html) $report .= $nl;

        // Theme & Plugins
        if ($as_html) $report .= '</table>' . $section_title('Theme & Plugins') . '<table style="width:100%; border-collapse:collapse; font-size:14px;">';
        $report .= $row('Active Theme', \wp_get_theme()->get('Name'));
        $report .= $row('Theme Version', \wp_get_theme()->get('Version'));
        $report .= $row('Active Plugins', count(\get_option('active_plugins', [])));
        $report .= $row('Contact Form 7', class_exists('WPCF7_ContactForm') ? 'Installed' : 'Not Installed');
        $report .= $row('Gravity Forms', class_exists('GFAPI') ? 'Installed' : 'Not Installed');
        if (!$as_html) $report .= $nl;

        // Current Performance
        if ($as_html) $report .= '</table>' . $section_title('Current Performance') . '<table style="width:100%; border-collapse:collapse; font-size:14px;">';
        $report .= $row('Memory Usage', size_format(memory_get_usage(true)));
        $report .= $row('Peak Memory', size_format(memory_get_peak_usage(true)));
        $report .= $row('Database Queries', \get_num_queries());
        $report .= $row('Admin Email', \get_option('admin_email'));
        $report .= $row('Timezone', \get_option('timezone_string') ?: 'UTC');
        if ($as_html) $report .= '</table>';
        if (!$as_html) $report .= $nl;

        if ($as_html) {
            $report .= '<div style="margin-top:32px; color:#888; font-size:12px;">End of report</div>';
        } else {
            $report .= "=== END REPORT ===\n";
        }

        return $report;
    }

    /**
     * AJAX: Analyze Common Issues
     */
    public function ajaxAnalyzeIssue(): void
    {
        check_ajax_referer('ctm_analyze_issue', 'nonce');
        
        $issueType = sanitize_text_field($_POST['issue_type'] ?? '');
        
        $analysis = $this->analyzeIssueType($issueType);
        
        wp_send_json_success(['analysis' => $analysis]);
    }


    private function analyzePluginConflicts(): array
    {
        $issues = [];
        $solutions = [];
        
        // Check for common conflicting plugins
        $active_plugins = \get_option('active_plugins', []);
        $potential_conflicts = [
            'wp-rocket/wp-rocket.php' => 'WP Rocket (caching)',
            'w3-total-cache/w3-total-cache.php' => 'W3 Total Cache',
            'wp-super-cache/wp-cache.php' => 'WP Super Cache',
            'autoptimize/autoptimize.php' => 'Autoptimize',
        ];
        
        foreach ($potential_conflicts as $plugin_file => $plugin_name) {
            if (in_array($plugin_file, $active_plugins)) {
                $issues[] = "Potential conflict with {$plugin_name}";
                $solutions[] = "Configure {$plugin_name} to exclude CTM plugin files from optimization";
                $solutions[] = "Add CTM AJAX endpoints to {$plugin_name} exclusion list";
            }
        }
        
        // Check for JavaScript conflicts
        if (wp_script_is('jquery', 'done')) {
            // jQuery is loaded, check version
            global $wp_scripts;
            if (isset($wp_scripts->registered['jquery'])) {
                $jquery_version = $wp_scripts->registered['jquery']->ver;
                if (version_compare($jquery_version, '1.12', '<')) {
                    $issues[] = 'jQuery version is outdated: ' . $jquery_version;
                    $solutions[] = 'Update WordPress to get newer jQuery version';
                    $solutions[] = 'Check for plugins that force older jQuery versions';
                }
            }
        }
        
        // Check PHP error reporting
        if (ini_get('display_errors')) {
            $issues[] = 'PHP error display is enabled (may interfere with AJAX)';
            $solutions[] = 'Disable display_errors in PHP configuration';
            $solutions[] = 'Use error logging instead of display_errors';
        }
        $status = empty($issues) ? 'healthy' : (count($issues) === 1 ? 'warning' : 'issues_found');
        $score = $status === 'healthy' ? 100 : ($status === 'warning' ? 70 : 0);
        return [
            'title' => 'Plugin Conflicts Analysis',
            'description' => 'Checking for potential conflicts with other plugins and system settings.',
            'status' => $status,
            'score' => $score,
            'issues' => $issues,
            'solutions' => $solutions
        ];
    }


    /**
     * Analyze specific issue types
     */
    public function analyzeIssueType(string $issueType): array
    {
        switch ($issueType) {
            case 'api_credentials':
                return $this->analyzeApiCredentials();
            case 'form_integration':
                return $this->analyzeFormIntegration();
            case 'network_connectivity':
                return $this->analyzeNetworkConnectivity();
            case 'plugin_conflicts':
                return $this->analyzePluginConflicts();
            default:
                return [
                    'title' => 'Unknown Issue Type',
                    'description' => 'The selected issue type is not recognized.',
                    'status' => 'error',
                    'issues' => ['Unknown issue type selected'],
                    'solutions' => ['Please select a valid issue type from the list']
                ];
        }
    }


    private function analyzeNetworkConnectivity(): array
    {
        $issues = [];
        $solutions = [];
        
        // Test basic connectivity
        if (!function_exists('curl_init') && !function_exists('file_get_contents')) {
            $issues[] = 'No HTTP request methods available';
            $solutions[] = 'Enable cURL extension in PHP';
            $solutions[] = 'Contact your hosting provider for assistance';
        }
        
        // Test SSL support
        if (!extension_loaded('openssl')) {
            $issues[] = 'SSL/TLS support is not available';
            $solutions[] = 'Enable OpenSSL extension in PHP';
            $solutions[] = 'Contact your hosting provider to enable SSL support';
        }
        
        // Test actual API connectivity using /api/v1/accounts (requires authentication)
        $apiKey = \get_option('ctm_api_key');
        $apiSecret = \get_option('ctm_api_secret');
        if (!$apiKey || !$apiSecret) {
            $issues[] = 'API credentials are not configured';
            $solutions[] = 'Enter your API key and secret in the General tab';
        } else {
            try {
                $response = wp_remote_get(\ctm_get_api_url() . '/api/v1/accounts', [
                    'timeout' => 10,
                    'sslverify' => true,
                    'headers' => [
                        'Authorization' => 'Basic ' . base64_encode($apiKey . ':' . $apiSecret),
                        'Accept' => 'application/json',
                    ],
                ]);
                
                if (is_wp_error($response)) {
                    $issues[] = 'Cannot connect to CallTrackingMetrics API: ' . $response->get_error_message();
                    $solutions[] = 'Check your server\'s internet connection';
                    $solutions[] = 'Verify firewall settings allow HTTPS connections';
                    $solutions[] = 'Contact your hosting provider if connection issues persist';
                } elseif (\wp_remote_retrieve_response_code($response) >= 400) {
                    $issues[] = 'CallTrackingMetrics API returned error: ' . \wp_remote_retrieve_response_code($response);
                    $solutions[] = 'Check CallTrackingMetrics service status';
                    $solutions[] = 'Verify your API credentials are correct and have access';
                    $solutions[] = 'Try again later if this is a temporary issue';
                }
            } catch (\Exception $e) {
                $issues[] = 'Network connectivity test failed: ' . $e->getMessage();
                $solutions[] = 'Check your server\'s network configuration';
                $solutions[] = 'Contact your hosting provider for network troubleshooting';
            }
        }
        $status = empty($issues) ? 'healthy' : (count($issues) === 1 ? 'warning' : 'issues_found');
        $score = $status === 'healthy' ? 100 : ($status === 'warning' ? 70 : 0);
        return [
            'title' => 'Network Connectivity Analysis',
            'description' => 'Testing network connectivity and ability to reach CallTrackingMetrics API (accounts endpoint).',
            'status' => $status,
            'score' => $score,
            'issues' => $issues,
            'solutions' => $solutions
        ];
    }


    private function analyzeFormIntegration(): array
    {
        $issues = [];
        $solutions = [];
        
        $cf7_active = class_exists('WPCF7_ContactForm');
        $gf_active = class_exists('GFAPI');
        
        if (!$cf7_active && !$gf_active) {
            $issues[] = 'No supported form plugins detected';
            $solutions[] = 'Install Contact Form 7 or Gravity Forms';
            $solutions[] = 'Activate the form plugin after installation';
        } else {
            $cf7_enabled = \get_option('ctm_api_cf7_enabled');
            $gf_enabled = \get_option('ctm_api_gf_enabled');
            
            if ($cf7_active && !$cf7_enabled) {
                $issues[] = 'Contact Form 7 is installed but CTM integration is disabled';
                $solutions[] = 'Enable Contact Form 7 integration in General settings';
            }
            
            if ($gf_active && !$gf_enabled) {
                $issues[] = 'Gravity Forms is installed but CTM integration is disabled';
                $solutions[] = 'Enable Gravity Forms integration in General settings';
            }
        }
        $status = empty($issues) ? 'healthy' : (count($issues) === 1 ? 'warning' : 'issues_found');
        $score = $status === 'healthy' ? 100 : ($status === 'warning' ? 70 : 0);
        return [
            'title' => 'Form Integration Analysis',
            'description' => 'Checking the configuration and setup of form plugin integrations.',
            'status' => $status,
            'score' => $score,
            'issues' => $issues,
            'solutions' => $solutions
        ];
    }

    public function analyzeApiCredentials(): array
    {
        $apiKey = \get_option('ctm_api_key');
        $apiSecret = \get_option('ctm_api_secret');
        $issues = [];
        $solutions = [];
        
        if (!$apiKey || !$apiSecret) {
            $issues[] = 'API credentials are not configured';
            $solutions[] = 'Enter your API key and secret in the General tab';
            $solutions[] = 'Contact CallTrackingMetrics support to obtain API credentials';
        } else {
            try {
                $apiService = new \CTM\Service\ApiService(\ctm_get_api_url());
                $accountInfo = $apiService->getAccountInfo($apiKey, $apiSecret);
                
                if (!$accountInfo || !isset($accountInfo['account'])) {
                    $issues[] = 'API credentials are invalid or expired';
                    $solutions[] = 'Verify your API key and secret are correct';
                    $solutions[] = 'Check if your CTM account has API access enabled';
                    $solutions[] = 'Contact CallTrackingMetrics support if credentials should be valid';
                }
            } catch (\Exception $e) {
                $issues[] = 'Failed to connect to CTM API: ' . $e->getMessage();
                $solutions[] = 'Check your internet connection';
                $solutions[] = 'Verify CallTrackingMetrics API service is available';
                $solutions[] = 'Check if your server can make HTTPS requests';
            }
        }
        $status = empty($issues) ? 'healthy' : 'issues_found';
        $score = $status === 'healthy' ? 100 : 0;
        return [
            'title' => 'API Credentials Analysis',
            'description' => 'Checking the validity and configuration of your CallTrackingMetrics API credentials.',
            'status' => $status,
            'score' => $score,
            'issues' => $issues,
            'solutions' => $solutions
        ];
    }


  /**
     * AJAX: Run Plugin Health Check
     */
    public function ajaxHealthCheck(): void
    {
        check_ajax_referer('ctm_health_check', 'nonce');
        
        $checks = [];
        
        // API Status Checks
        $apiKey = \get_option('ctm_api_key');
        $apiSecret = \get_option('ctm_api_secret');
        
        // API Key Check
        if ($apiKey && $apiSecret) {
            $checks[] = ['name' => 'API Key', 'status' => 'pass', 'message' => 'Configured'];
        } else {
            $checks[] = ['name' => 'API Key', 'status' => 'fail', 'message' => 'Not configured'];
        }
        
        // API Connection Check
        if ($apiKey && $apiSecret) {
            try {
                $apiService = new \CTM\Service\ApiService(\ctm_get_api_url());
                $accountInfo = $apiService->getAccountInfo($apiKey, $apiSecret);
                if ($accountInfo && isset($accountInfo['account'])) {
                    $checks[] = ['name' => 'API Connection', 'status' => 'pass', 'message' => 'Connected'];
                    $checks[] = ['name' => 'Account Access', 'status' => 'pass', 'message' => 'Accessible'];
                } else {
                    $checks[] = ['name' => 'API Connection', 'status' => 'fail', 'message' => 'Failed'];
                    $checks[] = ['name' => 'Account Access', 'status' => 'fail', 'message' => 'No access'];
                }
            } catch (\Exception $e) {
                $checks[] = ['name' => 'API Connection', 'status' => 'fail', 'message' => 'Connection failed'];
                $checks[] = ['name' => 'Account Access', 'status' => 'fail', 'message' => 'No access'];
            }
        } else {
            $checks[] = ['name' => 'API Connection', 'status' => 'fail', 'message' => 'No credentials'];
            $checks[] = ['name' => 'Account Access', 'status' => 'fail', 'message' => 'No credentials'];
        }
        
        // Form Integration Checks
        $cf7_active = class_exists('WPCF7_ContactForm');
        $gf_active = class_exists('GFAPI');
        
        $checks[] = ['name' => 'Contact Form 7', 'status' => $cf7_active ? 'pass' : 'warning', 'message' => $cf7_active ? 'Installed' : 'Not installed'];
        $checks[] = ['name' => 'Gravity Forms', 'status' => $gf_active ? 'pass' : 'warning', 'message' => $gf_active ? 'Installed' : 'Not installed'];
        
        // Server Environment Checks
        $checks[] = ['name' => 'PHP Version', 'status' => version_compare(PHP_VERSION, '7.4', '>=') ? 'pass' : 'warning', 'message' => PHP_VERSION];
        $checks[] = ['name' => 'cURL Extension', 'status' => function_exists('curl_init') ? 'pass' : 'fail', 'message' => function_exists('curl_init') ? 'Available' : 'Missing'];
        $checks[] = ['name' => 'SSL Support', 'status' => extension_loaded('openssl') ? 'pass' : 'fail', 'message' => extension_loaded('openssl') ? 'Available' : 'Missing'];
        
        // Memory Check
        $memory_limit = \wp_convert_hr_to_bytes(\ini_get('memory_limit'));
        $memory_status = $memory_limit >= 128 * 1024 * 1024 ? 'pass' : 'warning';
        $checks[] = ['name' => 'Memory Limit', 'status' => $memory_status, 'message' => size_format($memory_limit)];
        
        // Plugin Status Checks
        $checks[] = ['name' => 'Plugin Version', 'status' => 'pass', 'message' => '2.0'];
        
        // Database Tables Check
        global $wpdb;
        $table_check = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}options'");
        $checks[] = ['name' => 'Database Tables', 'status' => $table_check ? 'pass' : 'fail', 'message' => $table_check ? 'Accessible' : 'Error'];
        
        // File Permissions Check
        $upload_dir = \wp_upload_dir();
        $writable = is_writable($upload_dir['basedir']);
        $checks[] = ['name' => 'File Permissions', 'status' => $writable ? 'pass' : 'warning', 'message' => $writable ? 'Writable' : 'Limited'];
        
        // Debug Mode Check
        $debug_enabled = \get_option('ctm_debug_enabled', false);
        $checks[] = ['name' => 'Debug Mode', 'status' => $debug_enabled ? 'pass' : 'warning', 'message' => $debug_enabled ? 'Enabled' : 'Disabled'];
        
        \wp_send_json_success(['checks' => $checks]);
    }

  




    /**
     * Get cache hits from cache plugins
     */
    private function getCacheHits(): string
    {
        // Priority 1: Try to get actual cache hit statistics
        
        // Method 1: Standard wp_cache_get_stats() function
        if (function_exists('wp_cache_get_stats')) {
            $stats = \wp_cache_get_stats();
            if (isset($stats['hits']) && is_numeric($stats['hits'])) {
                $hits = intval($stats['hits']);
                if ($hits > 0) {
                    return number_format($hits) . ' hits';
                } else {
                    return '0 hits (cache active)';
                }
            }
        }
        
        // Method 2: Redis Object Cache statistics
        if (class_exists('Redis') && function_exists('wp_cache_get_stats')) {
            try {
                global $wp_object_cache;
                if (isset($wp_object_cache) && method_exists($wp_object_cache, 'get_stats')) {
                    $cache_stats = $wp_object_cache->get_stats();
                    if (isset($cache_stats['hits'])) {
                        return number_format($cache_stats['hits']) . ' hits (Redis)';
                    }
                }
            } catch (\Exception $e) {
                // Continue to next method
            }
        }
        
        // Method 3: Memcached statistics
        if (class_exists('Memcached') && function_exists('wp_cache_get_stats')) {
            try {
                global $wp_object_cache;
                if (isset($wp_object_cache) && method_exists($wp_object_cache, 'getStats')) {
                    $memcached_stats = $wp_object_cache->getStats();
                    if (is_array($memcached_stats)) {
                        foreach ($memcached_stats as $server_stats) {
                            if (isset($server_stats['get_hits'])) {
                                return number_format($server_stats['get_hits']) . ' hits (Memcached)';
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                // Continue to next method
            }
        }
        
        // Method 4: W3 Total Cache specific statistics
        if (class_exists('W3_Config')) {
            try {
                if (function_exists('w3_instance') && function_exists('W3TC')) {
                    $w3tc_config = \w3_instance('W3_Config');
                    if (method_exists($w3tc_config, 'get_stats')) {
                        $w3_stats = $w3tc_config->get_stats();
                        if (isset($w3_stats['cache_hits'])) {
                            return number_format($w3_stats['cache_hits']) . ' hits (W3TC)';
                        }
                    }
                }
                return 'W3TC active (stats N/A)';
            } catch (\Exception $e) {
                return 'W3TC active (stats error)';
            }
        }
        
        // Method 5: WP Rocket cache detection
        if (class_exists('WP_Rocket\Engine\Cache\WPCache')) {
            // WP Rocket doesn't expose hit statistics easily
            return 'WP Rocket active (stats N/A)';
        }
        
        // Method 6: WP Super Cache
        if (function_exists('wp_cache_is_enabled') && \wp_cache_is_enabled()) {
            // Check if we can get any statistics from WP Super Cache
            global $wp_super_cache_stats;
            if (isset($wp_super_cache_stats) && is_array($wp_super_cache_stats)) {
                if (isset($wp_super_cache_stats['cache_hits'])) {
                    return number_format($wp_super_cache_stats['cache_hits']) . ' hits (WP Super Cache)';
                }
            }
            return 'WP Super Cache active';
        }
        
        // Method 7: LiteSpeed Cache
        if (class_exists('LiteSpeed\Core') || defined('LSCWP_V')) {
            return 'LiteSpeed Cache active (stats N/A)';
        }
        
        // Method 8: WP Fastest Cache
        if (class_exists('WpFastestCache')) {
            return 'WP Fastest Cache active (stats N/A)';
        }
        
        // Method 9: Cachify
        if (class_exists('Cachify')) {
            return 'Cachify active (stats N/A)';
        }
        
        // Method 10: External object cache detection
        if (\wp_using_ext_object_cache()) {
            return 'External object cache active';
        }
        
        // Method 11: Basic object cache detection
        if (class_exists('Redis') || class_exists('Memcached')) {
            return 'Object cache available';
        }
        
        return 'No caching detected';
    }

    /**
     * Get slow queries from debug.log
     */
    private function getSlowQueries(): string
    {
        global $wpdb;
        
        // First check if SAVEQUERIES is enabled for real-time analysis
        if (defined('SAVEQUERIES') && constant('SAVEQUERIES') && isset($wpdb->queries)) {
            $slow_count = 0;
            $total_slow_time = 0;
            
            foreach ($wpdb->queries as $query) {
                $query_time = floatval($query[1]);
                if ($query_time > 0.1) { // Consider queries > 100ms as slow
                    $slow_count++;
                    $total_slow_time += $query_time;
                }
            }
            
            if ($slow_count > 0) {
                $avg_slow_time = round($total_slow_time / $slow_count * 1000, 2);
                return $slow_count . ' slow queries (avg: ' . $avg_slow_time . 'ms)';
            } else {
                return '0 slow queries';
            }
        }
        
        // Fallback to debug log analysis
        $debug_log = WP_CONTENT_DIR . '/debug.log';
        if (!file_exists($debug_log) || !is_readable($debug_log)) {
            return 'N/A (Enable SAVEQUERIES for analysis)';
        }

        $file_size = filesize($debug_log);
        if ($file_size === 0) {
            return '0 slow queries (Clean log)';
        }

        $handle = fopen($debug_log, 'r');
        if ($handle) {
            $read_size = min(10240, $file_size);
            fseek($handle, max(0, $file_size - $read_size));
            $log_content = fread($handle, $read_size);
            fclose($handle);

            // Count slow query patterns
            $slow_queries = substr_count($log_content, 'slow query') + 
                          substr_count($log_content, 'Slow query') +
                          substr_count($log_content, 'Query took');
             
            return $slow_queries . ' slow queries (from log)';
        }
        
        return 'N/A (Cannot analyze queries)';
    }
    /**
     * Store API response time for monitoring
     */
    private function storeApiResponseTime(float $responseTime): void
    {
        try {
            $response_times = \get_option('ctm_api_response_times', []);
            
            if (!is_array($response_times)) {
                $response_times = [];
            }
            
            // Add current response time with timestamp
            $response_times[time()] = $responseTime;
            
            // Clean old entries (older than 24 hours)
            $twenty_four_hours_ago = time() - (24 * 60 * 60);
            $response_times = array_filter($response_times, function($timestamp) use ($twenty_four_hours_ago) {
                return $timestamp >= $twenty_four_hours_ago;
            }, ARRAY_FILTER_USE_KEY);
            
            // Limit to prevent excessive data storage (keep last 100 response times max)
            if (count($response_times) > 100) {
                $response_times = array_slice($response_times, -100, null, true);
            }
            
            update_option('ctm_api_response_times', $response_times);
        } catch (\Exception $e) {
            // Silently fail to avoid disrupting API monitoring
            $this->loggingSystem->logActivity('API Response Time Storage Error: ' . $e->getMessage(), 'error');
        }
    }


    /**
     * Get average API response time
     */
    private function getApiResponseTime(): string
    {
        // Check for stored API response times from recent calls
        $response_times = \get_option('ctm_api_response_times', []);
        
        if (!empty($response_times) && is_array($response_times)) {
            // Clean old entries (older than 24 hours)
            $twenty_four_hours_ago = time() - (24 * 60 * 60);
            $recent_times = [];
            
            foreach ($response_times as $timestamp => $response_time) {
                if ($timestamp >= $twenty_four_hours_ago) {
                    $recent_times[] = $response_time;
                }
            }
            
            if (!empty($recent_times)) {
                $avg_response_time = round(array_sum($recent_times) / count($recent_times), 2);
                $count = count($recent_times);
                
                // Add performance context
                if ($avg_response_time < 200) {
                    return $avg_response_time . 'ms (excellent)';
                } elseif ($avg_response_time < 500) {
                    return $avg_response_time . 'ms (good)';
                } elseif ($avg_response_time < 1000) {
                    return $avg_response_time . 'ms (fair)';
                } else {
                    return $avg_response_time . 'ms (slow)';
                }
            }
        }
        
        // Fallback: Test API response time now
        $api_key = \get_option('ctm_api_key');
        $api_secret = \get_option('ctm_api_secret');
        
        if ($api_key && $api_secret) {
            $start_time = microtime(true);
            
            // Make a simple API call to test response time
            $response = wp_remote_head(\ctm_get_api_url() . '/api/v1/accounts', [
                'timeout' => 10,
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($api_key . ':' . $api_secret),
                    'Accept' => 'application/json',
                ],
            ]);
            
            $response_time = round((microtime(true) - $start_time) * 1000, 2);
            
            if (!is_wp_error($response)) {
                $http_code = \wp_remote_retrieve_response_code($response);
                
                if ($http_code >= 200 && $http_code < 400) {
                    // Store this response time for future reference
                    $this->storeApiResponseTime($response_time);
                    
                    // Add performance context
                    if ($response_time < 200) {
                        return $response_time . 'ms (live test, excellent)';
                    } elseif ($response_time < 500) {
                        return $response_time . 'ms (live test, good)';
                    } elseif ($response_time < 1000) {
                        return $response_time . 'ms (live test, fair)';
                    } else {
                        return $response_time . 'ms (live test, slow)';
                    }
                } else {
                    return 'Error: HTTP ' . $http_code;
                }
            } else {
                return 'Error: ' . $response->get_error_message();
            }
        }
        
        return 'API not configured';
    }



    /**
     * Get Error Rate
     */
    private function getErrorRate(): string
    {
        // Check WordPress debug log for recent errors
        $debug_log = WP_CONTENT_DIR . '/debug.log';
        
        if (!file_exists($debug_log) || !is_readable($debug_log)) {
            return 'No debug log found';
        }
        
        $file_size = filesize($debug_log);
        if ($file_size === 0) {
            return '0 errors (Clean log)';
        }
        
        // Read last 10KB of log file for recent errors
        $handle = fopen($debug_log, 'r');
        if ($handle) {
            $read_size = min(10240, $file_size); // 10KB or file size
            fseek($handle, max(0, $file_size - $read_size));
            $log_content = fread($handle, $read_size);
            fclose($handle);
            
            // Count error occurrences in recent log
            $error_count = substr_count($log_content, '[error]') + 
                          substr_count($log_content, 'Fatal error') + 
                          substr_count($log_content, 'PHP Warning') +
                          substr_count($log_content, 'PHP Notice');
            
            if ($error_count === 0) {
                return '0 recent errors';
            } elseif ($error_count < 5) {
                return $error_count . ' recent errors (Low)';
            } elseif ($error_count < 20) {
                return $error_count . ' recent errors (Medium)';
            } else {
                return $error_count . ' recent errors (High)';
            }
        }
        
        return 'Cannot read debug log';
    }

    /**
     * Get Active Sessions
     */
    private function getActiveSessions(): string
    {
        global $wpdb;
        // Count active user sessions from WordPress
        $logged_in_users = count_users();
        $total_users = $logged_in_users['total_users'];
        // Check for active sessions in the last hour
        $usermeta_table = property_exists($wpdb, 'usermeta') ? $wpdb->usermeta : $wpdb->prefix . 'usermeta';
        $active_sessions = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$usermeta_table} 
             WHERE meta_key LIKE %s 
             AND meta_value > %d",
            'session_tokens',
            time() - 3600 // Last hour
        ));
        if ($active_sessions > 0) {
            return $active_sessions . ' active';
        } elseif ($total_users > 0) {
            return '0 active (' . $total_users . ' total users)';
        }
        return 'No sessions tracked';
    }


    /**
     * Get Network I/O metrics
     */
    private function getNetworkIO(): string
    {
        // Test network connectivity and speed
        $start_time = microtime(true);
        
        // Use WordPress HTTP API for better compatibility
        $response = wp_remote_head(\ctm_get_api_url() . '/api/v1/accounts', [
            'timeout' => 5,
            'sslverify' => true
        ]);
        
        if (is_wp_error($response)) {
            return 'Error: ' . $response->get_error_message();
        }
        
        $response_time = round((microtime(true) - $start_time) * 1000, 2);
        $response_code = \wp_remote_retrieve_response_code($response);
        
        if ($response_code >= 200 && $response_code < 300) {
            return $response_time . 'ms (Good)';
        } elseif ($response_code >= 400) {
            return $response_time . 'ms (HTTP ' . $response_code . ')';
        }
        
        return $response_time . 'ms';
    }



    /**
     * Get API calls in the last 24 hours
     */
    private function getApiCalls24h(): string
    {
        global $wpdb;
        
        // Check for API call logs in WordPress options or custom table
        $api_calls_option = \get_option('ctm_api_calls_24h', null);
        
        if ($api_calls_option !== null && is_array($api_calls_option)) {
            // Clean old entries (older than 24 hours)
            $current_time = time();
            $twenty_four_hours_ago = $current_time - (24 * 60 * 60);
            
            $recent_calls = array_filter($api_calls_option, function($timestamp) use ($twenty_four_hours_ago) {
                return $timestamp >= $twenty_four_hours_ago;
            });
            
            // Update the option with cleaned data
            update_option('ctm_api_calls_24h', $recent_calls);
            
            $call_count = count($recent_calls);
            
            if ($call_count === 0) {
                return '0 calls (24h)';
            } elseif ($call_count === 1) {
                return '1 call (24h)';
            } else {
                return number_format($call_count) . ' calls (24h)';
            }
        }
        
        // Fallback: Try to estimate from debug logs
        $debug_log = WP_CONTENT_DIR . '/debug.log';
        if (file_exists($debug_log) && is_readable($debug_log)) {
            $file_size = filesize($debug_log);
            if ($file_size > 0) {
                $handle = fopen($debug_log, 'r');
                if ($handle) {
                    // Read last 50KB for recent API calls
                    $read_size = min(51200, $file_size);
                    fseek($handle, max(0, $file_size - $read_size));
                    $log_content = fread($handle, $read_size);
                    fclose($handle);
                    
                    // Count CallTrackingMetrics API calls in recent logs
                    $api_call_patterns = [
                        'calltrackingmetrics.com',
                        'CTM API',
                        'api.calltrackingmetrics',
                        'CallTrackingMetrics API'
                    ];
                    
                    $api_calls = 0;
                    foreach ($api_call_patterns as $pattern) {
                        $api_calls += substr_count(strtolower($log_content), strtolower($pattern));
                    }
                    
                    if ($api_calls > 0) {
                        return $api_calls . ' calls (est. from logs)';
                    }
                }
            }
        }
        
        // Fallback: Check if API credentials are configured
        $api_key = \get_option('ctm_api_key');
        $api_secret = \get_option('ctm_api_secret');
        
        if ($api_key && $api_secret) {
            // API is configured but no tracking data available
            return '0 calls (tracking not enabled)';
        }
        
        return 'API not configured';
    }

    /**
     * Get frontend queries
     */
    private function getFrontendQueries(): string
    {
        global $wpdb;
        
        if (is_admin()) {
            // For admin pages, show total queries but indicate it's admin context
            $total_queries = isset($wpdb->num_queries) ? $wpdb->num_queries : get_num_queries();
            return $total_queries . ' (Admin)';
        } else {
            // For frontend, show actual queries
            $total_queries = isset($wpdb->num_queries) ? $wpdb->num_queries : get_num_queries();
            return $total_queries . ' (Frontend)';
        }
    }

        /**
     * Calculate plugin load time
     */
    private function calculatePluginLoadTime(): string
    {
        // Get WordPress load time as a proxy for plugin load time
        if (isset($_SERVER['REQUEST_TIME_FLOAT'])) {
            $total_load_time = round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2);
            
            // Estimate plugin portion (rough approximation)
            $active_plugins_count = count(\get_option('active_plugins', []));
            if ($active_plugins_count > 0) {
                // Rough estimate: assume plugins take 20-40% of total load time
                $plugin_percentage = min(40, max(20, $active_plugins_count * 2));
                $estimated_plugin_time = round(($total_load_time * $plugin_percentage) / 100, 2);
                return $estimated_plugin_time . 'ms (est.)';
            }
        }
        
        return 'N/A';
    }

    /**
     * Calculate theme load time
     */
    private function calculateThemeLoadTime(): string
    {
        // Get current theme information
        $current_theme = \wp_get_theme();
        $theme_name = $current_theme->get('Name');
        
        if (isset($_SERVER['REQUEST_TIME_FLOAT'])) {
            $total_load_time = round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2);
            
            // Estimate theme portion based on complexity factors
            $complexity_score = 0;
            
            // Factor 1: Theme complexity based on template files
            $theme_dir = \get_template_directory();
            if (is_dir($theme_dir)) {
                $php_files = glob($theme_dir . '/*.php');
                $template_count = count($php_files);
                
                // More templates = more complex theme
                if ($template_count > 50) {
                    $complexity_score += 3; // Complex theme
                } elseif ($template_count > 20) {
                    $complexity_score += 2; // Medium theme
                } else {
                    $complexity_score += 1; // Simple theme
                }
            }
            
            // Factor 2: Check for common performance-heavy features
            $functions_php = $theme_dir . '/functions.php';
            if (file_exists($functions_php)) {
                $functions_content = \file_get_contents($functions_php);
                
                // Check for performance indicators
                if (strpos($functions_content, 'wp_enqueue_script') !== false) {
                    $complexity_score += 1; // Custom scripts
                }
                if (strpos($functions_content, 'wp_enqueue_style') !== false) {
                    $complexity_score += 1; // Custom styles
                }
                if (strpos($functions_content, 'add_action') !== false) {
                    $hook_count = \substr_count($functions_content, 'add_action');
                    $complexity_score += min(2, $hook_count / 10); // Many hooks
                }
                if (strpos($functions_content, 'WP_Query') !== false || 
                    strpos($functions_content, 'get_posts') !== false) {
                    $complexity_score += 1; // Custom queries
                }
            }
            
            // Factor 3: Check for CSS/JS files
            $style_css = $theme_dir . '/style.css';
            if (file_exists($style_css)) {
                $css_size = \filesize($style_css);
                if ($css_size > 100000) { // > 100KB
                    $complexity_score += 2;
                } elseif ($css_size > 50000) { // > 50KB
                    $complexity_score += 1;
                }
            }
            
            // Factor 4: Check if it's a known framework/parent theme
            $parent_theme = $current_theme->parent();
            if ($parent_theme) {
                $complexity_score += 1; // Child themes add overhead
            }
            
            // Common heavy themes detection
            $heavy_themes = ['avada', 'divi', 'enfold', 'betheme', 'x-theme', 'jupiter'];
            $theme_slug = strtolower($current_theme->get_stylesheet());
            foreach ($heavy_themes as $heavy_theme) {
                if (strpos($theme_slug, $heavy_theme) !== false) {
                    $complexity_score += 3;
                    break;
                }
            }
            
            // Calculate estimated theme load time
            // Base: 5-15% of total load time, increased by complexity
            $base_percentage = 8; // Base 8%
            $complexity_percentage = min(15, $complexity_score * 2); // Up to 15% more
            $theme_percentage = $base_percentage + $complexity_percentage;
            
            $estimated_theme_time = round(($total_load_time * $theme_percentage) / 100, 2);
            
            // Add context about theme complexity
            if ($complexity_score >= 8) {
                return $estimated_theme_time . 'ms (complex theme)';
            } elseif ($complexity_score >= 5) {
                return $estimated_theme_time . 'ms (medium theme)';
            } else {
                return $estimated_theme_time . 'ms (simple theme)';
            }
        }
        
        return 'N/A (timing unavailable)';
    }



    /**
     * Get cache misses from cache plugins
     */
    private function getCacheMisses(): string
    {
        // Priority 1: Try to get actual cache miss statistics with hit ratios
        
        // Method 1: Standard wp_cache_get_stats() function
        if (function_exists('wp_cache_get_stats')) {
            $stats = \wp_cache_get_stats();
            if (isset($stats['misses']) && is_numeric($stats['misses'])) {
                $hits = isset($stats['hits']) && is_numeric($stats['hits']) ? intval($stats['hits']) : 0;
                $misses = intval($stats['misses']);
                $total = $hits + $misses;
                
                if ($total > 0) {
                    $hit_ratio = round(($hits / $total) * 100, 1);
                    return number_format($misses) . ' misses (' . $hit_ratio . '% hit rate)';
                } else if ($misses > 0) {
                    return number_format($misses) . ' misses';
                } else {
                    return '0 misses (cache active)';
                }
            }
        }
        
        // Method 2: Redis Object Cache statistics
        if (class_exists('Redis')) {
            try {
                global $wp_object_cache;
                if (isset($wp_object_cache) && method_exists($wp_object_cache, 'get_stats')) {
                    $cache_stats = $wp_object_cache->get_stats();
                    if (isset($cache_stats['misses']) && isset($cache_stats['hits'])) {
                        $hits = intval($cache_stats['hits']);
                        $misses = intval($cache_stats['misses']);
                        $total = $hits + $misses;
                        
                        if ($total > 0) {
                            $hit_ratio = round(($hits / $total) * 100, 1);
                            return number_format($misses) . ' misses (' . $hit_ratio . '% hit rate, Redis)';
                        }
                    }
                }
            } catch (\Exception $e) {
                // Continue to next method
            }
        }
        
        // Method 3: Memcached statistics
        if (class_exists('Memcached')) {
            try {
                global $wp_object_cache;
                if (isset($wp_object_cache) && method_exists($wp_object_cache, 'getStats')) {
                    $memcached_stats = $wp_object_cache->getStats();
                    if (is_array($memcached_stats)) {
                        foreach ($memcached_stats as $server_stats) {
                            if (isset($server_stats['get_misses']) && isset($server_stats['get_hits'])) {
                                $hits = intval($server_stats['get_hits']);
                                $misses = intval($server_stats['get_misses']);
                                $total = $hits + $misses;
                                
                                if ($total > 0) {
                                    $hit_ratio = round(($hits / $total) * 100, 1);
                                    return number_format($misses) . ' misses (' . $hit_ratio . '% hit rate, Memcached)';
                                }
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                // Continue to next method
            }
        }
        
        // Method 4: Check for caching plugins without detailed stats
        $cache_plugins = [];
        
        if (class_exists('WP_Rocket\Engine\Cache\WPCache')) {
            $cache_plugins[] = 'WP Rocket';
        }
        if (class_exists('W3_Config')) {
            $cache_plugins[] = 'W3TC';
        }
        if (function_exists('wp_cache_is_enabled') && \wp_cache_is_enabled()) {
            $cache_plugins[] = 'WP Super Cache';
        }
        if (class_exists('LiteSpeed\Core') || defined('LSCWP_V')) {
            $cache_plugins[] = 'LiteSpeed';
        }
        if (class_exists('WpFastestCache')) {
            $cache_plugins[] = 'WP Fastest Cache';
        }
        if (class_exists('Cachify')) {
            $cache_plugins[] = 'Cachify';
        }
        if (\wp_using_ext_object_cache()) {
            $cache_plugins[] = 'External Object Cache';
        }
        
        if (!empty($cache_plugins)) {
            $plugin_list = implode(', ', $cache_plugins);
            return 'Cache active (' . $plugin_list . ') - stats N/A';
        }
        
        return 'No cache miss tracking';
    }

} 