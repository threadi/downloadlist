<?php
/**
 * Plugin Name:       Download List Block with Icons
 * Description:       Provides a Gutenberg block for capturing a download list with file type specific icons.
 * Requires at least: 5.8
 * Requires PHP:      8.0
 * Version:           @@VersionNumber@@
 * Author:            Thomas Zwirner
 * Author URI:		  https://www.thomaszwirner.de
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       downloadlist
 *
 * @package           downloadlist
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use downloadlist\Helper;
use downloadlist\Iconsets;
use downloadlist\Installer;

// save the plugin-path.
const DL_PLUGIN = __FILE__;

// embed necessary files.
require_once 'inc/autoload.php';
if( is_admin() ) {
	require_once 'inc/admin.php';
}
// include all icon-set-files.
foreach (glob(plugin_dir_path(DL_PLUGIN)."inc/iconsets/*.php") as $filename) {
	include_once $filename;
}

// on activation or deactivation of this plugin
register_activation_hook( DL_PLUGIN, array( Installer::get_instance(), 'activation' ) );
register_deactivation_hook( DL_PLUGIN, array( Installer::get_instance(), 'deactivation' ) );

/**
 * Initialize the plugin.
 *
 * @return void
 * @noinspection PhpUnused
 */
function downloadlist_init(): void {
	load_plugin_textdomain( 'downloadlist', false, dirname( plugin_basename( DL_PLUGIN ) ) . '/languages' );

	// Include block only if Gutenberg exists.
	if ( function_exists( 'register_block_type' ) ) {
		register_block_type(__DIR__, array(
			'render_callback' => 'downloadlist_render_block',
			'attributes' => array(
				"files" => array(
					"type" => "array"
				),
				"hideFileSize" => array(
					"type" => "boolean",
					"default" => false
				),
				"hideDescription" => array(
					"type" => "boolean",
					"default" => false
				),
				"hideIcon" => array(
					"type" => "boolean",
					"default" => false
				),
				"linkTarget" => array(
					"type" => "string",
					"default" => "direct"
				),
				"iconset" => array(
					"type" => "string",
					"default" => ""
				),
				"file_types_set" => array(
					"type" => "boolean",
					"default" => false
				),
				"preview" => array(
					"type" => "boolean",
					"default" => false
				)
			)
		));
		wp_set_script_translations('downloadlist-list-editor-script', 'downloadlist', plugin_dir_path(DL_PLUGIN) . '/languages/');
		wp_enqueue_style( 'downloadlist-list-css', plugins_url( '/block/style-index.css', DL_PLUGIN ));
	}
}
add_action( 'init', 'downloadlist_init' );

/**
 * Embed iconset-css.
 *
 * @return void
 */
function downloadlist_enqueue_styles(): void {
	// get global styles.
	wp_enqueue_style(
		'downloadlist-iconsets',
		helper::get_style_url()
	);

	// get iconset-styles.
	foreach( Iconsets::get_instance()->get_icon_sets() as $iconset_obj ) {
		foreach( $iconset_obj->get_style_files() as $file ) {
			if( !empty($file['handle']) && !empty($file['path']) ) {
				wp_enqueue_style(
					$file['handle'],
					$file['path']
				);
			}
			if( !empty($file['handle']) && empty($file['path']) ) {
				wp_enqueue_style(
					$file['handle']
				);
			}
		}
	}
}
add_action( 'wp_enqueue_scripts', 'downloadlist_enqueue_styles', PHP_INT_MAX );

/**
 * Filter query from media library to show single attachment.
 *
 * @param $query
 * @return mixed
 * @noinspection PhpUnused
 */
function downloadlist_ajax_query_attachments_args($query) {
	if( !empty($_REQUEST['query']['downloadlist_post_id']) ) {
		$query['p'] = absint($_REQUEST['query']['downloadlist_post_id']);
	}
	return $query;
}
add_filter( 'ajax_query_attachments_args', 'downloadlist_ajax_query_attachments_args');

/**
 * Add endpoint for requests from our own Block.
 *
 * @return void
 * @noinspection PhpUnused
 */
function downloadlist_add_rest_api() {
	register_rest_route( 'downloadlist/v1', '/files/', array(
		'methods' => WP_REST_SERVER::READABLE,
		'callback' => 'downloadlist_api_return_file_data',
		'permission_callback' => function () {
			return current_user_can( 'edit_posts' );
		}
	) );
}
add_action( 'rest_api_init', 'downloadlist_add_rest_api');

/**
 * Return file data depending on post-IDs in request.
 *
 * @param WP_REST_Request $request
 * @return string[]
 * @noinspection PhpUnused
 */
function downloadlist_api_return_file_data( WP_REST_Request $request ): array {
	// get the post_ids from request
	$postIds = $request->get_param( 'post_id' );
	if( !empty($postIds) ) {
		// get the file data
		$array = array();
		foreach( $postIds as $postId ) {
			$js = wp_prepare_attachment_for_js($postId);
			if( !empty($js) ) {
				$array[] = $js;
			}
		}

		// return the collected file data
		return $array;
	}
	return array();
}

/**
 * Return the block-content for widgets which use our block.
 *
 * @param $content
 * @param $instance
 * @return string
 * @noinspection PhpUnused
 */
function downloadlist_get_widget_block_content($content, $instance): string {
	if( false === strpos($instance['content'], 'wp:downloadlist/list') ) {
		return $content;
	}
	return downloadlist_get_content($instance['content']);
}
//add_filter( 'widget_block_content', 'downloadlist_get_widget_block_content', 10, 2);

/**
 * Get template from own plugin or theme.
 *
 * @param $template
 * @return mixed|string
 */
function downloadlist_get_template( $template )
{
	if (is_embed()) {
		return $template;
	}

	$themeTemplate = locate_template(trailingslashit(basename(dirname(__FILE__))) . $template);
	if ($themeTemplate) {
		return $themeTemplate;
	}
	return plugin_dir_path(__FILE__) . 'templates/' . $template;
}

/**
 * Add icon as custom posttype.
 *
 * @return void
 * @noinspection PhpUnused
 */
function downloadlist_add_position_posttype(): void {
	// set labels for our own cpt.
	$labels = [
		'name'                => __( 'Download List Icons', 'downloadlist' ),
		'singular_name'       => __( 'Download List Icon', 'downloadlist'),
		'menu_name'           => __( 'Download List Icons', 'downloadlist'),
		'parent_item_colon'   => __( 'Parent Download List  Icon', 'downloadlist'),
		'all_items'           => __( 'All Icons', 'downloadlist'),
		'add_new'			  => __( 'Add new Icon', 'downloadlist' ),
		'add_new_item'		  => __( 'Add new Icon', 'downloadlist' ),
		'view_item'           => __( 'View Download List Icon', 'downloadlist'),
		'view_items'          => __( 'View Download List Icons', 'downloadlist'),
		'search_items'        => __( 'Search Download List Icon', 'downloadlist'),
		'not_found'           => __( 'Not Found', 'downloadlist'),
		'not_found_in_trash'  => __( 'Not found in Trash', 'downloadlist')
	];

	// set arguments for our own cpt.
	$args = [
		'label'               => $labels['name'],
		'description'         => '',
		'labels'              => $labels,
		'supports'            => array(),
		'public'              => true,
		'hierarchical'        => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'has_archive'         => false,
		'can_export'          => false,
		'exclude_from_search' => false,
		'taxonomies' 	      => array( 'dl_icon_set' ),
		'publicly_queryable'  => false,
		'show_in_rest'        => false,
		'capability_type'     => 'post',
		'rewrite' => [
			'slug' => 'downloadlist_icons'
		]
	];
	register_post_type( 'dl_icons', $args );

	// disable block editor for our own cpt.
	remove_post_type_support( 'dl_icons', 'editor' );
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
	$icon_set_array = [
		'hierarchical' => false,
		'labels' => array(
			'name' => _x( 'Iconsets', 'taxonomy general name', 'downloadlist' ),
			'singular_name' => _x( 'Iconset', 'taxonomy singular name', 'downloadlist' ),
			'search_items' =>  __( 'Search iconset', 'downloadlist' ),
			'edit_item' =>  __( 'Edit iconset', 'downloadlist' ),
			'update_item' 	=> __( 'Update iconset', 'downloadlist' ),
			'menu_name' 	=> __( 'Iconsets', 'downloadlist' ),
			'add_new'	    => __( 'Add new Iconset', 'downloadlist' ),
			'add_new_item'  => __( 'Add new Iconset', 'downloadlist' ),
		),
		'public' => false,
		'show_ui' => true,
		'show_in_menu' => true,
		'show_in_nav_menus' => true,
		'show_admin_column' => true,
		'show_tagcloud' => true,
		'show_in_quick_edit' => true,
		'show_in_rest' => true,
		'query_var' => true,
		'rewrite' => true,
		'capabilities' => [
			'manage_terms' => 'manage_options',
			'edit_terms' => 'manage_options',
			'delete_terms' => 'manage_options',
			'assign_terms' => 'manage_options',
		]
	];

	// remove slugs for not logged in users.
	if( !is_user_logged_in() ) {
		$icon_set_array['rewrite'] = false;
	}

	// register taxonomy
	register_taxonomy('dl_icon_set', array( 'dl_icons' ), $icon_set_array);

	// add term meta for default-marker.
	register_term_meta( 'dl_icon_set', 'default', array(
		'type' => 'integer',
		'single' => true,
		'show_in_rest' => true
	) );
}
add_action( 'init', 'downloadlist_add_taxonomies' );

/**
 * Register WP Cli.
 *
 * @noinspection PhpUnused
 * @noinspection PhpUndefinedClassInspection
 */
function downloadlist_cli_register_commands(): void {
	WP_CLI::add_command('downloadlist', 'downloadlist\cli');
}
add_action( 'cli_init', 'downloadlist_cli_register_commands' );

/**
 * Add endpoint for requests from our own Blocks.
 *
 * @return void
 * @noinspection PhpUnused
 */
function downloadlist_rest_api(): void
{
	register_rest_route( 'downloadlist/v1', '/filetypes/', array(
		'methods' => WP_REST_SERVER::READABLE,
		'callback' => 'downloadlist_rest_api_filetypes',
		'permission_callback' => function () {
			return current_user_can( 'edit_posts' );
		}
	) );
}
add_action( 'rest_api_init', 'downloadlist_rest_api');

/**
 * Return possible file-types for Block Editor via REST API.
 *
 * @param WP_REST_Request $request The request-object.
 * @return array
 * @noinspection PhpUnused
 */
function downloadlist_rest_api_filetypes( WP_REST_Request $request ): array {
	$iconset = sanitize_text_field($request->get_param( 'iconset' ));

	// bail if no iconset is given.
	if( empty($iconset) ) {
		return array();
	}

	// get the iconset as object
	$iconset_obj = Iconsets::get_instance()->get_iconset_by_slug( $iconset );

	// bail if no matching iconset was found.
	if( false === $iconset_obj ) {
		return array();
	}

	// get the file-types for this iconset.
	$file_types = $iconset_obj->get_file_types();

	// convert array to object-array so every entry has its own index.
	$resulting_array = array();
	foreach( $file_types as $key => $file_type ) {
		$resulting_array[] = array(
			'id' => $key,
			'value' => $file_type
		);
	}

	// return resulting list.
	return $resulting_array;
}

/**
 * Add our own image sizes for icons.
 *
 * @return void
 */
function downloadlist_add_image_size(): void {
	// get all iconsets.
	$query = array(
		'taxonomy' => 'dl_icon_set',
		'hide_empty' => false,
	);
	$results = new WP_Term_Query( $query );
	foreach( $results->get_terms() as $term ) {
		// get iconset as object.
		$iconset_obj = Iconsets::get_instance()->get_iconset_by_slug( $term->slug );

		// bail if this is a generic iconset with font as base and not images.
		if( $iconset_obj->is_generic() ) {
			continue;
		}

		// get width and height set on this iconset.
		$width = absint(get_term_meta( $term->term_id, 'width', true ));
		$height = absint(get_term_meta( $term->term_id, 'height', true ));

		// bail if no width or height is available.
		if( 0 === $width || 0 === $height ) {
			continue;
		}

		// set image size.
		add_image_size( 'downloadlist-icon-'.$term->slug, $width, $height );
	}
}
add_action( 'after_setup_theme', 'downloadlist_add_image_size' );

function downloadlist_render_block( $attributes ): string {
	$output = '';

	// get block-classes
	$block_html_attributes = get_block_wrapper_attributes();

	if (!empty($attributes['files'])) {
		// hide icon if set
		$hide_icon = '';
		if(!empty($attributes['hideIcon'])) {
			$hide_icon = ' hide-icon';
		}

		// marker for icon-set to use.
		$iconset = '';
		if(!empty($attributes['iconset'])) {
			$iconset = 'iconset-'.$attributes['iconset'];
		}

		// get Block Editor wrapper attributes.
		$wrapper_attributes = get_block_wrapper_attributes();

		ob_start();
		include downloadlist_get_template('list-start.php');
		$output = ob_get_clean();

		// get the configured files for this Block
		foreach ($attributes['files'] as $file) {
			// get the file-id
			$fileId = $file['id'];

			// get the mimetype
			$mimetype = get_post_mime_type($fileId);

			// if nothing could be loaded do not output anything
			if( empty($mimetype) ) {
				continue;
			}

			// split the mimetype to get type and subtype
			$mimetypeArray = explode("/", $mimetype);
			$type = $mimetypeArray[0];
			$subtype = $mimetypeArray[1];

			// get the Post
			$attachment = get_post($fileId);

			// get the meta-data like JS (like human-readable filesize)
			$file_meta = wp_prepare_attachment_for_js($fileId);

			// get the file size
			$fileSize =  ' (' . $file_meta['filesizeHumanReadable'] . ')';
			if(!empty($attributes['hideFileSize'])) {
				$fileSize = '';
			}

			// get the description
			$description =  '<br />'.$attachment->post_content;
			if(!empty($attributes['hideDescription'])) {
				$description = '';
			}

			// get download URL
			$url = wp_get_attachment_url($fileId);
			$downloadAttribute = " download";
			if(!empty($attributes['linkTarget']) && $attributes['linkTarget'] == 'attachmentpage' ) {
				$url = get_permalink($fileId);
				$downloadAttribute = "";
			}

			// add it to output
			ob_start();
			include downloadlist_get_template('list-item.php');
			$output .= ob_get_clean();
		}
		ob_start();
		include downloadlist_get_template('list-end.php');
		$output .= ob_get_clean();
	}

	return $output;
}
