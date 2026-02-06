<?php

declare(strict_types=1);

namespace CTM\Service;

/**
 * Duplicate Form Submission Prevention Service
 *
 * Prevents duplicate form submissions by using WordPress transients
 * with keys based on CTM session ID and form ID.
 *
 * @since 2.0.0
 */
class DuplicatePreventionService
{
    /**
     * Default expiration time for duplicate prevention (in seconds)
     *
     * @since 2.0.0
     * @var int
     */
    private const DEFAULT_EXPIRATION = 604800; // 7 days (604,800 seconds)

    /**
     * Check if a form submission is a duplicate
     *
     * @since 2.0.0
     * @param string $formId The form ID (CF7 or GF)
     * @param string $formType The form type ('cf7' or 'gf')
     * @param int $expirationSeconds How long to prevent duplicates (default: 60 seconds)
     * @return bool True if this is a duplicate submission
     */
    public function isDuplicateSubmission(string $formId, string $formType, int $expirationSeconds = self::DEFAULT_EXPIRATION): bool
    {
        try {
            $settings = $this->getSettings();

            // Only attempt to get CTM session if enabled
            $ctmSessionId = null;
            if ($settings['use_ctm_session']) {
                $ctmSessionId = $this->getCTMSessionId();
            }

            if (empty($ctmSessionId)) {
                // If we can't get a CTM session ID (or it's disabled), check if we should fall back to IP
                if (!$settings['fallback_to_ip']) {
                    return false; // Fallback disabled, allow submission
                }

                return $this->isDuplicateByIP($formId, $formType, $expirationSeconds);
            }

            // if (defined('WP_DEBUG') && WP_DEBUG) {
            //     error_log("CTM Debug: Using CTM session ID: " . substr($ctmSessionId, 0, 10) . "...");
            // }

            $transientKey = $this->generateTransientKey($ctmSessionId, $formId, $formType);
            // error_log("CTM Debug: Generated transient key: $transientKey");

            $existingSubmission = get_transient($transientKey);
            // if (defined('WP_DEBUG') && WP_DEBUG) {
            //     error_log("CTM Debug: get_transient result for key '$transientKey': " . ($existingSubmission !== false ? 'found' : 'not found'));
            // }

            if ($existingSubmission !== false) {
                // if (defined('WP_DEBUG') && WP_DEBUG) {
                //     error_log("CTM Debug: Duplicate submission detected for key: " . substr($transientKey, 0, 20) . "...");
                // }
                return true; // This is a duplicate
            }

            // Set the transient to prevent future duplicates
            $setResult = set_transient($transientKey, time(), $expirationSeconds);
            // if (defined('WP_DEBUG') && WP_DEBUG) {
            //     error_log("CTM Debug: Set transient result: " . ($setResult ? 'success' : 'failed'));
            //     error_log("CTM Debug: Transient expiration set to: {$expirationSeconds} seconds");
            // }

            // Debug logging disabled to prevent white screen issues
            // if ($setResult && defined('WP_DEBUG') && WP_DEBUG) {
            //     $verifyTransient = get_transient($transientKey);
            //     error_log("CTM Debug: Immediate verification - get_transient for key '$transientKey': " . var_export($verifyTransient, true));
            //
            //     $optionKey = '_transient_' . $transientKey;
            //     $optionValue = get_option($optionKey);
            //     error_log("CTM Debug: Direct option check for '$optionKey': " . var_export($optionValue, true));
            //
            //     $timeoutKey = '_transient_timeout_' . $transientKey;
            //     $timeoutValue = get_option($timeoutKey);
            //     error_log("CTM Debug: Transient timeout for '$timeoutKey': " . var_export($timeoutValue, true));
            // }

            return false; // Not a duplicate

        } catch (\Exception $e) {
            // Log the error and fall back to allowing the submission
            // error_log('CTM Duplicate Prevention Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            return false; // Allow submission if duplicate prevention fails
        }
    }

    /**
     * Get the CTM session ID from the current page
     *
     * @since 2.0.0
     * @return string|null The CTM session ID or null if not available
     */
    public function getCTMSessionId(): ?string
    {
        // Try to get from cookies first (most reliable)
        if (isset($_COOKIE['ctm_session_id'])) {
            return sanitize_text_field($_COOKIE['ctm_session_id']);
        }

        // Try to get from JavaScript variable if available
        if (isset($_POST['ctm_session_id'])) {
            return sanitize_text_field($_POST['ctm_session_id']);
        }

        // Try to get from request headers
        if (isset($_SERVER['HTTP_X_CTM_SESSION_ID'])) {
            return sanitize_text_field($_SERVER['HTTP_X_CTM_SESSION_ID']);
        }

        return null;
    }

    /**
     * Fallback duplicate prevention using IP address
     *
     * @since 2.0.0
     * @param string $formId The form ID
     * @param string $formType The form type
     * @param int $expirationSeconds Expiration time
     * @return bool True if duplicate by IP
     */
    private function isDuplicateByIP(string $formId, string $formType, int $expirationSeconds): bool
    {
        try {
            // Debug logging disabled to prevent white screen
            // if (defined('WP_DEBUG') && WP_DEBUG) {
            //     error_log("CTM Debug: Using IP-based duplicate prevention for form {$formId} ({$formType})");
            // }

            $ipAddress = $this->getClientIP();
            // Debug logging disabled to prevent white screen
            // if (defined('WP_DEBUG') && WP_DEBUG) {
            //     error_log("CTM Debug: Client IP: {$ipAddress}");
            // }

            $transientKey = $this->generateIPTransientKey($ipAddress, $formId, $formType);
            // Debug logging disabled to prevent white screen
            // if (defined('WP_DEBUG') && WP_DEBUG) {
            //     error_log("CTM Debug: isDuplicateByIP - Generated IP transient key: $transientKey");
            // }

            $existingSubmission = get_transient($transientKey);
            // Debug logging disabled to prevent white screen
            // if (defined('WP_DEBUG') && WP_DEBUG) {
            //     error_log("CTM Debug: isDuplicateByIP - get_transient result for IP key '$transientKey': " . ($existingSubmission !== false ? 'found' : 'not found'));
            // }

            if ($existingSubmission !== false) {
                // Debug logging disabled to prevent white screen
                // if (defined('WP_DEBUG') && WP_DEBUG) {
                //     error_log("CTM Debug: IP-based duplicate detected for key: " . substr($transientKey, 0, 20) . "...");
                // }
                return true; // This is a duplicate
            }

            // Set the transient to prevent future duplicates
            $setResult = set_transient($transientKey, time(), $expirationSeconds);
            // Debug logging disabled to prevent white screen
            // if (defined('WP_DEBUG') && WP_DEBUG) {
            //     error_log("CTM Debug: IP transient set result: " . ($setResult ? 'success' : 'failed'));
            // }

            return false; // Not a duplicate

        } catch (\Exception $e) {
            // Log the error and fall back to allowing the submission
            // error_log('CTM IP Duplicate Prevention Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            return false; // Allow submission if IP-based prevention fails
        }
    }

    /**
     * Generate a transient key for CTM session-based duplicate prevention
     *
     * @since 2.0.0
     * @param string $sessionId The CTM session ID
     * @param string $formId The form ID
     * @param string $formType The form type
     * @return string The transient key
     */
    private function generateTransientKey(string $sessionId, string $formId, string $formType): string
    {
        return "ctm_form_submission_{$formId}_{$formType}_{$sessionId}";
    }

    /**
     * Generate a transient key for IP-based duplicate prevention
     *
     * @since 2.0.0
     * @param string $ipAddress The client IP address
     * @param string $formId The form ID
     * @param string $formType The form type
     * @return string The transient key
     */
    private function generateIPTransientKey(string $ipAddress, string $formId, string $formType): string
    {
        return "ctm_form_submission_ip_{$formId}_{$formType}_{$ipAddress}";
    }

    /**
     * Get the client IP address
     *
     * @since 2.0.0
     * @return string The client IP address
     */
    private function getClientIP(): string
    {
        $ipKeys = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_CLIENT_IP',        // Client IP
            'HTTP_X_FORWARDED_FOR',  // X-Forwarded-For
            'HTTP_X_FORWARDED',      // X-Forwarded
            'HTTP_FORWARDED_FOR',    // Forwarded-For
            'HTTP_FORWARDED',        // Forwarded
            'REMOTE_ADDR'            // Remote address
        ];

        foreach ($ipKeys as $key) {
            if (isset($_SERVER[$key]) && !empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];

                // Handle X-Forwarded-For which can contain multiple IPs
                if ($key === 'HTTP_X_FORWARDED_FOR') {
                    $ips = explode(',', $ip);
                    $ip = trim($ips[0]);
                }

                // Validate IP address
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        // Safe fallback - check if REMOTE_ADDR exists before using it
        if (isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        }

        return '127.0.0.1';
    }

    /**
     * Clear duplicate prevention for a specific form submission
     *
     * @since 2.0.0
     * @param string $formId The form ID
     * @param string $formType The form type
     * @return void
     */
    public function clearDuplicatePrevention(string $formId, string $formType): void
    {
        $ctmSessionId = $this->getCTMSessionId();

        if (!empty($ctmSessionId)) {
            $transientKey = $this->generateTransientKey($ctmSessionId, $formId, $formType);
            delete_transient($transientKey);
        }

        // Also clear IP-based prevention
        $ipAddress = $this->getClientIP();
        $ipTransientKey = $this->generateIPTransientKey($ipAddress, $formId, $formType);
        delete_transient($ipTransientKey);
    }

    /**
     * Get the current duplicate prevention settings
     *
     * @since 2.0.0
     * @return array The current settings
     */
    public function getSettings(): array
    {
        return [
            'enabled' => (bool) get_option('ctm_duplicate_prevention_enabled', true),
            'expiration_seconds' => (int) get_option('ctm_duplicate_prevention_expiration', self::DEFAULT_EXPIRATION),
            'use_ctm_session' => (bool) get_option('ctm_duplicate_prevention_use_session', true),
            'fallback_to_ip' => (bool) get_option('ctm_duplicate_prevention_fallback_ip', true)
        ];
    }

    /**
     * Check if duplicate prevention is enabled
     *
     * @since 2.0.0
     * @return bool True if enabled
     */
    public function isEnabled(): bool
    {
        $settings = $this->getSettings();
        return $settings['enabled'];
    }

    private function isDuplicatePreventionEnabled(): bool
    {
        $settings = $this->getSettings();
        return $settings['enabled'];
    }

    private function shouldUseIPFallback(): bool
    {
        $settings = $this->getSettings();
        return $settings['fallback_to_ip'];
    }
}
