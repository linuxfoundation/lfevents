<?php
/**
 * Plugin Name:       Sponsor Directory Block
 * Description:       Imports sponsor directory data from a published Google Sheet and displays a filterable sponsor listing.
 * Requires at least: 6.3
 * Requires PHP:      8.2
 * Version:           0.1.0
 * Author:            The Linux Foundation
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       sponsor-directory-block
 *
 * @package Sponsor_Directory_Block
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/includes/class-sponsor-directory-importer.php';
require_once __DIR__ . '/includes/render.php';

/**
 * Register the block and its import endpoint.
 *
 * @return void
 */
function lf_sponsor_directory_block_init() {
	register_block_type(
		__DIR__ . '/build',
		array(
			'render_callback' => 'lf_sponsor_directory_render',
		)
	);
}
add_action( 'init', 'lf_sponsor_directory_block_init' );

/**
 * Register the authenticated CSV import route.
 *
 * @return void
 */
function lf_sponsor_directory_register_rest_routes() {
	$importer = new Sponsor_Directory_Importer();
	$importer->register_routes();
}
add_action( 'rest_api_init', 'lf_sponsor_directory_register_rest_routes' );
