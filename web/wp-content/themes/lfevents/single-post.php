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

<main role="main" id="main" class="main-container-body">
<?php get_template_part( 'template-parts/non-event-hero' ); ?>
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<div class="container wrap">
						<?php the_content(); ?>
						<?php get_template_part( 'template-parts/edit-link' ); ?>
					</div>
				</article>
				<section class="container wrap large-padding-top large-padding-bottom">
					<div>
						<?php the_post_navigation(); ?>
					</div>
				</section>
			<?php endwhile; ?>
		</main>
<?php
get_footer();
