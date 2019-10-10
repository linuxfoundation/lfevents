<?php

namespace POSTMETACONTROLS;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Trait ValidateConditions
 */
trait ValidateConditions {

	/**
	 * Validate values based on given conditions.
	 */
	protected function validate_conditions( $props = array(), $schemas = array() ) {

		$is_valid = true;

		foreach ( $schemas as $key => $schema ) {

			if ( ! isset( $schema['conditions'] ) || false === $schema['conditions'] ) {
				continue;
			}

			$conditions = $schema['conditions'];

			if ( is_array( $conditions ) ) {

				foreach ( $conditions as $condition ) {
					$is_valid = $this->validate_condition( $condition, $props[ $key ] );

					if ( false === $is_valid ) {
						return false;
					}
				}

			} else {
				$is_valid = $this->validate_condition( $conditions, $props[ $key ] );
			}

			if ( false === $is_valid ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Validate value based on given condition.
	 */
	private function validate_condition( $condition, $value ) {

		if ( 'not_empty' === $condition && empty( $value ) ) {
			return false;
		} elseif ( is_bool( $condition ) && false === $condition ) {
			return false;
		}

		return true;
	}
}
