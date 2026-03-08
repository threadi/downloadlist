<?php
/**
 * File, which register the themify iconset.
 *
 * @package download-list-block-with-icons
 */

// prevent direct access.
defined( 'ABSPATH' ) || exit;

use DownloadListWithIcons\Iconsets\Iconset_Base;
use DownloadListWithIcons\Iconsets\Iconsets\Themify;

/**
 * Register the themify iconset.
 *
 * @param array<int,Iconset_Base> $iconset_list The list of iconsets.
 * @return array<int,Iconset_Base>
 */
function downloadlist_register_themify_iconset( array $iconset_list ): array {
	$iconset_list[] = Themify::get_instance();
	return $iconset_list;
}
add_filter( 'downloadlist_register_iconset', 'downloadlist_register_themify_iconset', 10, 1 );
