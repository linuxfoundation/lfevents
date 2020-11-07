<?php
/**
 * Plugin Name:     Track Grid
 * Description:     A linkable grid of tracks for an event
 * Version:         0.1.0
 * Author:          James Hunt
 * License:         GPL-2.0-or-later
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:     track-grid
 *
 * @package         lf
 */

/**
 * Registers all block assets so that they can be enqueued through the block editor
 * in the corresponding context.
 *
 * @see https://developer.wordpress.org/block-editor/tutorials/block-tutorial/applying-styles-with-stylesheets/
 */
function lf_track_grid_block_init() {
	$dir = dirname( __FILE__ );

	$script_asset_path = "$dir/build/index.asset.php";
	if ( ! file_exists( $script_asset_path ) ) {
		throw new Error(
			'You need to run `npm start` or `npm run build` for the "lf/track-grid" block first.'
		);
	}
	$index_js     = 'build/index.js';
	$script_asset = require( $script_asset_path );
	wp_register_script(
		'lf-track-grid-block-editor',
		plugins_url( $index_js, __FILE__ ),
		$script_asset['dependencies'],
		$script_asset['version']
	);
	wp_set_script_translations( 'lf-track-grid-block-editor', 'track-grid' );

	$editor_css = 'build/index.css';
	wp_register_style(
		'lf-track-grid-block-editor',
		plugins_url( $editor_css, __FILE__ ),
		array(),
		filemtime( "$dir/$editor_css" )
	);

	$style_css = 'build/style-index.css';
	wp_register_style(
		'lf-track-grid-block',
		plugins_url( $style_css, __FILE__ ),
		array(),
		filemtime( "$dir/$style_css" )
	);

	register_block_type( 'lf/track-grid', array(
		'editor_script' => 'lf-track-grid-block-editor',
		'editor_style'  => 'lf-track-grid-block-editor',
		'style'         => 'lf-track-grid-block',
	) );
}
add_action( 'init', 'lf_track_grid_block_init' );
