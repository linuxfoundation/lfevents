<?php
/**
 * Plugin Name:       Stats Block
 * Description:       A responsive row of stat cards (big number + label) whose numbers count up from zero when the block scrolls into view.
 * Plugin URI:        https://github.com/linuxfoundation/lfevents/tree/main/web/wp-content/plugins/stats-block
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Version:           0.1.0
 * Author:            The Linux Foundation
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       stats-block
 *
 * @package           WordPress
 */

defined( 'ABSPATH' ) || exit;

define( 'LF_STATS_BLOCK_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Registers the block from its compiled block.json metadata.
 */
function lf_stats_block_init() {
	$build_dir = LF_STATS_BLOCK_DIR . 'build';

	if ( ! file_exists( $build_dir . '/block.json' ) ) {
		return;
	}

	register_block_type( $build_dir );
}
add_action( 'init', 'lf_stats_block_init' );
