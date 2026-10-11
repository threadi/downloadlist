<?php
/**
 * Tests for folders of folder plugins as source of download lists.
 *
 * @package download-list-block-with-icons
 */

namespace DownloadListWithIcons\Tests\Unit\Plugin;

use DownloadListWithIcons\Plugin\Helper;
use DownloadListWithIcons\Tests\DownloadListTestCase;
use WP_Block_Supports;
use WP_REST_Request;

/**
 * Object to test folders as source of download lists.
 */
class Folders extends DownloadListTestCase {
	/**
	 * The ID of the parent folder.
	 *
	 * @var int
	 */
	private int $folder = 0;

	/**
	 * The ID of the sub folder.
	 *
	 * @var int
	 */
	private int $subfolder = 0;

	/**
	 * The file in the parent folder.
	 *
	 * @var int
	 */
	private int $file_in_folder = 0;

	/**
	 * The file in the sub folder.
	 *
	 * @var int
	 */
	private int $file_in_subfolder = 0;

	/**
	 * Register a folder taxonomy like folder plugins do and add files to it.
	 *
	 * @return void
	 */
	public function set_up(): void {
		parent::set_up();

		// register the taxonomy like Folderly does.
		register_taxonomy(
			'test_folders',
			'attachment',
			array(
				'label'        => 'Test Folders',
				'hierarchical' => true,
				'public'       => false,
			)
		);

		// create the folders.
		$this->folder    = self::factory()->term->create(
			array(
				'taxonomy' => 'test_folders',
				'name'     => 'Manuals',
			)
		);
		$this->subfolder = self::factory()->term->create(
			array(
				'taxonomy' => 'test_folders',
				'name'     => 'Archive',
				'parent'   => $this->folder,
			)
		);

		// create the files.
		$this->file_in_folder    = self::factory()->attachment->create_object(
			array(
				'file'           => 'manual.zip',
				'post_mime_type' => 'application/zip',
				'post_title'     => 'Manual',
			)
		);
		$this->file_in_subfolder = self::factory()->attachment->create_object(
			array(
				'file'           => 'old-manual.zip',
				'post_mime_type' => 'application/zip',
				'post_title'     => 'Old manual',
			)
		);
		wp_set_object_terms( $this->file_in_folder, array( $this->folder ), 'test_folders' );
		wp_set_object_terms( $this->file_in_subfolder, array( $this->subfolder ), 'test_folders' );

		WP_Block_Supports::get_instance()::$block_to_render = array(
			'blockName' => 'downloadlist/list',
		);
	}

	/**
	 * Remove the taxonomy.
	 *
	 * @return void
	 */
	public function tear_down(): void {
		unregister_taxonomy( 'test_folders' );
		parent::tear_down();
	}

	/**
	 * Test the detection of folder taxonomies.
	 *
	 * @return void
	 */
	public function test_get_folder_taxonomies(): void {
		$taxonomies = Helper::get_folder_taxonomies();
		$this->assertArrayHasKey( 'test_folders', $taxonomies );
		$this->assertSame( 'Test Folders', $taxonomies['test_folders'] );

		// our own download lists are no folders.
		$this->assertArrayNotHasKey( 'dl_icon_lists', $taxonomies );
	}

	/**
	 * Test the tree of folders.
	 *
	 * @return void
	 */
	public function test_get_folders(): void {
		$folders = array_values(
			array_filter(
				Helper::get_folders(),
				function ( $folder ) {
					return 'test_folders' === $folder['taxonomy'];
				}
			)
		);
		$this->assertCount( 2, $folders );
		$this->assertSame( 'Manuals', $folders[0]['name'] );
		$this->assertSame( 0, $folders[0]['depth'] );
		$this->assertSame( 'Archive', $folders[1]['name'] );
		$this->assertSame( 1, $folders[1]['depth'] );
		$this->assertSame( 'test_folders:' . $this->subfolder, $folders[1]['id'] );
	}

	/**
	 * Test the output of a block with a folder as source.
	 *
	 * @return void
	 */
	public function test_render_block_with_folder(): void {
		// without sub folders.
		$html = \DownloadListWithIcons\Plugin\Init::get_instance()->render_block(
			array(
				'folder'         => $this->folder,
				'folderTaxonomy' => 'test_folders',
			)
		);
		$this->assertStringContainsString( 'attachment-' . $this->file_in_folder, $html );
		$this->assertStringNotContainsString( 'attachment-' . $this->file_in_subfolder, $html );

		// with sub folders.
		$html = \DownloadListWithIcons\Plugin\Init::get_instance()->render_block(
			array(
				'folder'           => $this->folder,
				'folderTaxonomy'   => 'test_folders',
				'folderSubfolders' => true,
			)
		);
		$this->assertStringContainsString( 'attachment-' . $this->file_in_folder, $html );
		$this->assertStringContainsString( 'attachment-' . $this->file_in_subfolder, $html );

		// a file which has been moved out of the folder is not shown anymore, even if it is saved in the block.
		$html = \DownloadListWithIcons\Plugin\Init::get_instance()->render_block(
			array(
				'files'          => array( array( 'id' => $this->file_in_subfolder ), array( 'id' => $this->file_in_folder ) ),
				'order'          => 'manual',
				'folder'         => $this->folder,
				'folderTaxonomy' => 'test_folders',
			)
		);
		$this->assertStringContainsString( 'attachment-' . $this->file_in_folder, $html );
		$this->assertStringNotContainsString( 'attachment-' . $this->file_in_subfolder, $html );
	}

	/**
	 * Test that nothing is shown if the folder plugin is not available anymore.
	 *
	 * @return void
	 */
	public function test_render_block_with_unknown_folder_taxonomy(): void {
		$html = \DownloadListWithIcons\Plugin\Init::get_instance()->render_block(
			array(
				'folder'         => $this->folder,
				'folderTaxonomy' => 'not_existing_folders',
			)
		);
		$this->assertSame( '', $html );

		// taxonomies which are not for attachments could not be used.
		$html = \DownloadListWithIcons\Plugin\Init::get_instance()->render_block(
			array(
				'folder'         => 1,
				'folderTaxonomy' => 'category',
			)
		);
		$this->assertSame( '', $html );
	}

	/**
	 * Test that a block with a download list shows its files, even if no files are saved in the block.
	 *
	 * @return void
	 */
	public function test_render_block_with_download_list_without_saved_files(): void {
		$term = wp_insert_term( 'My list', 'dl_icon_lists' );
		$this->assertIsArray( $term );
		wp_set_object_terms( $this->file_in_folder, array( $term['term_id'] ), 'dl_icon_lists' );

		$html = \DownloadListWithIcons\Plugin\Init::get_instance()->render_block(
			array(
				'files' => array(),
				'list'  => $term['term_id'],
			)
		);
		$this->assertStringContainsString( 'attachment-' . $this->file_in_folder, $html );
	}

	/**
	 * Test the REST endpoints for folders.
	 *
	 * @return void
	 */
	public function test_rest_endpoints(): void {
		// not allowed for subscribers.
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$response = rest_do_request( new WP_REST_Request( 'GET', '/downloadlist/v1/folders' ) );
		$this->assertSame( 403, $response->get_status() );

		// allowed for editors.
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'editor' ) ) );
		$response = rest_do_request( new WP_REST_Request( 'GET', '/downloadlist/v1/folders' ) );
		$this->assertSame( 200, $response->get_status() );
		$this->assertContains( 'test_folders:' . $this->folder, wp_list_pluck( (array) $response->get_data(), 'id' ) );

		// the files of a folder.
		$request = new WP_REST_Request( 'GET', '/downloadlist/v1/folderfiles' );
		$request->set_param( 'taxonomy', 'test_folders' );
		$request->set_param( 'folder', $this->folder );
		$request->set_param( 'subfolders', true );
		$ids = wp_list_pluck( (array) rest_do_request( $request )->get_data(), 'id' );
		sort( $ids );
		$expected = array( $this->file_in_folder, $this->file_in_subfolder );
		sort( $expected );
		$this->assertSame( $expected, $ids );

		// taxonomies which are no folders return nothing.
		$request->set_param( 'taxonomy', 'category' );
		$this->assertSame( array(), rest_do_request( $request )->get_data() );
	}

	/**
	 * Test that folders could not be used if the support for folders is disabled.
	 *
	 * @return void
	 */
	public function test_folder_support_disabled(): void {
		update_option( 'downloadlist_folder_support', 0 );

		// no folders are detected, except if the setting is ignored.
		$this->assertFalse( Helper::is_folder_support_enabled() );
		$this->assertSame( array(), Helper::get_folder_taxonomies() );
		$this->assertArrayHasKey( 'test_folders', Helper::get_folder_taxonomies( true ) );
		$this->assertSame( array(), Helper::get_folders() );

		// a block with a folder shows nothing.
		$html = \DownloadListWithIcons\Plugin\Init::get_instance()->render_block(
			array(
				'folder'         => $this->folder,
				'folderTaxonomy' => 'test_folders',
			)
		);
		$this->assertSame( '', $html );

		// the REST API returns no folders.
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'editor' ) ) );
		$response = rest_do_request( new WP_REST_Request( 'GET', '/downloadlist/v1/folders' ) );
		$this->assertSame( array(), $response->get_data() );

		// enabled again, the folder is used.
		update_option( 'downloadlist_folder_support', 1 );
		$html = \DownloadListWithIcons\Plugin\Init::get_instance()->render_block(
			array(
				'folder'         => $this->folder,
				'folderTaxonomy' => 'test_folders',
			)
		);
		$this->assertStringContainsString( 'attachment-' . $this->file_in_folder, $html );
	}

	/**
	 * Test that a download list is used, if a block has a download list and a folder.
	 *
	 * @return void
	 */
	public function test_get_list_source_prefers_download_list(): void {
		$source = Helper::get_list_source(
			array(
				'list'           => 5,
				'folder'         => $this->folder,
				'folderTaxonomy' => 'test_folders',
			)
		);
		$this->assertIsArray( $source );
		$this->assertSame( 'dl_icon_lists', $source['taxonomy'] );
		$this->assertSame( 5, $source['term_id'] );

		// without a source.
		$this->assertFalse( Helper::get_list_source( array() ) );
		$this->assertFalse( Helper::get_list_source( array( 'folder' => $this->folder ) ) );
	}

	/**
	 * Test the number of files of a folder in the overview of the usage.
	 *
	 * @return void
	 */
	public function test_usage_table_counts_files_of_folders(): void {
		$content  = '<!-- wp:downloadlist/list {"folder":' . $this->folder . ',"folderTaxonomy":"test_folders"} /-->';
		$content .= '<!-- wp:downloadlist/list {"folder":' . $this->folder . ',"folderTaxonomy":"test_folders","folderSubfolders":true} /-->';
		$this->assertSame( array( 1, 2 ), \DownloadListWithIcons\Plugin\Admin\Usage_Table::get_file_counts( $content ) );
	}
}
