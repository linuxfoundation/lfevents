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
 * Gets related LFEvents.
 *
 * @param int $post_id ID of Event to get related Events.
 * @return array
 */
function lfe_get_related_events( $post_id ) {
	$related_events = [];

	return $related_events;
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
