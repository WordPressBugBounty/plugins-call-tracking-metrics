<?php
/**
 * Gravity Forms Integration Service
 * 
 * This file contains the GFService class that handles integration between
 * Gravity Forms plugin and CallTrackingMetrics, including form submission
 * processing, field mapping, and data formatting.
 * 
 * @package     CallTrackingMetrics
 * @subpackage  Service
 * @author      CallTrackingMetrics Team
 * @copyright   2024 CallTrackingMetrics
 * @license     GPL-2.0+
 * @version     2.0
 * @since       1.0.0
 */

namespace CTM\Service;

// Only import GFAPI if Gravity Forms is available
if (class_exists('GFAPI')) {
    // GFAPI is available - no need to import as it's in global namespace
}

/**
 * Gravity Forms Service Class
 * 
 * Handles all Gravity Forms related functionality including:
 * - Form submission data processing and validation
 * - Field mapping between GF fields and CTM fields
 * - Complex field type handling (file uploads, multi-part fields)
 * - Form configuration and metadata retrieval
 * - Integration with CTM API data format
 * 
 * This service acts as a bridge between Gravity Forms' complex data structure
 * and the CallTrackingMetrics API requirements, handling GF's advanced
 * field types and validation rules.
 * 
 * @since 1.0.0
 */
class GFService extends BaseFormService
{

    /**
     * Process Gravity Forms submission for CTM API
     * 
     * Takes raw GF form submission data and converts it into the format
     * expected by the CallTrackingMetrics API. Handles complex field types,
     * data validation, and metadata extraction.
     * 
     * @since 1.0.0
     * @param array $entry The GF entry data containing submitted values
     * @param array $form  The GF form configuration array
     * @return array|null Formatted data for CTM API or null on failure
     */
    public function processSubmission(array $entry, array $form): ?array
    {
        // Optimized Gravity Forms submission processing for CTM API

        // Validate that Gravity Forms is available and data is valid
        if (!class_exists('GFAPI') || empty($entry) || empty($form)) {
            return null;
        }
        
        try {

            // Build a mapping of entry keys to field labels and types
            $fieldMapping = [];
            $fieldTypes = [];
            if (!empty($form['fields']) && is_array($form['fields'])) {
                foreach ($form['fields'] as $field) {
                    if (!is_object($field) || !isset($field->id)) {
                        continue;
                    }
                    $fieldId = (string)$field->id;
                    $fieldLabel = $this->getFieldLabel($field, 'Field ' . $fieldId);
                    $fieldType = $field->type ?? 'text';
                    
                    $fieldMapping[$fieldId] = $fieldLabel;
                    $fieldTypes[$fieldId] = $fieldType;
                    
                    // Handle multi-input fields (like name, address)
                    if (!empty($field->inputs) && is_array($field->inputs)) {
                        foreach ($field->inputs as $input) {
                            if (!is_array($input) || !isset($input['id'])) {
                                continue;
                            }
                            $inputId = (string)$input['id'];
                            $inputLabel = trim((string)($input['label'] ?? ''));
                            if ($inputLabel === '') {
                                $inputLabel = $fieldLabel . ' ' . $inputId;
                            }
                            $fieldMapping[$inputId] = $inputLabel;
                            $fieldTypes[$inputId] = $fieldType;
                        }
                    }
                }
            }

            // Helper function to slugify keys
            $slugify = function($str) {
                $str = strtolower($str);
                $str = preg_replace('/[^a-z0-9]+/', '_', $str);
                $str = trim($str, '_');
                return $str;
            };

            // Map entry data using field labels as keys
            $fieldsWithLabels = [];
            foreach ($entry as $key => $value) {
                // Skip Gravity Forms internal fields
                if (in_array($key, ['id', 'status', 'form_id', 'ip', 'source_url', 'currency', 'post_id', 
                                   'date_created', 'date_updated', 'is_starred', 'is_read', 'user_agent', 
                                   'payment_status', 'payment_date', 'payment_amount', 'payment_method', 
                                   'transaction_id', 'is_fulfilled', 'created_by', 'transaction_type', 'source_id'])) {
                    continue;
                }
                
                $fieldLabel = $fieldMapping[(string)$key] ?? ('Field ' . $key);
                $slug = $slugify($fieldLabel);
                $fieldsWithLabels[$slug] = $value;
            }

            // Merge address fields into a single string
            $addressParts = [
                'Street Address', 'Address Line 2', 'City', 'State / Province', 'ZIP / Postal Code', 'Country'
            ];
            $addressValues = [];
            foreach ($addressParts as $part) {
                if (!empty($fieldsWithLabels[$slugify($part)])) {
                    $addressValues[] = $fieldsWithLabels[$slugify($part)];
                }
            }
            if ($addressValues) {
                $fieldsWithLabels['address'] = implode(', ', $addressValues);
            }

            // Merge name fields into a single string
            $nameParts = ['Prefix', 'First', 'Middle', 'Last', 'Suffix'];
            $nameValues = [];
            foreach ($nameParts as $part) {
                if (!empty($fieldsWithLabels[$slugify($part)])) {
                    $nameValues[] = $fieldsWithLabels[$slugify($part)];
                }
            }
            if ($nameValues) {
                $fieldsWithLabels['name'] = implode(' ', $nameValues);
            }

            // Extract form_reactor fields with proper field detection.
            // Keep legacy behavior from 1.2.16: default US country code for standard GF phone fields.
            $formReactor = [
                'country_code' => ''
            ];
            
            // Find phone number field (prefer GF field type, fallback to label match)
            $phoneNumber = '';
            if (!empty($form['fields']) && is_array($form['fields'])) {
                foreach ($form['fields'] as $field) {
                    if (!is_object($field) || !isset($field->id)) {
                        continue;
                    }
                    $fieldType = $field->type ?? '';
                    if ($fieldType === 'phone') {
                        $rawValue = $entry[$field->id] ?? '';
                        $phoneNumber = $this->sanitizeFieldValue($rawValue, 'phone');
                        if (($field->phoneFormat ?? '') === 'standard') {
                            $formReactor['country_code'] = '1';
                        }
                        if (!empty($phoneNumber)) {
                            break;
                        }
                    }
                }
            }
            if (empty($phoneNumber)) {
                foreach ($fieldsWithLabels as $fieldName => $value) {
                    $fieldNameLower = strtolower($fieldName);
                    if (strpos($fieldNameLower, 'phone') !== false || 
                        strpos($fieldNameLower, 'tel') !== false) {
                        $phoneNumber = $value;
                        break;
                    }
                }
            }
            if (!empty($phoneNumber)) {
                $formReactor['phone_number'] = $phoneNumber;
            }
            
            // Find name field
            $callerName = '';
            // First check specifically for the 'name' key we generated earlier
            if (!empty($fieldsWithLabels['name'])) {
                $callerName = $fieldsWithLabels['name'];
            } else {
                // Fallback to searching keys
                foreach ($fieldsWithLabels as $fieldName => $value) {
                    $fieldNameLower = strtolower($fieldName);
                    if (strpos($fieldNameLower, 'name') !== false && 
                        (strpos($fieldNameLower, 'first') !== false || 
                         strpos($fieldNameLower, 'last') !== false || 
                         strpos($fieldNameLower, 'full') !== false)) {
                        $callerName = $value;
                        break;
                    }
                }
            }
            if (!empty($callerName)) {
                $formReactor['caller_name'] = $callerName;
            }
            
            // Find email field. Prefer the GF field type because labels can be blank
            // when a form uses placeholders as its visible field names.
            $emailField = '';
            if (!empty($form['fields']) && is_array($form['fields'])) {
                foreach ($form['fields'] as $field) {
                    if (!is_object($field) || !isset($field->id) || ($field->type ?? '') !== 'email') {
                        continue;
                    }

                    $emailField = $this->sanitizeFieldValue($entry[$field->id] ?? '', 'email');
                    if ($emailField !== '') {
                        break;
                    }
                }
            }
            if ($emailField === '') {
                foreach ($fieldsWithLabels as $fieldName => $value) {
                    if (strpos(strtolower($fieldName), 'email') !== false) {
                        $emailField = $this->sanitizeFieldValue($value, 'email');
                        if ($emailField !== '') {
                            break;
                        }
                    }
                }
            }
            if ($emailField !== '') {
                $formReactor['email'] = $emailField;
            }

            // Restore legacy field mapping (field_{id}) for backward compatibility
            $legacyFields = [];
            $legacyLabels = [];
            
            // Track seen types to avoid duplicating primary contact fields in custom fields list
            $seenTypes = ['name' => false, 'phone' => false, 'email' => false];
            
            if (!empty($form['fields']) && is_array($form['fields'])) {
                foreach ($form['fields'] as $field) {
                    if (!is_object($field) || !isset($field->id)) {
                        continue;
                    }
                    
                    $fieldId = $field->id;
                    
                    // Skip admin/internal fields
                    $fieldLabel = $this->getFieldLabel($field, 'Field ' . $fieldId);
                    if ($this->isAdminField($field->type, $fieldLabel)) {
                        continue;
                    }
                    
                    // Skip primary contact fields to avoid duplication in custom fields
                    if (in_array($field->type, ['name', 'phone', 'email'])) {
                        if (!$seenTypes[$field->type]) {
                            $seenTypes[$field->type] = true;
                            continue; // Skip the first one (primary)
                        }
                    }
                    
                    // Determine value
                    $cleanValue = null;
                    
                    // Handle specific complex types
                    if ($field->type === 'name' && is_array($field->inputs)) {
                        $nameParts = [];
                        foreach ($field->inputs as $input) {
                            if (!empty($entry[$input['id']])) {
                                $nameParts[] = $entry[$input['id']];
                            }
                        }
                        if (!empty($nameParts)) {
                            $cleanValue = implode(' ', $nameParts);
                        }
                    } elseif ($field->type === 'address' && is_array($field->inputs)) {
                        $addrParts = [];
                        foreach ($field->inputs as $input) {
                            if (!empty($entry[$input['id']])) {
                                $addrParts[] = $entry[$input['id']];
                            }
                        }
                        if (!empty($addrParts)) {
                            $cleanValue = implode(', ', $addrParts);
                        }
                    } else {
                        // Standard fields
                        if (isset($entry[$fieldId])) {
                            $cleanValue = $this->sanitizeFieldValue($entry[$fieldId], $field->type ?? 'text');
                        }
                    }
                    
                    if (!empty($cleanValue)) {
                        $legacyKey = 'field_' . $fieldId;
                        $legacyFields[$legacyKey] = $cleanValue;
                        $legacyLabels[$legacyKey] = $fieldLabel;
                    }
                }
            }

            // Extract basic form information
            $formId = $form['id'];
            $formTitle = $form['title'] ?? '';
            // Build the payload to match 1.2.16 legacy format
            $payload = [
                'form_reactor'      => $formReactor,
                'field'             => $legacyFields,
                'label'             => $legacyLabels,
                'labels'            => [],
                'type'              => 'Gravity Forms',
                'id'                => (string)$formId,
                'name'              => $formTitle,
                '__ctm_api_authorized__' => '1',
                'visitor_sid'       => $_COOKIE['__ctmid'] ?? '',
                'domain'            => $_SERVER['HTTP_HOST'] ?? '',
            ];

            return $this->addPluginMetadataToPayload($payload);
        } catch (\Exception $e) {
            // Log error but don't break the form submission
            $this->logInternal('GF Processing Error: ' . $e->getMessage(), 'error');
            return null;
        }
    }

    /**
     * Get all Gravity Forms available on the site
     * 
     * Retrieves a list of all GF forms with their IDs, titles, and status.
     * Used for form mapping configuration in the admin interface.
     * 
     * @since 1.0.0
     * @return array Array of form objects with id, title, and status properties
     */
    public function getForms(): array
    {
        // Check if Gravity Forms is available
        if (!class_exists('GFAPI')) {
            return [];
        }

        $forms = [];
        
        try {
            // Get all GF forms using the API
            if (method_exists('\GFAPI', 'get_forms')) {
                $gf_forms = \GFAPI::get_forms();
                
                // Format forms for our use
                foreach ($gf_forms as $form) {
                    $forms[] = [
                        'id' => $form['id'],
                        'title' => $form['title'] ?? '',
                        'status' => isset($form['is_active']) ? ($form['is_active'] ? 'active' : 'inactive') : 'inactive',
                        // 'field_count' => count($form['fields']),
                        'entries_count' => method_exists('\GFAPI', 'count_entries') ? \GFAPI::count_entries($form['id']) : 0,
                    ];
                }
            }
            
        } catch (\Exception $e) {
            $this->logInternal('GF getForms Error: ' . $e->getMessage(), 'error');
        }
        
        return $forms;
    }

    /**
     * Get form fields for a specific Gravity Forms form
     * 
     * Extracts all available fields from a GF form including their
     * types, IDs, labels, and properties for mapping configuration.
     * 
     * @since 1.0.0
     * @param int $formId The form ID to get fields for
     * @return array Array of field information
     */
    public function getFormFields(int $formId): array
    {
        // Check if Gravity Forms is available
        if (!class_exists('GFAPI')) {
            return [];
        }

        $fields = [];
        
        try {
            // Get form configuration from GF API
            if (method_exists('\GFAPI', 'get_form')) {
                $form = \GFAPI::get_form($formId);
            } else {
                return [];
            }
            
            if (!$form || !isset($form['fields'])) {
                return [];
            }
            
            // Process each field in the form
            foreach ($form['fields'] as $field) {
                // Skip page breaks and section breaks
                if (in_array($field->type, ['page', 'section', 'html'])) {
                    continue;
                }
                
                // Handle multi-part fields (name, address, etc.)
                if ($this->isMultiPartField($field)) {
                    $subFields = $this->getSubFields($field);
                    $fields = array_merge($fields, $subFields);
                } else {
                    // Single field
                    $fields[] = [
                        'id' => $field->id,
                        'name' => $this->getFieldName($field),
                        'type' => $this->normalizeFieldType($field->type),
                        'label' => $field->label,
                        'required' => $field->isRequired ?? false,
                        'gf_type' => $field->type, // Keep original GF type
                        'choices' => $this->getFieldChoices($field),
                    ];
                }
            }
            
        } catch (\Exception $e) {
            $this->logInternal('GF getFormFields Error: ' . $e->getMessage(), 'error');
        }
        
        return $fields;
    }

    /**
     * Map form fields according to configured mapping
     * 
     * Transforms GF field data into CTM-compatible format using
     * the configured field mapping rules. Handles complex GF field types.
     * 
     * @since 1.0.0
     * @param array $entry        The GF entry data
     * @param array $form         The GF form configuration
     * @param array $fieldMapping The configured field mapping
     * @return array Mapped field data for CTM API
     */
    private function mapFormFields(array $entry, array $form, array $fieldMapping): array
    {
        $mappedFields = [];
        
        // Check if form has fields and they are properly structured
        if (empty($form['fields']) || !is_array($form['fields'])) {
            $this->logDebug("Form has no fields or fields are not properly structured");
            return $mappedFields;
        }
        
        foreach ($form['fields'] as $field) {
            // Skip if field is not properly structured
            if (!is_object($field) || !isset($field->id)) {
                continue;
            }
            
            $fieldId = $field->id;
            $fieldValue = $entry[$fieldId] ?? null;
            if ($this->isAdminField($field->type, $field->label ?? '')) {
                continue;
            }
            // Skip conditional fields not present in entry
            if (!array_key_exists($fieldId, $entry)) {
                $this->logDebug("Field {$fieldId} skipped (conditional or not present in entry)");
                continue;
            }
            // File Uploads (already handled)
            if ($this->normalizeFieldType($field->type) === 'file') {
                if (!empty($fieldValue)) {
                    // Gravity Forms stores file uploads as a string (single) or serialized array (multiple)
                    $urls = is_array($fieldValue) ? $fieldValue : (is_serialized($fieldValue) ? unserialize($fieldValue) : [$fieldValue]);
                    $urls = array_filter((array)$urls, function($url) { return filter_var($url, FILTER_VALIDATE_URL); });
                    if (!empty($urls)) {
                        $ctmFieldName = $fieldMapping[$fieldId] ?? $this->getFieldName($field);
                        $mappedFields[$ctmFieldName] = count($urls) === 1 ? reset($urls) : array_values($urls);
                    }
                }
                continue;
            }
            // Address Fields (already handled)
            if ($this->normalizeFieldType($field->type) === 'address' && isset($field->inputs)) {
                $address = [];
                foreach ($field->inputs as $input) {
                    $inputId = $input['id'];
                    $inputValue = $entry[$inputId] ?? '';
                    if (!empty($inputValue)) {
                        $part = $input['label'] ?? $inputId;
                        $address[$part] = $this->sanitizeFieldValue($inputValue, $field->type ?? 'text');
                    }
                }
                if (!empty($address)) {
                    $ctmFieldName = $fieldMapping[$fieldId] ?? $this->getFieldName($field);
                    $mappedFields[$ctmFieldName] = $address;
                }
                continue;
            }
            // Checkboxes and Lists: send as arrays if possible
            if (in_array($this->normalizeFieldType($field->type), ['checkbox', 'list'])) {
                if (!empty($fieldValue)) {
                    $ctmFieldName = $fieldMapping[$fieldId] ?? $this->getFieldName($field);
                    $arrayValue = is_array($fieldValue) ? $fieldValue : (is_serialized($fieldValue) ? unserialize($fieldValue) : explode(',', $fieldValue));
                    $arrayValue = array_filter((array)$arrayValue);
                    $mappedFields[$ctmFieldName] = $arrayValue;
                }
                continue;
            }
            // Unsupported field types: skip and log
            $supportedTypes = ['text','textarea','select','multiselect','number','phone','email','url','date','time','file','radio','checkbox','name','address','hidden','list','post_title','post_content','post_excerpt'];
            if (!in_array($this->normalizeFieldType($field->type), $supportedTypes)) {
                $this->logDebug("Field {$fieldId} ({$field->type}) skipped (unsupported type)");
                continue;
            }
            // Single field processing
            $fieldName = $this->getFieldName($field);
            $ctmFieldName = $fieldMapping[$fieldId] ?? $fieldName;
            $cleanValue = $this->sanitizeFieldValue($fieldValue, $field->type ?? 'text');
            if (!empty($cleanValue)) {
                $mappedFields[$ctmFieldName] = $cleanValue;
            }
        }
        return $mappedFields;
    }

    /**
     * Check if a field is a multi-part field (name, address, etc.)
     * 
     * @since 1.0.0
     * @param object $field The GF field object
     * @return bool True if field has multiple parts
     */
    private function isMultiPartField($field): bool
    {
        return in_array($field->type, ['name', 'address', 'time', 'date']);
    }

    /**
     * Check if a field is an administrative field that shouldn't be sent to CTM
     * 
     * @since 1.0.0
     * @param string $fieldType The field type
     * @param string $fieldLabel The field label
     * @return bool True if field is administrative
     */
    protected function isAdminField(string $fieldType, string $fieldLabel = ''): bool
    {
        $adminTypes = ['page', 'section', 'html', 'captcha', 'password', 'hidden', 'honeypot', 'submit', 'button'];
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
     * Get sub-fields for multi-part fields
     * 
     * @since 1.0.0
     * @param object $field The GF field object
     * @return array Array of sub-field information
     */
    private function getSubFields($field): array
    {
        $subFields = [];
        
        // Handle different multi-part field types
        switch ($field->type) {
            case 'name':
                $nameParts = ['prefix', 'first', 'middle', 'last', 'suffix'];
                foreach ($nameParts as $part) {
                    if (isset($field->inputs)) {
                        foreach ($field->inputs as $input) {
                            if (strpos($input['id'], ".{$part}") !== false) {
                                $subFields[] = [
                                    'id' => $input['id'],
                                    'name' => $field->label . ' - ' . ucfirst($part),
                                    'type' => 'text',
                                    'label' => $input['label'] ?? ucfirst($part),
                                    'required' => $field->isRequired ?? false,
                                    'gf_type' => 'name_part',
                                ];
                            }
                        }
                    }
                }
                break;
                
            case 'address':
                $addressParts = ['street', 'street2', 'city', 'state', 'zip', 'country'];
                foreach ($addressParts as $part) {
                    if (isset($field->inputs)) {
                        foreach ($field->inputs as $input) {
                            if (strpos($input['id'], ".{$part}") !== false) {
                                $subFields[] = [
                                    'id' => $input['id'],
                                    'name' => $field->label . ' - ' . ucfirst($part),
                                    'type' => 'text',
                                    'label' => $input['label'] ?? ucfirst($part),
                                    'required' => $field->isRequired ?? false,
                                    'gf_type' => 'address_part',
                                ];
                            }
                        }
                    }
                }
                break;
        }
        
        return $subFields;
    }

    /**
     * Process multi-part field data
     * 
     * @since 1.0.0
     * @param object $field        The GF field object
     * @param array  $entry        The entry data
     * @param array  $fieldMapping The field mapping configuration
     * @return array Processed sub-field data
     */
    private function processMultiPartField($field, array $entry, array $fieldMapping): array
    {
        $subFieldData = [];
        
        if (isset($field->inputs)) {
            foreach ($field->inputs as $input) {
                $inputId = $input['id'];
                $inputValue = $entry[$inputId] ?? '';
                
                if (!empty($inputValue)) {
                    $inputName = $input['label'] ?? "Field {$inputId}";
                    $ctmFieldName = $fieldMapping[$inputId] ?? $inputName;
                    $cleanValue = $this->sanitizeFieldValue($inputValue, $field->type ?? 'text');
                    
                    $subFieldData[$ctmFieldName] = $cleanValue;
                }
            }
        }
        
        return $subFieldData;
    }

    /**
     * Get a readable field name for a GF field
     * 
     * @since 1.0.0
     * @param object $field The GF field object
     * @return string The field name
     */
    private function getFieldName($field): string
    {
        return $this->getFieldLabel($field, "Field {$field->id}");
    }

    /**
     * Get a usable Gravity Forms field name when labels are intentionally blank.
     *
     * Forms that display placeholder text instead of visible labels can provide
     * blank field labels to the submission hook. Prefer the display label and
     * placeholder, then use an admin label when no visible metadata exists.
     *
     * @param object $field    Gravity Forms field object
     * @param string $fallback Value to use when the field has no usable metadata
     * @return string
     */
    private function getFieldLabel(object $field, string $fallback): string
    {
        foreach (['label', 'placeholder', 'adminLabel'] as $property) {
            if (!isset($field->{$property})) {
                continue;
            }

            $value = trim((string)$field->{$property});
            if ($value !== '') {
                return $value;
            }
        }

        return $fallback;
    }

    /**
     * Get field choices for select/radio/checkbox fields
     * 
     * @since 1.0.0
     * @param object $field The GF field object
     * @return array Array of field choices
     */
    private function getFieldChoices($field): array
    {
        if (!isset($field->choices) || !is_array($field->choices)) {
            return [];
        }
        
        $choices = [];
        foreach ($field->choices as $choice) {
            $choices[] = [
                'text' => $choice['text'] ?? '',
                'value' => $choice['value'] ?? '',
            ];
        }
        
        return $choices;
    }

    /**
     * Normalize GF field types to standard types
     * 
     * Converts Gravity Forms specific field types to more generic
     * field types that are easier to work with.
     * 
     * @since 1.0.0
     * @param string $gfType The original GF field type
     * @return string The normalized field type
     */
    protected function normalizeFieldType(string $gfType): string
    {
        $typeMap = [
            'text' => 'text',
            'textarea' => 'textarea',
            'select' => 'select',
            'multiselect' => 'multiselect',
            'number' => 'number',
            'phone' => 'phone',
            'email' => 'email',
            'website' => 'url',
            'date' => 'date',
            'time' => 'time',
            'fileupload' => 'file',
            'radio' => 'radio',
            'checkbox' => 'checkbox',
            'name' => 'name',
            'address' => 'address',
            'hidden' => 'hidden',
            'list' => 'list',
            'post_title' => 'text',
            'post_content' => 'textarea',
            'post_excerpt' => 'textarea',
        ];
        
        return $typeMap[$gfType] ?? 'text';
    }

    /**
     * Sanitize and clean field values based on field type
     * 
     * Cleans form field values by removing unwanted characters,
     * handling arrays, and ensuring data safety based on field type.
     * 
     * @since 1.0.0
     * @param mixed  $value The raw field value
     * @param string $fieldType The type of field (email, phone, url, etc.)
     * @return string The sanitized field value
     */
    protected function sanitizeFieldValue($value, string $fieldType = 'text'): string
    {
        // Handle array values (checkboxes, multi-select, list fields)
        if (is_array($value)) {
            // Filter out empty values and join with commas
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
                $value = esc_url_raw($value);
                break;
            case 'phone':
                // Remove non-numeric characters except + and spaces
                $value = preg_replace('/[^0-9+\s\-\(\)]/', '', $value);
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
     * Check if a form has a phone number field
     * 
     * @since 2.0.0
     * @param array $form The GF form configuration
     * @return bool True if form has a phone field
     */
    public function hasPhoneField(array $form): bool
    {
        if (empty($form['fields'])) {
            return false;
        }

        foreach ($form['fields'] as $field) {
            $fieldType = $field->type ?? '';
            $fieldLabel = strtolower($field->label ?? '');
            $fieldName = strtolower($field->inputName ?? '');
            
            // Check for phone field type
            if ($fieldType === 'phone') {
                return true;
            }
            
            // Check for phone-related field names and labels
            $phoneKeywords = ['phone', 'telephone', 'tel', 'mobile', 'cell', 'number'];
            foreach ($phoneKeywords as $keyword) {
                if (strpos($fieldLabel, $keyword) !== false || strpos($fieldName, $keyword) !== false) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Get phone field information for a form
     * 
     * @since 2.0.0
     * @param array $form The GF form configuration
     * @return array|null Phone field info or null if not found
     */
    public function getPhoneField(array $form): ?array
    {
        if (empty($form['fields'])) {
            return null;
        }

        foreach ($form['fields'] as $field) {
            $fieldType = $field->type ?? '';
            $fieldLabel = strtolower($field->label ?? '');
            $fieldName = strtolower($field->inputName ?? '');
            
            // Check for phone field type
            if ($fieldType === 'phone') {
                return [
                    'id' => $field->id,
                    'label' => $field->label,
                    'type' => $field->type,
                    'name' => $field->inputName
                ];
            }
            
            // Check for phone-related field names and labels
            $phoneKeywords = ['phone', 'telephone', 'tel', 'mobile', 'cell', 'number'];
            foreach ($phoneKeywords as $keyword) {
                if (strpos($fieldLabel, $keyword) !== false || strpos($fieldName, $keyword) !== false) {
                    return [
                        'id' => $field->id,
                        'label' => $field->label,
                        'type' => $field->type,
                        'name' => $field->inputName
                    ];
                }
            }
        }
        
        return null;
    }

    // Add debug logging helper
    private function logDebug($msg) {
        $this->logInternal($msg, 'debug');
    }
}
