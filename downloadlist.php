<?php
/**
 * Plugin Name:       Download List Block with Icons
 * Description:       Provides a Gutenberg block for capturing a download list with file type specific icons.
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Version:           1.0.4
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
 */
function downloadlist_init() {
	load_plugin_textdomain( 'downloadlist', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	// Include block only if Gutenberg exists.
	if ( function_exists( 'register_block_type' ) ) {
		register_block_type(__DIR__);
		wp_set_script_translations('downloadlist-list-editor-script', 'downloadlist', plugin_dir_path(__FILE__) . '/languages/');
		wp_enqueue_style( 'downloadlist-list-css', plugins_url( '/build/style-index.css', __FILE__ ));
		wp_enqueue_style('dashicons');
	}
}
add_action( 'init', 'downloadlist_init' );

/**
 * Parse the post_content to replace the blocks HTML-comment with its actual output.
 * This is done here to get the actual media-file data.
 *
 * @param $content
 * @return mixed
 */
function downloadlist_get_content( $content ) {
	$content = get_the_content();

	// check if content has Blocks
	if( has_blocks( $content )) {
		// get the Blocks to parse
		$blocks = parse_blocks($content);
		if (!empty($blocks)) {
			$allBlocks = [];
			// Loop through the Blocks and check if one id downloadlist/list
			foreach ($blocks as $block) {
				if ($block['blockName'] === 'downloadlist/list') {
					if (!empty($block['attrs']['files'])) {
						$output = '<div><ul class="downloadlist-list">';

						// get the configured files for this Block
						foreach ($block['attrs']['files'] as $file) {
							// get the file-id
							$fileId = $file['id'];

							// get the mimetype and split it
							$mimetype = get_post_mime_type($fileId);
							$mimetypeArray = explode("/",$mimetype);
							$type = $mimetypeArray[0];
							$subtype = $mimetypeArray[1];

							// get the Post
							$attachment = get_post($fileId);

							// get the meta-data like JS (like human-readable filesize)
							$file_meta = wp_prepare_attachment_for_js($fileId);

							// add it to output
							$output .= '<li class="file_' . $type . ' file_' . $subtype . '"><a href="' . wp_get_attachment_url($file['id']) . '" download>' . $attachment->post_title . '</a> (' . $file_meta['filesizeHumanReadable'] . ')<br />' . $attachment->post_content . '</li>';
						}
						$output .= '</ul></div>';

						$block['innerHTML'] = $output;
						$block['innerContent'] = [$output];
					}
				}
				if (!empty($block['blockName'])) {
					$allBlocks[] = $block;
				}
			}
			// serialize the updated Blocks to the content
			$content = serialize_blocks($allBlocks);
		}
	}
	return $content;
}
add_filter( 'the_content', 'downloadlist_get_content', 20 );
