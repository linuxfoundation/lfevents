<?php
/**
 * Plugin Name:     Custom iFrame
 * Description:     Embed an iframe, Google Sheet or Newsletter with better formatting
 * Version:         0.1.0
 * Author:          James Hunt
 * License:         GPL-2.0-or-later
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:     custom-iframe
 *
 * @package         lf
 */

/**
 * Registers all block assets so that they can be enqueued through the block editor
 * in the corresponding context.
 *
 * @throws Error This.
 *
 * @see https://developer.wordpress.org/block-editor/tutorials/block-tutorial/applying-styles-with-stylesheets/
 */
function lf_custom_iframe_block_init() {
	$dir = dirname( __FILE__ );

	$script_asset_path = "$dir/build/index.asset.php";
	if ( ! file_exists( $script_asset_path ) ) {
		throw new Error(
			'You need to run `npm start` or `npm run build` for the "lf/custom-iframe" block first.'
		);
	}
	$index_js     = 'build/index.js';
	$script_asset = require $script_asset_path;
	wp_register_script(
		'lf-custom-iframe-block-editor',
		plugins_url( $index_js, __FILE__ ),
		$script_asset['dependencies'],
		$script_asset['version']
	);
	wp_set_script_translations( 'lf-custom-iframe-block-editor', 'custom-iframe' );

	$editor_css = 'build/index.css';
	wp_register_style(
		'lf-custom-iframe-block-editor',
		plugins_url( $editor_css, __FILE__ ),
		array(),
		filemtime( "$dir/$editor_css" )
	);

	$style_css = 'build/style-index.css';
	wp_register_style(
		'lf-custom-iframe-block',
		plugins_url( $style_css, __FILE__ ),
		array(),
		filemtime( "$dir/$style_css" )
	);

	register_block_type(
		'lf/custom-iframe',
		array(
			'editor_script' => 'lf-custom-iframe-block-editor',
			'editor_style'  => 'lf-custom-iframe-block-editor',
			'style'         => 'lf-custom-iframe-block',
		)
	);
}
add_action( 'init', 'lf_custom_iframe_block_init' );
