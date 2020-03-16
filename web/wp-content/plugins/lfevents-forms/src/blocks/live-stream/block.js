//  Import CSS.
import './editor.scss';
import './style.scss';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { Fragment } = wp.element;
const { InspectorControls } = wp.blockEditor || wp.editor;
const { PanelBody, TextControl } = wp.components;

registerBlockType( 'lf/form-live-stream', {
	title: __( 'Live Stream' ),
	icon: 'list-view',
	category: 'lfe-forms',
	keywords: [
		__( 'Live Stream' ),
		__( 'Live Stream Form' ),
		__( 'Linux' ),
	],
	attributes: {
		action: {
			type: 'string',
		},
		redirectUrl: {
			type: 'string',
		},
	},
	edit: ( props ) => {
		const { attributes, setAttributes } = props;
		const { action, redirectUrl } = attributes;
		return (
			<Fragment>
				<InspectorControls>
					<PanelBody title={ __( 'Form Options' ) }>
						<TextControl
							label="Action"
							value={ action }
							onChange={ value => {
								setAttributes( { action: value } );
							} }
						/>
						<TextControl
							label="Redirect URL"
							value={ redirectUrl }
							onChange={ value => {
								setAttributes( { redirectUrl: value } );
							} }
						/>
					</PanelBody>
				</InspectorControls>
				<div className={ props.className }>
					<h4>LFEvents: Live Stream Form</h4>
				</div>
			</Fragment>
		);
	},

	save: () => null,
} );
