<?php
/**
 * LFEvents helper functions
 *
 * @package WordPress
 * @subpackage Twenty_Nineteen
 * @since 1.0.0
 */

/**
 * Gets all post types currently used for LFEvents.
 *
 * @return array
 */
function lfe_get_post_types() {
	$post_types   = [ 'page' ];
	$current_year = date( 'Y' );

	for ( $x = 2019; $x <= $current_year; $x++ ) {
		$post_types[] = 'lfevent' . $x;
	}
	return $post_types;
}

/**
 * Gets related LFEvents for current post.  Only returns Events for the current year.
 */
function lfe_get_related_events() {
	global $post;
	$terms = wp_get_post_terms( $post->ID, 'lfevent-category', array( 'fields' => 'ids' ) );

	$args = array(
		'post_type'   => 'page',
		'post_parent' => 0,
		'tax_query'   => array(
			array(
				'taxonomy' => 'lfevent-category',
				'field'    => 'term_id',
				'terms'    => $terms,
			),
		),
	);

	$the_query = new WP_Query( $args );

	if ( $the_query->have_posts() ) {
		echo '<li class="page_item page_item_has_children">';
		echo '<a href="' . esc_url( home_url( '/' ) ) . '">Other Events</a>';
		echo '<ul class="children">';

		while ( $the_query->have_posts() ) {
			$the_query->the_post();
			echo '<li><a href="' . esc_url( get_the_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></li>';
		}

		echo '</ul>';
		echo '</li>';

		wp_reset_postdata(); // Restore original Post Data.
	} else {
		echo '<li class="page_item"><a href="' . esc_url( home_url( '/' ) ) . '">Other Events</a></li>';
	}

}

/**
 * Gets all archives of a particular LFEvent.
 *
 * @param text $event_slug Slug of Event to get archive.
 * @return array
 */
function lfe_get_archive( $event_slug ) {
	global $wpdb;
	$myposts = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT * FROM $wpdb->posts
			WHERE (post_type like %s
			OR post_type = 'page')
			AND post_parent = 0
			AND post_status = 'Publish'
			AND post_name = %s
			ORDER BY post_type ASC",
			'lfevent%',
			$event_slug
		)
	);

	return $myposts;
}


/**
 * Theme support
 */
function lfe_setup_theme_supported_features() {

	// Add support for Block Styles.
	add_theme_support( 'align-wide' );

}

add_action( 'after_setup_theme', 'lfe_setup_theme_supported_features' );
