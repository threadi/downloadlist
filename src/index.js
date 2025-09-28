/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * Registers a new block provided a unique name and an object defining its behavior.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
import { registerBlockType, createBlock } from '@wordpress/blocks';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * All files containing `style` keyword are bundled together. The code used
 * gets applied both to the front of your site and to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './style.scss';

/**
 * Internal dependencies
 */
import edit from './edit';
import save from "./save";

/**
 * Every block starts by registering a new block type definition.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
registerBlockType( 'downloadlist/list', {
	title: __( 'Download List Block with Icons', 'download-list-block-with-icons' ),
	description: __('Provides a Gutenberg block for capturing a download list with file type specific icons.', 'download-list-block-with-icons'),

	transforms: {
		from: [
			{
				type: 'block',
				blocks: [ 'core/file', 'core/audio', 'core/video' ],
				transform: ( { id } ) => {
					return createBlock( 'downloadlist/list', {
						'files': [{
							'id': id
						}],
						'preview': true
					} );
				},
			},
		],
	},

	/**
	 * @see ./edit.js
	 */
	edit: edit,

	/**
	 * @see ./save.js
	 */
	save: save,
} );
