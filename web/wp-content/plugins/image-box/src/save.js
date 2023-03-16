import { useBlockProps, RichText } from '@wordpress/block-editor';
import classnames from 'classnames';

export default function save( { attributes } ) {
	const { columns, height, align, alignAll } = attributes;
	const range = ( start, end ) => {
		return Array.from( { length: end - start }, ( v, k ) => k + start );
	};

	const mainStyle = {
		'--image-box-height': height ? `${ height }px` : '250px',
	};

	const columnCount = classnames( {
		[ `columns-${ columns }` ]: columns,
	} );

	const wrapperClasses = classnames(
		'image-box-wrapper',
		'wp-block-lf-image-box',
		columnCount,
		{ [ `align-center` ]: alignAll === 'align-center' },
		{ [ `align-left` ]: alignAll === 'align-left' },
		{ [ `align-right` ]: alignAll === 'align-right' },
		{ alignleft: align === 'left' },
		{ aligncenter: align === 'center' },
		{ alignright: align === 'right' },
		{ alignwide: align === 'wide' },
		{ alignfull: align === 'full' }
	);

	return (
		<div style={ mainStyle } className={ wrapperClasses }>
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

				let ariaText = typeof title === 'string' ? title.replace(/(<([^>]+)>)/gi, '') : 'Find out more';

				return (
					<div
						{ ...blockProps }
						style={ columnStyles }
						className={ `column column-${ i }` }
						key={ i }
					>
						{ link && (
							<a
								aria-label={ ariaText }
								className="box-link"
								href={ link }
								{ ...( newWindow ? { target: '_blank' } : {} ) }
								{ ...( newWindow
									? { rel: 'noopener noreferrer' }
									: {} ) }
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
