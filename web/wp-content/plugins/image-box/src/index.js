import { registerBlockType, createBlock } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

import './style.scss';
import Edit from './edit';
import save from './save';

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
			default: 'false',
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
	transforms: {
		from: [
			{
				type: 'block',
				blocks: [ 'ugb/image-box' ],
				transform: function ( attributes ) {
					return createBlock(
						'lf/image-box',
						{
							description1: attributes.description1,
							description2: attributes.description2,
							description3: attributes.description3,
							description4: attributes.description4,
							height: attributes.height,
							imageId1: attributes.imageID1,
							imageId2: attributes.imageID2,
							imageId3: attributes.imageID3,
							imageId4: attributes.imageID4,
							imageUrl1: attributes.imageURL1,
							imageUrl2: attributes.imageURL2,
							imageUrl3: attributes.imageURL3,
							imageUrl4: attributes.imageURL4,
							link1: attributes.link1,
							link2: attributes.link2,
							link3: attributes.link3,
							link4: attributes.link4,
							newWindow1: attributes.newTab1,
							newWindow2: attributes.newTab2,
							newWindow3: attributes.newTab3,
							newWindow4: attributes.newTab4,
							title1: attributes.title1,
							title2: attributes.title2,
							title3: attributes.title3,
							title4: attributes.title4,
						},
					);
				},
			},
		],
	},
	edit: Edit,
	save,
} );
