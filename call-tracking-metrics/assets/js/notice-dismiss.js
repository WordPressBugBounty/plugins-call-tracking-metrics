function dismissTopNotice(type) {
    // Hide the notice immediately
    const noticeId = 'top-' + type + '-notice';
    const notice = document.getElementById(noticeId);
    if (notice) {
        notice.style.transition = 'opacity 0.3s ease';
        notice.style.opacity = '0';
        setTimeout(() => {
            notice.style.display = 'none';
        }, 300);
    }
    // Send AJAX request to save dismiss preference
    const formData = new FormData();
    formData.append('action', 'ctm_dismiss_notice');
    formData.append('notice_type', type);
    formData.append('nonce', ctmNoticeData.nonce);
    fetch(ctmNoticeData.ajaxurl, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            ctmShowToast('Notice dismissed successfully', 'success');
        } else {
            ctmShowToast(data.data?.message || 'Failed to dismiss notice', 'error');
        }
    })
    .catch(error => {
        console.error('Error dismissing notice:', error);
        ctmShowToast('Network error occurred while dismissing notice', 'error');
    });
} 