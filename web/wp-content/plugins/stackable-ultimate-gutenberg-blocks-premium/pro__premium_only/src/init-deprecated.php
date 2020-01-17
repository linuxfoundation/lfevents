<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'stackable_premium_block_assets_deprecated' ) ) {

	/**
	 * Enqueue block assets for both frontend + backend.
	 *
	 * @since 2.0
	 */
	function stackable_premium_block_assets_deprecated() {
		$enqueue_styles_in_frontend = apply_filters( 'stackable_enqueue_styles', ! is_admin() );

		if ( stackable_should_load_v1_styles() && $enqueue_styles_in_frontend ) {
			wp_enqueue_style(
				'ugb-style-css-premium-deprecated',
				plugins_url( 'dist/frontend_blocks_deprecated__premium_only.css', STACKABLE_FILE ),
				array( 'ugb-style-css-premium' ),
				STACKABLE_VERSION
			);
		}
	}
	add_action( 'enqueue_block_assets', 'stackable_premium_block_assets_deprecated' );
}
