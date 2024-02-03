<?php
/**
 * File which holds the list of possible custom sets.
 *
 * @package download-list-block-with-icons
 */

use downloadlist\iconsets\Custom;

/**
 * Register the custom iconset.
 *
 * @param array $iconset_list The list of iconsets.
 * @return array
 */
function downloadlist_register_custom_iconset( array $iconset_list ): array {
	// get all possible custom iconsets from db.
	$query     = array(
		'taxonomy'   => 'dl_icon_set',
		'hide_empty' => false,
		'meta_query' => array(
			'relation' => 'OR',
			array(
				'key' => 'type',
				'value' => 'custom'
			),
			array(
				'key' => 'type',
				'compare' => 'NOT EXISTS'
			)
		)
	);
	$icon_sets = new \WP_Term_Query( $query );
	if( ! empty($icon_sets->terms) ) {
		foreach ($icon_sets->get_terms() as $term) {
			$iconset_obj = new Custom();
			$iconset_obj->set_slug($term->slug);
			$iconset_list[] = $iconset_obj;
		}
	}
	else {
		// use initial custom iconset.
		$iconset_list[] = Custom::get_instance();
	}
	return $iconset_list;
}
add_filter( 'downloadlist_register_iconset', 'downloadlist_register_custom_iconset', 10, 1 );
