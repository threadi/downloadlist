/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/
 */
import { _x } from '@wordpress/i18n';

/**
 * Return the actual date as timestamp.
 *
 * @returns {number}
 */
export function getActualDate() {
	return new Date().getTime();
}

/**
 * Format bytes as human-readable text.
 *
 * @param bytes Number of bytes.
 * @param si True to use metric (SI) units, aka powers of 1000. False to use
 *           binary (IEC), aka powers of 1024.
 * @param dp Number of decimal places to display.
 *
 * @source https://stackoverflow.com/questions/10420352/converting-file-size-in-bytes-to-human-readable-string
 * @return Formatted string.
 */
export function humanFileSize(bytes, si= false, dp= 1) {
	const thresh = si ? 1000 : 1024;

	if (Math.abs(bytes) < thresh) {
		return bytes + ' B';
	}

	const units = si
		? [
			_x('kB', 'unit symbols', 'downloadlist'),
			_x('MB', 'unit symbols', 'downloadlist'),
			_x('GB', 'unit symbols', 'downloadlist'),
			_x('TB', 'unit symbols', 'downloadlist'),
			_x('PB', 'unit symbols', 'downloadlist'),
			_x('EB', 'unit symbols', 'downloadlist'),
			_x('ZB', 'unit symbols', 'downloadlist'),
			_x('YB', 'unit symbols', 'downloadlist')
		]
		: [
			_x('KiB', 'unit symbols', 'downloadlist'),
			_x('MiB', 'unit symbols', 'downloadlist'),
			_x('GiB', 'unit symbols', 'downloadlist'),
			_x('TiB', 'unit symbols', 'downloadlist'),
			_x('PiB', 'unit symbols', 'downloadlist'),
			_x('EiB', 'unit symbols', 'downloadlist'),
			_x('ZiB', 'unit symbols', 'downloadlist'),
			_x('YiB', 'unit symbols', 'downloadlist')
		];
	let u = -1;
	const r = 10**dp;

	do {
		bytes /= thresh;
		++u;
	} while (Math.round(Math.abs(bytes) * r) / r >= thresh && u < units.length - 1);


	return bytes.toFixed(dp) + ' ' + units[u];
}
