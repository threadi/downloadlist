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
}
