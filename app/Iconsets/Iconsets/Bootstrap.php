<?php
/**
 * File for bootstrap iconset.
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
 * Definition for bootstrap iconset.
 */
class Bootstrap extends Iconset_Base implements Iconset {
	/**
	 * Set type of this iconset.
	 *
	 * @var string
	 */
	protected string $type = 'bootstrap';

	/**
	 * Set slug of this iconset.
	 *
	 * @var string
	 */
	protected string $slug = 'bootstrap';

	/**
	 * This iconset is a generic iconset (e.g. a font) where users can not add custom icons.
	 *
	 * @var bool
	 */
	protected bool $generic = true;

	/**
	 * Instance of this object.
	 *
	 * @var ?Bootstrap
	 */
	private static ?Bootstrap $instance = null;

	/**
	 * Return the instance of this Singleton object.
	 */
	public static function get_instance(): Bootstrap {
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
		$this->label = __( 'Bootstrap', 'download-list-block-with-icons' );
	}

	/**
	 * Return all possible bootstrap as array.
	 *
	 * @return array<string,string>
	 */
	private function get_icon_codes(): array {
		$bootstrapicons = array_flip( Helper::get_mime_types() );
		ksort( $bootstrapicons );
		$bootstrapicons['application']                                    = '\F10B';
		$bootstrapicons['application/java']                               = '\F10B';
		$bootstrapicons['application/javascript']                         = '\F10B';
		$bootstrapicons['application/msword']                             = '\F10B';
		$bootstrapicons['application/octet-stream']                       = '\F10B';
		$bootstrapicons['application/onenote']                            = '\F10B';
		$bootstrapicons['application/oxps']                               = '\F10B';
		$bootstrapicons['application/pdf']                                = '\F640';
		$bootstrapicons['application/rar']                                = '\F10B';
		$bootstrapicons['application/rtf']                                = '\F10B';
		$bootstrapicons['application/ttaf+xml']                           = '\F10B';
		$bootstrapicons['application/vnd.apple.keynote']                  = '\F10B';
		$bootstrapicons['application/vnd.apple.numbers']                  = '\F10B';
		$bootstrapicons['application/vnd.apple.pages']                    = '\F10B';
		$bootstrapicons['application/vnd.ms-access']                      = '\F10B';
		$bootstrapicons['application/vnd.ms-excel']                       = '\F10B';
		$bootstrapicons['application/vnd.ms-excel.addin.macroEnabled.12'] = '\F10B';
		$bootstrapicons['application/vnd.ms-excel.sheet.binary.macroEnabled.12']      = '\F10B';
		$bootstrapicons['application/vnd.ms-excel.sheet.macroEnabled.12']             = '\F10B';
		$bootstrapicons['application/vnd.ms-excel.template.macroEnabled.12']          = '\F10B';
		$bootstrapicons['application/vnd.ms-powerpoint']                              = '\F10B';
		$bootstrapicons['application/vnd.ms-powerpoint.addin.macroEnabled.12']        = '\F10B';
		$bootstrapicons['application/vnd.ms-powerpoint.presentation.macroEnabled.12'] = '\F10B';
		$bootstrapicons['application/vnd.ms-powerpoint.slide.macroEnabled.12']        = '\F10B';
		$bootstrapicons['application/vnd.ms-powerpoint.slideshow.macroEnabled.12']    = '\F10B';
		$bootstrapicons['application/vnd.ms-powerpoint.template.macroEnabled.12']     = '\F10B';
		$bootstrapicons['application/vnd.ms-project']                                 = '\F10B';
		$bootstrapicons['application/vnd.ms-word.document.macroEnabled.12']           = '\F10B';
		$bootstrapicons['application/vnd.ms-word.template.macroEnabled.12']           = '\F10B';
		$bootstrapicons['application/vnd.ms-write']                                   = '\F10B';
		$bootstrapicons['application/vnd.ms-xpsdocument']                             = '\F10B';
		$bootstrapicons['application/vnd.oasis.opendocument.chart']                   = '\F10B';
		$bootstrapicons['application/vnd.oasis.opendocument.database']                = '\F10B';
		$bootstrapicons['application/vnd.oasis.opendocument.formula']                 = '\F10B';
		$bootstrapicons['application/vnd.oasis.opendocument.graphics']                = '\F10B';
		$bootstrapicons['application/vnd.oasis.opendocument.presentation']            = '\F10B';
		$bootstrapicons['application/vnd.oasis.opendocument.spreadsheet']             = '\F10B';
		$bootstrapicons['application/vnd.oasis.opendocument.text']                    = '\F10B';
		$bootstrapicons['application/vnd.openxmlformats-officedocument.presentationml.presentation'] = '\F10B';
		$bootstrapicons['application/vnd.openxmlformats-officedocument.presentationml.slide']        = '\F10B';
		$bootstrapicons['application/vnd.openxmlformats-officedocument.presentationml.slideshow']    = '\F10B';
		$bootstrapicons['application/vnd.openxmlformats-officedocument.presentationml.template']     = '\F10B';
		$bootstrapicons['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']         = '\F10B';
		$bootstrapicons['application/vnd.openxmlformats-officedocument.spreadsheetml.template']      = '\F10B';
		$bootstrapicons['application/vnd.openxmlformats-officedocument.wordprocessingml.document']   = '\F10B';
		$bootstrapicons['application/vnd.openxmlformats-officedocument.wordprocessingml.template']   = '\F10B';
		$bootstrapicons['application/wordperfect']       = '\F10B';
		$bootstrapicons['application/x-7z-compressed']   = '\F10B';
		$bootstrapicons['application/x-gzip']            = '\F10B';
		$bootstrapicons['application/x-msdownload']      = '\F10B';
		$bootstrapicons['application/x-shockwave-flash'] = '\F10B';
		$bootstrapicons['application/x-tar']             = '\F10B';
		$bootstrapicons['application/zip']               = '\F10B';
		$bootstrapicons['audio']                         = '\F49E';
		$bootstrapicons['audio/aac']                     = '\F49E';
		$bootstrapicons['audio/flac']                    = '\F49E';
		$bootstrapicons['audio/midi']                    = '\F49E';
		$bootstrapicons['audio/mpeg']                    = '\F21A';
		$bootstrapicons['audio/ogg']                     = '\F49E';
		$bootstrapicons['audio/wav']                     = '\F49E';
		$bootstrapicons['audio/x-matroska']              = '\F49E';
		$bootstrapicons['audio/x-ms-wax']                = '\F49E';
		$bootstrapicons['audio/x-ms-wma']                = '\F49E';
		$bootstrapicons['audio/x-realaudio']             = '\F49E';
		$bootstrapicons['image']                         = '\F226';
		$bootstrapicons['image/bmp']                     = '\F226';
		$bootstrapicons['image/gif']                     = '\F226';
		$bootstrapicons['image/heic']                    = '\F226';
		$bootstrapicons['image/jpeg']                    = '\F226';
		$bootstrapicons['image/png']                     = '\F226';
		$bootstrapicons['image/tiff']                    = '\F226';
		$bootstrapicons['image/webp']                    = '\F226';
		$bootstrapicons['image/x-icon']                  = '\F226';
		$bootstrapicons['pdf']                           = '\F640';
		$bootstrapicons['text/calendar']                 = '\F1F6';
		$bootstrapicons['text/css']                      = '\F742';
		$bootstrapicons['text/html']                     = '\F749';
		$bootstrapicons['text/plain']                    = '\F75D';
		$bootstrapicons['text/richtext']                 = '\F766';
		$bootstrapicons['text/tab-separated-values']     = '\F766';
		$bootstrapicons['text/tsv']                      = '\F766';
		$bootstrapicons['text/vtt']                      = '\F766';
		$bootstrapicons['video']                         = '\F21A';
		$bootstrapicons['video/3gpp2']                   = '\F21A';
		$bootstrapicons['video/3gpp']                    = '\F21A';
		$bootstrapicons['video/avi']                     = '\F21A';
		$bootstrapicons['video/divx']                    = '\F21A';
		$bootstrapicons['video/mp4']                     = '\F74F';
		$bootstrapicons['video/mpeg']                    = '\F21A';
		$bootstrapicons['video/ogg']                     = '\F21A';
		$bootstrapicons['video/quicktime']               = '\F21A';
		$bootstrapicons['video/webm']                    = '\F21A';
		$bootstrapicons['video/quicktime']               = '\F21A';
		$bootstrapicons['video/x-flv']                   = '\F21A';
		$bootstrapicons['video/x-matroska']              = '\F21A';
		$bootstrapicons['video/x-ms-asf']                = '\F21A';
		$bootstrapicons['video/x-ms-wm']                 = '\F21A';
		$bootstrapicons['video/x-ms-wmv']                = '\F21A';
		$bootstrapicons['video/x-ms-wmx']                = '\F21A';

		/**
		 * Filter the list of bootstrap icons. This list is an array with the not optimized
		 * mime type as index and the bootstrap-unicode as value.
		 *
		 * Example:
		 * ```
		 * add_filter( 'downloadlist_bootstrap_icons', function( $list ) {
		 *  $list['application/example'] = '\f42';
		 *  return $list;
		 * });
		 * ```
		 *
		 * @param array<string,string> $bootstrapicons List of the icons.
		 * @since 3.0.0 Available since 3.0.0
		 */
		return apply_filters( 'downloadlist_bootstrap_icons', $bootstrapicons );
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
				$style         .= '.wp-block-downloadlist-list.iconset-' . $term_slug . ' .file_' . $type . ':before { content: "' . $icon . '";font-family: "bootstrap-icons", sans-serif;font-size: ' . $width . 'px; }';
				$types[ $type ] = 1;
			}
			if ( ! empty( $subtype ) ) {
				$style            .= '.wp-block-downloadlist-list.iconset-' . $term_slug . ' .file_' . $subtype . ':before { content: "' . $icon . '";font-family: "bootstrap-icons", sans-serif;font-size: ' . $width . 'px; }';
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
				'handle' => 'bootstrap',
				'url'    => plugins_url( '/css/bootstrap/bootstrap-icons.css', DL_PLUGIN ),
				'path'   => plugin_dir_path( DL_PLUGIN ) . '/css/bootstrap/bootstrap-icons.css',
			),
		);

		/**
		 * Filter the files used for bootstrap.
		 *
		 * @param array<int,array<string,mixed>> $files List of the files.
		 * @since 3.4.0 Available since 3.4.0
		 */
		return apply_filters( 'downloadlist_bootstrap_files', $files );
	}
}
