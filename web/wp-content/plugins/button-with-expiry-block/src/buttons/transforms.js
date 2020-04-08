/**
 * WordPress dependencies
 */
import { createBlock } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import { name } from './block.json';

const transforms = {
	from: [
		{
			type: 'block',
			isMultiBlock: true,
			blocks: [ 'lf/button-with-expiry' ],
			transform: ( buttons ) =>
				// Creates the buttons block
				createBlock(
					name,
					{},
					// Loop the selected buttons
					buttons.map( ( attributes ) =>
						// Create singular button in the buttons block
						createBlock( 'lf/button-with-expiry', attributes )
					)
				),
		},
	],
};

export default transforms;
