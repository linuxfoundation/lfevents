<?php
/**
 * Blocks Initializer
 *
 * Enqueue CSS/JS of all the blocks.
 *
 * @since   1.0.0
 * @package CGB
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue Gutenberg block assets for both frontend + backend.
 *
 * Assets enqueued:
 * 1. blocks.style.build.css - Frontend + Backend.
 * 2. blocks.build.js - Backend.
 * 3. blocks.editor.build.css - Backend.
 *
 * @uses {wp-blocks} for block type registration & related functions.
 * @uses {wp-element} for WP Element abstraction — structure of blocks.
 * @uses {wp-i18n} to internationalize the block's text.
 * @uses {wp-editor} for WP editor styles.
 * @since 1.0.0
 */
function button_with_expiry_block_assets() { // phpcs:ignore
	// Register block editor script for backend.
	wp_register_script(
		'button_with_expiry-block-js', // Handle.
		plugins_url( '/dist/blocks.build.js', dirname( __FILE__ ) ),
		array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ),
		filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.build.js' ),
		true
	);

	/**
	 * Register Gutenberg block on server-side.
	 *
	 * Register the block on server-side to ensure that the block
	 * scripts and styles for both frontend and backend are
	 * enqueued when the editor loads.
	 *
	 * @link https://wordpress.org/gutenberg/handbook/blocks/writing-your-first-block-type#enqueuing-block-scripts
	 * @since 1.16.0
	 */
	register_block_type(
		'lf/buttons-with-expiry',
		array(
			'editor_script'   => 'button_with_expiry-block-js',
			'editor_style'    => 'button_with_expiry-block-editor-css',
		)
	);

	register_block_type(
		'lf/button-with-expiry',
		array(
			'render_callback' => 'button_with_expiry_callback',
		)
	);
}

// phpcs:disable
function button_with_expiry_callback( $attributes, $content ) {
	$expire_at   = isset( $attributes['expireAt'] ) ? $attributes['expireAt'] : time() + 86400;
	$expire_text = isset( $attributes['expireText'] ) ? $attributes['expireText'] : false;
	$time_left   = $expire_at - time();

	if ( $time_left < 0 ) {

		if ( empty( $expire_text ) ) {
			return;
		}

		$dom = new DOMDocument();
		$dom->loadXML( $content );
		$a = $dom->getElementsByTagName( 'a' )->item( 0 );
		if ( $a ) {
			$classes = $a->getAttribute( 'class' );
			$a->setAttribute( 'class', $classes . ' disabled' );
			$a->nodeValue = $expire_text;
			return $dom->saveHTML();
		}

	}

	return $content;
}

add_action( 'init', 'button_with_expiry_block_assets' );
