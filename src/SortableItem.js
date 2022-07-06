/**
 * Add individual dependencies.
 */
import {useSortable} from '@dnd-kit/sortable';
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { dragHandle } from '@wordpress/icons';
import {CSS} from '@dnd-kit/utilities';

/**
 * Represents single sortable item in list.
 *
 * @param props
 * @returns {JSX.Element}
 * @constructor
 */
export function SortableItem(props) {
	const {
		attributes,
		listeners,
		setNodeRef,
		transform,
		transition,
	} = useSortable({id: props.file.id});

	/**
	 * Remote this item from the list.
	 *
	 * @param index
	 */
	function removeListItem(index) {
		props.files.splice(index, 1);
		props.object.setAttributes( { files: props.files, date: new Date } );
	}

	/**
	 * Define style which is necessary for drag-animation.
	 *
	 * @type {{transform: string, transition: *}}
	 */
	const style = {
		transform: CSS.Transform.toString(transform),
		transition,
	};

	return (
		<div ref={setNodeRef} style={style} {...attributes} {...listeners} id={`file${props.file.id}`} key={`file${props.file.id}`} className={`editor-styles-wrapper wp-block-downloadlist-list-draggable file_${props.file.type} file_${props.file.subtype}`}>
			<Button className="downloadlist-list-trash" onClick={() => removeListItem(props.index)} title={__('remove from list', 'downloadlist')}/>
			<Button title={__( 'hold to pull', 'downloadlist' )}>{dragHandle}</Button>
			<a href={props.file.url}>{props.file.title}</a> ({props.file.filesizeHumanReadable})<br/>{props.file.description}
		</div>
	);
}
