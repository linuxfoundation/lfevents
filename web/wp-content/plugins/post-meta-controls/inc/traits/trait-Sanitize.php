<?php

namespace POSTMETACONTROLS;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Trait Sanitize
 */
trait Sanitize {

	/**
	 * Sanitize HTML svg.
	 *
	 * https://wordpress.stackexchange.com/a/316943 | CC BY-SA 3.0
	 */
	public function sanitize_html_svg( $value ) {
		if ( empty( $value ) ) {
			return '';
		}

		$allowed_svg = array(
			'svg' => array(
				'class'           => true,
				'aria-hidden'     => true,
				'aria-labelledby' => true,
				'role'            => true,
				'width'           => true,
				'height'          => true,
				'stroke-width'    => true,
				'viewbox'         => true,
			),
			'rect' => array(
				'class'        => true,
				'stroke-width' => true,
				'fill'         => true,
				'x'            => true,
				'y'            => true,
			),
			'g'       => array( 'class' => true, 'stroke-width' => true, 'fill' => true ),
			'path'    => array( 'class' => true, 'stroke-width' => true, 'fill' => true, 'd' => true, ),
			'polygon' => array( 'class' => true, 'stroke-width' => true, 'fill' => true, 'points' => true, ),
			'title'   => array( 'class' => true, 'title' => true ),
		);
		$value = wp_kses( $value, $allowed_svg );
		$value = wp_json_encode( $value );

		return $value;
	}

	/**
	 * Sanitize HTML.
	 */
	public function sanitize_html( $value ) {
		$value = wp_kses_post( $value );
		$value = wp_json_encode( $value );

		return $value;
	}

	/**
	 * Sanitize HTML raw.
	 */
	public function sanitize_html_raw( $value ) {
		if ( ! is_string( $value ) ) {
			return '';
		}

		$value = wp_json_encode( $value );

		return $value;
	}

	/**
	 * Sanitize string and/or integer.
	 */
	public function sanitize_string_integer( $value ) {
		if ( ! is_string( $value ) && ! is_int( $value ) ) {
			return '';
		}

		return $value;
	}

	/**
	 * Sanitize id.
	 */
	public function sanitize_id( $value ) {
		$value = $this->sanitize_string_integer( $value );

		return \sanitize_key( $value );
	}

	/**
	 * Sanitize text.
	 */
	public function sanitize_text( $value ) {
		$value = $this->sanitize_string_integer( $value );

		return \sanitize_text_field( $value );
	}

	/**
	 * Sanitize textarea.
	 */
	public function sanitize_textarea( $value ) {
		$value = $this->sanitize_string_integer( $value );

		return \sanitize_textarea_field( $value );
	}

	/**
	 * Sanitize float.
	 */
	public function sanitize_float( $value ) {
		return round( abs( floatval( $value ) ), 2 );
	}

	/**
	 * Sanitize integer.
	 */
	public function sanitize_integer( $value ) {
		return \absint( $value );
	}

	/**
	 * Sanitize boolean.
	 *
	 * https://github.com/WPTRT/code-examples/blob/master/customizer/sanitization-callbacks.php
	 */
	public function sanitize_boolean( $value ) {
		return isset( $value ) && true == $value ? true : false;
	}

	/**
	 * Sanitize checkbox.
	 */
	public function sanitize_checkbox( $value ) {
		$value = $this->sanitize_boolean( $value );

		return true === $value ? '1' : '0';
	}

	/**
	 * Sanitize array.
	 */
	public function sanitize_array( $value ) {
		return is_array( $value ) ? $value : array();
	}

	/**
	 * Sanitize value inside an options array.
	 */
	public function sanitize_options( $value = '', $options = array(), $default_value = '' ) {

		$value         = \sanitize_key( $value );
		$options       = $this->sanitize_array( $options );
		$default_value = \sanitize_key( $default_value );

		if ( empty( $options ) ) {
			return $default_value;
		}

		$options = array_map( function( $option ) {
			return $option['value'];
		}, $options );

		// If the input is a valid key, return it. Otherwise, return the default_value.
		return in_array( $value, $options ) ? $value : $default_value;
	}

	/**
	 * Sanitize number range.
	 */
	public function sanitize_range( $value = 50, $min = 0, $max = 100 ) {

		$value = \absint( $value );
		$min   = \absint( $min );
		$max   = \absint( $max );

		$value = max( $value, $min );
		$value = min( $value, $max );

		return $value;
	}

	/**
	 * Sanitize float range.
	 */
	public function sanitize_range_float( $value = 50, $min = 0, $max = 100 ) {

		$value = $this->sanitize_float( $value );
		$min   = $this->sanitize_float( $min );
		$max   = $this->sanitize_float( $max );

		$value = max( $value, $min );
		$value = min( $value, $max );

		return (string) $value;
	}

	/**
	 * Sanitize color text input for HEX, rgb and rgba.
	 *
	 * https://stackoverflow.com/a/31245990 | CC BY-SA 3.0
	 */
	public function sanitize_color( $value ) {

		$color          = \sanitize_text_field( $value );
		$regex_rgb_rgba = '/rgba?\(((25[0-5]|2[0-4]\d|1\d{1,2}|\d\d?)\s*,\s*?){2}(25[0-5]|2[0-4]\d|1\d{1,2}|\d\d?)\s*,?\s*([01]\.?\d*?)?\)/';
		$regex_hex      = '/#([a-fA-F0-9]{3}){1,2}\b/';

		// If the color is HEX, rgb or rgba return it.
		if (
			true == preg_match( $regex_hex, $color ) ||
			true == preg_match( $regex_rgb_rgba, $color )
		) {
			return $color;
		}

		return '';
	}
}
