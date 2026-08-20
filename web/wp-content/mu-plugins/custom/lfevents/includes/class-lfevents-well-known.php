<?php
/**
 * Serves /.well-known/api-catalog.
 *
 * @package    LFEvents
 * @subpackage LFEvents/includes
 */

/**
 * Generates and serves the API catalog discovery document.
 */
class LFEvents_Well_Known {

	/**
	 * The request path for the API catalog, without leading slash.
	 *
	 * @link https://www.rfc-editor.org/rfc/rfc9727
	 */
	const API_CATALOG_PATH = '.well-known/api-catalog';

	/**
	 * Serve the API catalog document when it is requested.
	 *
	 * Hooked to 'parse_request', same as LFEvents_LLMS_Txt::maybe_serve().
	 *
	 * @param WP $wp The WordPress environment instance.
	 */
	public function maybe_serve( $wp ) {
		if ( is_admin() || ! is_object( $wp ) || ! isset( $wp->request ) ) {
			return;
		}

		$request = trim( (string) $wp->request, '/' );

		if ( self::API_CATALOG_PATH === $request ) {
			$this->serve_api_catalog();
		}
	}

	/**
	 * Output the API catalog document and exit.
	 *
	 * @link https://www.rfc-editor.org/rfc/rfc9727
	 */
	private function serve_api_catalog() {
		status_header( 200 );
		header( 'Content-Type: application/linkset+json; profile="https://www.rfc-editor.org/info/rfc9727"' );

		if ( ! is_user_logged_in() ) {
			header( 'Cache-Control: public, max-age=60, s-maxage=43200, stale-while-revalidate=86400, stale-if-error=604800' );
		}

		echo wp_json_encode( $this->build_api_catalog() ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON-encoded, every value below is a WP-generated URL.
		exit;
	}

	/**
	 * Build the API catalog Linkset document.
	 *
	 * The REST API root (wp-json) already returns a machine-readable
	 * description of every registered route, including the Events endpoint,
	 * so it is used as the service-desc rather than a separately maintained
	 * OpenAPI document.
	 *
	 * @return array
	 */
	private function build_api_catalog() {
		return array(
			'linkset' => array(
				array(
					'anchor'       => esc_url_raw( rest_url( LFEvents_API::NAMESPACE_V1 . '/events' ) ),
					'service-desc' => array(
						array(
							'href' => esc_url_raw( rest_url() ),
							'type' => 'application/json',
						),
					),
					'description'  => 'Linux Foundation Events API: upcoming and past conference and event data.',
				),
			),
		);
	}
}