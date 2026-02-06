<?php

declare(strict_types=1);

namespace CTM\Admin\Ajax;

use CTM\Service\FormImportService;
use CTM\Admin\LoggingSystem;

/**
 * Form Import AJAX Handler
 * 
 * Handles AJAX requests for form import functionality
 * 
 * @since 2.0.0
 */
class FormImportAjax
{
    /**
     * Form import service
     * 
     * @var FormImportService
     */
    private FormImportService $formImportService;

    /**
     * Logging System instance
     * 
     * @since 2.0.0
     * @var LoggingSystem|null
     */
    private $loggingSystem;

    /**
     * Constructor
     * 
     * @param FormImportService $formImportService Form import service
     * @param LoggingSystem|null $loggingSystem The logging system
     */
    public function __construct(FormImportService $formImportService, $loggingSystem = null)
    {
        $this->formImportService = $formImportService;
        $this->loggingSystem = $loggingSystem;
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
        if ($this->loggingSystem && $this->loggingSystem->isDebugEnabled()) {
            $this->loggingSystem->logActivity($message, $type);
        }
    }

    /**
     * Register AJAX handlers
     * 
     * @since 2.0.0
     */
    public function registerHandlers(): void
    {
        add_action('wp_ajax_ctm_get_available_forms', [$this, 'getAvailableForms']);
        add_action('wp_ajax_ctm_import_form', [$this, 'importForm']);
        add_action('wp_ajax_ctm_preview_form', [$this, 'previewForm']);
        add_action('wp_ajax_ctm_sync_form', [$this, 'syncForm']);
        add_action('wp_ajax_ctm_update_form', [$this, 'updateForm']);
        add_action('wp_ajax_ctm_preview_wp_form', [$this, 'previewWPForm']);
    }

    /**
     * Get available forms from CTM
     * 
     * @since 2.0.0
     */
    public function getAvailableForms(): void
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ctm_form_import_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
        }

        // Get API credentials
        $apiKey = get_option('ctm_api_key');
        $apiSecret = get_option('ctm_api_secret');

        if (!$apiKey || !$apiSecret) {
            wp_send_json_error(['message' => 'API credentials not configured']);
        }

        try {
            $forms = $this->formImportService->getAvailableForms($apiKey, $apiSecret);
            
            if ($forms === false) {
                wp_send_json_error(['message' => 'Failed to load forms from CallTrackingMetrics']);
            }

            // Add import status to each form
            if ($forms) {
                foreach ($forms as &$form) {
                    $importInfo = $this->formImportService->getImportedFormInfo($form['id']);
                    $form['import_status'] = $importInfo;
                }
                unset($form); // Break reference
            }

            wp_send_json_success([
                'forms' => $forms,
                'message' => count($forms) . ' forms loaded successfully'
            ]);

        } catch (\Exception $e) {
            wp_send_json_error(['message' => 'Failed to load forms: ' . $e->getMessage()]);
        }
    }

    /**
     * Import a form from CTM
     * 
     * @since 2.0.0
     */
    public function importForm(): void
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ctm_form_import_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
        }

        try {
            $ctmFormId = sanitize_text_field($_POST['ctm_form_id'] ?? '');
            $targetType = sanitize_text_field($_POST['target_type'] ?? '');
            $formTitle = sanitize_text_field($_POST['form_title'] ?? '');
            $forceDuplicate = filter_var($_POST['force_duplicate'] ?? false, FILTER_VALIDATE_BOOLEAN);

            if (empty($ctmFormId) || empty($targetType) || empty($formTitle)) {
                wp_send_json_error(['message' => 'All fields are required']);
            }

            // Get API credentials
            $apiKey = get_option('ctm_api_key');
            $apiSecret = get_option('ctm_api_secret');

            if (!$apiKey || !$apiSecret) {
                wp_send_json_error(['message' => 'API credentials not configured']);
            }

            // Get the specific form data
            $forms = $this->formImportService->getAvailableForms($apiKey, $apiSecret);
            $ctmForm = null;

            if ($forms) {
                foreach ($forms as $form) {
                    if ($form['id'] == $ctmFormId) {
                        $ctmForm = $form;
                        break;
                    }
                }
            }

            if (!$ctmForm) {
                wp_send_json_error(['message' => 'Form not found']);
            }

            // Check for duplicates if not forcing
            if (!$forceDuplicate) {
                if ($targetType === 'cf7') {
                    $duplicate = $this->formImportService->checkForCF7Duplicate($ctmForm, $formTitle);
                    if ($duplicate) {
                        wp_send_json_success([
                            'duplicate_found' => true,
                            'existing_form' => $duplicate,
                            'message' => 'A form with identical content already exists.'
                        ]);
                        return;
                    }
                } elseif ($targetType === 'gf') {
                    $duplicate = $this->formImportService->checkForGFDuplicate($ctmForm, $formTitle);
                    if ($duplicate) {
                        wp_send_json_success([
                            'duplicate_found' => true,
                            'existing_form' => $duplicate,
                            'message' => 'A form with identical content already exists.'
                        ]);
                        return;
                    }
                }
            }

            // Import the form
            if ($targetType === 'cf7') {
                $result = $this->formImportService->importToCF7($ctmForm, $formTitle);
            } elseif ($targetType === 'gf') {
                $result = $this->formImportService->importToGF($ctmForm, $formTitle);
            } else {
                wp_send_json_error(['message' => 'Invalid target type']);
                return;
            }

            if ($result['success']) {
                wp_send_json_success([
                    'message' => $result['message'],
                    'form_id' => $result['form_id'],
                    'target_type' => $targetType,
                    'duplicate_found' => false
                ]);
            } else {
                wp_send_json_error(['message' => $result['error']]);
            }

        } catch (\Exception $e) {
            wp_send_json_error(['message' => 'Import failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Preview a form before import
     * 
     * @since 2.0.0
     */
    public function previewForm(): void
    {
        // Debug: Log that the method is being called
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ctm_form_import_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
        }

        try {
            $ctmFormId = sanitize_text_field($_POST['ctm_form_id'] ?? '');
            $targetType = sanitize_text_field($_POST['target_type'] ?? '');

            if (empty($ctmFormId) || empty($targetType)) {
                wp_send_json_error(['message' => 'Form ID and target type are required']);
                return;
            }

            // Get API credentials
            $apiKey = get_option('ctm_api_key');
            $apiSecret = get_option('ctm_api_secret');

            if (!$apiKey || !$apiSecret) {
                wp_send_json_error(['message' => 'API credentials not configured']);
                return;
            }

            // Get the specific form data
            $forms = $this->formImportService->getAvailableForms($apiKey, $apiSecret);
            
            if ($forms === false) {
                wp_send_json_error(['message' => 'Failed to load forms from CallTrackingMetrics']);
                return;
            }
            
            $ctmForm = null;
            if ($forms) {
                foreach ($forms as $form) {
                    if ($form['id'] == $ctmFormId) {
                        $ctmForm = $form;
                        break;
                    }
                }
            }

            if (!$ctmForm) {
                wp_send_json_error(['message' => 'Form not found']);
                return;
            }

            // Generate preview based on target type
            $rawCode = '';
            $renderedForm = '';
            
            // Debug logging removed for production
            
            if ($targetType === 'cf7') {
                $rawCode = $this->generateCF7RawCode($ctmForm);
                $renderedForm = $this->generateCF7Preview($ctmForm);
                // CF7 preview generated successfully
            } elseif ($targetType === 'gf') {
                $rawCode = $this->generateGFRawCode($ctmForm);
                $renderedForm = $this->generateGFPreview($ctmForm);
                // GF preview generated successfully
            } else {
                wp_send_json_error(['message' => 'Invalid target type: ' . $targetType]);
                return;
            }

            if (empty($rawCode) && empty($renderedForm)) {
                wp_send_json_error(['message' => 'No preview content was generated']);
                return;
            }
            wp_send_json_success([
                'raw_code' => $rawCode,
                'rendered_form' => $renderedForm,
                'form_data' => $ctmForm
            ]);

        } catch (\Exception $e) {
            wp_send_json_error(['message' => 'Preview failed: ' . $e->getMessage()]);
        } catch (\Error $e) {
            wp_send_json_error(['message' => 'Preview failed due to fatal error: ' . $e->getMessage()]);
        }
    }

    /**
     * Generate CF7 preview using real Contact Form 7 rendering
     * 
     * @since 2.0.0
     * @param array $ctmForm The CTM form data
     * @return string The preview HTML
     */
    private function generateCF7Preview(array $ctmForm): string
    {
        if (!class_exists('WPCF7_ContactForm')) {
            return '<div class="notice notice-error"><p>Contact Form 7 is not installed or activated.</p></div>';
        }

        try {
            // Convert CTM form to CF7 format using FormImportService
            $cf7Content = $this->formImportService->convertToCF7Format($ctmForm);
            
            // If no content, return a debug message
            if (empty($cf7Content)) {
                return '<div class="notice notice-warning"><p>No form content could be generated. Check the form data structure.</p></div>';
            }
            
            // Since CF7 doesn't allow easy temporary form creation, use a direct rendering approach
            // Always use the direct preview method to show actual HTML form elements
            return $this->generateCF7DirectPreview($cf7Content, $ctmForm);

            // This code is no longer used - preview is handled by the methods above
            return '';
            
        } catch (\Exception $e) {
            return '<div class="notice notice-error"><p>Error generating CF7 preview: ' . esc_html($e->getMessage()) . '</p></div>';
        }
    }

    /**
     * Generate GF preview using custom rendering
     * 
     * @since 2.0.0
     * @param array $ctmForm The CTM form data
     * @return string The preview HTML
     */
    private function generateGFPreview(array $ctmForm): string
    {
        if (!class_exists('GFAPI')) {
            return '<div class="notice notice-error"><p>Gravity Forms is not installed or activated.</p></div>';
        }

        try {
            // Convert CTM form to GF format using FormImportService
            $gfFormArray = $this->formImportService->convertToGFFormat($ctmForm, 'Preview: ' . ($ctmForm['name'] ?? 'CTM Form'));
            
            // Form conversion completed
            
            // If no form data, return a debug message
            if (empty($gfFormArray) || empty($gfFormArray['fields'])) {
                $debugInfo = [
                    'ctm_keys' => array_keys($ctmForm),
                    'gf_keys' => array_keys($gfFormArray ?? []),
                    'fields_exist' => isset($gfFormArray['fields']),
                    'fields_count' => isset($gfFormArray['fields']) ? count($gfFormArray['fields']) : 0
                ];
                return '<div class="notice notice-warning"><p>No form fields could be generated for Gravity Forms.</p><pre style="font-size: 11px; background: #f9f9f9; padding: 10px; margin-top: 10px;">' . print_r($debugInfo, true) . '</pre></div>';
            }

            // Use GF's actual plugin rendering for preview
            // Create a temporary form in GF to get proper rendering
            try {
                // Convert to GF format and create a temporary form
                $tempFormId = $this->createTemporaryGFForm($gfFormArray);
                if ($tempFormId) {
                    try {
                        $preview = $this->generateGFWPPreview((string)$tempFormId);
                        // Clean up temporary form
                        $this->cleanupTemporaryGFForm($tempFormId);
                        return $preview;
                    } catch (\Exception $previewException) {
                        // Clean up temporary form even if preview fails
                        $this->cleanupTemporaryGFForm($tempFormId);
                        error_log('GF Preview Error: ' . $previewException->getMessage());
                        // Fall through to basic preview
                    }
                }
            } catch (\Exception $e) {
                error_log('GF Temp Form Creation Error: ' . $e->getMessage());
                // Fallback to basic preview if temporary form creation fails
            }
            
            // Fallback to basic preview if temporary form creation fails
            return $this->generateBasicGFPreview($gfFormArray);

            
        } catch (\Exception $e) {
            return '<div class="notice notice-error"><p>Error generating GF preview: ' . esc_html($e->getMessage()) . '</p></div>';
        }
    }

    /**
     * Create a temporary GF form for preview purposes
     * 
     * @since 2.0.0
     * @param array $gfFormArray The GF form array
     * @return int|null The temporary form ID or null on failure
     */
    private function createTemporaryGFForm(array $gfFormArray): ?int
    {
        if (!class_exists('GFAPI')) {
            return null;
        }
        
        try {
            // Add a temporary flag to the form title
            $gfFormArray['title'] = 'CTM Preview - ' . $gfFormArray['title'] . ' (Temporary)';
            
            // Create the form using GF API
            $formId = \GFAPI::add_form($gfFormArray);
            
            if ($formId && !is_wp_error($formId)) {
                return $formId;
            }
        } catch (\Exception $e) {
            // Form creation failed
        }
        
        return null;
    }
    
    /**
     * Clean up a temporary GF form
     * 
     * @since 2.0.0
     * @param int $formId The form ID to delete
     * @return void
     */
    private function cleanupTemporaryGFForm(int $formId): void
    {
        if (!class_exists('GFAPI')) {
            return;
        }
        
        try {
            \GFAPI::delete_form($formId);
        } catch (\Exception $e) {
            // Form cleanup failed
        }
    }

    /**
     * Generate CF7 preview using direct shortcode processing
     * 
     * @since 2.0.0
     * @param string $cf7Content The CF7 form content
     * @param array $ctmForm The CTM form data
     * @return string The preview HTML
     */
    private function generateCF7DirectPreview(string $cf7Content, array $ctmForm): string
    {
        $this->logInternal('Preview Debug - Using direct CF7 shortcode processing');
        
        ob_start();
        echo '<div class="ctm-cf7-preview">';
        echo '<div class="preview-header" style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-left: 4px solid #007cba; border-radius: 4px;">';
        echo '<h4 style="margin: 0; color: #007cba;">Contact Form 7 Preview</h4>';
        echo '<p style="margin: 5px 0 0; color: #666; font-size: 14px;">This is how your form will appear when imported to Contact Form 7</p>';
        echo '</div>';
        
        // Create actual HTML form (disabled for preview only)
        echo '<form class="wpcf7-form" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 4px; pointer-events: none; opacity: 0.8;" onsubmit="return false;">';
        
        // Parse and render CF7 shortcodes as actual HTML form elements
        // Handle CF7 format: <label> Label Text [shortcode] </label>
        
        // Use regex to match complete label blocks
        if (preg_match_all('/<label>\s*([^<\[]*?)\s*(\[[^\]]+\])\s*<\/label>/s', $cf7Content, $labelMatches, PREG_SET_ORDER)) {
            foreach ($labelMatches as $match) {
                $labelText = trim($match[1]);
                $shortcode = trim($match[2], '[]');
                
                $rendered = $this->renderCF7ShortcodeAsHTML($shortcode);
                
                if (!empty($labelText) && strpos($shortcode, 'submit') === false) {
                    echo '<div style="margin-bottom: 15px;">';
                    echo '<label style="display: block; margin-bottom: 5px; font-weight: 500; color: #333;">' . esc_html($labelText) . '</label>';
                    echo $rendered;
                    echo '</div>';
                } else {
                    echo '<div style="margin-bottom: 15px;">' . $rendered . '</div>';
                }
            }
        }
        
        // Handle standalone shortcodes (like submit button)
        if (preg_match_all('/^\s*(\[[^\]]+\])\s*$/m', $cf7Content, $standaloneMatches)) {
            foreach ($standaloneMatches[1] as $shortcodeWithBrackets) {
                $shortcode = trim($shortcodeWithBrackets, '[]');
                $rendered = $this->renderCF7ShortcodeAsHTML($shortcode);
                echo '<div style="margin-bottom: 15px;">' . $rendered . '</div>';
            }
        }
        
        echo '</form>';
        echo '</div>';
        
        return ob_get_clean();
    }

    /**
     * Render a CF7 shortcode as actual HTML form element
     * 
     * @since 2.0.0
     * @param string $shortcode The CF7 shortcode content
     * @return string The rendered HTML form element
     */
    private function renderCF7ShortcodeAsHTML(string $shortcode): string
    {
        $parts = explode(' ', $shortcode);
        $type = $parts[0];
        $name = isset($parts[1]) ? $parts[1] : '';
        
        // Common styles for form elements
        $inputStyle = 'width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; line-height: 1.5;';
        
        switch ($type) {
            case 'text':
                $placeholder = $this->extractAttribute($shortcode, 'placeholder') ?: '';
                return '<input type="text" name="' . esc_attr($name) . '" placeholder="' . esc_attr($placeholder) . '" style="' . $inputStyle . '">';
                
            case 'email':
                return '<input type="email" name="' . esc_attr($name) . '" style="' . $inputStyle . '">';
                
            case 'tel':
                return '<input type="tel" name="' . esc_attr($name) . '" style="' . $inputStyle . '">';
                
            case 'url':
                return '<input type="url" name="' . esc_attr($name) . '" style="' . $inputStyle . '">';
                
            case 'number':
                return '<input type="number" name="' . esc_attr($name) . '" style="' . $inputStyle . '">';
                
            case 'date':
                return '<input type="date" name="' . esc_attr($name) . '" style="' . $inputStyle . '">';
                
            case 'textarea':
                return '<textarea name="' . esc_attr($name) . '" rows="4" style="' . $inputStyle . ' resize: vertical;"></textarea>';
                
            case 'select':
                $options = $this->extractSelectOptions($shortcode);
                $html = '<select name="' . esc_attr($name) . '" style="' . $inputStyle . '">';
                foreach ($options as $option) {
                    $html .= '<option value="' . esc_attr($option) . '">' . esc_html($option) . '</option>';
                }
                $html .= '</select>';
                return $html;
                
            case 'checkbox':
                $options = $this->extractCheckboxOptions($shortcode);
                $html = '';
                foreach ($options as $option) {
                    $html .= '<label style="display: block; margin-bottom: 5px;">';
                    $html .= '<input type="checkbox" name="' . esc_attr($name) . '[]" value="' . esc_attr($option) . '" style="margin-right: 8px;">';
                    $html .= esc_html($option);
                    $html .= '</label>';
                }
                return $html;
                
            case 'radio':
                $options = $this->extractRadioOptions($shortcode);
                $html = '';
                foreach ($options as $option) {
                    $html .= '<label style="display: block; margin-bottom: 5px;">';
                    $html .= '<input type="radio" name="' . esc_attr($name) . '" value="' . esc_attr($option) . '" style="margin-right: 8px;">';
                    $html .= esc_html($option);
                    $html .= '</label>';
                }
                return $html;
                
            case 'file':
                return '<input type="file" name="' . esc_attr($name) . '" style="' . $inputStyle . '">';
                
            case 'submit':
                $value = isset($parts[1]) ? str_replace(['"', "'"], '', $parts[1]) : 'Submit';
                return '<input type="submit" value="' . esc_attr($value) . '" disabled style="background: #ccc; color: #666; padding: 10px 20px; border: none; border-radius: 4px; cursor: not-allowed; font-size: 14px; font-weight: 500;" title="This is a preview - button is disabled">';
                
            default:
                return '<input type="text" name="' . esc_attr($name) . '" style="' . $inputStyle . '">';
        }
    }
    
    /**
     * Extract attribute value from shortcode
     */
    private function extractAttribute(string $shortcode, string $attribute): string
    {
        if (preg_match('/' . $attribute . ':([^\s\]]+)/', $shortcode, $matches)) {
            return $matches[1];
        }
        return '';
    }
    
    /**
     * Extract select options from shortcode
     */
    private function extractSelectOptions(string $shortcode): array
    {
        // Simple implementation - in real CF7, options would be defined differently
        return ['Option 1', 'Option 2', 'Option 3'];
    }
    
    /**
     * Extract checkbox options from shortcode
     */
    private function extractCheckboxOptions(string $shortcode): array
    {
        // Simple implementation
        return ['Option 1', 'Option 2', 'Option 3'];
    }
    
    /**
     * Extract radio options from shortcode
     */
    private function extractRadioOptions(string $shortcode): array
    {
        // Simple implementation
        return ['Option 1', 'Option 2', 'Option 3'];
    }

    /**
     * Render a CF7 shortcode for preview purposes (legacy method)
     * 
     * @since 2.0.0
     * @param string $shortcode The CF7 shortcode content
     * @return string The rendered HTML
     */
    private function renderCF7ShortcodeForPreview(string $shortcode): string
    {
        $parts = explode(' ', $shortcode);
        $type = $parts[0] ?? '';
        $name = '';
        
        // Extract field name
        for ($i = 1; $i < count($parts); $i++) {
            if (!str_contains($parts[$i], ':')) {
                $name = $parts[$i];
                break;
            }
        }
        
        switch ($type) {
            case 'text':
                return '<input type="text" name="' . esc_attr($name) . '" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" placeholder="Text input">';
            case 'email':
                return '<input type="email" name="' . esc_attr($name) . '" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" placeholder="Email address">';
            case 'tel':
                return '<input type="tel" name="' . esc_attr($name) . '" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" placeholder="Phone number">';
            case 'date':
                return '<input type="date" name="' . esc_attr($name) . '" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">';
            case 'textarea':
                return '<textarea name="' . esc_attr($name) . '" rows="4" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" placeholder="Message"></textarea>';
            case 'file':
                return '<input type="file" name="' . esc_attr($name) . '" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">';
            case 'submit':
                $label = str_replace(['"', "'"], '', implode(' ', array_slice($parts, 1)));
                return '<input type="submit" value="' . esc_attr($label ?: 'Submit') . '" style="background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">';
            default:
                return '<input type="text" name="' . esc_attr($name) . '" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" placeholder="' . esc_attr($type) . ' field">';
        }
    }

    /**
     * Generate a fallback CF7 preview when the main method fails
     * 
     * @since 2.0.0
     * @param string $cf7Content The CF7 form content
     * @param array $ctmForm The CTM form data
     * @return string The fallback preview HTML
     */
    private function generateCF7FallbackPreview(string $cf7Content, array $ctmForm): string
    {
        $this->logInternal('Preview Debug - Using CF7 fallback preview method');
        
        ob_start();
        echo '<div class="ctm-cf7-preview">';
        echo '<div class="preview-header" style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-left: 4px solid #007cba; border-radius: 4px;">';
        echo '<h4 style="margin: 0; color: #007cba;">Contact Form 7 Preview</h4>';
        echo '<p style="margin: 5px 0 0; color: #666; font-size: 14px;">Preview of form structure (CF7 shortcode format)</p>';
        echo '</div>';
        
        // Display the CF7 shortcode content in a styled format
        echo '<div class="cf7-content-preview" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 4px; font-family: monospace; white-space: pre-wrap; line-height: 1.5;">';
        echo esc_html($cf7Content);
        echo '</div>';
        
        echo '<div class="preview-note" style="margin-top: 15px; padding: 10px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px; color: #856404;">';
        echo '<strong>Note:</strong> This is the raw CF7 shortcode format. After import, it will render as a proper form.';
        echo '</div>';
        
        echo '</div>';
        return ob_get_clean();
    }

    /**
     * Sync existing WordPress form with CallTrackingMetrics form
     * 
     * @since 2.0.0
     */
    public function syncForm(): void
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ctm_form_import_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
            return;
        }

        try {
            $wpFormId = sanitize_text_field($_POST['wp_form_id'] ?? '');
            $wpFormType = sanitize_text_field($_POST['wp_form_type'] ?? '');
            $ctmFormId = sanitize_text_field($_POST['ctm_form_id'] ?? '');

            if (empty($wpFormId) || empty($wpFormType) || empty($ctmFormId)) {
                wp_send_json_error(['message' => 'Missing required parameters']);
                return;
            }

            // Validate form type
            if (!in_array($wpFormType, ['cf7', 'gf'])) {
                wp_send_json_error(['message' => 'Invalid form type']);
                return;
            }

            // Sync based on form type
            if ($wpFormType === 'cf7') {
                $this->syncCF7Form($wpFormId, $ctmFormId);
            } else {
                $this->syncGFForm($wpFormId, $ctmFormId);
            }

            wp_send_json_success([
                'message' => 'Form synced successfully',
                'wp_form_id' => $wpFormId,
                'ctm_form_id' => $ctmFormId
            ]);

        } catch (\Exception $e) {
            wp_send_json_error(['message' => 'Sync failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Sync Contact Form 7 form with CTM
     * 
     * @since 2.0.0
     * @param string $wpFormId WordPress form ID
     * @param string $ctmFormId CTM form ID
     */
    private function syncCF7Form(string $wpFormId, string $ctmFormId): void
    {
        // Verify CF7 form exists
        if (!class_exists('WPCF7_ContactForm')) {
            throw new \Exception('Contact Form 7 is not installed');
        }

        $cf7Form = \WPCF7_ContactForm::get_instance($wpFormId);
        if (!$cf7Form) {
            throw new \Exception('Contact Form 7 form not found');
        }

        // Store CTM metadata
        update_post_meta($wpFormId, '_ctm_imported', true);
        update_post_meta($wpFormId, '_ctm_form_id', $ctmFormId);
        update_post_meta($wpFormId, '_ctm_import_date', current_time('mysql'));
        update_post_meta($wpFormId, '_ctm_sync_method', 'manual_sync');
    }

    /**
     * Sync Gravity Forms form with CTM
     * 
     * @since 2.0.0
     * @param string $wpFormId WordPress form ID
     * @param string $ctmFormId CTM form ID
     */
    private function syncGFForm(string $wpFormId, string $ctmFormId): void
    {
        // Verify GF form exists
        if (!class_exists('GFAPI')) {
            throw new \Exception('Gravity Forms is not installed');
        }

        $gfForm = \GFAPI::get_form($wpFormId);
        if (!$gfForm) {
            throw new \Exception('Gravity Forms form not found');
        }

        // Store CTM metadata
        gform_update_meta($wpFormId, '_ctm_imported', true);
        gform_update_meta($wpFormId, '_ctm_form_id', $ctmFormId);
        gform_update_meta($wpFormId, '_ctm_import_date', current_time('mysql'));
        gform_update_meta($wpFormId, '_ctm_sync_method', 'manual_sync');
    }

    /**
     * Update existing WordPress form with content from CallTrackingMetrics form
     * 
     * @since 2.0.0
     */
    public function updateForm(): void
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ctm_form_import_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
            return;
        }

        try {
            $wpFormId = sanitize_text_field($_POST['wp_form_id'] ?? '');
            $wpFormType = sanitize_text_field($_POST['wp_form_type'] ?? '');
            $ctmFormId = sanitize_text_field($_POST['ctm_form_id'] ?? '');

            if (empty($wpFormId) || empty($wpFormType) || empty($ctmFormId)) {
                wp_send_json_error(['message' => 'Missing required parameters']);
                return;
            }

            // Validate form type
            if (!in_array($wpFormType, ['cf7', 'gf'])) {
                wp_send_json_error(['message' => 'Invalid form type']);
                return;
            }

            // Get API credentials
            $apiKey = get_option('ctm_api_key');
            $apiSecret = get_option('ctm_api_secret');

            if (!$apiKey || !$apiSecret) {
                wp_send_json_error(['message' => 'API credentials not configured']);
                return;
            }

            // Get CTM form data
            $forms = $this->formImportService->getAvailableForms($apiKey, $apiSecret);
            
            if ($forms === false) {
                wp_send_json_error(['message' => 'Failed to load forms from CallTrackingMetrics']);
                return;
            }

            $ctmForm = null;
            foreach ($forms as $form) {
                if ($form['id'] == $ctmFormId) {
                    $ctmForm = $form;
                    break;
                }
            }

            if (!$ctmForm) {
                wp_send_json_error(['message' => 'CTM form not found']);
                return;
            }

            // Update based on form type
            if ($wpFormType === 'cf7') {
                $this->updateCF7Form($wpFormId, $ctmForm);
            } else {
                $this->updateGFForm($wpFormId, $ctmForm);
            }

            wp_send_json_success([
                'message' => 'Form updated successfully',
                'wp_form_id' => $wpFormId,
                'ctm_form_id' => $ctmFormId
            ]);

        } catch (\Exception $e) {
            wp_send_json_error(['message' => 'Update failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Update Contact Form 7 form with CTM content
     * 
     * @since 2.0.0
     * @param string $wpFormId WordPress form ID
     * @param array $ctmForm CTM form data
     */
    private function updateCF7Form(string $wpFormId, array $ctmForm): void
    {
        // Verify CF7 form exists
        if (!class_exists('WPCF7_ContactForm')) {
            throw new \Exception('Contact Form 7 is not installed');
        }

        $cf7Form = \WPCF7_ContactForm::get_instance($wpFormId);
        if (!$cf7Form) {
            throw new \Exception('Contact Form 7 form not found');
        }

        // Convert CTM form to CF7 format
        $cf7Content = $this->formImportService->convertToCF7Format($ctmForm);

        // Update the form content
        $cf7Form->set_properties([
            'form' => $cf7Content,
            'title' => $ctmForm['name'] ?? $cf7Form->title()
        ]);

        // Save the updated form
        $cf7Form->save();

        // Update metadata
        update_post_meta($wpFormId, '_ctm_import_date', current_time('mysql'));
        update_post_meta($wpFormId, '_ctm_sync_method', 'manual_update');
    }

    /**
     * Update Gravity Forms form with CTM content
     * 
     * @since 2.0.0
     * @param string $wpFormId WordPress form ID
     * @param array $ctmForm CTM form data
     */
    private function updateGFForm(string $wpFormId, array $ctmForm): void
    {
        // Verify GF form exists
        if (!class_exists('GFAPI')) {
            throw new \Exception('Gravity Forms is not installed');
        }

        $gfForm = \GFAPI::get_form($wpFormId);
        if (!$gfForm) {
            throw new \Exception('Gravity Forms form not found');
        }

        // Convert CTM form to GF format
        $gfFormArray = $this->formImportService->convertToGFFormat($ctmForm, $ctmForm['name'] ?? $gfForm['title']);

        // Update form properties
        $gfForm['title'] = $gfFormArray['title'];
        $gfForm['fields'] = $gfFormArray['fields'];

        // Save the updated form
        $result = \GFAPI::update_form($gfForm);
        if (is_wp_error($result)) {
            throw new \Exception('Failed to update Gravity Form: ' . $result->get_error_message());
        }

        // Update metadata
        gform_update_meta($wpFormId, '_ctm_import_date', current_time('mysql'));
        gform_update_meta($wpFormId, '_ctm_sync_method', 'manual_update');
    }

    /**
     * Generate preview for existing WordPress form
     * 
     * @since 2.0.0
     */
    public function previewWPForm(): void
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ctm_form_import_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
            return;
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
            return;
        }

        try {
            $formId = sanitize_text_field($_POST['form_id'] ?? '');
            $formType = sanitize_text_field($_POST['form_type'] ?? '');

            if (empty($formId) || empty($formType)) {
                wp_send_json_error(['message' => 'Missing required parameters']);
                return;
            }

            // Validate form type
            if (!in_array($formType, ['cf7', 'gf'])) {
                wp_send_json_error(['message' => 'Invalid form type']);
                return;
            }

            // Generate preview and raw code based on form type
            if ($formType === 'cf7') {
                $preview = $this->generateCF7WPPreview($formId);
                $rawCode = $this->generateCF7WPRawCode($formId);
            } else {
                $preview = $this->generateGFWPPreview($formId);
                $rawCode = $this->generateGFWPRawCode($formId);
            }

            wp_send_json_success([
                'preview' => $preview,
                'raw_code' => $rawCode,
                'form_id' => $formId,
                'form_type' => $formType
            ]);

        } catch (\Exception $e) {
            wp_send_json_error(['message' => 'Preview generation failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Generate raw code for existing Contact Form 7 form
     * 
     * @since 2.0.0
     * @param string $formId CF7 form ID
     * @return string Raw form code
     */
    private function generateCF7WPRawCode(string $formId): string
    {
        if (!class_exists('WPCF7_ContactForm')) {
            return 'Contact Form 7 is not available';
        }

        $form = \WPCF7_ContactForm::get_instance($formId);
        if (!$form) {
            return 'Contact Form 7 form not found';
        }

        return $form->prop('form');
    }

    /**
     * Generate preview for Contact Form 7 form
     * 
     * @since 2.0.0
     * @param string $formId CF7 form ID
     * @return string HTML preview
     */
    private function generateCF7WPPreview(string $formId): string
    {
        // Verify CF7 is available
        if (!class_exists('WPCF7_ContactForm')) {
            throw new \Exception('Contact Form 7 is not installed');
        }

        $cf7Form = \WPCF7_ContactForm::get_instance($formId);
        if (!$cf7Form) {
            throw new \Exception('Contact Form 7 form not found');
        }

        // Try to render using CF7's built-in methods
        try {
            // Use CF7's shortcode rendering
            $shortcode = '[contact-form-7 id="' . $formId . '"]';
            $preview = do_shortcode($shortcode);
            
            // If shortcode didn't work, try direct form HTML
            if (empty($preview) || $preview === $shortcode) {
                $preview = $cf7Form->form_html();
            }
            
            // If still empty, generate basic preview
            if (empty($preview)) {
                $preview = $this->generateBasicCF7Preview($cf7Form);
            }
            
        } catch (\Exception $e) {
            $preview = $this->generateBasicCF7Preview($cf7Form);
        }
        
        // Wrap in a styled container
        return '<div class="ctm-form-preview bg-gray-50 p-6 rounded-lg border">' . 
               '<div class="max-w-2xl mx-auto">' . $preview . '</div>' . 
               '</div>';
    }

    /**
     * Generate basic CF7 preview when shortcode fails
     * 
     * @since 2.0.0
     * @param \WPCF7_ContactForm $cf7Form CF7 form instance
     * @return string HTML preview
     */
    private function generateBasicCF7Preview(\WPCF7_ContactForm $cf7Form): string
    {
        $formContent = $cf7Form->prop('form');
        
        if (empty($formContent)) {
            return '<p class="text-gray-500 italic">No form content available for preview.</p>';
        }

        // Basic CF7 shortcode parsing for preview
        $html = '<form class="wpcf7-form" style="max-width: 600px;">';
        
        // Parse common CF7 shortcodes
        $formContent = preg_replace('/\[text\*?\s+([^\]]+)\]/', '<input type="text" name="$1" class="form-control mb-3" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" required>', $formContent);
        $formContent = preg_replace('/\[email\*?\s+([^\]]+)\]/', '<input type="email" name="$1" class="form-control mb-3" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" required>', $formContent);
        $formContent = preg_replace('/\[textarea\*?\s+([^\]]+)\]/', '<textarea name="$1" rows="4" class="form-control mb-3" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" required></textarea>', $formContent);
        $formContent = preg_replace('/\[submit\s+"([^"]+)"\]/', '<button type="submit" class="btn btn-primary" style="background: #0073aa; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">$1</button>', $formContent);
        
        // Convert line breaks to proper HTML
        $formContent = nl2br($formContent);
        
        $html .= $formContent;
        $html .= '</form>';
        
        return $html;
    }

    /**
     * Generate raw code for existing Gravity Forms form
     * 
     * @since 2.0.0
     * @param string $formId GF form ID
     * @return string Raw form code (human-readable format)
     */
    private function generateGFWPRawCode(string $formId): string
    {
        if (!class_exists('GFAPI')) {
            return 'Gravity Forms is not available';
        }

        $form = \GFAPI::get_form($formId);
        if (!$form) {
            return 'Gravity Forms form not found';
        }

        // Format the GF form as readable code (same format as import preview)
        $rawCode = "Gravity Forms Form Configuration:\n\n";
        $rawCode .= "Form Title: " . $form['title'] . "\n\n";
        $rawCode .= "Fields:\n";
        
        if (isset($form['fields']) && is_array($form['fields'])) {
            foreach ($form['fields'] as $field) {
                $rawCode .= "- " . $field['label'] . " (" . $field['type'] . ")\n";
                if (isset($field['choices']) && is_array($field['choices'])) {
                    foreach ($field['choices'] as $choice) {
                        $rawCode .= "  * " . $choice['text'] . "\n";
                    }
                }
            }
        }
        
        if (isset($form['notifications']) && is_array($form['notifications'])) {
            $rawCode .= "\nNotifications:\n";
            foreach ($form['notifications'] as $notification) {
                $rawCode .= "- " . $notification['name'] . " (to: " . $notification['to'] . ")\n";
            }
        }
        
        return $rawCode;
    }

    /**
     * Generate preview for Gravity Forms form
     * 
     * @since 2.0.0
     * @param string $formId GF form ID
     * @return string HTML preview
     */
    private function generateGFWPPreview(string $formId): string
    {
        // Verify GF is available
        if (!class_exists('GFAPI')) {
            throw new \Exception('Gravity Forms is not installed');
        }

        $gfForm = \GFAPI::get_form($formId);
        if (!$gfForm) {
            throw new \Exception('Gravity Forms form not found');
        }

        // Try to render using GF's built-in methods
        try {
            // Use GF's shortcode rendering
            $shortcode = '[gravityform id="' . $formId . '" title="false" description="false" ajax="false"]';
            $preview = do_shortcode($shortcode);
            
            // If shortcode didn't work, generate basic preview
            if (empty($preview) || $preview === $shortcode) {
                $preview = $this->generateBasicGFPreview($gfForm);
            }
            
        } catch (\Exception $e) {
            $preview = $this->generateBasicGFPreview($gfForm);
        }
        
        // Wrap in a styled container
        return '<div class="ctm-form-preview bg-gray-50 p-6 rounded-lg border">' . 
               '<div class="max-w-2xl mx-auto">' . $preview . '</div>' . 
               '</div>';
    }

    /**
     * Generate basic GF preview when shortcode fails
     * 
     * @since 2.0.0
     * @param array $gfForm GF form array
     * @return string HTML preview
     */
    private function generateBasicGFPreview(array $gfForm): string
    {
        try {
            if (empty($gfForm['fields'])) {
                return '<p class="text-gray-500 italic">No form fields available for preview.</p>';
            }

            $html = '<form class="gform_wrapper" style="max-width: 600px;">';
            
            foreach ($gfForm['fields'] as $field) {
            $html .= '<div class="gfield mb-4">';
            
            // Add field label
            if (!empty($field['label'])) {
                $required = !empty($field['isRequired']) ? ' <span style="color: red;">*</span>' : '';
                $html .= '<label class="gfield_label" style="display: block; font-weight: bold; margin-bottom: 5px;">' . 
                         esc_html($field['label']) . $required . '</label>';
            }
            
            // Generate field HTML based on type
            switch ($field['type']) {
                case 'text':
                case 'email':
                case 'phone':
                    $type = $field['type'] === 'phone' ? 'tel' : $field['type'];
                    $html .= '<input type="' . $type . '" class="gfield_input" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">';
                    break;
                
                case 'textarea':
                    $html .= '<textarea class="gfield_input" rows="4" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"></textarea>';
                    break;
                
                case 'select':
                    $html .= '<select class="gfield_input" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">';
                    $html .= '<option value="">Choose...</option>';
                    if (!empty($field['choices'])) {
                        foreach ($field['choices'] as $choice) {
                            $html .= '<option value="' . esc_attr($choice['value']) . '">' . esc_html($choice['text']) . '</option>';
                        }
                    }
                    $html .= '</select>';
                    break;
                
                case 'radio':
                case 'checkbox':
                    if (!empty($field['choices'])) {
                        foreach ($field['choices'] as $choice) {
                            $html .= '<label style="display: block; margin-bottom: 5px;">';
                            $html .= '<input type="' . $field['type'] . '" name="field_' . $field['id'] . '" value="' . esc_attr($choice['value']) . '" style="margin-right: 8px;">';
                            $html .= esc_html($choice['text']);
                            $html .= '</label>';
                        }
                    }
                    break;
                
                case 'fileupload':
                    $html .= '<input type="file" class="gfield_input" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">';
                    break;
                
                default:
                    $html .= '<input type="text" class="gfield_input" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">';
                    break;
            }
            
            // Add field description
            if (!empty($field['description'])) {
                $html .= '<div class="gfield_description" style="font-size: 12px; color: #666; margin-top: 5px;">' . 
                         esc_html($field['description']) . '</div>';
            }
            
            $html .= '</div>';
        }
        
        $html .= '<div class="gform_footer mt-4">';
        $html .= '<button type="submit" class="gform_button button" style="background: #0073aa; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">Submit</button>';
        $html .= '</div>';
        $html .= '</form>';
        
        return $html;
        
        } catch (\Exception $e) {
            error_log('GF Basic Preview Error: ' . $e->getMessage());
            return '<div class="notice notice-error"><p>Error generating basic GF preview: ' . esc_html($e->getMessage()) . '</p></div>';
        }
    }

    /**
     * Generate CF7 raw code
     * 
     * @since 2.0.0
     * @param array $ctmForm The CTM form data
     * @return string The raw CF7 code
     */
    private function generateCF7RawCode(array $ctmForm): string
    {
        try {
            // Convert CTM form to CF7 format using FormImportService
            $cf7Content = $this->formImportService->convertToCF7Format($ctmForm);
            
            // Return the raw CF7 shortcode content
            return $cf7Content;
            
        } catch (\Exception $e) {
            $this->logInternal('CF7 Raw Code Generation Error: ' . $e->getMessage(), 'error');
            return 'Error generating CF7 raw code: ' . $e->getMessage();
        }
    }

    /**
     * Generate GF raw code
     * 
     * @since 2.0.0
     * @param array $ctmForm The CTM form data
     * @return string The raw GF code
     */
    private function generateGFRawCode(array $ctmForm): string
    {
        try {
            // Convert CTM form to GF format using FormImportService
            $gfFormArray = $this->formImportService->convertToGFFormat($ctmForm, $ctmForm['name']);
            
            // Format the GF form array as readable code
            $rawCode = "Gravity Forms Form Configuration:\n\n";
            $rawCode .= "Form Title: " . $gfFormArray['title'] . "\n\n";
            $rawCode .= "Fields:\n";
            
            foreach ($gfFormArray['fields'] as $field) {
                $rawCode .= "- " . $field['label'] . " (" . $field['type'] . ")\n";
                if (isset($field['choices'])) {
                    foreach ($field['choices'] as $choice) {
                        $rawCode .= "  * " . $choice['text'] . "\n";
                    }
                }
            }
            
            if (isset($gfFormArray['notifications'])) {
                $rawCode .= "\nNotifications:\n";
                foreach ($gfFormArray['notifications'] as $notification) {
                    $rawCode .= "- " . $notification['name'] . " (to: " . $notification['to'] . ")\n";
                }
            }
            
            return $rawCode;
            
        } catch (\Exception $e) {
            $this->logInternal('GF Raw Code Generation Error: ' . $e->getMessage(), 'error');
            return 'Error generating GF raw code: ' . $e->getMessage();
        }
    }
}