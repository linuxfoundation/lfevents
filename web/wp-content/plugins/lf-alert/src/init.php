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
function lf_alert_cgb_block_assets() { // phpcs:ignore
	// Register block styles for both frontend + backend.
	wp_register_style(
		'lf_alert-cgb-style-css', // Handle.
		plugins_url( 'dist/blocks.style.build.css', dirname( __FILE__ ) ),
		is_admin() ? array( 'wp-editor' ) : null,
		filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.style.build.css' )
	);

	// Register block editor script for backend.
	wp_register_script(
		'lf_alert-cgb-block-js', // Handle.
		plugins_url( '/dist/blocks.build.js', dirname( __FILE__ ) ),
		array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ),
		filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.build.js' ),
		true // Enqueue the script in the footer.
	);

	// Register block editor styles for backend.
	wp_register_style(
		'lf_alert-cgb-block-editor-css', // Handle.
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
		'lf/alert',
		array(
			'editor_script'   => 'lf_alert-cgb-block-js',
			'editor_style'    => 'lf_alert-cgb-block-editor-css',
			'render_callback' => 'lf_alert_callback',
		)
	);
}

function lf_alert_callback( $attributes ) { // phpcs:ignore
	ob_start();

	$text             = isset( $attributes['text'] ) ? $attributes['text'] : false;
	$background_color = isset( $attributes['backgroundColor'] ) ? $attributes['backgroundColor'] : '';
	$text_color       = isset( $attributes['textColor'] ) ? $attributes['textColor'] : '#212326';
	$expire_at        = isset( $attributes['expireAt'] ) ? $attributes['expireAt'] : time() + 86400;
	$time_left        = $expire_at - time();

	if ( empty( $text ) ) {
		return;
	}

	if ( $time_left < 0 ) {
		return;
	}

	$styles = '--bg-color: ' . esc_attr( $background_color ) . ';';
	$styles .= ' --text-color: ' . esc_attr( $text_color ) . ';';

	?>
	<div class="wp-block-lf-alert" style="<?php echo esc_html( $styles ); ?>">
		<?php echo apply_filters( 'the_content', $text ); // phpcs:ignore ?>
	</div>
	<?php

	return ob_get_clean();
}

// Hook: Block assets.
add_action( 'init', 'lf_alert_cgb_block_assets' );
