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
 * @param array $list The list of iconsets.
 * @return array
 */
function downloadlist_register_custom_iconset( array $list ): array {
	$list[] = Custom::get_instance();
	return $list;
}
add_filter( 'downloadlist_register_iconset', 'downloadlist_register_custom_iconset', 10, 1 );
