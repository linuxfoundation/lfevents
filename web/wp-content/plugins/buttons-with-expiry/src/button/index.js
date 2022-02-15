/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { button as icon } from '@wordpress/icons';
import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import Edit from './edit';
import save from './save';

const { attributes } = metadata;

registerBlockType( 'lf/button-with-expiry', {
	title: __( 'Button With Expiry' ),
	icon,
	category: 'design',
	description: __( 'Fork of Gutenberg Buttons but with Expiry options' ),
	keywords: [
		'button',
		'cta',
		'expiry',
		'lf',
		'cncf',
		'button with expiry',
		'linux',
	],
	attributes: {
		...attributes,
		willExpire: {
			type: 'bool',
			default: false,
		},
		expireAt: {
			type: 'string',
			default: new Date(),
		},
		expireText: {
			type: 'string',
		},
		size: {
			type: 'string',
		},
	},
	supports: {
		align: false,
	},
	example: {
		attributes: {
			className: 'is-style-fill',
			backgroundColor: 'vivid-green-cyan',
			text: __( 'Learn More' ),
		},
	},
	parent: [ 'lf/buttons-with-expiry' ],
	edit: Edit,
	save,
} );
