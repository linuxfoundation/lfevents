import { registerBlockType } from '@wordpress/blocks';
import { Fragment } from '@wordpress/element';
import { Placeholder } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import './style.scss';
import './editor.scss';

import Inspector from './inspector';

registerBlockType( 'lf/custom-iframe', {
	title: __( 'Custom iFrame', 'custom-iframe' ),
	description: __(
		'Embed an iframe, Google Sheet or Newsletter with better formatting',
		'custom-iframe'
	),
	category: 'common',
	icon: 'welcome-view-site',
	keywords: [
		__( 'iframe' ),
		__( 'google' ),
		__( 'spreadsheet' ),
		__( 'embed' ),
		__( 'sheet' ),
	],
	supports: {
		align: [ 'wide', 'full' ],
		html: false,
	},
	attributes: {
		iframeSrc: {
			type: 'string',
		},
		iframeType: {
			type: 'string',
			default: 'default',
		},
		transformedUrl: {
			type: 'string',
		},
		iframeMaxWidth: {
			type: 'string',
		},
		iframeHeight: {
			type: 'string',
		},
		borderColor: {
			type: 'string',
		},
		borderPresent: {
			type: 'boolean',
			default: false,
		},
		rightPadding: {
			type: 'boolean',
			default: false,
		},
		align: {
			type: 'string',
			default: 'wide',
		},
		className: {
			type: 'string',
		},
	},
	edit: ( props ) => {
		const { attributes } = props;
		const { align, className, transformedUrl } = attributes;

		const iframeStyle = {
			width: '100%',
			maxWidth: attributes.iframeMaxWidth || '100%',
			height: attributes.iframeHeight || '700px',
			borderColor: attributes.borderColor || '#000000',
			...( attributes.borderPresent && {
				borderWidth: '1px',
				borderStyle: 'solid',
				paddingTop: '1rem',
				paddingLeft: '1rem',
				paddingBottom: '1rem',
				paddingRight: '1rem',
			} ),
			...( attributes.rightPadding && {
				paddingRight: '0rem',
			} ),
		};

		const blockContent = transformedUrl ? (
			<article
				className={ `wp-lf-custom-iframe align${ align } ${
					className ? className : ''
				} ` }
			>
				<section className="iframe-overlay"></section>
				<iframe
					title="iframe"
					id="iframe"
					src={ transformedUrl }
					style={ iframeStyle }
					frameBorder="0"
				></iframe>
			</article>
		) : (
			<Placeholder
				icon={ 'welcome-view-site' }
				label={ __(
					'Enter the iFrame URL you want to embed in the sidebar. '
				) }
			/>
		);

		return (
			<Fragment>
				<Inspector { ...props } />
				{ blockContent }
			</Fragment>
		);
	},

	save: ( props ) => {
		const { attributes } = props;
		const { align, className } = attributes;

		const iframeStyle = {
			width: '100%',
			maxWidth: attributes.iframeMaxWidth || '100%',
			height: attributes.iframeHeight || '700px',
			borderColor: attributes.borderColor || '#000000',
			...( attributes.borderPresent && {
				borderWidth: '1px',
				borderStyle: 'solid',
				paddingTop: '1rem',
				paddingLeft: '1rem',
				paddingBottom: '1rem',
				paddingRight: '1rem',
			} ),
			...( attributes.rightPadding && {
				paddingRight: '0rem',
			} ),
		};

		return (
			<Fragment>
				<div
					className={ `wp-lf-custom-iframe align${ align } ${
						className ? className : ''
					} loading-bg` }
				>
					<iframe
						title="iframe"
						id="iframe"
						src={ attributes.transformedUrl }
						style={ iframeStyle }
						frameBorder="0"
						scrolling="yes"
					></iframe>
				</div>
			</Fragment>
		);
	},
} );
