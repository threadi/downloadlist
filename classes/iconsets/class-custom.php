<?php
/**
 * File for custom iconset.
 *
 * @package download-list-block-with-icons
 */

namespace downloadlist\iconsets;

// prevent direct access.
defined( 'ABSPATH' ) || exit;

use downloadlist\Iconset;
use downloadlist\Iconset_Base;
use WP_Query;

/**
 * Definition for custom iconset.
 */
class Custom extends Iconset_Base implements Iconset {
	/**
	 * Set type of this iconset.
	 *
	 * @var string
	 */
	protected string $type = 'custom';

	/**
	 * Set slug of this iconset.
	 *
	 * @var string
	 */
	protected string $slug = 'custom';

	/**
	 * This iconset uses generated graphics.
	 *
	 * @var bool
	 */
	protected bool $gfx = true;

	/**
	 * Initialize the object.
	 *
	 * @return void
	 */
	public function init(): void {
		$this->label = __( 'My custom iconset', 'download-list-block-with-icons' );
	}

	/**
	 * Get style for given file-type.
	 *
	 * @param int    $post_id ID of the icon-post.
	 * @param string $term_slug The slug of the term this iconset is using.
	 * @param string $filetype Name for the filetype to add.
	 * @return string
	 */
	public function get_style_for_filetype( int $post_id, string $term_slug, string $filetype ): string {
		$styles = '';

		// get the icon of the given post.
		$attachment_id = absint( get_post_meta( $post_id, 'icon', true ) );

		// bail if no id for icon is given.
		if ( 0 === $attachment_id ) {
			return $styles;
		}

		// get image url.
		$url = wp_get_attachment_image_url( $attachment_id, 'downloadlist-icon-' . $term_slug );

		// add output of this image for given file-type.
		if ( false !== $url ) {
			$styles .= '.wp-block-downloadlist-list.iconset-' . $term_slug . ' .file_' . $filetype . ':before { content: url("' . esc_url( $url ) . '"); }';
		}

		// return resulting styles.
		return $styles;
	}

	/**
	 * Return the by iconset supported filetypes.
	 *
	 * @return array
	 */
	public function get_file_types(): array {
		// define array for resulting list.
		$file_types = array();

		// get list of all possible file-types in this iconset.
		$query   = array(
			'post_type'   => 'dl_icons',
			'post_status' => 'publish',
			'meta_query'  => array(
				array(
					'key'     => 'file_type',
					'compare' => 'EXISTS',
				),
			),
			'tax_query'   => array(
				array(
					'taxonomy' => 'dl_icon_set',
					'terms'    => $this->get_slug(),
					'field'    => 'slug',
					'operator' => '=',
				),
			),
			'fields'      => 'ids',
		);
		$results = new WP_Query( $query );
		foreach ( $results->posts as $post_id ) {
			// get the file type setting.
			$file_type = get_post_meta( $post_id, 'file_type', true );

			// bail if file type is already on list.
			if ( in_array( $file_type, $file_types, true ) ) {
				continue;
			}

			// add file type to the list.
			$file_types[ $post_id ] = $file_type;
		}

		// return resulting list.
		return $file_types;
	}

	/**
	 * Get icons this set is assigned to.
	 *
	 * @return array The post-IDs of the icons as array.
	 */
	public function get_icons(): array {
		$query   = array(
			'post_type'   => 'dl_icons',
			'post_status' => 'any',
			'fields'      => 'ids',
			'tax_query'   => array(
				array(
					'taxonomy' => 'dl_icon_set',
					'terms'    => $this->get_slug(),
					'field'    => 'slug',
					'operator' => '=',
				),
			),
		);
		$results = new WP_Query( $query );

		// bail on no results.
		if( 0 === $results->found_posts ) {
			return array();
		}

		// return the resulting list.
		return $results->get_posts();
	}
}
