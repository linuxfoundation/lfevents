<?php
/**
 * Plugin Name:       Sessionize Blocks
 * Description:       Sessionize-powered blocks for schedules and featured speakers.
 * Version:           0.1.0
 * Author:            Chris Abraham
 * Text Domain:       sessionize-blocks
 *
 * @package           Sessionize_Blocks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers all Sessionize blocks.
 *
 * Each block lives in its own directory under blocks/ with its own block.json.
 *
 * @return void
 */
function sessionize_register_blocks() {
	$blocks_dir = __DIR__ . '/blocks';

	register_block_type( $blocks_dir . '/sessionize-schedule' );
	register_block_type( $blocks_dir . '/sessionize-speakers' );
}
add_action( 'init', 'sessionize_register_blocks' );

/**
 * Loads DOMPurify before the schedule block's view script runs, so session
 * descriptions can be safely sanitized and formatted on the front end (see
 * blocks/sessionize-schedule/src/view.js — sanitizeDescriptionHtml() looks
 * for window.DOMPurify and safely falls back to escaped plain text when
 * it's missing, which is why Markdown-lite formatting silently doesn't
 * render, and multiple blank lines in a description can show up as
 * oversized gaps, without this).
 *
 * Vendored locally (blocks/sessionize-schedule/assets/purify.min.js) rather
 * than pulled from a CDN, so it always loads even if a third-party script
 * host is blocked or slow. Enqueued as a normal, render-blocking script in
 * the <head> — not deferred, not async — rather than wired up as a formal
 * dependency of the block's auto-generated view-script handle, since that
 * handle's exact name is a WordPress implementation detail. WordPress
 * enqueues block view scripts either deferred or in the footer, and both
 * of those always run after every plain synchronous <head> script, so this
 * ordering holds regardless of block registration internals.
 *
 * @return void
 */
function sessionize_enqueue_dompurify() {
	$path = __DIR__ . '/blocks/sessionize-schedule/assets/purify.min.js';
	if ( ! file_exists( $path ) ) {
		return;
	}

	wp_enqueue_script(
		'sessionize-dompurify',
		plugins_url( 'blocks/sessionize-schedule/assets/purify.min.js', __FILE__ ),
		array(),
		'3.4.14',
		false // In the <head>, not deferred — see note above.
	);
}
add_action( 'wp_enqueue_scripts', 'sessionize_enqueue_dompurify' );
