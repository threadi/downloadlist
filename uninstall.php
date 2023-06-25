<?php
/**
 * Tasks to run during uninstallation of this plugin.
 */

use downloadlist\Uninstaller;

Uninstaller::get_instance()->run();
