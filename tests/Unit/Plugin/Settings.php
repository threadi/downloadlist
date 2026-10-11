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

		// numbers must be saved as numbers (the attribute is an integer).
		$this->assertStringContainsString( '"previewWidth":48', $new_content );

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

	/**
	 * Test the structure of the settings: block settings, styles and the settings of the plugin itself.
	 *
	 * @return void
	 */
	public function test_settings_structure(): void {
		$settings_obj = \DownloadListWithIcons\Plugin\Settings::get_instance()->get_settings_obj();

		// the frame settings are in their own section of the block settings.
		foreach ( array( 'downloadlist_media_border_width', 'downloadlist_media_border_color', 'downloadlist_media_border_radius', 'downloadlist_media_padding', 'downloadlist_media_background' ) as $option ) {
			$setting = $settings_obj->get_setting( $option );
			$this->assertNotFalse( $setting, $option );
			$section = $setting->get_section();
			$this->assertNotNull( $section, $option );
			$this->assertSame( 'styles', $section->get_name(), $option );
			$tab = $section->get_tab();
			$this->assertNotFalse( $tab, $option );
			$this->assertSame( 'downloadlist_general', $tab->get_name(), $option );
		}

		// the support for folders is a setting of the plugin, not of the block.
		$setting = $settings_obj->get_setting( 'downloadlist_folder_support' );
		$this->assertNotFalse( $setting );
		$section = $setting->get_section();
		$this->assertNotNull( $section );
		$this->assertSame( 'folders', $section->get_name() );
		$tab = $section->get_tab();
		$this->assertNotFalse( $tab );
		$this->assertSame( 'downloadlist_plugin', $tab->get_name() );
		$this->assertEmpty( $setting->get_custom_var( 'block-name' ) );

		// the support for folders is enabled by default.
		$this->assertTrue( \DownloadListWithIcons\Plugin\Helper::is_folder_support_enabled() );
	}

	/**
	 * Test that invalid colors are not saved as setting.
	 *
	 * @return void
	 */
	public function test_color_settings_are_sanitized(): void {
		$settings = \DownloadListWithIcons\Plugin\Settings::get_instance();
		$this->assertSame( '#dddddd', $settings->sanitize_color( '#dddddd' ) );
		$this->assertSame( '', $settings->sanitize_color( 'red;background:url(https://example.com/x.png)' ) );
		$this->assertSame( '', $settings->sanitize_color( '"><script>alert(1)</script>' ) );
		$this->assertSame( '', $settings->sanitize_color( array( '#dddddd' ) ) );
		$this->assertSame( '', $settings->sanitize_color( null ) );

		// the callback is used for both color settings.
		$settings_obj = $settings->get_settings_obj();
		foreach ( array( 'downloadlist_media_border_color', 'downloadlist_media_background' ) as $option ) {
			$setting = $settings_obj->get_setting( $option );
			$this->assertNotFalse( $setting );
			$field = $setting->get_field();
			$this->assertNotNull( $field );
			$this->assertSame( '', call_user_func( $field->get_sanitize_callback(), 'red' ), $option );
		}
	}

	/**
	 * Test that inheriting the settings does not add settings of the plugin itself to the blocks.
	 *
	 * @return void
	 */
	public function test_inherit_settings_sets_only_block_settings(): void {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		update_option( 'downloadlist_media_border_width', 2 );

		$post_id = self::factory()->post->create( array( 'post_content' => '<!-- wp:downloadlist/list {"files":[{"id":1}]} /-->' ) );
		\DownloadListWithIcons\Plugin\Settings::get_instance()->inherit_settings_to_blocks();

		$new_content = (string) get_post_field( 'post_content', $post_id, 'raw' );
		$this->assertStringContainsString( '"mediaBorderWidth":2', $new_content );
		$this->assertStringNotContainsString( 'folder_support', $new_content );
		$this->assertStringNotContainsString( 'folderSupport', $new_content );
	}
}
