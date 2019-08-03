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
function pricing_block_cgb_block_assets() { // phpcs:ignore
	// Register block styles for both frontend + backend.
	wp_register_style(
		'pricing_block-cgb-style-css', // Handle.
		plugins_url( 'dist/blocks.style.build.css', dirname( __FILE__ ) ), // Block style CSS.
		array( 'wp-editor' ), // Dependency to include the CSS after it.
		null // filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.style.build.css' ) // Version: File modification time.
	);

	// Register block editor script for backend.
	wp_register_script(
		'pricing_block-cgb-block-js', // Handle.
		plugins_url( '/dist/blocks.build.js', dirname( __FILE__ ) ), // Block.build.js: We register the block here. Built with Webpack.
		array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ), // Dependencies, defined above.
		null, // filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.build.js' ), // Version: filemtime — Gets file modification time.
		true // Enqueue the script in the footer.
	);

	// Register block editor styles for backend.
	wp_register_style(
		'pricing_block-cgb-block-editor-css', // Handle.
		plugins_url( 'dist/blocks.editor.build.css', dirname( __FILE__ ) ), // Block editor CSS.
		array( 'wp-edit-blocks' ), // Dependency to include the CSS after it.
		null // filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.editor.build.css' ) // Version: File modification time.
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
		'cgb/block-pricing-block', array(
			// Enqueue blocks.style.build.css on both frontend & backend.
//			'style'         => 'pricing_block-cgb-style-css',
			// Enqueue blocks.build.js in the editor only.
			'editor_script' => 'pricing_block-cgb-block-js',
			// Enqueue blocks.editor.build.css in the editor only.
			'editor_style'  => 'pricing_block-cgb-block-editor-css',
			'render_callback' => 'block_callback', // The render callback.
		)
	);
}


// Hook: Block assets.
add_action( 'init', 'pricing_block_cgb_block_assets' );

/**
 * CALLBACK
 *
 * Render callback for the dynamic block.
 *
 * Instead of rendering from the block's save(), this callback will render the front-end
 *
 * @since    1.0.0
 * @param array $att Attributes from the JS block.
 * @return string Rendered HTML.
 */
function block_callback( $att ) {
	$top_labels = $att['topLabels'];
	$dates = $att['dates'];
	$left_labels = $att['leftLabels'];
	$prices = $att['prices'];
	$expire_text = $att['expireText'];
	$color1 = $att['color1'];
	$color2 = $att['color2'];
	$tz = $att['timeZone'];
	$yesterday = new DateTime( 'now', new DateTimeZone( $tz ) );
	$yesterday->sub( new DateInterval( 'P1D' ) );

	$column = 0;
	$row = 0;

	if ( ! $top_labels || ! $dates || ! $left_labels || ! $prices ) {
		return '';
	}

	$html = '<table class="wp-block-table alignwide"><tr><th></th>';
	foreach ( $top_labels as $label ) {
		if ( $label ) {
			$column++;
			try {
				$date_start = new DateTime( $dates[ $column - 1 ], new DateTimeZone( $tz ) );
				$date_end = new DateTime( $dates[ $column ], new DateTimeZone( $tz ) );
			} catch (Exception $e) {
				return;
			}
			if ( $column > 1 ) {
				$date_start->add( new DateInterval( 'P1D' ) );
			}
			$html .= '<th>' . $label . '<br>' . jb_verbose_date_range( $date_start, $date_end ) . '<br>11:59 pm Local</th>';
		}
	}
	$html .= '</tr>';

	foreach ( $left_labels as $label ) {
		if ( $label ) {
			$html .= '<tr><td>' . $label . '</td>';
			for ( $i = 0; $i < $column; $i++ ) {
				$date_end = new DateTime( $dates[ $i + 1 ], new DateTimeZone( $tz ) );
			if ( $date_end < $yesterday ) {
					$html .= '<td class="expired"><s>' . $prices[ $i ][ $row ] . '</s><br>' . $expire_text . '</td>';
				} else {
					$html .= '<td>' . $prices[ $i ][ $row ] . '</td>';
				}
			}
			$row++;
			$html .= '</tr>';
		}
	}
	$html .= '</table>';

	return $html;

}
