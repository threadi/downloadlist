<?php
/**
 * File for interface-object for iconsets.
 */

namespace downloadlist;

/**
 * Definition for requirements for iconsets.
 */
interface Iconset {
	/**
	 * Initialize the object.
	 *
	 * @return void
	 */
	public function init(): void;

	/**
	 * Return style for given filetype.
	 *
	 * @param int $post_id ID of the icon-post.
	 * @param int $term_id ID of the iconset-term.
	 * @param string $filetype Name for the filetype to add.
	 * @return string
	 */
	public function get_style_for_filetype( int $post_id, int $term_id, string $filetype ): string;

	/**
	 * Return the by iconset supported filetypes.
	 *
	 * @return array
	 */
	public function get_file_types(): array;
}
