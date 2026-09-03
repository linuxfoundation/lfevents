<?php
/**
 * WP-CLI commands for the Sessionize cache.
 *
 * @package Sessionize_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages the cached Sessionize event data.
 */
class Sessionize_CLI {

	/**
	 * Refreshes cached Sessionize data.
	 *
	 * ## OPTIONS
	 *
	 * [--code=<api-code>]
	 * : Refresh only this Sessionize API code. Defaults to every code found in
	 * published content.
	 *
	 * ## EXAMPLES
	 *
	 *     wp sessionize refresh
	 *     wp sessionize refresh --code=t0vgv3tv
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 */
	public function refresh( $args, $assoc_args ) {
		$code  = isset( $assoc_args['code'] ) ? $assoc_args['code'] : '';
		$codes = '' !== $code ? array( $code ) : Sessionize_Registry::codes();

		if ( empty( $codes ) ) {
			WP_CLI::warning( 'No Sessionize API codes found. Add a Sessionize block to a page first.' );
			return;
		}

		$failed = 0;

		foreach ( $codes as $api_code ) {
			$result = Sessionize_Store::refresh( $api_code );

			if ( is_wp_error( $result ) ) {
				++$failed;
				WP_CLI::warning( sprintf( '%s: %s', $api_code, $result->get_error_message() ) );
				continue;
			}

			Sessionize_Cron::purge_edge_cache( $api_code );

			$meta = Sessionize_Store::meta( $api_code );

			WP_CLI::log(
				sprintf(
					'%s: %d sessions, %d speakers, %s cached.',
					$api_code,
					isset( $meta['sessions'] ) ? (int) $meta['sessions'] : 0,
					isset( $meta['speakers'] ) ? (int) $meta['speakers'] : 0,
					size_format( isset( $meta['bytes'] ) ? (int) $meta['bytes'] : 0 )
				)
			);
		}

		if ( $failed > 0 ) {
			WP_CLI::error( sprintf( '%d of %d refreshes failed. Previously cached data was kept.', $failed, count( $codes ) ) );
		}

		WP_CLI::success( sprintf( 'Refreshed %d event(s).', count( $codes ) ) );
	}

	/**
	 * Shows the cache status for every known Sessionize API code.
	 *
	 * ## EXAMPLES
	 *
	 *     wp sessionize status
	 */
	public function status() {
		$codes = Sessionize_Registry::codes();

		if ( empty( $codes ) ) {
			WP_CLI::warning( 'No Sessionize API codes found.' );
			return;
		}

		$rows = array();

		foreach ( $codes as $api_code ) {
			$meta = Sessionize_Store::meta( $api_code );

			$rows[] = array(
				'code'         => $api_code,
				'last_success' => empty( $meta['last_success'] ) ? 'never' : human_time_diff( $meta['last_success'] ) . ' ago',
				'sessions'     => isset( $meta['sessions'] ) ? (int) $meta['sessions'] : 0,
				'speakers'     => isset( $meta['speakers'] ) ? (int) $meta['speakers'] : 0,
				'size'         => size_format( isset( $meta['bytes'] ) ? (int) $meta['bytes'] : 0 ),
				'last_error'   => empty( $meta['last_error'] ) ? '' : $meta['last_error'],
			);
		}

		WP_CLI\Utils\format_items( 'table', $rows, array( 'code', 'last_success', 'sessions', 'speakers', 'size', 'last_error' ) );
	}
}

WP_CLI::add_command( 'sessionize', 'Sessionize_CLI' );
