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
		<article id="post-<?php the_ID(); ?>" class="cell medium-6 large-4 callout large-margin-bottom">

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
					<?php get_template_part( 'template-parts/svg/calendar' ); ?>
					<?php echo esc_html( jb_verbose_date_range( $dt_date_start, $dt_date_end ) ); ?>
				</span>

				<span class="country">
					<?php get_template_part( 'template-parts/svg/map-marker' ); ?>
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
						<?php get_template_part( 'template-parts/svg/bullhorn' ); ?>

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
	if ( -1 == currentUrl.indexOf( 'calendar/archive' ) ) {
		//we are on the event-calendar page.
		newUrl = currentUrl.replace( 'calendar', 'calendar/archive' );
		$( '#switch-archive-view' ).attr( "href", newUrl );
		$( '#switch-archive-view' ).html( 'View Past Events' );
	} else {
		//we are on the event-calendar/archive page.
		newUrl = currentUrl.replace( 'calendar/archive', 'calendar' );
		$( '#switch-archive-view' ).attr( "href", newUrl );
		$( '#switch-archive-view' ).html( 'View Upcoming Events' );
	}
});
</script>
