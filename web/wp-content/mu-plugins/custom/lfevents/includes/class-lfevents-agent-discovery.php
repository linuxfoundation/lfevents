<?php
/**
 * Discovers third-party conferences for each Theme Calendar category using an
 * LLM with web search, and files them as External Event drafts for review.
 *
 * @package    LFEvents
 * @subpackage LFEvents/includes
 */

/**
 * Agent-driven External Event discovery.
 */
class LFEvents_Agent_Discovery {

	const CRON_HOOK = 'lfevents_agent_discovery';

	const OPTION_ENABLED    = 'lfe-agent-discovery-enabled';
	const OPTION_HORIZON    = 'lfe-agent-horizon-months';
	const OPTION_MAX_EVENTS = 'lfe-agent-max-events';
	const OPTION_CURSOR     = 'lfevents_agent_discovery_cursor';
	const OPTION_LOG        = 'lfevents_agent_discovery_log';

	const DEFAULT_HORIZON    = 18;
	const DEFAULT_MAX_EVENTS = 15;
	const LOG_LIMIT          = 30;
	const KNOWN_LIMIT        = 100;
	const FUZZY_THRESHOLD    = 0.85;

	const META_FINGERPRINT = 'lfes_external_agent_fingerprint';
	const META_SOURCE      = 'lfes_external_agent_source';
	const META_CONFIDENCE  = 'lfes_external_agent_confidence';
	const META_EVIDENCE    = 'lfes_external_agent_evidence';
	const META_LAST_SEEN   = 'lfes_external_agent_last_seen';

	/**
	 * Post statuses considered "already known" for dedupe (WP_Query needs trash listed explicitly).
	 *
	 * @var string[]
	 */
	const KNOWN_STATUSES = array( 'publish', 'draft', 'pending', 'future', 'private', 'trash' );

	/**
	 * LiteLLM client.
	 *
	 * @var LFEvents_LiteLLM_Client
	 */
	private $client;

	/**
	 * Constructor.
	 *
	 * @param LFEvents_LiteLLM_Client|null $client Optional client, for testing.
	 */
	public function __construct( $client = null ) {
		$this->client = $client ? $client : new LFEvents_LiteLLM_Client();
	}

	// ----- Scheduling -----

	/**
	 * Whether the weekly cron should run in this environment. Live only by default
	 * so multidevs don't duplicate spend; override with the filter for local testing.
	 *
	 * @return bool
	 */
	public static function cron_allowed() {
		$is_live = isset( $_ENV['PANTHEON_ENVIRONMENT'] ) && 'live' === $_ENV['PANTHEON_ENVIRONMENT'];
		return (bool) apply_filters( 'lfevents_agent_discovery_cron_allowed', $is_live );
	}

	/**
	 * Keeps the weekly schedule in sync with the enabled option. Hooked to init.
	 */
	public static function sync_schedule() {
		$enabled   = (bool) get_option( self::OPTION_ENABLED ) && self::cron_allowed();
		$scheduled = wp_next_scheduled( self::CRON_HOOK );

		if ( $enabled && ! $scheduled ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'weekly', self::CRON_HOOK );
		} elseif ( ! $enabled && $scheduled ) {
			wp_unschedule_event( $scheduled, self::CRON_HOOK );
		}
	}

	/**
	 * Cron callback: processes the next category in rotation.
	 */
	public static function cron_run() {
		( new self() )->run_next();
	}

	// ----- Scope -----

	/**
	 * Categories attached to at least one published Theme Calendar.
	 *
	 * @return WP_Term[] Sorted by name.
	 */
	public static function get_categories_in_scope() {
		$calendar_ids = get_posts(
			array(
				'post_type'      => 'lfe_theme_calendar',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
			)
		);
		if ( ! $calendar_ids ) {
			return array();
		}

		$terms = wp_get_object_terms( $calendar_ids, 'lfevent-category', array( 'orderby' => 'name' ) );
		return is_wp_error( $terms ) ? array() : $terms;
	}

	/**
	 * Runs discovery for the category after the last one processed.
	 *
	 * @return array|WP_Error Run summary.
	 */
	public function run_next() {
		$terms = self::get_categories_in_scope();
		if ( ! $terms ) {
			return new WP_Error( 'lfe_agent_no_categories', 'No Event Categories are attached to a published Theme Calendar.' );
		}

		$ids    = wp_list_pluck( $terms, 'term_id' );
		$cursor = (int) get_option( self::OPTION_CURSOR, 0 );
		$pos    = array_search( $cursor, $ids, true );
		$next   = $ids[ ( false === $pos ) ? 0 : ( $pos + 1 ) % count( $ids ) ];

		update_option( self::OPTION_CURSOR, $next, false );
		return $this->run( $next );
	}

	// ----- Main run -----

	/**
	 * Discovers events for one category and files them as drafts.
	 *
	 * @param int  $term_id  lfevent-category term id.
	 * @param bool $dry_run  When true nothing is written; candidates are returned for inspection.
	 * @return array|WP_Error {
	 *   term: string, model: string, found: int, created: int, updated: int, skipped: int,
	 *   rejected: int, web_search_requests: int, errors: string[], candidates: array
	 * }
	 */
	public function run( $term_id, $dry_run = false ) {
		$term = get_term( (int) $term_id, 'lfevent-category' );
		if ( ! $term || is_wp_error( $term ) ) {
			return new WP_Error( 'lfe_agent_bad_term', 'Unknown Event Category.' );
		}

		$started = time();
		$known   = $this->get_known_events( $term->term_id );
		$result  = $this->client->chat( $this->build_messages( $term, $known ) );

		if ( is_wp_error( $result ) ) {
			$this->log( $term, array( 'errors' => array( $result->get_error_message() ) ), $started, $dry_run );
			return $result;
		}

		$payload = LFEvents_LiteLLM_Client::extract_json( $result['content'] );
		if ( null === $payload || ! isset( $payload['events'] ) || ! is_array( $payload['events'] ) ) {
			$error = new WP_Error( 'lfe_agent_bad_payload', 'The model did not return a JSON object with an "events" array.', array( 'content' => $result['content'] ) );
			$this->log(
				$term,
				array(
					'errors'              => array( $error->get_error_message() ),
					'web_search_requests' => $result['web_search_requests'],
				),
				$started,
				$dry_run
			);
			return $error;
		}

		$summary = array(
			'term'                => $term->name,
			'model'               => $result['model'],
			'found'               => count( $payload['events'] ),
			'created'             => 0,
			'updated'             => 0,
			'skipped'             => 0,
			'rejected'            => 0,
			'web_search_requests' => $result['web_search_requests'],
			'errors'              => array(),
			'candidates'          => array(),
		);

		foreach ( $payload['events'] as $raw ) {
			$candidate = $this->validate_candidate( is_array( $raw ) ? $raw : array() );
			if ( is_wp_error( $candidate ) ) {
				++$summary['rejected'];
				$summary['candidates'][] = array(
					'title'  => is_array( $raw ) ? (string) ( $raw['title'] ?? '' ) : '',
					'action' => 'rejected',
					'reason' => $candidate->get_error_message(),
				);
				continue;
			}

			$existing = $this->find_existing( $candidate, $known );
			$action   = 'create';
			if ( $existing ) {
				$action = 'draft' === $existing['status'] && ! $existing['is_lf'] ? 'update' : 'skip';
			}

			if ( ! $dry_run ) {
				if ( 'create' === $action ) {
					$post_id = $this->create_draft( $candidate, $term );
					if ( is_wp_error( $post_id ) ) {
						$summary['errors'][] = $candidate['title'] . ': ' . $post_id->get_error_message();
						$action              = 'error';
					} else {
						$known[] = $this->known_entry( $post_id, $candidate['title'], $candidate['url'], 'draft', false );
					}
				} elseif ( 'update' === $action ) {
					$this->update_draft( $existing['post_id'], $candidate );
				}
			}

			$counter = array(
				'create' => 'created',
				'update' => 'updated',
				'skip'   => 'skipped',
			);
			if ( isset( $counter[ $action ] ) ) {
				++$summary[ $counter[ $action ] ];
			}
			$summary['candidates'][] = array(
				'title'      => $candidate['title'],
				'url'        => $candidate['url'],
				'dates'      => $candidate['date_start'] . ( $candidate['date_end'] !== $candidate['date_start'] ? ' – ' . $candidate['date_end'] : '' ),
				'location'   => trim( $candidate['city'] . ( $candidate['city'] && $candidate['country'] ? ', ' : '' ) . $candidate['country'] . ( $candidate['virtual'] ? ' (virtual)' : '' ) ),
				'confidence' => $candidate['confidence'],
				'action'     => $action,
				'reason'     => $existing ? 'matches ' . ( $existing['is_lf'] ? 'LF event' : $existing['status'] . ' external event' ) . ' #' . $existing['post_id'] : '',
			);
		}

		$this->log( $term, $summary, $started, $dry_run );
		return $summary;
	}

	// ----- Prompt -----

	/**
	 * Builds the chat messages for a category.
	 *
	 * @param WP_Term $term  Category.
	 * @param array   $known Known events from get_known_events().
	 * @return array
	 */
	private function build_messages( WP_Term $term, array $known ) {
		$horizon = max( 1, (int) get_option( self::OPTION_HORIZON, self::DEFAULT_HORIZON ) );
		$max     = max( 1, (int) get_option( self::OPTION_MAX_EVENTS, self::DEFAULT_MAX_EVENTS ) );
		$today   = gmdate( 'Y-m-d' );
		$until   = gmdate( 'Y-m-d', strtotime( "+{$horizon} months" ) );

		$exclusions = array_map(
			fn( $k ) => '- ' . $k['title'] . ( $k['url'] ? ' (' . $k['url'] . ')' : '' ),
			array_slice( $known, 0, self::KNOWN_LIMIT )
		);

		$system = 'You are a research assistant that finds real, upcoming technology conferences and summits for a public events calendar run by The Linux Foundation. '
			. 'You must use web search to verify every event and must only report events for which you found the official event website. '
			. 'Never invent events, dates, or URLs. If you are unsure about a detail, leave that field empty rather than guessing. '
			. 'Respond with a single JSON object and nothing else: no prose, no markdown fences.';

		$user = "Find up to {$max} upcoming conferences or summits anywhere in the world on the theme \"{$term->name}\""
			. ( $term->description ? " ({$term->description})" : '' )
			. " that start between {$today} and {$until}.\n\n"
			. "Requirements:\n"
			. "- Multi-track conferences, summits and major industry events only (no meetups, webinars, workshops, courses or trade-show booths).\n"
			. "- Prefer well-established events with a published date and official website.\n"
			. "- Include virtual-only events when notable.\n"
			. "- Do NOT include events organised by The Linux Foundation or its foundations/projects (CNCF, OpenJS, LF AI & Data, etc.).\n"
			. "- Do NOT include any of these already-known events:\n"
			. ( $exclusions ? implode( "\n", $exclusions ) : '- (none)' ) . "\n\n"
			. "Return exactly this JSON shape:\n"
			. '{"events":[{"title":"","url":"official event website URL","date_start":"YYYY-MM-DD","date_end":"YYYY-MM-DD","city":"","country":"country name in English","virtual":false,"organizer":"","description":"1-2 plain-text sentences, max 400 characters","source_urls":["pages where you verified the details"],"confidence":"high|medium|low","notes":"anything an editor should double-check"}]}' . "\n\n"
			. 'Use an empty string for unknown text fields, and for a one-day event set date_end equal to date_start.';

		return array(
			array(
				'role'    => 'system',
				'content' => $system,
			),
			array(
				'role'    => 'user',
				'content' => $user,
			),
		);
	}

	// ----- Known events & dedupe -----

	/**
	 * LF Events (upcoming) and External Events (any status incl. trash) in the category.
	 *
	 * @param int $term_id Category term id.
	 * @return array[] Entries from known_entry().
	 */
	private function get_known_events( $term_id ) {
		$known     = array();
		$tax_query = array(
			array(
				'taxonomy' => 'lfevent-category',
				'field'    => 'term_id',
				'terms'    => array( (int) $term_id ),
			),
		);

		$args              = LFEvents_API::build_event_query_args( 'upcoming' );
		$args['tax_query'] = $tax_query; //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		foreach ( ( new WP_Query( $args ) )->posts as $post ) {
			$url     = (string) get_post_meta( $post->ID, 'lfes_external_url', true );
			$known[] = $this->known_entry( $post->ID, $post->post_title, $url ? $url : get_permalink( $post ), $post->post_status, true );
		}

		$externals = new WP_Query(
			array(
				'post_type'      => 'lfe_external_event',
				'post_status'    => self::KNOWN_STATUSES,
				'posts_per_page' => -1,
				'no_found_rows'  => true,
				'tax_query'      => $tax_query, //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			)
		);
		foreach ( $externals->posts as $post ) {
			$known[] = $this->known_entry( $post->ID, $post->post_title, (string) get_post_meta( $post->ID, 'lfes_external_event_url', true ), $post->post_status, false );
		}

		return $known;
	}

	/**
	 * Builds one known-event record.
	 *
	 * @param int    $post_id Post id.
	 * @param string $title   Title.
	 * @param string $url     Event URL.
	 * @param string $status  Post status.
	 * @param bool   $is_lf   Whether this is an LF Event (vs External Event).
	 * @return array
	 */
	private function known_entry( $post_id, $title, $url, $status, $is_lf ) {
		$year = (int) substr( (string) get_post_meta( $post_id, $is_lf ? 'lfes_date_start' : 'lfes_external_date_start', true ), 0, 4 );
		return array(
			'post_id'     => (int) $post_id,
			'title'       => (string) $title,
			'url'         => (string) $url,
			'fingerprint' => $url ? self::fingerprint( $url ) : '',
			'status'      => (string) $status,
			'is_lf'       => (bool) $is_lf,
			'year'        => $year,
			'country'     => self::country_name( $post_id ),
		);
	}

	/**
	 * Finds an already-known event matching the candidate, or null.
	 *
	 * Order: stored fingerprint meta (any category), URL fingerprint against known
	 * events, then fuzzy title within the same start year and country.
	 *
	 * @param array $candidate Validated candidate.
	 * @param array $known     Known events.
	 * @return array|null Known entry.
	 */
	private function find_existing( array $candidate, array $known ) {
		$fingerprint = self::fingerprint( $candidate['url'] );

		$by_meta = get_posts(
			array(
				'post_type'      => 'lfe_external_event',
				'post_status'    => self::KNOWN_STATUSES,
				'posts_per_page' => 1,
				'no_found_rows'  => true,
				'meta_key'       => self::META_FINGERPRINT, //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'     => $fingerprint, //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			)
		);
		if ( $by_meta ) {
			$post = $by_meta[0];
			return $this->known_entry( $post->ID, $post->post_title, $candidate['url'], $post->post_status, false );
		}

		foreach ( $known as $entry ) {
			if ( $entry['fingerprint'] && $entry['fingerprint'] === $fingerprint ) {
				return $entry;
			}
		}

		$year         = (int) substr( $candidate['date_start'], 0, 4 );
		$needle       = self::normalize_title( $candidate['title'] );
		$needle_no_yr = trim( preg_replace( '/\b20\d{2}\b/', '', $needle ) );
		foreach ( $known as $entry ) {
			if ( $entry['year'] && $entry['year'] !== $year ) {
				continue;
			}
			if ( $entry['country'] && $candidate['country'] && strcasecmp( $entry['country'], $candidate['country'] ) !== 0 ) {
				continue;
			}
			$hay = trim( preg_replace( '/\b20\d{2}\b/', '', self::normalize_title( $entry['title'] ) ) );
			if ( '' === $hay || '' === $needle_no_yr ) {
				continue;
			}
			similar_text( $needle_no_yr, $hay, $percent );
			if ( $percent / 100 >= self::FUZZY_THRESHOLD ) {
				return $entry;
			}
		}

		return null;
	}

	// ----- Validation -----

	/**
	 * Validates and normalises one model-supplied event.
	 *
	 * @param array $raw Raw candidate.
	 * @return array|WP_Error Normalised candidate with dates as YYYY/MM/DD.
	 */
	private function validate_candidate( array $raw ) {
		$title = sanitize_text_field( (string) ( $raw['title'] ?? '' ) );
		if ( '' === $title ) {
			return new WP_Error( 'lfe_agent_invalid', 'missing title' );
		}

		$url  = esc_url_raw( trim( (string) ( $raw['url'] ?? '' ) ), array( 'http', 'https' ) );
		$host = (string) wp_parse_url( $url, PHP_URL_HOST );
		if ( '' === $url || ! str_contains( $host, '.' ) ) {
			return new WP_Error( 'lfe_agent_invalid', 'missing or invalid URL' );
		}
		// Fabricated domains don't resolve; cheap guard against hallucinated URLs.
		if ( apply_filters( 'lfevents_agent_discovery_verify_dns', true ) && gethostbyname( $host ) === $host ) {
			return new WP_Error( 'lfe_agent_invalid', 'URL host does not resolve: ' . $host );
		}

		$start = self::parse_date( $raw['date_start'] ?? '' );
		if ( ! $start ) {
			return new WP_Error( 'lfe_agent_invalid', 'missing or invalid start date' );
		}
		$end = self::parse_date( $raw['date_end'] ?? '' );
		if ( ! $end || $end < $start ) {
			$end = $start;
		}

		$today   = new DateTime( 'today', new DateTimeZone( 'UTC' ) );
		$horizon = ( clone $today )->modify( '+' . max( 1, (int) get_option( self::OPTION_HORIZON, self::DEFAULT_HORIZON ) ) . ' months' );
		if ( $end < $today ) {
			return new WP_Error( 'lfe_agent_invalid', 'event has already ended' );
		}
		if ( $start > $horizon ) {
			return new WP_Error( 'lfe_agent_invalid', 'start date is beyond the discovery horizon' );
		}

		$sources = array_values(
			array_filter(
				array_map(
					fn( $u ) => esc_url_raw( trim( (string) $u ), array( 'http', 'https' ) ),
					is_array( $raw['source_urls'] ?? null ) ? $raw['source_urls'] : array()
				)
			)
		);

		$confidence = strtolower( sanitize_text_field( (string) ( $raw['confidence'] ?? '' ) ) );
		if ( ! in_array( $confidence, array( 'high', 'medium', 'low' ), true ) ) {
			$confidence = 'low';
		}

		$description = trim( wp_strip_all_tags( (string) ( $raw['description'] ?? '' ) ) );
		if ( mb_strlen( $description ) > 400 ) {
			$description = rtrim( mb_substr( $description, 0, 397 ) ) . '…';
		}

		return array(
			'title'       => $title,
			'url'         => $url,
			'date_start'  => $start->format( 'Y/m/d' ),
			'date_end'    => $end->format( 'Y/m/d' ),
			'city'        => sanitize_text_field( (string) ( $raw['city'] ?? '' ) ),
			'country'     => sanitize_text_field( (string) ( $raw['country'] ?? '' ) ),
			'virtual'     => filter_var( $raw['virtual'] ?? false, FILTER_VALIDATE_BOOLEAN ),
			'organizer'   => sanitize_text_field( (string) ( $raw['organizer'] ?? '' ) ),
			'description' => $description,
			'source_urls' => $sources,
			'confidence'  => $confidence,
			'notes'       => sanitize_textarea_field( (string) ( $raw['notes'] ?? '' ) ),
		);
	}

	// ----- Persistence -----

	/**
	 * Inserts a draft External Event.
	 *
	 * @param array   $candidate Validated candidate.
	 * @param WP_Term $term      Category to assign.
	 * @return int|WP_Error Post id.
	 */
	private function create_draft( array $candidate, WP_Term $term ) {
		$country_term = self::match_country_term( $candidate['country'] );

		$post_id = wp_insert_post(
			array(
				'post_type'   => 'lfe_external_event',
				'post_status' => 'draft',
				'post_title'  => $candidate['title'],
				'meta_input'  => array(
					'lfes_external_date_start'  => $candidate['date_start'],
					'lfes_external_date_end'    => $candidate['date_end'],
					'lfes_external_event_url'   => $candidate['url'],
					'lfes_external_organizer'   => $candidate['organizer'],
					'lfes_external_description' => $candidate['description'],
					'lfes_external_city'        => $candidate['city'],
					'lfes_external_virtual'     => $candidate['virtual'] ? '1' : '',
					self::META_FINGERPRINT      => self::fingerprint( $candidate['url'] ),
					self::META_SOURCE           => '1',
					self::META_CONFIDENCE       => $candidate['confidence'],
					self::META_EVIDENCE         => $this->format_evidence( $candidate, $country_term ),
					self::META_LAST_SEEN        => gmdate( 'Y/m/d' ),
				),
			),
			true
		);
		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		wp_set_object_terms( $post_id, array( $term->term_id ), 'lfevent-category' );
		if ( $country_term ) {
			wp_set_object_terms( $post_id, array( $country_term->term_id ), 'lfevent-country' );
		}

		return $post_id;
	}

	/**
	 * Refreshes a still-draft External Event with newer details. Title and URL are
	 * left alone so editor corrections survive.
	 *
	 * @param int   $post_id   Existing draft.
	 * @param array $candidate Validated candidate.
	 */
	private function update_draft( $post_id, array $candidate ) {
		$fields = array(
			'lfes_external_date_start'  => $candidate['date_start'],
			'lfes_external_date_end'    => $candidate['date_end'],
			'lfes_external_organizer'   => $candidate['organizer'],
			'lfes_external_description' => $candidate['description'],
			'lfes_external_city'        => $candidate['city'],
		);
		foreach ( $fields as $key => $value ) {
			if ( '' !== $value ) {
				update_post_meta( $post_id, $key, $value );
			}
		}
		update_post_meta( $post_id, 'lfes_external_virtual', $candidate['virtual'] ? '1' : '' );
		update_post_meta( $post_id, self::META_CONFIDENCE, $candidate['confidence'] );
		update_post_meta( $post_id, self::META_LAST_SEEN, gmdate( 'Y/m/d' ) );
		if ( ! get_post_meta( $post_id, self::META_FINGERPRINT, true ) ) {
			update_post_meta( $post_id, self::META_FINGERPRINT, self::fingerprint( $candidate['url'] ) );
		}

		$country_term = self::match_country_term( $candidate['country'] );
		if ( $country_term && ! has_term( '', 'lfevent-country', $post_id ) ) {
			wp_set_object_terms( $post_id, array( $country_term->term_id ), 'lfevent-country' );
		}

		$previous = (string) get_post_meta( $post_id, self::META_EVIDENCE, true );
		update_post_meta( $post_id, self::META_EVIDENCE, trim( $this->format_evidence( $candidate, $country_term ) . "\n\n--- earlier ---\n" . $previous ) );
	}

	/**
	 * Human-readable evidence block stored on the draft.
	 *
	 * @param array        $candidate    Validated candidate.
	 * @param WP_Term|null $country_term Matched country, if any.
	 * @return string
	 */
	private function format_evidence( array $candidate, $country_term ) {
		$lines   = array();
		$lines[] = 'Seen: ' . gmdate( 'Y-m-d' ) . ' · Confidence: ' . $candidate['confidence'];
		if ( $candidate['source_urls'] ) {
			$lines[] = 'Sources:';
			foreach ( $candidate['source_urls'] as $u ) {
				$lines[] = '- ' . $u;
			}
		}
		if ( $candidate['notes'] ) {
			$lines[] = 'Notes: ' . $candidate['notes'];
		}
		if ( $candidate['country'] && ! $country_term ) {
			$lines[] = 'Country "' . $candidate['country'] . '" did not match an Event Country term - set it manually.';
		}
		return implode( "\n", $lines );
	}

	// ----- Logging -----

	/**
	 * Appends a run summary to the rolling log option.
	 *
	 * @param WP_Term $term    Category.
	 * @param array   $summary Partial or full run summary.
	 * @param int     $started Unix timestamp the run began.
	 * @param bool    $dry_run Whether nothing was written.
	 */
	private function log( WP_Term $term, array $summary, $started, $dry_run ) {
		$log   = self::get_log();
		$log[] = array(
			'time'                => $started,
			'duration'            => time() - $started,
			'term'                => $term->name,
			'term_id'             => $term->term_id,
			'model'               => (string) ( $summary['model'] ?? LFEvents_LiteLLM_Client::get_model() ),
			'dry_run'             => (bool) $dry_run,
			'found'               => (int) ( $summary['found'] ?? 0 ),
			'created'             => (int) ( $summary['created'] ?? 0 ),
			'updated'             => (int) ( $summary['updated'] ?? 0 ),
			'skipped'             => (int) ( $summary['skipped'] ?? 0 ),
			'rejected'            => (int) ( $summary['rejected'] ?? 0 ),
			'web_search_requests' => (int) ( $summary['web_search_requests'] ?? 0 ),
			'errors'              => array_values( (array) ( $summary['errors'] ?? array() ) ),
		);
		update_option( self::OPTION_LOG, array_slice( $log, -self::LOG_LIMIT ), false );
	}

	/**
	 * Run log, oldest first.
	 *
	 * @return array
	 */
	public static function get_log() {
		$log = get_option( self::OPTION_LOG, array() );
		return is_array( $log ) ? $log : array();
	}

	// ----- Helpers -----

	/**
	 * Stable key for a URL: scheme, www., trailing slash, fragment and tracking params ignored.
	 *
	 * @param string $url URL.
	 * @return string
	 */
	public static function normalize_url( $url ) {
		$parts = wp_parse_url( trim( (string) $url ) );
		if ( empty( $parts['host'] ) ) {
			return strtolower( trim( (string) $url ) );
		}
		$host = preg_replace( '/^www\./i', '', strtolower( $parts['host'] ) );
		$path = rtrim( $parts['path'] ?? '', '/' );

		$query = '';
		if ( ! empty( $parts['query'] ) ) {
			parse_str( $parts['query'], $params );
			$params = array_filter( $params, fn( $k ) => 0 !== stripos( (string) $k, 'utm_' ) && ! in_array( strtolower( (string) $k ), array( 'fbclid', 'gclid', 'ref' ), true ), ARRAY_FILTER_USE_KEY );
			ksort( $params );
			$query = $params ? '?' . http_build_query( $params ) : '';
		}

		return $host . strtolower( $path ) . $query;
	}

	/**
	 * Dedupe fingerprint for a URL.
	 *
	 * @param string $url URL.
	 * @return string
	 */
	public static function fingerprint( $url ) {
		return sha1( self::normalize_url( $url ) );
	}

	/**
	 * Lower-case alphanumeric title for fuzzy comparison.
	 *
	 * @param string $title Title.
	 * @return string
	 */
	private static function normalize_title( $title ) {
		return trim( preg_replace( '/\s+/', ' ', preg_replace( '/[^a-z0-9 ]+/', ' ', strtolower( remove_accents( (string) $title ) ) ) ) );
	}

	/**
	 * Parses YYYY-MM-DD or YYYY/MM/DD.
	 *
	 * @param mixed $value Raw date.
	 * @return DateTime|null
	 */
	private static function parse_date( $value ) {
		$value = trim( (string) $value );
		foreach ( array( 'Y-m-d', 'Y/m/d' ) as $format ) {
			$dt = DateTime::createFromFormat( '!' . $format, $value, new DateTimeZone( 'UTC' ) );
			if ( $dt && $dt->format( $format ) === $value ) {
				return $dt;
			}
		}
		return null;
	}

	/**
	 * Country term name for a post, or ''.
	 *
	 * @param int $post_id Post id.
	 * @return string
	 */
	private static function country_name( $post_id ) {
		$terms = wp_get_post_terms( $post_id, 'lfevent-country' );
		return ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : '';
	}

	/**
	 * Finds an lfevent-country term by name, then slug.
	 *
	 * @param string $country Country name.
	 * @return WP_Term|null
	 */
	private static function match_country_term( $country ) {
		$country = trim( (string) $country );
		if ( '' === $country ) {
			return null;
		}
		$aliases = array(
			'usa'                      => 'United States',
			'us'                       => 'United States',
			'u.s.'                     => 'United States',
			'united states of america' => 'United States',
			'uk'                       => 'United Kingdom',
			'u.k.'                     => 'United Kingdom',
			'great britain'            => 'United Kingdom',
			'scotland'                 => 'United Kingdom',
			'wales'                    => 'United Kingdom',
			'the netherlands'          => 'Netherlands',
			'holland'                  => 'Netherlands',
			'republic of korea'        => 'South Korea',
			'russia'                   => 'Russian Federation',
			'uae'                      => 'United Arab Emirates',
			'viet nam'                 => 'Vietnam',
		);
		$key     = strtolower( $country );
		if ( isset( $aliases[ $key ] ) ) {
			$country = $aliases[ $key ];
		}

		$term = get_term_by( 'name', $country, 'lfevent-country' );
		if ( ! $term ) {
			$term = get_term_by( 'slug', sanitize_title( $country ), 'lfevent-country' );
		}
		return $term ? $term : null;
	}
}
