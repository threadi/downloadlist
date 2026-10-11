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

	/**
	 * Test that a download attribute from a hook is escaped as complete attribute.
	 *
	 * @return void
	 */
	public function test_render_block_escapes_download_attribute(): void {
		$attachment_id = self::factory()->attachment->create_upload_object( UNIT_TESTS_DATA_PLUGIN_DIR . '/test-image.png' );

		WP_Block_Supports::get_instance()::$block_to_render = array(
			'blockName' => 'downloadlist/list',
		);

		// set a file name with a quote.
		$callback = function () {
			return 'download="my"file.png"';
		};
		add_filter( 'downloadlist_link_download_attribute', $callback );
		$html = \DownloadListWithIcons\Plugin\Init::get_instance()->render_block( array( 'files' => array( array( 'id' => $attachment_id ) ) ) );
		remove_filter( 'downloadlist_link_download_attribute', $callback );

		$this->assertStringContainsString( 'download="my&quot;file.png"', $html );
	}

	/**
	 * Test that the file format label works for file types without own label.
	 *
	 * @return void
	 */
	public function test_render_block_with_format_label_of_unknown_type(): void {
		$attachment_id = self::factory()->attachment->create_object(
			array(
				'file'           => 'document.odt',
				'post_mime_type' => 'application/vnd.oasis.opendocument.text',
				'post_title'     => 'Document',
			)
		);

		WP_Block_Supports::get_instance()::$block_to_render = array(
			'blockName' => 'downloadlist/list',
		);

		// warnings would let this test fail.
		$html = \DownloadListWithIcons\Plugin\Init::get_instance()->render_block(
			array(
				'files'               => array( array( 'id' => $attachment_id ) ),
				'showFileFormatLabel' => true,
			)
		);
		$this->assertStringContainsString( 'application/vnd.oasis.opendocument.text', $html );
	}

	/**
	 * Test that invalid entries in the list of files are ignored.
	 *
	 * @return void
	 */
	public function test_render_block_ignores_invalid_files(): void {
		WP_Block_Supports::get_instance()::$block_to_render = array(
			'blockName' => 'downloadlist/list',
		);

		$html = \DownloadListWithIcons\Plugin\Init::get_instance()->render_block( array( 'files' => array( 'abc', array( 'title' => 'x' ), array( 'id' => 0 ) ) ) );
		$this->assertIsString( $html );
		$this->assertStringNotContainsString( '<li', $html );
	}
}
