<?php
/**
 * Tests for class \DownloadListWithIcons\Icons\Icons.
 *
 * @package download-list-block-with-icons
 */

namespace DownloadListWithIcons\Tests\Unit\Icons;

use DownloadListWithIcons\Tests\DownloadListTestCase;

/**
 * Object to test functions in the class \DownloadListWithIcons\Icons\Icons.
 */
class Icons extends DownloadListTestCase {
	/**
	 * Remove the request data after each test.
	 *
	 * @return void
	 */
	public function tear_down(): void {
		$_POST = array();
		parent::tear_down();
	}

	/**
	 * Create an icon and return its ID.
	 *
	 * @return int
	 */
	private function create_icon(): int {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		return self::factory()->post->create(
			array(
				'post_type'   => 'dl_icons',
				'post_status' => 'publish',
			)
		);
	}

	/**
	 * Test that the chosen image of an icon is saved.
	 *
	 * @return void
	 */
	public function test_save_icon_image(): void {
		$post_id       = $this->create_icon();
		$attachment_id = self::factory()->attachment->create_upload_object( UNIT_TESTS_DATA_PLUGIN_DIR . '/test-image.png' );

		$_POST = array(
			'_wpnonce'  => wp_create_nonce( 'update-post_' . $post_id ),
			'icon'      => (string) $attachment_id,
			'file_type' => 'application/pdf',
			'unicode'   => '',
		);
		\DownloadListWithIcons\Icons\Icons::get_instance()->check_taxonomy( $post_id );

		$this->assertSame( $attachment_id, absint( get_post_meta( $post_id, 'icon', true ) ) );
		$this->assertSame( 'application/pdf', get_post_meta( $post_id, 'file_type', true ) );

		// remove the image.
		$_POST['icon'] = '';
		\DownloadListWithIcons\Icons\Icons::get_instance()->check_taxonomy( $post_id );
		$this->assertEmpty( get_post_meta( $post_id, 'icon', true ) );
	}

	/**
	 * Test that unknown file types and invalid characters in the unicode are not saved.
	 *
	 * @return void
	 */
	public function test_invalid_values_are_not_saved(): void {
		$post_id = $this->create_icon();

		$_POST = array(
			'_wpnonce'  => wp_create_nonce( 'update-post_' . $post_id ),
			'icon'      => '',
			'file_type' => 'x{} body{display:none} .y',
			'unicode'   => 'f101";} body{display:none',
		);
		\DownloadListWithIcons\Icons\Icons::get_instance()->check_taxonomy( $post_id );

		$this->assertEmpty( get_post_meta( $post_id, 'file_type', true ) );
		$this->assertSame( '\f101bddae', get_post_meta( $post_id, 'unicode', true ) );
	}

	/**
	 * Test that nothing is saved without a valid nonce.
	 *
	 * @return void
	 */
	public function test_nothing_is_saved_without_nonce(): void {
		$post_id = $this->create_icon();

		$_POST = array(
			'_wpnonce'  => 'invalid',
			'file_type' => 'application/pdf',
		);
		\DownloadListWithIcons\Icons\Icons::get_instance()->check_taxonomy( $post_id );

		$this->assertEmpty( get_post_meta( $post_id, 'file_type', true ) );
	}
}
