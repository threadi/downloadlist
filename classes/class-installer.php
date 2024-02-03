<?php
/**
 * File to handle installer-tasks.
 *
 * @package download-list-block-with-icons
 */

namespace downloadlist;

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
		if ( ! static::$instance instanceof static ) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	/**
	 * Run during installation/activation of this plugin.
	 *
	 * @return void
	 */
	public function activation(): void {
		// initialize our own post-type and taxonomies during installation.
		downloadlist_add_position_posttype();
		downloadlist_add_taxonomies();

		// add generic iconset.
		helper::add_generic_iconsets();

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
