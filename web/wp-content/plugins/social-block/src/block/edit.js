/**
 * Edit screen code
 *
 * @package WordPress
 * @since 1.0.0
 *
 * @phpcs:disable WordPress.WhiteSpace.OperatorSpacing.NoSpaceAfter
 * @phpcs:disable WordPress.WhiteSpace.OperatorSpacing.NoSpaceBefore
 * @phpcs:disable Generic.WhiteSpace.ScopeIndent.IncorrectExact
 * @phpcs:disable Generic.WhiteSpace.ScopeIndent.Incorrect
 * @phpcs:disable PEAR.Functions.FunctionCallSignature.Indent
 */

const { __ } = wp.i18n;
const { Component, Fragment } = wp.element;
const { InspectorControls } = wp.blockEditor || wp.editor;
const { PanelBody, SelectControl } = wp.components;

const WeChat = ( {
	fill = '#000000',
} ) => (
	<svg xmlns="http://www.w3.org/2000/svg" aria-label="WeChat"
		role="img" viewBox="0 0 512 512" fill="#fff">
		<rect width="512" height="512" rx="15%"
			fill={ fill } />
		<path fill="#FFF"
			d="m402 369c23-17 38-42 38-70 0-51-50-92-111-92s-110 41-110 92 49 92 110 92c13 0 25-2 36-5 4-1 8 0 9 1l25 14c3 2 6 0 5-4l-6-22c0-3 2-5 4-6m-110-85a15 15 0 1 1 0-29 15 15 0 0 1 0 29m74 0a15 15 0 1 1 0-29 15 15 0 0 1 0 29" />
		<path fill="#FFF"
			d="m205 105c-73 0-132 50-132 111 0 33 17 63 45 83 3 2 5 5 4 10l-7 24c-1 5 3 7 6 6l30-17c3-2 7-3 11-2 26 8 48 6 51 6-24-84 59-132 123-128-10-52-65-93-131-93m-44 93a18 18 0 1 1 0-35 18 18 0 0 1 0 35m89 0a18 18 0 1 1 0-35 18 18 0 0 1 0 35" />
	</svg>
);

const LinkedIn = ( {
	fill = '#000000',
} ) => (
	<svg xmlns="http://www.w3.org/2000/svg" aria-label="LinkedIn"
		role="img" viewBox="0 0 512 512"
		fill={ fill } >
		<rect width="512" height="512" rx="15%"
			fill={ fill } />
		<circle cx="142" cy="138" r="37" fill="#FFF" />
		<path stroke="#FFF" strokeWidth="66"
			d="M244 194v198M142 194v198" />
		<path fill="#FFF"
			d="M276 282c0-20 13-40 36-40 24 0 33 18 33 45v105h66V279c0-61-32-89-76-89-34 0-51 19-59 32" />
	</svg>
);

const QQ = ( {
	fill = '#000000',
} ) => (
	<svg xmlns="http://www.w3.org/2000/svg" aria-label="QQ"
		role="img" viewBox="0 0 512 512">
		<rect width="512" height="512" rx="15%"
			fill={ fill } />
		<path id="svg_2"
			d="m261,398a57,32 0 0 0 114,0a57,32 0 0 0 -114,0zm-124,0a57,32 0 0 0 114,0a57,32 0 0 0 -114,0z"
			fill="#FFFFFF" />
		<path id="svg_3"
			d="m375.444366,228.559479l0,-56a119,119 0 0 0 -236.999985,0l0,56c-18.000015,25 -35.000015,55 -37.000015,78c0,44 13,40 13,40c5,0 15.000015,-9 23.000015,-20c19,55 65,93 119.999985,93s101,-38 120,-93c8,11 18,20 23,20c0,0 13,4 13,-40c0,-23 -17,-54 -37,-78l-1,0z"
			fill="#FFFFFF" />
		<path id="svg_6"
			d="m371.717529,208a235,225 0 0 1 -230.000015,0c-10,-7 -15.000008,-2 -19.000008,12s-6,18 6.000008,26l32,15c-6,32 -5,63 -5,65c1,13 12,12 27,12c14,-1 26,0 26,-15c0,-8 0,-27 3,-46c15,3 29,5 46.000015,5c67,0 126,-35 127,-36l-13,-38z"
			fill="#FFFFFF" />
	</svg>
);

const YouTube = ( {
	fill = '#000000',
} ) => (
	<svg fill={ fill } viewBox="0 0 512 512"
		xmlns="http://www.w3.org/2000/svg">
		<rect height="512" rx="15%" width="512" />
		<path
			d="m427 169c-4-15-17-27-32-31-34-9-239-10-278 0-15 4-28 16-32 31-9 38-10 135 0 174 4 15 17 27 32 31 36 10 241 10 278 0 15-4 28-16 32-31 9-36 9-137 0-174"
			fill="#FFF" />
		<path d="m220 203v106l93-53" /></svg>
);

const Facebook = ( {
	fill = '#000000',
} ) => (
	<svg xmlns="http://www.w3.org/2000/svg" aria-label="Facebook"
		role="img" viewBox="0 0 512 512">
		<rect width="512" height="512" rx="15%"
			fill={ fill } />
		<path fill="#FFF"
			d="M355.6 330l11.4-74h-71v-48c0-20.2 9.9-40 41.7-40H370v-63s-29.3-5-57.3-5c-58.5 0-96.7 35.4-96.7 99.6V256h-65v74h65v182h80V330h59.6z" />
	</svg>
);

const Twitter = ( {
	fill = '#000000',
} ) => (
	<svg xmlns="http://www.w3.org/2000/svg" aria-label="Twitter"
		role="img" viewBox="0 0 512 512">
		<rect width="512" height="512" rx="15%"
			fill={ fill } />
		<path fill="#fff"
			d="M437 152a72 72 0 0 1-40 12 72 72 0 0 0 32-40 72 72 0 0 1-45 17 72 72 0 0 0-122 65 200 200 0 0 1-145-74 72 72 0 0 0 22 94 72 72 0 0 1-32-7 72 72 0 0 0 56 69 72 72 0 0 1-32 1 72 72 0 0 0 67 50 200 200 0 0 1-105 29 200 200 0 0 0 309-179 200 200 0 0 0 35-37" />
	</svg>
);

const Instagram = ( {
	fill = '#000000',
} ) => (
	<svg viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg">
		<rect height="512" rx="15%" width="512"
			fill={ fill } />
		<g fill="none" stroke="#fff" strokeWidth="29">
			<rect height="296" rx="78" width="296" x="108"
				y="108" />
			<circle cx="256" cy="256" r="69" />
		</g>
		<circle cx="343" cy="169" fill="#fff" r="19" />
	</svg>
);

class SocialBlock extends Component {
	render() {
		const { attributes, setAttributes } = this.props;
		const { iconColor, menu_color_1, menu_color_2, menu_color_3, wechat_url, linkedin_url, qq_url, youtube_url, facebook_url, twitter_url, instagram_url } = attributes;

		const inspectorControls = (
			<InspectorControls key="social-block-panel">
				<PanelBody title={ __( 'Settings' ) } initialOpen={ true }>

					<SelectControl
						label={ __( 'Icon Color' ) }
						value={ iconColor }
						options={ [
							{
								label: __( 'Default (Black)' ),
								value: '#000000',
							},
							{
								label: __( 'White' ),
								value: '#FFFFFF',
							},
							{
								label: __( 'Menu Color 1' ),
								value: menu_color_1,
							},
							{
								label: __( 'Menu Color 2' ),
								value: menu_color_2,
							},
							{
								label: __( 'Menu Color 3' ),
								value: menu_color_3,
							},
						] }
						onChange={ value =>
							setAttributes( { iconColor: '' !== value ? value : '' } )
	  }
					/>
				</PanelBody>
			</InspectorControls>
		);

		return (
			<Fragment>
				{ inspectorControls }
				<div className={ this.props.className }>
					<ul className="social-block-icon-wrapper">
						{ wechat_url ?
							<WeChat fill={ iconColor } /> : ''
						}
						{ linkedin_url &&
						<LinkedIn fill={ iconColor } />
						}
						{ qq_url &&
						<QQ fill={ iconColor } />
						}
						{ youtube_url &&
						<YouTube fill={ iconColor } />
						}
						{ facebook_url &&
						<Facebook fill={ iconColor } />
						}
						{ twitter_url &&
						<Twitter fill={ iconColor } />
						}
						{ instagram_url &&
						<Instagram fill={ iconColor } />
						}

					</ul>
				</div>
			</Fragment>
		);
	}
}

export default SocialBlock;
