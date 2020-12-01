<?php
/**
 * The template for displaying all single posts and attachments
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

get_header();
get_template_part( 'template-parts/global-header' );
?>

<?php get_template_part( 'template-parts/featured-image' ); ?>
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
				<?php get_template_part( 'template-parts/about-page-header' ); ?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<div class="entry-content">
							<?php the_content(); ?>
							<?php edit_post_link( __( '(Edit)', 'foundationpress' ), '<span class="edit-link">', '</span>' ); ?>
					</div>
				</article>
				<div class="entry-content large-padding-top large-padding-bottom">
					<div class="">
						<?php the_post_navigation(); ?>
						<div class="large-padding-top">
							<?php // comments_template(); ?>
						</div>
					</div>
				</div>
			<?php endwhile; ?>
		</main>
	</div>
</div>
<?php
get_footer();
