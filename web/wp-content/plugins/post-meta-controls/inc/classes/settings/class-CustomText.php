<?php

namespace POSTMETACONTROLS;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Class Base Setting CustomText
 */
class CustomText extends Setting {

	protected function set_defaults() {
		$this_defaults = array(
			'type'    => 'custom_text',
			'content' => '',
		);

		$parent_defaults = Setting::get_defaults();

		$this->props_defaults =
			wp_parse_args( $this_defaults, $parent_defaults );
	}

	protected function set_schema() {
		$this_schema = array(
			'content' => array(
				'type' => array(
					'_all' => array(
						'type'    => 'id',
						'content' => array( '_all' => 'text' ),
						'href'    => 'text',
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
