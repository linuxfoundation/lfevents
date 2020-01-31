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
			$file['error'] = '[WP SVG Autocrop]: ' . $response->error;
		}

		return $file;
	}
}
