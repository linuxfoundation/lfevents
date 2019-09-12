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
							?>

							<div id="post-<?php the_ID(); ?>" class="cell medium-6">

								<h4 class="medium-margin-right small-margin-bottom line-height-tight">
									<a class="unstyled-link" href="<?php echo esc_html( lfe_get_event_url( $post->ID ) ); ?>">
										<strong><?php echo esc_html( get_the_title( $post->ID ) ); ?></strong>
									</a>
								</h4>

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
					<svg aria-hidden="true" focusable="false" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="icon--inline"><g class="fa-group"><path fill="currentColor" d="M0 192v272a48 48 0 0 0 48 48h352a48 48 0 0 0 48-48V192zm128 244a12 12 0 0 1-12 12H76a12 12 0 0 1-12-12v-40a12 12 0 0 1 12-12h40a12 12 0 0 1 12 12zm0-128a12 12 0 0 1-12 12H76a12 12 0 0 1-12-12v-40a12 12 0 0 1 12-12h40a12 12 0 0 1 12 12zm128 128a12 12 0 0 1-12 12h-40a12 12 0 0 1-12-12v-40a12 12 0 0 1 12-12h40a12 12 0 0 1 12 12zm0-128a12 12 0 0 1-12 12h-40a12 12 0 0 1-12-12v-40a12 12 0 0 1 12-12h40a12 12 0 0 1 12 12zm128 128a12 12 0 0 1-12 12h-40a12 12 0 0 1-12-12v-40a12 12 0 0 1 12-12h40a12 12 0 0 1 12 12zm0-128a12 12 0 0 1-12 12h-40a12 12 0 0 1-12-12v-40a12 12 0 0 1 12-12h40a12 12 0 0 1 12 12z" class="fa-secondary"></path><path fill="currentColor" d="M448 112v48H0v-48a48 48 0 0 1 48-48h48V16a16 16 0 0 1 16-16h32a16 16 0 0 1 16 16v48h128V16a16 16 0 0 1 16-16h32a16 16 0 0 1 16 16v48h48a48 48 0 0 1 48 48z" class="fa-primary"></path></g></svg>
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

						echo '<h5 class="text-medium no-margin"><a href="' . esc_html( get_post_meta( $post->ID, 'lfes_community_external_url', true ) ) . '">' . esc_html( get_the_title() ) . '</a></h5>';
						echo '<p class="text-tiny medium-margin-bottom">' . esc_html( jb_verbose_date_range( $dt_date_start, $dt_date_end ) );
						$country = wp_get_post_terms( $post->ID, 'lfevent-country' );
						if ( $country ) {
							$country = $country[0]->name;
							$city = get_post_meta( $post->ID, 'lfes_community_city', true );
							if ( $city ) {
								$city .= ', ';
							}
							echo ' | ' . esc_html( $city ) . esc_html( $country ) . '</p>';
						}
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
