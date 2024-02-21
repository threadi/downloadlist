<?php
/**
 * Tasks to run during uninstallation of this plugin.
 *
 * @package download-list-block-with-icons
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// if uninstall.php is not called by WordPress, die.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

// do nothing if PHP-version is not 8.0 or newer.
if ( version_compare( PHP_VERSION, '8.0', '<' ) ) {
	return;
}

use downloadlist\Uninstaller;

// save the plugin-path.
const DL_PLUGIN = __FILE__;

// save the plugin-version.
const DL_VERSION = '@@VersionNumber@@';

// save transient-list-name.
const DL_TRANSIENT_LIST = 'downloadlist_transients';

// embed necessary files.
require_once 'inc/autoload.php';

// run installer.
Uninstaller::get_instance()->run();
