<?php
/**
 * Admin tool for External Events researched manually with Claude Desktop.
 *
 * An admin generates a research prompt for an Event Category, runs it in
 * Claude Desktop with web search, and pastes the JSON result back here. The
 * tool validates and de-duplicates the results and files them as drafts.
 *
 * @package    LFEvents
 * @subpackage LFEvents/includes
 */

/**
 * The "Import from AI Search" screen under External Events.
 */
class LFEvents_External_Import {

	const PAGE_SLUG = 'lfe-external-import';

	const META_FINGERPRINT = 'lfes_external_import_fingerprint';
	const META_SOURCE      = 'lfes_external_import_source';
	const META_CONFIDENCE  = 'lfes_external_import_confidence';
	const META_DATE        = 'lfes_external_import_date';
	const META_EVIDENCE    = 'lfes_external_import_evidence';

	const DEFAULT_MAX_EVENTS = 30;
	const DEFAULT_HORIZON    = 18;
	const MAX_HORIZON        = 36;
	const MAX_EVENTS_PER_RUN = 100;
	const PROMPT_KNOWN_LIMIT = 200;
	const MAX_JSON_BYTES     = 500000;
	const FUZZY_THRESHOLD    = 0.85;

	/**
	 * Statuses that count as "already known" (WP_Query needs trash listed explicitly).
	 *
	 * @var string[]
	 */
	const KNOWN_STATUSES = array( 'publish', 'future', 'private', 'draft', 'pending', 'trash' );

	/**
	 * Current preview, set while handling a Preview submission.
	 *
	 * @var array|null
	 */
	private $preview = null;

	/**
	 * Error messages to show on the page.
	 *
	 * @var string[]
	 */
	private $errors = array();

	/**
	 * Success message HTML to show on the page.
	 *
	 * @var string
	 */
	private $notice = '';

	/**
	 * Pasted JSON, re-displayed when a preview fails.
	 *
	 * @var string
	 */
	private $pasted = '';

	// ----- Registration -----

	/**
	 * Registers the fingerprint meta, which is not editable in the sidebar.
	 */
	public function register_meta() {
		register_post_meta(
			'lfe_external_event',
			self::META_FINGERPRINT,
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => false,
				'sanitize_callback' => 'sanitize_text_field',
			)
		);
	}

	/**
	 * Adds the screen under the External Events menu.
	 */
	public function add_page() {
		$hook = add_submenu_page(
			'edit.php?post_type=lfe_external_event',
			'Import External Events from AI Search',
			'Import from AI Search',
			self::capability(),
			self::PAGE_SLUG,
			array( $this, 'render_page' )
		);
		if ( $hook ) {
			add_action( 'load-' . $hook, array( $this, 'handle_request' ) );
		}
	}

	/**
	 * Capability required to use the importer. Imports can refresh other users' drafts and
	 * add categories to published events, so this is Editor-level rather than create_posts.
	 *
	 * @return string
	 */
	public static function capability() {
		$post_type = get_post_type_object( 'lfe_external_event' );
		return $post_type ? $post_type->cap->edit_others_posts : 'edit_others_posts';
	}

	/**
	 * URL of the importer screen.
	 *
	 * @param array $args Extra query args.
	 * @return string
	 */
	public static function page_url( $args = array() ) {
		return add_query_arg(
			array_merge(
				array(
					'post_type' => 'lfe_external_event',
					'page'      => self::PAGE_SLUG,
				),
				$args
			),
			admin_url( 'edit.php' )
		);
	}

	// ----- Request handling -----

	/**
	 * Handles Preview and Import submissions before the page renders.
	 */
	public function handle_request() {
		if ( ! current_user_can( self::capability() ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to import External Events.' ) );
		}

		if ( isset( $_GET['imported'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$result = get_transient( $this->result_key() );
			if ( $result ) {
				delete_transient( $this->result_key() );
				$this->notice = $result;
			}
		}

		if ( ! isset( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
			return;
		}

		$action = isset( $_POST['lfe_ext_import_action'] ) ? sanitize_key( $_POST['lfe_ext_import_action'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( 'preview' === $action ) {
			check_admin_referer( 'lfe_ext_import_preview' );
			$this->handle_preview();
		} elseif ( 'import' === $action ) {
			check_admin_referer( 'lfe_ext_import_commit' );
			$this->handle_import();
		}
	}

	/**
	 * Parses pasted JSON, validates and de-duplicates it, and stores the preview.
	 */
	private function handle_preview() {
		// Parsed as JSON and every field is sanitised individually in validate_candidate().
		$raw = isset( $_POST['import_json'] ) ? (string) wp_unslash( $_POST['import_json'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		$this->pasted = $raw;
		if ( '' === trim( $raw ) ) {
			$this->errors[] = 'Paste the JSON produced by Claude into the box first.';
			return;
		}
		if ( strlen( $raw ) > self::MAX_JSON_BYTES ) {
			$this->errors[] = 'That JSON is too large. Import one category at a time.';
			return;
		}

		$payload = self::extract_json( $raw );
		if ( ! is_array( $payload ) || ! isset( $payload['events'] ) || ! is_array( $payload['events'] ) ) {
			$this->errors[] = 'Could not find a JSON object with an "events" list. Copy the whole JSON code block from Claude\'s reply, from the opening { to the closing }.';
			return;
		}

		$term_id = isset( $_POST['import_category'] ) ? absint( $_POST['import_category'] ) : 0; //phpcs:ignore WordPress.Security.NonceVerification.Missing
		$term    = $term_id ? get_term( $term_id, 'lfevent-category' ) : null;
		if ( ! $term && ! empty( $payload['category'] ) ) {
			$term = get_term_by( 'slug', sanitize_title( (string) $payload['category'] ), 'lfevent-category' );
		}
		if ( ! $term || is_wp_error( $term ) ) {
			$this->errors[] = 'Choose the Event Category these events belong to.';
			return;
		}

		$events = array_slice( $payload['events'], 0, self::MAX_EVENTS_PER_RUN );
		$rows   = $this->evaluate( $events, $term );
		$token  = wp_generate_password( 20, false );

		set_transient(
			$this->preview_key( $token ),
			array(
				'term_id' => $term->term_id,
				'rows'    => $rows,
			),
			HOUR_IN_SECONDS
		);

		$this->preview = array(
			'token'     => $token,
			'term'      => $term,
			'rows'      => $rows,
			'truncated' => count( $payload['events'] ) > self::MAX_EVENTS_PER_RUN,
		);
	}

	/**
	 * Creates/updates the selected preview rows, then redirects back with a summary.
	 */
	private function handle_import() {
		$token = isset( $_POST['token'] ) ? sanitize_text_field( wp_unslash( $_POST['token'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Missing
		$data  = $token ? get_transient( $this->preview_key( $token ) ) : false;
		$term  = $data ? get_term( (int) $data['term_id'], 'lfevent-category' ) : null;
		if ( ! $data || ! $term || is_wp_error( $term ) ) {
			$this->errors[] = 'This preview has expired or was already imported. Paste the JSON again and click Preview.';
			return;
		}

		$selected = isset( $_POST['rows'] ) ? array_map( 'absint', (array) wp_unslash( $_POST['rows'] ) ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Missing
		$index    = $this->get_dedupe_index();
		$counts   = array(
			'create' => 0,
			'update' => 0,
			'tag'    => 0,
			'skip'   => 0,
		);

		foreach ( $selected as $i ) {
			if ( empty( $data['rows'][ $i ]['candidate'] ) ) {
				continue;
			}
			$candidate = $data['rows'][ $i ]['candidate'];

			// Re-check against fresh data in case something was added since the preview.
			$match  = $this->find_existing( $candidate, $index );
			$action = $this->decide( $match, $term )['action'];

			if ( 'create' === $action ) {
				$post_id = $this->create_draft( $candidate, $term );
				if ( is_wp_error( $post_id ) ) {
					++$counts['skip'];
					continue;
				}
				$index[] = $this->index_entry( $post_id, $candidate['title'], $candidate['url'], 'draft', false, array( $term->term_id ), $candidate['date_start'], $candidate['country'] );
			} elseif ( 'update' === $action ) {
				$this->update_draft( $match['post_id'], $candidate, $term );
			} elseif ( 'tag' === $action ) {
				wp_set_object_terms( $match['post_id'], array( $term->term_id ), 'lfevent-category', true );
			}
			++$counts[ $action ];
		}

		delete_transient( $this->preview_key( $token ) );

		$drafts_url = add_query_arg(
			array(
				'post_type'   => 'lfe_external_event',
				'post_status' => 'draft',
			),
			admin_url( 'edit.php' )
		);
		set_transient(
			$this->result_key(),
			sprintf(
				'Imported into <strong>%s</strong>: %d new draft(s), %d draft(s) updated, %d published event(s) added to this category%s. <a href="%s">Review drafts &rarr;</a>',
				esc_html( $term->name ),
				$counts['create'],
				$counts['update'],
				$counts['tag'],
				$counts['skip'] ? sprintf( ', %d skipped because they already exist', $counts['skip'] ) : '',
				esc_url( $drafts_url )
			),
			5 * MINUTE_IN_SECONDS
		);

		wp_safe_redirect( self::page_url( array( 'imported' => 1 ) ) );
		exit;
	}

	/**
	 * Transient key for a preview.
	 *
	 * @param string $token Preview token.
	 * @return string
	 */
	private function preview_key( $token ) {
		return 'lfe_ext_import_' . get_current_user_id() . '_' . md5( $token );
	}

	/**
	 * Transient key for the post-import summary.
	 *
	 * @return string
	 */
	private function result_key() {
		return 'lfe_ext_import_result_' . get_current_user_id();
	}

	// ----- Prompt -----

	/**
	 * Builds the research prompt the admin pastes into Claude Desktop.
	 *
	 * @param WP_Term $term    Category.
	 * @param int     $max     Max events to ask for.
	 * @param int     $horizon Look-ahead window in months.
	 * @return string
	 */
	public function build_prompt( WP_Term $term, $max, $horizon ) {
		$today = gmdate( 'Y-m-d' );
		$until = gmdate( 'Y-m-d', strtotime( "+{$horizon} months" ) );

		// Term descriptions may carry HTML/entities and stray trailing separators.
		$scope = trim( wp_strip_all_tags( html_entity_decode( (string) $term->description, ENT_QUOTES, 'UTF-8' ) ), " \t\n\r,;" );

		$known = array_filter( $this->get_dedupe_index(), fn( $e ) => in_array( $term->term_id, $e['terms'], true ) );
		$known = array_slice( $known, 0, self::PROMPT_KNOWN_LIMIT );
		$lines = array_map( fn( $e ) => '- ' . $e['title'] . ( $e['url'] ? ' (' . $e['url'] . ')' : '' ), $known );

		$schema = array(
			'category'  => $term->slug,
			'generated' => $today,
			'events'    => array(
				array(
					'title'       => 'Official event name, including the year if it has one',
					'url'         => 'https://official-event-website/',
					'date_start'  => 'YYYY-MM-DD',
					'date_end'    => 'YYYY-MM-DD',
					'city'        => '',
					'country'     => 'Full country name in English',
					'virtual'     => false,
					'organizer'   => 'Organising company or community',
					'description' => 'One or two plain-text sentences, max 400 characters',
					'source_urls' => array( 'https://pages-where-you-confirmed-the-details/' ),
					'confidence'  => 'high | medium | low',
					'notes'       => 'Anything an editor should double-check',
				),
			),
		);

		return "You are helping The Linux Foundation maintain a public, worldwide calendar of conferences on the theme \"{$term->name}\""
			. ( $scope ? " ({$scope})" : '' ) . ".\n\n"
			. "Use web search to find up to {$max} upcoming conferences or summits on this theme, anywhere in the world, that start between {$today} and {$until}.\n\n"
			. "How to research:\n"
			. "- Search broadly: by sub-topic, by region (North America, Europe, Asia-Pacific, Latin America, Middle East & Africa), and for next year's editions of recurring events.\n"
			. "- Open each event's official website and confirm the dates and location there before including it.\n"
			. "- Never invent events, dates or URLs. If you cannot confirm a detail, leave that field empty and explain in \"notes\".\n\n"
			. "What to include:\n"
			. "- Multi-track conferences, summits and major industry events only. Do not include meetups, webinars, single workshops, courses, hackathons or vendor sales events.\n"
			. "- Notable virtual-only events are welcome.\n"
			. "- Do not include events organised by The Linux Foundation or its foundations and projects (for example CNCF, OpenSSF, PyTorch Foundation, LF AI & Data, Agentic AI Foundation).\n"
			. "- Do not include these events, which are already on our calendar or were previously rejected:\n"
			. ( $lines ? implode( "\n", $lines ) : '- (none yet)' ) . "\n\n"
			. "Output format:\n"
			. "When you have finished researching, reply with ONLY a single JSON code block and no other text, in exactly this shape:\n\n"
			. "```json\n" . wp_json_encode( $schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "\n```\n\n"
			. "Field rules:\n"
			. "- Keep \"category\" exactly as \"{$term->slug}\".\n"
			. "- Dates use YYYY-MM-DD. For a one-day event, date_end equals date_start.\n"
			. "- \"virtual\" is true only if people can attend online; use an empty city for virtual-only events.\n"
			. "- \"confidence\": high = dates and location confirmed on the official site; medium = confirmed on a reliable secondary source; low = announced but details unconfirmed.\n"
			. '- Use an empty string for any unknown text field.';
	}

	// ----- Evaluation -----

	/**
	 * Validates and de-duplicates pasted events.
	 *
	 * @param array   $events Raw events.
	 * @param WP_Term $term   Target category.
	 * @return array[] Rows: candidate|null, raw_title, action, reason, post_id.
	 */
	private function evaluate( array $events, WP_Term $term ) {
		$index = $this->get_dedupe_index();
		$seen  = array();
		$rows  = array();

		foreach ( $events as $raw ) {
			$raw       = is_array( $raw ) ? $raw : array();
			$candidate = $this->validate_candidate( $raw );

			if ( is_wp_error( $candidate ) ) {
				$rows[] = array(
					'candidate' => null,
					'raw_title' => sanitize_text_field( (string) ( $raw['title'] ?? '(untitled)' ) ),
					'action'    => 'reject',
					'reason'    => $candidate->get_error_message(),
					'post_id'   => 0,
				);
				continue;
			}

			$fingerprint = self::fingerprint( $candidate['url'] );
			if ( isset( $seen[ $fingerprint ] ) ) {
				$rows[] = array(
					'candidate' => $candidate,
					'raw_title' => $candidate['title'],
					'action'    => 'skip',
					'reason'    => 'Duplicate of another event in this JSON',
					'post_id'   => 0,
				);
				continue;
			}
			$seen[ $fingerprint ] = true;

			$match    = $this->find_existing( $candidate, $index );
			$decision = $this->decide( $match, $term );
			$rows[]   = array(
				'candidate' => $candidate,
				'raw_title' => $candidate['title'],
				'action'    => $decision['action'],
				'reason'    => $decision['reason'],
				'post_id'   => $match ? $match['post_id'] : 0,
			);
		}

		return $rows;
	}

	/**
	 * What to do with a candidate given the existing event it matched (if any).
	 *
	 * @param array|null $existing Index entry.
	 * @param WP_Term    $term     Target category.
	 * @return array { action: create|update|tag|skip, reason: string }
	 */
	private function decide( $existing, WP_Term $term ) {
		if ( ! $existing ) {
			return array(
				'action' => 'create',
				'reason' => '',
			);
		}

		$label = '"' . $existing['title'] . '" (#' . $existing['post_id'] . ')';

		if ( $existing['is_lf'] ) {
			$reason = 'Already listed as the Linux Foundation event ' . $label;
			$action = 'skip';
		} elseif ( 'trash' === $existing['status'] ) {
			$reason = 'Previously rejected (in Trash): ' . $label;
			$action = 'skip';
		} elseif ( in_array( $existing['status'], array( 'draft', 'pending' ), true ) ) {
			$reason = 'Will refresh the existing draft ' . $label;
			$action = 'update';
		} elseif ( in_array( $term->term_id, $existing['terms'], true ) ) {
			$reason = 'Already published: ' . $label;
			$action = 'skip';
		} else {
			$reason = 'Already published in another category; will add "' . $term->name . '" to ' . $label;
			$action = 'tag';
		}

		if ( in_array( $action, array( 'update', 'tag' ), true ) && ! current_user_can( 'edit_post', $existing['post_id'] ) ) {
			$reason = 'You don\'t have permission to edit ' . $label;
			$action = 'skip';
		}

		return array(
			'action' => $action,
			'reason' => $reason,
		);
	}

	/**
	 * Validates and normalises one pasted event.
	 *
	 * @param array $raw Raw event.
	 * @return array|WP_Error Candidate with dates as YYYY/MM/DD.
	 */
	private function validate_candidate( array $raw ) {
		$title = sanitize_text_field( (string) ( $raw['title'] ?? '' ) );
		if ( '' === $title ) {
			return new WP_Error( 'lfe_import_invalid', 'Missing title' );
		}

		$url  = esc_url_raw( trim( (string) ( $raw['url'] ?? '' ) ), array( 'http', 'https' ) );
		$host = (string) wp_parse_url( $url, PHP_URL_HOST );
		if ( '' === $url || ! str_contains( $host, '.' ) ) {
			return new WP_Error( 'lfe_import_invalid', 'Missing or invalid website URL' );
		}
		// A host that doesn't resolve usually means a hallucinated URL.
		if ( apply_filters( 'lfevents_external_import_verify_dns', true ) && gethostbyname( $host ) === $host ) {
			return new WP_Error( 'lfe_import_invalid', 'Website ' . $host . ' does not exist' );
		}

		$start = self::parse_date( $raw['date_start'] ?? '' );
		if ( ! $start ) {
			return new WP_Error( 'lfe_import_invalid', 'Missing or invalid start date' );
		}
		$end = self::parse_date( $raw['date_end'] ?? '' );
		if ( ! $end || $end < $start ) {
			$end = $start;
		}

		$today = new DateTime( 'today', new DateTimeZone( 'UTC' ) );
		if ( $end < $today ) {
			return new WP_Error( 'lfe_import_invalid', 'Event has already ended' );
		}
		if ( $start > ( clone $today )->modify( '+' . self::MAX_HORIZON . ' months' ) ) {
			return new WP_Error( 'lfe_import_invalid', 'Start date is more than ' . self::MAX_HORIZON . ' months away' );
		}

		$sources = array_values(
			array_filter(
				array_map(
					fn( $u ) => esc_url_raw( trim( (string) $u ), array( 'http', 'https' ) ),
					is_array( $raw['source_urls'] ?? null ) ? $raw['source_urls'] : array()
				)
			)
		);

		$confidence = strtolower( trim( sanitize_text_field( (string) ( $raw['confidence'] ?? '' ) ) ) );
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

	// ----- Known events & dedupe -----

	/**
	 * Every upcoming LF Event and every External Event (any status, including trash).
	 *
	 * @return array[] Entries from index_entry().
	 */
	private function get_dedupe_index() {
		$index = array();

		foreach ( ( new WP_Query( LFEvents_API::build_event_query_args( 'upcoming' ) ) )->posts as $post ) {
			$url     = (string) get_post_meta( $post->ID, 'lfes_external_url', true );
			$index[] = $this->index_entry(
				$post->ID,
				$post->post_title,
				$url ? $url : get_permalink( $post ),
				$post->post_status,
				true,
				wp_get_post_terms( $post->ID, 'lfevent-category', array( 'fields' => 'ids' ) ),
				(string) get_post_meta( $post->ID, 'lfes_date_start', true ),
				self::country_name( $post->ID )
			);
		}

		$externals = new WP_Query(
			array(
				'post_type'      => 'lfe_external_event',
				'post_status'    => self::KNOWN_STATUSES,
				'posts_per_page' => -1,
				'no_found_rows'  => true,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);
		foreach ( $externals->posts as $post ) {
			$entry = $this->index_entry(
				$post->ID,
				$post->post_title,
				(string) get_post_meta( $post->ID, 'lfes_external_event_url', true ),
				$post->post_status,
				false,
				wp_get_post_terms( $post->ID, 'lfevent-category', array( 'fields' => 'ids' ) ),
				(string) get_post_meta( $post->ID, 'lfes_external_date_start', true ),
				self::country_name( $post->ID )
			);
			// Keeps matching the originally imported URL after an editor corrects it.
			$stored = (string) get_post_meta( $post->ID, self::META_FINGERPRINT, true );
			if ( $stored && ! in_array( $stored, $entry['fingerprints'], true ) ) {
				$entry['fingerprints'][] = $stored;
			}
			$index[] = $entry;
		}

		return $index;
	}

	/**
	 * Builds one dedupe index entry.
	 *
	 * @param int          $post_id    Post id.
	 * @param string       $title      Title.
	 * @param string       $url        Event URL.
	 * @param string       $status     Post status.
	 * @param bool         $is_lf      Whether this is an LF Event.
	 * @param int[]|object $terms      lfevent-category term ids.
	 * @param string       $date_start Start date (YYYY/MM/DD).
	 * @param string       $country    Country name.
	 * @return array
	 */
	private function index_entry( $post_id, $title, $url, $status, $is_lf, $terms, $date_start, $country ) {
		return array(
			'post_id'      => (int) $post_id,
			'title'        => (string) $title,
			'url'          => (string) $url,
			'fingerprints' => $url ? array( self::fingerprint( $url ) ) : array(),
			'status'       => (string) $status,
			'is_lf'        => (bool) $is_lf,
			'terms'        => is_array( $terms ) ? array_map( 'intval', $terms ) : array(),
			'year'         => (int) substr( (string) $date_start, 0, 4 ),
			'country'      => (string) $country,
		);
	}

	/**
	 * Finds a known event matching the candidate: by URL, then by similar title
	 * in the same year and country.
	 *
	 * @param array $candidate Validated candidate.
	 * @param array $index     Dedupe index.
	 * @return array|null Index entry.
	 */
	private function find_existing( array $candidate, array $index ) {
		$fingerprint = self::fingerprint( $candidate['url'] );
		foreach ( $index as $entry ) {
			if ( in_array( $fingerprint, $entry['fingerprints'], true ) ) {
				return $entry;
			}
		}

		$year   = (int) substr( $candidate['date_start'], 0, 4 );
		$needle = self::title_key( $candidate['title'] );
		if ( '' === $needle ) {
			return null;
		}
		foreach ( $index as $entry ) {
			if ( $entry['year'] && $entry['year'] !== $year ) {
				continue;
			}
			if ( $entry['country'] && $candidate['country'] && 0 !== strcasecmp( $entry['country'], $candidate['country'] ) ) {
				continue;
			}
			$hay = self::title_key( $entry['title'] );
			if ( '' === $hay ) {
				continue;
			}
			similar_text( $needle, $hay, $percent );
			if ( $percent / 100 >= self::FUZZY_THRESHOLD ) {
				return $entry;
			}
		}

		return null;
	}

	// ----- Persistence -----

	/**
	 * Inserts a draft External Event.
	 *
	 * @param array   $candidate Validated candidate.
	 * @param WP_Term $term      Category.
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
					self::META_DATE             => gmdate( 'Y/m/d' ),
					self::META_EVIDENCE         => $this->format_evidence( $candidate, $country_term ),
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
	 * Refreshes a still-draft External Event. Title and URL are kept so editor corrections survive.
	 *
	 * @param int     $post_id   Draft id.
	 * @param array   $candidate Validated candidate.
	 * @param WP_Term $term      Category.
	 */
	private function update_draft( $post_id, array $candidate, WP_Term $term ) {
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
		update_post_meta( $post_id, self::META_DATE, gmdate( 'Y/m/d' ) );
		if ( ! get_post_meta( $post_id, self::META_FINGERPRINT, true ) ) {
			update_post_meta( $post_id, self::META_FINGERPRINT, self::fingerprint( $candidate['url'] ) );
		}

		wp_set_object_terms( $post_id, array( $term->term_id ), 'lfevent-category', true );
		$country_term = self::match_country_term( $candidate['country'] );
		if ( $country_term && ! has_term( '', 'lfevent-country', $post_id ) ) {
			wp_set_object_terms( $post_id, array( $country_term->term_id ), 'lfevent-country' );
		}

		$previous = (string) get_post_meta( $post_id, self::META_EVIDENCE, true );
		update_post_meta( $post_id, self::META_EVIDENCE, trim( $this->format_evidence( $candidate, $country_term ) . ( $previous ? "\n\n--- earlier import ---\n" . $previous : '' ) ) );
	}

	/**
	 * Human-readable evidence stored on the draft for the reviewer.
	 *
	 * @param array        $candidate    Validated candidate.
	 * @param WP_Term|null $country_term Matched country.
	 * @return string
	 */
	private function format_evidence( array $candidate, $country_term ) {
		$user    = wp_get_current_user();
		$lines   = array();
		$lines[] = 'Imported ' . gmdate( 'Y-m-d' ) . ( $user->exists() ? ' by ' . $user->display_name : '' ) . ' - confidence: ' . $candidate['confidence'];
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
			$lines[] = 'Country "' . $candidate['country'] . '" did not match an Event Country - set it manually.';
		}
		return implode( "\n", $lines );
	}

	// ----- Rendering -----

	/**
	 * Renders the importer screen.
	 */
	public function render_page() {
		// Read-only GET parameters for the prompt generator.
		$term_id = isset( $_GET['category'] ) ? absint( $_GET['category'] ) : 0; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$max     = isset( $_GET['max'] ) ? min( 50, max( 1, absint( $_GET['max'] ) ) ) : self::DEFAULT_MAX_EVENTS; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$horizon = isset( $_GET['horizon'] ) ? min( self::MAX_HORIZON, max( 1, absint( $_GET['horizon'] ) ) ) : self::DEFAULT_HORIZON; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$term    = $term_id ? get_term( $term_id, 'lfevent-category' ) : null;
		$term    = ( $term && ! is_wp_error( $term ) ) ? $term : null;

		$drafts_url = add_query_arg(
			array(
				'post_type'   => 'lfe_external_event',
				'post_status' => 'draft',
			),
			admin_url( 'edit.php' )
		);
		?>
		<div class="wrap lfe-ext-import">
			<h1>Import External Events from AI Search</h1>

			<?php foreach ( $this->errors as $error ) : ?>
				<div class="notice notice-error"><p><?php echo esc_html( $error ); ?></p></div>
			<?php endforeach; ?>
			<?php if ( $this->notice ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php echo wp_kses_post( $this->notice ); ?></p></div>
			<?php endif; ?>

			<p style="max-width:52em">
				Use this tool to find third-party conferences for a Theme Calendar. You generate a research prompt here, run it in
				<strong>Claude Desktop</strong> with web search turned on, and paste Claude's JSON answer back here. Events are checked
				against everything already on the site and saved as <strong>drafts</strong>. Nothing appears on the site until you publish it.
			</p>

			<style>
				.lfe-ext-import h2 { margin-top: 2em; padding-top: 1em; border-top: 1px solid #dcdcde; }
				.lfe-ext-import ol li { margin-bottom: .5em; }
				.lfe-ext-import .lfe-badge { display: inline-block; padding: 1px 8px; border-radius: 10px; font-size: 12px; font-weight: 600; }
				.lfe-ext-import .lfe-badge--create { background: #d1e7dd; color: #0a3622; }
				.lfe-ext-import .lfe-badge--update, .lfe-ext-import .lfe-badge--tag { background: #cfe2ff; color: #052c65; }
				.lfe-ext-import .lfe-badge--skip { background: #e2e3e5; color: #2b2f32; }
				.lfe-ext-import .lfe-badge--reject { background: #f8d7da; color: #58151c; }
				.lfe-ext-import tr.lfe-row--inactive td { color: #787c82; }
			</style>

			<h2>Step 1 &mdash; Generate the research prompt</h2>
			<form method="get" action="<?php echo esc_url( admin_url( 'edit.php' ) ); ?>">
				<input type="hidden" name="post_type" value="lfe_external_event">
				<input type="hidden" name="page" value="<?php echo esc_attr( self::PAGE_SLUG ); ?>">
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="lfe-import-category">Event Category</label></th>
						<td>
							<?php $this->render_category_select( 'category', 'lfe-import-category', $term ? $term->term_id : 0, 'Choose a category&hellip;' ); ?>
							<p class="description">The category's <strong>Description</strong> (Events &rsaquo; Event Categories) is added to the prompt to describe the theme, e.g. "machine learning, LLMs, AI agents, MCP". A good description gives better results.</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="lfe-import-max">Number of events</label></th>
						<td><input id="lfe-import-max" type="number" min="1" max="50" class="small-text" name="max" value="<?php echo esc_attr( $max ); ?>"> <span class="description">maximum to ask for</span></td>
					</tr>
					<tr>
						<th scope="row"><label for="lfe-import-horizon">Look ahead</label></th>
						<td><input id="lfe-import-horizon" type="number" min="1" max="<?php echo esc_attr( self::MAX_HORIZON ); ?>" class="small-text" name="horizon" value="<?php echo esc_attr( $horizon ); ?>"> months</td>
					</tr>
				</table>
				<?php submit_button( 'Generate prompt', 'secondary', '', false ); ?>
			</form>

			<?php if ( $term ) : ?>
				<p style="margin-top:1.5em"><strong>Prompt for &ldquo;<?php echo esc_html( $term->name ); ?>&rdquo;</strong> &mdash; it already lists the events we have, so Claude won't suggest them again.</p>
				<textarea id="lfe-import-prompt" class="large-text code" rows="18" readonly onclick="this.select()"><?php echo esc_textarea( $this->build_prompt( $term, $max, $horizon ) ); ?></textarea>
				<p>
					<button type="button" class="button button-primary" onclick="var t=document.getElementById('lfe-import-prompt');t.select();(navigator.clipboard?navigator.clipboard.writeText(t.value):Promise.reject()).then(function(){this.textContent='Copied!';}.bind(this)).catch(function(){document.execCommand('copy');});">Copy prompt</button>
				</p>
			<?php endif; ?>

			<h2>Step 2 &mdash; Run the search in Claude Desktop</h2>
			<ol style="max-width:52em">
				<li>Open <strong>Claude Desktop</strong> (or claude.ai) and start a <strong>new chat</strong>.</li>
				<li>Make sure <strong>Web search</strong> is turned on in the tools menu of the message box. For a more thorough search you can also turn on <strong>Research</strong>; it takes longer but usually finds more events.</li>
				<li>Paste the prompt from Step 1 and send it. The search can take several minutes.</li>
				<li>If Claude asks a question, reply <em>&ldquo;Use your best judgement and return the JSON.&rdquo;</em></li>
				<li>When it finishes, copy the JSON code block from its reply (use the copy button on the code block). If Claude put the result in an artifact or file, copy its full contents.</li>
				<li>Optional: for more results, reply <em>&ldquo;Search again, focusing on regions and sub-topics you haven't covered, and return one combined JSON.&rdquo;</em> and use the combined JSON.</li>
			</ol>

			<h2>Step 3 &mdash; Paste the results and preview</h2>
			<form method="post" action="<?php echo esc_url( self::page_url( $term ? array( 'category' => $term->term_id ) : array() ) ); ?>">
				<?php wp_nonce_field( 'lfe_ext_import_preview' ); ?>
				<input type="hidden" name="lfe_ext_import_action" value="preview">
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="lfe-import-json-category">Add to category</label></th>
						<td>
							<?php $this->render_category_select( 'import_category', 'lfe-import-json-category', $this->preview ? $this->preview['term']->term_id : ( $term ? $term->term_id : 0 ), 'Use the category named in the JSON' ); ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="lfe-import-json">Claude's JSON</label></th>
						<td>
							<textarea id="lfe-import-json" name="import_json" class="large-text code" rows="10" placeholder='{"category": "...", "events": [ ... ]}'><?php echo esc_textarea( $this->errors ? $this->pasted : '' ); ?></textarea>
							<p class="description">Nothing is saved yet. The next screen shows what will happen to each event.</p>
						</td>
					</tr>
				</table>
				<?php submit_button( 'Preview', 'secondary', '', false ); ?>
			</form>

			<?php
			if ( $this->preview ) {
				$this->render_preview();
			}
			?>

			<h2>Step 4 &mdash; Review and publish</h2>
			<ol style="max-width:52em">
				<li>Open the <a href="<?php echo esc_url( $drafts_url ); ?>">draft External Events</a>. Imported events show <strong>AI search</strong> and Claude's confidence in the Source column.</li>
				<li>Open each draft and check the <strong>Import Review</strong> panel in the Event Settings sidebar for Claude's sources and notes. Visit the event website and confirm the name, dates, city and country.</li>
				<li>If the review notes say the country didn't match, set it in the <strong>Event Countries</strong> panel.</li>
				<li><strong>Publish</strong> the event to add it to every Theme Calendar that uses its category.</li>
				<li>For events that don't belong on the calendar, <strong>Move to Trash</strong> rather than deleting them. Trashed events are listed as &ldquo;previously rejected&rdquo; in future prompts and are skipped on import, so they won't come back. Emptying the trash removes that memory.</li>
			</ol>
		</div>
		<?php
	}

	/**
	 * Renders the preview table and Import form.
	 */
	private function render_preview() {
		$term   = $this->preview['term'];
		$rows   = $this->preview['rows'];
		$counts = array_count_values( wp_list_pluck( $rows, 'action' ) );
		?>
		<div id="lfe-import-preview" style="margin-top:1.5em">
			<h3>Preview for &ldquo;<?php echo esc_html( $term->name ); ?>&rdquo;</h3>
			<p>
				<?php
				printf(
					'%d event(s) in the JSON: <strong>%d new</strong>, %d draft update(s), %d category addition(s), %d already on the site, %d rejected.',
					count( $rows ),
					(int) ( $counts['create'] ?? 0 ),
					(int) ( $counts['update'] ?? 0 ),
					(int) ( $counts['tag'] ?? 0 ),
					(int) ( $counts['skip'] ?? 0 ),
					(int) ( $counts['reject'] ?? 0 )
				);
				if ( $this->preview['truncated'] ) {
					echo ' Only the first ' . (int) self::MAX_EVENTS_PER_RUN . ' events were read.';
				}
				?>
				Low-confidence events are unticked by default.
			</p>
			<form method="post" action="<?php echo esc_url( self::page_url() ); ?>">
				<?php wp_nonce_field( 'lfe_ext_import_commit' ); ?>
				<input type="hidden" name="lfe_ext_import_action" value="import">
				<input type="hidden" name="token" value="<?php echo esc_attr( $this->preview['token'] ); ?>">
				<table class="widefat striped">
					<thead>
						<tr>
							<td class="check-column"><span class="screen-reader-text">Import</span></td>
							<th>Event</th>
							<th>Dates</th>
							<th>Location</th>
							<th>Organizer</th>
							<th>Confidence</th>
							<th>What will happen</th>
						</tr>
					</thead>
					<tbody>
					<?php foreach ( $rows as $i => $row ) : ?>
						<?php
						$c          = $row['candidate'];
						$actionable = in_array( $row['action'], array( 'create', 'update', 'tag' ), true );
						$labels     = array(
							'create' => 'New draft',
							'update' => 'Update draft',
							'tag'    => 'Add category',
							'skip'   => 'Skip',
							'reject' => 'Rejected',
						);
						?>
						<tr class="<?php echo $actionable ? '' : 'lfe-row--inactive'; ?>">
							<th scope="row" class="check-column">
								<input type="checkbox" name="rows[]" value="<?php echo esc_attr( $i ); ?>" aria-label="<?php echo esc_attr( 'Import ' . $row['raw_title'] ); ?>"
									<?php checked( $actionable && $c && 'low' !== $c['confidence'] ); ?>
									<?php disabled( ! $actionable ); ?>>
							</th>
							<td>
								<?php if ( $c ) : ?>
									<strong><a href="<?php echo esc_url( $c['url'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $c['title'] ); ?></a></strong>
									<?php if ( $c['description'] ) : ?>
										<br><span class="description"><?php echo esc_html( wp_trim_words( $c['description'], 25 ) ); ?></span>
									<?php endif; ?>
									<?php if ( $c['notes'] ) : ?>
										<br><em>Note: <?php echo esc_html( $c['notes'] ); ?></em>
									<?php endif; ?>
								<?php else : ?>
									<strong><?php echo esc_html( $row['raw_title'] ); ?></strong>
								<?php endif; ?>
							</td>
							<td><?php echo $c ? esc_html( $c['date_start'] . ( $c['date_end'] !== $c['date_start'] ? ' – ' . $c['date_end'] : '' ) ) : '&ndash;'; ?></td>
							<td>
								<?php
								if ( $c ) {
									$place = trim( $c['city'] . ( $c['city'] && $c['country'] ? ', ' : '' ) . $c['country'] );
									echo esc_html( $place . ( $c['virtual'] ? ( $place ? ' + ' : '' ) . 'Virtual' : '' ) );
								} else {
									echo '&ndash;';
								}
								?>
							</td>
							<td><?php echo $c ? esc_html( $c['organizer'] ) : '&ndash;'; ?></td>
							<td><?php echo $c ? esc_html( ucfirst( $c['confidence'] ) ) : '&ndash;'; ?></td>
							<td>
								<span class="lfe-badge lfe-badge--<?php echo esc_attr( $row['action'] ); ?>"><?php echo esc_html( $labels[ $row['action'] ] ); ?></span>
								<?php if ( $row['reason'] ) : ?>
									<br><small>
										<?php if ( $row['post_id'] && current_user_can( 'edit_post', $row['post_id'] ) ) : ?>
											<a href="<?php echo esc_url( get_edit_post_link( $row['post_id'] ) ); ?>"><?php echo esc_html( $row['reason'] ); ?></a>
										<?php else : ?>
											<?php echo esc_html( $row['reason'] ); ?>
										<?php endif; ?>
									</small>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				<?php if ( ( $counts['create'] ?? 0 ) + ( $counts['update'] ?? 0 ) + ( $counts['tag'] ?? 0 ) ) : ?>
					<?php submit_button( 'Import selected as drafts', 'primary' ); ?>
				<?php else : ?>
					<p><strong>Nothing new to import.</strong></p>
				<?php endif; ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Category dropdown, with categories used by a published Theme Calendar listed first.
	 *
	 * @param string $name        Field name.
	 * @param string $id          Element id.
	 * @param int    $selected    Selected term id.
	 * @param string $placeholder Label for the empty option (may contain entities).
	 */
	private function render_category_select( $name, $id, $selected, $placeholder ) {
		$terms = get_terms(
			array(
				'taxonomy'   => 'lfevent-category',
				'hide_empty' => false,
			)
		);
		$terms = is_wp_error( $terms ) ? array() : $terms;

		$calendar_ids = get_posts(
			array(
				'post_type'      => 'lfe_theme_calendar',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
			)
		);
		$in_use       = $calendar_ids ? wp_get_object_terms( $calendar_ids, 'lfevent-category', array( 'fields' => 'ids' ) ) : array();
		$in_use       = is_wp_error( $in_use ) ? array() : array_map( 'intval', $in_use );

		$groups = array(
			'Used by a Theme Calendar' => array_filter( $terms, fn( $t ) => in_array( $t->term_id, $in_use, true ) ),
			'Other categories'         => array_filter( $terms, fn( $t ) => ! in_array( $t->term_id, $in_use, true ) ),
		);
		?>
		<select name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $id ); ?>">
			<option value="0"><?php echo wp_kses( $placeholder, array() ); ?></option>
			<?php foreach ( $groups as $label => $group ) : ?>
				<?php if ( $group ) : ?>
					<optgroup label="<?php echo esc_attr( $label ); ?>">
						<?php foreach ( $group as $t ) : ?>
							<option value="<?php echo esc_attr( $t->term_id ); ?>" <?php selected( (int) $selected, $t->term_id ); ?>><?php echo esc_html( html_entity_decode( $t->name, ENT_QUOTES, 'UTF-8' ) ); ?></option>
						<?php endforeach; ?>
					</optgroup>
				<?php endif; ?>
			<?php endforeach; ?>
		</select>
		<?php
	}

	// ----- Helpers -----

	/**
	 * Extracts the first JSON object from pasted text, tolerating prose and code fences.
	 *
	 * @param string $text Pasted text.
	 * @return array|null
	 */
	public static function extract_json( $text ) {
		$text    = trim( (string) $text );
		$decoded = json_decode( $text, true );
		if ( is_array( $decoded ) ) {
			return $decoded;
		}

		$start = strpos( $text, '{' );
		$end   = strrpos( $text, '}' );
		if ( false === $start || false === $end || $end <= $start ) {
			return null;
		}
		$decoded = json_decode( substr( $text, $start, $end - $start + 1 ), true );
		return is_array( $decoded ) ? $decoded : null;
	}

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
			$params = array_filter(
				$params,
				fn( $k ) => 0 !== stripos( (string) $k, 'utm_' ) && ! in_array( strtolower( (string) $k ), array( 'fbclid', 'gclid', 'ref' ), true ),
				ARRAY_FILTER_USE_KEY
			);
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
	 * Lower-case alphanumeric title with years removed, for fuzzy matching.
	 *
	 * @param string $title Title.
	 * @return string
	 */
	private static function title_key( $title ) {
		$key = strtolower( remove_accents( html_entity_decode( (string) $title, ENT_QUOTES, 'UTF-8' ) ) );
		$key = preg_replace( '/\b20\d{2}\b/', ' ', $key );
		$key = preg_replace( '/[^a-z0-9 ]+/', ' ', $key );
		return trim( preg_replace( '/\s+/', ' ', $key ) );
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
	 * Finds an lfevent-country term by name (with common aliases), then slug.
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
