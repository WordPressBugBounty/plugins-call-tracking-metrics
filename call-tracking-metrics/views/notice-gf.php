<div id="top-gf-notice" class="bg-[#e6f7ff] border-l-4 border-[#02bdf6] text-[#16294f] p-4 rounded flex items-center justify-between gap-4 mb-2">
    <div class="flex flex-col gap-2">
        <div class="flex items-center gap-4">
            <span class="font-semibold font-brand-heading"><?php _e('Enable Gravity Forms integration', 'call-tracking-metrics'); ?></span>
            <a href="<?= esc_url($gf_url) ?>" target="_blank" rel="noopener" class="bg-[#02bdf6] hover:bg-[#324a85] !text-white px-4 py-2 rounded shadow transition"><?php _e('Get Gravity Forms', 'call-tracking-metrics'); ?></a>
        </div>
        <div class="text-sm mt-1 font-brand-body">
            <strong><?php _e('Note:', 'call-tracking-metrics'); ?></strong> <?php _e('It is required to use a form that captures a telephone number (<code>input type="tel"</code>) in order for Gravity Forms to integrate properly with our FormReactor. For more information, see', 'call-tracking-metrics'); ?> <a href="https://www.calltrackingmetrics.com/support/integrations/wordpress" target="_blank" rel="noopener" class="underline text-[#02bdf6]"><?php _e('Using the CallTrackingMetrics WordPress Plugin', 'call-tracking-metrics'); ?></a>.
        </div>
    </div>
    <button type="button" onclick="dismissTopNotice('gf')" class="text-[#02bdf6] hover:text-[#324a85] p-1 rounded hover:bg-[#e6f7ff] transition" title="<?php _e('Dismiss this notice.', 'call-tracking-metrics'); ?>">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
    </button>
</div>

<!-- JS moved to assets/js/notice-dismiss.js --> 