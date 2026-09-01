import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
	CheckboxControl,
	Notice,
	PanelBody,
	TextControl,
	TextareaControl,
	ToggleControl,
} from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

import metadata from './block.json';

/**
 * Providers are injected from PHP (see agent-prompt-block.php) so the editor UI
 * and the rendered output can never drift apart. The list below is only a
 * fallback for when that inline script has not run.
 */
const FALLBACK_PROVIDERS = [
	{ value: 'claude', label: 'Claude' },
	{ value: 'chatgpt', label: 'ChatGPT' },
	{ value: 'gemini', label: 'Gemini' },
	{ value: 'perplexity', label: 'Perplexity' },
	{ value: 'copilot', label: 'Microsoft Copilot' },
];

const getProviders = () =>
	Array.isArray( window.lfAgentPromptProviders ) &&
	window.lfAgentPromptProviders.length
		? window.lfAgentPromptProviders
		: FALLBACK_PROVIDERS;

export default function Edit( { attributes, setAttributes } ) {
	const { label, prompt, contextUrl, providers, showCopy } = attributes;
	const blockProps = useBlockProps();
	const available = getProviders();

	// Keep the saved order aligned with the registry order, so the avatars in
	// the pill always appear in a predictable sequence.
	const toggleProvider = ( slug, isChecked ) => {
		const next = available
			.map( ( p ) => p.value )
			.filter( ( value ) =>
				value === slug ? isChecked : providers.includes( value )
			);

		setAttributes( { providers: next } );
	};

	const hasNothingToShow = ! providers.length && ! showCopy;

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Prompt', 'agent-prompt-block' ) }>
					<TextControl
						label={ __( 'Button label', 'agent-prompt-block' ) }
						value={ label }
						onChange={ ( value ) =>
							setAttributes( { label: value } )
						}
						__nextHasNoMarginBottom
					/>
					<TextControl
						label={ __( 'Context URL', 'agent-prompt-block' ) }
						help={ __(
							'Optional. A document the assistant should read first. Leave empty to use the current page. Point this at a plain-text or Markdown export where one exists, since assistants parse those more reliably than HTML.',
							'agent-prompt-block'
						) }
						type="url"
						value={ contextUrl }
						placeholder={ __(
							'Current page',
							'agent-prompt-block'
						) }
						onChange={ ( value ) =>
							setAttributes( { contextUrl: value } )
						}
						__nextHasNoMarginBottom
					/>
					<TextareaControl
						label={ __( 'Prompt text', 'agent-prompt-block' ) }
						help={ __(
							'What the assistant should do once it has the context.',
							'agent-prompt-block'
						) }
						rows={ 8 }
						value={ prompt }
						onChange={ ( value ) =>
							setAttributes( { prompt: value } )
						}
						__nextHasNoMarginBottom
					/>
				</PanelBody>

				<PanelBody title={ __( 'Assistants', 'agent-prompt-block' ) }>
					{ available.map( ( provider ) => (
						<CheckboxControl
							key={ provider.value }
							label={ provider.label }
							checked={ providers.includes( provider.value ) }
							onChange={ ( isChecked ) =>
								toggleProvider( provider.value, isChecked )
							}
							__nextHasNoMarginBottom
						/>
					) ) }
					<ToggleControl
						label={ __(
							'Show "Copy prompt"',
							'agent-prompt-block'
						) }
						checked={ showCopy }
						onChange={ ( value ) =>
							setAttributes( { showCopy: value } )
						}
						__nextHasNoMarginBottom
					/>
					{ hasNothingToShow && (
						<Notice status="warning" isDismissible={ false }>
							{ __(
								'Select at least one assistant or enable "Copy prompt", otherwise nothing renders.',
								'agent-prompt-block'
							) }
						</Notice>
					) }
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				<ServerSideRender
					block={ metadata.name }
					attributes={ attributes }
					EmptyResponsePlaceholder={ () => (
						<Notice status="info" isDismissible={ false }>
							{ __(
								'Add a prompt and pick at least one assistant in the block settings.',
								'agent-prompt-block'
							) }
						</Notice>
					) }
				/>
			</div>
		</>
	);
}
