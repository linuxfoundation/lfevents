<?php
/**
 * Splash page header
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
	<a class="home-link"
		href="<?php echo esc_url( home_url( '/' ) ); ?>"><img loading="eager"
			src="<?php echo get_stylesheet_directory_uri() . '/dist/assets/images/' . foundationpress_asset_path( $home_img ); //phpcs:ignore ?>"></a>
</header>
</div>
