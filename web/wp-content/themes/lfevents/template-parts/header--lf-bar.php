<?php
/**
 * Header LF Bar
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

?>

<div class="header--lf-bar">

	<div class="container wrap header--lf-bar__container">

		<a class="header--lf-bar__logo-link" aria-label="Linux Foundation"
			title="Linux Foundation" href="https://linuxfoundation.org">
			<img width="258" height="15" loading="eager"
				class="header--lf-bar__image" alt="Linux Foundation logo"
				src="<?php echo esc_html( get_stylesheet_directory_uri() . '/dist/assets/images/' . foundationpress_asset_path( 'linux-foundation-hztl-white.svg' ) ); ?>">
		</a>


		<?php if ( is_lfeventsci() ) : ?>
		<div class="header--lf-bar__lfx">

			<!-- for anon -->
			<button
				class="is-auth0 only-anonymous is-login-link button-reset header--lf-bar__link">Sign
				In</button>

			<button
				class="is-auth0 only-anonymous is-signup-link button-reset header--lf-bar__create-profile">Create
				Community Profile</button>

				<!-- for auth  -->
				<img
					class="is-auth0 is-auth0-avatar only-authenticated header--lf-bar__avatar" />

				<a class="is-auth0 only-authenticated header--lf-bar__link"
					href="https://myprofile.lfx.linuxfoundation.org/">My LF
					Profile</a>
		</div>
		<?php endif; ?>


	</div>
</div>
