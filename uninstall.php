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
 */

// If uninstall is not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

require_once dirname( __FILE__ ) . '/includes/WP_CSP.php';
require_once dirname( __FILE__ ) . '/admin/WP_CSP_Admin.php';

WP_CSP_Admin::plugin_uninstall();
