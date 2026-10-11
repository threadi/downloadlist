<?php
/**
 * Tests for the generated style file with the styles of all iconsets.
 *
 * @package download-list-block-with-icons
 */

namespace DownloadListWithIcons\Tests\Unit\Scenarios;

use DownloadListWithIcons\Plugin\Helper;
use DownloadListWithIcons\Tests\DownloadListTestCase;

/**
 * Object to test the generated style file.
 */
class StylesOfAllIconsets extends DownloadListTestCase {
	/**
	 * Test that saving one iconset keeps the styles of all other iconsets in the file.
	 *
	 * @return void
	 */
	public function test_styles_of_all_iconsets_are_kept(): void {
		// create a custom iconset with one icon.
		$term = wp_insert_term( 'Unit test iconset', 'dl_icon_set', array( 'slug' => 'unit-test-iconset' ) );
		$this->assertIsArray( $term );
		update_term_meta( $term['term_id'], 'type', 'custom' );
		$icon_id = self::factory()->post->create(
			array(
				'post_type'   => 'dl_icons',
				'post_status' => 'publish',
			)
		);
		update_post_meta( $icon_id, 'file_type', 'application/pdf' );
		update_post_meta( $icon_id, 'unicode', wp_slash( '\f101' ) );
		wp_set_object_terms( $icon_id, $term['term_id'], 'dl_icon_set' );

		// generate the styles only "for this iconset" as before.
		Helper::generate_css( $term['term_id'] );

		// get the file.
		$styles = (string) file_get_contents( Helper::get_style_path() );

		// it must contain the custom iconset and a generic iconset.
		$this->assertStringContainsString( '.iconset-unit-test-iconset .file_pdf', $styles );
		$this->assertStringContainsString( '.iconset-dashicons', $styles );

		// the unicode must be kept and no font size of 0 must be set.
		$this->assertStringContainsString( 'content: "\f101"', $styles );
		$this->assertStringNotContainsString( 'font-size: 0px', $styles );
	}

	/**
	 * Test that the file exists also if no styles are generated.
	 *
	 * @return void
	 */
	public function test_file_exists_without_styles(): void {
		add_filter( 'downloadlist_generate_css', '__return_empty_string' );
		Helper::generate_css();
		remove_filter( 'downloadlist_generate_css', '__return_empty_string' );

		$this->assertFileExists( Helper::get_style_path() );

		// generate the styles again for the following tests.
		Helper::generate_css();
	}

	/**
	 * Test that a custom iconset uses only its own icons.
	 *
	 * @return void
	 */
	public function test_custom_iconset_uses_only_own_icons(): void {
		// create two custom iconsets with one icon each.
		$file_types = array(
			'unit-test-set-a' => 'application/pdf',
			'unit-test-set-b' => 'application/zip',
		);
		foreach ( $file_types as $slug => $file_type ) {
			$term = wp_insert_term( $slug, 'dl_icon_set', array( 'slug' => $slug ) );
			$this->assertIsArray( $term );
			update_term_meta( $term['term_id'], 'type', 'custom' );
			$icon_id = self::factory()->post->create(
				array(
					'post_type'   => 'dl_icons',
					'post_status' => 'publish',
				)
			);
			update_post_meta( $icon_id, 'file_type', $file_type );
			wp_set_object_terms( $icon_id, $term['term_id'], 'dl_icon_set' );
		}

		$iconset = \DownloadListWithIcons\Iconsets\Iconsets::get_instance()->get_iconset_by_slug( 'unit-test-set-a' );
		$this->assertNotFalse( $iconset );
		$this->assertSame( array( 'application/pdf' ), array_values( $iconset->get_file_types() ) );
		$this->assertCount( 1, $iconset->get_icons() );
	}
}
