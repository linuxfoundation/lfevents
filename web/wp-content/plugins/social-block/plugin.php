<?php
/**
 * Plugin Name: Social Block
 * Plugin URI: https://github.com/LF-Engineering/lfevents/
 * Description: Gutenberg block which inserts the social media icons for each event.
 * Author: James Hunt
 * Author URI: https://www.cncf.io
 * Version: 1.0.0
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
require_once plugin_dir_path( __FILE__ ) . 'src/init.php';
