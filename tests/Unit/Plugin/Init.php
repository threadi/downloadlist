<?php
/**
 * Tests for class \DownloadListWithIcons\Plugin\Init.
 *
 * @package download-list-block-with-icons
 */

namespace DownloadListWithIcons\Tests\Unit\Plugin;

use DownloadListWithIcons\Tests\DownloadListTestCase;
use WP_Block_Supports;

/**
 * Object to test functions in the class \DownloadListWithIcons\Plugin\Init.
 */
class Init extends DownloadListTestCase {

	/**
	 * Test if the returning variable is a string.
	 *
	 * @return void
	 */
	public function test_render_block_without_settings(): void {
		$html = \DownloadListWithIcons\Plugin\Init::get_instance()->render_block( array() );
		$this->assertIsString( $html );
		$this->assertEmpty( $html );
	}

	/**
	 * Test if the returning variable is a string.
	 *
	 * @return void
	 */
	public function test_render_block_with_settings(): void {
		// create the test attachment.
		$attachment_id = self::factory()->attachment->create_upload_object( UNIT_TESTS_DATA_PLUGIN_DIR . '/test-image.png' );

		// get mime infos.
		$mime_type = get_post_mime_type( $attachment_id );

		// get its parts.
		$mime_type_parts = explode( '/', $mime_type );

		// set settings for a simple block.
		$settings = array(
			'files' => array(
				array(
					'id' => $attachment_id,
				)
			)
		);

		// set a block.
		WP_Block_Supports::get_instance()::$block_to_render = array(
			'blockName' => 'downloadlist/list',
		);

		// test it.
		$html = \DownloadListWithIcons\Plugin\Init::get_instance()->render_block( $settings );
		$this->assertIsString( $html );
		$this->assertNotEmpty( $html );
		$this->assertStringContainsString( 'attachment-' . $attachment_id, $html );
		$this->assertStringContainsString( 'file_' . $mime_type_parts[0] , $html );
		$this->assertStringContainsString( 'file_' . $mime_type_parts[1] , $html );
	}
}
