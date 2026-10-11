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
use DownloadListWithIcons\Plugin\Settings;
use WP_Post;
use WP_Query;
use WP_Term;
use WP_Term_Query;

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
	 * Initialize the iconsets.
	 *
	 * @return void
	 */
	public function init(): void {
		add_action( 'wp_enqueue_scripts', array( $this, 'register_styles' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'register_styles' ) );
		add_action( 'enqueue_block_assets', array( $this, 'enqueue_styles_in_editor' ), 10, 0 );
		add_action( 'admin_action_downloadlist_iconset_default', array( $this, 'set_default_by_request' ) );
		add_action( 'pre_get_posts', array( $this, 'hide_generated_iconsets' ) );
	}

	/**
	 * Register our own iconset-css-files for enqueuing.
	 *
	 * @return void
	 */
	public function register_styles(): void {
		// generate the style file if it does not exist. Try it only once per hour, e.g. if uploads is not writable.
		$style_path = Helper::get_style_path();
		if ( false === file_exists( $style_path ) && false === get_transient( 'downloadlist_css_generation_tried' ) ) {
			set_transient( 'downloadlist_css_generation_tried', 1, HOUR_IN_SECONDS );
			Helper::generate_css();
		}

		// get global styles. Use the time of the last generation as version, so browsers load a changed file.
		wp_register_style(
			'downloadlist-iconsets',
			Helper::get_style_url(),
			array(),
			file_exists( $style_path ) ? (string) filemtime( $style_path ) : DL_VERSION,
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
	 * Return all iconsets, which are registered.
	 *
	 * @return array<int,Iconset_Base>
	 */
	public function get_icon_sets(): array {
		$list = array();

		/**
		 * Register a single iconset through adding it to the list.
		 *
		 * The iconset must be an object extending "Iconset_Base" and implement Iconset.
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
	 * @param array<int,string> $post_status List of post status to search for.
	 *
	 * @return array<int,int>
	 */
	public function get_generic_sets_cpts( array $post_status = array( 'any', 'trash' ) ): array {
		$query = array(
			'post_type'      => 'dl_icons',
			'post_status'    => $post_status,
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Necessary meta lookup.
				'relation' => 'AND',
				array(
					'key'     => 'generic-downloadlist',
					'compare' => 'EXISTS',
				),
			),
		);
		$posts = new WP_Query( $query );

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
	 * Return iconset based on the slug.
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
	 * Return the preset default iconset.
	 *
	 * @return Iconset_Base|false
	 */
	public function get_preset_default_iconset(): Iconset_Base|false {
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
	 * Run the enqueuing of iconset styles (used in frontend and block editor).
	 *
	 * @param array<int,Iconset_Base> $iconsets List of iconsets to enqueue.
	 * @return void
	 */
	public function enqueue_styles_run( array $iconsets = array() ): void {
		// make sure the styles are registered.
		if ( ! wp_style_is( 'downloadlist-iconsets', 'registered' ) ) {
			$this->register_styles();
		}

		// enqueue the main styles.
		wp_enqueue_style( 'downloadlist-iconsets' );

		// if no iconsets are given, use all.
		if ( empty( $iconsets ) ) {
			$iconsets = $this->get_icon_sets();
		}

		// enqueue each style of the configured iconsets.
		foreach ( $iconsets as $iconset_obj ) {
			// add the files of this iconset in the frontend.
			foreach ( $iconset_obj->get_style_files() as $file ) {
				// bail if handle is empty.
				if ( empty( $file['handle'] ) ) {
					continue;
				}
				wp_enqueue_style( 'downloadlist-' . $file['handle'] );
			}
		}
	}

	/**
	 * Enqueue the styles of all iconsets in the block editor.
	 *
	 * In frontend the styles are enqueued only if the block is used (see Init::enqueue_styles()).
	 *
	 * @return void
	 */
	public function enqueue_styles_in_editor(): void {
		$false = false;
		/**
		 * Load the styles of all iconsets on every page in frontend, e.g. if download lists are loaded via AJAX.
		 *
		 * @since 4.2.0 Available since 4.2.0.
		 * @param bool $false Set to true to load the styles of all iconsets.
		 */
		$load_all = apply_filters( 'downloadlist_enqueue_all_iconsets', $false );

		// bail if this is not the backend (enqueue_block_assets runs also in frontend).
		if ( ! is_admin() && ! $load_all ) {
			return;
		}

		// enqueue all iconsets.
		$this->enqueue_styles_run();
	}

	/**
	 * Set iconset as default via link-request.
	 *
	 * @return void
	 */
	public function set_default_by_request(): void {
		check_ajax_referer( 'downloadlist-set_iconset-default', 'nonce' );

		// bail if capability is missing.
		if ( ! current_user_can( Settings::get_instance()->get_settings_obj()->get_capability() ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to do this.', 'download-list-block-with-icons' ), 403 );
		}

		// get the term-ID from request.
		$term_id = ! empty( $_GET['term_id'] ) ? absint( $_GET['term_id'] ) : 0;

		// set it only if it is an iconset.
		if ( $term_id > 0 && get_term( $term_id, 'dl_icon_set' ) instanceof \WP_Term ) {
			// set this term-ID as default.
			$this->set_default_iconset( $term_id );
		}

		// redirect user.
		wp_safe_redirect( (string) wp_get_referer() );
		exit;
	}

	/**
	 * Hide post-entry, which are assigned to generated iconsets.
	 *
	 * @param WP_Query $query The Query.
	 * @return void
	 */
	public function hide_generated_iconsets( WP_Query $query ): void {
		// bail if condition is not met.
		if ( ! ( isset( $query->query['post_type'] ) && 'dl_icons' === $query->query['post_type'] && is_admin() && $query->is_main_query() ) ) {
			return;
		}

		// add filter for generic iconsets (keep an existing meta query).
		$meta_query = $query->get( 'meta_query' );
		if ( ! is_array( $meta_query ) ) {
			$meta_query = array();
		}
		$meta_query[] = array(
			'key'     => 'generic-downloadlist',
			'compare' => 'NOT EXISTS',
		);
		$query->set( 'meta_query', $meta_query ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Necessary meta lookup.

		// add a filter for slugs, which are marked as generic iconsets (keep an existing tax query).
		$tax_query = $query->get( 'tax_query' );
		if ( ! is_array( $tax_query ) ) {
			$tax_query = array();
		}
		$tax_query[] = array(
			'taxonomy' => 'dl_icon_set',
			'terms'    => $this->get_generic_sets_as_slug_array(),
			'field'    => 'slug',
			'operator' => 'NOT IN',
		);
		$query->set( 'tax_query', $tax_query ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Necessary meta lookup; admin/sync context.
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

	/**
	 * Return the default iconset.
	 *
	 * @return Iconset_Base|false
	 */
	public function get_default_iconset(): Iconset_Base|false {
		// delete all default-marker for icon-sets.
		$query   = array(
			'taxonomy'   => 'dl_icon_set',
			'hide_empty' => false,
			'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Necessary meta lookup.
				array(
					'key'     => 'default',
					'value'   => 1,
					'compare' => '=',
				),
			),
		);
		$results = new WP_Term_Query( $query );

		// bail on no results.
		if ( empty( $results->terms ) ) {
			return false;
		}

		// return its object.
		return $this->get_iconset_by_slug( $results->terms[0]->slug );
	}

	/**
	 * Set given iconset as default.
	 *
	 * @param int $term_id ID of the term to set as default.
	 * @return void
	 */
	public function set_default_iconset( int $term_id ): void {
		// delete all default-marker for icon-sets.
		$query   = array(
			'taxonomy'   => 'dl_icon_set',
			'hide_empty' => false,
		);
		$results = new WP_Term_Query( $query );

		// get the results.
		$terms = $results->get_terms();

		// convert them if terms are not an array.
		if ( ! is_array( $terms ) ) {
			$terms = array( $terms );
		}

		// loop through them.
		foreach ( $terms as $term ) {
			// bail if entry is not a "WP_Term" object.
			if ( ! $term instanceof WP_Term ) {
				continue;
			}

			// delete the default marker.
			delete_term_meta( $term->term_id, 'default' );
		}

		// mark this as default icon-set.
		update_term_meta( $term_id, 'default', 1 );
	}
}
