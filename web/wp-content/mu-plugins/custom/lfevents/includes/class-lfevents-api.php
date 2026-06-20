<?php
/**
 * REST API for LFEvents.
 *
 * Exposes a public read-only endpoint that lists Events with a small
 * set of fields useful for external consumers (calendars, widgets, etc.).
 *
 * @package    LFEvents
 * @subpackage LFEvents/includes
 */

/**
 * Registers and serves the LFEvents REST API.
 */
class LFEvents_API {

	const NAMESPACE_V1 = 'lfevents/v1';

	/**
	 * Hook into rest_api_init.
	 */
	public function register_routes() {
		register_rest_route(
			self::NAMESPACE_V1,
			'/events',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'permission_callback' => '__return_true',
				'callback'            => array( $this, 'get_events' ),
				'args'                => array(
					's'      => array(
						'description'       => 'Free text search on event name.',
						'type'              => 'string',
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					),
					'status' => array(
						'description'       => 'Filter by event status.',
						'type'              => 'string',
						'required'          => false,
						'default'           => 'upcoming',
						'enum'              => array( 'upcoming', 'past', 'all' ),
						'sanitize_callback' => 'sanitize_key',
					),
				),
			)
		);
	}

	/**
	 * Handle GET /lfevents/v1/events.
	 *
	 * @param WP_REST_Request $request The REST request.
	 * @return WP_REST_Response
	 */
	public function get_events( WP_REST_Request $request ) {
		$search = trim( (string) $request->get_param( 's' ) );
		$status = $request->get_param( 'status' );
		if ( ! in_array( $status, array( 'upcoming', 'past', 'all' ), true ) ) {
			$status = 'upcoming';
		}

		$post_types = lfe_get_post_types();

		$meta_query = array(
			'relation' => 'AND',
			array(
				'relation' => 'OR',
				array(
					'key'     => 'lfes_hide_from_listings',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'     => 'lfes_hide_from_listings',
					'value'   => 'hide',
					'compare' => '!=',
				),
			),
			array(
				'key'     => 'lfes_date_start',
				'value'   => '',
				'compare' => '!=',
			),
		);

		if ( 'upcoming' === $status ) {
			$meta_query[] = array(
				'relation' => 'OR',
				array(
					'key'     => 'lfes_event_has_passed',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'     => 'lfes_event_has_passed',
					'value'   => '1',
					'compare' => '!=',
				),
			);
		} elseif ( 'past' === $status ) {
			$meta_query[] = array(
				'key'     => 'lfes_event_has_passed',
				'value'   => '1',
				'compare' => '=',
			);
		}

		$args = array(
			'post_type'      => $post_types,
			'post_status'    => 'publish',
			'post_parent'    => 0,
			'posts_per_page' => -1,
			'no_found_rows'  => true,
			'meta_query'     => $meta_query, //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'orderby'        => 'meta_value',
			'meta_key'       => 'lfes_date_start', //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'order'          => 'past' === $status ? 'DESC' : 'ASC',
		);

		if ( '' !== $search ) {
			$args['s']                   = $search;
			$args['lfevents_title_only'] = true;
		}

		$title_only_filter = function ( $search_sql, $wp_query ) {
			global $wpdb;
			if ( ! $wp_query->get( 'lfevents_title_only' ) ) {
				return $search_sql;
			}
			$term = $wp_query->get( 's' );
			if ( '' === $term ) {
				return $search_sql;
			}
			$like = '%' . $wpdb->esc_like( $term ) . '%';
			return $wpdb->prepare( " AND ({$wpdb->posts}.post_title LIKE %s) ", $like );
		};
		add_filter( 'posts_search', $title_only_filter, 10, 2 );

		$query = new WP_Query( $args );

		remove_filter( 'posts_search', $title_only_filter, 10 );

		$events = array();

		foreach ( $query->posts as $post ) {
			$events[] = $this->format_event( $post );
		}

		return new WP_REST_Response( $events, 200 );
	}

	/**
	 * Convert a post into the JSON response shape.
	 *
	 * @param WP_Post $post The event post.
	 * @return array
	 */
	private function format_event( WP_Post $post ) {
		$post_id      = $post->ID;
		$external_url = (string) get_post_meta( $post_id, 'lfes_external_url', true );
		$url          = $external_url ? $external_url : get_permalink( $post );

		$country_name  = '';
		$country_terms = get_the_terms( $post_id, 'lfevent-country' );
		if ( is_array( $country_terms ) && ! empty( $country_terms ) ) {
			$country_name = $country_terms[0]->name;
		}

		$virtual            = get_post_meta( $post_id, 'lfes_virtual', true );
		$white_logo_url     = $this->resolve_media_url( get_post_meta( $post_id, 'lfes_white_logo', true ) );
		$black_logo_url     = $this->resolve_media_url( get_post_meta( $post_id, 'lfes_black_logo', true ) );
		$featured_image_url = (string) get_the_post_thumbnail_url( $post_id, 'full' );

		return array(
			'id'                   => $post_id,
			'name'                 => html_entity_decode( get_the_title( $post ), ENT_QUOTES, 'UTF-8' ),
			'description'          => (string) get_post_meta( $post_id, 'lfes_description', true ),
			'date_start'           => (string) get_post_meta( $post_id, 'lfes_date_start', true ),
			'date_end'             => (string) get_post_meta( $post_id, 'lfes_date_end', true ),
			'external_url'         => $external_url,
			'white_logo'           => $white_logo_url,
			'black_logo'           => $black_logo_url,
			'menu_color'           => (string) get_post_meta( $post_id, 'lfes_menu_color', true ),
			'menu_color_2'         => (string) get_post_meta( $post_id, 'lfes_menu_color_2', true ),
			'menu_color_3'         => (string) get_post_meta( $post_id, 'lfes_menu_color_3', true ),
			'menu_text_color'      => (string) get_post_meta( $post_id, 'lfes_menu_text_color', true ),
			'featured_image'       => $featured_image_url,
			'start_date'           => (string) get_post_meta( $post_id, 'lfes_date_start', true ),
			'end_date'             => (string) get_post_meta( $post_id, 'lfes_date_end', true ),
			'location'             => array(
				'city'    => (string) get_post_meta( $post_id, 'lfes_city', true ),
				'country' => (string) $country_name,
				'region'  => (string) get_post_meta( $post_id, 'lfes_region', true ),
				'virtual' => (bool) $virtual,
			),
			'cfp_active'           => (bool) get_post_meta( $post_id, 'lfes_cfp_active', true ),
			'cfp_date_start'       => (string) get_post_meta( $post_id, 'lfes_cfp_date_start', true ),
			'cfp_date_end'         => (string) get_post_meta( $post_id, 'lfes_cfp_date_end', true ),
			'cta_speak_url'        => (string) get_post_meta( $post_id, 'lfes_cta_speak_url', true ),
			'cta_register_url'     => (string) get_post_meta( $post_id, 'lfes_cta_register_url', true ),
			'cta_sponsor_url'      => (string) get_post_meta( $post_id, 'lfes_cta_sponsor_url', true ),
			'cta_sponsor_date_end' => (string) get_post_meta( $post_id, 'lfes_cta_sponsor_date_end', true ),
			'cta_schedule_url'     => (string) get_post_meta( $post_id, 'lfes_cta_schedule_url', true ),
			'cta_videos_url'       => (string) get_post_meta( $post_id, 'lfes_cta_videos_url', true ),
			'url'                  => $url,
		);
	}

	/**
	 * Resolve a media meta value to a URL.
	 *
	 * Supports attachment IDs and direct URLs.
	 *
	 * @param mixed $value Raw media meta value.
	 * @return string
	 */
	private function resolve_media_url( $value ) {
		if ( empty( $value ) ) {
			return '';
		}

		if ( is_numeric( $value ) ) {
			$attachment_url = wp_get_attachment_url( (int) $value );
			return $attachment_url ? (string) $attachment_url : '';
		}

		if ( is_string( $value ) ) {
			return $value;
		}

		return '';
	}
}
