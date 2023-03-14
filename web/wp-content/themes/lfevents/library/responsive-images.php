<?php
/**
 * Configure responsive images sizes
 *
 * @package WordPress
 * @subpackage FoundationPress
 * @since FoundationPress 2.6.0
 */

// Add additional image sizes.
add_image_size( 'fp-small', 640 );
add_image_size( 'fp-medium', 1024 );
add_image_size( 'fp-large', 1200 );
add_image_size( 'fp-xlarge', 1920 );

// profile pictures.
add_image_size( 'profile-200', 200, 200, array( 'center', 'center' ) );
add_image_size( 'profile-310', 310, 310, array( 'center', 'center' ) );

// venue location image.
add_image_size( 'venue', 600, 600, false );

/**
 * Register the new image sizes for use in the add media modal in wp-admin.
 *
 * @param array $sizes Comment.
 */
function foundationpress_custom_sizes( $sizes ) {
	return array_merge(
		$sizes,
		array(
			'fp-small'  => __( 'FP Small' ),
			'fp-medium' => __( 'FP Medium' ),
			'fp-large'  => __( 'FP Large' ),
			'fp-xlarge' => __( 'FP XLarge' ),
		)
	);
}
add_filter( 'image_size_names_choose', 'foundationpress_custom_sizes' );

/**
 * Remove inline width and height attributes for post thumbnails.
 *
 * @param string $html Comment.
 * @param int    $post_id Comment.
 * @param int    $post_image_id Comment.
 */
function remove_thumbnail_dimensions( $html, $post_id, $post_image_id ) {
	if ( ! strpos( $html, 'attachment-shop_single' ) ) {
		$html = preg_replace( '/^(width|height)=\"\d*\"\s/', '', $html );
	}
	return $html;
}
add_filter( 'post_thumbnail_html', 'remove_thumbnail_dimensions', 10, 3 );
