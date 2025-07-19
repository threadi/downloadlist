/**
 * Sort list of files by their titles using string-compare.
 *
 * @param list The list.
 * @param orderby The order direction.
 * @returns {[]}
 */
export function sortByTitle( list, orderby ) {
	if( 'descending' === orderby ) {
		list.sort((a, b) => a.title.localeCompare(b.title, undefined, {numeric: true, sensitivity: 'base'}))
	}
	else {
		list.sort((a, b) => b.title.localeCompare(a.title, undefined, {numeric: true, sensitivity: 'base'}))
	}
	return list;
}
