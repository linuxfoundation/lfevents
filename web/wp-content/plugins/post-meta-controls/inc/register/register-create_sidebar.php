<?php

namespace POSTMETACONTROLS;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

// Load the main plugin function.
add_action( 'init', __NAMESPACE__ . '\create_sidebar' );
