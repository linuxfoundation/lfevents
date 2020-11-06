import { registerBlockType, createBlock } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

import './style.scss';
import Edit from './edit';
import save from './save';

registerBlockType( 'lf/icon-list', {
	title: __( 'Icon List' ),
	description: __( 'Customised unordered list with icons' ),
	category: 'common',
	icon: 'list-view',
	attributes: {
		values: {
			type: "string",
			source: "html",
			selector: "ul",
			multiline: "li",
			default: ""
		},
		type: {
			type: "string"
		},
		selectedIcon: {
			type: "string"
		},
		iconSize: {
			type: 'integer',
			default: 20,
		},
		columnCount: {
			type: 'integer',
			default: 1,
		},
		listGap: {
			type: 'integer',
			default: 10,
		},
	},
	keywords: [
		__( 'list' ),
		__( 'icon list' ),
		__( 'checklist' ),
		__( 'tick list' ),
	],
	supports: {
		align: [ 'wide', 'full' ],
	},
	transforms: {
    from: [
      {
        type: 'block',
				blocks: ['ugb/icon-list'],
				transform: function (attributes) {
          return createBlock("lf/icon-list", {
						values: attributes.text
          });
				},
			},
			{
        type: 'block',
				blocks: ['core/list'],
				transform: function (attributes) {
          return createBlock("lf/icon-list", {
						values: attributes.values
          });
				},

      },
    ],
  },
	edit: Edit,
	save,
} );
