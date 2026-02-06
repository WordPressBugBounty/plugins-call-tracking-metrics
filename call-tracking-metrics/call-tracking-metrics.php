<?php
/**
 * CallTrackingMetrics WordPress Plugin
 *
 * This plugin integrates WordPress with CallTrackingMetrics (CTM) service to provide
 * comprehensive call tracking, form submission tracking, and analytics capabilities.
 *
 * Features:
 * - API integration with CTM service
 * - Contact Form 7 (CF7) integration
 * - Gravity Forms (GF) integration
 * - Debug logging and monitoring
 * - Dashboard widgets and analytics
 * - AJAX-powered admin interface
 * - Field mapping between forms and CTM
 *
 * @package     CallTrackingMetrics
 * @author      CallTrackingMetrics Team
 * @copyright   2024 CallTrackingMetrics
 * @license     GPL-2.0+
 * @version     2.1.7
 * @link        https://calltrackingmetrics.com
 *
 * @wordpress-plugin
 * Plugin Name: CallTrackingMetrics
 * Plugin URI: https://calltrackingmetrics.com
 * Description: A call tracking solution for WordPress - tracks errors, analytics, security, performance and more
 * Version: 2.1.7
 * Requires PHP: 8.2
 * Author: CallTrackingMetrics Team
 */

if (defined('CTM_BOOTSTRAPPED')) {
    return;
}
define('CTM_BOOTSTRAPPED', true);

// Prevent direct access to this file, except during tests or CLI
if (!defined('ABSPATH') && !defined('CTM_TESTING') && php_sapi_name() !== 'cli') {
    exit('Direct access forbidden.');
}

// Simple error handling to prevent white screens
set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return;
    }
    return true; // Let WordPress handle the error
});

// Simple exception handler
set_exception_handler(function($exception) {
    if (is_admin()) {
        echo '<div class="wrap"><div class="notice notice-error"><p>Plugin Error: Call Tracking Metrics encountered an error. Please check the error logs or contact support.</p></div></div>';
    }
});

// Check PHP version before loading Composer to prevent platform_check fatal error
if (version_compare(PHP_VERSION, '8.2', '<')) {
    add_action('admin_notices', function() {
        echo '<div class="notice notice-error"><p>' . esc_html__('CallTrackingMetrics requires PHP 8.2 or higher. Please upgrade your PHP version.', 'call-tracking-metrics') . '</p></div>';
    });
    return;
}

// Load Composer autoloader for dependency management
require_once __DIR__ . '/vendor/autoload.php';

// Define plugin file constant for use throughout the plugin
if (!defined('CTM_PLUGIN_FILE')) {
    define('CTM_PLUGIN_FILE', __FILE__);
}

// Define plugin version constant
if (!defined('CTM_VERSION')) {
    define('CTM_VERSION', '2.1.7');
}

// Import required classes
use CTM\Service\ApiService;
use CTM\Service\CF7Service;
use CTM\Service\GFService;
use CTM\Admin\Options;
use CTM\Admin\LoggingSystem;

/**
 * Get the CTM API base URL
 *
 * Returns the configured API URL from settings, or the default URL if not set.
 * This function provides a centralized way to access the API URL throughout the plugin.
 *
 * @since 2.0.0
 * @return string The API base URL
 */
if (!function_exists('ctm_get_api_url')) {
function ctm_get_api_url(): string
{
    // Allow overriding via constant (e.g., for development environments)
    if (defined('CTM_API_BASE_URL') && CTM_API_BASE_URL) {
        $api_url = CTM_API_BASE_URL;
    } else {
        $api_url = get_option('ctm_api_base_url', 'https://api.calltrackingmetrics.com');
    }

    // Ensure URL is properly formatted
    $api_url = trim($api_url);

    // If empty, return default
    if (empty($api_url)) {
        return 'https://api.calltrackingmetrics.com';
    }

    // Ensure URL has protocol
    if (!preg_match('/^https?:\/\//', $api_url)) {
        $api_url = 'https://' . $api_url;
    }

    // Remove trailing slash
    return rtrim($api_url, '/');
}
}

if (!function_exists('ctm_get_available_api_endpoints')) {
/**
 * Retrieve the list of available CTM API endpoints.
 *
 * @since 2.0.0
 * @return array<string,string> Associative array of endpoint => label.
 */
function ctm_get_available_api_endpoints(): array
{
    $endpoints = [
        'https://api.calltrackingmetrics.com' => __('Global (.com)', 'call-tracking-metrics'),
        'https://api.calltrackingmetrics.de' => __('Europe (.de)', 'call-tracking-metrics'),
    ];

    /**
     * Filter the available CTM API endpoints.
     *
     * @since 2.0.0
     * @param array<string,string> $endpoints
     */
    return apply_filters('ctm_available_api_endpoints', $endpoints);
}
}

/**
 * Main CallTrackingMetrics Plugin Class
 *
 * This is the core plugin class that orchestrates all functionality including:
 * - Service integrations (API, CF7, GF)
 * - Admin interface management
 * - WordPress hook registration
 * - Form submission handling
 * - Tracking script injection
 *
 * @since 2.0.0
 */
class CallTrackingMetrics
{
    /**
     * API service instance for CTM communication
     *
     * @since 2.0.0
     * @var ApiService
     */
    private ApiService $apiService;

    /**
     * Contact Form 7 service handler
     *
     * @since 2.0.0
     * @var CF7Service
     */
    private CF7Service $cf7Service;

    /**
     * Gravity Forms service handler
     *
     * @since 2.0.0
     * @var GFService
     */
    private GFService $gfService;

    /**
     * Admin options and settings manager
     *
     * @since 2.0.0
     * @var Options
     */
    private Options $adminOptions;

    /**
     * Logging system for debugging and monitoring
     *
     * @since 2.0.0
     * @var LoggingSystem
     */
    private LoggingSystem $loggingSystem;

    /**
     * Initialize the plugin
     *
     * Sets up all services, registers WordPress hooks, and initializes
     * the admin interface and logging system.
     *
     * @since 2.0.0
     */
    public function __construct()
    {
        // Make plugin instance globally accessible
        global $ctm_plugin;
        $ctm_plugin = $this;

        // Set up global error handling to prevent white screens
        $this->setupGlobalErrorHandling();

        // Load plugin translations
        /** @noinspection PhpUndefinedFunctionInspection */
        add_action('init', function() {
            \load_plugin_textdomain('call-tracking-metrics', false, dirname(\plugin_basename(__FILE__)) . '/languages');
        });
        // Initialize core services
        $this->apiService = new ApiService(\ctm_get_api_url());
        $this->cf7Service = new CF7Service();
        $this->gfService = new GFService();
        $this->adminOptions = new Options();
        $this->loggingSystem = new \CTM\Admin\LoggingSystem();
        $this->loggingSystem->initializeLoggingSystem();

        // Initialize admin components (AJAX handlers, mapping assets, etc.)
        $this->adminOptions->initialize();

        // Initialize form logs AJAX handlers
        $this->initializeFormLogsAjax();

        // Initialize form usage AJAX handlers
        $this->initializeFormUsageAjax();

        // Initialize log loading AJAX handlers
        $this->initializeLogLoadingAjax();

        // Register core WordPress hooks
        $this->registerCoreHooks();

        // Register plugin functionality hooks
        $this->registerPluginHooks();

        // Register conditional hooks (dashboard widgets)
        $this->registerConditionalHooks();

        // Check forms for phone numbers and show warnings
        add_action('admin_notices', [$this, 'showPhoneNumberWarnings']);

        // Register activation/deactivation hooks
        $this->registerLifecycleHooks();
    }

    /**
     * Register core WordPress admin hooks
     *
     * @since 2.0.0
     * @return void
     */
    private function registerCoreHooks(): void
    {
        add_action('admin_init', [$this->adminOptions, 'registerSettings']);
        add_action('admin_menu', [$this->adminOptions, 'registerSettingsPage']);

    }

    /**
     * Register plugin functionality hooks
     *
     * @since 2.0.0
     * @return void
     */
    private function registerPluginHooks(): void
    {
        // Frontend tracking script injection
        add_action('wp_head', [$this, 'printTrackingScript']);

        // Form integration initialization
        add_action('init', [$this, 'formInit']);

        // Admin dashboard integration
        add_action('admin_menu', [$this, 'attachDashboard']);

        // Form confirmation handlers
        add_action('wp_footer', [$this, 'cf7Confirmation'], 10, 1);
        add_action('wp_footer', [$this, 'gfSessionTracking'], 10, 1);
        // add_filter('gform_confirmation', [$this, 'gfConfirmation'], 10, 4);

        // Enqueue Tailwind CSS for admin pages (settings, debug, etc.)
        add_action('admin_enqueue_scripts', function($hook) {
            // Only enqueue on the CallTrackingMetrics admin pages
            if (strpos($hook, 'call-tracking-metrics') === false) return;

            $css_file = plugin_dir_path(__FILE__) . 'css/optmized.css';
            $version = file_exists($css_file) ? filemtime($css_file) : CTM_VERSION;
            wp_enqueue_style(
                'ctm-tailwind',
                plugins_url('css/optmized.css', __FILE__),
                [],
                $version
            );
        });
        // Merge: Enqueue debug JS and localize export nonce for debug tab
        add_action('admin_enqueue_scripts', function($hook) {
            // Only enqueue on the CallTrackingMetrics debug/settings page
            if (strpos($hook, 'call-tracking-metrics') === false) return;

            wp_enqueue_script(
                'ctm-toast',
                plugins_url('assets/js/toast.js', __FILE__),
                [],
                null,
                true
            );

            // Enqueue debug JS if not already enqueued
            wp_enqueue_script(
                'ctm-debug-js',
                plugins_url('assets/js/debug.js', __FILE__),
                ['jquery'],
                CTM_VERSION,
                true
            );

            $assetsDir = plugin_dir_path(__FILE__) . 'assets/js/';
            $enqueueScript = function(string $handle, string $file, array $deps = []) use ($assetsDir) {
                $fullPath = $assetsDir . $file;
                if (!file_exists($fullPath)) {
                    return false;
                }
                wp_enqueue_script(
                    $handle,
                    plugins_url('assets/js/' . $file, __FILE__),
                    $deps,
                    CTM_VERSION,
                    true
                );
                return true;
            };

            $enqueueScript('ctm-api-tab-js', 'api-tab.js', ['jquery']);
            // Enqueue Documentation tab JS
            wp_enqueue_script(
                'ctm-documentation-tab-js',
                plugins_url('assets/js/documentation-tab.js', __FILE__),
                [],
                CTM_VERSION,
                true
            );

            // Enqueue Notice dismiss JS
            wp_enqueue_script(
                'ctm-notice-dismiss-js',
                plugins_url('assets/js/notice-dismiss.js', __FILE__),
                [],
                CTM_VERSION,
                true
            );

            // Localize export diagnostic report nonce
            wp_localize_script('ctm-debug-js', 'ctmDebugVars', [
                'ajaxurl' => admin_url('admin-ajax.php'),
                'ctm_export_diagnostic_report_nonce' => wp_create_nonce('ctm_export_diagnostic_report'),
            ]);

            // Localize notice dismiss data
            wp_localize_script('ctm-notice-dismiss-js', 'ctmNoticeData', [
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('ctm_dismiss_notice'),
            ]);

            $generalScriptLoaded = $enqueueScript('ctm-general-tab-js', 'general-tab.js');
            $formLogsScriptLoaded = $enqueueScript('ctm-form-logs-js', 'form-logs.js', ['jquery']);

            // Enqueue unified preview JS
            wp_enqueue_script(
                'ctm-preview-js',
                plugins_url('assets/js/ctm-preview.js', __FILE__),
                ['jquery'],
                CTM_VERSION,
                true
            );

            // Enqueue admin tab control JS
            wp_enqueue_script(
                'ctm-admin-tab-control-js',
                plugins_url('assets/js/admin-tab-control.js', __FILE__),
                ['jquery'],
                CTM_VERSION,
                true
            );

                        // Localize admin tab control data
            $apiConnected = $this->adminOptions->isApiConnected();
            // Debug: Log the API connection status
            if ($this->loggingSystem && $this->loggingSystem->isDebugEnabled()) {
                $this->loggingSystem->logActivity('JavaScript localization - apiConnected: ' . ($apiConnected ? 'true' : 'false'), 'debug');
            }
            wp_localize_script('ctm-admin-tab-control-js', 'ctmAdminVars', [
                'apiConnected' => $apiConnected,
                'ajaxurl' => admin_url('admin-ajax.php'),
            ]);

            // Localize general tab data
            if ($generalScriptLoaded) {
                wp_localize_script('ctm-general-tab-js', 'ctmGeneralData', [
                    'ajaxurl' => admin_url('admin-ajax.php'),
                    'dismissNonce' => wp_create_nonce('ctm_dismiss_notice'),
                    'generalNonce' => wp_create_nonce('ctm_general_nonce'),
                    'testNonce' => wp_create_nonce('ctm_test_api_connection'),
                ]);
            }

            // Localize form logs data
            if ($formLogsScriptLoaded) {
                wp_localize_script('ctm-form-logs-js', 'ctmFormLogsData', [
                    'ajaxurl' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('ctm_form_logs'),
                    'debug_enabled' => get_option('ctm_debug_enabled', false),
                ]);
            }

            // Add modal styles and wpfooter positioning fix
            wp_add_inline_style('ctm-tailwind', '
                body.ctm-modal-open #adminmenumain,
                body.ctm-modal-open #adminmenuwrap,
                body.ctm-modal-open #adminmenu {
                    display: none !important;
                }
                body.ctm-modal-open #wpcontent {
                    margin-left: 0 !important;
                }
                body.ctm-modal-open #wpfooter {
                    margin-left: 0 !important;
                }
                body.ctm-modal-open #ctm-form-logs-modal {
                    z-index: 999999 !important;
                }

                /* Fix wpfooter positioning to prevent overlay issues */
                #wpfooter {
                    position: relative !important;
                    bottom: auto !important;
                    left: auto !important;
                    right: auto !important;
                }

                /* Ensure proper spacing below content */
                .wrap {
                    margin-bottom: 60px !important;
                }
            ');
        });

    }

    /**
     * Register conditional hooks based on settings
     *
     * @since 2.0.0
     * @return void
     */
    private function registerConditionalHooks(): void
    {
        // Always register the dashboard widget hook, but check the setting inside
        add_action('wp_dashboard_setup', [$this, 'maybeAddDashboardWidget']);
    }

    /**
     * Conditionally add dashboard widget based on settings
     *
     * @since 2.0.0
     * @return void
     */
    public function maybeAddDashboardWidget(): void
    {
        // Only add dashboard widget if enabled in settings
        if (get_option('ctm_dashboard_enabled')) {
            $this->adminOptions->addDashboardWidget();
        }
    }

    /**
     * Set up global error handling to prevent white screens
     *
     * @since 2.0.0
     * @return void
     */
    private function setupGlobalErrorHandling(): void
    {
        // Set up error handler for fatal errors
        set_error_handler(function($severity, $message, $file, $line) {
            // Only handle errors if they're not being suppressed
            if (!(error_reporting() & $severity)) {
                return false;
            }

            // Log the error but don't throw an exception
            if (class_exists('\CTM\Admin\LoggingSystem')) {
                $loggingSystem = new \CTM\Admin\LoggingSystem();
                if ($loggingSystem->isDebugEnabled()) {
                    $loggingSystem->logActivity("PHP Error: {$message} in {$file} on line {$line}", 'error');
                }
            }

            // Return false to let PHP handle the error normally
            return false;
        });

        // Set up exception handler for uncaught exceptions
        set_exception_handler(function($exception) {
            // Log the exception but don't cause a white screen
            if (class_exists('\CTM\Admin\LoggingSystem')) {
                $loggingSystem = new \CTM\Admin\LoggingSystem();
                if ($loggingSystem->isDebugEnabled()) {
                    $loggingSystem->logActivity("Uncaught Exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine(), 'error');
                }
            }

            // Don't output anything to prevent white screens
            return true;
        });
    }

    /**
     * Register plugin activation and deactivation hooks
     *
     * @since 2.0.0
     * @return void
     */
    private function registerLifecycleHooks(): void
    {
        register_activation_hook(__FILE__, [LoggingSystem::class, 'onPluginActivation']);
        register_deactivation_hook(__FILE__, [LoggingSystem::class, 'onPluginDeactivation']);
    }

    /**
     * Print the CTM tracking script in the site head
     *
     * Injects the CallTrackingMetrics JavaScript tracking code into the
     * website's <head> section for visitor and call tracking.
     *
     * @since 2.0.0
     * @return void
     */
    public function printTrackingScript(): void
    {
        echo "<!-- CTM DEBUG: printTrackingScript ENTER -->\n";
        // Only inject tracking script on frontend pages and if enabled
        if (!is_admin() && get_option('ctm_auto_inject_tracking_script', 1)) {
            $script = $this->getTrackingScript();
            if ($script) {
                echo "<!-- CTM Tracking Script Injection (v" . CTM_VERSION . ") -->\n";
                echo $script;
                echo "\n<!-- End CTM Tracking Script (v" . CTM_VERSION . ") -->\n";
            }
        }
    }

    /**
     * Initialize form integrations based on enabled plugins
     *
     * Conditionally sets up Contact Form 7 and Gravity Forms integrations
     * based on plugin availability and user settings.
     *
     * @since 2.0.0
     * @return void
     */
    public function formInit(): void
    {
        // Initialize Contact Form 7 integration if enabled and active
        if ($this->cf7Enabled() && $this->cf7Active()) {
            add_action('wpcf7_before_send_mail', [$this, 'submitCF7'], 10, 3);
        }

        // Initialize Gravity Forms integration if enabled and active
        if ($this->gfEnabled() && $this->gfActive()) {
            // Use validation hook to prevent duplicate submissions before processing
            add_filter('gform_validation', [$this, 'validateGFDuplicate'], 10, 1);
            // Keep the after submission hook for processing successful submissions
            add_action('gform_after_submission', [$this, 'submitGF'], 10, 2);
            // Customize validation message for duplicate submissions
            add_filter('gform_validation_message', [$this, 'customizeGFValidationMessage'], 10, 2);
        }
    }

    /**
     * Output Contact Form 7 confirmation tracking JavaScript
     *
     * Injects JavaScript code that tracks CF7 form submissions
     * for analytics and conversion tracking.
     *
     * @since 2.0.0
     * @return void
     */
    public function cf7Confirmation(): void
    {
        // JavaScript event listener for CF7 mail sent event
        echo "<script type='text/javascript'>\n";
        echo "document.addEventListener('wpcf7mailsent', function(event) {\n";
        echo "  try { 
";
        echo "    __ctm.tracker.trackEvent('', ' ', 'form'); 
";
        echo "    __ctm.tracker.popQueue(); 
";
        echo "  } catch(e) { 
";
        echo "    console.log('CTM tracking error:', e); 
";
        echo "  }
";
        echo "}, false);
";
        echo "</script>";

        // Add CTM session tracking and duplicate prevention
        echo "<script type='text/javascript'>\n";
        echo "// CTM Session Tracking and Duplicate Prevention\n";
        echo "document.addEventListener('DOMContentLoaded', function() {\n";
        echo "  // Function to get CTM session ID\n";
        echo "  function getCTMSessionId() {\n";
        echo "    try {\n";
        echo "      if (typeof __ctm !== 'undefined' && __ctm.tracker && __ctm.tracker.getSessionId) {\n";
        echo "        return __ctm.tracker.getSessionId();\n";
        echo "      }
";
        echo "      // Fallback: try to get from localStorage or generate a unique ID\n";
        echo "      let sessionId = localStorage.getItem('ctm_session_id');\n";
        echo "      if (!sessionId) {\n";
        echo "        sessionId = 'ctm_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);\n";
        echo "        localStorage.setItem('ctm_session_id', sessionId);\n";
        echo "      }
";
        echo "      return sessionId;\n";
        echo "    } catch(e) {\n";
        echo "      console.log('CTM session tracking error:', e);\n";
        echo "      return null;\n";
        echo "    }
";
        echo "  }
";
        echo "  
";
        echo "  // Add session ID to all CF7 forms\n";
        echo "  const cf7Forms = document.querySelectorAll('.wpcf7-form');\n";
        echo "  cf7Forms.forEach(function(form) {\n";
        echo "    const sessionId = getCTMSessionId();\n";
        echo "    if (sessionId) {\n";
        echo "      // Add hidden input for session ID\n";
        echo "      let sessionInput = form.querySelector('input[name=\"ctm_session_id\"]');\n";
        echo "      if (!sessionInput) {\n";
        echo "        sessionInput = document.createElement('input');\n";
        echo "        sessionInput.type = 'hidden';\n";
        echo "        sessionInput.name = 'ctm_session_id';\n";
        echo "        form.appendChild(sessionInput);\n";
        echo "      }
";
        echo "      sessionInput.value = sessionId;\n";
        echo "      
";
        echo "      // Set cookie for server-side access\n";
        echo "      document.cookie = 'ctm_session_id=' + sessionId + '; path=/; max-age=3600';\n";
        echo "    }
";
        echo "  });
";
        echo "});
";
        echo "</script>";
    }

    /**
     * Output Gravity Forms session tracking JavaScript
     *
     * Injects JavaScript code that tracks GF form submissions
     * for duplicate prevention using CTM session IDs.
     *
     * @since 2.0.0
     * @return void
     */
    public function gfSessionTracking(): void
    {
        // Only add if Gravity Forms is active
        if (!$this->gfActive()) {
            return;
        }

        // Add CTM session tracking and duplicate prevention for Gravity Forms
        echo "<script type='text/javascript'>\n";
        echo "// CTM Session Tracking for Gravity Forms\n";
        echo "document.addEventListener('DOMContentLoaded', function() {\n";
        echo "  // Function to get CTM session ID\n";
        echo "  function getCTMSessionId() {\n";
        echo "    try {\n";
        echo "      if (typeof __ctm !== 'undefined' && __ctm.tracker && __ctm.tracker.getSessionId) {\n";
        echo "        return __ctm.tracker.getSessionId();\n";
        echo "      }
";
        echo "      // Fallback: try to get from localStorage or generate a unique ID\n";
        echo "      let sessionId = localStorage.getItem('ctm_session_id');\n";
        echo "      if (!sessionId) {\n";
        echo "        sessionId = 'ctm_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);\n";
        echo "        localStorage.setItem('ctm_session_id', sessionId);\n";
        echo "      }
";
        echo "      return sessionId;\n";
        echo "    } catch(e) {\n";
        echo "      console.log('CTM session tracking error:', e);\n";
        echo "      return null;\n";
        echo "    }
";
        echo "  }
";
        echo "  
";
        echo "  // Add session ID to all Gravity Forms\n";
        echo "  const gfForms = document.querySelectorAll('.gform_wrapper form');\n";
        echo "  gfForms.forEach(function(form) {\n";
        echo "    const sessionId = getCTMSessionId();\n";
        echo "    if (sessionId) {\n";
        echo "      // Add hidden input for session ID\n";
        echo "      let sessionInput = form.querySelector('input[name=\"ctm_session_id\"]');\n";
        echo "      if (!sessionInput) {\n";
        echo "        sessionInput = document.createElement('input');\n";
        echo "        sessionInput.type = 'hidden';\n";
        echo "        sessionInput.name = 'ctm_session_id';\n";
        echo "        form.appendChild(sessionInput);\n";
        echo "      }
";
        echo "      sessionInput.value = sessionId;\n";
        echo "      
";
        echo "      // Set cookie for server-side access\n";
        echo "      document.cookie = 'ctm_session_id=' + sessionId + '; path=/; max-age=3600';\n";
        echo "    }
";
        echo "  });
";
        echo "});
";
        echo "</script>";
    }

    /**
     * Modify Gravity Forms confirmation message to include tracking script.
     * Ported from version 1.2.16.
     *
     * @since 2.1.0
     * @param mixed $confirmation The confirmation message/array.
     * @param array $form         The form object.
     * @param array $entry        The entry object.
     * @param bool  $is_ajax      Whether the form is submitted via AJAX.
     * @return mixed
     */
    public function gfConfirmation($confirmation, $form, $entry, $is_ajax)
    {
        if (is_array($confirmation)) {
            if (isset($confirmation["redirect"])) {
                $code = "window.location = \"" . esc_url_raw($confirmation["redirect"]) . "\";";
                $confirmation = "";
            } else {
                return $confirmation;
            }
        } elseif (strpos($confirmation, "<script") !== false && strpos($confirmation, "function gf_ctm_redirect(") !== false) {
            $code = "gf_ctm_redirect_old();";
        } else {
            $code = "";
        }

        // Get account ID from tracking script
        $tracker = '';
        $script = $this->getTrackingScript();
        if (preg_match('/(\d+).tctm.co\/t.js/', $script, $matches) == 1) {
            $tracker = $matches[1];
        }

        // Safety check: if no tracker ID found, return original confirmation to prevent JS errors
        if (empty($tracker)) {
            return $confirmation;
        }

        // Generate the tracking script with a failsafe
        $tracking_js = sprintf(
            "<script>(function() {\n                var executed = false;\n                function doConfirmation() {\n                    if (executed) return;\n                    executed = true;\n                    %s\n                }\n
                // Failsafe: If CTM doesn't load within 2 seconds, proceed anyway\n                setTimeout(doConfirmation, 2000);\n
                if (window.location != window.parent.location) {\n                    var tracker = document.createElement('script');\n                    tracker.setAttribute('src', '//%s.tctm.co/t.js');\n                    document.head.appendChild(tracker);\n                }\n                __ctm_http_requests = [];\n                (function(open) {\n                    XMLHttpRequest.prototype.open = function() {\n                        __ctm_http_requests.push(this);\n                        this.addEventListener(\"readystatechange\", function() {\n                            if (this.readyState == 4) {\n                                var index = __ctm_http_requests.indexOf(this);\n                                if (index > -1) __ctm_http_requests.splice(index, 1);\n                            }\n                        }, false);\n                        open.apply(this, arguments);\n                    };\n                })(XMLHttpRequest.prototype.open);\n                window.__ctm_loaded = window.__ctm_loaded || [];\n                window.__ctm_loaded.push(function() {\n                    try {\n                        __ctm.tracker.trackEvent('', ' ', 'form');\n                        __ctm.tracker.popQueue();\n                    } catch(e) {}
                    if (typeof gf_ctm_redirect != 'undefined') {\n                        gf_ctm_redirect_old = gf_ctm_redirect;\n                        gf_ctm_redirect = function() {};\n                    }\n                    var send_time = (new Date()).getTime();\n                    var redirect = setInterval(function() {\n                        if (__ctm_http_requests.length == 0 || (new Date()).getTime() - send_time > 5000) {\n                            clearInterval(redirect);\n                            doConfirmation();\n                        }\n                    }, 10);\n                });\n            })();</script>",
            $code, // Passed to doConfirmation first
            esc_attr($tracker) // Passed to tracker src
        );

        return $confirmation . $tracking_js;
    }

    /**
     * Check if API is connected (helper function for views)
     *
     * @since 2.0.0
     * @return bool True if API is connected
     */
    public function ctm_is_api_connected(): bool
    {
        return $this->adminOptions->isApiConnected();
    }

    /**
     * Handle Contact Form 7 submission and send data to CTM API
     *
     * Processes CF7 form submissions, formats the data, and sends it
     * to the CallTrackingMetrics API for lead tracking.
     *
     * @since 2.0.0
     * @param object $form The CF7 form object
     * @param mixed  $abort Deprecated abort flag or other data
     * @param object $submission The CF7 submission object (CF7 5.7+)
     * @return void
     */
    public function submitCF7($form, $abort = null, $submission = null): void
    {
        // Suppress any output that might break JSON response
        ob_start();

        try {
            // Handle arguments for different CF7 versions
            if ($submission === null && $abort instanceof \WPCF7_Submission) {
                $submission = $abort;
                $abort = null;
            }

            // Ensure we have a submission object if possible
            if (!$submission && class_exists('\WPCF7_Submission')) {
                $submission = \WPCF7_Submission::get_instance();
            }

            // Check if form submission is already skipped/aborted via skip_mail (CF7 5.7+)
            if ($submission && isset($submission->skip_mail) && $submission->skip_mail) {
                ob_end_clean();
                return;
            }

            // Fallback check for older CF7 versions
            if (true === $abort) {
                ob_end_clean();
                return;
            }

            // Check for duplicate submission prevention
            $duplicatePrevention = new \CTM\Service\DuplicatePreventionService();
            if ($form && method_exists($form, 'id')) {
                $isDuplicate = $duplicatePrevention->isDuplicateSubmission($form->id(), 'cf7');

                if ($duplicatePrevention->isEnabled() && $isDuplicate) {
                    // Provide user feedback for duplicate submission
                    if (method_exists($form, 'set_invalid_fields')) {
                        $form->set_invalid_fields([['name' => '', 'reason' => 'You have already submitted this form. Please wait before submitting again.']]);
                    } elseif (method_exists($form, 'set_invalid_field')) {
                        $form->set_invalid_field('', 'You have already submitted this form. Please wait before submitting again.');
                    }

                    // Abort mail sending
                    if ($submission) {
                        $submission->skip_mail = true;
                    }
                    
                    ob_end_clean();
                    return;
                }
            }

            // Set error handler to prevent fatal errors
            $original_error_handler = set_error_handler(function($severity, $message, $file, $line) {
                if (!(error_reporting() & $severity)) {
                    return false;
                }
                throw new \ErrorException($message, 0, $severity, $file, $line);
            });

            try {
                // Get form submission data
                $dataObject = $submission ?: \WPCF7_Submission::get_instance();

                if (!$dataObject) {
                    // Cannot process without submission data
                    if ($original_error_handler) {
                        set_error_handler($original_error_handler);
                    }
                    ob_end_clean();
                    return;
                }

                $data = $dataObject->get_posted_data();

                // Process the submission through CF7 service
                $result = $this->cf7Service->processSubmission($form, $data);

                // Send processed data to CTM API if credentials are available
                $apiKey = get_option('ctm_api_key');
                $apiSecret = get_option('ctm_api_secret');

                if ($result && $apiKey && $apiSecret) {
                    try {
                        $response = $this->apiService->submitFormReactor($result, $apiKey, $apiSecret, 'wpcf7-' . $form->id());

                        // Log the submission for debugging and monitoring
                        $this->loggingSystem->logFormSubmission(
                            'cf7',
                            $form->id(),
                            $form->title(),
                            $result,
                            $response,
                            ['entry_id' => null]
                        );

                        // Check for API errors and surface to user
                        if (!$response || (isset($response['status']) && $response['status'] === 'error')) {
                            $reason = $response['reason'] ?? 'Unknown error';
                            $errorType = $response['error_type'] ?? '';
                            $userMessage = '';

                            // Handle specific error types
                            if ($errorType === 'phone_format') {
                                $userMessage = 'Please enter a valid phone number in international format (e.g., +1234567890).';
                            } elseif (stripos($reason, 'throttle') !== false || stripos($reason, 'rate limit') !== false) {
                                $userMessage = 'You are submitting too quickly. Please wait a moment and try again.';
                            } elseif (stripos($reason, 'phone') !== false) {
                                $userMessage = 'A valid phone number is required.';
                            } elseif (stripos($reason, 'email') !== false) {
                                $userMessage = 'A valid email address is required.';
                            } elseif (stripos($reason, 'required') !== false) {
                                $userMessage = 'Please fill out all required fields.';
                            } elseif ($errorType === 'api_error') {
                                // Don't abort for technical API errors
                                $this->logInternal("CF7 submission proceeded despite CTM API error: $reason", 'warning');
                            } else {
                                $userMessage = 'Submission failed: ' . esc_html($reason);
                            }

                            if (!empty($userMessage)) {
                                // Show error to user using CF7 API
                                if (method_exists($form, 'set_invalid_fields')) {
                                    $form->set_invalid_fields([['name' => '', 'reason' => $userMessage]]);
                                } elseif (method_exists($form, 'set_invalid_field')) {
                                    $form->set_invalid_field('', $userMessage);
                                }
                                
                                // Abort mail sending
                                if ($submission) {
                                    $submission->skip_mail = true;
                                }
                            }
                        }
                    } catch (\Throwable $e) {
                        $this->logInternal('CF7 API Error: ' . $e->getMessage(), 'error');
                        $this->loggingSystem->logFormSubmission(
                            'cf7',
                            $form->id(),
                            $form->title(),
                            $result,
                            ['error' => $e->getMessage()],
                            ['entry_id' => null]
                        );
                    }
                }
            } catch (\Throwable $e) {
                $this->logInternal('CF7 Submission Error: ' . $e->getMessage(), 'error');
            } finally {
                if (isset($original_error_handler) && $original_error_handler) {
                    set_error_handler($original_error_handler);
                }
            }
        } catch (\Throwable $e) {
            // Catch-all for any other errors to prevent JSON corruption
            if (isset($this->loggingSystem)) {
                $this->logInternal('Critical CF7 Error: ' . $e->getMessage(), 'error');
            }
        }

        // Clean any output buffer to ensure valid JSON response from CF7
        ob_end_clean();
    }

    /**
     * Validate Gravity Forms submission for duplicate prevention
     *
     * This runs BEFORE the form is processed, allowing us to prevent
     * duplicate submissions and show proper error messages.
     *
     * @since 2.0.0
     * @param array $validation_result The validation result array
     * @return array The modified validation result
     */
    public function validateGFDuplicate($validation_result): array
    {
        // Only check if validation hasn't already failed
        if ($validation_result['is_valid']) {
            $form = $validation_result['form'];
            $form_id = $form['id'];

            error_log("CTM DEBUG: validateGFDuplicate called for form ID: " . $form_id);

            // Check for duplicate submission prevention
            $duplicatePrevention = new \CTM\Service\DuplicatePreventionService();
            if ($duplicatePrevention->isEnabled() && $duplicatePrevention->isDuplicateSubmission($form_id, 'gf')) {
                error_log("CTM DEBUG: GF Duplicate Submission Prevented in validation: Form ID " . $form_id);

                // Mark validation as failed
                $validation_result['is_valid'] = false;

                // Set a clear validation message
                $validation_result['form']['validationSummary'] = 'Duplicate submission detected. You have already submitted this form. Please wait before submitting again.';

                // Add validation error to the first field to make it more visible
                if (!empty($form['fields'])) {
                    $firstField = $form['fields'][0];
                    $fieldId = $firstField->id;

                    // Add field-specific validation error
                    if (!isset($validation_result['form']['validationSummary'])) {
                        $validation_result['form']['validationSummary'] = '';
                    }
                    $validation_result['form']['validationSummary'] .= ' Duplicate submission detected.';
                }
            }
        }

        return $validation_result;
    }

    /**
     * Customize Gravity Forms validation message for duplicate submissions
     *
     * @since 2.0.0
     * @param string $message The validation message
     * @param array $form The form configuration
     * @return string The customized validation message
     */
    public function customizeGFValidationMessage($message, $form): string
    {
        // Check if this form has a duplicate submission validation error
        if (isset($form['validationSummary']) && strpos($form['validationSummary'], 'Duplicate submission detected') !== false) {
            return '<div class="gform_validation_errors" style="background: #fef7f7; border: 1px solid #dc3232; border-radius: 4px; padding: 15px; margin: 15px 0; text-align: center;">
                <p style="color: #dc3232; font-size: 16px; margin: 0; font-weight: bold;">⚠️ Duplicate Submission</p>
                <p style="color: #333; font-size: 14px; margin: 8px 0 0 0;">You have already submitted this form. Please wait before submitting again.</p>
            </div>';
        }

        return $message;
    }

    /**
     * Handle Gravity Forms submission and send data to CTM API
     *
     * Processes GF form submissions, formats the data, and sends it
     * to the CallTrackingMetrics API for lead tracking.
     *
     * @since 2.0.0
     * @param array $entry The GF entry data
     * @param array $form  The GF form configuration
     * @return void
     */
    public function submitGF($entry, $form): void
    {
        // Set error handler to prevent fatal errors
        $original_error_handler = set_error_handler(function($severity, $message, $file, $line) {
            if (!(error_reporting() & $severity)) {
                return false;
            }
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });

        try {
            // Process the submission through GF service
            $result = $this->gfService->processSubmission($entry, $form);

            // Skip if processing failed
            if ($result === null) {
                return;
            }

            // Send processed data to CTM API if credentials are available
            $apiKey = get_option('ctm_api_key');
            $apiSecret = get_option('ctm_api_secret');

            if ($result && $apiKey && $apiSecret) {
                try {
                    $response = $this->apiService->submitFormReactor($result, $apiKey, $apiSecret, $form['id']);

                    // Log the submission for debugging and monitoring
                    $this->loggingSystem->logFormSubmission(
                        'gf',
                        $form['id'],
                        $form['title'],
                        $result,
                        $response,
                        ['entry_id' => $entry['id']]
                    );

                    // Check for API errors and log them
                    if (!$response || (isset($response['status']) && $response['status'] !== 'success')) {
                        $reason = $response['reason'] ?? 'Unknown error';
                        $errorType = $response['error_type'] ?? '';

                        if ($errorType === 'phone_format') {
                            $this->logInternal('GF Submission Error: Phone number format error - ' . $reason, 'error');
                        } else {
                            $this->logInternal('GF Submission Error: ' . $reason, 'error');
                        }
                    }
                } catch (\Throwable $e) {
                    // Log the API error (catch both Exception and Error)
                    $this->logInternal('GF API Error: ' . $e->getMessage(), 'error');

                    // Don't break the form submission - just log the error
                    // This prevents white screens while still tracking the issue
                    $this->loggingSystem->logFormSubmission(
                        'gf',
                        $form['id'],
                        $form['title'],
                        $result,
                        ['error' => $e->getMessage()],
                        ['entry_id' => $entry['id']]
                    );
                }

                // Return a JSON response to the frontend indicating success or error
                if (defined('DOING_AJAX') && DOING_AJAX) {
                    if (isset($response) && isset($response['status']) && $response['status'] === 'success') {
                        wp_send_json_success([
                            'message' => 'Form submitted successfully',
                            'response' => $response,
                        ]);
                    } else {
                        $errorMessage = isset($e) ? $e->getMessage() : 'Unknown error';
                        $errorMsg = $response['reason'] ?? $errorMessage;
                        wp_send_json_error([
                            'message' => 'Failed to submit form',
                            'error' => $errorMsg,
                        ]);
                    }
                    // Always exit after sending JSON response in AJAX context
                    exit;
                }
            }
        } catch (\Throwable $e) {
            // Log any unexpected errors but don't break the form submission
            $this->logInternal('GF Submission Error: ' . $e->getMessage(), 'error');

            // Don't throw the exception to prevent white screens
            // Just log the error and let the form submission continue
        } finally {
            // Restore original error handler
            if ($original_error_handler) {
                set_error_handler($original_error_handler);
            }
        }
    }

    /**
     * Initialize form logs AJAX handlers
     *
     * @since 2.0.0
     * @return void
     */
    private function initializeFormLogsAjax(): void
    {
        // Register AJAX handlers for form logs
        add_action('wp_ajax_ctm_get_form_logs', [$this, 'ajaxGetFormLogs']);
        add_action('wp_ajax_ctm_clear_form_logs', [$this, 'ajaxClearFormLogs']);
        add_action('wp_ajax_ctm_get_form_log_stats', [$this, 'ajaxGetFormLogStats']);
    }

    /**
     * Initialize form usage AJAX handlers
     *
     * @since 2.0.0
     * @return void
     */
    private function initializeFormUsageAjax(): void
    {
        // Initialize the FormUsageAjax handler
        new \CTM\Admin\Ajax\FormUsageAjax();
    }

    /**
     * Initialize log loading AJAX handlers
     *
     * @since 2.0.0
     * @return void
     */
    private function initializeLogLoadingAjax(): void
    {
        add_action('wp_ajax_ctm_load_more_logs', [$this, 'ajaxLoadMoreLogs']);
        add_action('wp_ajax_ctm_load_more_days', [$this, 'ajaxLoadMoreDays']);
    }

    /**
     * AJAX handler for getting form-specific logs
     *
     * @since 2.0.0
     * @return void
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
     * AJAX handler for clearing form-specific logs
     *
     * @since 2.0.0
     * @return void
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
     * AJAX handler for getting form log statistics
     *
     * @since 2.0.0
     * @return void
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
     * AJAX handler for loading more logs
     *
     * @since 2.0.0
     * @return void
     */
    public function ajaxLoadMoreLogs(): void
    {
        check_ajax_referer('ctm_load_more_logs', 'nonce');

        $date = sanitize_text_field($_POST['date'] ?? '');
        $offset = (int) ($_POST['offset'] ?? 0);
        $limit = (int) ($_POST['limit'] ?? 20);

        if (empty($date)) {
            wp_send_json_error(['message' => 'Date is required']);
            return;
        }

        try {
            // Get logs for the specific date using the new database method
            $logs = $this->loggingSystem->getLogsForDate($date);

            if (empty($logs)) {
                wp_send_json_error(['message' => 'No logs found for this date']);
                return;
            }

            // Get the subset of logs based on offset and limit
            $total_count = count($logs);
            $entries = array_slice($logs, $offset, $limit);
            $has_more = ($offset + $limit) < $total_count;

            wp_send_json_success([
                'entries' => $entries,
                'total' => $total_count,
                'has_more' => $has_more,
                'offset' => $offset + $limit
            ]);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => 'Failed to load more logs: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX handler for loading more days
     *
     * @since 2.0.0
     * @return void
     */
    public function ajaxLoadMoreDays(): void
    {
        check_ajax_referer('ctm_load_more_days', 'nonce');
        $offset = (int) ($_POST['offset'] ?? 0);
        $limit = (int) ($_POST['limit'] ?? 5);

        try {
            // Get available dates using the new database method
            $available_dates = $this->loggingSystem->getAvailableLogDates();
            if (empty($available_dates)) {
                wp_send_json_error(['message' => 'No log dates found']);
                return;
            }

            $total_days = count($available_dates);
            $requested_dates = array_slice($available_dates, $offset, $limit);
            $has_more = ($offset + $limit) < $total_days;

            $days = [];
            foreach ($requested_dates as $date) {
                $logs = $this->loggingSystem->getLogsForDate($date);
                if (empty($logs)) continue;

                $error_count = 0;
                $warning_count = 0;
                $info_count = 0;
                $debug_count = 0;

                foreach ($logs as $entry) {
                    switch ($entry['type']) {
                        case 'error': $error_count++; break;
                        case 'warning': $warning_count++; break;
                        case 'info': $info_count++; break;
                        case 'debug': $debug_count++; break;
                    }
                }

                $days[] = [
                    'date' => $date,
                    'logs' => $logs,
                    'error_count' => $error_count,
                    'warning_count' => $warning_count,
                    'info_count' => $info_count,
                    'debug_count' => $debug_count,
                    'total_count' => count($logs)
                ];
            }

            wp_send_json_success([
                'days' => $days,
                'total_days' => $total_days,
                'has_more' => $has_more,
                'offset' => $offset + $limit
            ]);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => 'Failed to load more days: ' . $e->getMessage()]);
        }
    }

    /**
     * Attach dashboard widget (stub for future modularization)
     *
     * This method is reserved for future dashboard functionality.
     * Currently serves as a placeholder for additional dashboard features.
     *
     * @since 2.0.0
     * @return void
     */
    public function attachDashboard(): void
    {
        // Future enhancement: Additional dashboard functionality
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
     * Check for forms without phone numbers and show warnings
     *
     * @since 2.0.0
     * @return void
     */
    public function checkFormsWithoutPhone(): void
    {
        $formsWithoutPhone = [];

        // Check CF7 forms
        if ($this->cf7Active()) {
            $cf7Forms = \WPCF7_ContactForm::find(['posts_per_page' => -1]);
            foreach ($cf7Forms as $form) {
                if (!$this->cf7Service->hasPhoneField($form)) {
                    $formsWithoutPhone[] = [
                        'type' => 'CF7',
                        'id' => $form->id(),
                        'title' => $form->title(),
                        'edit_url' => admin_url('admin.php?page=wpcf7&post=' . $form->id() . '&action=edit')
                    ];
                }
            }
        }

        // Check GF forms
        if ($this->gfActive()) {
            $gfForms = \GFAPI::get_forms();
            foreach ($gfForms as $form) {
                if (!$this->gfService->hasPhoneField($form)) {
                    $formsWithoutPhone[] = [
                        'type' => 'GF',
                        'id' => $form['id'],
                        'title' => $form['title'],
                        'edit_url' => admin_url('admin.php?page=gf_edit_forms&id=' . $form['id'])
                    ];
                }
            }
        }

        // Store the results for display
        if (!empty($formsWithoutPhone)) {
            update_option('ctm_forms_without_phone', $formsWithoutPhone);
        } else {
            delete_option('ctm_forms_without_phone');
        }
    }

    /**
     * Show warnings for forms without phone numbers
     *
     * @since 2.0.0
     * @return void
     */
    public function showPhoneNumberWarnings(): void
    {
        // Only show on CTM admin pages
        if (!isset($_GET['page']) || $_GET['page'] !== 'call-tracking-metrics') {
            return;
        }

        // Check for forms without phone numbers
        $this->checkFormsWithoutPhone();
        $formsWithoutPhone = get_option('ctm_forms_without_phone', []);

        if (!empty($formsWithoutPhone)) {
            $count = count($formsWithoutPhone);
            $formTypes = array_unique(array_column($formsWithoutPhone, 'type'));
            $formTypeText = implode(' and ', $formTypes);

            echo '<div class="notice notice-warning is-dismissible">';
            echo '<p><strong>CallTrackingMetrics Warning:</strong> ';
            echo sprintf(
                '%d form%s without phone number fields detected. Forms without phone numbers will not work with CTM. ',
                $count,
                $count === 1 ? '' : 's'
            );
            echo '<a href="' . admin_url('admin.php?page=call-tracking-metrics&tab=forms') . '">View forms</a>';
            echo '</p>';

            if ($count <= 5) {
                echo '<ul style="margin-left: 20px; margin-top: 10px;">';
                foreach ($formsWithoutPhone as $form) {
                    echo '<li><strong>' . esc_html($form['title']) . '</strong> (' . esc_html($form['type']) . ') - ';
                    echo '<a href="' . esc_url($form['edit_url']) . '">Edit form</a></li>';
                }
                echo '</ul>';
            }

            echo '</div>';
        }
    }

    // ===================================================================
    // Helper Methods for Plugin State Management
    // ===================================================================

    /**
     * Check if Contact Form 7 integration is enabled
     *
     * @since 2.0.0
     * @return bool True if CF7 integration is enabled
     */
    private function cf7Enabled(): bool
    {
        return (bool) get_option('ctm_api_cf7_enabled', true);
    }

    /**
     * Check if Contact Form 7 plugin is active
     *
     * @since 2.0.0
     * @return bool True if CF7 plugin is active
     */
    private function cf7Active(): bool
    {
        return is_plugin_active('contact-form-7/wp-contact-form-7.php');
    }

    /**
     * Check if Gravity Forms integration is enabled
     *
     * @since 2.0.0
     * @return bool True if GF integration is enabled
     */
    private function gfEnabled(): bool
    {
        return (bool) get_option('ctm_api_gf_enabled', true);
    }

    /**
     * Check if Gravity Forms plugin is active
     *
     * @since 2.0.0
     * @return bool True if GF plugin is active
     */
    private function gfActive(): bool
    {
        return is_plugin_active('gravityforms/gravityforms.php');
    }

    /**
     * Generate the appropriate tracking script for the site
     *
     * Returns either a custom tracking script or the default CTM tracking
     * script based on authentication status and configuration.
     *
     * @since 2.0.0
     * @return string The tracking script HTML
     */
    /**
     * Get the tracking domain based on the configured API URL
     *
     * @since 2.1.0
     * @return string The tracking domain
     */
    private function getTrackingDomain(): string
    {
        $api_url = \ctm_get_api_url();
        $host = parse_url($api_url, PHP_URL_HOST);
        $port = parse_url($api_url, PHP_URL_PORT);

        if (!$host) {
            return 'tctm.co';
        }

        // Handle standard production endpoints
        if ($host === 'api.calltrackingmetrics.com') {
            return 'tctm.co';
        }
        if ($host === 'api.calltrackingmetrics.de') {
            return 'tctm.de';
        }

        // For custom/development environments, derive domain from API host
        // Remove 'api.' prefix if present
        if (strpos($host, 'api.') === 0) {
            $host = substr($host, 4);
        }

        // Include port if present (important for dev environments)
        if ($port) {
            $host .= ':' . $port;
        }

        return $host;
    }

    /**
     * Generate the appropriate tracking script for the site
     *
     * Returns either a custom tracking script or the default CTM tracking
     * script based on authentication status and configuration.
     *
     * @since 2.0.0
     * @return string The tracking script HTML
     */
    private function getTrackingScript(): string
    {
        $stored_script = get_option('call_track_account_script');
        $script_to_inject = '';

        // 1. If we have a stored script, prefer it (handles custom scripts and API-fetched dev URLs)
        if (!empty($stored_script)) {
            $script_to_inject = $stored_script;

            // Handle protocol-relative URLs or URLs starting with http
            if (substr($script_to_inject, 0, 2) === '//' || strpos($script_to_inject, 'http') === 0) {
                $script_to_inject = '<script data-cfasync="false" async src="' . esc_url($script_to_inject) . '"></script>';
            } elseif (strpos($script_to_inject, '<script') === false) {
                // If it's a raw JS snippet or URL, wrap it
                $script_to_inject = '<script data-cfasync="false" async>' . $script_to_inject . '</script>';
            }
        } else {
            // 2. Fallback to account-specific dynamic tracking script if authorized
            $accountId = get_option('ctm_api_auth_account');
            if ($accountId && $this->authorized()) {
                $domain = $this->getTrackingDomain();
                $scriptUrl = defined('CTM_TRACKING_SCRIPT_URL') ? CTM_TRACKING_SCRIPT_URL : '//' . esc_attr($accountId) . '.' . $domain . '/t.js';
                $script_to_inject = '<script data-cfasync="false" async src="' . $scriptUrl . '"></script>';
            }
        }

        if (empty($script_to_inject)) {
            return '';
        }

        // Force HTTPS for dev ports (4433, 4443) to prevent ORB blocking on HTTP sites
        if ((strpos($script_to_inject, ':4433') !== false || strpos($script_to_inject, ':4443') !== false) && strpos($script_to_inject, 'src="//') !== false) {
            $script_to_inject = str_replace('src="//', 'src="https://', $script_to_inject);
        }

        return $script_to_inject;
    }

    /**
     * Check if the plugin is in authorization mode
     *
     * @since 2.0.0
     * @return bool True if API credentials are provided
     */
    private function authorizing(): bool
    {
        return (bool) (get_option('ctm_api_key') && get_option('ctm_api_secret'));
    }

    /**
     * Check if the plugin is properly authorized with CTM
     *
     * @since 2.0.0
     * @return bool True if authorized or credentials are missing
     */
    private function authorized(): bool
    {
        return (bool) (get_option('ctm_api_auth_account') || !get_option('ctm_api_key') || !get_option('ctm_api_secret'));
    }
}

// ===================================================================
// Plugin Bootstrap
// ===================================================================

/**
 * Initialize the CallTrackingMetrics plugin
 *
 * This creates the main plugin instance and starts all functionality.
 * The plugin will automatically register all necessary hooks and
 * initialize all services.
 */
// Only instantiate if not running under test
if (!defined('CTM_TESTING')) {
    new CallTrackingMetrics();
}

add_action('admin_init', function() {
    global $wp_version;
    $min_wp = '6.5';
    $min_gf = '2.7';
    $min_cf7 = '5.7';
    $gf_active = class_exists('GFAPI');
    $cf7_active = class_exists('WPCF7_ContactForm');
    $notices = [];
    if (version_compare($wp_version, $min_wp, '<')) {
        $notices[] = 'CallTrackingMetrics requires WordPress ' . $min_wp . ' or higher. You are running ' . $wp_version . '.';
    }
    if ($gf_active) {
        if (defined('GF_VERSION')) {
            $gf_version = constant('GF_VERSION');
            if (version_compare($gf_version, $min_gf, '<')) {
                $notices[] = 'CallTrackingMetrics requires Gravity Forms ' . $min_gf . ' or higher. You are running ' . $gf_version . '.';
            }
        } // else: skip version check if GF_VERSION is not defined
    }
    // if ($cf7_active && defined('WPCF7_VERSION') && version_compare(WPCF7_VERSION, $min_cf7, '<')) {
    //     $notices[] = 'CallTrackingMetrics requires Contact Form 7 ' . $min_cf7 . ' or higher. You are running ' . WPCF7_VERSION . '.';
    // }
    // if(!get_option('call_track_account_script')) {
    //     $notices[] = 'CallTrackingMetrics tracking script is missing. Please save your API credentials to generate the tracking script.';
    // }
    if (!empty($notices)) {
        add_action('admin_notices', function() use ($notices) {
            foreach ($notices as $msg) {
                echo '<div class="notice notice-error"><p>' . esc_html($msg) . '</p></div>';
            }
        });
        // Optionally, disable integrations if version is not met
        // Temporarily disabled to prevent potential timeout issues
        // update_option('ctm_api_gf_enabled', false);
        // update_option('ctm_api_cf7_enabled', false);
    }
});

add_action('admin_footer', function() {
    echo '<div id="ctm-toast-container" style="position: fixed; top: 1.5rem; right: 1.5rem; z-index: 9999;"></div>';
});
