<?php
/**
 * Google Sheets CSV importer for the Sponsor Directory block.
 *
 * @package Sponsor_Directory_Block
 */

/**
 * Imports and validates sponsor directory rows.
 */
class Sponsor_Directory_Importer {

	/**
	 * REST namespace.
	 *
	 * @var string
	 */
	const REST_NAMESPACE = 'sponsor-directory/v1';

	/**
	 * Maximum CSV response size in bytes.
	 *
	 * @var int
	 */
	const MAX_RESPONSE_SIZE = 2097152;

	/**
	 * Maximum number of non-empty data rows.
	 *
	 * @var int
	 */
	const MAX_ROWS = 100;

	/**
	 * Required CSV headers mapped to internal keys.
	 *
	 * @var array
	 */
	const REQUIRED_HEADERS = array(
		'sponsor name'   => 'name',
		'post id'        => 'post_id',
		'level'          => 'level',
		'description'    => 'description',
		'categories'     => 'categories',
		'booth location' => 'booth_location',
	);

	/**
	 * Register REST routes.
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			self::REST_NAMESPACE,
			'/import',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'permission_callback' => array( $this, 'can_import' ),
				'callback'            => array( $this, 'import' ),
				'args'                => array(
					'url' => array(
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => 'sanitize_url',
					),
				),
			)
		);
	}

	/**
	 * Check whether the current user can import sponsor data.
	 *
	 * @return bool
	 */
	public function can_import() {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Fetch, parse, and validate a published Google Sheets CSV.
	 *
	 * @param WP_REST_Request $request REST request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function import( WP_REST_Request $request ) {
		$url = (string) $request->get_param( 'url' );

		if ( ! $this->is_allowed_url( $url ) ) {
			return new WP_Error(
				'sponsor_directory_invalid_url',
				__( 'Enter an HTTPS published Google Sheets CSV URL from docs.google.com.', 'sponsor-directory-block' ),
				array( 'status' => 400 )
			);
		}

		$response = wp_safe_remote_get(
			$url,
			array(
				'timeout'             => 15,
				'redirection'         => 3,
				'limit_response_size' => self::MAX_RESPONSE_SIZE,
			)
		);

		if ( is_wp_error( $response ) ) {
			return new WP_Error(
				'sponsor_directory_fetch_failed',
				sprintf(
					/* translators: %s: HTTP request error. */
					__( 'The Google Sheet could not be downloaded: %s', 'sponsor-directory-block' ),
					$response->get_error_message()
				),
				array( 'status' => 502 )
			);
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $status_code ) {
			return new WP_Error(
				'sponsor_directory_http_error',
				sprintf(
					/* translators: %d: HTTP status code. */
					__( 'Google returned HTTP status %d for the Sheet.', 'sponsor-directory-block' ),
					$status_code
				),
				array( 'status' => 502 )
			);
		}

		$body = wp_remote_retrieve_body( $response );
		if ( '' === trim( $body ) ) {
			return new WP_Error(
				'sponsor_directory_empty_csv',
				__( 'The published Sheet returned an empty CSV file.', 'sponsor-directory-block' ),
				array( 'status' => 400 )
			);
		}

		$parsed = $this->parse_csv( $body );
		if ( is_wp_error( $parsed ) ) {
			return $parsed;
		}

		$result = $this->validate_rows( $parsed['rows'], $parsed['columns'] );

		return new WP_REST_Response(
			array(
				'sponsors'   => $result['sponsors'],
				'importedAt' => gmdate( 'c' ),
				'report'     => $result['report'],
			),
			200
		);
	}

	/**
	 * Restrict imports to published Google Sheets URLs.
	 *
	 * @param string $url URL to validate.
	 * @return bool
	 */
	private function is_allowed_url( $url ) {
		$parts = wp_parse_url( $url );

		return is_array( $parts )
			&& isset( $parts['scheme'], $parts['host'], $parts['path'] )
			&& 'https' === strtolower( $parts['scheme'] )
			&& 'docs.google.com' === strtolower( $parts['host'] )
			&& 0 === strpos( $parts['path'], '/spreadsheets/' );
	}

	/**
	 * Parse CSV content and resolve required column indexes.
	 *
	 * @param string $csv CSV response body.
	 * @return array|WP_Error
	 */
	private function parse_csv( $csv ) {
		try {
			$file = new SplTempFileObject( self::MAX_RESPONSE_SIZE );
		} catch ( RuntimeException $exception ) {
			return new WP_Error(
				'sponsor_directory_csv_open_failed',
				__( 'The CSV could not be opened for parsing.', 'sponsor-directory-block' ),
				array( 'status' => 500 )
			);
		}

		$file->fwrite( $csv );
		$file->rewind();

		$headers = $file->fgetcsv( ',', '"', '\\' );
		if ( false === $headers ) {
			return new WP_Error(
				'sponsor_directory_missing_headers',
				__( 'The CSV does not contain a header row.', 'sponsor-directory-block' ),
				array( 'status' => 400 )
			);
		}

		$columns = array();
		foreach ( $headers as $index => $header ) {
			$normalized = $this->normalize_header( $header );
			if ( isset( self::REQUIRED_HEADERS[ $normalized ] ) ) {
				$columns[ self::REQUIRED_HEADERS[ $normalized ] ] = $index;
			}
		}

		$missing = array_diff( array_values( self::REQUIRED_HEADERS ), array_keys( $columns ) );
		if ( ! empty( $missing ) ) {
			$labels         = array_flip( self::REQUIRED_HEADERS );
			$missing_labels = array_map(
				function ( $key ) use ( $labels ) {
					return ucwords( $labels[ $key ] );
				},
				$missing
			);
			return new WP_Error(
				'sponsor_directory_invalid_headers',
				sprintf(
					/* translators: %s: comma-separated missing CSV headers. */
					__( 'The CSV is missing required columns: %s.', 'sponsor-directory-block' ),
					implode( ', ', $missing_labels )
				),
				array( 'status' => 400 )
			);
		}

		$rows       = array();
		$row_number = 1;
		while ( ! $file->eof() ) {
			$row = $file->fgetcsv( ',', '"', '\\' );
			++$row_number;
			if ( false === $row || $this->is_empty_row( $row ) ) {
				continue;
			}
			$rows[] = array(
				'number' => $row_number,
				'values' => $row,
			);
		}
		if ( count( $rows ) > self::MAX_ROWS ) {
			return new WP_Error(
				'sponsor_directory_too_many_rows',
				sprintf(
					/* translators: %d: maximum sponsor row count. */
					__( 'The CSV contains more than the maximum of %d sponsor rows.', 'sponsor-directory-block' ),
					self::MAX_ROWS
				),
				array( 'status' => 400 )
			);
		}

		return array(
			'columns' => $columns,
			'rows'    => $rows,
		);
	}

	/**
	 * Validate parsed rows and match Sponsor CPT posts.
	 *
	 * @param array $rows Parsed CSV rows.
	 * @param array $columns Required column indexes.
	 * @return array
	 */
	private function validate_rows( $rows, $columns ) {
		$skipped    = array();
		$candidates = array();
		$seen_ids   = array();

		foreach ( $rows as $row ) {
			$values      = $row['values'];
			$row_number  = $row['number'];
			$name        = sanitize_text_field( $this->column_value( $values, $columns['name'] ) );
			$post_id_raw = trim( $this->column_value( $values, $columns['post_id'] ) );

			if ( '' === $name ) {
				$skipped[] = $this->report_item( $row_number, __( 'Sponsor Name is blank.', 'sponsor-directory-block' ) );
				continue;
			}

			if ( ! ctype_digit( $post_id_raw ) || (int) $post_id_raw < 1 ) {
				$skipped[] = $this->report_item( $row_number, __( 'Post ID must be a positive whole number.', 'sponsor-directory-block' ) );
				continue;
			}

			$post_id = (int) $post_id_raw;
			if ( isset( $seen_ids[ $post_id ] ) ) {
				$skipped[] = $this->report_item( $row_number, __( 'Post ID duplicates an earlier row.', 'sponsor-directory-block' ) );
				continue;
			}
			$seen_ids[ $post_id ] = true;

			$candidates[] = array(
				'row'           => $row_number,
				'name'          => $name,
				'postId'        => $post_id,
				'level'         => sanitize_text_field( $this->column_value( $values, $columns['level'] ) ),
				'description'   => sanitize_textarea_field( $this->column_value( $values, $columns['description'] ) ),
				'categories'    => $this->parse_categories( $this->column_value( $values, $columns['categories'] ) ),
				'boothLocation' => sanitize_text_field( $this->column_value( $values, $columns['booth_location'] ) ),
			);
		}

		$post_ids    = wp_list_pluck( $candidates, 'postId' );
		$matched_ids = empty( $post_ids ) ? array() : get_posts(
			array(
				'post_type'              => 'lfe_sponsor',
				'post_status'            => 'publish',
				'post__in'               => $post_ids,
				'posts_per_page'         => self::MAX_ROWS,
				'fields'                 => 'ids',
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);
		$matched_ids = array_fill_keys( array_map( 'intval', $matched_ids ), true );

		$sponsors = array();
		$warnings = array();
		foreach ( $candidates as $candidate ) {
			$post_id = $candidate['postId'];
			if ( ! isset( $matched_ids[ $post_id ] ) ) {
				$skipped[] = $this->report_item( $candidate['row'], __( 'Post ID does not match a published Sponsor post.', 'sponsor-directory-block' ) );
				continue;
			}

			if ( ! has_post_thumbnail( $post_id ) ) {
				$warnings[] = $this->report_item( $candidate['row'], __( 'The Sponsor post has no featured image.', 'sponsor-directory-block' ) );
			}
			if ( ! get_post_meta( $post_id, 'lfes_sponsor_url', true ) ) {
				$warnings[] = $this->report_item( $candidate['row'], __( 'The Sponsor post has no forwarding URL.', 'sponsor-directory-block' ) );
			}

			unset( $candidate['row'] );
			$sponsors[] = $candidate;
		}

		usort(
			$sponsors,
			function ( $first, $second ) {
				$comparison = strcasecmp( $first['name'], $second['name'] );
				return 0 !== $comparison ? $comparison : $first['postId'] <=> $second['postId'];
			}
		);

		return array(
			'sponsors' => $sponsors,
			'report'   => array(
				'matchedCount' => count( $sponsors ),
				'skippedCount' => count( $skipped ),
				'skipped'      => $skipped,
				'warnings'     => $warnings,
			),
		);
	}

	/**
	 * Normalize a CSV header for matching.
	 *
	 * @param string $header Header value.
	 * @return string
	 */
	private function normalize_header( $header ) {
		$header = preg_replace( '/^\xEF\xBB\xBF/', '', (string) $header );
		$header = preg_replace( '/\s+/', ' ', trim( $header ) );
		return strtolower( $header );
	}

	/**
	 * Determine whether every value in a CSV row is blank.
	 *
	 * @param array $row CSV row.
	 * @return bool
	 */
	private function is_empty_row( $row ) {
		foreach ( $row as $value ) {
			if ( '' !== trim( (string) $value ) ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Safely read a CSV column.
	 *
	 * @param array $values CSV values.
	 * @param int   $index Column index.
	 * @return string
	 */
	private function column_value( $values, $index ) {
		return isset( $values[ $index ] ) ? trim( (string) $values[ $index ] ) : '';
	}

	/**
	 * Split, sanitize, and de-duplicate comma-delimited categories.
	 *
	 * @param string $value Category field.
	 * @return array
	 */
	private function parse_categories( $value ) {
		$categories = array();
		$seen       = array();
		foreach ( explode( ',', $value ) as $category ) {
			$category = sanitize_text_field( trim( $category ) );
			$key      = strtolower( $category );
			if ( '' === $category || isset( $seen[ $key ] ) ) {
				continue;
			}
			$seen[ $key ] = true;
			$categories[] = $category;
		}
		return $categories;
	}

	/**
	 * Create a row-level import report item.
	 *
	 * @param int    $row CSV row number.
	 * @param string $reason Human-readable reason.
	 * @return array
	 */
	private function report_item( $row, $reason ) {
		return array(
			'row'    => (int) $row,
			'reason' => $reason,
		);
	}
}
