<?php
/**
 * Calendar buttons
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

$calendar_link = '/about/calendar/archive';
$calendar_text = 'View Past Events';

if ( ! is_lfeventsci() ) {
	$calendar_text = '查看往期活动 View Past Events';
}

if ( is_singular( 'lfe_about_page' ) && is_single( 'archive' ) ) {
	$calendar_link = '/about/calendar/';
	$calendar_text = 'View Upcoming Events';
	if ( ! is_lfeventsci() ) {
		$calendar_text = '查看即将举办的活动 View Upcoming Events';
	}
}
?>

<h5 class="calendar-buttons">
	<strong>
	<a href="<?php echo esc_url( $calendar_link ); ?>"><?php echo esc_html( $calendar_text ); ?></a>
	</strong>
</h5>
