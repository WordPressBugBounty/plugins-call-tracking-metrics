<?php
/**
 * AJAX Handlers Management
 * 
 * This file contains the main AJAX handlers coordinator class that manages
 * all AJAX request handlers for the CallTrackingMetrics plugin.
 * 
 * @package     CallTrackingMetrics
 * @subpackage  Admin
 * @author      CallTrackingMetrics Team
 * @copyright   2024 CallTrackingMetrics
 * @license     GPL-2.0+
 * @version     2.0.0
 * @link        https://calltrackingmetrics.com
 * @since       1.0.0
 */

namespace CTM\Admin;

use CTM\Admin\Ajax\FormAjax;
use CTM\Admin\Ajax\LogAjax;
use CTM\Admin\Ajax\ApiAjax;
use CTM\Admin\Ajax\SystemAjax;
use CTM\Admin\Ajax\SystemSecurityAjax;
use CTM\Admin\Ajax\SystemPerformanceAjax;
use CTM\Admin\Ajax\FormImportAjax;
use CTM\Service\FormImportService;
use CTM\Service\ApiService;
use CTM\Service\CF7Service;
use CTM\Service\GFService;

/**
 * AJAX Handlers Coordinator
 * 
 * Central coordinator for all AJAX request handlers in the CTM plugin.
 * This class manages the instantiation and registration of all AJAX handlers
 * including form handlers, API handlers, system handlers, and logging handlers.
 * 
 * Provides dependency injection capabilities for testing and modular design.
 * All AJAX handlers are instantiated with proper dependencies and registered
 * through this central coordinator.
 * 
 * @package     CallTrackingMetrics
 * @subpackage  Admin
 * @author      CallTrackingMetrics Team
 * @since       1.0.0
 * @version     2.0.0
 */
class AjaxHandlers
{
    /**
     * Logging system instance
     * 
     * @since 1.0.0
     * @var LoggingSystem
     */
    private LoggingSystem $loggingSystem;

    /**
     * Settings renderer instance
     * 
     * @since 1.0.0
     * @var SettingsRenderer
     */
    private SettingsRenderer $renderer;

    /**
     * Form AJAX handler instance
     * 
     * @since 1.0.0
     * @var FormAjax
     */
    private FormAjax $formAjax;

    /**
     * Log AJAX handler instance
     * 
     * @since 1.0.0
     * @var LogAjax
     */
    private LogAjax $logAjax;

    /**
     * API AJAX handler instance
     * 
     * @since 1.0.0
     * @var ApiAjax
     */
    private ApiAjax $apiAjax;

    /**
     * System AJAX handler instance
     * 
     * @since 1.0.0
     * @var SystemAjax
     */
    private SystemAjax $systemAjax;

    /**
     * System security AJAX handler instance
     * 
     * @since 2.0.0
     * @var SystemSecurityAjax
     */
    private SystemSecurityAjax $systemSecurityAjax;

    /**
     * System performance AJAX handler instance
     * 
     * @since 2.0.0
     * @var SystemPerformanceAjax
     */
    private SystemPerformanceAjax $systemPerformanceAjax;

    /**
     * Form import AJAX handler instance
     * 
     * @since 2.0.0
     * @var FormImportAjax
     */
    private FormImportAjax $formImportAjax;

    /**
     * Initialize AJAX handlers coordinator
     * 
     * Sets up all AJAX handler instances with dependency injection support.
     * If dependencies are not provided, default instances will be created.
     * This allows for easy testing and modular design.
     * 
     * @since 1.0.0
     * @param LoggingSystem|null              $loggingSystem         Optional logging system instance
     * @param SettingsRenderer|null           $renderer              Optional settings renderer instance
     * @param FormAjax|null                   $formAjax              Optional form AJAX handler instance
     * @param LogAjax|null                    $logAjax               Optional log AJAX handler instance
     * @param ApiAjax|null                    $apiAjax               Optional API AJAX handler instance
     * @param SystemAjax|null                 $systemAjax            Optional system AJAX handler instance
     * @param SystemSecurityAjax|null         $systemSecurityAjax    Optional security AJAX handler instance
     * @param SystemPerformanceAjax|null      $systemPerformanceAjax Optional performance AJAX handler instance
     * @param FormImportAjax|null             $formImportAjax        Optional form import AJAX handler instance
     */
    public function __construct(
        $loggingSystem = null,
        $renderer = null,
        $formAjax = null,
        $logAjax = null,
        $apiAjax = null,
        $systemAjax = null,
        $systemSecurityAjax = null,
        $systemPerformanceAjax = null,
        $formImportAjax = null
    ) {
        $this->loggingSystem = $loggingSystem ?: new LoggingSystem();
        $this->renderer = $renderer ?: new SettingsRenderer();
        $this->formAjax = $formAjax ?: new FormAjax();
        $this->logAjax = $logAjax ?: new LogAjax($this->loggingSystem);
        $this->apiAjax = $apiAjax ?: new ApiAjax();
        $this->systemAjax = $systemAjax ?: new SystemAjax($this->loggingSystem);
        $this->systemSecurityAjax = $systemSecurityAjax ?: new SystemSecurityAjax($this->loggingSystem);
        $this->systemPerformanceAjax = $systemPerformanceAjax ?: new SystemPerformanceAjax($this->loggingSystem);
        $this->formImportAjax = $formImportAjax ?: new FormImportAjax(
            new FormImportService(
                new ApiService(ctm_get_api_url()),
                new CF7Service(),
                new GFService()
            )
        );
    }

    /**
     * Register all AJAX handlers
     * 
     * Registers all AJAX handlers with WordPress. This method should be called
     * during plugin initialization to ensure all AJAX endpoints are available.
     * 
     * Each handler is responsible for registering its own specific AJAX actions
     * with WordPress using add_action() for both logged-in and non-logged-in users
     * as appropriate.
     * 
     * @since 1.0.0
     * @return void
     */
    public function registerHandlers(): void
    {
        $this->formAjax->registerHandlers();
        $this->logAjax->registerHandlers();
        $this->apiAjax->registerHandlers();
        $this->systemAjax->registerHandlers();
        $this->systemSecurityAjax->registerHandlers();
        $this->systemPerformanceAjax->registerHandlers();
        $this->formImportAjax->registerHandlers();
    }
} 