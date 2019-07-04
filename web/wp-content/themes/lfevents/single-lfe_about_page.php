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

get_header(); ?>

<div data-sticky-container>
	<header class="main-header sticky" data-sticky data-sticky-on="large" data-options="marginTop:0;">

		<button class="menu-toggler button alignright hide-for-large" data-toggle="main-menu">
			<span class="hamburger-icon"></span>
		</button>

		<a class="home-link" href="<?php echo esc_url( home_url( '/' ) ); ?>"><img src="<?php echo get_stylesheet_directory_uri() . '/dist/assets/images/' . foundationpress_asset_path( 'lfevents-horizontal-white-blue.svg' ); //phpcs:ignore ?>"></a>

		<nav id="main-menu" class="main-menu show-for-large" data-toggler="show-for-large" role="navigation">
			<?php foundationpress_about_pages_nav(); ?>
		</nav>
	</header>
</div>

<div class="main-container">
	<div class="main-grid">
		<main class="main-content-full-width">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<?php get_template_part( 'template-parts/content', 'page' ); ?>
				<?php comments_template(); ?>
			<?php endwhile; ?>
		</main>
		<?php /* get_sidebar(); */ ?>
	</div>
</div>
<?php
get_footer();
