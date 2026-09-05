<?php
/**
 * Durable cache for normalized Sessionize event data.
 *
 * Design notes:
 *
 *  - The payload lives in a non-autoloaded **option**, gzipped, rather than in a
 *    transient. A large event's normalized payload can exceed the ~1MB per-key
 *    limit of a Redis object cache, where an oversized transient is silently
 *    dropped and every pageview turns into a cache miss. An option is backed by
 *    the database, so the data survives regardless of object-cache limits.
 *
 *  - Reads are stale-while-revalidate. A page render returns whatever is stored,
 *    even if it is past its TTL, and schedules a background refresh. A normal
 *    pageview must never block on sessionize.com being reachable or fast.
 *
 *  - A failed refresh never clears good data. The last successful payload keeps
 *    being served, so a Sessionize outage leaves the published schedule intact
 *    instead of emptying it.
 *
 * @package Sessionize_Blocks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stores and retrieves cached Sessionize payloads.
 */
class Sessionize_Store {

	/**
	 * Prefix for the option holding a single event's payload.
	 *
	 * @var string
	 */
	const OPTION_PREFIX = 'sessionize_data_';

	/**
	 * Prefix for the option holding a single event's sync metadata.
	 *
	 * @var string
	 */
	const META_PREFIX = 'sessionize_meta_';

	/**
	 * Default cache lifetime, in seconds, before a background refresh is queued.
	 *
	 * @var int
	 */
	const DEFAULT_TTL = 900;

	/**
	 * How long a cold-start fetch lock is held, in seconds.
	 *
	 * @var int
	 */
	const LOCK_TTL = 60;

	/**
	 * In-request memoization, so several blocks on one page decode only once.
	 *
	 * @var array
	 */
	private static $memo = array();

	/**
	 * Returns the option name holding an event's payload.
	 *
	 * The API code is hashed to keep the option name within the column length and
	 * free of characters that would need escaping.
	 *
	 * @param string $api_code Sessionize API code.
	 * @return string Option name.
	 */
	private static function data_option( $api_code ) {
		return self::OPTION_PREFIX . md5( strtolower( (string) $api_code ) );
	}

	/**
	 * Returns the option name holding an event's sync metadata.
	 *
	 * @param string $api_code Sessionize API code.
	 * @return string Option name.
	 */
	private static function meta_option( $api_code ) {
		return self::META_PREFIX . md5( strtolower( (string) $api_code ) );
	}

	/**
	 * Returns the option name holding an event's cold-start fetch lock.
	 *
	 * @param string $api_code Sessionize API code.
	 * @return string Option name.
	 */
	private static function lock_option( $api_code ) {
		return 'sessionize_lock_' . md5( strtolower( (string) $api_code ) );
	}

	/**
	 * Returns the cache lifetime in seconds.
	 *
	 * @return int TTL in seconds.
	 */
	public static function ttl() {
		/**
		 * Filters how long cached Sessionize data is considered fresh.
		 *
		 * Past this age the stored copy is still served, but a background refresh
		 * is queued.
		 *
		 * @param int $ttl Lifetime in seconds.
		 */
		$ttl = (int) apply_filters( 'sessionize_cache_ttl', self::DEFAULT_TTL );

		return max( 60, $ttl );
	}

	/**
	 * Reads the stored payload for an event without triggering any refresh.
	 *
	 * @param string $api_code Sessionize API code.
	 * @return array|null Normalized payload, or null when nothing is stored.
	 */
	public static function peek( $api_code ) {
		$key = strtolower( (string) $api_code );

		if ( array_key_exists( $key, self::$memo ) ) {
			return self::$memo[ $key ];
		}

		$stored = get_option( self::data_option( $api_code ) );
		$data   = null;

		if ( is_string( $stored ) && '' !== $stored ) {
			$data = self::decode( $stored );
		}

		if ( is_array( $data ) && isset( $data['version'] ) && Sessionize_Normalizer::VERSION !== (int) $data['version'] ) {
			// Cached under an older schema; treat as absent so it gets rebuilt.
			$data = null;
		}

		self::$memo[ $key ] = $data;

		return $data;
	}

	/**
	 * Gets event data for rendering.
	 *
	 * Returns the stored copy immediately when there is one, queueing a background
	 * refresh if it is stale. Only a genuine cold start — nothing stored at all —
	 * performs a blocking fetch, and that is guarded by a lock so concurrent
	 * requests do not stampede the Sessionize API.
	 *
	 * @param string $api_code Sessionize API code.
	 * @return array|null Normalized payload, or null when unavailable.
	 */
	public static function get( $api_code ) {
		if ( ! Sessionize_Client::is_valid_api_code( $api_code ) ) {
			return null;
		}

		$data = self::peek( $api_code );

		if ( is_array( $data ) ) {
			$fetched_at = isset( $data['fetched_at'] ) ? (int) $data['fetched_at'] : 0;

			if ( ( time() - $fetched_at ) > self::ttl() ) {
				self::queue_refresh( $api_code );
			}

			return $data;
		}

		// Cold start: nothing cached at all. One request pays the latency.
		$lock = self::acquire_lock( $api_code );

		if ( false === $lock ) {
			return null;
		}

		$refreshed = self::refresh( $api_code );
		self::release_lock( $api_code, $lock );

		return is_wp_error( $refreshed ) ? null : $refreshed;
	}

	/**
	 * Queues a background refresh for an event.
	 *
	 * @param string $api_code Sessionize API code.
	 * @return void
	 */
	public static function queue_refresh( $api_code ) {
		$args = array( (string) $api_code );

		if ( wp_next_scheduled( 'sessionize_refresh_event', $args ) ) {
			return;
		}

		wp_schedule_single_event( time(), 'sessionize_refresh_event', $args );
	}

	/**
	 * Fetches, normalizes and stores fresh data for an event.
	 *
	 * On failure the previously stored payload is left untouched, so the site
	 * keeps serving the last known good schedule.
	 *
	 * @param string $api_code Sessionize API code.
	 * @return array|WP_Error Normalized payload, or WP_Error on failure.
	 */
	public static function refresh( $api_code ) {
		if ( ! Sessionize_Client::is_valid_api_code( $api_code ) ) {
			return new WP_Error(
				'sessionize_invalid_api_code',
				__( 'Invalid Sessionize API code.', 'sessionize-blocks' )
			);
		}

		$raw = Sessionize_Client::fetch_event( $api_code );

		if ( is_wp_error( $raw ) ) {
			self::record_failure( $api_code, $raw->get_error_message() );
			return $raw;
		}

		$data    = Sessionize_Normalizer::normalize( $raw );
		$encoded = self::encode( $data );

		if ( null === $encoded ) {
			$error = new WP_Error(
				'sessionize_encode_failed',
				__( 'Could not encode the normalized Sessionize payload.', 'sessionize-blocks' )
			);
			self::record_failure( $api_code, $error->get_error_message() );
			return $error;
		}

		// Refuse to overwrite a good payload with an empty one. An event with no
		// sessions is almost always a partial or erroring upstream response.
		if ( empty( $data['all']['sessions'] ) && is_array( self::peek( $api_code ) ) ) {
			$error = new WP_Error(
				'sessionize_empty_result',
				__( 'Sessionize returned no sessions; keeping the previously cached data.', 'sessionize-blocks' )
			);
			self::record_failure( $api_code, $error->get_error_message() );
			return $error;
		}

		update_option( self::data_option( $api_code ), $encoded, false );

		self::$memo[ strtolower( (string) $api_code ) ] = $data;

		update_option(
			self::meta_option( $api_code ),
			array(
				'last_success'  => time(),
				'bytes'         => strlen( $encoded ),
				'sessions'      => count( $data['all']['sessions'] ),
				'speakers'      => count( $data['all']['speakers'] ),
				'last_error'    => '',
				'last_error_at' => 0,
			),
			false
		);

		/**
		 * Fires after an event's cached data has been refreshed successfully.
		 *
		 * @param string $api_code Sessionize API code.
		 * @param array  $data     The normalized payload.
		 */
		do_action( 'sessionize_data_refreshed', (string) $api_code, $data );

		return $data;
	}

	/**
	 * Records a failed refresh without disturbing the stored payload.
	 *
	 * @param string $api_code Sessionize API code.
	 * @param string $message  Error message.
	 * @return void
	 */
	private static function record_failure( $api_code, $message ) {
		$meta = get_option( self::meta_option( $api_code ) );
		if ( ! is_array( $meta ) ) {
			$meta = array();
		}

		$meta['last_error']    = (string) $message;
		$meta['last_error_at'] = time();

		update_option( self::meta_option( $api_code ), $meta, false );
	}

	/**
	 * Returns sync metadata for an event.
	 *
	 * @param string $api_code Sessionize API code.
	 * @return array Metadata with last_success, bytes, sessions, speakers, last_error, last_error_at.
	 */
	public static function meta( $api_code ) {
		$meta = get_option( self::meta_option( $api_code ) );

		return is_array( $meta ) ? $meta : array();
	}

	/**
	 * Deletes all stored data for an event.
	 *
	 * @param string $api_code Sessionize API code.
	 * @return void
	 */
	public static function delete( $api_code ) {
		delete_option( self::data_option( $api_code ) );
		delete_option( self::meta_option( $api_code ) );
		unset( self::$memo[ strtolower( (string) $api_code ) ] );
	}

	/**
	 * Serializes a payload for storage.
	 *
	 * Gzipped so a large event stays well clear of any row or object-cache size
	 * ceiling, then base64'd because the option value goes through database and
	 * cache layers that expect text.
	 *
	 * @param array $data Normalized payload.
	 * @return string|null Encoded payload, or null on failure.
	 */
	private static function encode( $data ) {
		$json = wp_json_encode( $data );
		if ( false === $json ) {
			return null;
		}

		if ( ! function_exists( 'gzencode' ) ) {
			return 'raw:' . $json;
		}

		$gz = gzencode( $json, 6 );
		if ( false === $gz ) {
			return null;
		}

		// Not obfuscation: gzencode() returns binary, and the option value has to
		// survive database and object-cache layers that expect text.
		return 'gz:' . base64_encode( $gz ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
	}

	/**
	 * Reverses encode().
	 *
	 * @param string $stored Stored option value.
	 * @return array|null Decoded payload, or null when it cannot be read.
	 */
	private static function decode( $stored ) {
		if ( 0 === strpos( $stored, 'raw:' ) ) {
			$json = substr( $stored, 4 );
		} elseif ( 0 === strpos( $stored, 'gz:' ) ) {
			if ( ! function_exists( 'gzdecode' ) ) {
				return null;
			}

			$binary = base64_decode( substr( $stored, 3 ), true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode -- Reverses encode(); see above.
			if ( false === $binary ) {
				return null;
			}

			$json = gzdecode( $binary );
			if ( false === $json ) {
				return null;
			}
		} else {
			return null;
		}

		$data = json_decode( $json, true );

		return is_array( $data ) ? $data : null;
	}

	/**
	 * Takes the cold-start fetch lock for an event.
	 *
	 * The Options API cannot arbitrate this. add_option()'s INSERT uses ON
	 * DUPLICATE KEY UPDATE, so the only thing standing between two concurrent
	 * callers is the check-then-act get_option() guard inside it — both can read
	 * "absent" and both can then be told they created the row. Locking is
	 * therefore done with statements MySQL itself makes atomic: INSERT IGNORE
	 * against the unique index on option_name to claim a free lock, and a
	 * compare-and-swap UPDATE to take over an expired one. Exactly one caller
	 * sees a row affected in either case.
	 *
	 * This mirrors WP_Upgrader::create_lock(), which lives in wp-admin and so is
	 * not loadable from a front-end render.
	 *
	 * @param string $api_code Sessionize API code.
	 * @return string|false Opaque lock value to pass to release_lock(), or false
	 *                      when another request holds the lock.
	 */
	private static function acquire_lock( $api_code ) {
		global $wpdb;

		$key   = self::lock_option( $api_code );
		$value = wp_generate_password( 20, false ) . ':' . time();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Lock acquisition has to be atomic in the database; the Options API cannot express it.
		$claimed = $wpdb->query( $wpdb->prepare( "INSERT IGNORE INTO {$wpdb->options} (option_name, option_value, autoload) VALUES (%s, %s, 'no')", $key, $value ) );

		self::flush_lock_cache( $key );

		if ( $claimed ) {
			return $value;
		}

		$current = get_option( $key );

		if ( ! is_string( $current ) || '' === $current ) {
			// The holder released it in the moment between the INSERT and this
			// read. Bow out rather than racing for it; the next request will claim
			// it cleanly.
			return false;
		}

		$held_since = (int) substr( (string) strrchr( $current, ':' ), 1 );

		if ( $held_since > ( time() - self::LOCK_TTL ) ) {
			return false;
		}

		/*
		 * The lock has expired, which means its holder died mid-fetch. Take it
		 * over with a compare-and-swap keyed on the exact stale value, so that if
		 * several requests all spot the same expired lock only the one whose
		 * UPDATE matches first is granted it. A read-then-write would hand the
		 * lock to all of them.
		 */
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- See above; this is the compare-and-swap.
		$taken = $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->options} SET option_value = %s WHERE option_name = %s AND option_value = %s", $value, $key, $current ) );

		self::flush_lock_cache( $key );

		return $taken ? $value : false;
	}

	/**
	 * Releases the cold-start fetch lock for an event.
	 *
	 * The delete is conditional on the caller still holding the lock. A request
	 * that overran LOCK_TTL and had its lock taken over must not delete the new
	 * owner's lock on its way out.
	 *
	 * @param string $api_code   Sessionize API code.
	 * @param string $lock_value Value returned by acquire_lock().
	 * @return void
	 */
	private static function release_lock( $api_code, $lock_value ) {
		global $wpdb;

		$key = self::lock_option( $api_code );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Ownership check and delete must be one atomic statement.
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name = %s AND option_value = %s", $key, $lock_value ) );

		self::flush_lock_cache( $key );
	}

	/**
	 * Drops the object-cache entries for a lock option.
	 *
	 * The lock is written with direct SQL, so the Options API's caches have to be
	 * invalidated by hand — including `notoptions`, which would otherwise keep
	 * reporting a freshly inserted lock as absent.
	 *
	 * @param string $key Lock option name.
	 * @return void
	 */
	private static function flush_lock_cache( $key ) {
		wp_cache_delete( $key, 'options' );
		wp_cache_delete( 'notoptions', 'options' );
	}
}
