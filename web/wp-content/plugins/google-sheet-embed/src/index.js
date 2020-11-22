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
		__( 'docs' ),
		__( 'iframe' ),
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
		iframeWidth: {
			type: 'string',
		},
		iframeHeight: {
			type: 'string',
		},
		iframeMaxWidth: {
			type: 'string',
		},
		borderColor: {
			type: 'string',
		},
		borderWidth: {
			type: 'number',
			default: 0,
		},
		align: {
			type: 'string',
			default: 'wide',
		},
		className: {
			type: 'string',
		},
	},
	edit: function( props ) {

		const { attributes } = props;
		const { align, className } = attributes;

		const iframeStyle = {
			width: attributes.iframeWidth || '100%',
			height: attributes.iframeHeight || '500px',
			maxWidth: attributes.iframeMaxWidth || '90%',
			borderWidth: attributes.borderWidth || '0',
			borderColor: attributes.borderColor || '#000000',
			borderStyle: 'solid',
			padding: '1rem',
		};


		const block = attributes.iframeSrc ?
			<div className={ `wp-lf-google-sheet-embed align${ align } ${ className } ` }>
				<div className="iframe-overlay"></div>
				<iframe
					title="iframe"
					id="iframe"
					src={ attributes.iframeSrc }
					style={ iframeStyle }
					frameBorder="0"></iframe></div> :
			<Placeholder
				icon={ 'welcome-view-site' }
				label={ __( 'Enter the Google Sheet URL to embed in the sidebar. The sheet should be set to viewable by public (view only).' ) }
			/>;

		return (
			<Fragment>
				<Inspector { ...props } />
				{ block }
			</Fragment>
		);
	},

	save: function( props ) {
		const { attributes } = props;
		const { align, className } = attributes;

	const iframeStyle = {
			width: attributes.iframeWidth || '100%',
			height: attributes.iframeHeight || '500px',
			maxWidth: attributes.iframeMaxWidth || '90%',
			borderWidth: attributes.borderWidth || '0',
			borderColor: attributes.borderColor || '#000000',
			borderStyle: 'solid',
			padding: '1rem',
		};

		return (
			<Fragment>
				<div className={ `wp-lf-google-sheet-embed align${ align } ${ className } loading-bg` }>
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
