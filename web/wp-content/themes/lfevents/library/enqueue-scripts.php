<?php
/**
 * Enqueue all styles and scripts
 *
 * Learn more about enqueue_script: {@link https://codex.wordpress.org/Function_Reference/wp_enqueue_script}
 * Learn more about enqueue_style: {@link https://codex.wordpress.org/Function_Reference/wp_enqueue_style }
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

// Check to see if rev-manifest exists for CSS and JS static asset revisioning
// https://github.com/sindresorhus/gulp-rev/blob/master/integration.md.
if ( ! function_exists( 'foundationpress_asset_path' ) ) :
	/**
	 * Comment
	 *
	 * @param string $filename Comment.
	 */
	function foundationpress_asset_path( $filename ) {
		$filename_split = explode( '.', $filename );
		$dir            = end( $filename_split );
		$manifest_path  = dirname( dirname( __FILE__ ) ) . '/dist/assets/' . $dir . '/rev-manifest.json';

		if ( file_exists( $manifest_path ) ) {
			$manifest = json_decode( file_get_contents( $manifest_path ), true );
		} else {
			$manifest = array();
		}

		if ( array_key_exists( $filename, $manifest ) ) {
			return $manifest[ $filename ];
		}
		return $filename;
	}
endif;


if ( ! function_exists( 'foundationpress_scripts' ) ) :
	/**
	 * Comment.
	 */
	function foundationpress_scripts() {

		// Enqueue the main Stylesheet.
		wp_enqueue_style( 'main-stylesheet', get_stylesheet_directory_uri() . '/dist/assets/css/' . foundationpress_asset_path( 'app.css' ), array(), filemtime( get_template_directory() . '/dist/assets/css/' . foundationpress_asset_path( 'app.css' ) ), 'all' );

		// Deregister the jquery version bundled with WordPress, but not in admin.
		if ( ! is_admin() ) {
			wp_deregister_script( 'jquery' );

			// jQuery placed in the header, as some plugins require that jQuery is loaded in the header.
			wp_enqueue_script( 'jquery', get_stylesheet_directory_uri() . '/src/assets/js/jquery/' . foundationpress_asset_path( 'jquery-3.5.1.min.js' ), array(), '3.5.1', false );

			// Deregister the jquery-migrate version bundled with WordPress.
			wp_deregister_script( 'jquery-migrate' );
		}

		// Enqueue Foundation scripts.
		wp_enqueue_script( 'foundation', get_stylesheet_directory_uri() . '/dist/assets/js/' . foundationpress_asset_path( 'app.js' ), array( 'jquery' ), filemtime( get_template_directory() . '/dist/assets/js/' . foundationpress_asset_path( 'app.js' ) ), true );

		// Add the comment-reply library on pages where it is necessary.
		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}

		if ( has_block( 'table' ) ) {
			wp_enqueue_script( 'responsive-table', get_stylesheet_directory_uri() . '/src/assets/js/lib/' . foundationpress_asset_path( 'restable.js' ), array( 'jquery' ), filemtime( get_template_directory() . '/src/assets/js/lib/' . foundationpress_asset_path( 'restable.js' ) ), true );
			wp_enqueue_script( 'responsive-table-code', get_stylesheet_directory_uri() . '/dist/assets/js/' . foundationpress_asset_path( 'responsive-table.js' ), array( 'jquery', 'responsive-table' ), filemtime( get_template_directory() . '/dist/assets/js/' . foundationpress_asset_path( 'responsive-table.js' ) ), true );
		}

		// Add scripts required for non-event pages.
		if ( show_non_event_menu() ) {
				// Add auth SSO/LFX assets.
			wp_enqueue_script( 'auth0', 'https://cdn.auth0.com/js/auth0-spa-js/1.13.3/auth0-spa-js.production.js', array(), '1', false );
			wp_enqueue_script( 'lf-auth0', 'https://cdn.dev.platform.linuxfoundation.org/wordpress-auth0.js', array(), '1', false );
			wp_enqueue_script( 'auth0-config', get_stylesheet_directory_uri() . '/dist/assets/js/' . foundationpress_asset_path( 'auth0.js' ), array( 'lf-auth0', 'auth0' ), filemtime( get_template_directory() . '/dist/assets/js/' . foundationpress_asset_path( 'auth0.js' ) ), false );
		}

		// Conditionally load china.js.
		$chinese_domains = "'www.lfasiallc.com', 'events19.lfasiallc.com', 'events.linuxfoundation.cn', 'events19.linuxfoundation.cn', 'www.lfasiallc.cn', 'lfasiallc.cn'";
		$current_domain  = parse_url( home_url(), PHP_URL_HOST );
		if ( strpos( $chinese_domains, $current_domain ) ) {
			// scripts for Chinese-audience sites.
			wp_enqueue_script( 'lfe_china', get_stylesheet_directory_uri() . '/dist/assets/js/' . foundationpress_asset_path( 'china.js' ), array(), '1.2.2', true );
		}

		// Dequeue the conditional-blocks-front-css.
		wp_dequeue_style( 'conditional-blocks-front-css' );

		if ( is_front_page() && ! is_admin() ) {
			wp_deregister_script( 'jquery-ui-datepicker' ); // searchandfilter.
			wp_dequeue_style( 'wp-block-library' ); // block library is not used on frontpage.
		}

		// Cookie script.
		wp_enqueue_script( 'osano', 'https://cmp.osano.com/16A0DbT9yDNIaQkvZ/3b49aaa9-15ab-4d47-a8fb-96cc25b5543c/osano.js', array(), '1', true );

	}
	add_action( 'wp_enqueue_scripts', 'foundationpress_scripts' );


endif;

	/**
	 * Dequeue front page scripts - later.
	 */
function lfe_dequeue_front_page_later() {
	if ( is_front_page() && ! is_admin() ) {
		wp_dequeue_style( 'photonic' );
	}
}
add_action( 'wp_enqueue_scripts', 'lfe_dequeue_front_page_later', 100 );
