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
import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { Button } from '@wordpress/components';
import { plus, dragHandle } from '@wordpress/icons';
import Sortable from 'gutenberg-sortable';

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

	/**
	 * Remote an item from the list.
	 *
	 * @param index
	 */
	function removeListItem(index) {
		files.splice(index, 1);
		object.setAttributes( { files: files, date: new Date } );
	};

	/**
	 * Collect return for the edit-function
	 */
	return (
		<div { ...useBlockProps() }>
			{files &&
				<div>
					<Sortable
						className="list"
						items={files}
						axis="y"
						onSortEnd={(files) => {object.setAttributes({ files: files })}}
					>
						{files.map((file, index) => (
							<div id={`file${file.id}`} className={`editor-styles-wrapper wp-block-downloadlist-list-draggable file_${file.type} file_${file.subtype}`}>
								<Button className="downloadlist-list-trash" onClick={() => removeListItem(index)} title={__('remove from list', 'downloadlist')}/>
								<Button title={__( 'hold to pull', 'downloadlist' )}>{dragHandle}</Button>
								<a href={file.url}>{file.title}</a> ({file.filesizeHumanReadable})<br/>{file.description}
							</div>
						))}
					</Sortable>
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
										// but update it
										files[index].alt = newFile.alt
										files[index].description = newFile.description
										files[index].title = newFile.title
									}
								})};
								if( !doNotAdd ) {
									files.push({
										id: newFile.id,
										alt: newFile.alt,
										description: newFile.description,
										filesizeHumanReadable: newFile.filesizeHumanReadable,
										type: newFile.type,
										subtype: newFile.subtype,
										title: newFile.title,
										url: newFile.url
									})
								}
							})}
							object.setAttributes({files: files, date: new Date})
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
