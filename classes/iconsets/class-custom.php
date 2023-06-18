<?php
/**
 * File for custom iconset.
 */

namespace downloadlist\iconsets;

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
	 * Initialize the object.
	 *
	 * @return void
	 */
	public function init(): void {
		$this->label = __( 'My custom iconset', 'downloadlist' );
	}

	/**
	 * Get style for given file-type.
	 *
	 * @param int $post_id ID of the icon-post.
	 * @param int $term_id ID of the iconset-term.
	 * @param string $filetype Name for the filetype to add.
	 * @return string
	 */
	public function get_style_for_filetype( int $post_id, int $term_id, string $filetype): string {
		$styles = '';

		// get the icon of the given post.
		$icon = absint(get_post_meta($post_id, 'icon', true));
		if( $icon > 0 ) {
			// get image url.
			$url = wp_get_attachment_url($icon);

			// add output of this image for given file-type.
			if( false !== $url ) {
				$styles .= '.wp-block-downloadlist-list.iconset-' . $term_id . ' .file_' . $filetype . ':before { content: url("' . esc_url($url) . '"); }';
			}
		}

		// return resulting styles.
		return $styles;
	}

	/**
	 * Return the by iconset supported filetypes.
	 *
	 * @return array
	 */
	public function get_file_types(): array	{
		// define array for resulting list.
		$file_types = array();

		// get list of all possible file-types in this iconset.
		$query = array(
			'post_type' => 'dl_icons',
			'post_status' => 'publish',
			'meta_query' => array(
				array(
					'key' => 'file_type',
					'compare' => 'EXISTS'
				)
			),
			'tax_query' => array(
				array(
					'taxonomy' => 'dl_icon_set',
					'terms' => $this->get_slug(),
					'field' => 'slug',
					'operator' => '='
				)
			),
			'fields' => 'ids'
		);
		$results = new WP_Query($query);
		foreach ($results->posts as $post_id) {
			$file_type = get_post_meta($post_id, 'file_type', true);
			if (!in_array($file_type, $file_types)) {
				$file_types[] = $file_type;
			}
		}

		// return resulting list.
		return $file_types;
	}
}
