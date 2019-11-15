<?php

namespace POSTMETACONTROLS;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Class Base Setting DateSingle
 */
class DateSingle extends Setting {

	use DateLocales;

	protected function set_defaults() {
		$this_defaults = array(
			'type'              => 'date_single',
			'default_value'     => '',
			'format'            => 'DD/MM/YYYY',
			'locale'            => 'en',
			'unavailable_dates' => array( array( 'before', 'today' ) ),
		);

		$parent_defaults = Setting::get_defaults();

		$this->props_defaults =
			wp_parse_args( $this_defaults, $parent_defaults );
	}

	protected function set_schema() {
		$this_schema = array(
			'default_value' => array(
				'type'   => 'text',
				'for_js' => true,
			),
			'format' => array(
				'type'   => 'text',
				'for_js' => true,
			),
			'locale' => array(
				'type'   => 'id',
				'for_js' => true,
			),
			'unavailable_dates' => array(
				'type'   => array( '_all' =>  array( '_all' => 'text' ) ),
				'for_js' => true,
			),
		);

		$parent_schema = Setting::get_schema();

		$this->props_schema =
			wp_parse_args( $this_schema, $parent_schema );
	}

	public function enqueue_locale() {

		$locale  = $this->props['locale'];
		$locales = $this->get_date_locales();

		if ( false === in_array( $locale, $locales ) ) {
			return;
		}

		// Add the action to enqueue the locale script.
		add_action(
			'pmc_before_enqueue',
			function() {

				// Enqueue the selected locale script.
				wp_enqueue_script(
					PLUGIN_NAME . '-moment_locales',
					BUILD_DIR . PLUGIN_NAME . '-moment-locales.js',
					array(),
					PLUGIN_VERSION,
					true // Enqueue in the footer.
				);
			}
		);
	}
}
