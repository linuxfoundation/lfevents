//  Import CSS.
import './editor.scss';
import './style.scss';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { Fragment } = wp.element;
const { InspectorControls } = wp.blockEditor || wp.editor;
const { PanelBody, TextControl } = wp.components;

registerBlockType( 'lf/form-newsletter', {
	title: __( 'Newsletter Form' ),
	icon: 'list-view',
	category: 'common',
	keywords: [
		__( 'Newsletter' ),
		__( 'Newsletter Form' ),
		__( 'Linux' ),
	],
	attributes: {
		action: {
			type: 'string',
		},
	},
	edit: ( props ) => {
		const { attributes, setAttributes } = props;
		const { action } = attributes;
		return (
			<Fragment>
				<InspectorControls>
					<PanelBody title={ __( 'Form action' ) }>
						<TextControl
							value={ action }
							onChange={ value => {
								setAttributes( { action: value } );
							} }
						/>
					</PanelBody>
				</InspectorControls>
				<div className={ props.className }>
					<h4>LFEvents: Newsletter Form</h4>
				</div>
			</Fragment>
		);
	},

	save: () => null,
} );
