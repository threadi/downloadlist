<?php
/**
 * File for general iconset-handling.
 *
 * @package download-list-block-with-icons
 */

namespace DownloadListWithIcons\Iconsets;

// prevent direct access.
defined( 'ABSPATH' ) || exit;

use WP_Post;
use WP_Query;

/**
 * Object for general iconset-handling.
 */
class Iconsets {
	/**
	 * Instance of this object.
	 *
	 * @var ?Iconsets
	 */
	private static ?Iconsets $instance = null;

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
	public static function get_instance(): Iconsets {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
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
	 * @return array<int,Iconset_Base>
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
		 * @param array<int,Iconset_Base> $list The list of iconsets.
		 */
		return apply_filters( 'downloadlist_register_iconset', $list );
	}

	/**
	 * Return the slugs of generic iconsets.
	 *
	 * @return array<int,string>
	 */
	public function get_generic_sets_as_slug_array(): array {
		// define list.
		$list = array();

		// loop through the iconsets.
		foreach ( $this->get_icon_sets() as $iconset_obj ) {
			// bail if this is not a generic iconset.
			if ( ! $iconset_obj->is_generic() ) {
				continue;
			}

			// add the iconset to the list.
			$list[] = $iconset_obj->get_slug();
		}

		// return results.
		return $list;
	}

	/**
	 * Return generic custom post types.
	 *
	 * @return array<int,int>
	 */
	public function get_generic_sets_cpts(): array {
		$query = array(
			'post_type'      => 'dl_icons',
			'post_status'    => array( 'any', 'trash' ),
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'     => 'generic-downloadlist',
					'compare' => 'EXISTS',
				),
			),
		);
		$posts = new WP_Query( $query );

		// bail on no results.
		if ( 0 === $posts->found_posts ) {
			return array();
		}

		// get the resulting entries.
		$list = array();
		foreach ( $posts->get_posts() as $post_id ) {
			if ( $post_id instanceof WP_Post ) {
				continue;
			}
			$list[] = $post_id;
		}
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
			// bail if it does not match.
			if ( $slug !== $iconset_obj->get_slug() ) {
				continue;
			}

			// return this object as it matches the slug.
			return $iconset_obj;
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
			// bail if this should not be default.
			if ( ! $iconset_obj->should_be_default() ) {
				continue;
			}

			// return the default iconset.
			return $iconset_obj;
		}
		return false;
	}
}
