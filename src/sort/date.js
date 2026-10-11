/**
 * Sort list of files by their date using int-compare.
 *
 * @param list The list.
 * @param orderby The order direction.
 * @returns {[]}
 */
export function sortByDate( list, orderby ) {
	if( 'descending' === orderby ) {
		list.sort((a, b) => (a.date || 0) - (b.date || 0))
	}
	else {
		list.sort((a, b) => (b.date || 0) - (a.date || 0))
	}
	return list;
}
