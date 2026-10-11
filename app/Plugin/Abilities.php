<?php
/**
 * This file contains the abilities of this plugin for the Abilities API of WordPress.
 *
 * @package download-list-block-with-icons
 */

namespace DownloadListWithIcons\Plugin;

// prevent direct access.
defined( 'ABSPATH' ) || exit;

use WP_Error;
use WP_Post;
use WP_Taxonomy;
use WP_Term;

/**
 * Object to register the abilities of this plugin.
 *
 * The abilities make the functions of this plugin usable for AI agents, automations and other plugins,
 * e.g. via the REST API of the Abilities API or via MCP. They are only registered if the Abilities API
 * is available (WordPress 6.9 or newer) and if they are enabled in the settings.
 */
class Abilities {
	/**
	 * The slug of our ability category.
	 */
	public const CATEGORY = 'download-list-block-with-icons';

	/**
	 * Mode: only abilities which read data.
	 */
	public const MODE_READ = 'read';

	/**
	 * Mode: all abilities, also the abilities which change data.
	 */
	public const MODE_ALL = 'all';

	/**
	 * Maximum number of entries in lists of the output, to keep the responses small.
	 */
	private const MAX_ENTRIES = 100;

	/**
	 * Instance of actual object.
	 *
	 * @var ?Abilities
	 */
	private static ?Abilities $instance = null;

	/**
	 * Constructor, not used as this a Singleton object.
	 */
	private function __construct() {}

	/**
	 * Prevent cloning of this object.
	 *
	 * @return void
	 */
	private function __clone() {}

	/**
	 * Return instance of this object as singleton.
	 *
	 * @return Abilities
	 */
	public static function get_instance(): Abilities {
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
		add_action( 'wp_abilities_api_categories_init', array( $this, 'register_category' ) );
		add_action( 'wp_abilities_api_init', array( $this, 'register_abilities' ) );
	}

	/**
	 * Return whether the Abilities API is available in this WordPress (since WordPress 6.9).
	 *
	 * @return bool
	 */
	public static function is_api_available(): bool {
		return function_exists( 'wp_register_ability' ) && function_exists( 'wp_register_ability_category' );
	}

	/**
	 * Return whether the abilities of this plugin are enabled in the settings.
	 *
	 * @return bool
	 */
	public static function is_enabled(): bool {
		return 1 === absint( get_option( 'downloadlist_abilities', 1 ) );
	}

	/**
	 * Return the mode for the abilities: only read or all.
	 *
	 * @return string
	 */
	public static function get_mode(): string {
		return self::MODE_ALL === get_option( 'downloadlist_abilities_mode', self::MODE_READ ) ? self::MODE_ALL : self::MODE_READ;
	}

	/**
	 * Register our ability category.
	 *
	 * @return void
	 */
	public function register_category(): void {
		// bail if the abilities are disabled.
		if ( ! self::is_api_available() || ! self::is_enabled() ) {
			return;
		}

		wp_register_ability_category(
			self::CATEGORY,
			array(
				'label'       => __( 'Download List Block with Icons', 'download-list-block-with-icons' ),
				'description' => __( 'Find, create and maintain download lists with files of the media library.', 'download-list-block-with-icons' ),
			)
		);
	}

	/**
	 * Register the abilities, depending on the mode in the settings.
	 *
	 * @return void
	 */
	public function register_abilities(): void {
		// bail if the abilities are disabled.
		if ( ! self::is_api_available() || ! self::is_enabled() ) {
			return;
		}

		foreach ( $this->get_abilities( self::get_mode() ) as $name => $args ) {
			wp_register_ability( $name, $args );
		}
	}

	/**
	 * Return the names of the abilities for the given mode.
	 *
	 * @param string $mode The mode.
	 *
	 * @return array<int,string>
	 */
	public function get_ability_names( string $mode ): array {
		return array_keys( $this->get_abilities( $mode ) );
	}

	/**
	 * Return the definitions of the abilities for the given mode.
	 *
	 * @param string $mode The mode.
	 *
	 * @return array<lowercase-string&non-falsy-string,array<string,mixed>>
	 */
	private function get_abilities( string $mode ): array {
		$abilities = $this->get_read_abilities();
		if ( self::MODE_ALL === $mode ) {
			$abilities = array_merge( $abilities, $this->get_write_abilities() );
		}
		return $abilities;
	}

	/**
	 * Return the meta data of an ability.
	 *
	 * @param bool $is_readonly    Whether the ability only reads data.
	 * @param bool $is_destructive Whether the ability may change or remove existing data.
	 * @param bool $is_idempotent  Whether repeated calls with the same input have no additional effect.
	 *
	 * @return array<string,mixed>
	 */
	private function get_meta( bool $is_readonly, bool $is_destructive, bool $is_idempotent ): array {
		return array(
			'annotations'  => array(
				'readonly'    => $is_readonly,
				'destructive' => $is_destructive,
				'idempotent'  => $is_idempotent,
			),
			'public'       => true,
			'show_in_rest' => true,
		);
	}

	/**
	 * Return the definitions of the abilities which only read data.
	 *
	 * @return array<lowercase-string&non-falsy-string,array<string,mixed>>
	 */
	private function get_read_abilities(): array {
		$object_schema = array(
			'type'       => 'object',
			'properties' => array(
				'id'       => array( 'type' => 'integer' ),
				'title'    => array( 'type' => 'string' ),
				'type'     => array( 'type' => 'string' ),
				'status'   => array( 'type' => 'string' ),
				'edit_url' => array( 'type' => 'string' ),
				'url'      => array( 'type' => 'string' ),
			),
		);
		$file_schema   = array(
			'type'       => 'object',
			'properties' => array(
				'id'        => array( 'type' => 'integer' ),
				'title'     => array( 'type' => 'string' ),
				'filename'  => array( 'type' => 'string' ),
				'mime_type' => array( 'type' => 'string' ),
				'url'       => array( 'type' => 'string' ),
				'filesize'  => array( 'type' => 'integer' ),
			),
		);

		return array(
			'downloadlist/get-usage'        => array(
				'label'               => __( 'Get usage of download lists', 'download-list-block-with-icons' ),
				'description'         => __( 'Returns the posts, pages, patterns and templates which contain a download list block, with the source and the number of files of each list.', 'download-list-block-with-icons' ),
				'category'            => self::CATEGORY,
				'input_schema'        => array(
					'type'                 => 'object',
					'properties'           => array(
						'page'     => array(
							'type'        => 'integer',
							'minimum'     => 1,
							'default'     => 1,
							'description' => __( 'The page of the results.', 'download-list-block-with-icons' ),
						),
						'per_page' => array(
							'type'        => 'integer',
							'minimum'     => 1,
							'maximum'     => self::MAX_ENTRIES,
							'default'     => 20,
							'description' => __( 'The number of objects per page.', 'download-list-block-with-icons' ),
						),
					),
					'additionalProperties' => false,
					'default'              => array(),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'total'   => array( 'type' => 'integer' ),
						'pages'   => array( 'type' => 'integer' ),
						'objects' => array(
							'type'  => 'array',
							'items' => $object_schema,
						),
					),
				),
				'execute_callback'    => array( $this, 'get_usage' ),
				'permission_callback' => array( $this, 'can_edit_posts' ),
				'meta'                => $this->get_meta( true, false, true ),
			),
			'downloadlist/get-file-usage'   => array(
				'label'               => __( 'Find where a file is shown', 'download-list-block-with-icons' ),
				'description'         => __( 'Returns the posts, pages, patterns and templates which show the given file of the media library in a download list block, also if the file is part of a download list or folder used by the block. Use it before deleting or replacing a file.', 'download-list-block-with-icons' ),
				'category'            => self::CATEGORY,
				'input_schema'        => array(
					'type'                 => 'object',
					'properties'           => array(
						'file_id' => array(
							'type'        => 'integer',
							'minimum'     => 1,
							'description' => __( 'The ID of the file in the media library.', 'download-list-block-with-icons' ),
						),
					),
					'required'             => array( 'file_id' ),
					'additionalProperties' => false,
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'file'    => $file_schema,
						'objects' => array(
							'type'  => 'array',
							'items' => $object_schema,
						),
					),
				),
				'execute_callback'    => array( $this, 'get_file_usage' ),
				'permission_callback' => array( $this, 'can_read_file' ),
				'meta'                => $this->get_meta( true, false, true ),
			),
			'downloadlist/get-sources'      => array(
				'label'               => __( 'Get download lists and folders', 'download-list-block-with-icons' ),
				'description'         => __( 'Returns the download lists of this plugin and the folders of folder plugins for the media library, which could be used as source of the files of a download list block.', 'download-list-block-with-icons' ),
				'category'            => self::CATEGORY,
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'download_lists' => array( 'type' => 'array' ),
						'folder_support' => array( 'type' => 'boolean' ),
						'folders'        => array( 'type' => 'array' ),
					),
				),
				'execute_callback'    => array( $this, 'get_sources' ),
				'permission_callback' => array( $this, 'can_edit_posts' ),
				'meta'                => $this->get_meta( true, false, true ),
			),
			'downloadlist/get-source-files' => array(
				'label'               => __( 'Get files of a download list or folder', 'download-list-block-with-icons' ),
				'description'         => __( 'Returns the files of the given download list or folder, as a download list block would show them.', 'download-list-block-with-icons' ),
				'category'            => self::CATEGORY,
				'input_schema'        => $this->get_source_schema( true ),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'files' => array(
							'type'  => 'array',
							'items' => $file_schema,
						),
					),
				),
				'execute_callback'    => array( $this, 'get_source_files' ),
				'permission_callback' => array( $this, 'can_edit_posts' ),
				'meta'                => $this->get_meta( true, false, true ),
			),
			'downloadlist/get-diagnostics'  => array(
				'label'               => __( 'Check download lists for problems', 'download-list-block-with-icons' ),
				'description'         => __( 'Checks whether this server can generate preview images of PDF files and returns the files shown in download lists which have no preview image, though their file type could have one.', 'download-list-block-with-icons' ),
				'category'            => self::CATEGORY,
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'pdf_preview_supported'      => array( 'type' => 'boolean' ),
						'folder_support'             => array( 'type' => 'boolean' ),
						'folder_plugins'             => array( 'type' => 'array' ),
						'files_in_use'               => array( 'type' => 'integer' ),
						'files_without_preview'      => array(
							'type'  => 'array',
							'items' => $file_schema,
						),
						'files_without_preview_type' => array( 'type' => 'integer' ),
					),
				),
				'execute_callback'    => array( $this, 'get_diagnostics' ),
				'permission_callback' => array( $this, 'can_edit_posts' ),
				'meta'                => $this->get_meta( true, false, true ),
			),
		);
	}

	/**
	 * Return the definitions of the abilities which change data.
	 *
	 * @return array<lowercase-string&non-falsy-string,array<string,mixed>>
	 */
	private function get_write_abilities(): array {
		return array(
			'downloadlist/assign-files-to-list' => array(
				'label'               => __( 'Assign files to a download list', 'download-list-block-with-icons' ),
				'description'         => __( 'Adds files of the media library to a download list. The list is given by its ID or by its name; a list with this name is created if it does not exist. Existing assignments are kept.', 'download-list-block-with-icons' ) . ' ' . $this->get_confirmation_hint(),
				'category'            => self::CATEGORY,
				'input_schema'        => array(
					'type'                 => 'object',
					'properties'           => array(
						'file_ids'  => array(
							'type'        => 'array',
							'items'       => array(
								'type'    => 'integer',
								'minimum' => 1,
							),
							'minItems'    => 1,
							'maxItems'    => 500,
							'description' => __( 'The IDs of the files in the media library.', 'download-list-block-with-icons' ),
						),
						'list_id'   => array(
							'type'        => 'integer',
							'minimum'     => 1,
							'description' => __( 'The ID of an existing download list.', 'download-list-block-with-icons' ),
						),
						'list_name' => array(
							'type'        => 'string',
							'minLength'   => 1,
							'maxLength'   => 200,
							'description' => __( 'The name of the download list, if no ID is given. It is created if it does not exist.', 'download-list-block-with-icons' ),
						),
					),
					'required'             => array( 'file_ids' ),
					'additionalProperties' => false,
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'list'     => array( 'type' => 'object' ),
						'created'  => array( 'type' => 'boolean' ),
						'assigned' => array( 'type' => 'array' ),
						'skipped'  => array( 'type' => 'array' ),
					),
				),
				'execute_callback'    => array( $this, 'assign_files_to_list' ),
				'permission_callback' => array( $this, 'can_assign_to_lists' ),
				'meta'                => $this->get_meta( false, false, true ),
			),
			'downloadlist/add-list-to-post'     => array(
				'label'               => __( 'Add a download list to a post', 'download-list-block-with-icons' ),
				'description'         => __( 'Adds a download list block at the end of the given post or page. The block shows the given files, all files of a download list or all files of a folder.', 'download-list-block-with-icons' ) . ' ' . $this->get_confirmation_hint(),
				'category'            => self::CATEGORY,
				'input_schema'        => $this->get_add_list_schema(),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'post_id'  => array( 'type' => 'integer' ),
						'edit_url' => array( 'type' => 'string' ),
						'block'    => array( 'type' => 'string' ),
					),
				),
				'execute_callback'    => array( $this, 'add_list_to_post' ),
				'permission_callback' => array( $this, 'can_edit_given_post' ),
				'meta'                => $this->get_meta( false, false, false ),
			),
			'downloadlist/inherit-settings'     => array(
				'label'               => __( 'Inherit settings to all download lists', 'download-list-block-with-icons' ),
				'description'         => __( 'Sets the default settings of the plugin on all existing download list blocks. This overwrites the individual settings of these blocks and cannot be undone.', 'download-list-block-with-icons' ) . ' ' . $this->get_confirmation_hint(),
				'category'            => self::CATEGORY,
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'objects' => array( 'type' => 'integer' ),
					),
				),
				'execute_callback'    => array( $this, 'inherit_settings' ),
				'permission_callback' => array( $this, 'can_manage_settings' ),
				'meta'                => $this->get_meta( false, true, true ),
			),
			'downloadlist/regenerate-styles'    => array(
				'label'               => __( 'Regenerate the styles of the icons', 'download-list-block-with-icons' ),
				'description'         => __( 'Generates the icon files and the styles of all iconsets again, e.g. if icons are not shown correctly.', 'download-list-block-with-icons' ) . ' ' . $this->get_confirmation_hint(),
				'category'            => self::CATEGORY,
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'success' => array( 'type' => 'boolean' ),
					),
				),
				'execute_callback'    => array( $this, 'regenerate_styles' ),
				'permission_callback' => array( $this, 'can_manage_settings' ),
				'meta'                => $this->get_meta( false, false, true ),
			),
		);
	}

	/**
	 * Return the hint for AI agents to confirm changes with the user before they use an ability which changes data.
	 *
	 * The description of an ability is read by AI agents, so this asks them to confirm. It is not a technical barrier:
	 * use the mode "Read only" or the permission settings of the AI client for this.
	 *
	 * @return string
	 */
	private function get_confirmation_hint(): string {
		return __( 'This ability changes the website. Only use it if the user explicitly asked for exactly this change. Before calling it, describe the planned change to the user and wait for their confirmation; do not treat the confirmation of an earlier, different step as permission.', 'download-list-block-with-icons' );
	}

	/**
	 * Return the input schema for a download list or folder.
	 *
	 * @param bool $required Whether the source is required.
	 *
	 * @return array<string,mixed>
	 */
	private function get_source_schema( bool $required ): array {
		$schema = array(
			'type'                 => 'object',
			'properties'           => array(
				'type'       => array(
					'type'        => 'string',
					'enum'        => array( 'download_list', 'folder' ),
					'description' => __( 'The type of the source: a download list of this plugin or a folder of a folder plugin.', 'download-list-block-with-icons' ),
				),
				'id'         => array(
					'type'        => 'integer',
					'minimum'     => 1,
					'description' => __( 'The ID of the download list or folder.', 'download-list-block-with-icons' ),
				),
				'taxonomy'   => array(
					'type'        => 'string',
					'description' => __( 'The taxonomy of the folder (only for folders), see the ability to get download lists and folders.', 'download-list-block-with-icons' ),
				),
				'subfolders' => array(
					'type'        => 'boolean',
					'default'     => false,
					'description' => __( 'Whether to include the files of subfolders (only for folders).', 'download-list-block-with-icons' ),
				),
			),
			'additionalProperties' => false,
		);
		if ( $required ) {
			$schema['required'] = array( 'type', 'id' );
		}
		return $schema;
	}

	/**
	 * Return the input schema to add a download list to a post.
	 *
	 * @return array<string,mixed>
	 */
	private function get_add_list_schema(): array {
		$schema                           = $this->get_source_schema( false );
		$schema['required']               = array( 'post_id' );
		$schema['properties']['type']     = array(
			'type'        => 'string',
			'enum'        => array( 'files', 'download_list', 'folder' ),
			'default'     => 'files',
			'description' => __( 'The source of the files: the given files, a download list of this plugin or a folder of a folder plugin.', 'download-list-block-with-icons' ),
		);
		$schema['properties']['post_id']  = array(
			'type'        => 'integer',
			'minimum'     => 1,
			'description' => __( 'The ID of the post or page.', 'download-list-block-with-icons' ),
		);
		$schema['properties']['file_ids'] = array(
			'type'        => 'array',
			'items'       => array(
				'type'    => 'integer',
				'minimum' => 1,
			),
			'maxItems'    => 500,
			'description' => __( 'The IDs of the files in the media library (only for the type "files").', 'download-list-block-with-icons' ),
		);
		$schema['properties']['preview']  = array(
			'type'        => 'boolean',
			'description' => __( 'Whether to show preview images instead of icons. Uses the default of the plugin settings if not given.', 'download-list-block-with-icons' ),
		);
		return $schema;
	}

	/**
	 * Return the given input as array.
	 *
	 * @param mixed $input The input.
	 *
	 * @return array<string,mixed>
	 */
	private function get_input( mixed $input ): array {
		return is_array( $input ) ? $input : array();
	}

	/**
	 * Permission: the user could edit posts.
	 *
	 * @return bool
	 */
	public function can_edit_posts(): bool {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Permission: the user could edit posts and read the given file.
	 *
	 * @param mixed $input The input.
	 *
	 * @return bool
	 */
	public function can_read_file( mixed $input = null ): bool {
		$input = $this->get_input( $input );
		return current_user_can( 'edit_posts' ) && ! empty( $input['file_id'] ) && current_user_can( 'read_post', absint( $input['file_id'] ) );
	}

	/**
	 * Permission: the user could assign files to download lists.
	 *
	 * @return bool
	 */
	public function can_assign_to_lists(): bool {
		$taxonomy = get_taxonomy( 'dl_icon_lists' );
		return $taxonomy instanceof WP_Taxonomy && current_user_can( $taxonomy->cap->assign_terms ) && current_user_can( 'upload_files' );
	}

	/**
	 * Permission: the user could edit the given post.
	 *
	 * @param mixed $input The input.
	 *
	 * @return bool
	 */
	public function can_edit_given_post( mixed $input = null ): bool {
		$input = $this->get_input( $input );
		return ! empty( $input['post_id'] ) && current_user_can( 'edit_post', absint( $input['post_id'] ) );
	}

	/**
	 * Permission: the user could manage the settings of this plugin.
	 *
	 * @return bool
	 */
	public function can_manage_settings(): bool {
		return current_user_can( Settings::get_instance()->get_settings_obj()->get_capability() );
	}

	/**
	 * Return the data of an object for the output.
	 *
	 * @param WP_Post $post The object.
	 *
	 * @return array<string,int|string>
	 */
	private function get_object_data( WP_Post $post ): array {
		$edit_url = current_user_can( 'edit_post', $post->ID ) ? get_edit_post_link( $post->ID, 'raw' ) : '';
		$url      = is_post_type_viewable( $post->post_type ) ? get_permalink( $post ) : false;
		return array(
			'id'       => $post->ID,
			'title'    => $post->post_title,
			'type'     => $post->post_type,
			'status'   => $post->post_status,
			'edit_url' => is_string( $edit_url ) ? $edit_url : '',
			'url'      => false !== $url ? $url : '',
		);
	}

	/**
	 * Return the data of a file for the output.
	 *
	 * @param int $file_id The ID of the file.
	 *
	 * @return array<string,int|string>
	 */
	private function get_file_data( int $file_id ): array {
		$file = get_attached_file( $file_id );
		$url  = wp_get_attachment_url( $file_id );
		$size = is_string( $file ) && file_exists( $file ) ? filesize( $file ) : 0;
		return array(
			'id'        => $file_id,
			'title'     => get_the_title( $file_id ),
			'filename'  => is_string( $file ) ? wp_basename( $file ) : '',
			'mime_type' => (string) get_post_mime_type( $file_id ),
			'url'       => is_string( $url ) ? $url : '',
			'filesize'  => is_int( $size ) ? $size : 0,
		);
	}

	/**
	 * Return the source of a block for the output.
	 *
	 * @param array<string,mixed> $attributes The block attributes.
	 *
	 * @return array<string,int|string|bool>
	 */
	private function get_block_source( array $attributes ): array {
		$source = Helper::get_list_source( $attributes );
		if ( false === $source ) {
			// a folder of a folder plugin which is not available anymore.
			if ( ! empty( $attributes['folder'] ) ) {
				return array(
					'type'      => 'folder',
					'id'        => absint( $attributes['folder'] ),
					'available' => false,
				);
			}
			return array( 'type' => 'files' );
		}

		// get the name of the download list or folder.
		$term = get_term( $source['term_id'], $source['taxonomy'] );
		return array(
			'type'       => 'dl_icon_lists' === $source['taxonomy'] ? 'download_list' : 'folder',
			'id'         => $source['term_id'],
			'name'       => $term instanceof WP_Term ? $term->name : '',
			'taxonomy'   => $source['taxonomy'],
			'subfolders' => 'dl_icon_lists' !== $source['taxonomy'] && $source['include_children'],
			'available'  => $term instanceof WP_Term,
		);
	}

	/**
	 * Ability: return the objects which contain a download list block.
	 *
	 * @param mixed $input The input.
	 *
	 * @return array<string,mixed>
	 */
	public function get_usage( mixed $input = null ): array {
		$input    = $this->get_input( $input );
		$per_page = ! empty( $input['per_page'] ) ? min( self::MAX_ENTRIES, absint( $input['per_page'] ) ) : 20;
		$query    = Usage::get_query(
			array(
				'posts_per_page' => $per_page,
				'paged'          => ! empty( $input['page'] ) ? absint( $input['page'] ) : 1,
			)
		);

		$objects = array();
		foreach ( $query->get_posts() as $post ) {
			// bail if this is not an object the user could read.
			if ( ! $post instanceof WP_Post || ! current_user_can( 'read_post', $post->ID ) ) {
				continue;
			}

			// collect the lists of this object.
			$lists      = array();
			$file_count = 0;
			foreach ( Usage::get_blocks( $post->post_content ) as $attributes ) {
				$count       = count( Usage::get_file_ids( $attributes ) );
				$file_count += $count;
				$lists[]     = array_merge( $this->get_block_source( $attributes ), array( 'file_count' => $count ) );
			}

			$objects[] = array_merge(
				$this->get_object_data( $post ),
				array(
					'lists'      => $lists,
					'file_count' => $file_count,
				)
			);
		}

		return array(
			'total'   => $query->found_posts,
			'pages'   => $query->max_num_pages,
			'objects' => $objects,
		);
	}

	/**
	 * Ability: return the objects which show the given file.
	 *
	 * @param mixed $input The input.
	 *
	 * @return array<string,mixed>|WP_Error
	 */
	public function get_file_usage( mixed $input = null ): array|WP_Error {
		$input   = $this->get_input( $input );
		$file_id = ! empty( $input['file_id'] ) ? absint( $input['file_id'] ) : 0;

		// bail if this is not a file.
		if ( 'attachment' !== get_post_type( $file_id ) ) {
			return new WP_Error( 'downloadlist_no_file', __( 'The given ID is not a file of the media library.', 'download-list-block-with-icons' ) );
		}

		$objects = array();
		foreach ( Usage::get_objects() as $post ) {
			// bail if the user could not read this object.
			if ( ! current_user_can( 'read_post', $post->ID ) ) {
				continue;
			}

			// check each list of this object.
			$via = array();
			foreach ( Usage::get_blocks( $post->post_content ) as $attributes ) {
				if ( in_array( $file_id, Usage::get_file_ids( $attributes ), true ) ) {
					$source = $this->get_block_source( $attributes );
					$via[]  = $source['type'];
				}
			}

			// bail if the file is not shown in this object.
			if ( empty( $via ) ) {
				continue;
			}

			$objects[] = array_merge( $this->get_object_data( $post ), array( 'via' => array_values( array_unique( $via ) ) ) );
		}

		return array(
			'file'    => $this->get_file_data( $file_id ),
			'objects' => $objects,
		);
	}

	/**
	 * Ability: return the download lists and folders.
	 *
	 * @return array<string,mixed>
	 */
	public function get_sources(): array {
		// get the download lists.
		$download_lists = array();
		$terms          = get_terms(
			array(
				'taxonomy'   => 'dl_icon_lists',
				'hide_empty' => false,
			)
		);
		if ( is_array( $terms ) ) {
			foreach ( $terms as $term ) {
				$download_lists[] = array(
					'id'    => $term->term_id,
					'name'  => $term->name,
					'count' => $term->count,
				);
			}
		}

		// get the folders.
		$folders = array();
		foreach ( Helper::get_folders() as $folder ) {
			$folders[] = array(
				'id'       => $folder['term_id'],
				'taxonomy' => $folder['taxonomy'],
				'plugin'   => $folder['taxonomy_label'],
				'name'     => $folder['name'],
				'depth'    => $folder['depth'],
				'count'    => $folder['count'],
			);
		}

		return array(
			'download_lists' => $download_lists,
			'folder_support' => Helper::is_folder_support_enabled(),
			'folders'        => $folders,
		);
	}

	/**
	 * Return the block attributes for the given source from an input.
	 *
	 * @param array<string,mixed> $input The input.
	 *
	 * @return array<string,mixed>|WP_Error
	 */
	private function get_source_attributes( array $input ): array|WP_Error {
		$id = ! empty( $input['id'] ) ? absint( $input['id'] ) : 0;

		// a download list.
		if ( isset( $input['type'] ) && 'download_list' === $input['type'] ) {
			if ( ! get_term( $id, 'dl_icon_lists' ) instanceof WP_Term ) {
				return new WP_Error( 'downloadlist_no_list', __( 'The download list does not exist.', 'download-list-block-with-icons' ) );
			}
			return array( 'list' => $id );
		}

		// a folder.
		$attributes = array(
			'folder'           => $id,
			'folderTaxonomy'   => isset( $input['taxonomy'] ) ? (string) $input['taxonomy'] : '',
			'folderSubfolders' => ! empty( $input['subfolders'] ),
		);
		if ( false === Helper::get_list_source( $attributes ) || ! get_term( $id, $attributes['folderTaxonomy'] ) instanceof WP_Term ) {
			return new WP_Error( 'downloadlist_no_folder', __( 'The folder does not exist, its folder plugin is not active or the use of folders is disabled in the settings.', 'download-list-block-with-icons' ) );
		}
		return $attributes;
	}

	/**
	 * Ability: return the files of a download list or folder.
	 *
	 * @param mixed $input The input.
	 *
	 * @return array<string,mixed>|WP_Error
	 */
	public function get_source_files( mixed $input = null ): array|WP_Error {
		$attributes = $this->get_source_attributes( $this->get_input( $input ) );
		if ( is_wp_error( $attributes ) ) {
			return $attributes;
		}

		$files = array();
		foreach ( Usage::get_file_ids( $attributes ) as $file_id ) {
			if ( ! current_user_can( 'read_post', $file_id ) ) {
				continue;
			}
			$files[] = $this->get_file_data( $file_id );
		}

		return array( 'files' => $files );
	}

	/**
	 * Ability: check the download lists for problems.
	 *
	 * @return array<string,mixed>
	 */
	public function get_diagnostics(): array {
		// collect all files shown in download lists.
		$file_ids = array();
		foreach ( Usage::get_objects() as $post ) {
			foreach ( Usage::get_blocks( $post->post_content ) as $attributes ) {
				$file_ids = array_merge( $file_ids, Usage::get_file_ids( $attributes ) );
			}
		}
		$file_ids = array_values( array_unique( $file_ids ) );

		// check their previews.
		$without_preview      = array();
		$without_preview_type = 0;
		foreach ( $file_ids as $file_id ) {
			$type = Helper::get_preview_type( (string) get_post_mime_type( $file_id ) );
			if ( 'none' === $type ) {
				++$without_preview_type;
				continue;
			}
			if ( false === Helper::get_preview_data( $file_id, 'thumbnail' ) && count( $without_preview ) < self::MAX_ENTRIES ) {
				$without_preview[] = $this->get_file_data( $file_id );
			}
		}

		return array(
			'pdf_preview_supported'      => Helper::is_pdf_preview_supported(),
			'folder_support'             => Helper::is_folder_support_enabled(),
			'folder_plugins'             => array_values( Helper::get_folder_taxonomies( true ) ),
			'files_in_use'               => count( $file_ids ),
			'files_without_preview'      => $without_preview,
			'files_without_preview_type' => $without_preview_type,
		);
	}

	/**
	 * Ability: assign files to a download list.
	 *
	 * @param mixed $input The input.
	 *
	 * @return array<string,mixed>|WP_Error
	 */
	public function assign_files_to_list( mixed $input = null ): array|WP_Error {
		$input   = $this->get_input( $input );
		$created = false;

		// get the download list by its ID.
		if ( ! empty( $input['list_id'] ) ) {
			$term = get_term( absint( $input['list_id'] ), 'dl_icon_lists' );
			if ( ! $term instanceof WP_Term ) {
				return new WP_Error( 'downloadlist_no_list', __( 'The download list does not exist.', 'download-list-block-with-icons' ) );
			}
		} elseif ( ! empty( $input['list_name'] ) && is_string( $input['list_name'] ) ) {
			// get or create the download list by its name.
			$name = sanitize_text_field( $input['list_name'] );
			$term = get_term_by( 'name', $name, 'dl_icon_lists' );
			if ( ! $term instanceof WP_Term ) {
				$taxonomy = get_taxonomy( 'dl_icon_lists' );
				if ( ! $taxonomy instanceof WP_Taxonomy || ! current_user_can( $taxonomy->cap->edit_terms ) ) {
					return new WP_Error( 'downloadlist_not_allowed', __( 'You are not allowed to create download lists.', 'download-list-block-with-icons' ) );
				}
				$result = wp_insert_term( $name, 'dl_icon_lists' );
				if ( is_wp_error( $result ) ) {
					return $result;
				}
				$term    = get_term( $result['term_id'], 'dl_icon_lists' );
				$created = true;
			}
		} else {
			return new WP_Error( 'downloadlist_missing_list', __( 'Give the ID or the name of the download list.', 'download-list-block-with-icons' ) );
		}

		// bail if the term could not be loaded.
		if ( ! $term instanceof WP_Term ) {
			return new WP_Error( 'downloadlist_no_list', __( 'The download list does not exist.', 'download-list-block-with-icons' ) );
		}

		// assign the files.
		$assigned = array();
		$skipped  = array();
		foreach ( isset( $input['file_ids'] ) && is_array( $input['file_ids'] ) ? $input['file_ids'] : array() as $file_id ) {
			$file_id = absint( $file_id );

			// skip all which are not files or which the user is not allowed to edit.
			if ( 'attachment' !== get_post_type( $file_id ) || ! current_user_can( 'edit_post', $file_id ) ) {
				$skipped[] = $file_id;
				continue;
			}

			// add the download list, keep the existing ones.
			$result = wp_set_object_terms( $file_id, array( $term->term_id ), 'dl_icon_lists', true );
			if ( is_wp_error( $result ) ) {
				$skipped[] = $file_id;
				continue;
			}
			$assigned[] = $file_id;
		}

		return array(
			'list'     => array(
				'id'   => $term->term_id,
				'name' => $term->name,
			),
			'created'  => $created,
			'assigned' => $assigned,
			'skipped'  => $skipped,
		);
	}

	/**
	 * Ability: add a download list block to a post.
	 *
	 * @param mixed $input The input.
	 *
	 * @return array<string,mixed>|WP_Error
	 */
	public function add_list_to_post( mixed $input = null ): array|WP_Error {
		$input   = $this->get_input( $input );
		$post_id = ! empty( $input['post_id'] ) ? absint( $input['post_id'] ) : 0;
		$post    = get_post( $post_id );

		// bail if the post does not exist or does not use the editor.
		if ( ! $post instanceof WP_Post || ! in_array( $post->post_type, Usage::get_post_types(), true ) ) {
			return new WP_Error( 'downloadlist_no_post', __( 'The post does not exist or does not use the block editor.', 'download-list-block-with-icons' ) );
		}

		// get the attributes for the source of the files.
		$type = isset( $input['type'] ) ? (string) $input['type'] : 'files';
		if ( 'files' === $type ) {
			$files = array();
			foreach ( isset( $input['file_ids'] ) && is_array( $input['file_ids'] ) ? $input['file_ids'] : array() as $file_id ) {
				$file_id = absint( $file_id );
				if ( 'attachment' === get_post_type( $file_id ) && current_user_can( 'read_post', $file_id ) ) {
					$files[] = array( 'id' => $file_id );
				}
			}
			if ( empty( $files ) ) {
				return new WP_Error( 'downloadlist_no_files', __( 'Give the IDs of existing files of the media library.', 'download-list-block-with-icons' ) );
			}
			$attributes = array( 'files' => $files );
		} else {
			$attributes = $this->get_source_attributes( $input );
			if ( is_wp_error( $attributes ) ) {
				return $attributes;
			}
		}

		// add the preview setting, if given.
		if ( isset( $input['preview'] ) ) {
			$attributes['showPreview'] = (bool) $input['preview'];
		}

		// create the block and add it at the end of the content.
		$block = serialize_block(
			array(
				'blockName'    => 'downloadlist/list',
				'attrs'        => $attributes,
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			)
		);

		// wp_update_post() expects slashed data, so slash it to keep backslashes.
		$result = wp_update_post(
			array(
				'ID'           => $post_id,
				'post_content' => wp_slash( '' === trim( $post->post_content ) ? $block : $post->post_content . "\n\n" . $block ),
			),
			true
		);
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$edit_url = get_edit_post_link( $post_id, 'raw' );
		return array(
			'post_id'  => $post_id,
			'edit_url' => is_string( $edit_url ) ? $edit_url : '',
			'block'    => $block,
		);
	}

	/**
	 * Ability: inherit the settings to all download list blocks.
	 *
	 * @return array<string,int>
	 */
	public function inherit_settings(): array {
		Settings::get_instance()->inherit_settings_to_blocks();
		return array(
			'objects' => absint( get_option( 'downloadlist_inheriting_max', 0 ) ),
		);
	}

	/**
	 * Ability: regenerate the icons and their styles.
	 *
	 * @return array<string,bool>
	 */
	public function regenerate_styles(): array {
		Helper::regenerate_icons();
		Helper::generate_css();
		return array( 'success' => true );
	}
}
