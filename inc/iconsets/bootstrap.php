<?php
/**
 * File which register the bootstrap iconset.
 *
 * @package download-list-block-with-icons
 */

use downloadlist\iconsets\Bootstrap;

/**
 * Register the bootstrap iconset.
 *
 * @param array $iconset_list The list of iconsets.
 * @return array
 */
function downloadlist_register_bootstrap_iconset( array $iconset_list ): array {
	$iconset_list[] = Bootstrap::get_instance();
	return $iconset_list;
}
add_filter( 'downloadlist_register_iconset', 'downloadlist_register_bootstrap_iconset', 10, 1 );
