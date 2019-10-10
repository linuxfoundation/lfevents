<?php

namespace POSTMETACONTROLS;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Class GlobalUtils
 */
class GlobalUtils extends Base {

	use Meta;

	private $default_value;
	private $meta;
	private $value;

	protected function before_set_defaults() {
		$this->set_default_value();
	}

	protected function after_validate_props() {
		$this->set_meta();
		$this->prepare_value();
	}

	/**
	 * Set the default value if given or false.
	 */
	private function set_default_value() {
		$this->default_value = isset( $this->props['default_value'] )
			? $this->props['default_value']
			: false;
	}

	/**
	 * Set props_defaults.
	 */
	protected function set_defaults() {
		$this->props_defaults = array(
			'type'          => '',
			'meta_key'      => '',
			'post_id'       => get_the_ID(),
			'is_single'     => true,
			'return_string' => true,
			'size'          => 'large',
			'return_array'  => true,
		);

		if (
			'checkbox_multiple' === $this->props['type'] ||
			'date_range' === $this->props['type'] ||
			'image_multiple' === $this->props['type']
		) {
			$this->props_defaults['is_single'] = false;
		}
	}

	/**
	 * Set props_schema.
	 */
	protected function set_schema() {
		$this->props_schema = array(
			'type' => array(
				'type'       => 'id',
				'conditions' => 'not_empty',
			),
			'meta_key' => array(
				'type'       => 'id',
				'conditions' => 'not_empty',
			),
			'post_id' => array(
				'type'       => 'integer',
				'conditions' => 'not_empty',
			),
			'is_single' => array(
				'type' => 'boolean',
			),
			'return_string' => array(
				'type' => 'boolean',
			),
			'size' => array(
				'type' => 'id',
			),
			'return_array' => array(
				'type' => 'boolean',
			),
		);
	}

	public function get_value() {
		return $this->value;
	}

	/**
	 * Call the prepare function depending on the prop type.
	 */
	private function prepare_value() {

		if ( false === $this->meta ) {
			$this->value = $this->default_value;
			return;
		}

		switch ( $this->props['type'] ) {
			case 'buttons':
				return $this->prepare_buttons();
				break;

			case 'checkbox':
				return $this->prepare_checkbox();
				break;

			case 'checkbox_multiple':
				return $this->prepare_checkbox_multiple();
				break;

			case 'color':
				return $this->prepare_color();
				break;

			case 'custom_text':
				return $this->prepare_custom_text();
				break;

			case 'date_range':
				return $this->prepare_date_range();
				break;

			case 'date_single':
				return $this->prepare_date_single();
				break;

			case 'image':
				return $this->prepare_image();
				break;

			case 'image_multiple':
				return $this->prepare_image_multiple();
				break;

			case 'radio':
				return $this->prepare_radio();
				break;

			case 'range':
				return $this->prepare_range();
				break;

			case 'range_float':
				return $this->prepare_range_float();
				break;

			case 'select':
				return $this->prepare_select();
				break;

			case 'text':
				return $this->prepare_text();
				break;

			case 'textarea':
				return $this->prepare_textarea();
				break;
		}
	}

	/**
	 * Set meta.
	 */
	private function set_meta() {

		if ( false === $this->props['valid'] ) {
			$this->meta = false;
			return;
		}

		$types = array(
			'buttons',
			'checkbox',
			'checkbox_multiple',
			'color',
			'custom_text',
			'date_range',
			'date_single',
			'image',
			'image_multiple',
			'radio',
			'range',
			'range_float',
			'select',
			'text',
			'textarea',
		);

		if ( ! in_array( $this->props['type'], $types ) ) {
			$this->meta = false;
			return;
		}

		$meta = get_post_meta(
			$this->props['post_id'],
			$this->props['meta_key'],
			$this->props['is_single']
		);

		// If the value is the same as the one returned by a non-existent meta key
		// we make sure it exists, and if it doesn't we return false.
		if ( '' === $meta || ( is_array( $meta ) && empty( $meta ) ) ) {
			$exists = $this->meta_key_exists(
				$this->props['post_id'],
				$this->props['meta_key']
			);

			if ( false === $exists ) {
				$this->meta = false;
				return;
			}
		}

		$this->meta = $meta;
	}

	private function prepare_buttons() {
		$this->value = $this->sanitize_id( $this->meta );
	}

	private function prepare_checkbox() {
		$this->value = '1' === $this->meta ? true : false;
	}

	private function prepare_checkbox_multiple() {

		$value       = $this->sanitize_array( $this->meta );
		$value_clean = array();

		foreach ( $value as $option_key => $option_value ) {
			$option_value = $this->sanitize_id( $option_value );

			if ( '' !== $option_value ) {
				$value_clean[] = $option_value;
			}
		}

		$this->value = $value_clean;
	}

	private function prepare_color() {

		$value = $this->sanitize_color( $this->meta );

		if ( false === $this->props['return_string'] ) {

			$color = $value;
			$alpha = 100;

			// https://stackoverflow.com/a/31245990 | CC BY-SA 3.0
			$regex_rgb_rgba = '/rgba?\(((25[0-5]|2[0-4]\d|1\d{1,2}|\d\d?)\s*,\s*?){2}(25[0-5]|2[0-4]\d|1\d{1,2}|\d\d?)\s*,?\s*([01]\.?\d*?)?\)/';

			// https://stackoverflow.com/a/9586150 | CC BY-SA 3.0
			$regex_rgb = '/rgb\(\s*?(\d{1,3}),\s*?(\d{1,3}),\s*?(\d{1,3})\s*?\)/';

			if (
				false == preg_match( $regex_rgb, $color ) &&
				true == preg_match( $regex_rgb_rgba, $color )
			) {

				$alpha = preg_replace( '/(.*?,\s?)(\d+(\.?\d+)?)(\s*?\))/', '$2', $color );
				$color = preg_replace( '/(rgb)(a)(.*?)(,\s?\d+(\.?\d+)?)(\s*?\))/', '$1$3$6', $color );
				$alpha = $this->sanitize_float( $alpha );
				$alpha = min( 1, $alpha );
				$alpha = 100 * $alpha;
				$alpha = $this->sanitize_integer( $alpha );

			}

			$value = array(
				'color' => $color,
				'alpha' => $alpha,
			);
		}

		$this->value = $value;
	}

	private function prepare_date_range() {

		$value       = $this->sanitize_array( $this->meta );
		$value_clean = array();

		foreach ( $value as $date_key => $date_value ) {
			$date_value = $this->sanitize_text( $date_value );

			if ( '' !== $date_value ) {
				$value_clean[] = $date_value;
			}
		}

		$this->value = $value_clean;
	}

	private function prepare_date_single() {
		$this->value = $this->sanitize_text( $this->meta );
	}

	private function prepare_image() {

		$value = $this->sanitize_integer( $this->meta );

		if ( false === $this->props['return_array'] ) {
			$this->value = $value;
			return;
		}

		// If not found returns false.
		$image = wp_get_attachment_image_src( $value, $this->props['size'] );

		if ( false === $image ) {
			$this->value = false;
			return;
		}

		$image = array(
			'url'    => $image[0],
			'width'  => $image[1],
			'height' => $image[2],
		);

		$this->value = $image;
	}

	private function prepare_image_multiple() {

		$value       = $this->sanitize_array( $this->meta );
		$value_clean = array();

		foreach ( $value as $key => $id ) {
			$id = $this->sanitize_integer( $id );

			if ( 0 !== $id ) {

				if ( false === $this->props['return_array'] ) {

					$value_clean[] = $id;

				} else {

					$image = wp_get_attachment_image_src( $id, $this->props['size'] );

					if ( false !== $image ) {
						$value_clean[ $id ] = array(
							'url'    => $image[0],
							'width'  => $image[1],
							'height' => $image[2],
						);
					}
				}
			}
		}

		$this->value = $value_clean;
	}

	private function prepare_radio() {
		$this->value = $this->sanitize_id( $this->meta );
	}

	private function prepare_range() {
		$this->value = $this->sanitize_integer( $this->meta );
	}

	private function prepare_range_float() {
		$this->value = $this->sanitize_float( $this->meta );
	}

	private function prepare_select() {
		$this->value = $this->sanitize_id( $this->meta );
	}

	private function prepare_text() {
		$this->value = $this->sanitize_text( $this->meta );
	}

	private function prepare_textarea() {
		$this->value = $this->sanitize_textarea( $this->meta );
	}
}
