import { RichText } from '@wordpress/block-editor';

// import helper functions.
import { range } from './utils.js';

export default function ( { attributes } ) {
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
		showKeynote,
		keynoteText,
		keynoteLink,
	} = attributes;

	const mainStyle = {
		'--track-height': height ? `${ height }px` : '250px',
		'--track-color1': color1,
		'--track-color2': color2,
		'--track-text-color': textColor,
	};

	return (
		<div
			style={ mainStyle }
			className={ `wp-block-lf-track-grid align${
				align ? align : 'wide'
			}` }
		>
			<ul className={ `track-wrapper columns-${ columns }` }>
				{ range( 1, tracks ).map( ( i ) => {
					const title = attributes[ `title${ i }` ];
					const link = attributes[ `link${ i }` ];

					return (
						<li
							className={ `track-box track-style box-${ i } ${ className }` }
							key={ i }
						>
							{ link && (
								<a className="box-link" href={ link }></a>
							) }
							{ ! RichText.isEmpty( title ) && (
								<RichText.Content
									tagName="h4"
									value={ title }
								/>
							) }
							{ ctaIcon === 'view-track' && (
								<div className="track-cta button transparent-outline">
									View Track
								</div>
							) }
							{ ctaIcon ===
								'is-style-track-double-angle-right' && (
								<h3 className="track-cta is-style-track-double-angle-right">
									&gt;&gt;
								</h3>
							) }
						</li>
					);
				} ) }
			</ul>
			{ showKeynote && (
				<div
					className={ `track-keynote track-style ${ className }` }
					style={ mainStyle }
				>
					{ keynoteLink && (
						<a className="box-link" href={ keynoteLink }></a>
					) }
					{ ! RichText.isEmpty( keynoteText ) && (
						<RichText.Content tagName="h4" value={ keynoteText } />
					) }
					{ ctaIcon && (
						<h3 className="track-cta is-style-track-double-angle-right">
							&gt;&gt;
						</h3>
					) }
				</div>
			) }
		</div>
	);
}
