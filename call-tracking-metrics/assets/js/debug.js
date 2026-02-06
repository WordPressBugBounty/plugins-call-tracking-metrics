// Toast notification function available globally
function ctmShowToast(message, type = 'info') {
  const container = document.getElementById('ctm-toast-container');
  if (!container) return;

  // Remove any existing toasts after a short delay
  Array.from(container.children).forEach(child => {
    child.style.opacity = 0;
    setTimeout(() => child.remove(), 500);
  });

  // Toast color based on type
  let bg = 'bg-blue-600';
  if (type === 'success') bg = 'bg-green-600';
  if (type === 'error') bg = 'bg-red-600';
  if (type === 'warning') bg = 'bg-yellow-600';

  // Create toast element
  const toast = document.createElement('div');
  toast.className = `${bg} !text-white px-4 py-2 rounded shadow mb-2 transition-opacity duration-500`;
  toast.style.opacity = 1;
  toast.textContent = message;

  container.appendChild(toast);

  // Fade out and remove after 3 seconds
  setTimeout(() => {
    toast.style.opacity = 0;
    setTimeout(() => toast.remove(), 500);
  }, 3000);
}

// Helper function to update element content
function updateElement(elementId, value) {
    const element = document.getElementById(elementId);
    if (element && value !== undefined) {
        element.textContent = value;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const exportBtn = document.getElementById('ctm-export-system-info');
    if (exportBtn) {
        exportBtn.addEventListener('click', function() {
            exportBtn.disabled = true;
            exportBtn.textContent = 'Exporting...';
            fetch(window.ctmDebugVars?.ajaxurl || window.ajaxurl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({
                    action: 'ctm_export_diagnostic_report',
                    nonce: window.ctmDebugVars?.ctm_export_diagnostic_report_nonce || ''
                })
            })
            .then(response => response.json())
            .then(data => {
                exportBtn.disabled = false;
                exportBtn.textContent = 'Export System Info';
                if (data.success && data.data && data.data.report) {
                    const blob = new Blob([data.data.report], {type: 'text/plain'});
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'ctm-system-info.txt';
                    document.body.appendChild(a);
                    a.click();
                    setTimeout(() => {
                        document.body.removeChild(a);
                        URL.revokeObjectURL(url);
                    }, 100);
                } else {
                    ctmShowToast('Failed to export system info: ' + (data.data?.message || 'Unknown error'), 'error');
                }
            })
            .catch(err => {
                exportBtn.disabled = false;
                exportBtn.textContent = 'Export System Info';
                ctmShowToast('Export failed: ' + err, 'error');
            });
        });
    }
}); 