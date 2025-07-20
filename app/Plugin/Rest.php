<?php
/**
 * This file contains the handling for REST API.
 *
 * @package download-list-block-with-icons
 */

namespace DownloadListWithIcons\Plugin;

// prevent direct access.
defined( 'ABSPATH' ) || exit;

use DownloadListWithIcons\Iconsets\Iconsets;
use WP_REST_Request;
use WP_REST_Server;

/**
 * Object to handle the REST API.
 */
class Rest {
	/**
	 * Instance of actual object.
	 *
	 * @var ?Rest
	 */
	private static ?Rest $instance = null;

	/**
	 * Constructor, not used as this a Singleton object.
	 */
	private function __construct() {}

	/**
	 * Prevent cloning of this object.
	 *
	 * @return void
	 */
	private function __clone() {}

	/**
	 * Return instance of this object as singleton.
	 *
	 * @return Rest
	 */
	public static function get_instance(): Rest {
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
		add_action( 'rest_api_init', array( $this, 'register' ) );
	}

	/**
	 * Add endpoint for requests from our own Block.
	 *
	 * @return void
	 * @noinspection PhpUnused
	 */
	public function register(): void {
		register_rest_route(
			'downloadlist/v1',
			'/files/',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_files_data' ),
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);
		register_rest_route(
			'downloadlist/v1',
			'/filetypes/',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_filetypes' ),
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);
	}

	/**
	 * Return file data depending on post-IDs in request.
	 *
	 * @param WP_REST_Request $request The request-object.
	 * @return array<int,mixed>
	 * @noinspection PhpUnused
	 */
	public function get_files_data( WP_REST_Request $request ): array {
		// get the post_ids from request.
		$post_ids = $request->get_param( 'post_ids' );

		// bail if no ids are given.
		if ( empty( $post_ids ) ) {
			return array();
		}

		// get the file data.
		$file_data = array();
		foreach ( $post_ids as $post_id ) {
			// get the JS-data of this attachment.
			$js = wp_prepare_attachment_for_js( $post_id );

			// bail if it is empty.
			if ( empty( $js ) ) {
				continue;
			}

			// add the JS-data of this attachment to the list.
			$file_data[] = $js;
		}

		/**
		 * Filter the resulting file data before we return them.
		 *
		 * @since 3.7.0 Available since 3.7.0.
		 * @param array<int,mixed> $file_data The response as array.
		 * @param WP_REST_Request $request The request.
		 */
		return apply_filters( 'downloadlist_api_return_file_data', $file_data, $request );
	}

	/**
	 * Return possible file-types for Block Editor via REST API.
	 *
	 * @param WP_REST_Request $request The request-object.
	 * @return array<int,array<string,mixed>>
	 * @noinspection PhpUnused
	 */
	public function get_filetypes( WP_REST_Request $request ): array {
		$iconset = sanitize_text_field( $request->get_param( 'iconset' ) );

		// bail if no iconset is given.
		if ( empty( $iconset ) ) {
			return array();
		}

		// get the iconset as object.
		$iconset_obj = Iconsets::get_instance()->get_iconset_by_slug( $iconset );

		// bail if no matching iconset was found.
		if ( false === $iconset_obj ) {
			return array();
		}

		// get the file-types for this iconset.
		$file_types = $iconset_obj->get_file_types();

		// convert array to object-array so every entry has its own index.
		$iconsets = array();
		foreach ( $file_types as $key => $file_type ) {
			$iconsets[] = array(
				'id'    => $key,
				'value' => $file_type,
			);
		}

		/**
		 * Filter the resulting list of iconsets before we return them.
		 *
		 * @3.7.0 Available since 3.7.0.
		 * @param array $iconsets List of iconsets.
		 * @param WP_REST_Request $request The request.
		 */
		return apply_filters( 'downloadlist_rest_api_filetypes', $iconsets, $request );
	}
}
