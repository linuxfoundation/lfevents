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

// Social Block callback.
require_once 'block/render-callback.php';

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
function social_block_assets() {
	// Register block styles for both frontend + backend.
	wp_register_style(
		'social_block_css',
		plugins_url( 'dist/blocks.style.build.css', dirname( __FILE__ ) ),
		is_admin() ? array( 'wp-editor' ) : null,
		filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.style.build.css' )
	);

	// Register block editor script for backend.
	wp_register_script(
		'social_block_scripts',
		plugins_url( '/dist/blocks.build.js', dirname( __FILE__ ) ),
		array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ),
		filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.build.js' ),
		true
	);

	// Register block editor styles for backend.
	wp_register_style(
		'social_block_editor_css',
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
		'lf/social-block',
		array(
			'attributes'      => array(
				'className' => array(
					'type' => 'string',
				),
				'menu_color_1' => array(
					'type'    => 'string',
				),
				'menu_color_2' => array(
					'type'    => 'string',
				),
				'menu_color_3' => array(
					'type'    => 'string',
				),
				'iconColor' => array(
					'type'    => 'string',
				),
				'iconSize' => array(
					'type'    => 'integer',
				),
				'wechat_url' => array(
					'type'    => 'string',
				),
				'linkedin_url' => array(
					'type'    => 'string',
				),
				'qq_url' => array(
					'type'    => 'string',
				),
				'youtube_url' => array(
					'type'    => 'string',
				),
				'facebook_url' => array(
					'type'    => 'string',
				),
				'twitter_url' => array(
					'type'    => 'string',
				),
				'instagram_url' => array(
					'type'    => 'string',
				),

			),
			// Enqueue blocks.style.build.css on both frontend & backend.
			// 'style'         => 'social_block_css', // phpcsignore.
			'editor_script'   => 'social_block_scripts',
			'editor_style'    => 'social_block_editor_css',
			'render_callback' => 'social_block_callback',
		)
	);
}
add_action( 'init', 'social_block_assets' );
