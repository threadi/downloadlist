<?php
/**
 * Tasks to run during uninstallation of this plugin.
 *
 * @package download-list-block-with-icons
 */

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
