<?php
/**
 * Theme Calendar event card.
 *
 * Expects $args['event'] as produced by lfe_get_theme_calendar_events().
 *
 * @package FoundationPress
 */

$event = isset( $args['event'] ) ? $args['event'] : null;
if ( ! $event ) {
	return;
}

if ( check_string_is_date( $event['date_start'] ) && check_string_is_date( $event['date_end'] ) ) {
	$date_range = jb_verbose_date_range( new DateTime( $event['date_start'] ), new DateTime( $event['date_end'] ) );
} elseif ( check_string_is_date( $event['date_start'] ) ) {
	$date_range = ( new DateTime( $event['date_start'] ) )->format( 'M j, Y' );
} else {
	$date_range = 'TBA';
}

$city = $event['city'];
if ( $city && $event['country'] ) {
	$city .= ', ';
}
$has_location = $city || $event['country'];
?>
<article id="post-<?php echo esc_attr( $event['id'] ); ?>" class="cell medium-6 large-4 callout large-margin-bottom theme-calendar-card<?php echo $event['is_external'] ? ' theme-calendar-card--external' : ' theme-calendar-card--lf'; ?>">

	<h5 class="medium-margin-right small-margin-bottom line-height-tight">
		<strong>
			<?php if ( $event['url'] ) { ?>
				<a href="<?php echo esc_url( $event['url'] ); ?>"<?php echo $event['is_external'] ? ' target="_blank" rel="noopener"' : ''; ?>><?php echo esc_html( $event['title'] ); ?></a>
			<?php } else { ?>
				<?php echo esc_html( $event['title'] ); ?>
			<?php } ?>
		</strong>
	</h5>

	<p class="event-meta text-small small-margin-bottom">
		<span class="date small-margin-right display-inline-block">
			<?php get_template_part( 'template-parts/svg/calendar' ); ?>
			<?php echo esc_html( $date_range ); ?>
		</span>
		<span class="display-inline-block">
			<?php if ( $has_location ) { ?>
				<span class="country">
					<?php get_template_part( 'template-parts/svg/map-marker' ); ?>
					<?php echo esc_html( $city ) . esc_html( $event['country'] ); ?>
					<?php echo $event['virtual'] ? ' and ' : ''; ?>
				</span>
			<?php } ?>
			<?php if ( $event['virtual'] ) { ?>
				<span class="virtual">
					<?php get_template_part( 'template-parts/svg/virtual-marker' ); ?>
					Virtual
				</span>
			<?php } ?>
		</span>
	</p>

	<?php if ( ! $event['is_external'] ) { ?>
		<p class="theme-calendar-card__source text-small small-margin-bottom">
			<img class="theme-calendar-card__logo" src="<?php echo esc_url( get_stylesheet_directory_uri() . '/src/images/lf-mark.svg' ); ?>" alt="" width="14" height="14">
			Linux Foundation Event
		</p>
	<?php } elseif ( $event['organizer'] ) { ?>
		<p class="theme-calendar-card__source text-small small-margin-bottom">Organized by <?php echo esc_html( $event['organizer'] ); ?></p>
	<?php } ?>

	<?php
	if ( $event['description'] ) {
		$parsedown = new Parsedown();
		$parsedown->setSafeMode( true );
		$allowed_elements = array(
			'href'   => true,
			'class'  => true,
			'alt'    => true,
			'rel'    => true,
			'target' => true,
		);
		?>
		<div class="text-small small-margin-bottom event-description">
			<?php
			echo wp_kses(
				$parsedown->text( $event['description'] ),
				array(
					'a'      => $allowed_elements,
					'br'     => array(),
					'ul'     => array(),
					'li'     => array(),
					'p'      => array(),
					'h4'     => array(),
					'h5'     => array(),
					'strong' => array(),
					'em'     => array(),
				)
			);
			?>
		</div>
		<?php
	}

	if ( ! $event['is_external'] ) {
		$pacific_tz = new DateTimeZone( 'America/Los_Angeles' );
		$time       = strtotime( wp_date( 'Y-m-d', null, $pacific_tz ) );
		?>
		<p class="event-meta text-small no-margin">
			<span class="cfp">
				<?php get_template_part( 'template-parts/svg/bullhorn' ); ?>
				CFP Status:
				<span class="text-weight-normal">
					<?php
					if ( '0' === $event['cfp_active'] ) {
						echo 'No Call for Proposals';
					} elseif ( ! $event['cfp_date_start'] ) {
						echo 'Details Coming Soon';
					} elseif ( strtotime( $event['cfp_date_end'] ) < $time ) {
						echo 'Closed';
					} elseif ( strtotime( $event['cfp_date_start'] ) <= $time ) {
						echo 'Closes ' . esc_html( ( new DateTime( $event['cfp_date_end'] ) )->format( 'l, M j, Y' ) );
					} else {
						echo 'Opens ' . esc_html( ( new DateTime( $event['cfp_date_start'] ) )->format( 'l, M j, Y' ) );
					}
					?>
				</span>
			</span>
		</p>
		<?php
	}
	?>

</article>
