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

<main role="main" id="main" class="main-container-body">
	<?php get_template_part( 'template-parts/non-event-hero' ); ?>
	<?php
	while ( have_posts() ) :
		the_post();
		?>
	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

		<div class="entry-content event-calendar-header wrap container">

			<?php get_template_part( 'template-parts/calendar-buttons' ); ?>

			<?php the_content(); ?>

			<?php get_template_part( 'template-parts/calendar-buttons' ); ?>

		</div>
		</div>
	</article>
	<?php endwhile; ?>
</main>
<?php
get_footer();
