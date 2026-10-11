<?php
/**
 * Creates the content for the Playwright tests: files, a download list, folders and posts with download list blocks.
 *
 * Runs once in WordPress Playground after the plugin has been activated.
 *
 * @package download-list-block-with-icons
 */

require_once '/wordpress/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

/**
 * Add a file to the media library.
 *
 * @param string $file_name The file name in the uploads directory.
 * @param string $content   The content of the file.
 * @param string $title     The title of the file.
 * @param string $mime_type The mime type.
 *
 * @return int
 */
function downloadlist_playwright_add_file( string $file_name, string $content, string $title, string $mime_type ): int {
	$upload = wp_upload_bits( $file_name, null, $content );
	$id     = wp_insert_attachment(
		array(
			'post_title'     => $title,
			'post_mime_type' => $mime_type,
			'post_status'    => 'inherit',
			'post_content'   => $title . ' description',
		),
		$upload['file']
	);
	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $upload['file'] ) );
	return $id;
}

/**
 * Add a published post with the given download list block.
 *
 * @param string               $slug       The slug of the post.
 * @param string               $title      The title of the post.
 * @param array<string,mixed>  $attributes The attributes of the block.
 *
 * @return void
 */
function downloadlist_playwright_add_post( string $slug, string $title, array $attributes ): void {
	wp_insert_post(
		array(
			'post_name'    => $slug,
			'post_title'   => $title,
			'post_status'  => 'publish',
			'post_content' => wp_slash( serialize_block( array(
				'blockName'    => 'downloadlist/list',
				'attrs'        => $attributes,
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			) ) ),
		)
	);
}

// the files.
$image   = downloadlist_playwright_add_file( 'playwright-image.png', (string) file_get_contents( __DIR__ . '/../../Data/test-image.png' ), 'Playwright Image', 'image/png' );
$archive = downloadlist_playwright_add_file( 'playwright-archive.zip', 'PK test archive', 'Playwright Archive', 'application/zip' );
$text    = downloadlist_playwright_add_file( 'playwright-text.txt', 'Playwright text file', 'Playwright Text', 'text/plain' );

// a download list with the archive.
$list = wp_insert_term( 'Playwright List', 'dl_icon_lists' );
wp_set_object_terms( $archive, array( $list['term_id'] ), 'dl_icon_lists' );

// a folder with the image and a subfolder with the text file.
$folder    = wp_insert_term( 'Playwright Folder', 'playwright_folders' );
$subfolder = wp_insert_term( 'Playwright Subfolder', 'playwright_folders', array( 'parent' => $folder['term_id'] ) );
wp_set_object_terms( $image, array( $folder['term_id'] ), 'playwright_folders' );
wp_set_object_terms( $text, array( $subfolder['term_id'] ), 'playwright_folders' );

// the posts with download lists.
downloadlist_playwright_add_post(
	'playwright-files',
	'Playwright Files',
	array(
		'files' => array(
			array( 'id' => $image ),
			array( 'id' => $archive ),
		),
	)
);
downloadlist_playwright_add_post( 'playwright-download-list', 'Playwright Download List', array( 'list' => $list['term_id'] ) );
downloadlist_playwright_add_post(
	'playwright-folder',
	'Playwright Folder',
	array(
		'folder'         => $folder['term_id'],
		'folderTaxonomy' => 'playwright_folders',
	)
);
downloadlist_playwright_add_post(
	'playwright-subfolders',
	'Playwright Subfolders',
	array(
		'folder'           => $folder['term_id'],
		'folderTaxonomy'   => 'playwright_folders',
		'folderSubfolders' => true,
	)
);
downloadlist_playwright_add_post(
	'playwright-preview',
	'Playwright Preview',
	array(
		'files'             => array(
			array( 'id' => $image ),
			array( 'id' => $archive ),
		),
		'showPreview'       => true,
		'previewWidth'      => 64,
		'mediaBorderWidth'  => 1,
		'mediaBorderColor'  => '#dddddd',
		'mediaBorderRadius' => 8,
		'mediaPadding'      => 4,
		'mediaBackground'   => '#f5f5f5',
	)
);
