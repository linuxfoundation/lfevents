<?php
/**
 * Upcoming events loop.
 * Used on home page as lfasiallc doesn't use search filter plugin.
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

?>

<div class="grid-x grid-margin-x grid-margin-y medium-margin-bottom">
		<?php
		// Upcoming Events.
		$query = new WP_Query(
			array(
				'post_type'      => 'page',
				'post_parent'    => 0,
				'no_found_rows'  => true,
				'meta_key'       => 'lfes_date_start',
				'orderby'        => array(
					'meta_value' => 'ASC',
					'title'      => 'ASC',
				),
				'order'          => 'ASC',
				'post_status'    => array( 'publish' ),
				'posts_per_page' => 100,
			)
		);
		if ( $query->have_posts() ) {
			$i = 0;
			while ( $query->have_posts() ) {
				$query->the_post();
				$hide_from_listings = get_post_meta( $post->ID, 'lfes_hide_from_listings', true );
				$event_has_passed   = get_post_meta( $post->ID, 'lfes_event_has_passed', true );
				if ( 'hide' === $hide_from_listings || $event_has_passed ) {
					continue;
				}

				$date_start = get_post_meta( $post->ID, 'lfes_date_start', true );
				if ( ! check_string_is_date( $date_start ) ) {
					$date_range = 'TBA';
				} else {
					$dt_date_start = new DateTime( $date_start );
					$dt_date_end   = new DateTime( get_post_meta( $post->ID, 'lfes_date_end', true ) );
					$date_range    = jb_verbose_date_range( $dt_date_start, $dt_date_end );
				}

				$register_url = get_post_meta( $post->ID, 'lfes_cta_register_url', true );

				$speak_url      = get_post_meta( $post->ID, 'lfes_cta_speak_url', true );
				$cfp_date_start = get_post_meta( $post->ID, 'lfes_cfp_date_start', true );
				$cfp_date_end   = get_post_meta( $post->ID, 'lfes_cfp_date_end', true );

				$sponsor_url      = get_post_meta( $post->ID, 'lfes_cta_sponsor_url', true );
				$sponsor_date_end = get_post_meta( $post->ID, 'lfes_cta_sponsor_date_end', true );

				$schedule_url = get_post_meta( $post->ID, 'lfes_cta_schedule_url', true );

				$description = get_post_meta( $post->ID, 'lfes_description', true );
				$parsedown   = new Parsedown();
				$description = $parsedown->text( $description );
				?>

				<div id="post-<?php the_ID(); ?>" class="cell medium-12 large-6 event callout">

					<h2 class="event-title medium-margin-right small-margin-bottom line-height-tight">
						<a class="unstyled-link" href="<?php echo esc_html( lfe_get_event_url( $post->ID ) ); ?>">
							<?php echo esc_html( get_the_title( $post->ID ) ); ?>
						</a>
					</h2>

					<p class="event-meta text-small small-margin-bottom">
						<span class="date small-margin-right display-inline-block">
							<?php get_template_part( 'template-parts/svg/calendar' ); ?>
							<?php echo esc_html( $date_range ); ?>
						</span>
						<span class="display-inline-block">
						<?php
						$country = wp_get_post_terms( $post->ID, 'lfevent-country' );
						$virtual = get_post_meta( $post->ID, 'lfes_virtual', true );
						if ( $country ) {
							?>
							<span class="country">
							<?php
							get_template_part( 'template-parts/svg/map-marker' );
							$country = $country[0]->name;
							$city    = get_post_meta( $post->ID, 'lfes_city', true );
							if ( $city ) {
								$city .= ', ';
							}
							echo esc_html( $city ) . esc_html( $country );
							if ( $virtual ) {
								echo ' and ';
							}
							?>
							</span>
							<?php
						}
						?>
						<?php
						if ( $virtual ) {
							?>
							<span class="virtual">
							<?php
							get_template_part( 'template-parts/svg/virtual-marker' );
							echo 'Virtual';
							?>
							</span>
							<?php
						}
						?>
						</span>
					</p>

					<div class="text-small small-margin-bottom event-description">
						<?php
						$allowed_elements = array(
							'href'   => true,
							'class'  => true,
							'alt'    => true,
							'rel'    => true,
							'target' => true,
						);
						echo wp_kses(
							$description,
							array(
								'a'      => $allowed_elements,
								'br'     => array(),
								'ul'     => array(),
								'li'     => array(),
								'p'      => array(),
								'h4'     => array(),
								'h5'     => array(),
								'strong' => array(),
							)
						);
						?>
					</div>

					<p class="homepage--call-to-action">
					<?php
					$have_button = false;
					$pacific_tz  = new DateTimeZone( 'America/Los_Angeles' ); // timezone for Pacific Time.
					$time        = strtotime( wp_date( 'Y-m-d', null, $pacific_tz ) ); // Return current day in PT.

					if ( $register_url ) {
						echo '<a aria-label="Register for ' . esc_html( get_the_title( $post->ID ) ) . '" href="' . esc_url( $register_url ) . '" >Register</a>';
						$have_button = true;
					}

					if ( $speak_url && strtotime( $cfp_date_end ) >= $time && strtotime( $cfp_date_start ) <= $time ) {
						echo '<a aria-label="Speak at ' . esc_html( get_the_title( $post->ID ) ) . '" href="' . esc_url( $speak_url ) . '">Speak</a>';
						$have_button = true;
					}

					if ( $sponsor_url && ( ! $sponsor_date_end || strtotime( $sponsor_date_end ) >= $time ) ) {
						echo '<a aria-label="Sponsor ' . esc_html( get_the_title( $post->ID ) ) . '" href="' . esc_url( $sponsor_url ) . '">Sponsor</a>';
						$have_button = true;
					}

					if ( $schedule_url ) {
						echo '<a aria-label="View schedule for ' . esc_html( get_the_title( $post->ID ) ) . '" href="' . esc_url( $schedule_url ) . '">Schedule</a>';
						$have_button = true;
					}

					if ( ! $have_button ) {
						echo '<a aria-label="Learn more about ' . esc_html( get_the_title( $post->ID ) ) . '" href="' . esc_html( lfe_get_event_url( $post->ID ) ) . '">Learn more</a>';
					}
					?>
					</p>

				</div>

				<?php
				++$i;
			}
			wp_reset_postdata();

			// if events 0 show message.
			if ( 0 === $i ) {
				get_template_part( 'template-parts/no-events-message' );
			}
		} else {
			get_template_part( 'template-parts/no-events-message' );
		}
		?>
	</div>
