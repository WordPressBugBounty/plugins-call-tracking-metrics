/**
 * Admin Tab Control JavaScript
 * 
 * Handles tab access control and prevents access to restricted tabs
 * when the API is not connected.
 * 
 * @package     CallTrackingMetrics
 * @since       2.0.0
 */

(function($) {
    'use strict';

    // Check if we're on a CTM admin page
    if (typeof ctmAdminVars === 'undefined') {
        return;
    }

    // Function to check if API is connected
    function isApiConnected() {
        // Handle both boolean true and string "1" from PHP
        return ctmAdminVars.apiConnected === true || ctmAdminVars.apiConnected === "1" || ctmAdminVars.apiConnected === 1;
    }

    // Function to block access to restricted tabs
    function blockRestrictedTabs() {
        if (!isApiConnected()) {
            // Define restricted tabs
            const restrictedTabs = ['api', 'import', 'forms', 'debug'];
            
            // Check current tab
            const urlParams = new URLSearchParams(window.location.search);
            const currentTab = urlParams.get('tab');
            
            if (restrictedTabs.includes(currentTab)) {
                // Redirect to general tab with error
                const newUrl = new URL(window.location.href);
                newUrl.searchParams.set('tab', 'general');
                newUrl.searchParams.set('error', 'api_required');
                window.location.href = newUrl.toString();
                return;
            }
            
            // Hide restricted tab links completely instead of showing them with badges
            restrictedTabs.forEach(tab => {
                const tabLink = $(`a[href*="tab=${tab}"]`);
                if (tabLink.length) {
                    tabLink.hide();
                }
            });
        }
    }

    // Function to show connection required notice
    function showConnectionNotice() {
        if (!isApiConnected()) {
            const notice = `
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <h3 class="text-sm font-medium text-blue-800">API Connection Required</h3>
                            <p class="text-sm text-blue-700 mt-1">Connect your API credentials to access all features including form import, management, and debugging tools.</p>
                        </div>
                    </div>
                </div>
            `;
            
            // Insert notice at the top of the content area
            const contentArea = $('.bg-gray-50.p-6.rounded-b-lg');
            if (contentArea.length) {
                contentArea.prepend(notice);
            }
        }
    }

    // Initialize when DOM is ready
    $(document).ready(function() {
        blockRestrictedTabs();
        showConnectionNotice();
        
        // Handle tab link clicks
        $('nav a[href*="tab="]').on('click', function(e) {
            if (!isApiConnected()) {
                const href = $(this).attr('href');
                const tab = new URLSearchParams(href).get('tab');
                
                if (['api', 'import', 'forms', 'debug'].includes(tab)) {
                    e.preventDefault();
                    
                    // Show toast notification
                    if (typeof ctmShowToast === 'function') {
                        ctmShowToast('API connection required to access this tab', 'warning');
                    } else {
                        alert('API connection required to access this tab');
                    }
                    
                    // Redirect to general tab
                    const newUrl = new URL(window.location.href);
                    newUrl.searchParams.set('tab', 'general');
                    newUrl.searchParams.set('error', 'api_required');
                    window.location.href = newUrl.toString();
                }
            }
        });
    });

})(jQuery);
