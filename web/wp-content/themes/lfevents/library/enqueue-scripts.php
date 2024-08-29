<?php
/**
 * Enqueue all styles and scripts
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

if ( ! function_exists( 'foundationpress_scripts' ) ) :
	/**
	 * FoundationPress Scripts.
	 */
	function foundationpress_scripts() {

		// Enqueue the main Stylesheet.
		if ( WP_DEBUG === true ) {
			// Use un-minified versions.
			wp_enqueue_style( 'main-stylesheet', get_stylesheet_directory_uri() . '/dist/css/app.css', array(), filemtime( get_template_directory() . '/dist/css/app.css' ), 'all' );
		} else {
			wp_enqueue_style( 'main-stylesheet', get_stylesheet_directory_uri() . '/dist/css/app.min.css', array(), filemtime( get_template_directory() . '/dist/css/app.min.css' ), 'all' );
		}

		// Deregister the jquery version bundled with WordPress, but not in admin.
		if ( ! is_admin() ) {
			wp_deregister_script( 'jquery' );

			// jQuery placed in the header, as some plugins require that jQuery is loaded in the header.
			wp_enqueue_script( 'jquery', get_stylesheet_directory_uri() . '/src/js/libraries/jquery-3.7.1.min.js', array(), '3.7.1', false );

			// Deregister the jquery-migrate version bundled with WordPress.
			wp_deregister_script( 'jquery-migrate' );
		}

		// Enqueue Foundation scripts.
		wp_enqueue_script( 'foundation', get_stylesheet_directory_uri() . '/dist/js/app.js', array( 'jquery' ), filemtime( get_template_directory() . '/dist/js/app.js' ), true );

		// Enqueue cookie script.
		wp_enqueue_script( 'osano', 'https://cmp.osano.com/16A0DbT9yDNIaQkvZ/3b49aaa9-15ab-4d47-a8fb-96cc25b5543c/osano.js', array(), '1', false );

		if ( has_block( 'table' ) ) {
			wp_enqueue_script( 'responsive-table', get_stylesheet_directory_uri() . '/src/js/libraries/restable.js', array( 'jquery' ), filemtime( get_template_directory() . '/src/js/libraries/restable.js' ), true );

			wp_enqueue_script( 'responsive-table-code', get_stylesheet_directory_uri() . '/dist/js/responsive-table.js', array( 'jquery', 'responsive-table' ), filemtime( get_template_directory() . '/dist/js/responsive-table.js' ), true );
		}

		// Add scripts required for non-event pages.
		if ( show_non_event_menu() ) {
			// Add auth SSO/LFX assets.
			wp_enqueue_script( 'auth0', 'https://cdn.auth0.com/js/auth0-spa-js/1.13.3/auth0-spa-js.production.js', array(), '1', false );

			// Use different Auth0 files depending on environment.
			if ( isset( $_SERVER['PANTHEON_ENVIRONMENT'] ) && 'live' == $_SERVER['PANTHEON_ENVIRONMENT'] ) {
				wp_enqueue_script( 'lf-auth0', 'https://cdn.platform.linuxfoundation.org/wordpress-auth0.js', array(), '1', false );
			} else {
				wp_enqueue_script( 'lf-auth0', 'https://cdn.dev.platform.linuxfoundation.org/wordpress-auth0.js', array(), '1', false );
			}

			wp_enqueue_script( 'auth0-config', get_stylesheet_directory_uri() . '/dist/js/auth0.js', array( 'lf-auth0', 'auth0' ), filemtime( get_template_directory() . '/dist/js/auth0.js' ), false );
		}

		// Conditionally load china.js.
		$chinese_domains = "'www.lfasiallc.com', 'events19.lfasiallc.com', 'events.linuxfoundation.cn', 'events19.linuxfoundation.cn', 'www.lfasiallc.cn', 'lfasiallc.cn'";
		$current_domain  = parse_url( home_url(), PHP_URL_HOST );
		if ( strpos( $chinese_domains, $current_domain ) ) {
			// scripts for Chinese-audience sites.
			wp_enqueue_script( 'lfe_china', get_stylesheet_directory_uri() . '/dist/js/china.js', array(), '1.2.2', true );
		}

		// Dequeue the conditional-blocks-front-css.
		wp_dequeue_style( 'conditional-blocks-front-css' );

		if ( is_front_page() && ! is_admin() ) {
			wp_deregister_script( 'jquery-ui-datepicker' ); // searchandfilter.
			wp_dequeue_style( 'wp-block-library' ); // block library is not used on frontpage.
		}

		if ( is_page() && is_singular() ) {
			wp_dequeue_style( 'search-filter-plugin-styles' );
		}
	}
	add_action( 'wp_enqueue_scripts', 'foundationpress_scripts' );

endif;

	/**
	 * Dequeue front page scripts - later.
	 */
function lfe_dequeue_front_page_later() {
	if ( is_front_page() && ! is_admin() ) {
		wp_dequeue_style( 'photonic' );
		wp_dequeue_style( 'photonic-slider' );
		wp_dequeue_style( 'photonic-lightbox' );
	}
}
add_action( 'wp_enqueue_scripts', 'lfe_dequeue_front_page_later', 100 );
