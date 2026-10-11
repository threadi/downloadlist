<?php
/**
 * Tests for class \DownloadListWithIcons\Plugin\Taxonomies.
 *
 * @package download-list-block-with-icons
 */

namespace DownloadListWithIcons\Tests\Unit\Plugin;

use DownloadListWithIcons\Tests\DownloadListTestCase;

/**
 * Object to test functions in the class \DownloadListWithIcons\Plugin\Taxonomies.
 */
class Taxonomies extends DownloadListTestCase {
	/**
	 * Test if the returning variable is a string.
	 *
	 * @return void
	 */
	public function test_are_taxonomies_registered(): void {
		foreach( \DownloadListWithIcons\Plugin\Taxonomies::get_instance()->get_taxonomies() as $taxonomy_name => $taxonomy ) {
			// get the taxonomy.
			$taxonomy_obj = get_taxonomy( $taxonomy_name );

			// test it.
			$this->assertInstanceOf( \WP_Taxonomy::class, $taxonomy_obj );
			$this->assertEquals( $taxonomy_name, $taxonomy_obj->name );
		}
	}

	/**
	 * Test that the default marker of an iconset is kept on updates without our form (e.g. quick edit or REST API).
	 *
	 * @return void
	 */
	public function test_default_iconset_is_kept_on_update_without_form(): void {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );

		// get the default iconset.
		$term = get_term_by( 'slug', 'dashicons', 'dl_icon_set' );
		$this->assertInstanceOf( \WP_Term::class, $term );
		\DownloadListWithIcons\Iconsets\Iconsets::get_instance()->set_default_iconset( $term->term_id );

		// update the term without our form.
		wp_update_term( $term->term_id, 'dl_icon_set', array( 'name' => 'Dashicons' ) );

		$this->assertEquals( 1, get_term_meta( $term->term_id, 'default', true ) );
	}
}
