<?php
/**
 * Register widget areas
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

if ( ! function_exists( 'foundationpress_footer_widgets' ) ) :

	function foundationpress_footer_widgets() {
		register_sidebar(
			array(
				'id'            => 'footer-widgets',
				'name'          => __( 'Footer widgets', 'foundationpress' ),
				'description'   => __( 'Drag widgets to this container.', 'foundationpress' ),
				'before_widget' => '<section id="%1$s" class="widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h6>',
				'after_title'   => '</h6>',
			)
		);

	}

	add_action( 'widgets_init', 'foundationpress_footer_widgets' );
endif;
