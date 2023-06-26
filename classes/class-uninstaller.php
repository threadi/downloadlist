<?php
/**
 * File to handle uninstaller-tasks.
 *
 * @package download-list-block-with-icons
 */

namespace downloadlist;

/**
 * Object to handle uninstaller-tasks.
 */
class Uninstaller {

	/**
	 * Instance of this object.
	 *
	 * @var ?Uninstaller
	 */
	private static ?Uninstaller $instance = null;

	/**
	 * Constructor for Init-Handler.
	 */
	private function __construct() {}

	/**
	 * Prevent cloning of this object.
	 *
	 * @return void
	 */
	private function __clone() { }

	/**
	 * Return the instance of this Singleton object.
	 */
	public static function get_instance(): Uninstaller {
		if ( ! static::$instance instanceof static ) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	/**
	 * Run uninstallation tasks.
	 *
	 * @return void
	 */
	public function run(): void {
		// delete our own post-type-entries.
		$query = array(
			'post_type'      => 'dl_icons',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		);
		$posts = new \WP_Query( $query );
		foreach ( $posts->posts as $post_id ) {
			// get the assigned media-file.
			$attachment_id = absint( get_post_meta( $post_id, 'icon', true ) );

			// remove the image-sizes generated by this plugin from the image.
			if ( $attachment_id > 0 ) {
				$attachment_meta = wp_get_attachment_metadata( $attachment_id );
				if ( ! empty( $attachment_meta['sizes'] ) ) {
					foreach ( $attachment_meta['sizes'] as $name => $size ) {
						if ( false !== strpos( $name, 'downloadlist-icon-' ) ) {
							unset( $attachment_meta['sizes'][ $name ] );
						}
					}
				}
				wp_update_attachment_metadata( $attachment_id, $attachment_meta );
			}

			// delete the entry.
			wp_delete_post( $post_id, true );
		}

		// delete entries of our own taxonomy for iconsets.
		$query     = array(
			'taxonomy'   => 'dl_icon_set',
			'hide_empty' => false,
		);
		$icon_sets = new \WP_Term_Query( $query );
		foreach ( $icon_sets->get_terms() as $term ) {
			wp_delete_term( $term->term_id, 'dl_icon_set' );
		}

		// delete style-file.
		$path = helper::get_style_path();
		if ( file_exists( $path ) ) {
			unlink( $path );
		}
	}
}
