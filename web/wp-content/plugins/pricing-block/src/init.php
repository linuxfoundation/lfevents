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
		'pricing_block-cgb-style-css',
		plugins_url( 'dist/blocks.style.build.css', __DIR__ ),
		null
	);

	// Register block editor script for backend.
	wp_register_script(
		'pricing_block-cgb-block-js',
		plugins_url( '/dist/blocks.build.js', __DIR__ ),
		array( 'wp-blocks', 'wp-i18n', 'wp-element' ),
		null,
		true
	);

	// Register block editor styles for backend.
	wp_register_style(
		'pricing_block-cgb-block-editor-css',
		plugins_url( 'dist/blocks.editor.build.css', __DIR__ ), // Block editor CSS.
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
		'cgb/block-pricing-block',
		array(
			// Enqueue blocks.style.build.css on both frontend & backend.
			// 'style'         => 'pricing_block-cgb-style-css',
			// Enqueue blocks.build.js in the editor only.
			'editor_script'   => 'pricing_block-cgb-block-js',
			// Enqueue blocks.editor.build.css in the editor only.
			'editor_style'    => 'pricing_block-cgb-block-editor-css',
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
	$top_labels  = $att['topLabels'] ?? '';
	$dates       = $att['dates'] ?? '';
	$left_labels = $att['leftLabels'] ?? '';
	$prices      = $att['prices'] ?? '';
	$expire_text = $att['expireText'] ?? 'Expired';
	$color1      = $att['color1'] ?? '';
	$color2      = $att['color2'] ?? '';
	$color3      = $att['color3'] ?? '';
	$color4      = $att['color4'] ?? '';
	$color_text  = $att['colorText'] ?? 'inherit';
	$tz          = $att['timeZone'] ?? '-0700';
	$language    = $att['language'] ?? 'ENG';
	$yesterday   = new DateTime( 'now', new DateTimeZone( $tz ) );
	$yesterday->sub( new DateInterval( 'P1D' ) );

	$column = 0;
	$row    = 0;
	$html   = '';

	if ( ! $top_labels || ! $dates || ! $left_labels || ! $prices ) {
		return;
	}

	// Convert old multi-dimensional prices to new single-dimensional prices.
	if ( is_array( $prices[0] ) ) {
		$new_prices = array();
		for ( $i = 0; $i < 4; $i++ ) {
			for ( $j = 0; $j < 7; $j++ ) {
				$new_index                = ( $j * 4 ) + $i;
				$new_prices[ $new_index ] = $prices[ $i ][ $j ];
			}
		}
		$prices = $new_prices;
	}

	$html .= '<div class="pricing-grid alignwide">';
	foreach ( $left_labels as $label ) {
		if ( $label ) {
			$html .= '<div class="attendee-type" style="color:' . $color_text . ';">';

			$html .= '<h4 class="attendee-type--name" style="color:' . $color1 . '; border-color:' . $color1 . ';">' . $label . '</h4>';
			for ( $i = 0; $i < 4; $i++ ) {
				if ( $top_labels[ $i ] ) {
					if ( 0 === $i ) {
						$color = $color1;
					} elseif ( 1 === $i ) {
						$color = $color2;
					} elseif ( 2 === $i ) {
						$color = $color3;
					} elseif ( 3 === $i ) {
						$color = $color4;
					}
					try {
						$date_start = new DateTime( $dates[ $i ], new DateTimeZone( $tz ) );
						$date_end   = new DateTime( $dates[ $i + 1 ], new DateTimeZone( $tz ) );
					} catch ( Exception $e ) {
						return;
					}
					if ( $i > 0 ) {
						$date_start->add( new DateInterval( 'P1D' ) );
					}
					if ( $date_end < $yesterday ) {
						$expired = 'expired';
					} else {
						$expired = '';
					}
					$html .= '<div class="price-cell ' . $expired . '" style="background-color:' . $color . ';">';

					$html .= '<div class="price-window" style="background-color:' . $color . '">';
					$html .= '<h5 class="price-window--name">' . $top_labels[ $i ];
					$html .= '<small class="price-window--date-range">' . jb_verbose_date_range( $date_start, $date_end, '<br>', $language ) . '</small>';
					$html .= '</h5>';
					$html .= '</div>';
					$html .= '<div class="price-amount">';
					$j     = $i + ( $row * 4 );
					if ( $date_end < $yesterday ) {
						$html .= '<s>' . $prices[ $j ] . '</s>';
						$html .= '<span class="expired-label">' . $expire_text . '</span>';
					} else {
						$html .= $prices[ $j ];
					}
					$html .= '</div>';

					$html .= '</div>';
				}
			}

			++$row;

			$html .= '</div>';
		}
	}
	$html .= '</div>';

	return $html;
}
