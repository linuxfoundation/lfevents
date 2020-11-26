<?php
/**
 * The Global Nav for the site
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

?>
<header class="main-header">

	<?php get_template_part( 'template-parts/header-left' ); ?>

	<button class="lf-hamburger" type="button" aria-label="Toggle Menu">
		<span class="hamburger-inner"></span>
	</button>

	<div class="mobile-menu-wrapper">
		<div class="header-menu-back"></div>

		<nav id="main-menu" class="main-menu" role="navigation">

			<?php
			// nav to the left.
			foundationpress_about_pages_nav();
			?>

			<?php is_lfeventsci() && foundationpress_lf_nav(); ?>

			<?php if ( is_lfeventsci() ) : ?>
			<ul class="menu lf-menu lfx-menu">

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

				<li
					class="is-auth0 only-authenticated menu-item-has-children menu-item">
					<a href="#">My Profile</a>

					<ul class="sub-menu">
						<li class="is-auth0 only-authenticated menu-item"><a
								href="https://myprofile.lfx.linuxfoundation.org/">Manage
								Profile</a></li>
						<li
							class="is-auth0 is-logout-link only-authenticated menu-item">
							<a href="#">Logout</a>
						</li>
					</ul>
				</li>
			</ul>
			<?php endif; ?>
		</nav>
	</div>
</header>
