<?php
/**
 * Template Name: Upcoming Events
 * Template Post Type: lfe_about_page
 *
 * @package FoundationPress
 */

get_header(); ?>

<div class="main-container">
	<div class="main-grid">
		<header>
			<h1 class="entry-title"><?php the_title(); ?></h1>
		</header>
		<main class="main-content-full-width">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<?php get_template_part( 'template-parts/content', 'page' ); ?>
			<?php endwhile; ?>
		</main>
	</div>
</div>
<?php
get_footer();
