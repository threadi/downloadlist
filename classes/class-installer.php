<?php
/**
 * File to handle installer-tasks.
 *
 * @package           downloadlist
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
		// set taxonomy-default-marker.
		if( !get_option( 'downloadlistTaxonomyDefaults', false ) ) {
			update_option( 'downloadlistTaxonomyDefaults', 0, true );
		}
	}

	/**
	 * Run during deactivation of this plugin.
	 *
	 * @return void
	 */
	public function deactivation(): void {

	}

}
