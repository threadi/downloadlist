/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

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
	useBlockProps
} from '@wordpress/block-editor';
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
	MenuItem
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
	let files = ( !object.attributes.files ? [] : object.attributes.files )

	// collect the fileIds as array
	let fileIds = [];
	{files.map((file) => {
		fileIds.push(file.id);
	})}

	// if we use a download list, get its files.
	if( !object.attributes.preview ) {
		// get the attachments which are assigned to the chosen list.
		let list_files = useSelect((select) => {
			return select('core').getEntityRecords( 'postType', 'attachment', {
				per_page: -1,
				dl_icon_lists: [object.attributes.list],
				_fields: 'id'
			} );
		}, [object.attributes.list]) || [];
		if( list_files.length > 0 && 'manual' !== object.attributes.order ) {
			fileIds = [];
			{list_files.map((file) => {
				fileIds.push(file.id);
			})}
		}
	}

	/**
	 * Revert preview-setting if block is selected.
	 */
	if( object.isSelected && object.attributes.preview ) {
		object.attributes.preview = false;
	}

	/**
	 * Run AJAX-request to get the data of each selected file, but not in preview-mode.
	 *
	 * @type {*[]}
	 */
	let allowed_file_types = [];
	let iconsets = [];
	if( !object.attributes.preview ) {
		// set actual date if it is not present.
		if (!object.attributes.date) {
			object.attributes.date = getActualDate()
		}

		// send request to our custom endpoint to get the data of the files.
		let attachments = useSelect((select) => {
			return select('core').getEntityRecords('downloadlist/v1', 'files', {
				post_ids: fileIds,
				date: object.attributes.date,
				per_page: fileIds.length
			}, []) || null;
		});
		if (attachments) {
			// loop through the results.
			files = [];
			let objectFiles = [];
			attachments.map((newFile) => {
				// collect the file-data for @SortableItem.
				files.push(newFile)
				// and add the file data for the file list for the block-settings.
				objectFiles.push({id: newFile.id})
			});

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

			// save a clean file list only with the id-property per file
			// -> case 1: update from version 1.x from this plugin
			// -> case 2: a file is not available anymore
			if (objectFiles.length > 0 && JSON.stringify(objectFiles) !== JSON.stringify(object.attributes.files)) {
				object.attributes.files = objectFiles;
			}
		}

		// useSelect to retrieve all post types
		const iconsets_array = useSelect((select) => {
			return select('core').getEntityRecords('taxonomy', 'dl_icon_set', {per_page: -1, hide_empty: true})
		}, []) || [];

		// Options in SelectControl expected format [{label: ..., value: ...}]
		iconsets = iconsets_array.map(
			// Format the options for display in the <SelectControl/>
			(icon) => ({
				label: icon.name,
				value: icon.slug
			})
		);

		// check if chosen iconset does exist.
		if (0 < object.attributes.iconset.length && iconsets_array.length > 0) {
			let found = false;
			for (let i = 0; i < iconsets_array.length; i++) {
				if (object.attributes.iconset === iconsets_array[i].slug) {
					found = true;
				}
			}
			if (false === found) {
				object.attributes.iconset = '';
			}
		}

		// if no iconset is set, use the default iconset.
		if (0 === object.attributes.iconset.length && iconsets_array.length > 0) {
			for (let i = 0; i < iconsets_array.length; i++) {
				if (1 === iconsets_array[i].meta.default) {
					object.attributes.iconset = iconsets_array[i].slug;
				}
			}
		}

		// retrieve all possible file types from our custom endpoint.
		let allowed_file_types_server_result = useSelect((select) => {
			return select('core').getEntityRecords('downloadlist/v1', 'filetypes', {
				per_page: -1,
				iconset: object.attributes.iconset
			});
		}, [object.attributes.iconset]) || [];

		allowed_file_types = allowed_file_types_server_result.map(
			(filetype) => filetype.value
		);
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

	// get the download lists.
	const download_lists = useSelect((select) => {
		return select('core').getEntityRecords( 'taxonomy', 'dl_icon_lists' );
	});

	// generate download list for options.
	let lists_to_use = [];
	let active_list = false;
	if( download_lists !== null ) {
		download_lists.map((list) => {
			if( list.slug === object.attributes.list ) {
				active_list = list;
			}
			lists_to_use.push({
				'label': list.name,
				'value': list.id,
			})
		});
	}

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
				order: 'manual'
			})
		}
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
		object.setAttributes({ order: 'title', orderby: orderby, files: sortByTitle( files, orderby ), date: getActualDate() })
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
			c.push(files[i - 1].title.localeCompare(files[i].title, undefined, {numeric: true, sensitivity: 'base'}));
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
		object.setAttributes({ order: 'size', orderby: orderby, files: sortBySize( files, orderby ), date: getActualDate() })
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
			c.push(file_array[i - 1].filesizeInBytes - file_array[i].filesizeInBytes);
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
		object.setAttributes({ order: 'date', orderby: orderby, files: sortByDate( files, orderby ), date: getActualDate() })
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
			c.push(file_array[i - 1].date - file_array[i].date);
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
		{newFiles.map((newFile) => {
			let doNotAdd = false;
			{files.map((file) => {
				if( file.id === newFile.id ) {
					// do not add
					doNotAdd = true;
				}
			})}
			if( !doNotAdd ) {
				files.push({
					id: newFile.id
				})
			}
		})}
		object.setAttributes({files: files, date: getActualDate()})
	}

	/**
	 * Add class name for used iconset.
	 */
	const blockProps = useBlockProps( {
		className: 'iconset-' + object.attributes.iconset,
	} );

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
					{lists_to_use &&
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
										onClick={ () => location.href=window.downloadlist_config.list_url }
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
						disabled={ ( object.attributes.files && object.attributes.files.length <= 1 ) || !object.attributes.files }
						onClick={ () => sortFilesByTitle() }
					/>
					<ToolbarButton
						icon={<svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="32" height="32" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24"><path fill="currentColor" d="M7 21H3v-2h4v-1H5a2 2 0 0 1-2-2v-1c0-1.1.9-2 2-2h2a2 2 0 0 1 2 2v4c0 1.11-.89 2-2 2m0-6H5v1h2M5 3h2a2 2 0 0 1 2 2v4c0 1.11-.89 2-2 2H5a2 2 0 0 1-2-2V5c0-1.1.9-2 2-2m0 6h2V5H5m7 0h10v2H12m0 12v-2h10v2m-10-8h10v2H12Z"/></svg>}
						label={__('Sort files by filesize', 'download-list-block-with-icons')}
						disabled={ ( object.attributes.files && object.attributes.files.length <= 1 ) || !object.attributes.files }
						onClick={ () => sortFilesByFileSize() }
					/>
					<ToolbarButton
						icon={<svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="32" height="32" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24"><path fill="currentColor" d="M20 17h3l-4 4l-4-4h3V3h2zM8 5c-3.86 0-7 3.13-7 7s3.13 7 7 7c3.86 0 7-3.13 7-7s-3.13-7-7-7m0 2.15c2.67 0 4.85 2.17 4.85 4.85c0 2.68-2.17 4.85-4.85 4.85c-2.68 0-4.85-2.17-4.85-4.85c0-2.68 2.17-4.85 4.85-4.85M7 9v3.69l3.19 1.84l.75-1.3l-2.44-1.41V9"/></svg>}
						label={__('Sort files by date', 'download-list-block-with-icons')}
						disabled={ ( object.attributes.files && object.attributes.files.length <= 1 ) || !object.attributes.files }
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
								value={ object.attributes.iconset }
								onChange={(value) => onChangeIconSet( value, object )}
								help={<ExternalLink href={ window.downloadlist_config.iconsets_url }>{ __( 'Manage Iconsets', 'download-list-block-with-icons' ) }</ExternalLink>}
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
			<div { ...useBlockProps()}>
				{files &&
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
					<Button variant="primary" className="has-text" onClick={ () => location.href=window.downloadlist_config.list_url } icon={plus} text={ __( 'Assign files to this download list', 'download-list-block-with-icons' ) } size="small"></Button>
				}
			</div>
		</div>
	);
}
