import { registerBlockType } from '@wordpress/blocks';
import { Fragment } from '@wordpress/element';
import { Placeholder } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import './style.scss';
import './editor.scss';

import Inspector from './inspector';

registerBlockType(
	'lf/google-sheet-embed', {
	title: __( 'Google Sheet Embed' ),
	description: __( 'Embed a Google Sheet' ),
	category: 'common',
	icon: 'welcome-view-site',
	keywords: [
		__( 'google' ),
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
		iframeWidth: {
			type: 'string',
			default: '100%',
		},
		iframeHeight: {
			type: 'string',
			default: '500px',
		},
		iframeMaxWidth: {
			type: 'string',
			default: '90%',
		},
		align: {
			type: 'string',
			default: 'wide',
		},
	},
	edit: function( props ) {
		const { attributes, className } = props;
		const { align } = attributes;

		const iframeStyle = {
			width: attributes.iframeWidth || '100%',
			height: attributes.iframeHeight || '500px',
			maxWidth: attributes.iframeMaxWidth || '90%',
		};

		const block = attributes.iframeSrc ?
			<div className={ `align${ align } ${ className }` }>
				<div className="iframe-overlay"></div>
				<iframe
					title="iframe"
					id="iframe"
					src={ attributes.iframeSrc }
					style={ iframeStyle }
					frameBorder="0"></iframe></div> :
			<Placeholder
				icon={ 'welcome-view-site' }
				label={ __( 'Enter the Google Sheey URL to embed in the sidebar. The sheet should be set to viewable by public (view only).' ) }
			/>;

		return (
			<Fragment>
				<Inspector { ...props } />
				{ block }
			</Fragment>
		);
	},

	save: function( props ) {
		const { attributes, className } = props;
		const { align } = attributes;

		const iframeStyle = {
			width: attributes.iframeWidth || '100%',
			height: attributes.iframeHeight || '500px',
			maxWidth: attributes.iframeMaxWidth || '90%',
		};

		return (
			<Fragment>
				<div className={ `align${ align } ${ className } loading-bg` }>
					<iframe
						title="iframe"
						id="iframe"
						src={ attributes.iframeSrc }
						style={ iframeStyle }
						frameBorder="0"
						scrolling="yes"
					></iframe>
				</div>
			</Fragment>
		);
	},
} );
