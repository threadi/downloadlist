/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/
 */
import { __, _n, sprintf } from '@wordpress/i18n';

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
	BlockControls,
	useBlockProps,
	__experimentalColorGradientSettingsDropdown as ColorGradientSettingsDropdown,
	__experimentalUseMultipleOriginColorsAndGradients as useMultipleOriginColorsAndGradients
} from '@wordpress/block-editor';
import { getBlockType } from '@wordpress/blocks';
import {
	Button,
	PanelBody,
	CheckboxControl,
	SelectControl,
	ToolbarButton,
	ExternalLink,
	TextControl,
	Dropdown,
	MenuGroup,
	MenuItemsChoice,
	MenuItem,
	RangeControl,
	Notice,
	__experimentalToolsPanel as ToolsPanel,
	__experimentalToolsPanelItem as ToolsPanelItem
} from '@wordpress/components';
import { plus } from '@wordpress/icons';
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
import { useSelect, dispatch } from '@wordpress/data';
import { useEffect } from '@wordpress/element';
import {sortByTitle} from "./sort/title";
import {sortBySize} from "./sort/size";
import {sortByDate} from "./sort/date";

/**
 * Prepare our custom endpoints.
 */
wp.domReady(() => {
	dispatch('core').addEntities([
		{
			name: 'files',
			label: __('Files', 'download-list-block-with-icons'),
			kind: 'downloadlist/v1',
			baseURL: '/downloadlist/v1/files'
		},
		{
			name: 'filetypes',
			kind: 'downloadlist/v1',
			baseURL: '/downloadlist/v1/filetypes'
		}
	])
});

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
	const isPreview = !! object.attributes.preview;

	// get the files saved in the block (only their IDs are used).
	const attributeFiles = Array.isArray( object.attributes.files ) ? object.attributes.files.filter( ( file ) => file && file.id > 0 ) : [];

	// collect the fileIds as array.
	let fileIds = attributeFiles.map( ( file ) => file.id );

	/**
	 * Revert preview-setting if block is selected.
	 */
	useEffect( () => {
		if ( object.isSelected && object.attributes.preview ) {
			object.setAttributes( { preview: false } );
		}
	}, [ object.isSelected ] );

	// if we use a download list, get its files (hooks are always called in the same order, also in preview).
	const list_files = useSelect( ( select ) => {
		if ( isPreview || ! object.attributes.list ) {
			return [];
		}
		return select( 'core' ).getEntityRecords( 'postType', 'attachment', {
			per_page: -1,
			dl_icon_lists: [ object.attributes.list ],
			_fields: 'id'
		} );
	}, [ isPreview, object.attributes.list ] ) || [];
	if ( list_files.length > 0 && 'manual' !== object.attributes.order ) {
		fileIds = list_files.map( ( file ) => file.id );
	}

	/**
	 * Get the data of each selected file from our endpoint, but not in preview-mode.
	 *
	 * The attribute "date" is not saved, it is only used to reload the data after changes.
	 */
	const fileIdsKey = fileIds.join( ',' );
	const { attachments, attachmentsFailed } = useSelect( ( select ) => {
		if ( isPreview || 0 === fileIds.length ) {
			return { attachments: [], attachmentsFailed: false };
		}
		const query = {
			post_ids: fileIds,
			date: object.attributes.date || 0,
			per_page: fileIds.length
		};
		const store = select( 'core' );
		return {
			attachments: store.getEntityRecords( 'downloadlist/v1', 'files', query ),
			attachmentsFailed: store.hasResolutionFailed ? store.hasResolutionFailed( 'getEntityRecords', [ 'downloadlist/v1', 'files', query ] ) : false
		};
	}, [ isPreview, fileIdsKey, object.attributes.date ] );

	// use the loaded data of the files, or only their IDs while loading.
	let files = attributeFiles;
	const filesLoaded = Array.isArray( attachments );
	if ( filesLoaded ) {
		files = [ ...attachments ];

		/**
		 * Sort the files list if download list is used.
		 */
		if( object.attributes.list > 0 && object.attributes.order && object.attributes.orderby ) {
			if ( 'title' === object.attributes.order ) {
				files = sortByTitle( files, object.attributes.orderby );
			}
			if ( 'size' === object.attributes.order ) {
				files = sortBySize( files, object.attributes.orderby );
			}
			if ( 'date' === object.attributes.order ) {
				files = sortByDate( files, object.attributes.orderby );
			}
		}
	}

	// get all iconsets.
	const iconsets_array = useSelect( ( select ) => {
		if ( isPreview ) {
			return [];
		}
		// "view" context: editors without the capability to edit the terms can read them too.
		return select( 'core' ).getEntityRecords( 'taxonomy', 'dl_icon_set', { per_page: -1, hide_empty: true, context: 'view' } );
	}, [ isPreview ] ) || [];

	// Options in SelectControl expected format [{label: ..., value: ...}]
	const iconsets = iconsets_array.map(
		// Format the options for display in the <SelectControl/>
		( icon ) => ( {
			label: icon.name,
			value: icon.slug
		} )
	);

	// get the iconset to use: the chosen one, if it exists, or the default iconset.
	// The attribute is not changed here, the server uses the default iconset in the same way.
	let iconset = object.attributes.iconset || '';
	if ( iconsets_array.length > 0 ) {
		if ( iconset.length > 0 && ! iconsets_array.some( ( item ) => item.slug === iconset ) ) {
			iconset = '';
		}
		if ( 0 === iconset.length ) {
			const default_iconset = iconsets_array.find( ( item ) => item.meta && 1 === item.meta.default );
			if ( default_iconset ) {
				iconset = default_iconset.slug;
			}
		}
	}

	// show previews if enabled, or if the list used the iconset of the former plugin "File-Preview" which is not available anymore.
	const showPreview = !! object.attributes.showPreview || ( 'preview' === object.attributes.iconset && iconsets_array.length > 0 && ! iconsets_array.some( ( item ) => 'preview' === item.slug ) );

	// count the PDF files without generated preview image (they show their icon).
	// count the files without preview image by their reason (they show their icon).
	const hasPreview = ( file ) => file.downloadlist_preview && ( file.downloadlist_preview.thumbnail || file.downloadlist_preview.medium );
	const countWithoutPreview = ( types ) => showPreview && filesLoaded ? files.filter( ( file ) => types.includes( file.downloadlist_preview_type ) && ! hasPreview( file ) ).length : 0;
	const pdfsWithoutPreview = countWithoutPreview( [ 'pdf' ] );
	const mediaWithoutCover = countWithoutPreview( [ 'audio', 'video' ] );
	const filesWithoutPreviewType = countWithoutPreview( [ 'none' ] );
	// wp_localize_script() transfers true as "1" and false as "".
	const pdfPreviewSupported = ! window.downloadlistJsVars || '1' === String( window.downloadlistJsVars.pdf_preview );

	// collect the hints about files without preview image.
	const previewHints = [];
	if ( pdfsWithoutPreview > 0 ) {
		previewHints.push(
			sprintf(
				/* translators: %d: number of PDF files. */
				_n( '%d PDF file has no preview image and is shown with its icon.', '%d PDF files have no preview image and are shown with their icon.', pdfsWithoutPreview, 'download-list-block-with-icons' ),
				pdfsWithoutPreview
			) + ' ' + ( pdfPreviewSupported ? __( 'They were probably uploaded before this server could generate previews. Regenerate their thumbnails, e.g. with the plugin "Regenerate Thumbnails" or with "wp media regenerate".', 'download-list-block-with-icons' ) : __( 'This server cannot generate preview images of PDF files. Ask your hosting for Imagick with Ghostscript and a policy which allows PDF files.', 'download-list-block-with-icons' ) )
		);
	}
	if ( mediaWithoutCover > 0 ) {
		previewHints.push(
			sprintf(
				/* translators: %d: number of audio and video files. */
				_n( '%d audio or video file has no cover image and is shown with its icon.', '%d audio or video files have no cover image and are shown with their icon.', mediaWithoutCover, 'download-list-block-with-icons' ),
				mediaWithoutCover
			) + ' ' + __( 'WordPress takes the cover from MP3 files during the upload. You can also set it as featured image when editing the file in the media library.', 'download-list-block-with-icons' )
		);
	}
	if ( filesWithoutPreviewType > 0 ) {
		previewHints.push(
			sprintf(
				/* translators: %d: number of files. */
				_n( '%d file has a file type without preview image and is shown with its icon.', '%d files have a file type without preview image and are shown with their icon.', filesWithoutPreviewType, 'download-list-block-with-icons' ),
				filesWithoutPreviewType
			)
		);
	}

	// retrieve all possible file types from our custom endpoint.
	const allowed_file_types_server_result = useSelect( ( select ) => {
		if ( isPreview || ! iconset ) {
			return [];
		}
		return select( 'core' ).getEntityRecords( 'downloadlist/v1', 'filetypes', {
			per_page: -1,
			iconset: iconset
		} );
	}, [ isPreview, iconset ] ) || [];

	const allowed_file_types = allowed_file_types_server_result.map(
		( filetype ) => filetype.value
	);

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

	// get the download lists.
	const download_lists = useSelect( ( select ) => {
		return select( 'core' ).getEntityRecords( 'taxonomy', 'dl_icon_lists', { per_page: -1, context: 'view' } );
	}, [] );

	// generate download list for options.
	const lists_to_use = Array.isArray( download_lists ) ? download_lists.map( ( list ) => ( {
		'label': list.name,
		'value': list.id,
	} ) ) : [];

	/**
	 * Return only the IDs of the given files, as only they are saved in the block.
	 *
	 * @param list
	 * @returns {{id: *}[]}
	 */
	const getFileIds = ( list ) => list.map( ( file ) => ( { id: file.id } ) );

	/**
	 * Save the new position on drag end
	 *
	 * @param event
	 */
	const handleDragEnd = (event) => {
		const { active, over } = event;

		// bail if the item has been dropped outside the list or on its own position.
		if ( ! active || ! over || active.id === over.id ) {
			return;
		}

		const active_index = files.findIndex( ( file ) => file.id === active.id );
		const over_index = files.findIndex( ( file ) => file.id === over.id );

		// bail if one of the positions could not be found.
		if ( -1 === active_index || -1 === over_index ) {
			return;
		}

		object.setAttributes({
			files: getFileIds( arrayMove( files, active_index, over_index ) ),
			order: 'manual'
		})
	};

	/**
	 * On change of list option
	 *
	 * @param newValue
	 */
	function onChangeList( newValue ) {
		object.setAttributes({ files: [], list: newValue, date: getActualDate() });
	}

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
	 * On change of description option.
	 *
	 * @param newValue
	 * @param object
	 */
	function onChangeHideDescription( newValue, object ) {
		object.setAttributes({ hideDescription: newValue });
	}

	/**
	 * On change of show file dates option.
	 *
	 * @param newValue
	 * @param object
	 */
	function onChangeShowFileDates( newValue, object ) {
		object.setAttributes({ showFileDates: newValue });
	}

	/**
	 * On change of show file format label.
	 *
	 * @param newValue
	 * @param object
	 */
	function onChangeShowFileFormatLabel( newValue, object ) {
		object.setAttributes({ showFileFormatLabel: newValue });
	}

	/**
	 * On change of icon option.
	 *
	 * @param newValue
	 * @param object
	 */
	function onChangeHideIcon( newValue, object ) {
		object.setAttributes({ hideIcon: newValue });
	}

	/**
	 * On change of link option.
	 *
	 * @param newValue
	 * @param object
	 */
	function onChangeHideLink( newValue, object ) {
		object.setAttributes({ hideLink: newValue });
	}

	/**
	 * On change of icon set option.
	 *
	 * @param newValue
	 * @param object
	 */
	function onChangeIconSet( newValue, object ) {
		object.setAttributes({ iconset: newValue });
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
	 * On change of link browser target
	 *
	 * @param newValue
	 * @param object
	 */
	function onChangeLinkBrowserTarget( newValue, object ) {
		object.setAttributes({ linkBrowserTarget: newValue });
	}

	/**
	 * On change of link browser target name.
	 *
	 * @param newValue
	 * @param object
	 */
	function onChangeLinkBrowserTargetName( newValue, object ) {
		object.setAttributes({ linkBrowserTargetName: newValue });
	}

	/**
	 * On change of robots.
	 *
	 * @param newValue
	 * @param object
	 */
	function onChangeLinkRobots( newValue, object ) {
		object.setAttributes({ robots: newValue });
	}

	/**
	 * On change of do not force download
	 *
	 * @param newValue
	 * @param object
	 */
	function onChangeDoNotForceDownload( newValue, object ) {
		object.setAttributes({ doNotForceDownload: newValue });
	}

	/**
	 * On change of do not force download
	 *
	 * @param newValue
	 * @param object
	 */
	function onChangeShowDownloadButton( newValue, object ) {
		object.setAttributes({ showDownloadButton: newValue });
	}

	/**
	 * On change of download link target.
	 *
	 * @param newValue
	 * @param object
	 */
	function onChangeDownloadLinkTarget( newValue, object ) {
		object.setAttributes({ downloadLinkTarget: newValue });
	}

	/**
	 * On change of download link target name.
	 *
	 * @param newValue
	 * @param object
	 */
	function onChangeDownloadLinkTargetName( newValue, object ) {
		object.setAttributes({ downloadLinkTargetName: newValue });
	}

	/**
	 * Sort files in list by their titles (with string compare).
	 */
	function sortFilesByTitle() {
		let orderby = getSortDirectionByTitle(files);
		object.setAttributes({ order: 'title', orderby: orderby, files: getFileIds( sortByTitle( [ ...files ], orderby ) ), date: getActualDate() })
	}

	/**
	 * Check alphabetical sorting of given files array.
	 *
	 * @param files
	 * @returns {string}
	 */
	function getSortDirectionByTitle(files) {
		const c = [];
		for (let i = 1; i < files.length; i++) {
			c.push( String( files[i - 1].title || '' ).localeCompare( String( files[i].title || '' ), undefined, {numeric: true, sensitivity: 'base'} ) );
		}

		if (c.every((n) => n <= 0)) return 'ascending';
		if (c.every((n) => n >= 0)) return 'descending';

		return 'unsorted';
	}

	/**
	 * Sort files in list by their sizes (with int-compare).
	 */
	function sortFilesByFileSize() {
		let orderby = getSortDirectionBySize(files);
		object.setAttributes({ order: 'size', orderby: orderby, files: getFileIds( sortBySize( [ ...files ], orderby ) ), date: getActualDate() })
	}

	/**
	 * Check sort of files by its sizes.
	 *
	 * @returns {string}
	 * @param file_array
	 */
	function getSortDirectionBySize(file_array) {
		const c = [];
		for (let i = 1; i < file_array.length; i++) {
			c.push( ( file_array[i - 1].filesizeInBytes || 0 ) - ( file_array[i].filesizeInBytes || 0 ) );
		}

		if (c.every((n) => n <= 0)) return 'ascending';
		if (c.every((n) => n >= 0)) return 'descending';

		return 'unsorted';
	}

	/**
	 * Sort files in list by their dates (with int-compare).
	 */
	function sortFilesByDate() {
		let orderby = getSortDirectionByDate(files);
		object.setAttributes({ order: 'date', orderby: orderby, files: getFileIds( sortByDate( [ ...files ], orderby ) ), date: getActualDate() })
	}

	/**
	 * Check sort of files by its dates.
	 *
	 * @returns {string}
	 * @param file_array
	 */
	function getSortDirectionByDate(file_array) {
		const c = [];
		for (let i = 1; i < file_array.length; i++) {
			c.push( ( file_array[i - 1].date || 0 ) - ( file_array[i].date || 0 ) );
		}

		if (c.every((n) => n <= 0)) return 'ascending';
		if (c.every((n) => n >= 0)) return 'descending';

		return 'unsorted';
	}

	/**
	 * Add given list of files to our list of files in this block.
	 *
	 * @param newFiles
	 */
	function addFiles( newFiles ) {
		const list = getFileIds( attributeFiles );
		newFiles.forEach( ( newFile ) => {
			if ( newFile && newFile.id > 0 && ! list.some( ( file ) => file.id === newFile.id ) ) {
				list.push( { id: newFile.id } );
			}
		} );
		object.setAttributes( { files: list, date: getActualDate() } );
	}

	/**
	 * Add class name for used iconset.
	 */
	const mediaFrame = getMediaFrame( object.attributes, showPreview );
	const colorGradientSettings = useMultipleOriginColorsAndGradients();
	const blockProps = useBlockProps( {
		className: 'iconset-' + iconset + mediaFrame.className,
		style: mediaFrame.style,
	} );

	// sorting is only possible if the data of the files are loaded.
	const sortDisabled = ! filesLoaded || files.length <= 1;

	/**
	 * Collect return for the edit-function
	 */
	return (
		<div { ...blockProps }>
			{
				<BlockControls>
					{allowed_file_types.length > 0 &&
						<MediaUploadCheck>
							<MediaUpload
								onSelect={ ( newFiles ) => addFiles( newFiles ) }
								multiple={ true }
								allowedTypes={ allowed_file_types }
								value={ object.attributes.files }
								render={ ( { open } ) => (
									<ToolbarButton disabled={ object.attributes.list > 0 } className="has-text" onClick={ open } icon={plus} text={ __( 'Add files', 'download-list-block-with-icons' ) } size="small"></ToolbarButton>
								) }
							/>
						</MediaUploadCheck>
					}
					{Array.isArray( download_lists ) &&
						<Dropdown
							renderToggle={ ( { isOpen, onToggle } ) => (
								<ToolbarButton className="has-text" onClick={ onToggle } icon={plus} text={ object.attributes.list > 0 ? __( 'Using a download list', 'download-list-block-with-icons' ) : __( 'Choose a download list', 'download-list-block-with-icons' ) } size="small"></ToolbarButton>
							) }
							renderContent={ () => <div><MenuGroup label={__('Choose download list', 'download-list-block-with-icons')}>
									<MenuItemsChoice
										choices={ lists_to_use }
										onSelect={ ( value ) => onChangeList( value ) }
										value={ object.attributes.list }>
									</MenuItemsChoice>
								</MenuGroup>
								<MenuGroup label={__('Options', 'download-list-block-with-icons')}>
									<MenuItem
										disabled={ 0 === object.attributes.list }
										onClick={ () => onChangeList( 0 ) }
									>
										{__('Deselect download list', 'download-list-block-with-icons')}
									</MenuItem>
									<MenuItem
										onClick={ () => window.location.href=window.downloadlist_config.list_url }
									>
										{__('Manage download lists', 'download-list-block-with-icons')}
									</MenuItem>
								</MenuGroup>
							</div>
							}
						/>
					}
					<ToolbarButton
						icon={<svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="32" height="32" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24"><path fill="currentColor" d="M12 5h10v2H12m0 12v-2h10v2m-10-8h10v2H12m-3 0v2l-3.33 4H9v2H3v-2l3.33-4H3v-2M7 3H5c-1.1 0-2 .9-2 2v6h2V9h2v2h2V5a2 2 0 0 0-2-2m0 4H5V5h2Z"/></svg>}
						label={__('Sort files by title', 'download-list-block-with-icons')}
						disabled={ sortDisabled }
						onClick={ () => sortFilesByTitle() }
					/>
					<ToolbarButton
						icon={<svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="32" height="32" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24"><path fill="currentColor" d="M7 21H3v-2h4v-1H5a2 2 0 0 1-2-2v-1c0-1.1.9-2 2-2h2a2 2 0 0 1 2 2v4c0 1.11-.89 2-2 2m0-6H5v1h2M5 3h2a2 2 0 0 1 2 2v4c0 1.11-.89 2-2 2H5a2 2 0 0 1-2-2V5c0-1.1.9-2 2-2m0 6h2V5H5m7 0h10v2H12m0 12v-2h10v2m-10-8h10v2H12Z"/></svg>}
						label={__('Sort files by filesize', 'download-list-block-with-icons')}
						disabled={ sortDisabled }
						onClick={ () => sortFilesByFileSize() }
					/>
					<ToolbarButton
						icon={<svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="32" height="32" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24"><path fill="currentColor" d="M20 17h3l-4 4l-4-4h3V3h2zM8 5c-3.86 0-7 3.13-7 7s3.13 7 7 7c3.86 0 7-3.13 7-7s-3.13-7-7-7m0 2.15c2.67 0 4.85 2.17 4.85 4.85c0 2.68-2.17 4.85-4.85 4.85c-2.68 0-4.85-2.17-4.85-4.85c0-2.68 2.17-4.85 4.85-4.85M7 9v3.69l3.19 1.84l.75-1.3l-2.44-1.41V9"/></svg>}
						label={__('Sort files by date', 'download-list-block-with-icons')}
						disabled={ sortDisabled }
						onClick={ () => sortFilesByDate() }
					/>
				</BlockControls>
			}
			{
				<InspectorControls>
					<PanelBody title={ __( 'Icons', 'download-list-block-with-icons' ) } initialOpen={ true }>
						<CheckboxControl
							label={__('Hide icons', 'download-list-block-with-icons')}
							checked={ object.attributes.hideIcon }
							onChange={ value => onChangeHideIcon( value, object ) }
							__nextHasNoMarginBottom
						/>
						{false === object.attributes.hideIcon &&
							<SelectControl
								label={__('Choose an iconset', 'download-list-block-with-icons')}
								options={ iconsets }
								value={ iconset }
								onChange={(value) => onChangeIconSet( value, object )}
								help={<ExternalLink href={ window.downloadlist_config.iconsets_url }>{ __( 'Manage Iconsets', 'download-list-block-with-icons' ) }</ExternalLink>}
								__next40pxDefaultSize
							/>
						}
						<CheckboxControl
							label={__('Show preview images', 'download-list-block-with-icons')}
							checked={ object.attributes.showPreview }
							onChange={ value => object.setAttributes( { showPreview: value } ) }
							help={ object.attributes.showPreview && ! pdfPreviewSupported && 0 === pdfsWithoutPreview ? __( 'This server cannot generate preview images of PDF files. Ask your hosting for Imagick with Ghostscript and a policy which allows PDF files.', 'download-list-block-with-icons' ) : __( 'Files without preview keep their icon.', 'download-list-block-with-icons' ) }
							__nextHasNoMarginBottom
						/>
						{previewHints.length > 0 &&
							<Notice status="info" isDismissible={ false } className="downloadlist-preview-hints">
								{ previewHints.map( ( hint, index ) => <p key={ index }>{ hint }</p> ) }
							</Notice>
						}
						{object.attributes.showPreview &&
							<RangeControl
								label={__('Size of preview images and icon frames in pixel', 'download-list-block-with-icons')}
								value={ object.attributes.previewWidth }
								onChange={ value => object.setAttributes( { previewWidth: value || 48 } ) }
								min={ 16 }
								max={ 600 }
								__nextHasNoMarginBottom
								__next40pxDefaultSize
							/>
						}
					</PanelBody>
					<PanelBody title={ __( 'Link', 'download-list-block-with-icons' ) } initialOpen={ false }>
						<CheckboxControl
							label={__('Show text instead of link', 'download-list-block-with-icons')}
							checked={ object.attributes.hideLink }
							onChange={ value => onChangeHideLink( value, object ) }
							__nextHasNoMarginBottom
						/>
						{false === object.attributes.hideLink && <div>
							<SelectControl
								label={__('Choose link target', 'download-list-block-with-icons')}
								value={ object.attributes.linkTarget }
								options={ [
									{ label: __('Direct link', 'download-list-block-with-icons'), value: 'direct' },
									{ label: __('Attachment page', 'download-list-block-with-icons'), value: 'attachmentpage' },
								] }
								onChange={ value => onChangeLinkTarget( value, object ) }
								__next40pxDefaultSize
							/>
							{'direct' === object.attributes.linkTarget &&
								<CheckboxControl
									label={__('Do not force download', 'download-list-block-with-icons')}
									checked={ object.attributes.doNotForceDownload }
									onChange={ value => onChangeDoNotForceDownload( value, object ) }
									__nextHasNoMarginBottom
								/>
							}
							<SelectControl
								label={__('Handling for link', 'download-list-block-with-icons')}
								value={ object.attributes.linkBrowserTarget }
								options={ [
									{ label: __('Do not use', 'download-list-block-with-icons'), value: '' },
									{ label: __('Same tab / window', 'download-list-block-with-icons'), value: '_self' },
									{ label: __('New tab / window', 'download-list-block-with-icons'), value: '_blank' },
									{ label: __('Parent window', 'download-list-block-with-icons'), value: '_parent' },
									{ label: __('Complete window', 'download-list-block-with-icons'), value: '_top' },
									{ label: __('Define frame name', 'download-list-block-with-icons'), value: 'own' },
								] }
								onChange={ value => onChangeLinkBrowserTarget( value, object ) }
								help={object.attributes.linkBrowserTarget.length > 0 && __( 'Be aware that this setting could be overridden by the visitors browser. It is also against the rules for accessibility in the web.', 'download-list-block-with-icons' ) }
								__next40pxDefaultSize
							/>
							{'own' === object.attributes.linkBrowserTarget && <TextControl label={__( 'Set frame name', 'download-list-block-with-icons' )} value={ object.attributes.linkBrowserTargetName } onChange={ value => onChangeLinkBrowserTargetName( value, object ) } />}
						</div>}
					</PanelBody>
					<PanelBody title={ __( 'Download button', 'download-list-block-with-icons' ) } initialOpen={ false }>
						<CheckboxControl
							label={__('Show download-button', 'download-list-block-with-icons')}
							checked={ object.attributes.showDownloadButton }
							onChange={ value => onChangeShowDownloadButton( value, object ) }
							__nextHasNoMarginBottom
						/>
						{true === object.attributes.showDownloadButton && <div>
							<SelectControl
								label={__('Handling for download-button', 'download-list-block-with-icons')}
								value={ object.attributes.downloadLinkTarget }
								options={ [
									{ label: __('Do not use', 'download-list-block-with-icons'), value: '' },
									{ label: __('Same tab / window', 'download-list-block-with-icons'), value: '_self' },
									{ label: __('New tab / window', 'download-list-block-with-icons'), value: '_blank' },
									{ label: __('Parent window', 'download-list-block-with-icons'), value: '_parent' },
									{ label: __('Complete window', 'download-list-block-with-icons'), value: '_top' },
									{ label: __('Define frame name', 'download-list-block-with-icons'), value: 'own' },
								] }
								onChange={ value => onChangeDownloadLinkTarget( value, object ) }
								help={object.attributes.downloadLinkTarget.length > 0 && __( 'Be aware that this setting could be overridden by the visitors browser. It is also against the rules for accessibility in the web.', 'download-list-block-with-icons' ) }
								__next40pxDefaultSize
							/>
							{'own' === object.attributes.downloadLinkTarget && <TextControl label={__( 'Set frame name', 'download-list-block-with-icons' )} value={ object.attributes.downloadLinkTargetName } onChange={ value => onChangeDownloadLinkTargetName( value, object ) } />}
						</div>
						}
					</PanelBody>
					<PanelBody title={ __( 'Advanced settings', 'download-list-block-with-icons' ) } initialOpen={ false }>
						<CheckboxControl
							label={__('Hide file sizes', 'download-list-block-with-icons')}
							checked={ object.attributes.hideFileSize }
							onChange={ value => onChangeHideFileSize( value, object ) }
							__nextHasNoMarginBottom
						/>
						<CheckboxControl
							label={__('Hide descriptions', 'download-list-block-with-icons')}
							checked={ object.attributes.hideDescription }
							onChange={ value => onChangeHideDescription( value, object ) }
							__nextHasNoMarginBottom
						/>
						<CheckboxControl
							label={__('Show file dates', 'download-list-block-with-icons')}
							checked={ object.attributes.showFileDates }
							onChange={ value => onChangeShowFileDates( value, object ) }
							__nextHasNoMarginBottom
						/>
						<CheckboxControl
							label={__('Show file format labels', 'download-list-block-with-icons')}
							checked={ object.attributes.showFileFormatLabel }
							onChange={ value => onChangeShowFileFormatLabel( value, object ) }
							__nextHasNoMarginBottom
						/>
						<SelectControl
							label={__('Robots', 'download-list-block-with-icons')}
							value={ object.attributes.robots }
							options={ [
								{ label: __('follow', 'download-list-block-with-icons'), value: 'follow' },
								{ label: __('nofollow', 'download-list-block-with-icons'), value: 'nofollow' },
							] }
							onChange={ value => onChangeLinkRobots( value, object ) }
							__next40pxDefaultSize
						/>
					</PanelBody>
					<PanelBody initialOpen={false} title={ __( 'Do you need help?', 'download-list-block-with-icons' ) }>
						<p>{__( 'You are welcome to contact our support forum if you have any questions.', 'download-list-block-with-icons' )}</p>
						<p>{<ExternalLink href={ window.downloadlist_config.support_url }>{ __( 'Go to supportforum', 'download-list-block-with-icons' ) }</ExternalLink>}</p>
					</PanelBody>
				</InspectorControls>
			}
			{
				<InspectorControls group="styles">
					<ToolsPanel
						className="downloadlist-media-frame-panel"
						label={ __( 'Frame for icons and previews', 'download-list-block-with-icons' ) }
						panelId={ object.clientId }
						resetAll={ () => object.setAttributes( getMediaFrameDefaults() ) }
						__experimentalFirstVisibleItemClass="first"
						__experimentalLastVisibleItemClass="last"
					>
						<ColorGradientSettingsDropdown
							panelId={ object.clientId }
							__experimentalIsRenderedInSidebar
							{ ...colorGradientSettings }
							gradients={ [] }
							disableCustomGradients
							settings={ [
								{
									colorValue: object.attributes.mediaBorderColor || undefined,
									onColorChange: ( value ) => object.setAttributes( {
										mediaBorderColor: value || '',
										// a frame color needs a frame width to be visible.
										mediaBorderWidth: value && ! object.attributes.mediaBorderWidth ? 1 : object.attributes.mediaBorderWidth,
									} ),
									label: __( 'Frame color', 'download-list-block-with-icons' ),
									resetAllFilter: () => ( { mediaBorderColor: getMediaFrameDefaults().mediaBorderColor } ),
								},
								{
									colorValue: object.attributes.mediaBackground || undefined,
									onColorChange: ( value ) => object.setAttributes( { mediaBackground: value || '' } ),
									label: __( 'Background color', 'download-list-block-with-icons' ),
									resetAllFilter: () => ( { mediaBackground: getMediaFrameDefaults().mediaBackground } ),
								},
							] }
						/>
						{ [
							{ name: 'mediaBorderWidth', label: __( 'Frame width in pixel', 'download-list-block-with-icons' ), min: 0, max: 20 },
							{ name: 'mediaBorderRadius', label: __( 'Frame radius in pixel', 'download-list-block-with-icons' ), min: 0, max: 100 },
							{ name: 'mediaPadding', label: __( 'Inner spacing in pixel', 'download-list-block-with-icons' ), min: 0, max: 50 },
							{ name: 'previewWidth', label: __( 'Size of preview images and icon frames in pixel', 'download-list-block-with-icons' ), min: 16, max: 600 },
						].map( ( control ) => (
							<ToolsPanelItem
								key={ control.name }
								panelId={ object.clientId }
								label={ control.label }
								hasValue={ () => object.attributes[ control.name ] !== getMediaFrameDefaults()[ control.name ] }
								onDeselect={ () => object.setAttributes( { [ control.name ]: getMediaFrameDefaults()[ control.name ] } ) }
								resetAllFilter={ () => ( { [ control.name ]: getMediaFrameDefaults()[ control.name ] } ) }
								isShownByDefault
							>
								<RangeControl
									label={ control.label }
									value={ object.attributes[ control.name ] }
									onChange={ ( value ) => object.setAttributes( { [ control.name ]: undefined === value ? getMediaFrameDefaults()[ control.name ] : value } ) }
									min={ control.min }
									max={ control.max }
									allowReset
									resetFallbackValue={ getMediaFrameDefaults()[ control.name ] }
									__nextHasNoMarginBottom
									__next40pxDefaultSize
								/>
							</ToolsPanelItem>
						) ) }
					</ToolsPanel>
				</InspectorControls>
			}
			<div>
				{attachmentsFailed &&
					<p>{ __( 'The files could not be loaded. Please reload the page.', 'download-list-block-with-icons' ) }</p>
				}
				{files &&
					<DndContext
						sensors={sensors}
						onDragEnd={handleDragEnd}
						modifiers={[restrictToVerticalAxis, restrictToWindowEdges]}
					>
						<SortableContext items={ files } strategy={verticalListSortingStrategy}>
							{files.map((file, index) => (
								<SortableItem key={file.id} file={file} index={index} files={files} object={object} showPreview={showPreview} />
							))}
						</SortableContext>
					</DndContext>
				}
				{files.length === 0 && 0 === object.attributes.list &&
					<MediaUploadCheck>
						<MediaUpload
							onSelect={ ( newFiles ) => addFiles( newFiles ) }
							multiple={ true }
							allowedTypes={ allowed_file_types }
							value={ object.attributes.files }
							render={ ( { open } ) => (
								<Button variant="primary" className="has-text" onClick={ open } icon={plus} text={ __( 'Add your first file', 'download-list-block-with-icons' ) } size="small"></Button>
							) }
						/>
					</MediaUploadCheck>
				}
				{files.length === 0 && object.attributes.list > 0 &&
					<Button variant="primary" className="has-text" onClick={ () => window.location.href=window.downloadlist_config.list_url } icon={plus} text={ __( 'Assign files to this download list', 'download-list-block-with-icons' ) } size="small"></Button>
				}
			</div>
		</div>
	);
}

/**
 * Return the class name and the CSS variables for the frame of icons and preview images.
 *
 * Must match Helper::get_media_frame() in PHP.
 *
 * @param attributes  The block attributes.
 * @param showPreview Whether previews are shown.
 * @returns {{hasFrame: boolean, className: string, style: object}}
 */
function getMediaFrame( attributes, showPreview ) {
	const clamp = ( value, min, max ) => Math.max( min, Math.min( max, parseInt( value, 10 ) || 0 ) );
	const size = attributes.previewWidth ? clamp( attributes.previewWidth, 16, 600 ) : 48;
	const borderWidth = clamp( attributes.mediaBorderWidth, 0, 20 );
	const borderRadius = clamp( attributes.mediaBorderRadius, 0, 300 );
	const padding = clamp( attributes.mediaPadding, 0, 100 );
	const borderColor = attributes.mediaBorderColor || '';
	const background = attributes.mediaBackground || '';
	const hasFrame = borderWidth > 0 || borderRadius > 0 || padding > 0 || '' !== background;

	// bail if no frame is used.
	if ( ! showPreview && ! hasFrame ) {
		return { hasFrame, className: '', style: {} };
	}

	// collect the CSS variables.
	const style = { '--downloadlist-media-size': size + 'px' };
	if ( borderWidth > 0 ) {
		style[ '--downloadlist-media-border-width' ] = borderWidth + 'px';
		if ( '' !== borderColor ) {
			style[ '--downloadlist-media-border-color' ] = borderColor;
		}
	}
	if ( borderRadius > 0 ) {
		style[ '--downloadlist-media-border-radius' ] = borderRadius + 'px';
	}
	if ( padding > 0 ) {
		style[ '--downloadlist-media-padding' ] = padding + 'px';
	}
	if ( '' !== background ) {
		style[ '--downloadlist-media-background' ] = background;
	}
	return { hasFrame, className: ' has-media-frame', style };
}

/**
 * Return the default values of the frame for icons and preview images.
 *
 * These are the defaults of the block, which come from the plugin settings.
 *
 * @returns {object}
 */
function getMediaFrameDefaults() {
	const attributes = getBlockType( 'downloadlist/list' )?.attributes || {};
	const getDefault = ( name, fallback ) => undefined !== attributes[ name ]?.default ? attributes[ name ].default : fallback;
	return {
		mediaBorderColor: getDefault( 'mediaBorderColor', '' ),
		mediaBorderWidth: getDefault( 'mediaBorderWidth', 0 ),
		mediaBorderRadius: getDefault( 'mediaBorderRadius', 0 ),
		mediaPadding: getDefault( 'mediaPadding', 0 ),
		mediaBackground: getDefault( 'mediaBackground', '' ),
		previewWidth: getDefault( 'previewWidth', 48 ),
	};
}
