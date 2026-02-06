<?php
/**
 * Debug Header Component
 * Displays debug mode status, statistics, and toggle controls
 */

// Ensure variables are available from parent context
$debugEnabled = $debugEnabled ?? false;
$log_stats = $log_stats ?? ['total_days' => 0, 'total_entries' => 0, 'total_size' => 0, 'type_counts' => []];
?>

<div class="bg-white rounded-xl shadow-lg border border-gray-200 p-4 mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <svg class="w-6 h-6 text-[#02bdf6]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h2 class="text-xl font-bold tracking-tight text-[#16294f] font-brand-heading"><?php _e('Debug Center', 'call-tracking-metrics'); ?></h2>
        </div>

        <div class="flex items-center gap-4 text-sm">
            <div class="flex items-center gap-1">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span class="font-semibold text-gray-900"><?= $log_stats['total_entries'] ?></span>
                <span class="text-gray-500"><?php _e('Entries', 'call-tracking-metrics'); ?></span>
            </div>
            <div class="flex items-center gap-1">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span class="font-semibold text-gray-900"><?= $log_stats['total_days'] ?></span>
                <span class="text-gray-500"><?php _e('Days', 'call-tracking-metrics'); ?></span>
            </div>
            <div class="flex items-center gap-1">
                <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke-width="2" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/>
                </svg>
                <span class="font-semibold text-gray-900"><?= $log_stats['type_counts']['error'] ?? 0 ?></span>
                <span class="text-gray-500"><?php _e('Errors', 'call-tracking-metrics'); ?></span>
            </div>
            <div class="flex items-center gap-1">
                <svg class="w-4 h-4 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke-width="2" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01"/>
                </svg>
                <span class="font-semibold text-gray-900"><?= $log_stats['type_counts']['warning'] ?? 0 ?></span>
                <span class="text-gray-500"><?php _e('Warnings', 'call-tracking-metrics'); ?></span>
            </div>
            <div class="flex items-center gap-1">
                <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke-width="2" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 16h.01M12 8v4"/>
                </svg>
                <span class="font-semibold text-gray-900"><?= $log_stats['type_counts']['info'] ?? 0 ?></span>
                <span class="text-gray-500"><?php _e('Info', 'call-tracking-metrics'); ?></span>
            </div>
            <div class="flex items-center gap-1">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <rect x="4" y="4" width="16" height="16" rx="2" stroke-width="2"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h8"/>
                </svg>
                <span class="font-semibold text-gray-900"><?= $log_stats['type_counts']['debug'] ?? 0 ?></span>
                <span class="text-gray-500"><?php _e('Debug', 'call-tracking-metrics'); ?></span>
            </div>
            <div class="flex items-center gap-1">
                <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <span class="font-semibold text-gray-900"><?= $log_stats['type_counts']['api'] ?? 0 ?></span>
                <span class="text-gray-500"><?php _e('API', 'call-tracking-metrics'); ?></span>
            </div>
        </div>
    </div>
</div> 