<?php
/**
 * Blocks Initializer
 *
 * Enqueue CSS/JS of all the blocks.
 *
 * @since   1.1.0
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
function button_with_expiry_block_assets() {
	// Register block editor script for backend.
	wp_register_script(
		'button_with_expiry-block-js', // Handle.
		plugins_url( '/dist/blocks.build.js', dirname( __FILE__ ) ),
		array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ),
		filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.build.js' ),
		true
	);

	// Register block editor styles for backend.
	wp_register_style(
		'button_with_expiry-block-editor-css',
		plugins_url( 'dist/blocks.editor.build.css', dirname( __FILE__ ) ),
		array( 'wp-edit-blocks' ),
		filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.editor.build.css' )
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
			'editor_script' => 'button_with_expiry-block-js',
			'editor_style'  => 'button_with_expiry-block-editor-css',
		)
	);

	register_block_type(
		'lf/button-with-expiry',
		array(
			'render_callback' => 'button_with_expiry_callback',
		)
	);
}


/**
 * Callback
 *
 * @param array  $attributes Post attributes.
 * @param string $content The content.
 */
function button_with_expiry_callback( $attributes, $content ) {

	$expire_at   = isset( $attributes['expireAt'] ) ? $attributes['expireAt'] : time() + 86400;
	$expire_text = isset( $attributes['expireText'] ) ? $attributes['expireText'] : false;
	$will_expire = isset( $attributes['willExpire'] ) ? $attributes['willExpire'] : false;

	$wordpress_timezone = get_option( 'timezone_string' );

	// right here right now - Pulls in timezone to set to WordPress site time.
	$now = new DateTime( 'now', new DateTimeZone( $wordpress_timezone ) );
	$now = $now->format( 'Y-m-d H:i:s' );

	// the expiry time; don't adjust it as already in EST.
	$expiry_time = new DateTime( "@$expire_at", new DateTimeZone( 'UTC' ) );
	$expiry_time = $expiry_time->format( 'Y-m-d H:i:s' );

	if ( $will_expire && $expiry_time < $now ) {

		if ( empty( $expire_text ) ) {
			return;
		}

		// strips out mismatched <br> tags that are used with multiple languages.
		$content = str_replace( '<br>', '', $content );

		$dom = new DOMDocument();
		$dom->loadXML( $content );
		$a = $dom->getElementsByTagName( 'a' )->item( 0 );
		if ( $a ) {
			$classes = $a->getAttribute( 'class' );
			$a->setAttribute( 'class', $classes . ' disabled' );
			$a->nodeValue = $expire_text; // phpcs:ignore
			return $dom->saveHTML();
		}
	}
	return $content;
}

add_action( 'init', 'button_with_expiry_block_assets' );
