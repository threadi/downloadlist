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
import { registerBlockType } from '@wordpress/blocks';

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
import Edit from './edit';
import save from "./save";

/**
 * Every block starts by registering a new block type definition.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
registerBlockType( 'downloadlist/list', {
	title: __( 'Download List with Icons', 'downloadlist' ),
	description: __('Provides a Gutenberg block for capturing a download list with file type specific icons.', 'downloadlist'),

	example: {
		attributes: {
			mode: 'preview'
		}
	},

	/**
	 * Attributes for this block.
	 */
	attributes: {
		files: {
			type: 'array'
		},
		hideFileSize: {
			type: 'boolean',
			default: false
		},
		hideDescription: {
			type: 'boolean',
			default: false
		},
		hideIcon: {
			type: 'boolean',
			default: false
		},
		linkTarget: {
			type: 'string',
			default: 'direct'
		}
	},

	/**
	 * @see ./edit.js
	 */
	edit: Edit,

	/**
	 * @see ./save.js
	 */
	save,
} );
