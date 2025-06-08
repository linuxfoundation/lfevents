<?php

class CB_FluentCRM_Integration {
	private $is_fluentcrm_active = false;
	private $is_pro = false;
	private $tested_version = ' 2.9.50';

	public function __construct() {
		$this->is_fluentcrm_active = defined( 'FLUENTCRM' );

				$this->is_pro = true;
		
		add_filter( 'conditional_blocks_register_condition_categories', [ $this, 'register_categories' ], 10, 1 );
		add_filter( 'conditional_blocks_register_condition_types', [ $this, 'register_conditions' ], 10, 1 );
				add_filter( 'conditional_blocks_register_check_fluentcrm_user_tag', [ $this, 'check_fluentcrm_user_tag' ], 10, 2 );
		add_filter( 'conditional_blocks_register_check_fluentcrm_user_list', [ $this, 'check_fluentcrm_user_list' ], 10, 2 );
			}

	/**
	 * Register condition categories for the Fluent CRM integration.
	 *
	 * @param array $categories The current list of categories.
	 * @return array The updated list of categories.
	 */
	public function register_categories( $categories ) {
		$categories[] = [ 
			'value' => 'fluentcrm',
			'label' => __( 'Fluent CRM', 'conditional-blocks' ),
			'icon' => plugins_url( 'assets/images/mini-colored/fluentcrm.svg', __DIR__ ),
			'tag' => 'plugin',
		];
		return $categories;
	}

	public function register_conditions( $conditions ) {

		$conditions[] = [ 
			'type' => 'fluentcrm_user_tag',
			'label' => __( 'User Tag', 'conditional-blocks' ),
			'is_pro' => true,
			'tag' => 'plugin',
			'is_disabled' => ! $this->is_fluentcrm_active || ! $this->is_pro,
			'description' => __( 'Check if the current user has specific Fluent CRM tags.', 'conditional-blocks' ),
			'category' => 'fluentcrm',
			'fields' => [ 
				[ 
					'key' => 'tag_relation',
					'type' => 'select',
					'attributes' => [ 
						'label' => __( 'User Relation to Tag', 'conditional-blocks' ),
						'help' => __( 'Check if the current user has or does not have specific Fluent CRM tags, or any tag at all.', 'conditional-blocks' ),
						'searchable' => false,
					],
					'options' => [ 
						[ 'label' => __( 'User has tag(s)', 'conditional-blocks' ), 'value' => 'has_tag' ],
						[ 'label' => __( 'User does not have tag(s)', 'conditional-blocks' ), 'value' => 'does_not_have_tag' ],
					],
				],
				[ 
					'key' => 'fluentcrm_tag',
					'type' => 'select',
					'attributes' => [ 
						'label' => __( 'Fluent CRM Tag', 'conditional-blocks' ),
						'help' => __( 'Select the specific tag for the condition, or select \'(Any Tag)\'.', 'conditional-blocks' ),
						'placeholder' => __( 'Select a tag', 'conditional-blocks' ),
						'searchable' => true,
					],
					'options' => ( $this->is_fluentcrm_active && method_exists( $this, 'get_fluentcrm_tag_options' ) ) ? $this->get_fluentcrm_tag_options() : [],
				],
				[ 
					'key' => 'blockAction',
					'type' => 'blockAction',
				],
			],
		];

		$conditions[] = [ 
			'type' => 'fluentcrm_user_list',
			'label' => __( 'User List', 'conditional-blocks' ),
			'is_pro' => true,
			'tag' => 'plugin',
			'is_disabled' => ! $this->is_fluentcrm_active || ! $this->is_pro,
			'description' => __( 'Check if the current user is on specific Fluent CRM lists.', 'conditional-blocks' ),
			'category' => 'fluentcrm',
			'fields' => [ 
				[ 
					'key' => 'list_relation',
					'type' => 'select',
					'attributes' => [ 
						'label' => __( 'User Relation to List', 'conditional-blocks' ),
						'help' => __( 'Check if the current user is or is not on specific Fluent CRM lists, or any list at all.', 'conditional-blocks' ),
						'searchable' => false,
					],
					'options' => [ 
						[ 'label' => __( 'User is on list(s)', 'conditional-blocks' ), 'value' => 'is_on_list' ],
						[ 'label' => __( 'User is not on list(s)', 'conditional-blocks' ), 'value' => 'is_not_on_list' ],
					],
				],
				[ 
					'key' => 'fluentcrm_list',
					'type' => 'select',
					'attributes' => [ 
						'label' => __( 'Fluent CRM Contact List', 'conditional-blocks' ),
						'help' => __( 'Select the specific list for the condition, or select \'(Any List)\'.', 'conditional-blocks' ),
						'placeholder' => __( 'Select a list', 'conditional-blocks' ),
						'searchable' => true,
					],
					'options' => ( $this->is_fluentcrm_active && method_exists( $this, 'get_fluentcrm_list_options' ) ) ? $this->get_fluentcrm_list_options() : [],
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
	 * Get Fluent CRM tag options for the select field.
	 * 
	 * @return array The list of Fluent CRM tag options.
	 */
	public function get_fluentcrm_tag_options() {
		$options = [ 
			[ 'label' => __( '(Any Tag)', 'conditional-blocks' ), 'value' => '__ANY__' ],
		];

		if ( ! $this->is_fluentcrm_active || ! function_exists( 'FluentCrmApi' ) ) {
			return $options;
		}

		try {
			$tagsApi = FluentCrmApi( 'tags' );
			$tags = $tagsApi->all(); // Fetch all tags

			if ( ! empty( $tags ) ) {
				foreach ( $tags as $tag ) {
					$options[] = [ 
						'label' => $tag->title,
						'value' => (string) $tag->id // Ensure value is string
					];
				}
			}
		} catch (\Exception $e) {
			error_log( 'Conditional Blocks - Fluent CRM Error fetching tags: ' . $e->getMessage() );
		}

		return $options;
	}

	/**
	 * Get Fluent CRM list options for the select field.
	 * 
	 * @return array The list of Fluent CRM list options.
	 */
	public function get_fluentcrm_list_options() {
		$options = [ 
			[ 'label' => __( '(Any List)', 'conditional-blocks' ), 'value' => '__ANY__' ],
		];

		if ( ! $this->is_fluentcrm_active || ! function_exists( 'FluentCrmApi' ) ) {
			return $options;
		}

		try {
			$listsApi = FluentCrmApi( 'lists' );
			$lists = $listsApi->all(); // Fetch all lists

			if ( ! empty( $lists ) ) {
				foreach ( $lists as $list ) {
					$options[] = [ 
						'label' => $list->title,
						'value' => (string) $list->id // Ensure value is string
					];
				}
			}
		} catch (\Exception $e) {
			// Handle potential errors if the API call fails
			error_log( 'Conditional Blocks - Fluent CRM Error fetching lists: ' . $e->getMessage() );
		}

		return $options;
	}

	/**
	 * Check Fluent CRM user tag condition
	 *
	 * @param bool $should_block_render Whether the block should render based on previous checks.
	 * @param array $condition The condition settings.
	 * @return bool Updated value for whether the block should render.
	 */
	public function check_fluentcrm_user_tag( $should_block_render, $condition ) {

		if ( ! $this->is_fluentcrm_active || ! function_exists( 'FluentCrmApi' ) ) {
			return $should_block_render; // FluentCRM not active, pass through previous result
		}

		$user_id = get_current_user_id();
		$contact = null;
		$user_tags = []; // Default to empty array

		if ( $user_id ) {
			$contactApi = FluentCrmApi( 'contacts' );
			$contact = $contactApi->getContactByUserRef( $user_id );

			if ( $contact && isset( $contact->tags ) ) {
				// FluentCRM stores tags as a relationship object, get the IDs
				$user_tags_objects = $contact->tags;
				foreach ( $user_tags_objects as $tag_object ) {
					$user_tags[] = (string) $tag_object->id; // Ensure comparison is done with strings
				}
			}
		}

		$has_match = false;
		$tag_relation = ! empty( $condition['tag_relation']['value'] ) ? $condition['tag_relation']['value'] : 'has_tag';
		$tag_id = ! empty( $condition['fluentcrm_tag']['value'] ) ? $condition['fluentcrm_tag']['value'] : '__ANY__'; // Default to __ANY__ if not set
		$block_action = ! empty( $condition['blockAction'] ) ? $condition['blockAction'] : 'showBlock';

		$user_has_any_tags = ! empty( $user_tags );

		if ( $tag_relation === 'does_not_have_tag' ) {
			if ( $tag_id === '__ANY__' ) {
				// Check if user has zero tags.
				$has_match = ! $user_has_any_tags;
			} else {
				// Check if user *does not* have the specific tag.
				// The FluentCRM $contact->hasAnyTagId() method might not work correctly if $contact is null,
				// so we use in_array on our derived $user_tags array.
				$has_match = ! in_array( $tag_id, $user_tags );
			}
		} elseif ( $tag_relation === 'has_tag' ) {
			if ( $tag_id === '__ANY__' ) {
				// Check if user has *at least one* tag.
				$has_match = $user_has_any_tags;
			} else {
				// Check if user *has* the specific tag.
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

	/**
	 * Check Fluent CRM user list condition
	 *
	 * @param bool $should_block_render Whether the block should render based on previous checks.
	 * @param array $condition The condition settings.
	 * @return bool Updated value for whether the block should render.
	 */
	public function check_fluentcrm_user_list( $should_block_render, $condition ) {
		// Prerequisite checks
		if ( ! $this->is_fluentcrm_active || ! function_exists( 'FluentCrmApi' ) ) {
			return $should_block_render; // FluentCRM not active, pass through previous result
		}

		$user_id = get_current_user_id();
		$contact = null;
		$user_lists = []; // Default to empty array

		if ( $user_id ) {
			$contactApi = FluentCrmApi( 'contacts' );
			$contact = $contactApi->getContactByUserRef( $user_id );

			if ( $contact && isset( $contact->lists ) ) {
				// FluentCRM stores lists as a relationship object, get the IDs
				$user_lists_objects = $contact->lists;
				foreach ( $user_lists_objects as $list_object ) {
					$user_lists[] = (string) $list_object->id; // Ensure comparison is done with strings
				}
			}
		}

		$has_match = false;
		$list_relation = ! empty( $condition['list_relation']['value'] ) ? $condition['list_relation']['value'] : 'is_on_list';
		$list_id = ! empty( $condition['fluentcrm_list']['value'] ) ? $condition['fluentcrm_list']['value'] : '__ANY__';
		$block_action = ! empty( $condition['blockAction'] ) ? $condition['blockAction'] : 'showBlock';

		$user_is_on_any_lists = ! empty( $user_lists );

		if ( $list_relation === 'is_not_on_list' ) {
			if ( $list_id === '__ANY__' ) {
				// Check if user is on *zero* lists.
				$has_match = ! $user_is_on_any_lists;
			} else {
				// Check if user is not on the specific list.
				// Use in_array for consistency and safety if $contact is null.
				$has_match = ! in_array( $list_id, $user_lists );
			}
		} elseif ( $list_relation === 'is_on_list' ) {
			if ( $list_id === '__ANY__' ) {
				// Check if user is on *at least one* list.
				$has_match = $user_is_on_any_lists;
			} else {
				// Check if user is on the specific list.
				$has_match = in_array( $list_id, $user_lists );
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


new CB_FluentCRM_Integration();