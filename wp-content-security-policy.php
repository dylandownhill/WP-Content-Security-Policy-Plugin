<?php
/**
 *  This file is part of WP Content Security Plugin.
 *
 *  Copyright 2015-2018 Dylan Downhill
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License
 *  as published by the Free Software Foundation; either version 2
 *  of the License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 *  ***
 *
 *  @package dylandownhill/wp-content-security-policy
 *  @license http://www.gnu.org/licenses/gpl-2.0.html
 *
 *  @wordpress-plugin
 *  Plugin Name: WP Content Security Policy Plugin
 *  Plugin URI: https://github.com/dylandownhill/WP-Content-Security-Policy-Plugin/
 *  Description: Setup, output, and log content security policy information.
 *  Author: Dylan Downhill
 *  Author URI: http://www.elixirinteractive.com
 *  Version: 2.3
 *  License: GNU General Public License v2 or later
 *  License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *  Text Domain: wp-typography
 *  Domain Path: /languages
 */

if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) === basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
	die( 'You can not access this page directly!' );
}

register_activation_hook( __FILE__,  array( 'WP_CSP_Admin', 'plugin_activation' ) );
register_deactivation_hook( __FILE__, array( 'WP_CSP_Admin', 'plugin_deactivation' ) );

require_once dirname( __FILE__ ) . '/includes/WP_CSP.php';
require_once dirname( __FILE__ ) . '/admin/WP_CSP_Admin.php';
