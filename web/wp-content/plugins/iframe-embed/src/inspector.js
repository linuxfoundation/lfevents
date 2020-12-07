import { __ } from '@wordpress/i18n';
import { Component } from '@wordpress/element';
import {
	PanelBody,
	TextControl,
	ToggleControl,
	SelectControl,
} from '@wordpress/components';
import {
	InspectorControls,
	PanelColorSettings,
	RichText,
} from '@wordpress/block-editor';

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
					iframeMaxWidth: '100%',
					iframeHeight: '700px',
					borderPresent: false,
				} );
			}
			if ( 'google-sheet' == type ) {
				setAttributes( {
					iframeMaxWidth: '500px',
					iframeHeight: '500px',
					borderPresent: true,
					borderColor: '#000000',
				} );
			}
		}

		function isValidWebUrl( url ) {
			let regEx = /^https?:\/\/(?:www\.)?[-a-zA-Z0-9@:%._\+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()@:%_\+.~#?&//=]*)$/gm;
			return regEx.test( url );
		}

		function processUrl( url ) {
			if ( ! url ) {
				setAttributes( {
					transformedUrl: '',
				} );
				return
			};
			if ( ! isValidWebUrl( url ) ) return;

			if ( transformedUrl ) {
				setAttributes( {
					transformedUrl: '',
				} );
			}

			// setup the regex to check against.
			let shareSheet = '/spreadsheets/d/([a-zA-Z0-9-_]+)';
			let publishSheet = '/spreadsheets/d/e/([a-zA-Z0-9-_]+)';
			let isShareSheet = new RegExp( shareSheet );
			let isPublishSheet = new RegExp( publishSheet );

			if ( url.match( isPublishSheet ) ) {
				let publishId = isPublishSheet.exec( url )[ 1 ];
				let publishUrl =
					'https://docs.google.com/spreadsheets/d/e/' +
					publishId +
					'/pubhtml?widget=false&chrome=false&gridlines=false';
				setAttributes( {
					transformedUrl: publishUrl,
				} );
			} else if ( url.match( isShareSheet ) ) {
				let shareId = isShareSheet.exec( url )[ 1 ];
				let shareUrl =
					'https://docs.google.com/spreadsheets/d/' +
					shareId +
					'/htmlembed?widget=false&chrome=false&gridlines=false';
				setAttributes( {
					transformedUrl: shareUrl,
				} );
			} else {
				setAttributes( {
					transformedUrl: url,
				} );
			}
		}

		return (
			<InspectorControls key="inspector">
				<PanelBody title={ __( 'iFrame Settings' ) } initialOpen="true">
					<TextControl
						label={ __( 'iFrame source URL' ) }
						value={ iframeSrc }
						placeholder={ __( 'https://your-embed.com' ) }
						onChange={ ( value ) => {
							setAttributes( { iframeSrc: value } ),
								processUrl( value );
						} }
					/>
					{ iframeSrc && ! isValidWebUrl( iframeSrc ) && (
						<>
							<RichText
								value={ __(
									"This doesn't look like a valid URL"
								) }
								className="url-warning"
							/>
						</>
					) }
					{ transformedUrl && (
						<>
							<SelectControl
								label={ __( 'iFrame Preset Styles' ) }
								help={ __(
									'Pick a preset style for your iFrame'
								) }
								value={ attributes.iframeType }
								options={ [
									{
										label: __( '100% width, no border' ),
										value: 'default',
									},
									{
										label: __(
											'Max 500px with 1px border'
										),
										value: 'google-sheet',
									},
								] }
								onChange={ ( value ) => {
									setAttributes( { iframeType: value } ),
									changeiFrame( value );
								} }
							/>
							<TextControl
								label={ __( 'iFrame Max Width' ) }
								value={ attributes.iframeMaxWidth || '100%' }
								help="Constrain the width of the iFrame. Accepts px or % values."
								onChange={ ( value ) => {
									setAttributes( { iframeMaxWidth: value } );
								} }
							/>
							<TextControl
								label={ __( 'iFrame Height' ) }
								value={ attributes.iframeHeight || '700px' }
								help="Set the fixed height of the iFrame. Only accepts px values."
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
