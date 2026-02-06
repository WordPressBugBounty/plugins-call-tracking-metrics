<?php
/**
 * Documentation Tab View
 * 
 * This view file displays the documentation tab in the CallTrackingMetrics admin interface, providing user guides, API documentation, and help resources.
 * 
 * @package     CallTrackingMetrics
 * @subpackage  Views
 * @author      CallTrackingMetrics Team
 * @copyright   2024 CallTrackingMetrics
 * @license     GPL-2.0+
 * @version     2.0.0
 * @link        https://calltrackingmetrics.com
 * @since       1.0.0
 */

// Documentation tab view with main tabs and subtabs
?>
<div class="mb-12">
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-8 mb-8">
        <div class="flex items-center mb-6 border-b border-[#02bdf6]">
            <svg class="w-4 h-4 mr-2 text-[#02bdf6]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
            </svg>
            <h2 class="text-2xl font-bold text-[#16294f] tracking-tight font-brand-heading"><?php _e('Documentation', 'call-tracking-metrics'); ?></h2>
        </div>
        <div id="main-doc-content">
                <!-- General documentation content -->
                <div class="space-y-8">
                    <!-- Installation Section -->
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-6 rounded-lg border border-blue-200">
                        <div class="flex items-center mb-4">
                            <div class="bg-blue-100 p-2 rounded-lg mr-3">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-blue-700"><?php _e('Quick Installation', 'call-tracking-metrics'); ?></h3>
                        </div>
                        <ol class="list-decimal pl-6 mb-4 space-y-2">
                            <li class="flex items-start">
                                <span class="mr-2">📦</span>
                                <?php _e('Download and install the plugin from the WordPress plugin directory or upload it manually.', 'call-tracking-metrics'); ?>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2">⚡</span>
                                <?php _e('Activate the plugin in your WordPress admin.', 'call-tracking-metrics'); ?>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2">⚙️</span>
                                <span class="whitespace-nowrap">
                                    <?php _e('Go to', 'call-tracking-metrics'); ?> 
                                     <b> Settings &gt; CallTrackingMetrics</b> 
                                    <?php _e('and enter your API credentials to connect.', 'call-tracking-metrics'); ?>
                                </span>
                            </li>
                        </ol>
                    </div>

                    <!-- API Key Management Section -->
                    <div class="bg-gradient-to-r from-green-50 to-emerald-50 p-6 rounded-lg border border-green-200">
                        <div class="flex items-center mb-4">
                            <div class="bg-green-100 p-2 rounded-lg mr-3">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-green-700"><?php _e('API Key Management', 'call-tracking-metrics'); ?></h3>
                        </div>
                        <ul class="list-disc pl-6 mb-4 space-y-2">
                            <li class="flex items-start">
                                <span class="mr-2">🌐</span>
                                <span class="whitespace-nowrap">
                                    <?php _e('Log in to your', 'call-tracking-metrics'); ?> <a href="https://app.calltrackingmetrics.com/accounts/edit#account-api" class="text-green-600 underline font-medium" target="_blank"><?php _e('CallTrackingMetrics account', 'call-tracking-metrics'); ?></a>.
                                </span>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2">🔧</span>
                                <span class="whitespace-nowrap">
                                    <?php _e('Navigate to', 'call-tracking-metrics'); ?>
                                    <b>Account Settings &gt; API Keys</b>
                                    .
                                </span>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2">📋</span>
                                <span class="whitespace-nowrap">
                                    <?php _e('Copy your', 'call-tracking-metrics'); ?>
                                    <b>API Key</b>
                                    <?php _e('and', 'call-tracking-metrics'); ?>
                                    <b>API Secret</b>
                                    <?php _e('and enter them in the plugin settings.', 'call-tracking-metrics'); ?>
                                </span>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2">💾</span>
                                <span class="whitespace-nowrap">
                                    <?php _e('Click', 'call-tracking-metrics'); ?>
                                    <b>Save Settings</b>
                                    <?php _e('to connect.', 'call-tracking-metrics'); ?>
                                </span>
                            </li>
                        </ul>
                    </div>

                    <!-- Integration Details Section -->
                    <div class="bg-gradient-to-r from-purple-50 to-pink-50 p-6 rounded-lg border border-purple-200">
                        <div class="flex items-center mb-4">
                            <div class="bg-purple-100 p-2 rounded-lg mr-3">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-purple-700"><?php _e('Integration Details', 'call-tracking-metrics'); ?></h3>
                        </div>
                        <p class="mb-4"><?php _e('This plugin integrates with Contact Form 7 and Gravity Forms to automatically submit form data to CallTrackingMetrics FormReactor.', 'call-tracking-metrics'); ?></p>
                        <ul class="list-disc pl-6 mb-4 space-y-2">
                            <li class="flex items-start">
                                <span class="mr-2">📞</span>
                                <?php _e('Forms must include a telephone number field (<code>input type="tel"</code>) to work properly.', 'call-tracking-metrics'); ?>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2">⚙️</span>
                                <?php _e('Enable the integration for your preferred form plugin in the settings.', 'call-tracking-metrics'); ?>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2">📊</span>
                                <?php _e('Form submissions will automatically appear in your CallTrackingMetrics dashboard.', 'call-tracking-metrics'); ?>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2">🔄</span>
                                <?php _e('Forms can be imported to multiple targets simultaneously (Contact Form 7 and Gravity Forms).', 'call-tracking-metrics'); ?>
                            </li>
                        </ul>
                    </div>

                    <!-- Troubleshooting Section -->
                    <div class="bg-gradient-to-r from-red-50 to-pink-50 p-6 rounded-lg border border-red-200">
                        <div class="flex items-center mb-4">
                            <div class="bg-red-100 p-2 rounded-lg mr-3">
                                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.08 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-red-700"><?php _e('Troubleshooting', 'call-tracking-metrics'); ?></h3>
                        </div>
                        <ul class="list-disc pl-6 mb-4 space-y-2">
                            <li class="flex items-start">
                                <span class="mr-2">🔑</span>
                                <strong><?php _e('API Connection Issues:', 'call-tracking-metrics'); ?></strong> <?php _e('Verify your API key and secret are correct and have proper permissions.', 'call-tracking-metrics'); ?>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2">📞</span>
                                <strong><?php _e('Form Integration Not Working:', 'call-tracking-metrics'); ?></strong> <?php _e('Ensure your form has a telephone number field and the integration is enabled.', 'call-tracking-metrics'); ?>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2">🌐</span>
                                <strong><?php _e('Tracking Script Issues:', 'call-tracking-metrics'); ?></strong> <?php _e('Check that the tracking script is properly loaded in your site\'s head section.', 'call-tracking-metrics'); ?>
                            </li>
                        </ul>
                    </div>

                    <!-- FAQ Section -->
                    <div class="bg-gradient-to-r from-teal-50 to-cyan-50 p-6 rounded-lg border border-teal-200">
                        <div class="flex items-center mb-4">
                            <div class="bg-teal-100 p-2 rounded-lg mr-3">
                                <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-teal-700"><?php _e('FAQ', 'call-tracking-metrics'); ?></h3>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <h4 class="font-semibold text-teal-800 mb-2"><?php _e('Q: How do I get my API credentials?', 'call-tracking-metrics'); ?></h4>
                                <p class="text-sm text-gray-700"><?php _e('A: Log into your CallTrackingMetrics account and go to Account Settings > API Keys to find your API Key and Secret.', 'call-tracking-metrics'); ?></p>
                            </div>
                            <div>
                                <h4 class="font-semibold text-teal-800 mb-2"><?php _e('Q: Which form plugins are supported?', 'call-tracking-metrics'); ?></h4>
                                <p class="text-sm text-gray-700"><?php _e('A: Contact Form 7 and Gravity Forms are currently supported. Both require a telephone number field in your forms.', 'call-tracking-metrics'); ?></p>
                            </div>
                            <div>
                                <h4 class="font-semibold text-teal-800 mb-2"><?php _e('Q: Can I map custom fields?', 'call-tracking-metrics'); ?></h4>
                                <p class="text-sm text-gray-700"><?php _e('A: Custom field mapping is not supported in this version. Form data is automatically submitted to FormReactor.', 'call-tracking-metrics'); ?></p>
                            </div>
                            <div>
                                <h4 class="font-semibold text-teal-800 mb-2"><?php _e('Q: How do I enable debugging?', 'call-tracking-metrics'); ?></h4>
                                <p class="text-sm text-gray-700"><?php _e('A: Check the "Enable Debugging" option in the General settings to access the Debug tab for troubleshooting.', 'call-tracking-metrics'); ?></p>
                            </div>
                            <div>
                                <h4 class="font-semibold text-teal-800 mb-2"><?php _e('Q: How do I test my API connection?', 'call-tracking-metrics'); ?></h4>
                                <p class="text-sm text-gray-700"><?php _e('A: Go to the API tab and click "Test Connection" to manually test your API credentials and view detailed results.', 'call-tracking-metrics'); ?></p>
                            </div>
                            <div>
                                <h4 class="font-semibold text-teal-800 mb-2"><?php _e('Q: How do I manage log files?', 'call-tracking-metrics'); ?></h4>
                                <p class="text-sm text-gray-700"><?php _e('A: In the Debug tab, use the Log Settings section to configure retention periods, auto-cleanup, and view daily logs.', 'call-tracking-metrics'); ?></p>
                            </div>
                            <div>
                                <h4 class="font-semibold text-teal-800 mb-2"><?php _e('Q: Can I import the same form to multiple targets?', 'call-tracking-metrics'); ?></h4>
                                <p class="text-sm text-gray-700"><?php _e('A: Yes! You can import the same CallTrackingMetrics form to both Contact Form 7 and Gravity Forms. The system will track all imports and show you which targets each form has been imported to.', 'call-tracking-metrics'); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- API Testing Guide Section -->
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-6 rounded-lg border border-blue-200">
                        <div class="flex items-center mb-4">
                            <div class="bg-blue-100 p-2 rounded-lg mr-3">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-blue-700"><?php _e('API Testing Guide', 'call-tracking-metrics'); ?></h3>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <h4 class="font-semibold text-blue-800 mb-2">🔍 <?php _e('Testing Your Connection', 'call-tracking-metrics'); ?></h4>
                                <ol class="list-decimal pl-6 text-sm text-gray-700 space-y-1">
                                    <li><?php _e('Navigate to the API tab in the plugin settings', 'call-tracking-metrics'); ?></li>
                                    <li><?php _e('Click the "Test Connection" button to verify your API credentials', 'call-tracking-metrics'); ?></li>
                                    <li><?php _e('Review the test results showing account information and performance metrics', 'call-tracking-metrics'); ?></li>
                                    <li><?php _e('Check the Technical Details section for connection quality and response times', 'call-tracking-metrics'); ?></li>
                                </ol>
                            </div>
                            <div>
                                <h4 class="font-semibold text-blue-800 mb-2">📊 <?php _e('Understanding Test Results', 'call-tracking-metrics'); ?></h4>
                                <ul class="list-disc pl-6 text-sm text-gray-700 space-y-1">
                                    <li><?php _e('Account Summary: Shows your account name, ID, and API access permissions', 'call-tracking-metrics'); ?></li>
                                    <li><?php _e('Performance Metrics: Displays response times and network overhead', 'call-tracking-metrics'); ?></li>
                                    <li><?php _e('Connection Quality: Indicates overall connection health (Good/Fair/Poor)', 'call-tracking-metrics'); ?></li>
                                    <li><?php _e('Technical Details: Shows request metadata and API endpoints used', 'call-tracking-metrics'); ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Debug & Logging Guide Section -->
                    <div class="bg-gradient-to-r from-purple-50 to-pink-50 p-6 rounded-lg border border-purple-200">
                        <div class="flex items-center mb-4">
                            <div class="bg-purple-100 p-2 rounded-lg mr-3">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-purple-700"><?php _e('Debug & Logging Guide', 'call-tracking-metrics'); ?></h3>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <h4 class="font-semibold text-purple-800 mb-2">🔧 <?php _e('Enabling Debug Mode', 'call-tracking-metrics'); ?></h4>
                                <ol class="list-decimal pl-6 text-sm text-gray-700 space-y-1">
                                    <li><?php _e('Go to the General tab in plugin settings', 'call-tracking-metrics'); ?></li>
                                    <li><?php _e('Check the "Enable Debugging" option', 'call-tracking-metrics'); ?></li>
                                    <li><?php _e('Save settings to access the Debug tab', 'call-tracking-metrics'); ?></li>
                                </ol>
                            </div>
                            <div>
                                <h4 class="font-semibold text-purple-800 mb-2">📋 <?php _e('Managing Logs', 'call-tracking-metrics'); ?></h4>
                                <ul class="list-disc pl-6 text-sm text-gray-700 space-y-1">
                                    <li><?php _e('Log Settings: Configure retention periods and auto-cleanup', 'call-tracking-metrics'); ?></li>
                                    <li><?php _e('Daily Logs: View and filter logs by date and type', 'call-tracking-metrics'); ?></li>
                                    <li><?php _e('Performance Monitor: Track system performance and resource usage', 'call-tracking-metrics'); ?></li>
                                    <li><?php _e('Health Check: Run comprehensive system diagnostics', 'call-tracking-metrics'); ?></li>
                                </ul>
                            </div>
                            <div>
                                <h4 class="font-semibold text-purple-800 mb-2">⚙️ <?php _e('Log Configuration', 'call-tracking-metrics'); ?></h4>
                                <ul class="list-disc pl-6 text-sm text-gray-700 space-y-1">
                                    <li><?php _e('Set log retention period (default: 30 days)', 'call-tracking-metrics'); ?></li>
                                    <li><?php _e('Enable/disable auto-cleanup of old logs', 'call-tracking-metrics'); ?></li>
                                    <li><?php _e('Clear all logs manually when needed', 'call-tracking-metrics'); ?></li>
                                    <li><?php _e('View logs grouped by date for easier navigation', 'call-tracking-metrics'); ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Support Section -->
                    <div class="bg-gradient-to-r from-emerald-50 to-green-50 p-6 rounded-lg border border-emerald-200">
                        <div class="flex items-center mb-4">
                            <div class="bg-emerald-100 p-2 rounded-lg mr-3">
                                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M12 2.25a9.75 9.75 0 109.75 9.75A9.75 9.75 0 0012 2.25z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-emerald-700"><?php _e('Support', 'call-tracking-metrics'); ?></h3>
                        </div>
                        <p class="mb-4"><?php _e('Need help with the CallTrackingMetrics WordPress plugin? Here are your support options:', 'call-tracking-metrics'); ?></p>
                        <ul class="list-disc pl-6 mb-4 space-y-2">
                            <li class="flex items-start">
                                <span class="mr-2">📚</span>
                                <a href="https://www.calltrackingmetrics.com/support/integrations/wordpress" target="_blank" rel="noopener" class="text-emerald-600 underline font-medium"><?php _e('Official Documentation', 'call-tracking-metrics'); ?></a>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2">🎧</span>
                                <a href="https://www.calltrackingmetrics.com/support" target="_blank" rel="noopener" class="text-emerald-600 underline font-medium"><?php _e('Contact Support', 'call-tracking-metrics'); ?></a>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2">🐛</span>
                                <?php _e('Enable the Debug tab in settings for detailed troubleshooting information.', 'call-tracking-metrics'); ?>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>