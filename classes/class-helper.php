<?php
/**
 * File with helper-functions for this plugin.
 *
 * @package           downloadlist
 */

namespace downloadlist;

use WP_Term_Query;

/**
 * Helper-method.
 */
class Helper {

	/**
	 * Return possible mime-types.
	 *
	 * @return array
	 */
	public static function get_mime_types(): array {
		// get the WordPress-list of mime-types.
		$mime_types = wp_get_mime_types();

		// add general mime-types.
		$mime_types['application'] = 'application';
		$mime_types['audio'] = 'audio';
		$mime_types['image'] = 'image';
		$mime_types['video'] = 'video';
		ksort($mime_types);

		// return the list.
		return $mime_types;
	}

	/**
	 * Get path to the generated style-file of this plugin.
	 *
	 * @return string
	 */
	public static function get_style_path(): string {
		$upload_dir = wp_get_upload_dir();
		return trailingslashit($upload_dir['basedir']).'downloadlist-style.css';
	}

	/**
	 * Get path to the generated style-file of this plugin.
	 *
	 * @return string
	 */
	public static function get_style_url(): string {
		$upload_dir = wp_get_upload_dir();
		return trailingslashit($upload_dir['baseurl']).'downloadlist-style.css';
	}

	/**
	 * Set given iconset as default.
	 *
	 * @param int $term_id
	 * @return void
	 */
	public static function set_iconset_default( int $term_id ): void {
		// delete all default-marker for icon-sets.
		$query = array(
			'taxonomy' => 'dl_icon_set',
			'hide_empty' => false,
			'meta_query' => array(
				array(
					''
				)
			)
		);
		$results = new WP_Term_Query( $query );
		foreach( $results->get_terms() as $term ) {
			delete_term_meta( $term->term_id, 'default' );
		}

		// mark this as default icon-set.
		update_term_meta( $term_id, 'default', 1 );
	}
}
