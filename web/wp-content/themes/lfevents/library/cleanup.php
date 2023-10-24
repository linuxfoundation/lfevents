<?php
/**
 * Clean up WordPress defaults
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

if ( ! function_exists( 'foundationpress_start_cleanup' ) ) :
	/**
	 * Start cleanup
	 */
	function foundationpress_start_cleanup() {

		// Launching operation cleanup.
		add_action( 'init', 'foundationpress_cleanup_head' );

		// Remove WP version from RSS.
		add_filter( 'the_generator', 'foundationpress_remove_rss_version' );

		// Remove version from stylesheet and scripts.
		add_filter( 'style_loader_src', 'lf_update_default_version_to_be_filemtime', 10, 2 );
		add_filter( 'script_loader_src', 'lf_update_default_version_to_be_filemtime', 10, 2 );
	}
	add_action( 'after_setup_theme', 'foundationpress_start_cleanup' );
endif;

if ( ! function_exists( 'foundationpress_cleanup_head' ) ) :
	/**
	 * Head Cleanup.
	 */
	function foundationpress_cleanup_head() {

		// EditURI link.
		remove_action( 'wp_head', 'rsd_link' );

		// Category feed links.
		remove_action( 'wp_head', 'feed_links_extra', 3 );

		// Post and comment feed links.
		remove_action( 'wp_head', 'feed_links', 2 );

		// Windows Live Writer.
		remove_action( 'wp_head', 'wlwmanifest_link' );

		// Index link.
		remove_action( 'wp_head', 'index_rel_link' );

		// Previous link.
		remove_action( 'wp_head', 'parent_post_rel_link', 10 );

		// Start link.
		remove_action( 'wp_head', 'start_post_rel_link', 10 );

		// Canonical.
		remove_action( 'wp_head', 'rel_canonical', 10 );

		// Shortlink.
		remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 );

		// Links for adjacent posts.
		remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10 );

		// WP version.
		remove_action( 'wp_head', 'wp_generator' );

		// Emoji detection script.
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );

		// Emoji styles.
		remove_action( 'wp_print_styles', 'print_emoji_styles' );

		// Remove emojis.
		add_filter( 'emoji_svg_url', '__return_false' );

		// Remove application passwords.
		add_filter( 'wp_is_application_passwords_available', '__return_false' );

		// Controls whether XML-RPC methods requiring authentication are enabled.
		add_filter( 'xmlrpc_enabled', '__return_false' );

		// Unregister the whole XML-RPC method space.
		add_filter(
			'xmlrpc_methods',
			function( $methods ) {
				return array();
			}
		);

		// Deactivate x-pingback HTTP header.
		add_filter(
			'wp_headers',
			function( $headers ) {
				unset( $headers['X-Pingback'] );
				return $headers;
			}
		);

	}
endif;

// Remove WP version from RSS.
if ( ! function_exists( 'foundationpress_remove_rss_version' ) ) :
	/**
	 * Remove RSS versions from feed.
	 */
	function foundationpress_remove_rss_version() {
		return '';
	}
endif;

// Remove injected CSS from recent comments widget.
if ( ! function_exists( 'lf_update_default_version_to_be_filemtime' ) ) :
	/**
	 * Replace WordPress version with filemtime (for security).
	 *
	 * @param string $src Src.
	 * @param string $handle Handle.
	 * @return void|string
	 */
	function lf_update_default_version_to_be_filemtime( $src, $handle ) {
		$query_string = wp_parse_url( $src, PHP_URL_QUERY );
		parse_str( $query_string, $query_args );

		// If there are no ver arguments, return the original URL.
		if ( ! isset( $query_args['ver'] ) ) {
			return $src;
		}

		$new_query_args = array();
		foreach ( $query_args as $key => $value ) {
			if ( 'ver' !== $key || is_numeric( $value ) && 10 == strlen( $value ) ) {
				$new_query_args[ $key ] = $value;
			}
		}

		$new_query_args['ver'] = filemtime( get_template_directory() . '/style.css' );
		$new_query_string      = http_build_query( $new_query_args );

		// If the original URL had a query string, add it back.
		if ( $query_string ) {
			$src = str_replace( $query_string, $new_query_string, $src );
		} else {
			$src = add_query_arg( $new_query_string, '', $src );
		}
		return $src;
	}
endif;
