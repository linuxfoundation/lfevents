import { __ } from '@wordpress/i18n';
import {
	RichText,
	InspectorControls,
	URLInput,
	PanelColorSettings,
} from '@wordpress/block-editor';
import {
	PanelBody,
	PanelRow,
	RangeControl,
	SelectControl,
	Dashicon,
	RadioControl,
} from '@wordpress/components';
import { Fragment } from '@wordpress/element';

import './editor.scss';

// import helper functions.
import { range } from './utils.js';

export default function Edit( { attributes, setAttributes, className } ) {
	const {
		tracks,
		columns,
		height,
		color1,
		color2,
		textColor,
		ctaIcon,
		align,
	} = attributes;

	const inspectorControls = (
		<InspectorControls key="lf-track-grid-panel">
			<PanelBody title={ __( 'Settings' ) } initialOpen={ true }>
				<RangeControl
					label={ __( 'No. of tracks' ) }
					min={ 1 }
					max={ 30 }
					value={ tracks }
					onChange={ ( value ) => setAttributes( { tracks: value } ) }
				/>
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
					label={ __( 'Min height of boxes' ) }
					min={ 100 }
					max={ 800 }
					step={ 10 }
					value={ height }
					onChange={ ( value ) => setAttributes( { height: value } ) }
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
				className={ `track-wrapper wp-block-lf-track-grid in-editor align${
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
							<RichText
								tagName="h4"
								value={ title }
								onChange={ ( title ) =>
									setAttributes( {
										[ `title${ i }` ]: title,
									} )
								}
								placeholder={ __( 'Track title' ) }
							/>
							<form
								className="blocks-button__inline-link"
								onSubmit={ ( event ) => event.preventDefault() }
							>
								<Dashicon
									icon="admin-links"
									style={ { marginRight: '5px' } }
								/>
								<URLInput
									value={ link }
									className="components-base-control__field"
									onChange={ ( link ) => {
										setAttributes( {
											[ `link${ i }` ]: link,
										} );
									} }
								/>
							</form>
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
			</div>
		</Fragment>
	);
}
