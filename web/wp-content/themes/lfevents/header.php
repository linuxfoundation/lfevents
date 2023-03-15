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
<html class="no-js" <?php language_attributes(); ?>>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<?php wp_head(); ?>
	<?php lfe_insert_favicon(); ?>
	<?php lfe_insert_structured_data(); ?>
	<?php lfe_insert_google_tag_manager_head(); ?>
	</head>

	<body <?php body_class( $_ENV['PANTHEON_SITE_NAME'] ); ?>>
		<?php wp_body_open(); ?>
		<?php
		// Skip Link.
		?>
		<a class="skip-link" href="#main">Skip to content</a>
		<?php lfe_insert_google_tag_manager_body(); ?>
		<div class="site-container <?php echo esc_html( ( ! show_non_event_menu() ) ? 'add-overflow' : '' ); ?>">
