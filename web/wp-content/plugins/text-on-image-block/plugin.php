<?php
/**
 * Plugin Name: Text on Image Gutenberg Block
 * Plugin URI: https://github.com/linuxfoundation/lfevents/tree/main/web/wp-content/plugins/text-on-image-block
 * Description: This is a custom block that creates a fully responsive text box on top of an image
 * Author: cjyabraham
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
require_once plugin_dir_path( __FILE__ ) . 'src/init.php';
