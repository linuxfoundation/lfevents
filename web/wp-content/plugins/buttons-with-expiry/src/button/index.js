/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { button as icon } from '@wordpress/icons';
import { registerBlockType } from '@wordpress/blocks';
// To get access to WordPress settings.
import { __experimentalGetSettings as getDateSettings } from '@wordpress/date';

import metadata from './block.json';
import Edit from './edit';
import save from './save';

const { attributes } = metadata;

// Timezone object from WordPress Settings.
const { timezone } = getDateSettings();

const offsetTime = () => {
	// Time now in WordPress Time.
	const wpTimeSetting = new Date().toLocaleString( 'en-US', {
		timeZone: timezone.string,
	} );
	const wpTime = Date.parse( wpTimeSetting ) / 1000;

	return wpTime;
};

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
