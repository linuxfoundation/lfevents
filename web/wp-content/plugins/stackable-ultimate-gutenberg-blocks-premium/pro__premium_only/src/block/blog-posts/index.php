<?php
/**
 * Post query changes for the `ugb/blog-posts` block.
 *
 * @package Stackable
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once( dirname( __FILE__ ) . '/designs.php' );

if ( ! function_exists( 'sbppq_map' ) ) {
	function sbppq_map( $s ) {
		return (int) $s;
	}
}
if ( ! function_exists( 'sbppq_filter' ) ) {
	function sbppq_filter( $s ) {
		return !! $s;
	}
}

if ( ! function_exists( 'stackable_blog_post_post_query_premium' ) ) {
	function stackable_blog_post_post_query_premium( $post_query, $attributes ) {
		$post_query['offset'] = $attributes['postOffset'];
		$post_query['exclude'] = array_filter( array_map( 'sbppq_map', explode( ',', $attributes['postExclude'] ) ), 'sbppq_filter' );
		$post_query['include'] = array_filter( array_map( 'sbppq_map', explode( ',', $attributes['postInclude'] ) ), 'sbppq_filter' );

		// Taxonomy for CPTs.
		$isCPT = $attributes['postType'] !== 'post' && $attributes['postType'] !== 'page';
		if ( $isCPT && ! empty( $attributes['taxonomyType'] ) && ! empty( $attributes['taxonomy'] ) ) {
			$post_query['tax_query'] = array(
				array(
					'taxonomy' => $attributes['taxonomyType'],
					'field' => 'term_id',
					'terms' => $attributes['taxonomy'],
					'operator' => 'IN',
				),
			);
		}

		return $post_query;
	}
	add_filter( 'stakckable/blog-post/post_query', 'stackable_blog_post_post_query_premium', 10, 2 );
}
