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
			// collect the file-data for @SortableItem
			files.push(newFile)
			// and save the actual list in the block-settings
			objectFiles.push({ id: newFile.id })
		});
		// save a clean file list only with the id-property per file
		// -> case 1: update from version 1.x from this plugin
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
	 * Sort files in list by their filesizes (int-compare).
	 */
	function sortFilesByFileSize() {
		files.sort((a, b) => a.filesizeInBytes - b.filesizeInBytes)
		object.setAttributes({files: files, date: getActualDate()})
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
						<Button isPrimary onClick={ open }>{plus} { __( 'Add files to list', 'downloadlist' ) }</Button>
					) }
				/>
			</MediaUploadCheck>
		</div>
	);
}

