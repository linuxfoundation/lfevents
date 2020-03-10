//  Import CSS.
import './editor.scss';
import './style.scss';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { Fragment } = wp.element;
const { InspectorControls } = wp.blockEditor || wp.editor;
const { PanelBody, TextControl } = wp.components;

registerBlockType( 'lf/form-visa-request', {
	title: __( 'Visa Request Form' ),
	icon: 'list-view',
	category: 'lfe-forms',
	keywords: [
		__( 'Visa Request' ),
		__( 'Visa Request Form' ),
		__( 'Linux' ),
	],
	attributes: {
		eventId: {
			type: 'string',
		},
	},
	edit: ( props ) => {
		const { attributes, setAttributes } = props;
		const { eventId } = attributes;
		return (
			<Fragment>
				<InspectorControls>
					<PanelBody title={ __( 'Form Event ID' ) }>
						<TextControl
							value={ eventId }
							onChange={ value => {
								setAttributes( { eventId: value } );
							} }
						/>
					</PanelBody>
				</InspectorControls>
				<div className={ props.className }>
					<h4>LFEvents: Visa Request Form</h4>
				</div>
			</Fragment>
		);
	},

	save: () => null,
} );
