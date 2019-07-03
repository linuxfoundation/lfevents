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

echo '<a id="switch-archive-view" href="#">Show Past Events</a>';
echo '<div class="grid-x grid-margin-x large-margin-top">';

if ( $query->have_posts() ) {
	$y = 0;
	$month = 0;
	while ( $query->have_posts() ) {
		$query->the_post();
		$dt_date_start = new DateTime( get_post_meta( $post->ID, 'lfes_date_start', true ) );
		$dt_date_end = new DateTime( get_post_meta( $post->ID, 'lfes_date_end', true ) );
		$cfp_date_start = get_post_meta( $post->ID, 'lfes_cfp_date_start', true );
		$cfp_date_end = get_post_meta( $post->ID, 'lfes_cfp_date_end', true );
		$dt_cfp_date_start = new DateTime( $cfp_date_start );
		$dt_cfp_date_end = new DateTime( $cfp_date_end );
		$cfp_active = get_post_meta( $post->ID, 'lfes_cfp_active', true );

		if ( ( 0 == $y ) || ( $y < (int) $dt_date_start->format( 'Y' ) ) ) {
			$y = (int) $dt_date_start->format( 'Y' );
			echo '<div class="cell"><h2 class="h4 header-bg-white-smoke medium-padding-left small-padding-top small-padding-bottom"><strong>' . esc_html( $y ) . '</strong></h2></div>';
			$month = (int) $dt_date_start->format( 'm' );
			echo '<div class="cell"><h3 class="h4">' . esc_html( $dt_date_start->format( 'F' ) ) . '</h3></div>';
		} elseif ( ( 0 == $month ) || ( $month < (int) $dt_date_start->format( 'm' ) ) ) {
			$month = (int) $dt_date_start->format( 'm' );
			echo '<div class="cell"><h3 class="h4">' . esc_html( $dt_date_start->format( 'F' ) ) . '</h3></div>';
		}
		?>
		<article id="post-<?php the_ID(); ?>" class="cell medium-6 large-4">
			<div class="callout">
				<h4 class="h5 no-margin"><strong>
				<?php
				if ( 'publish' == $post->post_status ) {
					echo '<a href="' . get_the_permalink() . '">' . get_the_title() . '</a>'; //phpcs:ignore
				} else {
					the_title();
				}
				?>
				</strong></h4>
				<p>
					<?php
					echo '<small>';
					if ( $dt_date_start != $dt_date_end ) {
						echo esc_html( $dt_date_start->format( 'm/j/Y' ) . ' - ' . $dt_date_end->format( 'm/j/Y' ) );
					} else {
						echo esc_html( $dt_date_start->format( 'm/j/Y' ) );
					}
					$country = wp_get_post_terms( $post->ID, 'lfevent-country' );
					if ( $country ) {
						$country = $country[0]->name;
						echo ' | ' . esc_html( get_post_meta( $post->ID, 'lfes_city', true ) ) . ', ' . esc_html( $country );
					}
					echo '</small>';
					echo '<br />CFP Status: ';

					if ( '0' === $cfp_active ) {
						echo 'No Call for Proposals';
					} elseif ( ! ( $cfp_date_start ) ) {
						echo 'Details Coming Soon';
					} elseif ( strtotime( $cfp_date_end ) < time() ) {
						echo 'Closed';
					} elseif ( strtotime( $cfp_date_end ) > time() && strtotime( $cfp_date_start ) < time() ) {
						echo 'Closes ' . esc_html( $dt_cfp_date_end->format( 'l, F j, Y' ) );
					} elseif ( strtotime( $cfp_date_end ) > time() && strtotime( $cfp_date_start ) > time() ) {
						echo 'Opens ' . esc_html( $dt_cfp_date_start->format( 'l, F j, Y' ) );
					}
					?>
				</p>
			</div>
		</article>
		<?php
	}
} else {
	echo 'No Results Found';
}
echo '</div>';
?>

<script>
// this script controls the appearence and behavior of the link to navigate between all and just upcoming events.
$( document ).ready( function() {
	var currentUrl = $( location ).attr( 'href' ) 
	if ( -1 == currentUrl.indexOf( 'events-calendar-archive' ) ) {
		//we are on the event-calendar page.
		newUrl = currentUrl.replace( 'events-calendar', 'events-calendar-archive' );
		$( 'a#switch-archive-view' ).attr( "href", newUrl );
	} else {
		//we are on the event-calendar-archive page.
		newUrl = currentUrl.replace( 'events-calendar-archive', 'events-calendar' );
		$( 'a#switch-archive-view' ).attr( "href", newUrl );
		$( 'a#switch-archive-view' ).html( 'Hide Past Events' );
	}
});
</script>
