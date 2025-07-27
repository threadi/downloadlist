<?php
/**
 * File to handle installer-tasks.
 *
 * @package download-list-block-with-icons
 */

namespace DownloadListWithIcons\Plugin;

// prevent direct access.

defined( 'ABSPATH' ) || exit;

/**
 * Object to handle installer-tasks.
 */
class Installer {

	/**
	 * Instance of this object.
	 *
	 * @var ?Installer
	 */
	private static ?Installer $instance = null;

	/**
	 * Constructor for Init-Handler.
	 */
	private function __construct() {}

	/**
	 * Prevent cloning of this object.
	 *
	 * @return void
	 */
	private function __clone() { }

	/**
	 * Return the instance of this Singleton object.
	 */
	public static function get_instance(): Installer {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Run during installation/activation of this plugin.
	 *
	 * @return void
	 */
	public function activation(): void {
		if ( ! get_option( 'downloadlistVersion', false ) ) {
			add_option( 'downloadlistVersion', DL_VERSION, '', true );
		}

		// initialize our own post-type and taxonomies during installation.
		Init::get_instance()->register_posttype();
		Taxonomies::get_instance()->init();

		// add generic iconset.
		Helper::add_generic_iconsets();

		// generate icons and styles.
		Helper::regenerate_icons();
		Helper::generate_css();
	}

	/**
	 * Run during deactivation of this plugin.
	 *
	 * @return void
	 */
	public function deactivation(): void {}
}
