<?php
/**
 * File for custom iconset.
 *
 * @package download-list-block-with-icons
 */

namespace DownloadListWithIcons\Iconsets\Iconsets;

// prevent direct access.
defined( 'ABSPATH' ) || exit;

use DownloadListWithIcons\Iconsets\Iconset;
use DownloadListWithIcons\Iconsets\Iconset_Base;
use WP_Post;
use WP_Query;
use WP_Term;

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
	 * Instance of this object.
	 *
	 * @var ?Custom
	 */
	private static ?Custom $instance = null;

	/**
	 * Return the instance of this Singleton object.
	 */
	public static function get_instance(): Custom {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Initialize the object.
	 *
	 * @return void
	 */
	public function init(): void {
		$this->label = __( 'My custom iconset', 'download-list-block-with-icons' );
	}

	/**
	 * Return style for given file-type.
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

		// get the unicode.
		$unicode = get_post_meta( $post_id, 'unicode', true );

		// bail if no id and not unicode for icon is given.
		if ( 0 === $attachment_id && empty( $unicode ) ) {
			return $styles;
		}

		// get image url to show icon as image.
		if ( $attachment_id > 0 ) {
			$url = wp_get_attachment_image_url( $attachment_id, 'downloadlist-icon-' . $term_slug );

			// add output of this image for given file-type.
			if ( false !== $url ) {
				$styles .= '.wp-block-downloadlist-list.iconset-' . $term_slug . ' .file_' . $filetype . ':before { content: url("' . esc_url( $url ) . '"); }';
			}
		}

		// show icon via unicode.
		if ( ! empty( $unicode ) ) {
			// get the term.
			$term = get_term_by( 'slug', $term_slug, 'dl_icon_set' );

			// bail if term could not be loaded.
			if ( ! $term instanceof WP_Term ) {
				return $styles;
			}

			// get the font size.
			$font_size = absint( get_term_meta( $term->term_id, 'font_size', true ) );

			// get the font weight.
			$font_weight = absint( get_term_meta( $term->term_id, 'font_weight', true ) );

			// add the styles.
			$styles .= '.wp-block-downloadlist-list.iconset-' . $term_slug . ' .file_' . $filetype . ':before { content: "' . esc_attr( $unicode ) . '";font-family: "' . esc_attr( $term_slug ) . '", sans-serif;font-size: ' . $font_size . 'px;font-weight: ' . $font_weight . ' }';
		}

		// return resulting styles.
		return $styles;
	}

	/**
	 * Return the by iconset supported filetypes.
	 *
	 * @return array<int,string>
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
		foreach ( $results->get_posts() as $post_id ) {
			if ( $post_id instanceof WP_Post ) {
				continue;
			}
			$post_id = absint( $post_id );

			// get the file type setting.
			$file_type = (string) get_post_meta( $post_id, 'file_type', true );

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
	 * Return icons this set is assigned to.
	 *
	 * @return array<int,int> The post-IDs of the icons as array.
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
		if ( 0 === $results->found_posts ) {
			return array();
		}

		// get the resulting list.
		$list = array();
		foreach ( $results->get_posts() as $post_id ) {
			if ( $post_id instanceof WP_Post ) {
				continue;
			}
			$list[] = absint( $post_id );
		}
		return $list;
	}
}
