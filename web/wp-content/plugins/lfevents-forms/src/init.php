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
function lfevents_forms_block_assets() { // phpcs:ignore
	// Register block editor script for backend.
	wp_register_script(
		'lfevents_forms-block-js', // Handle.
		plugins_url( '/dist/blocks.build.js', dirname( __FILE__ ) ),
		array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ),
		filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.build.js' ),
		true // Enqueue the script in the footer.
	);

	// Register block editor styles for backend.
	wp_register_style(
		'lfevents_forms-block-editor-css',
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
		'lf/forms',
		array(
			// Enqueue blocks.build.js in the editor only.
			'editor_script' => 'lfevents_forms-block-js',
			// Enqueue blocks.editor.build.css in the editor only.
			'editor_style'  => 'lfevents_forms-block-editor-css',
		)
	);
}

/**
 * Create a custom category for blocks.
 *
 * @param array $categories All registered categories.
 *
 * @return array
 */
function lfevents_forms_block_category( $categories ) {
	return array_merge(
		$categories,
		array(
			array(
				'slug'  => 'lfe-forms',
				'title' => 'LFEvents Forms',
			),
		)
	);
}

function lfevents_forms_block_add_frontend_assets() { // phpcs:ignore
	$present_blocks = lfevents_forms_get_present_blocks();
	$allowed_blocks = array( 'lf/form-newsletter', 'lf/form-visa-request', 'lf/form-live-stream' );

	foreach ( $present_blocks as $block ) {
		if ( in_array( $block['blockName'], $allowed_blocks ) ) {
			wp_enqueue_script(
				'lfevents_forms-sfmc',
				plugins_url( 'src/blocks/sfmc-forms.js', dirname( __FILE__ ) ),
				array( 'jquery' ),
				filemtime( plugin_dir_path( __FILE__ ) . 'blocks/sfmc-forms.js' ),
				true
			);
		}
	}
}

if ( ! function_exists( 'lfevents_forms_checkout_inner_blocks' ) ) {
	function lfevents_forms_checkout_inner_blocks( $block ) { // phpcs:ignore
		static $current_blocks = array();

		$current = $block;

		if ( 'core/block' == $block['blockName'] ) {
			$current = parse_blocks( get_post_field( 'post_content', $block['attrs']['ref'] ) )[0];
		}

		if ( '' != $current['blockName'] ) {
			array_push( $current_blocks, $current );
			if ( count( $current['innerBlocks'] ) > 0 ) {
				foreach ( $current['innerBlocks'] as $inner_block ) {
					lfevents_forms_checkout_inner_blocks( $inner_block );
				}
			}
		}

		return $current_blocks;
	}
}

if ( ! function_exists( 'lfevents_forms_get_present_blocks' ) ) {
	function lfevents_forms_get_present_blocks() { // phpcs:ignore
		$present_blocks = array();
		$posts_array    = get_post();

		if ( ! empty( $posts_array ) ) {
			foreach ( parse_blocks( $posts_array->post_content ) as $block ) {
				$present_blocks = lfevents_forms_checkout_inner_blocks( $block );
			}
		}

		return $present_blocks;
	}
}

add_action( 'init', 'lfevents_forms_block_assets' );
add_filter( 'block_categories', 'lfevents_forms_block_category' );
add_action( 'wp_enqueue_scripts', 'lfevents_forms_block_add_frontend_assets' );
