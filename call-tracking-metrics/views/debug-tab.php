<?php
// Debug tab view with comprehensive logging system
// Variables are passed from the parent context: $debugEnabled, $log

$retention_days = (int) get_option('ctm_log_retention_days', 7);
$auto_cleanup = get_option('ctm_log_auto_cleanup', true);

// Get log statistics and available dates - OPTIMIZED for performance using new database system
$log_stats = ['total_days' => 0, 'total_entries' => 0, 'total_size' => 0, 'type_counts' => []];
$available_dates = [];

if ($debugEnabled) {
    if (isset($loggingSystem) && $loggingSystem) {
        $all_dates = $loggingSystem->getAvailableLogDates();
        
        // Only show last 5 days initially for performance
        $available_dates = array_slice($all_dates, 0, 5);
        
        // Calculate statistics only for displayed dates to improve performance
        $total_entries = 0;
        $total_size = 0;
        $type_counts = [];
        
        foreach ($available_dates as $date) {
            $logs = $loggingSystem->getLogsForDate($date);
            if (!empty($logs)) {
                $total_entries += count($logs);
                
                // Count all entries for accurate type counts
                foreach ($logs as $log_entry) {
                    $type = $log_entry['type'] ?? 'unknown';
                    $type_counts[$type] = ($type_counts[$type] ?? 0) + 1;
                }
                
                // Only sample first 100 entries for size calculation (performance)
                $sample_logs = array_slice($logs, 0, 100);
                $sample_size = 0;
                
                foreach ($sample_logs as $log_entry) {
                    // Calculate approximate size of this entry
                    $sample_size += strlen(json_encode($log_entry));
                }
                
                // Estimate total size for this date based on sample
                $total_logs_for_date = count($logs);
                $sampled_logs_for_date = count($sample_logs);
                if ($sampled_logs_for_date > 0) {
                    $estimated_date_size = ($sample_size / $sampled_logs_for_date) * $total_logs_for_date;
                    $total_size += $estimated_date_size;
                }
            }
        }
        
        $log_stats = [
            'total_days' => count($all_dates),
            'total_entries' => $total_entries,
            'total_size' => $total_size,
            'type_counts' => $type_counts,
            'oldest_log' => !empty($all_dates) ? end($all_dates) : null,
            'newest_log' => !empty($all_dates) ? reset($all_dates) : null,
            'total_available_days' => count($all_dates)
        ];
    }
}
?>


<!-- Add Toast Container for notifications -->
<div id="ctm-toast-container" style="position: fixed; top: 1.5rem; right: 1.5rem; z-index: 9999;"></div>


<div class="mb-8">
    <?php 
    // Include debug header component
    include plugin_dir_path(__FILE__) . 'debug-includes/debug-header.php';
    ?>
    <!-- Advanced Debug Features -->
    <div class="grid grid-cols-1 gap-4">
        <?php include plugin_dir_path(__FILE__) . 'debug-includes/health-check.php'; ?>
        <?php include plugin_dir_path(__FILE__) . 'debug-includes/performance-monitor.php'; ?>
        <?php include plugin_dir_path(__FILE__) . 'debug-includes/log-settings.php'; ?>
    </div>

    
    
    <!-- Include global JavaScript functions -->
    <?php include plugin_dir_path(__FILE__) . 'debug-includes/debug-javascript.php'; ?>
</div>

<script>
function togglePanel(panelId) {
    const content = document.getElementById(panelId + '-content');
    const icon = document.getElementById(panelId + '-icon');
    
    if (content && icon) {
        if (content.classList.contains('hidden')) {
            content.classList.remove('hidden');
            icon.style.transform = 'rotate(180deg)';
            // Store panel state
            localStorage.setItem('ctm-panel-' + panelId, 'open');
        } else {
            content.classList.add('hidden');
            icon.style.transform = 'rotate(0deg)';
            // Store panel state
            localStorage.setItem('ctm-panel-' + panelId, 'closed');
        }
    }
}

// Restore panel states on page load
document.addEventListener('DOMContentLoaded', function() {
    const panels = ['system-performance-monitor'];
    
    panels.forEach(function(panelId) {
        const state = localStorage.getItem('ctm-panel-' + panelId);
        const content = document.getElementById(panelId + '-content');
        const icon = document.getElementById(panelId + '-icon');
        
        if (content && icon && state === 'open') {
            content.classList.remove('hidden');
            icon.style.transform = 'rotate(180deg)';
        }
    });
});
</script>