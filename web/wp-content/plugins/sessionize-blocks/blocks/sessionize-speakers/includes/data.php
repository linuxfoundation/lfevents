<?php
/**
 * Server-side data preparation for the Sessionize Speakers block.
 *
 * PHP port of the selection and metadata logic in src/view.js, so the speaker
 * grid can be rendered as real HTML instead of being assembled in the browser.
 * The two implementations need to agree: the client script re-renders the grid
 * from the inline JSON on load, so a mismatch shows up as a visible flicker.
 *
 * @package Sessionize_Blocks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'sz_speakers_norm' ) ) {
	/**
	 * Normalizes a name for comparison. Mirrors norm_() in view.js.
	 *
	 * @param string $value Raw name.
	 * @return string Normalized name.
	 */
	function sz_speakers_norm( $value ) {
		$value = strtolower( trim( (string) $value ) );

		return preg_replace( '/[\'\x{2018}\x{2019}`]/u', '', $value );
	}
}

if ( ! function_exists( 'sz_speakers_full_name' ) ) {
	/**
	 * Returns a speaker's display name.
	 *
	 * @param array $speaker Speaker record.
	 * @return string Full name.
	 */
	function sz_speakers_full_name( $speaker ) {
		if ( ! empty( $speaker['fullName'] ) ) {
			return trim( (string) $speaker['fullName'] );
		}

		$first = isset( $speaker['firstName'] ) ? (string) $speaker['firstName'] : '';
		$last  = isset( $speaker['lastName'] ) ? (string) $speaker['lastName'] : '';

		return trim( $first . ' ' . $last );
	}
}

if ( ! function_exists( 'sz_speakers_company_from_tagline' ) ) {
	/**
	 * Derives a company name from a speaker's tag line.
	 *
	 * Mirrors parseCompanyFromTagline_() in view.js: prefer an explicit
	 * "… at Company" / "… @ Company" suffix, otherwise fall back to the last
	 * comma-separated segment.
	 *
	 * @param string $tag_line Speaker tag line.
	 * @return string Company name, or an empty string.
	 */
	function sz_speakers_company_from_tagline( $tag_line ) {
		$tag_line = trim( (string) $tag_line );

		if ( '' === $tag_line ) {
			return '';
		}

		if ( preg_match( '/\s(?:at|@)\s(.+)$/i', $tag_line, $match ) ) {
			return trim( $match[1] );
		}

		$parts = array_values( array_filter( array_map( 'trim', explode( ',', $tag_line ) ) ) );

		return count( $parts ) >= 2 ? end( $parts ) : '';
	}
}

if ( ! function_exists( 'sz_speakers_link_kind' ) ) {
	/**
	 * Classifies a speaker link so the right icon and brand colour is used.
	 *
	 * Mirrors getSpeakerLinkKind_() in view.js.
	 *
	 * @param string $link_type Sessionize link type.
	 * @param string $title     Link title.
	 * @param string $url       Link URL.
	 * @return string One of the known kinds, or 'other'.
	 */
	function sz_speakers_link_kind( $link_type, $title, $url ) {
		$key = strtolower( trim( (string) ( $link_type ? $link_type : $title ) ) );
		$key = str_replace( '_', ' ', $key );

		$by_key = array(
			'twitter'         => 'x',
			'x'               => 'x',
			'x (twitter)'     => 'x',
			'linkedin'        => 'linkedin',
			'facebook'        => 'facebook',
			'instagram'       => 'instagram',
			'ig'              => 'instagram',
			'sessionize'      => 'sessionize',
			'bluesky'         => 'bluesky',
			'bsky'            => 'bluesky',
			'youtube'         => 'youtube',
			'yt'              => 'youtube',
			'github'          => 'github',
			'gh'              => 'github',
			'blog'            => 'blog',
			'company website' => 'company',
			'company'         => 'company',
		);

		if ( isset( $by_key[ $key ] ) ) {
			return $by_key[ $key ];
		}

		$host = strtolower( (string) wp_parse_url( trim( (string) $url ), PHP_URL_HOST ) );
		$host = preg_replace( '/^www\./', '', $host );

		if ( '' === $host ) {
			return 'other';
		}

		$by_host = array(
			'x'          => array( 'twitter.com', 'x.com' ),
			'linkedin'   => array( 'linkedin.com' ),
			'facebook'   => array( 'facebook.com', 'fb.com' ),
			'instagram'  => array( 'instagram.com' ),
			'sessionize' => array( 'sessionize.com' ),
			'bluesky'    => array( 'bsky.app', 'bsky.social', 'bluesky.app' ),
			'youtube'    => array( 'youtube.com', 'youtu.be' ),
			'github'     => array( 'github.com', 'github.io' ),
		);

		foreach ( $by_host as $kind => $domains ) {
			foreach ( $domains as $domain ) {
				if ( $host === $domain || substr( $host, -strlen( '.' . $domain ) ) === '.' . $domain ) {
					return $kind;
				}
			}
		}

		return 'other';
	}
}

if ( ! function_exists( 'sz_speakers_link_label' ) ) {
	/**
	 * Returns the accessible label for a speaker link.
	 *
	 * @param string $kind      Link kind from sz_speakers_link_kind().
	 * @param string $link_type Sessionize link type.
	 * @param string $title     Link title.
	 * @return string Label.
	 */
	function sz_speakers_link_label( $kind, $link_type, $title ) {
		$labels = array(
			'x'          => 'X',
			'linkedin'   => 'LinkedIn',
			'facebook'   => 'Facebook',
			'instagram'  => 'Instagram',
			'sessionize' => 'Sessionize',
			'bluesky'    => 'Bluesky',
			'youtube'    => 'YouTube',
			'github'     => 'GitHub',
			'blog'       => 'Blog',
			'company'    => 'Company Website',
			'other'      => 'Other',
		);

		if ( isset( $labels[ $kind ] ) && 'other' !== $kind ) {
			return $labels[ $kind ];
		}

		$fallback = trim( (string) ( $title ? $title : $link_type ) );

		return '' !== $fallback ? $fallback : 'Link';
	}
}

if ( ! function_exists( 'sz_speakers_link_sort_order' ) ) {
	/**
	 * Returns the display order for a link kind.
	 *
	 * @param string $kind Link kind.
	 * @return int Sort weight.
	 */
	function sz_speakers_link_sort_order( $kind ) {
		$order = array(
			'x'          => 1,
			'linkedin'   => 2,
			'bluesky'    => 3,
			'facebook'   => 4,
			'instagram'  => 5,
			'youtube'    => 6,
			'github'     => 7,
			'sessionize' => 8,
			'blog'       => 9,
			'company'    => 10,
			'other'      => 11,
		);

		return isset( $order[ $kind ] ) ? $order[ $kind ] : 99;
	}
}

if ( ! function_exists( 'sz_speakers_links' ) ) {
	/**
	 * Returns a speaker's links, classified and sorted for display.
	 *
	 * @param array $speaker Speaker record.
	 * @return array List of arrays with url, kind and label.
	 */
	function sz_speakers_links( $speaker ) {
		if ( empty( $speaker['links'] ) || ! is_array( $speaker['links'] ) ) {
			return array();
		}

		$links = array();

		foreach ( $speaker['links'] as $index => $link ) {
			if ( ! is_array( $link ) || empty( $link['url'] ) ) {
				continue;
			}

			$url = esc_url_raw( trim( (string) $link['url'] ), array( 'http', 'https', 'mailto' ) );
			if ( '' === $url ) {
				continue;
			}

			$link_type = isset( $link['linkType'] ) ? (string) $link['linkType'] : '';
			$title     = isset( $link['title'] ) ? (string) $link['title'] : '';
			$kind      = sz_speakers_link_kind( $link_type, $title, $url );

			$links[] = array(
				'url'   => $url,
				'kind'  => $kind,
				'label' => sz_speakers_link_label( $kind, $link_type, $title ),
				'order' => sz_speakers_link_sort_order( $kind ),
				'index' => $index,
			);
		}

		usort(
			$links,
			function ( $a, $b ) {
				if ( $a['order'] !== $b['order'] ) {
					return $a['order'] - $b['order'];
				}

				return $a['index'] - $b['index'];
			}
		);

		return $links;
	}
}

if ( ! function_exists( 'sz_speakers_sessions_by_speaker' ) ) {
	/**
	 * Groups scheduled sessions by speaker id.
	 *
	 * Mirrors buildSessionsBySpeakerId_() in view.js, including its rule that
	 * sessions without a usable start time are skipped — those are placeholder
	 * entries that were never actually scheduled.
	 *
	 * @param array $all Normalized "all" payload.
	 * @return array Map of speaker id to a time-sorted list of sessions.
	 */
	function sz_speakers_sessions_by_speaker( $all ) {
		$rooms = sessionize_rooms_by_id( $all );
		$map   = array();

		$sessions = isset( $all['sessions'] ) && is_array( $all['sessions'] ) ? $all['sessions'] : array();

		foreach ( $sessions as $session ) {
			if ( ! is_array( $session ) ) {
				continue;
			}

			$starts_at = isset( $session['startsAt'] ) ? trim( (string) $session['startsAt'] ) : '';
			$start     = sessionize_parse_time( $starts_at );

			if ( null === $start ) {
				continue;
			}

			$room_id   = isset( $session['roomId'] ) ? (string) $session['roomId'] : '';
			$room_name = isset( $rooms[ $room_id ] ) ? $rooms[ $room_id ] : '';

			if ( '' === $room_name && ! empty( $session['room'] ) && is_string( $session['room'] ) ) {
				$room_name = trim( $session['room'] );
			}

			$duration = isset( $session['duration'] ) ? (int) $session['duration'] : 0;

			$entry = array(
				'id'              => isset( $session['id'] ) ? (string) $session['id'] : '',
				'title'           => isset( $session['title'] ) ? trim( (string) $session['title'] ) : '',
				'startsAt'        => $starts_at,
				'endsAt'          => isset( $session['endsAt'] ) ? trim( (string) $session['endsAt'] ) : '',
				'durationMinutes' => $duration > 0 ? $duration : null,
				'room'            => $room_name,
				'abstract'        => isset( $session['description'] ) ? Sessionize_Sanitizer::to_plain_text( (string) $session['description'] ) : '',
				'sortKey'         => $start->getTimestamp(),
			);

			$speaker_ids = isset( $session['speakers'] ) && is_array( $session['speakers'] ) ? $session['speakers'] : array();

			foreach ( $speaker_ids as $speaker_id ) {
				$key = (string) $speaker_id;

				if ( ! isset( $map[ $key ] ) ) {
					$map[ $key ] = array();
				}

				$map[ $key ][] = $entry;
			}
		}

		foreach ( $map as $key => $list ) {
			usort(
				$list,
				function ( $a, $b ) {
					if ( $a['sortKey'] !== $b['sortKey'] ) {
						return $a['sortKey'] - $b['sortKey'];
					}

					return strcmp( $a['title'], $b['title'] );
				}
			);

			$map[ $key ] = $list;
		}

		return $map;
	}
}

if ( ! function_exists( 'sz_speakers_prepare' ) ) {
	/**
	 * Builds the ordered, filtered list of speakers the block should render.
	 *
	 * @param array $all    Normalized "all" payload.
	 * @param array $config The block's speaker config array.
	 * @return array List of prepared speaker arrays.
	 */
	function sz_speakers_prepare( $all, $config ) {
		$speakers = isset( $all['speakers'] ) && is_array( $all['speakers'] ) ? $all['speakers'] : array();

		if ( empty( $speakers ) ) {
			return array();
		}

		$title_map    = Sessionize_Normalizer::question_title_map( $all );
		$by_speaker   = sz_speakers_sessions_by_speaker( $all );
		$excluded     = array_map( 'sz_speakers_norm', $config['excludeSpeakersExact'] );
		$forced_order = array_map( 'sz_speakers_norm', $config['forceOrderExact'] );

		$prepared = array();

		foreach ( $speakers as $speaker ) {
			if ( ! is_array( $speaker ) || ! isset( $speaker['id'] ) ) {
				continue;
			}

			if ( $config['topSpeakersOnly'] && empty( $speaker['isTopSpeaker'] ) ) {
				continue;
			}

			$full_name = sz_speakers_full_name( $speaker );

			if ( in_array( sz_speakers_norm( $full_name ), $excluded, true ) ) {
				continue;
			}

			$company = Sessionize_Normalizer::answer( $speaker, $config['companyQuestionId'], $title_map );
			if ( '' === $company ) {
				$tag_line = isset( $speaker['tagLine'] ) ? $speaker['tagLine'] : '';
				$company  = sz_speakers_company_from_tagline( $tag_line );
			}

			$logo = Sessionize_Normalizer::answer( $speaker, $config['companyLogoUrlQuestionId'], $title_map );
			if ( '' === $logo ) {
				$logo = Sessionize_Normalizer::answer( $speaker, $config['companyLogoUploadQuestionId'], $title_map );
			}

			$speaker_id = (string) $speaker['id'];

			$prepared[] = array(
				'id'        => $speaker_id,
				'fullName'  => $full_name,
				'firstName' => isset( $speaker['firstName'] ) ? (string) $speaker['firstName'] : '',
				'lastName'  => isset( $speaker['lastName'] ) ? (string) $speaker['lastName'] : '',
				'avatar'    => isset( $speaker['profilePicture'] ) ? esc_url_raw( (string) $speaker['profilePicture'] ) : '',
				'title'     => Sessionize_Normalizer::answer( $speaker, $config['speakerTitleQuestionId'], $title_map ),
				'company'   => $company,
				'logo'      => '' !== $logo ? esc_url_raw( $logo ) : '',
				'bio'       => Sessionize_Sanitizer::to_plain_text( isset( $speaker['bio'] ) ? (string) $speaker['bio'] : '' ),
				'links'     => sz_speakers_links( $speaker ),
				'sessions'  => isset( $by_speaker[ $speaker_id ] ) ? $by_speaker[ $speaker_id ] : array(),
			);
		}

		usort(
			$prepared,
			function ( $a, $b ) use ( $forced_order ) {
				$a_name = sz_speakers_norm( $a['fullName'] );
				$b_name = sz_speakers_norm( $b['fullName'] );

				$a_forced = array_search( $a_name, $forced_order, true );
				$b_forced = array_search( $b_name, $forced_order, true );

				if ( false !== $a_forced && false !== $b_forced ) {
					return $a_forced - $b_forced;
				}
				if ( false !== $a_forced ) {
					return -1;
				}
				if ( false !== $b_forced ) {
					return 1;
				}

				$a_last = sz_speakers_norm( $a['lastName'] );
				$b_last = sz_speakers_norm( $b['lastName'] );
				if ( $a_last !== $b_last ) {
					return strcmp( $a_last, $b_last );
				}

				$a_first = sz_speakers_norm( $a['firstName'] );
				$b_first = sz_speakers_norm( $b['firstName'] );
				if ( $a_first !== $b_first ) {
					return strcmp( $a_first, $b_first );
				}

				return strcmp( $a_name, $b_name );
			}
		);

		return $prepared;
	}
}

if ( ! function_exists( 'sz_speakers_inline_payload' ) ) {
	/**
	 * Builds the trimmed payload handed to the front-end script.
	 *
	 * Shape-compatible with the Sessionize "All" response the script used to
	 * fetch, minus everything this block never reads, so swapping the network
	 * call for an inline read needed no other changes.
	 *
	 * Speakers the block does not display, and sessions none of the displayed
	 * speakers are on, are dropped. The script applies the same selection rules
	 * itself, so filtering here only saves page weight — on an event with many
	 * more speakers than the block shows, that saving is most of the payload.
	 *
	 * @param array $all      Normalized "all" payload.
	 * @param array $speakers Output of sz_speakers_prepare().
	 * @return array Trimmed payload.
	 */
	function sz_speakers_inline_payload( $all, $speakers ) {
		$kept_ids = array();

		foreach ( $speakers as $speaker ) {
			$kept_ids[ $speaker['id'] ] = true;
		}

		$source_speakers = isset( $all['speakers'] ) && is_array( $all['speakers'] ) ? $all['speakers'] : array();
		$out_speakers    = array();

		foreach ( $source_speakers as $speaker ) {
			if ( is_array( $speaker ) && isset( $speaker['id'] ) && isset( $kept_ids[ (string) $speaker['id'] ] ) ) {
				$out_speakers[] = $speaker;
			}
		}

		$session_keys    = array( 'id', 'title', 'description', 'startsAt', 'endsAt', 'duration', 'roomId', 'speakers' );
		$out_sessions    = array();
		$source_sessions = isset( $all['sessions'] ) && is_array( $all['sessions'] ) ? $all['sessions'] : array();

		foreach ( $source_sessions as $session ) {
			if ( ! is_array( $session ) ) {
				continue;
			}

			$session_speakers = isset( $session['speakers'] ) && is_array( $session['speakers'] ) ? $session['speakers'] : array();
			$is_relevant      = false;

			foreach ( $session_speakers as $speaker_id ) {
				if ( isset( $kept_ids[ (string) $speaker_id ] ) ) {
					$is_relevant = true;
					break;
				}
			}

			if ( ! $is_relevant ) {
				continue;
			}

			$trimmed = array();
			foreach ( $session_keys as $key ) {
				if ( isset( $session[ $key ] ) ) {
					$trimmed[ $key ] = $session[ $key ];
				}
			}

			$out_sessions[] = $trimmed;
		}

		return array(
			'speakers'  => $out_speakers,
			'sessions'  => $out_sessions,
			'rooms'     => isset( $all['rooms'] ) ? $all['rooms'] : array(),
			'questions' => isset( $all['questions'] ) ? $all['questions'] : array(),
		);
	}
}

if ( ! function_exists( 'sz_speakers_session_when' ) ) {
	/**
	 * Formats a session's date/time/duration line.
	 *
	 * Mirrors formatSessionWhenMeta_() in view.js.
	 *
	 * @param array $session Prepared session entry.
	 * @param array $config  The block's speaker config array.
	 * @return string Formatted string, or an empty string.
	 */
	function sz_speakers_session_when( $session, $config ) {
		$start = sessionize_parse_time( $session['startsAt'] );

		if ( null === $start ) {
			return '';
		}

		$end = sessionize_parse_time( $session['endsAt'] );

		$date_str  = 'dmy' === $config['dateFormat'] ? $start->format( 'j M Y' ) : $start->format( 'M j, Y' );
		$start_str = sessionize_format_time( $start, $config['timeFormat'] );
		$end_str   = null === $end ? '' : sessionize_format_time( $end, $config['timeFormat'] );
		$duration  = ! empty( $session['durationMinutes'] ) ? $session['durationMinutes'] . ' min' : '';

		$parts = array( $date_str );

		if ( '' !== $end_str ) {
			$parts[] = $start_str . '-' . $end_str;
		} else {
			$parts[] = $start_str;
		}

		if ( '' !== $duration ) {
			$parts[] = $duration;
		}

		return implode( ' • ', array_filter( $parts ) );
	}
}
