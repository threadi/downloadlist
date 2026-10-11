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
	 * Removes custom icons, iconsets and the titles and descriptions of files for download lists.
	 *
	 * ## OPTIONS
	 *
	 * [--yes]
	 * : Reset without asking for confirmation.
	 *
	 * @param array<int,string>    $args List of arguments.
	 * @param array<string,string> $assoc_args List of options.
	 *
	 * @return void
	 * @noinspection PhpUnused
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function reset_plugin( array $args = array(), array $assoc_args = array() ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundBeforeLastUsed
		\WP_CLI::confirm( __( 'This removes all custom icons, iconsets and the titles and descriptions of files for download lists. Do you want to reset the plugin?', 'download-list-block-with-icons' ), $assoc_args );

		Uninstaller::get_instance()->run( false );
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
