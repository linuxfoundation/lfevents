<?php
/**
 * Plugin Name:       Sessionize Schedule
 * Description:       A custom dynamic block for the Sessionize schedule.
 * Version:           1.0.0
 * Author:            Chris Abraham
 * Text Domain:       sessionize-schedule
 *
 * @package           Sessionize_Schedule
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 *
 * @return void
 */
function custom_register_sessionize_schedule() {
	register_block_type( __DIR__ );
}
add_action( 'init', 'custom_register_sessionize_schedule' );
