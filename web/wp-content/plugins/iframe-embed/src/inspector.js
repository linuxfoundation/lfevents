import { __ } from '@wordpress/i18n';
import { Component } from '@wordpress/element';
import { PanelBody, TextControl, RangeControl, SelectControl } from '@wordpress/components';
import { InspectorControls, PanelColorSettings } from '@wordpress/block-editor';

/**
 * Inspector controls
 */
export default class Inspector extends Component {
	render() {
		const { attributes, setAttributes } = this.props;

		return (
			<InspectorControls key="inspector">
				<PanelBody title={ __( 'Settings' ) }
					initialOpen="true" >
					<TextControl
						label={ __( 'iFrame source URL' ) }
						value={ attributes.iframeSrc }
						onChange={ ( value ) => {
							setAttributes( { iframeSrc: value } );
						} }
					/>
<SelectControl
					label={ __( 'Choose Type of iFrame' ) }
					help={ __( 'Select this to set defaults for the type of iFrame you choose' ) }
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
					onChange={ ( value ) =>
						setAttributes( { iframeType: value } ),
						changeSettings ( value )
					}
				/>
					<TextControl
						label={ __( 'iFrame Width' ) }
						value={ attributes.iframeWidth }
						help="Set this if the default options don't work i.e. 100%"
						onChange={ ( value ) => {
							setAttributes( { iframeWidth: value } );
						} }
					/>
					<TextControl
						label={ __( 'iFrame Max Width' ) }
						value={ attributes.iframeMaxWidth }
						help="Set this if the default options don't work i.e. 100%"
						onChange={ ( value ) => {
							setAttributes( { iframeMaxWidth: value } );
						} }
					/>
					<TextControl
						label={ __( 'iFrame Height' ) }
						value={ attributes.iframeHeight }
						help="Set this if the default options don't work i.e. 1000px"
						onChange={ ( value ) => {
							setAttributes( { iframeHeight: value } );
						} }
					/>
					<RangeControl
						label={ __( 'Border Width' ) }
						min={ 0 }
						max={ 20 }
						value={ attributes.borderWidth }
						onChange={ value => setAttributes( { borderWidth: value } ) }
					/>
					<PanelColorSettings
					title="Border Color"
					initialOpen={ true }
					colorSettings={ [
						{
							value: attributes.borderColor,
							onChange: colorValue =>
								setAttributes( {
									borderColor: colorValue,
								} ),
							label: 'Chosen Color',
						},
					] }
				></PanelColorSettings>
				</PanelBody>
			</InspectorControls>
		);
	}
}
