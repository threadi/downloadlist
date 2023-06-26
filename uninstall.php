<?php
/**
 * Tasks to run during uninstallation of this plugin.
 *
 * @package download-list-block-with-icons
 */

use downloadlist\Uninstaller;

Uninstaller::get_instance()->run();
