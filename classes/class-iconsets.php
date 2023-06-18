<?php
/**
 * File for general iconset-handling.
 */

namespace downloadlist;

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

	public function init(): void {}

	/**
	 * Get all iconsets which are registered.
	 *
	 * @return array
	 */
	public function get_icon_sets(): array {
		return apply_filters( 'downloadlist_register_iconset', array() );
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
		foreach( $this->get_icon_sets() as $iconset_obj ) {
			if( $iconset_obj->is_generic() ) {
				$list[] = $iconset_obj->get_slug();
			}
		}

		// return results.
		return $list;
	}

	/**
	 * Get iconset based on slug.
	 *
	 * @param string $slug
	 * @return array
	 */
	public function get_icon_set_by_slug(string $slug): array {
		foreach( $this->get_icon_sets() as $iconset_obj ) {
			if( $slug === $iconset_obj->get_slug() ) {
				return array($iconset_obj);
			}
		}
		return array();
	}
}
