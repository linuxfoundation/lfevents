<?php
/**
 * The template for the homepage
 *
 * @package FoundationPress
 */

get_header();
get_template_part( 'template-parts/global-nav' );
?>

<div class="main-container xlarge-padding-bottom">

	<?php
	// Top homepage content.
	$query = new WP_Query(
		array(
			'post_type' => 'lfe_about_page',
			'name' => 'homepage',
		)
	);
	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			?>
			<div class="home-hero">
				<div class="bg-image" style="background-image: url(<?php echo esc_html( get_the_post_thumbnail_url() ); ?>);"></div>
				<div class="bg-animation"></div>
				<div class="grid-container">
					<?php
					the_content();
					?>
				</div>
			</div>
			<?php
		}
		wp_reset_postdata();
	}
	?>

	<div class="grid-container xlarge-padding-bottom">
		<div class="grid-x grid-margin-x">
			<div class="cell medium-7 large-8">
				<div class="grid-x grid-margin-x">
					<?php
					// Upcoming Events.
					$query = new WP_Query(
						array(
							'post_type' => 'page',
							'post_parent' => 0,
							'no_found_rows' => true,
							'meta_key'   => 'lfes_date_start',
							'orderby'    => 'meta_value',
							'order'      => 'ASC',
							'post_status' => array( 'draft', 'publish' ),
						)
					);
					if ( $query->have_posts() ) {
						while ( $query->have_posts() ) {
							$query->the_post();
							$dt_date_start = new DateTime( get_post_meta( $post->ID, 'lfes_date_start', true ) );
							$dt_date_end = new DateTime( get_post_meta( $post->ID, 'lfes_date_end', true ) );
							$register_url = get_post_meta( $post->ID, 'lfes_cta_register_url', true );
							$speak_url = get_post_meta( $post->ID, 'lfes_cta_speak_url', true );
							$sponsor_url = get_post_meta( $post->ID, 'lfes_cta_sponsor_url', true );

							?>
							<article id="post-<?php the_ID(); ?>" class="cell large-6">
								<div class="callout large-margin-bottom">
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
										?>
									</p>
									<p>
										<?php
										if ( $register_url ) {
											echo '<a href="' . esc_url( $register_url ) . '">Register</a>';
										}
										if ( $speak_url ) {
											echo '<a href="' . esc_url( $speak_url ) . '">Speak</a>';
										}
										if ( $sponsor_url ) {
											echo '<a href="' . esc_url( $sponsor_url ) . '">Sponsor</a>';
										}
										?>
									</p>
								</div>
							</article>
							<?php
						}
						wp_reset_postdata();
					}
					?>
				</div>
				<a class="button" href="<?php echo esc_url( home_url( '/about/events-calendar' ) ); ?>">Full Events Calendar</a>
			</div>
			<div class="cell medium-5 large-4">
				<h3 class="large-margin-bottom">Latest News</h3>
				<?php
				// Latest News.
				$query = new WP_Query(
					array(
						'post_type' => 'post',
						'no_found_rows' => true,
						'posts_per_page' => 3,
					)
				);
				if ( $query->have_posts() ) {
					while ( $query->have_posts() ) {
						$query->the_post();
						echo '<h5 class="no-margin"><a href="' . esc_html( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></h5>';
						echo '<p class="text-small small-margin-bottom">' . get_the_date() . '</p>';
						echo '<p class="large-margin-bottom">' . esc_html( get_the_excerpt() ) . '</p>';
					}
				}
				wp_reset_postdata();
				?>
				<a class="button" href="<?php echo esc_url( home_url( '/about/news' ) ); ?>">Read More News</a>
			</div>
		</div>
	</div>

</div>
<?php
get_footer();
