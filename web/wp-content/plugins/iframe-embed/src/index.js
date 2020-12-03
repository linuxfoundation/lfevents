import { registerBlockType } from '@wordpress/blocks';
import { Fragment } from '@wordpress/element';
import { Placeholder } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import './style.scss';
import './editor.scss';

import Inspector from './inspector';

registerBlockType(
	'lf/iframe-embed', {
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
		iframeType: {
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
			<div className={ `wp-lf-iframe-embed align${ align } ${ className } ` }>
				<div className="iframe-overlay"></div>
				<iframe
					title="iframe"
					id="iframe"
					src={ attributes.iframeSrc }
					style={ iframeStyle }
					frameBorder="0"></iframe></div> :
			<Placeholder
				icon={ 'welcome-view-site' }
				label={ __( 'Enter the iFrame URL you want to embed in the sidebar. ' ) }
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
				<div className={ `wp-lf-iframe-embed align${ align } ${ className } loading-bg` }>
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
