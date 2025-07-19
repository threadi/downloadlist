<?php
/**
 * File to handle all taxonomies of this plugin.
 *
 * @package download-list-block-with-icons
 */

namespace DownloadListWithIcons\Plugin;

// prevent direct access.

defined( 'ABSPATH' ) || exit;

/**
 * Object to handle all taxonomies of this plugin.
 */
class Taxonomies {

	/**
	 * Instance of this object.
	 *
	 * @var ?Taxonomies
	 */
	private static ?Taxonomies $instance = null;

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
	public static function get_instance(): Taxonomies {
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
		// use hooks.
		add_action( 'init', array( $this, 'register' ) );
		add_action( 'dl_icon_set_pre_add_form', array( $this, 'show_iconset_description' ) );
		add_action( 'dl_icon_lists_pre_add_form', array( $this, 'show_list_description' ) );
		add_filter( 'manage_edit-dl_icon_set_columns', array( $this, 'set_iconset_columns' ) );
		add_filter( 'manage_edit-dl_icon_lists_columns', array( $this, 'set_lists_columns' ) );

		// use our own hooks.
		add_filter( 'downloadlist_taxonomies', array( $this, 'filter_taxonomies' ) );
	}

	/**
	 * Register all taxonomies of this plugin.
	 *
	 * @return void
	 */
	public function register(): void {
		// loop through the taxonomies.
		foreach ( $this->get_taxonomies() as $taxonomy_name => $settings ) {
			// bail if post_types or args are not set.
			if ( ! isset( $settings['post_types'], $settings['args'] ) ) {
				continue;
			}

			// register this taxonomy.
			register_taxonomy( $taxonomy_name, $settings['post_types'], $settings['args'] );

			// register metas for this taxonomy, if set.
			if ( ! empty( $settings['metas'] ) ) {
				foreach ( $settings['metas'] as $meta_key => $meta_settings ) {
					// add term meta for default-marker.
					register_term_meta(
						$taxonomy_name,
						$meta_key,
						$meta_settings
					);
				}
			}
		}
	}

	/**
	 * Return list of all supported taxonomies.
	 *
	 * @return array<string,mixed>
	 */
	private function get_taxonomies(): array {
		$taxonomies = array(
			'dl_icon_set'   => array(
				'post_types' => array( 'dl_icons' ),
				'args'       => array(
					'hierarchical'       => false,
					'labels'             => array(
						'name'          => _x( 'Iconsets', 'taxonomy general name', 'download-list-block-with-icons' ),
						'singular_name' => _x( 'Iconset', 'taxonomy singular name', 'download-list-block-with-icons' ),
						'search_items'  => __( 'Search iconset', 'download-list-block-with-icons' ),
						'edit_item'     => __( 'Edit iconset', 'download-list-block-with-icons' ),
						'update_item'   => __( 'Update iconset', 'download-list-block-with-icons' ),
						'menu_name'     => __( 'Iconsets', 'download-list-block-with-icons' ),
						'add_new'       => __( 'Add new Iconset', 'download-list-block-with-icons' ),
						'add_new_item'  => __( 'Add new Iconset', 'download-list-block-with-icons' ),
						'back_to_items' => __( 'Go to iconsets', 'download-list-block-with-icons' ),
					),
					'public'             => false,
					'show_ui'            => true,
					'show_in_menu'       => true,
					'show_in_nav_menus'  => true,
					'show_admin_column'  => true,
					'show_tagcloud'      => true,
					'show_in_quick_edit' => true,
					'show_in_rest'       => true,
					'query_var'          => true,
					'rewrite'            => true,
					'capabilities'       => array(
						'manage_terms' => 'manage_options',
						'edit_terms'   => 'manage_options',
						'delete_terms' => 'manage_options',
						'assign_terms' => 'manage_options',
					),
				),
				'metas'      => array(
					'default' => array(
						'type'         => 'integer',
						'single'       => true,
						'show_in_rest' => true,
					),
				),
			),
			'dl_icon_lists' => array(
				'post_types' => array( 'attachment' ),
				'args'       => array(
					'hierarchical'       => false,
					'labels'             => array(
						'name'          => _x( 'Download Lists', 'taxonomy general name', 'download-list-block-with-icons' ),
						'singular_name' => _x( 'Download List', 'taxonomy singular name', 'download-list-block-with-icons' ),
						'search_items'  => __( 'Search Download Lists', 'download-list-block-with-icons' ),
						'edit_item'     => __( 'Edit Download List', 'download-list-block-with-icons' ),
						'update_item'   => __( 'Update Download List', 'download-list-block-with-icons' ),
						'menu_name'     => __( 'Download Lists', 'download-list-block-with-icons' ),
						'add_new'       => __( 'Add new Download List', 'download-list-block-with-icons' ),
						'add_new_item'  => __( 'Add new Download List', 'download-list-block-with-icons' ),
						'back_to_items' => __( 'Go to Download Lists', 'download-list-block-with-icons' ),
					),
					'public'             => false,
					'show_ui'            => true,
					'show_in_menu'       => true,
					'show_in_nav_menus'  => true,
					'show_admin_column'  => true,
					'show_tagcloud'      => true,
					'show_in_quick_edit' => true,
					'show_in_rest'       => true,
					'query_var'          => true,
					'rewrite'            => true,
					'capabilities'       => array(
						'manage_terms' => 'manage_options',
						'edit_terms'   => 'manage_options',
						'delete_terms' => 'manage_options',
						'assign_terms' => 'manage_options',
					),
				),
			),
		);

		/**
		 * Filter the taxonomies this plugin is supporting.
		 *
		 * @since 4.0.0 Available since 4.0.0.
		 * @param array<string,mixed> $taxonomies List of taxonomies.
		 */
		return apply_filters( 'downloadlist_taxonomies', $taxonomies );
	}

	/**
	 * Change setting for taxonomies.
	 *
	 * @param array<string,mixed> $taxonomies List of taxonomies.
	 * @return array<string,mixed>
	 */
	public function filter_taxonomies( array $taxonomies ): array {
		// bail if user is logged in.
		if ( is_user_logged_in() ) {
			return $taxonomies;
		}

		// change settings for iconset taxonomy.
		$taxonomies['dl_icon_set']['args']['rewrite']      = false;
		$taxonomies['dl_icon_set']['args']['show_in_rest'] = false;

		// change settings for list taxonomy.
		$taxonomies['dl_icon_lists']['args']['rewrite']      = false;
		$taxonomies['dl_icon_lists']['args']['show_in_rest'] = false;

		// return list of taxonomies.
		return $taxonomies;
	}

	/**
	 * Show description for iconset handling.
	 *
	 * @return void
	 */
	public function show_iconset_description(): void {
		echo '<p>' . esc_html__( 'Icon sets contain information about which icons should be used for which file types.', 'download-list-block-with-icons' ) . '</p>';
	}

	/**
	 * Show description for list handling.
	 *
	 * @return void
	 */
	public function show_list_description(): void {
		echo '<p>' . wp_kses_post( __( 'Download Lists contain files that are to be displayed in a single list of files. Assign files in your media library to these list to show them in the list. This possibility is added by the plugin <em>Download List with Icons</em>.', 'download-list-block-with-icons' ) ) . '</p>';
	}

	/**
	 * Add column for default-marker in iconset-table.
	 *
	 * @param array<string,string> $columns List of columns.
	 * @return array<string,string>
	 */
	public function set_iconset_columns( array $columns ): array {
		// add column for iconset.
		$columns['downloadlist_iconset_default'] = __( 'Default iconset', 'download-list-block-with-icons' );

		// remove count-row.
		unset( $columns['posts'], $columns['description'] );

		// return resulting array.
		return $columns;
	}

	/**
	 * Remove columns in lists-table.
	 *
	 * @param array<string,string> $columns List of columns.
	 * @return array<string,string>
	 */
	public function set_lists_columns( array $columns ): array {
		// remove count-row.
		unset( $columns['posts'], $columns['description'], $columns['slug'] );

		// return resulting array.
		return $columns;
	}
}
