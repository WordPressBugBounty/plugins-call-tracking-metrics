
<script>

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

</script> 