<?php
/**
 * This file contains the table which lists all objects using our block.
 *
 * @package download-list-block-with-icons
 */

namespace DownloadListWithIcons\Plugin\Admin;

// prevent direct access.
defined( 'ABSPATH' ) || exit;

use DownloadListWithIcons\Plugin\Helper;
use WP_List_Table;
use WP_Post;
use WP_Query;

// load the list table class of WordPress, if it is not loaded yet.
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Table which lists all posts, pages and other objects which use our block.
 */
class Usage_Table extends WP_List_Table {
	/**
	 * Number of entries per page.
	 *
	 * @var int
	 */
	private int $per_page = 20;

	/**
	 * Return the post types which could contain our block.
	 *
	 * These are all post types which use the editor (posts, pages, custom post types, synced patterns, templates).
	 *
	 * @return array<int,string>
	 */
	public static function get_post_types(): array {
		return array_values( array_diff( get_post_types_by_support( 'editor' ), array( 'attachment' ) ) );
	}

	/**
	 * Return the columns of this table.
	 *
	 * @return array<string,string>
	 */
	public function get_columns(): array {
		return array(
			'title'     => __( 'Title', 'download-list-block-with-icons' ),
			'post_type' => __( 'Type', 'download-list-block-with-icons' ),
			'status'    => __( 'Status', 'download-list-block-with-icons' ),
			'lists'     => __( 'Download lists', 'download-list-block-with-icons' ),
			'files'     => __( 'Files', 'download-list-block-with-icons' ),
			'modified'  => __( 'Last modified', 'download-list-block-with-icons' ),
		);
	}

	/**
	 * Return the sortable columns.
	 *
	 * @return array<string,array<int,string|bool>>
	 */
	protected function get_sortable_columns(): array {
		return array(
			'title'    => array( 'title', false ),
			'modified' => array( 'modified', true ),
		);
	}

	/**
	 * Load the entries for the actual page of the table.
	 *
	 * @return void
	 */
	public function prepare_items(): void {
		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns(), 'title' );

		// get the sorting from request.
		$orderby = isset( $_GET['orderby'] ) && 'title' === sanitize_key( wp_unslash( $_GET['orderby'] ) ) ? 'title' : 'modified'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$order   = isset( $_GET['order'] ) && 'asc' === sanitize_key( wp_unslash( $_GET['order'] ) ) ? 'ASC' : 'DESC'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $_GET['order'] ) && 'title' === $orderby ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$order = 'ASC';
		}

		// search for all objects with our block in their content.
		$query = new WP_Query(
			array(
				'post_type'              => self::get_post_types(),
				'post_status'            => 'any',
				's'                      => '<!-- wp:downloadlist/list',
				'sentence'               => true,
				'search_columns'         => array( 'post_content' ),
				'orderby'                => $orderby,
				'order'                  => $order,
				'posts_per_page'         => $this->per_page,
				'paged'                  => $this->get_pagenum(),
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);

		// collect the entries.
		$this->items = array();
		foreach ( $query->get_posts() as $post ) {
			if ( ! $post instanceof WP_Post ) {
				continue;
			}
			$this->items[] = $post;
		}

		// set the pagination.
		$this->set_pagination_args(
			array(
				'total_items' => $query->found_posts,
				'per_page'    => $this->per_page,
				'total_pages' => $query->max_num_pages,
			)
		);
	}

	/**
	 * Show the title with links to edit and view the object.
	 *
	 * @param WP_Post $item The object.
	 *
	 * @return string
	 */
	protected function column_title( WP_Post $item ): string {
		// get the title.
		$title = '' !== $item->post_title ? $item->post_title : __( '(no title)', 'download-list-block-with-icons' );

		// get the edit link, if the user is allowed to edit this object.
		$edit_link = current_user_can( 'edit_post', $item->ID ) ? get_edit_post_link( $item->ID ) : '';

		// show only the title if the user could not edit this object.
		if ( empty( $edit_link ) ) {
			$output = '<strong>' . esc_html( $title ) . '</strong>';
		} else {
			$output = '<strong><a href="' . esc_url( $edit_link ) . '">' . esc_html( $title ) . '</a></strong>';
		}

		// collect the actions.
		$actions = array();
		if ( ! empty( $edit_link ) ) {
			$actions['edit'] = '<a href="' . esc_url( $edit_link ) . '">' . esc_html__( 'Edit', 'download-list-block-with-icons' ) . '</a>';
		}
		if ( is_post_type_viewable( $item->post_type ) && 'trash' !== $item->post_status ) {
			$view_link = get_permalink( $item );
			if ( ! empty( $view_link ) ) {
				$actions['view'] = '<a href="' . esc_url( $view_link ) . '" target="_blank">' . esc_html( 'publish' === $item->post_status ? __( 'View', 'download-list-block-with-icons' ) : __( 'Preview', 'download-list-block-with-icons' ) ) . '</a>';
			}
		}

		// return the resulting output.
		return $output . $this->row_actions( $actions );
	}

	/**
	 * Show the post type of the object.
	 *
	 * @param WP_Post $item The object.
	 *
	 * @return string
	 */
	protected function column_post_type( WP_Post $item ): string {
		$post_type = get_post_type_object( $item->post_type );
		if ( is_null( $post_type ) ) {
			return esc_html( $item->post_type );
		}
		return esc_html( $post_type->labels->singular_name );
	}

	/**
	 * Show the status of the object.
	 *
	 * @param WP_Post $item The object.
	 *
	 * @return string
	 */
	protected function column_status( WP_Post $item ): string {
		$status = get_post_status_object( $item->post_status );
		if ( is_null( $status ) || ! is_string( $status->label ) ) {
			return esc_html( $item->post_status );
		}
		return esc_html( $status->label );
	}

	/**
	 * Show the number of download lists in the object.
	 *
	 * @param WP_Post $item The object.
	 *
	 * @return string
	 */
	protected function column_lists( WP_Post $item ): string {
		return esc_html( number_format_i18n( substr_count( $item->post_content, '<!-- wp:downloadlist/list' ) ) );
	}

	/**
	 * Show the number of files in the download lists of the object.
	 *
	 * If the object contains more than one list, the number per list is shown in addition.
	 *
	 * @param WP_Post $item The object.
	 *
	 * @return string
	 */
	protected function column_files( WP_Post $item ): string {
		// get the number of files per list.
		$counts = self::get_file_counts( $item->post_content );

		// bail if no list has been found.
		if ( empty( $counts ) ) {
			return '';
		}

		// get the total.
		$output = number_format_i18n( array_sum( $counts ) );

		// add the number per list, if more than one list is used.
		if ( count( $counts ) > 1 ) {
			$output .= ' (' . implode( ' + ', array_map( 'number_format_i18n', $counts ) ) . ')';
		}

		// return the resulting output.
		return esc_html( $output );
	}

	/**
	 * Return the number of files for each download list in the given content.
	 *
	 * Counts like the output of the block: the files chosen in the block and, if a download list is
	 * used, the files assigned to this download list.
	 *
	 * @param string $content The content.
	 *
	 * @return array<int,int>
	 */
	public static function get_file_counts( string $content ): array {
		$counts = array();
		self::collect_file_counts( parse_blocks( $content ), $counts );
		return $counts;
	}

	/**
	 * Collect the number of files of our blocks in the given list of blocks, including nested blocks.
	 *
	 * @param array<int|string,mixed> $blocks The parsed blocks.
	 * @param array<int,int>          $counts The list of counts.
	 *
	 * @return void
	 */
	private static function collect_file_counts( array $blocks, array &$counts ): void {
		foreach ( $blocks as $block ) {
			// bail if this is not a block.
			if ( ! is_array( $block ) ) {
				continue;
			}

			// count the files of our block.
			if ( isset( $block['blockName'] ) && 'downloadlist/list' === $block['blockName'] ) {
				$counts[] = self::get_file_count( isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array() );
			}

			// check the inner blocks.
			if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
				self::collect_file_counts( $block['innerBlocks'], $counts );
			}
		}
	}

	/**
	 * Return the number of files for the given block attributes.
	 *
	 * @param array<string,mixed> $attributes The block attributes.
	 *
	 * @return int
	 */
	private static function get_file_count( array $attributes ): int {
		// get the IDs of the files chosen in the block.
		$file_ids = array();
		if ( ! empty( $attributes['files'] ) && is_array( $attributes['files'] ) ) {
			foreach ( $attributes['files'] as $file ) {
				if ( is_array( $file ) && ! empty( $file['id'] ) ) {
					$file_ids[] = absint( $file['id'] );
				}
			}
		}

		// get the download list or folder used as source.
		$source = Helper::get_list_source( $attributes );

		// if no source is used, count the existing files.
		if ( false === $source ) {
			return count(
				array_filter(
					array_unique( $file_ids ),
					function ( $file_id ) {
						return 'attachment' === get_post_type( $file_id );
					}
				)
			);
		}

		// get the files of the source.
		$source_files = Helper::get_source_file_ids( $source );

		// bail if the source has no files: the block shows nothing in this case.
		if ( empty( $source_files ) ) {
			return 0;
		}

		// count the files of a folder.
		if ( 'dl_icon_lists' !== $source['taxonomy'] ) {
			return count( $source_files );
		}

		// count the files which are assigned to a download list (like the block does).
		return count(
			array_filter(
				array_unique( array_merge( $file_ids, $source_files ) ),
				function ( $file_id ) {
					$terms = wp_get_object_terms( $file_id, 'dl_icon_lists', array( 'fields' => 'ids' ) );
					return ! empty( $terms ) && ! is_wp_error( $terms );
				}
			)
		);
	}

	/**
	 * Show the date of the last modification.
	 *
	 * @param WP_Post $item The object.
	 *
	 * @return string
	 */
	protected function column_modified( WP_Post $item ): string {
		$date = get_post_modified_time( (string) get_option( 'date_format' ) . ' ' . (string) get_option( 'time_format' ), false, $item, true );
		if ( ! is_string( $date ) ) {
			return '';
		}
		return esc_html( $date );
	}

	/**
	 * Show a hint if no object uses our block.
	 *
	 * @return void
	 */
	public function no_items(): void {
		echo esc_html__( 'The download list block is not used anywhere yet.', 'download-list-block-with-icons' );
	}

	/**
	 * Do not show the table navigation above the table if there is only one page.
	 *
	 * @param 'top'|'bottom' $which The position: top or bottom.
	 *
	 * @return void
	 */
	protected function display_tablenav( $which ): void {
		if ( 'top' === $which && $this->get_pagination_arg( 'total_pages' ) <= 1 ) {
			return;
		}
		parent::display_tablenav( $which );
	}
}
