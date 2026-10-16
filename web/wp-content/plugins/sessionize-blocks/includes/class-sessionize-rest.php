<?php
/**
 * Editor REST endpoint.
 *
 * Gives the block editor the list of fields an event actually has in
 * Sessionize (categories and their values, custom questions, speakers and
 * speaker companies), so editors can pick them from dropdowns instead of typing exact
 * field names by hand.
 *
 * The request is made by WordPress on the server, through the same client and
 * cache the front end already uses, so the browser never talks to
 * sessionize.com directly and no CORS rules are involved.
 *
 * @package Sessionize_Blocks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers and serves the editor options endpoint.
 */
class Sessionize_Rest {

	/**
	 * REST namespace.
	 *
	 * @var string
	 */
	const NAMESPACE = 'sessionize-blocks/v1';

	/**
	 * Hooks into WordPress.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	/**
	 * Registers the routes.
	 *
	 * @return void
	 */
	public static function register_routes() {
		register_rest_route(
			self::NAMESPACE,
			'/options',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_options' ),
				'permission_callback' => array( __CLASS__, 'can_edit' ),
				'args'                => array(
					'api_code'      => array(
						'required'          => true,
						'type'              => 'string',
						'validate_callback' => array( __CLASS__, 'validate_api_code' ),
					),
					'refresh'       => array(
						'type'    => 'boolean',
						'default' => false,
					),
					'company_field' => array(
						'type'              => 'string',
						'default'           => '',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'company_override_field' => array(
						'type'              => 'string',
						'default'           => '',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'tagline_mode'  => array(
						'type'    => 'string',
						'default' => 'schedule',
						'enum'    => array( 'schedule', 'speakers' ),
					),
				),
			)
		);
	}

	/**
	 * Only people who can edit content may use the endpoint.
	 *
	 * @return bool
	 */
	public static function can_edit() {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Validates the API code argument.
	 *
	 * @param string $value Submitted API code.
	 * @return bool|WP_Error
	 */
	public static function validate_api_code( $value ) {
		if ( Sessionize_Client::is_valid_api_code( $value ) ) {
			return true;
		}

		return new WP_Error(
			'sessionize_invalid_api_code',
			__( 'That does not look like a Sessionize API code (letters and numbers only).', 'sessionize-blocks' ),
			array( 'status' => 400 )
		);
	}

	/**
	 * Returns the selectable options for one event.
	 *
	 * Without `refresh`, only the copy already cached by the plugin is read (the
	 * same copy the live page renders from), so the editor's automatic load
	 * never contacts Sessionize or writes anything; an event that has not been
	 * cached yet returns `notCached` and the editor offers a button instead.
	 *
	 * With `refresh` (the editor's button), Sessionize is queried again exactly
	 * as the Tools > Sessionize screen does: same permission, cache updated,
	 * and the hosting edge cache cleared so live pages show the new data.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function get_options( $request ) {
		$api_code = (string) $request->get_param( 'api_code' );
		$warning  = '';

		if ( $request->get_param( 'refresh' ) ) {
			if ( ! current_user_can( Sessionize_Admin::CAPABILITY ) ) {
				return new WP_Error(
					'sessionize_cannot_refresh',
					__( 'You do not have permission to refresh Sessionize data.', 'sessionize-blocks' ),
					array( 'status' => 403 )
				);
			}

			$had_data = is_array( Sessionize_Store::peek( $api_code ) );
			$had_meta = array() !== Sessionize_Store::meta( $api_code );
			$data     = Sessionize_Store::refresh( $api_code );

			if ( is_wp_error( $data ) ) {
				if ( ! $had_data ) {
					// Nothing was stored for this code before (usually a mistyped
					// code), so don't leave an error record behind for it.
					if ( ! $had_meta ) {
						Sessionize_Store::delete( $api_code );
					}

					return new WP_Error(
						'sessionize_fetch_failed',
						sprintf(
							/* translators: %s: error message from Sessionize. */
							__( 'Could not load this event from Sessionize. Check the API code. (%s)', 'sessionize-blocks' ),
							$data->get_error_message()
						),
						array( 'status' => 502 )
					);
				}

				// Keep showing the last good copy, with a warning.
				$warning = $data->get_error_message();
				$data    = Sessionize_Store::peek( $api_code );
			} else {
				Sessionize_Cron::purge_edge_cache( $api_code );
			}
		} else {
			$data = Sessionize_Store::peek( $api_code );

			if ( ! is_array( $data ) ) {
				return rest_ensure_response( array( 'notCached' => true ) );
			}
		}

		if ( ! is_array( $data ) || empty( $data['all'] ) ) {
			return new WP_Error(
				'sessionize_no_data',
				__( 'Sessionize returned no data for this API code.', 'sessionize-blocks' ),
				array( 'status' => 502 )
			);
		}

		$all = $data['all'];

		return rest_ensure_response(
			array(
				'fetchedAt'  => isset( $data['fetched_at'] ) ? (int) $data['fetched_at'] : 0,
				'warning'    => $warning,
				'categories' => self::categories( $all ),
				'questions'  => self::questions( $all ),
				'companies'  => self::companies(
					$all,
					sched_question_ref( $request->get_param( 'company_field' ) ),
					sched_question_ref( $request->get_param( 'company_override_field' ) ),
					'speakers' === $request->get_param( 'tagline_mode' )
				),
				'speakers'   => self::speakers( $all ),
				'counts'     => array(
					'sessions' => isset( $all['sessions'] ) ? count( $all['sessions'] ) : 0,
					'speakers' => isset( $all['speakers'] ) ? count( $all['speakers'] ) : 0,
				),
			)
		);
	}

	/**
	 * Lists categories (e.g. Track, Session Type) and their values.
	 *
	 * @param array $all Normalized "all" payload.
	 * @return array List of { title, items[] }.
	 */
	private static function categories( $all ) {
		$out = array();

		foreach ( isset( $all['categories'] ) ? (array) $all['categories'] : array() as $category ) {
			if ( ! is_array( $category ) || empty( $category['title'] ) ) {
				continue;
			}

			$items = array();
			foreach ( isset( $category['items'] ) ? (array) $category['items'] : array() as $item ) {
				$name = is_array( $item ) && isset( $item['name'] ) ? trim( (string) $item['name'] ) : '';
				if ( '' !== $name ) {
					$items[] = $name;
				}
			}

			$out[] = array(
				'title' => trim( (string) $category['title'] ),
				'items' => array_values( array_unique( $items ) ),
			);
		}

		return $out;
	}

	/**
	 * Lists custom questions, tagged with whether they hold session or speaker data.
	 *
	 * The "All" view does not say which form a question belongs to, so this is
	 * worked out from where answers to it actually appear. A question nobody has
	 * answered yet comes back as 'unknown' and is offered everywhere.
	 *
	 * @param array $all Normalized "all" payload.
	 * @return array List of { id, title, type, scope }.
	 */
	private static function questions( $all ) {
		$on_sessions = self::answered_question_ids( isset( $all['sessions'] ) ? $all['sessions'] : array() );
		$on_speakers = self::answered_question_ids( isset( $all['speakers'] ) ? $all['speakers'] : array() );

		$out = array();

		foreach ( isset( $all['questions'] ) ? (array) $all['questions'] : array() as $question ) {
			if ( ! is_array( $question ) || ! isset( $question['id'] ) ) {
				continue;
			}

			$id = (int) $question['id'];
			if ( $id <= 0 ) {
				continue;
			}

			$title = '';
			if ( isset( $question['question'] ) ) {
				$title = trim( (string) $question['question'] );
			} elseif ( isset( $question['name'] ) ) {
				$title = trim( (string) $question['name'] );
			}

			$session = isset( $on_sessions[ $id ] );
			$speaker = isset( $on_speakers[ $id ] );

			if ( $session && $speaker ) {
				$scope = 'both';
			} elseif ( $session ) {
				$scope = 'session';
			} elseif ( $speaker ) {
				$scope = 'speaker';
			} else {
				$scope = 'unknown';
			}

			$out[] = array(
				'id'    => $id,
				'title' => '' !== $title ? $title : (string) $id,
				'type'  => isset( $question['questionType'] ) ? (string) $question['questionType'] : '',
				'scope' => $scope,
			);
		}

		return $out;
	}

	/**
	 * Collects the ids of every question answered on a set of records.
	 *
	 * @param array $records Sessions or speakers.
	 * @return array Set of question ids (as keys).
	 */
	private static function answered_question_ids( $records ) {
		$ids = array();

		foreach ( (array) $records as $record ) {
			if ( ! is_array( $record ) || empty( $record['questionAnswers'] ) ) {
				continue;
			}

			foreach ( (array) $record['questionAnswers'] as $row ) {
				$id = null;
				if ( is_array( $row ) ) {
					if ( isset( $row['questionId'] ) ) {
						$id = (int) $row['questionId'];
					} elseif ( isset( $row['id'] ) ) {
						$id = (int) $row['id'];
					} elseif ( isset( $row['question']['id'] ) ) {
						$id = (int) $row['question']['id'];
					}
				}
				if ( $id ) {
					$ids[ $id ] = true;
				}
			}
		}

		return $ids;
	}

	/**
	 * Lists speaker company names, worked out the same way the front end does
	 * (override field, then company field, then the speaker's tagline).
	 *
	 * @param array           $all          Normalized "all" payload.
	 * @param int|string|null $company_ref  Configured company question.
	 * @param int|string|null $override_ref Configured company override question.
	 * @param bool            $strict       Speakers-block tagline rule (see company_from_tagline()).
	 * @return array Sorted, de-duplicated company names.
	 */
	private static function companies( $all, $company_ref, $override_ref, $strict = false ) {
		$title_map = Sessionize_Normalizer::question_title_map( $all );
		$names     = array();

		foreach ( isset( $all['speakers'] ) ? (array) $all['speakers'] : array() as $speaker ) {
			if ( ! is_array( $speaker ) ) {
				continue;
			}

			$company = '';
			if ( null !== $override_ref ) {
				$company = Sessionize_Normalizer::answer( $speaker, $override_ref, $title_map );
			}
			if ( '' === $company && null !== $company_ref ) {
				$company = Sessionize_Normalizer::answer( $speaker, $company_ref, $title_map );
			}
			if ( '' === $company ) {
				$tagline = isset( $speaker['tagLine'] ) ? $speaker['tagLine'] : ( isset( $speaker['tagline'] ) ? $speaker['tagline'] : '' );
				$company = self::company_from_tagline( $tagline, $strict );
			}

			$company = trim( $company );
			if ( '' !== $company ) {
				$names[ strtolower( $company ) ] = $company;
			}
		}

		$names = array_values( $names );
		natcasesort( $names );

		return array_values( $names );
	}

	/**
	 * Lists every speaker's display name and whether they are marked as a
	 * Top Speaker in Sessionize, so the Speakers block's editor can limit its
	 * suggestions to whoever the page will actually show.
	 *
	 * @param array $all Normalized "all" payload.
	 * @return array List of { name, top }, sorted by name.
	 */
	private static function speakers( $all ) {
		$out = array();

		foreach ( isset( $all['speakers'] ) ? (array) $all['speakers'] : array() as $speaker ) {
			if ( ! is_array( $speaker ) ) {
				continue;
			}

			// Same rule as sz_speakers_full_name() in the Speakers block.
			if ( ! empty( $speaker['fullName'] ) ) {
				$name = trim( (string) $speaker['fullName'] );
			} else {
				$first = isset( $speaker['firstName'] ) ? (string) $speaker['firstName'] : '';
				$last  = isset( $speaker['lastName'] ) ? (string) $speaker['lastName'] : '';
				$name  = trim( $first . ' ' . $last );
			}

			if ( '' === $name ) {
				continue;
			}

			$out[] = array(
				'name' => $name,
				'top'  => ! empty( $speaker['isTopSpeaker'] ),
			);
		}

		usort(
			$out,
			function ( $a, $b ) {
				return strnatcasecmp( $a['name'], $b['name'] );
			}
		);

		return $out;
	}

	/**
	 * Reads a company out of a speaker tagline.
	 *
	 * Mirrors parseCompanyFromTagline() in the schedule block's view.js, which
	 * falls back to the whole tagline. With $strict it instead mirrors
	 * sz_speakers_company_from_tagline() in the Speakers block, which returns
	 * nothing when there is no "at"/"@" or comma to go on.
	 *
	 * @param string $tagline Speaker tagline.
	 * @param bool   $strict  Speakers-block rule.
	 * @return string Company name.
	 */
	private static function company_from_tagline( $tagline, $strict = false ) {
		$t = trim( (string) $tagline );
		if ( '' === $t ) {
			return '';
		}

		if ( preg_match( '/\s(?:at|@)\s(.+)$/i', $t, $m ) ) {
			return trim( $m[1] );
		}

		$parts = array_values( array_filter( array_map( 'trim', explode( ',', $t ) ) ) );
		if ( count( $parts ) >= 2 ) {
			return end( $parts );
		}

		return $strict ? '' : $t;
	}
}
