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
function text_on_image_block_cgb_block_assets() { // phpcs:ignore
	// Register block styles for both frontend + backend.
	wp_register_style(
		'text_on_image_block-cgb-style-css',
		plugins_url( 'dist/blocks.style.build.css', dirname( __FILE__ ) ),
		array( 'wp-editor' ),
		filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.style.build.css' )
	);

	// Register block editor script for backend.
	wp_register_script(
		'text_on_image_block-cgb-block-js',
		plugins_url( '/dist/blocks.build.js', dirname( __FILE__ ) ),
		array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ),
		filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.build.js' ),
		true
	);

	// Register block editor styles for backend.
	wp_register_style(
		'text_on_image_block-cgb-block-editor-css',
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
		'cgb/block-text-on-image-block',
		array(
			'editor_script'   => 'text_on_image_block-cgb-block-js',
			'editor_style'    => 'text_on_image_block-cgb-block-editor-css',
			'render_callback' => 'text_on_image_block_callback',
		)
	);
}

// Hook: Block assets.
add_action( 'init', 'text_on_image_block_cgb_block_assets' );

/**
 * Callback for speakers block.
 *
 * @param array $attributes Atts.
 */
function text_on_image_block_callback( $attributes ) {
	$image_url = isset( $attributes['imgUrl'] ) ? $attributes['imgUrl'] : false;
	$image_id  = isset( $attributes['imgId'] ) ? $attributes['imgId'] : false;
	$text      = isset( $attributes['bodyContent'] ) ? $attributes['bodyContent'] : '';

	if ( empty( $image_url ) && empty( $text ) && empty( $image_id ) ) {
		return;
	}

	ob_start();

	?>
	<div class="alignfull lfe-image-and-text pull-right">
		<?php echo wp_get_attachment_image( $image_id, 'full' ); ?>
		<div class="text">
			<blockquote>
				<div class="copy-bd"><?php echo apply_filters( 'the_content', $text ); // phpcs:ignore ?></div>
			</blockquote>
		</div>
	</div>
	<?php

	return ob_get_clean();
}
