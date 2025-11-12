<?php
/**
 * This file contains the main init-object for this plugin.
 *
 * @package download-list-block-with-icons
 */

namespace DownloadListWithIcons\Plugin;

// prevent direct access.
defined( 'ABSPATH' ) || exit;

use DownloadListWithIcons\Files\Files;
use DownloadListWithIcons\Icons\Icons;
use DownloadListWithIcons\Iconsets\Iconset_Base;
use DownloadListWithIcons\Iconsets\Iconsets;
use DownloadListWithIcons\Plugin\Admin\Admin;
use WP_Post;
use WP_Query;
use WP_Term;
use WP_Term_Query;

/**
 * Initialize the plugin, connect all together.
 */
class Init {
	/**
	 * Instance of actual object.
	 *
	 * @var ?Init
	 */
	private static ?Init $instance = null;

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
	 * @return Init
	 */
	public static function get_instance(): Init {
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
		// initialize updates.
		Updates::get_instance()->init();

		// initialize the settings.
		Settings::get_instance()->init();

		// initialize the taxonomies.
		Taxonomies::get_instance()->init();

		// initialize support for attachment files.
		Files::get_instance()->init();

		// initialize the icons.
		Icons::get_instance()->init();

		// initialize the iconsets.
		Iconsets::get_instance()->init();

		// initialize the REST support.
		Rest::get_instance()->init();

		// initialize the admin support.
		Admin::get_instance()->init();

		// initialize the third party support.
		ThirdPartySupport::get_instance()->init();

		// use hooks.
		add_action( 'init', array( $this, 'register_block' ) );
		add_filter( 'render_block', array( $this, 'enqueue_styles' ), 10, 2 );
		add_action( 'cli_init', array( $this, 'add_cli' ) );
		add_action( 'after_setup_theme', array( $this, 'add_image_size' ) );
		add_filter( 'wp_prepare_attachment_for_js', array( $this, 'prepare_attachment_for_js' ), 10, 2 );
		add_filter( 'term_updated_messages', array( $this, 'updated_shows_messages' ) );
		add_filter( 'ajax_query_attachments_args', array( $this, 'change_ajax_query_attachments_args' ) );

		// use our own hooks.
		add_filter( 'downloadlist_generate_classname', array( $this, 'generate_classname' ) );
		add_filter( 'downloadlist_api_return_file_data', array( $this, 'add_mime_label' ) );
	}

	/**
	 * Register our own block.
	 *
	 * @return void
	 */
	public function register_block(): void {
		// bail if block editor is not enabled.
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		// register our custom block type.
		register_block_type(
			plugin_dir_path( DL_PLUGIN ),
			array(
				'render_callback' => array( $this, 'render_block' ),
				'attributes'      => array(
					'files'                  => array(
						'type' => 'array',
					),
					'hideFileSize'           => array(
						'type'    => 'boolean',
						'default' => 1 === absint( get_option( 'downloadlist_hide_file_sizes' ) ),
					),
					'hideDescription'        => array(
						'type'    => 'boolean',
						'default' => 1 === absint( get_option( 'downloadlist_hide_description' ) ),
					),
					'hideIcon'               => array(
						'type'    => 'boolean',
						'default' => 1 === absint( get_option( 'downloadlist_hide_icons' ) ),
					),
					'hideLink'               => array(
						'type'    => 'boolean',
						'default' => 1 === absint( get_option( 'downloadlist_link_text' ) ),
					),
					'linkTarget'             => array(
						'type'    => 'string',
						'default' => get_option( 'downloadlist_link_target' ),
					),
					'robots'                 => array(
						'type'    => 'string',
						'default' => get_option( 'downloadlist_robots' ),
					),
					'iconset'                => array(
						'type'    => 'string',
						'default' => get_option( 'downloadlist_iconset', 'dashicons' ),
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
						'default' => 1 === absint( get_option( 'downloadlist_link_no_forced_download' ) ),
					),
					'showDownloadButton'     => array(
						'type'    => 'boolean',
						'default' => 1 === absint( get_option( 'downloadlist_show_download_button' ) ),
					),
					'downloadLinkTarget'     => array(
						'type'    => 'string',
						'default' => get_option( 'downloadlist_link_browser_target' ),
					),
					'downloadLinkTargetName' => array(
						'type'    => 'string',
						'default' => get_option( 'downloadlist_download_button_browser_target_own' ),
					),
					'linkBrowserTarget'      => array(
						'type'    => 'string',
						'default' => get_option( 'downloadlist_link_browser_target' ),
					),
					'linkBrowserTargetName'  => array(
						'type'    => 'string',
						'default' => get_option( 'downloadlist_link_browser_target_own' ),
					),
					'list'                   => array(
						'type'    => 'integer',
						'default' => 0,
					),
					'order'                  => array(
						'type'    => 'string',
						'default' => '',
					),
					'orderby'                => array(
						'type'    => 'string',
						'default' => '',
					),
					'showFileDates'          => array(
						'type'    => 'boolean',
						'default' => 1 === absint( get_option( 'downloadlist_show_file_dates' ) ),
					),
					'showFileFormatLabel'    => array(
						'type'    => 'boolean',
						'default' => 1 === absint( get_option( 'downloadlist_show_file_format_labels' ) ),
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

	/**
	 * Enqueue style if our block is used anywhere in the output.
	 *
	 * @param string              $block_content The block content.
	 * @param array<string,mixed> $block The used block.
	 *
	 * @return string
	 */
	public function enqueue_styles( string $block_content, array $block ): string {
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

		// use default iconset if none is set.
		if ( empty( $block['attrs']['iconset'] ) ) {
			$block['attrs']['iconset'] = get_option( 'downloadlist_iconset' );
		}

		// bail if no iconset is configured.
		if ( empty( $block['attrs']['iconset'] ) ) {
			return $block_content;
		}

		// get iconset object.
		$iconsets_obj = Iconsets::get_instance();

		// get the object of the used iconset.
		$iconset = $iconsets_obj->get_iconset_by_slug( $block['attrs']['iconset'] );

		// bail if no iconset could be loaded.
		if ( ! $iconset ) {
			return $block_content;
		}

		// enqueue the iconset.
		$iconsets_obj->enqueue_styles_run( array( $iconset ) );

		// return the block content.
		return $block_content;
	}

	/**
	 * Register WP Cli.
	 *
	 * @noinspection PhpUnused
	 */
	public function add_cli(): void {
		\WP_CLI::add_command( 'download-list-block-with-icons', '\DownloadListWithIcons\Plugin\Cli' );
	}

	/**
	 * Add our own image sizes for icons.
	 *
	 * @return void
	 */
	public function add_image_size(): void {
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

	/**
	 * Render a single downloadlist-block.
	 *
	 * This is the main function for output in editor and frontend.
	 *
	 * @param array<string,mixed> $attributes List of attributes for this block.
	 * @return string
	 * @noinspection PhpUnused
	 */
	public function render_block( array $attributes ): string {
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

		/**
		 * Get all files assigned to a given download list.
		 * Add missing files in file list depending on order setting.
		 */
		if ( ! empty( $attributes['list'] ) ) {
			$query            = array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'posts_per_page' => -1,
				'tax_query'      => array(
					array(
						'taxonomy' => 'dl_icon_lists',
						'field'    => 'term_id',
						'terms'    => $attributes['list'],
					),
				),
				'fields'         => 'ids',
			);
			$additional_files = new WP_Query( $query );

			// if result is empty, do not show anything.
			if ( 0 === $additional_files->found_posts ) {
				return '';
			}

			// get the list of files as simple ID-array.
			$files = wp_list_pluck( $attributes['files'], 'id' );

			// check each file found in db which is assigned to the chosen download list.
			foreach ( $additional_files->get_posts() as $file_id ) {
				// get the int value.
				$file_id = absint( $file_id ); // @phpstan-ignore argument.type

				// bail if file exist in list.
				if ( in_array( $file_id, $files, true ) ) {
					continue;
				}

				// add file if it is missing in the list.
				$attributes['files'][] = array( 'id' => $file_id );
			}

			// check each file in list if it is assigned to the chosen download list.
			foreach ( $attributes['files'] as $index => $file ) {
				// format the index.
				$index = absint( $index );

				// get data about the assigned term.
				$term = wp_get_object_terms( $file['id'], 'dl_icon_lists' );

				// bail if term is set.
				if ( ! empty( $term ) ) {
					// get the meta-data like JS (like human-readable filesize).
					$file_meta = wp_prepare_attachment_for_js( $file['id'] );

					// bail if this is not an array.
					if ( ! is_array( $file_meta ) ) {
						continue;
					}

					// add the file data for sorting.
					$attributes['files'][ $index ]['title'] = $file_meta['title'];
					$attributes['files'][ $index ]['date']  = $file_meta['date'];
					$attributes['files'][ $index ]['size']  = $file_meta['filesizeInBytes'];

					continue;
				}

				// remove this entry from the list.
				unset( $attributes['files'][ absint( $index ) ] );
			}

			// sort the resulting list.
			if ( 'title' === $attributes['order'] ) {
				usort(
					$attributes['files'],
					function ( $a, $b ) {
						return strcmp( $a['title'], $b['title'] );
					}
				);
			}
			if ( 'size' === $attributes['order'] ) {
				usort(
					$attributes['files'],
					function ( $a, $b ) {
						return $a['size'] <=> $b['size'];
					}
				);
			}
			if ( 'date' === $attributes['order'] ) {
				usort(
					$attributes['files'],
					function ( $a, $b ) {
						return $a['date'] <=> $b['date'];
					}
				);
			}
			if ( 'ascending' === $attributes['orderby'] ) {
				$attributes['files'] = array_reverse( $attributes['files'], true );
			}
		}

		// get the possible mime labels.
		$mime_labels = $this->get_mime_labels();

		// variable for block-specific styles.
		$styles = '';

		// get Block Editor wrapper attributes.
		$wrapper_attributes = get_block_wrapper_attributes();

		// generate begin of the file-list.
		ob_start();
		include Templates::get_instance()->get( 'list-start.php' );
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
			$description = '<br>' . $attachment->post_content;
			if ( ! empty( $attributes['hideDescription'] ) || empty( $attachment->post_content ) ) {
				$description = '';
			}

			// get the file date, if enabled.
			$file_date = '';
			if ( ! empty( $attributes['showFileDates'] ) && ! empty( $attachment->post_date ) ) {
				$file_date = '<br>' . gmdate( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), (int) strtotime( $attachment->post_date ) );
			}

			// get the file format label, if enabled.
			$file_format_label = '';
			if ( ! empty( $attributes['showFileFormatLabel'] ) ) {
				$file_format_label = '<br>' . $mime_labels[ $attachment->post_mime_type ];
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
				include Templates::get_instance()->get( 'button-download.php' );
				$download_button = ob_get_clean();
			}

			// if text should be output instead of link, use the other template.
			ob_start();
			if ( false !== $attributes['hideLink'] ) {
				// add the not-linked entry to output.
				include Templates::get_instance()->get( 'list-item-not-linked.php' );
			} else {
				// add the linked entry to output.
				include Templates::get_instance()->get( 'list-item.php' );
			}
			$output .= ob_get_clean();
		}

		// generate end of the file-list.
		ob_start();
		include Templates::get_instance()->get( 'list-end.php' );
		$output .= ob_get_clean();

		// output block-specific style.
		if ( ! empty( $styles ) ) {
			$output .= '<style>' . $styles . '</style>';
		}

		// return resulting output.
		return $output;
	}

	/**
	 * Sanitize the class names generated from mime types.
	 *
	 * @param string $class_name The given class name.
	 * @return string
	 */
	public function generate_classname( string $class_name ): string {
		return sanitize_html_class( $class_name );
	}

	/**
	 * Filter query from media library to show single attachment.
	 *
	 * @param array<string,mixed> $query The query-array.
	 * @return array<string,mixed>
	 */
	public function change_ajax_query_attachments_args( array $query ): array {
		if ( ! empty( $_REQUEST['query']['downloadlist_post_id'] ) && ! empty( $_REQUEST['query']['downloadlist_nonce'] ) && false !== wp_verify_nonce( sanitize_key( $_REQUEST['query']['downloadlist_nonce'] ), 'downloadlist-edit-attachment' ) ) {
			$query['p'] = absint( $_REQUEST['query']['downloadlist_post_id'] );
		}
		return $query;
	}

	/**
	 * Update the messages after updating or deleting terms in our taxonomy.
	 *
	 * @param array<string,array<int,string>> $messages List of messages.
	 * @return array<string,array<int,string>>
	 */
	public function updated_shows_messages( array $messages ): array {
		$messages['dl_icon_set'] = array(
			1 => __( 'Iconset added.', 'download-list-block-with-icons' ),
			3 => __( 'Iconset updated.', 'download-list-block-with-icons' ),
			6 => __( 'Iconset deleted.', 'download-list-block-with-icons' ),
		);
		return $messages;
	}

	/**
	 * Use custom title and description for attachment.
	 *
	 * @param array<string,mixed> $response Array with response for JS.
	 * @param WP_Post             $attachment The attachment-object.
	 * @return array<string,mixed>
	 */
	public function prepare_attachment_for_js( array $response, WP_Post $attachment ): array {
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

	/**
	 * Add mime labels to the REST API response.
	 *
	 * @param array<int,mixed> $files List of files.
	 * @return array<int,mixed>
	 */
	public function add_mime_label( array $files ): array {
		// get the possible labels.
		$mime_labels = $this->get_mime_labels();

		foreach ( $files as $index => $file ) {
			// bail if no mime is set.
			if ( empty( $file['mime'] ) ) {
				continue;
			}

			// bail if no label is set for this mime.
			if ( empty( $mime_labels[ $file['mime'] ] ) ) {
				$files[ $index ]['downloadlist_mime_label'] = $file['mime'];
				continue;
			}

			// set the label.
			$files[ $index ]['downloadlist_mime_label'] = $mime_labels[ $file['mime'] ];
		}

		// return resulting lis of files.
		return $files;
	}

	/**
	 * Return list of possible mime labels.
	 *
	 * @return array<string,string>
	 */
	private function get_mime_labels(): array {
		$list = array(
			'application/pdf' => __( 'PDF-file', 'download-list-block-with-icons' ),
			'application/zip' => __( 'ZIP-file', 'download-list-block-with-icons' ),
			'image/gif'       => __( 'GIF-image', 'download-list-block-with-icons' ),
			'image/jpeg'      => __( 'JPEG-image', 'download-list-block-with-icons' ),
			'image/jpg'       => __( 'JPEG-image', 'download-list-block-with-icons' ),
			'image/png'       => __( 'PNG-image', 'download-list-block-with-icons' ),
		);

		/**
		 * Filter the list of possible mime labels.
		 *
		 * @since 4.0.0 Available since 4.0.0.
		 * @param array<string,string> $list List of possible mime labels.
		 */
		return apply_filters( 'downloadlist_mime_labels', $list );
	}
}
