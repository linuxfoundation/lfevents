import { registerBlockType } from '@wordpress/blocks';
import { button as icon } from '@wordpress/icons';
import { __ } from '@wordpress/i18n';

// import transforms from './transforms';
import Edit from './edit';
import save from './save';

import './editor.scss';

registerBlockType( 'lf/buttons-with-expiry', {
	apiVersion: 2,
	title: __( 'Buttons with Expiry', 'buttons-with-expiry' ),
	description: __(
		'Fork of Gutenberg Buttons but with Expiry options',
		'buttons-with-expiry'
	),
	icon,
	category: 'design',
	keywords: [
		__( 'Buttons With Expiry' ),
		__( 'Linux' ),
		__( 'button' ),
		__( 'cta' ),
		__( 'cncf' ),
		__( 'lf' ),
		__( 'expiry' ),
	],
	attributes: {},
	supports: {
		align: true,
		alignWide: false,
	},
	example: {
		innerBlocks: [
			{
				name: 'lf/button-with-expiry',
				attributes: { text: __( 'Find out more' ) },
			},
			{
				name: 'lf/button-with-expiry',
				attributes: { text: __( 'Contact us' ) },
			},
		],
	},
	// transforms,
	edit: Edit,
	save,
} );
