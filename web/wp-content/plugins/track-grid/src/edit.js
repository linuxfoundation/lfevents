import { __ } from '@wordpress/i18n';
import {
	RichText,
	InspectorControls,
	URLInput,
	PanelColorSettings,
} from '@wordpress/block-editor';
import {
	Button,
	PanelBody,
	PanelRow,
	RadioControl,
	RangeControl,
	SelectControl,
	ToggleControl,
} from '@wordpress/components';
import { Fragment } from '@wordpress/element';
import './editor.scss';

// import helper functions.
import { range } from './utils.js';

export default function Edit( {
	attributes,
	setAttributes,
	className,
	isSelected,
} ) {
	const {
		tracks,
		columns,
		height,
		color1,
		color2,
		textColor,
		ctaIcon,
		align,
		showKeynote,
		keynoteText,
		keynoteLink,
		keynoteNewWindow,
	} = attributes;

	const inspectorControls = (
		<InspectorControls key="lf-track-grid-panel">
			<PanelBody title={ __( 'Settings' ) } initialOpen={ true }>
				<RangeControl
					label={ __( 'No. of tracks' ) }
					min={ 1 }
					max={ 30 }
					value={ tracks }
					onChange={ ( value ) =>
						setAttributes( { tracks: Number( value ) } )
					}
				/>
				<RangeControl
					label={ __( 'No. of columns' ) }
					min={ 1 }
					max={ 4 }
					value={ columns }
					onChange={ ( value ) =>
						setAttributes( { columns: Number( value ) } )
					}
				/>
				<RangeControl
					label={ __( 'Min height of boxes' ) }
					min={ 100 }
					max={ 800 }
					step={ 10 }
					value={ height }
					onChange={ ( value ) =>
						setAttributes( { height: Number( value ) } )
					}
				/>
				<SelectControl
					label={ __( 'CTA icon' ) }
					value={ ctaIcon }
					options={ [
						{
							label: __( 'Nothing' ),
							value: '',
						},
						{
							label: __( 'Double Angle Right' ),
							value: 'is-style-track-double-angle-right',
						},
						{
							label: __( 'View Track' ),
							value: 'view-track',
						},
					] }
					onChange={ ( value ) =>
						setAttributes( { ctaIcon: '' !== value ? value : '' } )
					}
				/>
				<PanelRow>
					<ToggleControl
						label="Show Keynote Speaker CTA"
						checked={ showKeynote }
						onChange={ () =>
							setAttributes( {
								showKeynote: ! showKeynote,
							} )
						}
					/>
				</PanelRow>
			</PanelBody>
			<PanelColorSettings
				title="Text & Background Colors"
				initialOpen={ true }
				description="Pick the colors for the background of this block.Pick the same colors for solid color background."
				colorSettings={ [
					{
						value: color1,
						onChange: ( colorValue ) =>
							setAttributes( {
								color1: colorValue,
							} ),
						label: 'Color 1',
					},
					{
						value: color2,
						onChange: ( colorValue ) =>
							setAttributes( {
								color2: colorValue,
							} ),
						label: 'Color 2',
					},
				] }
			></PanelColorSettings>
			<PanelBody>
				<PanelRow>
					<RadioControl
						label="Text Color"
						selected={ textColor }
						onChange={ ( value ) =>
							setAttributes( { textColor: value } )
						}
						options={ [
							{ label: 'White Text', value: '#FFFFFF' },
							{ label: 'Black Text', value: '#000000' },
						] }
					/>
				</PanelRow>
			</PanelBody>
		</InspectorControls>
	);

	const mainStyle = {
		'--track-height': height ? `${ height }px` : '250px',
		'--track-color1': color1,
		'--track-color2': color2,
		'--track-text-color': textColor,
	};

	return (
		<Fragment>
			{ inspectorControls }
			<div
				style={ mainStyle }
				className={ `wp-block-lf-track-grid in-editor align${
					align ? align : 'wide'
				}` }
			>
				<ul className={ `track-wrapper columns-${ columns }` }>
					{ range( 1, tracks ).map( ( i ) => {
						const title = attributes[ `title${ i }` ];
						const link = attributes[ `link${ i }` ];
						const newWindow = attributes[ `newWindow${ i }` ];

						return (
							<li
								className={ `track-box track-style box-${ i } ${
									className ? className : ''
								}` }
								key={ i }
							>
								<RichText
									tagName="h4"
									value={ title }
									onChange={ ( newTitle ) =>
										setAttributes( {
											[ `title${ i }` ]: newTitle,
										} )
									}
									placeholder={ __( 'Track title' ) }
								/>
								{ isSelected && (
									<>
										<form
											className="block-editor-media-placeholder__url-input-form"
											onSubmit={ ( event ) =>
												event.preventDefault()
											}
										>
											<URLInput
												value={ link }
												className="block-editor-media-placeholder__url-input-field"
												onChange={ ( newLink ) => {
													setAttributes( {
														[ `link${ i }` ]: newLink,
													} );
												} }
												placeholder="Add Link"
											/>
											<Button
												icon="editor-break"
												label={ __( 'Apply' ) }
												className="block-editor-media-placeholder__url-input-submit-button is-primary"
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
						<RichText
							tagName="h4"
							value={ keynoteText }
							onChange={ ( value ) =>
								setAttributes( { keynoteText: value } )
							}
							placeholder="View our Keynote Speakers"
						/>
						{ ctaIcon && (
							<h3 className="track-cta is-style-track-double-angle-right">
								&gt;&gt;
							</h3>
						) }
						{ isSelected && (
							<>
								<form
									className="block-editor-media-placeholder__url-input-form"
									onSubmit={ ( event ) =>
										event.preventDefault()
									}
								>
									<URLInput
										value={ keynoteLink }
										className="block-editor-media-placeholder__url-input-field"
										onChange={ ( value ) => {
											setAttributes( {
												keynoteLink: value,
											} );
										} }
										placeholder="Add Link"
									/>
									<Button
										icon="editor-break"
										label={ __( 'Apply' ) }
										className="is-primary"
										type="submit"
									/>
								</form>
								{ keynoteLink && (
									<ToggleControl
										label="Open in new window"
										checked={ keynoteNewWindow }
										onChange={ ( value ) => {
											setAttributes( {
												keynoteNewWindow: Boolean(
													value
												),
											} );
										} }
									/>
								) }
							</>
						) }
					</div>
				) }
			</div>
		</Fragment>
	);
}
