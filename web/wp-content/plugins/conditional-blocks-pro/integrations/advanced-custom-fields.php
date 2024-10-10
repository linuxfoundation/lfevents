<?php
class CB_AFC_Integration {
	private $is_acf_active = false;
	private $is_pro = false;
	private $tested_version = '6.2.8';

	public function __construct() {
		$this->is_acf_active = class_exists( 'ACF' );
				$this->is_pro = true;
		
		add_filter( 'conditional_blocks_register_condition_categories', [ $this, 'register_categories' ], 10, 1 );
		add_filter( 'conditional_blocks_register_condition_types', [ $this, 'register_conditions' ], 10, 1 );
				add_filter( 'conditional_blocks_register_check_acf_field_value', [ $this, 'acf_field_value' ], 10, 2 );
			}

	public function register_categories( $categories ) {
		$categories[] = [ 
			'value' => 'advanced_custom_fields',
			'label' => __( 'Advanced Custom Fields (ACF)', 'conditional-blocks' ),
			'icon' => plugins_url( 'assets/images/mini-colored/advanced-custom-fields.svg', __DIR__ ), // URL or path to your icon, or dashicon name.
			'tag' => 'plugin',
		];
		return $categories;
	}

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
					],
					'options' => class_exists( 'ACF' ) ? $this->get_acf_options() : [],
				],
				[ 
					'key' => 'operator',
					'type' => 'select',
					'attributes' => [ 
						'label' => __( 'Operator', 'conditional-blocks' ),
						'help' => __( 'Select a operator used to check the value', 'conditional-blocks' ),
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
	
	public function acf_field_value( $should_block_render, $condition ) {

		if ( ! function_exists( 'get_field_object' ) || ! function_exists( 'get_field' ) ) {
			return $should_block_render;
		}

		$has_match = false;

		$acf_field_id = ! empty( $condition['acf_field']['value'] ) ? $condition['acf_field']['value'] : '';

		if ( empty( $acf_field_id ) ) {
			return $should_block_render;
		}
		/**
		 * The ACF Field Value can contain multiple values, and nested array if using the "return array" format. 
		 * 
		 * Use 	$field_object = get_field_object( $acf_field_id );
		 * To get the entire ACF object. We can test specific field types in the future.
		 * 
		 * @link https://www.advancedcustomfields.com/resources/get_field_object
		 */

		$acf_value = get_field( $acf_field_id );

		$field_value = cb_maybe_flatten_meta( $acf_value, 'value' ); // ACF can return arrays with key/value.

		$operator = ! empty( $condition['operator']['value'] ) ? $condition['operator']['value'] : 'not_empty';

		switch ( $operator ) {
			case 'not_empty':
				$has_match = ! empty( $field_value );
				break;
			case 'empty':
				$has_match = empty( $field_value );
				break;
			case 'equal':
				$has_match = $field_value === $condition['expected_value'];
				break;
			case 'not_equal':
				$has_match = $field_value !== $condition['expected_value'];
				break;
			case 'contains':
				$has_match = strpos( $field_value, $condition['expected_value'] ) !== false;
				break;
			case 'not_contains':
				$has_match = strpos( $field_value, $condition['expected_value'] ) === false;
				break;
			case 'greater_than':
				$has_match = (int) $field_value > (int) $condition['expected_value'];
				break;
			case 'less_than':
				$has_match = (int) $field_value < (int) $condition['expected_value'];
				break;
			case 'greater_than_or_equal_to':
				$has_match = (int) $field_value >= (int) $condition['expected_value'];
				break;
			case 'less_than_or_equal_to':
				$has_match = (int) $field_value <= (int) $condition['expected_value'];
				break;
		}
		return $has_match;
	}

	
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
				// Get all the fields within the field group
				$fields = acf_get_fields( $group['key'] );

				if ( ! $fields ) {
					continue;
				}

				$group_options = [];

				// Loop through each field and add it to the group options
				foreach ( $fields as $field ) {
					$group_options[] = [ 
						'label' => $field['label'],
						'value' => $field['name']
					];
				}

				$options[] = [ 
					'label' => $group['title'],
					'options' => $group_options
				];
			}
		}

		return $options;
	}
}

// Initialize the class to set up the hooks.
new CB_AFC_Integration();
