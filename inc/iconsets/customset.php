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
	$iconset_list[] = Custom::get_instance();
	return $iconset_list;
}
add_filter( 'downloadlist_register_iconset', 'downloadlist_register_custom_iconset', 10, 1 );
