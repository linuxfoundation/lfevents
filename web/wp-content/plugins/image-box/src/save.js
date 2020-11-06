// import classnames from 'classnames'
import { __ } from '@wordpress/i18n';
import { RichText } from '@wordpress/block-editor';

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
			className={ `image-box-wrapper wp-block-lf-image-box align${ align } columns-${ columns } ${ alignAll }` }
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

				return (
					<div
						style={ columnStyles }
						className={ `column column-${ i }` }
						key={ i }
					>
						{ link && (
							<a
								className="box-link"
								href={ link }
								target={ newWindow ? '_blank' : '_self' }
								rel={ newWindow ? 'noopener noreferrer' : '' }
							></a>
						) }
						<div class="image-box-overlay"></div>
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
