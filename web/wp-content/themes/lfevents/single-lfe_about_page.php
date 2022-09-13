<?php //phpcs:ignore
/**
 * The template for displaying about pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages and that
 * other "pages" on your WordPress site will use a different template.
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

get_header();
get_template_part( 'template-parts/header-global' );
?>
<main role="main" id="main" class="main-container-body">
	<?php get_template_part( 'template-parts/non-event-hero' ); ?>
	<?php
	while ( have_posts() ) :
		the_post();
		?>
	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<div class="entry-content container wrap">
			<?php the_content(); ?>
			<?php get_template_part( 'template-parts/edit-link' ); ?>
		</div>
	</article>
	<?php endwhile; ?>
</main>
<?php
get_footer();
