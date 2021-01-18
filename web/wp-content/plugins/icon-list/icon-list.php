<?php
/**
 * Plugin Name:     Icon List Block
 * Description:     Customised unordered list with icons
 * Version:         0.1.1
 * Author:          James Hunt
 * License:         GPL-2.0-or-later
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:     icon-list
 *
 * @package         lf
 */

/**
 * Registers all block assets so that they can be enqueued through the block editor
 * in the corresponding context.
 *
 * @throws Error This.
 */
function lf_icon_list_block_init() {
	$dir = dirname( __FILE__ );

	$script_asset_path = "$dir/build/index.asset.php";
	if ( ! file_exists( $script_asset_path ) ) {
		throw new Error(
			'You need to run `npm start` or `npm run build` for the "lf/icon-list" block first.'
		);
	}
	$index_js     = 'build/index.js';
	$script_asset = require $script_asset_path;
	wp_register_script(
		'lf-icon-list-block-editor',
		plugins_url( $index_js, __FILE__ ),
		$script_asset['dependencies'],
		$script_asset['version']
	);
	wp_set_script_translations( 'lf-icon-list-block-editor', 'icon-list' );

	$editor_css = 'build/index.css';
	wp_register_style(
		'lf-icon-list-block-editor',
		plugins_url( $editor_css, __FILE__ ),
		array(),
		filemtime( "$dir/$editor_css" )
	);

	$style_css = 'build/style-index.css';
	wp_register_style(
		'lf-icon-list-block',
		plugins_url( $style_css, __FILE__ ),
		array(),
		filemtime( "$dir/$style_css" )
	);

	register_block_type(
		'lf/icon-list',
		array(
			'editor_script' => 'lf-icon-list-block-editor',
			'editor_style'  => 'lf-icon-list-block-editor',
		)
	);
}
add_action( 'init', 'lf_icon_list_block_init' );
