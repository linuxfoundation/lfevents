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
	<div class="entry-content">
		<?php get_template_part( 'template-parts/about-page-header' ); ?>
		<?php the_content(); ?>
		<?php edit_post_link( __( '(Edit)', 'foundationpress' ), '<span class="edit-link">', '</span>' ); ?>
	</div>
</article>
			<?php endwhile; ?>
		</main>
	</div>
</div>
<?php
get_footer();
