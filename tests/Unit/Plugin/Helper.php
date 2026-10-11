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

	/**
	 * Test that adding the generic iconsets again does not add duplicate icon entries.
	 *
	 * @return void
	 */
	public function test_add_generic_iconsets_without_duplicates(): void {
		$before = count( \DownloadListWithIcons\Iconsets\Iconsets::get_instance()->get_generic_sets_cpts() );

		\DownloadListWithIcons\Plugin\Helper::add_generic_iconsets();
		\DownloadListWithIcons\Plugin\Helper::add_generic_iconsets();

		$this->assertSame( $before, count( \DownloadListWithIcons\Iconsets\Iconsets::get_instance()->get_generic_sets_cpts() ) );
	}

	/**
	 * Test that strings for CSS cannot end the string or the rule.
	 *
	 * @return void
	 */
	public function test_sanitize_css_string(): void {
		$this->assertSame( '\\f101', \DownloadListWithIcons\Plugin\Helper::sanitize_css_string( '\\f101' ) );
		$this->assertSame( 'a  body display:none', \DownloadListWithIcons\Plugin\Helper::sanitize_css_string( 'a"; } body {display:none' ) );
	}

	/**
	 * Test that adding the generic iconsets again keeps the default iconset chosen by the user.
	 *
	 * @return void
	 */
	public function test_add_generic_iconsets_keeps_chosen_default(): void {
		$term = get_term_by( 'slug', 'bootstrap', 'dl_icon_set' );
		$this->assertInstanceOf( \WP_Term::class, $term );
		\DownloadListWithIcons\Iconsets\Iconsets::get_instance()->set_default_iconset( $term->term_id );

		\DownloadListWithIcons\Plugin\Helper::add_generic_iconsets();

		$this->assertEquals( 1, get_term_meta( $term->term_id, 'default', true ) );
	}
}
