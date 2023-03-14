import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

import './style.scss';
import Edit from './edit';
import save from './save';
import deprecated from './deprecated';

export const schema = {
	columns: {
		type: 'number',
		default: 3,
	},
	height: {
		type: 'number',
		default: 300,
	},
	alignAll: {
		type: 'string',
		default: 'align-center',
	},
};

// now loop over the following to make the full schema.
{
	[ 1, 2, 3, 4 ].forEach( ( i ) => {
		schema[ `title${ i }` ] = {
			source: 'html',
			selector: `.image-box-wrapper > *:nth-child(${ i }) h4`,
			default: __( 'Title' ),
		};
		schema[ `description${ i }` ] = {
			source: 'html',
			selector: `.image-box-wrapper > *:nth-child(${ i }) p`,
			default: __( 'Description' ),
		};
		schema[ `imageUrl${ i }` ] = {
			type: 'string',
		};
		schema[ `imageId${ i }` ] = {
			type: 'number',
		};
		schema[ `link${ i }` ] = {
			type: 'string',
			source: 'attribute',
			selector: `.image-box-wrapper > *:nth-child(${ i }) a`,
			attribute: 'href',
		};
		schema[ `newWindow${ i }` ] = {
			type: 'boolean',
			default: false,
		};
	} );
}

registerBlockType( 'lf/image-box', {
	title: __( 'Image Box' ),
	description: __( 'A row of images with hover overlay effect' ),
	category: 'common',
	icon: 'images-alt',
	keywords: [ __( 'image box' ), __( 'featured' ), __( 'overlay' ) ],
	supports: {
		align: [ 'wide', 'full' ],
	},
	attributes: schema,
	edit: Edit,
	save,
	deprecated: [
		{
			attributes: schema,
			save: deprecated,
		},
	],
} );
