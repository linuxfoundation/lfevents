import { __ } from '@wordpress/i18n';
import { Component } from '@wordpress/element';
import { PanelBody, TextControl } from '@wordpress/components';
import { InspectorControls } from '@wordpress/block-editor';

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
						label={ __( 'Google Sheet URL' ) }
						value={ attributes.iframeSrc }
						placeholder="https://wwww.google.com"
						onChange={ ( value ) => {
							setAttributes( { iframeSrc: value } );
						} }
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
				</PanelBody>
			</InspectorControls>
		);
	}
}
