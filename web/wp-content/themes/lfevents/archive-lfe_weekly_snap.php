<?php // phpcs:ignoreFile // due to wrong CPT naming.
/**
 * The archive listing page for weekly snaps
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

get_header();
get_template_part( 'template-parts/global-header' );
?>
<main role="main" id="main" class="main-container-body">
<?php get_template_part( 'template-parts/non-event-hero' ); ?>
<div class="container wrap">
This is a description of the Weekly Snaps in general.
</div>
			<?php
			while ( have_posts() ) :
				the_post();
                ?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<div class="container wrap">
                    <h2 class="large-margin-top"><?php echo get_the_date(); ?></h2>
					<?php the_content(); ?>
					<?php get_template_part( 'template-parts/edit-link' ); ?>
					</div>
				</article>
                <?php
    		endwhile;
			?>
		</main>
<?php
get_footer();
