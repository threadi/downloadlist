<?php
/**
 * File which holds the list of possible fontawesome.
 */

use downloadlist\helper;
use downloadlist\iconsets\Fontawesome;

/**
 * Register the custom iconset.
 *
 * @param $list
 * @return array
 */
function downloadlist_register_fontawesome_iconset( $list ): array {
	$list[] = Fontawesome::get_instance();
	return $list;
}
add_filter( 'downloadlist_register_iconset', 'downloadlist_register_fontawesome_iconset', 10, 1 );

/**
 * Return list of possible fontawesome-icons.
 *
 * @return array
 */
function downloadlist_fontawesome(): array {
	$dashicons = array_flip(helper::get_mime_types());
	ksort($dashicons);
	$dashicons['application'] = '\f497';
	$dashicons['application/java'] = '\f497';
	$dashicons['application/javascript'] = '\f497';
	$dashicons['application/msword'] = '\f497';
	$dashicons['application/octet-stream'] = '\f497';
	$dashicons['application/onenote'] = '\f497';
	$dashicons['application/oxps'] = '\f497';
	$dashicons['application/pdf'] = '\f190';
	$dashicons['application/rar'] = '\f497';
	$dashicons['application/rtf'] = '\f497';
	$dashicons['application/ttaf+xml'] = '\f497';
	$dashicons['application/vnd.apple.keynote'] = '\f497';
	$dashicons['application/vnd.apple.numbers'] = '\f497';
	$dashicons['application/vnd.apple.pages'] = '\f497';
	$dashicons['application/vnd.ms-access'] = '\f497';
	$dashicons['application/vnd.ms-excel'] = '\f497';
	$dashicons['application/vnd.ms-excel.addin.macroEnabled.12'] = '\f497';
	$dashicons['application/vnd.ms-excel.sheet.binary.macroEnabled.12'] = '\f497';
	$dashicons['application/vnd.ms-excel.sheet.macroEnabled.12'] = '\f497';
	$dashicons['application/vnd.ms-excel.template.macroEnabled.12'] = '\f497';
	$dashicons['application/vnd.ms-powerpoint'] = '\f497';
	$dashicons['application/vnd.ms-powerpoint.addin.macroEnabled.12'] = '\f497';
	$dashicons['application/vnd.ms-powerpoint.presentation.macroEnabled.12'] = '\f497';
	$dashicons['application/vnd.ms-powerpoint.slide.macroEnabled.12'] = '\f497';
	$dashicons['application/vnd.ms-powerpoint.slideshow.macroEnabled.12'] = '\f497';
	$dashicons['application/vnd.ms-powerpoint.template.macroEnabled.12'] = '\f497';
	$dashicons['application/vnd.ms-project'] = '\f497';
	$dashicons['application/vnd.ms-word.document.macroEnabled.12'] = '\f497';
	$dashicons['application/vnd.ms-word.template.macroEnabled.12'] = '\f497';
	$dashicons['application/vnd.ms-write'] = '\f497';
	$dashicons['application/vnd.ms-xpsdocument'] = '\f497';
	$dashicons['application/vnd.oasis.opendocument.chart'] = '\f497';
	$dashicons['application/vnd.oasis.opendocument.database'] = '\f497';
	$dashicons['application/vnd.oasis.opendocument.formula'] = '\f497';
	$dashicons['application/vnd.oasis.opendocument.graphics'] = '\f497';
	$dashicons['application/vnd.oasis.opendocument.presentation'] = '\f497';
	$dashicons['application/vnd.oasis.opendocument.spreadsheet'] = '\f497';
	$dashicons['application/vnd.oasis.opendocument.text'] = '\f497';
	$dashicons['application/vnd.openxmlformats-officedocument.presentationml.presentation'] = '\f497';
	$dashicons['application/vnd.openxmlformats-officedocument.presentationml.slide'] = '\f497';
	$dashicons['application/vnd.openxmlformats-officedocument.presentationml.slideshow'] = '\f497';
	$dashicons['application/vnd.openxmlformats-officedocument.presentationml.template'] = '\f497';
	$dashicons['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'] = '\f497';
	$dashicons['application/vnd.openxmlformats-officedocument.spreadsheetml.template'] = '\f497';
	$dashicons['application/vnd.openxmlformats-officedocument.wordprocessingml.document'] = '\f497';
	$dashicons['application/vnd.openxmlformats-officedocument.wordprocessingml.template'] = '\f497';
	$dashicons['application/wordperfect'] = '\f497';
	$dashicons['application/x-7z-compressed'] = '\f497';
	$dashicons['application/x-gzip'] = '\f497';
	$dashicons['application/x-msdownload'] = '\f497';
	$dashicons['application/x-shockwave-flash'] = '\f497';
	$dashicons['application/x-tar'] = '\f497';
	$dashicons['application/zip'] = '\f497';
	$dashicons['audio'] = '\f500';
	$dashicons['audio/aac'] = '\f500';
	$dashicons['audio/flac'] = '\f500';
	$dashicons['audio/midi'] = '\f500';
	$dashicons['audio/mpeg'] = '\f500';
	$dashicons['audio/ogg'] = '\f500';
	$dashicons['audio/wav'] = '\f500';
	$dashicons['audio/x-matroska'] = '\f500';
	$dashicons['audio/x-ms-wax'] = '\f500';
	$dashicons['audio/x-ms-wma'] = '\f500';
	$dashicons['audio/x-realaudio'] = '\f500';
	$dashicons['image'] = '\f128';
	$dashicons['image/bmp'] = '\f128';
	$dashicons['image/gif'] = '\f128';
	$dashicons['image/heic'] = '\f128';
	$dashicons['image/jpeg'] = '\f128';
	$dashicons['image/png'] = '\f128';
	$dashicons['image/tiff'] = '\f128';
	$dashicons['image/webp'] = '\f128';
	$dashicons['image/x-icon'] = '\f128';
	$dashicons['pdf'] = '\f190';
	$dashicons['text/calendar'] = '\f145'; // !!!
	$dashicons['text/css'] = '\f491'; // !!!
	$dashicons['text/html'] = '\f491';
	$dashicons['text/plain'] = '\f491';
	$dashicons['text/richtext'] = '\f491';
	$dashicons['text/tab-separated-values'] = '\f491';
	$dashicons['text/tsv'] = '\f491';
	$dashicons['text/vtt'] = '\f491';
	$dashicons['video'] = '\f126';
	$dashicons['video/3gpp2'] = '\f126';
	$dashicons['video/3gpp'] = '\f126';
	$dashicons['video/avi'] = '\f126';
	$dashicons['video/divx'] = '\f126';
	$dashicons['video/mp4'] = '\f126';
	$dashicons['video/mpeg'] = '\f126';
	$dashicons['video/ogg'] = '\f126';
	$dashicons['video/quicktime'] = '\f126';
	$dashicons['video/webm'] = '\f126';
	$dashicons['video/quicktime'] = '\f126';
	$dashicons['video/x-flv'] = '\f126';
	$dashicons['video/x-matroska'] = '\f126';
	$dashicons['video/x-ms-asf'] = '\f126';
	$dashicons['video/x-ms-wm'] = '\f126';
	$dashicons['video/x-ms-wmv'] = '\f126';
	$dashicons['video/x-ms-wmx'] = '\f126';
	return $dashicons;
}
