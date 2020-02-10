<?php
// phpcs:ignoreFile
namespace WpSvgAutocrop\Controllers;

defined( 'ABSPATH' ) || exit;

use WpSvgAutocrop\Services\Crop;

/**
 * Intercepts all SVG uploads and make a API call to optimize the file.
 */
class Upload {

	public function __construct() {
		add_filter( 'wp_handle_upload_prefilter', array( $this, 'handle_upload' ), 20 );
	}

	/**
	 * Handle the upload.
	 *
	 * @param array $file The file attributes.
	 *
	 * @return array
	 */
	public function handle_upload( $file ) {
		if ( 'image/svg+xml' === $file['type'] ) {
			return $this->process_file( $file );
		}

		return $file;
	}

	/**
	 * Call Autocrop API and process the file.
	 *
	 * @param array $file The file attributes.
	 *
	 * @return array
	 */
	public function process_file( $file ) {
		$crop     = new Crop();
		$response = $crop->run( file_get_contents( $file['tmp_name'] ), $file['name'] );

		if ( $response->success ) {
			file_put_contents( $file['tmp_name'], $response->result );
		} else {
			$file['error'] = '[SVG Autocrop]: ' . $this->prepare_error_message( $response->error );
		}

		return $file;
	}

	private function prepare_error_message( $message ) {
		if ( strpos( $message, 'SVG file contains an image' ) !== false || strpos( $message, 'SVG file embeds a png' ) !== false ) {
			return rtrim( $message, '.\t\n ' ) . '.' . PHP_EOL . ' See more details in https://github.com/cncf/wp-svg-autocrop#why-cant-my-svg-include-a-png-or-jpg';
		}

		if ( strpos( $message, 'SVG file has a <text> element' ) !== false ) {
			return rtrim( $message, '.\t\n ' ) . '.' . PHP_EOL . ' See more details in https://github.com/cncf/wp-svg-autocrop#why-cant-my-svg-include-text-or-tspan-tags';
		}

		return $message;
	}
}
