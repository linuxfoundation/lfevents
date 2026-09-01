<?php
/**
 * Registry of supported AI assistants.
 *
 * Each entry defines how to build a "deep link" that opens the assistant with
 * the prompt already typed into its composer.
 *
 * @package WordPress
 */

defined( 'ABSPATH' ) || exit;

/**
 * Returns the supported assistants keyed by slug.
 *
 * `url` is a sprintf template where `%s` is replaced with the URL-encoded prompt.
 *
 * @return array<string, array<string, string>>
 */
function lf_agent_prompt_get_providers() {
	$providers = array(
		'claude'     => array(
			'label' => __( 'Claude', 'agent-prompt-block' ),
			'url'   => 'https://claude.ai/new?q=%s',
			'icon'  => 'claude',
			'color' => '#D97757',
		),
		'chatgpt'    => array(
			'label' => __( 'ChatGPT', 'agent-prompt-block' ),
			'url'   => 'https://chatgpt.com/?q=%s&hints=search',
			'icon'  => 'openai',
			'color' => 'currentColor',
		),
		'gemini'     => array(
			'label' => __( 'Gemini', 'agent-prompt-block' ),
			'url'   => 'https://gemini.google.com/app?q=%s',
			'icon'  => 'sparkle',
			'color' => 'currentColor',
		),
		'perplexity' => array(
			'label' => __( 'Perplexity', 'agent-prompt-block' ),
			'url'   => 'https://www.perplexity.ai/search?q=%s',
			'icon'  => 'sparkle',
			'color' => 'currentColor',
		),
		'copilot'    => array(
			'label' => __( 'Microsoft Copilot', 'agent-prompt-block' ),
			'url'   => 'https://copilot.microsoft.com/?q=%s',
			'icon'  => 'sparkle',
			'color' => 'currentColor',
		),
	);

	/**
	 * Filters the assistants offered by the Agent Prompt block.
	 *
	 * @param array $providers Assistants keyed by slug.
	 */
	return apply_filters( 'lf_agent_prompt_providers', $providers );
}

/**
 * Builds the deep link for a single provider.
 *
 * @param string $slug   Provider slug.
 * @param string $prompt Raw, unencoded prompt text.
 * @return string Empty string when the provider is unknown.
 */
function lf_agent_prompt_build_url( $slug, $prompt ) {
	$providers = lf_agent_prompt_get_providers();

	if ( ! isset( $providers[ $slug ] ) ) {
		return '';
	}

	return sprintf( $providers[ $slug ]['url'], rawurlencode( $prompt ) );
}
