<?php

namespace POSTMETACONTROLS;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Trait CastSchema
 */
trait CastSchema {

	/**
	 * Cast an array of properties and values given a schema array.
	 */
	protected function cast_schema( $elements = array(), $schema = array() ) {

		foreach ( $elements as $key => $value ) {

			if ( isset( $schema[ $key ]['type'] ) ) {
				$type = $schema[ $key ]['type'];
			} elseif ( isset( $schema[ $key ] ) ) {
				$type = $schema[ $key ];
			} elseif ( isset( $schema['_all'] ) ) {
				$type = $schema['_all'];
			} else {
				unset( $elements[ $key ] );
				continue;
			}

			if ( is_array( $type ) ) {
				$value = $this->cast_array( $value );

				$elements[ $key ] = $this->cast_schema( $value, $type );
				continue;
			}

			switch ( $type ) {
				case 'html':
					$elements[ $key ] = $this->sanitize_html( $value );
					break;

				case 'html_svg':
					$elements[ $key ] = $this->sanitize_html_svg( $value );
					break;

				case 'html_raw':
					$elements[ $key ] = $this->sanitize_html_raw( $value );
					break;

				case 'id':
					$elements[ $key ] = $this->sanitize_id( $value );
					break;

				case 'text':
					$elements[ $key ] = $this->sanitize_text( $value );
					break;

				case 'textarea':
					$elements[ $key ] = $this->sanitize_textarea( $value );
					break;

				case 'float':
					$elements[ $key ] = $this->sanitize_float( $value );
					break;

				case 'integer':
					$elements[ $key ] = $this->sanitize_integer( $value );
					break;

				case 'boolean':
					$elements[ $key ] = $this->sanitize_boolean( $value );
					break;

				default:
					$elements[ $key ] = '';
					break;
			}
		}

		return $elements;
	}
}
