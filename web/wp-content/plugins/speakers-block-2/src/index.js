import { registerBlockType } from '@wordpress/blocks';

import Edit from './edit';

registerBlockType( 'cgb/block-speakers-block-2', {
	edit: Edit,
} );
