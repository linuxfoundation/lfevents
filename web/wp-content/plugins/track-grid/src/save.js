import { RichText } from '@wordpress/block-editor';

// import helper functions.
import { range } from './utils.js';

export default function save( { attributes } ) {
	const {
		tracks,
		columns,
		height,
		color1,
		color2,
		textColor,
		ctaIcon,
		align,
		className,
	} = attributes;

	const mainStyle = {
		'--track-height': height ? `${ height }px` : '250px',
		'--track-color1': color1,
		'--track-color2': color2,
		'--track-text-color': textColor,
	};

	return (
		<ul
			style={ mainStyle }
			className={ `track-wrapper wp-block-lf-track-grid align${
				align ? align : 'wide'
			} columns-${ columns }` }
		>
			{ range( 1, tracks ).map( ( i ) => {
				const title = attributes[ `title${ i }` ];
				const link = attributes[ `link${ i }` ];

				return (
					<li
						className={ `track-box box-${ i } ${ className }` }
						key={ i }
					>
						{ link && <a className="box-link" href={ link }></a> }
						{ ! RichText.isEmpty( title ) && (
							<RichText.Content tagName="h4" value={ title } />
						) }
						{ ctaIcon === 'view-track' && (
							<div className="track-cta button transparent-outline">
								View Track
							</div>
						) }
						{ ctaIcon === 'is-style-track-double-angle-right' && (
							<h3 className="track-cta is-style-track-double-angle-right">
								&gt;&gt;
							</h3>
						) }
					</li>
				);
			} ) }
		</ul>
	);
}
