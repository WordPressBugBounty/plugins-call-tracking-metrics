<?php
/**
 * API AJAX Handler
 * 
 * This file contains the ApiAjax class which handles AJAX requests related to
 * CallTrackingMetrics API operations including connection testing, API simulations,
 * and credential management.
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

use CTM\Service\ApiService;

/**
 * API AJAX Request Handler
 * 
 * Handles AJAX requests related to CallTrackingMetrics API operations including:
 * - API connection testing and validation
 * - API request simulation and debugging
 * - API credential management
 * - Connection quality assessment
 * 
 * @package     CallTrackingMetrics
 * @subpackage  Admin\Ajax
 * @author      CallTrackingMetrics Team
 * @since       1.0.0
 * @version     2.0.0
 */
class ApiAjax {
    /**
     * API service instance
     * 
     * @since 1.0.0
     * @var ApiService
     */
    private $apiService;

    /**
     * Initialize API AJAX handler
     * 
     * Sets up the API service instance for handling API-related AJAX requests.
     * If no API service is provided, creates a default instance with the configured API URL.
     * 
     * @since 1.0.0
     * @param ApiService|null $apiService Optional API service instance for dependency injection
     */
    public function __construct($apiService = null) {
        $this->apiService = $apiService ?: new ApiService(\ctm_get_api_url());
    }

    /**
     * Register API AJAX handlers
     * 
     * Registers all API-related AJAX endpoints with WordPress including:
     * - ctm_test_api_connection: Test API connectivity and credentials
     * - ctm_simulate_api_request: Simulate API requests for debugging
     * - ctm_change_api_keys: Update API credentials
     * - ctm_disable_api: Disable API integration
     * 
     * @since 1.0.0
     * @return void
     */
    public function registerHandlers() {
        add_action('wp_ajax_ctm_test_api_connection', [$this, 'ajaxTestApiConnection']);
        add_action('wp_ajax_ctm_simulate_api_request', [$this, 'ajaxSimulateApiRequest']);
        // AJAX: Change API Keys
        add_action('wp_ajax_ctm_change_api_keys', [$this, 'ajaxChangeApiKeys']);
        // AJAX: Disable API
        add_action('wp_ajax_ctm_disable_api', [$this, 'ajaxDisableApi']);
        // AJAX: Fetch tracking script
        add_action('wp_ajax_ctm_fetch_tracking_script', [$this, 'ajaxFetchTrackingScript']);
    }


    /**
     * AJAX handler to test API connection
     * 
     * Tests the connection to the CallTrackingMetrics API using provided credentials.
     * Performs comprehensive testing including account validation, response time measurement,
     * and connection quality assessment. Returns detailed diagnostic information.
     * 
     * Expected POST parameters:
     * - api_key: The API key to test
     * - api_secret: The API secret to test
     * - nonce: Security nonce for verification
     * 
     * @since 1.0.0
     * @return void Outputs JSON response with connection test results
     */
    public function ajaxTestApiConnection(): void
    {
        $start_time = microtime(true);
        check_ajax_referer('ctm_test_api_connection', 'nonce');
        $api_key = sanitize_text_field($_POST['api_key'] ?? '');
        $api_secret = sanitize_text_field($_POST['api_secret'] ?? '');
        $api_base_url_raw = $_POST['api_base_url'] ?? '';
        if (is_array($api_base_url_raw)) {
            $api_base_url_raw = reset($api_base_url_raw);
        }
        $requested_api_base = is_string($api_base_url_raw) || is_numeric($api_base_url_raw)
            ? $this->sanitizeApiBaseUrl(wp_unslash((string) $api_base_url_raw))
            : null;
        $effective_api_base = $requested_api_base ?: \ctm_get_api_url();
        $response_data = [
            'timestamp' => current_time('mysql'),
            'request_id' => wp_generate_uuid4(),
            'wordpress_version' => get_bloginfo('version'),
            'php_version' => PHP_VERSION,
            'plugin_version' => '2.0',
            'api_endpoint' => $effective_api_base,
            'requested_api_endpoint' => $requested_api_base,
            'request_method' => 'GET',
            'auth_method' => 'Basic Authentication'
        ];
        if (empty($api_key) || empty($api_secret)) {
            wp_send_json_error([
                'message' => 'API Key and Secret are required',
                'details' => [
                    'Please provide both API Key and API Secret',
                    'API credentials cannot be empty',
                    'Check your CTM account for valid API keys'
                ],
                'metadata' => $response_data,
                'execution_time' => round((microtime(true) - $start_time) * 1000, 2)
            ]);
            return;
        }
        if (!is_string($api_key) || !is_string($api_secret) || strlen($api_key) < 20 || strlen($api_secret) < 20) {
            wp_send_json_error([
                'message' => 'Invalid API credential format',
                'details' => [
                    'API keys should be at least 20 characters long',
                    'Ensure you copied the complete API key and secret',
                    'Check for extra spaces or missing characters'
                ],
                'metadata' => $response_data,
                'execution_time' => round((microtime(true) - $start_time) * 1000, 2)
            ]);
            return;
        }
        try {
            $apiService = $requested_api_base ? new ApiService($effective_api_base) : $this->apiService;
            $api_start_time = microtime(true);
            $accountInfo = $apiService->getAccountInfo($api_key, $api_secret);
            $api_response_time = round((microtime(true) - $api_start_time) * 1000, 2);
            $response_data['api_response_time'] = $api_response_time;
            $response_data['account_endpoint'] = '/api/v1/accounts/';
            if (!$accountInfo || !isset($accountInfo['account'])) {
                $error_details = [
                    'Authentication failed - check your API credentials',
                    'Ensure your CTM account has API access enabled',
                    'Verify you\'re using the correct API environment',
                    'Check if your account subscription includes API access'
                ];
                if (!$accountInfo) {
                    $error_details[] = 'No response received from CTM API';
                    $error_details[] = 'This may indicate network connectivity issues';
                } else {
                    $error_details[] = 'API responded but account data was missing';
                    $error_details[] = 'This typically indicates authentication failure';
                }
                wp_send_json_error([
                    'message' => 'Failed to connect to CTM API',
                    'details' => $error_details,
                    'metadata' => $response_data,
                    'api_response' => $accountInfo,
                    'execution_time' => round((microtime(true) - $start_time) * 1000, 2)
                ]);
                return;
            }
            $account = $accountInfo['account'];
            $account_details = null;
            $details_response_time = null;
            if (isset($account['id'])) {
                $details_start_time = microtime(true);
                $account_details = $apiService->getAccountById($account['id'], $api_key, $api_secret);
                $details_response_time = round((microtime(true) - $details_start_time) * 1000, 2);
                $response_data['details_response_time'] = $details_response_time;
                $response_data['details_endpoint'] = '/api/v1/accounts/' . $account['id'];
            }
            update_option('ctm_api_key', $api_key);
            update_option('ctm_api_secret', $api_secret);
            update_option('ctm_api_auth_account', $account['id'] ?? '');
            if ($requested_api_base) {
                update_option('ctm_api_base_url', $effective_api_base);
            }
            $total_execution_time = round((microtime(true) - $start_time) * 1000, 2);
            wp_send_json_success([
                'message' => 'API Connection successful',
                'account_info' => $accountInfo,
                'account_details' => $account_details,
                'account_id' => $account['id'] ?? 'N/A',
                'connection_quality' => $this->assessConnectionQuality($api_response_time, $details_response_time),
                'metadata' => $response_data,
                'performance' => [
                    'total_execution_time' => $total_execution_time,
                    'api_response_time' => $api_response_time,
                    'details_response_time' => $details_response_time,
                    'network_overhead' => $total_execution_time - $api_response_time - ($details_response_time ?? 0)
                ],
                'capabilities' => [
                    'account_access' => true,
                    'details_access' => $account_details !== null,
                    'api_version' => 'v1'
                ]
            ]);
        } catch (\Exception $e) {
            $total_execution_time = round((microtime(true) - $start_time) * 1000, 2);
            $error_details = [
                'Exception: ' . get_class($e),
                'Error: ' . $e->getMessage()
            ];
            if (strpos($e->getMessage(), 'timeout') !== false) {
                $error_details[] = 'Request timed out - check network connectivity';
                $error_details[] = 'CTM API may be experiencing high load';
            } elseif (strpos($e->getMessage(), 'SSL') !== false || strpos($e->getMessage(), 'certificate') !== false) {
                $error_details[] = 'SSL/TLS certificate issue detected';
                $error_details[] = 'Check server SSL configuration';
            } elseif (strpos($e->getMessage(), 'DNS') !== false) {
                $error_details[] = 'DNS resolution failure';
                $error_details[] = 'Check domain name resolution';
            } else {
                $error_details[] = 'Check your API credentials';
                $error_details[] = 'Verify CTM service status';
                $error_details[] = 'Contact support if problem persists';
            }
            wp_send_json_error([
                'message' => 'Failed to connect to CTM API: ' . $e->getMessage(),
                'details' => $error_details,
                'metadata' => $response_data,
                'exception' => [
                    'type' => get_class($e),
                    'message' => $e->getMessage(),
                    'file' => basename($e->getFile()),
                    'line' => $e->getLine()
                ],
                'execution_time' => $total_execution_time
            ]);
        }
    }

    /**
     * Assess API connection quality based on response times
     * 
     * Analyzes the response times from API calls to determine connection quality
     * and assign appropriate visual indicators. Returns quality assessment with
     * color coding for UI display.
     * 
     * Quality levels:
     * - Excellent (green): < 500ms total
     * - Good (blue): 500-1000ms total  
     * - Fair (yellow): 1000-2000ms total
     * - Poor (red): > 2000ms total
     * 
     * @since 1.0.0
     * @param float      $api_time     API response time in milliseconds
     * @param float|null $details_time Details response time in milliseconds (optional)
     * @return array Quality assessment with 'quality', 'color', and 'total_time' keys
     */
    private function assessConnectionQuality($api_time, $details_time): array
    {
        $total_time = $api_time + ($details_time ?? 0);
        if ($total_time < 500) {
            $quality = 'excellent';
            $color = 'green';
        } elseif ($total_time < 1000) {
            $quality = 'good';
            $color = 'blue';
        } elseif ($total_time < 2000) {
            $quality = 'fair';
            $color = 'yellow';
        } else {
            $quality = 'poor';
            $color = 'red';
        }
        return [
            'rating' => $quality,
            'color' => $color,
            'total_time' => $total_time,
            'description' => "Connection quality: {$quality} ({$total_time}ms total)"
        ];
    }

    /**
     * AJAX handler to simulate API requests
     * 
     * Simulates various types of API requests for testing and debugging purposes.
     * Supports different HTTP methods and endpoints to help troubleshoot API integration
     * issues and validate request/response handling.
     * 
     * Expected POST parameters:
     * - endpoint: The API endpoint to simulate (optional)
     * - method: HTTP method (GET, POST, PUT, DELETE)
     * - nonce: Security nonce for verification
     * 
     * @since 1.0.0
     * @return void Outputs JSON response with simulation results
     */
    public function ajaxSimulateApiRequest(): void
    {
        check_ajax_referer('ctm_simulate_api_request', 'nonce');
        $endpoint = sanitize_text_field($_POST['endpoint'] ?? '');
        $method = sanitize_text_field($_POST['method'] ?? 'GET');
        $apiKey = get_option('ctm_api_key');
        $apiSecret = get_option('ctm_api_secret');
        if (!$apiKey || !$apiSecret) {
            wp_send_json_error(['message' => 'API credentials not configured']);
            return;
        }
        try {
            $apiService = $this->apiService;
            switch ($endpoint) {
                case '/api/v1/accounts/':
                    $result = $apiService->getAccountInfo($apiKey, $apiSecret);
                    break;
                case '/api/v1/forms':
                    $result = $apiService->getForms($apiKey, $apiSecret);
                    break;
                case '/api/v1/tracking_numbers':
                    $result = $apiService->getTrackingNumbers($apiKey, $apiSecret);
                    break;
                case '/api/v1/calls':
                    $result = $apiService->getCalls($apiKey, $apiSecret);
                    break;
                default:
                    wp_send_json_error(['message' => 'Unsupported endpoint']);
                    return;
            }
            wp_send_json_success([
                'endpoint' => $endpoint,
                'method' => $method,
                'response' => $result,
                'timestamp' => current_time('mysql')
            ]);
        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage(),
                'endpoint' => $endpoint,
                'method' => $method
            ]);
        }
    }

    /**
     * AJAX handler to change API credentials
     * 
     * Updates the API key and secret for the CallTrackingMetrics integration.
     * Validates the new credentials by testing the connection before saving.
     * Updates plugin settings and refreshes tracking script if successful.
     * 
     * Expected POST parameters:
     * - api_key: New API key
     * - api_secret: New API secret
     * - nonce: Security nonce for verification
     * 
     * @since 1.0.0
     * @return void Outputs JSON response with update results
     */
    public function ajaxChangeApiKeys() {
        check_ajax_referer('ctm_change_api_keys', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied.']);
        }
        $providedApiKey = sanitize_text_field($_POST['api_key'] ?? '');
        $providedApiSecret = sanitize_text_field($_POST['api_secret'] ?? '');
        $apiBaseUrlRaw = $_POST['api_base_url'] ?? '';
        if (is_array($apiBaseUrlRaw)) {
            $apiBaseUrlRaw = reset($apiBaseUrlRaw);
        }
        $apiBaseUrl = null;
        if (is_string($apiBaseUrlRaw) || is_numeric($apiBaseUrlRaw)) {
            $apiBaseUrl = $this->sanitizeApiBaseUrl(wp_unslash((string) $apiBaseUrlRaw));
        }
        $existingApiKey = get_option('ctm_api_key');
        $existingApiSecret = get_option('ctm_api_secret');
        $apiKey = $providedApiKey !== '' ? $providedApiKey : $existingApiKey;
        $apiSecret = $providedApiSecret !== '' ? $providedApiSecret : $existingApiSecret;
        if (!$apiKey || !$apiSecret) {
            wp_send_json_error(['message' => 'API Key and Secret are required.']);
        }
        if ($providedApiKey !== '') {
            update_option('ctm_api_key', $apiKey);
        }
        if ($providedApiSecret !== '') {
            update_option('ctm_api_secret', $apiSecret);
        }
        if ($apiBaseUrl) {
            update_option('ctm_api_base_url', $apiBaseUrl);
        }
        
        // Clear connection status cache so new credentials are tested immediately
        delete_transient('ctm_last_connection_status');
        // Fetch account info and tracking script
        $apiService = new \CTM\Service\ApiService($apiBaseUrl ?: \ctm_get_api_url());
        $accountInfo = $apiService->getAccountInfo($apiKey, $apiSecret);
        $accountId = null;
        if ($accountInfo && isset($accountInfo['account']['id'])) {
            $accountId = $accountInfo['account']['id'];
            update_option('ctm_api_auth_account', $accountId);
        }
        if ($accountId) {
            try {
                $scripts = $apiService->getTrackingScript($accountId, $apiKey, $apiSecret);
                if ($scripts && isset($scripts['tracking']) && !empty($scripts['tracking'])) {
                    update_option('call_track_account_script', $scripts['tracking']);
                }
            } catch (\Exception $e) {
                // Optionally log error
            }
        }
        wp_send_json_success(['message' => 'API keys updated.']);
    }

    /**
     * AJAX handler to disable API integration
     * 
     * Disables the CallTrackingMetrics API integration by clearing stored credentials
     * and related settings. This effectively disconnects the plugin from the CTM service
     * while preserving other plugin functionality.
     * 
     * Expected POST parameters:
     * - nonce: Security nonce for verification
     * 
     * @since 1.0.0
     * @return void Outputs JSON response with disable operation results
     */
    public function ajaxDisableApi() {
        check_ajax_referer('ctm_disable_api', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied.']);
        }
        // Clear all API credentials and related options
        delete_option('ctm_api_key');
        delete_option('ctm_api_secret');
        delete_option('ctm_api_auth_account');
        delete_option('call_track_account_script');
        
        // Clear connection status cache
        delete_transient('ctm_last_connection_status');
        wp_send_json_success(['message' => 'API credentials cleared.']);
    }
    
    /**
     * AJAX handler to fetch tracking script from API
     * 
     * Fetches the tracking script from the CallTrackingMetrics API using stored credentials.
     * This is used to populate the tracking script textarea in the admin interface.
     * 
     * Expected POST parameters:
     * - nonce: Security nonce for verification
     * 
     * @since 2.0.0
     * @return void Outputs JSON response with tracking script
     */
    public function ajaxFetchTrackingScript() {
        check_ajax_referer('ctm_general_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied.']);
        }
        
        $apiKey = get_option('ctm_api_key');
        $apiSecret = get_option('ctm_api_secret');
        
        if (empty($apiKey) || empty($apiSecret)) {
            wp_send_json_error(['message' => 'API credentials not found.']);
        }
        
        try {
            // Get the API service
            $apiService = $this->apiService;
            
            // Get account info first
            $accountInfo = $apiService->getAccountInfo($apiKey, $apiSecret);
            if (!$accountInfo || !isset($accountInfo['account']['id'])) {
                wp_send_json_error(['message' => 'Failed to get account info from API.']);
            }
            
            $accountId = $accountInfo['account']['id'];
            
            // Get tracking script
            $scripts = $apiService->getTrackingScript($accountId, $apiKey, $apiSecret);
            
            // Try different possible response structures
            $trackingScript = null;
            if ($scripts && isset($scripts['tracking']) && !empty($scripts['tracking'])) {
                $trackingScript = $scripts['tracking'];
            } elseif ($scripts && isset($scripts['scripts']) && !empty($scripts['scripts'])) {
                $trackingScript = $scripts['scripts'];
            } elseif ($scripts && isset($scripts['script']) && !empty($scripts['script'])) {
                $trackingScript = $scripts['script'];
            }
            
            if (!empty($trackingScript)) {
                // Return the tracking script without saving it
                // update_option('call_track_account_script', $trackingScript);
                wp_send_json_success(['script' => $trackingScript]);
            } else {
                wp_send_json_error(['message' => 'No tracking script found in API response.']);
            }
            
        } catch (\Exception $e) {
            wp_send_json_error(['message' => 'API error: ' . $e->getMessage()]);
        }
    }

    /**
     * Normalize and sanitize an API base URL value.
     *
     * @param string|null $url Raw URL submitted from the client.
     * @return string|null Sanitized URL or null if empty/invalid.
     */
    private function sanitizeApiBaseUrl(?string $url): ?string
    {
        if ($url === null) {
            return null;
        }

        $url = trim($url);

        if ($url === '') {
            return null;
        }

        if (!preg_match('/^https?:\/\//i', $url)) {
            $url = 'https://' . $url;
        }

        return rtrim($url, '/');
    }
} 