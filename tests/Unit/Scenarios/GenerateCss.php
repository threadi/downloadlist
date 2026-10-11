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
class GenerateCss extends DownloadListTestCase {
	/**
	 * Prepare the test environment.
	 *
	 * @return void
	 */
	public function set_up(): void {
		parent::set_up();

		// add generic iconset.
		\DownloadListWithIcons\Plugin\Helper::add_generic_iconsets();
	}

	/**
	 * Test if the returning variable is a string.
	 *
	 * @return void
	 */
	public function test_generate_complete_css(): void {
		// get the style file path.
		$style_path = \DownloadListWithIcons\Plugin\Helper::get_style_path();

		// remove the file, so it is sure that it is generated again.
		wp_delete_file( $style_path );
		$this->assertFileDoesNotExist( $style_path );

		// generate CSS.
		\DownloadListWithIcons\Plugin\Helper::generate_css();

		// test it.
		$this->assertFileExists( $style_path );
		$this->assertNotEmpty( file_get_contents( $style_path ) );
	}
}
