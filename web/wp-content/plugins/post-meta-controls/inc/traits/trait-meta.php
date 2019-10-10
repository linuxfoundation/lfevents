<?php

namespace POSTMETACONTROLS;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Trait Meta
 */
trait Meta {

	/**
	 * Check if meta key exists.
	 */
	protected function meta_key_exists( $post_id = 0, $meta_key = '' ) {

		$keys = get_post_custom_keys( $post_id );

		if ( false === is_array( $keys ) ) {
			return false;
		}

		return in_array( $meta_key, $keys );
	}

	/**
	 * Get the meta type given the setting type.
	 */
	protected function get_meta_type( $props = array() ) {

		switch ( $props['type'] ) {
			case 'repeatable':
				$props['type'] = $props['type_to_repeat'];

				return $this->get_meta_type( $props );
				break;

			case 'checkbox':
				return 'boolean';
				break;

			case 'range_float':
				return 'number';
				break;

			case 'image':
			case 'image_multiple':
			case 'range':
				return 'integer';
				break;

			default:
				return 'string';
				break;
		}
	}

	/**
	 * Get the meta single property value given the setting type.
	 */
	protected function get_meta_single( $setting_type = '' ) {

		if (
			'repeatable' === $setting_type ||
			'date_range' === $setting_type ||
			'checkbox_multiple' === $setting_type ||
			'image_multiple' === $setting_type
		) {
			return false;
		}

		return true;
	}

	/**
	 * Get the meta sanitize callback function.
	 */
	protected function get_meta_sanitize( $props = array() ) {

		switch ( $props['type'] ) {
			case 'repeatable':
				$props['type'] = $props['type_to_repeat'];

				return $this->get_meta_sanitize( $props );
				break;

			case 'checkbox':
				return array( $this, 'sanitize_checkbox' );
				break;

			case 'textarea':
				return array( $this, 'sanitize_textarea' );
				break;

			case 'image':
			case 'image_multiple':
				return function ( $value ) {
					return \absint( $value );
				};
				break;

			case 'range':
				$min = $props['min'];
				$max = $props['max'];
				return function ( $value ) use ( $min, $max ) {
					return $this->sanitize_range( $value, $min, $max );
				};
				break;

			case 'range_float':
				$min = $props['min'];
				$max = $props['max'];
				return function ( $value ) use ( $min, $max ) {
					return $this->sanitize_range_float( $value, $min, $max );
				};
				break;

			case 'buttons':
			case 'radio':
			case 'select':
			case 'checkbox_multiple':
				$options       = $props['options'];
				$default_value = 'checkbox_multiple' === $props['type']
					? ''
					: $props['default_value'];

				return function ( $value ) use ( $options, $default_value ) {
					return $this->sanitize_options( $value, $options, $default_value );
				};
				break;

			default:
				return array( $this, 'sanitize_text' );
				break;
		}
	}
}
