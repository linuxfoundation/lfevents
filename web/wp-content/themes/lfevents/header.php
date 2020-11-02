<?php
/**
 * The template for displaying the header
 *
 * Displays all of the head element and everything up until the "container" div.
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

?>
<!doctype html>
<html class="no-js" <?php language_attributes(); ?> >
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<?php lfe_insert_favicon(); ?>
		<link rel="apple-touch-icon" sizes="180x180" href="<?php echo get_stylesheet_directory_uri() . '/dist/assets/images/favicons/' . foundationpress_asset_path( 'apple-touch-icon.png' ); //phpcs:ignore?>">
		<link rel="icon" type="image/png" sizes="16x16" href="<?php echo get_stylesheet_directory_uri() . '/dist/assets/images/favicons/' . foundationpress_asset_path( 'favicon-16x16.png' );  //phpcs:ignore?>">
		<link rel="manifest" href="<?php echo get_stylesheet_directory_uri() . '/dist/assets/images/favicons/' . foundationpress_asset_path( 'site.webmanifest' );  //phpcs:ignore?>">
		<link rel="mask-icon" href="<?php echo get_stylesheet_directory_uri() . '/dist/assets/images/favicons/' . foundationpress_asset_path( 'safari-pinned-tab.svg' );  //phpcs:ignore?>">
		<meta name="msapplication-TileColor" content="#da532c">
		<meta name="msapplication-config" content="<?php echo get_stylesheet_directory_uri() . '/dist/assets/images/favicons/' . foundationpress_asset_path( 'browserconfig.xml' );  //phpcs:ignore?>">
		<meta name="theme-color" content="#ffffff">
		<?php lfe_insert_google_tag_manager_head(); ?>
		<?php wp_head(); ?>
		<?php lfe_insert_structured_data(); ?>
	</head>
	<body <?php body_class( $_ENV['PANTHEON_SITE_NAME'] ); ?>>
		<?php lfe_insert_google_tag_manager_body(); ?>
		<div class="site-container">
