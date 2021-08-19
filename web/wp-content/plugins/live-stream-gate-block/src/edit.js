import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import {
	BlockControls,
	PlainText,
	transformStyles,
	InspectorControls,
	useBlockProps,
	store as blockEditorStore,
} from '@wordpress/block-editor';
import {
	ToolbarButton,
	Dashicon,
	Disabled,
	SandBox,
	ToolbarGroup,
	PanelBody,
	ToggleControl,
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';

import './editor.scss';

export default function Edit( { attributes, setAttributes, isSelected } ) {
	const [ isPreview, setIsPreview ] = useState();

	const styles = useSelect( ( select ) => {
		const defaultStyles = `
			html,body,:root {
				margin: 0 !important;
				padding: 0 !important;
				overflow: visible !important;
				min-height: auto !important;
			}
		`;

		return [
			defaultStyles,
			...transformStyles(
				select( blockEditorStore ).getSettings().styles
			),
		];
	}, [] );

	function switchToPreview() {
		setIsPreview( true );
	}

	function switchToHTML() {
		setIsPreview( false );
	}

	return (
		<div
			{ ...useBlockProps( {
				className: 'components-placeholder wp-block-embed is-large',
			} ) }
		>
			<div className="components-placeholder__label">
				<span className="block-editor-block-icon has-colors">
					<Dashicon icon="embed-video" />
				</span>
				Live Stream Gate
			</div>
			<div className="components-placeholder__instructions">
				Paste your embed code below, it will be hidden behind an LF SSO
				gate unless the user is logged in. The SSO check can be disabled in the block Settings sidebar.
			</div>
			<BlockControls>
				<ToolbarGroup>
					<ToolbarButton
						className="components-tab-button"
						isPressed={ ! isPreview }
						onClick={ switchToHTML }
					>
						<span>Embed</span>
					</ToolbarButton>
					<ToolbarButton
						className="components-tab-button"
						isPressed={ isPreview }
						onClick={ switchToPreview }
					>
						<span>{ __( 'Preview' ) }</span>
					</ToolbarButton>
				</ToolbarGroup>
			</BlockControls>
			<InspectorControls key="live-stream-gate-block-panel">
				<PanelBody title={ __( 'Settings' ) } initialOpen={ true }>
					<ToggleControl
						label="Disable SSO Check"
						checked={ attributes.ssoDisabled }
						onChange={ () =>
							setAttributes( {
								ssoDisabled: ! attributes.ssoDisabled,
							} )
						}
						help={
							attributes.ssoDisabled
								? 'SSO check is disabled.'
								: 'SSO check is enabled.'
						}
					/>
				</PanelBody>
			</InspectorControls>
			<Disabled.Consumer>
				{ ( isDisabled ) =>
					isPreview || isDisabled ? (
						<>
							<SandBox
								html={ attributes.content }
								styles={ styles }
							/>
							{ /* Overlay to stop preview being selected  */ }
							{ ! isSelected && (
								<div className="block-library-html__preview-overlay"></div>
							) }
						</>
					) : (
						<pre>
							<PlainText
								value={ attributes.content }
								onChange={ ( content ) =>
									setAttributes( { content } )
								}
								placeholder={ __(
									'Paste your embed code here'
								) }
								aria-label={ __(
									'Paste your embed code here'
								) }
							/>
						</pre>
					)
				}
			</Disabled.Consumer>
		</div>
	);
}
