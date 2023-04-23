/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-block-editor/#useBlockProps
 */
import { useBlockProps } from '@wordpress/block-editor';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';

/**
 * Add individual dependencies.
 */
import {
	MediaUpload,
	MediaUploadCheck,
	InspectorControls,
	BlockControls
} from '@wordpress/block-editor';
import {
	Button,
	PanelBody,
	CheckboxControl,
	SelectControl,
	ToolbarButton
} from '@wordpress/components';
import { plus, sort } from '@wordpress/icons';
import {
	DndContext,
	TouchSensor,
	MouseSensor,
	useSensor,
	useSensors,
	KeyboardSensor
} from "@dnd-kit/core";
import {
	restrictToVerticalAxis,
	restrictToWindowEdges,
} from '@dnd-kit/modifiers';
import {
	arrayMove,
	SortableContext,
	sortableKeyboardCoordinates,
	verticalListSortingStrategy
} from "@dnd-kit/sortable";
import { SortableItem } from "./sortableItem";
import { getActualDate } from "./components";
const { useSelect } = wp.data;

const ALLOWED_MEDIA_TYPES = [ 'application', 'audio', 'video' ];

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#edit
 *
 * @param object
 * @return {WPElement} Element to render.
 */
export default function Edit( object ) {
	let files = ( !object.attributes.files ? [] : object.attributes.files )

	// collect the fileIds as array
	let fileIds = [];
	{files.map((file, index) => {
		fileIds.push(file.id);
	})}

	// let js know our custom endpoint
	let dispatch = wp.data.dispatch;
	dispatch( 'core' ).addEntities( [
		{
			name: 'files',           // route name
			kind: 'downloadlist/v1', // namespace
			baseURL: '/downloadlist/v1/files' // API path without /wp-json
		}
	]);

	// set actual date if it is not present
	if( !object.attributes.date ) {
		object.attributes.date = getActualDate()
	}

	// send request to our custom endpoint to get the data of the files
	let attachments = useSelect( ( select ) => {
		return select('core').getEntityRecords('downloadlist/v1', 'files', {
			post_id: fileIds,
			date: object.attributes.date,
			per_page: fileIds.length
		}, [] ) || null;
	});
	if( attachments ) {
		// loop through the results
		files = [];
		let objectFiles = [];
		attachments.map((newFile) => {
			let objectFilesEntry = { id: newFile.id };
			if( newFile.id < 0 ) {
				let file = object.attributes.files.filter(obj => {
					return parseInt(obj.id) === parseInt(newFile.id)
				});
				newFile = file[0];
				objectFilesEntry = file[0];
			}
			// collect the file-data for @SortableItem
			files.push(newFile)
			// and save the actual list in the block-settings
			objectFiles.push(objectFilesEntry)
		});
		// save a clean file list only with the id-property per file
		// -> case 1: update from version 1.x of this plugin
		// -> case 2: a file is not available anymore
		if( JSON.stringify(objectFiles) !== JSON.stringify(object.attributes.files) ) {
			object.setAttributes( { files: objectFiles } );
		}
	}

	/**
	 * Define sensors for sortable via dnd-kit.
	 */
	const sensors = useSensors(
		useSensor(MouseSensor, {
			// Require the mouse to move by 10 pixels before activating
			activationConstraint: {
				distance: 10
			}
		}),
		useSensor(TouchSensor, {
			// Press delay of 250ms, with tolerance of 5px of movement
			activationConstraint: {
				delay: 250,
				tolerance: 5
			}
		}),
		useSensor(KeyboardSensor, {
			coordinateGetter: sortableKeyboardCoordinates
		})
	);

	/**
	 * Save the new position on drag end
	 *
	 * @param event
	 */
	const handleDragEnd = (event) => {
		const { active, over } = event;
		if (active?.id !== over?.id) {
			let active_index = files.findIndex(function(slide) {
				return slide.id === active.id
			});
			let over_index = files.findIndex(function(slide) {
				return slide.id === over.id
			});
			let _files = arrayMove(files, active_index, over_index);
			object.setAttributes({
				files: _files,
			})
		}
	};

	/**
	 * On change of file size option
	 *
	 * @param newValue
	 * @param object
	 */
	function onChangeHideFileSize( newValue, object ) {
		object.setAttributes({ hideFileSize: newValue });
	}

	/**
	 * On change of description option
	 *
	 * @param newValue
	 * @param object
	 */
	function onChangeHideDescription( newValue, object ) {
		object.setAttributes({ hideDescription: newValue });
	}

	/**
	 * On change of icon option
	 *
	 * @param newValue
	 * @param object
	 */
	function onChangeHideIcon( newValue, object ) {
		object.setAttributes({ hideIcon: newValue });
	}

	/**
	 * On change of link target
	 *
	 * @param newValue
	 * @param object
	 */
	function onChangeLinkTarget( newValue, object ) {
		object.setAttributes({ linkTarget: newValue });
	}

	/**
	 * Sort files in list by their titles (string compare).
	 */
	function sortFilesByTitle() {
		files.sort((a, b) => a.title.localeCompare(b.title))
		object.setAttributes({files: files, date: getActualDate()})
	}

	/**
	 * Sort files in list by their file-sizes (int-compare).
	 */
	function sortFilesByFileSize() {
		files.sort((a, b) => a.filesizeInBytes - b.filesizeInBytes)
		object.setAttributes({files: files, date: getActualDate()})
	}

	/**
	 * Set events in external file list.
	 */
	function setEventsForExternalFileList() {
		// set event to delete single file from list
		jQuery('a.downloadlist-delete-entry').off('click').on('click', function(e) {
			e.preventDefault();
			jQuery(this).parents('li').remove();
		});

		// reset numbering of the files
		let i = 0;
		jQuery('span.downloadlist-file-number').each(function() {
			i = i + 1;
			jQuery(this).html(i)
		});
	}

	/**
	 * Create our own media library window.
	 *
	 * @param value
	 * @param object
	 * @param files
	 */
	function openMediaLibrary( value, object, files ) {
		// secure the default media router config
		const defaultMediaRouterConfig = wp.media.view.MediaFrame.Select.prototype.browseRouter;

		// generate item list for external files
		let htmlListForExternalFiles = getHtmlListForExternalFiles( object );

		// add our own tab to the router for our own media library
		wp.media.view.MediaFrame.Select.prototype.browseRouter = function( routerView ) {
			routerView.set({
				upload: {
					text:     wp.media.view.l10n.uploadFilesTitle,
					priority: 20
				},
				browse: {
					text:     wp.media.view.l10n.mediaLibraryTitle,
					priority: 40
				},
				external_fields: {
					text:     __('external files', 'downloadlist'),
					priority: 60
				}
			});
		};

		/**
		 * Create our own media library window.
		 *
		 * @type {wp.media.view.MediaFrame}
		 */
		let frame = wp.media({
			frame: 'select',
			title: __('Choose files for list', 'downloadlist'),
			button: {
				text: __('Choose this files', 'downloadlist')
			},
			multiple: 'add',
			library: {
				type: ALLOWED_MEDIA_TYPES
			}
		});

		/**
		 * Preselect already chosen files in media library.
		 */
		frame.on('open',function() {
			let selection = frame.state().get('selection');
			object.attributes.files.forEach(function (obj) {
				let attachment = wp.media.attachment(obj.id);
				selection.add(attachment ? [attachment] : []);
			});
		});

		/**
		 * Show our own content if external_fields-tab is active
		 * TODO Formular in Sitzung speichern und wieder hervorholen statt es jedes Mal neu zu erzeugen
		 * TODO sollte auch beim Aufruf des Fensters ausgeführt werden
		 */
		wp.media.frame.on('content:activate:external_fields',function() {
			// add the output
			jQuery('.media-modal-content .media-frame-content').html(htmlListForExternalFiles);

			// add event to add a new file-entry
			jQuery('a.downloadlist-add-external-file').on('click', function(e) {
				e.preventDefault();
				jQuery('.downloadlist-external-list li').last().clone(false).appendTo('.downloadlist-external-list');
				setEventsForExternalFileList();
			});

			// set events to file list
			setEventsForExternalFileList();
		});

		/**
		 * Save selected files to the list.
		 */
		frame.on('select', function() {
			// get the selected files
			const attachments = frame.state().get('selection').map(function (attachment) {
				return attachment.toJSON();
			});

			// get the external files
			const external_files = [];
			let i = 0;
			jQuery('.downloadlist-external-list li').each(function() {
				// TODO Pflichtangaben prüfen
				if( jQuery(this).find("input.downloadlist-title").val().length > 0 ) {
					i = i - 1;
					let entry = {
						'id': i,
						'title': jQuery(this).find('input.downloadlist-title').val(),
						'filename': jQuery(this).find('input.downloadlist-url').val(),
						'description': jQuery(this).find('input.downloadlist-description').val(),
						'url': jQuery(this).find('input.downloadlist-url').val(),
						'link': jQuery(this).find('input.downloadlist-url').val(),
						'filesizeHumanReadable': jQuery(this).find('input.downloadlist-filesize').val(),
						'filesizeInBytes': jQuery(this).find('input.downloadlist-filesize').val()
					};
					external_files.push(entry);
				}
			});

			// merge them
			let results = [...attachments, ...external_files];

			// update the list
			object.setAttributes({files: results, date: getActualDate()})
		});

		/**
		 * Revert the media setting to default if our own media library is closed.
		 */
		frame.on('close', function() {
			// remove our own tab
			wp.media.view.MediaFrame.Select.prototype.browseRouter = defaultMediaRouterConfig;
		});

		// Finally, open the modal on click
		frame.open();
	}

	/**
	 * Create output for our own list of external files.
	 *
	 * @returns {string}
	 */
	function getHtmlListForExternalFiles( object ) {
		// define fields per external file
		let fields = {
			'heading': {
				'type': 'h3',
				'title': __('File #', 'downloadlist')
			},
			'url': {
				'type': 'text',
				'name': 'url',
				'title': __('URL', 'downloadlist'),
				'value': '[URL]'
			},
			'filesize': {
				'type': 'text',
				'name': 'filesize',
				'title': __('filesize in bytes', 'downloadlist'),
				'value': '[FILESIZE]'
			},
			'mimetype': {
				'type': 'select',
				'name': 'mimetype',
				'title': __('filetype', 'downloadlist'),
				'options': [
					'<option value="file">' + __('file', 'downloadlist') + '</option>',
					'<option value="pdf">' + __('PDF', 'downloadlist') + '</option>'
				]
			},
			'title': {
				'type': 'text',
				'name': 'title',
				'title': __('title', 'downloadlist'),
				'value': '[TITLE]'
			},
			'description': {
				'type': 'textarea',
				'name': 'description',
				'title': __('description', 'downloadlist'),
				'value': '[DESC]'
			}
		};

		// create list-item from fields
		let listitem = '<li>';
		for (const [key, value] of Object.entries(fields)) {
			switch( value.type ) {
				case 'h3':
					listitem = listitem + '<h3>' + value.title + '<span class="downloadlist-file-number">[NUMBER]</span> <a href="#" class="downloadlist-delete-entry">' + __('delete entry', 'downloadlist') + '</a></h3>';
					break;
				case 'text':
					listitem = listitem + '<span><label for="' + value.name + '[NUMBER]">' + value.title + '</label><input type="text" id="' + value.name + '[NUMBER]" name="' + value.name + '" value="' + value.value + '" class="downloadlist-' + value.name + '"></span>';
					break;
				case 'select':
					listitem = listitem + '<span><label for="' + value.name + '[NUMBER]">' + value.title + '</label><select id="' + value.name + '[NUMBER]" name="' + value.name + '" class="downloadlist-' + value.name + '">' + value.options + '</select></span>';
					break;
				case 'textarea':
					listitem = listitem + '<span><label for="' + value.name + '[NUMBER]">' + value.title + '</label><textarea id="' + value.name + '[NUMBER]" name="' + value.name + '" class="downloadlist-' + value.name + '">' + value.value + '</textarea></span>';
					break;
			}
		}
		listitem = listitem + '</li>';

		// create html-output
		let html = '<div class="downloadlist-external-wrapper"><h2>' + __('External files for this list', 'downloadlist') + '</h2>';
		html = html + '<ul class="downloadlist-external-list">';
		let i = 0;
		for (const [key, value] of Object.entries(object.attributes.files)) {
			if( value.id < 0 ) {
				i = i + 1;
				// replace number
				let item = listitem;

				// replace title
				item = item.replace('[TITLE]', value.title);

				// replace description
				item = item.replace('[DESC]', value.description);

				// replace url
				item = item.replace('[URL]', value.url);

				// replace filesize
				item = item.replace('[FILESIZE]', value.filesizeInBytes);

				// add to list
				html = html + item.replace(/\[NUMBER\]/g, i + '');
			}
		}

		// add field for new entry
		html = html + listitem.replace(/\[NUMBER\]/g, (i+1) + '').replace('[TITLE]', '').replace('[TITLE]', '').replace('[DESC]', '').replace('[URL]', '').replace('[FILESIZE]', '');

		// return completed string
		return html + '</ul><a href="#" class="downloadlist-add-external-file button media-button button-primary button-large media-button-select">' + __('Add additional external file', 'downloadlist') + '</a></div>';
	}

	/**
	 * Collect return for the edit-function
	 */
	return (
		<div { ...useBlockProps() }>
			{
				<BlockControls>
					<ToolbarButton
						icon={<svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="32" height="32" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24"><path fill="currentColor" d="M12 5h10v2H12m0 12v-2h10v2m-10-8h10v2H12m-3 0v2l-3.33 4H9v2H3v-2l3.33-4H3v-2M7 3H5c-1.1 0-2 .9-2 2v6h2V9h2v2h2V5a2 2 0 0 0-2-2m0 4H5V5h2Z"/></svg>}
						label={__('Sort files by title', 'downloadlist')}
						onClick={ () => sortFilesByTitle() }
					/>
					<ToolbarButton
						icon={<svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="32" height="32" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24"><path fill="currentColor" d="M7 21H3v-2h4v-1H5a2 2 0 0 1-2-2v-1c0-1.1.9-2 2-2h2a2 2 0 0 1 2 2v4c0 1.11-.89 2-2 2m0-6H5v1h2M5 3h2a2 2 0 0 1 2 2v4c0 1.11-.89 2-2 2H5a2 2 0 0 1-2-2V5c0-1.1.9-2 2-2m0 6h2V5H5m7 0h10v2H12m0 12v-2h10v2m-10-8h10v2H12Z"/></svg>}
						label={__('Sort files by filesize', 'downloadlist')}
						onClick={ () => sortFilesByFileSize() }
					/>
				</BlockControls>
			}
			{
				<InspectorControls>
					<PanelBody title={ __( 'Settings', 'downloadlist' ) }>
						<CheckboxControl
							label={__('Hide icon', 'downloadlist')}
							checked={ object.attributes.hideIcon }
							onChange={ value => onChangeHideIcon( value, object ) }
						/>
						<CheckboxControl
							label={__('Hide file size', 'downloadlist')}
							checked={ object.attributes.hideFileSize }
							onChange={ value => onChangeHideFileSize( value, object ) }
						/>
						<CheckboxControl
							label={__('Hide description', 'downloadlist')}
							checked={ object.attributes.hideDescription }
							onChange={ value => onChangeHideDescription( value, object ) }
						/>
						<SelectControl
							label={__('Choose link target', 'downloadlist')}
							value={ object.attributes.linkTarget }
							options={ [
								{ label: __('direct link', 'downloadlist'), value: 'direct' },
								{ label: __('attachment page', 'downloadlist'), value: 'attachmentpage' },
							] }
							onChange={ value => onChangeLinkTarget( value, object ) }
						/>
					</PanelBody>
				</InspectorControls>
			}
			{files &&
				<div>
					<DndContext
						sensors={sensors}
						onDragEnd={handleDragEnd}
						modifiers={[restrictToVerticalAxis, restrictToWindowEdges]}
					>
						<SortableContext items={ files } strategy={verticalListSortingStrategy}>
							{files.map((file, index) => (
								<SortableItem key={index} file={file} index={index} files={files} object={object} />
							))}
						</SortableContext>
					</DndContext>
				</div>
			}
			<MediaUploadCheck>
				<MediaUpload
					onSelect={ ( newFiles ) => {
						{newFiles.map((newFile) => {
							let doNotAdd = false;
							{files.map((file, index) => {
								if( file.id === newFile.id ) {
									// do not add
									doNotAdd = true;
								}
							})};
							if( !doNotAdd ) {
								files.push({
									id: newFile.id
								})
							}
						})}
						object.setAttributes({files: files, date: getActualDate()})
					}
					}
					multiple={true}
					allowedTypes={ ALLOWED_MEDIA_TYPES }
					value={ object.attributes.files }
					render={ ( { open } ) => (
						<Button isPrimary onClick={ value => openMediaLibrary(value, object, files) }>{plus} { __( 'Add files to list', 'downloadlist' ) }</Button>
					) }
				/>
			</MediaUploadCheck>
		</div>
	);
}

