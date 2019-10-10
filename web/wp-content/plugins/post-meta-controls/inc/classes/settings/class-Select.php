<?php

namespace POSTMETACONTROLS;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Class Base Setting Select
 */
class Select extends Setting {

	use PrepareOptions;

	protected function before_set_schema() {
		Setting::before_set_schema();
		$this->props['options'] = $this->prepare_options( $this->props['options'] );
	}

	protected function set_defaults() {
		$this_defaults = array(
			'type'          => 'select',
			'default_value' => '',
			'options'       => array(),
		);

		$parent_defaults = Setting::get_defaults();

		$this->props_defaults =
			wp_parse_args( $this_defaults, $parent_defaults );
	}

	protected function set_schema() {
		$this_schema = array(
			'default_value' => array(
				'type'   => 'id',
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
		);

		$parent_schema = Setting::get_schema();

		$this->props_schema =
			wp_parse_args( $this_schema, $parent_schema );
	}
}
