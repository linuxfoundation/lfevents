<?php
/**
 *  Class for registering Conditional Blocks.
 *
 * @package conditional-blocks-pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class for registering blocks.
 */
class Conditional_Blocks_Register_Blocks {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Late priority to make sure all blocks have been registered first.
		add_action( 'wp_loaded', array( $this, 'register_for_server_side_render' ), 999 );
		add_filter( 'rest_pre_dispatch', array( $this, 'maybe_remove_conditional_blocks_attributes' ), 10, 3 );
	}

	/**
	 * Register the conditional blocks block attribute for each blocks
	 * This allows us to bypass the REST API error for server side rendered block.
	 * We are keeping this until this issue is resolved https://github.com/WordPress/gutenberg/issues/16850
	 */
	public function register_for_server_side_render() {

		$registered_blocks = WP_Block_Type_Registry::get_instance()->get_all_registered();

		foreach ( $registered_blocks as $name => $block ) {

			// Keep legacy conditions. We need them for converting.
			$block->attributes['conditionalBlocksAttributes'] = array(
				'type' => 'object',
				'default' => array(),
			);

			$block->attributes['conditionalBlocks'] = array(
				'type' => 'array',
				'default' => []
			);
		}
	}

	/**
	 * Fixes issue where block attributes are passed onto other REST API functions such as WP_Query.
	 * This will remove the conditionalBlocksAttributes from all requests that are not for block render.
	 *
	 * @param mixed  $result  Response to replace the requested version with.
	 * @param object $server  Server instance.
	 * @param object $request Request used to generate the response.
	 *
	 * @return array Returns updated results.
	 */
	public function maybe_remove_conditional_blocks_attributes( $result, $server, $request ) {

		if ( strpos( $request->get_route(), '/wp/v2/block-renderer' ) !== false ) {

			if ( isset( $request['attributes'] ) && ( isset( $request['attributes']['conditionalBlocksAttributes'] ) || isset( $request['attributes']['conditionalBlocks'] ) ) ) {
				$attributes = $request['attributes'];
				unset( $attributes['conditionalBlocksAttributes'] );
				unset( $attributes['conditionalBlocks'] );
				$request['attributes'] = $attributes; // Changes the $request object.
			}
		}

		return $result;
	}
}

new Conditional_Blocks_Register_Blocks();
