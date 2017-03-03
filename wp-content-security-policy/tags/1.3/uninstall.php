<?php
// If uninstall is not called from WordPress, exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

require_once( dirname(__file__).'/includes/wpCSPclass.php' );
require_once( dirname(__file__).'/admin/wpCSPadmin.php' );

wpCSPAdmin::plugin_uninstall();