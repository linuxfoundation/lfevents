<?php
class CB_AFC_Integration {
	private $is_acf_active = false;
	private $is_pro = false;
	private $tested_version = '6.3.12';

	public function __construct() {
		$this->is_acf_active = class_exists( 'ACF' );
				$this->is_pro = true;
		
		add_filter( 'conditional_blocks_register_condition_categories', [ $this, 'register_categories' ], 10, 1 );
		add_filter( 'conditional_blocks_register_condition_types', [ $this, 'register_conditions' ], 10, 1 );
				add_filter( 'conditional_blocks_register_check_acf_field_value', [ $this, 'check_acf_field_value' ], 10, 2 );
			}

	/**
	 * Register condition categories for the ACF integration.
	 * 
	 * Adds the 'Advanced Custom Fields' category to the list of available categories.
	 * 
	 * @param array $categories The list of available categories.
	 * @return array The updated list of categories.
	 */
	public function register_categories( $categories ) {
		$categories[] = [ 
			'value' => 'advanced_custom_fields',
			'label' => __( 'Advanced Custom Fields (ACF)', 'conditional-blocks' ),
			'icon' => plugins_url( 'assets/images/mini-colored/advanced-custom-fields.svg', __DIR__ ), // URL or path to your icon, or dashicon name.
			'tag' => 'plugin',
		];
		return $categories;
	}

	/**
	 * Register condition types for the ACF integration.
	 * 
	 * Adds the 'ACF Field Value' condition type to the list of available types.
	 * 
	 * @param array $conditions The list of available condition types.
	 * @return array The updated list of condition types.
	 */
	public function register_conditions( $conditions ) {

		$conditions[] = [ 
			'type' => 'acf_field_value',
			'label' => __( 'ACF Field Value', 'conditional-blocks' ),
			'is_pro' => true,
			'tag' => 'plugin',
			'is_disabled' => ! $this->is_acf_active || ! $this->is_pro || ! class_exists( 'ACF' ),
			'description' => '',
			'category' => 'advanced_custom_fields',
			'fields' => [ 
				[ 
					'key' => 'acf_field',
					'type' => 'select',
					'attributes' => [ 
						'label' => __( 'ACF Field', 'conditional-blocks' ),
						'help' => __( 'Select a ACF Field from a Field Group', 'conditional-blocks' ),
						'placeholder' => __( 'Select a field', 'conditional-blocks' ),
						'searchable' => true,
					],
					'options' => class_exists( 'ACF' ) ? $this->get_acf_options() : [],
				],
				[ 
					'key' => 'operator',
					'type' => 'select',
					'attributes' => [ 
						'label' => __( 'Operator', 'conditional-blocks' ),
						'help' => __( 'Select a operator used to check the value', 'conditional-blocks' ),
						'searchable' => true,
					],
					'options' => [ 
						[ 'label' => __( 'Has any value', 'conditional-blocks' ), 'value' => 'not_empty' ],
						[ 'label' => __( 'No value', 'conditional-blocks' ), 'value' => 'empty' ],
						[ 'label' => __( 'Equal to', 'conditional-blocks' ), 'value' => 'equal' ],
						[ 'label' => __( 'Not equal to', 'conditional-blocks' ), 'value' => 'not_equal' ],
						[ 'label' => __( 'Contains', 'conditional-blocks' ), 'value' => 'contains' ],
						[ 'label' => __( 'Does not contain', 'conditional-blocks' ), 'value' => 'not_contains' ],
						[ 'label' => __( 'Greater than', 'conditional-blocks' ), 'value' => 'greater_than' ],
						[ 'label' => __( 'Less than', 'conditional-blocks' ), 'value' => 'less_than' ],
						[ 'label' => __( 'Greater than or equal to', 'conditional-blocks' ), 'value' => 'greater_than_or_equal_to' ],
						[ 'label' => __( 'Less than or equal to', 'conditional-blocks' ), 'value' => 'less_than_or_equal_to' ],
					],
				],
				[ 
					'key' => 'expected_value',
					'type' => 'text',
					'requires' => [ 
						'operator' => [ 'equal', 'not_equal', 'contains', 'not_contains', 'greater_than', 'less_than', 'greater_than_or_equal_to', 'less_than_or_equal_to' ],
					],
					'attributes' => [ 
						'label' => __( 'Field Value', 'conditional-blocks' ),
						'help' => __( 'Set the value to compare against - case sensitive.', 'conditional-blocks' ),
						'placeholder' => '',
					],
				],
			],
		];

		return $conditions;
	}
	// @if type = 'premium'

	/**
	 * Check ACF field value condition
	 * 
	 * This function handles both regular ACF fields and repeater/group subfields.
	 * For repeater subfields, it uses dot notation (field.subfield) to identify the field
	 * and checks each row in the repeater based on the match_type.
	 * 
	 * @since 3.2.0 Added support for dot notation to handle repeater subfields
	 * @since 3.2.0 Added support for group fields with hierarchical labels
	 * 
	 * @param bool $should_block_render Whether the block should render
	 * @param array $condition The condition to check
	 * @return bool Whether the condition matches
	 */
	public function check_acf_field_value( $should_block_render, $condition ) {

		if ( ! function_exists( 'get_field_object' ) || ! function_exists( 'get_field' ) ) {
			return $should_block_render;
		}

		$has_match = false;

		$acf_field_id = ! empty( $condition['acf_field']['value'] ) ? $condition['acf_field']['value'] : '';

		if ( empty( $acf_field_id ) ) {
			return $should_block_render;
		}

		// Check if this might be a repeater field subfield based on the dot notation
		if ( strpos( $acf_field_id, '.' ) !== false ) {
			$parts = explode( '.', $acf_field_id, 2 );

			if ( count( $parts ) === 2 ) {
				$repeater_field_id = $parts[0];
				$subfield_name = $parts[1];

				// Get the repeater field value
				$repeater_values = get_field( $repeater_field_id );

				// If repeater is empty or not an array, return false
				if ( empty( $repeater_values ) || ! is_array( $repeater_values ) ) {
					return false;
				}

				$expected_value = isset( $condition['expected_value'] ) ? $condition['expected_value'] : '';
				$operator = ! empty( $condition['operator']['value'] ) ? $condition['operator']['value'] : 'equal';
				$match_type = ! empty( $condition['match_type']['value'] ) ? $condition['match_type']['value'] : 'any_row';

				// Check each row in the repeater
				$matching_rows = 0;
				$total_rows = count( $repeater_values );

				foreach ( $repeater_values as $row ) {
					// Skip if the subfield doesn't exist in this row
					if ( ! isset( $row[ $subfield_name ] ) ) {
						continue;
					}

					$subfield_value = $row[ $subfield_name ];

					// For array values, convert to string for comparison
					if ( is_array( $subfield_value ) ) {
						$subfield_value = cb_maybe_flatten_meta( $subfield_value, 'value' );
					}

					// Use the helper method to check if the value matches based on the operator
					$row_matches = $this->check_value_with_operator( $subfield_value, $expected_value, $operator );

					if ( $row_matches ) {
						$matching_rows++;

						// For 'any_row', we can return as soon as we find a match
						if ( $match_type === 'any_row' ) {
							$has_match = true;
							break;
						}
					}
				}

				// For 'all_rows', all rows must match
				if ( $match_type === 'all_rows' && $matching_rows === $total_rows && $total_rows > 0 ) {
					$has_match = true;
				}

				return $has_match;
			}
		}

		/**
		 * Handle regular ACF fields (non-repeater subfields)
		 * 
		 * The ACF Field Value can contain multiple values, and nested arrays if using the "return array" format.
		 * We use cb_maybe_flatten_meta to handle these complex values for comparison.
		 * 
		 * @link https://www.advancedcustomfields.com/resources/get_field_object
		 */
		$acf_value = get_field( $acf_field_id );

		// ACF can return arrays with key/value pairs, so flatten if needed
		$field_value = cb_maybe_flatten_meta( $acf_value, 'value' );

		$operator = ! empty( $condition['operator']['value'] ) ? $condition['operator']['value'] : 'not_empty';
		$expected_value = isset( $condition['expected_value'] ) ? $condition['expected_value'] : '';

		// Use the helper method to check if the value matches based on the operator
		$has_match = $this->check_value_with_operator( $field_value, $expected_value, $operator );

		return $has_match;
	}

	/**
	 * Helper method to check if a value matches the expected value based on the operator
	 *
	 * @param mixed $value The value to check
	 * @param mixed $expected_value The expected value to compare against
	 * @param string $operator The operator to use for comparison
	 * @return bool Whether the value matches based on the operator
	 */
	private function check_value_with_operator( $value, $expected_value, $operator ) {
		switch ( $operator ) {
			case 'not_empty':
				return ! empty( $value );
			case 'empty':
				return empty( $value );
			case 'equal':
				return $value === $expected_value;
			case 'not_equal':
				return $value !== $expected_value;
			case 'contains':
				return is_string( $value ) && strpos( $value, $expected_value ) !== false;
			case 'not_contains':
				return is_string( $value ) && strpos( $value, $expected_value ) === false;
			case 'greater_than':
				return (float) $value > (float) $expected_value;
			case 'less_than':
				return (float) $value < (float) $expected_value;
			case 'greater_than_or_equal_to':
				return (float) $value >= (float) $expected_value;
			case 'less_than_or_equal_to':
				return (float) $value <= (float) $expected_value;
			default:
				return false;
		}
	}

	/**
	 * Get ACF field options for the select field.
	 * 
	 * Retrieves all ACF field groups and their fields, and formats them as options for the select field.
	 * 
	 * @return array The list of ACF field options.
	 */
	public function get_acf_options() {
		if ( ! function_exists( 'acf_get_field_groups' ) ) {
			return [];
		}

		// Get all the field groups
		$field_groups = acf_get_field_groups();
		$options = [];

		// Check if any field group exists
		if ( $field_groups ) {
			foreach ( $field_groups as $group ) {
				// Skip if group doesn't have key or title
				if ( empty( $group['key'] ) || empty( $group['title'] ) ) {
					continue;
				}

				// Get all the fields within the field group
				$fields = acf_get_fields( $group['key'] );

				if ( ! $fields ) {
					continue;
				}

				$group_options = [];

				// Loop through each field and add it to the group options
				foreach ( $fields as $field ) {
					if ( empty( $field['label'] ) || empty( $field['name'] ) ) {
						continue;
					}

					// Process repeater fields
					if ( $field['type'] === 'repeater' ) {
						$subfields = $this->get_repeater_subfields( $field );

						if ( ! empty( $subfields ) ) {
							foreach ( $subfields as $subfield ) {
								$group_options[] = [ 
									'label' => $field['label'] . ' → ' . $subfield['label'],
									'value' => $field['name'] . '.' . $subfield['value']
								];
							}
						}
					}
					// Process group fields
					else if ( $field['type'] === 'group' ) {
						// Get subfields of the group
						if ( ! empty( $field['sub_fields'] ) && is_array( $field['sub_fields'] ) ) {
							foreach ( $field['sub_fields'] as $subfield ) {
								if ( empty( $subfield['label'] ) || empty( $subfield['name'] ) ) {
									continue;
								}

								// Add each subfield with the group hierarchy in the label
								$group_options[] = [ 
									'label' => $field['label'] . ' → ' . $subfield['label'],
									'value' => $field['name'] . '_' . $subfield['name']
								];
							}
						}
					}
					// Process regular fields
					else {
						$group_options[] = [ 
							'label' => $field['label'],
							'value' => $field['name']
						];
					}
				}

				if ( ! empty( $group_options ) ) {
					$options[] = [ 
						'label' => $group['title'],
						'options' => $group_options
					];
				}
			}
		}

		return $options;
	}

	/**
	 * Extract subfields from a repeater field
	 *
	 * @param array $repeater_field
	 * @return array
	 */
	private function get_repeater_subfields( $repeater_field ) {
		$subfields = [];

		if ( empty( $repeater_field['sub_fields'] ) || ! is_array( $repeater_field['sub_fields'] ) ) {
			return $subfields;
		}

		foreach ( $repeater_field['sub_fields'] as $subfield ) {
			if ( empty( $subfield['label'] ) || empty( $subfield['name'] ) ) {
				continue;
			}

			$subfields[] = [ 
				'label' => $subfield['label'],
				'value' => $subfield['name'],
				'type' => $subfield['type'],
			];
		}

		return $subfields;
	}
}

// Initialize the class to set up the hooks.
new CB_AFC_Integration();
