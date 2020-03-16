/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
// import { button as icon } from '@wordpress/icons';
const { registerBlockType } = wp.blocks;

/**
 * Internal dependencies
 */
import transforms from './transforms';
import edit from './edit';
import metadata from './block.json';
import save from './save';
import './editor.scss';

const { name, layout } = metadata;

registerBlockType( name, {
	title: __( 'Buttons With Expiry' ),
	layout,
	category: 'layout',
	keywords: [
		__( 'Buttons With Expiry' ),
		__( 'Linux' ),
	],
	attributes: {},
	supports: {
		align: true,
		alignWide: false,
	},
	transforms,
	edit,
	save,
} );
