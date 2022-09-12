<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * e.g., it puts together the home page when no home.php file exists.
 *
 * Learn more: {@link https://codex.wordpress.org/Template_Hierarchy}
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

get_header();
get_template_part( 'template-parts/header-global' );
?>

<main role="main" id="main" class="main-container-body">
	<?php get_template_part( 'template-parts/non-event-hero' ); ?>
	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<div class="container wrap">
		<?php
		if ( have_posts() ) :
			;
			?>
			<div class="grid-x grid-margin-x medium-up-2 large-up-3">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
<article class="cell callout large-margin-bottom">
								<h4 class="no-margin line-height-tight"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
								<p class="text-small small-margin-top"><?php echo the_date(); ?></p>
								<p class=""><?php the_excerpt(); ?></p>
								</article>
			<?php endwhile; ?>
</div>
			<?php
			else :
				get_template_part( 'template-parts/content', 'none' );
			endif;
			?>
		</div>

			<?php
			if ( function_exists( 'foundationpress_pagination' ) ) :
				foundationpress_pagination();
			elseif ( is_paged() ) :
				?>
				<nav id="post-nav">
					<div class="post-previous"><?php next_posts_link( __( '&larr; Older posts', 'foundationpress' ) ); ?></div>
					<div class="post-next"><?php previous_posts_link( __( 'Newer posts &rarr;', 'foundationpress' ) ); ?></div>
				</nav>
			<?php endif; ?>
		</main>
<?php
get_footer();
