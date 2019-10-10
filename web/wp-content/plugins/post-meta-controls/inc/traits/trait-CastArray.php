<?php

namespace POSTMETACONTROLS;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Trait CastArray
 */
trait CastArray {

	/**
	 * Utility that returns an array.
	 * If the provided value is not an array the function will wrap it in one.
	 */
	function cast_array( $value ) {
		if ( is_array( $value ) ) {
			return $value;
		}

		if ( is_string( $value ) || is_int( $value ) || is_bool( $value ) ) {
			return array( $value );
		}

		return array();
	}
}
