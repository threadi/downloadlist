<?php
/**
 * File for Fontawesome iconset.
 */

namespace downloadlist\iconsets;

use downloadlist\Iconset;
use downloadlist\Iconset_Base;

/**
 * Definition for Fontawesome iconset.
 */
class Fontawesome extends Iconset_Base implements Iconset {
	/**
	 * Set type of this iconset.
	 *
	 * @var string
	 */
	protected string $type = 'fontawesome';

	/**
	 * Set slug of this iconset.
	 *
	 * @var string
	 */
	protected string $slug = 'fontawesome';

	/**
	 * This iconset is a generic iconset (e.g. a font) where users can not add custom icons.
	 *
	 * @var bool
	 */
	protected bool $generic = true;

	/**
	 * Initialize the object.
	 *
	 * @return void
	 */
	public function init(): void {
		$this->label = __( 'FontAweSome', 'downloadlist' );
	}

	/**
	 * Get style for given file-type.
	 *
	 * @param int $post_id ID of the icon-post.
	 * @param int $term_id ID of the iconset-term.
	 * @param string $filetype Name for the filetype to add.
	 * @return string
	 */
	public function get_style_for_filetype( int $post_id, int $term_id, string $filetype ): string {
		// TODO
		return '';
	}

	/**
	 * Return the by iconset supported filetypes.
	 *
	 * @return array
	 */
	public function get_file_types(): array	{
		// TODO
		return array();
	}
}
