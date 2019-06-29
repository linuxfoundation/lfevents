<?php
/**
 * The template for the homepage
 *
 * @package FoundationPress
 */

get_header(); ?>

<div data-sticky-container>
	<header class="main-header sticky" data-sticky data-sticky-on="large" data-options="marginTop:0;">

		<button class="menu-toggler button alignright hide-for-large" data-toggle="main-menu">
			<span class="hamburger-icon"></span>
		</button>

		<a class="home-link" href="<?php echo esc_url( home_url( '/' ) ); ?>"><img src="<?php echo get_stylesheet_directory_uri() . '/dist/assets/images/' . foundationpress_asset_path( 'logo_lfe_blue.png' ); //phpcs:ignore ?>"></a>

		<nav id="main-menu" class="main-menu show-for-large" data-toggler="show-for-large" role="navigation">
			<?php foundationpress_about_pages_nav(); ?>
		</nav>
	</header>
</div>

<div class="main-container about-page">
	<div class="main-grid grid-container">
		<main class="main-content-full-width">

			<?php
			$query = new WP_Query(
				array(
					'post_type' => 'lfe_about_page',
					'name' => 'homepage',
				)
			);
			if ( $query->have_posts() ) {
				while ( $query->have_posts() ) {
					$query->the_post();
					the_content();
				}
				wp_reset_postdata();
			}
			?>

			<hr>

			[list of events]

		</main>
	</div>
</div>
<?php
get_footer();
