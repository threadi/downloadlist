<?php
/**
 * Tests for class \DownloadListWithIcons\Plugin\Helper.
 *
 * @package download-list-block-with-icons
 */

namespace DownloadListWithIcons\Tests\Unit\Plugin;

use DownloadListWithIcons\Tests\DownloadListTestCase;

/**
 * Object to test functions in the class \DownloadListWithIcons\Plugin\Helper.
 */
class Helper extends DownloadListTestCase {
	/**
	 * Test if the returning variable is a string.
	 *
	 * @return void
	 */
	public function test_get_type_and_subtype_from_mimetype(): void {
		$mimetype = \DownloadListWithIcons\Plugin\Helper::get_type_and_subtype_from_mimetype( 'image/png' );
		$this->assertIsArray( $mimetype );
		$this->assertNotEmpty( $mimetype );
	}

	/**
	 * Test if the returning variable is a string.
	 *
	 * @return void
	 */
	public function test_get_failed_type_and_subtype_from_mimetype(): void {
		$mimetype = \DownloadListWithIcons\Plugin\Helper::get_type_and_subtype_from_mimetype( '' );
		$this->assertIsArray( $mimetype );
		$this->assertEmpty( $mimetype );
	}
}
