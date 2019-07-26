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
		$dt_date_start = new DateTime( get_post_meta( $post->ID, 'lfes_community_date_start', true ) );
		$dt_date_end = new DateTime( get_post_meta( $post->ID, 'lfes_community_date_end', true ) );

		if ( ( 0 == $y ) || ( $y < (int) $dt_date_start->format( 'Y' ) ) ) {
			$y = (int) $dt_date_start->format( 'Y' );
			echo '<h2 class="cell event-calendar-year">' . esc_html( $y ) . '</h2>';
			$month = (int) $dt_date_start->format( 'm' );
			echo '<h3 class="cell event-calendar-month">' . esc_html( $dt_date_start->format( 'F' ) ) . '</h3>';
		} elseif ( ( 0 == $month ) || ( $month < (int) $dt_date_start->format( 'm' ) ) ) {
			$month = (int) $dt_date_start->format( 'm' );
			echo '<h3 class="cell event-calendar-month">' . esc_html( $dt_date_start->format( 'F' ) ) . '</h3>';
		}
		?>
		<article id="post-<?php the_ID(); ?>" class="cell medium-6 callout">
			<h4 class="h5 no-margin"><strong>
			<?php
			echo '<a href="' . esc_html( get_post_meta( $post->ID, 'lfes_community_external_url', true ) ) . '">' . esc_html( get_the_title() ) . '</a>';
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
					echo ' | ' . esc_html( get_post_meta( $post->ID, 'lfes_community_city', true ) ) . ', ' . esc_html( $country );
				}
				echo '</small>';
				?>
			</p>
		</article>
		<?php
	}
} else {
	echo 'No Results Found';
}
echo '</div>';
?>
