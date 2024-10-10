<?php
/**
 * Function used with the Condition API to check if a block should be rendered when using the BlockAction field.
 * 
 * @param mixed $has_match
 * @param mixed $condition
 * @return boolean
 */
function cb_check_block_action( $has_match, $condition ) {

	$block_action = ! empty( $condition['blockAction'] ) ? $condition['blockAction'] : 'showBlock';

	if ( $has_match && $block_action === 'showBlock' ) {
		return true;
	} elseif ( ! $has_match && $block_action === 'hideBlock' ) {
		return true;
	}

	return false;
}

/**
 * Determines the post id of the current post. Supports nested loops.
 * 
 * The loop post id is preferred over the queried object id. This allows us to support query-loop blocks.
 * @return bool|int
 */
function cb_get_target_post_id() {
	$current_post_id = get_queried_object_id();
	$maybe_loop_post = get_the_ID();

	if ( ! empty( $maybe_loop_post ) && $maybe_loop_post !== $current_post_id ) {
		$current_post_id = get_the_ID();
	}

	return $current_post_id;
}

/**
 * Flattens an value into a string, handling nested arrays and strings.
 * Objects are skipped, and arrays are converted to their string representation.
 * Empty strings are filtered out from the final result.
 * 
 * We'll need to support WP Post Objects later.
 *
 * @param mixed  $meta_value The value to be processed. Can be a string, array, or other types.
 * @param string $key       Optional. The specific key to look for in nested arrays. Defaults to 'value'.
 *
 * @return string The flattened string representation of the value, with arrays exported and objects skipped.
 */
function cb_maybe_flatten_meta( $meta_value, $nested_key = false ) {
	// If the value is not an array, simply return the trimmed value if it's a string, or return as is.
	if ( ! is_array( $meta_value ) ) {
		return is_string( $meta_value ) ? trim( $meta_value ) : $meta_value;
	}

	// Flatten the array and trim string values or export arrays, skip objects.
	$flattened_values = array_map( function ($item) use ($nested_key) {
		// Skip if the item is an object.
		if ( is_object( $item ) ) {
			return '';
		}

		if ( is_array( $item ) ) {
			if ( $nested_key && isset( $item[ $nested_key ] ) && ! is_object( $item[ $nested_key ] ) ) {
				$value = $item[ $nested_key ];
				// If the key's value is a string, trim it; if it's an array, export it.
				return is_string( $value ) ? trim( $value ) : ( is_array( $value ) ? var_export( $value, true ) : '' );
			} else {
				// If the item itself is an array without the specified key, export the whole item.
				return var_export( $item, true );
			}
		}
		// If the item is a string, return the trimmed string.
		return is_string( $item ) ? trim( $item ) : $item;
	}, $meta_value );

	// Filter out empty strings and return the imploded string.
	return implode( ' ', array_filter( $flattened_values, 'strlen' ) );
}

function conditional_blocks_get_ip_address() {
	if ( isset( $_SERVER['HTTP_X_REAL_IP'] ) ) {
		$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_REAL_IP'] ) );
	} elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		// Proxy servers can send through this header like this: X-Forwarded-For: client1, proxy1, proxy2.
		// Make sure we always only send through the first IP in the list which should always be the client IP..
		$ip = (string) rest_is_ip_address( trim( current( preg_split( '/,/', sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) ) ) ) );
	} elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
		$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
	}

	if ( empty( $ip ) || $ip === '127.0.0.1' || $ip === '::1' ) {
		return false;
	}

	return $ip;
}

