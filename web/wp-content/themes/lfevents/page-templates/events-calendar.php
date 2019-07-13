<?php
/**
 * Template Name: Events Calendar
 * Template Post Type: lfe_about_page
 *
 * @package FoundationPress
 */

get_header();
get_template_part( 'template-parts/global-nav' );
?>

<div class="main-container about-page">
	<div class="main-grid grid-container">
		<main class="main-content-full-width">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<header>
						<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
					</header>
					<div class="event-calendar-container">
						<?php the_content(); ?>
					</div>
				</article>
			<?php endwhile; ?>
		</main>
	</div>
</div>
<?php
get_footer();
