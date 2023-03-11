<?php
/**
 * CPT definitions
 *
 * @link       https://events.linuxfoundation.org/
 * @since      1.1.0
 *
 * @package    Lf_Mu
 * @subpackage Lf_Mu/admin/partials
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$opts = array(
	'labels'       => array(
		'name'          => __( 'About Pages' ),
		'singular_name' => __( 'About Page' ),
		'all_items'     => __( 'All About Pages' ),
	),
	'public'       => true,
	'has_archive'  => true,
	'show_in_rest' => true,
	'hierarchical' => true,
	'menu_icon'    => 'dashicons-info',
	'rewrite'      => array( 'slug' => 'about' ),
	'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'page-attributes', 'author' ),
);

register_post_type( 'lfe_about_page', $opts );

$opts = array(
	'public'        => true,
	'has_archive'   => true,
	'show_in_rest'  => true,
	'hierarchical'  => true,
	'menu_icon'     => 'dashicons-admin-page',
	'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'custom-fields', 'page-attributes', 'author' ),
	'menu_position' => 30,
);

$current_year = gmdate( 'Y' );
for ( $x = 2016; $x <= $current_year; $x++ ) {
	$opts['labels']  = array(
		'name'          => $x . ' Events',
		'singular_name' => $x . ' Event',
		'all_items'     => 'All ' . $x . ' Events',
	);
	$opts['rewrite'] = array( 'slug' => 'archive/' . $x );

	register_post_type( 'lfevent' . $x, $opts );
}

$opts = array(
	'labels'             => array(
		'name'          => __( 'Speakers' ),
		'singular_name' => __( 'Speaker' ),
		'all_items'     => __( 'All Speakers' ),
	),
	'show_in_rest'       => true,
	'public'             => false, // not publicly viewable.
	'publicly_queryable' => false, // not publicly queryable.
	'show_ui'            => true, // But still show admin UI.
	'menu_icon'          => 'dashicons-groups',
	'rewrite'            => array( 'slug' => 'speakers' ),
	'supports'           => array( 'title', 'editor', 'thumbnail', 'custom-fields', 'author', 'revisions' ),
);

register_post_type( 'lfe_speaker', $opts );

$opts = array(
	'labels'             => array(
		'name'          => __( 'Staff' ),
		'singular_name' => __( 'Staff' ),
		'all_items'     => __( 'All Staff' ),
	),
	'show_in_rest'       => true,
	'public'             => false, // not publicly viewable.
	'publicly_queryable' => false, // not publicly queryable.
	'show_ui'            => true, // But still show admin UI.
	'menu_icon'          => 'dashicons-buddicons-buddypress-logo',
	'rewrite'            => array( 'slug' => 'staff' ),
	'supports'           => array( 'title', 'editor', 'thumbnail', 'custom-fields', 'author' ),
);

register_post_type( 'lfe_staff', $opts );

$opts = array(
	'labels'             => array(
		'name'          => __( 'Sponsors' ),
		'singular_name' => __( 'Sponsor' ),
		'all_items'     => __( 'All Sponsors' ),
	),
	'show_in_rest'       => true,
	'public'             => false, // not publicly viewable.
	'publicly_queryable' => false, // not publicly queryable.
	'show_ui'            => true, // But still show admin UI.
	'menu_icon'          => 'dashicons-star-filled',
	'rewrite'            => array( 'slug' => 'sponsors' ),
	'supports'           => array( 'title', 'editor', 'thumbnail', 'custom-fields', 'author', 'revisions' ),
);

register_post_type( 'lfe_sponsor', $opts );

$opts = array(
	'labels'             => array(
		'name'          => __( 'Community Events' ),
		'singular_name' => __( 'Community Event' ),
		'all_items'     => __( 'All Community Events' ),
	),
	'public'             => false, // not publicly viewable.
	'publicly_queryable' => false, // not publicly queryable.
	'show_ui'            => true, // But still show admin UI.
	'has_archive'        => false,
	'show_in_nav_menus'  => false,
	'show_in_rest'       => true,
	'hierarchical'       => true,
	'menu_icon'          => 'dashicons-admin-site',
	'rewrite'            => array( 'slug' => 'community' ),
	'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'custom-fields', 'author' ),
);

register_post_type( 'lfe_community_event', $opts );