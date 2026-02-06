<?php
/**
 * Plugin Health Check Component
 * Simplified system assessment and health monitoring
 */
?>

<div class="bg-white rounded-xl shadow-lg border border-gray-200">
    <div class="flex items-center justify-between p-4 cursor-pointer hover:bg-gray-50 transition-colors" onclick="togglePanel('health-check')">
        <div class="flex items-center gap-2">
            <svg id="health-check-icon" class="w-5 h-5 text-green-600 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="transform: rotate(180deg);">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h3 class="text-lg font-semibold text-gray-900"><?php _e('Plugin Health Check', 'call-tracking-metrics'); ?></h3>
        </div>
        <button onclick="event.stopPropagation(); exportHealthReport()" class="bg-gray-600 hover:bg-gray-700 !text-white font-medium px-4 py-1 rounded-lg flex items-center gap-2 text-sm transition">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <?php _e('Export', 'call-tracking-metrics'); ?>
        </button>
    </div>
    
    <div id="health-check-content" class="border-t border-gray-200 p-6">
        <!-- Overall Health Score -->
        <div class="bg-gradient-to-br from-green-50 to-green-100 border border-green-200 rounded-xl p-6 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-green-500 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-xl font-bold text-green-800"><?php _e('Overall Health Score', 'call-tracking-metrics'); ?></h4>
                        <p class="text-sm text-green-600"><?php _e('System status assessment', 'call-tracking-metrics'); ?></p>
                    </div>
                </div>
                <div class="text-right">
                    <div id="health-score" class="text-4xl font-bold text-green-600">--</div>
                    <div class="text-sm text-green-700"><?php _e('out of 100', 'call-tracking-metrics'); ?></div>
                </div>
            </div>
        </div>

        <!-- Health Check Categories -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <!-- API Configuration -->
            <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex items-center gap-2 mb-3">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    <h5 class="font-bold text-gray-900"><?php _e('API Configuration', 'call-tracking-metrics'); ?></h5>
                </div>
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-sm">
                        <span><?php _e('API Key', 'call-tracking-metrics'); ?></span>
                        <span id="check-api-key" class="health-indicator">⏳</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span><?php _e('API Connection', 'call-tracking-metrics'); ?></span>
                        <span id="check-api-connection" class="health-indicator">⏳</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span><?php _e('Account Access', 'call-tracking-metrics'); ?></span>
                        <span id="check-account-access" class="health-indicator">⏳</span>
                    </div>
                </div>
            </div>

            <!-- Server Requirements -->
            <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex items-center gap-2 mb-3">
                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                    </svg>
                    <h5 class="font-bold text-gray-900"><?php _e('Server Requirements', 'call-tracking-metrics'); ?></h5>
                </div>
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-sm">
                        <span><?php _e('PHP Version', 'call-tracking-metrics'); ?></span>
                        <span id="check-php-version" class="health-indicator">⏳</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span><?php _e('cURL Extension', 'call-tracking-metrics'); ?></span>
                        <span id="check-curl" class="health-indicator">⏳</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span><?php _e('SSL Support', 'call-tracking-metrics'); ?></span>
                        <span id="check-ssl" class="health-indicator">⏳</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span><?php _e('Memory Limit', 'call-tracking-metrics'); ?></span>
                        <span id="check-memory" class="health-indicator">⏳</span>
                    </div>
                </div>
            </div>

            <!-- Plugin Status -->
            <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex items-center gap-2 mb-3">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2m0 0V1a1 1 0 011-1h2a1 1 0 011 1v3M7 4H5a1 1 0 00-1 1v3m0 0v8a2 2 0 002 2h10a2 2 0 002-2V8m0 0V5a1 1 0 00-1-1h-2M7 4h10"/>
                    </svg>
                    <h5 class="font-bold text-gray-900"><?php _e('Plugin Status', 'call-tracking-metrics'); ?></h5>
                </div>
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-sm">
                        <span><?php _e('Plugin Version', 'call-tracking-metrics'); ?></span>
                        <span id="check-plugin-version" class="health-indicator">⏳</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span><?php _e('Database Tables', 'call-tracking-metrics'); ?></span>
                        <span id="check-database-tables" class="health-indicator">⏳</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span><?php _e('File Permissions', 'call-tracking-metrics'); ?></span>
                        <span id="check-file-permissions" class="health-indicator">⏳</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span><?php _e('Debug Mode', 'call-tracking-metrics'); ?></span>
                        <span id="check-debug-mode" class="health-indicator">⏳</span>
                    </div>
                </div>
            </div>

            <!-- Form Integration -->
            <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex items-center gap-2 mb-3">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h5 class="font-bold text-gray-900"><?php _e('Form Integration', 'call-tracking-metrics'); ?></h5>
                </div>
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-sm">
                        <span><?php _e('Contact Form 7', 'call-tracking-metrics'); ?></span>
                        <span id="check-cf7" class="health-indicator">⏳</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span><?php _e('Gravity Forms', 'call-tracking-metrics'); ?></span>
                        <span id="check-gf" class="health-indicator">⏳</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Button -->
        <div class="flex justify-center">
            <button onclick="runHealthCheck()" id="health-check-btn" class="bg-green-600 hover:bg-green-700 !text-white font-medium px-6 py-2 rounded-lg transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
                <?php _e('Run Health Check', 'call-tracking-metrics'); ?>
            </button>
        </div>
    </div>
</div>

<script>
// Export Health Report
function exportHealthReport() {
    const button = event.target;
    const originalText = button.textContent;
    
    button.disabled = true;
    button.textContent = 'Generating...';
    
    // Collect health check data
    const healthData = {
        timestamp: new Date().toISOString(),
        overall_score: document.getElementById('health-score')?.textContent || 'N/A',
        checks: []
    };
    
    // Collect individual check results
    const checkElements = document.querySelectorAll('.health-indicator');
    checkElements.forEach(element => {
        const checkName = element.parentElement?.querySelector('span')?.textContent || 'Unknown';
        const status = element.textContent === '✓' ? 'pass' : 
                     element.textContent === '⚠' ? 'warning' : 
                     element.textContent === '✗' ? 'fail' : 'pending';
        
        healthData.checks.push({
            name: checkName,
            status: status
        });
    });
    
    // Generate report
    const reportContent = `
Call Tracking Metrics - Health Report
Generated: ${new Date().toLocaleString()}

Overall Health Score: ${healthData.overall_score}/100

Check Results:
${healthData.checks.map(check => 
    `${check.name}: ${check.status.toUpperCase()}`
).join('\n')}

Report generated by Call Tracking Metrics Plugin
    `.trim();
    
    // Download file
    const blob = new Blob([reportContent], { type: 'text/plain' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `ctm-health-report-${new Date().toISOString().split('T')[0]}.txt`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
    
    button.disabled = false;
    button.textContent = originalText;
                ctmShowToast('Health report exported successfully', 'success');
}

// Run Health Check
function runHealthCheck() {
    const button = document.getElementById('health-check-btn');
    
    if (!button) {
        ctmShowToast('Health check button not found', 'error');
        return;
    }
    
    button.disabled = true;
    button.innerHTML = `
        <svg class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
        </svg>
        Running Checks...
    `;
    
    // Reset all health indicators
    const healthIndicators = document.querySelectorAll('.health-indicator');
    healthIndicators.forEach(indicator => {
        indicator.textContent = '⏳';
        indicator.className = 'health-indicator text-blue-600';
    });
    
    const formData = new FormData();
    formData.append('action', 'ctm_health_check');
    formData.append('nonce', '<?= wp_create_nonce('ctm_health_check') ?>');
    
    fetch('<?= admin_url('admin-ajax.php') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const checks = data.data.checks;
            
            // Update individual check indicators
            const checkMapping = {
                'API Key': 'check-api-key',
                'API Connection': 'check-api-connection',
                'Account Access': 'check-account-access',
                'Contact Form 7': 'check-cf7',
                'Gravity Forms': 'check-gf',
                'PHP Version': 'check-php-version',
                'cURL Extension': 'check-curl',
                'SSL Support': 'check-ssl',
                'Memory Limit': 'check-memory',
                'Plugin Version': 'check-plugin-version',
                'Database Tables': 'check-database-tables',
                'File Permissions': 'check-file-permissions',
                'Debug Mode': 'check-debug-mode'
            };
            
            checks.forEach(check => {
                const elementId = checkMapping[check.name];
                if (elementId) {
                    const element = document.getElementById(elementId);
                    if (element) {
                        const icon = check.status === 'pass' ? '✓' : 
                                   check.status === 'warning' ? '⚠' : '✗';
                        const colorClass = check.status === 'pass' ? 'text-green-600' : 
                                         check.status === 'warning' ? 'text-yellow-600' : 'text-red-600';
                        
                        element.textContent = icon;
                        element.className = `health-indicator ${colorClass}`;
                        element.title = check.message || '';
                    }
                }
            });
            
            // Update overall health score
            const healthScore = document.getElementById('health-score');
            if (healthScore) {
                const passedChecks = checks.filter(c => c.status === 'pass').length;
                const totalChecks = checks.length;
                const score = Math.round((passedChecks / totalChecks) * 100);
                
                healthScore.textContent = score;
                healthScore.className = score >= 80 ? 'text-4xl font-bold text-green-600' :
                                       score >= 60 ? 'text-4xl font-bold text-yellow-600' :
                                       'text-4xl font-bold text-red-600';
            }
            
            const failedChecks = checks.filter(c => c.status === 'fail').length;
            const warningChecks = checks.filter(c => c.status === 'warning').length;
            
            if (failedChecks === 0 && warningChecks === 0) {
                ctmShowToast('All health checks passed!', 'success');
            } else if (failedChecks > 0) {
                ctmShowToast(`Health check completed with ${failedChecks} failures and ${warningChecks} warnings`, 'error');
            } else {
                ctmShowToast(`Health check completed with ${warningChecks} warnings`, 'warning');
            }
        } else {
            ctmShowToast('Health check failed to run', 'error');
        }
    })
    .catch(error => {
        console.error('Health check error:', error);
                    ctmShowToast('Network error during health check', 'error');
    })
    .finally(() => {
        button.disabled = false;
        button.innerHTML = `
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Run Health Check
        `;
    });
}

// Auto-load health check on page load
document.addEventListener('DOMContentLoaded', function() {
    runHealthCheck();
});
</script>
 