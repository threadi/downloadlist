<?php
/**
 * Test for a single scenario.
 *
 * @package download-list-block-with-icons
 */

namespace DownloadListWithIcons\Tests\Unit\Scenarios;

use DownloadListWithIcons\Tests\DownloadListTestCase;

/**
 * Object to test a single scenario.
 */
class AddGenericIconsets extends DownloadListTestCase {
	/**
	 * Test that missing generic icon entries are added and existing ones are not added twice.
	 *
	 * @return void
	 */
	public function test_add_generic_iconsets(): void {
		// remove the generic icon entries.
		foreach ( \DownloadListWithIcons\Iconsets\Iconsets::get_instance()->get_generic_sets_cpts() as $post_id ) {
			wp_delete_post( $post_id, true );
		}
		$this->assertEmpty( \DownloadListWithIcons\Iconsets\Iconsets::get_instance()->get_generic_sets_cpts() );

		// generate them.
		\DownloadListWithIcons\Plugin\Helper::add_generic_iconsets();
		$generic_iconsets = \DownloadListWithIcons\Iconsets\Iconsets::get_instance()->get_generic_sets_cpts();
		$this->assertNotEmpty( $generic_iconsets );

		// generate them again: nothing must be added.
		\DownloadListWithIcons\Plugin\Helper::add_generic_iconsets();
		$this->assertEquals( $generic_iconsets, \DownloadListWithIcons\Iconsets\Iconsets::get_instance()->get_generic_sets_cpts() );
	}
}
