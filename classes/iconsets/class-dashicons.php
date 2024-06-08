<?php
/**
 * File for dashicon iconset.
 *
 * @package download-list-block-with-icons
 */

namespace downloadlist\iconsets;

// prevent also other direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use downloadlist\Helper;
use downloadlist\Iconset;
use downloadlist\Iconset_Base;
use WP_Term;

/**
 * Definition for dashicon iconset.
 */
class Dashicons extends Iconset_Base implements Iconset {
	/**
	 * Set type of this iconset.
	 *
	 * @var string
	 */
	protected string $type = 'dashicons';

	/**
	 * Set slug of this iconset.
	 *
	 * @var string
	 */
	protected string $slug = 'dashicons';

	/**
	 * This iconset should be default on installation.
	 *
	 * @var bool
	 */
	protected bool $should_be_default = true;

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
		$this->label = __( 'Dashicons', 'download-list-block-with-icons' );
	}

	/**
	 * Get all possible dashicons as array.
	 *
	 * @return array
	 */
	private function get_icon_codes(): array {
		$dashicons = array_flip( helper::get_mime_types() );
		ksort( $dashicons );
		$dashicons['application']                                    = '\f497';
		$dashicons['application/java']                               = '\f497';
		$dashicons['application/javascript']                         = '\f497';
		$dashicons['application/msword']                             = '\f497';
		$dashicons['application/octet-stream']                       = '\f497';
		$dashicons['application/onenote']                            = '\f497';
		$dashicons['application/oxps']                               = '\f497';
		$dashicons['application/pdf']                                = '\f190';
		$dashicons['application/rar']                                = '\f497';
		$dashicons['application/rtf']                                = '\f497';
		$dashicons['application/ttaf+xml']                           = '\f497';
		$dashicons['application/vnd.apple.keynote']                  = '\f497';
		$dashicons['application/vnd.apple.numbers']                  = '\f497';
		$dashicons['application/vnd.apple.pages']                    = '\f497';
		$dashicons['application/vnd.ms-access']                      = '\f497';
		$dashicons['application/vnd.ms-excel']                       = '\f497';
		$dashicons['application/vnd.ms-excel.addin.macroEnabled.12'] = '\f497';
		$dashicons['application/vnd.ms-excel.sheet.binary.macroEnabled.12']      = '\f497';
		$dashicons['application/vnd.ms-excel.sheet.macroEnabled.12']             = '\f497';
		$dashicons['application/vnd.ms-excel.template.macroEnabled.12']          = '\f497';
		$dashicons['application/vnd.ms-powerpoint']                              = '\f497';
		$dashicons['application/vnd.ms-powerpoint.addin.macroEnabled.12']        = '\f497';
		$dashicons['application/vnd.ms-powerpoint.presentation.macroEnabled.12'] = '\f497';
		$dashicons['application/vnd.ms-powerpoint.slide.macroEnabled.12']        = '\f497';
		$dashicons['application/vnd.ms-powerpoint.slideshow.macroEnabled.12']    = '\f497';
		$dashicons['application/vnd.ms-powerpoint.template.macroEnabled.12']     = '\f497';
		$dashicons['application/vnd.ms-project']                                 = '\f497';
		$dashicons['application/vnd.ms-word.document.macroEnabled.12']           = '\f497';
		$dashicons['application/vnd.ms-word.template.macroEnabled.12']           = '\f497';
		$dashicons['application/vnd.ms-write']                                   = '\f497';
		$dashicons['application/vnd.ms-xpsdocument']                             = '\f497';
		$dashicons['application/vnd.oasis.opendocument.chart']                   = '\f497';
		$dashicons['application/vnd.oasis.opendocument.database']                = '\f497';
		$dashicons['application/vnd.oasis.opendocument.formula']                 = '\f497';
		$dashicons['application/vnd.oasis.opendocument.graphics']                = '\f497';
		$dashicons['application/vnd.oasis.opendocument.presentation']            = '\f497';
		$dashicons['application/vnd.oasis.opendocument.spreadsheet']             = '\f497';
		$dashicons['application/vnd.oasis.opendocument.text']                    = '\f497';
		$dashicons['application/vnd.openxmlformats-officedocument.presentationml.presentation'] = '\f497';
		$dashicons['application/vnd.openxmlformats-officedocument.presentationml.slide']        = '\f497';
		$dashicons['application/vnd.openxmlformats-officedocument.presentationml.slideshow']    = '\f497';
		$dashicons['application/vnd.openxmlformats-officedocument.presentationml.template']     = '\f497';
		$dashicons['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']         = '\f497';
		$dashicons['application/vnd.openxmlformats-officedocument.spreadsheetml.template']      = '\f497';
		$dashicons['application/vnd.openxmlformats-officedocument.wordprocessingml.document']   = '\f497';
		$dashicons['application/vnd.openxmlformats-officedocument.wordprocessingml.template']   = '\f497';
		$dashicons['application/wordperfect']       = '\f497';
		$dashicons['application/x-7z-compressed']   = '\f497';
		$dashicons['application/x-gzip']            = '\f497';
		$dashicons['application/x-msdownload']      = '\f497';
		$dashicons['application/x-shockwave-flash'] = '\f497';
		$dashicons['application/x-tar']             = '\f497';
		$dashicons['application/zip']               = '\f497';
		$dashicons['audio']                         = '\f500';
		$dashicons['audio/aac']                     = '\f500';
		$dashicons['audio/flac']                    = '\f500';
		$dashicons['audio/midi']                    = '\f500';
		$dashicons['audio/mpeg']                    = '\f500';
		$dashicons['audio/ogg']                     = '\f500';
		$dashicons['audio/wav']                     = '\f500';
		$dashicons['audio/x-matroska']              = '\f500';
		$dashicons['audio/x-ms-wax']                = '\f500';
		$dashicons['audio/x-ms-wma']                = '\f500';
		$dashicons['audio/x-realaudio']             = '\f500';
		$dashicons['image']                         = '\f128';
		$dashicons['image/bmp']                     = '\f128';
		$dashicons['image/gif']                     = '\f128';
		$dashicons['image/heic']                    = '\f128';
		$dashicons['image/jpeg']                    = '\f128';
		$dashicons['image/png']                     = '\f128';
		$dashicons['image/tiff']                    = '\f128';
		$dashicons['image/webp']                    = '\f128';
		$dashicons['image/x-icon']                  = '\f128';
		$dashicons['pdf']                           = '\f190';
		$dashicons['text/calendar']                 = '\f145';
		$dashicons['text/css']                      = '\f491';
		$dashicons['text/html']                     = '\f491';
		$dashicons['text/plain']                    = '\f491';
		$dashicons['text/richtext']                 = '\f491';
		$dashicons['text/tab-separated-values']     = '\f491';
		$dashicons['text/tsv']                      = '\f491';
		$dashicons['text/vtt']                      = '\f491';
		$dashicons['video']                         = '\f126';
		$dashicons['video/3gpp2']                   = '\f126';
		$dashicons['video/3gpp']                    = '\f126';
		$dashicons['video/avi']                     = '\f126';
		$dashicons['video/divx']                    = '\f126';
		$dashicons['video/mp4']                     = '\f126';
		$dashicons['video/mpeg']                    = '\f126';
		$dashicons['video/ogg']                     = '\f126';
		$dashicons['video/quicktime']               = '\f126';
		$dashicons['video/webm']                    = '\f126';
		$dashicons['video/quicktime']               = '\f126';
		$dashicons['video/x-flv']                   = '\f126';
		$dashicons['video/x-matroska']              = '\f126';
		$dashicons['video/x-ms-asf']                = '\f126';
		$dashicons['video/x-ms-wm']                 = '\f126';
		$dashicons['video/x-ms-wmv']                = '\f126';
		$dashicons['video/x-ms-wmx']                = '\f126';

		/**
		 * Filter the list of dashicons. This list is an array with the not optimized
		 * mime type as index and the bootstrap-unicode as value.
		 *
		 * Example:
		 * ```
		 * add_filter( 'downloadlist_dashicons_icons', function( $list ) {
		 *  $list['application/example'] = '\f42';
		 *  return $list;
		 * });
		 * ```
		 *
		 * @param array $dashicons List of the icons.
		 * @since 3.0.0 Available since 3.0.0
		 */
		return apply_filters( 'downloadlist_dashicons_icons', $dashicons );
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

		// get output if term is available.
		if ( $term instanceof WP_Term ) {
			// get the width on the term as font-size.
			$width = absint( get_term_meta( $term->term_id, 'width', true ) );

			// list of types already generated.
			$types = array();

			// loop through the icons and add them to styling.
			foreach ( $this->get_icon_codes() as $local_filetype => $icon ) {
				list($type, $subtype) = Helper::get_type_and_subtype_from_mimetype( $local_filetype );
				if ( empty( $types[ $type ] ) ) {
					$style         .= '.wp-block-downloadlist-list.iconset-' . $term_slug . ' .file_' . $type . ':before { content: "' . $icon . '";font-family: "dashicons", sans-serif;font-size: ' . $width . 'px; }';
					$types[ $type ] = 1;
				}
				if ( ! empty( $subtype ) && empty( $types[ $subtype ] ) ) {
					$style            .= '.wp-block-downloadlist-list.iconset-' . $term_slug . ' .file_' . $subtype . ':before { content: "' . $icon . '";font-family: "dashicons", sans-serif;font-size: ' . $width . 'px; }';
					$types[ $subtype ] = 1;
				}
			}
		}

		// return resulting code.
		return $style;
	}

	/**
	 * Return the by iconset supported filetypes.
	 *
	 * @return array
	 */
	public function get_file_types(): array {
		return array_keys( $this->get_icon_codes() );
	}

	/**
	 * Return the style-files this iconset is using.
	 *
	 * @return array
	 */
	public function get_style_files(): array {
		$files = array(
			array(
				'handle' => 'dashicons',
				'depends' => array( 'dashicons' )
			),
		);

		/**
		 * Filter the files used for dashicons.
		 *
		 * @param array $files List of the files.
		 * @since 3.4.0 Available since 3.4.0
		 */
		return apply_filters( 'downloadlist_dashicons_files', $files );
	}
}
