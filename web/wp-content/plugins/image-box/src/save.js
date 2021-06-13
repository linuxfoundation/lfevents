import { useBlockProps, RichText } from '@wordpress/block-editor';

export default function save( { attributes } ) {
	const { columns, height, align, alignAll } = attributes;
	const range = ( start, end ) => {
		return Array.from( { length: end - start }, ( v, k ) => k + start );
	};

	const mainStyle = {
		'--image-box-height': height ? `${ height }px` : '250px',
	};

	return (
		<div
			style={ mainStyle }
			className={ `image-box-wrapper wp-block-lf-image-box align${ align ? align : 'none' } columns-${ columns } ${ alignAll }` }
		>
			{ range( 1, columns + 1 ).map( ( i ) => {
				const imageUrl = attributes[ `imageUrl${ i }` ];
				const title = attributes[ `title${ i }` ];
				const description = attributes[ `description${ i }` ];
				const link = attributes[ `link${ i }` ];
				const newWindow = attributes[ `newWindow${ i }` ];

				const columnStyles = {
					[ `--image-box-img${ i }` ]: imageUrl
						? `url(${ imageUrl })`
						: undefined,
				};

				const blockProps = useBlockProps.save();

				return (
					<div { ...blockProps }
						style={ columnStyles }
						className={ `column column-${ i }` }
						key={ i }
					>
						{ link && (
							<a
								className="box-link"
								href={ link }
								{...(newWindow ? {target: '_blank'} : {})}
								{...(newWindow ? {rel: 'noopener'} : {})}
							></a>
						) }
						<div className="image-box-overlay"></div>
						<div className="image-box-content">
							{ ! RichText.isEmpty( title ) && (
								<RichText.Content
									tagName="h4"
									value={ title }
								/>
							) }
							{ ! RichText.isEmpty( description ) && (
								<RichText.Content
									tagName="p"
									value={ description }
								/>
							) }
						</div>
					</div>
				);
			} ) }
		</div>
	);
}
