<?php
/**
 * Plugin Name: LFEvents Forms
 * Description: Create Gutenberg blocks to allow insert Linux Foundation forms.
 * Author: fuerzastudio
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

$plugin_path = plugin_dir_path( __FILE__ );

/**
 * Load views
 */
require_once $plugin_path . 'src/blocks/newsletter/index.php';
require_once $plugin_path . 'src/blocks/visa-request/index.php';

/**
 * Block Initializer.
 */
require_once $plugin_path . 'src/init.php';
