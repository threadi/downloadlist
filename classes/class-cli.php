<?php
/**
 * File to handle CLI-tasks.
 *
 * @package download-list-block-with-icons
 */

namespace downloadlist;

// prevent direct access.
defined( 'ABSPATH' ) || exit;

/**
 * Manage download-lists via CLI.
 */
class Cli {
	/**
	 * Reset all settings of this plugin.
	 *
	 * @return void
	 * @noinspection PhpUnused
	 */
	public function reset_plugin(): void {
		Uninstaller::get_instance()->run();
		Installer::get_instance()->activation();
	}

	/**
	 * Regenerate the styles of configured iconsets.
	 *
	 * @return void
	 * @noinspection PhpUnused
	 */
	public function generate_styles(): void {
		Helper::generate_css();
	}

	/**
	 * Regenerate media files used by this plugin as icons with the configured sizes.
	 *
	 * @return void
	 * @noinspection PhpUnused
	 */
	public function regenerate_icons(): void {
		Helper::regenerate_icons();
	}
}
