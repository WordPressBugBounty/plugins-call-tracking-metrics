<?php
/**
 * CallTrackingMetrics API Service
 *
 * This file contains the ApiService class that handles all communication
 * with the CallTrackingMetrics API including authentication, data submission,
 * and account information retrieval.
 *
 * @package     CallTrackingMetrics
 * @subpackage  Service
 * @author      CallTrackingMetrics Team
 * @copyright   2024 CallTrackingMetrics
 * @license     GPL-2.0+
 * @version     2.1.3
 * @since       1.0.0
 */

namespace CTM\Service;

/**
 * CallTrackingMetrics API Service Class
 *
 * Handles all interactions with the CallTrackingMetrics API including:
 * - HTTP client configuration and request handling
 * - Authentication with API credentials
 * - Account information retrieval
 * - Form submission data transmission
 * - Error handling and response processing
 *
 * This service uses WordPress HTTP API for reliable communication
 * and includes comprehensive error handling and logging.
 *
 * @since 1.0.0
 */
class ApiService
{
    /**
     * Base URL for the CallTrackingMetrics API
     *
     * @since 1.0.0
     * @var string
     */
    private string $baseUrl;

    /**
     * HTTP client timeout in seconds
     *
     * @since 1.0.0
     * @var int
     */
    private int $timeout = 60;

    /**
     * User agent string for API requests
     *
     * @since 1.0.0
     * @var string
     */
    private string $userAgent;

    /**
     * Whether to suppress logging for automatic connection checks
     *
     * @since 2.0.0
     * @var bool
     */
    private bool $silentMode = false;

    /**
     * Initialize the API service
     *
     * Sets up the base URL and user agent for API communication.
     * The base URL should include the protocol and domain.
     *
     * @since 1.0.0
     * @param string $baseUrl The base URL for the CTM API
     */
    public function __construct(string $baseUrl)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->userAgent = 'CallTrackingMetrics-WordPress-Plugin/2.1 (+' . home_url() . ')';
    }

    /**
     * Set silent mode to suppress logging for automatic connection checks
     *
     * @since 2.0.0
     * @param bool $silent Whether to suppress logging
     * @return self
     */
    public function setSilentMode(bool $silent = true): self
    {
        $this->silentMode = $silent;
        return $this;
    }

    /**
     * Internal logging helper to prevent server log pollution
     *
     * @since 2.0.0
     * @param string $message The message to log
     * @param string $type The log type (error, debug, api, etc.)
     */
    private function logInternal(string $message, string $type = 'debug'): void
    {
        // Skip logging if in silent mode (for automatic connection checks)
        if ($this->silentMode) {
            return;
        }

        if (class_exists('\CTM\Admin\LoggingSystem')) {
            $loggingSystem = new \CTM\Admin\LoggingSystem();
            if ($loggingSystem->isDebugEnabled()) {
                $loggingSystem->logActivity($message, $type);
            }
        }
    }

    /**
     * Get account information from the CTM API
     *
     * Retrieves basic account information using the provided API credentials.
     * This is typically used for authentication testing and account validation.
     *
     * @since 1.0.0
     * @param string $apiKey    The API key for authentication
     * @param string $apiSecret The API secret for authentication
     * @return array|null The account information array or null on failure
     */
    public function getAccountInfo(string $apiKey, string $apiSecret): ?array
    {
        $endpoint = '/api/v1/accounts/';
        $start = microtime(true);
        try {
            $response = $this->makeRequest('GET', $endpoint, [], $apiKey, $apiSecret);
            $elapsed = (microtime(true) - $start) * 1000;
            $this->trackApiCall();
            $this->trackApiResponseTime($elapsed);
            // Return the first account if multiple accounts exist
            if (isset($response['accounts']) && is_array($response['accounts']) && !empty($response['accounts'])) {
                return ['account' => $response['accounts'][0]];
            }
            // Handle single account response
            if (isset($response['account'])) {
                return $response;
            }
            return null;
        } catch (\Exception $e) {
            $this->logInternal('API Error (getAccountInfo): ' . $e->getMessage(), 'error');
            return null;
        }
    }

    /**
     * Get detailed account information by account ID
     *
     * Retrieves comprehensive account details for a specific account ID.
     * This provides more detailed information than the basic account info.
     *
     * @since 1.0.0
     * @param string $accountId The account ID to retrieve details for
     * @param string $apiKey    The API key for authentication
     * @param string $apiSecret The API secret for authentication
     * @return array|null The detailed account information or null on failure
     */
    public function getAccountById(string $accountId, string $apiKey, string $apiSecret): ?array
    {
        $endpoint = "/api/v1/accounts/{$accountId}";
        $start = microtime(true);
        try {
            $data = $this->makeRequest('GET', $endpoint, [], $apiKey, $apiSecret);
            $elapsed = (microtime(true) - $start) * 1000;
            $this->trackApiCall();
            $this->trackApiResponseTime($elapsed);
            if (isset($data['error']) || (isset($data['status']) && $data['status'] === 'error')) {
                return null;
            }
            return $data;
        } catch (\Exception $e) {
            $this->logInternal('API Error (getAccountById): ' . $e->getMessage(), 'error');
            return null;
        }
    }

    /**
     * Submit form data to the CTM Form Reactor API
     *
     * Sends processed form submission data to CallTrackingMetrics for
     * lead tracking and analytics. The data should be pre-formatted
     * according to CTM API specifications.
     *
     * @since 1.0.0
     * @param array  $formData  The formatted form submission data
     * @param string $apiKey    The API key for authentication
     * @param string $apiSecret The API secret for authentication
     * @return array|null The API response or null on failure
     */
    public function submitFormReactor(array $formData, string $apiKey, string $apiSecret, $formId = null): ?array
    {
        // Work on a copy of formData to avoid side effects on the original array
        $payload = $formData;

        // Ensure form_reactor array exists
        if (!isset($payload['form_reactor']) || !is_array($payload['form_reactor'])) {
            $payload['form_reactor'] = [];
        }

        // Format phone number before sending and sync to form_reactor
        if (isset($payload['phone_number'])) {
            $payload['phone_number'] = $this->formatPhoneNumber($payload['phone_number']);
            // Always sync the formatted phone number to form_reactor to ensure it's present and formatted
            $payload['form_reactor']['phone_number'] = $payload['phone_number'];
        }

        // Sync other key fields if missing in form_reactor
        foreach (['caller_name', 'email', 'country_code'] as $key) {
            if (isset($payload[$key]) && !isset($payload['form_reactor'][$key])) {
                $payload['form_reactor'][$key] = $payload[$key];
            }
        }

        // Remove top-level contact fields from the payload after syncing to form_reactor
        // This ensures the data is strictly nested where the app expects it
        unset($payload['phone_number'], $payload['caller_name'], $payload['email'], $payload['country_code']);

        // If formId is provided, ensure it's in the form_reactor data
        if ($formId) {
            // Check if formId matches legacy format (wpcf7-ID) and extract purely numeric ID if needed for the nested object
            // However, the legacy behavior was to NOT include form_id in the nested object, so we skip adding it here.
            // $payload['form_reactor']['form_id'] = $formId;
        }

        $endpoint = '/api/v1/formreactor/'.$formId;
        $start = microtime(true);

        try {
            $data = $this->makeRequest(
                method: 'POST',
                endpoint: $endpoint,
                data: $payload,
                apiKey: $apiKey,
                apiSecret: $apiSecret,
                contentType: 'application/json'
            );
            $elapsed = (microtime(true) - $start) * 1000;
            $this->trackApiCall();
            $this->trackApiResponseTime($elapsed);

            // Return null on non-2xx response
            if (isset($data['error']) || (isset($data['status']) && $data['status'] === 'error')) {
                return null;
            }
            return $data;
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $this->logInternal('API Error (submitFormReactor): ' . $errorMessage, 'error');

            // Check for specific HTTP 406 error with phone number formatting issue
            if (strpos($errorMessage, 'HTTP 406') !== false && strpos($errorMessage, 'E.164') !== false) {
                $this->logInternal('Phone number formatting error detected: ' . $errorMessage, 'error');
                // Return a structured error response for phone number issues
                return [
                    'status' => 'error',
                    'reason' => 'Phone number must be in E.164 format (e.g., +1234567890)',
                    'error_type' => 'phone_format',
                    'original_error' => $errorMessage
                ];
            }

            // Return a generic error response instead of null to prevent white screens
            return [
                'status' => 'error',
                'reason' => 'API communication failed: ' . substr($errorMessage, 0, 100),
                'error_type' => 'api_error',
                'original_error' => $errorMessage
            ];
        }
    }

    /**
     * Get all form reactors for an account
     *
     * Retrieves a list of all form reactors configured in the CTM account.
     * This can be used for form mapping and configuration purposes.
     *
     * @since 1.0.0
     * @param string $accountId The account ID
     * @param string $apiKey    The API key for authentication
     * @param string $apiSecret The API secret for authentication
     * @return array|null Array of form reactors or null on failure
     */
    public function getFormReactors(string $accountId, string $apiKey, string $apiSecret, int $page = 1, int $perPage = 50): ?array
    {
        $endpoint = "/api/v1/accounts/{$accountId}/form_reactors";
        $params = [
            'page' => max(1, $page),
            'per_page' => min(100, max(1, $perPage))
        ];

        $start = microtime(true);
        try {
            $data = $this->makeRequest('GET', $endpoint, $params, $apiKey, $apiSecret);
            $elapsed = (microtime(true) - $start) * 1000;
            $this->trackApiCall();
            $this->trackApiResponseTime($elapsed);
            if (isset($data['error']) || (isset($data['status']) && $data['status'] === 'error')) {
                return null;
            }
            return $data;
        } catch (\Exception $e) {
            $this->logInternal('API Error (getFormReactors): ' . $e->getMessage(), 'error');
            return null;
        }
    }

    /**
     * Format phone number to E.164 format
     *
     * Converts various phone number formats to E.164 format required by CTM API.
     *
     * @since 2.0.0
     * @param string $phoneNumber The phone number to format
     * @return string The formatted phone number in E.164 format
     */
    private function formatPhoneNumber(string $phoneNumber): string
    {
        // Remove all non-numeric characters except + and spaces
        $cleaned = preg_replace('/[^0-9+\s\-\(\)]/', '', $phoneNumber);

        // Remove spaces and parentheses
        $cleaned = preg_replace('/[\s\(\)]/', '', $cleaned);

        // If it already starts with +, return as is
        if (strpos($cleaned, '+') === 0) {
            return $cleaned;
        }

        // If it starts with 1 and is 11 digits, add +
        if (strlen($cleaned) === 11 && substr($cleaned, 0, 1) === '1') {
            return '+' . $cleaned;
        }

        // If it's 10 digits (US number), add +1
        if (strlen($cleaned) === 10) {
            return '+1' . $cleaned;
        }

        // If it's 7 digits (local number), assume US and add +1
        if (strlen($cleaned) === 7) {
            return '+1' . $cleaned;
        }

        // If it doesn't start with + and is longer than 10 digits, add +
        if (strlen($cleaned) > 10 && substr($cleaned, 0, 1) !== '+') {
            return '+' . $cleaned;
        }

        // For any other number without + prefix, assume US and add +1
        if (strlen($cleaned) > 0 && substr($cleaned, 0, 1) !== '+') {
            return '+1' . $cleaned;
        }

        // Return as is if we can't determine format
        return $cleaned;
    }

    /**
     * Get all form reactors for an account with automatic pagination
     *
     * Retrieves all form reactors by automatically handling pagination.
     *
     * @since 1.0.0
     * @param string $accountId The account ID
     * @param string $apiKey    The API key for authentication
     * @param string $apiSecret The API secret for authentication
     * @param int    $perPage   Items per page (default: 50, max: 100)
     * @return array|null Array of all form reactors or null on failure
     */
    public function getAllFormReactors(string $accountId, string $apiKey, string $apiSecret, int $perPage = 50): ?array
    {
        $allFormReactors = [];
        $page = 1;
        $hasMorePages = true;

        while ($hasMorePages) {
            $response = $this->getFormReactors($accountId, $apiKey, $apiSecret, $page, $perPage);

            if (!$response || !isset($response['form_reactors'])) {
                break;
            }

            $formReactors = $response['form_reactors'];
            $allFormReactors = array_merge($allFormReactors, $formReactors);

            // Check if there are more pages
            $totalPages = $response['pagination']['total_pages'] ?? 1;
            $hasMorePages = $page < $totalPages;
            $page++;

                    // Safety check to prevent infinite loops
        if ($page > 50) {
            $this->logInternal('API Error: Pagination limit exceeded (50 pages)', 'error');
            break;
        }
        }

        return [
            'form_reactors' => $allFormReactors,
            'pagination' => [
                'total_items' => count($allFormReactors),
                'total_pages' => $page - 1,
                'per_page' => $perPage
            ]
        ];
    }

    /**
     * Get a specific form reactor by ID
     *
     * Retrieves detailed information about a specific form reactor.
     *
     * @since 1.0.0
     * @param string $accountId      The account ID
     * @param string $formReactorId  The form reactor ID
     * @param string $apiKey         The API key for authentication
     * @param string $apiSecret      The API secret for authentication
     * @return array|null Form reactor details or null on failure
     */
    public function getFormReactorById(string $accountId, string $formReactorId, string $apiKey, string $apiSecret): ?array
    {
        $endpoint = "/api/v1/accounts/{$accountId}/form_reactors/{$formReactorId}";
        $start = microtime(true);
        try {
            $data = $this->makeRequest('GET', $endpoint, [], $apiKey, $apiSecret);
            $elapsed = (microtime(true) - $start) * 1000;
            $this->trackApiCall();
            $this->trackApiResponseTime($elapsed);
            if (isset($data['error']) || (isset($data['status']) && $data['status'] === 'error')) {
                return null;
            }
            return $data;
        } catch (\Exception $e) {
            $this->logInternal('API Error (getFormReactorById): ' . $e->getMessage(), 'error');
            return null;
        }
    }

    /**
     * Get forms from CTM API
     *
     * Retrieves forms directly from the CTM API using the form_reactors endpoint.
     * This method handles the new API response format with 'forms' array.
     *
     * @since 2.0.0
     * @param string $apiKey    The API key for authentication
     * @param string $apiSecret The API secret for authentication
     * @param int    $page      Page number (default: 1)
     * @param int    $perPage   Items per page (default: 50, max: 100)
     * @return array|null Array of forms or null on failure
     */
    public function getFormsDirect(string $apiKey, string $apiSecret, int $page = 1, int $perPage = 50): ?array
    {
        // First get account information to get the account ID
        $accountInfo = $this->getAccountInfo($apiKey, $apiSecret);
        if (!$accountInfo || !isset($accountInfo['account']['id'])) {
            $this->logInternal('API Error: Could not retrieve account information for forms', 'error');
            return null;
        }

        $accountId = $accountInfo['account']['id'];

        $endpoint = "/api/v1/accounts/{$accountId}/form_reactors";
        $params = [
            'page' => max(1, $page),
            'per_page' => min(100, max(1, $perPage))
        ];

        $start = microtime(true);
        try {
            $data = $this->makeRequest('GET', $endpoint, $params, $apiKey, $apiSecret);
            $elapsed = (microtime(true) - $start) * 1000;
            $this->trackApiCall();
            $this->trackApiResponseTime($elapsed);

            if (isset($data['error']) || (isset($data['status']) && $data['status'] === 'error')) {
                $this->logInternal('API Error: API returned error response', 'error');
                return null;
            }

            return $data;
        } catch (\Exception $e) {
            $this->logInternal('API Error (getFormsDirect): ' . $e->getMessage(), 'error');
            return null;
        }
    }

    /**
     * Get forms from CTM API (backward compatibility)
     *
     * @since 1.0.0
     * @param string $apiKey    The API key for authentication
     * @param string $apiSecret The API secret for authentication
     * @return array|null Array of forms or null on failure
     */
    public function getForms(string $apiKey, string $apiSecret): ?array
    {
        // First try the direct forms endpoint
        $forms = $this->getFormsDirect($apiKey, $apiSecret);
        if ($forms && isset($forms['forms'])) {
            return $forms;
        }

        // Fallback to account-based approach
        $accountInfo = $this->getAccountInfo($apiKey, $apiSecret);
        if (!$accountInfo || !isset($accountInfo['account']['id'])) {
            return null;
        }

        $accountId = $accountInfo['account']['id'];
        return $this->getFormReactors($accountId, $apiKey, $apiSecret);
    }

    /**
     * Get tracking numbers for an account with pagination support
     *
     * Retrieves all tracking phone numbers associated with the account.
     * This can be used for call tracking and analytics purposes.
     *
     * @since 1.0.0
     * @param string $apiKey    The API key for authentication
     * @param string $apiSecret The API secret for authentication
     * @param int    $page      Page number (default: 1)
     * @param int    $perPage   Items per page (default: 50, max: 100)
     * @return array|null Array of tracking numbers or null on failure
     */
    public function getTrackingNumbers(string $apiKey, string $apiSecret, int $page = 1, int $perPage = 50): ?array
    {
        $endpoint = '/api/v1/tracking_numbers';
        $params = [
            'page' => max(1, $page),
            'per_page' => min(100, max(1, $perPage))
        ];

        $start = microtime(true);
        try {
            $data = $this->makeRequest('GET', $endpoint, $params, $apiKey, $apiSecret);
            $elapsed = (microtime(true) - $start) * 1000;
            $this->trackApiCall();
            $this->trackApiResponseTime($elapsed);
            if (isset($data['error']) || (isset($data['status']) && $data['status'] === 'error')) {
                return null;
            }
            return $data;
        } catch (\Exception $e) {
            $this->logInternal('API Error (getTrackingNumbers): ' . $e->getMessage(), 'error');
            return null;
        }
    }

    /**
     * Get call data for analytics
     *
     * Retrieves call data and analytics from the CTM API.
     * Can be filtered by date range and other parameters.
     *
     * @since 1.0.0
     * @param string $apiKey    The API key for authentication
     * @param string $apiSecret The API secret for authentication
     * @param array  $params    Query parameters for filtering (optional)
     * @return array|null Array of call data or null on failure
     */
    public function getCalls(string $apiKey, string $apiSecret, array $params = []): ?array
    {
        $endpoint = '/api/v1/calls';
        $start = microtime(true);
        try {
            $data = $this->makeRequest('GET', $endpoint, $params, $apiKey, $apiSecret);
            $elapsed = (microtime(true) - $start) * 1000;
            $this->trackApiCall();
            $this->trackApiResponseTime($elapsed);
            if (isset($data['error']) || (isset($data['status']) && $data['status'] === 'error')) {
                return null;
            }
            return $data;
        } catch (\Exception $e) {
            $this->logInternal('API Error (getCalls): ' . $e->getMessage(), 'error');
            return null;
        }
    }

    /**
     * Get the tracking script for an account
     *
     * @param string $accountId
     * @param string $apiKey
     * @param string $apiSecret
     * @return array|null
     */
    public function getTrackingScript(string $accountId, string $apiKey, string $apiSecret): ?array
    {
        $start = microtime(true);
        try {
            $result = $this->makeRequest('GET', "/api/v1/accounts/{$accountId}/scripts", [], $apiKey, $apiSecret);
            $elapsed = (microtime(true) - $start) * 1000;
            $this->trackApiCall();
            $this->trackApiResponseTime($elapsed);
            return $result;
        } catch (\Exception $e) {
            $this->logInternal('API Error (getTrackingScript): ' . $e->getMessage(), 'error');
            return null;
        }
    }

    /**
     * Track API call for rate limiting/monitoring
     *
     * @since 2.0.0
     * @return void
     */
    private function trackApiCall(): void
    {
        $calls = get_option('ctm_api_calls_24h', []);
        if (!is_array($calls)) {
            $calls = [];
        }

        $now = time();
        $calls[] = $now;

        // Remove calls older than 24 hours
        $calls = array_filter($calls, function($timestamp) use ($now) {
            return $timestamp > ($now - 86400);
        });

        update_option('ctm_api_calls_24h', array_values($calls), false);
    }

    /**
     * Track API response time
     *
     * @since 2.0.0
     * @param float $duration Duration in milliseconds
     * @return void
     */
    private function trackApiResponseTime(float $duration): void
    {
        $times = get_option('ctm_api_response_times', []);
        if (!is_array($times)) {
            $times = [];
        }

        $times[] = $duration;

        // Keep last 100 response times
        if (count($times) > 100) {
            $times = array_slice($times, -100);
        }

        update_option('ctm_api_response_times', $times, false);
    }

    /**
     * Get a unique key for an API call
     *
     * @since 2.0.0
     * @param string $url The API URL
     * @param string $method The HTTP method
     * @return string Unique key
     */
    private function getApiCallKey(string $url, string $method): string
    {
        return md5($method . '_' . $url);
    }

    /**
     * Set the timeout for API requests
     *
     * @param int $timeout Timeout in seconds
     * @return self
     */
    public function setTimeout(int $timeout): self
    {
        $this->timeout = max(1, $timeout);
        return $this;
    }

    /**
     * Get the current timeout setting
     *
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * Get the base URL
     *
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Make an HTTP request to the CTM API
     *
     * Core method that handles all HTTP communication with the API including
     * authentication, request formatting, error handling, and response processing.
     *
     * @since 1.0.0
     * @param string $method    HTTP method (GET, POST, PUT, DELETE)
     * @param string $endpoint  API endpoint path
     * @param array  $data      Request data for POST/PUT requests
     * @param string $apiKey    API key for authentication
     * @param string $apiSecret API secret for authentication
     * @param string $contentType Content-Type header (default: application/json)
     * @return array The decoded API response
     * @throws \Exception On HTTP errors or invalid responses
     */
    private function makeRequest(string $method, string $endpoint, array $data = [], string $apiKey = '', string $apiSecret = '', string $contentType = 'application/json'): array
    {
        $url = $this->baseUrl . $endpoint;

        // Use internal logging system
        $loggingSystem = null;
        if (class_exists('\CTM\Admin\LoggingSystem')) {
            $loggingSystem = new \CTM\Admin\LoggingSystem();
        }

        // Only log if debug mode is enabled, logging system is available, and not in silent mode
        $should_log = $loggingSystem && $loggingSystem->isDebugEnabled() && !$this->silentMode;

        if ($should_log) {
            // Group API calls by URL and method
            $api_key = $this->getApiCallKey($url, $method);
            $loggingSystem->logActivity("API Request - URL: {$url}, Method: {$method}", 'api', [
                'api_call_key' => $api_key,
                'url' => $url,
                'method' => $method
            ]);
        }

        $args = [
            'method'  => strtoupper($method),
            'timeout' => $this->timeout,
            'headers' => [
                'User-Agent'   => $this->userAgent,
                'Accept'       => 'application/json',
                'Content-Type' => $contentType,
            ],
        ];

        if (!empty($apiKey) && !empty($apiSecret)) {
            $args['headers']['Authorization'] = 'Basic ' . base64_encode($apiKey . ':' . $apiSecret);
        }

        // Handle body encoding
        if (in_array($method, ['POST', 'PUT']) && !empty($data)) {
            if ($contentType === 'application/json') {
                $args['body'] = json_encode($data);
            } elseif ($contentType === 'application/x-www-form-urlencoded') {
                $args['body'] = http_build_query($data);
            } else {
                $args['body'] = $data;
            }
        }

        $response = \wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            $errorMessage = 'HTTP request failed: ' . $response->get_error_message();
            if ($should_log) {
                $loggingSystem->logActivity("API Error - {$errorMessage}", 'error');
            }
            throw new \Exception($errorMessage);
        }

        $statusCode = \wp_remote_retrieve_response_code($response);
        $body = \wp_remote_retrieve_body($response);

        // Handle non-JSON "success" response from CTM API
        if (trim($body) === 'success') {
            return ['status' => 'success'];
        }

        if ($statusCode >= 400) {
            $errorMessage = 'HTTP ' . $statusCode . ' error: ' . $body;
            if ($should_log) {
                $loggingSystem->logActivity("API Error - {$errorMessage}", 'error');
            }
            throw new \Exception($errorMessage);
        }

        $result = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $errorMessage = 'Invalid JSON response: ' . json_last_error_msg();
            if ($should_log) {
                $loggingSystem->logActivity("API Error - {$errorMessage}", 'error');
                $loggingSystem->logActivity("API Error - Raw response: " . substr($body, 0, 500), 'error');
            }
            throw new \Exception($errorMessage);
        }

        return $result;
    }

    /**
     * Validate API credentials
     *
     * @since 2.0.0
     * @param string $apiKey The API key
     * @param string $apiSecret The API secret
     * @return bool True if credentials are valid, false otherwise
     */
    public function validateCredentials(string $apiKey, string $apiSecret): bool
    {
        try {
            $response = wp_remote_request($this->baseUrl . '/accounts', [
                'method' => 'GET',
                'timeout' => $this->timeout,
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($apiKey . ':' . $apiSecret),
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ]
            ]);

            if (is_wp_error($response)) {
                return false;
            }

            $status_code = wp_remote_retrieve_response_code($response);
            return $status_code === 200;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check API health status
     *
     * @since 2.0.0
     * @return bool True if API is healthy, false otherwise
     */
    public function checkApiHealth(): bool
    {
        try {
            $response = wp_remote_get($this->baseUrl . '/ping', [
                'timeout' => $this->timeout
            ]);

            if (is_wp_error($response)) {
                return false;
            }

            $status_code = wp_remote_retrieve_response_code($response);
            return $status_code === 200;
        } catch (\Exception $e) {
            return false;
        }
    }
}
