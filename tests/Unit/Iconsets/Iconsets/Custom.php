<?php
/**
 * Tests for class \DownloadListWithIcons\Iconsets\Iconsets\Custom.
 *
 * @package download-list-block-with-icons
 */

namespace DownloadListWithIcons\Tests\Unit\Plugin;

use DownloadListWithIcons\Tests\DownloadListTestCase;

/**
 * Object to test functions in the class \DownloadListWithIcons\Iconsets\Iconsets\Custom.
 */
class Custom extends DownloadListTestCase {
	/**
	 * The object.
	 *
	 * @var \DownloadListWithIcons\Iconsets\Iconsets\Custom
	 */
	private \DownloadListWithIcons\Iconsets\Iconsets\Custom $object;

	/**
	 * Prepare the test environment.
	 *
	 * @return void
	 */
	public function set_up(): void {
		$this->object = new \DownloadListWithIcons\Iconsets\Iconsets\Custom();
	}

	/**
	 * Test if the returning variable is a string.
	 *
	 * @return void
	 */
	public function test_get_label(): void {
		$label = $this->object->get_label();
		$this->assertIsString( $label );
		$this->assertNotEmpty( $label );
	}

	/**
	 * Test if the returning variable is a boolean.
	 *
	 * @return void
	 */
	public function test_has_label(): void {
		$has_label = $this->object->has_label();
		$this->assertIsBool( $has_label );
		$this->assertTrue( $has_label );
	}

	/**
	 * Test if the returning variable is a string.
	 *
	 * @return void
	 */
	public function test_get_type(): void {
		$type = $this->object->get_type();
		$this->assertIsString( $type );
		$this->assertNotEmpty( $type );
	}

	/**
	 * Test if the returning variable is a string.
	 *
	 * @return void
	 */
	public function test_get_slug(): void {
		$slug = $this->object->get_slug();
		$this->assertIsString( $slug );
		$this->assertNotEmpty( $slug );
	}

	/**
	 * Test if the returning variable is a boolean.
	 *
	 * @return void
	 */
	public function test_should_be_default(): void {
		$should_be_default = $this->object->should_be_default();
		$this->assertIsBool( $should_be_default );
		$this->assertFalse( $should_be_default );
	}

	/**
	 * Test if the returning variable is a boolean.
	 *
	 * @return void
	 */
	public function test_is_generic(): void {
		$is_generic = $this->object->is_generic();
		$this->assertIsBool( $is_generic );
		$this->assertFalse( $is_generic );
	}

	/**
	 * Test if the returning variable is a boolean.
	 *
	 * @return void
	 */
	public function test_is_gfx(): void {
		$is_gfx = $this->object->is_gfx();
		$this->assertIsBool( $is_gfx );
		$this->assertTrue( $is_gfx );
	}

	/**
	 * Test if the returning variable is an array.
	 *
	 * @return void
	 */
	public function test_get_file_types(): void {
		$file_types = $this->object->get_file_types();
		$this->assertIsArray( $file_types );
		$this->assertEmpty( $file_types );
	}

	/**
	 * Test if the returning variable is an array.
	 *
	 * @return void
	 */
	public function test_get_icons(): void {
		$icons = $this->object->get_icons();
		$this->assertIsArray( $icons );
		$this->assertEmpty( $icons );
	}

	/**
	 * Test if the returning variable is a string.
	 *
	 * @return void
	 */
	public function test_get_style_for_file(): void {
		// create the test attachment.
		$attachment_id = self::factory()->attachment->create_upload_object( UNIT_TESTS_DATA_PLUGIN_DIR . '/test-image.png' );

		// test it.
		$style = $this->object->get_style_for_file( $attachment_id );
		$this->assertIsString( $style );
		$this->assertEmpty( $style );
	}

	/**
	 * Test if the returning variable is an array.
	 *
	 * @return void
	 */
	public function test_get_style_files(): void {
		$styles = $this->object->get_style_files();
		$this->assertIsArray( $styles );
		$this->assertEmpty( $styles );
	}

	/**
	 * Test if the returning variable is a string.
	 *
	 * @return void
	 */
	public function test_get_style_for_filetype(): void {
		$style = $this->object->get_style_for_filetype( 0, '', '' );
		$this->assertIsString( $style );
		$this->assertEmpty( $style );
	}

}
