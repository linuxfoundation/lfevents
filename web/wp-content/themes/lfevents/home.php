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
				<?php
				$args = array(
					'post_parent'    => $post->ID,
					'post_type'      => 'attachment',
					'numberposts'    => -1, // show all.
					'post_status'    => 'any',
					'post_mime_type' => 'image',
					'orderby'        => 'menu_order',
					'order'          => 'ASC',
				);
				$images = get_posts( $args );
				$i = 0;
				if ( $images ) {
					?>
					<div class="bg-images">
						<?php
						foreach ( $images as $key => $image ) {
							if ( 0 == $i ) {
								$active = 'active';
							} else {
								$active = '';
							}
							?>
							<div class="bg-image <?php echo esc_html( $active ); ?>" style="background-image: url(<?php echo esc_html( wp_get_attachment_url( $image->ID ) ); ?>);"></div>
							<?php
							$i++;
						}
						?>
					</div>
					<?php
				}
				?>

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
			<div class="cell medium-8 large-9 xlarge-margin-bottom">
				<div class="grid-x grid-margin-x grid-margin-y medium-margin-bottom">
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
							'post_status' => array( 'publish' ),
							'posts_per_page' => 100,
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
							$description = get_post_meta( $post->ID, 'lfes_description', true );

							$dt_date_end_1d_after = new DateTime( get_post_meta( $post->ID, 'lfes_date_end', true ) );
							$dt_date_end_1d_after->add( new DateInterval( 'P1D' ) );
							$dt_now = new DateTime( 'now' );
							if ( $dt_date_end_1d_after < $dt_now ) {
								// event has passed so don't show it on the homepage.
								continue;
							}

							?>

							<div id="post-<?php the_ID(); ?>" class="cell medium-6">

								<h4 class="medium-margin-right small-margin-bottom line-height-tight">
									<a class="unstyled-link" href="<?php echo esc_html( lfe_get_event_url( $post->ID ) ); ?>">
										<strong><?php echo esc_html( get_the_title( $post->ID ) ); ?></strong>
									</a>
								</h4>

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

								<p class="text-small small-margin-bottom">
									<?php
									echo esc_html( $description );
									?>
								</p>

								<p class="homepage--call-to-action">
									<?php
									if ( $register_url ) {
										echo '<a href="' . esc_url( $register_url ) . '" >Register</a>';
									}

									if ( $speak_url ) {
										echo '<a href="' . esc_url( $speak_url ) . '">Speak</a>';
									}

									if ( $sponsor_url ) {
										echo '<a href="' . esc_url( $sponsor_url ) . '">Sponsor</a>';
									}

									if ( ! $register_url && ! $speak_url && ! $sponsor_url ) {
										echo '<a href="' . esc_html( lfe_get_event_url( $post->ID ) ) . '">Learn more</a>';
									}
									?>
								</p>

							</div>

							<?php
						}
						wp_reset_postdata();
					}
					?>
				</div>
				<a class="button gray large expanded" href="<?php echo esc_url( home_url( '/about/calendar' ) ); ?>">
					<?php get_template_part( 'template-parts/svg/calendar' ); ?>
					<strong>Search Our Events Calendar</strong>
					<small class="text-small small-margin-top uppercase display-block">(all upcoming &amp; past events)</small>
				</a>
			</div>
			<div class="cell medium-4 large-3">
				<h4 class="medium-margin-bottom">Latest News</h4>
				<?php
				$query = new WP_Query(
					array(
						'post_type' => 'post',
						'no_found_rows' => true,
						'posts_per_page' => 5,
					)
				);
				if ( $query->have_posts() ) {
					while ( $query->have_posts() ) {
						$query->the_post();
						echo '<h5 class="text-medium no-margin"><a href="' . esc_html( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></h5>';
						echo '<p class="text-tiny medium-margin-bottom">' . get_the_date() . '</p>';
					}
				}
				wp_reset_postdata();
				?>
				<p class="xlarge-margin-bottom"><a href="<?php echo esc_url( home_url( '/about/news' ) ); ?>"><strong>More News&hellip;</strong></a></p>

				<h4 class="medium-margin-bottom large-margin-top">Community Events</h4>
				<?php
				$query = new WP_Query(
					array(
						'post_type' => 'lfe_community_event',
						'no_found_rows' => true,
						'posts_per_page' => 10,
						'meta_key'   => 'lfes_community_date_start',
						'orderby'    => 'meta_value',
						'order'      => 'ASC',
					)
				);
				if ( $query->have_posts() ) {
					while ( $query->have_posts() ) {
						$query->the_post();
						$dt_date_start = new DateTime( get_post_meta( $post->ID, 'lfes_community_date_start', true ) );
						$dt_date_end = new DateTime( get_post_meta( $post->ID, 'lfes_community_date_end', true ) );

						echo '<h5 class="text-medium no-margin">';
						echo '<a target="_blank" href="' . esc_html( get_post_meta( $post->ID, 'lfes_community_external_url', true ) ) . '">';
						echo esc_html( get_the_title() );
						echo '&nbsp;';
						echo get_template_part( 'template-parts/svg/external-link' );
						echo '</a>';
						echo '</h5>';

						echo '<p class="text-tiny medium-margin-bottom">';
						echo esc_html( jb_verbose_date_range( $dt_date_start, $dt_date_end ) );
						$country = wp_get_post_terms( $post->ID, 'lfevent-country' );
						if ( $country ) {
							$country = $country[0]->name;
							$city = get_post_meta( $post->ID, 'lfes_community_city', true );
							if ( $city ) {
								$city .= ', ';
							}
							echo ' | ' . esc_html( $city ) . esc_html( $country );
						}
						echo '</p>';
					}
				}
				wp_reset_postdata();
				?>
				<p class="xlarge-margin-bottom"><a href="<?php echo esc_url( home_url( '/about/community' ) ); ?>"><strong>More Community Events&hellip;</strong></a></p>
			</div>
		</div>
	</div>

</div>
<?php
get_footer();
