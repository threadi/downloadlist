<?php
/**
 * File which holds the list of possible custom sets.
 */

use downloadlist\iconsets\Custom;

/**
 * Register the custom iconset.
 *
 * @param $list
 * @return array
 */
function downloadlist_register_custom_iconset( $list ): array {
	$list[] = Custom::get_instance();
	return $list;
}
add_filter( 'downloadlist_register_iconset', 'downloadlist_register_custom_iconset', 10, 1 );
