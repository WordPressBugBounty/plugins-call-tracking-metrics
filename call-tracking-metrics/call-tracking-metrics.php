<?php
/*
	Plugin Name: CallTrackingMetrics
	Plugin URI: https://www.calltrackingmetrics.com/
	Description: View your CallTrackingMetrics daily call volume in your WordPress Dashboard, and integrate with Contact Form 7 and Gravity Forms.
	Author: CallTrackingMetrics
	Version: 1.2.12
	Author URI: https://www.calltrackingmetrics.com/
*/

if ( !defined('WP_PLUGIN_URL') ) {
  define('WP_PLUGIN_URL', WP_CONTENT_URL . '/plugins');
}
if ( !defined('WP_PLUGIN_DIR') ) {
  define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins');
}

function ctm_WP_error() {
	echo "<div id='ctm_error' class='update-message notice inline notice-error notice-alt' style='margin:30px 0 10px 0;'><p><strong>Your WordPress version is too old for the CallTrackingMetrics plugin.</strong></p></div>";
}
function ctm_PHP_error() {
	echo "<div id='ctm_error' class='update-message notice inline notice-error notice-alt' style='margin:30px 0 10px 0;'><p><strong>Your PHP version is too old for the CallTrackingMetrics plugin.</strong></p></div>";
}

/****** requirements ******/
function ctm_requirements() {

	$ctm_fail = false;

	if (version_compare($GLOBALS["wp_version"], "3.5.0", "<")) {
		add_action('admin_notices', 'ctm_WP_error');
		$ctm_fail = true;
	}

	if (version_compare(PHP_VERSION, "5.3", "<")) {
		add_action('admin_notices', 'ctm_PHP_error');
		$ctm_fail = true;
	}

	if ( !$ctm_fail ) {

		if ( is_admin() ) {

			function e_ctm_css($hook) {
				$hook_ctm = 'settings_page_call-tracking-metrics';
		    if ($hook != $hook_ctm) {
		      return;
		    } else {
					wp_register_style( 'c_t_m', plugins_url('css/c_t_m.min.css', __FILE__) );
					wp_enqueue_style( 'c_t_m' );
		    }
			}
			add_action( 'admin_enqueue_scripts', 'e_ctm_css' );

			function e_ctm_js($hook) {
    		if ( $hook == 'index.php') {
    			wp_enqueue_script( 'highcharts_mustache', plugins_url('js/highcharts-mustache.min.js', __FILE__) );
    		}
			}
			add_action( 'admin_enqueue_scripts', 'e_ctm_js' );

		}

		require_once( trailingslashit(dirname(__FILE__)) . 'ctm.php' );

	}

}//ctm_requirements

/****** settings link on plugin page ******/
function ctm_settings_link($links, $file) {
	$plugin = plugin_basename(__FILE__);
  if ($file != $plugin) {
  	return $links;
  } else {
  	$settings_link = '<a href="' . admin_url('options-general.php?page=call-tracking-metrics') . '">'  . esc_html(__('Settings', 'call-tracking-metrics')) . '</a>';
    array_unshift($links, $settings_link);
    return $links;
  }
}
add_filter('plugin_action_links', 'ctm_settings_link', 10, 2);

/****** uninstall cleanup ******/
function deactivate_ctm() {
	$ctm_oa = array( "ctm_api_key", "ctm_api_secret", "ctm_api_active_key", "ctm_api_active_secret", "ctm_api_auth_account", "ctm_api_connect_failed", "ctm_api_stats", "ctm_api_stats_expires", "ctm_api_dashboard_enabled", "ctm_api_tracking_enabled", "ctm_api_cf7_enabled", "ctm_api_gf_enabled", "call_track_account_script", "ctm_api_cf7_logs", "ctm_api_gf_logs" );
	foreach ($ctm_oa as $option) {
		delete_option( $option );
  }
  delete_transient( 'ctm_stats_cache' );
}
register_uninstall_hook(__FILE__, 'deactivate_ctm');

/****** run thru requirements ******/
if ( defined('ABSPATH') ) {
	ctm_requirements();
}
