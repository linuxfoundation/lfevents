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
 *
 * @param int $parent_id ID of top parent post of the Event.
 *
 * @return array
 */
function lfe_get_related_events( $parent_id ) {
	$related_events = [];

	$terms = wp_get_post_terms( $parent_id, 'lfevent-category', array( 'fields' => 'ids' ) );

	$args = array(
		'post_type'   => 'page',
		'post_parent' => 0,
		'no_found_rows' => true,  // used to improve performance.
		'update_post_meta_cache' => false, // used to improve performance.
		'update_post_term_cache' => false, // used to improve performance.
		'post__not_in' => array( $parent_id ), // ignores current post.
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
		while ( $the_query->have_posts() ) {
			$the_query->the_post();
			$related_events[] = array( 'ID' => get_the_ID() );
		}
	}
	wp_reset_postdata(); // Restore original Post Data.

	return $related_events;

}

/**
 * Gets all archives of a particular LFEvent.
 *
 * @param int $parent_id ID of top parent post of the Event.
 *
 * @return array
 */
function lfe_get_archive( $parent_id ) {
	global $wpdb;
	$parent_post = get_post( $parent_id );

	$myposts = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT * FROM $wpdb->posts
			WHERE (post_type like %s
			OR post_type = 'page')
			AND post_parent = 0
			AND post_status = 'Publish'
			AND post_name = %s
			AND id <> %d
			ORDER BY post_type ASC",
			'lfevent%',
			$parent_post->post_name,
			$parent_id
		)
	);

	return $myposts;
}

/**
 * Generates the "Other Events" menu item.
 *
 * @param int $parent_id ID of top parent post of the Event.
 */
function lfe_get_other_events( $parent_id ) {
	$related_events = lfe_get_related_events( $parent_id );
	$archive_events = lfe_get_archive( $parent_id );

	if ( $related_events || $archive_events ) {
		echo '<li class="page_item page_item_has_children">';
	} else {
		echo '<li class="page_item">';
	}

	echo '<a href="' . esc_url( home_url( '/' ) ) . '">Other Events</a>';

	if ( $related_events || $archive_events ) {
		echo '<ul class="children">';

		foreach ( $related_events as $p ) {
			echo '<li><a href="' . esc_url( get_permalink( $p['ID'] ) ) . '">Related: ' . esc_html( get_post_type( $p['ID'] ) . ' - ' . get_the_title( $p['ID'] ) ) . '</a></li>';
		}

		foreach ( $archive_events as $p ) {
			echo '<li><a href="' . esc_url( get_permalink( $p->ID ) ) . '">Archive: ' . esc_html( get_post_type( $p->ID ) . ' - ' . get_the_title( $p->ID ) ) . '</a></li>';
		}

		echo '</ul>';
	}

	echo '</li>';
}


/**
 * Theme support
 */
function lfe_setup_theme_supported_features() {

	// Add support for Block Styles.
	add_theme_support( 'align-wide' );

}

add_action( 'after_setup_theme', 'lfe_setup_theme_supported_features' );
