<?php

namespace POSTMETACONTROLS;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Class Base Setting Buttons
 */
class Buttons extends Setting {

	protected function set_defaults() {
		$this_defaults = array(
			'type'          => 'buttons',
			'default_value' => '',
			'allow_empty'   => false,
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
			'allow_empty' => array(
				'type'   => 'boolean',
				'for_js' => true,
			),
			'options' => array(
				'type' => array(
					'_all' => array(
						'value'         => 'id',
						'title'         => 'text',
						'icon_dashicon' => 'id',
						'icon_svg'      => 'html_svg',
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
