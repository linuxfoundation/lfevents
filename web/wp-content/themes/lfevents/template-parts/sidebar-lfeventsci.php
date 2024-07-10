<?php
/**
 * Sidebar content for the lfeventsci site
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

?>

<h3 class="sidebar__title medium-margin-bottom">Latest News</h3>
<?php
$query = new WP_Query(
	array(
		'post_type'      => 'post',
		'no_found_rows'  => true,
		'posts_per_page' => 5,
	)
);
if ( $query->have_posts() ) {
	while ( $query->have_posts() ) {
		$query->the_post();
		echo '<h4 class="sidebar-item__title text-medium no-margin"><a href="' . esc_html( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></h4>';
		echo '<p class="text-tiny medium-margin-bottom">' . get_the_date() . '</p>';
	}
}
wp_reset_postdata();
?>
<p class="xlarge-margin-bottom"><a href="<?php echo esc_url( home_url( '/about/news' ) ); ?>"><strong>More News&hellip;</strong></a></p>

<h3 class="sidebar__title medium-margin-bottom large-margin-top">Community Events</h3>
<?php
$query = new WP_Query(
	array(
		'post_type'      => 'lfe_community_event',
		'no_found_rows'  => true,
		'posts_per_page' => 10,
		'meta_key'       => 'lfes_community_date_start',
		'orderby'        => 'meta_value',
		'order'          => 'ASC',
	)
);
if ( $query->have_posts() ) {
	while ( $query->have_posts() ) {
		$query->the_post();
		$dt_date_start = new DateTime( get_post_meta( $post->ID, 'lfes_community_date_start', true ) );
		$dt_date_end   = new DateTime( get_post_meta( $post->ID, 'lfes_community_date_end', true ) );

		echo '<h4 class="sidebar-item__title text-medium no-margin"><a class="prevent-orphaned-icon" target="_blank" rel="noopener noreferrer" href="' . esc_html( get_post_meta( $post->ID, 'lfes_community_external_url', true ) ) . '">';
		echo esc_html( get_the_title() );
		echo '&nbsp;';
		echo esc_html( get_template_part( 'template-parts/svg/external-link' ) );
		echo '</a>';
		echo '</h4>';

		echo '<p class="text-tiny medium-margin-bottom">';
		echo esc_html( jb_verbose_date_range( $dt_date_start, $dt_date_end ) );
		$country = wp_get_post_terms( $post->ID, 'lfevent-country' );
		$virtual = get_post_meta( $post->ID, 'lfes_community_virtual', true );
		if ( $country ) {
			$country = $country[0]->name;
			$city    = get_post_meta( $post->ID, 'lfes_community_city', true );
			if ( $city ) {
				$city .= ', ';
			}
			echo ' | ' . esc_html( $city ) . esc_html( $country );
		} elseif ( $virtual ) {
			echo ' | Virtual';
		}
		echo '</p>';
	}
}
wp_reset_postdata();
?>
<p class="xlarge-margin-bottom"><a href="<?php echo esc_url( home_url( '/about/community' ) ); ?>"><strong>More Community Events&hellip;</strong></a></p>
