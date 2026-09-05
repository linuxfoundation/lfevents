<?php
/**
 * Schema.org structured data for Sessionize blocks.
 *
 * Rendering the schedule server-side makes the programme readable, but search
 * engines still infer a lot from unstructured markup. Emitting explicit
 * schema.org JSON-LD lets them (and AI agents) resolve sessions, times, rooms
 * and speakers without guessing.
 *
 * @package Sessionize_Blocks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds and prints JSON-LD for a rendered Sessionize block.
 */
class Sessionize_JsonLd {

	/**
	 * Maximum number of sub-events to describe.
	 *
	 * Large conferences can run to many hundreds of sessions; beyond a point the
	 * markup costs more in page weight than it earns in structured coverage, and
	 * the sessions are all present in the rendered HTML regardless.
	 */
	const MAX_SUB_EVENTS = 300;

	/**
	 * Maximum length of a description, in characters.
	 *
	 * The full abstract is already in the rendered HTML. Repeating it in full
	 * here would roughly double the page weight for no gain — consumers of this
	 * markup use it to identify and structure a session, not to read it.
	 */
	const MAX_DESCRIPTION = 400;

	/**
	 * Flattens a description to a short plain-text summary.
	 *
	 * @param string $description Sanitized description HTML.
	 * @return string Plain text summary.
	 */
	private static function summarize( $description ) {
		$text = Sessionize_Sanitizer::to_plain_text( $description );
		$text = trim( preg_replace( '/\s+/u', ' ', $text ) );

		if ( '' === $text || mb_strlen( $text ) <= self::MAX_DESCRIPTION ) {
			return $text;
		}

		$truncated  = mb_substr( $text, 0, self::MAX_DESCRIPTION );
		$last_space = mb_strrpos( $truncated, ' ' );

		if ( false !== $last_space && $last_space > 0 ) {
			$truncated = mb_substr( $truncated, 0, $last_space );
		}

		return rtrim( $truncated, ' ,.;:-' ) . '…';
	}

	/**
	 * Builds the Event graph for a schedule block.
	 *
	 * @param array  $sessions Output of sched_prepare_sessions().
	 * @param string $name     Event name.
	 * @param string $url      Canonical URL of the page holding the block.
	 * @return array|null JSON-LD structure, or null when there is nothing to describe.
	 */
	public static function schedule( $sessions, $name, $url ) {
		if ( empty( $sessions ) ) {
			return null;
		}

		$url        = esc_url_raw( (string) $url );
		$sub_events = array();

		foreach ( array_slice( $sessions, 0, self::MAX_SUB_EVENTS ) as $session ) {
			if ( '' === $session['title'] ) {
				continue;
			}

			$sub_event = array(
				'@type'     => 'Event',
				'name'      => $session['title'],
				'startDate' => $session['start']->format( 'Y-m-d\TH:i:s' ),
				'endDate'   => $session['end']->format( 'Y-m-d\TH:i:s' ),
			);

			if ( '' !== $url ) {
				$sub_event['url'] = add_query_arg( 'id', rawurlencode( $session['id'] ), $url );
			}

			$description = self::summarize( $session['description'] );

			if ( '' !== $description ) {
				$sub_event['description'] = $description;
			}

			if ( '' !== $session['room'] ) {
				$sub_event['location'] = array(
					'@type' => 'Place',
					'name'  => $session['room'],
				);
			}

			if ( ! empty( $session['speakerNames'] ) ) {
				$sub_event['performer'] = array_map(
					function ( $speaker_name ) {
						return array(
							'@type' => 'Person',
							'name'  => $speaker_name,
						);
					},
					$session['speakerNames']
				);
			}

			$sub_events[] = $sub_event;
		}

		if ( empty( $sub_events ) ) {
			return null;
		}

		$first = reset( $sessions );
		$last  = end( $sessions );

		$event = array(
			'@context'  => 'https://schema.org',
			'@type'     => 'Event',
			'name'      => $name,
			'startDate' => $first['start']->format( 'Y-m-d\TH:i:s' ),
			'endDate'   => $last['end']->format( 'Y-m-d\TH:i:s' ),
			'subEvent'  => $sub_events,
		);

		if ( '' !== $url ) {
			$event['url'] = $url;
		}

		return $event;
	}

	/**
	 * Builds the ItemList graph for a speakers block.
	 *
	 * @param array  $speakers Output of sz_speakers_prepare().
	 * @param string $name     List name.
	 * @return array|null JSON-LD structure, or null when there is nothing to describe.
	 */
	public static function speakers( $speakers, $name ) {
		if ( empty( $speakers ) ) {
			return null;
		}

		$elements = array();
		$position = 0;

		foreach ( $speakers as $speaker ) {
			if ( '' === $speaker['fullName'] ) {
				continue;
			}

			++$position;

			$person = array(
				'@type' => 'Person',
				'name'  => $speaker['fullName'],
			);

			if ( '' !== $speaker['title'] ) {
				$person['jobTitle'] = $speaker['title'];
			}

			if ( '' !== $speaker['company'] ) {
				$person['worksFor'] = array(
					'@type' => 'Organization',
					'name'  => $speaker['company'],
				);
			}

			if ( '' !== $speaker['avatar'] ) {
				$person['image'] = $speaker['avatar'];
			}

			if ( '' !== $speaker['bio'] ) {
				$person['description'] = self::summarize( $speaker['bio'] );
			}

			if ( ! empty( $speaker['links'] ) ) {
				$person['sameAs'] = array_values( array_map(
					function ( $link ) {
						return $link['url'];
					},
					$speaker['links']
				) );
			}

			$elements[] = array(
				'@type'    => 'ListItem',
				'position' => $position,
				'item'     => $person,
			);
		}

		if ( empty( $elements ) ) {
			return null;
		}

		return array(
			'@context'        => 'https://schema.org',
			'@type'           => 'ItemList',
			'name'            => $name,
			'itemListElement' => $elements,
		);
	}

	/**
	 * Prints a JSON-LD structure as a script tag.
	 *
	 * @param array|null $data JSON-LD structure.
	 * @return string Script element, or an empty string.
	 */
	public static function render( $data ) {
		if ( empty( $data ) ) {
			return '';
		}

		$json = wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

		if ( false === $json ) {
			return '';
		}

		// Neutralize any sequence that could close the script element early.
		$json = str_replace( array( '<', '>', '&' ), array( '\u003C', '\u003E', '\u0026' ), $json );

		return '<script type="application/ld+json">' . $json . '</script>';
	}
}
