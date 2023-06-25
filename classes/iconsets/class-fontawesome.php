<?php
/**
 * File for Fontawesome iconset.
 */

// TODO !!! https://fontawesome.com/docs/web/setup/host-yourself/webfonts#reference-font-awesome-in-your-project

namespace downloadlist\iconsets;

use downloadlist\Helper;
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
	 * @param string $term_slug ID of the iconset-term.
	 * @param string $filetype Name for the filetype to add.
	 * @return string
	 */
	public function get_style_for_filetype( int $post_id, string $term_slug, string $filetype ): string {
		$style = '';
		foreach( $this->get_icon_codes() as $filetype => $icon ) {
			$mimetypeArray = explode("/", $filetype);
			$type = $mimetypeArray[0];
			$subtype = '';
			if( !empty($mimetypeArray[1]) ) {
				$subtype = $mimetypeArray[1];
			}
			$style .= '.wp-block-downloadlist-list.iconset-' . $term_slug . ' .file_' . $type . ':before { content: "' . $icon . '";font-family: "Font Awesome 6 Free"; }';
			if( !empty($subtype) ) {
				$style .= '.wp-block-downloadlist-list.iconset-' . $term_slug . ' .file_' . $subtype . ':before { content: "' . $icon . '";font-family: "Font Awesome 6 Free"; }';
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
	 * Get all possible dashicons as array.
	 *
	 * @return array
	 */
	private function get_icon_codes(): array {
		$font_awesome_icons = array_flip(helper::get_mime_types());
		ksort($font_awesome_icons);
		$font_awesome_icons['application'] = '\f013';
		$font_awesome_icons['application/java'] = '\f4e4';
		$font_awesome_icons['application/javascript'] = '\f3b8';
		$font_awesome_icons['application/msword'] = '\f1c2';
		$font_awesome_icons['application/octet-stream'] = '\f019';
		$font_awesome_icons['application/onenote'] = '\f328';
		$font_awesome_icons['application/oxps'] = '\f013';
		$font_awesome_icons['application/pdf'] = '\f1c1';
		$font_awesome_icons['application/rar'] = '\f1c6';
		$font_awesome_icons['application/rtf'] = '\f013';
		$font_awesome_icons['application/ttaf+xml'] = '\f013';
		$font_awesome_icons['application/vnd.apple.keynote'] = '\f013';
		$font_awesome_icons['application/vnd.apple.numbers'] = '\f013';
		$font_awesome_icons['application/vnd.apple.pages'] = '\f013';
		$font_awesome_icons['application/vnd.ms-access'] = '\f013';
		$font_awesome_icons['application/vnd.ms-excel'] = '\f1c3';
		$font_awesome_icons['application/vnd.ms-excel.addin.macroEnabled.12'] = '\f1c3';
		$font_awesome_icons['application/vnd.ms-excel.sheet.binary.macroEnabled.12'] = '\f1c3';
		$font_awesome_icons['application/vnd.ms-excel.sheet.macroEnabled.12'] = '\f1c3';
		$font_awesome_icons['application/vnd.ms-excel.template.macroEnabled.12'] = '\f1c3';
		$font_awesome_icons['application/vnd.ms-powerpoint'] = '\f1c4';
		$font_awesome_icons['application/vnd.ms-powerpoint.addin.macroEnabled.12'] = '\f1c4';
		$font_awesome_icons['application/vnd.ms-powerpoint.presentation.macroEnabled.12'] = '\f1c4';
		$font_awesome_icons['application/vnd.ms-powerpoint.slide.macroEnabled.12'] = '\f1c4';
		$font_awesome_icons['application/vnd.ms-powerpoint.slideshow.macroEnabled.12'] = '\f1c4';
		$font_awesome_icons['application/vnd.ms-powerpoint.template.macroEnabled.12'] = '\f1c4';
		$font_awesome_icons['application/vnd.ms-project'] = '\f013';
		$font_awesome_icons['application/vnd.ms-word.document.macroEnabled.12'] = '\f1c2';
		$font_awesome_icons['application/vnd.ms-word.template.macroEnabled.12'] = '\f1c2';
		$font_awesome_icons['application/vnd.ms-write'] = '\f013';
		$font_awesome_icons['application/vnd.ms-xpsdocument'] = '\f013';
		$font_awesome_icons['application/vnd.oasis.opendocument.chart'] = '\f013';
		$font_awesome_icons['application/vnd.oasis.opendocument.database'] = '\f013';
		$font_awesome_icons['application/vnd.oasis.opendocument.formula'] = '\f013';
		$font_awesome_icons['application/vnd.oasis.opendocument.graphics'] = '\f013';
		$font_awesome_icons['application/vnd.oasis.opendocument.presentation'] = '\f013';
		$font_awesome_icons['application/vnd.oasis.opendocument.spreadsheet'] = '\f013';
		$font_awesome_icons['application/vnd.oasis.opendocument.text'] = '\f013';
		$font_awesome_icons['application/vnd.openxmlformats-officedocument.presentationml.presentation'] = '\f013';
		$font_awesome_icons['application/vnd.openxmlformats-officedocument.presentationml.slide'] = '\f013';
		$font_awesome_icons['application/vnd.openxmlformats-officedocument.presentationml.slideshow'] = '\f013';
		$font_awesome_icons['application/vnd.openxmlformats-officedocument.presentationml.template'] = '\f013';
		$font_awesome_icons['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'] = '\f013';
		$font_awesome_icons['application/vnd.openxmlformats-officedocument.spreadsheetml.template'] = '\f013';
		$font_awesome_icons['application/vnd.openxmlformats-officedocument.wordprocessingml.document'] = '\f013';
		$font_awesome_icons['application/vnd.openxmlformats-officedocument.wordprocessingml.template'] = '\f013';
		$font_awesome_icons['application/wordperfect'] = '\f013';
		$font_awesome_icons['application/x-7z-compressed'] = '\f1c6';
		$font_awesome_icons['application/x-gzip'] = '\f1c6';
		$font_awesome_icons['application/x-msdownload'] = '\f497';
		$font_awesome_icons['application/x-shockwave-flash'] = '\f497';
		$font_awesome_icons['application/x-tar'] = '\f1c6';
		$font_awesome_icons['application/zip'] = '\f1c6';
		$font_awesome_icons['audio'] = '\f1c7';
		$font_awesome_icons['audio/aac'] = '\f1c7';
		$font_awesome_icons['audio/flac'] = '\f1c7';
		$font_awesome_icons['audio/midi'] = '\f1c7';
		$font_awesome_icons['audio/mpeg'] = '\f1c7';
		$font_awesome_icons['audio/ogg'] = '\f1c7';
		$font_awesome_icons['audio/wav'] = '\f1c7';
		$font_awesome_icons['audio/x-matroska'] = '\f1c7';
		$font_awesome_icons['audio/x-ms-wax'] = '\f1c7';
		$font_awesome_icons['audio/x-ms-wma'] = '\f1c7';
		$font_awesome_icons['audio/x-realaudio'] = '\f1c7';
		$font_awesome_icons['image'] = '\f03e';
		$font_awesome_icons['image/bmp'] = '\f03e';
		$font_awesome_icons['image/gif'] = '\f03e';
		$font_awesome_icons['image/heic'] = '\f03e';
		$font_awesome_icons['image/jpeg'] = '\f03e';
		$font_awesome_icons['image/png'] = '\f03e';
		$font_awesome_icons['image/tiff'] = '\f03e';
		$font_awesome_icons['image/webp'] = '\f03e';
		$font_awesome_icons['image/x-icon'] = '\f03e';
		$font_awesome_icons['pdf'] = '\f1c1';
		$font_awesome_icons['text/calendar'] = '\f133';
		$font_awesome_icons['text/css'] = '\f38b';
		$font_awesome_icons['text/html'] = '\f13b';
		$font_awesome_icons['text/plain'] = '\f15b';
		$font_awesome_icons['text/richtext'] = '\f15b';
		$font_awesome_icons['text/tab-separated-values'] = '\f15b';
		$font_awesome_icons['text/tsv'] = '\f15b';
		$font_awesome_icons['text/vtt'] = '\f15b';
		$font_awesome_icons['video'] = '\f03d';
		$font_awesome_icons['video/3gpp2'] = '\f03d';
		$font_awesome_icons['video/3gpp'] = '\f03d';
		$font_awesome_icons['video/avi'] = '\f03d';
		$font_awesome_icons['video/divx'] = '\f03d';
		$font_awesome_icons['video/mp4'] = '\f03d';
		$font_awesome_icons['video/mpeg'] = '\f03d';
		$font_awesome_icons['video/ogg'] = '\f03d';
		$font_awesome_icons['video/quicktime'] = '\f03d';
		$font_awesome_icons['video/webm'] = '\f03d';
		$font_awesome_icons['video/quicktime'] = '\f03d';
		$font_awesome_icons['video/x-flv'] = '\f03d';
		$font_awesome_icons['video/x-matroska'] = '\f03d';
		$font_awesome_icons['video/x-ms-asf'] = '\f03d';
		$font_awesome_icons['video/x-ms-wm'] = '\f03d';
		$font_awesome_icons['video/x-ms-wmv'] = '\f03d';
		$font_awesome_icons['video/x-ms-wmx'] = '\f03d';
		return apply_filters( 'downloadlist_fontawesome_icons', $font_awesome_icons );
	}

	/**
	 * Return the style-files this iconset is using.
	 *
	 * @return array
	 */
	public function get_style_files(): array {
		return array(
			array(
				'handle' => 'fontawesome',
				'path' => plugins_url( '/css/fontawesome/fontawesome6.css', DL_PLUGIN )
			)
		);
	}
}
