import { RichText } from '@wordpress/block-editor';

export default function save( { attributes } ) {
	const { values, type, selectedIcon, iconSize, columnCount, listGap  } = attributes;

	// declare these as css variables and attach to the div surrounding list.
	const style = {
		'--list-gap': listGap ? `${ listGap }px` : '0',
		'--icon-size': iconSize ? `${ iconSize }px` : '20px',
		'--column-count': columnCount ? `${ columnCount }` : '1',
	}

	return (
		<div style={ style } className={ selectedIcon }>
		<RichText.Content
			tagName="ul"
			multiline="li"
			value={ values }
			type={ type }
		/>
		</div>
	);
}
