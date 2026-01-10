<?php
/**
 * File for handling files.
 *
 * @package download-list-block-with-icons
 */

namespace DownloadListWithIcons\Plugin;

// prevent direct access.
defined( 'ABSPATH' ) || exit;

/**
 * Object to handle files.
 */
class ThirdPartySupport {
	/**
	 * Instance of this object.
	 *
	 * @var ?ThirdPartySupport
	 */
	private static ?ThirdPartySupport $instance = null;

	/**
	 * Constructor for Init-Handler.
	 */
	private function __construct() {}

	/**
	 * Prevent cloning of this object.
	 *
	 * @return void
	 */
	private function __clone() {}

	/**
	 * Return the instance of this Singleton object.
	 */
	public static function get_instance(): ThirdPartySupport {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Initialize this object.
	 *
	 * @return void
	 */
	public function init(): void {
		add_filter( 'easy_language_possible_post_types', array( $this, 'remove_easy_language_support' ) );
	}

	/**
	 * Exclude our own cpt from Easy Language.
	 *
	 * @param array<string,mixed> $post_types List of post types.
	 * @return array<string,mixed>
	 */
	public function remove_easy_language_support( array $post_types ): array {
		if ( ! empty( $post_types['dl_icons'] ) ) {
			unset( $post_types['dl_icons'] );
		}
		return $post_types;
	}
}
