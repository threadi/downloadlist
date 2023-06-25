# Template for own icon set

In the following code must be adjusted:

* type and slug
* the label in constructor
* in get_icon_codes an array of the available unicode symbols assigned to the file types must be defined as an array.
* get_style_for_filetype the style to output must be adjusted (name of the font at least)

# Code

```
<?php
/**
 * File for custom iconset.
 */

namespace downloadlist\iconsets;

use downloadlist\Iconset;
use downloadlist\Iconset_Base;

/**
 * Definition for custom iconset.
 */
class My_Custom_Iconset extends Iconset_Base implements Iconset {
	/**
	 * Set type of this iconset.
	 *
	 * @var string
	 */
	protected string $type = 'my_custom_iconset';

	/**
	 * Set slug of this iconset.
	 *
	 * @var string
	 */
	protected string $slug = 'my_custom_iconset';

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
		$this->label = __( 'My Custom Iconset', 'my-text-domain' );
	}

	/**
	 * Get all possible dashicons as array.
	 *
	 * @return array
	 */
	private function get_icon_codes(): array {
		return array();
	}

	/**
	 * Get style for given file-type.
	 *
	 * @param int $post_id ID of the icon-post.
	 * @param string $term_slug ID of the iconset-term.
	 * @param string $filetype Name for the filetype to add.
	 * @return string
	 */
	public function get_style_for_filetype( int $post_id, string $term_slug, string $filetype ): string {
		$style = '';
		foreach( $this->get_icon_codes() as $filetype => $dashicon ) {
			$mimetypeArray = explode("/", $filetype);
			$type = $mimetypeArray[0];
			$subtype = '';
			if( !empty($mimetypeArray[1]) ) {
				$subtype = $mimetypeArray[1];
			}
			$style .= '.wp-block-downloadlist-list.iconset-' . $term_slug . ' .file_' . $type . ':before { content: "' . $dashicon . '";font-family: 'my font'; }';
			if( !empty($subtype) ) {
				$style .= '.wp-block-downloadlist-list.iconset-' . $term_slug . ' .file_' . $subtype . ':before { content: "' . $dashicon . '";font-family: 'my font'; }';
			}
		}
		return $style;
	}

	/**
	 * Return the by iconset supported filetypes.
	 *
	 * @return array
	 */
	public function get_file_types(): array	{
		return array_keys($this->get_icon_codes());
	}

	/**
	 * Get icons this set is assigned to.
	 *
	 * @return array
	 */
	public function get_icons(): array {
		return array();
	}

	/**
	 * Return the style-files this iconset is using.
	 *
	 * @return array
	 */
	public function get_style_files(): array {
		return array();
	}
}
```
