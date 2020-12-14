import { registerBlockType } from '@wordpress/blocks';
import { Fragment } from '@wordpress/element';
import { Placeholder } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import './style.scss';
import './editor.scss';

import Inspector from './inspector';

registerBlockType( 'lf/iframe-embed', {
	title: __( 'iFrame Embed' ),
	description: __( 'Embed an iFrame (includes settings for Google Sheets)' ),
	category: 'common',
	icon: 'welcome-view-site',
	keywords: [
		__( 'iframe' ),
		__( 'google' ),
		__( 'docs' ),
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
		const { align, className } = attributes;

		const iframeStyle = {
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

		const block = attributes.transformedUrl ? (
			<div
				className={ `wp-lf-iframe-embed align${ align } ${
					className ? className : ''
				} ` }
			>
				<div className="iframe-overlay"></div>
				<iframe
					title="iframe"
					id="iframe"
					src={ attributes.transformedUrl }
					style={ iframeStyle }
					frameBorder="0"
				></iframe>
			</div>
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
				{ block }
			</Fragment>
		);
	},

	save: ( props ) => {
		const { attributes } = props;
		const { align, className } = attributes;

		const iframeStyle = {
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
					className={ `wp-lf-iframe-embed align${ align } ${
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
