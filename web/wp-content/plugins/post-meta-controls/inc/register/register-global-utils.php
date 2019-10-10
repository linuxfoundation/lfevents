<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

use POSTMETACONTROLS\GlobalUtils;

if ( ! function_exists( 'pmc_get_buttons' ) ) {
	/**
	 * Setting - Buttons. Returns a string with the selected option;
	 * or the $default_value passed (false) if the meta key doesn't exist.
	 */
	function pmc_get_buttons( $meta_key = '', $post_id = '', $default_value = false ) {
		$props = array(
			'type'          => 'buttons',
			'meta_key'      => $meta_key,
			'post_id'       => ! empty( $post_id ) ? $post_id : get_the_ID(),
			'default_value' => $default_value,
		);
		$instance = new GlobalUtils( $props );
		return $instance->get_value();
	}
}

if ( ! function_exists( 'pmc_get_checkbox' ) ) {
	/**
	 * Setting - Checkbox. Returns true or false;
	 * or the $default_value passed (an empty string '') if the meta key doesn't exist.
	 */
	function pmc_get_checkbox( $meta_key = '', $post_id = '', $default_value = '' ) {
		$props = array(
			'type'          => 'checkbox',
			'meta_key'      => $meta_key,
			'post_id'       => ! empty( $post_id ) ? $post_id : get_the_ID(),
			'default_value' => $default_value,
		);
		$instance = new GlobalUtils( $props );
		return $instance->get_value();
	}
}

if ( ! function_exists( 'pmc_get_checkbox_multiple' ) ) {
	/**
	 * Setting - Checkbox Multiple. Returns an array of strings with the selected options;
	 * or the $default_value passed (false) if the meta key doesn't exist.
	 */
	function pmc_get_checkbox_multiple( $meta_key = '', $post_id = '', $default_value = false ) {
		$props = array(
			'type'          => 'checkbox_multiple',
			'meta_key'      => $meta_key,
			'post_id'       => ! empty( $post_id ) ? $post_id : get_the_ID(),
			'default_value' => $default_value,
		);
		$instance = new GlobalUtils( $props );
		return $instance->get_value();
	}
}

if ( ! function_exists( 'pmc_get_color' ) ) {
	/**
	 * Setting - Color. Returns a color string or an array:
	 * array( 'color' => 'rgb(0,0,0)', 'alpha' => 50 );
	 * or the $default_value passed (false) if the meta key doesn't exist.
	 */
	function pmc_get_color(
		$meta_key = '',
		$post_id = '',
		$default_value = false,
		$return_string = true
	) {
		$props = array(
			'type'          => 'color',
			'meta_key'      => $meta_key,
			'post_id'       => ! empty( $post_id ) ? $post_id : get_the_ID(),
			'default_value' => $default_value,
			'return_string' => $return_string,
		);
		$instance = new GlobalUtils( $props );
		return $instance->get_value();
	}
}

if ( ! function_exists( 'pmc_get_date_range' ) ) {
	/**
	 * Setting - Date Range. Returns an array of two strings: start date and end date;
	 * or the $default_value passed (false) if the meta key doesn't exist.
	 */
	function pmc_get_date_range( $meta_key = '', $post_id = '', $default_value = false ) {
		$props = array(
			'type'          => 'date_range',
			'meta_key'      => $meta_key,
			'post_id'       => ! empty( $post_id ) ? $post_id : get_the_ID(),
			'default_value' => $default_value,
		);
		$instance = new GlobalUtils( $props );
		return $instance->get_value();
	}
}

if ( ! function_exists( 'pmc_get_date_single' ) ) {
	/**
	 * Setting - Date Single. Returns a string;
	 * or the $default_value passed (false) if the meta key doesn't exist.
	 */
	function pmc_get_date_single( $meta_key = '', $post_id = '', $default_value = false ) {
		$props = array(
			'type'          => 'date_single',
			'meta_key'      => $meta_key,
			'post_id'       => ! empty( $post_id ) ? $post_id : get_the_ID(),
			'default_value' => $default_value,
		);
		$instance = new GlobalUtils( $props );
		return $instance->get_value();
	}
}

if ( ! function_exists( 'pmc_get_image' ) ) {
	/**
	 * Setting - Image. Returns an integer which is the image id or an array with the image properties:
	 * array( 'url' => '#', 'width' => 123, 'height' => 456 );
	 * or the $default_value passed (false) if the meta key doesn't exist.
	 */
	function pmc_get_image(
		$meta_key = '',
		$post_id = '',
		$default_value = false,
		$size = 'large',
		$return_array = true
	) {
		$props = array(
			'type'          => 'image',
			'meta_key'      => $meta_key,
			'post_id'       => ! empty( $post_id ) ? $post_id : get_the_ID(),
			'default_value' => $default_value,
			'size'          => $size,
			'return_array'  => $return_array,
		);
		$instance = new GlobalUtils( $props );
		return $instance->get_value();
	}
}

if ( ! function_exists( 'pmc_get_image_multiple' ) ) {
	/**
	 * Setting - Image Multiple. Returns an array of integers which are the images id
	 * or an array of arrays with the images properties:
	 * array( '123' => array( 'url' => '#', 'width' => 123, 'height' => 456 ) );
	 * or the $default_value passed (false) if the meta key doesn't exist.
	 */
	function pmc_get_image_multiple(
		$meta_key = '',
		$post_id = '',
		$default_value = false,
		$size = 'large',
		$return_array = true
	) {
		$props = array(
			'type'          => 'image_multiple',
			'meta_key'      => $meta_key,
			'post_id'       => ! empty( $post_id ) ? $post_id : get_the_ID(),
			'default_value' => $default_value,
			'size'          => $size,
			'return_array'  => $return_array,
		);
		$instance = new GlobalUtils( $props );
		return $instance->get_value();
	}
}

if ( ! function_exists( 'pmc_get_radio' ) ) {
	/**
	 * Setting - Radio. Returns a string with the selected option;
	 * or the $default_value passed (false) if the meta key doesn't exist.
	 */
	function pmc_get_radio( $meta_key = '', $post_id = '', $default_value = false ) {
		$props = array(
			'type'          => 'radio',
			'meta_key'      => $meta_key,
			'post_id'       => ! empty( $post_id ) ? $post_id : get_the_ID(),
			'default_value' => $default_value,
		);
		$instance = new GlobalUtils( $props );
		return $instance->get_value();
	}
}

if ( ! function_exists( 'pmc_get_range' ) ) {
	/**
	 * Setting - Range. Returns an integer; or the $default_value passed (false)
	 * if the meta key doesn't exist.
	 */
	function pmc_get_range( $meta_key = '', $post_id = '', $default_value = false ) {
		$props = array(
			'type'          => 'range',
			'meta_key'      => $meta_key,
			'post_id'       => ! empty( $post_id ) ? $post_id : get_the_ID(),
			'default_value' => $default_value,
		);
		$instance = new GlobalUtils( $props );
		return $instance->get_value();
	}
}

if ( ! function_exists( 'pmc_get_range_float' ) ) {
	/**
	 * Setting - Range Float. Returns a float; or the $default_value passed (false)
	 * if the meta key doesn't exist.
	 */
	function pmc_get_range_float( $meta_key = '', $post_id = '', $default_value = false ) {
		$props = array(
			'type'          => 'range_float',
			'meta_key'      => $meta_key,
			'post_id'       => ! empty( $post_id ) ? $post_id : get_the_ID(),
			'default_value' => $default_value,
		);
		$instance = new GlobalUtils( $props );
		return $instance->get_value();
	}
}

if ( ! function_exists( 'pmc_get_select' ) ) {
	/**
	 * Setting - Select. Returns a string with the selected option;
	 * or the $default_value passed (false) if the meta key doesn't exist.
	 */
	function pmc_get_select( $meta_key = '', $post_id = '', $default_value = false ) {
		$props = array(
			'type'          => 'select',
			'meta_key'      => $meta_key,
			'post_id'       => ! empty( $post_id ) ? $post_id : get_the_ID(),
			'default_value' => $default_value,
		);
		$instance = new GlobalUtils( $props );
		return $instance->get_value();
	}
}

if ( ! function_exists( 'pmc_get_text' ) ) {
	/**
	 * Setting - Text. Returns a string;
	 * or the $default_value passed (false) if the meta key doesn't exist.
	 */
	function pmc_get_text( $meta_key = '', $post_id = '', $default_value = false ) {
		$props = array(
			'type'          => 'text',
			'meta_key'      => $meta_key,
			'post_id'       => ! empty( $post_id ) ? $post_id : get_the_ID(),
			'default_value' => $default_value,
		);
		$instance = new GlobalUtils( $props );
		return $instance->get_value();
	}
}

if ( ! function_exists( 'pmc_get_textarea' ) ) {
	/**
	 * Setting - Textarea. Returns a string;
	 * or the $default_value passed (false) if the meta key doesn't exist.
	 */
	function pmc_get_textarea( $meta_key = '', $post_id = '', $default_value = false ) {
		$props = array(
			'type'          => 'textarea',
			'meta_key'      => $meta_key,
			'post_id'       => ! empty( $post_id ) ? $post_id : get_the_ID(),
			'default_value' => $default_value,
		);
		$instance = new GlobalUtils( $props );
		return $instance->get_value();
	}
}
