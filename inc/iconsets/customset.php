<?php
/**
 * File which register the custom iconset.
 *
 * @package download-list-block-with-icons
 */

// prevent direct access.
defined( 'ABSPATH' ) || exit;

use DownloadListWithIcons\Iconsets\Iconset_Base;
use DownloadListWithIcons\Iconsets\Iconsets\Custom;

/**
 * Register the custom iconset.
 *
 * @param array<int,Iconset_Base> $iconset_list The list of iconsets.
 * @return array<int,Iconset_Base>
 */
function downloadlist_register_custom_iconset( array $iconset_list ): array {
	// get all possible custom iconsets from db.
	$query     = array(
		'taxonomy'   => 'dl_icon_set',
		'hide_empty' => false,
		'meta_query' => array(
			'relation' => 'OR',
			array(
				'key'   => 'type',
				'value' => 'custom',
			),
			array(
				'key'     => 'type',
				'compare' => 'NOT EXISTS',
			),
		),
	);
	$icon_sets = new WP_Term_Query( $query );

	// get the results.
	$terms = $icon_sets->get_terms();

	if ( is_array( $terms ) ) {
		foreach ( $terms as $term ) {
			// bail if item is not a term.
			if ( ! $term instanceof WP_Term ) {
				continue;
			}

			// create object.
			$iconset_obj = new Custom();
			$iconset_obj->set_slug( $term->slug );

			// add it to the list.
			$iconset_list[] = $iconset_obj;
		}
	} else {
		// use only the initial custom iconset.
		$iconset_list[] = Custom::get_instance();
	}

	// return the resulting list of custom iconsets.
	return $iconset_list;
}
add_filter( 'downloadlist_register_iconset', 'downloadlist_register_custom_iconset', 10, 1 );
