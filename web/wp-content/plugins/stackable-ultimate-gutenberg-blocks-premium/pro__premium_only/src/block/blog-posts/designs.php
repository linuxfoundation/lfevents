<?php
/**
 * Save output for the premium designs of the `ugb/blog-posts` block.
 *
 * @package Stackable
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'stackable_blog_posts_design_image_card' ) ) {
	/**
	 * Create our own save markup for image card layout.
	 *
	 * @param string $post_markup New markup.
	 * @param array $attributes Block attributes.
	 * @param array $comps Block component markups.
	 * @param integer $index Index
	 *
	 * @return string New markup.
	 */
	function stackable_blog_posts_design_image_card( $post_markup, $attributes, $comps, $index ) {
		if ( $attributes['design'] !== 'image-card' ) {
			return $post_markup;
		}

		$defaults = array(
			'contentOrder' => 'meta,title,excerpt,category',
		);
		$attributes = stackable_attributes_default( $attributes, $defaults );

		$content = array(
			'meta' => $attributes['showMeta'] ? $comps['meta'] : '',
			'title' => $attributes['showTitle'] ? $comps['title'] : '',
			'excerpt' => $attributes['showExcerpt'] || $attributes['showReadmore'] ?
				( $attributes['showExcerpt'] ? $comps['excerpt'] : '' ) . ( $attributes['showReadmore'] ? $comps['readmore'] : '' ) :
				'',
			'category' => $attributes['showCategory'] ? $comps['category'] : '',
		);

		// Default meta,title,excerpt,category.
		$comps_to_display = explode( ',', $attributes['contentOrder'] );

		return sprintf(
			'<article class="%s"><div class="ugb-blog-posts__header">%s%s%s</div><div class="%s">%s%s</div></article>',
			$comps['itemClasses'],
			$attributes['showImage'] ? $comps['featuredImageBackground'] : '',
			$content[ $comps_to_display[ 0 ] ],
			$content[ $comps_to_display[ 1 ] ],
			$comps['contentClasses'],
			$content[ $comps_to_display[ 2 ] ],
			$content[ $comps_to_display[ 3 ] ]
		);
	}
	add_filter( 'stackable/blog-posts/edit.output', 'stackable_blog_posts_design_image_card', 10, 4 );
}
