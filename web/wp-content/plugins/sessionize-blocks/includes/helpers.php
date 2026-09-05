<?php
/**
 * Shared helpers for the Sessionize block render templates.
 *
 * @package Sessionize_Blocks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'sched_parse_csv' ) ) {
	/**
	 * Parses a comma-separated block attribute into a list of trimmed values.
	 *
	 * @param string $value Comma-separated string.
	 * @return array List of non-empty trimmed values.
	 */
	function sched_parse_csv( $value ) {
		if ( empty( $value ) ) {
			return array();
		}

		return array_values( array_filter( array_map( 'trim', explode( ',', (string) $value ) ) ) );
	}
}

if ( ! function_exists( 'sched_question_ref' ) ) {
	/**
	 * Normalizes a Sessionize question reference.
	 *
	 * Blocks accept either the numeric question id or the question's title, so
	 * the value is kept as an int when numeric, a string when it is a label, and
	 * null when blank.
	 *
	 * @param string $value Configured question reference.
	 * @return int|string|null
	 */
	function sched_question_ref( $value ) {
		$value = trim( (string) $value );

		if ( '' === $value ) {
			return null;
		}

		if ( is_numeric( $value ) ) {
			return (int) $value;
		}

		return $value;
	}
}

/**
 * Fetches cached event data for a block render, registering the API code as it goes.
 *
 * @param string $api_code Sessionize API code.
 * @return array|null Normalized payload, or null when unavailable.
 */
function sessionize_block_data( $api_code ) {
	if ( ! Sessionize_Client::is_valid_api_code( $api_code ) ) {
		return null;
	}

	Sessionize_Registry::remember( $api_code, get_the_ID() );

	return Sessionize_Store::get( $api_code );
}

/**
 * Renders an inline JSON island for a block's front-end script to read.
 *
 * The JSON flags close every character that could terminate the script element
 * or start a new tag, so a hostile session title can't break out of the block.
 *
 * @param mixed  $data       Data to encode.
 * @param string $class_name Class name for the script element.
 * @return string HTML for the script element.
 */
function sessionize_inline_json( $data, $class_name ) {
	$json = wp_json_encode(
		$data,
		JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES
	);

	if ( false === $json ) {
		$json = 'null';
	}

	return '<script type="application/json" class="' . esc_attr( $class_name ) . '">' . $json . '</script>';
}

/**
 * Builds a lookup of speaker id => speaker record.
 *
 * @param array $all Normalized "all" payload.
 * @return array Map of speaker id to speaker record.
 */
function sessionize_speakers_by_id( $all ) {
	$map = array();

	if ( empty( $all['speakers'] ) || ! is_array( $all['speakers'] ) ) {
		return $map;
	}

	foreach ( $all['speakers'] as $speaker ) {
		if ( is_array( $speaker ) && isset( $speaker['id'] ) ) {
			$map[ (string) $speaker['id'] ] = $speaker;
		}
	}

	return $map;
}

/**
 * Builds a lookup of room id => room name.
 *
 * @param array $all Normalized "all" payload.
 * @return array Map of room id to room name.
 */
function sessionize_rooms_by_id( $all ) {
	$map = array();

	if ( empty( $all['rooms'] ) || ! is_array( $all['rooms'] ) ) {
		return $map;
	}

	foreach ( $all['rooms'] as $room ) {
		if ( is_array( $room ) && isset( $room['id'] ) ) {
			$map[ (string) $room['id'] ] = isset( $room['name'] ) ? (string) $room['name'] : '';
		}
	}

	return $map;
}

/**
 * Builds a lookup of category item id => item name and parent category title.
 *
 * @param array $all Normalized "all" payload.
 * @return array Map of item id to array with name and categoryTitle.
 */
function sessionize_category_items_by_id( $all ) {
	$map = array();

	if ( empty( $all['categories'] ) || ! is_array( $all['categories'] ) ) {
		return $map;
	}

	foreach ( $all['categories'] as $category ) {
		if ( ! is_array( $category ) || empty( $category['items'] ) || ! is_array( $category['items'] ) ) {
			continue;
		}

		$category_title = isset( $category['title'] ) ? (string) $category['title'] : '';

		foreach ( $category['items'] as $item ) {
			if ( ! is_array( $item ) || ! isset( $item['id'] ) ) {
				continue;
			}

			$map[ (string) $item['id'] ] = array(
				'name'          => isset( $item['name'] ) ? (string) $item['name'] : '',
				'categoryTitle' => $category_title,
			);
		}
	}

	return $map;
}

/**
 * Parses a Sessionize timestamp without applying any timezone conversion.
 *
 * Sessionize returns naive local timestamps such as `2025-09-15T14:30:00` with no
 * offset, and the front-end scripts read them as local wall-clock time. The server
 * has to do the same: applying the site timezone here would shift every displayed
 * time, and the shift would then visibly correct itself when the client script
 * hydrated. UTC is used purely as a neutral reference frame so that formatting the
 * value back out returns exactly the digits Sessionize supplied.
 *
 * @param string $value Sessionize timestamp.
 * @return DateTimeImmutable|null Parsed value, or null when unparseable.
 */
function sessionize_parse_time( $value ) {
	$value = trim( (string) $value );

	if ( '' === $value ) {
		return null;
	}

	try {
		return new DateTimeImmutable( $value, new DateTimeZone( 'UTC' ) );
	} catch ( Exception $e ) {
		return null;
	}
}

/**
 * Formats a Sessionize timestamp as a time of day.
 *
 * @param DateTimeImmutable|null $date        Parsed timestamp.
 * @param string                 $time_format Either '12h' or '24h'.
 * @return string Formatted time, or an empty string.
 */
function sessionize_format_time( $date, $time_format ) {
	if ( ! $date instanceof DateTimeImmutable ) {
		return '';
	}

	return '24h' === $time_format ? $date->format( 'H:i' ) : $date->format( 'g:i A' );
}

/**
 * Formats a Sessionize timestamp as a date.
 *
 * @param DateTimeImmutable|null $date        Parsed timestamp.
 * @param string                 $date_format Either 'mdy' or 'dmy'.
 * @return string Formatted date, or an empty string.
 */
function sessionize_format_date( $date, $date_format ) {
	if ( ! $date instanceof DateTimeImmutable ) {
		return '';
	}

	return 'dmy' === $date_format ? $date->format( 'l, j F Y' ) : $date->format( 'l, F j, Y' );
}
