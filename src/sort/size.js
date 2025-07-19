/**
 * Sort list of files by their file sizes using int-compare.
 *
 * @param list The list.
 * @param orderby The order direction.
 * @returns {[]}
 */
export function sortBySize( list, orderby ) {
	if( 'descending' === orderby ) {
		list.sort((a, b) => a.filesizeInBytes - b.filesizeInBytes)
	}
	else {
		list.sort((a, b) => b.filesizeInBytes - a.filesizeInBytes)
	}
	return list;
}
