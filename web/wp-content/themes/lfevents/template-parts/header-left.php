<?php
/**
 * Header Left
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

?>

<div class="header-left-wrapper">

<?php if ( is_lfeventsci() ) : ?>
<a class="logo-link" aria-label="Linux Foundation"
	title="Linux Foundation" href="https://linuxfoundation.org">
	<img width="109" height="36" loading="eager"
		alt="Linux Foundation logo"
		src="<?php echo esc_html( get_stylesheet_directory_uri() . '/dist/assets/images/' . foundationpress_asset_path( 'lf-logo.svg' ) ); ?>"></a>

<div class="home-link">
	<a aria-label="<?php bloginfo( 'name' ); ?>" class=""
		title="<?php bloginfo( 'name' ); ?>"
		href="<?php echo esc_url( home_url( '/' ) ); ?>">Events</a>
</div>
<?php else : ?>
<a aria-label="<?php bloginfo( 'name' ); ?>" class=""
	title="<?php bloginfo( 'name' ); ?>"
	href="<?php echo esc_url( home_url( '/' ) ); ?>">
	<img width="200" loading="eager" alt="<?php bloginfo( 'name' ); ?>"
		src="<?php echo esc_html( get_stylesheet_directory_uri() . '/dist/assets/images/' . foundationpress_asset_path( 'lf-asia-color.svg' ) ); ?>">
</a>
<?php endif; ?>
</div>
