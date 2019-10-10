<?php

namespace POSTMETACONTROLS;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Class Base Setting Color
 */
class Color extends Setting {

	use PreparePalette;

	protected function before_set_schema() {
		Setting::before_set_schema();
		$this->props['palette'] = $this->prepare_palette( $this->props['palette'] );
	}

	protected function set_defaults() {
		$this_defaults = array(
			'type'          => 'color',
			'default_value' => '',
			'alpha_control' => false,
			'palette'       => array(),
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
			'alpha_control' => array(
				'type'   => 'boolean',
				'for_js' => true,
			),
			'palette' => array(
				'type' => array(
					'_all' => array(
						'name'  => 'id',
						'color' => 'text',
					),
				),
				'for_js' => true,
			),
		);

		$parent_schema = Setting::get_schema();

		$this->props_schema =
			wp_parse_args( $this_schema, $parent_schema );
	}
}
