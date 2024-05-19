<?php
/**
 * Plugin Name: Sponsors Dynamic Block
 * Plugin URI: https://github.com/linuxfoundation/lfevents/tree/main/web/wp-content/plugins/sponsors-dynamic-block
 * Description: Gutenberg block which allows for insertion of a Sponsors in a page/post. It requires an existing Sponsors CPT already setup <a href="https://github.com/linuxfoundation/lfevents/blob/master/web/wp-content/mu-plugins/custom/lfevents/admin/class-lfevents-admin.php">here</a>.
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
