/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
const { registerBlockType } = wp.blocks;

/**
 * Internal dependencies
 */
import edit from './edit';
import metadata from './block.json';
import save from './save';
import './editor.scss';

const { name, category, attributes } = metadata;

registerBlockType( name, {
	title: __( 'Button With Expiry' ),
	category,
	description: __(
		'Prompt visitors to take action with a button-style link.'
	),
	keywords: [ 'button', 'button with expiry', 'linux' ],
	attributes: {
		...attributes,
		expireAt: {
			type: 'number',
			default: 60 * ( 1440 + Math.ceil( Date.now() / 60000 ) ), // 24 hours from Date.now
		},
		expireText: {
			type: 'string',
		},
	},
	supports: {
		align: true,
		alignWide: false,
	},
	parent: [ 'lf/buttons-with-expiry' ],
	edit,
	save,
} );

