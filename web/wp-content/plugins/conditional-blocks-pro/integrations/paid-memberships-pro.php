<?php
class CB_Paid_Memberships_Pro_Integration {
	private $is_pmpro_active = false;
	private $is_pro = false;
	private $tested_version = '3.0.1';

	public function __construct() {
		$this->is_pmpro_active = defined( 'PMPRO_VERSION' );
				$this->is_pro = true;
		
		add_filter( 'conditional_blocks_register_condition_categories', [ $this, 'register_categories' ], 10, 1 );
		add_filter( 'conditional_blocks_register_condition_types', [ $this, 'register_conditions' ], 10, 1 );
				add_filter( 'conditional_blocks_register_check_pmpro_user_field', [ $this, 'check_pmpro_user_field' ], 10, 2 );
		add_filter( 'conditional_blocks_register_check_pmpro_membership_level', [ $this, 'check_pmpro_membership_level' ], 10, 2 );
			}

	public function register_categories( $categories ) {
		$categories[] = [ 
			'value' => 'paid_memberships_pro',
			'label' => __( 'Paid Memberships Pro (PMPro)', 'conditional-blocks' ),
			'icon' => plugins_url( 'assets/images/mini-colored/paid-memberships-pro.svg', __DIR__ ),
			'tag' => 'plugin',
		];
		return $categories;
	}

	public function register_conditions( $conditions ) {

		$conditions[] = [ 
			'type' => 'pmpro_membership_level',
			'label' => __( 'Membership Level', 'conditional-blocks' ),
			'is_pro' => true,
			'tag' => 'plugin',
			'is_disabled' => ! $this->is_pmpro_active || ! $this->is_pro,
			'description' => __( 'Check if the current user hold a specific membership level.', 'conditional-blocks' ),
			'category' => 'paid_memberships_pro',
			'fields' => [ 
				[ 
					'key' => 'membership_level',
					'type' => 'select',
					'attributes' => [ 
						'label' => __( 'Membership Level', 'conditional-blocks' ),
						'help' => __( 'Select a level', 'conditional-blocks' ),
						'placeholder' => __( 'Select a level', 'conditional-blocks' ),
					],
					'options' => $this->get_membership_options(),
				],
				[ 
					'key' => 'blockAction',
					'type' => 'blockAction',
				],
			],
		];

		$conditions[] = [ 
			'type' => 'pmpro_user_field',
			'label' => __( 'User Field', 'conditional-blocks' ),
			'is_pro' => true,
			'tag' => 'plugin',
			'is_disabled' => ! $this->is_pmpro_active || ! $this->is_pro,
			'description' => __( 'Check a user field value from Paid Memberships Pro.', 'conditional-blocks' ),
			'category' => 'paid_memberships_pro',
			'fields' => [ 
				[ 
					'key' => 'user_field',
					'type' => 'select',
					'attributes' => [ 
						'label' => __( 'User Field', 'conditional-blocks' ),
						'help' => __( 'Select a user field from PMPro', 'conditional-blocks' ),
						'placeholder' => __( 'Select a field', 'conditional-blocks' ),
						'searchable' => true
					],
					'options' => $this->get_pmpro_fields_for_options(),
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
					],
				],
			],
		];

		return $conditions;
	}
	
	/**
	 * Condition check for pmpro_membership_level.
	 * 
	 * pmpro_hasMembershipLevel($levels, $user_id) is a function from Paid Memberships Pro.
	 * 
	 * $levels is an string|array of level ids to check against, 'E' is expired. Leave blank for any level.
	 * $user_id defaults to current user.
	 * 
	 * @param mixed $should_block_render
	 * @param mixed $condition
	 * @return mixed
	 */
	public function check_pmpro_membership_level( $should_block_render, $condition ) {

		if ( ! function_exists( 'pmpro_hasMembershipLevel' ) ) {
			return $should_block_render;
		}

		$has_match = false;

		$membership_level = ! empty( $condition['membership_level']['value'] ) ? $condition['membership_level']['value'] : '';

		if ( empty( $membership_level ) ) {
			return $should_block_render;
		}

		switch ( $membership_level ) {
			case 'no-membership':
				$has_match = ! pmpro_hasMembershipLevel();
				break;
			case 'any-membership':
				$has_match = pmpro_hasMembershipLevel();
				break;
			case 'expired-membership':
				$has_match = pmpro_hasMembershipLevel( 'E' ); // E is expired from Paid Memberships Pro docs.
				break;
			default:
				$has_match = pmpro_hasMembershipLevel( $membership_level ); // Check for specific level id - string or int.
				break;
		}

		$should_block_render = cb_check_block_action( $has_match ? true : false, $condition );

		return $should_block_render;
	}

	/**
	 * Condition check for pmpro_user_field.
	 * 
	 * @param mixed $should_block_render
	 * @param mixed $condition
	 * @return mixed
	 */
	public function check_pmpro_user_field( $should_block_render, $condition ) {

		$has_match = false;

		$pmpro_field_key = ! empty( $condition['user_field']['value'] ) ? $condition['user_field']['value'] : '';

		if ( empty( $pmpro_field_key ) ) {
			return $should_block_render;
		}

		// Get current WP User.
		$user_id = get_current_user_id();

		$user_meta_value = get_user_meta( $user_id, $pmpro_field_key, true );
		$user_meta_value = cb_maybe_flatten_meta( $user_meta_value );

		$operator = ! empty( $condition['operator']['value'] ) ? $condition['operator']['value'] : 'not_empty';

		switch ( $operator ) {
			case 'not_empty':
				$has_match = ! empty( $user_meta_value );
				break;
			case 'empty':
				$has_match = empty( $user_meta_value );
				break;
			case 'equal':
				$has_match = $user_meta_value === $condition['expected_value'];
				break;
			case 'not_equal':
				$has_match = $user_meta_value !== $condition['expected_value'];
				break;
			case 'contains':
				$has_match = strpos( $user_meta_value, $condition['expected_value'] ) !== false;
				break;
			case 'not_contains':
				$has_match = strpos( $user_meta_value, $condition['expected_value'] ) === false;
				break;
			case 'greater_than':
				$has_match = (int) $user_meta_value > (int) $condition['expected_value'];
				break;
			case 'less_than':
				$has_match = (int) $user_meta_value < (int) $condition['expected_value'];
				break;
			case 'greater_than_or_equal_to':
				$has_match = (int) $user_meta_value >= (int) $condition['expected_value'];
				break;
			case 'less_than_or_equal_to':
				$has_match = (int) $user_meta_value <= (int) $condition['expected_value'];
				break;
		}

		$should_block_render = $has_match;

		return $should_block_render;
	}
	
	/**
	 * Helper function to get all user fields for the select field.
	 * @return array
	 */
	function get_pmpro_fields_for_options() {
		global $pmpro_user_fields, $pmpro_field_groups;

		$option_groups = [];

		// Check if any field group exists
		if ( $pmpro_field_groups && $pmpro_user_fields ) {
			foreach ( $pmpro_field_groups as $group ) {

				$options = [];

				// Check if any field exists in the group.
				if ( ! empty( $pmpro_user_fields[ $group->name ] ) ) {
					// Loop through each field in the group.
					foreach ( $pmpro_user_fields[ $group->name ] as $field ) {
						$options[] = [ 
							'label' => $field->label,
							'value' => $field->meta_key,
						];
					}
				}

				$option_groups[] = [ 
					'label' => $group->name,
					'options' => $options,
				];
			}
		}


		return $option_groups;
	}

	/**
	 * Helper function to get all membership levels for the select field.
	 * @return array
	 */
	public function get_membership_options() {

		if ( ! function_exists( 'pmpro_getAllLevels' ) ) {
			return [];
		}

		// Get all level, each level is stored as the key.
		$pmpro_levels = pmpro_getAllLevels();

		$option_groups[] = [ 
			'label' => __( 'General', 'conditional-blocks' ),
			'options' => [ 
				[ 
					'label' => __( 'No Membership', 'conditional-blocks' ),
					'value' => 'no-membership'
				],
				[ 
					'label' => __( 'Any Membership', 'conditional-blocks' ),
					'value' => 'any-membership'
				],
				[ 
					'label' => __( 'Expired Membership', 'conditional-blocks' ),
					'value' => 'expired-membership'
				]
			]
		];


		$level_options = [];

		// Check if any field group exists
		if ( $pmpro_levels ) {
			foreach ( $pmpro_levels as $level ) {
				$level_options[] = [ 
					'label' => $level->name,
					'value' => $level->id
				];
			}
		}

		$option_groups[] = [ 
			'label' => __( 'Membership Levels', 'conditional-blocks' ),
			'options' => $level_options,
		];

		return $option_groups;
	}
}

// Initialize the class to set up the hooks.
new CB_Paid_Memberships_Pro_Integration();
