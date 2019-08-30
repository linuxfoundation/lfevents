<?php
/**
 * Template Name: Multi-part Page with Menu
 *
 * @package FoundationPress
 */

/**
 * Adds the menu section to the bottom of the content of each post.
 *
 * @param string $content Content of the post.
 */
function lfe_content_filter( $content ) {
	// get all div tags of class "wp-block-cgb-block-tab-container-block".
	$tag_regex = '/<[^>]*class="[^"]*\bwp-block-cgb-block-tab-container-block\b[^"]*"[^>]*>/i';
	preg_match_all( $tag_regex, $content, $matches );

	if ( ! $matches ) {
		return $content;
	}

	$menu = '<div class="tab-container-block-menu"><ul>';

	// grab the data-menu-title and id from each tag to construct the menu.
	foreach ( $matches[0] as $match ) {
		preg_match( '/id="([^"]*)"/i', $match, $id );
		preg_match( '/data-menu-title="([^"]*)"/i', $match, $menu_title );

		$menu .= '<li><a href="#' . $id[1] . '">' . $menu_title[1] . '</a></li>';
	}

	$menu .= '</ul></div>';

	// add the menu markup to the end of $content.
	return $content . $menu;
}
add_filter( 'the_content', 'lfe_content_filter' );

include( get_template_directory() . '/singular.php' );
