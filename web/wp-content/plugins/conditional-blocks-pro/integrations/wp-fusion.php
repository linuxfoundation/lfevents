<?php
class CB_WPFusion_Integration {
	private $is_wp_fusion_active = false;
	private $is_pro = false;
	private $tested_version = '3.45.2.2';

	public function __construct() {
		$this->is_wp_fusion_active = function_exists( 'wp_fusion' );
				$this->is_pro = true;
		
		add_filter( 'conditional_blocks_register_condition_categories', [ $this, 'register_categories' ], 10, 1 );
		add_filter( 'conditional_blocks_register_condition_types', [ $this, 'register_conditions' ], 10, 1 );
				add_filter( 'conditional_blocks_register_check_wp_fusion_user_tag', [ $this, 'check_wp_fusion_user_tag' ], 10, 2 );
			}

	/**
	 * Register condition categories for the WP Fusion integration.
	 *
	 * @param array $categories The list of available categories.
	 * @return array The updated list of categories.
	 */
	public function register_categories( $categories ) {
		$categories[] = [ 
			'value' => 'wp_fusion',
			'label' => __( 'WP Fusion', 'conditional-blocks' ),
			'icon' => plugins_url( 'assets/images/mini-colored/wp-fusion.svg', __DIR__ ),
			'tag' => 'plugin',
		];
		return $categories;
	}

	/**
	 * Register condition types for the WP Fusion integration.
	 *
	 * @param array $conditions The list of available condition types.
	 * @return array The updated list of condition types.
	 */
	public function register_conditions( $conditions ) {

		$conditions[] = [ 
			'type' => 'wp_fusion_user_tag',
			'label' => __( 'User Tag', 'conditional-blocks' ),
			'is_pro' => true,
			'tag' => 'plugin',
			'is_disabled' => ! $this->is_wp_fusion_active || ! $this->is_pro,
			'description' => '',
			'category' => 'wp_fusion',
			'fields' => [ 

				[ 
					'key' => 'tag_relation',
					'type' => 'select',
					'attributes' => [ 
						'label' => __( 'User Relation to Tag', 'conditional-blocks' ),
						'help' => __( 'Check if the current user has or does not have specific WP Fusion tags, or any tag at all.', 'conditional-blocks' ),
						'searchable' => false,
					],
					'options' => [ 
						[ 'label' => __( 'User has tag(s)', 'conditional-blocks' ), 'value' => 'has_tag' ],
						[ 'label' => __( 'User does not have tag(s)', 'conditional-blocks' ), 'value' => 'does_not_have_tag' ], // Means user has zero tags
					],
				],
				[ 
					'key' => 'wp_fusion_tag',
					'type' => 'select',
					'attributes' => [ 
						'label' => __( 'WP Fusion Tag', 'conditional-blocks' ),
						'help' => __( 'Select the specific tag for the condition, or select \'(Any Tag)\'.', 'conditional-blocks' ),
						'placeholder' => __( 'Select a tag', 'conditional-blocks' ),
						'searchable' => true,
					],
					'options' => ( $this->is_wp_fusion_active && method_exists( $this, 'get_wp_fusion_tag_options' ) ) ? $this->get_wp_fusion_tag_options() : [],
				],
				[ 
					'key' => 'blockAction',
					'type' => 'blockAction',
				],
			],
		];

		return $conditions;
	}

	
	/**
	 * Get WP Fusion tag options for the select field.
	 *
	 * @return array The list of WP Fusion tag options.
	 */
	public function get_wp_fusion_tag_options() {
		if ( ! $this->is_wp_fusion_active || ! function_exists( 'wp_fusion' ) ) {
			return [];
		}

		$available_tags = wp_fusion()->settings->get( 'available_tags' );
		$real_tag_options = [];

		if ( ! empty( $available_tags ) && is_array( $available_tags ) ) {
			foreach ( $available_tags as $id => $label ) {
				$real_tag_options[] = [ 
					'label' => $label,
					'value' => $id
				];
			}
		}

		// Sort real tags alphabetically by label
		usort( $real_tag_options, function ($a, $b) {
			return strcmp( $a['label'], $b['label'] );
		} );

		// Prepend ONLY the __ANY__ special option
		$options = array_merge(
			[ 
				[ 'label' => __( '(Any Tag)', 'conditional-blocks' ), 'value' => '__ANY__' ],
			],
			$real_tag_options
		);

		return $options;
	}

	/**
	 * Check WP Fusion user tag condition
	 *
	 * @param bool $should_block_render Whether the block should render
	 * @param array $condition The condition to check
	 * @return bool Whether the condition matches
	 */
	public function check_wp_fusion_user_tag( $should_block_render, $condition ) {

		// Only check if WP Fusion itself is active initially. User check happens later.
		if ( ! $this->is_wp_fusion_active || ! function_exists( 'wp_fusion' ) ) {
			return $should_block_render;
		}

		$has_match = false;

		$tag_relation = ! empty( $condition['tag_relation']['value'] ) ? $condition['tag_relation']['value'] : 'has_tag';
		$tag_id = ! empty( $condition['wp_fusion_tag']['value'] ) ? $condition['wp_fusion_tag']['value'] : ''; // May not be set if tag_relation is 'does_not_have_tag'
		$block_action = ! empty( $condition['blockAction'] ) ? $condition['blockAction'] : 'showBlock';


		$user_tags = [];

		if ( isset( wp_fusion()->user ) ) {
			$user_tags = wp_fusion()->user->get_tags();
		}

		if ( $tag_relation === 'does_not_have_tag' ) {
			// Condition: User does not have the specified tag, or any tag if '__ANY__' is selected.
			// If tag_id is empty (shouldn't happen with UI), defaults to checking if user has *any* tag.
			if ( empty( $tag_id ) || $tag_id === '__ANY__' ) {
				// Check if user has *zero* tags.
				$has_match = empty( $user_tags );
			} else {
				// Check if user *does not* have the specific tag.
				$has_match = ! in_array( $tag_id, $user_tags );
			}

		} elseif ( $tag_relation === 'has_tag' ) {
			// Condition: User has the specified tag, or any tag if '__ANY__' is selected.
			// Requires a logged-in user. If $user_tags is empty, this will correctly evaluate to false.
			if ( empty( $tag_id ) || $tag_id === '__ANY__' ) {
				// Selected: (Any Tag) - Check if user has *at least one* tag.
				$has_match = ! empty( $user_tags );
			} else {
				// Selected: Specific Tag - Check if user *has* the specific tag.
				$has_match = in_array( $tag_id, $user_tags );
			}
		}

		if ( ( $has_match && $block_action === 'showBlock' ) || ( ! $has_match && $block_action === 'hideBlock' ) ) {
			$should_block_render = true;
		} else {
			$should_block_render = false;
		}

		return $should_block_render;
	}

	}

// Initialize the class to set up the hooks.
new CB_WPFusion_Integration();