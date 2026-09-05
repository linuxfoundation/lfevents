<?php
/**
 * Normalizes raw Sessionize API responses into the payload the blocks consume.
 *
 * The normalized "all" payload deliberately keeps the same top-level shape as
 * the Sessionize `/view/All` response (sessions, speakers, rooms, categories,
 * questions). The front-end scripts already understand that shape, so keeping it
 * means swapping their network fetch for an inline JSON read is a small, low-risk
 * change rather than a rewrite of ~5,700 lines of view code.
 *
 * Two things do change:
 *
 *  1. Session descriptions are sanitized here, once, into allowlisted HTML. That
 *     also removes the risk of the PHP and JS Markdown-lite renderers drifting
 *     apart: the browser now receives HTML that PHP already rendered, so both
 *     sides display exactly the same thing.
 *  2. The GridSmart payload is slimmed hard. The grid renderer only ever reads
 *     room/slot structure and session *ids* out of it, looking the real session
 *     up in its own derived map, so shipping a second full copy of every session
 *     would roughly double the payload for no benefit.
 *
 * @package Sessionize_Blocks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Turns raw Sessionize responses into a cacheable, browser-ready payload.
 */
class Sessionize_Normalizer {

	/**
	 * Payload schema version.
	 *
	 * Bump this whenever the normalized shape changes in a way that older cached
	 * copies could not satisfy; the store treats a mismatch as a cache miss.
	 *
	 * @var int
	 */
	const VERSION = 1;

	/**
	 * Normalizes a raw client response.
	 *
	 * @param array $raw Result of Sessionize_Client::fetch_event().
	 * @return array Normalized payload.
	 */
	public static function normalize( $raw ) {
		$all  = isset( $raw['all'] ) && is_array( $raw['all'] ) ? $raw['all'] : array();
		$grid = isset( $raw['grid'] ) && is_array( $raw['grid'] ) ? $raw['grid'] : null;

		return array(
			'version'    => self::VERSION,
			'fetched_at' => time(),
			'all'        => self::normalize_all( $all ),
			'grid'       => null === $grid ? null : self::normalize_grid( $grid ),
		);
	}

	/**
	 * Normalizes the `/view/All` payload.
	 *
	 * @param array $all Raw "All" response.
	 * @return array Normalized payload with sessions, speakers, rooms, categories, questions.
	 */
	private static function normalize_all( $all ) {
		$sessions = self::array_of( $all, 'sessions' );

		foreach ( $sessions as $index => $session ) {
			if ( ! is_array( $session ) ) {
				unset( $sessions[ $index ] );
				continue;
			}

			$description                       = isset( $session['description'] ) ? $session['description'] : '';
			$sessions[ $index ]['description'] = Sessionize_Sanitizer::description( $description );
		}

		return array(
			'sessions'   => array_values( $sessions ),
			'speakers'   => self::array_of( $all, 'speakers' ),
			'rooms'      => self::array_of( $all, 'rooms' ),
			'categories' => self::array_of( $all, 'categories' ),
			'questions'  => self::array_of( $all, 'questions' ),
		);
	}

	/**
	 * Slims the GridSmart payload down to the structure the grid view actually reads.
	 *
	 * @param array $grid Raw "GridSmart" response.
	 * @return array Slimmed grid days.
	 */
	private static function normalize_grid( $grid ) {
		$days = array();

		foreach ( $grid as $day ) {
			if ( ! is_array( $day ) ) {
				continue;
			}

			$rooms = array();
			foreach ( self::array_of( $day, 'rooms' ) as $room ) {
				if ( ! is_array( $room ) ) {
					continue;
				}

				$room_sessions = array();
				foreach ( self::array_of( $room, 'sessions' ) as $session ) {
					if ( is_array( $session ) && isset( $session['id'] ) ) {
						$room_sessions[] = array( 'id' => $session['id'] );
					}
				}

				$rooms[] = array(
					'id'       => isset( $room['id'] ) ? $room['id'] : null,
					'name'     => isset( $room['name'] ) ? (string) $room['name'] : '',
					'sessions' => $room_sessions,
				);
			}

			$time_slots = array();
			foreach ( self::array_of( $day, 'timeSlots' ) as $slot ) {
				if ( ! is_array( $slot ) ) {
					continue;
				}

				$slot_rooms = array();
				foreach ( self::array_of( $slot, 'rooms' ) as $slot_room ) {
					if ( ! is_array( $slot_room ) ) {
						continue;
					}

					$session_id = null;
					if ( isset( $slot_room['session'] ) && is_array( $slot_room['session'] ) && isset( $slot_room['session']['id'] ) ) {
						$session_id = $slot_room['session']['id'];
					}

					if ( null === $session_id ) {
						continue;
					}

					$slot_rooms[] = array(
						'id'      => isset( $slot_room['id'] ) ? $slot_room['id'] : null,
						'session' => array( 'id' => $session_id ),
					);
				}

				$time_slots[] = array(
					'slotStart' => isset( $slot['slotStart'] ) ? (string) $slot['slotStart'] : '',
					'rooms'     => $slot_rooms,
				);
			}

			$days[] = array(
				'date'      => isset( $day['date'] ) ? (string) $day['date'] : '',
				'rooms'     => $rooms,
				'timeSlots' => $time_slots,
			);
		}

		return $days;
	}

	/**
	 * Reads a key from an array and guarantees a list back.
	 *
	 * @param array  $source Source array.
	 * @param string $key    Key to read.
	 * @return array List value, or an empty array.
	 */
	private static function array_of( $source, $key ) {
		if ( ! isset( $source[ $key ] ) || ! is_array( $source[ $key ] ) ) {
			return array();
		}

		return $source[ $key ];
	}

	/**
	 * Builds a lookup of normalized question title => numeric question id.
	 *
	 * Mirrors normalizeQuestionLookupKey() in the view scripts, so a block can be
	 * configured with either a numeric Sessionize question id or the human
	 * readable question title.
	 *
	 * @param array $all Normalized "all" payload.
	 * @return array Map of lowercased question title to question id.
	 */
	public static function question_title_map( $all ) {
		$map = array();

		foreach ( self::array_of( $all, 'questions' ) as $question ) {
			if ( ! is_array( $question ) || ! isset( $question['id'] ) ) {
				continue;
			}

			$question_id = (int) $question['id'];
			if ( $question_id <= 0 ) {
				continue;
			}

			$candidates = array();
			if ( isset( $question['question'] ) ) {
				$candidates[] = $question['question'];
			}
			if ( isset( $question['name'] ) ) {
				$candidates[] = $question['name'];
			}

			foreach ( $candidates as $candidate ) {
				$key = strtolower( trim( (string) $candidate ) );
				if ( '' !== $key && ! isset( $map[ $key ] ) ) {
					$map[ $key ] = $question_id;
				}
			}
		}

		return $map;
	}

	/**
	 * Resolves a configured question reference to a numeric question id.
	 *
	 * @param int|string|null $ref       Numeric id, question title, or null.
	 * @param array           $title_map Output of question_title_map().
	 * @return int|null Question id, or null when it cannot be resolved.
	 */
	public static function resolve_question_id( $ref, $title_map ) {
		if ( null === $ref || '' === $ref ) {
			return null;
		}

		if ( is_numeric( $ref ) ) {
			$id = (int) $ref;
			return $id > 0 ? $id : null;
		}

		$key = strtolower( trim( (string) $ref ) );

		return isset( $title_map[ $key ] ) ? $title_map[ $key ] : null;
	}

	/**
	 * Reads the question id off a questionAnswers row.
	 *
	 * Sessionize is inconsistent here: the id can sit directly on the row, under
	 * a `questionId` key, or nested inside a `question` object. Mirrors
	 * getQuestionId() in blocks/sessionize-schedule/src/view.js.
	 *
	 * @param mixed $row A questionAnswers entry, or a nested question object.
	 * @return int|null Question id, or null when absent.
	 */
	private static function row_question_id( $row ) {
		if ( is_numeric( $row ) ) {
			return (int) $row;
		}

		if ( ! is_array( $row ) ) {
			return null;
		}

		$keys = array( 'id', 'questionId', 'question_id' );

		foreach ( $keys as $key ) {
			if ( isset( $row[ $key ] ) && is_numeric( $row[ $key ] ) ) {
				return (int) $row[ $key ];
			}
		}

		if ( isset( $row['question'] ) && is_array( $row['question'] ) ) {
			foreach ( $keys as $key ) {
				if ( isset( $row['question'][ $key ] ) && is_numeric( $row['question'][ $key ] ) ) {
					return (int) $row['question'][ $key ];
				}
			}
		}

		return null;
	}

	/**
	 * Reads the answer value off a questionAnswers row.
	 *
	 * Mirrors extractAnswerValue() in blocks/sessionize-schedule/src/view.js.
	 *
	 * @param mixed $row A questionAnswers entry.
	 * @return string The answer, or an empty string.
	 */
	private static function row_answer_value( $row ) {
		if ( ! is_array( $row ) ) {
			return '';
		}

		if ( isset( $row['answer'] ) && null !== $row['answer'] ) {
			if ( is_array( $row['answer'] ) ) {
				return trim( implode( ', ', array_filter( $row['answer'] ) ) );
			}

			return trim( (string) $row['answer'] );
		}

		foreach ( array( 'answerValue', 'answerExtra' ) as $key ) {
			if ( isset( $row[ $key ] ) && null !== $row[ $key ] ) {
				return trim( (string) $row[ $key ] );
			}
		}

		return '';
	}

	/**
	 * Reads a question answer off a Sessionize speaker or session record.
	 *
	 * @param array           $record    Speaker or session record.
	 * @param int|string|null $ref       Configured question reference.
	 * @param array           $title_map Output of question_title_map().
	 * @return string The answer, or an empty string.
	 */
	public static function answer( $record, $ref, $title_map ) {
		$question_id = self::resolve_question_id( $ref, $title_map );
		if ( null === $question_id ) {
			return '';
		}

		foreach ( self::array_of( $record, 'questionAnswers' ) as $row ) {
			if ( self::row_question_id( $row ) === $question_id ) {
				return self::row_answer_value( $row );
			}
		}

		return '';
	}
}
