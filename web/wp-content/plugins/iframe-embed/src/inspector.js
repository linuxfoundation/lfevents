import { __ } from '@wordpress/i18n';
import { Component } from '@wordpress/element';
import {
	PanelBody,
	TextControl,
	ToggleControl,
	SelectControl,
} from '@wordpress/components';
import { InspectorControls, PanelColorSettings } from '@wordpress/block-editor';

/**
 * Inspector controls
 */
export default class Inspector extends Component {
	render() {
		const { attributes, setAttributes } = this.props;
		const { iframeSrc, transformedUrl, borderPresent } = attributes;

		function changeiFrame( type ) {
			if ( 'default' == type ) {
				setAttributes( {
					iframeWidth: '100%',
					iframeMaxWidth: '100%',
					iframeHeight: '700px',
					borderPresent: false,
					transformedUrl: '',
				} );
			}
			if ( 'google-sheet' == type ) {
				setAttributes( {
					iframeWidth: '100%',
					iframeMaxWidth: '500px',
					iframeHeight: '500px',
					borderPresent: true,
					borderColor: '#000000',
				} );
				parseUrl( iframeSrc );
			}
		}

		function parseUrl( url ) {
			let spreadsheetId = new RegExp(
				'/spreadsheets/d/([a-zA-Z0-9-_]+)'
			).exec( url )[ 1 ];
			let displayUrl =
				'https://docs.google.com/spreadsheets/d/' +
				spreadsheetId +
				'/htmlembed?widget=false&chrome=false&gridlines=false';
			setAttributes( {
				transformedUrl: displayUrl,
			} );
		}

		return (
			<InspectorControls key="inspector">
				<PanelBody title={ __( 'iFrame Settings' ) } initialOpen="true">
					<TextControl
						label={ __( 'iFrame source URL' ) }
						value={ iframeSrc }
						onChange={ ( value ) => {
							setAttributes( { iframeSrc: value } ),
								parseUrl( value );
						} }
					/>
					{ iframeSrc && (
						<>
							<SelectControl
								label={ __( 'Choose Type of iFrame' ) }
								help={ __(
									'Select this to set defaults for the type of iFrame you choose'
								) }
								value={ attributes.iframeType }
								options={ [
									{
										label: __( 'Default' ),
										value: 'default',
									},
									{
										label: __( 'Google Sheet' ),
										value: 'google-sheet',
									},
								] }
								onChange={ ( value ) => {
									setAttributes( { iframeType: value } ),
										changeiFrame( value );
								} }
							/>
							{ attributes.iframeType === 'google-sheet' && (
								<TextControl
									label={ __( 'Transformed URL' ) }
									value={ transformedUrl }
									help="We transform the Google Sheet URL to display it in the best way for users"
									disabled
								/>
							) }
							<TextControl
								label={ __( 'iFrame Width' ) }
								value={ attributes.iframeWidth || '100%' }
								help="Width of the iFrame - 100% normally works"
								onChange={ ( value ) => {
									setAttributes( { iframeWidth: value } );
								} }
							/>
							<TextControl
								label={ __( 'iFrame Max Width' ) }
								value={ attributes.iframeMaxWidth || '100%' }
								help="Constrain the width of the iFrame - e.g. if you don't want it full width"
								onChange={ ( value ) => {
									setAttributes( { iframeMaxWidth: value } );
								} }
							/>
							<TextControl
								label={ __( 'iFrame Height' ) }
								value={ attributes.iframeHeight || '700px' }
								help="Set the fixed height of the iFrame"
								onChange={ ( value ) => {
									setAttributes( { iframeHeight: value } );
								} }
							/>
							<ToggleControl
								label="Show Border"
								help="Add border around the iFrame"
								checked={ borderPresent }
								onChange={ () =>
									setAttributes( {
										borderPresent: ! borderPresent,
									} )
								}
							/>
							<PanelColorSettings
								title="Border Color"
								help="Control the color of the border around the iFrame"
								initialOpen={ true }
								colorSettings={ [
									{
										value: attributes.borderColor,
										onChange: ( colorValue ) =>
											setAttributes( {
												borderColor: colorValue,
											} ),
										label: 'Chosen Color',
									},
								] }
							></PanelColorSettings>
						</>
					) }
				</PanelBody>
			</InspectorControls>
		);
	}
}
