<?php

namespace POSTMETACONTROLS;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Class Base Setting RangeFloat
 */
class RangeFloat extends Setting {

	protected function set_defaults() {
		$this_defaults = array(
			'type'          => 'range_float',
			'default_value' => 50,
			'step'          => 1,
			'min'           => 0,
			'max'           => 100,
		);

		$parent_defaults = Setting::get_defaults();

		$this->props_defaults =
			wp_parse_args( $this_defaults, $parent_defaults );
	}

	protected function set_schema() {
		$this_schema = array(
			'default_value' => array(
				'type'   => 'float',
				'for_js' => true,
			),
			'step' => array(
				'type'       => 'float',
				'for_js'     => true,
				'conditions' => array(
					$this->props['step'] > 0,
					( $this->props['max'] - $this->props['min'] ) > $this->props['step'],
				),
			),
			'min' => array(
				'type'   => 'float',
				'for_js' => true,
			),
			'max' => array(
				'type'       => 'float',
				'for_js'     => true,
				'conditions' => $this->props['max'] > $this->props['min'],
			),
		);

		$parent_schema = Setting::get_schema();

		$this->props_schema =
			wp_parse_args( $this_schema, $parent_schema );
	}
}
