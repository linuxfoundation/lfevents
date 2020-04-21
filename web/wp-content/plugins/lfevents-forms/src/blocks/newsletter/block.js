//  Import CSS.
import './editor.scss';
import './style.scss';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { Fragment } = wp.element;
const { InspectorControls } = wp.blockEditor || wp.editor;
const { PanelBody, TextControl, RadioControl } = wp.components;

registerBlockType( 'lf/form-newsletter', {
	title: __( 'Newsletter Form' ),
	icon: 'list-view',
	category: 'lfe-forms',
	keywords: [
		__( 'Newsletter' ),
		__( 'Newsletter Form' ),
		__( 'Linux' ),
	],
	attributes: {
		action: {
			type: 'string',
		},
		style: {
			type: 'string',
		},
	},
	edit: ( props ) => {
		const { attributes, setAttributes } = props;
		const { action, style } = attributes;
		return (
			<Fragment>
				<InspectorControls>
					<PanelBody title={ __( 'Form Action' ) }>
						<TextControl
							value={ action }
							onChange={ value => {
								setAttributes( { action: value } );
							} }
						/>
					</PanelBody>
					<PanelBody title={ __( 'Form Style' ) }>
						<RadioControl
							label="Style"
							help="The style of the current newsletter form"
							selected={ style }
							options={ [
								{ label: 'Box', value: 'box' },
								{ label: 'Full-width', value: 'full-width' },
							] }
							onChange={ value => {
								setAttributes( { style: value } );
							} }
						/>
					</PanelBody>
				</InspectorControls>
				{ /* <div className={ props.className }>
					<h4>LFEvents: Newsletter Form</h4>
				</div> */ }
				<div className={ `lfevents-forms form-newsletter ${ style }` }>
					<div id="message"></div>

					<div class="newsletter__form">
						<label className="cell medium-6" htmlFor="FirstName">
							<input type="text" name="FirstName" placeholder="First name" required="" />
						</label>

						<label className="cell medium-6" htmlFor="LastName">
							<input type="text" name="LastName" placeholder="Last name" required="" />
						</label>
						<label htmlFor="EmailAddress">
							<input type="email" name="EmailAddress" placeholder="Email address" required="" />
						</label>

						<input className="button" type="submit" value="SIGN UP!" id="submitbtn" />
					</div>

				</div>
			</Fragment>
		);
	},

	save: () => null,
} );
