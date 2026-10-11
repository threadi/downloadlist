<?php
/**
 * Tests for class \DownloadListWithIcons\Plugin\Uninstaller.
 *
 * @package download-list-block-with-icons
 */

namespace DownloadListWithIcons\Tests\Unit\Plugin;

use DownloadListWithIcons\Tests\DownloadListTestCase;

/**
 * Object to test functions in the class \DownloadListWithIcons\Plugin\Uninstaller.
 */
class Uninstaller extends DownloadListTestCase {
	/**
	 * Install the plugin again after each test, as the following tests need it.
	 *
	 * @return void
	 */
	public function tear_down(): void {
		\DownloadListWithIcons\Plugin\Installer::get_instance()->activation();
		parent::tear_down();
	}

	/**
	 * Test that a reset keeps the download lists and removes the iconsets with their meta data.
	 *
	 * @return void
	 */
	public function test_reset_keeps_download_lists(): void {
		$list    = wp_insert_term( 'Unit test list', 'dl_icon_lists' );
		$iconset = get_term_by( 'slug', 'dashicons', 'dl_icon_set' );
		$this->assertIsArray( $list );
		$this->assertInstanceOf( \WP_Term::class, $iconset );

		\DownloadListWithIcons\Plugin\Uninstaller::get_instance()->run( false );

		$this->assertInstanceOf( \WP_Term::class, get_term( $list['term_id'], 'dl_icon_lists' ) );
		$this->assertNull( get_term( $iconset->term_id, 'dl_icon_set' ) );
		$this->assertEmpty( get_term_meta( $iconset->term_id, 'type', true ) );
	}

	/**
	 * Test that the uninstallation removes the download lists.
	 *
	 * @return void
	 */
	public function test_uninstall_removes_download_lists(): void {
		$list = wp_insert_term( 'Unit test list 2', 'dl_icon_lists' );
		$this->assertIsArray( $list );

		\DownloadListWithIcons\Plugin\Uninstaller::get_instance()->run();

		$this->assertNull( get_term( $list['term_id'], 'dl_icon_lists' ) );
	}
}
