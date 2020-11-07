import { createBlock } from '@wordpress/blocks';

const transforms = {
	from: [
		{
			type: 'block',
			blocks: [ 'core/buttons' ],
			transform( attrs, objects ) {
				return createBlock(
					'lf/buttons-with-expiry',
					{
						align: attrs.align,
						count: 1,
					},
					objects.map( ( object ) => {
						return createBlock(
							'lf/button-with-expiry',
							{
								url: object.attributes.url,
								text: object.attributes.text,
								title: object.attributes.text,
								backgroundColor: object.attributes.backgroundColor,
								textColor: object.attributes.textColor,
								customBackgroundColor: object.attributes.backgroundColor,
								customTextColor: object.attributes.textColor,
								linkTarget: object.attributes.linkTarget,
							},
						);
					}
					)
				);
			},
		},
		{
			type: 'block',
			isMultiBlock: true,
			blocks: [ 'ugb/button' ],
			transform: function( buttons ) {
				return createBlock( 'lf/buttons-with-expiry', {},
					buttons.map( ( attributes ) => {
						return createBlock( 'lf/button-with-expiry', {
							url: attributes.url,
							title: attributes.text,
							text: attributes.text,
							backgroundColor: attributes.color,
							customBackgroundColor: attributes.color,
							textColor: attributes.textColor2,
							customTextColor: attributes.textColor2,
							linkTarget: attributes.newTab,
						} );
					}
					)
				);
			},
		},
	],
};

export default transforms;
