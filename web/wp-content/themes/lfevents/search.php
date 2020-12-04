<?php
/**
 * The template for displaying search results pages.
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

get_header();
get_template_part( 'template-parts/global-header' );
?>
<main role="main" id="main" class="main-container-body">
	<?php get_template_part( 'template-parts/non-event-hero' ); ?>
	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<div class="container wrap">
			<?php if ( have_posts() ) : ?>
			<div class="grid-x grid-margin-x medium-up-2 large-up-3">
				<?php
				while ( have_posts() ) :
					the_post();

					$date_start = get_post_meta( $post->ID, 'lfes_date_start', true );
					if ( ! check_string_is_date( $date_start ) ) {
						$date_range = 'TBA';
					} else {
						$dt_date_start = new DateTime( $date_start );
						$dt_date_end   = new DateTime( get_post_meta( $post->ID, 'lfes_date_end', true ) );
						$date_range    = jb_verbose_date_range( $dt_date_start, $dt_date_end );
					}
					?>
				<article class="cell callout large-margin-bottom">
					<h4><a href="<?php the_permalink(); ?>"
							title="<?php the_title(); ?>">
							<?php the_title(); ?></a></h4>
							<span class="date small-margin-right display-inline-block">
							<?php get_template_part( 'template-parts/svg/calendar' ); ?>
							<?php echo esc_html( $date_range ); ?>
						</span>
				</article>
					<?php
				endwhile;
				else :
					?>
				<p>Sorry, but nothing matched your search terms. Please try again with some different keywords.</p>
					<?php
					get_search_form();
	endif;
				?>
			</div>
		</div>
	</article>
	<?php
	if ( function_exists( 'foundationpress_pagination' ) ) :
		foundationpress_pagination();
		endif;
	?>
	<div class="container wrap">
	<?php get_template_part( 'searchform' ); ?>
	</div>
</main>
<?php
get_footer();
