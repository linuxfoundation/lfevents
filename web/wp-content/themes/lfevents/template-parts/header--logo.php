<?php
/**
 * Header Logo
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

?>

<div class="header--logo">

	<?php if ( is_lfeventsci() ) : ?>
	<a aria-label="<?php bloginfo( 'name' ); ?>" class="header--logo__link"
		title="<?php bloginfo( 'name' ); ?>"
		href="<?php echo esc_url( home_url( '/' ) ); ?>">
		<img width="161" height="48" loading="eager" class="header--logo__image"
			alt="Linux Foundation Events logo"
			src="<?php echo esc_html( get_stylesheet_directory_uri() . '/dist/assets/images/' . foundationpress_asset_path( 'lf-events-logo.svg' ) ); ?>">
	</a>
	<?php else : ?>
	<a aria-label="<?php bloginfo( 'name' ); ?>" class="header--logo__link"
		title="<?php bloginfo( 'name' ); ?>"
		href="<?php echo esc_url( home_url( '/' ) ); ?>">
		<img width="200" height="25" loading="eager" class="header--logo__image"
			alt="<?php bloginfo( 'name' ); ?>"
			src="<?php echo esc_html( get_stylesheet_directory_uri() . '/dist/assets/images/' . foundationpress_asset_path( 'logo_lfasiallc_white.svg' ) ); ?>">
	</a>
	<?php endif; ?>
</div>
