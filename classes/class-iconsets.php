<?php
/**
 * File for general iconset-handling.
 *
 * @package download-list-block-with-icons
 */

namespace downloadlist;

// prevent also other direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Object for general iconset-handling.
 */
class Iconsets {
	/**
	 * Instance of this object.
	 *
	 * @var ?iconsets
	 */
	private static ?iconsets $instance = null;

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
	public static function get_instance(): iconsets {
		if ( ! static::$instance instanceof static ) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	/**
	 * Initialize the iconset.
	 *
	 * @return void
	 */
	public function init(): void {}

	/**
	 * Get all iconsets which are registered.
	 *
	 * @return array
	 */
	public function get_icon_sets(): array {
		$list = array();

		/**
		 * Register a single iconset through adding it to the list.
		 *
		 * The iconset must be an object extending Iconset_Base and implement Iconset.
		 *
		 * @since 3.0.0 Available since 3.0.0.
		 *
		 * @param array $list The list of iconsets.
		 */
		return apply_filters( 'downloadlist_register_iconset', $list );
	}

	/**
	 * Return the slugs of generic iconsets.
	 *
	 * @return array
	 */
	public function get_generic_sets_as_slug_array(): array {
		// define list.
		$list = array();

		// loop through the iconsets.
		foreach ( $this->get_icon_sets() as $iconset_obj ) {
			if ( $iconset_obj->is_generic() ) {
				$list[] = $iconset_obj->get_slug();
			}
		}

		// return results.
		return $list;
	}

	/**
	 * Get iconset based on slug.
	 *
	 * @param string $slug The slug of the iconset.
	 * @return Iconset_Base|false
	 */
	public function get_iconset_by_slug( string $slug ): Iconset_Base|false {
		foreach ( $this->get_icon_sets() as $iconset_obj ) {
			if ( $slug === $iconset_obj->get_slug() ) {
				return $iconset_obj;
			}
		}
		return false;
	}

	/**
	 * Return the default iconset.
	 *
	 * @return Iconset_Base|false
	 */
	public function get_default_iconset(): Iconset_Base|false {
		foreach ( $this->get_icon_sets() as $iconset_obj ) {
			if ( $iconset_obj->should_be_default() ) {
				return $iconset_obj;
			}
		}
		return false;
	}
}
