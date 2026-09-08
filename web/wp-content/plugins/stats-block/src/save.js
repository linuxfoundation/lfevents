import { useBlockProps, RichText } from '@wordpress/block-editor';

export default function save( { attributes } ) {
	const {
		stats = [],
		columns = 4,
		animate = true,
		duration = 2000,
	} = attributes;

	const blockProps = useBlockProps.save( { className: 'lf-stats' } );

	if ( animate ) {
		blockProps[ 'data-lf-stats' ] = '';
		blockProps[ 'data-duration' ] = String( duration );
	}

	return (
		<div { ...blockProps }>
			<div
				className="lf-stats__grid"
				style={ { '--lf-stats-columns': String( columns ) } }
			>
				{ stats.map( ( stat, index ) => (
					<div className="lf-stats__item" key={ index }>
						<RichText.Content
							tagName="span"
							className="lf-stats__number"
							data-lf-stats-number=""
							value={ stat.number }
						/>
						<RichText.Content
							tagName="span"
							className="lf-stats__label"
							value={ stat.label }
						/>
					</div>
				) ) }
			</div>
		</div>
	);
}
