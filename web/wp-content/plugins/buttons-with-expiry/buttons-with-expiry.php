<?php
/**
 * Plugin Name:     Buttons with Expiry
 * Description:     Fork of Gutenberg Buttons but with Expiry options
 * Version:         0.2.0
 * Author:          James Hunt
 * License:         GPL-2.0-or-later
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:     buttons-with-expiry
 *
 * @package         lf
 */

/**
 * Registers all block assets so that they can be enqueued through the block editor
 * in the corresponding context.
 *
 * @see https://developer.wordpress.org/block-editor/tutorials/block-tutorial/applying-styles-with-stylesheets/
 */
function lf_buttons_with_expiry_block_init() {
	$dir = __DIR__;

	$script_asset_path = "$dir/build/index.asset.php";
	if ( ! file_exists( $script_asset_path ) ) {
		throw new Error(
			'You need to run `npm start` or `npm run build` for the "lf/buttons-with-expiry" block first.'
		);
	}
	$index_js     = 'build/index.js';
	$script_asset = require( $script_asset_path );
	wp_register_script(
		'lf-buttons-with-expiry-block-editor',
		plugins_url( $index_js, __FILE__ ),
		$script_asset['dependencies'],
		$script_asset['version']
	);
	wp_set_script_translations( 'lf-buttons-with-expiry-block-editor', 'buttons-with-expiry' );

	$editor_css = 'build/index.css';
	wp_register_style(
		'lf-buttons-with-expiry-block-editor',
		plugins_url( $editor_css, __FILE__ ),
		array(),
		filemtime( "$dir/$editor_css" )
	);

	register_block_type(
		'lf/buttons-with-expiry',
		array(
			'editor_script' => 'lf-buttons-with-expiry-block-editor',
			'editor_style'  => 'lf-buttons-with-expiry-block-editor',
		)
	);

	register_block_type(
		'lf/button-with-expiry',
		array(
			'render_callback' => 'button_with_expiry_callback',
		)
	);

}
add_action( 'init', 'lf_buttons_with_expiry_block_init' );

/**
 * Callback
 *
 * @param array  $attributes Post attributes.
 * @param string $content The content.
 */
function button_with_expiry_callback( $attributes, $content ) {

	$expire_at   = isset( $attributes['expireAt'] ) ? $attributes['expireAt'] : time() + 86400;
	$expire_text = isset( $attributes['expireText'] ) ? $attributes['expireText'] : false;
	$will_expire = isset( $attributes['willExpire'] ) ? $attributes['willExpire'] : false;

	$wordpress_timezone = get_option( 'timezone_string' );

	// right here right now - Pulls in timezone to set to WordPress site time.
	$now = new DateTime( 'now', new DateTimeZone( $wordpress_timezone ) );
	$now = $now->format( 'Y-m-d H:i:s' );

	// the expiry time; don't adjust it as already in EST.
	$expiry_time = new DateTime( "@$expire_at" );
	$expiry_time->setTimezone( new DateTimeZone( $wordpress_timezone ) );
	$expiry_time = $expiry_time->format( 'Y-m-d H:i:s' );

	if ( $will_expire ) {

		// strips out mismatched <br> tags that are used with multiple languages.
		$content = str_replace( '<br>', '', $content );
		$content = str_replace( '&nbsp;', '', $content );

		$dom = new DOMDocument();
		$dom->loadXML( $content );
		$a = $dom->getElementsByTagName( 'a' )->item( 0 );
		if ( $a ) {
			$classes = $a->getAttribute( 'class' );
			if ( $expiry_time < $now ) {
				$a->setAttribute( 'class', $classes . ' disabled' );
			}
			$a->nodeValue = $expiry_time; // phpcs:ignore
			return $dom->saveHTML();
		}
	}
	return $content;
}
