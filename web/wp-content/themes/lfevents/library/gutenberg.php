<?php
/**
 * Gutenberg functions.
 *
 * @package WordPress.
 */

if ( ! function_exists( 'foundationpress_gutenberg_support' ) ) :
	/**
	 * Each color should match in _custom-color-palette.scss
	 */
	function foundationpress_gutenberg_support() {

		add_theme_support(
			'editor-color-palette',
			array(
				array(
					'name'  => __( 'Black' ),
					'slug'  => 'black',
					'color' => '#212326',
				),
				array(
					'name'  => __( 'Charcoal' ),
					'slug'  => 'charcoal',
					'color' => '#393c41',
				),
				array(
					'name'  => __( 'Dark Gray' ),
					'slug'  => 'dark-gray',
					'color' => '#5d626a',
				),
				array(
					'name'  => __( 'Light Gray' ),
					'slug'  => 'light-gray',
					'color' => '#d3d5d9',
				),
				array(
					'name'  => __( 'Off White' ),
					'slug'  => 'off-white',
					'color' => '#ecedee',
				),
				array(
					'name'  => __( 'White' ),
					'slug'  => 'white',
					'color' => '#fefefe',
				),
				array(
					'name'  => __( 'Dark Fuschia' ),
					'slug'  => 'dark-fuschia',
					'color' => '#6e1042',
				),
				array(
					'name'  => __( 'Dark Violet' ),
					'slug'  => 'dark-violet',
					'color' => '#411E4F',
				),
				array(
					'name'  => __( 'Dark Indigo' ),
					'slug'  => 'dark-indigo',
					'color' => '#1A267D',
				),
				array(
					'name'  => __( 'Dark Blue' ),
					'slug'  => 'dark-blue',
					'color' => '#17405c',
				),
				array(
					'name'  => __( 'Dark Aqua' ),
					'slug'  => 'dark-aqua',
					'color' => '#0e5953',
				),
				array(
					'name'  => __( 'Dark Green' ),
					'slug'  => 'dark-green',
					'color' => '#0b5329',
				),
				array(
					'name'  => __( 'Light Fuschia' ),
					'slug'  => 'light-fuschia',
					'color' => '#AD1457',
				),
				array(
					'name'  => __( 'Light Violet' ),
					'slug'  => 'light-violet',
					'color' => '#6C3483',
				),
				array(
					'name'  => __( 'Light Indigo' ),
					'slug'  => 'light-indigo',
					'color' => '#4653B0',
				),
				array(
					'name'  => __( 'Light Blue' ),
					'slug'  => 'light-blue',
					'color' => '#2874A6',
				),
				array(
					'name'  => __( 'Light Aqua' ),
					'slug'  => 'light-aqua',
					'color' => '#148f85',
				),
				array(
					'name'  => __( 'Light Green' ),
					'slug'  => 'light-green',
					'color' => '#117a3d',
				),
				array(
					'name'  => __( 'Dark Chartreuse' ),
					'slug'  => 'dark-chartreuse',
					'color' => '#3d5e0f',
				),
				array(
					'name'  => __( 'Dark Yellow' ),
					'slug'  => 'dark-yellow',
					'color' => '#878700',
				),
				array(
					'name'  => __( 'Dark Gold' ),
					'slug'  => 'dark-gold',
					'color' => '#8c7000',
				),
				array(
					'name'  => __( 'Dark Orange' ),
					'slug'  => 'dark-orange',
					'color' => '#784e12',
				),
				array(
					'name'  => __( 'Dark Umber' ),
					'slug'  => 'dark-umber',
					'color' => '#6E2C00',
				),
				array(
					'name'  => __( 'Dark Red' ),
					'slug'  => 'dark-red',
					'color' => '#641E16',
				),
				array(
					'name'  => __( 'Light Chartreuse' ),
					'slug'  => 'light-chartreuse',
					'color' => '#699b23',
				),
				array(
					'name'  => __( 'Light Yellow' ),
					'slug'  => 'light-yellow',
					'color' => '#b0b000',
				),
				array(
					'name'  => __( 'Light Gold' ),
					'slug'  => 'light-gold',
					'color' => '#c29b00',
				),
				array(
					'name'  => __( 'Light Orange' ),
					'slug'  => 'light-orange',
					'color' => '#c2770e',
				),
				array(
					'name'  => __( 'Light Umber' ),
					'slug'  => 'light-umber',
					'color' => '#b8510d',
				),
				array(
					'name'  => __( 'Light Red' ),
					'slug'  => 'light-red',
					'color' => '#922B21',
				),
				array(
					'name'  => __( 'LF Light Blue' ),
					'slug'  => 'lf-primary-400',
					'color' => '#0099cc',
				),
				array(
					'name'  => __( 'LF Dark Blue' ),
					'slug'  => 'lf-primary-700',
					'color' => '#003366',
				),
				array(
					'name'  => __( 'LF Text Color' ),
					'slug'  => 'lf-grey-700',
					'color' => '#333333',
				),
				array(
					'name'  => __( 'LF Light Text Color' ),
					'slug'  => 'lf-grey-400',
					'color' => '#7a7a7a',
				),
			)
		);

	}

	add_action( 'after_setup_theme', 'foundationpress_gutenberg_support' );
endif;
