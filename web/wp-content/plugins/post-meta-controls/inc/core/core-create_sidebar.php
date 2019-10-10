<?php

namespace POSTMETACONTROLS;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Create sidebar properties array
 *
 * @since 1.0.0
 */
function create_sidebar() {

	// Filter used to add custom sidebars inside other plugins/themes.
	$props_raw = apply_filters( 'pmc_create_sidebar', array() );

	if ( ! is_array( $props_raw ) || empty( $props_raw ) ) {
		return;
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
		return;
	}

	// Register the meta fields for those settings that are of meta data_type.
	register_meta( $instances['settings'] );
}
