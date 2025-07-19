<?php
/**
 * File which register the bootstrap iconset.
 *
 * @package download-list-block-with-icons
 */

// prevent direct access.
defined( 'ABSPATH' ) || exit;

use DownloadListWithIcons\Iconsets\Iconset_Base;
use DownloadListWithIcons\Iconsets\Iconsets\Bootstrap;

/**
 * Register the bootstrap iconset.
 *
 * @param array<int,Iconset_Base> $iconset_list The list of iconsets.
 * @return array<int,Iconset_Base>
 */
function downloadlist_register_bootstrap_iconset( array $iconset_list ): array {
	$iconset_list[] = Bootstrap::get_instance();
	return $iconset_list;
}
add_filter( 'downloadlist_register_iconset', 'downloadlist_register_bootstrap_iconset', 10, 1 );
