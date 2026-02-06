<?php
/**
 * System Performance AJAX Handler
 * 
 * This file contains the SystemPerformanceAjax class which handles AJAX requests
 * related to performance monitoring, metrics collection, and performance analysis.
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

class SystemPerformanceAjax {
    private $loggingSystem;

    public function __construct(LoggingSystem $loggingSystem)
    {
        $this->loggingSystem = $loggingSystem;
    }

    public function registerHandlers() {
        add_action('wp_ajax_ctm_get_performance_metrics', [$this, 'ajaxGetPerformanceMetrics']);
        add_action('wp_ajax_ctm_performance_analysis', [$this, 'ajaxPerformanceAnalysis']);
        add_action('wp_ajax_ctm_test_performance', [$this, 'ajaxTestPerformance']);
        add_action('wp_ajax_wp_ajax_test', [$this, 'ajaxTestWordPressAjax']);
    }

    public function ajaxGetPerformanceMetrics(): void
    {
       
        try {
            global $wpdb;
            $client_metrics = isset($_POST['client_metrics']) ? json_decode(stripslashes($_POST['client_metrics']), true) : null;
            $memory_limit = ini_get('memory_limit');
            $memory_limit_bytes = wp_convert_hr_to_bytes($memory_limit);
            $current_usage = memory_get_usage(true);
            if (is_numeric($memory_limit_bytes) && $memory_limit_bytes > 0 && is_numeric($current_usage)) {
                $memory_percentage = round(($current_usage / $memory_limit_bytes) * 100, 1) . '%';
            } else {
                $memory_percentage = 'N/A';
            }
            $total_queries = isset($wpdb->num_queries) ? $wpdb->num_queries : get_num_queries();
            $query_time = 'N/A';
            if (defined('SAVEQUERIES') && constant('SAVEQUERIES') && isset($wpdb->queries)) {
                $total_time = 0;
                foreach ($wpdb->queries as $query) {
                    $total_time += $query[1];
                }
                $query_time = round($total_time * 1000, 2) . 'ms';
            } else {
                $query_time = 'N/A (Enable SAVEQUERIES)';
            }
            $server_load = 'N/A';
            if (function_exists('sys_getloadavg')) {
                $load = sys_getloadavg();
                $server_load = round($load[0], 2) . ' (1min)';
            }
            $disk_space = 'N/A';
            $upload_dir = wp_upload_dir();
            if (function_exists('disk_free_space') && isset($upload_dir['basedir'])) {
                $free_bytes = disk_free_space($upload_dir['basedir']);
                $disk_space = $free_bytes ? size_format($free_bytes) . ' free' : 'N/A';
            }
            $ttfb = 'N/A';
            if (isset($_SERVER['REQUEST_TIME_FLOAT'])) {
                $ttfb = round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2) . 'ms';
            }
            $dom_ready = 'N/A (Client-side)';
            $load_complete = 'N/A (Client-side)';
            $scripts_loaded = 'N/A (Client-side)';
            $styles_loaded = 'N/A (Client-side)';
            $images_loaded = 'N/A (Client-side)';
            if ($client_metrics && is_array($client_metrics)) {
                // Use internal
                if ($this->loggingSystem && $this->loggingSystem->isDebugEnabled()) {
                    $this->loggingSystem->logActivity('Performance: Received client metrics: ' . json_encode($client_metrics), 'debug');
                }
                if (isset($client_metrics['domContentLoaded'])) {
                    $dom_value = floatval($client_metrics['domContentLoaded']);
                    if ($dom_value > 0 && $dom_value < 60000) {
                        $dom_ready = round($dom_value, 2) . 'ms';
                    } else if ($dom_value > 0) {
                        $dom_ready = round($dom_value, 2) . 'ms (high)';
                    } else {
                        $dom_ready = 'N/A (invalid timing)';
                    }
                }
                if (isset($client_metrics['loadComplete'])) {
                    $load_value = floatval($client_metrics['loadComplete']);
                    if ($load_value > 0 && $load_value < 120000) {
                        $load_complete = round($load_value, 2) . 'ms';
                    } else if ($load_value > 0) {
                        $load_complete = round($load_value, 2) . 'ms (high)';
                    } else {
                        $load_complete = 'N/A (invalid timing)';
                    }
                }
                if (isset($client_metrics['scriptsLoaded'])) {
                    $scripts_count = intval($client_metrics['scriptsLoaded']);
                    if ($scripts_count >= 0) {
                        $scripts_loaded = $scripts_count . ' scripts';
                    }
                }
                if (isset($client_metrics['stylesLoaded'])) {
                    $styles_count = intval($client_metrics['stylesLoaded']);
                    if ($styles_count >= 0) {
                        $styles_loaded = $styles_count . ' stylesheets';
                    }
                }
                if (isset($client_metrics['imagesLoaded'])) {
                    $images_count = intval($client_metrics['imagesLoaded']);
                    if ($images_count >= 0) {
                        $images_loaded = $images_count . ' images';
                    }
                }
            }
            // Call helpers before array construction
            $cache_hits = $this->getCacheHits();

            $cache_misses = $this->getCacheMisses();
            $api_calls_24h = $this->getApiCalls24h();
            $api_response_time = $this->getApiResponseTime();
            $theme_load_time = $this->calculateThemeLoadTime();
            $plugin_load_time = $this->calculatePluginLoadTime();
            $frontend_queries = $this->getFrontendQueries();
            $network_io = $this->getNetworkIO();
            $active_sessions = $this->getActiveSessions();
            $error_rate = $this->getErrorRate();
            $slow_queries = $this->getSlowQueries();
            $metrics = [
                'current_memory' => size_format(memory_get_usage(true)),
                'peak_memory' => size_format(memory_get_peak_usage(true)),
                'memory_percentage' => $memory_percentage,
                'memory_limit' => $memory_limit,
                'execution_time' => round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2) . 'ms',
                'time_limit' => ini_get('max_execution_time') . 's',
                'cpu_usage' => function_exists('sys_getloadavg') ? round(sys_getloadavg()[0], 2) : 'N/A',
                'current_queries' => get_num_queries(),
                'total_queries' => $total_queries,
                'query_time' => $query_time,
                'total_query_time' => $query_time,
                'slow_queries' => $slow_queries,
                'cache_hits' => $cache_hits,
                'cache_misses' => $cache_misses,
                'db_version' => $GLOBALS['wpdb']->db_version(),
                'page_load_time' => round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2) . 'ms',
                'server_response' => isset($_SERVER['REQUEST_TIME_FLOAT']) ? round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2) . 'ms' : 'N/A',
                'server_load' => $server_load,
                'ttfb' => $ttfb,
                'dom_ready' => $dom_ready,
                'load_complete' => $load_complete,
                'scripts_loaded' => $scripts_loaded,
                'styles_loaded' => $styles_loaded,
                'images_loaded' => $images_loaded,
                'theme_load_time' => $theme_load_time,
                'plugin_load_time' => $plugin_load_time,
                'admin_queries' => is_admin() ? get_num_queries() : 'N/A',
                'frontend_queries' => $frontend_queries,
                'cron_jobs' => count(_get_cron_array()),
                'current_timestamp' => current_time('Y-m-d H:i:s'),
                'disk_space' => $disk_space,
                'disk_usage' => $disk_space,
                'network_io' => $network_io,
                'active_sessions' => $active_sessions,
                'error_rate' => $error_rate,
                'last_updated' => current_time('H:i:s'),
                'memory_usage' => size_format(memory_get_usage(true)),
                'db_queries' => get_num_queries(),
                'api_calls' => $api_calls_24h,
                'api_response_time' => $api_response_time,
                'php_version' => PHP_VERSION,
                'wp_version' => get_bloginfo('version'),
                // System Information Data
                'wordpress_env' => [
                    'version' => get_bloginfo('version'),
                    'language' => get_locale(),
                    'debug_mode' => WP_DEBUG ? 'Enabled' : 'Disabled',
                    'memory_limit' => WP_MEMORY_LIMIT,
                    'multisite' => is_multisite() ? 'Yes' : 'No',
                    'timezone' => get_option('timezone_string') ?: 'UTC'
                ],
                'server_env' => [
                    'php_version' => PHP_VERSION,
                    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                    'os' => PHP_OS,
                    'memory_limit' => ini_get('memory_limit'),
                    'max_execution_time' => ini_get('max_execution_time') . 's',
                    'upload_max_size' => ini_get('upload_max_filesize')
                ],
                'database_info' => [
                    'db_version' => $GLOBALS['wpdb']->db_version(),
                    'db_host' => DB_HOST,
                    'db_name' => DB_NAME,
                    'table_prefix' => $GLOBALS['wpdb']->prefix,
                    'db_charset' => DB_CHARSET,
                    'current_queries' => get_num_queries()
                ]
            ];
            
            wp_send_json_success($metrics);
        } catch (\Throwable $e) {
            if ($this->loggingSystem && $this->loggingSystem->isDebugEnabled()) {
                $this->loggingSystem->logActivity('Error in ajaxGetPerformanceMetrics: ' . $e->getMessage(), 'error');
                $this->loggingSystem->logActivity('Error trace: ' . $e->getTraceAsString(), 'error');
            }
            wp_send_json_error([
                'message' => 'Failed to get performance metrics: ' . $e->getMessage()
            ]);
        }
    }

    public function ajaxTestWordPressAjax(): void
    {
        wp_send_json_success([
            'data' => [
                'test' => 'WordPress AJAX is working',
                'timestamp' => current_time('mysql'),
                'php_version' => PHP_VERSION
            ],
            'message' => 'WordPress AJAX test successful'
        ]);
    }

    public function ajaxTestPerformance(): void
    {
        wp_send_json_success([
            'data' => [
                'test' => 'success',
                'timestamp' => current_time('mysql'),
                'memory_usage' => size_format(memory_get_usage(true)),
                'php_version' => PHP_VERSION,
                'wp_version' => get_bloginfo('version')
            ],
            'message' => 'Test endpoint working'
        ]);
    }

    public function ajaxPerformanceAnalysis(): void
    {
        check_ajax_referer('ctm_performance_analysis', 'nonce');
        global $wpdb;
        $metrics = [];
        $optimizations = [];
        $score = 100;
        $metrics['load_time'] = isset($_SERVER['REQUEST_TIME_FLOAT']) ? round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2) : null;
        if ($metrics['load_time'] > 2000) {
            $score -= 20;
            $optimizations[] = 'Optimize page load time (reduce to under 2s).';
        }
        $metrics['db_queries'] = isset($wpdb->num_queries) ? $wpdb->num_queries : get_num_queries();
        if ($metrics['db_queries'] > 100) {
            $score -= 10;
            $optimizations[] = 'Reduce the number of database queries.';
        }
        $metrics['memory_usage'] = round(memory_get_usage(true) / 1024 / 1024, 2);
        if ($metrics['memory_usage'] > 128) {
            $score -= 10;
            $optimizations[] = 'Optimize memory usage (keep under 128MB if possible).';
        }
        $cache_hit_rate = null;
        if (function_exists('wp_cache_get_stats')) {
            $stats = wp_cache_get_stats();
            if (isset($stats['hits']) && isset($stats['misses'])) {
                $total = $stats['hits'] + $stats['misses'];
                $cache_hit_rate = $total > 0 ? round($stats['hits'] / $total * 100, 1) : null;
            }
        }
        $metrics['cache_hit_rate'] = ($cache_hit_rate !== null) ? $cache_hit_rate : 'N/A';
        if (is_numeric($cache_hit_rate) && $cache_hit_rate < 80) {
            $score -= 10;
            $optimizations[] = 'Improve cache hit rate (target 80%+).';
        }
        $metrics['plugin_load_time'] = method_exists($this, 'calculatePluginLoadTime') ? $this->calculatePluginLoadTime() : 'N/A';
        if (is_numeric($metrics['plugin_load_time']) && $metrics['plugin_load_time'] > 500) {
            $score -= 10;
            $optimizations[] = 'Reduce plugin load time.';
        }
        $metrics['theme_load_time'] = method_exists($this, 'calculateThemeLoadTime') ? $this->calculateThemeLoadTime() : 'N/A';
        if (is_numeric($metrics['theme_load_time']) && $metrics['theme_load_time'] > 500) {
            $score -= 10;
            $optimizations[] = 'Reduce theme load time.';
        }
        $score = max(0, min(100, $score));
        wp_send_json_success([
            'results' => [
                'performance_score' => $score,
                'metrics' => $metrics,
                'optimizations' => $optimizations
            ]
        ]);
    }

    // Helper methods (copied from SystemAjax)
    private function getCacheHits(): string {
        if (function_exists('wp_cache_get_stats')) {
            $stats = wp_cache_get_stats();
            if (isset($stats['hits']) && is_numeric($stats['hits'])) {
                $hits = intval($stats['hits']);
                if ($hits > 0) {
                    return number_format($hits) . ' hits';
                } else {
                    return '0 hits (cache active)';
                }
            }
        }
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
        return 'N/A';
    }
    private function getCacheMisses(): string {
        if (function_exists('wp_cache_get_stats')) {
            $stats = wp_cache_get_stats();
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
        return 'N/A';
    }
    private function getSlowQueries(): string {
        global $wpdb;
        if (defined('SAVEQUERIES') && constant('SAVEQUERIES') && isset($wpdb->queries)) {
            $slow_count = 0;
            $total_slow_time = 0;
            foreach ($wpdb->queries as $query) {
                $query_time = floatval($query[1]);
                if ($query_time > 0.1) {
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
            $slow_queries = substr_count($log_content, 'slow query') + 
                          substr_count($log_content, 'Slow query') +
                          substr_count($log_content, 'Query took');
            return $slow_queries . ' slow queries (from log)';
        }
        return 'N/A (Cannot analyze queries)';
    }
    private function getApiCalls24h(): string {
        $response_times = get_option('ctm_api_response_times', []);
        if (!empty($response_times) && is_array($response_times)) {
            $twenty_four_hours_ago = time() - (24 * 60 * 60);
            $recent_times = [];
            foreach ($response_times as $timestamp => $response_time) {
                if ($timestamp >= $twenty_four_hours_ago) {
                    $recent_times[] = $response_time;
                }
            }
            return count($recent_times) . ' calls (24h)';
        }
        return 'N/A';
    }
    private function getApiResponseTime(): string {
        $response_times = get_option('ctm_api_response_times', []);
        if (!empty($response_times) && is_array($response_times)) {
            $twenty_four_hours_ago = time() - (24 * 60 * 60);
            $recent_times = [];
            foreach ($response_times as $timestamp => $response_time) {
                if ($timestamp >= $twenty_four_hours_ago) {
                    $recent_times[] = $response_time;
                }
            }
            if (!empty($recent_times)) {
                $avg_response_time = round(array_sum($recent_times) / count($recent_times), 2);
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
        return 'N/A';
    }
    private function calculateThemeLoadTime(): string {
        $current_theme = wp_get_theme();
        $theme_name = $current_theme->get('Name');
        if (isset($_SERVER['REQUEST_TIME_FLOAT'])) {
            $total_load_time = round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2);
            $complexity_score = 0;
            $theme_dir = get_template_directory();
            if (is_dir($theme_dir)) {
                $php_files = glob($theme_dir . '/*.php');
                $template_count = count($php_files);
                if ($template_count > 50) {
                    $complexity_score += 3;
                } elseif ($template_count > 20) {
                    $complexity_score += 2;
                } else {
                    $complexity_score += 1;
                }
            }
            $functions_php = $theme_dir . '/functions.php';
            // Could add more logic here if needed
            $theme_time = $total_load_time * (0.2 + 0.1 * $complexity_score);
            return round($theme_time, 2) . 'ms';
        }
        return 'N/A';
    }
    private function calculatePluginLoadTime(): string {
        if (isset($_SERVER['REQUEST_TIME_FLOAT'])) {
            $total_load_time = round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2);
            $plugin_time = $total_load_time * 0.25;
            return round($plugin_time, 2) . 'ms';
        }
        return 'N/A';
    }
    private function getFrontendQueries(): string {
        if (is_admin()) {
            return 'N/A';
        }
        return get_num_queries();
    }
    private function getNetworkIO(): string {
        return 'N/A';
    }
    private function getActiveSessions(): string {
        return 'N/A';
    }
    private function getErrorRate(): string {
        $debug_log = WP_CONTENT_DIR . '/debug.log';
        if (!file_exists($debug_log) || !is_readable($debug_log)) {
            return 'No debug log found';
        }
        $file_size = filesize($debug_log);
        if ($file_size === 0) {
            return '0 errors (Clean log)';
        }
        $handle = fopen($debug_log, 'r');
        if ($handle) {
            $read_size = min(10240, $file_size);
            fseek($handle, max(0, $file_size - $read_size));
            $log_content = fread($handle, $read_size);
            fclose($handle);
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
} 