<?php
/**
 * Plugin Name:       Download List Block with Icons
 * Description:       Provides a Gutenberg Block to manage a download list with file type specific icons.
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Version:           @@VersionNumber@@
 * Author:            Thomas Zwirner
 * Author URI:        https://www.thomaszwirner.de
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       download-list-block-with-icons
 *
 * @package download-list-block-with-icons
 */

// prevent direct access.
defined( 'ABSPATH' ) || exit;

// do nothing if PHP-version is not 8.0 or newer.
if ( PHP_VERSION_ID < 80000 ) { // @phpstan-ignore smaller.alwaysFalse
	return;
}

use downloadlist\Helper;
use downloadlist\Iconset_Base;
use downloadlist\Iconsets;
use downloadlist\Installer;

// save the plugin-path.
const DL_PLUGIN = __FILE__;

// save the plugin-version.
const DL_VERSION = '@@VersionNumber@@';

// save transient-list-name.
const DL_TRANSIENT_LIST = 'downloadlist_transients';

// embed necessary files.
require_once __DIR__ . '/inc/autoload.php';
if ( is_admin() ) {
	require_once __DIR__ . '/inc/admin.php';
}

// get the files.
$dl_iconset_files = glob( plugin_dir_path( DL_PLUGIN ) . 'inc/iconsets/*.php' );

// include all icon-set-files.
if ( is_array( $dl_iconset_files ) ) {
	foreach ( $dl_iconset_files as $filename ) {
		include_once $filename;
	}
}

// on activation or deactivation of this plugin.
register_activation_hook( DL_PLUGIN, array( Installer::get_instance(), 'activation' ) );
register_deactivation_hook( DL_PLUGIN, array( Installer::get_instance(), 'deactivation' ) );

/**
 * Initialize the plugin.
 *
 * @return void
 * @noinspection PhpUnused
 */
function downloadlist_init(): void {
	// bail if block editor is not enabled.
	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}

	// register our custom block type.
	register_block_type(
		__DIR__,
		array(
			'render_callback' => 'downloadlist_render_block',
			'attributes'      => array(
				'files'                  => array(
					'type' => 'array',
				),
				'hideFileSize'           => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'hideDescription'        => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'hideIcon'               => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'hideLink'               => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'linkTarget'             => array(
					'type'    => 'string',
					'default' => 'direct',
				),
				'robots'                 => array(
					'type'    => 'string',
					'default' => 'follow',
				),
				'iconset'                => array(
					'type'    => 'string',
					'default' => '',
				),
				'file_types_set'         => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'preview'                => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'doNotForceDownload'     => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'showDownloadButton'     => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'downloadLinkTarget'     => array(
					'type'    => 'string',
					'default' => '',
				),
				'downloadLinkTargetName' => array(
					'type'    => 'string',
					'default' => '',
				),
				'linkBrowserTarget'      => array(
					'type'    => 'string',
					'default' => '',
				),
				'linkBrowserTargetName'  => array(
					'type'    => 'string',
					'default' => '',
				),
			),
		)
	);

	// add php-vars to our js-script.
	wp_localize_script(
		'downloadlist-list-editor-script',
		'downloadlistJsVars',
		array(
			'downloadlist_nonce' => wp_create_nonce( 'downloadlist-edit-attachment' ),
		)
	);
}
add_action( 'init', 'downloadlist_init' );

/**
 * Register our own iconset-css-files for enqueuing.
 *
 * @return void
 */
function downloadlist_register_styles(): void {
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
	foreach ( Iconsets::get_instance()->get_icon_sets() as $iconset_obj ) {
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
add_action( 'wp_enqueue_scripts', 'downloadlist_register_styles' );

/**
 * Enqueue style if our block is used anywhere in the output.
 *
 * @param string              $block_content The block content.
 * @param array<string,mixed> $block The used block.
 *
 * @return string
 */
function downloadlist_enqueue_styles( string $block_content, array $block ): string {
	// bail if script is already enqueued.
	if ( wp_script_is( 'downloadlist-iconsets' ) ) {
		return $block_content;
	}

	// bail if no block name is set.
	if ( empty( $block['blockName'] ) ) {
		return $block_content;
	}

	// bail if this is not our block.
	if ( 'downloadlist/list' !== $block['blockName'] ) {
		return $block_content;
	}

	// bail if no iconset is configured.
	if ( empty( $block['attrs']['iconset'] ) ) {
		return $block_content;
	}

	// get the object of the used iconset.
	$iconset = Iconsets::get_instance()->get_iconset_by_slug( $block['attrs']['iconset'] );

	// bail if no iconset could be loaded.
	if ( ! $iconset ) {
		return $block_content;
	}

	// enqueue the iconset.
	downloadlist_enqueue_styles_run( array( $iconset ) );

	// return the block content.
	return $block_content;
}
add_filter( 'render_block', 'downloadlist_enqueue_styles', 10, 2 );

/**
 * Run the enqueuing (used in frontend and block editor).
 *
 * @param array<int,Iconset_Base> $iconsets List of iconsets to enqueue.
 * @return void
 */
function downloadlist_enqueue_styles_run( array $iconsets = array() ): void {
	// enqueue the main styles.
	wp_enqueue_style( 'downloadlist-iconsets' );

	// if no iconsets are given, use all.
	if ( empty( $iconsets ) ) {
		$iconsets = Iconsets::get_instance()->get_icon_sets();
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
 * Filter query from media library to show single attachment.
 *
 * @param array<string,mixed> $query The query-array.
 * @return array<string,mixed>
 * @noinspection PhpUnused
 */
function downloadlist_ajax_query_attachments_args( array $query ): array {
	if ( ! empty( $_REQUEST['query']['downloadlist_post_id'] ) && ! empty( $_REQUEST['query']['downloadlist_nonce'] ) && false !== wp_verify_nonce( sanitize_key( $_REQUEST['query']['downloadlist_nonce'] ), 'downloadlist-edit-attachment' ) ) {
		$query['p'] = absint( $_REQUEST['query']['downloadlist_post_id'] );
	}
	return $query;
}
add_filter( 'ajax_query_attachments_args', 'downloadlist_ajax_query_attachments_args' );

/**
 * Add endpoint for requests from our own Block.
 *
 * @return void
 * @noinspection PhpUnused
 */
function downloadlist_add_rest_api(): void {
	register_rest_route(
		'downloadlist/v1',
		'/files/',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'downloadlist_api_return_file_data',
			'permission_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
		)
	);
}
add_action( 'rest_api_init', 'downloadlist_add_rest_api' );

/**
 * Return file data depending on post-IDs in request.
 *
 * @param WP_REST_Request $request The request-object.
 * @return array<int,mixed>
 * @noinspection PhpUnused
 */
function downloadlist_api_return_file_data( WP_REST_Request $request ): array {
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
 * Get template from our own plugin or the used theme.
 *
 * @param string $template The template-path.
 * @return string
 */
function downloadlist_get_template( string $template ): string {
	if ( is_embed() ) {
		return $template;
	}

	// get the template from theme-directory, if available.
	$theme_template = locate_template( trailingslashit( basename( dirname( DL_PLUGIN ) ) ) . $template );
	if ( $theme_template ) {
		return $theme_template;
	}

	// return the path to the plugin-own template.
	return plugin_dir_path( __FILE__ ) . 'templates/' . $template;
}

/**
 * Add icon as custom posttype.
 *
 * @return void
 * @noinspection PhpUnused
 */
function downloadlist_add_position_posttype(): void {
	// set labels for our own cpt.
	$labels = array(
		'name'              => __( 'Download List Icons', 'download-list-block-with-icons' ),
		'singular_name'     => __( 'Download List Icon', 'download-list-block-with-icons' ),
		'menu_name'         => __( 'Download List Icons', 'download-list-block-with-icons' ),
		'parent_item_colon' => __( 'Parent Download List  Icon', 'download-list-block-with-icons' ),
		'all_items'         => __( 'All Icons', 'download-list-block-with-icons' ),
		'add_new'           => __( 'Add new Icon', 'download-list-block-with-icons' ),
		'add_new_item'      => __( 'Add new Icon', 'download-list-block-with-icons' ),
		'edit_item'         => __( 'Edit Icon', 'download-list-block-with-icons' ),
		'view_item'         => __( 'View Download List Icon', 'download-list-block-with-icons' ),
		'view_items'        => __( 'View Download List Icons', 'download-list-block-with-icons' ),
		'search_items'      => __( 'Search Download List Icon', 'download-list-block-with-icons' ),
		'not_found'         => __( 'Not Found', 'download-list-block-with-icons' ),
	);

	// set arguments for our own cpt.
	$args = array(
		'label'               => $labels['name'],
		'description'         => '',
		'labels'              => $labels,
		'supports'            => array( 'title' ),
		'public'              => false,
		'hierarchical'        => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'has_archive'         => false,
		'can_export'          => false,
		'exclude_from_search' => true,
		'taxonomies'          => array( 'dl_icon_set' ),
		'publicly_queryable'  => false,
		'show_in_rest'        => false,
		'capability_type'     => 'post',
		'rewrite'             => array(
			'slug' => 'downloadlist_icons',
		),
		'menu_icon'           => trailingslashit( plugin_dir_url( DL_PLUGIN ) ) . 'gfx/dl_icon.png',
	);
	register_post_type( 'dl_icons', $args );
}
add_action( 'init', 'downloadlist_add_position_posttype', 10 );

/**
 * Add taxonomies used with the downloadlist posttype.
 * Each will be visible in REST-API, also public.
 *
 * @return void
 * @noinspection PhpUnused
 */
function downloadlist_add_taxonomies(): void {
	// set default taxonomy-settings.
	$icon_set_array = array(
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
	);

	// remove this taxonomy from views for not logged-in users.
	if ( ! is_user_logged_in() ) {
		$icon_set_array['rewrite']      = false;
		$icon_set_array['show_in_rest'] = false;
	}

	// register taxonomy.
	register_taxonomy( 'dl_icon_set', array( 'dl_icons' ), $icon_set_array );

	// add term meta for default-marker.
	register_term_meta(
		'dl_icon_set',
		'default',
		array(
			'type'         => 'integer',
			'single'       => true,
			'show_in_rest' => true,
		)
	);
}
add_action( 'init', 'downloadlist_add_taxonomies' );

/**
 * Register WP Cli.
 *
 * @noinspection PhpUnused
 */
function downloadlist_cli_register_commands(): void {
	WP_CLI::add_command( 'download-list-block-with-icons', 'downloadlist\cli' );
}
add_action( 'cli_init', 'downloadlist_cli_register_commands' );

/**
 * Add endpoint for requests from our own Block.
 *
 * @return void
 * @noinspection PhpUnused
 */
function downloadlist_rest_api(): void {
	register_rest_route(
		'downloadlist/v1',
		'/filetypes/',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'downloadlist_rest_api_filetypes',
			'permission_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
		)
	);
}
add_action( 'rest_api_init', 'downloadlist_rest_api' );

/**
 * Return possible file-types for Block Editor via REST API.
 *
 * @param WP_REST_Request $request The request-object.
 * @return array<int,array<string,mixed>>
 * @noinspection PhpUnused
 */
function downloadlist_rest_api_filetypes( WP_REST_Request $request ): array {
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

/**
 * Add our own image sizes for icons.
 *
 * @return void
 */
function downloadlist_add_image_size(): void {
	// get all iconsets.
	$query   = array(
		'taxonomy'   => 'dl_icon_set',
		'hide_empty' => false,
	);
	$results = new WP_Term_Query( $query );

	// get the results.
	$terms = $results->get_terms();

	// convert result if it is not an array.
	if ( ! is_array( $terms ) ) {
		$terms = array( $terms );
	}

	// loop through the results.
	foreach ( $terms as $term ) {
		// bail if item is not a term.
		if ( ! $term instanceof WP_Term ) {
			continue;
		}

		// get iconset as object.
		$iconset_obj = Iconsets::get_instance()->get_iconset_by_slug( $term->slug );

		// bail if this is an iconset without generated images.
		if ( $iconset_obj instanceof Iconset_Base && false === $iconset_obj->is_gfx() ) {
			continue;
		}

		// get width and height set on this iconset.
		$width  = absint( get_term_meta( $term->term_id, 'width', true ) );
		$height = absint( get_term_meta( $term->term_id, 'height', true ) );

		// bail if no width or height is available.
		if ( 0 === $width || 0 === $height ) {
			continue;
		}

		// set image size.
		add_image_size( 'downloadlist-icon-' . $term->slug, $width, $height );
	}
}
add_action( 'after_setup_theme', 'downloadlist_add_image_size' );

/**
 * Render a single downloadlist-block.
 *
 * This is the main function for output in editor and frontend.
 *
 * @param array<string,mixed> $attributes List of attributes for this block.
 * @return string
 * @noinspection PhpUnused
 */
function downloadlist_render_block( array $attributes ): string {
	// bail if no files are given.
	if ( empty( $attributes['files'] ) ) {
		return '';
	}

	// collect the output.
	$output = '';

	// hide icon if set.
	$hide_icon = '';
	if ( ! empty( $attributes['hideIcon'] ) ) {
		$hide_icon = ' hide-icon';
	}

	// marker for icon-set to use.
	$iconset     = '';
	$iconset_obj = null;
	if ( ! empty( $attributes['iconset'] ) ) {
		$iconset     = 'iconset-' . $attributes['iconset'];
		$iconset_obj = Iconsets::get_instance()->get_iconset_by_slug( $attributes['iconset'] );
		// if no iconset could be detected, get the default iconset.
		if ( false === $iconset_obj ) {
			$iconset_obj = Iconsets::get_instance()->get_default_iconset();
			if ( ! $iconset_obj ) {
				$iconset = 'iconset-generic';
			} else {
				$iconset = 'iconset-' . $iconset_obj->get_slug();
			}
		}
	} else {
		// set default iconset if none is set (for lists from < 3.0).
		$iconset_obj = Iconsets::get_instance()->get_default_iconset();
		if ( ! $iconset_obj ) {
			$iconset = 'iconset-generic';
		} else {
			$iconset = 'iconset-' . $iconset_obj->get_slug();
		}
	}

	// variable for block-specific styles.
	$styles = '';

	// get Block Editor wrapper attributes.
	$wrapper_attributes = get_block_wrapper_attributes();

	// generate begin of the file-list.
	ob_start();
	include downloadlist_get_template( 'list-start.php' );
	$output = ob_get_clean();

	// get the configured files for this Block.
	foreach ( $attributes['files'] as $file ) {
		// get the file-id.
		$file_id = $file['id'];

		// get the mimetype.
		$mimetype = get_post_mime_type( $file_id );

		// if nothing could be loaded do not output anything.
		if ( empty( $mimetype ) ) {
			continue;
		}

		// split the mimetype to get type and subtype.
		list( $type, $subtype ) = Helper::get_type_and_subtype_from_mimetype( $mimetype );

		// get the post.
		$attachment = get_post( $file_id );

		// bail if attachment could not be loaded.
		if ( ! $attachment instanceof WP_Post ) {
			continue;
		}

		// get the meta-data like JS (like human-readable filesize).
		$file_meta = wp_prepare_attachment_for_js( $file_id );

		// get custom attachment title, if set.
		if ( isset( $file_meta['title'] ) ) {
			$attachment->post_title = $file_meta['title'];
		}

		// use filename if no title is set.
		if ( empty( $attachment->post_title ) && isset( $file_meta['filename'] ) ) {
			$attachment->post_title = $file_meta['filename'];
		}

		// get custom attachment description, if set.
		if ( isset( $file_meta['description'] ) ) {
			$attachment->post_content = $file_meta['description'];
		}

		// get the file size.
		$filesize = '';
		if ( empty( $attributes['hideFileSize'] ) && ! empty( $file_meta['filesizeHumanReadable'] ) ) {
			$filesize = ' (' . $file_meta['filesizeHumanReadable'] . ')';
		}

		// get the description.
		$description = '<br />' . $attachment->post_content;
		if ( ! empty( $attributes['hideDescription'] ) || empty( $attachment->post_content ) ) {
			$description = '';
		}

		// get the download URL of the file.
		$url                     = wp_get_attachment_url( $file_id );
		$download_link_attribute = ' download';
		if ( ! empty( $attributes['linkTarget'] ) && 'attachmentpage' === $attributes['linkTarget'] && 1 === absint( get_option( 'wp_attachment_pages_enabled', 1 ) ) ) {
			$url                     = get_permalink( $file_id );
			$download_link_attribute = '';
		}

		// prevent forcing of download via html-attribute.
		if ( ! empty( $attributes['linkTarget'] ) && 'direct' === $attributes['linkTarget'] && ! empty( $attributes['doNotForceDownload'] ) ) {
			$download_link_attribute = '';
		}

		/**
		 * Filter the download attribute for the link.
		 *
		 * @since 3.6.0 Available since 3.6.0.
		 * @param string $download_link_attribute The value.
		 * @param array $file The attributes for single file.
		 */
		$download_link_attribute = apply_filters( 'downloadlist_link_download_attribute', $download_link_attribute, $file );

		// if we have a link target set, use this.
		$link_target = '';
		if ( ! empty( $attributes['linkBrowserTarget'] ) ) {
			$link_target = $attributes['linkBrowserTarget'];
			if ( 'own' === $link_target ) {
				$link_target = '';
				if ( ! empty( $attributes['linkBrowserTargetName'] ) ) {
					$link_target = $attributes['linkBrowserTargetName'];
				}
			}
		}

		/**
		 * Filter the target attribute for the link.
		 *
		 * @since 3.6.0 Available since 3.6.0.
		 * @param string $link_target The value.
		 * @param array $file The attributes for single file.
		 */
		$link_target = apply_filters( 'downloadlist_link_target_attribute', $link_target, $file );

		// set rel-attribute.
		$rel_attribute = '';
		if ( ! empty( $attributes['robots'] ) && 'follow' !== $attributes['robots'] ) {
			$rel_attribute = $attributes['robots'];
		}

		/**
		 * Filter the rel-attribute.
		 *
		 * @since 3.5.0 Available since 3.5.0
		 * @param string $rel_attribute The rel-value.
		 * @param array $file The attributes for single file.
		 */
		$rel_attribute = apply_filters( 'downloadlist_rel_attribute', $rel_attribute, $file );

		// get individual styles for this file from used iconset.
		if ( $iconset_obj instanceof Iconset_Base ) {
			$styles .= $iconset_obj->get_style_for_file( $file_id );
		}

		// get optional download-button.
		$download_button = '';
		if ( ! empty( $attributes['showDownloadButton'] ) ) {
			// add the download attribute.
			$download_button_attribute = ' download';

			/**
			 * Filter the download attribute for the download button.
			 *
			 * @since 3.6.0 Available since 3.6.0.
			 * @param string $download_button The value.
			 * @param array $file The attributes for single file.
			 */
			$download_button = apply_filters( 'downloadlist_download_button_download_attribute', $download_button, $file );

			// get the link target for the download button.
			$download_target_attribute = '';
			if ( ! empty( $attributes['downloadLinkTarget'] ) ) {
				$download_target_attribute = $attributes['downloadLinkTarget'];
				if ( 'own' === $download_target_attribute ) {
					$download_target_attribute = '';
					if ( ! empty( $attributes['downloadLinkTargetName'] ) ) {
						$download_target_attribute = $attributes['downloadLinkTargetName'];
					}
				}
			}

			/**
			 * Filter the target attribute for the download button.
			 *
			 * @since 3.6.0 Available since 3.6.0.
			 * @param string $download_target_attribute The value.
			 * @param array $file The attributes for single file.
			 */
			$download_target_attribute = apply_filters( 'downloadlist_download_button_target_attribute', $download_target_attribute, $file );

			ob_start();
			include downloadlist_get_template( 'button-download.php' );
			$download_button = ob_get_clean();
		}

		// if text should be output instead of link, use the other template.
		ob_start();
		if ( false !== $attributes['hideLink'] ) {
			// add the not-linked entry to output.
			include downloadlist_get_template( 'list-item-not-linked.php' );
		} else {
			// add the linked entry to output.
			include downloadlist_get_template( 'list-item.php' );
		}
		$output .= ob_get_clean();
	}

	// generate end of the file-list.
	ob_start();
	include downloadlist_get_template( 'list-end.php' );
	$output .= ob_get_clean();

	// output block-specific style.
	if ( ! empty( $styles ) ) {
		$output .= '<style>' . $styles . '</style>';
	}

	// return resulting output.
	return $output;
}

/**
 * Update the messages after updating or deleting terms in our taxonomy.
 *
 * @param array<string,array<int,string>> $messages List of messages.
 * @return array<string,array<int,string>>
 */
function downloadlist_updated_shows_messages( array $messages ): array {
	$messages['dl_icon_set'] = array(
		1 => __( 'Iconset added.', 'download-list-block-with-icons' ),
		3 => __( 'Iconset updated.', 'download-list-block-with-icons' ),
		6 => __( 'Iconset deleted.', 'download-list-block-with-icons' ),
	);
	return $messages;
}
add_filter( 'term_updated_messages', 'downloadlist_updated_shows_messages' );

/**
 * Update the messages after updating or deleting posts in our cpt.
 *
 * @param array<string,array<int,string>> $messages List of messages.
 * @return array<string,array<int,string>>
 */
function downloadlist_change_post_labels( array $messages ): array {
	$messages['dl_icons'] = array(
		1 => __( 'Icon updated.', 'download-list-block-with-icons' ),
		6 => __( 'Icon added.', 'download-list-block-with-icons' ),
	);
	return $messages;
}
add_filter( 'post_updated_messages', 'downloadlist_change_post_labels' );

/**
 * Update the messages on bulk-actions in our cpt.
 *
 * @param array<string,array<string,mixed>> $messages List of messages.
 * @param array<string,mixed>               $bulk_counts Count of events.
 * @return array<string,array<string,mixed>>
 */
function downloadlist_change_post_labels_bulk( array $messages, array $bulk_counts ): array {
	/* translators: %1$d: Number of pages. */
	$messages['dl_icons']['trashed'] = _n( '%1$d icon moved to the trash.', '%1$d icons moved to the trash.', absint( $bulk_counts['trashed'] ) );
	/* translators: %1$d: Number of pages. */
	$messages['dl_icons']['untrashed'] = _n( '%1$d icon restored from the trash.', '%1$d icon restored from the trash.', absint( $bulk_counts['untrashed'] ) );

	// return resulting list.
	return $messages;
}
add_filter( 'bulk_post_updated_messages', 'downloadlist_change_post_labels_bulk', 10, 2 );

/**
 * Use custom title and description for attachment.
 *
 * @param array<string,mixed> $response Array with response for JS.
 * @param WP_Post             $attachment The attachment-object.
 * @return array<string,mixed>
 */
function downloadlist_wp_prepare_attachment_for_js( array $response, WP_Post $attachment ): array {
	// bail if nonce does not match.
	if ( ! empty( $_REQUEST['query']['downloadlist_nonce'] ) && false === wp_verify_nonce( sanitize_key( $_REQUEST['query']['downloadlist_nonce'] ), 'downloadlist-edit-attachment' ) ) {
		return $response;
	}

	// bail if attachment-data are queried for attachment-edit-page.
	if ( ! empty( $_REQUEST['action'] ) && 'query-attachments' === $_REQUEST['action'] ) {
		return $response;
	}

	// get actual custom title.
	$dl_title = get_post_meta( $attachment->ID, 'dl_title', true );
	if ( ! empty( $dl_title ) ) {
		$response['title'] = $dl_title;
	}

	// get actual custom description.
	$dl_description = get_post_meta( $attachment->ID, 'dl_description', true );
	if ( ! empty( $dl_description ) ) {
		$response['description'] = nl2br( $dl_description );
	}

	// return resulting response.
	return $response;
}
add_filter( 'wp_prepare_attachment_for_js', 'downloadlist_wp_prepare_attachment_for_js', 10, 2 );

/**
 * Sanitize the class names generated from mime types.
 *
 * @param string $class_name The given class name.
 * @return string
 */
function downloadlist_generate_classname( string $class_name ): string {
	return sanitize_html_class( $class_name );
}
add_filter( 'downloadlist_generate_classname', 'downloadlist_generate_classname' );
