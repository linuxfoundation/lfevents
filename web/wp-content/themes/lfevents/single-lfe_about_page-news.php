<?php // phpcs:ignoreFile // due to wrong CPT naming.
/**
 * The template for displaying a specific about page with the slug "news"
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
				// check the content is there before showing this section.
				if ( strlen( get_the_content() ) > 0 ) :
					?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<div class="container wrap">
					<?php the_content(); ?>
					<?php get_template_part( 'template-parts/edit-link' ); ?>
					</div>
				</article>
					<?php
				endif;
		endwhile;
			?>
			<section class="entry-content">
					<div class="grid-x grid-margin-x medium-up-2 large-up-3">
						<?php
						query_posts( 'posts_per_page=60' );
						// The Loop.
						if ( have_posts() ) :
							while ( have_posts() ) :
								the_post();
								?>
								<article class="cell callout large-margin-bottom">
								<h4 class="no-margin line-height-tight"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
								<p class="text-small small-margin-top"><?php echo the_date(); ?></p>
								<p class=""><?php the_excerpt(); ?></p>
								</article>
								<?php
						endwhile;
						endif;
						// Reset Query.
						wp_reset_query();
						?>
					</div>
				</section>
		</main>
<?php
get_footer();
