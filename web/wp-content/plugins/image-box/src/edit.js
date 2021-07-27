import { __ } from '@wordpress/i18n';
import {
	RichText,
	InspectorControls,
	URLInput,
	MediaUpload,
} from '@wordpress/block-editor';
import {
	Button,
	PanelBody,
	SelectControl,
	RangeControl,
	Dashicon,
	Placeholder,
	ToggleControl,
} from '@wordpress/components';
import { Fragment } from '@wordpress/element';

import './editor.scss';

export default function Edit( { attributes, setAttributes, isSelected } ) {
	const { columns, height, align, alignAll } = attributes;
	const inspectorControls = (
		<InspectorControls key="lf-image-box">
			<PanelBody title={ __( 'Settings' ) }>
				<RangeControl
					label={ __( 'No. of columns' ) }
					min={ 1 }
					max={ 4 }
					value={ columns }
					onChange={ ( value ) =>
						setAttributes( { columns: value } )
					}
				/>
				<RangeControl
					label={ __( 'Height of box' ) }
					min={ 250 }
					max={ 800 }
					step={ 10 }
					value={ height }
					onChange={ ( value ) => setAttributes( { height: value } ) }
				/>
				<SelectControl
					label={ __( 'Align all the text' ) }
					value={ alignAll }
					options={ [
						{
							label: __( 'Center' ),
							value: 'align-center',
						},
						{
							label: __( 'Left' ),
							value: 'align-left',
						},
						{
							label: __( 'Right' ),
							value: 'align-right',
						},
					] }
					onChange={ ( value ) =>
						setAttributes( { alignAll: value } )
					}
				/>
			</PanelBody>
		</InspectorControls>
	);

	const mainStyle = {
		'--image-box-height': height ? `${ height }px` : '250px',
	};

	return (
		<Fragment>
			{ inspectorControls }
			<div
				style={ mainStyle }
				className={ `image-box-wrapper wp-block-lf-image-box editor-only align${ align } columns-${ columns } ${ alignAll }` }
			>
				{ /* start of columns */ }
				{ [ 1, 2, 3, 4 ].map( ( i ) => {
					const imageUrl = attributes[ `imageUrl${ i }` ];
					const imageId = attributes[ `imageId${ i }` ];
					const title = attributes[ `title${ i }` ];
					const description = attributes[ `description${ i }` ];
					const link = attributes[ `link${ i }` ];
					const newWindow = attributes[ `newWindow${ i }` ];

					function selectImage( value ) {
						setAttributes( {
							[ `imageUrl${ i }` ]: value.url,
						} );
						setAttributes( {
							[ `imageId${ i }` ]: value.id,
						} );
					}

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
							<div className="column-image">
								{ ! imageUrl && (
									<Placeholder label="Add an image as a background">
										<MediaUpload
											onSelect={ selectImage }
											render={ ( { open } ) => {
												return (
													<Button
														onClick={ open }
														className="is-button is-default is-large is-primary"
													>
														Upload image
													</Button>
												);
											} }
										/>
									</Placeholder>
								) }
								{ imageUrl && (
									<Button
										onClick={ () => {
											setAttributes( {
												[ `imageUrl${ i }` ]: '',
												[ `imageId${ i }` ]: '',
											} );
										} }
										className="components-button block-editor-media-placeholder__button block-editor-media-placeholder__upload-button is-secondary"
									>
										Clear image
									</Button>
								) }
							</div>
							<div className="column-text">
								<RichText
									tagName="h4"
									value={ title }
									onChange={ ( newTitle ) =>
										setAttributes( {
											[ `title${ i }` ]: newTitle,
										} )
									}
									placeholder={ __( 'Title' ) }
								/>
								<RichText
									tagName="p"
									placeholder={ __( 'Image description' ) }
									value={ description }
									onChange={ ( newDescription ) =>
										setAttributes( {
											[ `description${ i }` ]: newDescription,
										} )
									}
								/>
								{ isSelected && (
									<>
										<form
											className="blocks-button__inline-link"
											onSubmit={ ( event ) =>
												event.preventDefault()
											}
										>
											<Dashicon icon="admin-links" />
											<URLInput
												value={ link }
												className="components-base-control__field"
												onChange={ ( newLink ) => {
													setAttributes( {
														[ `link${ i }` ]: newLink,
													} );
												} }
											/>
											<Button
												icon="editor-break"
												label={ __( 'Apply' ) }
												className="is-primary"
												type="submit"
											/>
										</form>
										{ link && (
											<ToggleControl
												label="Open in new window"
												checked={ newWindow }
												onChange={ () =>
													setAttributes( {
														[ `newWindow${ i }` ]: ! newWindow,
													} )
												}
											/>
										) }
									</>
								) }
							</div>
						</div>
					);
				} ) }
			</div>
		</Fragment>
	);
}
