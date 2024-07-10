<?php
/**
 * Register theme support for languages, menus, post-thumbnails, post-formats etc.
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

if ( ! function_exists( 'foundationpress_theme_support' ) ) :

	/**
	 * Comment.
	 */
	function foundationpress_theme_support() {
		// Add language support.
		load_theme_textdomain( 'foundationpress', get_template_directory() . '/languages' );

		// Switch default core markup for search form, comment form, and comments to output valid HTML5.
		add_theme_support(
			'html5',
			array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
			)
		);

		// Add menu support.
		add_theme_support( 'menus' );

		// Let WordPress manage the document title.
		add_theme_support( 'title-tag' );

		// Add post thumbnail support: http://codex.wordpress.org/Post_Thumbnails.
		add_theme_support( 'post-thumbnails' );

		// RSS thingy.
		add_theme_support( 'automatic-feed-links' );

		// Add post formats support: http://codex.wordpress.org/Post_Formats.
		add_theme_support( 'post-formats', array( 'aside', 'gallery', 'link', 'image', 'quote', 'status', 'video', 'audio', 'chat' ) );

		add_theme_support( 'editor-styles' );

		// Add support for Block Styles.
		add_theme_support( 'align-wide' );

		// Disable core block patterns.
		remove_theme_support( 'core-block-patterns' );

		// Remove duotone SVG filter injection.
		remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );

		// Disable new widget screen.
		remove_theme_support( 'widgets-block-editor' );

		// Add foundation.css as editor style https://codex.wordpress.org/Editor_Style.
		add_editor_style( 'dist/css/editor.css' ); //phpcs:ignore.
	}

	add_action( 'after_setup_theme', 'foundationpress_theme_support' );
endif;
