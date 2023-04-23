<?php
/**
 * Plugin Name:       Download List Block with Icons
 * Description:       Provides a Gutenberg block for capturing a download list with file type specific icons.
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Version:           @@VersionNumber@@
 * Author:            Thomas Zwirner
 * Author URI:		  https://www.thomaszwirner.de
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       downloadlist
 *
 * @package           create-block
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Initialize the plugin.
 *
 * @return void
 * @noinspection PhpUnused
 */
function downloadlist_init() {
	load_plugin_textdomain( 'downloadlist', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	// Include block only if Gutenberg exists.
	if ( function_exists( 'register_block_type' ) ) {
		register_block_type(__DIR__);
		wp_set_script_translations('downloadlist-list-editor-script', 'downloadlist', plugin_dir_path(__FILE__) . '/languages/');
		wp_enqueue_style( 'downloadlist-list-css', plugins_url( '/block/style-index.css', __FILE__ ));
		wp_enqueue_style('dashicons');
	}
}
add_action( 'init', 'downloadlist_init' );

add_action( 'admin_enqueue_scripts', function() {
	wp_enqueue_media();
	wp_enqueue_script( 'media-grid' );
	wp_enqueue_script( 'media' );

	// admin-specific styles
	wp_enqueue_style('downloadlist-admin-css',
		plugin_dir_url(__FILE__) . '/admin/style.css',
		[],
		filemtime(plugin_dir_path(__FILE__) . '/admin/style.css'),
	);
});

/**
 * Parse the post_content to replace the blocks HTML-comment with its actual output.
 * This is done here to get the actual media-file data.
 *
 * @param $content
 * @return string
 * @noinspection PhpUnused
 */
function downloadlist_get_content( $content ): string
{
	// check if content has Blocks
	if( has_blocks( $content )) {
		// get the Blocks to parse
		$blocks = parse_blocks($content);
		if (!empty($blocks)) {
			$updatedBlocks = downloadlist_get_content_block_loop($blocks);
			// serialize the updated Blocks to the content
			$content = serialize_blocks($updatedBlocks);
		}
	}
	return $content;
}
add_filter( 'the_content', 'downloadlist_get_content', 5, 1 );

/**
 * Loop through each Block.
 *
 * @param $blocks
 * @return array
 * @noinspection PhpUnused
 */
function downloadlist_get_content_block_loop($blocks): array
{
	$updatedBlocks = [];
	foreach ($blocks as $block) {
		if (!empty($block['blockName'])) {
			if (!empty($block['innerBlocks'])) {
				$block['innerBlocks'] = downloadlist_get_content_block_loop($block['innerBlocks']);
			}
			if ($block['blockName'] === 'downloadlist/list') {
				if (!empty($block['attrs']['files'])) {
					// hide icon if set
					$hide_icon = '';
					if(!empty($block['attrs']['hideIcon'])) {
						$hide_icon = ' hideIcon';
					}

					ob_start();
					include downloadlist_get_template('list-start.php');
					$output = ob_get_clean();

					// get the configured files for this Block
					foreach ($block['attrs']['files'] as $file) {
						// get the file-id
						$fileId = $file['id'];

						// get the mimetype
						$mimetype = get_post_mime_type($fileId);
						if( $fileId < 0 ) {
							$mimetype = '/';
						}

						// if nothing could be loaded do not output anything
						if( empty($mimetype) && $fileId > 0 ) {
							continue;
						}

						// split the mimetype to get type and subtype
						$mimetypeArray = explode("/", $mimetype);
						$type = $mimetypeArray[0];
						$subtype = $mimetypeArray[1];
						if( $fileId < 0 ) {
							$type = $file['type'];
							$subtype = $file['subtype'];
						}

						// get the meta-data like JS (like human-readable filesize)
						$file_meta = wp_prepare_attachment_for_js($fileId);
						if( $fileId < 0 ) {
							$file_meta = $file;
						}

						// get the file size
						$fileSize =  ' (' . (!empty($file_meta['filesizeHumanReadable']) ? $file_meta['filesizeHumanReadable'] : '') . ')';
						if(!empty($block['attrs']['hideFileSize'])) {
							$fileSize = '';
						}

						// get the Post
						$attachment = get_post($fileId);
						if( $fileId < 0 ) {
							$attachment = (object)[
								'post_title' => !empty($file['title']) ? $file['title'] : '',
								'post_content' => !empty($file['description']) ? $file['description'] : ''
							];
						}

						// get the description
						$description =  '<br />'.$attachment->post_content;
						if(!empty($block['attrs']['hideDescription'])) {
							$description = '';
						}

						// get download URL
						$url = wp_get_attachment_url($fileId);
						if( $fileId < 0 ) {
							$url = !empty($file['link']) ? $file['link'] : '';
						}

						$downloadAttribute = " download";
						if(!empty($block['attrs']['linkTarget']) && $block['attrs']['linkTarget'] == 'attachmentpage' ) {
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

					$block['innerHTML'] = $output;
					$block['innerContent'] = [$output];
				}
			}
		}
		$updatedBlocks[] = $block;
	}
	return $updatedBlocks;
}

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
 * Return file data depending on postIds in request.
 *
 * @param WP_REST_Request $request
 * @return string[]
 * @noinspection PhpUnused
 */
function downloadlist_api_return_file_data( WP_REST_Request $request ): array
{
	// get the post_ids from request
	$postIds = $request->get_param( 'post_id' );
	if( !empty($postIds) ) {
		// get the file data
		$array = [];
		foreach( $postIds as $postId ) {
			// if id is < 0 it's an external file
			// -> set only its id
			if( $postId < 0 ) {
				$array[] = ['id' => $postId];
				continue;
			}
			$js = wp_prepare_attachment_for_js($postId);
			if( !empty($js) ) {
				$array[] = $js;
			}
		}

		// return the collected file data
		return $array;
	}
	return [];
}

/**
 * Return the block-content for widgets which use our block.
 *
 * @param $content
 * @param $instance
 * @return string
 * @noinspection PhpUnused
 */
function downloadlist_get_widget_block_content($content, $instance): string
{
	if( false === strpos($instance['content'], 'wp:downloadlist/list') ) {
		return $content;
	}
	return downloadlist_get_content($instance['content']);
}
add_filter( 'widget_block_content', 'downloadlist_get_widget_block_content', 10, 2);

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
