<?php
/**
 * The template for displaying the header
 *
 * Displays all of the head element and everything up until the "container" div.
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

 $event_menu = ( show_non_event_menu() ) ? '' : 'add-overflow';

 $cncf_font = get_post_meta( lfe_get_event_parent_id( $post ), 'lfes_cncf_font', true ) ? 'use-cncf-font' : '';

 $all_classes = array(
	 'site-container',
	 $event_menu,
	 $cncf_font,
 );
 $classes = implode( ' ', $all_classes );
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
		<a class="skip-link" href="#main">Skip to content</a>
		<?php lfe_insert_google_tag_manager_body(); ?>
		<div class="<?php echo esc_attr( $classes ); ?>">
