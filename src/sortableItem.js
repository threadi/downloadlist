/**
 * Add individual dependencies.
 */
import { useSortable } from '@dnd-kit/sortable';
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { Icon, dragHandle, edit } from '@wordpress/icons';
import { CSS } from '@dnd-kit/utilities';
import { getActualDate } from "./components";

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
		props.object.setAttributes( { files: props.files, date: getActualDate() } );
	}

	/**
	 * Edit the clicked item.
	 *
	 * @param index
	 */
	let myFrame
	function editListItem(postId) {
		// Create a new media frame
		myFrame = wp.media({
			title: __('Edit file', 'downloadlist'),
			button: {
				text: __('Save', 'downloadlist'),
			},
			multiple: false,
			library: {
				downloadlist_post_id: postId // filter for this postId via ajax_query_attachments_args
			},
		})

		// show only the requested file on open
		myFrame.on( 'open', function() {
			let selection = myFrame.state().get( 'selection' );
			selection.reset( postId ? [ wp.media.attachment( postId ) ] : [] );
			jQuery(".attachments-browser > .media-toolbar, #menu-item-upload, .load-more-count").hide();
		});

		// reload the file list on close
		myFrame.on( 'close', function() {
			props.object.setAttributes( { files: props.files, date: getActualDate() } );
			jQuery(".attachments-browser > .media-toolbar, #menu-item-upload, .load-more-count").show();
		});

		// Finally, open the modal on click
		myFrame.open()
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

	/**
	 * Get or hide file size
	 *
	 * @type {string}
	 */
	let fileSize = '';
	if( props.file.filesizeHumanReadable && !props.object.attributes.hideFileSize ) {
		fileSize = ' (' + props.file.filesizeHumanReadable + ')';
	}

	/**
	 * Get or hide description
	 *
	 * @type {string}
	 */
	let description = props.file.description;
	if( props.object.attributes.hideDescription ) {
		description = '';
	}

	/**
	 * Set class to hide icons
	 */
	let hideIcons = ''
	if( props.object.attributes.hideIcon ) {
		hideIcons = ' hideIcons'
	}

	/**
	 * Set link target depending on setting
	 */
	let linkTarget = props.file.url
	if( props.object.attributes.linkTarget && props.object.attributes.linkTarget === 'attachmentpage' ) {
		linkTarget = props.file.link
	}

	/**
	 * Set title
	 */
	let title = __('Loading ..', 'downloadlist');
	if( props.file.title ) {
		title = props.file.title;
	}

	return (
		<div ref={setNodeRef} style={style} {...attributes} {...listeners} id={`file${props.file.id}`} key={`file${props.file.id}`} className={`editor-styles-wrapper wp-block-downloadlist-list-draggable file_${props.file.type} file_${props.file.subtype}${hideIcons}`}>
			<Button className="downloadlist-list-trash" onClick={() => removeListItem(props.index)} title={__('Remove from list', 'downloadlist')}/>
			<Button className="downloadlist-list-edit" onClick={() => editListItem(props.file.id)} title={__( 'Edit file', 'downloadlist' )}><Icon icon={ edit } /></Button>
			<Button title={__( 'hold to pull', 'downloadlist' )}>{dragHandle}</Button>
			<a href={linkTarget}>{title}</a>{fileSize}{<div dangerouslySetInnerHTML={{ __html: description }}/>}
		</div>
	);
}
