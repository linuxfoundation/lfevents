<?php
/**
 * The Global Nav for the site
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

?>
<header class="header-global">
	<?php get_template_part( 'template-parts/header--lf-bar' ); ?>

	<div class="header-global__logo-nav">
		<div class="container wrap header-global__logo-nav-container">

			<?php get_template_part( 'template-parts/header--logo' ); ?>

			<button class="header-global__hamburger" type="button"
				aria-label="Toggle Menu">
				<span></span>
			</button>

			<div class="header-global__mobile-menu-container">
				<div class="header-global__mobile-menu-underlay"></div>
				<nav class="header-global__main-menu" role="navigation">
					<?php foundationpress_about_pages_nav(); ?>

					<?php if ( is_lfeventsci() ) : ?>
					<div class="header-global__mobile-menu-lfx">
						<div class="header-global__mobile-menu-addition">
							<!-- for anon -->
							<button
								class="is-auth0 only-anonymous is-login-link button-reset header-global__mobile-menu-lfx-link">Sign
								In</button>
							<!-- for auth  -->
							<a class="is-auth0 only-authenticated header-global__mobile-menu-lfx-link"
								href="https://myprofile.lfx.linuxfoundation.org/">My
								LF
								Profile</a>
						</div>
						<button
							class="is-auth0 only-anonymous is-signup-link button-reset header-global__mobile-menu-lfx-button">Create
							Community Profile</button>
					</div>
					<?php endif; ?>
				</nav>
			</div>
		</div>
	</div>
</header>
