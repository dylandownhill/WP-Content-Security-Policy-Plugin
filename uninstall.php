<?php
// If uninstall is not called from WordPress, exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

require_once( dirname(__file__).'/includes/WP_CSP.php' );
require_once( dirname(__file__).'/admin/WP_CSP_Admin.php' );

WP_CSP_Admin::plugin_uninstall();