<?php //phpcs:ignore
/**
 * The template for displaying Theme Calendars: all upcoming LF and external
 * events sharing the calendar's Event Category, grouped by year and month.
 *
 * @package FoundationPress
 */

get_header();
get_template_part( 'template-parts/header-global' );
?>
<main role="main" id="main" class="main-container-body">
	<?php get_template_part( 'template-parts/non-event-hero' ); ?>
	<?php
	while ( have_posts() ) :
		the_post();
		$events = lfe_get_theme_calendar_events( get_the_ID() );
		lfe_insert_theme_calendar_structured_data( $events );
		?>
	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<div class="entry-content event-calendar-header wrap container">
			<?php the_content(); ?>
		</div>

		<div class="theme-calendar wrap container">
			<div class="grid-x grid-margin-x">
				<?php
				if ( $events ) {
					$lf_count       = count( array_filter( $events, fn( $e ) => ! $e['is_external'] ) );
					$external_count = count( $events ) - $lf_count;
					?>
					<p class="cell results-count">
						Displaying <?php echo esc_html( count( $events ) ); ?> events
						(<?php echo esc_html( $lf_count ); ?> Linux Foundation, <?php echo esc_html( $external_count ); ?> community &amp; industry)
					</p>
					<?php
					$y     = 0;
					$month = 0;
					foreach ( $events as $event ) {
						if ( ! check_string_is_date( $event['date_start'] ) ) {
							if ( 'TBA' !== $y ) {
								$y = 'TBA';
								echo '<h2 class="cell event-calendar-year">TBA</h2>';
							}
						} else {
							$dt_date_start = new DateTime( $event['date_start'] );
							$event_year    = (int) $dt_date_start->format( 'Y' );
							$event_month   = (int) $dt_date_start->format( 'm' );
							if ( $y !== $event_year ) {
								$y     = $event_year;
								$month = $event_month;
								echo '<h2 class="cell event-calendar-year">' . esc_html( $y ) . '</h2>';
								echo '<h3 class="cell event-calendar-month">' . esc_html( $dt_date_start->format( 'F' ) ) . '</h3>';
							} elseif ( $month !== $event_month ) {
								$month = $event_month;
								echo '<h3 class="cell event-calendar-month">' . esc_html( $dt_date_start->format( 'F' ) ) . '</h3>';
							}
						}
						get_template_part( 'template-parts/theme-calendar-card', null, array( 'event' => $event ) );
					}
				} else {
					get_template_part( 'template-parts/no-events-message' );
				}
				?>
			</div>
			<?php get_template_part( 'template-parts/edit-link' ); ?>
		</div>
	</article>
	<?php endwhile; ?>
</main>
<?php
get_footer();
