<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'stackable_premium_block_assets' ) ) {

	/**
	* Enqueue block assets for both frontend + backend.
	*
	* @since 0.1
	*/
	function stackable_premium_block_assets() {

		$enqueue_styles_in_frontend = apply_filters( 'stackable_enqueue_styles', ! is_admin() );
		$enqueue_scripts_in_frontend = apply_filters( 'stackable_enqueue_scripts', ! is_admin() );

		if ( is_admin() || $enqueue_styles_in_frontend ) {
			wp_enqueue_style(
				'ugb-style-css-premium',
				plugins_url( 'dist/frontend_blocks__premium_only.css', STACKABLE_FILE ),
				array( 'ugb-style-css' ),
				STACKABLE_VERSION
			);
		}

		if ( $enqueue_scripts_in_frontend ) {
			wp_enqueue_script(
				'ugb-block-frontend-js-premium',
				plugins_url( 'dist/frontend_blocks__premium_only.js', STACKABLE_FILE ),
				array( 'ugb-block-frontend-js' ),
				STACKABLE_VERSION
			);
		}
	}
	add_action( 'enqueue_block_assets', 'stackable_premium_block_assets' );
}

if ( ! function_exists( 'stackable_premium_block_editor_assets' ) ) {

	/**
	 * Enqueue block assets for backend editor.
	 *
	 * @since 0.1
	 */
	function stackable_premium_block_editor_assets() {

		// This should enqueue BEFORE the main Stackable block script.
		wp_enqueue_script(
			'ugb-block-js-premium',
			plugins_url( 'dist/editor_blocks__premium_only.js', STACKABLE_FILE ),
			array( 'ugb-block-js-vendor', 'code-editor', 'wp-blocks', 'wp-element', 'wp-components', 'wp-editor', 'wp-util', 'wp-plugins', 'wp-edit-post', 'wp-i18n' ),
			STACKABLE_VERSION
		);

		// Add translations.
		wp_set_script_translations( 'ugb-block-js-premium', STACKABLE_I18N );

		wp_enqueue_style(
			'ugb-block-editor-css-premium',
			plugins_url( 'dist/editor_blocks__premium_only.css', STACKABLE_FILE ),
			array( 'ugb-block-editor-css' ),
			STACKABLE_VERSION
		);
	}
	add_action( 'enqueue_block_editor_assets', 'stackable_premium_block_editor_assets', 9 );
}
