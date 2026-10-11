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

		// run plugin activation for each test class, as WordPress removes all data after each test class.
		\DownloadListWithIcons\Plugin\Installer::get_instance()->activation();

		// commit the data, so the rollback after each test does not remove it.
		self::commit_transaction();
	}

	/**
	 * Check for an array of specific object types (including parent classes and interfaces).
	 *
	 * @param string        $type The class or interface name.
	 * @param array<mixed>  $array The list of objects.
	 * @param string        $message The message on failure.
	 *
	 * @return void
	 */
	public function assertArrayHasObjectOfType( string $type, array $array, string $message = '' ): void {
		$found = false;
		foreach( $array as $obj ) {
			if ( $obj instanceof $type ) {
				$found = true;
				break;
			}
		}
		$this->assertTrue( $found, $message );
	}
}
