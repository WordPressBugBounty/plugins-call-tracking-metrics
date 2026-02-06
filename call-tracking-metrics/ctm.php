<?php
if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/call-tracking-metrics.php';

// Migrate legacy activation (ctm.php) to the primary plugin file.
if (is_admin()) {
    include_once ABSPATH . 'wp-admin/includes/plugin.php';

    add_action('admin_init', function (): void {
        if (!current_user_can('activate_plugins')) {
            return;
        }

        $legacy = plugin_basename(__FILE__);
        $primary = plugin_basename(__DIR__ . '/call-tracking-metrics.php');

        if (is_plugin_active($legacy) && !is_plugin_active($primary)) {
            activate_plugin($primary, '', false, true);
        }

        if (is_plugin_active($legacy)) {
            deactivate_plugins($legacy, true);
        }
    });
}
