<?php
/**
 * Search & Filter Pro Results Template
 *
 * @package   Search_Filter
 * @author    Ross Morsali
 * @link      https://searchandfilter.com
 * @copyright 2018 Search & Filter
 *
 * Note: these templates are not full page templates, rather
 * just an encaspulation of the your results loop which should
 * be inserted in to other pages by using a shortcode - think
 * of it as a template part
 *
 * This template is an absolute base example showing you what
 * you can do, for more customisation see the WordPress docs
 * and using template tags -
 *
 * http://codex.wordpress.org/Template_Tags
 */

global $post;

echo '<div class="grid-x grid-margin-x">';

if ( $query->have_posts() ) {
	$y = 0;
	$month = 0;
	while ( $query->have_posts() ) {
		$query->the_post();
		if ( 'page' == $post->post_type ) {
			$is_upcoming_events = true;
		} else {
			$is_upcoming_events = false;
		}
		$dt_date_start = new DateTime( get_post_meta( $post->ID, 'lfes_date_start', true ) );
		$dt_date_end = new DateTime( get_post_meta( $post->ID, 'lfes_date_end', true ) );
		$cfp_date_start = get_post_meta( $post->ID, 'lfes_cfp_date_start', true );
		$cfp_date_end = get_post_meta( $post->ID, 'lfes_cfp_date_end', true );
		$dt_cfp_date_start = new DateTime( $cfp_date_start );
		$dt_cfp_date_end = new DateTime( $cfp_date_end );
		$cfp_active = get_post_meta( $post->ID, 'lfes_cfp_active', true );

		if ( $is_upcoming_events ) {
			if ( ( 0 == $y ) || ( $y < (int) $dt_date_start->format( 'Y' ) ) ) {
				$y = (int) $dt_date_start->format( 'Y' );
				echo '<h2 class="cell event-calendar-year">' . esc_html( $y ) . '</h2>';
				$month = (int) $dt_date_start->format( 'm' );
				echo '<h3 class="cell event-calendar-month">' . esc_html( $dt_date_start->format( 'F' ) ) . '</h3>';
			} elseif ( ( 0 == $month ) || ( $month < (int) $dt_date_start->format( 'm' ) ) ) {
				$month = (int) $dt_date_start->format( 'm' );
				echo '<h3 class="cell event-calendar-month">' . esc_html( $dt_date_start->format( 'F' ) ) . '</h3>';
			}
		} else {
			if ( ( 0 == $y ) || ( $y > (int) $dt_date_start->format( 'Y' ) ) ) {
				$y = (int) $dt_date_start->format( 'Y' );
				echo '<h2 class="cell event-calendar-year">' . esc_html( $y ) . '</h2>';
				$month = (int) $dt_date_start->format( 'm' );
				echo '<h3 class="cell event-calendar-month">' . esc_html( $dt_date_start->format( 'F' ) ) . '</h3>';
			} elseif ( ( 0 == $month ) || ( $month > (int) $dt_date_start->format( 'm' ) ) ) {
				$month = (int) $dt_date_start->format( 'm' );
				echo '<h3 class="cell event-calendar-month">' . esc_html( $dt_date_start->format( 'F' ) ) . '</h3>';
			}
		}
		?>
		<article id="post-<?php the_ID(); ?>" class="cell medium-6 callout large-margin-bottom">

			<h5 class="medium-margin-right small-margin-bottom line-height-tight">
				<strong>
					<?php
					if ( 'publish' == $post->post_status ) {
						echo '<a href="' . lfe_get_event_url( $post->ID ) . '">' . get_the_title() . '</a>'; //phpcs:ignore
					} else {
						the_title();
					}
					?>
				</strong>
			</h5>

			<p class="event-meta text-small small-margin-bottom">

				<span class="date small-margin-right">
					<svg aria-hidden="true" focusable="false" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="icon--inline"><g class="fa-group"><path fill="currentColor" d="M0 192v272a48 48 0 0 0 48 48h352a48 48 0 0 0 48-48V192zm128 244a12 12 0 0 1-12 12H76a12 12 0 0 1-12-12v-40a12 12 0 0 1 12-12h40a12 12 0 0 1 12 12zm0-128a12 12 0 0 1-12 12H76a12 12 0 0 1-12-12v-40a12 12 0 0 1 12-12h40a12 12 0 0 1 12 12zm128 128a12 12 0 0 1-12 12h-40a12 12 0 0 1-12-12v-40a12 12 0 0 1 12-12h40a12 12 0 0 1 12 12zm0-128a12 12 0 0 1-12 12h-40a12 12 0 0 1-12-12v-40a12 12 0 0 1 12-12h40a12 12 0 0 1 12 12zm128 128a12 12 0 0 1-12 12h-40a12 12 0 0 1-12-12v-40a12 12 0 0 1 12-12h40a12 12 0 0 1 12 12zm0-128a12 12 0 0 1-12 12h-40a12 12 0 0 1-12-12v-40a12 12 0 0 1 12-12h40a12 12 0 0 1 12 12z" class="fa-secondary"></path><path fill="currentColor" d="M448 112v48H0v-48a48 48 0 0 1 48-48h48V16a16 16 0 0 1 16-16h32a16 16 0 0 1 16 16v48h128V16a16 16 0 0 1 16-16h32a16 16 0 0 1 16 16v48h48a48 48 0 0 1 48 48z" class="fa-primary"></path></g></svg>
					<?php echo esc_html( jb_verbose_date_range( $dt_date_start, $dt_date_end ) ); ?>
				</span>

				<span class="country">
					<svg aria-hidden="true" focusable="false" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" class="icon--inline"><path fill="currentColor" d="M172.268 501.67C26.97 291.031 0 269.413 0 192 0 85.961 85.961 0 192 0s192 85.961 192 192c0 77.413-26.97 99.031-172.268 309.67-9.535 13.774-29.93 13.773-39.464 0zM192 272c44.183 0 80-35.817 80-80s-35.817-80-80-80-80 35.817-80 80 35.817 80 80 80z" class=""></path></svg>
					<?php
					$country = wp_get_post_terms( $post->ID, 'lfevent-country' );
					if ( $country ) {
						$country = $country[0]->name;
						$city = get_post_meta( $post->ID, 'lfes_city', true );
						if ( $city ) {
							$city .= ', ';
						}
						echo esc_html( $city ) . esc_html( $country );
					}
					?>
				</span>
			</p>

			<?php
			if ( $is_upcoming_events ) {
			?>
			<p class="event-meta text-small no-margin">
				<span class="cfp">
					<svg aria-hidden="true" focusable="false" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" class="icon--inline"><path fill="currentColor" d="M576 240c0-23.63-12.95-44.04-32-55.12V32.01C544 23.26 537.02 0 512 0c-7.12 0-14.19 2.38-19.98 7.02l-85.03 68.03C364.28 109.19 310.66 128 256 128H64c-35.35 0-64 28.65-64 64v96c0 35.35 28.65 64 64 64h33.7c-1.39 10.48-2.18 21.14-2.18 32 0 39.77 9.26 77.35 25.56 110.94 5.19 10.69 16.52 17.06 28.4 17.06h74.28c26.05 0 41.69-29.84 25.9-50.56-16.4-21.52-26.15-48.36-26.15-77.44 0-11.11 1.62-21.79 4.41-32H256c54.66 0 108.28 18.81 150.98 52.95l85.03 68.03a32.023 32.023 0 0 0 19.98 7.02c24.92 0 32-22.78 32-32V295.13C563.05 284.04 576 263.63 576 240zm-96 141.42l-33.05-26.44C392.95 311.78 325.12 288 256 288v-96c69.12 0 136.95-23.78 190.95-66.98L480 98.58v282.84z" class=""></path></svg>

					CFP Status:
					<span class="text-weight-normal">
						<?php
						if ( '0' === $cfp_active ) {
							echo 'No Call for Proposals';
						} elseif ( ! ( $cfp_date_start ) ) {
							echo 'Details Coming Soon';
						} elseif ( strtotime( $cfp_date_end ) < time() ) {
							echo 'Closed';
						} elseif ( strtotime( $cfp_date_end ) > time() && strtotime( $cfp_date_start ) < time() ) {
							echo 'Closes ' . esc_html( $dt_cfp_date_end->format( 'l, M j, Y' ) );
						} elseif ( strtotime( $cfp_date_end ) > time() && strtotime( $cfp_date_start ) > time() ) {
							echo 'Opens ' . esc_html( $dt_cfp_date_start->format( 'l, M j, Y' ) );
						}
						?>
					</span>
				</span>
			</p>
			<?php
			}
			?>

		</article>
		<?php
	}
} else {
	echo 'No Results Found';
}
echo '</div>';
?>

<script>
// this controls the appearence and behavior of the link to navigate between upcoming and past events.
$( document ).ready( function() {
	if ( $( '#switch-archive-view' ).length === 0 ) {
		$( '#event-calendar-header' ).append( '<a class="button" id="switch-archive-view" href="#"></a>' );
	}
	var currentUrl = $( location ).attr( 'href' )
	if ( -1 == currentUrl.indexOf( 'events-calendar/archive' ) ) {
		//we are on the event-calendar page.
		newUrl = currentUrl.replace( 'events-calendar', 'events-calendar/archive' );
		$( '#switch-archive-view' ).attr( "href", newUrl );
		$( '#switch-archive-view' ).html( 'View Past Events' );
	} else {
		//we are on the event-calendar/archive page.
		newUrl = currentUrl.replace( 'events-calendar/archive', 'events-calendar' );
		$( '#switch-archive-view' ).attr( "href", newUrl );
		$( '#switch-archive-view' ).html( 'View Upcoming Events' );
	}
});
</script>
