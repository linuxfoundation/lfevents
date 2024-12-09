<?php
/**
 * Plugin Name:       Core Block Customisations
 * Description:       Customisations to core blocks.
 * Requires at least: 6.0
 * Requires PHP:      7.0
 * Version:           0.1.0
 * Author:            James Hunt
 * Author URI:        https://www.thetwopercent.co.uk
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       core-block-customisations
 *
 * @package           create-block
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! defined( 'CBC_PATH' ) ) {
	define( 'CBC_PATH', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'CBC_URL' ) ) {
	define( 'CBC_URL', plugin_dir_url( __FILE__ ) );
}

/**
 * Enqueue Script
 *
 * @return void
 */
function lf_custom_attributes_editor_scripts() {
	wp_register_script(
		'core-block-customisations',
		CBC_URL . 'build/index.js',
		array( 'wp-blocks', 'wp-dom', 'wp-dom-ready', 'wp-edit-post' ),
		filemtime( CBC_PATH . 'build/index.js' ),
		true
	);
	wp_enqueue_script( 'core-block-customisations' );
}
add_action( 'enqueue_block_editor_assets', 'lf_custom_attributes_editor_scripts' );

/**
 * Load Cover Block customisations
 *
 * @param string $block_content Content of the block.
 * @param array  $block Block attributes.
 */
function lf_load_cover_block_customisations( $block_content, $block ) {
	if ( 'core/cover' === $block['blockName'] &&
		isset( $block['attrs']['activateVideo'], $block['attrs']['videoBackground'] ) &&
		$block['attrs']['activateVideo'] &&
		! empty( $block['attrs']['videoBackground'] ) ) {

		$video_url = wp_get_attachment_url( $block['attrs']['videoBackground'] );
		$cover_url = $block['attrs']['url'] ?? null;

		if ( isset( $block['attrs']['id'] ) ) {
			$image_id  = $block['attrs']['id'];
			$image_alt = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
		} else {
			$image_alt = '';
		}

		if ( ! empty( $video_url ) || ! empty( $cover_url ) ) {
			ob_start();
			?>
	<div class="wp-block-cover alignfull has-video-background">
		<div aria-hidden="true" class="cover-bg__overlay"></div>

		<link rel="preload" as="image" fetchpriority="high" href="<?php echo esc_url( $cover_url ); ?>">
		<img src="<?php echo esc_url( $cover_url ); ?>"
				class="cover-bg__poster" style="width: 100%; height: 100%;"
				alt="<?php echo esc_attr( $image_alt ); ?>" decoding="async">

		<div class="cover-bg__video-wrapper">
			<video class="cover-bg__video" loop muted playsinline width="100%"
					preload="none" style="width: 100%;
				height: 100%;
				object-fit: cover;
				position: absolute;
				z-index: 1;
				top: 0;
				left: 0;">
					<source
						src="<?php echo esc_url( $video_url ); ?>"
						type="video/mp4">
					<img src="<?php echo esc_url( $cover_url ); ?>"
						alt="<?php echo esc_attr( $image_alt ); ?>">
				</video>
		</div>

		<div class="cover-bg__content">
			<div class="container wrap">
						<?php

						if ( ! empty( $block['innerBlocks'] ) ) {
							foreach ( $block['innerBlocks'] as $inner_block ) {
								echo render_block( $inner_block ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							}
						}
						?>
			</div>
		</div>
	</div>
			<?php
			$custom_markup = ob_get_clean();
			return $custom_markup;
		}
	}
	return $block_content;
}
add_filter( 'render_block', 'lf_load_cover_block_customisations', 10, 2 );

/**
 * Enqueue Script for Frontend
 */
function lf_enqueue_frontend_script() {
	global $post;

	if ( $post ) {
		$blocks = parse_blocks( $post->post_content );

		foreach ( $blocks as $block ) {
			if ( 'core/cover' === $block['blockName'] ) {
				if ( isset( $block['attrs']['activateVideo'] ) && $block['attrs']['activateVideo'] ) {
					wp_enqueue_script(
						'core-block-customisations-video',
						CBC_URL . 'js/video.js',
						array(),
						filemtime( CBC_PATH . 'js/video.js' ),
						true
					);
					break;
				}
			}
		}
	}
}
add_action( 'wp_enqueue_scripts', 'lf_enqueue_frontend_script' );
