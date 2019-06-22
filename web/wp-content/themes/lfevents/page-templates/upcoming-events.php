<?php
/**
 * Template Name: Upcoming Events
 * Template Post Type: lfe_about_page
 *
 * @package FoundationPress
 */

get_header(); ?>

<div data-sticky-container>
	<header class="main-header sticky" data-sticky data-sticky-on="large" data-options="marginTop:0;">

		<button class="menu-toggler button alignright hide-for-large" data-toggle="main-menu">
			<span class="hamburger-icon"></span>
		</button>

		<a class="home-link" href="<?php echo esc_url( home_url( '/' ) ); ?>"><img src="<?php echo get_stylesheet_directory_uri() . '/dist/assets/images/' . foundationpress_asset_path( 'logo_lfe_blue.png' ); ?>"></a>

		<nav id="main-menu" class="main-menu show-for-large" data-toggler="show-for-large" role="navigation">
			<?php foundationpress_about_pages_nav(); ?>
		</nav>
	</header>
</div>

<div class="main-container about-page">
	<div class="main-grid grid-container">
		<main class="main-content-full-width">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<?php get_template_part( 'template-parts/content' ); ?>
			<?php endwhile; ?>

			<?php lfe_get_upcoming_events(); ?>
		</main>
	</div>
</div>
<?php
get_footer();
