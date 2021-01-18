<?php
/**
 * The template for the homepage
 *
 * @package FoundationPress
 */

get_header();
get_template_part( 'template-parts/global-header' );
?>

<div class="main-container large-padding-bottom" role="main">

	<?php
	get_template_part( 'template-parts/home-hero' );
	?>

	<div class="grid-container">
		<div class="grid-x grid-margin-x">
			<div class="cell medium-8 large-9 xlarge-margin-bottom">
				<?php
				if ( is_lfeventsci() ) {
					echo do_shortcode( '[searchandfilter id="4480"]' );
					echo do_shortcode( '[searchandfilter id="4480" show="results"]' );
				} else {
					// upcoming events loop.
					get_template_part( 'template-parts/upcoming-events-loop' );
				}
				?>
				<a class="button gray large expanded" href="<?php echo esc_url( home_url( '/about/calendar' ) ); ?>">
					<?php get_template_part( 'template-parts/svg/calendar' ); ?>
					<?php
					if ( is_lfeventsci() ) {
						echo '<strong>Search Our Events Calendar</strong>';
						echo '<small class="text-small small-margin-top uppercase display-block">(all upcoming &amp; past events)</small>';
					} else {
						echo '<strong>搜索我们的活动日历</strong>';
						echo '<small class="text-small small-margin-top uppercase display-block">Search Our Events Calendar</small>';
					}
					?>
				</a>
			</div>
			<div class="cell medium-4 large-3">
				<?php
				if ( is_lfeventsci() ) {
					get_template_part( 'template-parts/sidebar-lfeventsci' );
				} else {
					get_template_part( 'template-parts/sidebar-lfasiallcci' );
				}
				?>
			</div>
		</div>
	</div>
</div>

<?php
get_footer();
