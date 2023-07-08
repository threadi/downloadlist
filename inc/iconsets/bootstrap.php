<?php
/**
 * File which holds the list of possible bootstrap-icons.
 *
 * @package download-list-block-with-icons
 */

use downloadlist\iconsets\Bootstrap;

/**
 * Register the custom iconset.
 *
 * @param array $list The list of iconsets.
 * @return array
 */
function downloadlist_register_bootstrap_iconset( array $list ): array {
	$list[] = Bootstrap::get_instance();
	return $list;
}
add_filter( 'downloadlist_register_iconset', 'downloadlist_register_bootstrap_iconset', 10, 1 );
