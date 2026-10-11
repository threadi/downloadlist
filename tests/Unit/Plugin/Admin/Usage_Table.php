<?php
/**
 * Tests for class \DownloadListWithIcons\Plugin\Admin\Usage_Table.
 *
 * @package download-list-block-with-icons
 */

namespace DownloadListWithIcons\Tests\Unit\Plugin\Admin;

use DownloadListWithIcons\Tests\DownloadListTestCase;

/**
 * Object to test functions in the class \DownloadListWithIcons\Plugin\Admin\Usage_Table.
 */
class Usage_Table extends DownloadListTestCase {
	/**
	 * Test that the table lists only objects which contain our block.
	 *
	 * @return void
	 */
	public function test_prepare_items(): void {
		set_current_screen( 'settings_page_downloadlist_settings' );
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );

		// objects with our block.
		$post_id    = self::factory()->post->create( array( 'post_content' => '<!-- wp:downloadlist/list {"files":[{"id":1}]} /-->' ) );
		$page_id    = self::factory()->post->create(
			array(
				'post_type'    => 'page',
				'post_status'  => 'draft',
				'post_content' => '<!-- wp:group --><div class="wp-block-group"><!-- wp:downloadlist/list /--><!-- wp:downloadlist/list /--></div><!-- /wp:group -->',
			)
		);
		$pattern_id = self::factory()->post->create(
			array(
				'post_type'    => 'wp_block',
				'post_content' => '<!-- wp:downloadlist/list /-->',
			)
		);

		// objects without our block.
		$other_id   = self::factory()->post->create(
			array(
				'post_title'   => 'wp:downloadlist/list',
				'post_content' => '<p>No block here.</p>',
			)
		);
		$trashed_id = self::factory()->post->create(
			array(
				'post_status'  => 'trash',
				'post_content' => '<!-- wp:downloadlist/list /-->',
			)
		);

		// load the table.
		$table = new \DownloadListWithIcons\Plugin\Admin\Usage_Table();
		$table->prepare_items();
		$ids = wp_list_pluck( $table->items, 'ID' );

		$this->assertContains( $post_id, $ids );
		$this->assertContains( $page_id, $ids );
		$this->assertContains( $pattern_id, $ids );
		$this->assertNotContains( $other_id, $ids );
		$this->assertNotContains( $trashed_id, $ids );

		// the output contains the edit link and the number of lists.
		ob_start();
		$table->display();
		$html = (string) ob_get_clean();
		$this->assertStringContainsString( 'post.php?post=' . $page_id . '&#038;action=edit', $html );
		$this->assertStringContainsString( 'column-lists\' data-colname="Download lists">2<', $html );
	}

	/**
	 * Test the number of files per list.
	 *
	 * @return void
	 */
	public function test_get_file_counts(): void {
		$file_1 = self::factory()->attachment->create_object( array( 'file' => 'a.zip', 'post_mime_type' => 'application/zip' ) );
		$file_2 = self::factory()->attachment->create_object( array( 'file' => 'b.zip', 'post_mime_type' => 'application/zip' ) );
		$file_3 = self::factory()->attachment->create_object( array( 'file' => 'c.zip', 'post_mime_type' => 'application/zip' ) );

		// a download list with two files.
		$term = wp_insert_term( 'My list', 'dl_icon_lists' );
		$this->assertIsArray( $term );
		wp_set_object_terms( $file_2, array( $term['term_id'] ), 'dl_icon_lists' );
		wp_set_object_terms( $file_3, array( $term['term_id'] ), 'dl_icon_lists' );

		// a block with two existing files and one deleted file, a nested block using the download list and an empty block.
		$content  = '<!-- wp:downloadlist/list {"files":[{"id":' . $file_1 . '},{"id":' . $file_2 . '},{"id":999999}]} /-->';
		$content .= '<!-- wp:group --><div class="wp-block-group"><!-- wp:downloadlist/list {"list":' . $term['term_id'] . ',"files":[{"id":' . $file_2 . '}]} /--></div><!-- /wp:group -->';
		$content .= '<!-- wp:downloadlist/list /-->';

		$this->assertSame( array( 2, 2, 0 ), \DownloadListWithIcons\Plugin\Admin\Usage_Table::get_file_counts( $content ) );
	}
}
