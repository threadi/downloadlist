/**
 * Add individual dependencies.
 */
import { useSortable } from '@dnd-kit/sortable';
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { Icon, dragHandle, edit, trash } from '@wordpress/icons';
import { CSS } from '@dnd-kit/utilities';
import { getActualDate } from "./components";
import { gmdateI18n } from '@wordpress/date';

/**
 * Represents single sortable item in list.
 *
 * @param props
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
			title: __('Edit file', 'download-list-block-with-icons'),
			button: {
				text: __('Save', 'download-list-block-with-icons'),
			},
			multiple: false,
			library: {
				downloadlist_post_id: postId, // filter for this postId via ajax_query_attachments_args
				downloadlist_nonce: downloadlistJsVars.downloadlist_nonce
			},
		})

		// show only the requested file on open
		myFrame.on( 'open', function() {
			let selection = myFrame.state().get( 'selection' );
			selection.reset( postId ? [ wp.media.attachment( postId ) ] : [] );
			jQuery(".attachments-browser > .media-toolbar, #menu-item-upload, .load-more-count").remove();
		});

		// reload the file list on close
		myFrame.on( 'close', function() {
			props.object.setAttributes( { files: props.files, date: getActualDate() } );
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
	 * Get or hide file size.
	 *
	 * @type {string}
	 */
	let fileSize = '';
	if( props.file.filesizeHumanReadable && !props.object.attributes.hideFileSize ) {
		fileSize = ' (' + props.file.filesizeHumanReadable + ')';
	}

	/**
	 * Get or hide description.
	 *
	 * @type {string}
	 */
	let description = props.file.description;
	if( props.object.attributes.hideDescription ) {
		description = '';
	}

	/**
	 * Set class to hide icons.
	 */
	let hideIcon = ''
	if( props.object.attributes.hideIcon ) {
		hideIcon = ' hide-icon'
	}

	/**
	 * Get or hide file dates
	 *
	 * @type {string}
	 */
	let file_date = '';
	let settings = wp.data.select( 'core' ).getSite();
	if( settings && props.object.attributes.showFileDates ) {
		file_date = gmdateI18n( settings.date_format + ' ' + settings.time_format, props.file.date );
	}

	/**
	 * Set link target depending on setting.
	 */
	let linkTarget = props.file.url
	if( props.object.attributes.linkTarget && props.object.attributes.linkTarget === 'attachmentpage' ) {
		linkTarget = props.file.link
	}

	/**
	 * Set title
	 */
	let title = __('Loading ..', 'download-list-block-with-icons');
	if( props.file.title ) {
		title = props.file.title;
	}
	else if( props.file.filename ) {
		title = props.file.filename;
	}

	/**
	 * Add download-button
	 */
	let downloadButton = ''
	if( props.object.attributes.showDownloadButton ) {
		downloadButton = '<a href="' + linkTarget + '" class="download-button button button-secondary">' + __('Download', 'download-list-block-with-icons') + '</a>'
	}

	return (
		<div ref={setNodeRef} style={style} {...attributes} {...listeners} id={`file${props.file.id}`} key={`file${props.file.id}`} className={`wp-block-downloadlist-list-draggable file_${props.file.type} file_${props.file.subtype}${hideIcon}`}>
			{0 === props.object.attributes.list && <Button className="downloadlist-list-trash" onClick={() => removeListItem(props.index)} title={__('Remove from list', 'download-list-block-with-icons')}><Icon icon={ trash } /></Button>}
			<Button className="downloadlist-list-edit" onClick={() => editListItem(props.file.id)} title={__( 'Edit file', 'download-list-block-with-icons' )}><Icon icon={ edit } /></Button>
			<Button title={__( 'hold to pull', 'download-list-block-with-icons' )}>{dragHandle}</Button>
			{!props.object.attributes.hideLink && <a href={linkTarget}>{title}</a>}{props.object.attributes.hideLink && title}{fileSize}{<span dangerouslySetInnerHTML={{ __html: downloadButton }}/>}{<div dangerouslySetInnerHTML={{ __html: description }}/>}{<div dangerouslySetInnerHTML={{ __html: file_date }}/>}
		</div>
	);
}
