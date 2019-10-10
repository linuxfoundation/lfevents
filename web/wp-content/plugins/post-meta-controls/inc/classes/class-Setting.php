<?php

namespace POSTMETACONTROLS;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Class Base Setting
 */
abstract class Setting extends Base {

	use Meta;

	protected function after_cast_props() {
		$this->set_id_with_prefix();
	}

	public function get_setting_type() {
		return $this->props['type'];
	}

	public function get_data_key_with_prefix() {
		return $this->props['data_key_with_prefix'];
	}

	protected function before_set_schema() {
		$this->prepare_data_type();
		$this->set_data_key_with_prefix();
	}

	protected function set_data_key_with_prefix() {

		if ( empty( $this->props['data_key'] ) ) {
			return;
		}

		$prefix = false !== $this->props['data_key_prefix']
			? $this->props['data_key_prefix']
			: $this->props['data_key_prefix_from_sidebar'];

		$prefix = (string) $prefix;

		$this->props['data_key_with_prefix'] = $prefix . $this->props['data_key'];
	}

	public function set_meta_key_exists( $post_id = 0 ) {

		if (
			true !== $this->props['valid'] ||
			'meta' !== $this->props['data_type']
		) {
			return;
		}

		$this->props['meta_key_exists'] =
			$this->meta_key_exists(
				$post_id,
				$this->props['data_key_with_prefix']
			);
	}

	public function register_meta() {

		if (
			true !== $this->props['valid'] ||
			'meta' !== $this->props['data_type'] ||
			true !== $this->props['register_meta']
		) {
			return;
		}

		$props         = $this->props;
		$meta_type     = $this->get_meta_type( $props );
		$meta_single   = $this->get_meta_single( $props['type'] );
		$meta_sanitize = $this->get_meta_sanitize( $props );
		$post_types    = $this->cast_array( $props['post_type'] );

		foreach ( $post_types as $post_type) {

			register_post_meta(
				$post_type,
				$props['data_key_with_prefix'],
				array(
					'show_in_rest'      => true,
					'single'            => $meta_single,
					'type'              => $meta_type,
					'sanitize_callback' => $meta_sanitize,
				)
			);
		}
	}

	private function prepare_data_type() {

		$data_type    = $this->props['data_type'];
		$setting_type = $this->props['type'];

		$setting_types_can_have_meta = array(
			'buttons',
			'checkbox',
			'checkbox_multiple',
			'color',
			'date_range',
			'date_single',
			'image',
			'image_multiple',
			'repeatable',
			'radio',
			'range',
			'range_float',
			'select',
			'text',
			'textarea',
		);
		if (
			'meta' === $data_type &&
			in_array( $setting_type, $setting_types_can_have_meta )
		) {
			return;
		}

		$setting_types_can_have_localstorage = array(
			'buttons',
			'checkbox',
			'checkbox_multiple',
			'color',
			'date_range',
			'date_single',
			'image',
			'image_multiple',
			'repeatable',
			'radio',
			'range',
			'range_float',
			'select',
			'text',
			'textarea',
		);
		if (
			'localstorage' === $data_type &&
			in_array( $setting_type, $setting_types_can_have_localstorage )
		) {
			return;
		}

		$this->props['data_type'] = 'none';
		$this->props['data_key']  = '';
	}

	protected function set_privates() {
		$this->props_privates = array(
			'data_key_with_prefix',
			'meta_key_exists',
		);
	}

	protected function get_defaults() {
		return array(
			'id'                           => wp_generate_uuid4(),
			'id_prefix'                    => '',
			'path'                         => array(),
			'label'                        => '',
			'post_type'                    => 'post', // It will be passed through cast_array().
			'type'                         => '',
			'help'                         => '',
			'data_type'                    => 'none',
			'meta_key_exists'              => false,
			'data_key'                     => '',
			'data_key_prefix'              => false,
			'data_key_with_prefix'         => '',
			'data_key_prefix_from_sidebar' => '',
			'register_meta'                => true,
			'ui_border_top'                => true,
		);
	}

	protected function get_schema() {
		return array(
			'id' => array(
				'type'       => 'id',
				'for_js'     => true,
				'conditions' => 'not_empty',
			),
			'id_prefix' => array(
				'type'   => 'id',
				'for_js' => false,
			),
			'path' => array(
				'type'       => array( '_all' => 'id' ),
				'for_js'     => true,
				'conditions' => 'not_empty',
			),
			'label' => array(
				'type'   => 'text',
				'for_js' => true,
			),
			'post_type' => array(
				'type'   => array( '_all' => 'id' ),
				'for_js' => false,
			),
			'type' => array(
				'type'       => 'id',
				'for_js'     => true,
				'conditions' => 'not_empty',
			),
			'help' => array(
				'type'   => 'text',
				'for_js' => true,
			),
			'data_type' => array(
				'type'   => 'id',
				'for_js' => true,
			),
			'meta_key_exists' => array(
				'type'   => 'boolean',
				'for_js' => true,
			),
			'data_key' => array(
				'type'       => 'id',
				'for_js'     => false,
				'conditions' => 'none' !== $this->props['data_type']
					? 'not_empty'
					: false,
			),
			'data_key_prefix' => array(
				'type'   => 'id',
				'for_js' => false,
			),
			'data_key_prefix_from_sidebar' => array(
				'type'   => 'id',
				'for_js' => false,
			),
			'data_key_with_prefix' => array(
				'type'   => 'id',
				'for_js' => true,
			),
			'register_meta' => array(
				'type'   => 'boolean',
				'for_js' => false,
			),
			'ui_border_top' => array(
				'type'   => 'boolean',
				'for_js' => true,
			),
		);
	}
}
