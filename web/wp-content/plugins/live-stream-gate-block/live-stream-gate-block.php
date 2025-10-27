<?php
/**
 * Plugin Name:       Live Stream Gate Block
 * Description:       Gates video streams behind LF SSO
 * Requires at least: 5.8
 * Requires PHP:      7.0
 * Version:           0.1.0
 * Author:            James Hunt
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       live-stream-gate-block
 *
 * @package           lf
 */

define( 'LIVE_STREAM_GATE_NAME', trim( dirname( plugin_basename( __FILE__ ) ), '/' ) );
define( 'LIVE_STREAM_GATE_URL', plugins_url() . '/' . LIVE_STREAM_GATE_NAME );

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/block-editor/tutorials/block-tutorial/writing-your-first-block-type/
 */
function lf_live_stream_gate_block_block_init() {
	register_block_type(
		__DIR__,
		array(
			'attributes'      => array(
				'className'   => array(
					'type' => 'string',
				),
				'anchor'      => array(
					'type' => 'string',
				),
				'content'     => array(
					'type' => 'string',
				),
				'ssoDisabled' => array(
					'type'    => 'boolean',
					'default' => false,
				),
			),
			'render_callback' => 'live_stream_gate_callback',
		)
	);
}
add_action( 'init', 'lf_live_stream_gate_block_block_init' );

/**
 * Enqueues needed files conditionally if block is present.
 *
 * @return void
 */
function lf_live_stream_gate_block_block_enqueue() {
	// Files only load when block is used.
	if ( has_block( 'lf/live-stream-gate-block' ) ) {

		wp_enqueue_script( 'auth0', 'https://cdn.auth0.com/js/auth0-spa-js/1.22/auth0-spa-js.production.js', array(), '1', false );

		// Use different Auth0 files depending on environment.
		if ( isset( $_SERVER['PANTHEON_ENVIRONMENT'] ) && 'live' == $_SERVER['PANTHEON_ENVIRONMENT'] ) {
			wp_enqueue_script( 'lf-auth0', 'https://cdn.platform.linuxfoundation.org/wordpress-auth0.js', array(), '1', false );
		} else {
			wp_enqueue_script( 'lf-auth0', 'https://cdn.dev.platform.linuxfoundation.org/wordpress-auth0.js', array(), '1', false );
		}

		wp_enqueue_script( 'auth0-config', 'https://events.linuxfoundation.org/wp-content/themes/lfevents/dist/js/auth0.js', array(), '1', false );
	}
}
add_action( 'wp_enqueue_scripts', 'lf_live_stream_gate_block_block_enqueue' );

/**
 * Callback
 *
 * @param array $block_attributes block attributes.
 */
function live_stream_gate_callback( $block_attributes ) {

	$classes      = isset( $block_attributes['className'] ) ? $block_attributes['className'] : '';
	$anchor       = isset( $block_attributes['anchor'] ) ? $block_attributes['anchor'] : '';
	$align        = isset( $block_attributes['align'] ) ? 'align-' . $block_attributes['align'] : '';
	$content      = isset( $block_attributes['content'] ) ? $block_attributes['content'] : '';
	$sso_disabled = isset( $block_attributes['ssoDisabled'] ) ? $block_attributes['ssoDisabled'] : '';

	if ( ! $content ) {
		return;
	}
	ob_start();

	// check for SSO disabled setting.
	if ( $sso_disabled ) : ?>

		<div class="wp-block-lf-live-stream-gate-block <?php echo esc_html( $align ); ?> <?php echo esc_html( $classes ); ?>" id="<?php echo esc_html( $anchor ); ?>">
			<?php echo $content; // phpcs:ignore.
			?>
		</div>
		<?php
		// show based on auth classes (eurgh).
	else :
		?>
		<div class="wp-block-lf-live-stream-gate-block-placeholder is-auth0 only-anonymous <?php echo esc_html( $classes ); ?>" id="<?php echo esc_html( $anchor ); ?>">
			<div class="wp-block-lf-live-stream-gate-block-placeholder-inner">
				<img src="<?php echo esc_url( LIVE_STREAM_GATE_URL . '/src/images/thelinuxfoundation-color.svg' ); ?>" alt="The Linux Foundation" width="200">

				<p><strong>Sign in to your LF account to access this content</strong></p>

				<button class="wp-block-lf-live-stream-gate-block-button is-signin-link is-auth0 only-anonymous is-login-link">Sign In</button>

				<p>Don't have an LF account? It's free and the easiest way to access content across LF sites.</p>

				<button class="wp-block-lf-live-stream-gate-block-button is-signup-link is-auth0 only-anonymous is-signup-link">Create Account</button>
			</div>
		</div>
		<div class="wp-block-lf-live-stream-gate-block is-auth0 only-authenticated <?php echo esc_html( $align ); ?> <?php echo esc_html( $classes ); ?>" id="<?php echo esc_html( $anchor ); ?>">
			<?php echo $content; // phpcs:ignore.
			?>
		</div>

		<?php
	endif;
	$block_content = ob_get_clean();
	return $block_content;
}
