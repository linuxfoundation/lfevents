/**
 * BLOCK: button-with-expiry-block
 *
 * Registering a basic block with Gutenberg.
 * Simple block, renders and saves the same content without any interactivity.
 */

//  Import CSS.
import './editor.scss';
import './style.scss';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { Fragment } = wp.element;
const {
	InspectorControls,
	PanelColorSettings,
	RichText,
	URLInput,
} = wp.blockEditor || wp.editor;
const { PanelBody, DateTimePicker } = wp.components;

registerBlockType( 'lf/button-with-expiry', {
	title: __( 'Button With Expiry' ),
	icon: 'share-alt2',
	category: 'common',
	keywords: [
		__( 'Button With Expiry' ),
		__( 'Linux' ),
	],
	attributes: {
		text: {
			type: 'string',
		},
		backgroundColor: {
			type: 'string',
		},
		textColor: {
			type: 'string',
		},
		expireAt: {
			type: 'number',
			default: 60 * ( 1440 + Math.ceil( Date.now() / 60000 ) ), // 24 hours from Date.now
		},
		link: {
			type: 'string',
		},
	},

	edit: ( props ) => {
		const { attributes, setAttributes } = props;
		const { text, backgroundColor, textColor, expireAt, link } = attributes;
		const styles = {
			backgroundColor: backgroundColor,
			color: textColor,
		};

		return (
			<Fragment>
				<InspectorControls>
					<PanelColorSettings
						title="Color Settings"
						initialOpen={ true }
						colorSettings={ [
							{
								value: backgroundColor,
								onChange: colorValue =>
									setAttributes( {
										backgroundColor: colorValue,
									} ),
								label: 'Background Color',
							},
							{
								value: textColor,
								onChange: colorValue =>
									setAttributes( {
										textColor: colorValue,
									} ),
								label: 'Text Color',
							},
						] }
					>
					</PanelColorSettings>
					<PanelBody title={ __( 'Expire Date' ) }>
						<DateTimePicker
							currentDate={ expireAt * 1000 }
							onChange={ value => {
								setAttributes( {
									expireAt: Math.floor( Date.parse( value ) / 1000 ),
								} );
							} }
						/>
					</PanelBody>
				</InspectorControls>
				<div className="wp-block-button-with-expiry">
					<RichText
						tagName="div"
						className="button button-text"
						style={ styles }
						value={ text }
						onChange={ ( value ) => setAttributes( { text: value } ) }
						placeholder={ __( 'Button text...' ) }
						keepPlaceholderOnFocus={ true }
						formattingControls={ [] }
					/>
					<URLInput
						className="button-url"
						value={ link }
						onChange={ value => setAttributes( { link: value } ) }
					/>
				</div>
			</Fragment>
		);
	},
	save: () => null,
} );
