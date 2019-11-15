<?php

namespace POSTMETACONTROLS;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Permissions check.
 */
function get_items_permission( $request ) {

	$data = $request->get_params();

	if ( empty( $data['post_id'] ) ) {
		return false;
	}

	if ( ! current_user_can( 'edit_post', $data['post_id'] ) ) {
		return false;
	}

	return true;
}

/**
 * Register a route to get the items data in the editor.
 */
add_action( 'rest_api_init', __NAMESPACE__ . '\register_route_items' );
function register_route_items() {

	register_rest_route(
		'post-meta-controls/v1',
		'/items',
		array(
			'methods'             => 'GET',
			'callback'            => __NAMESPACE__ . '\get_items',
			'permission_callback' => __NAMESPACE__ . '\get_items_permission',
		)
	);
}
function get_items( $request ) {

	$data = $request->get_params();

	if ( empty( $data['post_id'] ) || empty( $data['post_type'] ) ) {
		// Gutenberg throws the error invalid_json if null is sent.
		return false;
	}

	$post_id   = $data['post_id'];
	$post_type = $data['post_type'];

	// Filter used to add custom sidebars inside other plugins/themes.
	$props_raw = apply_filters( 'pmc_create_sidebar', array() );

	if ( ! is_array( $props_raw ) ) {
		// Gutenberg throws the error invalid_json if null is sent.
		return false;
	}

	if ( empty( $props_raw ) ) {
		// Gutenberg throws the error invalid_json if null is sent.
		return false;
	}

	// Create the class instances for each item: sidebars, tabs, panels and settings.
	$instances = create_instances( $props_raw );

	if (
		empty( $instances ) ||
		empty( $instances['sidebars'] ) ||
		empty( $instances['tabs'] ) ||
		empty( $instances['panels'] ) ||
		empty( $instances['settings'] )
	) {
		// Gutenberg throws the error invalid_json if null is sent.
		return false;
	}

	// Set this property here, as the post id wasn't available before.
	set_meta_key_exists( $instances['settings'], $post_id );

	// $post_type = get_post_type();

	// Create an array of properties to localize in the main script.
	// It checks that the instance is assigned to the current post type.
	$props = array(
		'sidebars' => get_props( $instances['sidebars'], $post_type ),
		'tabs'     => get_props( $instances['tabs'], $post_type ),
		'panels'   => get_props( $instances['panels'], $post_type ),
		'settings' => get_props( $instances['settings'], $post_type ),
	);

	if (
		empty( $props['sidebars'] ) ||
		empty( $props['tabs'] ) ||
		empty( $props['panels'] ) ||
		empty( $props['settings'] )
	) {
		// Gutenberg throws the error invalid_json if null is sent.
		return false;
	}

	return $props;
}
