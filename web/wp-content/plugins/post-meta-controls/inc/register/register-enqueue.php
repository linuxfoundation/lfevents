<?php

namespace POSTMETACONTROLS;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Enqueue the plugin styles and scripts, and the main function object.
 *
 * @since 1.0.0
 */
add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\enqueue' );
function enqueue() {

	do_action( 'pmc_before_enqueue' );

	wp_enqueue_style(
		PLUGIN_NAME,
		BUILD_DIR . PLUGIN_NAME . '.css',
		array(),
		PLUGIN_VERSION
	);

	wp_enqueue_script(
		PLUGIN_NAME,
		BUILD_DIR . PLUGIN_NAME . '.js',
		array(
			'lodash',
			'moment',
			// If wp-block-editor is registered (from WP 5.2)
			// enqueue it. Otherwise enqueue wp-editor.
			isset( $wp_scripts->registered['wp-block-editor'] )
				? 'wp-block-editor'
				: 'wp-editor',
			'wp-editor',
			'wp-api-fetch',
			'wp-components',
			'wp-compose',
			'wp-data',
			'wp-dom-ready',
			'wp-edit-post',
			'wp-element',
			'wp-hooks',
			'wp-i18n',
			'wp-plugins',
			'wp-url',
		),
		PLUGIN_VERSION,
		true // Enqueue in the footer.
	);
}
