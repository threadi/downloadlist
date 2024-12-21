<?php
/**
 * File which register the dashicons iconset.
 *
 * @package download-list-block-with-icons
 */

use downloadlist\iconsets\Dashicons;

/**
 * Register the dashicons iconset.
 *
 * @param array $iconset_list The list of iconsets.
 * @return array
 */
function downloadlist_register_dashicons_iconset( array $iconset_list ): array {
	$iconset_list[] = Dashicons::get_instance();
	return $iconset_list;
}
add_filter( 'downloadlist_register_iconset', 'downloadlist_register_dashicons_iconset', 10, 1 );
