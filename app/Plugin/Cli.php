<?php
/**
 * File to handle CLI-tasks of this plugin.
 *
 * @package download-list-block-with-icons
 */

namespace DownloadListWithIcons\Plugin;

// prevent direct access.
defined( 'ABSPATH' ) || exit;

/**
 * Manage download-lists-with-icons via CLI.
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

		// return ok-message.
		\WP_CLI::success( __( 'Plugin have been reset.', 'download-list-block-with-icons' ) );
	}

	/**
	 * Regenerate the styles of configured iconsets.
	 *
	 * @return void
	 * @noinspection PhpUnused
	 */
	public function generate_styles(): void {
		Helper::generate_css();

		// return ok-message.
		\WP_CLI::success( __( 'Styles have been generated.', 'download-list-block-with-icons' ) );
	}

	/**
	 * Regenerate media files used by this plugin as icons with the configured sizes.
	 *
	 * @return void
	 * @noinspection PhpUnused
	 */
	public function regenerate_icons(): void {
		Helper::regenerate_icons();

		// return ok-message.
		\WP_CLI::success( __( 'Icons have been generated.', 'download-list-block-with-icons' ) );
	}

	/**
	 * Inherit settings to all download list blocks.
	 *
	 * @return void
	 */
	public function inherit_settings(): void {
		Settings::get_instance()->inherit_settings_to_blocks();

		// return ok-message.
		\WP_CLI::success( __( 'Settings have been saved on all download list blocks.', 'download-list-block-with-icons' ) );
	}
}
