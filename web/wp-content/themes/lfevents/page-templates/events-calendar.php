<?php
/**
 * Template Name: Events Calendar
 * Template Post Type: lfe_about_page
 *
 * @package FoundationPress
 */

get_header();
get_template_part( 'template-parts/global-header' );
?>

<!--
<div class="main-container">
	<div class="main-grid">
		<main class="main-content-full-width"> -->

		<div class="">
	<div class="">
		<main class="" style="width: 100%">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<div class="entry-content event-calendar-header">
						<?php get_template_part( 'template-parts/about-page-header' ); ?>
						<div>
							<div class="event-calendar-container">
								<?php the_content(); ?>
							</div>
						</div>
					</div>
				</article>
			<?php endwhile; ?>
		</main>
	</div>
</div>
<?php
get_footer();
