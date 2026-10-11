<?php
/**
 * Tests for class \DownloadListWithIcons\Plugin\Settings.
 *
 * @package download-list-block-with-icons
 */

namespace DownloadListWithIcons\Tests\Unit\Plugin;

use DownloadListWithIcons\Tests\DownloadListTestCase;

/**
 * Object to test functions in the class \DownloadListWithIcons\Plugin\Settings.
 */
class Settings extends DownloadListTestCase {
	/**
	 * Test that inheriting the settings keeps backslashes in the content and updates nested blocks.
	 *
	 * @return void
	 */
	public function test_inherit_settings_keeps_backslashes_and_updates_nested_blocks(): void {
		// as administrator (who may save unfiltered HTML), so only the saving itself is tested.
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );

		// set a setting which will be inherited.
		update_option( 'downloadlist_hide_file_sizes', 1 );

		// content with a backslash, a JSON escape in a block attribute and our block in a group.
		$content  = '<!-- wp:paragraph {"placeholder":"a\u003cb"} --><p>C:\path\to\file</p><!-- /wp:paragraph -->';
		$content .= '<!-- wp:group --><div class="wp-block-group"><!-- wp:downloadlist/list {"files":[{"id":1}]} /--></div><!-- /wp:group -->';

		// create the post (wp_insert_post() expects slashed data).
		$post_id = self::factory()->post->create( array( 'post_content' => wp_slash( $content ) ) );

		// run the inheriting.
		\DownloadListWithIcons\Plugin\Settings::get_instance()->inherit_settings_to_blocks();

		// get the new content.
		$new_content = (string) get_post_field( 'post_content', $post_id, 'raw' );

		// the backslashes must still be there.
		$this->assertStringContainsString( 'C:\path\to\file', $new_content );
		$this->assertStringContainsString( '\u003c', $new_content );

		// the block in the group must have the setting.
		$this->assertStringContainsString( '"hideFileSize":true', $new_content );

		// the marker for the running process must be removed.
		$this->assertFalse( get_option( 'downloadlist_inheriting_running' ) );
	}

	/**
	 * Test that adding the block to a post keeps backslashes in the content.
	 *
	 * @return void
	 */
	public function test_add_block_in_post_keeps_backslashes(): void {
		$post_id = self::factory()->post->create( array( 'post_content' => wp_slash( '<!-- wp:paragraph --><p>C:\path</p><!-- /wp:paragraph -->' ) ) );

		\DownloadListWithIcons\Plugin\Settings::get_instance()->add_block_in_post( (string) $post_id );

		$new_content = (string) get_post_field( 'post_content', $post_id, 'raw' );
		$this->assertStringContainsString( 'C:\path', $new_content );
		$this->assertStringContainsString( '<!-- wp:downloadlist/list /-->', $new_content );
	}
}
