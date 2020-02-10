<?php
// phpcs:ignoreFile
namespace WpSvgAutocrop\Services;

defined( 'ABSPATH' ) || exit;

use Exception;

class Crop {

	protected $base_url = 'https://autocrop.cncf.io/autocrop';

	/**
	 * Execute the api call to optimize the SVGs.
	 *
	 * @param string $file_content The SVG content.
	 * @param string $file_name The file name.
	 *
	 * @return stdClass
	 */
	public function run( $file_content, $file_name ) {
		try {
			$request = $this->request(
				$this->base_url,
				array(
					'svg'   => $file_content,
					'title' => $file_name,
				)
			);
			return $this->get_body( $request );
		} catch ( Exception $e ) {
			return $e->getMessage();
		}
	}

	/**
	 * A request wrapper for all api calls.
	 *
	 * @param string $url Api endpoint.
	 * @param array  $data The arguments.
	 * @param string $method The HTTP method.
	 *
	 * @return WP_Error|stdClass
	 */
	protected function request( $url, $data = array(), $method = 'POST' ) {
		$params = array(
			'method'  => $method,
			'timeout' => 60,
			'headers' => array(
				'Content-Type'  => 'application/json',
			),
		);

		if ( 'POST' === $method && ! empty( $data ) ) {
			$params['body'] = json_encode( $data );
		}

		return wp_safe_remote_request( $url, $params );
	}

	/**
	 * Get body and normalize the response type.
	 *
	 * @param WP_Error|stdClass $response wp_safe_remote_request response.
	 *
	 * @return stdClass
	 */
	protected function get_body( $response ) {
		$body          = new \stdClass();
		$body->success = false;

		if ( is_wp_error( $response ) ) {
			$body->error = $response->get_error_message();
			return $body;
		}

		if ( ! isset( $response['body'] ) ) {
			$body->error = __( 'Empty API response.', 'wp-svg-autocrop' );
			return $body;
		}

		return json_decode( $response['body'] );
	}
}
