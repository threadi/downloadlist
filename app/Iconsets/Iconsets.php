<?php
/**
 * File for general iconset-handling.
 *
 * @package download-list-block-with-icons
 */

namespace DownloadListWithIcons\Iconsets;

// prevent direct access.
defined( 'ABSPATH' ) || exit;

use DownloadListWithIcons\Plugin\Helper;
use WP_Post;
use WP_Query;

/**
 * Object for general iconset-handling.
 */
class Iconsets {
	/**
	 * Instance of this object.
	 *
	 * @var ?Iconsets
	 */
	private static ?Iconsets $instance = null;

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
	public static function get_instance(): Iconsets {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Initialize the iconset.
	 *
	 * @return void
	 */
	public function init(): void {
		add_action( 'wp_enqueue_scripts', array( $this, 'register_styles' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'register_styles' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_styles_run' ), 10, 0 );
		add_action( 'admin_action_downloadlist_iconset_default', array( $this, 'set_default_by_request' ) );
		add_action( 'pre_get_posts', array( $this, 'hide_generated_iconsets' ) );
	}

	/**
	 * Register our own iconset-css-files for enqueuing.
	 *
	 * @return void
	 */
	public function register_styles(): void {
		if ( false === file_exists( Helper::get_style_path() ) ) {
			Helper::generate_css();
		}

		// get global styles.
		wp_register_style(
			'downloadlist-iconsets',
			Helper::get_style_url(),
			array(),
			Helper::get_file_version( Helper::get_style_path() ),
		);

		// get iconset-styles.
		foreach ( self::get_instance()->get_icon_sets() as $iconset_obj ) {
			foreach ( $iconset_obj->get_style_files() as $file ) {
				// bail if handle is empty.
				if ( empty( $file['handle'] ) ) {
					continue;
				}

				// register style if path and URL are given.
				if ( ! empty( $file['path'] ) && ! empty( $file['url'] ) ) {
					wp_register_style(
						'downloadlist-' . $file['handle'],
						$file['url'],
						array(),
						Helper::get_file_version( $file['path'] )
					);
				}

				// register style if only dependent style name is given.
				if ( empty( $file['path'] ) && ! empty( $file['depends'] ) ) {
					wp_register_style( 'downloadlist-' . $file['handle'], false, $file['depends'], DL_VERSION );
				}
			}
		}
	}

	/**
	 * Get all iconsets which are registered.
	 *
	 * @return array<int,Iconset_Base>
	 */
	public function get_icon_sets(): array {
		$list = array();

		/**
		 * Register a single iconset through adding it to the list.
		 *
		 * The iconset must be an object extending Iconset_Base and implement Iconset.
		 *
		 * @since 3.0.0 Available since 3.0.0.
		 *
		 * @param array<int,Iconset_Base> $list The list of iconsets.
		 */
		return apply_filters( 'downloadlist_register_iconset', $list );
	}

	/**
	 * Return the slugs of generic iconsets.
	 *
	 * @return array<int,string>
	 */
	public function get_generic_sets_as_slug_array(): array {
		// define list.
		$list = array();

		// loop through the iconsets.
		foreach ( $this->get_icon_sets() as $iconset_obj ) {
			// bail if this is not a generic iconset.
			if ( ! $iconset_obj->is_generic() ) {
				continue;
			}

			// add the iconset to the list.
			$list[] = $iconset_obj->get_slug();
		}

		// return results.
		return $list;
	}

	/**
	 * Return generic custom post types.
	 *
	 * @return array<int,int>
	 */
	public function get_generic_sets_cpts(): array {
		$query = array(
			'post_type'      => 'dl_icons',
			'post_status'    => array( 'any', 'trash' ),
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'     => 'generic-downloadlist',
					'compare' => 'EXISTS',
				),
			),
		);
		$posts = new WP_Query( $query );

		// bail on no results.
		if ( 0 === $posts->found_posts ) {
			return array();
		}

		// get the resulting entries.
		$list = array();
		foreach ( $posts->get_posts() as $post_id ) {
			if ( $post_id instanceof WP_Post ) {
				continue;
			}
			$list[] = $post_id;
		}
		return $list;
	}

	/**
	 * Get iconset based on slug.
	 *
	 * @param string $slug The slug of the iconset.
	 * @return Iconset_Base|false
	 */
	public function get_iconset_by_slug( string $slug ): Iconset_Base|false {
		foreach ( $this->get_icon_sets() as $iconset_obj ) {
			// bail if it does not match.
			if ( $slug !== $iconset_obj->get_slug() ) {
				continue;
			}

			// return this object as it matches the slug.
			return $iconset_obj;
		}
		return false;
	}

	/**
	 * Return the default iconset.
	 *
	 * @return Iconset_Base|false
	 */
	public function get_default_iconset(): Iconset_Base|false {
		foreach ( $this->get_icon_sets() as $iconset_obj ) {
			// bail if this should not be default.
			if ( ! $iconset_obj->should_be_default() ) {
				continue;
			}

			// return the default iconset.
			return $iconset_obj;
		}
		return false;
	}

	/**
	 * Run the enqueuing (used in frontend and block editor).
	 *
	 * @param array<int,Iconset_Base> $iconsets List of iconsets to enqueue.
	 * @return void
	 */
	public function enqueue_styles_run( array $iconsets = array() ): void {
		// enqueue the main styles.
		wp_enqueue_style( 'downloadlist-iconsets' );

		// if no iconsets are given, use all.
		if ( empty( $iconsets ) ) {
			$iconsets = $this->get_icon_sets();
		}

		// enqueue each style of the configured iconsets.
		foreach ( $iconsets as $iconset_obj ) {
			// add the files of this iconset in frontend.
			foreach ( $iconset_obj->get_style_files() as $file ) {
				wp_enqueue_style( 'downloadlist-' . $file['handle'] );
			}
		}
	}

	/**
	 * Set iconset as default via link-request.
	 *
	 * @return void
	 * @noinspection PhpNoReturnAttributeCanBeAddedInspection
	 */
	public function set_default_by_request(): void {
		check_ajax_referer( 'downloadlist-set_iconset-default', 'nonce' );

		// get the term-ID from request.
		$term_id = ! empty( $_GET['term_id'] ) ? absint( $_GET['term_id'] ) : 0;

		if ( $term_id > 0 ) {
			// set this term-ID as default.
			Helper::set_iconset_default( $term_id );
		}

		// redirect user.
		wp_safe_redirect( (string) wp_get_referer() );
		exit;
	}

	/**
	 * Hide post-entry which are assigned to generated iconsets.
	 *
	 * @param WP_Query $query The Query.
	 * @return void
	 */
	public function hide_generated_iconsets( WP_Query $query ): void {
		// bail if condition is not met.
		if ( ! ( 'dl_icons' === $query->query['post_type'] && is_admin() && $query->is_main_query() ) ) {
			return;
		}

		// add filter for generic iconsets.
		$query->set(
			'meta_query',
			array(
				array(
					'key'     => 'generic-downloadlist',
					'compare' => 'NOT EXISTS',
				),
			)
		);

		// add filter for slugs which are marked as generic iconsets.
		$query->set(
			'tax_query',
			array(
				array(
					'taxonomy' => 'dl_icon_set',
					'terms'    => $this->get_generic_sets_as_slug_array(),
					'field'    => 'slug',
					'operator' => 'NOT IN',
				),
			)
		);
	}

	/**
	 * Return the edit link for all iconsets.
	 *
	 * @return string
	 */
	public function get_edit_link(): string {
		return add_query_arg(
			array(
				'taxonomy'  => 'dl_icon_set',
				'post_type' => 'dl_icons',
			),
			get_admin_url() . 'edit-tags.php'
		);
	}
}
