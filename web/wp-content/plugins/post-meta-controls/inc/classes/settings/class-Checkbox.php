<?php

namespace POSTMETACONTROLS;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Class Base Setting Checkbox
 */
class Checkbox extends Setting {

	protected function set_defaults() {
		$this_defaults = array(
			'type'          => 'checkbox',
			'default_value' => false,
			'input_label'   => '',
			'use_toggle'    => false,
		);

		$parent_defaults = Setting::get_defaults();

		$this->props_defaults =
			wp_parse_args( $this_defaults, $parent_defaults );
	}

	protected function set_schema() {
		$this_schema = array(
			'default_value' => array(
				'type'   => 'boolean',
				'for_js' => true,
			),
			'input_label' => array(
				'type'       => 'text',
				'for_js'     => true,
				'conditions' => 'not_empty',
			),
			'use_toggle' => array(
				'type'   => 'boolean',
				'for_js' => true,
			),
		);

		$parent_schema = Setting::get_schema();

		$this->props_schema =
			wp_parse_args( $this_schema, $parent_schema );
	}
}
