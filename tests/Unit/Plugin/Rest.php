<?php
/**
 * Tests for class \DownloadListWithIcons\Plugin\Rest.
 *
 * @package download-list-block-with-icons
 */

namespace DownloadListWithIcons\Tests\Unit\Plugin;

use DownloadListWithIcons\Tests\DownloadListTestCase;
use WP_REST_Request;

/**
 * Object to test functions in the class \DownloadListWithIcons\Plugin\Rest.
 */
class Rest extends DownloadListTestCase {
	/**
	 * Test that the endpoint for files returns only files the user is allowed to read.
	 *
	 * @return void
	 */
	public function test_files_endpoint_checks_read_permission(): void {
		// create users.
		$admin       = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$contributor = self::factory()->user->create( array( 'role' => 'contributor' ) );

		// create a file which is attached to a private post of the administrator.
		$private_post_id = self::factory()->post->create(
			array(
				'post_status' => 'private',
				'post_author' => $admin,
			)
		);
		$attachment_id   = self::factory()->attachment->create_upload_object( UNIT_TESTS_DATA_PLUGIN_DIR . '/test-image.png', $private_post_id );

		// create the request.
		$request = new WP_REST_Request( 'GET', '/downloadlist/v1/files' );
		$request->set_param( 'post_ids', array( $attachment_id ) );

		// the contributor must not get the data.
		wp_set_current_user( $contributor );
		$this->assertEmpty( \DownloadListWithIcons\Plugin\Rest::get_instance()->get_files_data( $request ) );

		// the administrator gets the data.
		wp_set_current_user( $admin );
		$result = \DownloadListWithIcons\Plugin\Rest::get_instance()->get_files_data( $request );
		$this->assertCount( 1, $result );
	}

	/**
	 * Test that the endpoint for files returns only attachments.
	 *
	 * @return void
	 */
	public function test_files_endpoint_returns_only_attachments(): void {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		$post_id = self::factory()->post->create();

		$request = new WP_REST_Request( 'GET', '/downloadlist/v1/files' );
		$request->set_param( 'post_ids', array( $post_id ) );

		$this->assertEmpty( \DownloadListWithIcons\Plugin\Rest::get_instance()->get_files_data( $request ) );
	}

	/**
	 * Test that the endpoint for files rejects invalid IDs.
	 *
	 * @return void
	 */
	public function test_files_endpoint_validates_ids(): void {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );

		$request = new WP_REST_Request( 'GET', '/downloadlist/v1/files' );
		$request->set_param( 'post_ids', array( 'abc' ) );
		$response = rest_do_request( $request );

		$this->assertSame( 400, $response->get_status() );
	}
}
