<?php

namespace POSTMETACONTROLS;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Class Base Setting CheckboxMultiple
 */
class CheckboxMultiple extends Setting {

	use PrepareOptions;

	protected function before_set_schema() {
		Setting::before_set_schema();
		$this->props['options'] = $this->prepare_options( $this->props['options'] );
	}

	protected function set_defaults() {
		$this_defaults = array(
			'type'          => 'checkbox_multiple',
			'default_value' => '', // It will be passed through cast_array().
			'options'       => array(),
			'use_toggle'    => false,
		);

		$parent_defaults = Setting::get_defaults();

		$this->props_defaults =
			wp_parse_args( $this_defaults, $parent_defaults );
	}

	protected function set_schema() {
		$this_schema = array(
			'default_value' => array(
				'type'   => array( '_all' => 'id' ),
				'for_js' => true,
			),
			'options' => array(
				'type' => array(
					'_all' => array(
						'value' => 'id',
						'label' => 'text',
					),
				),
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
