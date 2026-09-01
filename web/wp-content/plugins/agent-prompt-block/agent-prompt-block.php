<?php
/**
 * Plugin Name:       Agent Prompt Block
 * Description:       Adds a "Discuss this with your agent" dropdown block that deep-links a pre-filled prompt into Claude, ChatGPT and other AI assistants, with a "Copy prompt" fallback.
 * Plugin URI:        https://github.com/linuxfoundation/lfevents/tree/main/web/wp-content/plugins/agent-prompt-block
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Version:           0.1.0
 * Author:            The Linux Foundation
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       agent-prompt-block
 *
 * @package           WordPress
 */

defined( 'ABSPATH' ) || exit;

define( 'LF_AGENT_PROMPT_BLOCK_DIR', plugin_dir_path( __FILE__ ) );

require_once LF_AGENT_PROMPT_BLOCK_DIR . 'includes/icons.php';
require_once LF_AGENT_PROMPT_BLOCK_DIR . 'includes/providers.php';
require_once LF_AGENT_PROMPT_BLOCK_DIR . 'includes/render.php';

/**
 * Registers the block from its compiled block.json metadata.
 */
function lf_agent_prompt_block_init() {
	$build_dir = LF_AGENT_PROMPT_BLOCK_DIR . 'build';

	if ( ! file_exists( $build_dir . '/block.json' ) ) {
		return;
	}

	register_block_type(
		$build_dir,
		array(
			'render_callback' => 'lf_agent_prompt_render_callback',
		)
	);
}
add_action( 'init', 'lf_agent_prompt_block_init' );

/**
 * Exposes the provider registry to the block editor so the UI stays in sync with PHP.
 */
function lf_agent_prompt_block_editor_assets() {
	if ( ! wp_script_is( 'lf-agent-prompt-editor-script', 'registered' ) ) {
		return;
	}

	$providers = array();

	foreach ( lf_agent_prompt_get_providers() as $slug => $provider ) {
		$providers[] = array(
			'value' => $slug,
			'label' => $provider['label'],
		);
	}

	wp_add_inline_script(
		'lf-agent-prompt-editor-script',
		'window.lfAgentPromptProviders = ' . wp_json_encode( $providers ) . ';',
		'before'
	);
}
add_action( 'enqueue_block_editor_assets', 'lf_agent_prompt_block_editor_assets' );
