<?php
/**
 * Plugin Name: China Video Block
 * Plugin URI: https://github.com/LF-Engineering/lfevents/tree/master/web/wp-content/plugins/create-guten-block/
 * Description: Gutenberg block that embeds one video for users in China, another for everyone else.  Useful to accomodate video sites that are blocked in China.
 * Author: cjyabraham
 * Version: 1.0.0
 * License: GPL2+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @package WordPress
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Block Initializer.
 */
require_once plugin_dir_path( __FILE__ ) . 'src/init.php';

require_once plugin_dir_path( __FILE__ ) . 'src/settings.php';
