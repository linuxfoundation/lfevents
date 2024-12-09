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
function sponsors_dynamic_block_cgb_block_assets() { // phpcs:ignore
	// Register block editor script for backend.
	wp_register_script(
		'sponsors-dynamic_block-cgb-block-js', // Handle.
		plugins_url( '/dist/blocks.build.js', __DIR__ ), // Block.build.js: We register the block here. Built with Webpack.
		array( 'wp-blocks', 'wp-i18n', 'wp-element' ),
		null,
		true
	);

	// Register block editor styles for backend.
	wp_register_style(
		'sponsors-dynamic_block-cgb-block-editor-css', // Handle.
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
		'cgb/block-sponsors-dynamic-block',
		array(
			// Enqueue blocks.style.build.css on both frontend & backend.
			// 'style'         => 'sponsors-dynamic_block-cgb-style-css',
			// Enqueue blocks.build.js in the editor only.
			'editor_script'   => 'sponsors-dynamic_block-cgb-block-js',
			// // Enqueue blocks.editor.build.css in the editor only.
			'editor_style'    => 'sponsors-dynamic_block-cgb-block-editor-css',
			'render_callback' => 'sponsors_dynamic_block_callback',
		)
	);
}

// Hook: Block assets.
add_action( 'init', 'sponsors_dynamic_block_cgb_block_assets' );

/**
 * Callback for sponsors-dynamic block.
 *
 * @param array  $attributes Atts.
 * @param string $content Content.
 */
function sponsors_dynamic_block_callback( $attributes, $content ) {
	if ( empty( $attributes['sponsors'] ) ) {
		return;
	}

	$sponsor_ids = array_map(
		function ( $sponsor ) {
			return intval( $sponsor['value'] );
		},
		$attributes['sponsors']
	);

	$query = new WP_Query(
		array(
			'update_post_term_cache' => false,
			'post_type'              => 'lfe_sponsor',
			'post_status'            => 'publish',
			'posts_per_page'         => -1,
			'post__in'               => $sponsor_ids,
			'orderby'                => 'title',
			'order'                  => 'ASC',
		)
	);

	if ( ! $query->have_posts() ) {
		return;
	}

	$tier_name = isset( $attributes['tierName'] ) ? $attributes['tierName'] : '';
	$tier_size = isset( $attributes['tierSize'] ) ? $attributes['tierSize'] : 'medium';

	$out = '<div class="wp-block-cgb-sponsors-block">';
	if ( $tier_name ) {
		$out .= '<h3 class="sponsors-logos--header">' . $tier_name . '</h3>';
	}
	$out .= '<div class="sponsors-logos ' . $tier_size . ' ' . get_sponsor_logos_class( $query->found_posts ) . '">';

	// temporarily deactivate the size calculation for SVGs.
	add_filter( 'safesvg_try_calculate_image_sizes', '__return_false' );

	while ( $query->have_posts() ) {
		$query->the_post();

		$id             = get_the_ID();
		$forwarding_url = esc_url( get_post_meta( $id, 'lfes_sponsor_url', true ) );
		$size           = get_post_meta( $id, 'lfes_sponsor_size', true );
		$alt_text       = esc_attr( get_post_meta( $id, 'lfes_sponsor_alt_text', true ) );

		if ( ! $alt_text ) {
			$alt_text = get_the_title();
		}

		$out .= '<div class="sponsors-logo-item">';
		if ( $forwarding_url ) {
			$out .= '<a href="' . $forwarding_url . '" title="Go to ' . $alt_text . '"';
			if ( is_numeric( $size ) && '100' !== $size ) {
				$scale = $size / 100;
				$out  .= ' style="-webkit-transform: scale(' . $scale . '); -ms-transform: scale(' . $scale . '); transform: scale(' . $scale . ');"';
			}
			$out .= ' target="_blank" rel="noopener">';
		} else {
			$out .= '<div';
			if ( is_numeric( $size ) && '100' !== $size ) {
				$scale = $size / 100;
				$out  .= ' style="-webkit-transform: scale(' . $scale . '); -ms-transform: scale(' . $scale . '); transform: scale(' . $scale . ');"';
			}
			$out .= '>';
		}

		$out .= get_the_post_thumbnail(
			$id,
			'post-thumbnail',
			array(
				'class'   => 'logo',
				'loading' => 'lazy',
				'alt'     => $alt_text . ' logo',
			)
		);

		if ( $forwarding_url ) {
			$out .= '</a>';
		} else {
			$out .= '</div>';
		}
		$out .= '</div>';
	}
	// reactivate size calculations for SVGs.
	remove_all_filters( 'safesvg_try_calculate_image_sizes' );

	$out .= '</div></div>';

	/* Restore original Post Data */
	wp_reset_postdata();

	return $out;
}

/**
 * Calculates the correct class to place on the logos div.
 *
 * @param int $count Number of logos.
 */
function get_sponsor_logos_class( $count ) {
	if ( 0 === $count % 2 ) {
		$out = 'even';
	} else {
		$out = 'odd';
	}

	for ( $i = 3; $i < 9; $i++ ) {
		if ( 1 === $count % $i ) {
			$out .= ' orphan-by-' . $i;
		}
	}
	return $out;
}
