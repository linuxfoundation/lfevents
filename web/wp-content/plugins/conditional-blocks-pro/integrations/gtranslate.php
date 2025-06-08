<?php
class CB_GTranslate_Integration {
	private $is_gtranslate_active = false;
	private $is_pro = false;
	private $tested_version = '3.0.7';

	public function __construct() {
		$this->is_gtranslate_active = class_exists( 'GTranslate' );

		add_filter( 'conditional_blocks_register_condition_categories', [ $this, 'register_categories' ], 10, 1 );
		add_filter( 'conditional_blocks_register_condition_types', [ $this, 'register_conditions' ], 10, 1 );
				$this->is_pro = true;
		add_filter( 'conditional_blocks_register_check_gtranslate_language', [ $this, 'check_gtranslate_language' ], 10, 2 );
			}

	public function register_categories( $categories ) {
		$categories[] = [ 
			'value' => 'gtranslate',
			'label' => __( 'GTranslate', 'conditional-blocks' ),
			'icon' => plugins_url( 'assets/images/mini-colored/gtranslate.svg', __DIR__ ),
			'tag' => 'plugin',
		];
		return $categories;
	}

	public function register_conditions( $conditions ) {



		$conditions[] = [ 
			'type' => 'gtranslate_language',
			'label' => __( 'Current Language', 'conditional-blocks' ),
			'is_pro' => true,
			'is_disabled' => ! $this->is_gtranslate_active || ! $this->is_pro,
			'description' => __( 'Check if the current language matches the selected language.', 'conditional-blocks' ),
			'category' => 'gtranslate',
			'fields' => [ 
				[ 
					'key' => 'language',
					'type' => 'select',
					'attributes' => [ 
						'label' => __( 'Language', 'conditional-blocks' ),
						'help' => __( 'Select the language to check against', 'conditional-blocks' ),
						'placeholder' => __( 'Select a language', 'conditional-blocks' ),
						'searchable' => true
					],
					'options' => $this->get_language_options()
				],
				[ 
					'key' => 'blockAction',
					'type' => 'blockAction',
				],
			],
		];


		return $conditions;
	}


	private function get_gtranslate_current_lang() {
		/**
		 * This disables caching of the original content on pages where GTranslate and Conditions Blocks are used.
		 * Set no-cache header to prevent caching of the original content
		 * This is important for GTranslate paid versions to ensure fresh translations
		 * are always served and not cached versions
		 */
		header( 'Cache-Control: no-cache' );

		$data = get_option( 'GTranslate' );
		$default_lang = isset( $data['default_language'] ) ? $data['default_language'] : 'en';

		// Method 1: Check for Pro/Enterprise header (most reliable)
		if ( isset( $_SERVER['HTTP_X_GT_LANG'] ) ) {
			return $_SERVER['HTTP_X_GT_LANG'];
		}

		// Method 2: Check for Pro/Enterprise URL structure
		if ( ! empty( $data['pro_version'] ) || ! empty( $data['enterprise_version'] ) ) {
			$request_uri = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
			if ( preg_match( '/^\/([a-z]{2}(-[a-z]{2})?)\//i', $request_uri, $matches ) ) {
				return $matches[1];
			}
		}

		// Method 3: Check for free version cookie
		if ( isset( $_COOKIE['googtrans'] ) ) {
			$googtrans = $_COOKIE['googtrans'];
			$lang_parts = explode( '/', $googtrans );
			if ( isset( $lang_parts[2] ) ) {
				return $lang_parts[2];
			}
		}

		// Return default language if no translation is active
		return $default_lang;
	}

	public function check_gtranslate_language( $should_render, $condition ) {
		if ( empty( $condition['language']['value'] ) ) {
			return $should_render;
		}

		$required_language = $condition['language']['value'];
		$current_language = $this->get_gtranslate_current_lang();

		$has_match = strtolower( $current_language ) === strtolower( $required_language );
		$block_action = ! empty( $condition['blockAction'] ) ? $condition['blockAction'] : 'showBlock';

		if ( $has_match && $block_action === 'showBlock' ) {
			$should_render = true;
		} elseif ( ! $has_match && $block_action === 'hideBlock' ) {
			$should_render = true;
		}

		return $should_render;
	}
	private function get_language_options() {
		$languages = conditional_blocks_get_language_codes();

		return array_map( function ($code, $language) {
			return [ 'label' => strtoupper( $code ) . ' - ' . $language, 'value' => $code ];
		}, array_keys( $languages ), $languages );
	}

}

new CB_GTranslate_Integration();