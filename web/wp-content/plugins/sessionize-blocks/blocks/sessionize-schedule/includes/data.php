<?php
/**
 * Server-side data preparation for the Sessionize Schedule block.
 *
 * PHP port of the derivation logic in src/view.js — enough of it to render the
 * list view as real HTML so search engines and AI agents can read the full
 * programme. The grid and speaker-wall views stay client-rendered: they are
 * alternative layouts of the same sessions and add no content a crawler needs.
 *
 * @package Sessionize_Blocks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'sched_hash_string' ) ) {
	/**
	 * Hashes a string with djb2, matching hashString() in view.js.
	 *
	 * Category colours are derived from this hash when no explicit override is
	 * configured, so the PHP and JS implementations have to agree exactly or the
	 * server-rendered cards would change colour on hydration. The string is read
	 * as UTF-16 code units because that is what JavaScript's charCodeAt() returns.
	 *
	 * @param string $value String to hash.
	 * @return int Non-negative hash.
	 */
	function sched_hash_string( $value ) {
		$hash  = 5381;
		$utf16 = mb_convert_encoding( (string) $value, 'UTF-16LE', 'UTF-8' );
		$len   = strlen( $utf16 );

		for ( $i = 0; $i + 1 < $len; $i += 2 ) {
			$code = ord( $utf16[ $i ] ) | ( ord( $utf16[ $i + 1 ] ) << 8 );
			$hash = ( ( $hash << 5 ) + $hash ) + $code;

			// Emulate JavaScript's `h = h | 0` 32-bit signed truncation.
			$hash &= 0xFFFFFFFF;
			if ( $hash >= 0x80000000 ) {
				$hash -= 0x100000000;
			}
		}

		return abs( $hash );
	}
}

if ( ! function_exists( 'sched_hex_to_hsl' ) ) {
	/**
	 * Converts a hex colour to HSL components. Mirrors hexToHsl() in view.js.
	 *
	 * @param string $hex Hex colour, with or without a leading hash.
	 * @return array|null Array with h, s and l, or null when unparseable.
	 */
	function sched_hex_to_hsl( $hex ) {
		$hex = ltrim( trim( (string) $hex ), '#' );

		if ( 3 === strlen( $hex ) ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}

		if ( 6 !== strlen( $hex ) || ! ctype_xdigit( $hex ) ) {
			return null;
		}

		$red   = hexdec( substr( $hex, 0, 2 ) ) / 255;
		$green = hexdec( substr( $hex, 2, 2 ) ) / 255;
		$blue  = hexdec( substr( $hex, 4, 2 ) ) / 255;

		$max   = max( $red, $green, $blue );
		$min   = min( $red, $green, $blue );
		$light = ( $max + $min ) / 2;
		$hue   = 0;
		$sat   = 0;

		if ( $max !== $min ) {
			$delta = $max - $min;
			$sat   = $light > 0.5 ? $delta / ( 2 - $max - $min ) : $delta / ( $max + $min );

			if ( $max === $red ) {
				$hue = ( $green - $blue ) / $delta + ( $green < $blue ? 6 : 0 );
			} elseif ( $max === $green ) {
				$hue = ( $blue - $red ) / $delta + 2;
			} else {
				$hue = ( $red - $green ) / $delta + 4;
			}

			$hue *= 60;
		}

		return array(
			'h' => (int) round( $hue ),
			's' => (int) round( $sat * 100 ),
			'l' => (int) round( $light * 100 ),
		);
	}
}

if ( ! function_exists( 'sched_primary_colors' ) ) {
	/**
	 * Returns the card colours for a primary category value.
	 *
	 * Mirrors primaryColorsFromName() in view.js.
	 *
	 * @param string $name      Primary category value.
	 * @param array  $overrides Map of category value to hex colour.
	 * @return array Array with bg and border CSS colour strings.
	 */
	function sched_primary_colors( $name, $overrides ) {
		if ( isset( $overrides[ $name ] ) && '' !== $overrides[ $name ] ) {
			$hsl = sched_hex_to_hsl( $overrides[ $name ] );

			if ( null !== $hsl ) {
				$sat = max( 55, $hsl['s'] );

				return array(
					'bg'     => 'hsl(' . $hsl['h'] . ' ' . $sat . '% 95%)',
					'border' => 'hsl(' . $hsl['h'] . ' ' . $sat . '% 50%)',
				);
			}
		}

		$hue = sched_hash_string( $name ) % 360;

		return array(
			'bg'     => 'hsl(' . $hue . ' 70% 95%)',
			'border' => 'hsl(' . $hue . ' 70% 50%)',
		);
	}
}

if ( ! function_exists( 'sched_answer_file_url' ) ) {
	/**
	 * Pulls an http(s) URL out of a session's answer to a question.
	 *
	 * Sessionize file-upload answers come back in several shapes — a bare string,
	 * a list of strings, or objects with any of several URL keys. Mirrors
	 * extractFileUrlFromAnswerRow() in view.js.
	 *
	 * @param array           $session   Session record.
	 * @param int|string|null $ref       Configured question reference.
	 * @param array           $title_map Question title to id map.
	 * @return string URL, or an empty string.
	 */
	function sched_answer_file_url( $session, $ref, $title_map ) {
		$question_id = Sessionize_Normalizer::resolve_question_id( $ref, $title_map );

		if ( null === $question_id || empty( $session['questionAnswers'] ) || ! is_array( $session['questionAnswers'] ) ) {
			return '';
		}

		$candidates = array();

		foreach ( $session['questionAnswers'] as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}

			$row_id = null;
			foreach ( array( 'id', 'questionId', 'question_id' ) as $key ) {
				if ( isset( $row[ $key ] ) && is_numeric( $row[ $key ] ) ) {
					$row_id = (int) $row[ $key ];
					break;
				}
			}

			if ( $row_id !== $question_id ) {
				continue;
			}

			foreach ( array( 'answer', 'answerValue', 'answerExtra' ) as $key ) {
				if ( isset( $row[ $key ] ) && null !== $row[ $key ] ) {
					$candidates[] = $row[ $key ];
				}
			}

			break;
		}

		foreach ( $candidates as $candidate ) {
			$url = sched_first_url_in( $candidate );

			if ( '' !== $url ) {
				return $url;
			}
		}

		return '';
	}
}

if ( ! function_exists( 'sched_first_url_in' ) ) {
	/**
	 * Finds the first http(s) URL in a scalar, list or object answer value.
	 *
	 * @param mixed $value Answer value.
	 * @return string URL, or an empty string.
	 */
	function sched_first_url_in( $value ) {
		if ( is_string( $value ) ) {
			$value = trim( $value );

			return preg_match( '#^https?://#i', $value ) ? $value : '';
		}

		if ( ! is_array( $value ) ) {
			return '';
		}

		$url_keys = array( 'url', 'fileUrl', 'fileURL', 'downloadUrl', 'downloadURL', 'value', 'href' );

		foreach ( $url_keys as $key ) {
			if ( isset( $value[ $key ] ) && is_string( $value[ $key ] ) ) {
				$candidate = trim( $value[ $key ] );

				if ( preg_match( '#^https?://#i', $candidate ) ) {
					return $candidate;
				}
			}
		}

		foreach ( $value as $item ) {
			$url = sched_first_url_in( $item );

			if ( '' !== $url ) {
				return $url;
			}
		}

		return '';
	}
}

if ( ! function_exists( 'sched_prepare_sessions' ) ) {
	/**
	 * Builds the renderable session list.
	 *
	 * @param array $all    Normalized "all" payload.
	 * @param array $config The block's schedule config array.
	 * @return array List of prepared session arrays, sorted by start time.
	 */
	function sched_prepare_sessions( $all, $config ) {
		$sessions = isset( $all['sessions'] ) && is_array( $all['sessions'] ) ? $all['sessions'] : array();

		if ( empty( $sessions ) ) {
			return array();
		}

		$speakers      = sessionize_speakers_by_id( $all );
		$rooms         = sessionize_rooms_by_id( $all );
		$items         = sessionize_category_items_by_id( $all );
		$title_map     = Sessionize_Normalizer::question_title_map( $all );
		$primary_title = $config['primaryFilterTitle'];

		$hidden_chip_categories = array_map( 'strtolower', $config['hideSessionChipsForCategories'] );
		$hide_all_chips_for     = array_map( 'strtolower', $config['hideAllChipsForPrimaryValues'] );

		$custom_link_refs = array(
			$config['customLinkField1QuestionId'],
			$config['customLinkField2QuestionId'],
			$config['customLinkField3QuestionId'],
			$config['customLinkField4QuestionId'],
			$config['customLinkField5QuestionId'],
		);

		$prepared = array();

		foreach ( $sessions as $session ) {
			if ( ! is_array( $session ) || empty( $session['startsAt'] ) ) {
				continue;
			}

			$start = sessionize_parse_time( $session['startsAt'] );

			if ( null === $start ) {
				continue;
			}

			$end = sched_session_end( $session, $start );

			$room_id   = isset( $session['roomId'] ) ? (string) $session['roomId'] : '';
			$room_name = isset( $rooms[ $room_id ] ) ? $rooms[ $room_id ] : '';

			// Category chips, and the primary value that drives the card colour.
			$primary_name = '';
			$tags         = array();

			$category_ids = isset( $session['categoryItems'] ) && is_array( $session['categoryItems'] ) ? $session['categoryItems'] : array();

			foreach ( $category_ids as $category_id ) {
				$key = (string) $category_id;

				if ( ! isset( $items[ $key ] ) ) {
					continue;
				}

				$item = $items[ $key ];

				if ( '' === $primary_name && $item['categoryTitle'] === $primary_title ) {
					$primary_name = $item['name'];
				}

				if ( in_array( strtolower( $item['categoryTitle'] ), $hidden_chip_categories, true ) ) {
					continue;
				}

				$tags[] = array(
					'title'     => $item['categoryTitle'],
					'name'      => $item['name'],
					'isPrimary' => $item['categoryTitle'] === $primary_title,
				);
			}

			// Primary category chip first, matching sessionTagsForTitles().
			usort(
				$tags,
				function ( $a, $b ) {
					if ( $a['isPrimary'] !== $b['isPrimary'] ) {
						return $a['isPrimary'] ? -1 : 1;
					}

					return strcmp( $a['name'], $b['name'] );
				}
			);

			if ( in_array( strtolower( $primary_name ), $hide_all_chips_for, true ) ) {
				$tags = array();
			}

			$speaker_names = array();
			$speaker_ids   = isset( $session['speakers'] ) && is_array( $session['speakers'] ) ? $session['speakers'] : array();

			foreach ( $speaker_ids as $speaker_id ) {
				$key = (string) $speaker_id;

				if ( ! isset( $speakers[ $key ] ) ) {
					continue;
				}

				$name = sched_speaker_display_name( $speakers[ $key ] );

				if ( '' !== $name ) {
					$speaker_names[] = $name;
				}
			}

			$custom_links = array();

			foreach ( $custom_link_refs as $ref ) {
				$url = sched_answer_file_url( $session, $ref, $title_map );

				if ( '' === $url ) {
					continue;
				}

				$label = sched_question_label( $all, $ref, $title_map );

				if ( '' === $label ) {
					continue;
				}

				$custom_links[] = array(
					'label' => $label,
					'url'   => esc_url_raw( $url ),
				);
			}

			$prepared[] = array(
				'id'            => isset( $session['id'] ) ? (string) $session['id'] : '',
				'title'         => isset( $session['title'] ) ? trim( (string) $session['title'] ) : '',
				'description'   => isset( $session['description'] ) ? (string) $session['description'] : '',
				'start'         => $start,
				'end'           => $end,
				'dayStr'        => $start->format( 'Y-m-d' ),
				'slotKey'       => $start->format( 'H:i' ),
				'sortKey'       => $start->getTimestamp(),
				'room'          => $room_name,
				'speakerNames'  => $speaker_names,
				'speakerIds'    => array_map( 'strval', $speaker_ids ),
				'tags'          => $tags,
				'primaryName'   => $primary_name,
				'primaryColors' => '' !== $primary_name ? sched_primary_colors( $primary_name, $config['primaryColorOverrides'] ) : null,
				'recordingUrl'  => isset( $session['recordingUrl'] ) ? esc_url_raw( trim( (string) $session['recordingUrl'] ) ) : '',
				'slidesUrl'     => esc_url_raw( sched_answer_file_url( $session, $config['presentationSlidesQuestionId'], $title_map ) ),
				'logoUrl'       => esc_url_raw( sched_answer_file_url( $session, 'Event Logo', $title_map ) ),
				'customLinks'   => $custom_links,
			);
		}

		usort(
			$prepared,
			function ( $a, $b ) {
				return $a['sortKey'] - $b['sortKey'];
			}
		);

		return $prepared;
	}
}

if ( ! function_exists( 'sched_session_end' ) ) {
	/**
	 * Resolves a session's end time.
	 *
	 * Mirrors getSessionEndMs() in view.js, including its 45 minute fallback for
	 * sessions with neither an end time nor a usable duration.
	 *
	 * @param array             $session Session record.
	 * @param DateTimeImmutable $start   Parsed start time.
	 * @return DateTimeImmutable End time.
	 */
	function sched_session_end( $session, $start ) {
		if ( ! empty( $session['endsAt'] ) ) {
			$end = sessionize_parse_time( $session['endsAt'] );

			if ( null !== $end && $end > $start ) {
				return $end;
			}
		}

		$minutes = isset( $session['duration'] ) ? (int) $session['duration'] : 0;

		if ( $minutes <= 0 ) {
			$minutes = 45;
		}

		return $start->modify( '+' . $minutes . ' minutes' );
	}
}

if ( ! function_exists( 'sched_speaker_display_name' ) ) {
	/**
	 * Returns a speaker's display name.
	 *
	 * @param array $speaker Speaker record.
	 * @return string Display name.
	 */
	function sched_speaker_display_name( $speaker ) {
		if ( ! empty( $speaker['fullName'] ) ) {
			return trim( (string) $speaker['fullName'] );
		}

		$first = isset( $speaker['firstName'] ) ? (string) $speaker['firstName'] : '';
		$last  = isset( $speaker['lastName'] ) ? (string) $speaker['lastName'] : '';

		return trim( $first . ' ' . $last );
	}
}

if ( ! function_exists( 'sched_question_label' ) ) {
	/**
	 * Returns the human readable label for a configured question reference.
	 *
	 * @param array           $all       Normalized "all" payload.
	 * @param int|string|null $ref       Configured question reference.
	 * @param array           $title_map Question title to id map.
	 * @return string Question label, or an empty string.
	 */
	function sched_question_label( $all, $ref, $title_map ) {
		$question_id = Sessionize_Normalizer::resolve_question_id( $ref, $title_map );

		if ( null === $question_id || empty( $all['questions'] ) || ! is_array( $all['questions'] ) ) {
			return '';
		}

		foreach ( $all['questions'] as $question ) {
			if ( ! is_array( $question ) || ! isset( $question['id'] ) || (int) $question['id'] !== $question_id ) {
				continue;
			}

			if ( ! empty( $question['question'] ) ) {
				return (string) $question['question'];
			}

			if ( ! empty( $question['name'] ) ) {
				return (string) $question['name'];
			}
		}

		return '';
	}
}

if ( ! function_exists( 'sched_group_by_day' ) ) {
	/**
	 * Groups prepared sessions by day and then by start time.
	 *
	 * @param array $sessions Output of sched_prepare_sessions().
	 * @return array Map of day string to a map of slot key to session lists.
	 */
	function sched_group_by_day( $sessions ) {
		$days = array();

		foreach ( $sessions as $session ) {
			$days[ $session['dayStr'] ][ $session['slotKey'] ][] = $session;
		}

		ksort( $days );

		foreach ( $days as $day => $slots ) {
			ksort( $slots );
			$days[ $day ] = $slots;
		}

		return $days;
	}
}

if ( ! function_exists( 'sched_day_heading' ) ) {
	/**
	 * Formats a day heading. Mirrors fmtDayDividerText() in view.js.
	 *
	 * @param string $day_str  Day in Y-m-d form.
	 * @param string $date_format Either 'mdy' or 'dmy'.
	 * @return string Heading text.
	 */
	function sched_day_heading( $day_str, $date_format ) {
		$date = sessionize_parse_time( $day_str . 'T00:00:00' );

		if ( null === $date ) {
			return $day_str;
		}

		return 'dmy' === $date_format
			? $date->format( 'l j F' )
			: $date->format( 'l F j' );
	}
}
