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
