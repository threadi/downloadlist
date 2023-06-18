<?php
/**
 * File to handle CLI-tasks.
 *
 * @package           downloadlist
 */

namespace downloadlist;

/**
 * Manage downloadlists via CLI.
 */
class Cli {
	/**
	 * Reset all settings of this plugin.
	 *
	 * @return void
	 */
	public function reset_plugin(): void {
		Uninstaller::get_instance()->run();
		Installer::get_instance()->activation();
	}

}
