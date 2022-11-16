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
function custom_attributes_editor_scripts() {
	wp_register_script(
		'core-block-customisations',
		CBC_URL . 'build/index.js',
		array( 'wp-blocks', 'wp-dom', 'wp-dom-ready', 'wp-edit-post' ),
		filemtime( CBC_PATH . 'build/index.js' ),
		true
	);
	wp_enqueue_script( 'core-block-customisations' );
}
add_action( 'enqueue_block_editor_assets', 'custom_attributes_editor_scripts' );
