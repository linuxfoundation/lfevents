<?php
/**
 * Serves a per-event Markdown mirror of a schedule page.
 *
 * Any page carrying a Sessionize Schedule block also answers at the same path
 * with `.md` appended — so `/my-event/program/schedule/` is mirrored at
 * `/my-event/program/schedule.md`.
 *
 * Why this exists: the schedule page renders the full programme as HTML, but an
 * AI agent asked to help someone plan their days reads that page through an
 * HTML-to-text extractor. Extractors drop `[hidden]` content, strip `<script>`
 * (so the JSON island is invisible to them), and concatenate adjacent elements
 * without separators, which fuses a session's title, room and speakers into one
 * run of text. They also truncate, and on a large event the sponsor wall and
 * filter UI can crowd out the sessions.
 *
 * A Markdown document sidesteps all of that: one heading per session, one
 * labelled field per line, abstracts in full, no navigation or chrome.
 *
 * The document is generated on request from the same cached payload the block
 * renders from, so it cannot drift from the page it mirrors.
 *
 * @package Sessionize_Blocks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Generates and serves the per-event schedule.md document.
 */
class Sessionize_Schedule_Md {

	/**
	 * Block name that makes a page eligible for a Markdown mirror.
	 *
	 * @var string
	 */
	const BLOCK_NAME = 'custom/sessionize-schedule';

	/**
	 * Suffix appended to a schedule page's path.
	 *
	 * @var string
	 */
	const SUFFIX = '.md';

	/**
	 * Registers hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'parse_request', array( __CLASS__, 'maybe_serve' ) );
		add_action( 'wp_head', array( __CLASS__, 'add_alternate_link' ) );
	}

	/**
	 * Serves the Markdown document when a `*.md` path resolves to a schedule page.
	 *
	 * Hooked to 'parse_request', which runs before WordPress decides the request
	 * is a 404, and before 'send_headers' — so the cache headers are emitted here.
	 *
	 * @param WP $wp The WordPress environment instance.
	 * @return void
	 */
	public static function maybe_serve( $wp ) {
		if ( is_admin() || ! is_object( $wp ) || ! isset( $wp->request ) ) {
			return;
		}

		$request = trim( (string) $wp->request, '/' );

		if ( '' === $request || substr( $request, -3 ) !== self::SUFFIX ) {
			return;
		}

		$post = self::resolve_post( substr( $request, 0, -3 ) );

		if ( ! $post instanceof WP_Post ) {
			return;
		}

		$attributes = self::find_block_attributes( $post->post_content );

		if ( null === $attributes ) {
			return;
		}

		$body = self::render( $post, $attributes );

		if ( '' === $body ) {
			return;
		}

		status_header( 200 );
		header( 'Content-Type: text/markdown; charset=utf-8' );
		header( 'X-Robots-Tag: noindex' );

		if ( ! is_user_logged_in() ) {
			header( 'Cache-Control: public, max-age=300, s-maxage=3600, stale-while-revalidate=86400, stale-if-error=604800' );
		}

		echo $body; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Plain text response assembled from stripped, decoded values.
		exit;
	}

	/**
	 * Returns the Markdown URL for a post, when it has one.
	 *
	 * Public so other code — llms.txt, for one — can advertise the mirror
	 * without needing to know how schedule pages are detected.
	 *
	 * @param WP_Post|int|null $post Post or post ID.
	 * @return string URL, or an empty string when there is no Markdown mirror.
	 */
	public static function url_for_post( $post ) {
		$post = get_post( $post );

		if ( ! $post instanceof WP_Post || 'publish' !== $post->post_status || '' !== $post->post_password ) {
			return '';
		}

		$attributes = self::find_block_attributes( $post->post_content );

		if ( null === $attributes ) {
			return '';
		}

		/*
		 * Never advertise a document that would 404. The endpoint renders
		 * nothing until the event's data has been cached at least once, so
		 * check the store — with peek(), which reads what is already there
		 * rather than triggering a fetch of its own.
		 */
		$data = Sessionize_Store::peek( $attributes['apiCode'] );

		if ( ! is_array( $data ) || empty( $data['all'] ) ) {
			return '';
		}

		return self::markdown_url( $post );
	}

	/**
	 * Points agents and crawlers at the Markdown mirror from the HTML page.
	 *
	 * @return void
	 */
	public static function add_alternate_link() {
		if ( ! is_singular() ) {
			return;
		}

		$url = self::url_for_post( get_post() );

		if ( '' === $url ) {
			return;
		}

		printf(
			'<link rel="alternate" type="text/markdown" href="%s" title="%s">' . "\n",
			esc_url( $url ),
			esc_attr__( 'Schedule as Markdown', 'sessionize-blocks' )
		);
	}

	/**
	 * Returns the Markdown URL for a schedule page.
	 *
	 * Pretty permalinks are required: with plain permalinks the schedule page is
	 * addressed by query string, so there is no path to append a suffix to.
	 *
	 * @param WP_Post $post Schedule page.
	 * @return string URL, or an empty string when unavailable.
	 */
	private static function markdown_url( $post ) {
		if ( ! get_option( 'permalink_structure' ) ) {
			return '';
		}

		$permalink = get_permalink( $post );

		if ( ! $permalink || false !== strpos( $permalink, '?' ) ) {
			return '';
		}

		return untrailingslashit( $permalink ) . self::SUFFIX;
	}

	/**
	 * Resolves a request path to a published, publicly readable post.
	 *
	 * Rewrite rules are honoured first, via url_to_postid(): archived events are
	 * served from a rewritten `archive/<year>/` prefix that does not appear in
	 * their slug hierarchy, so matching on the hierarchy alone would miss them.
	 * get_page_by_path() then covers the plain hierarchical case.
	 *
	 * @param string $path Request path without the `.md` suffix.
	 * @return WP_Post|null Matching post, or null.
	 */
	private static function resolve_post( $path ) {
		$path = trim( $path, '/' );

		if ( '' === $path ) {
			return null;
		}

		$post    = null;
		$post_id = url_to_postid( home_url( '/' . $path . '/' ) );

		if ( $post_id > 0 ) {
			$post = get_post( $post_id );
		}

		if ( ! $post instanceof WP_Post ) {
			$post_types = function_exists( 'lfe_get_post_types' ) ? lfe_get_post_types() : array( 'page' );
			$post       = get_page_by_path( $path, OBJECT, $post_types );
		}

		if ( ! $post instanceof WP_Post ) {
			return null;
		}

		// Never expose drafts, private pages or password-protected content.
		if ( 'publish' !== $post->post_status || '' !== $post->post_password ) {
			return null;
		}

		return $post;
	}

	/**
	 * Finds the first Sessionize Schedule block in post content.
	 *
	 * @param string $content Post content.
	 * @return array|null Attributes with block defaults applied, or null when absent.
	 */
	private static function find_block_attributes( $content ) {
		if ( ! has_blocks( $content ) || false === strpos( $content, self::BLOCK_NAME ) ) {
			return null;
		}

		$attributes = self::walk_blocks( parse_blocks( $content ) );

		if ( null === $attributes ) {
			return null;
		}

		$block_type = WP_Block_Type_Registry::get_instance()->get_registered( self::BLOCK_NAME );

		if ( $block_type instanceof WP_Block_Type ) {
			$attributes = $block_type->prepare_attributes_for_render( $attributes );
		}

		return $attributes;
	}

	/**
	 * Recursively looks for a schedule block with a usable API code.
	 *
	 * @param array $blocks Parsed blocks.
	 * @return array|null Raw block attributes, or null.
	 */
	private static function walk_blocks( $blocks ) {
		foreach ( $blocks as $block ) {
			if ( isset( $block['blockName'] ) && self::BLOCK_NAME === $block['blockName'] ) {
				$api_code = isset( $block['attrs']['apiCode'] ) ? (string) $block['attrs']['apiCode'] : '';

				if ( Sessionize_Client::is_valid_api_code( $api_code ) ) {
					return is_array( $block['attrs'] ) ? $block['attrs'] : array();
				}
			}

			if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
				$found = self::walk_blocks( $block['innerBlocks'] );

				if ( null !== $found ) {
					return $found;
				}
			}
		}

		return null;
	}

	/**
	 * Renders the Markdown document.
	 *
	 * @param WP_Post $post       Schedule page.
	 * @param array   $attributes Block attributes.
	 * @return string Markdown, or an empty string when there is no cached data.
	 */
	private static function render( $post, $attributes ) {
		require_once dirname( __DIR__ ) . '/blocks/sessionize-schedule/includes/data.php';

		$data = Sessionize_Store::get( $attributes['apiCode'] );

		if ( ! is_array( $data ) || empty( $data['all'] ) ) {
			return '';
		}

		$config   = sched_build_config( $attributes );
		$sessions = sched_prepare_sessions( $data['all'], $config );

		if ( empty( $sessions ) ) {
			return '';
		}

		$page_url = (string) get_permalink( $post );
		$days     = sched_group_by_day( $sessions );

		$lines = self::render_header( $post, $sessions, $days, $page_url );

		foreach ( $days as $day => $slots ) {
			$lines[] = '## ' . self::day_heading( $day, $config['dateFormat'] );
			$lines[] = '';

			foreach ( $slots as $slot_sessions ) {
				foreach ( $slot_sessions as $session ) {
					$lines = array_merge( $lines, self::render_session( $session, $config, $page_url ) );
				}
			}
		}

		return implode( "\n", $lines ) . "\n";
	}

	/**
	 * Builds the document preamble: what this is, and how to read it.
	 *
	 * @param WP_Post $post     Schedule page.
	 * @param array   $sessions Prepared sessions.
	 * @param array   $days     Sessions grouped by day.
	 * @param string  $page_url Canonical schedule page URL.
	 * @return array Markdown lines.
	 */
	private static function render_header( $post, $sessions, $days, $page_url ) {
		$title = self::event_title( $post );

		$lines   = array();
		$lines[] = '# ' . $title . ' — Schedule';
		$lines[] = '';
		$lines[] = 'Machine-readable mirror of the schedule page. Generated ' . gmdate( 'Y-m-d' ) . '.';
		$lines[] = '';
		$lines[] = '- Source: ' . $page_url;
		$lines[] = '- Sessions: ' . count( $sessions ) . ' across ' . count( $days ) . ' days';
		$lines[] = '- Times are the event\'s local times, exactly as published. Timing and rooms are subject to change.';
		$lines[] = '- Each session links back to the schedule page, which opens that session\'s details.';
		$lines[] = '';

		$tracks = self::track_counts( $sessions );

		if ( ! empty( $tracks ) ) {
			$lines[] = '## Tracks';
			$lines[] = '';

			foreach ( $tracks as $name => $count ) {
				$lines[] = '- ' . $name . ' (' . $count . ')';
			}

			$lines[] = '';
		}

		return $lines;
	}

	/**
	 * Renders a single session.
	 *
	 * @param array  $session  Prepared session.
	 * @param array  $config   Schedule configuration.
	 * @param string $page_url Canonical schedule page URL.
	 * @return array Markdown lines.
	 */
	private static function render_session( $session, $config, $page_url ) {
		$start = sessionize_format_time( $session['start'], $config['timeFormat'] );
		$end   = sessionize_format_time( $session['end'], $config['timeFormat'] );

		$when = '' !== $end ? $start . '–' . $end : $start;

		$lines   = array();
		$lines[] = '### ' . $when . ' · ' . self::to_text( $session['title'] );
		$lines[] = '';

		if ( '' !== $session['room'] ) {
			$lines[] = '- Room: ' . self::to_text( $session['room'] );
		}

		if ( ! empty( $session['speakerNames'] ) ) {
			$lines[] = '- Speakers: ' . self::to_text( implode( ', ', $session['speakerNames'] ) );
		}

		if ( '' !== $session['primaryName'] ) {
			$lines[] = '- Track: ' . self::to_text( $session['primaryName'] );
		}

		$labels = array();
		foreach ( $session['tags'] as $tag ) {
			if ( empty( $tag['isPrimary'] ) ) {
				$labels[] = self::to_text( $tag['name'] );
			}
		}

		if ( ! empty( $labels ) ) {
			$lines[] = '- Labels: ' . implode( ', ', $labels );
		}

		if ( '' !== $session['id'] && '' !== $page_url ) {
			$lines[] = '- Link: ' . add_query_arg( 'id', rawurlencode( $session['id'] ), $page_url );
		}

		if ( '' !== $session['slidesUrl'] ) {
			$lines[] = '- Slides: ' . $session['slidesUrl'];
		}

		if ( '' !== $session['recordingUrl'] ) {
			$lines[] = '- Recording: ' . $session['recordingUrl'];
		}

		foreach ( $session['customLinks'] as $link ) {
			$lines[] = '- ' . self::to_text( $link['label'] ) . ': ' . $link['url'];
		}

		$description = self::to_text( $session['description'] );

		if ( '' !== $description ) {
			$lines[] = '';
			$lines[] = $description;
		}

		$lines[] = '';

		return $lines;
	}

	/**
	 * Counts sessions per track, most populous first.
	 *
	 * @param array $sessions Prepared sessions.
	 * @return array Map of track name to session count.
	 */
	private static function track_counts( $sessions ) {
		$counts = array();

		foreach ( $sessions as $session ) {
			if ( '' === $session['primaryName'] ) {
				continue;
			}

			$name            = self::to_text( $session['primaryName'] );
			$counts[ $name ] = isset( $counts[ $name ] ) ? $counts[ $name ] + 1 : 1;
		}

		arsort( $counts );

		return $counts;
	}

	/**
	 * Formats a day heading, including the year.
	 *
	 * The on-page heading omits the year because the surrounding page supplies
	 * it; a document read on its own cannot rely on that.
	 *
	 * @param string $day_str     Day in Y-m-d form.
	 * @param string $date_format Either 'mdy' or 'dmy'.
	 * @return string Heading text.
	 */
	private static function day_heading( $day_str, $date_format ) {
		$date = sessionize_parse_time( $day_str . 'T00:00:00' );

		if ( null === $date ) {
			return $day_str;
		}

		return 'dmy' === $date_format
			? $date->format( 'l, j F Y' )
			: $date->format( 'l, F j, Y' );
	}

	/**
	 * Returns the event name for the document title.
	 *
	 * Schedule pages are children of an Event, whose title is the event name;
	 * the page's own title is usually just "Schedule".
	 *
	 * @param WP_Post $post Schedule page.
	 * @return string Event name.
	 */
	private static function event_title( $post ) {
		$ancestors = get_post_ancestors( $post );

		if ( ! empty( $ancestors ) ) {
			$root  = end( $ancestors );
			$title = get_the_title( $root );

			if ( '' !== $title ) {
				return self::to_text( $title );
			}
		}

		return self::to_text( get_the_title( $post ) );
	}

	/**
	 * Converts stored HTML to plain text suitable for a Markdown document.
	 *
	 * Block-level tags become line breaks before the rest are stripped, so
	 * paragraphs in an abstract survive as paragraphs rather than running
	 * together into a single wall of text.
	 *
	 * @param string $html Source HTML.
	 * @return string Plain text.
	 */
	private static function to_text( $html ) {
		$html = (string) $html;

		if ( '' === $html ) {
			return '';
		}

		$html = preg_replace( '#<br\s*/?>#i', "\n", $html );
		$html = preg_replace( '#</(p|div|h[1-6])>#i', "\n\n", $html );
		$html = preg_replace( '#<li[^>]*>#i', "\n- ", $html );

		$text = wp_strip_all_tags( $html );
		$text = html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );

		// Collapse runs of spaces without touching the line breaks added above.
		$text = preg_replace( '/[ \t]+/', ' ', $text );
		$text = preg_replace( '/ *\n */', "\n", $text );
		$text = preg_replace( '/\n{3,}/', "\n\n", $text );

		return trim( $text );
	}
}
