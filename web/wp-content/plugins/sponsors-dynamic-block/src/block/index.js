/**
 * BLOCK: sponsors-dynamic-block
 *
 * Registering a basic block with Gutenberg.
 * Simple block, renders and saves the same content without any interactivity.
 */

//  Import CSS.
import './styles/style.scss';
import './styles/editor.scss';

import Edit from './edit.js';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;

registerBlockType( 'cgb/block-sponsors-dynamic-block', {
	title: __( 'Sponsors' ),
	icon: 'editor-kitchensink',
	category: 'common',
	keywords: [
		__( 'Sponsors' ),
	],
	attributes: {
		sponsors: {
			type: 'array',
			default: [],
		},
		tierName: {
			type: 'string',
			default: '',
		},
		tierSize: {
			type: 'string',
			default: 'medium',
		},
	},
	edit: Edit,
} );
