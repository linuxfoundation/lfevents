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
				<?php get_template_part( 'template-parts/content' ); ?>
			<?php endwhile; ?>
		</main>
	</div>
</div>
<?php
get_footer();
