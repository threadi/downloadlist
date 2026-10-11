<?php
/**
 * Tests for class \DownloadListWithIcons\Plugin\Admin\Admin.
 *
 * @package download-list-block-with-icons
 */

namespace DownloadListWithIcons\Tests\Unit\Admin;

use DownloadListWithIcons\Tests\DownloadListTestCase;
use WPDieException;

/**
 * Object to test functions in the class \DownloadListWithIcons\Plugin\Admin\Admin.
 */
class Admin extends DownloadListTestCase {
	/**
	 * Simulate an AJAX request, where wp_die() throws an exception instead of stopping PHP.
	 *
	 * @return void
	 */
	public function set_up(): void {
		parent::set_up();
		add_filter( 'wp_doing_ajax', '__return_true' );
		add_filter(
			'wp_die_ajax_handler',
			function () {
				return function ( $message ) {
					throw new WPDieException( is_string( $message ) ? $message : '' );
				};
			}
		);
		ob_start();
	}

	/**
	 * Remove the output of the request.
	 *
	 * @return void
	 */
	public function tear_down(): void {
		ob_end_clean();
		unset( $_REQUEST['nonce'], $_POST['option_name'] );
		parent::tear_down();
	}

	/**
	 * Test that users who cannot see the notices cannot dismiss them.
	 *
	 * @return void
	 */
	public function test_subscriber_cannot_dismiss_notices(): void {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$_REQUEST['nonce']    = wp_create_nonce( 'etfw-dismiss-nonce' );
		$_POST['option_name'] = 'downloadlist_php_hint';

		$stopped = false;
		try {
			\DownloadListWithIcons\Plugin\Admin\Admin::get_instance()->secure_dismiss_request();
		} catch ( WPDieException $e ) {
			$stopped = true;
		}

		// the request must be stopped with an error (not because of the nonce).
		$this->assertTrue( $stopped );
		$this->assertStringContainsString( '"success":false', (string) ob_get_contents() );
	}

	/**
	 * Test that notices of other plugins with the same library are not stopped.
	 *
	 * @return void
	 */
	public function test_notices_of_other_plugins_are_not_stopped(): void {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$_REQUEST['nonce']    = wp_create_nonce( 'etfw-dismiss-nonce' );
		$_POST['option_name'] = 'other_plugin_notice';

		\DownloadListWithIcons\Plugin\Admin\Admin::get_instance()->secure_dismiss_request();
		$this->assertTrue( true );
	}

	/**
	 * Test that administrators can dismiss notices (the request is not stopped).
	 *
	 * @return void
	 */
	public function test_administrator_can_dismiss_notices(): void {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );

		\DownloadListWithIcons\Plugin\Admin\Admin::get_instance()->secure_dismiss_request();
		$this->assertTrue( true );
	}
}
