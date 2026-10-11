<?php
/**
 * Tests for class \DownloadListWithIcons\Plugin\Abilities.
 *
 * @package download-list-block-with-icons
 */

namespace DownloadListWithIcons\Tests\Unit\Plugin;

use DownloadListWithIcons\Plugin\Abilities as Plugin_Abilities;
use DownloadListWithIcons\Tests\DownloadListTestCase;
use WP_Abilities_Registry;

/**
 * Object to test the abilities of this plugin.
 */
class Abilities extends DownloadListTestCase {
	/**
	 * A file in a download list.
	 *
	 * @var int
	 */
	private int $file_in_list = 0;

	/**
	 * A file which is chosen directly in a block.
	 *
	 * @var int
	 */
	private int $file_in_block = 0;

	/**
	 * A file which is not used anywhere.
	 *
	 * @var int
	 */
	private int $unused_file = 0;

	/**
	 * The download list.
	 *
	 * @var int
	 */
	private int $list_id = 0;

	/**
	 * The post with a download list block which uses the download list.
	 *
	 * @var int
	 */
	private int $post_with_list = 0;

	/**
	 * The page with a download list block with a chosen file.
	 *
	 * @var int
	 */
	private int $page_with_file = 0;

	/**
	 * Prepare files, a download list and posts using them.
	 *
	 * @return void
	 */
	public function set_up(): void {
		parent::set_up();

		// skip if the Abilities API is not available (WordPress before 6.9).
		if ( ! Plugin_Abilities::is_api_available() ) {
			$this->markTestSkipped( 'The Abilities API is not available.' );
		}

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );

		$this->file_in_list  = self::factory()->attachment->create_object(
			array(
				'file'           => 'list.zip',
				'post_mime_type' => 'application/zip',
				'post_title'     => 'In list',
			)
		);
		$this->file_in_block = self::factory()->attachment->create_object(
			array(
				'file'           => 'block.pdf',
				'post_mime_type' => 'application/pdf',
				'post_title'     => 'In block',
			)
		);
		$this->unused_file   = self::factory()->attachment->create_object(
			array(
				'file'           => 'unused.zip',
				'post_mime_type' => 'application/zip',
				'post_title'     => 'Unused',
			)
		);

		$term = wp_insert_term( 'Manuals', 'dl_icon_lists' );
		$this->assertIsArray( $term );
		$this->list_id = $term['term_id'];
		wp_set_object_terms( $this->file_in_list, array( $this->list_id ), 'dl_icon_lists' );

		$this->post_with_list = self::factory()->post->create( array( 'post_content' => '<!-- wp:downloadlist/list {"list":' . $this->list_id . '} /-->' ) );
		$this->page_with_file = self::factory()->post->create(
			array(
				'post_type'    => 'page',
				'post_content' => '<!-- wp:group --><div class="wp-block-group"><!-- wp:downloadlist/list {"files":[{"id":' . $this->file_in_block . '}]} /--></div><!-- /wp:group -->',
			)
		);
	}

	/**
	 * Execute an ability like an external client does.
	 *
	 * @param string $name  The name of the ability.
	 * @param mixed  $input The input.
	 *
	 * @return mixed
	 */
	private function execute( string $name, mixed $input = null ): mixed {
		$ability = wp_get_ability( $name );
		$this->assertNotNull( $ability, $name );
		return $ability->execute( $input );
	}

	/**
	 * Test that only the reading abilities are registered by default and that they are public.
	 *
	 * @return void
	 */
	public function test_default_registration(): void {
		$this->assertSame( Plugin_Abilities::MODE_READ, Plugin_Abilities::get_mode() );
		$this->assertTrue( Plugin_Abilities::is_enabled() );

		foreach ( Plugin_Abilities::get_instance()->get_ability_names( Plugin_Abilities::MODE_READ ) as $name ) {
			$ability = wp_get_ability( $name );
			$this->assertNotNull( $ability, $name );
			$this->assertTrue( $ability->get_meta_item( 'show_in_rest' ), $name );
			$this->assertTrue( $ability->get_meta()['annotations']['readonly'], $name );
		}

		// the writing abilities are not registered in this mode.
		$this->assertFalse( wp_has_ability( 'downloadlist/assign-files-to-list' ) );
		$this->assertFalse( wp_has_ability( 'downloadlist/add-list-to-post' ) );
	}

	/**
	 * Test the abilities per mode.
	 *
	 * @return void
	 */
	public function test_ability_names_per_mode(): void {
		$read = Plugin_Abilities::get_instance()->get_ability_names( Plugin_Abilities::MODE_READ );
		$all  = Plugin_Abilities::get_instance()->get_ability_names( Plugin_Abilities::MODE_ALL );
		$this->assertCount( 5, $read );
		$this->assertCount( 9, $all );
		$this->assertSame( array(), array_diff( $read, $all ) );
		$this->assertContains( 'downloadlist/inherit-settings', $all );

		// invalid modes are handled as "read".
		update_option( 'downloadlist_abilities_mode', 'everything' );
		$this->assertSame( Plugin_Abilities::MODE_READ, Plugin_Abilities::get_mode() );
	}

	/**
	 * Test that all abilities are registered in mode "all" and none if they are disabled.
	 *
	 * @return void
	 */
	public function test_registration_depends_on_settings(): void {
		$all = Plugin_Abilities::get_instance()->get_ability_names( Plugin_Abilities::MODE_ALL );

		// run the registration again with only our callback.
		$register = function () {
			foreach ( Plugin_Abilities::get_instance()->get_ability_names( Plugin_Abilities::MODE_ALL ) as $name ) {
				if ( wp_has_ability( $name ) ) {
					wp_unregister_ability( $name );
				}
			}
			remove_all_actions( 'wp_abilities_api_init' );
			add_action( 'wp_abilities_api_init', array( Plugin_Abilities::get_instance(), 'register_abilities' ) );
			do_action( 'wp_abilities_api_init', WP_Abilities_Registry::get_instance() );
		};

		// mode "all".
		update_option( 'downloadlist_abilities_mode', Plugin_Abilities::MODE_ALL );
		$register();
		foreach ( $all as $name ) {
			$this->assertTrue( wp_has_ability( $name ), $name );
		}
		$ability = wp_get_ability( 'downloadlist/inherit-settings' );
		$this->assertNotNull( $ability );
		$this->assertTrue( $ability->get_meta()['annotations']['destructive'] );

		// the abilities which change data ask AI agents to confirm with the user, the reading abilities do not.
		$read = Plugin_Abilities::get_instance()->get_ability_names( Plugin_Abilities::MODE_READ );
		foreach ( $all as $name ) {
			$ability = wp_get_ability( $name );
			$this->assertNotNull( $ability, $name );
			if ( in_array( $name, $read, true ) ) {
				$this->assertStringNotContainsString( 'wait for their confirmation', $ability->get_description(), $name );
				continue;
			}
			$this->assertFalse( $ability->get_meta()['annotations']['readonly'], $name );
			$this->assertStringContainsString( 'wait for their confirmation', $ability->get_description(), $name );
		}

		// disabled.
		update_option( 'downloadlist_abilities', 0 );
		$register();
		foreach ( $all as $name ) {
			$this->assertFalse( wp_has_ability( $name ), $name );
		}

		// back to the default.
		delete_option( 'downloadlist_abilities' );
		delete_option( 'downloadlist_abilities_mode' );
		$register();
	}

	/**
	 * Test the ability to get the usage.
	 *
	 * @return void
	 */
	public function test_get_usage(): void {
		$result = $this->execute( 'downloadlist/get-usage' );
		$this->assertIsArray( $result );
		$this->assertSame( 2, $result['total'] );
		$objects = array_column( $result['objects'], null, 'id' );
		$this->assertArrayHasKey( $this->post_with_list, $objects );
		$this->assertSame( 'download_list', $objects[ $this->post_with_list ]['lists'][0]['type'] );
		$this->assertSame( 'Manuals', $objects[ $this->post_with_list ]['lists'][0]['name'] );
		$this->assertSame( 1, $objects[ $this->post_with_list ]['file_count'] );
		$this->assertSame( 'files', $objects[ $this->page_with_file ]['lists'][0]['type'] );
		$this->assertNotEmpty( $objects[ $this->page_with_file ]['edit_url'] );

		// invalid input is rejected.
		$this->assertWPError( $this->execute( 'downloadlist/get-usage', array( 'per_page' => 1000 ) ) );
	}

	/**
	 * Test the ability to find where a file is shown.
	 *
	 * @return void
	 */
	public function test_get_file_usage(): void {
		$result = $this->execute( 'downloadlist/get-file-usage', array( 'file_id' => $this->file_in_list ) );
		$this->assertIsArray( $result );
		$this->assertSame( array( $this->post_with_list ), array_column( $result['objects'], 'id' ) );
		$this->assertSame( array( 'download_list' ), $result['objects'][0]['via'] );
		$this->assertSame( 'In list', $result['file']['title'] );

		$result = $this->execute( 'downloadlist/get-file-usage', array( 'file_id' => $this->file_in_block ) );
		$this->assertSame( array( $this->page_with_file ), array_column( $result['objects'], 'id' ) );

		$result = $this->execute( 'downloadlist/get-file-usage', array( 'file_id' => $this->unused_file ) );
		$this->assertSame( array(), $result['objects'] );

		// no file.
		$this->assertWPError( $this->execute( 'downloadlist/get-file-usage', array( 'file_id' => $this->post_with_list ) ) );
		$this->assertWPError( $this->execute( 'downloadlist/get-file-usage', array() ) );
	}

	/**
	 * Test the abilities to get the download lists and their files.
	 *
	 * @return void
	 */
	public function test_get_sources_and_files(): void {
		$result = $this->execute( 'downloadlist/get-sources' );
		$this->assertIsArray( $result );
		$this->assertContains( 'Manuals', array_column( $result['download_lists'], 'name' ) );
		$this->assertTrue( $result['folder_support'] );

		$result = $this->execute(
			'downloadlist/get-source-files',
			array(
				'type' => 'download_list',
				'id'   => $this->list_id,
			)
		);
		$this->assertIsArray( $result );
		$this->assertSame( array( $this->file_in_list ), array_column( $result['files'], 'id' ) );
		$this->assertSame( 'application/zip', $result['files'][0]['mime_type'] );

		// not existing sources.
		$this->assertWPError(
			$this->execute(
				'downloadlist/get-source-files',
				array(
					'type' => 'download_list',
					'id'   => 999999,
				)
			)
		);
		$this->assertWPError(
			$this->execute(
				'downloadlist/get-source-files',
				array(
					'type'     => 'folder',
					'id'       => $this->list_id,
					'taxonomy' => 'category',
				)
			)
		);
	}

	/**
	 * Test the diagnostics.
	 *
	 * @return void
	 */
	public function test_get_diagnostics(): void {
		$result = $this->execute( 'downloadlist/get-diagnostics' );
		$this->assertIsArray( $result );
		$this->assertSame( 2, $result['files_in_use'] );

		// the PDF has no preview image, the ZIP file could not have one.
		$this->assertSame( array( $this->file_in_block ), array_column( $result['files_without_preview'], 'id' ) );
		$this->assertSame( 1, $result['files_without_preview_type'] );
	}

	/**
	 * Test that the reading abilities need the right to edit posts.
	 *
	 * @return void
	 */
	public function test_permissions_of_reading_abilities(): void {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$this->assertWPError( $this->execute( 'downloadlist/get-usage' ) );
		$this->assertWPError( $this->execute( 'downloadlist/get-sources' ) );
		$this->assertWPError( $this->execute( 'downloadlist/get-file-usage', array( 'file_id' => $this->file_in_list ) ) );

		wp_set_current_user( 0 );
		$this->assertWPError( $this->execute( 'downloadlist/get-diagnostics' ) );
	}

	/**
	 * Test the ability to assign files to a download list.
	 *
	 * @return void
	 */
	public function test_assign_files_to_list(): void {
		$abilities = Plugin_Abilities::get_instance();
		$this->assertTrue( $abilities->can_assign_to_lists() );

		// to a new list by its name.
		$result = $abilities->assign_files_to_list(
			array(
				'file_ids'  => array( $this->unused_file, $this->post_with_list ),
				'list_name' => 'Reports',
			)
		);
		$this->assertIsArray( $result );
		$this->assertTrue( $result['created'] );
		$this->assertSame( array( $this->unused_file ), $result['assigned'] );
		$this->assertSame( array( $this->post_with_list ), $result['skipped'] );
		$this->assertTrue( has_term( 'Reports', 'dl_icon_lists', $this->unused_file ) );

		// to the same list again by its name: not created twice, existing assignments are kept.
		$result = $abilities->assign_files_to_list(
			array(
				'file_ids'  => array( $this->file_in_list ),
				'list_name' => 'Reports',
			)
		);
		$this->assertIsArray( $result );
		$this->assertFalse( $result['created'] );
		$this->assertTrue( has_term( 'Reports', 'dl_icon_lists', $this->file_in_list ) );
		$this->assertTrue( has_term( $this->list_id, 'dl_icon_lists', $this->file_in_list ) );

		// errors.
		$this->assertWPError( $abilities->assign_files_to_list( array( 'file_ids' => array( $this->unused_file ) ) ) );
		$this->assertWPError(
			$abilities->assign_files_to_list(
				array(
					'file_ids' => array( $this->unused_file ),
					'list_id'  => 999999,
				)
			)
		);

		// editors are not allowed to manage download lists.
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'editor' ) ) );
		$this->assertFalse( $abilities->can_assign_to_lists() );
	}

	/**
	 * Test the ability to add a download list to a post.
	 *
	 * @return void
	 */
	public function test_add_list_to_post(): void {
		$abilities = Plugin_Abilities::get_instance();
		$post_id   = self::factory()->post->create( array( 'post_content' => wp_slash( '<!-- wp:paragraph --><p>C:\path</p><!-- /wp:paragraph -->' ) ) );
		$this->assertTrue( $abilities->can_edit_given_post( array( 'post_id' => $post_id ) ) );

		// with a download list.
		$result = $abilities->add_list_to_post(
			array(
				'post_id' => $post_id,
				'type'    => 'download_list',
				'id'      => $this->list_id,
				'preview' => true,
			)
		);
		$this->assertIsArray( $result );
		$content = (string) get_post_field( 'post_content', $post_id, 'raw' );
		$this->assertStringContainsString( '<!-- wp:downloadlist/list {"list":' . $this->list_id . ',"showPreview":true} /-->', $content );
		$this->assertStringContainsString( 'C:\path', $content );

		// with files.
		$result = $abilities->add_list_to_post(
			array(
				'post_id'  => $post_id,
				'file_ids' => array( $this->file_in_block, 999999 ),
			)
		);
		$this->assertIsArray( $result );
		$this->assertStringContainsString( '{"files":[{"id":' . $this->file_in_block . '}]}', (string) get_post_field( 'post_content', $post_id, 'raw' ) );

		// errors.
		$this->assertWPError( $abilities->add_list_to_post( array( 'post_id' => $post_id ) ) );
		$this->assertWPError( $abilities->add_list_to_post( array( 'post_id' => $this->file_in_block ) ) );
		$this->assertWPError(
			$abilities->add_list_to_post(
				array(
					'post_id' => $post_id,
					'type'    => 'download_list',
					'id'      => 999999,
				)
			)
		);

		// other users could not change this post.
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'contributor' ) ) );
		$this->assertFalse( $abilities->can_edit_given_post( array( 'post_id' => $post_id ) ) );
	}

	/**
	 * Test the abilities for the tasks of the plugin.
	 *
	 * @return void
	 */
	public function test_tasks(): void {
		$abilities = Plugin_Abilities::get_instance();
		$this->assertTrue( $abilities->can_manage_settings() );

		update_option( 'downloadlist_hide_file_sizes', 1 );
		$result = $abilities->inherit_settings();
		$this->assertSame( 2, $result['objects'] );
		$this->assertStringContainsString( '"hideFileSize":true', (string) get_post_field( 'post_content', $this->post_with_list, 'raw' ) );

		$this->assertSame( array( 'success' => true ), $abilities->regenerate_styles() );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'editor' ) ) );
		$this->assertFalse( $abilities->can_manage_settings() );
	}
}
