<?php
/**
 * The Global Nav for the site
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

if ( is_lfeventsci() ) {
	$home_img = 'logo_lfevents_white.svg';
} else {
	$home_img = 'logo_lfasiallc_white.svg';
}
?>

<div data-sticky-container>
	<header class="main-header sticky" data-sticky data-sticky-on="large" data-options="marginTop:0;">

		<button class="menu-toggler button alignright hide-for-large" data-toggle="main-menu" aria-label="Mobile Navigation">
			<span class="hamburger-icon is-white"></span>
		</button>

		<a aria-label="Go to home page" class="home-link" href="<?php echo esc_url( home_url( '/' ) ); ?>"><img loading="eager" alt="<?php bloginfo( 'name' ); ?>" src="<?php echo get_stylesheet_directory_uri() . '/dist/assets/images/' . foundationpress_asset_path( $home_img ); //phpcs:ignore ?>"></a>

		<nav id="main-menu" class="main-menu show-for-large" data-toggler="show-for-large" role="navigation">
			<?php foundationpress_about_pages_nav(); ?>
			<img class="is-auth0 is-auth0-avatar" />
			<a class="is-auth0 only-anonymous is-login-link">Sign In</a>
			<a class="is-auth0 only-anonymous is-signup-link">Create Community Profile</a>
			<a class="is-auth0 only-authenticated is-logout-link">Logout</a>
		</nav>
	</header>
</div>
