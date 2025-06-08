<?php
class CB_Meta_Box_Integration {
	private $is_meta_box_active = false;
	private $is_pro = false;
	private $tested_version = '5.9.5';

	public function __construct() {
		$this->is_meta_box_active = function_exists( 'rwmb_meta' );

				$this->is_pro = true;
		
		add_filter( 'conditional_blocks_register_condition_categories', [ $this, 'register_categories' ], 10, 1 );
		add_filter( 'conditional_blocks_register_condition_types', [ $this, 'register_conditions' ], 10, 1 );
				add_filter( 'conditional_blocks_register_check_meta_box_field_value', [ $this, 'meta_box_field_value' ], 10, 2 );
			}

	public function register_categories( $categories ) {
		$categories[] = [ 
			'value' => 'meta_box',
			'label' => __( 'Meta Box', 'conditional-blocks' ),
			'icon' => plugins_url( 'assets/images/mini-colored/meta-box.svg', __DIR__ ),
			'tag' => 'plugin',
		];
		return $categories;
	}

	public function register_conditions( $conditions ) {

		$conditions[] = [ 
			'type' => 'meta_box_field_value',
			'label' => __( 'Meta Box Field Value', 'conditional-blocks' ),
			'is_pro' => true,
			'tag' => 'plugin',
			'is_disabled' => ! $this->is_meta_box_active || ! $this->is_pro,
			'description' => '',
			'category' => 'meta_box',
			'fields' => [ 
				[ 
					'key' => 'mb_field',
					'type' => 'select',
					'attributes' => [ 
						'label' => __( 'Meta Box Field', 'conditional-blocks' ),
						'help' => __( 'Select a Field', 'conditional-blocks' ),
						'placeholder' => __( 'Select a field', 'conditional-blocks' ),
					],
					'options' => $this->is_meta_box_active ? $this->structure_field_groups() : [],
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
	
	public function meta_box_field_value( $should_block_render, $condition ) {

		if ( ! function_exists( 'rwmb_meta' ) ) {
			return $should_block_render;
		}

		$field_id = ! empty( $condition['mb_field']['value'] ) ? $condition['mb_field']['value'] : '';

		if ( empty( $field_id ) ) {
			return $should_block_render;
		}

		$field_value = rwmb_meta( $field_id );
		$field_value = cb_maybe_flatten_meta( $field_value );

		$has_match = false;

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
	
	public function structure_field_groups() {
		$options = [];

		$field_groups = apply_filters( 'rwmb_meta_boxes', [] );

		if ( $field_groups ) {
			foreach ( $field_groups as $group ) {
				if ( empty( $group['title'] ) || empty( $group['fields'] ) ) {
					continue;
				}

				$group_options = [];

				foreach ( $group['fields'] as $field ) {
					if ( empty( $field['name'] ) || empty( $field['id'] ) ) {
						continue;
					}

					$group_options[] = [ 
						'label' => $field['name'],
						'value' => $field['id']
					];
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
}

// Initialize the class to set up the hooks.
new CB_Meta_Box_Integration();
