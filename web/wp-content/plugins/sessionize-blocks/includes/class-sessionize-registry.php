<?php
/**
 * Tracks which Sessionize API codes are actually in use on this site.
 *
 * Cron needs to know what to keep warm, and the edge-cache purge needs to know
 * which URLs to clear after a refresh. Both come from here.
 *
 * @package Sessionize_Blocks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registry of Sessionize API codes and the posts that use them.
 */
class Sessionize_Registry {

	/**
	 * Option holding the registry.
	 *
	 * @var string
	 */
	const OPTION = 'sessionize_api_codes';

	/**
	 * Block names to look for when scanning post content.
	 *
	 * @var array
	 */
	const BLOCK_NAMES = array(
		'custom/sessionize-schedule',
		'custom/sessionize-speakers',
	);

	/**
	 * Registers hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'save_post', array( __CLASS__, 'scan_post' ), 20, 2 );
		add_action( 'deleted_post', array( __CLASS__, 'forget_post' ) );
	}

	/**
	 * Returns the whole registry.
	 *
	 * @return array Map of API code to array with post_ids and last_seen.
	 */
	public static function all() {
		$registry = get_option( self::OPTION );

		return is_array( $registry ) ? $registry : array();
	}

	/**
	 * Returns every registered API code.
	 *
	 * @return array List of API codes.
	 */
	public static function codes() {
		return array_keys( self::all() );
	}

	/**
	 * Returns the post ids known to use an API code.
	 *
	 * @param string $api_code Sessionize API code.
	 * @return array List of post ids.
	 */
	public static function post_ids( $api_code ) {
		$registry = self::all();

		if ( ! isset( $registry[ $api_code ]['post_ids'] ) || ! is_array( $registry[ $api_code ]['post_ids'] ) ) {
			return array();
		}

		return array_map( 'intval', $registry[ $api_code ]['post_ids'] );
	}

	/**
	 * Records that a post uses an API code.
	 *
	 * Called from the block render callbacks as well as from save_post, so codes
	 * still get registered for content that arrives some other way (an import, a
	 * direct database edit, a reusable block). The option is only written when
	 * something actually changed, so this stays cheap on a normal pageview.
	 *
	 * @param string $api_code Sessionize API code.
	 * @param int    $post_id  Post id, or 0 when unknown.
	 * @return void
	 */
	public static function remember( $api_code, $post_id = 0 ) {
		if ( ! Sessionize_Client::is_valid_api_code( $api_code ) ) {
			return;
		}

		$registry = self::all();
		$post_id  = (int) $post_id;
		$changed  = false;

		if ( ! isset( $registry[ $api_code ] ) || ! is_array( $registry[ $api_code ] ) ) {
			$registry[ $api_code ] = array(
				'post_ids'  => array(),
				'last_seen' => 0,
			);
			$changed               = true;
		}

		$post_ids = isset( $registry[ $api_code ]['post_ids'] ) && is_array( $registry[ $api_code ]['post_ids'] )
			? array_map( 'intval', $registry[ $api_code ]['post_ids'] )
			: array();

		if ( $post_id > 0 && ! in_array( $post_id, $post_ids, true ) ) {
			$post_ids[]                        = $post_id;
			$registry[ $api_code ]['post_ids'] = array_values( $post_ids );
			$changed                           = true;
		}

		$last_seen = isset( $registry[ $api_code ]['last_seen'] ) ? (int) $registry[ $api_code ]['last_seen'] : 0;
		if ( ( time() - $last_seen ) > DAY_IN_SECONDS ) {
			$registry[ $api_code ]['last_seen'] = time();
			$changed                            = true;
		}

		if ( $changed ) {
			update_option( self::OPTION, $registry, false );
		}
	}

	/**
	 * Rescans a post for Sessionize blocks when it is saved.
	 *
	 * @param int     $post_id Post id.
	 * @param WP_Post $post    Post object.
	 * @return void
	 */
	public static function scan_post( $post_id, $post ) {
		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return;
		}

		if ( ! $post instanceof WP_Post ) {
			return;
		}

		self::forget_post( $post_id );

		foreach ( self::find_api_codes( $post->post_content ) as $api_code ) {
			self::remember( $api_code, $post_id );
			if ( null === Sessionize_Store::peek( $api_code ) ) {
				Sessionize_Store::queue_refresh( $api_code );
			}
		}
	}

	/**
	 * Removes a post from every API code's post list.
	 *
	 * The API code itself is kept, since another post may still use it and the
	 * cached data is worth holding onto either way.
	 *
	 * @param int $post_id Post id.
	 * @return void
	 */
	public static function forget_post( $post_id ) {
		$registry = self::all();
		$post_id  = (int) $post_id;
		$changed  = false;

		foreach ( $registry as $api_code => $entry ) {
			if ( ! isset( $entry['post_ids'] ) || ! is_array( $entry['post_ids'] ) ) {
				continue;
			}

			$filtered = array_values(
				array_filter(
					array_map( 'intval', $entry['post_ids'] ),
					function ( $id ) use ( $post_id ) {
						return $id !== $post_id;
					}
				)
			);

			if ( count( $filtered ) !== count( $entry['post_ids'] ) ) {
				$registry[ $api_code ]['post_ids'] = $filtered;
				$changed                           = true;
			}
		}

		if ( $changed ) {
			update_option( self::OPTION, $registry, false );
		}
	}

	/**
	 * Extracts Sessionize API codes from post content.
	 *
	 * @param string $content Post content.
	 * @return array List of unique API codes.
	 */
	public static function find_api_codes( $content ) {
		if ( ! has_blocks( $content ) ) {
			return array();
		}

		$codes = array();
		self::walk_blocks( parse_blocks( $content ), $codes );

		return array_values( array_unique( $codes ) );
	}

	/**
	 * Recursively collects API codes out of a parsed block tree.
	 *
	 * @param array $blocks Parsed blocks.
	 * @param array $codes  Collected codes, by reference.
	 * @return void
	 */
	private static function walk_blocks( $blocks, &$codes ) {
		foreach ( $blocks as $block ) {
			if ( isset( $block['blockName'] ) && in_array( $block['blockName'], self::BLOCK_NAMES, true ) ) {
				$api_code = isset( $block['attrs']['apiCode'] ) ? (string) $block['attrs']['apiCode'] : '';

				if ( Sessionize_Client::is_valid_api_code( $api_code ) ) {
					$codes[] = $api_code;
				}
			}

			if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
				self::walk_blocks( $block['innerBlocks'], $codes );
			}
		}
	}
}
