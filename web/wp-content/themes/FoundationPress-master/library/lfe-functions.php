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

	echo '<li class="page_item page_item_has_children other-events">';
	echo '<a>Other Events</a>';
	echo '<ul class="children">';
	echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">All Events</a></li>';

	foreach ( $related_events as $p ) {
		echo '<li><a href="' . esc_url( get_permalink( $p['ID'] ) ) . '"><small class="menu-item-pre-text">Related:</small> ' . esc_html( get_post_type( $p['ID'] ) . ' - ' . get_the_title( $p['ID'] ) ) . '</a></li>';
	}

	foreach ( $archive_events as $p ) {
		echo '<li><a href="' . esc_url( get_permalink( $p->ID ) ) . '"><small class="menu-item-pre-text">Archive:</small> ' . esc_html( get_post_type( $p->ID ) . ' - ' . get_the_title( $p->ID ) ) . '</a></li>';
	}

	echo '</ul></li>';
}


/**
 * Theme support
 */
function lfe_setup_theme_supported_features() {

	// Add support for Block Styles.
	add_theme_support( 'align-wide' );

}

add_action( 'after_setup_theme', 'lfe_setup_theme_supported_features' );


/**
 * We need this function to remove links on parent menu items.
 *
 * @param string $args Args for the wp_list_pages funciton.
 */
function lfe_remove_parent_links( $args ) {
	$pages = wp_list_pages( $args );
	$pages = explode( '</li>', $pages );
	$count = 0;
	foreach ( $pages as $page ) {
		if ( strstr( $page, '<ul class=\'children\'>' ) ) {
			$page = explode( '<ul class=\'children\'>', $page );
			$page[0] = preg_replace( '/(<[^>]+) href=".*?"/i', '$1', $page[0] );
			if ( count( $page ) == 3 ) {
				$page[1] = preg_replace( '/(<[^>]+) href=".*?"/i', '$1', $page[1] );
			}
			$page = implode( '<ul class=\'children\'>', $page );
		}
		$pages[ $count ] = $page;
		$count++;
	}
	$pages = implode( '</li>', $pages );
	echo $pages; //phpcs:ignore
}

/**
 * Outputs the Sponsors page for an Event if a Sponsors page exists.
 *
 * @param int $parent_id ID of top parent post of the Event.
 */
function lfe_get_sponsors( $parent_id ) {
	$post_types = lfe_get_post_types();

	$args = array(
		'post_type' => $post_types,
		'post_parent' => $parent_id,
		'name' => 'sponsors',
		'no_found_rows' => true,  // used to improve performance.
		'update_post_meta_cache' => false, // used to improve performance.
		'update_post_term_cache' => false, // used to improve performance.
	);

	$the_query = new WP_Query( $args );

	if ( $the_query->have_posts() ) {
		while ( $the_query->have_posts() ) {
			$the_query->the_post();
			?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<div class="entry-content">
					<?php the_content(); ?>
					<?php edit_post_link( __( '(Edit Sponsors)', 'foundationpress' ), '<span class="edit-link">', '</span>' ); ?>
				</div>
			</article>
			<?php
		}
	}
	wp_reset_postdata(); // Restore original Post Data.

}
