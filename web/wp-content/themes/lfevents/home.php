<?php
/**
 * The template for the homepage
 *
 * @package FoundationPress
 */

get_header();
get_template_part( 'template-parts/global-nav' );

/**
 * Makes the date pretty.  Got the code from https://9seeds.com/pretty-php-date-ranges/.
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
		if ( $start_date->format( 'Y' )  != $end_date->format( 'Y' ) ) {
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
			  $date_range .= ' - ' . $end_date_pretty;
		}
	}
	return $date_range;
}

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

							$menu_color = get_post_meta( $post->ID, 'lfes_menu_color', true );
							$menu_color_2 = get_post_meta( $post->ID, 'lfes_menu_color_2', true );
							$menu_color_3 = get_post_meta( $post->ID, 'lfes_menu_color_3', true );
							$menu_text_color = get_post_meta( $post->ID, 'lfes_menu_text_color', true );
							$background_style = 'background-color: ' . $menu_color . ';';
							if ( $menu_color_2 ) {
								$background_style = 'background: linear-gradient(90deg, ' . $menu_color . ' 0%, ' . $menu_color_2 . ' 100%);';
							}
							$text_style = 'color: ' . $menu_text_color . ';';

							$logo = get_post_meta( $post->ID, 'lfes_' . $menu_text_color . '_logo', true );
							if ( $logo ) {
								$event_title_content = '<img src="' . wp_get_attachment_url( $logo ) . '" alt="' . get_the_title( $post->ID ) . '">';  //phpcs:ignore
							} else {
								$event_title_content = get_the_title( $post->ID );
							}

							?>
							<article id="post-<?php the_ID(); ?>" class="cell large-6 home-card large-margin-bottom" style="<?php echo esc_html( $background_style . $text_style ); ?>">
								<div class="bg-image" style="background-image: url(<?php echo esc_html( get_the_post_thumbnail_url() ); ?>);"></div>
								<div class="card-header">
									<?php
									if ( 'publish' == $post->post_status ) {
										echo '<a class="card-header-link" href="' . get_the_permalink( $post->ID ) . '">'; //phpcs:ignore
									}

										echo $event_title_content;

										echo '<span class="date">';
										echo jb_verbose_date_range( $dt_date_start, $dt_date_end );
										echo '</span>';

										$country = wp_get_post_terms( $post->ID, 'lfevent-country' );
										if ( $country ) {
											$country = $country[0]->name;
											echo '<span class="country">' . esc_html( get_post_meta( $post->ID, 'lfes_city', true ) ) . ', ' . esc_html( $country ) . '</span>';
										}

									if ( 'publish' == $post->post_status ) {
										echo '</a>'; //phpcs:ignore
									}
									?>
								</div>
								<div class="card-footer">
									<?php if ( $register_url || $speak_url || $sponsor_url ) { ?>
										<div class="links" style="<?php echo esc_html( $background_style . $text_style ); ?>">
											<?php
											if ( $register_url ) {
												echo '<a class="link" href="' . esc_url( $register_url ) . '">Register</a>';
											}
											if ( $speak_url ) {
												echo '<a class="link" href="' . esc_url( $speak_url ) . '">Speak</a>';
											}
											if ( $sponsor_url ) {
												echo '<a class="link" href="' . esc_url( $sponsor_url ) . '">Sponsor</a>';
											}
											?>
										</div>
									<?php } ?>
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
