<?php
/**
 * Plugin Name:     Image Box Block
 * Description:     A row of images with hover overlay effect
 * Version:         0.1.1
 * Author:          James Hunt
 * License:         GPL-2.0-or-later
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:     image-box
 *
 * @package         lf
 */

/**
 * Registers all block assets so that they can be enqueued through the block editor
 * in the corresponding context.
 *
 * @throws Error Throws error if the build/index.asset.php file doesn't exist.
 * @see https://developer.wordpress.org/block-editor/tutorials/block-tutorial/applying-styles-with-stylesheets/
 */
function lf_image_box_block_init() {
	$dir = __DIR__;

	$script_asset_path = "$dir/build/index.asset.php";
	if ( ! file_exists( $script_asset_path ) ) {
		throw new Error(
			'You need to run `npm start` or `npm run build` for the "lf/image-box" block first.'
		);
	}
	$index_js     = 'build/index.js';
	$script_asset = require $script_asset_path;
	wp_register_script(
		'lf-image-box-block-editor',
		plugins_url( $index_js, __FILE__ ),
		$script_asset['dependencies'],
		$script_asset['version']
	);
	wp_set_script_translations( 'lf-image-box-block-editor', 'image-box' );

	$editor_css = 'build/index.css';
	wp_register_style(
		'lf-image-box-block-editor',
		plugins_url( $editor_css, __FILE__ ),
		array(),
		filemtime( "$dir/$editor_css" )
	);

	$style_css = 'build/style-index.css';
	wp_register_style(
		'lf-image-box-block',
		plugins_url( $style_css, __FILE__ ),
		array(),
		filemtime( "$dir/$style_css" )
	);

	register_block_type(
		'lf/image-box',
		array(
			'editor_script' => 'lf-image-box-block-editor',
			'editor_style'  => 'lf-image-box-block-editor',
		// 'style'         => 'lf-image-box-block',
		)
	);
}
add_action( 'init', 'lf_image_box_block_init' );
