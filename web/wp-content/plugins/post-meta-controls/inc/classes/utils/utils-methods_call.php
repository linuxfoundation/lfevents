<?php

namespace POSTMETACONTROLS;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Trigger the register_meta method.
 *
 * @since 1.0.0
 */
function register_meta( $setting_instances = array() ) {

	$data_key_array = array();

	foreach ( $setting_instances as $setting_instance ) {

		$data_key = $setting_instance->get_data_key_with_prefix();

		// We only register the first setting with a certain meta_key,
		// although we let it be modified with other setting controls inside js.
		if ( ! in_array( $data_key, $data_key_array ) ) {

			$setting_instance->register_meta();

			$data_key_array[] = $data_key;

		}
	}
}

/**
 * Trigger the enqueue_locale method and enqueue date scripts.
 *
 * @since 1.0.0
 */
function enqueue_locale( $setting_instances = array() ) {

	foreach ( $setting_instances as $setting_instance ) {

		$type = $setting_instance->get_setting_type();

		if ( 'date_range' === $type || 'date_single' === $type ) {
			$setting_instance->enqueue_locale();
		}
	}
}

/**
 * Get the clean props array for the given class instances.
 *
 * @since 1.0.0
 */
function get_props( $instances = array(), $post_type_current = '' ) {

	$props_array = array();

	foreach ( $instances as $instance ) {

		$post_type = $instance->get_post_type();

		// Only push the props from the settings that belong to the current post type.
		if (
			empty( $post_type ) ||
			( is_array( $post_type ) && in_array( $post_type_current, $post_type ) ) ||
			$post_type_current === $post_type
		) {
			$props_array[] = $instance->get_props_for_js();
		}
	}

	return $props_array;
}

/**
 * Set meta_key_exists prop value.
 *
 * @since 1.0.0
 */
function set_meta_key_exists( $setting_instances = array(), $post_id = 0 ) {

	$post_id = ! empty( $post_id ) ? $post_id : get_the_ID();

	foreach ( $setting_instances as $setting_instance ) {

		$setting_instance->set_meta_key_exists( $post_id );

	}
}
