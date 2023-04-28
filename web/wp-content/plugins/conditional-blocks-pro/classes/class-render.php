<?php
/**
 *  Class for handling Block Render
 *
 * @package conditional-blocks-pro
 */
 // phpcs:disable  WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class for handling Block Render
 */
class Conditional_Blocks_Render_Block {

	/**
	 * Set the current block content which can be modifed by CB.
	 *
	 * @var string
	 */
	private $current_block_content = '';
	/**
	 * Set the results of condition sets for debugging.
	 *
	 * @var array
	 */
	private $logged_results = array();

	/**
	 * Fire off the render block functions.
	 */
	public function init() {
		// Hook in to each block before it's rendered.
		add_filter( 'render_block', array( $this, 'render_block' ), 999, 2 );

		// Register each condition check.
		add_filter( 'conditional_blocks_register_check_lockdown', array( $this, 'lockdown' ), 10, 2 );
		add_filter( 'conditional_blocks_register_check_userLoggedIn', array( $this, 'userLoggedIn' ), 10, 2 );
		add_filter( 'conditional_blocks_register_check_userLoggedOut', array( $this, 'userLoggedOut' ), 10, 2 );

		add_filter( 'conditional_blocks_register_check_posts', array( $this, 'posts' ), 10, 2 );
		add_filter( 'conditional_blocks_register_check_postTaxonomyTerms', array( $this, 'postTaxonomyTerms' ), 10, 2 );
		add_filter( 'conditional_blocks_register_check_postType', array( $this, 'postType' ), 10, 2 );
		add_filter( 'conditional_blocks_register_check_archive', array( $this, 'archive' ), 10, 2 );
		add_filter( 'conditional_blocks_register_check_dateRange', array( $this, 'dateRange' ), 10, 2 );
		add_filter( 'conditional_blocks_register_check_dateRecurring', array( $this, 'dateRecurring' ), 10, 2 );
		add_filter( 'conditional_blocks_register_check_userRoles', array( $this, 'userRoles' ), 10, 2 );
		add_filter( 'conditional_blocks_register_check_userMeta', array( $this, 'userMeta' ), 10, 2 );
		add_filter( 'conditional_blocks_register_check_userAgents', array( $this, 'userAgents' ), 10, 2 );
		add_filter( 'conditional_blocks_register_check_domainReferrers', array( $this, 'domainReferrers' ), 10, 2 );
		add_filter( 'conditional_blocks_register_check_queryStrings', array( $this, 'queryStrings' ), 10, 2 );
		add_filter( 'conditional_blocks_register_check_postMeta', array( $this, 'postMeta' ), 10, 2 );
		add_filter( 'conditional_blocks_register_check_cookies', array( $this, 'cookies' ), 10, 2 );
		add_filter( 'conditional_blocks_register_check_urlPaths', array( $this, 'urlPaths' ), 10, 2 );
		add_filter( 'conditional_blocks_register_check_postIds', array( $this, 'postIds' ), 10, 2 );
		add_filter( 'conditional_blocks_register_check_phpLogic', array( $this, 'phpLogic' ), 10, 2 );
		// WooCommerce.
		add_filter( 'conditional_blocks_register_check_wcCartTotal', array( $this, 'wcCartTotal' ), 10, 2 );
		add_filter( 'conditional_blocks_register_check_wcCustomerTotalSpent', array( $this, 'wcCustomerTotalSpent' ), 10, 2 );
		add_filter( 'conditional_blocks_register_check_wcCustomerRecentOrder', array( $this, 'wcCustomerRecentOrder' ), 10, 2 );
		add_filter( 'conditional_blocks_register_check_wcCartProducts', array( $this, 'wcCartProducts' ), 10, 2 );
		add_filter( 'conditional_blocks_register_check_wcCartProductCategories', array( $this, 'wcCartProductCategories' ), 10, 2 );
		add_filter( 'conditional_blocks_register_check_wcGeoLocation', array( $this, 'wcGeoLocation' ), 10, 2 );

		// Presets.
		add_filter( 'conditional_blocks_register_check_presets', array( $this, 'presets' ), 10, 2 );
	}

	/**
	 * Filter block content before displaying.
	 *
	 * @param string $block_content the block content.
	 * @param array  $block the whole Gutenberg block object including attributes.
	 * @return string $block_content the new block content.
	 */
	public function render_block( $block_content, $block ) {

		/**
		 * Prevent loading on admin & REST. Otherwise Gutenberg freaks out.
		 */
		if ( is_admin() || defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return $block_content;
		}

		// Skip empty block.
		if ( empty( $block_content ) ) {
			return $block_content;
		}

		// Reset the current block content and reset logs.
		$this->current_block_content = $block_content;
		$this->logged_results = array();

		$condition_sets = $this->get_condition_sets_from_block( $block );

		if ( empty( $condition_sets ) ) {
			return $block_content;
		}

		$should_render = $this->has_valid_sets( $condition_sets );

		if ( $should_render ) {
			return $this->current_block_content; // Modifed block.
		}

		return ''; // Don't render block.
	}

	/**
	 * Get the condition sets from the block attributes with backwards compat.
	 *
	 * @param object $block object.
	 * @return array $condition_sets sets of conditions.
	 */
	public function get_condition_sets_from_block( $block ) {

		// Reorder to test first if v3, then v2, then v1.
		$v3_sets = ! empty( $block['attrs']['conditionalBlocks']['sets'] ) ? $block['attrs']['conditionalBlocks']['sets'] : false;

		$v2_conditions = ! empty( $block['attrs']['conditionalBlocks']['conditions'] ) ? $block['attrs']['conditionalBlocks']['conditions'] : false;

		$v1_conditions = ! empty( $block['attrs']['conditionalBlocksAttributes'] ) && ! empty( $block['attrs']['conditionalBlocksAttributes'] ) ? $block['attrs']['conditionalBlocksAttributes'] : false;

		// Check for v3 conditions first.
		if ( $v3_sets !== false ) {
			$condition_sets = $v3_sets;
		} elseif ( $v2_conditions !== false ) { // Then check for v2 conditions.
			$condition_sets = $this->convert_v2_to_v3_condition_sets( $v2_conditions );
		} elseif ( $v1_conditions !== false ) { // Finally, check for v1 conditions.
			$condition_sets = $this->convert_v2_to_v3_condition_sets( $this->convert_v1_to_v2_conditions( $v1_conditions ) );
		} else {
			$condition_sets = false;
		}

		return $condition_sets;
	}

	/**
	 * Check if any of the Condition Sets passes all criteria.
	 *
	 * @param array $condition_sets an array of sets containing their own conditions.
	 * @return boolean true if there is atleast one valid set of conditions.
	 */
	public function has_valid_sets( $condition_sets ) {

		$has_valid_set = false;

		foreach ( $condition_sets  as $index => $set ) {

			$conditions = $set['conditions'];

			if ( empty( $conditions ) ) {
				continue;
			}

			$should_render = $this->check_conditions( $conditions );

			if ( $should_render === true ) {
				$has_valid_set = true;
			}
		}

		return $has_valid_set;
	}

	/**
	 * Determine if the current block should be rendered based on applied conditions.
	 *
	 * @param array $conditions all conditions applied to the block.
	 * @return mixed $block_content could be an empty string.
	 */
	public function check_conditions( $conditions ) {

		$results = array(
			'single' => array(), // Default checking of a single condition.
			'stacked' => array(), // Stacked Condition Types with OR logic.
			'aggregated' => array(), // Log the result of each type for debugging later.
			'should_render' => false, // The final decider if the the conditions allow the block to be rendered.
		);

		$results = array();

		foreach ( $conditions as $index => $condition ) {

			$type = ! empty( $condition['type'] ) ? $condition['type'] : false;

			if ( ! $type ) {
				continue;
			}

			// responsiveScreenSizes will modify the existing html. Handle this early.
			if ( $type === 'responsiveScreenSizes' && is_array( $condition['showOn'] ) ) {
				 $this->current_block_content = $this->apply_responsive_screensizes( $this->current_block_content, $condition['showOn'] );

				 // Early for modifed markup.
				 $results['single'][] = true;
				continue;
			}

			/**
			 * Trigger the registered check for the condition type.
			 *
			 * Defaults to false.
			 */
			$should_render = apply_filters( 'conditional_blocks_register_check_' . $type, false, $condition );

			/**
			 * Stacked Types will make specific condition types act as 'OR" logic.
			 *
			 * Depreciated: We'll treat this function as depreciated since we have built-in OR support.
			 * Keeping for now for backward campatability when blocks multiple date related conditions.
			 */
			$stacked_checks = apply_filters( 'conditonal_blocks_register_stacked_types', array( 'dateRange', 'dateRecurring' ) );

			$is_stackable = in_array( $type, $stacked_checks, true );

			if ( $is_stackable ) {
				$results['stacked'][ $type ][] = $should_render;
			} else {
				$results['single'][] = $should_render;
			}

			$results['aggregated'][] = array(
				'type' => $type,
				'should_render' => $should_render,
			);
		}

		$should_render = $this->verify_conditions_are_met( $results );// The outcome for all conditions.
		$results['should_render'] = $should_render;

		// Got it.
		$this->logged_results[] = $results;

		return $should_render;
	}

	/**
	 * Determine if the block should be rendered based on Condition Sets.
	 *
	 * @param array $results results from checking all conditions in a set.
	 * @return bool true or false if results of a set are met.
	 */
	public function verify_conditions_are_met( $results ) {

		// All single checks need to be TRUE otherwise the block content will be hidden.
		if ( ! empty( $results['single'] ) && in_array( false, $results['single'], true ) ) {
			return false;
		}

		// A stacked condition type requires ATLEAST one to be TRUE otherwise the block content will be hidden.
		if ( ! empty( $results['stacked'] ) ) {
			foreach ( $results['stacked'] as $stack_index => $results_array ) {
				if ( ! empty( $results_array ) && ! in_array( true, $results_array, true ) ) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Condition checks.
	 */

	/**
	 * Add device visibility per block.
	 *
	 * @param string $block_content the whole block object.
	 * @param array  $show_on screensizes the block should appear on.
	 * @return string $block_content
	 */
	public function apply_responsive_screensizes( $block_content, $show_on ) {

		$html_classes = '';

		if ( ! in_array( 'showMobileScreen', $show_on, true ) ) {
			$html_classes .= 'conblock-hide-mobile ';
		}

		if ( ! in_array( 'showTabletScreen', $show_on, true ) ) {
			$html_classes .= 'conblock-hide-tablet ';
		}

		if ( ! in_array( 'showDesktopScreen', $show_on, true ) ) {
			$html_classes .= 'conblock-hide-desktop ';
		}

		if ( ! empty( $html_classes ) ) {

			// Replace the first occurance of class=" without classes.
			// We need the classes to be added directly to the blocks. Wrapping classes can sometimes block full width content.
			$needle = 'class="';

			// Find the first occurance.
			$find_class_tag = strpos( $block_content, $needle );

			if ( $find_class_tag !== false ) {
				// Our classes.
				$replacement = 'class="' . $html_classes . ' ';
				// Replace it.
				$new_block = substr_replace( $block_content, $replacement, $find_class_tag, strlen( $needle ) );
			} else {
				// Fallback to wrapping classes when block has no exsisting classes.
				$new_block = '<div class="' . $html_classes . '">' . $block_content . '</div>';
			}

			// Make sure to add frontend CSS to handle the responsive blocks.
			do_action( 'conditional_blocks_enqueue_frontend_responsive_css' );

			return $new_block;
		} else {
			return $block_content;
		}

	}

	/**
	 * Lockdown, this block has been isolated from everyone.
	 *
	 * @param bool  $should_render if condition passed validation.
	 * @param array $condition condition config.
	 * @return bool $should_render.
	 */
	public function lockdown( $should_render, $condition ) {
		return false;
	}

	/**
	 * Check if the user us logged in.
	 *
	 * @param bool  $should_render if condition passed validation.
	 * @param array $condition condition config.
	 * @return bool $should_render.
	 */
	public function userLoggedIn( $should_render, $condition ) {

		$should_render = is_user_logged_in();

		return $should_render;
	}

	/**
	 * Check if the user is logged out.
	 *
	 * @param bool  $should_render if condition passed validation.
	 * @param array $condition condition config.
	 * @return bool $should_render.
	 */
	public function userLoggedOut( $should_render, $condition ) {

		$should_render = ! is_user_logged_in();

		return $should_render;
	}

	/**
	 * Check the current post ID.
	 *
	 * @param bool  $should_render if condition passed validation.
	 * @param array $condition condition config.
	 * @return bool $should_render.
	 */
	public function posts( $should_render, $condition ) {

		$has_match = false;

		if ( empty( $condition['posts'] ) ) {
			return $should_render;
		}

		$current_post_id = get_queried_object_id();

		$selected_posts = array_column( $condition['posts'], 'value' );

		if ( in_array( $current_post_id, $selected_posts, true ) ) {
			$has_match = true;
		}

		$block_action  = ! empty( $condition['blockAction'] ) ? $condition['blockAction'] : 'showBlock';

		if ( $has_match && $block_action === 'showBlock' ) {
			$should_render = true;
		} elseif ( ! $has_match && $block_action === 'hideBlock' ) {
			$should_render = true;
		}

		return $should_render;
	}

	/**
	 * Check the current post type.
	 *
	 * @param bool  $should_render if condition passed validation.
	 * @param array $condition condition config.
	 * @return bool $should_render.
	 */
	public function postType( $should_render, $condition ) {

		$has_match = false;

		$selected_post_types = array_column( $condition['postTypes'], 'value' );

		if ( is_singular( $selected_post_types ) ) {
			$has_match = true;
		}

		$block_action  = ! empty( $condition['blockAction'] ) ? $condition['blockAction'] : 'showBlock';

		if ( $has_match && $block_action === 'showBlock' ) {
			$should_render = true;
		} elseif ( ! $has_match && $block_action === 'hideBlock' ) {
			$should_render = true;
		}

		return $should_render;
	}

	/**
	 * Check the current post taxonomy terms.
	 *
	 * @param bool  $should_render if condition passed validation.
	 * @param array $condition condition config.
	 * @return bool $should_render.
	 */
	public function postTaxonomyTerms( $should_render, $condition ) {

		$has_match = false;

		if ( empty( $condition['postType']['value'] ) || empty( $condition['taxonomy']['value'] ) ) {
			return $should_render;
		}

		$current_post_id = get_queried_object_id();

		// We are checking terms, otherwise we are checking for 'any'.
		if ( ! empty( $condition['terms'] ) ) {

			$selected_terms = array_column( $condition['terms'], 'value' );

			if ( has_term( $selected_terms, $condition['taxonomy']['value'], $current_post_id ) ) {
				$has_match = true;
			}
		} else {
			if ( has_term( '', $condition['taxonomy']['value'], $current_post_id ) ) {
				$has_match = true;
			}
		}

		$block_action  = ! empty( $condition['blockAction'] ) ? $condition['blockAction'] : 'showBlock';

		if ( $has_match && $block_action === 'showBlock' ) {
			$should_render = true;
		} elseif ( ! $has_match && $block_action === 'hideBlock' ) {
			$should_render = true;
		}

		return $should_render;
	}

	/**
	 * Check the current page is an archive.
	 *
	 * @param bool  $should_render if condition passed validation.
	 * @param array $condition condition config.
	 * @return bool $should_render.
	 */
	public function archive( $should_render, $condition ) {

		$has_match = false;
		$archive_type = ! empty( $condition['archiveType'] ) ? $condition['archiveType'] : 'all';

		if ( is_archive() ) {
			if ( $archive_type === 'all' ) {
				$has_match = true;
			} else if ( $archive_type === 'postTypes' ) {
				$has_match = $this->maybe_check_terms_archive( $condition );
			}
		}

		$block_action  = ! empty( $condition['blockAction'] ) ? $condition['blockAction'] : 'showBlock';

		if ( $has_match && $block_action === 'showBlock' ) {
			$should_render = true;
		} elseif ( ! $has_match && $block_action === 'hideBlock' ) {
			$should_render = true;
		}

			return $should_render;
	}

	/**
	 * Check if current date compared to a date range.
	 *
	 * @param bool  $should_render if condition passed validation.
	 * @param array $condition condition config.
	 * @return bool $should_render.
	 */
	public function dateRange( $should_render, $condition ) {

		if ( empty( $condition['startTime'] ) ) {
			return false;
		}

		if ( is_numeric( $condition['startTime'] ) ) {
			$start_timestamp = (int) $condition['startTime'];
		} else {
			$start = DateTime::createFromFormat( 'Y-m-d\TH:i:s', $condition['startTime'], wp_timezone() );
			$start_timestamp = (int) $start->format( 'U' );
		}

		$end = ! empty( $condition['hasEndDate'] ) && ! empty( $condition['endTime'] ) ? $condition['endTime'] : false;

		$right_now = new DateTime();
		$right_now = $right_now->getTimestamp();

		// No end time. We are only checking the start.
		if ( ! $end ) {
			if ( $right_now >= $start_timestamp ) {
				$should_render = true;
			}

			return $should_render;
		}

		if ( is_numeric( $end ) ) {
			$end_timestamp = (int) $end;
		} else {
			$end_obj = DateTime::createFromFormat( 'Y-m-d\TH:i:s', $end, wp_timezone() );
			$end_timestamp = (int) $end_obj->format( 'U' );
		}

		// Check if we are in the range.
		if ( ( $right_now >= $start_timestamp ) && ( $right_now <= $end_timestamp ) ) {
			$should_render = true;
		}

		return $should_render;
	}

	/**
	 * Check if current date compared recurring dates.
	 *
	 * @param bool  $should_render if condition passed validation.
	 * @param array $condition condition config.
	 * @return bool $should_render.
	 */
	public function dateRecurring( $should_render, $condition ) {

		$recurring_days = ! empty( $condition['recurringDays'] ) ? $condition['recurringDays'] : false;

		if ( empty( $recurring_days ) ) {
			return $should_render;
		}

		$today_number = (int) wp_date( 'N' ); // e.g 1,2,3 for mon,tues,wed.

		// Correct Sunday if today is sunday.
		$today_number = $today_number === 7 ? 0 : $today_number;

		$day_match = false;

		foreach ( $recurring_days as $day ) {
			if ( $today_number === (int) $day ) {
				$day_match = true;
				break;
			}
		}

		if ( $day_match ) {

			$start = ! empty( $condition['startTime'] ) ? $condition['startTime'] : '00:00';
			$end = ! empty( $condition['endTime'] ) ? $condition['endTime'] : '23:59';

			$current_time = intval( wp_date( 'Hi' ) ); // formatted: 1517.

			// If the current time is not inbetween allowed time frame.
			if ( $current_time > intval( str_replace( ':', '', $start ) ) && $current_time < intval( str_replace( ':', '', $end ) ) ) {
				$should_render = true;
			}
		}

		return $should_render;
	}

	/**
	 * Check if current user roles.
	 *
	 * @param bool  $should_render if condition passed validation.
	 * @param array $condition condition config.
	 * @return bool $should_render.
	 */
	public function userRoles( $should_render, $condition ) {

		// User is not logged in.
		if ( ! is_user_logged_in() ) {
			return $should_render;
		}

		$user          = wp_get_current_user();
		$current_roles = (array) $user->roles;

		$has_role_match = false;

		foreach ( $condition['allowedRoles'] as $role ) {
			// $role will either have the value or array with label + value.
			$role = is_array( $role ) ? $role['value'] : $role;

			if ( in_array( $role, $current_roles, true ) ) {
				$has_role_match = true;
				break;
			}
		}

		$block_action  = ! empty( $condition['blockAction'] ) ? $condition['blockAction'] : 'showBlock';

		if ( $has_role_match && $block_action === 'showBlock' ) {
			$should_render = true;
		} elseif ( ! $has_role_match && $block_action === 'hideBlock' ) {
			$should_render = true;
		}

		return $should_render;
	}

	/**
	 * Check the http user agents. e.g. devices and browsers
	 *
	 * @param bool  $should_render if condition passed validation.
	 * @param array $condition condition config.
	 * @return bool $should_render.
	 */
	public function userAgents( $should_render, $condition ) {

		$allowed_agents = $condition['allowedAgents'];

		if ( empty( $allowed_agents ) ) {
			return false;
		}

		$current_agent = $this->parse_user_agent();

		if ( empty( $current_agent['platform'] ) && empty( $current_agent['browser'] ) ) {
			return false;
		}

		$u_platform = strtolower( $current_agent['platform'] );
		$u_browser = strtolower( $current_agent['browser'] );

		$matched_agent  = false;

		foreach ( $allowed_agents as $allowed_agent ) {
			$allowed_agent = is_array( $allowed_agent ) ? $allowed_agent['value'] : $allowed_agent;

			if ( $u_platform === $allowed_agent || $u_browser === $allowed_agent ) {
				$matched_agent  = true;
				break;
			}
		}

		$block_action  = ! empty( $condition['blockAction'] ) ? $condition['blockAction'] : 'showBlock';

		if ( $matched_agent && $block_action === 'showBlock' ) {
			$should_render = true;
		} elseif ( ! $matched_agent && $block_action === 'hideBlock' ) {
			$should_render  = true;
		}

		return $should_render;
	}

	/**
	 * Check the domain referer conditions.
	 *
	 * @param bool  $should_render if condition passed validation.
	 * @param array $condition condition config.
	 * @return bool $should_render.
	 */
	public function domainReferrers( $should_render, $condition ) {

		$allowed_referers = ! empty( $condition['domainReferrers'] ) ? $condition['domainReferrers'] : false;

		if ( ! empty( $allowed_referers ) ) {

			$matches_referer = false;

			if ( $this->is_allowed_referer( $allowed_referers ) ) {
				$matches_referer  = true;
			}

			$block_action  = ! empty( $condition['blockAction'] ) ? $condition['blockAction'] : 'showBlock';

			if ( $matches_referer && $block_action === 'showBlock' ) {
				$should_render = true;
			} elseif ( ! $matches_referer && $block_action === 'hideBlock' ) {
				$should_render = true;
			}
		}

		return $should_render;
	}

	/**
	 * Check if current Query Strings match a pattern.
	 *
	 * A normal pattern would be like success=true
	 * Also check if the query strings don't match using ! in front !success=true
	 *
	 * @param bool  $should_render if condition passed validation.
	 * @param array $condition condition config.
	 * @return bool $should_render.
	 */
	public function queryStrings( $should_render, $condition ) {
		$allowed_query_strings = $condition['allowedStrings'] ?? false;
		$current_query_string = $_SERVER['QUERY_STRING'] ?? false;
		$block_action = $condition['blockAction'] ?? 'showBlock';
		$requirement = $condition['requirement'] ?? 'present'; // or notPresent.

		if ( ! $allowed_query_strings || ! $current_query_string ) {
			return $should_render;
		}

		$query_strings_array = explode( "\n", $allowed_query_strings );

		$detected = 0;

		foreach ( $query_strings_array as $query_pattern ) {

			$cleaned_query_pattern = ltrim( $query_pattern, '!' );
			$is_present = strpos( $current_query_string, $cleaned_query_pattern ) !== false;

			if ( empty( $cleaned_query_pattern ) ) {
				continue;
			}

			if ( $is_present ) {
				$detected++;
			}
		}

		$has_match = ( $requirement === 'present' && $detected > 0 ) || ( $requirement === 'notPresent' && $detected === 0 );

		if ( $has_match && $block_action === 'showBlock' ) {
			$should_render = true;
		} elseif ( ! $has_match && $block_action === 'hideBlock' ) {
			$should_render = true;
		}

		return $should_render;

	}


	/**
	 *  Check User Meta condition.
	 *
	 * @param bool  $should_render if condition passed validation.
	 * @param array $condition condition config.
	 * @return bool $should_render.
	 */
	public function userMeta( $should_render, $condition ) {

		$meta_key = ! empty( $condition['metaKey'] ) ? $condition['metaKey'] : false;
		$meta_operator = ! empty( $condition['metaOperator'] ) ? $condition['metaOperator'] : false;
		$meta_value = ! empty( $condition['metaValue'] ) ? $condition['metaValue'] : '';

		if ( $meta_key && $meta_operator && $meta_value ) {

			if ( $this->has_required_meta( 'user', $meta_key, $meta_operator, $meta_value ) ) {
				$should_render = true;
			}
		}

		return $should_render;
	}

	/**
	 *  Check post meta condition.
	 *
	 * @param bool  $should_render if condition passed validation.
	 * @param array $condition condition config.
	 * @return bool $should_render.
	 */
	public function postMeta( $should_render, $condition ) {

		$meta_key = ! empty( $condition['metaKey'] ) ? $condition['metaKey'] : false;
		$meta_operator = ! empty( $condition['metaOperator'] ) ? $condition['metaOperator'] : false;
		$meta_value = ! empty( $condition['metaValue'] ) ? $condition['metaValue'] : '';

		if ( $meta_key && $meta_operator ) {

			if ( $this->has_required_meta( 'post', $meta_key, $meta_operator, $meta_value ) ) {
				$should_render = true;
			}
		}

		return $should_render;
	}

	/**
	 *  Check cookies condition.
	 *
	 * @param bool  $should_render if condition passed validation.
	 * @param array $condition condition config.
	 * @return bool $should_render.
	 */
	public function cookies( $should_render, $condition ) {

		$selected_cookies = ! empty( $condition['selectedCookies'] ) ? $condition['selectedCookies'] : false;

		/**
		 * Check for Cookies.
		 */
		if ( $selected_cookies ) {

			// Default to "block is only allowed on these paths".
			$should_contain = empty( $condition['shouldShowForSelectedCookies'] ) || $condition['shouldShowForSelectedCookies'] === 'yes' ? true : false;

			// Make line breaks into an array.
			$selected_cookies = explode( "\n", $selected_cookies );

			if ( ! is_array( $selected_cookies ) ) {
				return false;
			}

			$has_cookie_match = false;

			foreach ( $selected_cookies as $selected_cookie ) {
				if ( isset( $_COOKIE[ $selected_cookie ] ) ) {
					// Cookie match.
					$has_cookie_match  = true;
					break;
				}
			}

			// Cookie should be present, but isn't.
			if ( $should_contain && $has_cookie_match ) {
				$should_render = true;
			} elseif ( ! $should_contain && ! $has_cookie_match ) {
				$should_render = true;
			}
		}

		return $should_render;
	}

	/**
	 *  Check Geo Location condition.
	 *
	 * @param bool  $should_render if condition passed validation.
	 * @param array $condition condition config.
	 * @return bool $should_render.
	 */
	public function wcGeoLocation( $should_render, $condition ) {

		$selected_countries = ! empty( $condition['countries'] ) ? $condition['countries'] : false;

		if ( ! class_exists( 'WC_Geolocation' ) ) {
			return $should_render;
		}

		/**
		 * Check if IP comes from the countries.
		 */
		if ( ! empty( $selected_countries ) ) {

			if ( ! is_array( $selected_countries ) ) {
				return false;
			}

			$has_match = false;

			$geo      = new WC_Geolocation();
			$user_ip  = $geo->get_ip_address();
			$user_geo = $geo->geolocate_ip( $user_ip ); // Get geolocated user data.
			$country_code  = $user_geo['country']; // Get the country code.

			foreach ( $selected_countries as $selected_country ) {
				if ( $country_code === $selected_country['value'] ) {
					$has_match = true;
					break;
				}
			}

			$block_action  = ! empty( $condition['blockAction'] ) ? $condition['blockAction'] : 'showBlock';

			if ( $has_match && $block_action === 'showBlock' ) {
				$should_render = true;
			} elseif ( ! $has_match && $block_action === 'hideBlock' ) {
				$should_render = true;
			}
		}

		return $should_render;
	}

	/**
	 *  Check the URL paths conditions.
	 *
	 * @param bool  $should_render if condition passed validation.
	 * @param array $condition condition config.
	 * @return bool $should_render.
	 */
	public function urlPaths( $should_render, $condition ) {

		$request_uri = ! empty( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : false;
		$selected_paths = ! empty( $condition['selectedPaths'] ) ? $condition['selectedPaths'] : false;

		if ( $request_uri && $selected_paths ) {

			// Default to "block is only allowed on these paths".
			$should_contain = empty( $condition['shouldContain'] ) || $condition['shouldContain'] === 'yes' ? true : false;

			// Make line breaks into an array, make sure they are integers.
			$selected_paths = explode( "\n", $selected_paths );

			$meets_requirements = false;

			foreach ( $selected_paths as $selected_path ) {
				if ( $should_contain ) {
					if ( strpos( $request_uri, trim( $selected_path ) ) !== false ) {
						// Path found.
						$meets_requirements   = true;
						break;
					}
				} else {
					if ( strpos( $request_uri, trim( $selected_path ) ) === false ) {
						// Path not found.
						$meets_requirements   = true;
						break;
					}
				}
			}
		}

		if ( $meets_requirements ) {
			$should_render = true;
		}

		return $should_render;
	}

	/**
	 *  Check post ids condition.
	 *
	 * @param bool  $should_render if condition passed validation.
	 * @param array $condition condition config.
	 * @return bool $should_render.
	 */
	public function postIds( $should_render, $condition ) {

		$post_ids = ! empty( $condition['postIds'] ) ? $condition['postIds'] : false;

		$current_post_id = get_the_ID();

		if ( ! empty( $post_ids ) && ! empty( $current_post_id ) ) {

			// Default to "block is only allowed on these post ids".
			$is_post_ids_allowed = empty( $condition['equal'] ) || $condition['equal'] === 'equal' ? true : false;

			// Make line breaks into an array, make sure they are integers.
			$post_ids = array_map( 'intval', explode( "\n", $post_ids ) );

			if ( $is_post_ids_allowed ) {
				if ( in_array( (int) $current_post_id, $post_ids, true ) ) {
					$should_render = true;
				}
			} else {
				if ( ! in_array( (int) $current_post_id, $post_ids, true ) ) {
					$should_render = true;
				}
			}
		}

		return $should_render;
	}

	/**
	 *  Check custom PHP Logic
	 *
	 * @param bool  $should_render if condition passed validation.
	 * @param array $condition condition config.
	 * @return bool $should_render.
	 */
	public function phpLogic( $should_render, $condition ) {

		$raw_functions = ! empty( $condition['phpLogic'] ) ? $condition['phpLogic'] : false;

		if ( $raw_functions ) {

			$raw_functions = ! empty( $condition['phpLogic'] ) ? $condition['phpLogic'] : false;

			$array_of_functions = explode( "\n", $raw_functions );

			$allowed_functions = array(
				'is_single',
				'has_term',
				'in_category',
				'is_category',
				'get_option',
				'has_tag',
				'is_tag',
				'is_tax',
				'is_page',
				'get_post_meta',
				'has_post_thumbnail',
				'has_excerpt',
				'is_sticky',
			);

			$allowed_functions = apply_filters( 'conditional_blocks_filter_php_logic_functions', $allowed_functions );

			$passed_functions = 0;

			foreach ( $array_of_functions as $whole_function ) {

				// Make sure there isn't an eval or call_user_func anywhere. Fucntion or params to prevent attacks.
				if ( strpos( $whole_function, 'eval' ) !== false || strpos( $whole_function, 'call_user_func' ) !== false ) {
					continue;
				}

				$function_name = strtok( $whole_function, '(' );
				$function_name  = ltrim( $function_name, '!' );

				if ( ! in_array( $function_name, $allowed_functions, true ) || ! is_callable( $function_name ) ) {
					continue;
				}

				// Find the params.
				preg_match( '/(?<=\()(.+)(?=\))/is', $whole_function, $match );
				// Params as string. eg "100, 'single'".
				$params_to_use = isset( $match[1] ) && ! empty( $match[1] ) ? $match[1] : null;

				$params_to_use = explode( ',', $params_to_use );

				$params_to_use = array_map(
					function( $param ) {
						return trim( $param, ' \'"' );
					},
					$params_to_use
				);

				$expected_bool = substr( $whole_function, 0, 1 ) === '!' ? false : true;

				$function_result = call_user_func_array( $function_name, $params_to_use );

				// Make sure we have the expected result.
				if ( $function_result === $expected_bool ) {
					$passed_functions++;
				}
			}

			// We must pass all functions for the block to show.
			if ( (int) $passed_functions === (int) count( $array_of_functions ) ) {
				$should_render = true;
			}
		}

		return $should_render;
	}

	/**
	 *  Check WooCommerce total spent by customer.
	 *
	 * @param bool  $should_render if condition passed validation.
	 * @param array $condition condition config.
	 * @return bool $should_render.
	 */
	public function wcCustomerTotalSpent( $should_render, $condition ) {

		// Make sure WC is active.
		if ( ! class_exists( 'WooCommerce' ) || ! function_exists( 'WC' ) ) {
			return $should_render;
		}

		$user_id = get_current_user_id();

		if ( empty( $user_id ) ) {
			return $should_render;
		}

		$total = (int) wc_get_customer_total_spent( $user_id );

		$more_than_value = ! empty( $condition['moreThanValue'] ) ? (int) $condition['moreThanValue'] : 0;
		$less_than_value = ! empty( $condition['lessThanValue'] ) ? (int) $condition['lessThanValue'] : 0;

		$is_more_than_required = empty( $more_than_value ) ? true : $total > $more_than_value;
		$is_less_than_required = empty( $less_than_value ) ? true : $total < $less_than_value;

		if ( $is_more_than_required && $is_less_than_required ) {
			$should_render = true;
		}

		return $should_render;
	}

	/**
	 *  Check WooCommerce Customer Recent Order
	 *
	 * @param bool  $should_render if condition passed validation.
	 * @param array $condition condition config.
	 * @return bool $should_render.
	 */
	public function wcCustomerRecentOrder( $should_render, $condition ) {

		// Make sure WC is active.
		if ( ! class_exists( 'WooCommerce' ) || ! function_exists( 'WC' ) ) {
			return $should_render;
		}

		$user_id = get_current_user_id();

		// No user ID if guest customer.
		if ( empty( $user_id ) ) {
			return $should_render;
		}

		$time_amount = ! empty( $condition['timeAmount'] ) ? (int) $condition['timeAmount'] : 7;
		$time_format = ! empty( $condition['timeFormat'] ) ? $condition['timeFormat'] : 'days';

		if ( $time_format === 'days' ) {
			$max_seconds_since_order = DAY_IN_SECONDS * $time_amount;

		} else if ( $time_format === 'hours' ) {
			$max_seconds_since_order = HOUR_IN_SECONDS * $time_amount;

		} else if ( $time_format === 'minutes' ) {
			$max_seconds_since_order = MINUTE_IN_SECONDS * $time_amount;
		}

		$last_order_max_timestamp = time() - $max_seconds_since_order;

		// Get an order since our max timestamp.
		$orders = wc_get_orders(
			array(
				'customer_id' => $user_id,
				'date_created' => '>' . ( $last_order_max_timestamp ),
				'limit' => 1,
			)
		);

		$has_match = false;

		if ( ! empty( $orders ) ) {
			$has_match = true;
		}

		$block_action  = ! empty( $condition['blockAction'] ) ? $condition['blockAction'] : 'showBlock';

		if ( $has_match && $block_action === 'showBlock' ) {
			$should_render = true;
		} elseif ( ! $has_match && $block_action === 'hideBlock' ) {
			$should_render = true;
		}

		return $should_render;
	}

	/**
	 *  Check WooCommerce Cart Total
	 *
	 * @param bool  $should_render if condition passed validation.
	 * @param array $condition condition config.
	 * @return bool $should_render.
	 */
	public function wcCartTotal( $should_render, $condition ) {

		// Make sure WC is active.
		if ( ! class_exists( 'WooCommerce' ) || ! function_exists( 'WC' ) ) {
			return $should_render;
		}

		$total = is_object( WC()->cart ) ? (int) WC()->cart->get_cart_contents_total() : 0;

		$more_than_value = ! empty( $condition['moreThanValue'] ) ? (int) $condition['moreThanValue'] : 0;
		$less_than_value = ! empty( $condition['lessThanValue'] ) ? (int) $condition['lessThanValue'] : 0;

		$is_more_than_required = empty( $more_than_value ) ? true : $total > $more_than_value;
		$is_less_than_required = empty( $less_than_value ) ? true : $total < $less_than_value;

		if ( $is_more_than_required && $is_less_than_required ) {
			$should_render = true;
		}

		return $should_render;
	}

	/**
	 *  Check WooCommerce Products in Cart.
	 *
	 * @param bool  $should_render if condition passed validation.
	 * @param array $condition condition config.
	 * @return bool $should_render.
	 */
	public function wcCartProducts( $should_render, $condition ) {

		// Make sure WC is active.
		if ( ! class_exists( 'WooCommerce' ) || ! function_exists( 'WC' ) ) {
			return $should_render;
		}

		if ( empty( $condition['products'] ) ) {
			return $should_render;
		}

		$cart = is_object( WC()->cart ) ? WC()->cart->get_cart() : array();

		$selected_product_ids = array_column( $condition['products'], 'value' );

		$matched_product_ids = array();

		foreach ( $cart as $cart_item ) {
			$product_id = $cart_item['product_id'];

			if ( in_array( $product_id, $selected_product_ids, true ) ) {
				$matched_product_ids[] = $product_id;
			};
		}

		// All products need to be in cart, otherwise any of the products.
		$requires_all_products = ! empty( $condition['requirement'] ) && $condition['requirement'] === 'all' ? 'all' : 'any'; // Defualt to any for backwards comapt.

		// Matches the condition requirements.
		$has_match = false;

		if ( $requires_all_products === 'all' ) {
			$has_match = ! empty( $matched_product_ids ) && ! array_diff( $selected_product_ids, $matched_product_ids ); // If there's no difference between the selected and found products.
		} else {
			$has_match = count( $matched_product_ids ) >= 1; // Atleast one of the selected products ids was found.
		}

		$block_action  = ! empty( $condition['blockAction'] ) ? $condition['blockAction'] : 'showBlock';

		if ( $has_match && $block_action === 'showBlock' ) {
			$should_render = true;
		} elseif ( ! $has_match && $block_action === 'hideBlock' ) {
			$should_render = true;
		}

		return $should_render;
	}

	/**
	 *  Check WooCommerce products categories in cart.
	 *
	 * @param bool  $should_render if condition passed validation.
	 * @param array $condition condition config.
	 * @return bool $should_render.
	 */
	public function wcCartProductCategories( $should_render, $condition ) {

		// Make sure WC is active.
		if ( ! class_exists( 'WooCommerce' ) || ! function_exists( 'WC' ) ) {
			return $should_render;
		}

		if ( empty( $condition['categories'] ) ) {
			return $should_render;
		}

		$cart = is_object( WC()->cart ) ? WC()->cart->get_cart() : array();

		$has_role_match = false;

		$selected_product_category_ids = array_column( $condition['categories'], 'value' );

		$has_match = false;

		foreach ( $cart as $cart_item ) {

			$product_id = $cart_item['product_id'];

			if ( has_term( $selected_product_category_ids, 'product_cat', $cart_item['product_id'] ) ) {
				$has_match = true;
				break;
			}
		}

		$block_action  = ! empty( $condition['blockAction'] ) ? $condition['blockAction'] : 'showBlock';

		if ( $has_match && $block_action === 'showBlock' ) {
			$should_render = true;
		} elseif ( ! $has_match && $block_action === 'hideBlock' ) {
			$should_render = true;
		}

		return $should_render;
	}

	/**
	 *  Check Preset
	 *
	 * @param bool  $should_render if condition passed validation.
	 * @param array $condition condition config.
	 * @return bool $should_render.
	 */
	public function presets( $should_render, $condition ) {

		$preset_ids = ! empty( $condition['presets'] ) ? $condition['presets'] : array();

		if ( ! empty( $preset_ids ) ) {

			$presets = get_option( 'conditional_blocks_presets', array() );

			if ( empty( $presets ) || ! is_array( $presets ) ) {
				return $should_render;
			}

			$presets_met = 0;

			foreach ( $preset_ids as $preset_id ) {

				$preset_id = is_array( $preset_id ) ? $preset_id['value'] : $preset_id;

				// Search for the preset id in all our presets. Returns the key we need.
				$preset_key = array_search( $preset_id, array_column( $presets, 'id' ), true );

				if ( $preset_key === false ) {
					continue;
				}

				$conditions_to_test = isset( $presets[ $preset_key ]['conditions'] ) && ! empty( $presets[ $preset_key ]['conditions'] ) ? $presets[ $preset_key ]['conditions'] : false;

				if ( $conditions_to_test === false ) {
					continue;
				}

				// Preset conditions cannot have "presets" therefore this shouldn't cause an inf loop.
				$should_render_preset_condition = $this->check_conditions( $conditions_to_test );

				if ( ! empty( $should_render_preset_condition ) ) {
					$presets_met++;
				}
			}

			$requirement  = ! empty( $condition['requirement'] ) ? $condition['requirement'] : 'all';

			if ( $requirement === 'all' && count( $preset_ids ) === $presets_met ) {
				$should_render = true;
			} elseif ( $requirement === 'any' && $presets_met > 0 ) {
				$should_render = true;
			}
		}

		return $should_render;
	}

	/**
	 * Check if current referer allows block.
	 *
	 * @param array $render_block array containing condition name and 'yes'/'no' if blocks should be rendered.
	 * @param array $conditions Block block conditions array.
	 * @param array $block the whole block object.
	 * @return array render_block
	 */
	public function is_allowed_referer( $allowed_referers = false ) {

		// No referer, don't show.
		if ( empty( $allowed_referers ) || empty( $_SERVER['HTTP_REFERER'] ) ) {
			return false;
		}

		$referer_parse = parse_url( $_SERVER['HTTP_REFERER'] );

		// No referer.
		if ( empty( $referer_parse ) ) {
			return false;
		}

		$allowed_referers = array_map( 'trim', explode( ',', $allowed_referers ) );

		// Check if referer is allowed by the block.
		if ( ! empty( $referer_parse['host'] ) && ( in_array( $referer_parse['host'], $allowed_referers, true ) ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check Post meta condition.
	 *
	 * @param string $meta_key array containing condition name and 'yes'/'no' if blocks should be rendered.
	 * @param string $meta_operator Block block conditions array.
	 * @param string $meta_value the whole block object.
	 * @return bool if current post has the required meta.
	 */
	public function has_required_meta( $meta_type, $meta_key, $meta_operator, $meta_value ) {

		$post_id = get_the_ID();

		if ( empty( $post_id ) ) {
			return false;
		}

		if ( $meta_type === 'post' ) {
			$selected_meta = get_post_meta( $post_id, $meta_key, true );
		} elseif ( $meta_type === 'user' ) {
			$selected_meta = get_user_meta( get_current_user_id(), $meta_key, true );
		}

		if ( '===' === $meta_operator ) {
			if ( $selected_meta === $meta_value ) {
				return true;
			}
		}

		if ( '!==' === $meta_operator ) {
			if ( $selected_meta !== $meta_value ) {
				return true;
			}
		}

		if ( 'true' === $meta_operator ) {
			if ( $selected_meta ) {
				return true;
			}
		}

		if ( 'false' === $meta_operator ) {
			if ( ! $selected_meta ) {
				return true;
			}
		}

		if ( 'empty' === $meta_operator ) {
			if ( empty( $selected_meta ) ) {
				return true;
			}
		}

		if ( 'notEmpty' === $meta_operator ) {
			if ( ! empty( $selected_meta ) ) {
				return true;
			}
		}

		if ( 'contains' === $meta_operator ) {
			if ( stripos( $selected_meta, $meta_value ) !== false ) {
				return true;
			}
		}

		if ( 'doesNotContain' === $meta_operator ) {
			if ( stripos( $selected_meta, $meta_value ) === false ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check term archive. Used in the Archive condition.
	 *
	 * @param [type] $condition
	 * @return void
	 */
	private function maybe_check_terms_archive( $condition ) {

		$required_post_type = ! empty( $condition['postType']['value'] ) ? $condition['postType']['value'] : false;
		$required_term_ids = ! empty( $condition['terms'] ) ? array_column( $condition['terms'], 'value' ) : false;
		$required_tax = ! empty( $condition['taxonomy']['value'] ) ? $condition['taxonomy']['value'] : false;
		$required_include_child_terms = ! empty( $condition['includeChildTerms'] ) ? $condition['includeChildTerms'] : false;

		// At minimum we need to the posttype check.
		if ( ! $required_post_type ) {
			return true; // Allow render.
		}

		// Hanlde post type archive pages only.
		if ( ! $required_term_ids && ! $required_tax ) {
			// Check the post type - Multiple post type can have the same taxonomy.
			if ( $required_post_type && is_post_type_archive( $required_post_type ) ) {
				return true;
			} else {
				return false;
			}
		}

		// Check the built-in taxonomies and custom taxomonies with is_tax.
		if ( $required_tax === 'category' ) {
			if ( ! is_category() ) {
				return false;
			}
		} else if ( $required_tax === 'post_tag' ) {
			if ( ! is_tag() ) {
				return false;
			}
		} else if ( $required_tax && ! is_tax( $required_tax ) ) {
			return false;
		}

		$current_object = get_queried_object();
		$current_term_id = ! empty( $current_object->term_id ) ? $current_object->term_id : false;

		if ( $current_term_id && $required_term_ids && $this->terms_matches_term( $required_term_ids, $current_term_id, $required_tax, $required_include_child_terms ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if the multiple terms match a single term. Allows checking of all subterms as well.
	 *
	 * @param [type] $required_term_ids
	 * @param [type] $current_term_id
	 * @param [type] $match_sub_all_children
	 * @return void
	 */
	public function terms_matches_term( $required_term_ids, $current_term_id, $current_tax, $match_sub_all_children ) {

		// Match the single term.
		if ( in_array( $current_term_id, $required_term_ids, true ) ) {
			return true;
		} elseif ( $match_sub_all_children ) {
			// Check if the current term is a child of our required term.
			foreach ( $required_term_ids as $required_term_id ) {
				$children = get_term_children( $required_term_id, $current_tax ); // We've already checked the tax.

				if ( ! empty( $children ) && in_array( $current_term_id, $children, true ) ) {
					return true;
					break;
				}
			}
		}
		return false;
	}

	/**
	 * Parses a user agent string into its important parts
	 * Slightly modifed from 1.2.0 to meet WP standards.
	 *
	 * @author Jesse G. Donat
	 * @link https://github.com/donatj/PhpUserAgent
	 * @link http://donatstudios.com/PHP-Parser-HTTP_USER_AGENT
	 * @param string|null $u_agent User agent string to parse or null. Uses $_SERVER['HTTP_USER_AGENT'] on NULL
	 * @return string[] an array with browser, version and platform keys
	 */
	public function parse_user_agent( $u_agent = null ) {

		 $platform_key        = 'platform';
		 $browser_key         = 'browser';
		 $browser_version_key = 'version';

		if ( $u_agent === null && isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
			$u_agent = (string) $_SERVER['HTTP_USER_AGENT'];
		}

		$platform = null;
		$browser  = null;
		$version  = null;

		$empty = array(
			$platform_key => $platform,
			$browser_key => $browser,
			$browser_version_key => $version,
		);

		if ( empty( $u_agent ) ) {
			return $empty;
		}

		if ( preg_match( '/\((.*?)\)/m', $u_agent, $parent_matches ) ) {
			preg_match_all(
				<<<'REGEX'
/(?P<platform>BB\d+;|Android|Adr|Symbian|CrOS|Tizen|iPhone|iPad|iPod|Linux|(Open|Net|Free)BSD|Macintosh|Windows(\ Phone)?|Silk|linux-gnu|BlackBerry|PlayBook|X11|(New\ )?Nintendo\ (WiiU?|3?DS|Switch)|Xbox(\ One)?)
(?:\ [^;]*)?
(?:;|$)/imx
REGEX
				,
				$parent_matches[1],
				$result
			);

			$priority = array( 'Xbox One', 'Xbox', 'Windows Phone', 'Tizen', 'Android', 'FreeBSD', 'NetBSD', 'OpenBSD', 'CrOS', 'X11' );

			$result[ $platform_key ] = array_unique( $result[ $platform_key ] );
			if ( count( $result[ $platform_key ] ) > 1 ) {
				if ( $keys = array_intersect( $priority, $result[ $platform_key ] ) ) {
					$platform = reset( $keys );
				} else {
					$platform = $result[ $platform_key ][0];
				}
			} elseif ( isset( $result[ $platform_key ][0] ) ) {
				$platform = $result[ $platform_key ][0];
			}
		}

		if ( $platform == 'linux-gnu' || $platform == 'X11' ) {
			$platform = 'Linux';
		} elseif ( $platform == 'CrOS' ) {
			$platform = 'Chrome OS';
		} elseif ( $platform == 'Adr' ) {
			$platform = 'Android';
		}

		preg_match_all(
			<<<'REGEX'
			%(?P<browser>Camino|Kindle(\ Fire)?|Firefox|Iceweasel|IceCat|Safari|MSIE|Trident|AppleWebKit|
			TizenBrowser|(?:Headless)?Chrome|YaBrowser|Vivaldi|IEMobile|Opera|OPR|Silk|Midori|Edge|Edg|CriOS|UCBrowser|Puffin|OculusBrowser|SamsungBrowser|
			Baiduspider|Applebot|Googlebot|YandexBot|bingbot|Lynx|Version|Wget|curl|
			Valve\ Steam\ Tenfoot|
			NintendoBrowser|PLAYSTATION\ (\d|Vita)+)
			(?:\)?;?)
			(?:(?:[:/ ])(?P<version>[0-9A-Z.]+)|/(?:[A-Z]*))%ix
REGEX
			,
			$u_agent,
			$result
		);

		// If nothing matched, return null (to avoid undefined index errors).
		if ( ! isset( $result[ $browser_key ][0] ) || ! isset( $result[ $browser_version_key ][0] ) ) {
			if ( preg_match( '%^(?!Mozilla)(?P<browser>[A-Z0-9\-]+)(/(?P<version>[0-9A-Z.]+))?%ix', $u_agent, $result ) ) {
				return array(
					$platform_key => $platform ?: null,
					$browser_key => $result[ $browser_key ],
					$browser_version_key => empty( $result[ $browser_version_key ] ) ? null : $result[ $browser_version_key ],
				);
			}

			return $empty;
		}

		if ( preg_match( '/rv:(?P<version>[0-9A-Z.]+)/i', $u_agent, $rv_result ) ) {
			$rv_result = $rv_result[ $browser_version_key ];
		}

		$browser = $result[ $browser_key ][0];
		$version = $result[ $browser_version_key ][0];

		$lowerBrowser = array_map( 'strtolower', $result[ $browser_key ] );

		$find = function ( $search, &$key = null, &$value = null ) use ( $lowerBrowser ) {
			$search = (array) $search;

			foreach ( $search as $val ) {
				$xkey = array_search( strtolower( $val ), $lowerBrowser );
				if ( $xkey !== false ) {
					$value = $val;
					$key   = $xkey;

					return true;
				}
			}

			return false;
		};

		$findT = function ( array $search, &$key = null, &$value = null ) use ( $find ) {
			$value2 = null;
			if ( $find( array_keys( $search ), $key, $value2 ) ) {
				$value = $search[ $value2 ];

				return true;
			}

			return false;
		};

		$key = 0;
		$val = '';
		if ( $findT(
			array(
				'OPR' => 'Opera',
				'UCBrowser' => 'UC Browser',
				'YaBrowser' => 'Yandex',
				'Iceweasel' => 'Firefox',
				'Icecat' => 'Firefox',
				'CriOS' => 'Chrome',
				'Edg' => 'Edge',
			),
			$key,
			$browser
		) ) {
			$version = $result[ $browser_version_key ][ $key ];
		} elseif ( $find( 'Playstation Vita', $key, $platform ) ) {
			$platform = 'PlayStation Vita';
			$browser  = 'Browser';
		} elseif ( $find( array( 'Kindle Fire', 'Silk' ), $key, $val ) ) {
			$browser  = $val == 'Silk' ? 'Silk' : 'Kindle';
			$platform = 'Kindle Fire';
			if ( ! ( $version = $result[ $browser_version_key ][ $key ] ) || ! is_numeric( $version[0] ) ) {
						$version = $result[ $browser_version_key ][ array_search( 'Version', $result[ $browser_key ] ) ];
			}
		} elseif ( $find( 'NintendoBrowser', $key ) || $platform == 'Nintendo 3DS' ) {
			$browser = 'NintendoBrowser';
			$version = $result[ $browser_version_key ][ $key ];
		} elseif ( $find( 'Kindle', $key, $platform ) ) {
			$browser = $result[ $browser_key ][ $key ];
			$version = $result[ $browser_version_key ][ $key ];
		} elseif ( $find( 'Opera', $key, $browser ) ) {
			$find( 'Version', $key );
			$version = $result[ $browser_version_key ][ $key ];
		} elseif ( $find( 'Puffin', $key, $browser ) ) {
			$version = $result[ $browser_version_key ][ $key ];
			if ( strlen( $version ) > 3 ) {
				$part = substr( $version, -2 );
				if ( ctype_upper( $part ) ) {
						$version = substr( $version, 0, -2 );

						$flags = array(
							'IP' => 'iPhone',
							'IT' => 'iPad',
							'AP' => 'Android',
							'AT' => 'Android',
							'WP' => 'Windows Phone',
							'WT' => 'Windows',
						);
						if ( isset( $flags[ $part ] ) ) {
							$platform = $flags[ $part ];
						}
				}
			}
		} elseif ( $find( array( 'Applebot', 'IEMobile', 'Edge', 'Midori', 'Vivaldi', 'OculusBrowser', 'SamsungBrowser', 'Valve Steam Tenfoot', 'Chrome', 'HeadlessChrome' ), $key, $browser ) ) {
			$version = $result[ $browser_version_key ][ $key ];
		} elseif ( $rv_result && $find( 'Trident' ) ) {
			$browser = 'MSIE';
			$version = $rv_result;
		} elseif ( $browser == 'AppleWebKit' ) {
			if ( $platform == 'Android' ) {
				$browser = 'Android Browser';
			} elseif ( strpos( $platform, 'BB' ) === 0 ) {
				$browser  = 'BlackBerry Browser';
				$platform = 'BlackBerry';
			} elseif ( $platform == 'BlackBerry' || $platform == 'PlayBook' ) {
				$browser = 'BlackBerry Browser';
			} else {
				$find( 'Safari', $key, $browser ) || $find( 'TizenBrowser', $key, $browser );
			}

			$find( 'Version', $key );
			$version = $result[ $browser_version_key ][ $key ];
		} elseif ( $pKey = preg_grep( '/playstation \d/i', $result[ $browser_key ] ) ) {
			$pKey = reset( $pKey );

			$platform = 'PlayStation ' . preg_replace( '/\D/', '', $pKey );
			$browser  = 'NetFront';
		}

		return array(
			$platform_key => $platform ?: null,
			$browser_key => $browser ?: null,
			$browser_version_key => $version ?: null,
		);
	}

	/**
	 * Convert v2 conditions to v3 sets of conditions.
	 *
	 * Conditional Blocks now uses sets of conditions. V2 is converted to a single set for compat.
	 *
	 * @param array $v2_conditions an array of conditions.
	 * @return array sets of conditions.
	 */
	public function convert_v2_to_v3_condition_sets( $v2_conditions ) {

		if ( empty( $v2_conditions ) ) {
			return false;
		}

		$sets = array();

		// Add our v2 conditions as a new set.
		$sets[] = array(
			'id' => false,
			'type' => 'set',
			'conditions' => $v2_conditions,
		);

		return $sets;
	}

	/**
	 * Convert legacy blocks to match the new condition layout.
	 *
	 * @param [type] $block
	 * @return void
	 */
	public function convert_v1_to_v2_conditions( $legacy_conditions ) {

		$conditions = array();

		$legacy_conditions['userState'] === 'logged-in' ? array_push(
			$conditions,
			array(
				'id' => wp_generate_uuid4(),
				'type' => 'userLoggedIn',
			)
		) : false;

		$legacy_conditions['userState'] === 'logged-out' ? array_push(
			$conditions,
			array(
				'id' => wp_generate_uuid4(),
				'type' => 'userLoggedOut',
			)
		) : false;

		$has_screensize = false;

		$show_on = array(
			'showMobileScreen',
			'showTabletScreen',
			'showDesktopScreen',
		);

		if ( isset( $legacy_conditions['hideOnMobile'] ) && $legacy_conditions['hideOnMobile'] === true ) {
			unset( $show_on[0] );
			$has_screensize = true;
		}

		if ( isset( $legacy_conditions['hideOnTablet'] ) && $legacy_conditions['hideOnTablet'] === true ) {
			unset( $show_on[1] );
			$has_screensize = true;
		}

		if ( isset( $legacy_conditions['hideOnDesktop'] ) && $legacy_conditions['hideOnDesktop'] === true ) {
			unset( $show_on[2] );
			$has_screensize = true;
		}

		if ( $has_screensize ) {
			array_push(
				$conditions,
				array(
					'id' => wp_generate_uuid4(),
					'type' => 'responsiveScreenSizes',
					'showOn' => array_values( $show_on ), // Make sure we only have the values.
				)
			);
		}

		if ( ! empty( $legacy_conditions['userRoles'] ) && is_array( $legacy_conditions['userRoles'] ) ) {
			array_push(
				$conditions,
				array(
					'id' => wp_generate_uuid4(),
					'type' => 'userRoles',
					'allowedRoles' => $legacy_conditions['userRoles'],
				)
			);
		}

		if ( ! empty( $legacy_conditions['httpUserAgent'] ) && is_array( $legacy_conditions['httpUserAgent'] ) ) {
			array_push(
				$conditions,
				array(
					'id' => wp_generate_uuid4(),
					'type' => 'userAgents',
					'allowedAgents' => $legacy_conditions['httpUserAgent'],
				)
			);
		}

		if ( ! empty( $legacy_conditions['httpReferer'] ) ) {
			array_push(
				$conditions,
				array(
					'id' => wp_generate_uuid4(),
					'type' => 'domainReferrers',
					'domainReferrers' => $legacy_conditions['httpReferer'],
				)
			);
		}

		if ( ! empty( $legacy_conditions['dates'] ) && is_array( $legacy_conditions['dates'] ) ) {

			foreach ( $legacy_conditions['dates'] as $date_range ) {

				if ( ! empty( $date_range['start'] ) && ! empty( $date_range['end'] ) ) {
					array_push(
						$conditions,
						array(
							'id' => wp_generate_uuid4(),
							'type' => 'dateRange',
							'startTime' => $date_range['start'],
							'endTime' => $date_range['end'],
							'hasEndDate' => true,
						)
					);
				}
			}
		}

		if ( isset( $legacy_conditions['postMeta']['key'] ) && ! empty( $legacy_conditions['postMeta']['key'] ) ) {

			array_push(
				$conditions,
				array(
					'id' => wp_generate_uuid4(),
					'type' => 'postMeta',
					'metaKey' => isset( $legacy_conditions['postMeta']['key'] ) ? $legacy_conditions['postMeta']['key'] : false,
					'metaOperator' => isset( $legacy_conditions['postMeta']['operator'] ) ? $legacy_conditions['postMeta']['operator'] : false,
					'metaValue' => isset( $legacy_conditions['postMeta']['value'] ) ? $legacy_conditions['postMeta']['value'] : false,
				)
			);
		}

		return $conditions;
	}
}

$class = new Conditional_Blocks_Render_Block();
$class->init();
