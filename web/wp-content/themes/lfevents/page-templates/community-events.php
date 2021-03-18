<?php
/**
 * Template Name: Community Events
 * Template Post Type: lfe_about_page
 *
 * @package FoundationPress
 */

get_header();
get_template_part( 'template-parts/global-header' );
?>

<main role="main" id="main" class="main-container-body">
<?php get_template_part( 'template-parts/non-event-hero' ); ?>

	<div class="grid-container">
		<div class="grid-x grid-margin-x">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<div class="cell large-8 xlarge-margin-bottom">
					<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
						<div class="entry-content container wrap">
								<?php the_content(); ?>
						</div>
					</article>
				</div>
			<?php endwhile; ?>
			<div class="cell large-4">
				<h4>Submit Your Event Listing</h4>
				<p>We are pleased to list events held by open source community organizations and our members. 
				We do not list events that do not adhere to an Event Code of Conduct.</p>
				<div style="height:20px" aria-hidden="true" class="wp-block-spacer"></div>
				<?php
				echo do_shortcode( '[hubspot type=form portal=8112310 id=b48baa7e-925a-4380-88b8-8eaf7e3d4832]' );
				?>


			</div>
		</div>
	</div>
</main>
<?php
get_footer();
