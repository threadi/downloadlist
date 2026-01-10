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
	 * Test if the returning variable is a string.
	 *
	 * @return void
	 */
	public function test_add_generic_iconsets(): void {
		// get the generic iconsets before.
		$generic_iconsets_before = \DownloadListWithIcons\Iconsets\Iconsets::get_instance()->get_generic_sets_cpts();

		// generate them.
		\DownloadListWithIcons\Plugin\Helper::add_generic_iconsets();

		// get the generic iconsets after.
		$generic_iconsets_after = \DownloadListWithIcons\Iconsets\Iconsets::get_instance()->get_generic_sets_cpts();

		// test it.
		$this->assertNotEquals( $generic_iconsets_before, $generic_iconsets_after );
	}
}
