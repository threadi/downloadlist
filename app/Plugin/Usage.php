<?php
/**
 * This file contains the functions to find the usage of our block and its files.
 *
 * @package download-list-block-with-icons
 */

namespace DownloadListWithIcons\Plugin;

// prevent direct access.
defined( 'ABSPATH' ) || exit;

use WP_Post;
use WP_Query;

/**
 * Object to find the objects which use our block and the files they show.
 */
class Usage {
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
	 * Return the query to find all objects with our block in their content.
	 *
	 * @param array<string,mixed> $args Additional arguments for the query, e.g. for sorting and paging.
	 *
	 * @return WP_Query
	 */
	public static function get_query( array $args = array() ): WP_Query {
		return new WP_Query(
			array_merge(
				array(
					'post_type'              => self::get_post_types(),
					'post_status'            => 'any',
					's'                      => '<!-- wp:downloadlist/list',
					'sentence'               => true,
					'search_columns'         => array( 'post_content' ),
					'orderby'                => 'modified',
					'order'                  => 'DESC',
					'posts_per_page'         => -1,
					'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
				),
				$args
			)
		);
	}

	/**
	 * Return all objects with our block in their content.
	 *
	 * @return array<int,WP_Post>
	 */
	public static function get_objects(): array {
		$objects = array();
		foreach ( self::get_query()->get_posts() as $post ) {
			if ( ! $post instanceof WP_Post ) {
				continue;
			}
			$objects[] = $post;
		}
		return $objects;
	}

	/**
	 * Return the attributes of all our blocks in the given content, including nested blocks.
	 *
	 * @param string $content The content.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	public static function get_blocks( string $content ): array {
		$blocks = array();
		self::collect_blocks( parse_blocks( $content ), $blocks );
		return $blocks;
	}

	/**
	 * Collect the attributes of our blocks in the given list of blocks, including nested blocks.
	 *
	 * @param array<int|string,mixed>        $blocks The parsed blocks.
	 * @param array<int,array<string,mixed>> $result The list of attributes.
	 *
	 * @return void
	 */
	private static function collect_blocks( array $blocks, array &$result ): void {
		foreach ( $blocks as $block ) {
			// bail if this is not a block.
			if ( ! is_array( $block ) ) {
				continue;
			}

			// add the attributes of our block.
			if ( isset( $block['blockName'] ) && 'downloadlist/list' === $block['blockName'] ) {
				$attributes = array();
				if ( isset( $block['attrs'] ) && is_array( $block['attrs'] ) ) {
					foreach ( $block['attrs'] as $key => $value ) {
						$attributes[ (string) $key ] = $value;
					}
				}
				$result[] = $attributes;
			}

			// check the inner blocks.
			if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
				self::collect_blocks( $block['innerBlocks'], $result );
			}
		}
	}

	/**
	 * Return the IDs of the files a block with the given attributes shows.
	 *
	 * Works like the output of the block: the files chosen in the block and, if a download list or folder is
	 * used, the files of this download list or folder.
	 *
	 * @param array<string,mixed> $attributes The block attributes.
	 *
	 * @return array<int,int>
	 */
	public static function get_file_ids( array $attributes ): array {
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

		// if no source is used, return the existing files.
		if ( false === $source ) {
			return array_values(
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
			return array();
		}

		// return the files of a folder.
		if ( 'dl_icon_lists' !== $source['taxonomy'] ) {
			return $source_files;
		}

		// return the files which are assigned to a download list (like the block does).
		return array_values(
			array_filter(
				array_unique( array_merge( $file_ids, $source_files ) ),
				function ( $file_id ) {
					$terms = wp_get_object_terms( $file_id, 'dl_icon_lists', array( 'fields' => 'ids' ) );
					return ! empty( $terms ) && ! is_wp_error( $terms );
				}
			)
		);
	}
}
