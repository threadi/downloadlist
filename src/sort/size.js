/**
 * Sort list of files by their file sizes using int-compare.
 *
 * @param list The list.
 * @param orderby The order direction.
 * @returns {[]}
 */
export function sortBySize( list, orderby ) {
	if( 'descending' === orderby ) {
		list.sort((a, b) => (a.filesizeInBytes || 0) - (b.filesizeInBytes || 0))
	}
	else {
		list.sort((a, b) => (b.filesizeInBytes || 0) - (a.filesizeInBytes || 0))
	}
	return list;
}
