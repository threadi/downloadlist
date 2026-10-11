# Template for own icon set

The following must be adjusted in the code:

* the namespace and the text domain of your plugin
* type and slug
* the label in `init()`
* in `get_icon_codes()` the available unicode symbols, assigned to the file types (mime type or main type like `image`)
* in `get_style_for_filetype()` the style to output (at least the name of the font)
* in `get_style_files()` the CSS file, which loads your font

The iconset is registered with the filter `downloadlist_register_iconset`. Deactivate and activate *Download List Block with Icons* once after adding it, so the iconset is created.

# Code

```php
<?php
/**
 * File for custom iconset.
 *
 * @package my-plugin
 */

namespace MyPlugin;

// prevent direct access.
defined( 'ABSPATH' ) || exit;

use DownloadListWithIcons\Iconsets\Iconset;
use DownloadListWithIcons\Iconsets\Iconset_Base;

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
	 * Return the unicode of the icon for each file type.
	 *
	 * @return array<string,string>
	 */
	private function get_icon_codes(): array {
		return array(
			'application/pdf' => '\f190',
			'image'           => '\f128',
		);
	}

	/**
	 * Get style for given file-type.
	 *
	 * @param int    $post_id ID of the icon-post.
	 * @param string $term_slug Slug of the iconset-term.
	 * @param string $filetype Name for the filetype to add.
	 * @return string
	 */
	public function get_style_for_filetype( int $post_id, string $term_slug, string $filetype ): string {
		$style = '';
		foreach ( $this->get_icon_codes() as $mime_type => $unicode ) {
			$mime_type_array = explode( '/', $mime_type );
			$type            = sanitize_html_class( $mime_type_array[0] );
			$subtype         = ! empty( $mime_type_array[1] ) ? sanitize_html_class( $mime_type_array[1] ) : '';
			$style          .= '.wp-block-downloadlist-list.iconset-' . sanitize_html_class( $term_slug ) . ' .file_' . $type . ':before { content: "' . $unicode . '"; font-family: "my font"; }';
			if ( ! empty( $subtype ) ) {
				$style .= '.wp-block-downloadlist-list.iconset-' . sanitize_html_class( $term_slug ) . ' .file_' . $subtype . ':before { content: "' . $unicode . '"; font-family: "my font"; }';
			}
		}
		return $style;
	}

	/**
	 * Return the by iconset supported filetypes.
	 *
	 * @return array<int,string>
	 */
	public function get_file_types(): array {
		return array_keys( $this->get_icon_codes() );
	}

	/**
	 * Return the style-files this iconset is using.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	public function get_style_files(): array {
		return array(
			array(
				'handle' => 'my-custom-iconset',
				'url'    => plugins_url( 'css/my-font.css', __FILE__ ),
				'path'   => plugin_dir_path( __FILE__ ) . 'css/my-font.css',
			),
		);
	}
}

/**
 * Register the iconset.
 */
add_filter(
	'downloadlist_register_iconset',
	function ( array $iconset_list ): array {
		$iconset_list[] = new My_Custom_Iconset();
		return $iconset_list;
	}
);
```
