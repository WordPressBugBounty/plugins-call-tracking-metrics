<div id="top-cf7-notice" class="bg-[#e6f7ff] border-l-4 border-[#02bdf6] text-[#16294f] p-4 rounded flex items-center justify-between gap-4 mb-2">
    <div class="flex flex-col gap-2">
        <div class="flex items-center gap-4">
            <span class="font-semibold font-brand-heading"><?php _e('Enable Contact Form 7 integration', 'call-tracking-metrics'); ?></span>
            <a href="<?= esc_url($cf7_url) ?>" class="bg-[#02bdf6] hover:bg-[#324a85] !text-white px-4 py-2 rounded shadow transition"><?php _e('Install Contact Form 7', 'call-tracking-metrics'); ?></a>
        </div>
        <div class="text-sm mt-1 font-brand-body">
            <strong><?php _e('Note:', 'call-tracking-metrics'); ?></strong> <?php _e('It is required to use a form that captures a telephone number (<code>input type="tel"</code>) in order for Contact Form 7 to integrate properly with our FormReactor. For more information, see', 'call-tracking-metrics'); ?> <a href="https://www.calltrackingmetrics.com/support/integrations/wordpress" target="_blank" rel="noopener" class="underline text-[#02bdf6]"><?php _e('Using the CallTrackingMetrics WordPress Plugin', 'call-tracking-metrics'); ?></a>.
        </div>
        <div class="text-sm mt-1 font-brand-body">
            <strong><?php _e('Note:', 'call-tracking-metrics'); ?></strong> <?php _e('If you will request international (non-U.S.) phone numbers with your Contact Form 7 forms, we recommend using the plugin', 'call-tracking-metrics'); ?> <a href="https://wordpress.org/plugins/cf7-international-telephone-input/" target="_blank" rel="noopener" class="underline text-[#02bdf6]"><?php _e('International Telephone Input for Contact Form 7', 'call-tracking-metrics'); ?></a> <?php _e('to avoid possible formatting issues with our FormReactor. Both', 'call-tracking-metrics'); ?> <code>[tel]</code> <?php _e('and', 'call-tracking-metrics'); ?> <code>[intl_tel]</code> <?php _e('are now supported as phone inputs.', 'call-tracking-metrics'); ?>
        </div>
    </div>
    <button type="button" onclick="dismissTopNotice('cf7')" class="text-[#02bdf6] hover:text-[#324a85] p-1 rounded hover:bg-[#e6f7ff] transition" title="<?php _e('Dismiss this notice.', 'call-tracking-metrics'); ?>">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
    </button>
</div>

<!-- JS moved to assets/js/notice-dismiss.js --> 