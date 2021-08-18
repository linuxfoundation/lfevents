import { registerBlockType } from '@wordpress/blocks';
import './style.scss';

import Edit from './edit';

registerBlockType( 'lf/live-stream-gate-block', {
	edit: Edit,
} );
