<?php

namespace POSTMETACONTROLS;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Class Base Panel
 */
class Panel extends Base {

	protected function after_cast_props() {
		$this->set_id_with_prefix();
	}

	protected function set_defaults() {
		$this->props_defaults = array(
			'id'            => wp_generate_uuid4(),
			'id_prefix'     => '',
			'path'          => array(),
			'label'         => '',
			'post_type'     => 'post', // It will be passed through cast_array().
			'initial_open'  => true,
			'collapsible'   => true,
			'icon_dashicon' => '',
			'icon_svg'      => '',
		);
	}

	protected function set_schema() {
		$this->props_schema = array(
			'id' => array(
				'type'       => 'id',
				'for_js'     => true,
				'conditions' => 'not_empty',
			),
			'id_prefix' => array(
				'type'   => 'id',
				'for_js' => true,
			),
			'path' => array(
				'type'       => array( '_all' => 'id' ),
				'for_js'     => true,
				'conditions' => 'not_empty',
			),
			'label' => array(
				'type'       => 'text',
				'for_js'     => true,
				'conditions' => true === $this->props['collapsible']
					? 'not_empty'
					: false,
			),
			'post_type' => array(
				'type'   => array( '_all' => 'id' ),
				'for_js' => false,
			),
			'initial_open' => array(
				'type'   => 'boolean',
				'for_js' => true,
			),
			'collapsible' => array(
				'type'   => 'boolean',
				'for_js' => true,
			),
			'icon_dashicon' => array(
				'type'   => 'id',
				'for_js' => true,
			),
			'icon_svg' => array(
				'type'   => 'html_svg',
				'for_js' => true,
			),
		);
	}
}
