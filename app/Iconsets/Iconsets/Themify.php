<?php
/**
 * File for themify iconset.
 *
 * @source https://themify.me/themify-icons
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
 * Definition for themify iconset.
 */
class Themify extends Iconset_Base implements Iconset {
	/**
	 * Set type of this iconset.
	 *
	 * @var string
	 */
	protected string $type = 'themify';

	/**
	 * Set slug of this iconset.
	 *
	 * @var string
	 */
	protected string $slug = 'themify';

	/**
	 * This iconset is a generic iconset (e.g. a font) where users can not add custom icons.
	 *
	 * @var bool
	 */
	protected bool $generic = true;

	/**
	 * Instance of this object.
	 *
	 * @var ?Themify
	 */
	private static ?Themify $instance = null;

	/**
	 * Return the instance of this Singleton object.
	 */
	public static function get_instance(): Themify {
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
		$this->label = __( 'Themify', 'download-list-block-with-icons' );
	}

	/**
	 * Return all possible themify as array.
	 *
	 * @return array<string,string>
	 */
	private function get_icon_codes(): array {
		$themify = array_flip( Helper::get_mime_types() );
		ksort( $themify );
		$themify['application']                                    = '\e747';
		$themify['application/java']                               = '\e747';
		$themify['application/javascript']                         = '\e747';
		$themify['application/msword']                             = '\e747';
		$themify['application/octet-stream']                       = '\e747';
		$themify['application/onenote']                            = '\e747';
		$themify['application/oxps']                               = '\e747';
		$themify['application/pdf']                                = '\e617';
		$themify['application/rar']                                = '\e747';
		$themify['application/rtf']                                = '\e747';
		$themify['application/ttaf+xml']                           = '\e747';
		$themify['application/vnd.apple.keynote']                  = '\e747';
		$themify['application/vnd.apple.numbers']                  = '\e747';
		$themify['application/vnd.apple.pages']                    = '\e747';
		$themify['application/vnd.ms-access']                      = '\e747';
		$themify['application/vnd.ms-excel']                       = '\e747';
		$themify['application/vnd.ms-excel.addin.macroEnabled.12'] = '\e747';
		$themify['application/vnd.ms-excel.sheet.binary.macroEnabled.12']      = '\e747';
		$themify['application/vnd.ms-excel.sheet.macroEnabled.12']             = '\e747';
		$themify['application/vnd.ms-excel.template.macroEnabled.12']          = '\e747';
		$themify['application/vnd.ms-powerpoint']                              = '\e747';
		$themify['application/vnd.ms-powerpoint.addin.macroEnabled.12']        = '\e747';
		$themify['application/vnd.ms-powerpoint.presentation.macroEnabled.12'] = '\e747';
		$themify['application/vnd.ms-powerpoint.slide.macroEnabled.12']        = '\e747';
		$themify['application/vnd.ms-powerpoint.slideshow.macroEnabled.12']    = '\e747';
		$themify['application/vnd.ms-powerpoint.template.macroEnabled.12']     = '\e747';
		$themify['application/vnd.ms-project']                                 = '\e747';
		$themify['application/vnd.ms-word.document.macroEnabled.12']           = '\e747';
		$themify['application/vnd.ms-word.template.macroEnabled.12']           = '\e747';
		$themify['application/vnd.ms-write']                                   = '\e747';
		$themify['application/vnd.ms-xpsdocument']                             = '\e747';
		$themify['application/vnd.oasis.opendocument.chart']                   = '\e747';
		$themify['application/vnd.oasis.opendocument.database']                = '\e747';
		$themify['application/vnd.oasis.opendocument.formula']                 = '\e747';
		$themify['application/vnd.oasis.opendocument.graphics']                = '\e747';
		$themify['application/vnd.oasis.opendocument.presentation']            = '\e747';
		$themify['application/vnd.oasis.opendocument.spreadsheet']             = '\e747';
		$themify['application/vnd.oasis.opendocument.text']                    = '\e747';
		$themify['application/vnd.openxmlformats-officedocument.presentationml.presentation'] = '\e747';
		$themify['application/vnd.openxmlformats-officedocument.presentationml.slide']        = '\e747';
		$themify['application/vnd.openxmlformats-officedocument.presentationml.slideshow']    = '\e747';
		$themify['application/vnd.openxmlformats-officedocument.presentationml.template']     = '\e747';
		$themify['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']         = '\e747';
		$themify['application/vnd.openxmlformats-officedocument.spreadsheetml.template']      = '\e747';
		$themify['application/vnd.openxmlformats-officedocument.wordprocessingml.document']   = '\e747';
		$themify['application/vnd.openxmlformats-officedocument.wordprocessingml.template']   = '\e747';
		$themify['application/wordperfect']       = '\e747';
		$themify['application/x-7z-compressed']   = '\e747';
		$themify['application/x-gzip']            = '\e747';
		$themify['application/x-msdownload']      = '\e747';
		$themify['application/x-shockwave-flash'] = '\e747';
		$themify['application/x-tar']             = '\e747';
		$themify['application/zip']               = '\e747';
		$themify['audio']                         = '\e6ad';
		$themify['audio/aac']                     = '\e6ad';
		$themify['audio/flac']                    = '\e6ad';
		$themify['audio/midi']                    = '\e6ad';
		$themify['audio/mpeg']                    = '\e6ad';
		$themify['audio/ogg']                     = '\e6ad';
		$themify['audio/wav']                     = '\e6ad';
		$themify['audio/x-matroska']              = '\e6ad';
		$themify['audio/x-ms-wax']                = '\e6ad';
		$themify['audio/x-ms-wma']                = '\e6ad';
		$themify['audio/x-realaudio']             = '\e6ad';
		$themify['image']                         = '\e633';
		$themify['image/bmp']                     = '\e633';
		$themify['image/gif']                     = '\e633';
		$themify['image/heic']                    = '\e633';
		$themify['image/jpeg']                    = '\e633';
		$themify['image/png']                     = '\e633';
		$themify['image/tiff']                    = '\e633';
		$themify['image/webp']                    = '\e633';
		$themify['image/x-icon']                  = '\e633';
		$themify['pdf']                           = '\e617';
		$themify['text/calendar']                 = '\e672';
		$themify['text/css']                      = '\e672';
		$themify['text/html']                     = '\e758';
		$themify['text/plain']                    = '\e672';
		$themify['text/richtext']                 = '\e672';
		$themify['text/tab-separated-values']     = '\e672';
		$themify['text/tsv']                      = '\e672';
		$themify['text/vtt']                      = '\e672';
		$themify['video']                         = '\e6ce';
		$themify['video/3gpp2']                   = '\e6ce';
		$themify['video/3gpp']                    = '\e6ce';
		$themify['video/avi']                     = '\e6ce';
		$themify['video/divx']                    = '\e6ce';
		$themify['video/mp4']                     = '\e6ce';
		$themify['video/mpeg']                    = '\e6ce';
		$themify['video/ogg']                     = '\e6ce';
		$themify['video/quicktime']               = '\e6ce';
		$themify['video/webm']                    = '\e6ce';
		$themify['video/quicktime']               = '\e6ce';
		$themify['video/x-flv']                   = '\e6ce';
		$themify['video/x-matroska']              = '\e6ce';
		$themify['video/x-ms-asf']                = '\e6ce';
		$themify['video/x-ms-wm']                 = '\e6ce';
		$themify['video/x-ms-wmv']                = '\e6ce';
		$themify['video/x-ms-wmx']                = '\e6ce';

		/**
		 * Filter the list of themify icons. This list is an array with the not optimized
		 * mime type as index and the themify-unicode as value.
		 *
		 * Example:
		 * ```
		 * add_filter( 'downloadlist_themify_icons', function( $list ) {
		 *  $list['application/example'] = '\f42';
		 *  return $list;
		 * });
		 * ```
		 *
		 * @param array<string,string> $themify List of the icons.
		 * @since 4.1.0 Available since 4.1.0
		 */
		return apply_filters( 'downloadlist_themify_icons', $themify );
	}

	/**
	 * Return style for given file-type.
	 *
	 * @param int    $post_id ID of the icon-post.
	 * @param string $term_slug Slug of the iconset-term.
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
		foreach ( $this->get_icon_codes() as $icon_filetype => $icon ) {
			list($type, $subtype) = Helper::get_type_and_subtype_from_mimetype( $icon_filetype );
			if ( empty( $types[ $type ] ) ) {
				$style         .= '.wp-block-downloadlist-list.iconset-' . $term_slug . ' .file_' . $type . ':before { content: "' . $icon . '";font-family: "themify", sans-serif;font-size: ' . $width . 'px; }';
				$types[ $type ] = 1;
			}
			if ( ! empty( $subtype ) ) {
				$style            .= '.wp-block-downloadlist-list.iconset-' . $term_slug . ' .file_' . $subtype . ':before { content: "' . $icon . '";font-family: "themify", sans-serif;font-size: ' . $width . 'px; }';
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
	 * Return the style-files this iconset is using.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	public function get_style_files(): array {
		$files = array(
			array(
				'handle' => 'themify',
				'url'    => plugins_url( '/css/themify/themify-icons.css', DL_PLUGIN ),
				'path'   => plugin_dir_path( DL_PLUGIN ) . '/css/themify/themify-icons.css',
			),
		);

		/**
		 * Filter the files used for bootstrap.
		 *
		 * @param array<int,array<string,mixed>> $files List of the files.
		 * @since 4.1.0 Available since 4.1.0
		 */
		return apply_filters( 'downloadlist_themify_files', $files );
	}
}
