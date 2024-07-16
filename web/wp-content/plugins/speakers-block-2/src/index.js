import { registerBlockType } from '@wordpress/blocks';

import Edit from './edit';

registerBlockType( 'cgb/block-speakers-block', {
	edit: Edit,
} );
