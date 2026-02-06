<?php
/**
 * Settings Page View
 * 
 * This view file displays the main settings page for the CallTrackingMetrics plugin admin interface.
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

// Ensure required variables are defined with defaults
$apiStatus = $apiStatus ?? 'not_connected';
$active_tab = $active_tab ?? 'general';
$tab_content = $tab_content ?? '';
$notices = $notices ?? [];
?>

<!-- Success Notices at the very top of the page -->
<?php if (!empty($notices)): ?>
    <div class="mb-6">
        <?php foreach ($notices as $notice) echo $notice; ?>
    </div>
<?php endif; ?>

<div class="wrap max-w-full mx-auto p-6 bg-white rounded-lg shadow-md">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
            <!-- CallTrackingMetrics Logo -->
            <div class="flex items-center">
               <img src="<?= plugin_dir_url(__DIR__) . '/assets/images/ctm_logo.png' ?>" alt="CallTrackingMetrics Logo" class="h-10">
            </div>
        </div>
        <!-- API Status Indicator -->
        <div class="flex items-center gap-3">
            <?php if ($apiStatus === 'connected'): ?>
                <div class="flex items-center gap-2 px-3 py-2 bg-[#e6f7ff] border border-[#02bdf6] rounded-lg">
                    <div class="w-2 h-2 bg-[#02bdf6] rounded-full animate-pulse"></div>
                    <span class="text-sm font-medium text-[#16294f]"><?php _e('API Connected', 'call-tracking-metrics'); ?></span>
                    <a href="?page=call-tracking-metrics&tab=api" class="text-[#02bdf6] hover:text-[#324a85] transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                    </a>
                </div>
            <?php else: ?>
                <div class="flex items-center gap-2 px-3 py-2 bg-red-50 border border-red-200 rounded-lg">
                    <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                    <span class="text-sm font-medium text-red-700"><?php _e('API Not Connected', 'call-tracking-metrics'); ?></span>
                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.08 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if ($apiStatus === 'connected'): ?>
        <!-- Full Tab Navigation (Connected) -->
        <nav class="flex space-x-2">
            <a href="?page=call-tracking-metrics&tab=general" class="flex items-center px-4 py-2 rounded-t-lg font-medium transition-colors duration-200 <?= $active_tab === 'general' ? 'bg-[#02bdf6] !text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span><?php _e('General', 'call-tracking-metrics'); ?></span>
            </a>

            <!-- <a href="?page=call-tracking-metrics&tab=mapping" class="flex items-center px-4 py-2 rounded-t-lg font-medium transition-colors duration-200 <?= $active_tab === 'mapping' ? 'bg-[#02bdf6] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 3v6a1 1 0 001 1h6"></path>
                </svg>
                <span><?php _e('Field Mapping', 'call-tracking-metrics'); ?></span>
            </a> -->
            <a href="?page=call-tracking-metrics&tab=api" class="flex items-center px-4 py-2 rounded-t-lg font-medium transition-colors duration-200 <?= $active_tab === 'api' ? 'bg-[#02bdf6] !text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path>
                </svg>
                <span><?php _e('API Information', 'call-tracking-metrics'); ?></span>
            </a>
            <a href="?page=call-tracking-metrics&tab=import" class="flex items-center px-4 py-2 rounded-t-lg font-medium transition-colors duration-200 <?= $active_tab === 'import' ? 'bg-[#02bdf6] !text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                </svg>
                <span><?php _e('Import Forms', 'call-tracking-metrics'); ?></span>
            </a>
            <a href="?page=call-tracking-metrics&tab=forms" class="flex items-center px-4 py-2 rounded-t-lg font-medium transition-colors duration-200 <?= $active_tab === 'forms' ? 'bg-[#02bdf6] !text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V9a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                </svg>
                <span><?php _e('Manage Forms', 'call-tracking-metrics'); ?></span>
            </a>
            <a href="?page=call-tracking-metrics&tab=documentation" class="flex items-center px-4 py-2 rounded-t-lg font-medium transition-colors duration-200 <?= $active_tab === 'documentation' ? 'bg-[#02bdf6] !text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
                <span><?php _e('Documentation', 'call-tracking-metrics'); ?></span>
            </a>
            <?php if (get_option('ctm_debug_enabled')): ?>
            <a href="?page=call-tracking-metrics&tab=debug" class="flex items-center px-4 py-2 rounded-t-lg font-medium transition-colors duration-200 <?= $active_tab === 'debug' ? 'bg-[#02bdf6] !text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                </svg>
                <span><?php _e('Debug', 'call-tracking-metrics'); ?></span>
            </a>
            <?php endif; ?>
        </nav>
    <?php else: ?>
        <!-- Minimal Tab Navigation (Not Connected) -->
        <nav class="flex space-x-2">
            <a href="?page=call-tracking-metrics&tab=general" class="flex items-center px-4 py-2 rounded-t-lg font-medium transition-colors duration-200 <?= $active_tab === 'general' ? 'bg-[#02bdf6] !text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span><?php _e('Get Started', 'call-tracking-metrics'); ?></span>
            </a>
            <a href="?page=call-tracking-metrics&tab=documentation" class="flex items-center px-4 py-2 rounded-t-lg font-medium transition-colors duration-200 <?= $active_tab === 'documentation' ? 'bg-[#02bdf6] !text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
                <span><?php _e('Documentation', 'call-tracking-metrics'); ?></span>
            </a>
            <span class="px-3 py-2 text-sm text-gray-500 italic flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0 0l-2-2m2 2l2-2m-2-7a3 3 0 100 6 3 3 0 000-6z"></path>
                </svg>
                <span><?php _e('More tabs available after connection', 'call-tracking-metrics'); ?></span>
            </span>
        </nav>
    <?php endif; ?>
    
    <div class="bg-gray-50 p-6 rounded-b-lg">
        <?= $tab_content ?>
    </div>
</div>

<!-- Support Footer -->
<div class="mt-6 rounded-lg shadow-sm border border-gray-200 overflow-hidden bg-gradient-to-br from-[#e6f7ff] via-white to-white max-w-full mx-auto wrap">
    <div class="flex flex-col md:flex-row items-center justify-between gap-3 px-6 py-4 bg-[#e6f7ff] border-b border-[#02bdf6]">
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center justify-center rounded-full bg-white border border-[#02bdf6] w-8 h-8 mr-2">
                <svg class="w-5 h-5 text-[#02bdf6]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path>
                </svg>
            </span>
            <h3 class="text-lg font-bold text-[#16294f] font-brand-heading tracking-tight"><?php _e('Need Help?', 'call-tracking-metrics'); ?></h3>
        </div>
        <div class="text-sm text-[#02bdf6] font-semibold tracking-wide">
            <?php _e('Plugin Version:', 'call-tracking-metrics'); ?> <span class="ml-1 bg-white px-2 py-0.5 rounded text-[#16294f] border border-[#02bdf6]"><?= esc_html(CTM_VERSION) ?></span>
        </div>
    </div>
    <div class="px-6 py-4 bg-white">
        <div class="flex flex-col md:flex-row gap-6 justify-center">
            <!-- Support -->
            <div class="flex-1 min-w-[200px]">
                <div class="flex items-center mb-2">
                    <span class="inline-flex items-center justify-center rounded-full bg-[#e6f7ff] border border-[#02bdf6] w-6 h-6 mr-2">
                        <svg class="w-3 h-3 text-[#02bdf6]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                    </span>
                    <h4 class="font-semibold text-[#16294f] font-brand-heading text-sm"><?php _e('Support', 'call-tracking-metrics'); ?></h4>
                </div>
                <ul class="space-y-0.5 text-xs pl-2">
                    <li>
                        <a href="https://calltrackingmetrics.zendesk.com/hc/en-us" target="_blank" rel="noopener noreferrer" class="text-[#02bdf6] hover:text-[#324a85] underline transition">
                            <?php _e('Help Center', 'call-tracking-metrics'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="https://calltrackingmetrics.zendesk.com/hc/en-us/requests/new" target="_blank" rel="noopener noreferrer" class="text-[#02bdf6] hover:text-[#324a85] underline transition">
                            <?php _e('Contact Support', 'call-tracking-metrics'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="mailto:support@calltrackingmetrics.com" class="text-[#02bdf6] hover:text-[#324a85] underline transition">
                            <?php _e('Email Support', 'call-tracking-metrics'); ?>
                        </a>
                    </li>
                </ul>
            </div>
            <!-- Resources -->
            <div class="flex-1 min-w-[200px]">
                <div class="flex items-center mb-2">
                    <span class="inline-flex items-center justify-center rounded-full bg-[#e6f7ff] border border-[#02bdf6] w-6 h-6 mr-2">
                        <svg class="w-3 h-3 text-[#02bdf6]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </span>
                    <h4 class="font-semibold text-[#16294f] font-brand-heading text-sm"><?php _e('Resources', 'call-tracking-metrics'); ?></h4>
                </div>
                <ul class="space-y-0.5 text-xs pl-2">
                    <li>
                        <a href="https://www.calltrackingmetrics.com/" target="_blank" rel="noopener noreferrer" class="text-[#02bdf6] hover:text-[#324a85] underline transition">
                            <?php _e('CallTrackingMetrics Website', 'call-tracking-metrics'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="?page=call-tracking-metrics&tab=documentation" class="text-[#02bdf6] hover:text-[#324a85] underline transition">
                            <?php _e('Plugin Documentation', 'call-tracking-metrics'); ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        
        <?php if ($apiStatus === 'connected'): ?>
        <!-- Quick Help Section -->
        <div class="mt-4 pt-3 border-t border-gray-200">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
                <div class="flex items-center space-x-3">
                    <span class="text-xs text-gray-600"><?php _e('Quick Help:', 'call-tracking-metrics'); ?></span>
                    <a href="?page=call-tracking-metrics&tab=debug" class="text-xs text-[#02bdf6] hover:text-[#324a85] underline"><?php _e('Debug Tools', 'call-tracking-metrics'); ?></a>
                    <span class="text-gray-400">|</span>
                    <a href="?page=call-tracking-metrics&tab=api" class="text-xs text-[#02bdf6] hover:text-[#324a85] underline"><?php _e('API Information', 'call-tracking-metrics'); ?></a>
                    <span class="text-gray-400">|</span>
                    <a href="?page=call-tracking-metrics&tab=logs" class="text-xs text-[#02bdf6] hover:text-[#324a85] underline"><?php _e('View Logs', 'call-tracking-metrics'); ?></a>
                </div>
                <div class="flex items-center space-x-2 text-xs text-gray-500">
                    <div class="flex items-center">
                        <svg class="w-3 h-3 mr-1 text-[#02bdf6]" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                        <span><?php _e('Powered by CallTrackingMetrics', 'call-tracking-metrics'); ?></span>
                    </div>
                    <span class="text-gray-400">|</span>
                    <div>
                        <?php _e('WP', 'call-tracking-metrics'); ?> <?= get_bloginfo('version') ?> | 
                        <?php _e('PHP', 'call-tracking-metrics'); ?> <?= PHP_VERSION ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div> 