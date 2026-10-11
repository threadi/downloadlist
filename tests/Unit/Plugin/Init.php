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

	/**
	 * Test that a preview image is shown for files with preview, if enabled.
	 *
	 * @return void
	 */
	public function test_render_block_with_preview(): void {
		$attachment_id = self::factory()->attachment->create_upload_object( UNIT_TESTS_DATA_PLUGIN_DIR . '/test-image.png' );

		WP_Block_Supports::get_instance()::$block_to_render = array(
			'blockName' => 'downloadlist/list',
		);

		// without the option no preview is shown.
		$html = \DownloadListWithIcons\Plugin\Init::get_instance()->render_block( array( 'files' => array( array( 'id' => $attachment_id ) ) ) );
		$this->assertStringNotContainsString( 'downloadlist-preview', $html );
		$this->assertStringNotContainsString( 'has-preview', $html );

		// with the option the preview is shown with the given width.
		$html = \DownloadListWithIcons\Plugin\Init::get_instance()->render_block(
			array(
				'files'        => array( array( 'id' => $attachment_id ) ),
				'showPreview'  => true,
				'previewWidth' => 64,
			)
		);
		$this->assertStringContainsString( 'class="downloadlist-preview"', $html );
		$this->assertStringContainsString( 'width="64"', $html );
		$this->assertStringContainsString( 'loading="lazy"', $html );
		$this->assertStringContainsString( 'has-preview', $html );
	}

	/**
	 * Test the frame for icons and previews on the list.
	 *
	 * @return void
	 */
	public function test_render_block_with_media_frame(): void {
		$attachment_id = self::factory()->attachment->create_upload_object( UNIT_TESTS_DATA_PLUGIN_DIR . '/test-image.png' );

		WP_Block_Supports::get_instance()::$block_to_render = array(
			'blockName' => 'downloadlist/list',
		);

		// lists without frame options keep their markup.
		$html = \DownloadListWithIcons\Plugin\Init::get_instance()->render_block( array( 'files' => array( array( 'id' => $attachment_id ) ) ) );
		$this->assertStringNotContainsString( 'has-media-frame', $html );
		$this->assertStringNotContainsString( 'style="', $html );

		// with frame options the list gets the class and the CSS variables.
		$html = \DownloadListWithIcons\Plugin\Init::get_instance()->render_block(
			array(
				'files'            => array( array( 'id' => $attachment_id ) ),
				'mediaBorderWidth' => 1,
				'mediaBorderColor' => '#dddddd',
				'mediaPadding'     => 6,
			)
		);
		$this->assertStringContainsString( 'has-media-frame', $html );
		$this->assertStringContainsString( 'style="--downloadlist-media-size:48px;--downloadlist-media-border-width:1px;--downloadlist-media-border-color:#dddddd;--downloadlist-media-padding:6px;"', $html );
	}

	/**
	 * Test that files without preview keep their icon, if previews are enabled.
	 *
	 * @return void
	 */
	public function test_render_block_with_preview_for_file_without_preview(): void {
		$attachment_id = self::factory()->attachment->create_object(
			array(
				'file'           => 'archive.zip',
				'post_mime_type' => 'application/zip',
				'post_title'     => 'Archive',
			)
		);

		WP_Block_Supports::get_instance()::$block_to_render = array(
			'blockName' => 'downloadlist/list',
		);

		$html = \DownloadListWithIcons\Plugin\Init::get_instance()->render_block(
			array(
				'files'       => array( array( 'id' => $attachment_id ) ),
				'showPreview' => true,
			)
		);
		$this->assertStringNotContainsString( 'downloadlist-preview', $html );
		$this->assertStringNotContainsString( 'has-preview', $html );
		$this->assertStringContainsString( 'file_zip', $html );
	}

	/**
	 * Test that lists with the iconset of the former plugin "File-Preview" show previews.
	 *
	 * @return void
	 */
	public function test_render_block_with_former_preview_iconset(): void {
		$attachment_id = self::factory()->attachment->create_upload_object( UNIT_TESTS_DATA_PLUGIN_DIR . '/test-image.png' );

		WP_Block_Supports::get_instance()::$block_to_render = array(
			'blockName' => 'downloadlist/list',
		);

		$html = \DownloadListWithIcons\Plugin\Init::get_instance()->render_block(
			array(
				'files'   => array( array( 'id' => $attachment_id ) ),
				'iconset' => 'preview',
			)
		);
		$this->assertStringContainsString( 'class="downloadlist-preview"', $html );
	}

	/**
	 * Test that the generated preview of a PDF file is used (like WordPress saves it with Imagick and Ghostscript).
	 *
	 * @return void
	 */
	public function test_render_block_with_preview_of_pdf(): void {
		$attachment_id = self::factory()->attachment->create_object(
			array(
				'file'           => '2026/10/document.pdf',
				'post_mime_type' => 'application/pdf',
				'post_title'     => 'Document',
			)
		);

		// set the metadata WordPress saves after generating the preview of the first page.
		wp_update_attachment_metadata(
			$attachment_id,
			array(
				'sizes' => array(
					'full'      => array(
						'file'      => 'document-pdf.jpg',
						'width'     => 1088,
						'height'    => 1408,
						'mime-type' => 'image/jpeg',
					),
					'thumbnail' => array(
						'file'      => 'document-pdf-116x150.jpg',
						'width'     => 116,
						'height'    => 150,
						'mime-type' => 'image/jpeg',
					),
				),
			)
		);

		WP_Block_Supports::get_instance()::$block_to_render = array(
			'blockName' => 'downloadlist/list',
		);

		$html = \DownloadListWithIcons\Plugin\Init::get_instance()->render_block(
			array(
				'files'        => array( array( 'id' => $attachment_id ) ),
				'showPreview'  => true,
				'previewWidth' => 58,
			)
		);

		// the small preview is used (not the full size) with the height in the right proportion.
		$this->assertStringContainsString( 'document-pdf-116x150.jpg', $html );
		$this->assertStringContainsString( 'width="58" height="75"', $html );
		$this->assertStringContainsString( 'has-preview', $html );
	}

	/**
	 * Test that the cover image of an audio file is used as preview.
	 *
	 * @return void
	 */
	public function test_render_block_with_cover_of_audio_file(): void {
		$cover_id = self::factory()->attachment->create_upload_object( UNIT_TESTS_DATA_PLUGIN_DIR . '/test-image.png' );
		$audio_id = self::factory()->attachment->create_object(
			array(
				'file'           => '2026/10/song.mp3',
				'post_mime_type' => 'audio/mpeg',
				'post_title'     => 'Song',
			)
		);

		WP_Block_Supports::get_instance()::$block_to_render = array(
			'blockName' => 'downloadlist/list',
		);
		$attributes = array(
			'files'       => array( array( 'id' => $audio_id ) ),
			'showPreview' => true,
		);

		// without cover the icon is used.
		$html = \DownloadListWithIcons\Plugin\Init::get_instance()->render_block( $attributes );
		$this->assertStringNotContainsString( 'downloadlist-preview', $html );

		// with cover the cover is used.
		set_post_thumbnail( $audio_id, $cover_id );
		$html = \DownloadListWithIcons\Plugin\Init::get_instance()->render_block( $attributes );
		$this->assertStringContainsString( 'class="downloadlist-preview"', $html );
		$this->assertStringContainsString( 'has-preview', $html );
	}

	/**
	 * Test that the REST API returns the preview data for the block editor.
	 *
	 * @return void
	 */
	public function test_add_preview_data(): void {
		$image_id = self::factory()->attachment->create_upload_object( UNIT_TESTS_DATA_PLUGIN_DIR . '/test-image.png' );
		$zip_id   = self::factory()->attachment->create_object(
			array(
				'file'           => 'archive.zip',
				'post_mime_type' => 'application/zip',
			)
		);

		$files = \DownloadListWithIcons\Plugin\Init::get_instance()->add_preview_data(
			array(
				array(
					'id'   => $image_id,
					'mime' => 'image/png',
				),
				array(
					'id'   => $zip_id,
					'mime' => 'application/zip',
				),
			)
		);

		$this->assertSame( 'image', $files[0]['downloadlist_preview_type'] );
		$this->assertIsArray( $files[0]['downloadlist_preview']['thumbnail'] );
		$this->assertSame( 'none', $files[1]['downloadlist_preview_type'] );
		$this->assertFalse( $files[1]['downloadlist_preview']['thumbnail'] );
	}
}
