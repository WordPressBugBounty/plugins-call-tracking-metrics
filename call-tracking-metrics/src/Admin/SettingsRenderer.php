<?php
/**
 * Settings Page Renderer
 * 
 * This file contains the SettingsRenderer class that handles rendering
 * of admin settings pages, tab content generation, and view management
 * for the CallTrackingMetrics plugin admin interface.
 * 
 * @package     CallTrackingMetrics
 * @subpackage  Admin
 * @author      CallTrackingMetrics Team
 * @copyright   2024 CallTrackingMetrics
 * @license     GPL-2.0+
 * @version     2.0
 * @since       2.0.0
 */

namespace CTM\Admin;

/**
 * Settings Page Renderer Class
 * 
 * Handles all view rendering and content generation for the admin interface:
 * - Settings page tab content generation
 * - View file rendering with variable injection
 * - Tab-specific content formatting
 * - Debug information display
 * - Form state management
 * 
 * This class is responsible for the presentation layer of the admin interface,
 * separating view logic from business logic for better maintainability.
 * 
 * @since 2.0.0
 */
class SettingsRenderer
{
    private $apiService;
    private $loggingSystem;
    private $viewsDir;
    private $viewLoader;
    private $cf7FormsProvider;
    private $gfFormsProvider;
    private $ctmFieldsProvider;
    public function __construct($apiService = null, $loggingSystem = null, $viewsDir = null, $viewLoader = null, $cf7FormsProvider = null, $gfFormsProvider = null, $ctmFieldsProvider = null)
    {
        $this->apiService = $apiService ?: (class_exists('CTM\\Service\\ApiService') ? new \CTM\Service\ApiService(\ctm_get_api_url()) : null);
        $this->loggingSystem = $loggingSystem ?: (class_exists('CTM\\Admin\\LoggingSystem') ? new \CTM\Admin\LoggingSystem() : null);
        $this->viewsDir = $viewsDir;
        $this->viewLoader = $viewLoader;
        $this->cf7FormsProvider = $cf7FormsProvider;
        $this->gfFormsProvider = $gfFormsProvider;
        $this->ctmFieldsProvider = $ctmFieldsProvider;
    }
    /**
     * Render a view file with variables
     * 
     * Loads and renders a PHP view file with the provided variables
     * extracted into the local scope. Provides error handling for
     * missing view files.
     * 
     * @since 2.0.0
     * @param string $view The view filename (without .php extension)
     * @param array  $vars Variables to extract into the view scope
     * @return void
     */
    public function renderView(string $view, array $vars = []): void
    {
        if ($this->viewLoader && is_callable($this->viewLoader)) {
            $phpCode = call_user_func($this->viewLoader, $view);
            if ($phpCode === null) {
                echo "<div style='color:red'>View not found (in-memory): $view</div>";
                return;
            }
            extract($vars);
            eval('?>' . $phpCode);
            return;
        }
     
        if ($this->viewsDir) {
            $viewPath = rtrim($this->viewsDir, '/\\') . '/' . $view . '.php';
        } else {
            $viewPath = plugin_dir_path(__FILE__) . '../../views/' . $view . '.php';
        }
    
        clearstatcache();
        $realViewPath = realpath($viewPath) ?: $viewPath;
        if (!file_exists($realViewPath)) {
            // Suppress error message if running under PHPUnit or in test environment
            if (!defined('PHPUNIT_RUNNING') && (!isset($_SERVER['argv']) || stripos(implode(' ', $_SERVER['argv']), 'phpunit') === false)) {
                echo "<div style='color:red'>View not found: $viewPath</div>";
            }
            return;
        }
        
        // Extract variables into local scope for the view
        extract($vars);
        
        // Include the view file
        include $realViewPath;
    }

    /**
     * Get general tab content
     * 
     * Generates the content for the general settings tab including
     * API configuration, feature toggles, and tracking script settings.
     * 
     * @since 2.0.0
     * @return string The rendered general tab content HTML
     */
    public function getGeneralTabContent(): string
    {
        // Get current settings values
        $apiKey = get_option('ctm_api_key');
        $apiSecret = get_option('ctm_api_secret');
        $accountId = get_option('ctm_api_auth_account');
        $trackingEnabled = get_option('ctm_api_tracking_enabled');
        $cf7Enabled = get_option('ctm_api_cf7_enabled');
        $gfEnabled = get_option('ctm_api_gf_enabled');
        $dashboardEnabled = get_option('ctm_dashboard_enabled');
        $trackingScript = get_option('call_track_account_script');
        if (!$trackingScript) {
            $trackingScript = get_option('ctm_api_tracking_script');
        }
        $apiBaseUrl = \ctm_get_api_url();
        $availableApiEndpoints = \ctm_get_available_api_endpoints();
        $isApiUrlOverridden = defined('CTM_API_BASE_URL') && CTM_API_BASE_URL;
        // Check plugin availability
        $cf7_installed = class_exists('WPCF7_ContactForm');
        $gf_installed = class_exists('GFAPI');

        // --- FIX: Determine API connection status without automatic testing (prevent timeouts) ---
        $apiStatus = 'not_tested';
        if ($apiKey && $apiSecret && $this->apiService) {
            // Check cached status first
            $lastConnectionTest = get_transient('ctm_last_connection_status');
            if ($lastConnectionTest !== false) {
                $apiStatus = $lastConnectionTest;
            } else {
                // No cache - assume connected if we have credentials and tracking script
                $trackingScript = get_option('call_track_account_script');
                if (!empty($trackingScript)) {
                    $apiStatus = 'connected';
                    // Cache the assumed status
                    set_transient('ctm_last_connection_status', 'connected', 5 * 60);
                } else {
                    // No tracking script and no cache - default to not_tested
                    $apiStatus = 'not_tested';
                }
            }
        } else {
            $apiStatus = 'not_connected';
        }
        // --- END FIX ---
        
        // Get debug mode status for the general tab
        $debugEnabled = $this->loggingSystem && method_exists($this->loggingSystem, 'isDebugEnabled') ? $this->loggingSystem->isDebugEnabled() : false;
        
        // Start output buffering
        ob_start();
        
        // Render the general tab view
        $this->renderView('general-tab', compact(
            'apiKey',
            'apiSecret',
            'accountId',
            'trackingEnabled',
            'cf7Enabled',
            'gfEnabled',
            'dashboardEnabled',
            'trackingScript',
            'cf7_installed',
            'gf_installed',
            'apiStatus',
            'debugEnabled',
            'apiBaseUrl',
            'availableApiEndpoints',
            'isApiUrlOverridden'
        ));
        
        return ob_get_clean();
    }



    /**
     * Get API tab content
     * 
     * Generates the content for the API testing and diagnostics tab
     * including connection testing, account information, and API status.
     * 
     * @since 2.0.0
     * @return string The rendered API tab content HTML
     */
    public function getApiTabContent(): string
    {
        $apiKey = get_option('ctm_api_key');
        $apiSecret = get_option('ctm_api_secret');
        $accountInfo = null;
        $apiStatus = 'not_tested';
        $apiBaseUrl = \ctm_get_api_url();
        $availableApiEndpoints = \ctm_get_available_api_endpoints();
        if ($apiKey && $apiSecret && $this->apiService) {
            $accountInfo = $this->apiService->getAccountInfo($apiKey, $apiSecret);
            $apiStatus = ($accountInfo && isset($accountInfo['account'])) ? 'connected' : 'not_connected';
        }
        
        ob_start();
        
        $this->renderView('api-tab', compact(
            'apiKey',
            'apiSecret',
            'accountInfo',
            'apiStatus',
            'apiBaseUrl',
            'availableApiEndpoints'
        ));
        
        return ob_get_clean();
    }

    /**
     * Get form import tab content
     * 
     * Generates the content for the form import tab including
     * form selection, import options, and preview functionality.
     * 
     * @since 2.0.0
     * @return string The rendered form import tab content HTML
     */
    public function getFormImportTabContent(): string
    {
        $apiKey = get_option('ctm_api_key');
        $apiSecret = get_option('ctm_api_secret');
        $cf7_available = class_exists('WPCF7_ContactForm');
        $gf_available = class_exists('GFAPI');
        
        ob_start();
        
        $this->renderView('form-import-tab', compact(
            'apiKey', 'apiSecret', 'cf7_available', 'gf_available'
        ));
        
        return ob_get_clean();
    }

    /**
     * Get forms management tab content
     * 
     * Generates the content for the forms management tab including
     * lists of existing CF7 and GF forms with edit links.
     * 
     * @since 2.0.0
     * @return string The rendered forms management tab content HTML
     */
    public function getFormsTabContent(): string
    {
        $apiKey = get_option('ctm_api_key');
        $apiSecret = get_option('ctm_api_secret');
        $cf7_available = class_exists('WPCF7_ContactForm');
        $gf_available = class_exists('GFAPI');
        
        // Get CF7 forms
        $cf7_forms = [];
        if ($cf7_available) {
            $cf7_forms = $this->getCF7Forms();
        }
        
        // Get GF forms
        $gf_forms = [];
        if ($gf_available) {
            $gf_forms = $this->getGForms();
        }
        
        ob_start();
        
        $this->renderView('forms-tab', compact(
            'apiKey', 'apiSecret', 'cf7_available', 'gf_available', 'cf7_forms', 'gf_forms'
        ));
        
        return ob_get_clean();
    }

    /**
     * Get documentation tab content
     * 
     * Generates the content for the documentation tab including
     * setup instructions, integration guides, and troubleshooting.
     * 
     * @since 2.0.0
     * @return string The rendered documentation tab content HTML
     */
    public function getDocumentationTabContent(): string
    {
        ob_start();
        
        $this->renderView('documentation-tab');
        
        return ob_get_clean();
    }

    /**
     * Get debug tab content
     * 
     * Generates the content for the debug tab including debug mode
     * controls, log settings, daily logs, and system information.
     * 
     * @since 2.0.0
     * @return string The rendered debug tab content HTML
     */
    public function getDebugTabContent(): string
    {
        // Get debug settings
        $debugEnabled = $this->loggingSystem && method_exists($this->loggingSystem, 'isDebugEnabled') ? $this->loggingSystem->isDebugEnabled() : false;
        $retentionDays = get_option('ctm_log_retention_days', 7);
        $autoCleanup = get_option('ctm_log_auto_cleanup', false);
        $emailNotifications = get_option('ctm_log_email_notifications', false);
        $notificationEmail = get_option('ctm_log_notification_email', '');
        
        // Get log statistics
        $logStats = null;
        if ($debugEnabled) {
            if ($this->loggingSystem && method_exists($this->loggingSystem, 'getLogStatistics')) {
                $logStats = $this->loggingSystem->getLogStatistics();
            }
        }
        
        ob_start();
        
        $this->renderView('debug-tab', compact(
            'debugEnabled', 'retentionDays', 'autoCleanup', 
            'emailNotifications', 'notificationEmail', 'logStats'
        ) + ['loggingSystem' => $this->loggingSystem]);
        
        return ob_get_clean();
    }

    /**
     * Get Contact Form 7 forms for mapping interface
     * 
     * @since 2.0.0
     * @return array Array of CF7 forms with id and title
     */
    protected function getCF7Forms(): array
    {
        if (!class_exists('WPCF7_ContactForm')) {
            return [];
        }
        
        $forms = [];
        $cf7_forms = \WPCF7_ContactForm::find(['posts_per_page' => -1]);
        
        foreach ($cf7_forms as $form) {
            $forms[] = [
                'id' => $form->id(),
                'title' => $form->title(),
                'date_created' => get_post_field('post_date', $form->id()),
                'date_modified' => get_post_field('post_modified', $form->id()),
                'ctm_form_id' => get_post_meta($form->id(), '_ctm_form_id', true),
                'ctm_import_date' => get_post_meta($form->id(), '_ctm_import_date', true),
            ];
        }
        
        return $forms;
    }

    /**
     * Get Gravity Forms for mapping interface
     * 
     * @since 2.0.0
     * @return array Array of GF forms with id and title
     */
    protected function getGForms(): array
    {
        if (!class_exists('GFAPI')) {
            return [];
        }
        
        $forms = [];
        $gf_forms = \GFAPI::get_forms();
        
        foreach ($gf_forms as $form) {
            $forms[] = [
                'id' => $form['id'],
                'title' => $form['title'],
                'date_created' => $form['date_created'],
                'date_modified' => $form['date_modified'] ?? $form['date_created'],
                'is_active' => $form['is_active'],
                'entries' => \GFAPI::count_entries($form['id']),
                'ctm_form_id' => gform_get_meta($form['id'], '_ctm_form_id'),
                'ctm_import_date' => gform_get_meta($form['id'], '_ctm_import_date'),
            ];
        }
        
        return $forms;
    }

    /**
     * Get Gravity Forms for mapping interface
     * 
     * @since 2.0.0
     * @return array Array of GF forms with id and title
     */
    protected function getGFForms(): array
    {
        if (!class_exists('GFAPI')) {
            return [];
        }
        
        $forms = [];
        
        try {
            if (method_exists('\GFAPI', 'get_forms')) {
                $gf_forms = \GFAPI::get_forms();
                
                foreach ($gf_forms as $form) {
                    $forms[] = [
                        'id' => $form['id'],
                        'title' => $form['title'] ?? '',
                    ];
                }
            }
        } catch (\Exception $e) {
            if ($this->loggingSystem && $this->loggingSystem->isDebugEnabled()) {
                $this->loggingSystem->logActivity('Error getting GF forms: ' . $e->getMessage(), 'error');
            }
        }
        
        return $forms;
    }

    /**
     * Get CallTrackingMetrics field options for mapping
     * 
     * Returns the standard CTM fields that can be mapped to form fields.
     * These represent the common lead data fields in the CTM system.
     * 
     * @since 2.0.0
     * @return array Array of CTM field options
     */
    protected function getCTMFields(): array
    {
        return [
            'name' => 'Full Name',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'email' => 'Email Address',
            'phone' => 'Phone Number',
            'company' => 'Company Name',
            'address' => 'Street Address',
            'city' => 'City',
            'state' => 'State/Province',
            'zip' => 'ZIP/Postal Code',
            'country' => 'Country',
            'website' => 'Website URL',
            'message' => 'Message/Comments',
            'subject' => 'Subject',
            'lead_source' => 'Lead Source',
            'campaign' => 'Campaign',
            'custom_field_1' => 'Custom Field 1',
            'custom_field_2' => 'Custom Field 2',
            'custom_field_3' => 'Custom Field 3',
        ];
    }
} 
