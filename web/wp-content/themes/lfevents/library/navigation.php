<?php
/**
 * Register Menus
 *
 * @link http://codex.wordpress.org/Function_Reference/register_nav_menus#Examples
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

register_nav_menus(
	array(
		'about-pages-nav' => esc_html__( 'Non-Event Nav' ),
		'lf-nav' => esc_html__( 'LF Sites Nav' ),
	)
);


/**
 * Non-Event Nav
 *
 * @link http://codex.wordpress.org/Function_Reference/wp_nav_menu
 */
if ( ! function_exists( 'foundationpress_about_pages_nav' ) ) {
	/** Comment */
	function foundationpress_about_pages_nav() {
		wp_nav_menu(
			array(
				'container'      => false,
				'menu_class'     => 'menu main-links-menu',
				'items_wrap'     => '<ul id="%1$s" class="%2$s">%3$s</ul>',
				'theme_location' => 'about-pages-nav',
				'depth'          => 2,
				'fallback_cb'    => false,
			)
		);
	}
}

/**
 * LF Sites nav
 *
 * @link http://codex.wordpress.org/Function_Reference/wp_nav_menu
 */
if ( ! function_exists( 'foundationpress_lf_nav' ) ) {
	/** Comment */
	function foundationpress_lf_nav() {
		wp_nav_menu(
			array(
				'container'      => false,
				'menu_class'     => 'menu all-lf-menu',
				'items_wrap'     => '<ul id="%1$s" class="%2$s">%3$s</ul>',
				'theme_location' => 'lf-nav',
				'depth'          => 2,
				'fallback_cb'    => false,
			)
		);
	}
}
