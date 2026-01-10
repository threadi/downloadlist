<?php
/**
 * Tests for class \DownloadListWithIcons\Iconsets\Iconsets.
 *
 * @package download-list-block-with-icons
 */

namespace DownloadListWithIcons\Tests\Unit\Plugin;

use DownloadListWithIcons\Tests\DownloadListTestCase;

/**
 * Object to test functions in the class \DownloadListWithIcons\Iconsets\Iconsets.
 */
class Iconsets extends DownloadListTestCase {
	/**
	 * Test if the returning variable is an array.
	 *
	 * @return void
	 */
	public function test_get_iconsets(): void {
		$iconsets = \DownloadListWithIcons\Iconsets\Iconsets::get_instance()->get_icon_sets();
		$this->assertIsArray( $iconsets );
		$this->assertNotEmpty( $iconsets );
		$this->assertCount( 3, $iconsets );
		$this->assertArrayHasObjectOfType( 'DownloadListWithIcons\Iconsets\Iconset_Base', $iconsets );
	}

	/**
	 * Test if the returning variable is an object.
	 *
	 * @return void
	 */
	public function test_get_bootstrap_iconset_by_slug(): void {
		$iconset = \DownloadListWithIcons\Iconsets\Iconsets::get_instance()->get_iconset_by_slug( 'bootstrap' );
		$this->assertIsObject( $iconset );
		$this->assertInstanceOf( 'DownloadListWithIcons\Iconsets\Iconsets\Bootstrap', $iconset );
	}

	/**
	 * Test if the returning variable is an object.
	 *
	 * @return void
	 */
	public function test_get_dashicons_iconset_by_slug(): void {
		$iconset = \DownloadListWithIcons\Iconsets\Iconsets::get_instance()->get_iconset_by_slug( 'dashicons' );
		$this->assertIsObject( $iconset );
		$this->assertInstanceOf( 'DownloadListWithIcons\Iconsets\Iconsets\Dashicons', $iconset );
	}

	/**
	 * Test if the returning variable is an object.
	 *
	 * @return void
	 */
	public function test_get_fontawesome_iconset_by_slug(): void {
		$iconset = \DownloadListWithIcons\Iconsets\Iconsets::get_instance()->get_iconset_by_slug( 'fontawesome' );
		$this->assertIsObject( $iconset );
		$this->assertInstanceOf( 'DownloadListWithIcons\Iconsets\Iconsets\Fontawesome', $iconset );
	}

	/**
	 * Test if the returning variable is an object.
	 *
	 * @return void
	 */
	public function test_get_default_iconset(): void {
		$iconset = \DownloadListWithIcons\Iconsets\Iconsets::get_instance()->get_default_iconset();
		$this->assertIsObject( $iconset );
		$this->assertInstanceOf( 'DownloadListWithIcons\Iconsets\Iconset_Base', $iconset );
	}

	/**
	 * Test if the returning variable is an object.
	 *
	 * @return void
	 */
	public function test_change_default_iconset_to_bootstrap(): void {
		// get the term ID of bootstrap iconset.
		$term_obj = get_term_by( 'slug', 'bootstrap', 'dl_icon_set' );
		$this->assertIsObject( $term_obj );
		$this->assertInstanceOf( 'WP_Term', $term_obj );

		// change it.
		\DownloadListWithIcons\Iconsets\Iconsets::get_instance()->set_default_iconset( $term_obj->term_id );

		// test it.
		$iconset = \DownloadListWithIcons\Iconsets\Iconsets::get_instance()->get_default_iconset();
		$this->assertIsObject( $iconset );
		$this->assertInstanceOf( 'DownloadListWithIcons\Iconsets\Iconsets\Bootstrap', $iconset );
	}

	/**
	 * Test if the returning variable is an object.
	 *
	 * @return void
	 */
	public function test_change_default_iconset_to_dashicons(): void {
		// get the term ID of bootstrap iconset.
		$term_obj = get_term_by( 'slug', 'dashicons', 'dl_icon_set' );
		$this->assertIsObject( $term_obj );
		$this->assertInstanceOf( 'WP_Term', $term_obj );

		// change it.
		\DownloadListWithIcons\Iconsets\Iconsets::get_instance()->set_default_iconset( $term_obj->term_id );

		// test it.
		$iconset = \DownloadListWithIcons\Iconsets\Iconsets::get_instance()->get_default_iconset();
		$this->assertIsObject( $iconset );
		$this->assertInstanceOf( 'DownloadListWithIcons\Iconsets\Iconsets\Dashicons', $iconset );
	}

	/**
	 * Test if the returning variable is an object.
	 *
	 * @return void
	 */
	public function test_change_default_iconset_to_fontawesome(): void {
		// get the term ID of bootstrap iconset.
		$term_obj = get_term_by( 'slug', 'fontawesome', 'dl_icon_set' );
		$this->assertIsObject( $term_obj );
		$this->assertInstanceOf( 'WP_Term', $term_obj );

		// change it.
		\DownloadListWithIcons\Iconsets\Iconsets::get_instance()->set_default_iconset( $term_obj->term_id );

		// test it.
		$iconset = \DownloadListWithIcons\Iconsets\Iconsets::get_instance()->get_default_iconset();
		$this->assertIsObject( $iconset );
		$this->assertInstanceOf( 'DownloadListWithIcons\Iconsets\Iconsets\Fontawesome', $iconset );
	}
}
