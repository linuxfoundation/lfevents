<?php
/**
 * Staff Listing Shortcode
 *
 * @package WordPress
 * @subpackage lf-theme
 * @since 1.0.0
 */

/**
 * Add shortcode.
 *
 * @param array $atts Attributes.
 */
function add_staff_shortcode( $atts ) {

	$meta_key = 'lfes_staff_order';

	$query = new WP_Query(
		array(
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'post_type'              => 'lfe_staff',
			'post_status'            => 'publish',
			'posts_per_page'         => 500,

			'meta_query'     => array(
				'relation' => 'OR',
				array(
					'key'     => $meta_key,
					'compare' => 'NOT EXISTS',
				),
				array(
					'relation' => 'OR',
					array(
						'key'   => $meta_key,
						'value' => 'on',
					),
					array(
						'key'     => $meta_key,
						'value'   => 'on',
						'compare' => '!=',
					),
				),
			),
			'orderby'        => array(
				'meta_value_num' => 'DESC',
				'title'          => 'ASC',
			),
		)
	);

	if ( ! $query->have_posts() ) {
		return;
	}

	ob_start();

	echo '<div class="wp-block-columns is-style-feature-grid">';
	while ( $query->have_posts() ) {
		$query->the_post();
		$id    = get_the_ID();
		$title = get_post_meta( $id, 'lfes_staff_title', true );
		echo '<div class="wp-block-column has-light-gray-background-color has-background">';
		echo get_the_post_thumbnail( $id, 'profile-200', array( 'loading' => 'lazy' ) );
		echo '<h3>' . esc_html( get_the_title() ) . '</h3>';
		echo '<p>' . esc_html( $title ) . '</p>';
		echo '</div>';
	}
	echo '</div>';

	$block_content = ob_get_clean();
	return $block_content;
}
add_shortcode( 'staff', 'add_staff_shortcode' );
