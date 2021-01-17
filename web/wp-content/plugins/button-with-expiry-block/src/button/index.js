/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { __experimentalGetSettings as getDateSettings } from '@wordpress/date';
const { registerBlockType } = wp.blocks;

/**
 * Internal dependencies
 */
import edit from './edit';
import metadata from './block.json';
import save from './save';
import './editor.scss';

const { category, attributes } = metadata;

// Timezone object from WordPress Settings.
const { timezone } = getDateSettings();

const offsetTime = () => {
	// Time now in WordPress Time.
	const wpTimeSetting = new Date().toLocaleString( 'en-US', { timeZone: timezone.string } );
	const wpTime = ( Date.parse( wpTimeSetting ) / 1000 );

	return wpTime;
};

registerBlockType( 'lf/button-with-expiry', {
	title: __( 'Button With Expiry' ),
	category,
	description: __(
		'Prompt visitors to take action with a button-style link.'
	),
	keywords: [ 'button', 'button with expiry', 'linux' ],
	attributes: {
		...attributes,
		willExpire: {
			type: 'bool',
			default: false,
		},
		expireAt: {
			type: 'number',
			default: offsetTime(),
		},
		expireText: {
			type: 'string',
		},
		size: {
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
