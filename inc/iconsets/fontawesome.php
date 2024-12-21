<?php
/**
 * File which register the fontawesome iconset.
 *
 * @package download-list-block-with-icons
 */

use downloadlist\iconsets\Fontawesome;

/**
 * Register the fontawesome iconset.
 *
 * @param array $iconset_list The list of iconsets.
 * @return array
 */
function downloadlist_register_fontawesome_iconset( array $iconset_list ): array {
	$iconset_list[] = Fontawesome::get_instance();
	return $iconset_list;
}
add_filter( 'downloadlist_register_iconset', 'downloadlist_register_fontawesome_iconset', 10, 1 );
