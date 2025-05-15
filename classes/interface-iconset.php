<?php
/**
 * File for interface-object for iconsets.
 *
 * @package download-list-block-with-icons
 */

namespace downloadlist;

// prevent direct access.
defined( 'ABSPATH' ) || exit;

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
	 * @param int    $post_id ID of the icon-post.
	 * @param string $term_slug ID of the iconset-term.
	 * @param string $filetype Name for the filetype to add.
	 * @return string
	 */
	public function get_style_for_filetype( int $post_id, string $term_slug, string $filetype ): string;

	/**
	 * Return style for single file.
	 *
	 * @param int $attachment_id ID of the attachment.
	 * @return string
	 */
	public function get_style_for_file( int $attachment_id ): string;

	/**
	 * Return the by iconset supported filetypes.
	 *
	 * @return array<int,string>
	 */
	public function get_file_types(): array;

	/**
	 * Get icons this set is assigned to.
	 *
	 * @return array<int,int>
	 */
	public function get_icons(): array;

	/**
	 * Return the style-files this iconset is using.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	public function get_style_files(): array;
}
