<?php
/*
Plugin Name: WP Content Security Policy Plugin
Plugin URI:  http://URI_Of_Page_Describing_Plugin_and_Updates
Description: Setup, output, and log content security policy information.
Version:     2.0
Author:      Dylan Downhill
Author URI:  http://www.elixirinteractive.com
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Domain Path: /languages
*/

if(!empty($_SERVER['SCRIPT_FILENAME']) && basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
	die('You can not access this page directly!');
}
	
register_activation_hook( __FILE__,  array( 'wpCSPAdmin','plugin_activation' ) );
register_deactivation_hook( __FILE__, array( 'wpCSPAdmin','plugin_deactivation' ) );


require_once( dirname(__file__).'/includes/wpCSPclass.php' );
require_once( dirname(__file__).'/admin/wpCSPadmin.php' );
