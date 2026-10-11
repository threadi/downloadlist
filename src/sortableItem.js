/**
 * Add individual dependencies.
 */
import { useSortable } from '@dnd-kit/sortable';
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { Icon, dragHandle, pencil, trash } from '@wordpress/icons';
import { CSS } from '@dnd-kit/utilities';
import { getActualDate } from "./components";
import { gmdateI18n } from '@wordpress/date';
import { useRef, useEffect } from '@wordpress/element';
import { safeHTML } from '@wordpress/dom';

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
	 * Remove this item from the list.
	 *
	 * Only the IDs of the files are saved in the block.
	 */
	function removeListItem() {
		const savedFiles = Array.isArray( props.object.attributes.files ) ? props.object.attributes.files : [];
		const newFiles = savedFiles
			.filter( ( file ) => file && file.id !== props.file.id )
			.map( ( file ) => ( { id: file.id } ) );
		props.object.setAttributes( { files: newFiles, date: getActualDate() } );
	}

	/**
	 * The media frame to edit this file. It is created only once per item.
	 */
	const frameRef = useRef( null );

	// remove the media frame if the item is removed.
	useEffect( () => {
		return () => {
			if ( frameRef.current ) {
				frameRef.current.off();
				frameRef.current.remove();
				frameRef.current = null;
			}
		};
	}, [] );

	/**
	 * Edit the clicked item.
	 *
	 * @param postId
	 */
	function editListItem(postId) {
		// use the existing frame.
		if ( frameRef.current ) {
			frameRef.current.open();
			return;
		}

		// Create a new media frame
		const myFrame = wp.media({
			title: __('Edit file', 'download-list-block-with-icons'),
			button: {
				text: __('Save', 'download-list-block-with-icons'),
			},
			multiple: false,
			library: {
				downloadlist_post_id: postId, // filter for this postId via ajax_query_attachments_args
				downloadlist_nonce: window.downloadlistJsVars.downloadlist_nonce
			},
		})

		// show only the requested file on open
		myFrame.on( 'open', function() {
			let selection = myFrame.state().get( 'selection' );
			selection.reset( postId ? [ wp.media.attachment( postId ) ] : [] );
			// hide elements only in this frame, not in other media frames.
			myFrame.$el.find(".attachments-browser > .media-toolbar, #menu-item-upload, .load-more-count").remove();
		});

		// reload the data of the files on close.
		myFrame.on( 'close', function() {
			props.object.setAttributes( { date: getActualDate() } );
		});

		// Finally, open the modal on click
		frameRef.current = myFrame;
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
	 * Get or hide file dates.
	 *
	 * @type {string}
	 */
	let file_date = '';
	const dateFormat = window.downloadlistJsVars && window.downloadlistJsVars.date_format ? window.downloadlistJsVars.date_format : 'Y-m-d';
	const timeFormat = window.downloadlistJsVars && window.downloadlistJsVars.time_format ? window.downloadlistJsVars.time_format : 'H:i';
	if( props.file.date && props.object.attributes.showFileDates ) {
		file_date = gmdateI18n( dateFormat + ' ' + timeFormat, props.file.date );
	}

	/**
	 * Get or hide file format label.
	 *
	 * @type {string}
	 */
	let file_format_label = '';
	if( props.object.attributes.showFileFormatLabel && props.file.downloadlist_mime_label ) {
		file_format_label = props.file.downloadlist_mime_label;
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
	 * Get the preview image, if enabled and if the file has one (e.g. images and PDF files with generated preview).
	 */
	let preview = null;
	if ( props.showPreview && props.file.downloadlist_preview ) {
		// the data comes from the server, so the editor shows the same preview as the frontend.
		const previewWidth = Math.min( 600, Math.max( 16, parseInt( props.object.attributes.previewWidth, 10 ) || 48 ) );
		const sizes = props.file.downloadlist_preview;
		const size = ( previewWidth > 150 ? sizes.medium : sizes.thumbnail ) || sizes.thumbnail || sizes.medium;
		if ( size && size.url && size.width && size.height ) {
			preview = <img src={ size.url } width={ previewWidth } height={ Math.round( size.height * previewWidth / size.width ) } alt="" className="downloadlist-preview" />;
		}
	}

	/**
	 * Get the description as safe HTML (it is already sanitized on the server, this is an additional protection).
	 */
	const descriptionHtml = description ? safeHTML( String( description ) ) : '';

	return (
		<div ref={setNodeRef} style={style} {...attributes} {...listeners} id={`file${props.file.id}`} className={`wp-block-downloadlist-list-draggable file_${props.file.type} file_${props.file.subtype}${hideIcon}${preview ? ' has-preview' : ''}`}>
			{0 === props.object.attributes.list && <Button className="downloadlist-list-trash" onClick={() => removeListItem()} title={__('Remove from list', 'download-list-block-with-icons')}><Icon icon={ trash } /></Button>}
			<Button className="downloadlist-list-edit" onClick={() => editListItem(props.file.id)} title={__( 'Edit file', 'download-list-block-with-icons' )}><Icon icon={ pencil } /></Button>
			<Button title={__( 'hold to pull', 'download-list-block-with-icons' )}>{dragHandle}</Button>
			{preview}{!props.object.attributes.hideLink && <a href={linkTarget}>{title}</a>}{props.object.attributes.hideLink && title}{fileSize}{props.object.attributes.showDownloadButton && <span><a href={linkTarget} className="download-button button button-secondary">{__('Download', 'download-list-block-with-icons')}</a></span>}{<div dangerouslySetInnerHTML={{ __html: descriptionHtml }}/>}{<div>{file_format_label}</div>}{<div>{file_date}</div>}
		</div>
	);
}
