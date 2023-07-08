<?php
/**
 * File which holds the list of possible dashicons.
 *
 * @package download-list-block-with-icons
 */

use downloadlist\iconsets\Dashicons;

/**
 * Register the custom iconset.
 *
 * @param array $list The list of iconsets.
 * @return array
 */
function downloadlist_register_dashicon_iconset( array $list ): array {
	$list[] = Dashicons::get_instance();
	return $list;
}
add_filter( 'downloadlist_register_iconset', 'downloadlist_register_dashicon_iconset', 10, 1 );
