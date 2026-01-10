<?php
/**
 * File to handle the main object for each test class.
 *
 * @package download-list-block-with-icons
 */

namespace DownloadListWithIcons\Tests;

use WP_UnitTestCase;

/**
 * Object to handle the preparations for each test class.
 */
abstract class DownloadListTestCase extends WP_UnitTestCase {

	/**
	 * Prepare the test environment for each test class.
	 *
	 * @return void
	 */
	public static function set_up_before_class(): void {
		parent::set_up_before_class();

		// prepare loading just one time.
		if ( ! did_action('downloadlist_test_preparation_loaded') ) {
			// run plugin activation.
			\DownloadListWithIcons\Plugin\Installer::get_instance()->activation();

			// mark as loaded.
			do_action('downloadlist_test_preparation_loaded');
		}
	}

	/**
	 * Check for an array of specific object types.
	 *
	 * @param $type
	 * @param $array
	 * @param $message
	 *
	 * @return void
	 */
	public function assertArrayHasObjectOfType( $type, $array, $message = '' ): void {
		$found = false;
		foreach( $array as $obj ) {
			if( in_array( $type, array( get_class( $obj ), get_parent_class( $obj ) ), true ) ) {
				$found = true;
				break;
			}
		}
		$this->assertTrue( $found, $message );
	}
}
