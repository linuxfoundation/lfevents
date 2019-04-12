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
		'post_type' => 'page',
		'post_parent' => 0,
		'tax_query' => array(
			array(
				'taxonomy' => 'lfevent-category',
				'field'    => 'term_id',
				'terms'    => $terms,
			),
		),
	);

	$the_query = new WP_Query( $args );

	if ( $the_query->have_posts() ) {
		echo '<ul>';
		while ( $the_query->have_posts() ) {
			$the_query->the_post();
			echo '<li>' . esc_html( get_the_title() ) . '</li>';
		}
		echo '</ul>';
		/* Restore original Post Data */
		wp_reset_postdata();
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
