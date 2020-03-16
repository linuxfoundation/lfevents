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
function countdown_block_assets() { // phpcs:ignore
	// Register block editor script for backend.
	wp_register_script(
		'countdown-block-cgb-block-js', // Handle.
		plugins_url( '/dist/blocks.build.js', dirname( __FILE__ ) ),
		array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ),
		filemtime( countdown_block_get_plugin_path() . 'dist/blocks.build.js' ),
		true
	);

	// Register block editor styles for backend.
	wp_register_style(
		'countdown-block-cgb-style-css', // Handle.
		plugins_url( 'dist/blocks.style.build.css', dirname( __FILE__ ) ),
		array(),
		filemtime( countdown_block_get_plugin_path() . 'dist/blocks.style.build.css' )
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
		'cgb/countdown-block',
		array(
			'editor_script'   => 'countdown-block-cgb-block-js',
			'render_callback' => 'countdown_block_callback',
		)
	);
}

function countdown_block_callback( $attributes ) { // phpcs:ignore
	$labels            = array();
	$block_id          = isset( $attributes['blockID'] ) ? $attributes['blockID'] : '';
	$end_date          = isset( $attributes['endDate'] ) ? $attributes['endDate'] : time() + 86400;
	$style             = isset( $attributes['style'] ) ? $attributes['style'] : 'Odometer';
	$circle_color      = isset( $attributes['circleColor'] ) ? $attributes['circleColor'] : '#2DB7F5';
	$expiry_message    = isset( $attributes['expiryMessage'] ) ? $attributes['expiryMessage'] : 'Timer expired';
	$labels['weeks']   = isset( $attributes['labelWeeks'] ) ? $attributes['labelWeeks'] : 'Weeks';
	$labels['days']    = isset( $attributes['labelDays'] ) ? $attributes['labelDays'] : 'Days';
	$labels['hours']   = isset( $attributes['labelHours'] ) ? $attributes['labelHours'] : 'Hours';
	$labels['minutes'] = isset( $attributes['labelMinutes'] ) ? $attributes['labelMinutes'] : 'Minutes';
	$labels['seconds'] = isset( $attributes['labelSeconds'] ) ? $attributes['labelSeconds'] : 'Seconds';
	$message_align     = isset( $attributes['messageAlign'] ) ? $attributes['messageAlign'] : 'left';
	$class_name        = isset( $attributes['className'] ) ? $attributes['className'] : '';

	$time_left = $end_date - time();
	$seconds   = $time_left % 60;
	$minutes   = ( ( $time_left - $seconds ) % 3600 ) / 60;
	$hours     = ( ( $time_left - $minutes * 60 - $seconds ) % 86400 ) / 3600;
	$days      = ( ( $time_left - $hours * 3600 - $minutes * 60 - $seconds ) % 604800 ) / 86400;
	$weeks     = ( $time_left - $days * 86400 - $hours * 3600 - $minutes * 60 - $seconds ) / 604800;
	$values    = compact( 'weeks', 'days', 'hours', 'minutes', 'seconds' );

	switch ( $style ) {
		case 'Regular':
			$selected_format = countdown_block_default_format( $values, $labels );
			break;
		case 'Circular':
			$selected_format = countdown_block_circular_format( $values, $labels, $circle_color );
			break;
		default:
			$selected_format = countdown_block_odometer_format( $values, $labels );
	}

	if ( $time_left > 0 ) {
		return '<div data-nosnippet ' . ( '' == $block_id ? '' : 'id="ub_countdown_' . $block_id . '"' ) . 'class="ub-countdown' .
				( isset( $class_name ) ? ' ' . esc_attr( $class_name ) : '' ) .
				'" data-expirymessage="' . $expiry_message . '" data-end_date="' . $end_date . '">
			' . $selected_format
			. '</div>';
	}
}

function countdown_block_generate_circle( $label, $value, $limit, $color ) { // phpcs:ignore
	$circle_path = 'M 50,50 m 0,-35 a 35,35 0 1 1 0,70 a 35,35 0 1 1 0,-70';
	$prefix      = 'ub_countdown_circle_';

	return '<div class="' . $prefix . $label . '">
				<svg height="70" width="70" viewBox="0 0 100 100">
					<path class="' . $prefix . 'trail" d="' . $circle_path . '" stroke-width="3" ></path>
					<path class="' . $prefix . 'path" d="' . $circle_path . '" stroke="' . $color .
						'" stroke-width="3" style="stroke-dasharray: ' . $value * 219.911 / $limit . 'px, 219.911px;"></path>
				</svg>
				<div class="' . $prefix . 'label ub_countdown_' . $label . '">' . $value . '</div>
			</div>';
}

function countdown_block_default_format( $values, $labels ) { // phpcs:ignore
	ob_start();

	?>
	<span class="ub_countdown_week"><?php echo esc_html( $values['weeks'] ); ?></span>
	<?php echo esc_html( strtolower( $labels['weeks'] ) ); ?>
	<span class="ub_countdown_day"><?php echo esc_html( $values['days'] ); ?></span>
	<?php echo esc_html( strtolower( $labels['days'] ) ); ?>
	<span class="ub_countdown_hour"><?php echo esc_html( $values['hours'] ); ?></span>
	<?php echo esc_html( strtolower( $labels['hours'] ) ); ?>
	<span class="ub_countdown_minute"><?php echo esc_html( $values['minutes'] ); ?></span>
	<?php echo esc_html( strtolower( $labels['minutes'] ) ); ?>
	<span class="ub_countdown_second"><?php echo esc_html( $values['seconds'] ); ?></span>
	<?php echo esc_html( strtolower( $labels['seconds'] ) ); ?>
	<?php

	return ob_get_clean();
}

function countdown_block_circular_format( $values, $labels, $circle_color ) { // phpcs:ignore
	ob_start();

	?>
	<div class="ub_countdown_circular_container">
		<?php
		// phpcs:disable
		echo countdown_block_generate_circle( 'week', $values['weeks'], 52, $circle_color );
		echo countdown_block_generate_circle( 'day', $values['days'], 7, $circle_color );
		echo countdown_block_generate_circle( 'hour', $values['hours'], 24, $circle_color );
		echo countdown_block_generate_circle( 'minute', $values['minutes'], 60, $circle_color );
		echo countdown_block_generate_circle( 'second', $values['seconds'], 60, $circle_color );
		// phpcs:enable
		?>
		<p><?php echo esc_html( $labels['weeks'] ); ?></p>
		<p><?php echo esc_html( $labels['days'] ); ?></p>
		<p><?php echo esc_html( $labels['hours'] ); ?></p>
		<p><?php echo esc_html( $labels['minutes'] ); ?></p>
		<p><?php echo esc_html( $labels['seconds'] ); ?></p>
	</div>
	<?php

	return ob_get_clean();
}

function countdown_block_odometer_format( $values, $labels ) { // phpcs:ignore
	ob_start();

	?>
	<div class="ub-countdown-odometer-container">
		<span><?php echo esc_html( $labels['weeks'] ); ?></span>
		<span></span>
		<span><?php echo esc_html( $labels['days'] ); ?></span>
		<span></span>
		<span><?php echo esc_html( $labels['hours'] ); ?></span>
		<span></span>
		<span><?php echo esc_html( $labels['minutes'] ); ?></span>
		<span></span>
		<span><?php echo esc_html( $labels['seconds'] ); ?></span>
		<div class="ub-countdown-odometer ub_countdown_week"><?php echo esc_html( $values['weeks'] ); ?></div>
		<span class="ub-countdown-separator">:</span><div class="ub-countdown-odometer ub_countdown_day"><?php echo esc_html( $values['days'] ); ?></div>
		<span class="ub-countdown-separator">:</span><div class="ub-countdown-odometer ub_countdown_hour"><?php echo esc_html( $values['hours'] < 10 ? '0' . $values['hours'] : $values['hours'] ); ?></div>
		<span class="ub-countdown-separator">:</span><div class="ub-countdown-odometer ub_countdown_minute"><?php echo esc_html( $values['minutes'] < 10 ? '0' . $values['minutes'] : $values['minutes'] ); ?></div>
		<span class="ub-countdown-separator">:</span><div class="ub-countdown-odometer ub_countdown_second"><?php echo esc_html( $values['seconds'] < 10 ? '0' . $values['seconds'] : $values['seconds'] ); ?></div>
	</div>
	<?php

	return ob_get_clean();
}

function countdown_block_add_frontend_assets() { //phpcs:ignore
	$present_blocks = lf_get_present_blocks();

	foreach ( $present_blocks as $block ) {
		if ( 'cgb/countdown-block' == $block['blockName'] ) {
			wp_enqueue_script(
				'lf-countdown-script',
				plugins_url( 'src/block/front.js', dirname( __FILE__ ) ),
				array(),
				filemtime( plugin_dir_path( __FILE__ ) . 'block/front.js' ),
				true
			);

			if ( ! isset( $block['attrs']['style'] ) ) {
				wp_enqueue_script(
					'lf-countdown-odometer-script',
					plugins_url( 'src/block/libs/odometer.js', dirname( __FILE__ ) ),
					array(),
					filemtime( plugin_dir_path( __FILE__ ) . 'block/libs/odometer.js' ),
					true
				);
				break;
			}
		}
	}
}

if ( ! function_exists( 'lf_checkout_inner_blocks' ) ) {
	function lf_checkout_inner_blocks( $block ) { // phpcs:ignore
		static $current_blocks = array();

		$current = $block;

		if ( 'core/block' == $block['blockName'] ) {
			$current = parse_blocks( get_post_field( 'post_content', $block['attrs']['ref'] ) )[0];
		}

		if ( '' != $current['blockName'] ) {
			array_push( $current_blocks, $current );
			if ( count( $current['innerBlocks'] ) > 0 ) {
				foreach ( $current['innerBlocks'] as $inner_block ) {
					lf_checkout_inner_blocks( $inner_block );
				}
			}
		}

		return $current_blocks;
	}
}

if ( ! function_exists( 'lf_get_present_blocks' ) ) {
	function lf_get_present_blocks() { // phpcs:ignore
		$present_blocks = array();
		$posts_array    = get_post();

		if ( ! empty( $posts_array ) ) {
			foreach ( parse_blocks( $posts_array->post_content ) as $block ) {
				$present_blocks = lf_checkout_inner_blocks( $block );
			}
		}

		return $present_blocks;
	}
}

add_action( 'init', 'countdown_block_assets' );
add_action( 'wp_enqueue_scripts', 'countdown_block_add_frontend_assets' );
