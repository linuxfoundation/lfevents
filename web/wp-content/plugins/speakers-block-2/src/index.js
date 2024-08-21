import { registerBlockType } from '@wordpress/blocks';

import Edit from './edit';

registerBlockType( 'lf/speakers-block-2', {
	edit: Edit,
} );
