<?php
class CB_EDD_Integration {
	private $is_edd_active = false;
	private $is_pro = false;
	private $tested_version = '3.2.11';

	public function __construct() {
		$this->is_edd_active = function_exists( 'EDD' );
				$this->is_pro = true;
		
		add_filter( 'conditional_blocks_register_condition_categories', [ $this, 'register_categories' ], 10, 1 );
		add_filter( 'conditional_blocks_register_condition_types', [ $this, 'register_conditions' ], 10, 1 );

				add_filter( 'conditional_blocks_register_check_edd_cart_value', [ $this, 'check_cart_value' ], 10, 2 );
		add_filter( 'conditional_blocks_register_check_edd_items_in_cart', [ $this, 'items_in_cart' ], 10, 2 );
		add_filter( 'conditional_blocks_register_check_edd_product_category_in_cart', [ $this, 'product_category_in_cart' ], 10, 2 );
		add_filter( 'conditional_blocks_register_check_edd_user_recurring_subscription', [ $this, 'user_recurring_subscription' ], 10, 2 );
		add_filter( 'conditional_blocks_register_check_edd_customer_purchase', [ $this, 'customer_purchase' ], 10, 2 );
			}

	public function register_categories( $categories ) {
		$categories[] = [ 
			'value' => 'easy_digital_downloads',
			'label' => 'Easy Digital Downloads',
			'icon' => plugins_url( 'assets/images/mini-colored/easy-digital-downloads.svg', __DIR__ ), // URL or path to your icon, or dashicon name.
			'tag' => 'plugin',
		];
		return $categories;
	}
	public function register_conditions( $conditions ) {

		$conditions[] = [ 
			'type' => 'edd_cart_value',
			'label' => __( 'Cart Value', 'conditional-blocks' ),
			'is_pro' => true,
			'tag' => 'plugin',
			'is_disabled' => ! $this->is_edd_active || ! $this->is_pro,
			'description' => __( 'Trigger the Block Action depending on the current customer cart value in Easy Digital Downloads.', 'conditional-blocks' ),
			'category' => 'easy_digital_downloads',
			'fields' => [ 
				[ 
					'key' => 'more_than',
					'type' => 'number',
					'attributes' => [ 
						'label' => __( 'More than', 'conditional-blocks' ),
						'value' => false,
						'placeholder' => __( 'Any Value', 'conditional-blocks' ),
						'help' => __( 'Leave blank for any value.', 'conditional-blocks' ),
					],
				],
				[ 
					'key' => 'less_than',
					'type' => 'number',
					'attributes' => [ 
						'label' => __( 'Less than', 'conditional-blocks' ),
						'value' => false,
						'placeholder' => __( 'Any Value', 'conditional-blocks' ),
						'help' => __( 'Leave blank for any value.', 'conditional-blocks' ),
					],
				],
				[ 
					'key' => 'blockAction',
					'type' => 'blockAction',
				],
			],
		];

		$conditions[] = [ 
			'type' => 'edd_items_in_cart',
			'label' => __( 'Product in Cart', 'conditional-blocks' ),
			'is_pro' => true,
			'tag' => 'plugin',
			'is_disabled' => ! $this->is_edd_active || ! $this->is_pro,
			'description' => __( 'Check if selected product is found in the customers cart.', 'conditional-blocks' ),
			'category' => 'easy_digital_downloads',
			'fields' => [ 
				[ 
					'key' => 'products',
					'type' => 'EDDProductSelect',
					'attributes' => [ 
						'label' => __( 'Products', 'conditional-blocks' ),
						'help' => __( 'Select a product to check for in the cart.', 'conditional-blocks' ),
						'placeholder' => __( 'Select Product', 'conditional-blocks' ),
						'multiple' => true,
					],
				],
				[ 
					'key' => 'blockAction',
					'type' => 'blockAction',
				],
			],
		];

		// EDD Product Categories in Cart.
		$conditions[] = [ 
			'type' => 'edd_product_category_in_cart',
			'label' => __( 'Product Category in Cart', 'conditional-blocks' ),
			'is_pro' => true,
			'tag' => 'plugin',
			'is_disabled' => ! $this->is_edd_active || ! $this->is_pro,
			'description' => __( 'Check if selected product category are found in the customers cart.', 'conditional-blocks' ),
			'category' => 'easy_digital_downloads',
			'fields' => [ 
				[ 
					'key' => 'categories',
					'type' => 'EDDProductCategorySelect',
					'attributes' => [ 
						'label' => __( 'Product Category', 'conditional-blocks' ),
						'help' => __( 'Select a product category to check for in the cart.', 'conditional-blocks' ),
						'placeholder' => __( 'Select Product Category', 'conditional-blocks' ),
					],
				],
				[ 
					'key' => 'blockAction',
					'type' => 'blockAction',
				],
			],
		];

		$conditions[] = [ 
			'type' => 'edd_user_recurring_subscription',
			'label' => __( 'Recurring Subscription', 'conditional-blocks' ),
			'is_pro' => true,
			'tag' => 'plugin',
			'is_disabled' => ! $this->is_edd_active || ! $this->is_pro || ! class_exists( 'EDD_Recurring_Subscriber' ),
			'description' => __( 'Check if the current user has a recurring subscription, optionally check for a selected product or status. This condition requires the EDD Recurring Payments extension.', 'conditional-blocks' ),
			'category' => 'easy_digital_downloads',
			'fields' => [ 
				[ 
					'key' => 'product',
					'type' => 'EDDProductSelect',
					'attributes' => [ 
						'label' => __( 'Product', 'conditional-blocks' ),
						'help' => __( 'Select product to check for a specific subscription, or leave blank for any.', 'conditional-blocks' ),
						'placeholder' => __( 'Select Product Subscription (Leave blank for any)', 'conditional-blocks' ),
					],
				],
				[ 
					'key' => 'statues',
					'type' => 'select',
					'attributes' => [ 
						'label' => __( 'Subscription Status', 'conditional-blocks' ),
						'help' => __( 'Select one or multiple statuses. Leave blank for any.', 'conditional-blocks' ), //  active, pending, cancelled, expired, trialling, failing, completed.
						'placeholder' => __( 'Select Subscription Status', 'conditional-blocks' ),
						'multiple' => true,
					],
					'options' => [ 
						[ 
							'value' => 'active',
							'label' => __( 'Active', 'conditional-blocks' ),
						],
						[ 
							'value' => 'pending',
							'label' => __( 'Pending', 'conditional-blocks' ),
						],
						[ 
							'value' => 'cancelled',
							'label' => __( 'Cancelled', 'conditional-blocks' ),
						],
						[ 
							'value' => 'expired',
							'label' => __( 'Expired', 'conditional-blocks' ),
						],
						[ 
							'value' => 'trialling',
							'label' => __( 'Trialling', 'conditional-blocks' ),
						],
						[ 
							'value' => 'failing',
							'label' => __( 'Failing', 'conditional-blocks' ),
						],
						[ 
							'value' => 'completed',
							'label' => __( 'Completed', 'conditional-blocks' ),
						],
					],
				],
				[ 
					'key' => 'blockAction',
					'type' => 'blockAction',
				],
			],
		];

		// EDD Customer has purchased specific product.
		$conditions[] = [ 
			'type' => 'edd_customer_purchase',
			'label' => __( 'Product Purchased', 'conditional-blocks' ),
			'is_pro' => true,
			'tag' => 'plugin',
			'is_disabled' => ! $this->is_edd_active || ! $this->is_pro,
			'description' => __( 'Check if the current user has purchased a specific product, or any product', 'conditional-blocks' ),
			'category' => 'easy_digital_downloads',
			'fields' => [ 
				[ 
					'key' => 'product',
					'type' => 'EDDProductSelect',
					'attributes' => [ 
						'label' => __( 'Product', 'conditional-blocks' ),
						'help' => __( 'Select a product from Easy Digital Downloads', 'conditional-blocks' ),
						'placeholder' => __( 'Select Product (Leave blank for any)', 'conditional-blocks' ),
					],
				],
				[ 
					'key' => 'blockAction',
					'type' => 'blockAction',
				],
			],
		];

		return $conditions;
	}

		public function check_cart_value( $should_block_render, $condition ) {

		$has_match = false;

		if ( ! $this->is_edd_active || ! function_exists( 'edd_get_cart_total' ) ) {
			return $should_block_render;
		}

		$total = (int) edd_get_cart_total();

		$more_than_value = ! empty( $condition['more_than'] ) ? (int) $condition['more_than'] : 0;
		$less_than_value = ! empty( $condition['less_than'] ) ? (int) $condition['less_than'] : 0;

		$is_more_than_required = empty( $more_than_value ) ? true : $total > $more_than_value;
		$is_less_than_required = empty( $less_than_value ) ? true : $total < $less_than_value;

		if ( $is_more_than_required && $is_less_than_required ) {
			$has_match = true;
		}

		$block_action = ! empty( $condition['blockAction'] ) ? $condition['blockAction'] : 'showBlock';

		if ( $has_match && $block_action === 'showBlock' ) {
			$should_block_render = true;
		} elseif ( ! $has_match && $block_action === 'hideBlock' ) {
			$should_block_render = true;
		}
		return $should_block_render;
	}

	public function items_in_cart( $should_block_render, $condition ) {

		$has_match = false;

		if ( ! $this->is_edd_active || ! function_exists( 'edd_item_in_cart' ) ) {
			return $should_block_render;
		}

		$selected_products = ! empty( $condition['products'] ) ? $condition['products'] : [];

		foreach ( $selected_products as $product_id ) {
			if ( edd_item_in_cart( $product_id ) ) {
				$has_match = true;
				break;
			}
		}

		$block_action = ! empty( $condition['blockAction'] ) ? $condition['blockAction'] : 'showBlock';

		if ( $has_match && $block_action === 'showBlock' ) {
			$should_block_render = true;
		} elseif ( ! $has_match && $block_action === 'hideBlock' ) {
			$should_block_render = true;
		}
		return $should_block_render;
	}

	public function product_category_in_cart( $should_block_render, $condition ) {

		$has_match = false;

		if ( ! $this->is_edd_active || ! function_exists( 'edd_get_cart_contents' ) ) {
			return $should_block_render;
		}

		// Get the current cart contents.
		$cart_items = edd_get_cart_contents();

		$selected_categories = ! empty( $condition['categories'] ) ? $condition['categories'] : [];

		// If the cart is empty, return false.
		if ( $cart_items ) {
			// Loop through each item in the cart.
			foreach ( $cart_items as $item ) {
				// Get the ID of the product (download).
				$product_id = $item['id'];

				// Check if the product has the specified category.
				if ( has_term( $selected_categories, 'download_category', $product_id ) ) {
					// If a product in the cart has the category, return true.
					$has_match = true;
				}
			}
		}

		$block_action = ! empty( $condition['blockAction'] ) ? $condition['blockAction'] : 'showBlock';

		if ( $has_match && $block_action === 'showBlock' ) {
			$should_block_render = true;
		} elseif ( ! $has_match && $block_action === 'hideBlock' ) {
			$should_block_render = true;
		}
		return $should_block_render;
	}

	/**
	 * edd_user_recurring_subscription.
	 *  https://easydigitaldownloads.com/docs/recurring-payments-developer-edd_recurring_subscriber/
	 * @param mixed $should_block_render
	 * @param mixed $condition
	 * @return bool
	 */
	public function user_recurring_subscription( $should_block_render, $condition ) {
		// Check if the current user has a valid active subscription.
		$has_match = false;

		if ( ! $this->is_edd_active || ! class_exists( 'EDD_Recurring_Subscriber' ) ) {
			return $should_block_render;
		}

		$user_id = get_current_user_id();

		if ( ! $user_id ) {
			return $should_block_render;
		}

		$subscriber = new EDD_Recurring_Subscriber( $user_id, true ); // Get the subscriber by user ID.

		$selected_product = ! empty( $condition['product']['value'] ) ? (int) $condition['product']['value'] : false;
		$selected_statuses = ! empty( $condition['statues'] ) ? $condition['statues'] : [];

		// foreach status extract the value only, drop the label.
		foreach ( $selected_statuses as $key => $status ) {
			$selected_statuses[] = $status['value'];
		}


		if ( $selected_product ) {
			//  active, pending, cancelled, expired, trialling, failing, completed.
			if ( ! empty( $subscriber->get_subscriptions( $selected_product, $selected_statuses ) ) ) {
				$has_match = true;
			}
		} else {
			// Get subscriptions regardless of product.
			if ( ! empty( $subscriber->get_subscriptions( false, $selected_statuses ) ) ) {
				$has_match = true;
			}
		}

		$block_action = ! empty( $condition['blockAction'] ) ? $condition['blockAction'] : 'showBlock';

		if ( $has_match && $block_action === 'showBlock' ) {
			$should_block_render = true;
		} elseif ( ! $has_match && $block_action === 'hideBlock' ) {
			$should_block_render = true;
		}
		return $should_block_render;
	}


	public function customer_purchase( $should_block_render, $condition ) {

		$has_match = false;

		if ( ! $this->is_edd_active || ! function_exists( 'edd_has_user_purchased' ) || ! class_exists( 'EDD_Customer' ) ) {
			return $should_block_render;
		}

		$user_id = get_current_user_id();

		$customer = new EDD_Customer( $user_id, true );

		if ( ! empty( $customer ) ) {

			$selected_product = ! empty( $condition['product']['value'] ) ? (int) $condition['product']['value'] : false;

			if ( $selected_product ) {
				if ( edd_has_user_purchased( $user_id, $selected_product ) ) {
					$has_match = true;
				}
			} else {
				if ( $customer->purchase_count >= 1 ) {
					$has_match = true;
				}
			}
		}

		$block_action = ! empty( $condition['blockAction'] ) ? $condition['blockAction'] : 'showBlock';

		if ( $has_match && $block_action === 'showBlock' ) {
			$should_block_render = true;
		} elseif ( ! $has_match && $block_action === 'hideBlock' ) {
			$should_block_render = true;
		}
		return $should_block_render;
	}

	}

// Initialize the class to set up the hooks.
new CB_EDD_Integration();
