<?php
/**
 * Sessionize HTTP client.
 *
 * Talks to the public Sessionize API. This is the only place in the plugin that
 * performs an outbound request to sessionize.com — everything else reads from
 * the cache layer in class-sessionize-store.php.
 *
 * @package Sessionize_Blocks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fetches raw event data from the Sessionize API.
 */
class Sessionize_Client {

	/**
	 * Base URL for the Sessionize v2 API.
	 *
	 * @var string
	 */
	const API_BASE = 'https://sessionize.com/api/v2/';

	/**
	 * How long to wait on a Sessionize response, in seconds.
	 *
	 * This only ever runs from cron or from a cold-start render, never from a
	 * normal cached pageview, so a generous timeout is safe.
	 *
	 * @var int
	 */
	const TIMEOUT = 15;

	/**
	 * Validates a Sessionize API code.
	 *
	 * The code comes from a block attribute, which is editor-supplied and
	 * therefore untrusted. It gets interpolated into an outbound request URL,
	 * so it has to be validated before use to prevent path traversal or
	 * request forgery against other hosts.
	 *
	 * @param string $api_code The API code to validate.
	 * @return bool True when the code is safe to use in a URL.
	 */
	public static function is_valid_api_code( $api_code ) {
		return (bool) preg_match( '/^[A-Za-z0-9]{4,64}$/', (string) $api_code );
	}

	/**
	 * Fetches and decodes a single Sessionize API view.
	 *
	 * @param string $api_code The Sessionize API code.
	 * @param string $view     The view name, e.g. 'All' or 'GridSmart'.
	 * @return array|WP_Error Decoded response, or WP_Error on failure.
	 */
	public static function fetch_view( $api_code, $view ) {
		if ( ! self::is_valid_api_code( $api_code ) ) {
			return new WP_Error(
				'sessionize_invalid_api_code',
				__( 'Invalid Sessionize API code.', 'sessionize-blocks' )
			);
		}

		if ( ! preg_match( '/^[A-Za-z0-9]+$/', (string) $view ) ) {
			return new WP_Error(
				'sessionize_invalid_view',
				__( 'Invalid Sessionize view name.', 'sessionize-blocks' )
			);
		}

		$url = self::API_BASE . $api_code . '/view/' . $view;

		$response = wp_remote_get(
			$url,
			array(
				'timeout'    => self::TIMEOUT,
				'user-agent' => 'WordPress/Sessionize-Blocks; ' . home_url( '/' ),
				'headers'    => array( 'Accept' => 'application/json' ),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== (int) $code ) {
			return new WP_Error(
				'sessionize_http_error',
				sprintf(
					/* translators: 1: Sessionize view name, 2: HTTP status code. */
					__( 'Sessionize returned HTTP %2$d for view "%1$s".', 'sessionize-blocks' ),
					$view,
					(int) $code
				)
			);
		}

		$body = wp_remote_retrieve_body( $response );
		if ( '' === trim( (string) $body ) ) {
			return new WP_Error(
				'sessionize_empty_body',
				__( 'Sessionize returned an empty response body.', 'sessionize-blocks' )
			);
		}

		$decoded = json_decode( $body, true );
		if ( null === $decoded && JSON_ERROR_NONE !== json_last_error() ) {
			return new WP_Error(
				'sessionize_bad_json',
				sprintf(
					/* translators: %s: JSON error message. */
					__( 'Could not decode the Sessionize response: %s', 'sessionize-blocks' ),
					json_last_error_msg()
				)
			);
		}

		return $decoded;
	}

	/**
	 * Fetches everything the blocks need for one event.
	 *
	 * The "All" view is required. The "GridSmart" view only powers the optional
	 * grid layout, so a failure there is tolerated and simply yields no grid
	 * data rather than failing the whole refresh.
	 *
	 * @param string $api_code The Sessionize API code.
	 * @return array|WP_Error Array with 'all' and 'grid' keys, or WP_Error.
	 */
	public static function fetch_event( $api_code ) {
		$all = self::fetch_view( $api_code, 'All' );
		if ( is_wp_error( $all ) ) {
			return $all;
		}

		if ( ! is_array( $all ) ) {
			return new WP_Error(
				'sessionize_unexpected_payload',
				__( 'The Sessionize "All" view did not return an object.', 'sessionize-blocks' )
			);
		}

		$grid = self::fetch_view( $api_code, 'GridSmart' );
		if ( is_wp_error( $grid ) || ! is_array( $grid ) ) {
			$grid = null;
		}

		return array(
			'all'  => $all,
			'grid' => $grid,
		);
	}
}
