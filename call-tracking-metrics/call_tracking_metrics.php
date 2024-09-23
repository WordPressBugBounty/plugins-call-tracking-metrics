<?php
if ( ! function_exists( 'is_plugin_active' ) ) {
     include_once( ABSPATH . '/wp-admin/includes/plugin.php' );
}
add_action( 'init', 'activate_new_ctm' );
function activate_new_ctm() {
	if ( function_exists('activate_plugin') ) { 
		activate_plugin( trailingslashit(dirname(__FILE__)) . 'call-tracking-metrics.php' );
	}
}
