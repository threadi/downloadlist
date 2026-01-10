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
		// sleep for 2 seconds to allow to check if the css file has been generated.
		sleep( 2 );

		// get the filesystem object.
		$wp_filesystem = \DownloadListWithIcons\Plugin\Helper::get_wp_filesystem();

		// get the style file path.
		$style_path = \DownloadListWithIcons\Plugin\Helper::get_style_path();

		// get the date of the file there.
		$file_date_before = $wp_filesystem->mtime( $style_path );

		// generate CSS.
		\DownloadListWithIcons\Plugin\Helper::generate_css();

		// test it.
		$this->assertFileExists( $style_path );
		$this->assertNotEquals( $file_date_before, $wp_filesystem->mtime( $style_path ) );
	}
}
