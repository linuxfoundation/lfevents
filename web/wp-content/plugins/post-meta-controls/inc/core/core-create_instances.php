<?php

namespace POSTMETACONTROLS;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Create instances of the classes
 *
 * @since 1.0.0
 */
function create_instances( $sidebars_props = array() ) {

	$instances = array(
		'sidebars' => array(),
		'tabs'     => array(),
		'panels'   => array(),
		'settings' => array(),
	);

	foreach ( $sidebars_props as $sidebar_props ) {

		if ( empty( $sidebar_props['tabs'] ) ) {
			continue;
		}

		$sidebar = new Sidebar( $sidebar_props );

		$instances['sidebars'][] = $sidebar;

		$sidebar_path = $sidebar->get_id();

		$root_props = array(
			'data_key_prefix_from_sidebar' => $sidebar->get_data_key_prefix(),
			'id_prefix'                    => $sidebar->get_id_prefix(),
			'post_type'                    => $sidebar->get_post_type(),
		);

		foreach ( $sidebar_props['tabs'] as $tab_props ) {

			if ( empty( $tab_props['panels'] ) ) {
				continue;
			}

			$tab_props = add_root_props( $tab_props, $sidebar_path, $root_props );

			$tab = new Tab( $tab_props );

			$instances['tabs'][] = $tab;

			$tab_path = array( $sidebar_path, $tab->get_id() );

			foreach ( $tab_props['panels'] as $panel_props ) {

				if ( empty( $panel_props['settings'] ) ) {
					continue;
				}

				$panel_props = add_root_props( $panel_props, $tab_path, $root_props );

				$panel = new Panel( $panel_props );

				$instances['panels'][] = $panel;

				$panel_path = array_merge( $tab_path, array( $panel->get_id() ) );

				foreach ( $panel_props['settings'] as $setting_props ) {

					$setting_props = add_root_props( $setting_props, $panel_path, $root_props, true );

					$setting = create_setting_instance( $setting_props );

					if ( is_array( $setting ) ) {

						foreach ( $setting as $setting_ind ) {
							if ( ! empty( $setting_ind ) ) {
								$instances['settings'][] = $setting_ind;
							}
						}

					} elseif ( ! empty( $setting ) ) {
						$instances['settings'][] = $setting;
					}
				}
			}
		}
	}

	return $instances;
}

/**
 * Add sidebar props to the props of a children element
 *
 * @since 1.1.0
 */
function add_root_props(
	$prop_raw = array(),
	$path = '',
	$root_props = array(),
	$add_data_key_prefix_from_sidebar = false
) {
	$prop_raw['path']      = $path;
	$prop_raw['id_prefix'] = $root_props['id_prefix'];
	$prop_raw['post_type'] = $root_props['post_type'];

	if ( true === $add_data_key_prefix_from_sidebar ) {
		$prop_raw['data_key_prefix_from_sidebar'] =
			$root_props['data_key_prefix_from_sidebar'];
	}

	return $prop_raw;
}

/**
 * Create setting class instance
 *
 * @since 1.1.0
 */
function create_setting_instance( $setting_props ) {

	$setting = null;

	switch ( $setting_props['type'] ) {
		case 'buttons':
			$setting = new Buttons( $setting_props );
			break;

		case 'checkbox':
			$setting = new Checkbox( $setting_props );
			break;

		case 'checkbox_multiple':
			$setting = new CheckboxMultiple( $setting_props );
			break;

		case 'color':
			$setting = new Color( $setting_props );
			break;

		case 'custom_text':
			$setting = new CustomText( $setting_props );
			break;

		case 'date_range':
			$setting = new DateRange( $setting_props );
			break;

		case 'date_single':
			$setting = new DateSingle( $setting_props );
			break;

		case 'image':
			$setting = new Image( $setting_props );
			break;

		case 'image_multiple':
			$setting = new ImageMultiple( $setting_props );
			break;

		case 'radio':
			$setting = new Radio( $setting_props );
			break;

		case 'range':
			$setting = new Range( $setting_props );
			break;

		case 'range_float':
			$setting = new RangeFloat( $setting_props );
			break;

		case 'select':
			$setting = new Select( $setting_props );
			break;

		case 'text':
			$setting = new Text( $setting_props );
			break;

		case 'textarea':
			$setting = new Textarea( $setting_props );
			break;

		// Pro:
		case 'custom_component':
			if ( class_exists( __NAMESPACE__ . '\CustomComponent' ) ) {
				$setting = new CustomComponent( $setting_props );
			}
			break;

		case 'custom_html':
			if ( class_exists( __NAMESPACE__ . '\CustomHTML' ) ) {
				$setting = new CustomHTML( $setting_props );
			}
			break;

		case 'repeatable':
			if ( class_exists( __NAMESPACE__ . '\Repeatable' ) ) {
				$setting = new Repeatable( $setting_props );
			}
			break;

		default:
			break;
	}

	return $setting;
}
