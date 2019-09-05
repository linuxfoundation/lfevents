<?php
/**
 * LFEvents helper functions
 *
 * @package WordPress
 * @subpackage Twenty_Nineteen
 * @since 1.0.0
 */

/**
 * Gets related LFEvents for current post.  Only returns Events for the current year.
 *
 * @param int $parent_id ID of top parent post of the Event.
 *
 * @return array
 */
function lfe_get_related_events( $parent_id ) {
	$related_events = [];

	$term = wp_get_post_terms( $parent_id, 'lfevent-category', array( 'fields' => 'ids' ) );

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
				'terms'    => $term[0],
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
 * @param int    $parent_id ID of top parent post of the Event.
 * @param string $background_style sets the solid or gradient background color.
 * @param string $menu_text_color color of the txt on the topnav.
 */
function lfe_get_other_events( $parent_id, $background_style, $menu_text_color ) {
	$related_events = lfe_get_related_events( $parent_id );

	echo '<li class="page_item page_item_has_children other-events">';
	echo '<a>View All Events</a>';
	echo '<ul class="children" style="' . esc_html( $background_style ) . '">';
	echo '<li><a href="' . esc_url( home_url( '/' ) ) . '"><img src="' . get_stylesheet_directory_uri() . '/dist/assets/images/' . foundationpress_asset_path( 'logo_lfevents_' . $menu_text_color . '.svg' ) . '"><span class="subtext">All Upcoming Events</span></a></li>'; //phpcs:ignore

	foreach ( $related_events as $p ) {
		$logo = get_post_meta( $p['ID'], 'lfes_' . $menu_text_color . '_logo', true );
		if ( $logo ) {
			$event_link_content = '<img src="' . wp_get_attachment_url( $logo ) . '" alt="' . get_the_title( $p['ID'] ) . '">';
		} else {
			$event_link_content = get_the_title( $p['ID'] );
		}

		echo '<li><a href="' . esc_url( lfe_get_event_url( $p['ID'] ) ) . '">' . $event_link_content . '</a></li>'; //phpcs:ignore
	}

	$term = wp_get_post_terms( $parent_id, 'lfevent-category', array( 'fields' => 'all' ) );

	echo '<li><a href="' . esc_url( home_url( '/about/calendar/archive/' ) ) . '"><img src="' . get_stylesheet_directory_uri() . '/dist/assets/images/' . foundationpress_asset_path( 'logo_lfevents_' . $menu_text_color . '.svg' ) . '"><span class="subtext">Past Events</span></a></li>'; //phpcs:ignore

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
 * Returns markup for child pages for the Event menu.
 *
 * @param int    $parent_id Parent ID for Event.
 * @param string $post_type Post type for Event.
 * @param string $background_style sets the background color.
 */
function lfe_get_event_menu( $parent_id, $post_type, $background_style ) {
	global $wpdb, $post;

	// first find which pages we need to exclude.
	$exclude = $wpdb->get_results( $wpdb->prepare( "select post_id from $wpdb->postmeta left join $wpdb->posts on post_id = id where meta_key = 'lfes_hide_from_menu' and meta_value = 1 and post_type = %s;", $post->post_type ), ARRAY_A );
	$exclude_ids = '';
	foreach ( $exclude as $ex ) {
		$exclude_ids .= $ex['post_id'] . ',';
	}

	// then get the pages we need.
	$args = array(
		'child_of'     => $parent_id,
		'sort_order'   => 'ASC',
		'sort_column'  => 'menu_order',
		'hierarchical' => 1,
		'title_li'     => '',
		'exclude'      => $exclude_ids,
		'post_type'    => $post_type,
		'post_status'  => 'publish',
		'echo'         => false,
	);
	$pages = wp_list_pages( $args );
	$pages = explode( '</li>', $pages );
	$count = 0;

	// now we remove the hyperlink for elements who have children.
	foreach ( $pages as $page ) {
		if ( strstr( $page, '<ul class=\'children\'>' ) ) {
			$page = explode( '<ul class=\'children\'>', $page );
			$page[0] = preg_replace( '/(<[^>]+) href=".*?"/i', '$1 href="#"', $page[0] );
			if ( count( $page ) == 3 ) {
				$page[1] = preg_replace( '/(<[^>]+) href=".*?"/i', '$1 href="#"', $page[1] );
			}
			$page = implode( '<ul class=\'children\' style=\'' . esc_html( $background_style ) . '\'>', $page );
		}
		$pages[ $count ] = $page;
		$count++;
	}
	$pages = implode( '</li>', $pages );

	return $pages; //phpcs:ignore
}

/**
 * Outputs the Sponsors page for an Event if a Sponsors page exists.
 *
 * @param int $parent_id ID of top parent post of the Event.
 */
function lfe_get_sponsors( $parent_id ) {
	global $post;
	if ( 'sponsors' === $post->post_name ) {
		return;
	}

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

/**
 * Enqueues scripts for lfe stuff.
 */
function lfe_scripts() {

	// https://instant.page/.
	wp_enqueue_script( 'instantpage', get_stylesheet_directory_uri() . '/dist/assets/js/' . foundationpress_asset_path( 'instantpage-1.2.2.js' ), array(), '1.2.2', true );

	$chinese_domains = "'www.lfasiallc.com', 'events19.lfasiallc.com', 'events.linuxfoundation.cn', 'events19.linuxfoundation.cn'";
	$current_domain = parse_url( home_url(), PHP_URL_HOST );
	if ( strpos( $chinese_domains, $current_domain ) ) {
		// scripts for Chinese-audience sites.
		wp_enqueue_script( 'lfe_china', get_stylesheet_directory_uri() . '/dist/assets/js/' . foundationpress_asset_path( 'china.js' ), array(), '1.2.2', true );
	}

}

add_action( 'wp_enqueue_scripts', 'lfe_scripts' );

/**
 * Adds the module tag to the instant.page script.
 *
 * @param string $tag the tag.
 * @param string $handle the handle.
 */
function lfe_instantpage_script_loader_tag( $tag, $handle ) {
	if ( 'instantpage' === $handle ) {
		$tag = str_replace( 'text/javascript', 'module', $tag );
	}
	return $tag;
}

add_filter( 'script_loader_tag', 'lfe_instantpage_script_loader_tag', 10, 2 );

/**
 * Removes the annoying Ultimate Blocks menu in the admin
 */
function lfe_custom_menu_page_removing() {
	remove_menu_page( 'ultimate-blocks-settings' );
	remove_menu_page( 'edit-comments.php' );
}
add_action( 'admin_menu', 'lfe_custom_menu_page_removing' );

/**
 * Inserts Google Analytics code on live sites.
 */
function lfe_insert_google_analytics() {
	$domains = "'events.linuxfoundation.org', 'www.lfasiallc.com', 'bagevent.com', 'www.cvent.com', 'events19.linuxfoundation.org', 'events19.lfasiallc.com', 'events.linuxfoundation.cn', 'events19.linuxfoundation.cn'";
	$current_domain = parse_url( home_url(), PHP_URL_HOST );
	$analytics_code = <<<EOD
<!-- Google Analytics -->
		<script>
		window.ga=window.ga||function(){(ga.q=ga.q||[]).push(arguments)};ga.l=+new Date;
		ga('create', 'UA-831873-5', 'auto', {allowLinker: true});
		ga('require', 'linker');
		ga('linker:autoLink', [$domains] );
		ga('send', 'pageview');
		</script>
EOD;
	$analytics_code .= "<script async src='https://www.google-analytics.com/analytics.js'></script>\n\t\t<!-- End Google Analytics -->\n"; //phpcs:ignore

	if ( strpos( $domains, $current_domain ) ) {
		// this is a live site so output the analytics code.
		echo $analytics_code; //phpcs:ignore
	}
}

/**
 * Makes the date pretty.  Adapted from https://9seeds.com/pretty-php-date-ranges/.
 *
 * @param datetime $start_date The start date.
 * @param datetime $end_date The end date.
 */
function jb_verbose_date_range( $start_date = '', $end_date = '' ) {

	$date_range = '';

	// If only one date, or dates are the same set to FULL verbose date.
	if ( empty( $start_date ) || empty( $end_date ) || ( $start_date->format( 'MjY' ) == $end_date->format( 'MjY' ) ) ) { // FjY == accounts for same day, different time.
		$start_date_pretty = $start_date->format( 'M jS, Y' );
		$end_date_pretty = $end_date->format( 'M jS, Y' );
	} else {
		 // Setup basic dates.
		$start_date_pretty = $start_date->format( 'M j' );
		$end_date_pretty = $end_date->format( 'jS, Y' );
		// If years differ add suffix and year to start_date.
		if ( $start_date->format( 'Y' ) != $end_date->format( 'Y' ) ) {
			$start_date_pretty .= $start_date->format( 'S, Y' );
		}

		// If months differ add suffix and year to end_date.
		if ( $start_date->format( 'M' ) != $end_date->format( 'M' ) ) {
			$end_date_pretty = $end_date->format( 'M ' ) . $end_date_pretty;
		}
	}

	// build date_range return string.
	if ( ! empty( $start_date ) ) {
		  $date_range .= $start_date_pretty;
	}

	// check if there is an end date and append if not identical.
	if ( ! empty( $end_date ) ) {
		if ( $end_date_pretty != $start_date_pretty ) {
			  $date_range .= '–' . $end_date_pretty;
		}
	}
	return $date_range;
}


/**
 * Changes the ellipses after the excerpt.
 *
 * @param string $more more text.
 */
function new_excerpt_more( $more ) {
	return '<span class="excerpt-ellipses">&hellip;</span>';
}
add_filter( 'excerpt_more', 'new_excerpt_more' );

/**
 * Inserts structured data into Event head according to https://developers.google.com/search/docs/data-types/event.
 * Only does this for the topmost Event page.
 */
function lfe_insert_structured_data() {
	global $post;

	if ( $post->post_parent || 'page' != $post->post_type ) {
		return;
	}

	$dt_date_start = new DateTime( get_post_meta( $post->ID, 'lfes_date_start', true ) );
	$dt_date_end = new DateTime( get_post_meta( $post->ID, 'lfes_date_end', true ) );
	$country = wp_get_post_terms( $post->ID, 'lfevent-country' );
	if ( $country ) {
		$country = $country[0]->name;
	}

	$out = '';

	$out .= '<script type="application/ld+json">';
	$out .= '{';
	$out .= '"@context": "http://schema.org/",';
	$out .= '"@type": "Event",';
	$out .= '"name": "' . esc_html( $post->post_title ) . '",';
	$out .= '"startDate": "' . $dt_date_start->format( 'Y-m-d' ) . '",';
	$out .= '"endDate": "' . $dt_date_end->format( 'Y-m-d' ) . '",';
	$out .= '"location": {';
	$out .= '  "@type": "Place",';
	$out .= '  "name": "' . esc_html( get_post_meta( $post->ID, 'lfes_venue', true ) ) . '",';
	$out .= '  "address": {';
	$out .= '	"@type": "PostalAddress",';
	$out .= '	"streetAddress": "' . esc_html( get_post_meta( $post->ID, 'lfes_street_address', true ) ) . '",';
	$out .= '	"addressLocality": "' . esc_html( get_post_meta( $post->ID, 'lfes_city', true ) ) . '",';
	$out .= '	"postalCode": "' . esc_html( get_post_meta( $post->ID, 'lfes_postal_code', true ) ) . '",';
	$out .= '	"addressRegion": "' . esc_html( get_post_meta( $post->ID, 'lfes_region', true ) ) . '",';
	$out .= '	"addressCountry": "' . esc_html( $country ) . '"';
	$out .= '  }';
	$out .= '},';
	$out .= '	"image": [ ';
	$out .= '	  "' . esc_html( get_the_post_thumbnail_url() ) . '"';
	$out .= '	 ],';
	$out .= '	"description": "' . esc_html( get_post_meta( $post->ID, 'lfes_description', true ) ) . '"';
	$out .= '}';
	$out .= '</script>';

	echo $out; //phpcs:ignore
}


/**
 * Wraps the logic for redirecting to 3rd-party Event sites.
 *
 * @param int $post_id Post id.
 */
function lfe_get_event_url( $post_id ) {
	$url = get_post_meta( $post_id, 'lfes_external_url', true );
	if ( $url ) {
		return $url;
	} else {
		return get_permalink( $post_id );
	}
}

/**
 * Sets the except length.
 *
 * @param int $length Number of words.
 */
function custom_excerpt_length( $length ) {
	return 18;
}
add_filter( 'excerpt_length', 'custom_excerpt_length', 999 );

/**
 * Exposes the meta data of a post that results from import of a feed item.
 *
 * @param array          $meta The original meta data array.
 * @param int            $post_id The ID of the post, which is being processed.
 * @param int            $feed_source The ID of the feed source post, which the current item is being imported by.
 * @param SimplePie_Item $feed_item The feed item, which is being imported.
 * @return array The new meta data array.
 */
function lfe_custom_post_meta( $meta, $post_id, $feed_source, $feed_item ) {
	if ( 243 === $feed_source || 1180 === $feed_source ) {
		$dt_date_start = get_post_meta( $post_id, 'lfes_community_date_start', true );
		if ( $dt_date_start ) {
			$dt_date_start = new DateTime( $dt_date_start );
			update_post_meta( $post_id, 'lfes_community_date_start', $dt_date_start->format( 'Y/m/d' ) );
		}
		if ( $dt_date_end ) {
			$dt_date_end = new DateTime( $dt_date_end );
			update_post_meta( $post_id, 'lfes_community_date_start', $dt_date_end->format( 'Y/m/d' ) );
		}
	}

	return $meta;
}
add_filter( 'wprss_ftp_post_meta', 'lfe_custom_post_meta', 10, 4 );
