<?php
/**
 * The Global Nav for the site
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

if ( is_lfeventsci() ) {
	$home_img = 'lf-logo.svg';
} else {
	$home_img = 'logo_lfasiallc_white.svg';
}
?>

<div data-sticky-container>
	<header class="main-header sticky" data-sticky data-sticky-on="large"
		data-options="marginTop:0;">

		<div class="header-left-wrapper">

			<a class="logo-link" aria-label="Linux Foundation" title="Linux Foundation"
				href="https://linuxfoundation.org">
				<img width="109" height="36" loading="eager"
					alt="<?php bloginfo( 'name' ); ?>"
					src="<?php echo esc_html( get_stylesheet_directory_uri() . '/dist/assets/images/' . foundationpress_asset_path( $home_img ) ); ?>"></a>

			<div class="home-link">
				<a aria-label="Events Homepage" class=""
					href="<?php echo esc_url( home_url( '/' ) ); ?>">Events</a>
			</div>

		</div>

		<button class="menu-toggler button hide-for-large"
			data-toggle="main-menu" aria-label="Mobile Navigation"
			style="border: 1px black solid">
			<span class="hamburger-icon"></span>
		</button>

		<nav id="main-menu" class="main-menu show-for-large"
			data-toggler="show-for-large" role="navigation">

			<!-- section to the left -->
			<?php foundationpress_about_pages_nav(); ?>

			<!-- section to the right -->
			<?php foundationpress_lf_nav(); ?>

			<ul class="lf-menu lfx-menu">

				<!-- for anon -->
				<li class="is-auth0 only-anonymous">
					<a href="#"
						class="is-auth0 only-anonymous is-login-link">Sign
						In</a>
				</li>

				<li class=""><a href="#"
						class="is-auth0 only-anonymous is-signup-link">Create
						Community Profile</a></li>

				<!-- for auth  -->
				<li class="is-auth0 only-authenticated"><img
						class="is-auth0 is-auth0-avatar" /></li>

				<li class="is-auth0 only-authenticated menu-item-has-children">
					<a href="#">My Profile</a>

					<ul class="sub-menu">
						<li class="is-auth0 only-authenticated"><a
								href="https://myprofile.lfx.linuxfoundation.org/">Manage
								Profile</a></li>
						<li class="is-auth0 is-logout-link only-authenticated">
							<a href="#">Logout</a>
						</li>
					</ul>
				</li>
			</ul>
		</nav>
	</header>
</div>
