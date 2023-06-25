<?php
/**
 * File which holds the list of possible dashicons.
 */

use downloadlist\iconsets\Dashicons;

/**
 * Register the custom iconset.
 *
 * @param $list
 * @return array
 */
function downloadlist_register_dashicon_iconset( $list ): array {
	$list[] = Dashicons::get_instance();
	return $list;
}
add_filter( 'downloadlist_register_iconset', 'downloadlist_register_dashicon_iconset', 10, 1 );

