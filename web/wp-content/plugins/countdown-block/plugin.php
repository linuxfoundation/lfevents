<?php
/**
 * Plugin Name: Countdown Block
 * Plugin URI: https://github.com/linuxfoundation/lfevents/tree/main/web/wp-content/plugins/countdown-block
 * Description: Gutenberg block which allows for insertion of a countdown in a page/post.
 * Author: fuerzastudio
 * Version: 0.1.0
 * License: GPL2+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @package CGB
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Block Initializer.
 */
function countdown_block_get_plugin_path() {
	return plugin_dir_path( __FILE__ );
}

require_once countdown_block_get_plugin_path() . 'src/init.php';
