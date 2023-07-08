<?php
/**
 * Tasks to run during uninstallation of this plugin.
 *
 * @package download-list-block-with-icons
 */

use downloadlist\Uninstaller;

// save the plugin-path.
const DL_PLUGIN = __FILE__;

// embed necessary files.
require_once 'inc/autoload.php';

Uninstaller::get_instance()->run();
