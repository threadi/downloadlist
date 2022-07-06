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
import { SortableItem } from "./SortableItem";

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
	 * Collect return for the edit-function
	 */
	return (
		<div { ...useBlockProps() }>
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
