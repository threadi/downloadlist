<?php
/**
 * File for Fontawesome iconset.
 *
 * @package download-list-block-with-icons
 */

namespace DownloadListWithIcons\Iconsets\Iconsets;

// prevent direct access.
defined( 'ABSPATH' ) || exit;

use DownloadListWithIcons\Iconsets\Iconset;
use DownloadListWithIcons\Iconsets\Iconset_Base;
use DownloadListWithIcons\Plugin\Helper;
use WP_Term;

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
	 * Instance of this object.
	 *
	 * @var ?Fontawesome
	 */
	private static ?Fontawesome $instance = null;

	/**
	 * Return the instance of this Singleton object.
	 */
	public static function get_instance(): Fontawesome {
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
		$this->label = __( 'FontAweSome', 'download-list-block-with-icons' );
	}

	/**
	 * Get style for given file-type.
	 *
	 * @param int    $post_id ID of the icon-post.
	 * @param string $term_slug ID of the iconset-term.
	 * @param string $filetype Name for the filetype to add.
	 * @return string
	 */
	public function get_style_for_filetype( int $post_id, string $term_slug, string $filetype ): string {
		// initialize return variable.
		$style = '';

		// get the term.
		$term = get_term_by( 'slug', $term_slug, 'dl_icon_set' );

		// bail if term is not available.
		if ( ! $term instanceof WP_Term ) {
			return $style;
		}

		// get the width on the term as font-size.
		$width = absint( get_term_meta( $term->term_id, 'width', true ) );

		// list of types already generated.
		$types = array();

		// loop through the icons and add them to styling.
		foreach ( $this->get_icon_codes() as $local_filetype => $icon ) {
			list($type, $subtype) = Helper::get_type_and_subtype_from_mimetype( $local_filetype );
			if ( empty( $types[ $type ] ) ) {
				$style         .= '.wp-block-downloadlist-list.iconset-' . $term_slug . ' .file_' . $type . ':before { content: "' . $icon . '";font-family: "Font Awesome 6 Free", sans-serif;font-size: ' . $width . 'px;font-weight: 900 }';
				$types[ $type ] = 1;
			}
			if ( ! empty( $subtype ) && empty( $types[ $subtype ] ) ) {
				$style            .= '.wp-block-downloadlist-list.iconset-' . $term_slug . ' .file_' . $subtype . ':before { content: "' . $icon . '";font-family: "Font Awesome 6 Free", sans-serif;font-size: ' . $width . 'px;font-weight: 900 }';
				$types[ $subtype ] = 1;
			}
		}

		// return resulting code.
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
	 * Get all possible dashicons as array.
	 *
	 * @return array<string,string>
	 */
	private function get_icon_codes(): array {
		$font_awesome_icons = array_flip( Helper::get_mime_types() );
		ksort( $font_awesome_icons );
		$font_awesome_icons['application']                                    = '\f013';
		$font_awesome_icons['application/java']                               = '\f4e4';
		$font_awesome_icons['application/javascript']                         = '\f3b8';
		$font_awesome_icons['application/msword']                             = '\f1c2';
		$font_awesome_icons['application/octet-stream']                       = '\f019';
		$font_awesome_icons['application/onenote']                            = '\f328';
		$font_awesome_icons['application/oxps']                               = '\f013';
		$font_awesome_icons['application/pdf']                                = '\f1c1';
		$font_awesome_icons['application/rar']                                = '\f1c6';
		$font_awesome_icons['application/rtf']                                = '\f013';
		$font_awesome_icons['application/ttaf+xml']                           = '\f013';
		$font_awesome_icons['application/vnd.apple.keynote']                  = '\f013';
		$font_awesome_icons['application/vnd.apple.numbers']                  = '\f013';
		$font_awesome_icons['application/vnd.apple.pages']                    = '\f013';
		$font_awesome_icons['application/vnd.ms-access']                      = '\f013';
		$font_awesome_icons['application/vnd.ms-excel']                       = '\f1c3';
		$font_awesome_icons['application/vnd.ms-excel.addin.macroEnabled.12'] = '\f1c3';
		$font_awesome_icons['application/vnd.ms-excel.sheet.binary.macroEnabled.12']      = '\f1c3';
		$font_awesome_icons['application/vnd.ms-excel.sheet.macroEnabled.12']             = '\f1c3';
		$font_awesome_icons['application/vnd.ms-excel.template.macroEnabled.12']          = '\f1c3';
		$font_awesome_icons['application/vnd.ms-powerpoint']                              = '\f1c4';
		$font_awesome_icons['application/vnd.ms-powerpoint.addin.macroEnabled.12']        = '\f1c4';
		$font_awesome_icons['application/vnd.ms-powerpoint.presentation.macroEnabled.12'] = '\f1c4';
		$font_awesome_icons['application/vnd.ms-powerpoint.slide.macroEnabled.12']        = '\f1c4';
		$font_awesome_icons['application/vnd.ms-powerpoint.slideshow.macroEnabled.12']    = '\f1c4';
		$font_awesome_icons['application/vnd.ms-powerpoint.template.macroEnabled.12']     = '\f1c4';
		$font_awesome_icons['application/vnd.ms-project']                                 = '\f013';
		$font_awesome_icons['application/vnd.ms-word.document.macroEnabled.12']           = '\f1c2';
		$font_awesome_icons['application/vnd.ms-word.template.macroEnabled.12']           = '\f1c2';
		$font_awesome_icons['application/vnd.ms-write']                                   = '\f013';
		$font_awesome_icons['application/vnd.ms-xpsdocument']                             = '\f013';
		$font_awesome_icons['application/vnd.oasis.opendocument.chart']                   = '\f013';
		$font_awesome_icons['application/vnd.oasis.opendocument.database']                = '\f013';
		$font_awesome_icons['application/vnd.oasis.opendocument.formula']                 = '\f013';
		$font_awesome_icons['application/vnd.oasis.opendocument.graphics']                = '\f013';
		$font_awesome_icons['application/vnd.oasis.opendocument.presentation']            = '\f013';
		$font_awesome_icons['application/vnd.oasis.opendocument.spreadsheet']             = '\f013';
		$font_awesome_icons['application/vnd.oasis.opendocument.text']                    = '\f013';
		$font_awesome_icons['application/vnd.openxmlformats-officedocument.presentationml.presentation'] = '\f013';
		$font_awesome_icons['application/vnd.openxmlformats-officedocument.presentationml.slide']        = '\f013';
		$font_awesome_icons['application/vnd.openxmlformats-officedocument.presentationml.slideshow']    = '\f013';
		$font_awesome_icons['application/vnd.openxmlformats-officedocument.presentationml.template']     = '\f013';
		$font_awesome_icons['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']         = '\f013';
		$font_awesome_icons['application/vnd.openxmlformats-officedocument.spreadsheetml.template']      = '\f013';
		$font_awesome_icons['application/vnd.openxmlformats-officedocument.wordprocessingml.document']   = '\f013';
		$font_awesome_icons['application/vnd.openxmlformats-officedocument.wordprocessingml.template']   = '\f013';
		$font_awesome_icons['application/wordperfect']       = '\f013';
		$font_awesome_icons['application/x-7z-compressed']   = '\f1c6';
		$font_awesome_icons['application/x-gzip']            = '\f1c6';
		$font_awesome_icons['application/x-msdownload']      = '\f497';
		$font_awesome_icons['application/x-shockwave-flash'] = '\f497';
		$font_awesome_icons['application/x-tar']             = '\f1c6';
		$font_awesome_icons['application/zip']               = '\f1c6';
		$font_awesome_icons['audio']                         = '\f1c7';
		$font_awesome_icons['audio/aac']                     = '\f1c7';
		$font_awesome_icons['audio/flac']                    = '\f1c7';
		$font_awesome_icons['audio/midi']                    = '\f1c7';
		$font_awesome_icons['audio/mpeg']                    = '\f1c7';
		$font_awesome_icons['audio/ogg']                     = '\f1c7';
		$font_awesome_icons['audio/wav']                     = '\f1c7';
		$font_awesome_icons['audio/x-matroska']              = '\f1c7';
		$font_awesome_icons['audio/x-ms-wax']                = '\f1c7';
		$font_awesome_icons['audio/x-ms-wma']                = '\f1c7';
		$font_awesome_icons['audio/x-realaudio']             = '\f1c7';
		$font_awesome_icons['image']                         = '\f03e';
		$font_awesome_icons['image/bmp']                     = '\f03e';
		$font_awesome_icons['image/gif']                     = '\f03e';
		$font_awesome_icons['image/heic']                    = '\f03e';
		$font_awesome_icons['image/jpeg']                    = '\f03e';
		$font_awesome_icons['image/png']                     = '\f03e';
		$font_awesome_icons['image/tiff']                    = '\f03e';
		$font_awesome_icons['image/webp']                    = '\f03e';
		$font_awesome_icons['image/x-icon']                  = '\f03e';
		$font_awesome_icons['pdf']                           = '\f1c1';
		$font_awesome_icons['text/calendar']                 = '\f133';
		$font_awesome_icons['text/css']                      = '\f38b';
		$font_awesome_icons['text/html']                     = '\f13b';
		$font_awesome_icons['text/plain']                    = '\f15b';
		$font_awesome_icons['text/richtext']                 = '\f15b';
		$font_awesome_icons['text/tab-separated-values']     = '\f15b';
		$font_awesome_icons['text/tsv']                      = '\f15b';
		$font_awesome_icons['text/vtt']                      = '\f15b';
		$font_awesome_icons['video']                         = '\f03d';
		$font_awesome_icons['video/3gpp2']                   = '\f03d';
		$font_awesome_icons['video/3gpp']                    = '\f03d';
		$font_awesome_icons['video/avi']                     = '\f03d';
		$font_awesome_icons['video/divx']                    = '\f03d';
		$font_awesome_icons['video/mp4']                     = '\f03d';
		$font_awesome_icons['video/mpeg']                    = '\f03d';
		$font_awesome_icons['video/ogg']                     = '\f03d';
		$font_awesome_icons['video/quicktime']               = '\f03d';
		$font_awesome_icons['video/webm']                    = '\f03d';
		$font_awesome_icons['video/quicktime']               = '\f03d';
		$font_awesome_icons['video/x-flv']                   = '\f03d';
		$font_awesome_icons['video/x-matroska']              = '\f03d';
		$font_awesome_icons['video/x-ms-asf']                = '\f03d';
		$font_awesome_icons['video/x-ms-wm']                 = '\f03d';
		$font_awesome_icons['video/x-ms-wmv']                = '\f03d';
		$font_awesome_icons['video/x-ms-wmx']                = '\f03d';

		/**
		 * Filter the list of fontawesome icons. This list is an array with the not optimized
		 * mime type as index and the bootstrap-unicode as value.
		 *
		 * Example:
		 * ```
		 * add_filter( 'downloadlist_fontawesome_icons', function( $list ) {
		 *  $list['application/example'] = '\f42';
		 *  return $list;
		 * });
		 * ```
		 *
		 * @param array<string,string> $font_awesome_icons List of the icons.
		 * @since 3.0.0 Available since 3.0.0
		 */
		return apply_filters( 'downloadlist_fontawesome_icons', $font_awesome_icons );
	}

	/**
	 * Return the style-files this iconset is using.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	public function get_style_files(): array {
		$files = array(
			array(
				'handle' => 'fontawesome',
				'url'    => plugins_url( '/css/fontawesome/fontawesome6.css', DL_PLUGIN ),
				'path'   => plugin_dir_path( DL_PLUGIN ) . '/css/fontawesome/fontawesome6.css',
			),
		);

		/**
		 * Filter the files used for fontawesome.
		 *
		 * @param array<int,array<string,mixed>> $files List of the files.
		 * @since 3.4.0 Available since 3.4.0
		 */
		return apply_filters( 'downloadlist_fontawesome_files', $files );
	}
}
