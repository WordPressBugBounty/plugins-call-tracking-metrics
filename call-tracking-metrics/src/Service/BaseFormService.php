<?php
/**
 * Base Form Service
 * 
 * This file contains the abstract BaseFormService class that provides common
 * functionality for all form processing services in the CallTrackingMetrics plugin.
 * 
 * @package     CallTrackingMetrics
 * @subpackage  Service
 * @author      CallTrackingMetrics Team
 * @copyright   2024 CallTrackingMetrics
 * @license     GPL-2.0+
 * @version     2.0.0
 * @link        https://calltrackingmetrics.com
 * @since       2.0.0
 */

namespace CTM\Service;

/**
 * Base Form Service
 * 
 * Provides common functionality for form processing services.
 * Contains shared methods for UTM parameter extraction, field sanitization,
 * logging, and other common form processing tasks.
 * 
 * @since 2.0.0
 */
abstract class BaseFormService
{
    /**
     * Logging System instance
     * 
     * @since 2.0.0
     * @var \CTM\Admin\LoggingSystem|null
     */
    protected $loggingSystem;

    /**
     * Initialize the base form service
     * 
     * @since 2.0.0
     * @param \CTM\Admin\LoggingSystem|null $loggingSystem The logging system
     */
    public function __construct($loggingSystem = null)
    {
        $this->loggingSystem = $loggingSystem;
    }

    /**
     * Internal logging helper to prevent server log pollution
     * 
     * @since 2.0.0
     * @param string $message The message to log
     * @param string $type The log type (error, debug, api, etc.)
     */
    protected function logInternal(string $message, string $type = 'debug'): void
    {
        if ($this->loggingSystem && $this->loggingSystem->isDebugEnabled()) {
            $this->loggingSystem->logActivity($message, $type);
        }
    }

    /**
     * Extract UTM parameters from URL ($_GET)
     * 
     * Used for form plugins that don't automatically capture UTM parameters.
     * Extracts current page UTM parameters at submission time.
     * 
     * @since 2.0.0
     * @return array UTM parameters from URL
     */
    protected function extractUtmFromUrl(): array
    {
        $utmParams = [];
        $utmKeys = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'];
        
        foreach ($utmKeys as $key) {
            if (!empty($_GET[$key])) {
                $utmParams[$key] = sanitize_text_field($_GET[$key]);
            }
        }
        
        return $utmParams;
    }

    /**
     * Extract UTM parameters from form entry data
     * 
     * Used for form plugins that automatically capture and store UTM parameters.
     * Extracts UTM parameters from stored entry data.
     * 
     * @since 2.0.0
     * @param array $entry The form entry data
     * @return array UTM parameters from entry
     */
    protected function extractUtmFromEntry(array $entry): array
    {
        $utmParams = [];
        $utmKeys = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'];
        
        foreach ($utmKeys as $key) {
            if (!empty($entry[$key])) {
                $utmParams[$key] = sanitize_text_field($entry[$key]);
            }
        }
        
        return $utmParams;
    }

    /**
     * Get the client's IP address with fallback methods
     * 
     * Attempts multiple methods to determine the client's real IP address,
     * accounting for proxies, load balancers, and CDNs.
     * 
     * @since 2.0.0
     * @return string The client IP address
     */
    protected function getClientIpAddress(): string
    {
        // Check for various headers that might contain the real IP
        $headers = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_CLIENT_IP',            // Proxy
            'HTTP_X_FORWARDED_FOR',      // Load balancer/proxy
            'HTTP_X_FORWARDED',          // Proxy
            'HTTP_X_CLUSTER_CLIENT_IP',  // Cluster
            'HTTP_FORWARDED_FOR',        // Proxy
            'HTTP_FORWARDED',            // Proxy
            'REMOTE_ADDR'                // Standard
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                
                // Handle comma-separated list (X-Forwarded-For can contain multiple IPs)
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                
                // Validate IP address
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        // Fallback to REMOTE_ADDR even if it's private/reserved
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    /**
     * Sanitize field value based on field type
     * 
     * Applies appropriate sanitization based on the field type to ensure
     * data integrity and security.
     * 
     * @since 2.0.0
     * @param mixed $value The field value to sanitize
     * @param string $fieldType The type of field (email, phone, url, etc.)
     * @return string The sanitized value
     */
    protected function sanitizeFieldValue($value, string $fieldType = 'text'): string
    {
        // Handle array values (checkboxes, multi-select, list fields)
        if (is_array($value)) {
            $value = implode(', ', array_filter($value));
        }
        
        // Convert to string
        $value = (string) $value;
        
        // Field-specific sanitization
        switch ($fieldType) {
            case 'email':
                $value = sanitize_email($value);
                break;
            case 'website':
            case 'url':
                $value = esc_url_raw($value);
                break;
            case 'phone':
                // Remove non-numeric characters except + and common phone formatting
                $value = preg_replace('/[^0-9+\s\-\(\)\.]/', '', $value);
                break;
            case 'number':
                $value = is_numeric($value) ? $value : '';
                break;
            default:
                $value = sanitize_text_field($value);
        }
        
        return trim($value);
    }

    /**
     * Build common payload structure for CTM API
     * 
     * Creates the base payload structure that all form submissions should have,
     * including visitor tracking, domain info, and metadata.
     * 
     * @since 2.0.0
     * @param array $formData The processed form data
     * @param string $formId The form ID
     * @param string $formTitle The form title
     * @return array The base payload structure
     */
    protected function buildBasePayload(array $formData, string $formId, string $formTitle): array
    {
        return [
            'phone_number'      => $formData['phone_number'] ?? '',
            'country_code'      => $formData['country_code'] ?? '',
            'type'              => 'API',
            'caller_name'       => $formData['caller_name'] ?? '',
            'email'             => $formData['email'] ?? '',
            'callback_number'   => $formData['callback_number'] ?? '',
            'delay_calling_by'  => $formData['delay_calling_by'] ?? '',
            'id'                => $formId,
            'name'              => $formTitle,
            '__ctm_api_authorized__' => '1',
            'visitor_sid'       => $_COOKIE['__ctmid'] ?? '',
            'domain'            => $_SERVER['HTTP_HOST'] ?? '',
            'ip_address'        => $this->getClientIpAddress(),
            'user_agent'        => $_SERVER['HTTP_USER_AGENT'] ?? '',
        ];
    }

    /**
     * Add UTM parameters to payload
     * 
     * Adds UTM parameters to the payload in a consistent format.
     * 
     * @since 2.0.0
     * @param array $payload The payload to modify
     * @param array $utmParams The UTM parameters to add
     * @return array The payload with UTM parameters
     */
    protected function addUtmToPayload(array $payload, array $utmParams): array
    {
        if (!empty($utmParams)) {
            $payload['utm_parameters'] = $utmParams;
        }
        
        return $payload;
    }

    /**
     * Add referrer information to payload
     * 
     * Adds HTTP referrer information if available.
     * 
     * @since 2.0.0
     * @param array $payload The payload to modify
     * @return array The payload with referrer information
     */
    protected function addReferrerToPayload(array $payload): array
    {
        if (!empty($_SERVER['HTTP_REFERER'])) {
            $payload['referrer'] = $_SERVER['HTTP_REFERER'];
        }
        
        return $payload;
    }

    /**
     * Add plugin metadata to payload
     *
     * Includes plugin version and account id for troubleshooting/analytics.
     *
     * @since 2.1.7
     * @param array $payload The payload to modify
     * @return array The payload with plugin metadata
     */
    protected function addPluginMetadataToPayload(array $payload): array
    {
        $pluginVersion = defined('CTM_VERSION') ? CTM_VERSION : '';
        if (!empty($pluginVersion)) {
            $payload['ctm_plugin_version'] = $pluginVersion;
        }

        $accountId = get_option('ctm_api_auth_account', '');
        if (!empty($accountId)) {
            $payload['ctm_account_id'] = (string) $accountId;
        }

        return $payload;
    }

    /**
     * Normalize field type for consistent processing
     * 
     * Maps various field type names to standardized types for consistent
     * processing across different form plugins.
     * 
     * @since 2.0.0
     * @param string $fieldType The original field type
     * @return string The normalized field type
     */
    protected function normalizeFieldType(string $fieldType): string
    {
        $typeMap = [
            'tel' => 'phone',
            'telephone' => 'phone',
            'mobile' => 'phone',
            'website' => 'url',
            'homepage' => 'url',
            'textarea' => 'text',
            'textbox' => 'text',
            'select' => 'dropdown',
            'radio' => 'radio',
            'checkbox' => 'checkbox',
            'file' => 'file',
            'upload' => 'file',
        ];
        
        return $typeMap[strtolower($fieldType)] ?? strtolower($fieldType);
    }

    /**
     * Check if a field should be excluded from processing
     * 
     * Determines if a field is an administrative field that should not
     * be included in the API payload.
     * 
     * @since 2.0.0
     * @param string $fieldType The field type
     * @param string $fieldLabel The field label
     * @return bool True if field should be excluded
     */
    protected function isAdminField(string $fieldType, string $fieldLabel = ''): bool
    {
        $adminTypes = ['hidden', 'honeypot', 'captcha', 'recaptcha', 'submit', 'button'];
        $adminLabels = ['submit', 'send', 'captcha', 'recaptcha', 'honeypot'];
        
        if (in_array(strtolower($fieldType), $adminTypes)) {
            return true;
        }
        
        $labelLower = strtolower($fieldLabel);
        foreach ($adminLabels as $adminLabel) {
            if (strpos($labelLower, $adminLabel) !== false) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Abstract method for processing form submissions
     * 
     * Each form service must implement this method to handle
     * form-specific submission processing.
     * 
     * @since 2.0.0
     * @param array $entry The form entry data
     * @param array $form The form configuration
     * @return array|null Processed payload or null on failure
     */
    abstract public function processSubmission(array $entry, array $form): ?array;

    /**
     * Abstract method for getting available forms
     * 
     * Each form service must implement this method to return
     * available forms for that form plugin.
     * 
     * @since 2.0.0
     * @return array Array of available forms
     */
    abstract public function getForms(): array;

    /**
     * Abstract method for checking if form has phone field
     * 
     * Each form service must implement this method to check
     * if a form has a phone number field.
     * 
     * @since 2.0.0
     * @param array $form The form configuration
     * @return bool True if form has phone field
     */
    abstract public function hasPhoneField(array $form): bool;
}
