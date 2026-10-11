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

	/**
	 * Test the detection which file types could have a preview.
	 *
	 * @return void
	 */
	public function test_get_preview_type(): void {
		$this->assertSame( 'image', \DownloadListWithIcons\Plugin\Helper::get_preview_type( 'image/jpeg' ) );
		$this->assertSame( 'pdf', \DownloadListWithIcons\Plugin\Helper::get_preview_type( 'application/pdf' ) );
		$this->assertSame( 'audio', \DownloadListWithIcons\Plugin\Helper::get_preview_type( 'audio/mpeg' ) );
		$this->assertSame( 'video', \DownloadListWithIcons\Plugin\Helper::get_preview_type( 'video/mp4' ) );
		$this->assertSame( 'none', \DownloadListWithIcons\Plugin\Helper::get_preview_type( 'application/zip' ) );
		$this->assertSame( 'none', \DownloadListWithIcons\Plugin\Helper::get_preview_type( '' ) );
	}

	/**
	 * Test that lists without frame options and without previews get no frame (compatibility with existing lists).
	 *
	 * @return void
	 */
	public function test_get_media_frame_without_options(): void {
		$frame = \DownloadListWithIcons\Plugin\Helper::get_media_frame( array(), false );
		$this->assertSame( '', $frame['class'] );
		$this->assertSame( '', $frame['style'] );

		// a frame color without frame width has no effect.
		$frame = \DownloadListWithIcons\Plugin\Helper::get_media_frame( array( 'mediaBorderColor' => '#ff0000' ), false );
		$this->assertSame( '', $frame['class'] );
	}

	/**
	 * Test the CSS variables of the frame.
	 *
	 * @return void
	 */
	public function test_get_media_frame_with_options(): void {
		$frame = \DownloadListWithIcons\Plugin\Helper::get_media_frame(
			array(
				'previewWidth'      => 64,
				'mediaBorderWidth'  => 1,
				'mediaBorderColor'  => '#dddddd',
				'mediaBorderRadius' => 8,
				'mediaPadding'      => 6,
				'mediaBackground'   => '#f5f5f5',
			),
			false
		);
		$this->assertSame( ' has-media-frame', $frame['class'] );
		$this->assertSame( '--downloadlist-media-size:64px;--downloadlist-media-border-width:1px;--downloadlist-media-border-color:#dddddd;--downloadlist-media-border-radius:8px;--downloadlist-media-padding:6px;--downloadlist-media-background:#f5f5f5;', $frame['style'] );

		// previews alone use the tile with the given size.
		$frame = \DownloadListWithIcons\Plugin\Helper::get_media_frame( array( 'previewWidth' => 5000 ), true );
		$this->assertSame( ' has-media-frame', $frame['class'] );
		$this->assertSame( '--downloadlist-media-size:600px;', $frame['style'] );

		// invalid colors are not used.
		$frame = \DownloadListWithIcons\Plugin\Helper::get_media_frame(
			array(
				'mediaBorderWidth' => 2,
				'mediaBorderColor' => 'red;background:url(https://example.com/x.png)',
			),
			false
		);
		$this->assertStringNotContainsString( 'border-color', $frame['style'] );
		$this->assertStringNotContainsString( 'url(', $frame['style'] );
	}

	/**
	 * Test the sanitizing of colors.
	 *
	 * @return void
	 */
	public function test_sanitize_css_color(): void {
		$this->assertSame( '#fff', \DownloadListWithIcons\Plugin\Helper::sanitize_css_color( '#fff' ) );
		$this->assertSame( '#11223344', \DownloadListWithIcons\Plugin\Helper::sanitize_css_color( ' #11223344 ' ) );
		$this->assertSame( 'rgba(0, 0, 0, 0.5)', \DownloadListWithIcons\Plugin\Helper::sanitize_css_color( 'rgba(0, 0, 0, 0.5)' ) );
		$this->assertSame( 'var(--wp--preset--color--primary)', \DownloadListWithIcons\Plugin\Helper::sanitize_css_color( 'var(--wp--preset--color--primary)' ) );
		$this->assertSame( '', \DownloadListWithIcons\Plugin\Helper::sanitize_css_color( 'red' ) );
		$this->assertSame( '', \DownloadListWithIcons\Plugin\Helper::sanitize_css_color( '#fff;color:red' ) );
		$this->assertSame( '', \DownloadListWithIcons\Plugin\Helper::sanitize_css_color( 'rgb(0,0,0);x:url(a)' ) );
		$this->assertSame( '', \DownloadListWithIcons\Plugin\Helper::sanitize_css_color( '"><script>' ) );
	}
}
