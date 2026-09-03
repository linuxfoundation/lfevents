<?php
/**
 * Scheduled refreshing of cached Sessionize data.
 *
 * Two entry points:
 *
 *  - `sessionize_refresh_all` runs on a recurring schedule and walks the registry.
 *  - `sessionize_refresh_event` is a single event queued by the stale-while-
 *    revalidate read path in Sessionize_Store::get().
 *
 * Note that on Pantheon, wp-cron is triggered roughly hourly rather than on the
 * declared interval, so the recurring schedule is a floor rather than a promise.
 * The single-event path is what actually keeps a busy event current.
 *
 * @package Sessionize_Blocks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wires up the cron schedule and handlers.
 */
class Sessionize_Cron {

	/**
	 * Recurring hook name.
	 *
	 * @var string
	 */
	const HOOK_ALL = 'sessionize_refresh_all';

	/**
	 * Single-event hook name.
	 *
	 * @var string
	 */
	const HOOK_ONE = 'sessionize_refresh_event';

	/**
	 * Custom schedule name.
	 *
	 * @var string
	 */
	const SCHEDULE = 'sessionize_quarter_hourly';

	/**
	 * Registers hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_filter( 'cron_schedules', array( __CLASS__, 'add_schedule' ) ); // phpcs:ignore WordPress.WP.CronInterval.ChangeDetected -- Intentional 15 minute schedule; see class docblock.
		add_action( self::HOOK_ALL, array( __CLASS__, 'refresh_all' ) );
		add_action( self::HOOK_ONE, array( __CLASS__, 'refresh_one' ) );
		add_action( 'init', array( __CLASS__, 'maybe_schedule' ) );
	}

	/**
	 * Adds the plugin's custom cron interval.
	 *
	 * @param array $schedules Existing schedules.
	 * @return array Schedules with the plugin's interval added.
	 */
	public static function add_schedule( $schedules ) {
		if ( ! isset( $schedules[ self::SCHEDULE ] ) ) {
			$schedules[ self::SCHEDULE ] = array(
				'interval' => 15 * MINUTE_IN_SECONDS,
				'display'  => __( 'Every 15 minutes (Sessionize)', 'sessionize-blocks' ),
			);
		}

		return $schedules;
	}

	/**
	 * Schedules the recurring refresh if it is not already scheduled.
	 *
	 * @return void
	 */
	public static function maybe_schedule() {
		if ( ! wp_next_scheduled( self::HOOK_ALL ) ) {
			wp_schedule_event( time() + MINUTE_IN_SECONDS, self::SCHEDULE, self::HOOK_ALL );
		}
	}

	/**
	 * Clears scheduled events. Called on plugin deactivation.
	 *
	 * @return void
	 */
	public static function unschedule() {
		wp_clear_scheduled_hook( self::HOOK_ALL );
	}

	/**
	 * Refreshes every registered event.
	 *
	 * @return void
	 */
	public static function refresh_all() {
		foreach ( Sessionize_Registry::codes() as $api_code ) {
			self::refresh_one( $api_code );
		}
	}

	/**
	 * Refreshes a single event and purges its pages from the edge cache.
	 *
	 * @param string $api_code Sessionize API code.
	 * @return void
	 */
	public static function refresh_one( $api_code ) {
		$result = Sessionize_Store::refresh( $api_code );

		if ( is_wp_error( $result ) ) {
			return;
		}

		self::purge_edge_cache( $api_code );
	}

	/**
	 * Clears the hosting edge cache for pages that embed this event.
	 *
	 * Without this the CDN keeps serving the previously rendered HTML long after
	 * the underlying data has been refreshed, which would defeat the point of
	 * rendering the schedule server-side.
	 *
	 * @param string $api_code Sessionize API code.
	 * @return void
	 */
	public static function purge_edge_cache( $api_code ) {
		if ( ! function_exists( 'pantheon_wp_clear_edge_paths' ) ) {
			return;
		}

		$paths = array();

		foreach ( Sessionize_Registry::post_ids( $api_code ) as $post_id ) {
			$permalink = get_permalink( $post_id );
			if ( ! $permalink ) {
				continue;
			}

			$path = wp_parse_url( $permalink, PHP_URL_PATH );
			if ( $path ) {
				$paths[] = $path;
			}
		}

		if ( ! empty( $paths ) ) {
			pantheon_wp_clear_edge_paths( array_values( array_unique( $paths ) ) );
		}
	}
}
