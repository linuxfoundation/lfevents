<?php
/**
 * Get data from the REST API.
 *
 * @package conditional-blocks-pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * REST API.
 */
class Conditional_Blocks_REST_V1 {
	/**
	 * Endpoint version
	 *
	 * @var [type]
	 */
	private $version;
	/**
	 * Endpoint name.
	 *
	 * @var [type]
	 */
	private $endpoint;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->version = '1';
		$this->endpoint = 'conditional-blocks/v' . $this->version;

		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
	}

	/**
	 * Register REST API
	 *
	 * Create a nice array with the available data used as options for conditions inside the block editor.
	 */
	public function register_rest_routes() {

		register_rest_route(
			$this->endpoint,
			'/update',
			array(
				'methods' => 'POST',
				'callback' => array( $this, 'callback_update' ),
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' ); // User data will only be available when called from WP itself.
				},
			)
		);

		register_rest_route(
			$this->endpoint,
			'/convert-legacy-conditions',
			array(
				'methods' => 'POST',
				'callback' => array( $this, 'callback_convert_legacy_conditions' ),
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' ); // User data will only be available when called from WP itself.
				},
			)
		);

				register_rest_route(
			$this->endpoint,
			'/validate-geolocation',
			array(
				'methods' => 'POST',
				'callback' => array( $this, 'callback_validate_geolocation' ),
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' ); // User data will only be available when called from WP itself.
				},
			)
		);
			}

	/**
	 * Convert the legacy conditions to fit the new structure.
	 *
	 * @param [type] $request_data
	 * @return WP_REST_Response response.
	 */
	public function callback_convert_legacy_conditions( $request_data ) {
		$parameters = $request_data->get_params();

		if ( empty( $parameters['legacyConditions'] ) ) {
			return new WP_REST_Response( false, 400 );
		}

		$class = new Conditional_Blocks_Render_Block();
		$converted = $class->convert_v1_to_v2_conditions( $parameters['legacyConditions'] );

		return new WP_REST_Response( $converted, 200 );
	}

	/**
	 * Update the database with changes to Conditional Blocks settings.
	 *
	 * @param WP_REST_Response $request_data data from REST API.
	 * @return WP_REST_Response return the response.
	 */
	public function callback_update( $request_data ) {
		$parameters = $request_data->get_params();

		if ( ! empty( $parameters['screensizes'] ) ) {
			$this->update_screensizes( $parameters['screensizes'] );
		}

		if ( isset( $parameters['developer_mode'] ) ) {
			update_option( 'conditional_blocks_developer_mode', $parameters['developer_mode'] ? true : false, false );
		}

		if ( isset( $parameters['open_from_toolbar'] ) ) {
			update_option( 'conditional_blocks_open_from_toolbar', $parameters['open_from_toolbar'] ? true : false, false );
		}

		if ( isset( $parameters['only_installed_integrations'] ) ) {
			update_option( 'conditional_blocks_only_installed_integrations', $parameters['only_installed_integrations'] ? true : false, false );
		}

		if ( isset( $parameters['ipinfo_api_key'] ) ) {
			update_option( 'conditional_blocks_ipinfo_api_key', $parameters['ipinfo_api_key'], false );
		}

				if ( isset( $parameters['presets'] ) && is_array( $parameters['presets'] ) ) {
			// Update Presets.
			update_option( 'conditional_blocks_presets', $parameters['presets'], false );
		}
		
		return new WP_REST_Response( $parameters, 200 );
	}

	/**
	 * Update screensizes for responsive blocks.
	 *
	 * @param array $options new array of sizes.
	 * @return array $screensizes updated sizes.
	 */
	public function update_screensizes( $options ) {

		$updated_options = array(
			'device_size_desktop_min' => ! empty( $options['device_size_desktop_min'] ) ? (int) $options['device_size_desktop_min'] : 1025,
			'device_size_tablet_max' => ! empty( $options['device_size_tablet_max'] ) ? (int) $options['device_size_tablet_max'] : 1024,
			'device_size_tablet_min' => ! empty( $options['device_size_tablet_min'] ) ? (int) $options['device_size_tablet_min'] : 768,
			'device_size_mobile_max' => ! empty( $options['device_size_mobile_max'] ) ? (int) $options['device_size_mobile_max'] : 767,
		);

		update_option( 'conditional_blocks_general', $updated_options, false );

		return $updated_options;
	}

		/**
	 * Test the geolocation functionality using IPInfo API
	 *
	 * @return WP_REST_Response
	 */
	public function callback_validate_geolocation( $request ) {
		$parameters = $request->get_params();
		$api_key = $parameters['api_key'] ?? '';

		if ( empty( $api_key ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'message' => __( 'IPInfo API key is required', 'conditional-blocks' ),
				),
				400
			);
		}

		$user_ip = conditional_blocks_get_ip_address();

		$args = [ 
			'headers' => [ 
				'Authorization' => 'Bearer ' . $api_key,
				'Referer' => home_url(),
			],
			'timeout' => 15,
		];

		$response = wp_remote_get( "https://ipinfo.io/{$user_ip}", $args );

		if ( is_wp_error( $response ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'message' => sprintf(
						__( 'Failed to connect to IPInfo API: %s', 'conditional-blocks' ),
						$response->get_error_message()
					),
				),
				500
			);
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( isset( $data['error'] ) ) {

			// IPinfo may return a string or an array with the message inside.
			$error_message = $data['error'];

			// Handle specific IPInfo security error
			if ( is_string( $error_message ) && strpos( $error_message, 'Token is not allowed access from this source. Please check your token security rules' ) !== false ) {
				return new WP_REST_Response(
					array(
						'success' => false,
						'error_type' => 'token_security',
						'message' => sprintf(
							__(
								'Your IPInfo token is not configured to allow access from:\n\nDomain: %s\nServer IP: %s\n\nPlease visit your IPInfo dashboard (https://ipinfo.io/account/token) and add both domain and IP to your token\'s security rules.\n\nNOTE: localhost/local development - you will need to use a live server to use geolocation features. See our documentation for guides on testing.',
								'conditional-blocks'
							),
							$_SERVER['HTTP_HOST'] ?? 'unknown',
							$_SERVER['SERVER_ADDR'] ?? 'unknown'
						),
					),
					403
				);
			}

			return new WP_REST_Response(
				array(
					'success' => false,
					'error_type' => 'api_error',
					'message' => sprintf(
						__( 'IPInfo API Error: %s', 'conditional-blocks' ),
						is_string( $error_message ) ? $error_message : ( is_array( $error_message ) && isset( $error_message['message'] ) ? $error_message['message'] : json_encode( $error_message ) )
					),
				),
				400
			);
		}

		if ( empty( $data['country'] ) || empty( $data['ip'] ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'message' => __( 'Invalid response from IPInfo API', 'conditional-blocks' ),
				),
				400
			);
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'data' => array(
					'ip' => $data['ip'],
					'city' => $data['city'] ?? '',
					'region' => $data['region'] ?? '',
					'country' => $data['country'],
					'loc' => $data['loc'] ?? '',
					'timezone' => $data['timezone'] ?? '',
				),
			),
			200
		);
	}
	}

new Conditional_Blocks_REST_V1();
